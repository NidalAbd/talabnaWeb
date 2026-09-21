<?php

namespace Tests\Feature\Points;

use App\Models\User;
use App\Services\Apple\SignedTransactionVerifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Passport;
use Tests\Concerns\MigratesTolerantly;
use Tests\Support\AppleJwsFactory;
use Tests\TestCase;

/**
 * iPhone purchases: the app sends the StoreKit 2 signed transaction. It is verified locally against Apple's root, so it works
 * with NO shared secret configured and never calls Apple.
 */
class AppleSignedPurchaseTest extends TestCase
{
    use MigratesTolerantly;

    private const URL = '/api/points/apple-verify';
    private AppleJwsFactory $apple;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateTolerantly();
        $this->apple = new AppleJwsFactory();
        $this->app->instance(SignedTransactionVerifier::class, new SignedTransactionVerifier($this->apple->rootPem));
        config(['services.apple_iap.shared_secret' => null, 'services.apple_iap.bundle_id' => 'com.talabna.talabna']);

        $id = DB::table('users')->insertGetId([
            'name' => 'buyer', 'user_name' => 'buyer1', 'email' => 'b@example.com', 'gender' => 'ذكر', 'password' => 'x',
            'auth_type' => 'email', 'is_active' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->user = User::find($id);
        Passport::actingAs($this->user);
        Http::fake(); // nothing may be sent to Apple
    }

    private function jws(array $claims = []): string
    {
        return $this->apple->transaction(array_merge(['bundleId' => 'com.talabna.talabna', 'productId' => 'points_10', 'transactionId' => '2000000555'], $claims));
    }

    private function buy(string $jws, string $product = 'points_10', string $tx = '2000000555')
    {
        return $this->postJson(self::URL, ['product_id' => $product, 'receipt' => $jws, 'transaction_id' => $tx]);
    }

    private function balance(): int
    {
        return (int) DB::table('palservice_points')->where('user_id', $this->user->id)->value('point');
    }

    public function test_a_signed_purchase_credits_points_without_any_shared_secret_and_without_calling_apple(): void
    {
        $this->buy($this->jws())->assertOk()->assertJsonPath('points', 10)->assertJsonPath('duplicate', false);

        $this->assertSame(10, $this->balance());
        $this->assertDatabaseHas('point_purchase_requests', ['user_id' => $this->user->id, 'apple_transaction_id' => '2000000555', 'points_requested' => 10]);
        Http::assertNothingSent();
    }

    public function test_replaying_the_same_signed_transaction_credits_once(): void
    {
        $this->buy($this->jws())->assertOk();
        $this->buy($this->jws())->assertOk()->assertJsonPath('duplicate', true);

        $this->assertSame(10, $this->balance());
    }

    public function test_someone_elses_transaction_cannot_be_claimed(): void
    {
        $this->buy($this->jws())->assertOk();

        $other = DB::table('users')->insertGetId(['name' => 'x', 'user_name' => 'other1', 'email' => 'o@example.com', 'gender' => 'ذكر', 'password' => 'x', 'auth_type' => 'email', 'is_active' => 'active', 'created_at' => now(), 'updated_at' => now()]);
        Passport::actingAs(User::find($other));
        $this->buy($this->jws())->assertStatus(409);
    }

    public function test_a_forged_or_foreign_transaction_credits_nothing_and_is_final(): void
    {
        $forger = new AppleJwsFactory();
        $forged = $forger->transaction(['bundleId' => 'com.talabna.talabna', 'productId' => 'points_100', 'transactionId' => '777']);

        $this->buy($forged, 'points_100', '777')->assertStatus(422)->assertJsonPath('retry', false);
        $this->assertSame(0, $this->balance());
    }

    public function test_the_wrong_app_product_or_transaction_is_rejected(): void
    {
        $this->buy($this->jws(['bundleId' => 'com.someone.else']))->assertStatus(422);
        $this->buy($this->jws(['productId' => 'points_1']))->assertStatus(422);            // says points_10 was bought, JWS says points_1
        $this->buy($this->jws(), 'points_10', 'made-up-tx')->assertStatus(422);
        $this->assertSame(0, $this->balance());
    }

    public function test_a_refunded_transaction_is_rejected(): void
    {
        $this->buy($this->jws(['revocationDate' => 1_700_000_000_000, 'revocationReason' => 0]))->assertStatus(422);
        $this->assertSame(0, $this->balance());
    }

    public function test_an_unknown_product_is_rejected_before_any_verification(): void
    {
        $this->buy($this->jws(['productId' => 'points_999']), 'points_999')->assertStatus(400);
    }
}
