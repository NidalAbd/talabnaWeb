<?php

namespace Tests\Feature\Ai;

use App\Models\AiRequest;
use App\Models\User;
use App\Services\Ai\AiLedger;
use App\Services\Ai\AiSettler;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Ramsey\Uuid\Uuid;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

/**
 * Paid AI: the server takes the price from its own table, charges BEFORE calling the provider, and gives the points back
 * exactly once whenever the provider fails, times out or is blocked. Every action leaves an ai_requests row.
 */
class AiActionsTest extends TestCase
{
    use MigratesTolerantly;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateTolerantly();
        DB::statement('PRAGMA foreign_keys = OFF');
        config(['services.openai.key' => 'test-key']);
        Storage::fake('local');

        // Production MySQL has 'refund' in the type enum (a MySQL-only migration); give the test ledger a plain string.
        Schema::dropIfExists('point_transactions');
        Schema::create('point_transactions', function ($t) {
            $t->id();
            $t->unsignedBigInteger('from_user_id')->nullable();
            $t->unsignedBigInteger('to_user_id')->nullable();
            $t->string('type');
            $t->integer('point');
            $t->string('status')->nullable();
            $t->text('metadata')->nullable();
            $t->timestamps();
        });

        $this->user = $this->makeUser('writer1', 30);
        Passport::actingAs($this->user);
    }

    private function makeUser(string $name, int $points): User
    {
        $id = DB::table('users')->insertGetId([
            'name' => $name, 'user_name' => $name, 'email' => "$name@example.com", 'gender' => 'ذكر', 'password' => 'x',
            'auth_type' => 'email', 'is_active' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('palservice_points')->insert(['user_id' => $id, 'point' => $points, 'created_at' => now(), 'updated_at' => now()]);

        return User::find($id);
    }

    private function fake(array $routes): void
    {
        Http::swap(new Factory());
        Http::fake($routes);
    }

    private function chat(array $json): array
    {
        return ['api.openai.com/v1/chat/completions' => Http::response(['choices' => [['message' => ['content' => json_encode($json)]]]])];
    }

    private function balance(?User $u = null): int
    {
        return (int) DB::table('palservice_points')->where('user_id', ($u ?? $this->user)->id)->value('point');
    }

    private function id(): string
    {
        return (string) Uuid::uuid4();
    }

    // ── pricing ───────────────────────────────────────────────────────────

    public function test_pricing_lists_every_action_its_cost_and_the_confirm_threshold(): void
    {
        $res = $this->getJson('/api/ai/pricing')->assertOk();

        $this->assertSame(30, $res->json('balance'));
        $this->assertSame(3, $res->json('confirm_from'));
        foreach (['enhance_post' => 2, 'translate_post' => 2, 'suggest_category' => 1, 'suggest_price' => 1, 'generate_image' => 3, 'generate_video' => 20] as $key => $cost) {
            $this->assertSame($cost, $res->json("features.$key.points"), $key);
            $this->assertTrue($res->json("features.$key.enabled"), $key);
        }
    }

    // ── text actions ──────────────────────────────────────────────────────

    public function test_improving_title_and_description_together_is_one_charge_and_one_record(): void
    {
        $this->fake($this->chat(['title' => 'Toyota Corolla 2018', 'description' => 'Clean car, one owner.']));

        $id = $this->id();
        $this->postJson('/api/ai/enhance-post', ['request_id' => $id, 'title' => 'toyota 2018', 'description' => 'good car', 'language' => 'en'])
            ->assertOk()
            ->assertJsonPath('status', 'succeeded')
            ->assertJsonPath('points_charged', 2)
            ->assertJsonPath('balance', 28)
            ->assertJsonPath('result.title', 'Toyota Corolla 2018')
            ->assertJsonPath('result.description', 'Clean car, one owner.');

        $this->assertSame(28, $this->balance());
        $this->assertDatabaseHas('ai_requests', ['uuid' => $id, 'user_id' => $this->user->id, 'feature' => 'enhance_post', 'points' => 2, 'status' => 'succeeded']);
        $this->assertDatabaseHas('point_transactions', ['from_user_id' => $this->user->id, 'type' => 'used', 'point' => 2]);
        Http::assertSentCount(1);
    }

    public function test_translation_returns_both_texts_for_one_charge(): void
    {
        $this->fake($this->chat(['title' => 'Voiture Toyota', 'description' => 'Bon état.']));

        $this->postJson('/api/ai/translate-post', ['request_id' => $this->id(), 'title' => 'Toyota car', 'description' => 'Good condition.', 'source_language' => 'en', 'target_language' => 'fr'])
            ->assertOk()->assertJsonPath('result.title', 'Voiture Toyota')->assertJsonPath('result.description', 'Bon état.')->assertJsonPath('points_charged', 2);

        Http::assertSent(fn ($r) => str_contains($r['messages'][0]['content'], 'from en to fr'));
        $this->assertSame(28, $this->balance());
    }

    public function test_translating_into_the_same_language_is_refused_before_any_charge(): void
    {
        $this->fake([]);
        $this->postJson('/api/ai/translate-post', ['request_id' => $this->id(), 'title' => 'x y z', 'source_language' => 'en', 'target_language' => 'en'])->assertStatus(422);
        $this->assertSame(30, $this->balance());
    }

    public function test_category_suggestion_only_returns_ids_that_exist(): void
    {
        $cat = DB::table('categories')->insertGetId(['name' => json_encode(['en' => 'Cars', 'ar' => 'سيارات']), 'created_at' => now(), 'updated_at' => now()]);
        $sub = DB::table('sub_categories')->insertGetId(['categories_id' => $cat, 'name' => json_encode(['en' => 'Sedan']), 'created_at' => now(), 'updated_at' => now()]);

        $this->fake($this->chat(['category_id' => $cat, 'sub_category_id' => $sub]));
        $this->postJson('/api/ai/suggest-category', ['request_id' => $this->id(), 'title' => 'Toyota Corolla sedan'])
            ->assertOk()->assertJsonPath('result.category_id', $cat)->assertJsonPath('result.sub_category_id', $sub);

        // The model invents an id: a failure, refunded.
        $this->fake($this->chat(['category_id' => 999999, 'sub_category_id' => 5]));
        $before = $this->balance();
        $this->postJson('/api/ai/suggest-category', ['request_id' => $this->id(), 'title' => 'Toyota Corolla sedan'])->assertStatus(422);
        $this->assertSame($before, $this->balance());
    }

    public function test_price_suggestion_and_a_nonsense_answer_that_is_refunded(): void
    {
        $this->fake($this->chat(['low' => 200000, 'typical' => 250000, 'high' => 300000, 'note' => 'Rough estimate only.']));
        $this->postJson('/api/ai/suggest-price', ['request_id' => $this->id(), 'title' => 'Corolla 2018', 'currency' => 'EGP'])
            ->assertOk()->assertJsonPath('result.typical', 250000)->assertJsonPath('points_charged', 1);

        $this->fake($this->chat(['low' => 900, 'typical' => 100, 'high' => 50, 'note' => 'x']));
        $before = $this->balance();
        $this->postJson('/api/ai/suggest-price', ['request_id' => $this->id(), 'title' => 'Corolla 2018', 'currency' => 'EGP'])->assertStatus(422);
        $this->assertSame($before, $this->balance());
    }

    // ── charging rules ────────────────────────────────────────────────────

    public function test_the_price_comes_from_the_server_table_and_a_client_price_is_ignored(): void
    {
        DB::table('ai_features')->where('key', 'enhance_post')->update(['points_cost' => 5]);
        $this->fake($this->chat(['title' => 'A', 'description' => 'B']));

        $this->postJson('/api/ai/enhance-post', ['request_id' => $this->id(), 'title' => 'a b c', 'points' => 0, 'cost' => 0])
            ->assertOk()->assertJsonPath('points_charged', 5);
        $this->assertSame(25, $this->balance());
    }

    public function test_not_enough_points_is_refused_without_calling_the_ai_or_charging(): void
    {
        DB::table('palservice_points')->where('user_id', $this->user->id)->update(['point' => 1]);
        $this->fake([]);

        $this->postJson('/api/ai/enhance-post', ['request_id' => $this->id(), 'title' => 'a b c'])
            ->assertStatus(402)->assertJsonPath('required', 2)->assertJsonPath('balance', 1);

        Http::assertNothingSent();
        $this->assertSame(0, AiRequest::count());
        $this->assertDatabaseMissing('point_transactions', ['type' => 'used']);
    }

    public function test_a_provider_failure_refunds_the_points_and_is_recorded(): void
    {
        $this->fake(['api.openai.com/*' => Http::response(['error' => ['message' => 'boom']], 500)]);

        $id = $this->id();
        $this->postJson('/api/ai/enhance-post', ['request_id' => $id, 'title' => 'a b c'])
            ->assertStatus(502)->assertJsonPath('status', 'failed')->assertJsonPath('refunded', true)->assertJsonPath('balance', 30);

        $this->assertSame(30, $this->balance(), 'a user must never pay for a failed AI call');
        $row = AiRequest::where('uuid', $id)->first();
        $this->assertSame('failed', $row->status);
        $this->assertSame('provider_error', $row->error_code);
        $this->assertNotNull($row->refund_transaction_id);
        $this->assertDatabaseHas('point_transactions', ['type' => 'used', 'point' => 2]);
        $this->assertDatabaseHas('point_transactions', ['type' => 'refund', 'point' => 2]);
    }

    public function test_an_unreachable_provider_refunds_too(): void
    {
        $this->fake(['api.openai.com/*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout')]);

        $this->postJson('/api/ai/enhance-post', ['request_id' => $this->id(), 'title' => 'a b c'])->assertStatus(502);
        $this->assertSame(30, $this->balance());
        $this->assertSame('provider_unreachable', AiRequest::first()->error_code);
    }

    public function test_repeating_the_same_request_id_never_charges_twice(): void
    {
        $this->fake($this->chat(['title' => 'A', 'description' => 'B']));
        $id = $this->id();
        $body = ['request_id' => $id, 'title' => 'a b c'];

        $this->postJson('/api/ai/enhance-post', $body)->assertOk();
        $this->postJson('/api/ai/enhance-post', $body)->assertOk()->assertJsonPath('result.title', 'A');

        $this->assertSame(28, $this->balance());
        $this->assertSame(1, AiRequest::count());
        Http::assertSentCount(1);
    }

    public function test_a_disabled_feature_is_refused_and_free(): void
    {
        DB::table('ai_features')->where('key', 'suggest_price')->update(['enabled' => false]);
        $this->fake([]);

        $this->postJson('/api/ai/suggest-price', ['request_id' => $this->id(), 'title' => 'a b c', 'currency' => 'EGP'])->assertStatus(403);
        $this->assertSame(30, $this->balance());
        Http::assertNothingSent();
    }

    public function test_without_an_openai_key_nothing_is_charged(): void
    {
        config(['services.openai.key' => null]);
        $this->postJson('/api/ai/enhance-post', ['request_id' => $this->id(), 'title' => 'a b c'])->assertStatus(503);
        $this->assertSame(30, $this->balance());
    }

    public function test_a_second_refund_never_happens(): void
    {
        $this->fake(['api.openai.com/*' => Http::response([], 500)]);
        $this->postJson('/api/ai/enhance-post', ['request_id' => $this->id(), 'title' => 'a b c'])->assertStatus(502);

        $ai = AiRequest::first();
        app(AiLedger::class)->fail($ai, 'again', 'again');
        app(AiSettler::class)->settle($ai);

        $this->assertSame(30, $this->balance());
        $this->assertSame(1, DB::table('point_transactions')->where('type', 'refund')->count());
    }

    // ── image ─────────────────────────────────────────────────────────────

    private function jpegB64(): string
    {
        return base64_encode(str_repeat('J', 4000));
    }

    public function test_image_generation_charges_stores_the_file_and_serves_it_to_its_owner_only(): void
    {
        $this->fake(['api.openai.com/v1/images/generations' => Http::response(['data' => [['b64_json' => $this->jpegB64()]]])]);

        $id = $this->id();
        $res = $this->postJson('/api/ai/generate-image', ['request_id' => $id, 'prompt' => 'a red bicycle leaning on a wall'])
            ->assertOk()->assertJsonPath('points_charged', 3)->assertJsonPath('file_type', 'image')->assertJsonPath('balance', 27);
        $this->assertStringEndsWith("/api/ai/requests/$id/file", $res->json('file_url'));
        Storage::disk('local')->assertExists("ai-results/$id.jpg");
        Http::assertSent(fn ($r) => str_contains($r['prompt'], 'a red bicycle') && $r['model'] === 'gpt-image-1');

        $this->get("/api/ai/requests/$id/file")->assertOk()->assertHeader('Content-Type', 'image/jpeg');

        Passport::actingAs($this->makeUser('other1', 5));
        $this->get("/api/ai/requests/$id/file")->assertStatus(404);
        $this->getJson("/api/ai/requests/$id")->assertStatus(404);
    }

    public function test_a_blocked_image_prompt_is_refunded_with_a_clear_message(): void
    {
        $this->fake(['api.openai.com/v1/images/generations' => Http::response(['error' => ['code' => 'moderation_blocked', 'message' => 'safety system']], 400)]);

        $this->postJson('/api/ai/generate-image', ['request_id' => $this->id(), 'prompt' => 'something not allowed'])
            ->assertStatus(422)->assertJsonPath('code', 'blocked')->assertJsonPath('refunded', true);

        $this->assertSame(30, $this->balance());
        $this->assertSame([], Storage::disk('local')->allFiles('ai-results'));
    }

    public function test_an_image_answer_without_an_image_is_refunded(): void
    {
        $this->fake(['api.openai.com/v1/images/generations' => Http::response(['data' => [['b64_json' => '']]])]);

        $this->postJson('/api/ai/generate-image', ['request_id' => $this->id(), 'prompt' => 'a red bicycle'])->assertStatus(422);
        $this->assertSame(30, $this->balance());
    }

    // ── video ─────────────────────────────────────────────────────────────

    public function test_video_is_charged_at_start_and_delivered_when_the_job_completes(): void
    {
        $this->fake([
            'api.openai.com/v1/videos/video_123/content' => Http::response(str_repeat('V', 5000), 200, ['Content-Type' => 'video/mp4']),
            'api.openai.com/v1/videos/video_123' => Http::sequence()
                ->push(['id' => 'video_123', 'status' => 'in_progress'])
                ->push(['id' => 'video_123', 'status' => 'completed']),
            'api.openai.com/v1/videos' => Http::response(['id' => 'video_123', 'status' => 'queued']),
        ]);

        $id = $this->id();
        $this->postJson('/api/ai/generate-video', ['request_id' => $id, 'prompt' => 'a bicycle rotating slowly'])
            ->assertStatus(202)->assertJsonPath('status', 'processing')->assertJsonPath('points_charged', 20)->assertJsonPath('balance', 10);
        $this->assertSame('video_123', AiRequest::where('uuid', $id)->value('provider_job_id'));

        $this->getJson("/api/ai/requests/$id")->assertStatus(202)->assertJsonPath('status', 'processing');
        $this->getJson("/api/ai/requests/$id")->assertOk()->assertJsonPath('status', 'succeeded')->assertJsonPath('file_type', 'video');

        Storage::disk('local')->assertExists("ai-results/$id.mp4");
        $this->assertSame(10, $this->balance());
        $this->get("/api/ai/requests/$id/file")->assertOk()->assertHeader('Content-Type', 'video/mp4');
    }

    public function test_a_video_that_fails_at_the_provider_is_refunded_when_the_app_checks(): void
    {
        $this->fake([
            'api.openai.com/v1/videos/video_9' => Http::response(['status' => 'failed', 'error' => ['code' => 'moderation_blocked']]),
            'api.openai.com/v1/videos' => Http::response(['id' => 'video_9']),
        ]);
        $id = $this->id();
        $this->postJson('/api/ai/generate-video', ['request_id' => $id, 'prompt' => 'a bicycle rotating slowly'])->assertStatus(202);
        $this->assertSame(10, $this->balance());

        $this->getJson("/api/ai/requests/$id")->assertStatus(422)->assertJsonPath('refunded', true);
        $this->assertSame(30, $this->balance());
    }

    public function test_a_video_that_cannot_be_started_is_refunded_immediately(): void
    {
        $this->fake(['api.openai.com/v1/videos' => Http::response(['error' => ['message' => 'no access']], 404)]);

        $this->postJson('/api/ai/generate-video', ['request_id' => $this->id(), 'prompt' => 'a bicycle rotating slowly'])
            ->assertStatus(502)->assertJsonPath('refunded', true)->assertJsonPath('code', 'provider_no_access');
        $this->assertSame(30, $this->balance());
    }

    public function test_an_abandoned_video_is_refunded_by_the_scheduled_settler_even_if_the_app_never_returns(): void
    {
        $this->fake([
            'api.openai.com/v1/videos/video_7' => Http::response(['status' => 'in_progress']),
            'api.openai.com/v1/videos' => Http::response(['id' => 'video_7']),
        ]);
        $id = $this->id();
        $this->postJson('/api/ai/generate-video', ['request_id' => $id, 'prompt' => 'a bicycle rotating slowly'])->assertStatus(202);

        app(AiSettler::class)->settleAll();
        $this->assertSame(10, $this->balance(), 'still running: nothing refunded yet');

        AiRequest::where('uuid', $id)->update(['created_at' => now()->subMinutes(25)]);
        $this->artisan('ai-points:settle')->assertExitCode(0);

        $row = AiRequest::where('uuid', $id)->first();
        $this->assertSame('failed', $row->status);
        $this->assertSame('timeout', $row->error_code);
        $this->assertSame(30, $this->balance());
    }

    public function test_a_crashed_text_request_left_processing_is_refunded_after_its_time_limit(): void
    {
        $ai = app(AiLedger::class)->start($this->user->id, 'enhance_post', $this->id());
        $this->assertSame(28, $this->balance());

        app(AiSettler::class)->settle($ai);
        $this->assertSame(28, $this->balance(), 'not overdue yet');

        AiRequest::whereKey($ai->id)->update(['created_at' => now()->subMinutes(10)]);
        app(AiSettler::class)->settle($ai);
        $this->assertSame(30, $this->balance());
        $this->assertSame('timeout', $ai->refresh()->error_code);
    }

    public function test_only_one_video_can_be_in_flight_per_user(): void
    {
        $this->fake(['api.openai.com/v1/videos' => Http::response(['id' => 'video_1'])]);
        $this->postJson('/api/ai/generate-video', ['request_id' => $this->id(), 'prompt' => 'a bicycle rotating slowly'])->assertStatus(202);

        $this->postJson('/api/ai/generate-video', ['request_id' => $this->id(), 'prompt' => 'another bicycle clip please'])->assertStatus(429);
        $this->assertSame(10, $this->balance(), 'the refused second request is free');
    }

    // ── auditing ──────────────────────────────────────────────────────────

    public function test_the_admin_summary_reconciles_charges_refunds_and_flags_nothing_when_all_is_well(): void
    {
        $this->fake($this->chat(['title' => 'A', 'description' => 'B']));
        $this->postJson('/api/ai/enhance-post', ['request_id' => $this->id(), 'title' => 'a b c'])->assertOk();
        $this->fake(['api.openai.com/*' => Http::response([], 500)]);
        $this->postJson('/api/ai/enhance-post', ['request_id' => $this->id(), 'title' => 'a b c'])->assertStatus(502);

        $data = app(\App\Http\Controllers\Api\AiMonitorController::class)->summary(new \Illuminate\Http\Request())->getData(true);

        $this->assertSame(2, $data['totals']['requests']);
        $this->assertSame(2, $data['totals']['points_earned']);
        $this->assertSame(2, $data['totals']['points_refunded']);
        $this->assertSame(['charge_without_request' => 0, 'failed_without_refund' => 0, 'refunded_twice' => 0], $data['integrity']);
        $this->assertSame(['refund_failed' => 0, 'overdue_processing' => 0], $data['attention']);
    }

    public function test_the_integrity_check_catches_a_charge_that_has_no_request_row(): void
    {
        DB::table('point_transactions')->insert([
            'from_user_id' => $this->user->id, 'to_user_id' => $this->user->id, 'type' => 'used', 'point' => 2, 'status' => 'completed',
            'metadata' => json_encode(['reason' => 'ai', 'feature' => 'enhance_post']), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $data = app(\App\Http\Controllers\Api\AiMonitorController::class)->summary(new \Illuminate\Http\Request())->getData(true);
        $this->assertSame(1, $data['integrity']['charge_without_request']);
    }

    public function test_history_lists_my_actions_with_refunds(): void
    {
        $this->fake(['api.openai.com/*' => Http::response([], 500)]);
        $this->postJson('/api/ai/enhance-post', ['request_id' => $this->id(), 'title' => 'a b c'])->assertStatus(502);

        $this->getJson('/api/ai/requests')->assertOk()->assertJsonPath('0.feature', 'enhance_post')->assertJsonPath('0.refunded', true);
    }

    public function test_the_admin_ai_usage_pages_are_admin_only_and_an_admin_can_refund_a_stuck_request(): void
    {
        $ai = app(AiLedger::class)->start($this->user->id, 'generate_image', $this->id());
        $this->assertSame(27, $this->balance());

        $this->actingAs($this->user, 'web')->getJson('/api/admin/ai-usage/summary')->assertStatus(403);
        $this->actingAs($this->user, 'web')->postJson("/api/admin/ai-usage/requests/{$ai->id}/refund")->assertStatus(403);
        $this->assertSame(27, $this->balance());

        $roleClass = config('laratrust.models.role');
        $role = $roleClass::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
        $admin = $this->makeUser('boss1', 0);
        $admin->attachRole($role);
        $this->actingAs($admin, 'web')->getJson('/api/admin/ai-usage/summary')->assertOk()->assertJsonPath('attention.refund_failed', 0);
        $this->actingAs($admin, 'web')->postJson("/api/admin/ai-usage/requests/{$ai->id}/refund")->assertOk()->assertJsonPath('refunded', true);
        $this->assertSame(30, $this->balance());
        $this->actingAs($admin, 'web')->postJson("/api/admin/ai-usage/requests/{$ai->id}/refund")->assertStatus(409);
        $this->assertSame(30, $this->balance());
    }
}
