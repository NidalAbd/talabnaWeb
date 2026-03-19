<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => ['ar' => 'الأساسي', 'en' => 'Basic'],
                'description' => [
                    'ar' => 'خطة أساسية للمستخدمين الجدد مع ميزات محدودة',
                    'en' => 'Basic plan for new users with limited features',
                ],
                'slug' => 'basic',
                'price_points' => 100,
                'duration_days' => 30,
                'features' => [
                    'ai_images_per_month' => 5,
                    'bonus_points_percent' => 0,
                    'featured_posts' => 1,
                    'auto_translate_posts' => false,
                    'priority_support' => false,
                    'max_photos_per_post' => 10,
                    'badge_discount_percent' => 0,
                ],
                'color' => '#2196F3',
                'icon' => 'star_outline',
                'sort_order' => 1,
                'is_active' => true,
                'is_popular' => false,
            ],
            [
                'name' => ['ar' => 'المحترف', 'en' => 'Pro'],
                'description' => [
                    'ar' => 'خطة احترافية مع ميزات متقدمة وترجمة تلقائية',
                    'en' => 'Professional plan with advanced features and auto-translation',
                ],
                'slug' => 'pro',
                'price_points' => 300,
                'duration_days' => 30,
                'features' => [
                    'ai_images_per_month' => 20,
                    'bonus_points_percent' => 5,
                    'featured_posts' => 5,
                    'auto_translate_posts' => true,
                    'priority_support' => false,
                    'max_photos_per_post' => 20,
                    'badge_discount_percent' => 10,
                ],
                'color' => '#FF9800',
                'icon' => 'star',
                'sort_order' => 2,
                'is_active' => true,
                'is_popular' => true,
            ],
            [
                'name' => ['ar' => 'الأعمال', 'en' => 'Business'],
                'description' => [
                    'ar' => 'خطة أعمال شاملة مع جميع الميزات وخصومات حصرية',
                    'en' => 'Comprehensive business plan with all features and exclusive discounts',
                ],
                'slug' => 'business',
                'price_points' => 500,
                'duration_days' => 30,
                'features' => [
                    'ai_images_per_month' => 50,
                    'bonus_points_percent' => 10,
                    'featured_posts' => 15,
                    'auto_translate_posts' => true,
                    'priority_support' => true,
                    'max_photos_per_post' => 30,
                    'badge_discount_percent' => 20,
                ],
                'color' => '#9C27B0',
                'icon' => 'workspace_premium',
                'sort_order' => 3,
                'is_active' => true,
                'is_popular' => false,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
