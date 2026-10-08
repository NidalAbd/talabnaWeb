<?php

namespace Tests\Feature\Feed;

use App\Models\ServicePost;
use App\Models\User;
use App\Services\Feed\PostDistribution;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

/** Post statistics (reached, stopped, opened, read, contacted) and how far each post spreads (2026-10-08). */
class DistributionTest extends TestCase
{
    use MigratesTolerantly;

    private int $cat;

    private int $sub;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateTolerantly();
        DB::statement('PRAGMA foreign_keys = OFF');
        Cache::flush();
        if (! Schema::hasColumn('service_posts', 'title')) {
            Schema::table('service_posts', function ($t) {
                $t->text('title')->nullable();
                $t->text('description')->nullable();
            });
        }
        $this->cat = DB::table('categories')->insertGetId(['name' => json_encode(['en' => 'Cars']), 'created_at' => now(), 'updated_at' => now()]);
        $this->sub = DB::table('sub_categories')->insertGetId(['categories_id' => $this->cat, 'name' => json_encode(['en' => 'Sedan']), 'created_at' => now(), 'updated_at' => now()]);
    }

    private function user(string $name, ?int $country = 1, ?int $city = 10): User
    {
        $id = DB::table('users')->insertGetId([
            'name' => $name, 'user_name' => $name, 'email' => "$name@example.com", 'gender' => 'ذكر', 'password' => 'x',
            'auth_type' => 'email', 'is_active' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ] + (Schema::hasColumn('users', 'country_id') ? ['country_id' => $country, 'city_id' => $city] : []));

        return User::find($id);
    }

    private function makePost(User $owner, array $extra = []): int
    {
        static $n = 0;
        $n++;

        return DB::table('service_posts')->insertGetId(array_merge([
            'user_id' => $owner->id, 'categories_id' => $this->cat, 'sub_categories_id' => $this->sub, 'title' => "post $n", 'description' => 'd',
            'price' => 100, 'price_currency_code' => 'EGP', 'price_currency_name' => '{}', 'location_latitudes' => 30, 'location_longitudes' => 31,
            'type' => 'عرض', 'have_badge' => 'عادي', 'state' => 'published', 'country_id' => 1, 'city_id' => 10,
            'created_at' => now()->subDays(5)->addMinutes($n), 'updated_at' => now(),
        ], $extra));
    }

    /** $n people from the crowd saw / stopped / opened / read the post. */
    private function audience(int $postId, int $seen, int $stopped = 0, int $opened = 0, int $read = 0): void
    {
        static $crowd = 0;
        for ($i = 0; $i < $seen; $i++) {
            $uid = 100000 + (++$crowd);
            DB::table('feed_seen')->insert(['user_id' => $uid, 'service_post_id' => $postId, 'seen_at' => now()]);
            if ($i < $stopped) {
                DB::table('post_engagements')->insert(['service_post_id' => $postId, 'user_id' => $uid, 'kind' => 'dwell', 'created_at' => now()]);
            }
            if ($i < $opened) {
                DB::table('post_views')->insert(['service_post_id' => $postId, 'user_id' => $uid, 'viewed_at' => now()]);
            }
            if ($i < $read) {
                DB::table('post_engagements')->insert(['service_post_id' => $postId, 'user_id' => $uid, 'kind' => 'read', 'created_at' => now()]);
            }
        }
    }

    // ── recording ─────────────────────────────────────────────────────────

    public function test_engagement_counts_once_per_person_and_never_the_owners_own(): void
    {
        $owner = $this->user('owner');
        $post = $this->makePost($owner);
        Passport::actingAs($this->user('reader'));

        $events = [['post_id' => $post, 'kind' => 'dwell'], ['post_id' => $post, 'kind' => 'dwell'], ['post_id' => $post, 'kind' => 'read'], ['post_id' => $post, 'kind' => 'call']];
        $this->postJson('/api/posts/engagement', ['events' => $events])->assertOk()->assertJsonPath('count', 3);
        $this->postJson('/api/posts/engagement', ['events' => $events])->assertOk();
        $this->assertSame(3, DB::table('post_engagements')->count(), 'sending again changes nothing');

        Passport::actingAs($owner);
        $this->postJson('/api/posts/engagement', ['events' => [['post_id' => $post, 'kind' => 'read']]])->assertOk()->assertJsonPath('count', 0);

        $this->postJson('/api/posts/engagement', ['events' => [['post_id' => $post, 'kind' => 'like']]])->assertStatus(422);
    }

    public function test_the_funnel_counts_each_step_from_its_own_record(): void
    {
        $owner = $this->user('owner');
        $post = $this->makePost($owner);
        $this->audience($post, seen: 50, stopped: 20, opened: 10, read: 6);
        DB::table('favorites')->insert(['user_id' => 5, 'favoritable_id' => $post, 'favoritable_type' => ServicePost::class, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('post_engagements')->insert(['service_post_id' => $post, 'user_id' => 7, 'kind' => 'whatsapp', 'created_at' => now()]);

        $f = app(PostDistribution::class)->funnel($post);

        $this->assertSame([50, 20, 10, 6], [$f['reach'], $f['stopped'], $f['opened'], $f['read']]);
        $this->assertSame(1, $f['saves']);
        $this->assertSame(1, $f['whatsapp']);
        $this->assertSame(2, $f['actions']);
    }

    // ── spreading ─────────────────────────────────────────────────────────

    private function decide(array $c, ?int $was, int $ageHours = 120, bool $badge = false): array
    {
        $c += ['reach' => 0, 'stopped' => 0, 'opened' => 0, 'read' => 0, 'actions' => 0];

        return app(PostDistribution::class)->decide($c, 0.4, $was, now()->subHours($ageHours), $badge);
    }

    public function test_a_new_post_is_tested_in_its_city_first(): void
    {
        $d = $this->decide(['reach' => 5, 'stopped' => 5, 'opened' => 5, 'read' => 5], null, ageHours: 2);
        $this->assertTrue($d['testing']);
        $this->assertSame(1, $d['stage'], 'even a great start waits for a real audience');
    }

    public function test_a_post_that_does_clearly_better_moves_out_one_stage_at_a_time(): void
    {
        $strong = ['reach' => 100, 'stopped' => 60, 'opened' => 40, 'read' => 25, 'actions' => 5];
        $d = $this->decide($strong, 1);
        $this->assertFalse($d['testing']);
        $this->assertGreaterThanOrEqual(PostDistribution::EXPAND_AT, $d['relative']);
        $this->assertSame(2, $d['stage'], 'city -> country');
        $this->assertSame(3, $this->decide($strong, 2)['stage'], 'country -> nearby countries');
        $this->assertSame(3, $this->decide(['reach' => 80] + $strong, 3)['stage'], 'needs 120 people reached before going everywhere');
        $this->assertSame(4, $this->decide(['reach' => 200, 'stopped' => 120, 'opened' => 80, 'read' => 50, 'actions' => 10], 3)['stage']);
        $this->assertSame(4, $this->decide(['reach' => 400, 'stopped' => 240, 'opened' => 160, 'read' => 100, 'actions' => 20], 4)['stage'], 'never beyond everywhere');
    }

    public function test_a_post_that_does_clearly_worse_moves_back_and_an_average_one_stays(): void
    {
        $this->assertSame(2, $this->decide(['reach' => 200, 'stopped' => 2], 3)['stage']);
        $this->assertSame(1, $this->decide(['reach' => 200], 1)['stage'], 'never below its city');
        $average = ['reach' => 100, 'stopped' => 20, 'opened' => 6, 'read' => 2];
        $this->assertSame(3, $this->decide($average, 3)['stage']);
    }

    public function test_a_few_lucky_views_do_not_decide_anything(): void
    {
        // 2 of 3 people read it: a great rate, but 3 people say little. The score stays near the average.
        $d = $this->decide(['reach' => 31, 'stopped' => 3, 'opened' => 2, 'read' => 2], 1);
        $this->assertLessThan(PostDistribution::EXPAND_AT, $d['relative']);
    }

    public function test_a_paid_badge_starts_one_stage_further(): void
    {
        $this->assertSame(2, $this->decide(['reach' => 3], null, ageHours: 1, badge: true)['stage']);
        $this->assertSame(2, $this->decide(['reach' => 300], 2, badge: true)['stage'], 'and never goes below it');
    }

    public function test_recompute_stores_every_live_posts_stage_and_its_numbers(): void
    {
        $owner = $this->user('owner');
        $strong = $this->makePost($owner);
        $weak = $this->makePost($owner);
        $new = $this->makePost($owner, ['created_at' => now()->subHour()]);
        $other = $this->makePost($owner);
        $this->audience($strong, seen: 100, stopped: 70, opened: 50, read: 30);
        $this->audience($weak, seen: 100, stopped: 2);
        $this->audience($other, seen: 100, stopped: 25, opened: 8, read: 3);
        DB::table('post_distribution')->insert(['service_post_id' => $strong, 'stage' => 2, 'testing' => false]);

        app(PostDistribution::class)->recompute();

        $stage = DB::table('post_distribution')->pluck('stage', 'service_post_id');
        $this->assertSame(3, (int) $stage[$strong]);
        $this->assertSame(1, (int) $stage[$weak]);
        $this->assertSame(1, (int) $stage[$new]);
        $this->assertSame(100, (int) DB::table('post_distribution')->where('service_post_id', $strong)->value('reach'));
        $this->artisan('posts:distribute')->assertExitCode(0);
    }

    // ── the feed ──────────────────────────────────────────────────────────

    public function test_the_feed_shows_posts_meant_for_this_viewer_first_and_still_shows_the_others(): void
    {
        if (! Schema::hasColumn('users', 'country_id')) {
            $this->markTestSkipped('users have no country here');
        }
        $me = $this->user('me', country: 1, city: 10);
        $owner = $this->user('seller', country: 1, city: 20);
        $friend = $this->user('friend', country: 1, city: 30);
        DB::table('followers')->insert(['user_id' => $friend->id, 'follower_id' => $me->id, 'created_at' => now(), 'updated_at' => now()]);

        $otherCityNew = $this->makePost($owner, ['city_id' => 20, 'created_at' => now()->subMinutes(1)]); // stage 1 in another city
        $myCity = $this->makePost($owner, ['city_id' => 10, 'created_at' => now()->subHours(3)]);          // stage 1, my city
        $countryWide = $this->makePost($owner, ['city_id' => 20, 'created_at' => now()->subHours(4)]);     // stage 2
        $followed = $this->makePost($friend, ['city_id' => 30, 'created_at' => now()->subHours(5)]);       // stage 1, but I follow them
        foreach ([$otherCityNew => 1, $myCity => 1, $countryWide => 2, $followed => 1] as $id => $stage) {
            DB::table('post_distribution')->insert(['service_post_id' => $id, 'stage' => $stage, 'testing' => $stage === 1]);
        }
        Passport::actingAs($me);

        $ids = collect($this->getJson('/api/feed')->assertOk()->json('servicePosts.data'))->pluck('id')->all();

        $this->assertSame([$myCity, $countryWide, $followed], array_slice($ids, 0, 3), 'meant for me, newest first');
        $this->assertSame($otherCityNew, $ids[3], 'not hidden: after the posts meant for me');
    }

    // ── what the owner sees ───────────────────────────────────────────────

    public function test_insights_show_the_funnel_and_the_stage_and_locked_insights_still_show_the_reach(): void
    {
        $owner = $this->user('owner');
        $post = $this->makePost($owner);
        $this->audience($post, seen: 40, stopped: 12, opened: 6, read: 3);
        DB::table('post_distribution')->insert(['service_post_id' => $post, 'stage' => 2, 'testing' => false, 'relative' => 1.1]);
        Passport::actingAs($owner);

        $locked = $this->getJson("/api/service_posts/$post/insights")->assertOk();
        $this->assertTrue($locked->json('locked'));
        $this->assertSame(40, $locked->json('reach'));

        DB::table('feature_unlocks')->insert(['user_id' => $owner->id, 'feature' => 'insights_post', 'target_id' => $post, 'points' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $open = $this->getJson("/api/service_posts/$post/insights")->assertOk();
        $this->assertSame([40, 12, 6, 3], [$open->json('funnel.reach'), $open->json('funnel.stopped'), $open->json('funnel.opened'), $open->json('funnel.read')]);
        $this->assertSame(2, $open->json('distribution.stage'));
        $this->assertFalse($open->json('distribution.testing'));
    }
}
