<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PremiumFeaturesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            [
                'name' => json_encode([
                    'ar' => 'منشور مميز',
                    'en' => 'Featured Post'
                ]),
                'description' => json_encode([
                    'ar' => 'عرض منشورك في مكان مميز لمدة 7 أيام',
                    'en' => 'Display your post in a featured position for 7 days'
                ]),
                'point_cost' => 50,
                'is_active' => true,
                'feature_type' => 'post_enhancement',
                'icon' => 'fas fa-star',
                'color' => '#FFD700',
                'benefits' => json_encode([
                    'ar' => ['زيادة المشاهدات', 'عرض مميز', 'أولوية في البحث'],
                    'en' => ['Increased views', 'Featured display', 'Search priority']
                ]),
                'display_order' => 1,
                'is_popular' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'ar' => 'منشور ذهبي',
                    'en' => 'Gold Post'
                ]),
                'description' => json_encode([
                    'ar' => 'منشور ذهبي مع زيادة كبيرة في المشاهدات',
                    'en' => 'Gold post with significant view increase'
                ]),
                'point_cost' => 100,
                'is_active' => true,
                'feature_type' => 'post_enhancement',
                'icon' => 'fas fa-medal',
                'color' => '#FFD700',
                'benefits' => json_encode([
                    'ar' => ['زيادة كبيرة في المشاهدات', 'عرض مميز جداً', 'أولوية قصوى'],
                    'en' => ['Major view increase', 'Premium display', 'Top priority']
                ]),
                'display_order' => 2,
                'is_popular' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'ar' => 'منشور ماسي',
                    'en' => 'Diamond Post'
                ]),
                'description' => json_encode([
                    'ar' => 'أعلى مستوى من التميز مع أقصى زيادة في المشاهدات',
                    'en' => 'Highest level of premium with maximum visibility'
                ]),
                'point_cost' => 200,
                'is_active' => true,
                'feature_type' => 'post_enhancement',
                'icon' => 'fas fa-gem',
                'color' => '#00CCFF',
                'benefits' => json_encode([
                    'ar' => ['أقصى زيادة في المشاهدات', 'عرض مميز جداً', 'أولوية قصوى', 'مميزات إضافية'],
                    'en' => ['Maximum view boost', 'Premium display', 'Top priority', 'Additional features']
                ]),
                'display_order' => 3,
                'is_popular' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'ar' => 'ملف شخصي مميز',
                    'en' => 'Premium Profile'
                ]),
                'description' => json_encode([
                    'ar' => 'ملف شخصي مميز مع مميزات إضافية',
                    'en' => 'Premium profile with additional features'
                ]),
                'point_cost' => 150,
                'is_active' => true,
                'feature_type' => 'user_benefit',
                'icon' => 'fas fa-crown',
                'color' => '#FF6B35',
                'benefits' => json_encode([
                    'ar' => ['ملف شخصي مميز', 'مميزات إضافية', 'أولوية في النتائج'],
                    'en' => ['Premium profile', 'Additional features', 'Priority in results']
                ]),
                'display_order' => 4,
                'is_popular' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($features as $feature) {
            DB::table('premium_features')->insert($feature);
        }
    }
} 