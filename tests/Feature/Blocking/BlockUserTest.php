<?php

namespace Tests\Feature\Blocking;

use App\Models\Comment;
use App\Models\ServicePost;
use App\Models\User;
use App\Support\Blocks;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

/** App Store guideline 1.2: blocking abusive users. */
class BlockUserTest extends TestCase
{
    use MigratesTolerantly;

    private User $me;
    private User $abuser;
    private User $friend;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateTolerantly();
        DB::statement('PRAGMA foreign_keys = OFF'); // posts here need no category/subcategory rows
        Blocks::forget();
        $this->me = $this->makeUser('me');
        $this->abuser = $this->makeUser('abuser');
        $this->friend = $this->makeUser('friend');
    }

    public function test_block_list_unblock_flow_and_idempotency(): void
    {
        Passport::actingAs($this->me);

        $this->postJson("/api/users/{$this->abuser->id}/block")->assertOk()->assertJson(['blocked' => true]);
        $this->postJson("/api/users/{$this->abuser->id}/block")->assertOk(); // no duplicate row
        $this->assertSame(1, DB::table('user_blocks')->count());

        $this->getJson('/api/blocked-users')->assertOk()
            ->assertJsonPath('blocked_users.0.id', $this->abuser->id);

        $this->deleteJson("/api/users/{$this->abuser->id}/block")->assertOk()->assertJson(['blocked' => false]);
        $this->getJson('/api/blocked-users')->assertOk()->assertJsonCount(0, 'blocked_users');
    }

    public function test_cannot_block_yourself(): void
    {
        Passport::actingAs($this->me);

        $this->postJson("/api/users/{$this->me->id}/block")->assertStatus(422);
        $this->assertSame(0, DB::table('user_blocks')->count());
    }

    public function test_requires_authentication(): void
    {
        $this->postJson("/api/users/{$this->abuser->id}/block")->assertStatus(401);
        $this->getJson('/api/blocked-users')->assertStatus(401);
    }

    public function test_blocked_users_posts_and_comments_disappear_for_the_blocker_only(): void
    {
        $abusivePost = $this->makePost($this->abuser);
        $friendPost = $this->makePost($this->friend);
        $this->makeComment($this->abuser, $abusivePost);
        $this->makeComment($this->friend, $friendPost);

        Passport::actingAs($this->me);
        $this->postJson("/api/users/{$this->abuser->id}/block")->assertOk();

        // Same request lifecycle as the API: the scope reads the signed-in user's block list.
        $this->assertSame([$friendPost], ServicePost::pluck('id')->all());
        $this->assertSame(1, Comment::count());

        // A different viewer still sees everything.
        Blocks::forget();
        Passport::actingAs($this->friend);
        $this->assertSame(2, ServicePost::count());
        $this->assertSame(2, Comment::count());
    }

    public function test_unblocking_restores_the_content(): void
    {
        $post = $this->makePost($this->abuser);
        Passport::actingAs($this->me);
        $this->postJson("/api/users/{$this->abuser->id}/block")->assertOk();
        $this->assertSame(0, ServicePost::count());

        $this->deleteJson("/api/users/{$this->abuser->id}/block")->assertOk();
        $this->assertSame([$post], ServicePost::pluck('id')->all());
    }

    public function test_guests_and_back_office_queries_are_not_filtered(): void
    {
        $this->makePost($this->abuser);
        DB::table('user_blocks')->insert(['blocker_id' => $this->me->id, 'blocked_id' => $this->abuser->id, 'created_at' => now(), 'updated_at' => now()]);

        $this->assertSame(1, ServicePost::count()); // no authenticated API user
    }

    public function test_blocking_prevents_new_conversations_in_both_directions(): void
    {
        DB::table('user_blocks')->insert(['blocker_id' => $this->me->id, 'blocked_id' => $this->abuser->id, 'created_at' => now(), 'updated_at' => now()]);

        Passport::actingAs($this->me);
        $this->postJson('/api/conversations', ['recipient_id' => $this->abuser->id])->assertStatus(403);

        Passport::actingAs($this->abuser);
        $this->postJson('/api/conversations', ['recipient_id' => $this->me->id])->assertStatus(403);
    }

    public function test_blocking_stops_messages_in_an_existing_conversation_and_hides_it(): void
    {
        Passport::actingAs($this->abuser);
        $conversationId = $this->postJson('/api/conversations', ['recipient_id' => $this->me->id])->assertStatus(201)->json('id');
        $this->postJson("/api/conversations/{$conversationId}/messages", ['body' => 'hello'])->assertStatus(201);

        Passport::actingAs($this->me);
        $this->postJson("/api/users/{$this->abuser->id}/block")->assertOk();

        $this->postJson("/api/conversations/{$conversationId}/messages", ['body' => 'reply'])->assertStatus(403);
        Passport::actingAs($this->abuser);
        $this->postJson("/api/conversations/{$conversationId}/messages", ['body' => 'again'])->assertStatus(403);

        Passport::actingAs($this->me);
        $this->getJson('/api/conversations')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_unrelated_conversations_are_unaffected(): void
    {
        Passport::actingAs($this->me);
        $this->postJson("/api/users/{$this->abuser->id}/block")->assertOk();

        $this->postJson('/api/conversations', ['recipient_id' => $this->friend->id])->assertStatus(201);
        $this->getJson('/api/conversations')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_feeds_and_chat_keep_working_before_the_block_migration_has_run(): void
    {
        $post = $this->makePost($this->friend);
        DB::statement('DROP TABLE user_blocks');
        Blocks::forget();

        Passport::actingAs($this->me);
        $this->assertSame([$post], ServicePost::pluck('id')->all());          // scope falls back to "nothing blocked"
        $this->postJson('/api/conversations', ['recipient_id' => $this->friend->id])->assertStatus(201);
        $this->getJson('/api/conversations')->assertOk()->assertJsonCount(1, 'data');
    }

    // ── helpers ─────────────────────────────────────────────────────────

    private function makeUser(string $name): User
    {
        $id = DB::table('users')->insertGetId([
            'name' => $name, 'user_name' => $name.rand(1, 99999), 'email' => $name.rand(1, 99999).'@example.com',
            'gender' => 'ذكر', 'password' => 'x', 'auth_type' => 'email', 'is_active' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return User::find($id);
    }

    private function makePost(User $owner): int
    {
        return DB::table('service_posts')->insertGetId(array_merge(
            $this->postDefaults(),
            ['user_id' => $owner->id, 'created_at' => now(), 'updated_at' => now()]
        ));
    }

    /** Fills every NOT NULL column of the (SQLite-migrated) service_posts table. */
    private function postDefaults(): array
    {
        $defaults = [];
        foreach (DB::select('PRAGMA table_info(service_posts)') as $col) {
            if ($col->notnull && $col->dflt_value === null && ! $col->pk && ! in_array($col->name, ['user_id', 'created_at', 'updated_at'], true)) {
                $defaults[$col->name] = str_contains(strtolower($col->type), 'int') || str_contains(strtolower($col->type), 'num') ? 1 : 'x';
            }
        }

        return $defaults;
    }

    private function makeComment(User $author, int $postId): void
    {
        DB::table('comments')->insert([
            'user_id' => $author->id, 'service_post_id' => $postId, 'content' => 'hi',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
