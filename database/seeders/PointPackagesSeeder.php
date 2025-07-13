<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PointPackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => json_encode([
                    'ar' => 'الباقة الأساسية',
                    'en' => 'Basic Package'
                ]),
                'description' => json_encode([
                    'ar' => 'باقة أساسية للنقاط',
                    'en' => 'Basic points package'
                ]),
                'points_amount' => 100,
                'price' => 5.00,
                'currency_code' => 'USD',
                'currency_name' => json_encode([
                    'ar' => 'دولار أمريكي',
                    'en' => 'US Dollar'
                ]),
                'is_active' => true,
                'is_popular' => false,
                'display_order' => 1,
                'features' => json_encode([
                    'ar' => ['100 نقطة', 'صالح لمدة 30 يوم'],
                    'en' => ['100 points', 'Valid for 30 days']
                ]),
                'validity_days' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'ar' => 'الباقة المتقدمة',
                    'en' => 'Advanced Package'
                ]),
                'description' => json_encode([
                    'ar' => 'باقة متقدمة مع نقاط أكثر',
                    'en' => 'Advanced package with more points'
                ]),
                'points_amount' => 500,
                'price' => 20.00,
                'currency_code' => 'USD',
                'currency_name' => json_encode([
                    'ar' => 'دولار أمريكي',
                    'en' => 'US Dollar'
                ]),
                'is_active' => true,
                'is_popular' => true,
                'display_order' => 2,
                'features' => json_encode([
                    'ar' => ['500 نقطة', 'صالح لمدة 60 يوم', 'خصم 10%'],
                    'en' => ['500 points', 'Valid for 60 days', '10% discount']
                ]),
                'validity_days' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => json_encode([
                    'ar' => 'الباقة المميزة',
                    'en' => 'Premium Package'
                ]),
                'description' => json_encode([
                    'ar' => 'باقة مميزة مع أقصى قيمة',
                    'en' => 'Premium package with maximum value'
                ]),
                'points_amount' => 1000,
                'price' => 35.00,
                'currency_code' => 'USD',
                'currency_name' => json_encode([
                    'ar' => 'دولار أمريكي',
                    'en' => 'US Dollar'
                ]),
                'is_active' => true,
                'is_popular' => false,
                'display_order' => 3,
                'features' => json_encode([
                    'ar' => ['1000 نقطة', 'صالح لمدة 90 يوم', 'خصم 20%', 'مميزات إضافية'],
                    'en' => ['1000 points', 'Valid for 90 days', '20% discount', 'Additional features']
                ]),
                'validity_days' => 90,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($packages as $package) {
            DB::table('point_packages')->insert($package);
        }
    }
} 