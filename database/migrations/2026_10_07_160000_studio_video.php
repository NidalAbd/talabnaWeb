<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Release C: cinematic video from a photo (same price as today's AI video); Business includes 2 AI videos a month. */
return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('ai_features')->where('key', 'studio_video')->exists()) {
            DB::table('ai_features')->insert(['key' => 'studio_video', 'points_cost' => 20, 'enabled' => true,
                'description' => 'Studio: cinematic video from a photo', 'created_at' => now(), 'updated_at' => now()]);
        }
        $plan = DB::table('subscription_plans')->where('slug', 'business')->first();
        if ($plan) {
            $features = json_decode($plan->features ?? '{}', true) ?: [];
            $features['ai_videos_per_month'] = $features['ai_videos_per_month'] ?? 2;
            DB::table('subscription_plans')->where('id', $plan->id)->update(['features' => json_encode($features)]);
        }
    }

    public function down(): void
    {
        DB::table('ai_features')->where('key', 'studio_video')->delete();
    }
};
