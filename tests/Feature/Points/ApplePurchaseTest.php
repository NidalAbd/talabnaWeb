<?php

namespace Tests\Feature\Points;

use App\Models\User;
use App\Services\AppleIapVerificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Passport;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

/** App Store points purchases. Apple's verifyReceipt is faked: no network or real receipt needed. */
class ApplePurchaseTest extends TestCase
{
    use MigratesTolerantly;

    private const URL = '/api/points/apple-verify';

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateTolerantly();
        config([
            'services.apple_iap.shared_secret' => 'test-secret',
            'services.apple_iap.bundle_id' => 'com.talabna.talabna',
        ]);
    }

    public function test_valid_purchase_credits_points_once(): void
    {
        $user = $this->actingUser();
        $this->fakeApple($this->receipt('points_10', 'tx-1'));

        $this->postJson(self::URL, $this->body('points_10', 'tx-1'))
            ->assertOk()->assertJsonPath('points', 10)->assertJsonPath('duplicate', false);

        $this->assertSame(10, $this->balance($user));
        $this->assertDatabaseHas('point_purchase_requests', [
            'user_id' => $user->id, 'apple_transaction_id' => 'tx-1', 'approval_type' => 'apple_iap', 'points_requested' => 10,
        ]);
        $this->assertDatabaseHas('point_transactions', ['to_user_id' => $user->id, 'type' => 'purchase', 'point' => 10]);
    }

    public function test_replaying_the_same_receipt_does_not_credit_twice(): void
    {
        $user = $this->actingUser();
        $this->fakeApple($this->receipt('points_5', 'tx-2'));

        $this->postJson(self::URL, $this->body('points_5', 'tx-2'))->assertOk();
        $this->postJson(self::URL, $this->body('points_5', 'tx-2'))->assertOk()->assertJsonPath('duplicate', true);

        $this->assertSame(5, $this->balance($user));
    }

    public function test_another_account_cannot_claim_a_processed_transaction(): void
    {
        $this->actingUser();
        $this->fakeApple($this->receipt('points_5', 'tx-3'));
        $this->postJson(self::URL, $this->body('points_5', 'tx-3'))->assertOk();

        $thief = $this->actingUser();
        $this->postJson(self::URL, $this->body('points_5', 'tx-3'))->assertStatus(409);
        $this->assertSame(0, $this->balance($thief));
    }

    public function test_sandbox_receipt_is_retried_against_sandbox(): void
    {
        $user = $this->actingUser();
        Http::fake([
            AppleIapVerificationService::PRODUCTION_URL => Http::response(['status' => 21007]),
            AppleIapVerificationService::SANDBOX_URL => Http::response($this->receipt('points_1', 'tx-4')),
        ]);

        $this->postJson(self::URL, $this->body('points_1', 'tx-4'))->assertOk();
        Http::assertSent(fn ($r) => $r->url() === AppleIapVerificationService::SANDBOX_URL);
        $this->assertSame(1, $this->balance($user));
    }

    public function test_receipt_from_another_app_is_rejected(): void
    {
        $user = $this->actingUser();
        $this->fakeApple($this->receipt('points_10', 'tx-5', 'com.someone.else'));

        $this->postJson(self::URL, $this->body('points_10', 'tx-5'))->assertStatus(422);
        $this->assertSame(0, $this->balance($user));
    }

    public function test_transaction_id_must_be_in_the_receipt(): void
    {
        $user = $this->actingUser();
        $this->fakeApple($this->receipt('points_10', 'real-tx'));

        $this->postJson(self::URL, $this->body('points_10', 'made-up-tx'))->assertStatus(422);
        $this->assertSame(0, $this->balance($user));
    }

    public function test_product_must_match_the_receipt(): void
    {
        $user = $this->actingUser();
        $this->fakeApple($this->receipt('points_1', 'tx-6')); // paid for 1 point...

        $this->postJson(self::URL, $this->body('points_100', 'tx-6'))->assertStatus(422); // ...claims 100
        $this->assertSame(0, $this->balance($user));
    }

    public function test_refunded_purchase_is_rejected(): void
    {
        $user = $this->actingUser();
        $this->fakeApple($this->receipt('points_10', 'tx-7', 'com.talabna.talabna', ['cancellation_date_ms' => '1700000000000']));

        $this->postJson(self::URL, $this->body('points_10', 'tx-7'))->assertStatus(422);
        $this->assertSame(0, $this->balance($user));
    }

    public function test_unknown_product_is_rejected_without_calling_apple(): void
    {
        $this->actingUser();
        Http::fake();

        $this->postJson(self::URL, $this->body('points_999999', 'tx-8'))->assertStatus(400);
        Http::assertNothingSent();
    }

    public function test_fails_closed_when_shared_secret_is_missing(): void
    {
        config(['services.apple_iap.shared_secret' => null]);
        $user = $this->actingUser();
        Http::fake();

        // 503 + retry: it is OUR configuration that is missing, not the customer's purchase that is bad, so the app keeps the
        // paid transaction open and credits it as soon as this is fixed.
        $this->postJson(self::URL, $this->body('points_10', 'tx-9'))->assertStatus(503)->assertJsonPath('retry', true);
        Http::assertNothingSent();
        $this->assertSame(0, $this->balance($user));
    }

    public function test_apple_being_unreachable_credits_nothing(): void
    {
        $user = $this->actingUser();
        Http::fake([AppleIapVerificationService::PRODUCTION_URL => Http::response('', 503)]);

        $this->postJson(self::URL, $this->body('points_10', 'tx-10'))->assertStatus(503)->assertJsonPath('retry', true);
        $this->assertSame(0, $this->balance($user));
    }

    public function test_apple_being_down_is_retryable_but_a_bad_receipt_is_final(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class); // six calls in one test
        $user = $this->actingUser();

        foreach ([21005, 21009, 21100] as $status) {
            Http::swap(new \Illuminate\Http\Client\Factory());
            $this->fakeApple(['status' => $status]);
            $this->postJson(self::URL, $this->body('points_10', "tx-down-$status"))->assertStatus(503)->assertJsonPath('retry', true);
        }
        foreach ([21002, 21008, 21010] as $status) {
            Http::swap(new \Illuminate\Http\Client\Factory());
            $this->fakeApple(['status' => $status]);
            $this->postJson(self::URL, $this->body('points_10', "tx-bad-$status"))->assertStatus(422)->assertJsonPath('retry', false);
        }
        $this->assertSame(0, $this->balance($user));
    }

    public function test_requires_authentication(): void
    {
        $this->postJson(self::URL, $this->body('points_10', 'tx-11'))->assertStatus(401);
    }

    // ── helpers ─────────────────────────────────────────────────────────

    private function actingUser(): User
    {
        $id = DB::table('users')->insertGetId([
            'name' => 'Buyer', 'user_name' => 'buyer'.rand(1, 999999), 'email' => 'b'.rand(1, 999999).'@example.com',
            'gender' => 'ذكر', 'password' => 'x', 'auth_type' => 'email', 'is_active' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $user = User::find($id);
        Passport::actingAs($user);

        return $user;
    }

    private function balance(User $user): int
    {
        return (int) DB::table('palservice_points')->where('user_id', $user->id)->sum('point');
    }

    private function body(string $product, string $tx): array
    {
        return ['product_id' => $product, 'receipt' => base64_encode('fake-receipt'), 'transaction_id' => $tx];
    }

    private function fakeApple(array $payload): void
    {
        Http::fake([AppleIapVerificationService::PRODUCTION_URL => Http::response($payload)]);
    }

    private function receipt(string $product, string $tx, string $bundle = 'com.talabna.talabna', array $extra = []): array
    {
        return [
            'status' => 0,
            'receipt' => [
                'bundle_id' => $bundle,
                'in_app' => [array_merge(['product_id' => $product, 'transaction_id' => $tx, 'quantity' => '1'], $extra)],
            ],
        ];
    }
}
