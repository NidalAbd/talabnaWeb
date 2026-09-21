<?php

namespace Tests\Feature\Feed;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

/** Category and subcategory feeds: fresh posts first, featured posts rotated into slots, never pinned on top. */
class CategoryFeedBadgesTest extends TestCase
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

        $id = DB::table('users')->insertGetId([
            'name' => 'me', 'user_name' => 'me', 'email' => 'me@example.com', 'gender' => 'ذكر', 'password' => 'x',
            'auth_type' => 'email', 'is_active' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->me = User::find($id);
        $role = config('laratrust.models.role')::create(['name' => 'member', 'display_name' => 'Member']);
        $perm = config('laratrust.models.permission')::create(['name' => 'view_service', 'display_name' => 'View']);
        $role->attachPermission($perm);
        $this->me->attachRole($role);
        Passport::actingAs($this->me);
    }

    private function makePost(array $extra = []): int
    {
        static $n = 0;
        $n++;

        return DB::table('service_posts')->insertGetId(array_merge([
            'user_id' => $this->me->id, 'categories_id' => $this->cat, 'sub_categories_id' => $this->sub, 'title' => "post $n", 'description' => 'd',
            'price' => 100, 'price_currency_code' => 'EGP', 'price_currency_name' => '{}', 'location_latitudes' => 30, 'location_longitudes' => 31,
            'type' => 'عرض', 'have_badge' => 'عادي', 'state' => 'published', 'created_at' => now()->subMinutes(2000 - $n), 'updated_at' => now(),
        ], $extra));
    }

    public static function feeds(): array
    {
        return [['category'], ['subcategory']];
    }

    private function url(string $which, int $page = 1): string
    {
        return $which === 'category'
            ? "/api/service_posts/categories/{$this->cat}?page=$page"
            : "/api/service_posts/categories/{$this->cat}/sub_categories/{$this->sub}?page=$page";
    }

    /** @dataProvider feeds */
    public function test_a_featured_post_is_not_pinned_above_fresh_posts(string $which): void
    {
        $old = $this->makePost(['have_badge' => 'ماسي', 'created_at' => now()->subDays(60)]);
        $fresh = [];
        for ($i = 0; $i < 12; $i++) {
            $fresh[] = $this->makePost();
        }

        $ids = collect($this->getJson($this->url($which))->assertOk()->json('servicePosts.data'))->pluck('id')->all();

        $this->assertSame($fresh[11], $ids[0], 'the newest post is first, not the old diamond one');
        $this->assertSame(1, array_search($old, $ids), 'the featured post sits in its slot');
    }

    /** @dataProvider feeds */
    public function test_no_post_appears_twice_across_pages_and_every_featured_one_is_reachable(string $which): void
    {
        foreach (range(1, 25) as $i) {
            $this->makePost();
        }
        $featured = [$this->makePost(['have_badge' => 'ماسي']), $this->makePost(['have_badge' => 'ذهبي']), $this->makePost(['have_badge' => 'ماسي'])];

        $all = [];
        foreach ([1, 2, 3, 4] as $page) {
            $all = array_merge($all, collect($this->getJson($this->url($which, $page))->json('servicePosts.data'))->pluck('id')->all());
        }

        $this->assertSame(count($all), count(array_unique($all)));
        $this->assertCount(28, $all);
        foreach ($featured as $id) {
            $this->assertContains($id, $all);
        }
    }

    /** @dataProvider feeds */
    public function test_expired_badges_are_ordinary_posts_and_filters_still_apply(string $which): void
    {
        $expired = $this->makePost(['have_badge' => 'ماسي', 'badge_expires_at' => now()->subDay(), 'type' => 'طلب']);
        $offer = $this->makePost(['have_badge' => 'ذهبي', 'type' => 'عرض']);

        $requests = collect($this->getJson($this->url($which).'&type='.urlencode('طلب'))->json('servicePosts.data'))->pluck('id')->all();

        $this->assertSame([$expired], $requests, 'the featured OFFER is filtered out, not injected');
        $this->assertNotContains($offer, $requests);
    }
}
