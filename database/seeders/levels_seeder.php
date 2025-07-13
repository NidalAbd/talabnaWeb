<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            [
                'name' => json_encode([
                    'ar' => 'عادي',
                    'en' => 'Regular'
                ]),
                'description' => json_encode([
                    'ar' => 'المنشور العادي بدون مميزات إضافية',
                    'en' => 'Regular post without additional features'
                ]),
                'icon' => 'fas fa-check',
                'color' => '#6c757d',
                'points_per_day' => 0,
                'view_boost_percentage' => 0,
                'display_order' => 1,
                'is_active' => true,
                'is_premium' => false,
                'features' => json_encode([])
            ],
            [
                'name' => json_encode([
                    'ar' => 'ذهبي',
                    'en' => 'Gold'
                ]),
                'description' => json_encode([
                    'ar' => 'منشور ذهبي مع مميزات متقدمة',
                    'en' => 'Gold post with advanced features'
                ]),
                'icon' => 'fas fa-medal',
                'color' => '#FFD700',
                'points_per_day' => 2,
                'view_boost_percentage' => 200,
                'display_order' => 2,
                'is_active' => true,
                'is_premium' => true,
                'features' => json_encode([
                    'ar' => ['زيادة المشاهدات بنسبة 200%', 'عرض في مكان مميز', 'أولوية في البحث'],
                    'en' => ['200% view boost', 'Premium placement', 'Search priority']
                ])
            ],
            [
                'name' => json_encode([
                    'ar' => 'ماسي',
                    'en' => 'Diamond'
                ]),
                'description' => json_encode([
                    'ar' => 'منشور ماسي مع أقصى مميزات متاحة',
                    'en' => 'Diamond post with maximum available features'
                ]),
                'icon' => 'fas fa-gem',
                'color' => '#00CCFF',
                'points_per_day' => 10,
                'view_boost_percentage' => 500,
                'display_order' => 3,
                'is_active' => true,
                'is_premium' => true,
                'features' => json_encode([
                    'ar' => ['زيادة المشاهدات بنسبة 500%', 'عرض في أعلى القائمة', 'أولوية قصوى في البحث', 'مميزات إضافية'],
                    'en' => ['500% view boost', 'Top placement', 'Highest search priority', 'Additional features']
                ])
            ]
        ];

        foreach ($levels as $level) {
            DB::table('levels')->insert($level);
        }
    }
} 