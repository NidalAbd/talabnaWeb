<?php

namespace Tests\Feature\Seo;

use App\Http\Controllers\SeoController;
use App\Models\ServicePost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

/** The listing's Product data shows ratings only when buyers really reviewed it (Search Console, 2026-10-10). */
class ProductReviewsSchemaTest extends TestCase
{
    use MigratesTolerantly;

    private function schema(int $postId): ?array
    {
        $m = new \ReflectionMethod(SeoController::class, 'productSchema');
        $m->setAccessible(true);

        return $m->invoke(app(SeoController::class), ServicePost::with('photos', 'user')->find($postId), 'iPhone 15', 'Clean', 'https://talbna.cloud/listing/1', 'Devices');
    }

    public function test_ratings_only_from_real_reviews_of_the_listing(): void
    {
        $this->migrateTolerantly();
        DB::statement('PRAGMA foreign_keys = OFF');
        if (! Schema::hasColumn('service_posts', 'title')) {
            Schema::table('service_posts', fn ($t) => $t->text('title')->nullable());
        }
        $seller = DB::table('users')->insertGetId(['name' => 'Seller', 'user_name' => 'seller', 'email' => 's@x.com', 'gender' => 'ذكر', 'password' => 'x', 'auth_type' => 'email', 'is_active' => 'active']);
        $buyer = DB::table('users')->insertGetId(['name' => 'Sara', 'user_name' => 'sara', 'email' => 'b@x.com', 'gender' => 'انثى', 'password' => 'x', 'auth_type' => 'email', 'is_active' => 'active']);
        $post = DB::table('service_posts')->insertGetId(['user_id' => $seller, 'title' => 'iPhone', 'price' => 900, 'price_currency_code' => 'USD', 'state' => 'published', 'type' => 'عرض', 'have_badge' => 'عادي', 'categories_id' => 2, 'sub_categories_id' => 1]);

        $none = $this->schema($post);
        $this->assertArrayNotHasKey('aggregateRating', $none, 'no made-up ratings');
        $this->assertArrayNotHasKey('review', $none);

        // A review of the seller in general is not a review of this item
        DB::table('reviews')->insert(['reviewer_id' => $buyer, 'reviewed_user_id' => $seller, 'rating' => 2, 'created_at' => now(), 'updated_at' => now()]);
        $this->assertArrayNotHasKey('aggregateRating', $this->schema($post));

        DB::table('reviews')->insert(['reviewer_id' => $buyer, 'reviewed_user_id' => $seller, 'service_post_id' => $post, 'rating' => 4, 'comment' => 'As described', 'created_at' => now(), 'updated_at' => now()]);
        $s = $this->schema($post);
        $this->assertSame(['@type' => 'AggregateRating', 'ratingValue' => 4.0, 'reviewCount' => 1, 'bestRating' => 5, 'worstRating' => 1], $s['aggregateRating']);
        $this->assertSame('Sara', $s['review'][0]['author']['name']);
        $this->assertSame(4, $s['review'][0]['reviewRating']['ratingValue']);
        $this->assertSame('As described', $s['review'][0]['reviewBody']);
    }
}
