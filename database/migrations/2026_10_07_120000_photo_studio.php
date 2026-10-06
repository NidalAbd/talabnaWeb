<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Release B (2026-10-07): AI Photo Studio and Snap to sell. Prices sit next to today's AI image (3 points); admins can
 * change them in AI pricing. Photos made or edited by AI carry ai_enhanced so the app can label them.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        foreach ([
            ['studio_light', 1, 'Studio: fix light and colour of a product photo'],
            ['studio_background', 2, 'Studio: clean studio background with a natural shadow'],
            ['studio_scene', 3, 'Studio: place the item in a lifestyle scene'],
            ['studio_cinematic', 4, 'Studio: HD cinematic shot'],
            ['snap_to_sell', 2, 'Snap to sell: fill a post from one photo'],
        ] as [$key, $points, $desc]) {
            if (! DB::table('ai_features')->where('key', $key)->exists()) {
                DB::table('ai_features')->insert(['key' => $key, 'points_cost' => $points, 'enabled' => true, 'description' => $desc,
                    'created_at' => $now, 'updated_at' => $now]);
            }
        }

        foreach (['photos'] as $t) {
            if (Schema::hasTable($t) && ! Schema::hasColumn($t, 'ai_enhanced')) {
                Schema::table($t, fn (Blueprint $table) => $table->boolean('ai_enhanced')->default(false));
            }
        }

        foreach (['pro', 'business'] as $slug) {
            $plan = DB::table('subscription_plans')->where('slug', $slug)->first();
            if (! $plan) {
                continue;
            }
            $features = json_decode($plan->features ?? '{}', true) ?: [];
            $features['snap_to_sell'] = true;
            DB::table('subscription_plans')->where('id', $plan->id)->update(['features' => json_encode($features)]);
        }
    }

    public function down(): void
    {
        DB::table('ai_features')->whereIn('key', ['studio_light', 'studio_background', 'studio_scene', 'studio_cinematic', 'snap_to_sell'])->delete();
    }
};
