<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Points packages on sale (owner, 2026-10-10): 5, 10, 25, 50, 100, 250, 500, all existing Google Play products
 * (250 instead of a new 200). Packages under 5 points are hidden. The app shows the store's price; these rows decide
 * which packages appear. 1000 points = 2 x 500 (Play caps one product at about USD 400).
 */
return new class extends Migration
{
    private function row(int $points, float $price, int $discount, int $order, bool $active): array
    {
        return [
            'name' => json_encode(['ar' => "$points نقطة", 'en' => "$points Points"], JSON_UNESCAPED_UNICODE),
            'description' => json_encode(['ar' => "خصم $discount%", 'en' => "$discount% off"], JSON_UNESCAPED_UNICODE),
            'points_amount' => $points, 'price' => $price, 'currency_code' => 'USD',
            'currency_name' => json_encode(['ar' => 'دولار', 'en' => 'USD'], JSON_UNESCAPED_UNICODE),
            'is_active' => $active, 'is_popular' => false, 'display_order' => $order, 'validity_days' => 0,
            'discount_percentage' => $discount, 'max_purchases' => 0, 'created_at' => now(), 'updated_at' => now(),
        ];
    }

    public function up(): void
    {
        DB::table('point_packages')->where('points_amount', '<', 5)->update(['is_active' => false, 'updated_at' => now()]);
        DB::table('point_packages')->where('points_amount', 200)->delete(); // an earlier step added it switched off
        foreach ([[250, 189.99, 24, 8, true], [500, 359.99, 28, 9, true]] as [$points, $price, $discount, $order, $active]) {
            if (! DB::table('point_packages')->where('points_amount', $points)->exists()) {
                DB::table('point_packages')->insert($this->row($points, $price, $discount, $order, $active));
            }
        }
    }

    public function down(): void
    {
        DB::table('point_packages')->whereIn('points_amount', [250, 500])->delete();
    }
};
