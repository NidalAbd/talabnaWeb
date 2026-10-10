<?php

namespace Tests\Feature\Social;

use App\Jobs\SendSocialDigest;
use App\Models\Notification as Row;
use App\Models\User;
use App\Notifications\SocialDigestNotification;
use App\Services\Social\ActivityDigest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Laravel\Passport\Passport;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

/** Comments, replies, likes and mentions are grouped per post (2026-10-10): one list row, few pushes. */
class ActivityDigestTest extends TestCase
{
    use MigratesTolerantly;

    private User $owner;
    private int $post;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateTolerantly();
        DB::statement('PRAGMA foreign_keys = OFF');
        Cache::flush();
        if (! \Illuminate\Support\Facades\Schema::hasColumn('service_posts', 'title')) {
            \Illuminate\Support\Facades\Schema::table('service_posts', fn ($t) => $t->text('title')->nullable());
        }
        Notification::fake();
        Queue::fake();
        $this->owner = $this->user('owner');
        $this->post = DB::table('service_posts')->insertGetId([
            'user_id' => $this->owner->id, 'title' => 'iPhone 15 Pro', 'state' => 'published', 'type' => 'عرض',
            'have_badge' => 'عادي', 'categories_id' => 2, 'sub_categories_id' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function user(string $name): User
    {
        return User::find(DB::table('users')->insertGetId([
            'name' => $name, 'user_name' => $name, 'email' => "$name@example.com", 'gender' => 'ذكر', 'password' => 'x',
            'auth_type' => 'email', 'is_active' => 'active', 'fcm_token' => "tok-$name", 'created_at' => now(), 'updated_at' => now(),
        ]));
    }

    private function comment(User $by, string $content = 'Still available?', ?int $parent = null): int
    {
        Passport::actingAs($by);

        return $this->postJson('/api/comments', ['service_post_id' => $this->post, 'content' => $content, 'parent_id' => $parent])->assertCreated()->json('id');
    }

    public function test_many_comments_make_one_row_one_push_now_and_one_summary_later(): void
    {
        foreach (['sara', 'omar', 'lina', 'adam'] as $n) {
            $this->comment($this->user($n));
        }

        $rows = Row::where('user_id', $this->owner->id)->where('type', 'comment')->get();
        $this->assertCount(1, $rows, 'one row per post');
        $this->assertStringContainsString('adam and 3 others commented', json_encode($rows[0]->message));
        $this->assertSame(['post', $this->post], [$rows[0]->target_type, (int) $rows[0]->target_id], 'tapping it opens the post');

        Notification::assertSentToTimes($this->owner, SocialDigestNotification::class, 1); // the first comment, at once
        Queue::assertPushed(SendSocialDigest::class, 1); // one summary for the rest

        ActivityDigest::flush($this->owner->id, 'comment', $this->post);
        Notification::assertSentToTimes($this->owner, SocialDigestNotification::class, 2);
        Notification::assertSentTo($this->owner, SocialDigestNotification::class, fn ($n) => str_contains($n->texts()[1], '3 new comments'));
    }

    public function test_likes_never_push_one_by_one(): void
    {
        foreach (['a1', 'a2', 'a3'] as $n) {
            Passport::actingAs($this->user($n));
            $this->postJson('/api/favorites', ['post_id' => $this->post])->assertOk();
        }
        Notification::assertNothingSent();
        Queue::assertPushed(SendSocialDigest::class, 1);
        $this->assertStringContainsString('a3 and 2 others liked', json_encode(Row::where('type', 'like')->sole()->message));

        ActivityDigest::flush($this->owner->id, 'like', $this->post);
        Notification::assertSentTo($this->owner, SocialDigestNotification::class, fn ($n) => str_contains($n->texts()[1], '3 people liked'));
    }

    public function test_own_comments_and_likes_tell_nobody(): void
    {
        $this->comment($this->owner);
        Passport::actingAs($this->owner);
        $this->postJson('/api/favorites', ['post_id' => $this->post])->assertOk();
        $this->assertSame(0, Row::count());
        Notification::assertNothingSent();
    }

    public function test_replies_tell_the_comment_author_and_mentions_tell_the_named_user(): void
    {
        $sara = $this->user('sara');
        $first = $this->comment($sara);
        $omar = $this->user('omar');
        $lina = $this->user('lina.k');
        $this->comment($omar, '@lina.k look at this, @sara', $first);

        $this->assertSame(1, Row::where('user_id', $sara->id)->where('type', 'comment_reply')->count());
        $this->assertSame(1, Row::where('user_id', $lina->id)->where('type', 'mention')->count());
        $this->assertSame(0, Row::where('user_id', $sara->id)->where('type', 'mention')->count(), 'sara already got the reply');
    }

    public function test_at_most_twenty_social_pushes_an_hour(): void
    {
        for ($i = 0; $i < 25; $i++) {
            $p = DB::table('service_posts')->insertGetId(['user_id' => $this->owner->id, 'title' => "p$i", 'state' => 'published', 'type' => 'عرض', 'have_badge' => 'عادي', 'categories_id' => 2, 'sub_categories_id' => 1]);
            ActivityDigest::record($this->owner->id, 'comment', \App\Models\ServicePost::find($p), $this->user("u$i"));
        }
        Notification::assertSentToTimes($this->owner, SocialDigestNotification::class, ActivityDigest::HOURLY_CAP);
        $this->assertSame(25, Row::where('user_id', $this->owner->id)->count(), 'the list still has all of them');
    }
}
