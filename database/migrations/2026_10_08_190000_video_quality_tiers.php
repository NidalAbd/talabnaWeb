<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Video qualities (2026-10-08, Nidal: let the user choose a quality, not a model). Each 8 s clip:
 *   normal - Veo 3.1 Lite 720p    (costs us ~$0.40)   5 points
 *   hd     - Veo 3.1 Fast 1080p   (~$0.96)           10 points
 *   pro    - Veo 3.1 1080p        (~$3.20)           20 points (Nidal: 20 or 25)
 * A point nets about $0.60-0.84 after Google Play's fee and tax. Apps that send no quality get hd (the feature rows
 * generate_video / studio_video keep its price).
 */
return new class extends Migration
{
    private const PRICES = ['normal' => 5, 'hd' => 10, 'pro' => 20];

    public function up(): void
    {
        if (! Schema::hasColumn('ai_requests', 'quality')) {
            Schema::table('ai_requests', fn (Blueprint $t) => $t->string('quality', 10)->nullable()->after('feature'));
        }
        foreach (['generate_video' => 'Video', 'studio_video' => 'Studio video'] as $feature => $label) {
            DB::table('ai_features')->where('key', $feature)->update(['points_cost' => self::PRICES['hd'], 'updated_at' => now()]);
            foreach (self::PRICES as $quality => $points) {
                DB::table('ai_features')->updateOrInsert(['key' => "{$feature}_{$quality}"], [
                    'points_cost' => $points, 'enabled' => true, 'description' => "$label, $quality quality",
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('ai_features')->where('key', 'like', '%video\\_normal')->orWhere('key', 'like', '%video\\_hd')->orWhere('key', 'like', '%video\\_pro')->delete();
        if (Schema::hasColumn('ai_requests', 'quality')) {
            Schema::table('ai_requests', fn (Blueprint $t) => $t->dropColumn('quality'));
        }
    }
};
