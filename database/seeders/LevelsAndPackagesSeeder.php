<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\PointPackage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LevelsAndPackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default levels
        $levels = [
            [
                'name' => [
                    'ar' => 'عادي',
                    'en' => 'Regular'
                ],
                'description' => [
                    'ar' => 'المستوى الأساسي للخدمات',
                    'en' => 'Basic service level'
                ],
                'icon' => 'fas fa-circle',
                'color' => '#6c757d',
                'points_per_day' => 0,
                'view_boost_percentage' => 0,
                'display_order' => 1,
                'is_active' => true,
                'is_premium' => false,
                'features' => [
                    'ar' => ['عرض أساسي', 'مدة محدودة'],
                    'en' => ['Basic display', 'Limited duration']
                ]
            ],
            [
                'name' => [
                    'ar' => 'ذهبي',
                    'en' => 'Gold'
                ],
                'description' => [
                    'ar' => 'مستوى ذهبي مع مزايا متقدمة',
                    'en' => 'Gold level with advanced features'
                ],
                'icon' => 'fas fa-star',
                'color' => '#ffd700',
                'points_per_day' => 10,
                'view_boost_percentage' => 25,
                'display_order' => 2,
                'is_active' => true,
                'is_premium' => true,
                'features' => [
                    'ar' => ['عرض مميز', 'مدة أطول', 'زيادة في المشاهدات'],
                    'en' => ['Featured display', 'Longer duration', 'View boost']
                ]
            ],
            [
                'name' => [
                    'ar' => 'ماسي',
                    'en' => 'Diamond'
                ],
                'description' => [
                    'ar' => 'أعلى مستوى مع جميع المزايا',
                    'en' => 'Highest level with all features'
                ],
                'icon' => 'fas fa-gem',
                'color' => '#00d4ff',
                'points_per_day' => 25,
                'view_boost_percentage' => 50,
                'display_order' => 3,
                'is_active' => true,
                'is_premium' => true,
                'features' => [
                    'ar' => ['عرض مميز جداً', 'مدة طويلة', 'زيادة كبيرة في المشاهدات', 'أولوية في البحث'],
                    'en' => ['Premium display', 'Extended duration', 'High view boost', 'Search priority']
                ]
            ]
        ];

        foreach ($levels as $levelData) {
            Level::create($levelData);
        }

        // Create default point packages
        $packages = [
            [
                'name' => [
                    'ar' => 'حزمة البداية',
                    'en' => 'Starter Package'
                ],
                'description' => [
                    'ar' => 'مثالية للمستخدمين الجدد للبدء',
                    'en' => 'Perfect for new users to get started'
                ],
                'points_amount' => 100,
                'price' => 9.99,
                'currency_code' => 'USD',
                'currency_name' => [
                    'ar' => 'دولار أمريكي',
                    'en' => 'US Dollar'
                ],
                'is_active' => true,
                'is_popular' => false,
                'display_order' => 1,
                'validity_days' => 365,
                'features' => [
                    'ar' => ['ترقية المستوى الأساسي', 'دعم لمدة 7 أيام', 'ميزات قياسية'],
                    'en' => ['Basic level upgrades', '7-day support', 'Standard features']
                ]
            ],
            [
                'name' => [
                    'ar' => 'حزمة احترافية',
                    'en' => 'Professional Package'
                ],
                'description' => [
                    'ar' => 'قيمة رائعة للمستخدمين النشطين',
                    'en' => 'Great value for active users'
                ],
                'points_amount' => 500,
                'price' => 39.99,
                'currency_code' => 'USD',
                'currency_name' => [
                    'ar' => 'دولار أمريكي',
                    'en' => 'US Dollar'
                ],
                'is_active' => true,
                'is_popular' => true,
                'display_order' => 2,
                'validity_days' => 365,
                'features' => [
                    'ar' => ['ترقية المستوى الذهبي', 'دعم ذو أولوية', 'ميزات متقدمة', 'مدة ممتدة'],
                    'en' => ['Gold level upgrades', 'Priority support', 'Advanced features', 'Extended duration']
                ]
            ],
            [
                'name' => [
                    'ar' => 'حزمة مميزة',
                    'en' => 'Premium Package'
                ],
                'description' => [
                    'ar' => 'أفضل قيمة للمستخدمين المتقدمين',
                    'en' => 'Best value for power users'
                ],
                'points_amount' => 1000,
                'price' => 69.99,
                'currency_code' => 'USD',
                'currency_name' => [
                    'ar' => 'دولار أمريكي',
                    'en' => 'US Dollar'
                ],
                'is_active' => true,
                'is_popular' => false,
                'display_order' => 3,
                'validity_days' => 365,
                'features' => [
                    'ar' => ['ترقية المستوى الماسي', 'دعم 24/7', 'جميع الميزات المميزة', 'أقصى مدة', 'أولوية في العرض'],
                    'en' => ['Diamond level upgrades', '24/7 support', 'All premium features', 'Maximum duration', 'Priority placement']
                ]
            ]
        ];

        foreach ($packages as $packageData) {
            PointPackage::create($packageData);
        }

        $this->command->info('Default levels and point packages created successfully!');
    }
}
