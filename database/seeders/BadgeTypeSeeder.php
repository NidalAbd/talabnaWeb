<?php

namespace Database\Seeders;

use App\Models\BadgeType;
use Illuminate\Database\Seeder;

class BadgeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            [
                'name' => ['ar' => 'ماسي', 'en' => 'Diamond'],
                'slug' => 'diamond',
                'color' => '#9C27B0',
                'icon' => 'mdi-diamond-stone',
                'points_per_day' => 10,
                'priority' => 1,
                'view_boost_percent' => 500,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'ذهبي', 'en' => 'Gold'],
                'slug' => 'gold',
                'color' => '#FFD700',
                'icon' => 'mdi-star',
                'points_per_day' => 5,
                'priority' => 2,
                'view_boost_percent' => 200,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'فضي', 'en' => 'Silver'],
                'slug' => 'silver',
                'color' => '#C0C0C0',
                'icon' => 'mdi-medal',
                'points_per_day' => 2,
                'priority' => 3,
                'view_boost_percent' => 100,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'name' => ['ar' => 'عادي', 'en' => 'Normal'],
                'slug' => 'normal',
                'color' => '#808080',
                'icon' => 'mdi-tag',
                'points_per_day' => 0,
                'priority' => 99,
                'view_boost_percent' => 0,
                'is_default' => true,
                'is_active' => true,
            ],
        ];

        foreach ($badges as $badge) {
            BadgeType::updateOrCreate(
                ['slug' => $badge['slug']],
                $badge
            );
        }

        $this->command->info('Badge types seeded successfully!');
    }
}
