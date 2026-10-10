<?php

namespace Tests\Feature\Posts;

use App\Models\ServicePost;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

/** GET /api/service_posts/reels: pages of 10, each different, with the viewer's like/follow flags, in few queries. */
class ReelsEndpointTest extends TestCase
{
    use MigratesTolerantly;

    private User $me;
    private array $owners = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateTolerantly();
        DB::statement('PRAGMA foreign_keys = OFF');
        // The tolerant test migrations leave the translated text columns out
        \Illuminate\Support\Facades\Schema::table('service_posts', function ($t) {
            $t->text('title')->nullable();
            $t->text('description')->nullable();
        });
        $this->me = $this->user('viewer');
        for ($i = 0; $i < 5; $i++) {
            $this->owners[] = $this->user("owner$i");
        }
        for ($i = 0; $i < 23; $i++) {
            $id = DB::table('service_posts')->insertGetId([
                'user_id' => $this->owners[$i % 5]->id, 'title' => "Reel $i", 'description' => 'd', 'state' => 'published',
                'type' => 'عرض', 'have_badge' => 'عادي', 'categories_id' => 7, 'sub_categories_id' => 1,
                'created_at' => now()->subMinutes($i), 'updated_at' => now(),
            ]);
            DB::table('photos')->insert(['photoable_type' => (new ServicePost)->getMorphClass(), 'photoable_id' => $id, 'src' => "storage/v$i.mp4", 'isVideo' => 1, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    private function user(string $name): User
    {
        return User::find(DB::table('users')->insertGetId([
            'name' => $name, 'user_name' => $name, 'email' => "$name@example.com", 'gender' => 'ذكر', 'password' => 'x',
            'auth_type' => 'email', 'is_active' => 'active', 'phones' => '00970599'.rand(100000, 999999), 'created_at' => now(), 'updated_at' => now(),
        ]));
    }

    public function test_pages_of_ten_each_different_until_the_end(): void
    {
        Passport::actingAs($this->me);
        $seen = [];
        foreach ([1 => 10, 2 => 10, 3 => 3, 4 => 0] as $page => $count) {
            $data = $this->getJson("/api/service_posts/reels?page=$page&seen_before=".now()->timestamp)->assertOk()->json('servicePosts.data');
            $this->assertCount($count, $data, "page $page");
            foreach ($data as $p) {
                $this->assertNotContains($p['id'], $seen, 'no reel twice');
                $seen[] = $p['id'];
            }
        }
        $this->assertCount(23, $seen);
    }

    public function test_like_follow_and_owner_details_are_right_in_few_queries(): void
    {
        $liked = ServicePost::where('user_id', $this->owners[1]->id)->value('id');
        DB::table('favorites')->insert(['user_id' => $this->me->id, 'favoritable_type' => (new ServicePost)->getMorphClass(), 'favoritable_id' => $liked, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('followers')->insert(['user_id' => $this->me->id, 'follower_id' => $this->owners[2]->id]);
        Passport::actingAs($this->me);

        DB::enableQueryLog();
        $data = collect($this->getJson('/api/service_posts/reels?page=1&seen_before='.now()->timestamp)->assertOk()->json('servicePosts.data'));
        $queries = count(DB::getQueryLog());

        $this->assertLessThan(25, $queries, "the page took $queries queries");
        foreach ($data as $p) {
            $this->assertSame($p['id'] === $liked, $p['is_favorited']);
            $this->assertSame((int) $p['user_id'] === $this->owners[2]->id, $p['is_followed']);
            $this->assertSame(User::find($p['user_id'])->user_name, $p['user_name']);
        }
    }

    public function test_a_featured_reel_takes_its_slot_instead_of_coming_first(): void
    {
        $diamond = DB::table('service_posts')->orderBy('id')->value('id'); // the oldest reel
        DB::table('service_posts')->where('id', $diamond)->update(['have_badge' => 'ماسي']);
        Passport::actingAs($this->me);

        $all = [];
        foreach ([1, 2, 3] as $page) {
            $ids = collect($this->getJson("/api/service_posts/reels?page=$page&seen_before=".now()->timestamp)->json('servicePosts.data'))->pluck('id')->all();
            if ($page === 1) {
                $this->assertSame(\App\Services\Feed\SponsoredPicker::SLOTS[0], array_search($diamond, $ids), 'in its slot, not first');
            }
            $all = array_merge($all, $ids);
        }
        $this->assertCount(23, $all);
        $this->assertSame(count($all), count(array_unique($all)));
    }
}
