<?php

namespace Tests\Feature\Social;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Laravel\Passport\Passport;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

/** Comments (2026-10-10): reply preview, likes, the owner's pin, top/new order; older apps still get every reply. */
class CommentsTest extends TestCase
{
    use MigratesTolerantly;

    private User $owner;
    private User $me;
    private int $post;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateTolerantly();
        DB::statement('PRAGMA foreign_keys = OFF');
        Notification::fake();
        Queue::fake();
        if (! \Illuminate\Support\Facades\Schema::hasColumn('service_posts', 'title')) {
            \Illuminate\Support\Facades\Schema::table('service_posts', fn ($t) => $t->text('title')->nullable());
        }
        $this->owner = $this->user('owner');
        $this->me = $this->user('me');
        $this->post = DB::table('service_posts')->insertGetId(['user_id' => $this->owner->id, 'title' => 'Car', 'state' => 'published', 'type' => 'عرض', 'have_badge' => 'عادي', 'categories_id' => 4, 'sub_categories_id' => 1]);
    }

    private function user(string $n): User
    {
        return User::find(DB::table('users')->insertGetId(['name' => $n, 'user_name' => $n, 'email' => "$n@example.com", 'gender' => 'ذكر', 'password' => 'x', 'auth_type' => 'email', 'is_active' => 'active']));
    }

    private function comment(?int $parent = null, int $minutesAgo = 0, ?User $by = null): int
    {
        return DB::table('comments')->insertGetId(['user_id' => ($by ?? $this->me)->id, 'service_post_id' => $this->post, 'content' => 'c', 'parent_id' => $parent, 'created_at' => now()->subMinutes($minutesAgo), 'updated_at' => now()]);
    }

    public function test_new_apps_get_two_replies_and_the_count_then_page_the_rest(): void
    {
        $c = $this->comment();
        foreach (range(1, 23) as $i) {
            $this->comment($c, 100 - $i);
        }
        Passport::actingAs($this->me);

        $row = $this->getJson("/api/commentsForPost/{$this->post}?replies=2")->assertOk()->json('data.0');
        $this->assertSame(23, $row['replies_count']);
        $this->assertCount(2, $row['replies']);

        $this->assertCount(10, $this->getJson("/api/comments/$c/replies?page=1")->json('data'));
        $this->assertCount(3, $this->getJson("/api/comments/$c/replies?page=3")->json('data'));
    }

    public function test_older_apps_still_get_every_reply(): void
    {
        $c = $this->comment();
        foreach (range(1, 7) as $i) {
            $this->comment($c);
        }
        Passport::actingAs($this->me);
        $row = $this->getJson("/api/commentsForPost/{$this->post}")->json('data.0');
        $this->assertCount(7, $row['replies']);
        $this->assertSame(7, $row['replies_count']);
    }

    public function test_like_toggles_and_counts(): void
    {
        $c = $this->comment();
        Passport::actingAs($this->me);
        $this->postJson("/api/comments/$c/like")->assertOk()->assertJson(['is_liked' => true, 'likes_count' => 1]);
        $row = $this->getJson("/api/commentsForPost/{$this->post}?replies=2")->json('data.0');
        $this->assertTrue($row['is_liked']);
        $this->postJson("/api/comments/$c/like")->assertJson(['is_liked' => false, 'likes_count' => 0]);
    }

    public function test_only_the_post_owner_pins_and_the_pinned_comment_comes_first(): void
    {
        $old = $this->comment(null, 60);
        $this->comment(null, 1);
        Passport::actingAs($this->me);
        $this->postJson("/api/comments/$old/pin")->assertForbidden();

        Passport::actingAs($this->owner);
        $this->postJson("/api/comments/$old/pin")->assertOk()->assertJson(['is_pinned' => true]);
        $first = $this->getJson("/api/commentsForPost/{$this->post}?replies=2")->json('data.0');
        $this->assertSame([$old, true], [$first['id'], $first['is_pinned']]);
    }

    public function test_top_sorts_by_likes(): void
    {
        $quiet = $this->comment(null, 1);
        $liked = $this->comment(null, 30);
        DB::table('comments')->where('id', $liked)->update(['likes_count' => 5]);
        Passport::actingAs($this->me);
        $this->assertSame($quiet, $this->getJson("/api/commentsForPost/{$this->post}?replies=2")->json('data.0.id'), 'new: newest first');
        $this->assertSame($liked, $this->getJson("/api/commentsForPost/{$this->post}?replies=2&sort=top")->json('data.0.id'));
    }
}
