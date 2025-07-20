<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            [
                'name' => json_encode(['ar' => 'عادي', 'en' => 'Regular']),
                'description' => json_encode(['ar' => 'المستوى العادي - المستوى الأساسي للمنشورات', 'en' => 'Regular level - Basic level for posts']),
                'icon' => 'circle',
                'color' => '#6c757d',
                'points_per_day' => 0,
                'view_boost_percentage' => 0,
                'display_order' => 0,
                'is_active' => true,
                'is_premium' => false,
                'features' => json_encode([
                    'ar' => ['عرض عادي', 'مدة غير محدودة'],
                    'en' => ['Standard display', 'Unlimited duration']
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode(['ar' => 'ذهبي', 'en' => 'Gold']),
                'description' => json_encode(['ar' => 'المستوى الذهبي - منشورات مميزة مع تعزيز المشاهدات', 'en' => 'Gold level - Premium posts with view boost']),
                'icon' => 'star',
                'color' => '#ffc107',
                'points_per_day' => 10,
                'view_boost_percentage' => 50,
                'display_order' => 1,
                'is_active' => true,
                'is_premium' => true,
                'features' => json_encode([
                    'ar' => ['تعزيز المشاهدات 50%', 'عرض مميز', 'مدة محدودة'],
                    'en' => ['50% view boost', 'Premium display', 'Limited duration']
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode(['ar' => 'ماسي', 'en' => 'Diamond']),
                'description' => json_encode(['ar' => 'المستوى الماسي - أعلى مستوى مع تعزيز كامل للمشاهدات', 'en' => 'Diamond level - Highest level with full view boost']),
                'icon' => 'gem',
                'color' => '#17a2b8',
                'points_per_day' => 20,
                'view_boost_percentage' => 100,
                'display_order' => 2,
                'is_active' => true,
                'is_premium' => true,
                'features' => json_encode([
                    'ar' => ['تعزيز المشاهدات 100%', 'عرض مميز جداً', 'مدة محدودة'],
                    'en' => ['100% view boost', 'Premium display', 'Limited duration']
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($levels as $level) {
            DB::table('levels')->insertOrIgnore($level);
        }

        $this->command->info('Levels seeded successfully!');
    }
}
