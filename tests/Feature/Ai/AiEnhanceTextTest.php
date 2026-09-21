<?php

namespace Tests\Feature\Ai;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Passport;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;
use App\Models\User;

/** Paid AI text enhancement: the server takes the price from its own table and never charges for a failure. */
class AiEnhanceTextTest extends TestCase
{
    use MigratesTolerantly;

    private const URL = '/api/ai/enhance-text';
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateTolerantly();
        DB::statement('PRAGMA foreign_keys = OFF');
        config(['services.openai.key' => 'test-key']);

        // Production MySQL has 'refund' in the type enum (a MySQL-only migration); SQLite skipped it, so
        // give the test ledger a plain string type.
        \Illuminate\Support\Facades\Schema::dropIfExists('point_transactions');
        \Illuminate\Support\Facades\Schema::create('point_transactions', function ($t) {
            $t->id();
            $t->unsignedBigInteger('from_user_id')->nullable();
            $t->unsignedBigInteger('to_user_id')->nullable();
            $t->string('type');
            $t->integer('point');
            $t->string('status')->nullable();
            $t->text('metadata')->nullable();
            $t->timestamps();
        });

        $id = DB::table('users')->insertGetId([
            'name' => 'Writer', 'user_name' => 'writer1', 'email' => 'w@example.com', 'gender' => 'ذكر', 'password' => 'x',
            'auth_type' => 'email', 'is_active' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('palservice_points')->insert(['user_id' => $id, 'point' => 5, 'created_at' => now(), 'updated_at' => now()]);
        $this->user = User::find($id);
        Passport::actingAs($this->user);
    }

    private function openAi(string $text = 'Clean 2018 Toyota Corolla, single owner.'): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => '"'.$text.'"']]]])]);
    }

    private function balance(): int
    {
        return (int) DB::table('palservice_points')->where('user_id', $this->user->id)->value('point');
    }

    private function enhance(array $extra = [])
    {
        return $this->postJson(self::URL, array_merge(['feature' => 'enhance_title', 'text' => 'toyota corolla 2018 good', 'language' => 'en'], $extra));
    }

    public function test_pricing_lists_the_actions_with_their_cost_and_the_users_balance(): void
    {
        $res = $this->getJson('/api/ai/pricing')->assertOk();

        $this->assertSame(5, $res->json('balance'));
        $this->assertSame(1, $res->json('features.enhance_title.points'));
        $this->assertTrue($res->json('features.enhance_title.enabled'));
        $this->assertFalse($res->json('features.generate_video.enabled'));   // not switched on yet
        $this->assertGreaterThan(0, $res->json('version'));
    }

    public function test_a_successful_enhancement_costs_the_listed_points_and_is_recorded(): void
    {
        $this->openAi('Toyota Corolla 2018, in good condition');

        $this->enhance()->assertOk()
            ->assertJsonPath('text', 'Toyota Corolla 2018, in good condition') // surrounding quotes stripped
            ->assertJsonPath('points_charged', 1)
            ->assertJsonPath('balance', 4);

        $this->assertSame(4, $this->balance());
        $this->assertDatabaseHas('point_transactions', ['from_user_id' => $this->user->id, 'type' => 'used', 'point' => 1]);
        Http::assertSent(fn ($r) => $r->url() === 'https://api.openai.com/v1/chat/completions' && str_contains($r['messages'][1]['content'], 'toyota corolla 2018 good'));
    }

    public function test_the_price_comes_from_the_server_table_and_a_client_price_is_ignored(): void
    {
        DB::table('ai_features')->where('key', 'enhance_description')->update(['points_cost' => 3]);
        $this->openAi('A neat description.');

        $this->enhance(['feature' => 'enhance_description', 'points' => 0, 'cost' => 0, 'price' => 0])
            ->assertOk()->assertJsonPath('points_charged', 3);

        $this->assertSame(2, $this->balance());
    }

    public function test_not_enough_points_is_refused_without_calling_the_ai_or_charging(): void
    {
        DB::table('palservice_points')->where('user_id', $this->user->id)->update(['point' => 0]);
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake();

        $this->enhance()->assertStatus(402)->assertJsonPath('required', 1)->assertJsonPath('balance', 0);

        Http::assertNothingSent();
        $this->assertDatabaseMissing('point_transactions', ['type' => 'used']);
    }

    public function test_an_ai_failure_gives_the_points_back(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(['api.openai.com/*' => Http::response(['error' => 'boom'], 500)]);

        $this->enhance()->assertStatus(502);

        $this->assertSame(5, $this->balance(), 'the user must never pay for a failed AI call');
        $this->assertDatabaseHas('point_transactions', ['type' => 'used', 'point' => 1]);
        $this->assertDatabaseHas('point_transactions', ['type' => 'refund', 'point' => 1]);
    }

    public function test_an_empty_ai_answer_is_a_failure_and_is_refunded(): void
    {
        $this->openAi('   ');

        $this->enhance()->assertStatus(502);
        $this->assertSame(5, $this->balance());
    }

    public function test_a_disabled_feature_is_refused_and_free(): void
    {
        DB::table('ai_features')->where('key', 'enhance_title')->update(['enabled' => false]);

        $this->enhance()->assertStatus(403);
        $this->assertSame(5, $this->balance());
    }

    public function test_without_an_openai_key_nothing_is_charged(): void
    {
        config(['services.openai.key' => null]);

        $this->enhance()->assertStatus(503);
        $this->assertSame(5, $this->balance());
    }

    public function test_only_the_two_text_features_are_accepted_here(): void
    {
        $this->enhance(['feature' => 'generate_video'])->assertStatus(422);
        $this->enhance(['text' => ''])->assertStatus(422);
        $this->assertSame(5, $this->balance());
    }
}
