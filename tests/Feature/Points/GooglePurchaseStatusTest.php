<?php

namespace Tests\Feature\Points;

use App\Models\User;
use App\Services\GooglePlayVerificationService;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Mockery\MockInterface;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

/** Google Play points purchases: what the app is told decides whether it keeps the paid purchase open. */
class GooglePurchaseStatusTest extends TestCase
{
    use MigratesTolerantly;

    private const URL = '/api/points/google-verify';
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateTolerantly();
        $id = DB::table('users')->insertGetId([
            'name' => 'buyer', 'user_name' => 'buyer1', 'email' => 'b@example.com', 'gender' => 'ذكر', 'password' => 'x',
            'auth_type' => 'email', 'is_active' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->user = User::find($id);
        Passport::actingAs($this->user);
    }

    /** One or several consecutive answers from Google (the last one repeats). */
    private function verifyReturns(array ...$results): void
    {
        $this->mock(GooglePlayVerificationService::class, function (MockInterface $m) use ($results) {
            $m->shouldReceive('getPointsForProduct')->andReturnUsing(fn ($id) => GooglePlayVerificationService::PRODUCT_POINTS[$id] ?? null);
            $m->shouldReceive('verifyPurchase')->andReturn(...$results);
            $m->shouldReceive('acknowledgePurchase')->andReturn(true);
        });
    }

    private function buy(string $order = 'GPA.1'): \Illuminate\Testing\TestResponse
    {
        return $this->postJson(self::URL, ['product_id' => 'points_10', 'purchase_token' => 'tok', 'order_id' => $order]);
    }

    private function balance(): int
    {
        return (int) DB::table('palservice_points')->where('user_id', $this->user->id)->value('point');
    }

    public function test_a_valid_purchase_credits_once_and_a_replay_is_success_without_a_second_credit(): void
    {
        $this->verifyReturns(['verified' => true, 'order_id' => 'GPA.1']);

        $this->buy()->assertOk()->assertJsonPath('points', 10)->assertJsonPath('duplicate', false);
        $this->buy()->assertOk()->assertJsonPath('duplicate', true);

        $this->assertSame(10, $this->balance());
    }

    public function test_google_or_our_side_failing_is_503_and_retryable_so_the_app_keeps_the_paid_purchase(): void
    {
        $this->verifyReturns(['verified' => false, 'error' => 'Failed to get access token', 'transient' => true]);

        $this->buy('GPA.2')->assertStatus(503)->assertJsonPath('retry', true)->assertJsonPath('success', false);
        $this->assertSame(0, $this->balance());
    }

    public function test_a_pending_payment_is_202_not_a_failure(): void
    {
        $this->verifyReturns(['verified' => false, 'error' => 'Payment is still pending', 'pending' => true]);

        $this->buy('GPA.3')->assertStatus(202)->assertJsonPath('pending', true)->assertJsonPath('retry', true);
    }

    public function test_a_token_google_says_is_invalid_is_final_422(): void
    {
        $this->verifyReturns(['verified' => false, 'error' => 'Verification request failed', 'transient' => false]);

        $this->buy('GPA.4')->assertStatus(422)->assertJsonPath('retry', false);
        $this->assertSame(0, $this->balance());
    }

    public function test_after_the_outage_the_same_purchase_credits_normally(): void
    {
        $this->verifyReturns(['verified' => false, 'error' => 'down', 'transient' => true], ['verified' => true, 'order_id' => 'GPA.5']);
        $this->buy('GPA.5')->assertStatus(503);

        $this->buy('GPA.5')->assertOk()->assertJsonPath('duplicate', false);
        $this->assertSame(10, $this->balance());
    }
}
