<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fair plan prices (2026-10-03). 1 point ≈ $1 in the stores, and à-la-carte
 * an AI image is 3 points, a translation 2, a Silver week 14 — so Basic at
 * 100 points for 5 images was ~6× its value. Each plan is now priced well
 * below what its contents cost separately. Priority support is dropped
 * (nothing delivered it). Existing subscribers keep what they bought.
 */
return new class extends Migration
{
    public function up(): void
    {
        $plans = [
            'basic' => [10, ['ai_images_per_month' => 5, 'bonus_points_percent' => 0, 'featured_posts' => 1,
                'auto_translate_posts' => false, 'priority_support' => false, 'max_photos_per_post' => 10, 'badge_discount_percent' => 0]],
            'pro' => [25, ['ai_images_per_month' => 20, 'bonus_points_percent' => 5, 'featured_posts' => 3,
                'auto_translate_posts' => true, 'priority_support' => false, 'max_photos_per_post' => 20, 'badge_discount_percent' => 10]],
            'business' => [50, ['ai_images_per_month' => 50, 'bonus_points_percent' => 10, 'featured_posts' => 8,
                'auto_translate_posts' => true, 'priority_support' => false, 'max_photos_per_post' => 30, 'badge_discount_percent' => 20]],
        ];
        foreach ($plans as $slug => [$price, $features]) {
            DB::table('subscription_plans')->where('slug', $slug)->update([
                'price_points' => $price,
                'features' => json_encode($features),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        foreach (['basic' => 100, 'pro' => 300, 'business' => 500] as $slug => $price) {
            DB::table('subscription_plans')->where('slug', $slug)->update(['price_points' => $price]);
        }
    }
};
