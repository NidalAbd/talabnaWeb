<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** 250 points (an existing Play product) instead of a new 200 package (owner, 2026-10-10). */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('point_packages')->where('points_amount', 200)->delete();
        if (! DB::table('point_packages')->where('points_amount', 250)->exists()) {
            DB::table('point_packages')->insert([
                'name' => json_encode(['ar' => '250 نقطة', 'en' => '250 Points'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode(['ar' => 'خصم 24%', 'en' => '24% off'], JSON_UNESCAPED_UNICODE),
                'points_amount' => 250, 'price' => 189.99, 'currency_code' => 'USD',
                'currency_name' => json_encode(['ar' => 'دولار', 'en' => 'USD'], JSON_UNESCAPED_UNICODE),
                'is_active' => true, 'is_popular' => false, 'display_order' => 8, 'validity_days' => 0,
                'discount_percentage' => 24, 'max_purchases' => 0, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('point_packages')->where('points_amount', 250)->delete();
    }
};
