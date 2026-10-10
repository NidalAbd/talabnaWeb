<?php

namespace Tests\Feature\Search;

use App\Models\ServicePost;
use App\Models\User;
use App\Services\PostAttributes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

/** Search and feeds by details, with options and counts (2026-10-10): "iPhone 15" -> 128 GB (2) · 256 GB (1) ... */
class DetailSearchTest extends TestCase
{
    use MigratesTolerantly;

    private User $me;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateTolerantly();
        DB::statement('PRAGMA foreign_keys = OFF');
        if (! Schema::hasColumn('service_posts', 'title')) {
            Schema::table('service_posts', function ($t) {
                $t->text('title')->nullable();
                $t->text('description')->nullable();
            });
        }
        $this->me = User::find(DB::table('users')->insertGetId(['name' => 'me', 'user_name' => 'me', 'email' => 'me@x.com', 'gender' => 'ذكر', 'password' => 'x', 'auth_type' => 'email', 'is_active' => 'active']));
        $role = config('laratrust.models.role')::create(['name' => 'member', 'display_name' => 'Member']);
        $role->attachPermission(config('laratrust.models.permission')::create(['name' => 'view_service', 'display_name' => 'View']));
        $this->me->attachRole($role);
        DB::table('categories')->insert(['id' => 2, 'name' => json_encode(['en' => 'Devices'])]);
        DB::table('sub_categories')->insert(['id' => 20, 'categories_id' => 2, 'name' => json_encode(['en' => 'Phones'])]);
    }

    private function phone(string $title, array $details): int
    {
        $post = new ServicePost();
        $post->forceFill([
            'user_id' => $this->me->id, 'title' => json_encode(['en' => $title]), 'description' => json_encode(['en' => 'Clean, with box']),
            'state' => 'published', 'type' => 'عرض', 'have_badge' => 'عادي', 'categories_id' => 2, 'sub_categories_id' => 20,
            'details' => PostAttributes::clean(2, $details),
        ])->save();

        return $post->id;
    }

    public function test_brand_spellings_from_before_mean_the_brand(): void
    {
        $this->assertSame('apple', PostAttributes::clean(2, ['brand' => 'ايفون'])['brand']);
        $this->assertSame('samsung', PostAttributes::clean(2, ['brand' => 'Samsung Galaxy'])['brand']);
        $this->assertSame('other', PostAttributes::clean(2, ['brand' => 'Nothing Phone'])['brand']);
        $this->assertSame('apple', PostAttributes::clean(2, ['brand' => 'apple'])['brand']);
        $this->assertArrayNotHasKey('color', PostAttributes::clean(2, ['color' => 'sky']) ?? []);
    }

    public function test_a_saved_post_is_indexed_and_follows_its_edits(): void
    {
        $id = $this->phone('iPhone 15', ['brand' => 'apple', 'model' => 'iPhone 15', 'storage_gb' => 128, 'color' => 'blue']);
        $rows = DB::table('post_attribute_values')->where('service_post_id', $id)->pluck('str', 'key')->all();
        $this->assertSame('apple', $rows['brand']);
        $this->assertSame('iphone 15', $rows['model'], 'text compared in lower case');
        $this->assertStringContainsString('iphone 15', DB::table('service_posts')->where('id', $id)->value('search_text'));

        $post = ServicePost::find($id);
        $post->details = PostAttributes::clean(2, ['brand' => 'apple', 'model' => 'iPhone 15', 'storage_gb' => 256, 'color' => 'black']);
        $post->save();
        $this->assertSame(256, (int) DB::table('post_attribute_values')->where('service_post_id', $id)->where('key', 'storage_gb')->value('num'));
        $this->assertSame(1, DB::table('post_attribute_values')->where('service_post_id', $id)->where('key', 'color')->count());
    }

    public function test_search_filters_by_details_and_offers_options_with_counts(): void
    {
        $a = $this->phone('iPhone 15 blue', ['brand' => 'apple', 'model' => 'iPhone 15', 'storage_gb' => 128, 'color' => 'blue']);
        $b = $this->phone('iPhone 15 black', ['brand' => 'apple', 'model' => 'iphone 15', 'storage_gb' => 128, 'color' => 'black']);
        $c = $this->phone('iPhone 15 256', ['brand' => 'apple', 'model' => 'iPhone 15', 'storage_gb' => 256, 'color' => 'blue']);
        $this->phone('Galaxy S24', ['brand' => 'samsung', 'model' => 'Galaxy S24', 'storage_gb' => 256]);
        Passport::actingAs($this->me);

        $r = $this->postJson('/api/search', ['search' => 'iPhone 15', 'facets' => 1])->assertOk();
        $this->assertSame(2, $r->json('facets.category_id'), 'the category most results are in');
        $storage = collect($r->json('facets.fields.storage_gb'))->pluck('count', 'value')->all();
        $this->assertSame([128 => 2, 256 => 1], $storage);
        $this->assertSame(['blue' => 2, 'black' => 1], collect($r->json('facets.fields.color'))->pluck('count', 'value')->all());
        $this->assertSame([['value' => 'iphone 15', 'count' => 3]], $r->json('facets.fields.model'));

        $ids = collect($this->postJson('/api/search', ['search' => 'iPhone 15', 'filters' => ['category_id' => 2, 'attr_storage_gb' => 128, 'attr_color' => 'blue']])
            ->json('posts.data'))->pluck('id')->all();
        $this->assertSame([$a], $ids);
        $this->assertNotContains($b, $ids);
        $this->assertNotContains($c, $ids);
    }

    public function test_the_category_feed_filters_by_model_in_any_case_and_counts(): void
    {
        $this->phone('a', ['model' => 'Galaxy S24', 'storage_gb' => 256]);
        $this->phone('b', ['model' => 'galaxy s24', 'storage_gb' => 512]);
        $this->phone('c', ['model' => 'Galaxy A55', 'storage_gb' => 128]);
        Passport::actingAs($this->me);

        $r = $this->getJson('/api/service_posts/categories/2?attr_model=GALAXY%20S24&facets=1')->assertOk();
        $this->assertCount(2, $r->json('servicePosts.data'));
        $this->assertSame([256 => 1, 512 => 1], collect($r->json('facets.storage_gb'))->pluck('count', 'value')->sortKeys()->all());

        // Older apps send a minimum
        $this->assertCount(2, $this->getJson('/api/service_posts/categories/2?attr_storage_gb_min=256')->json('servicePosts.data'));
    }

    public function test_a_search_query_for_fulltext_needs_every_long_word(): void
    {
        $this->assertSame('+iphone* +pro*', \App\Services\PostSearch::booleanQuery('iPhone 15 Pro'));
        $this->assertNull(\App\Services\PostSearch::booleanQuery('s 15'));
    }
}
