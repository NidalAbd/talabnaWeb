<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Prices in points (2026-10-08, Nidal: "are they fair against what a point really costs?"). A point is sold at
 * $0.80-$0.99 (about $0.70-$0.84 after Google's share). Small AI text jobs cost us under a cent and were 1-2 points,
 * a featured post was $60-$300 a month, plans $20-$100: high for the region, and only 3 purchases in 90 days.
 *
 *  - AI: the suggestions that help people post are free; text jobs 1 point; images 1-3; video 10 (Veo costs ~$1.6-3.2).
 *  - Badges: priced per number of days (longer is cheaper per day); Diamond had less boost than Gold.
 *  - Plans 6 / 15 / 35 points a month; the shop 10; advanced insights 5.
 */
return new class extends Migration
{
    private const AI = [
        'suggest_category' => 0, 'suggest_price' => 0,
        'enhance_post' => 1, 'enhance_resume' => 1, 'translate_post' => 1, 'snap_to_sell' => 1,
        'studio_light' => 1, 'studio_background' => 1, 'generate_image' => 2, 'studio_scene' => 2, 'studio_cinematic' => 3,
        'generate_video' => 10, 'studio_video' => 10,
    ];

    /** slug => [1-day price, view boost %, price for 1..7 days] */
    private const BADGES = [
        'silver' => [1, 50, [1 => 1, 2 => 1, 3 => 2, 4 => 2, 5 => 3, 6 => 3, 7 => 4]],
        'gold' => [2, 100, [1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => 5, 6 => 6, 7 => 7]],
        'diamond' => [3, 200, [1 => 3, 2 => 5, 3 => 7, 4 => 8, 5 => 9, 6 => 11, 7 => 12]],
    ];

    private const PLANS = ['basic' => 6, 'pro' => 15, 'business' => 35];

    private const UNLOCKS = ['unlock.shop_page.points' => 10, 'unlock.advanced_insights.points' => 5];

    public function up(): void
    {
        if (! Schema::hasColumn('badge_types', 'price_by_days')) {
            Schema::table('badge_types', fn (Blueprint $t) => $t->json('price_by_days')->nullable()->after('points_per_day'));
        }
        foreach (self::AI as $key => $points) {
            DB::table('ai_features')->where('key', $key)->update(['points_cost' => $points, 'updated_at' => now()]);
        }
        foreach (self::BADGES as $slug => [$perDay, $boost, $table]) {
            DB::table('badge_types')->where('slug', $slug)->update([
                'points_per_day' => $perDay,
                'view_boost_percent' => $boost,
                'price_by_days' => json_encode(array_map('intval', array_combine(array_map('strval', array_keys($table)), $table))),
                'updated_at' => now(),
            ]);
        }
        foreach (self::PLANS as $slug => $points) {
            DB::table('subscription_plans')->where('slug', $slug)->update(['price_points' => $points, 'updated_at' => now()]);
        }
        foreach (self::UNLOCKS as $key => $points) {
            DB::table('app_settings')->updateOrInsert(['key' => $key], ['value' => (string) $points, 'updated_at' => now(), 'created_at' => now()]);
        }
    }

    public function down(): void
    {
        // Prices are business data: an older migration does not restore them.
    }
};
