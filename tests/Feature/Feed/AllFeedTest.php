<?php

namespace Tests\Feature\Feed;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

/** GET /api/feed: one request for the All feed, fresh posts first, featured posts spread through it. */
class AllFeedTest extends TestCase
{
    use MigratesTolerantly;

    private User $me;
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
        $this->me = $this->makeUser('me');
        Passport::actingAs($this->me);
    }

    private function makeUser(string $name): User
    {
        $id = DB::table('users')->insertGetId([
            'name' => $name, 'user_name' => $name, 'email' => "$name@example.com", 'gender' => 'ذكر', 'password' => 'x',
            'auth_type' => 'email', 'is_active' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);

        return User::find($id);
    }

    private function makePost(array $extra = []): int
    {
        static $n = 0;
        $n++;

        return DB::table('service_posts')->insertGetId(array_merge([
            'user_id' => $this->me->id, 'categories_id' => $this->cat, 'sub_categories_id' => $this->sub, 'title' => "post $n", 'description' => 'd',
            'price' => 100, 'price_currency_code' => 'EGP', 'price_currency_name' => '{}', 'location_latitudes' => 30, 'location_longitudes' => 31,
            'type' => 'عرض', 'have_badge' => 'عادي', 'state' => 'published', 'created_at' => now()->subMinutes(1000 - $n), 'updated_at' => now(),
        ], $extra));
    }

    private function feed(array $q = [])
    {
        return $this->getJson('/api/feed?'.http_build_query($q));
    }

    public function test_it_returns_the_same_shape_the_category_feeds_do_with_the_per_post_extras(): void
    {
        $this->makePost();

        $r = $this->feed()->assertOk();
        $row = $r->json('servicePosts.data.0');
        foreach (['id', 'title', 'user_name', 'is_favorited', 'is_followed', 'distance', 'comments_count', 'favorites_count'] as $key) {
            $this->assertArrayHasKey($key, $row, $key);
        }
        $this->assertSame('me', $row['user_name']);
        $this->assertFalse($row['is_favorited']);
        $this->assertSame(1, $r->json('servicePosts.current_page'));
    }

    public function test_newest_first_and_a_featured_post_is_not_pinned_on_top(): void
    {
        $old = $this->makePost(['have_badge' => 'ماسي', 'created_at' => now()->subDays(30)]);
        $fresh = [];
        for ($i = 0; $i < 12; $i++) {
            $fresh[] = $this->makePost();
        }

        $ids = collect($this->feed()->assertOk()->json('servicePosts.data'))->pluck('id')->all();

        $this->assertNotSame($old, $ids[0], 'a featured post does not sit above fresh posts');
        $this->assertSame($fresh[11], $ids[0], 'the newest organic post is first');
        $this->assertContains($old, $ids, 'the featured post still appears, in a featured slot');
        $this->assertSame(1, array_search($old, $ids));
    }

    public function test_pages_do_not_repeat_posts_and_featured_ones_appear_once(): void
    {
        foreach (range(1, 30) as $i) {
            $this->makePost();
        }
        $featured = [$this->makePost(['have_badge' => 'ماسي']), $this->makePost(['have_badge' => 'ذهبي']), $this->makePost(['have_badge' => 'ماسي'])];

        $all = [];
        foreach ([1, 2, 3, 4] as $page) {
            $all = array_merge($all, collect($this->feed(['page' => $page])->json('servicePosts.data'))->pluck('id')->all());
        }

        $this->assertSame(count($all), count(array_unique($all)), 'no post twice');
        foreach ($featured as $id) {
            $this->assertContains($id, $all);
        }
        $this->assertCount(33, $all);
    }

    public function test_an_expired_badge_is_not_featured_and_unpublished_or_inactive_owners_are_hidden(): void
    {
        $expired = $this->makePost(['have_badge' => 'ماسي', 'badge_expires_at' => now()->subDay()]);
        $draft = $this->makePost(['state' => 'not published']);
        $banned = $this->makeUser('gone');
        DB::table('users')->where('id', $banned->id)->update(['is_active' => 'banned']);
        $hidden = $this->makePost(['user_id' => $banned->id]);

        $ids = collect($this->feed()->json('servicePosts.data'))->pluck('id')->all();

        $this->assertContains($expired, $ids, 'still a normal post in date order');
        $this->assertNotContains($draft, $ids);
        $this->assertNotContains($hidden, $ids);
    }

    public function test_filters_apply_to_organic_and_featured_posts(): void
    {
        $req = $this->makePost(['type' => 'طلب', 'price' => 50]);
        $offer = $this->makePost(['type' => 'عرض', 'price' => 500]);
        $featuredOffer = $this->makePost(['type' => 'عرض', 'have_badge' => 'ذهبي', 'price' => 600]);

        $onlyRequests = collect($this->feed(['type' => 'طلب'])->json('servicePosts.data'))->pluck('id')->all();
        $this->assertSame([$req], $onlyRequests);

        $priced = collect($this->feed(['min_price' => 400, 'max_price' => 550])->json('servicePosts.data'))->pluck('id')->all();
        $this->assertSame([$offer], $priced, 'the featured 600 is outside the range so it is not injected');
        $this->assertNotContains($featuredOffer, $priced);
    }

    public function test_near_and_reels_categories_are_left_out_and_bad_input_is_refused(): void
    {
        $reels = DB::table('categories')->insertGetId(['id' => 7, 'name' => json_encode(['en' => 'Reels']), 'created_at' => now(), 'updated_at' => now()]);
        $rp = $this->makePost(['categories_id' => $reels]);
        $ok = $this->makePost();

        $ids = collect($this->feed()->json('servicePosts.data'))->pluck('id')->all();
        $this->assertContains($ok, $ids);
        $this->assertNotContains($rp, $ids);

        $this->feed(['type' => 'nonsense'])->assertStatus(422);
        $this->feed(['page' => 0])->assertStatus(422);
    }

    public function test_it_makes_a_small_fixed_number_of_queries_however_many_posts(): void
    {
        foreach (range(1, 25) as $i) {
            $this->makePost();
        }
        $this->feed()->assertOk(); // warm caches

        DB::enableQueryLog();
        $this->feed()->assertOk();
        $few = count(DB::getQueryLog());
        DB::disableQueryLog();

        foreach (range(1, 25) as $i) {
            $this->makePost();
        }
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->feed()->assertOk();
        $more = count(DB::getQueryLog());

        $this->assertLessThan(25, $few, 'not a few queries per post');
        $this->assertSame($few, $more, 'the number of queries does not grow with the number of posts');
    }
}
