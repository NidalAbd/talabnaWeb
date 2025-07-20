<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Level>
 */
class LevelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $levelNames = [
            'Regular' => 'عادي',
            'Gold' => 'ذهبي',
            'Diamond' => 'ماسي',
            'Platinum' => 'بلاتيني',
            'VIP' => 'VIP'
        ];

        $levelName = $this->faker->randomElement(array_keys($levelNames));
        $isPremium = $levelName !== 'Regular';

        return [
            'name' => json_encode([
                'en' => $levelName,
                'ar' => $levelNames[$levelName]
            ]),
            'description' => json_encode([
                'en' => "{$levelName} level description",
                'ar' => "وصف المستوى {$levelNames[$levelName]}"
            ]),
            'icon' => $this->faker->randomElement(['star', 'gem', 'crown', 'diamond', 'circle']),
            'color' => $this->faker->randomElement(['#6c757d', '#ffc107', '#17a2b8', '#28a745', '#dc3545']),
            'points_per_day' => $isPremium ? $this->faker->numberBetween(5, 25) : 0,
            'view_boost_percentage' => $isPremium ? $this->faker->numberBetween(25, 100) : 0,
            'display_order' => $this->faker->numberBetween(0, 10),
            'is_active' => true,
            'is_premium' => $isPremium,
            'features' => json_encode([
                'en' => $isPremium ? ['Premium display', 'View boost', 'Limited duration'] : ['Standard display', 'Unlimited duration'],
                'ar' => $isPremium ? ['عرض مميز', 'تعزيز المشاهدات', 'مدة محدودة'] : ['عرض عادي', 'مدة غير محدودة']
            ]),
        ];
    }

    /**
     * Indicate that the level is regular (non-premium).
     */
    public function regular(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => json_encode(['en' => 'Regular', 'ar' => 'عادي']),
            'description' => json_encode(['en' => 'Regular level', 'ar' => 'المستوى العادي']),
            'icon' => 'circle',
            'color' => '#6c757d',
            'points_per_day' => 0,
            'view_boost_percentage' => 0,
            'display_order' => 0,
            'is_premium' => false,
        ]);
    }

    /**
     * Indicate that the level is premium.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_premium' => true,
            'points_per_day' => $this->faker->numberBetween(5, 25),
            'view_boost_percentage' => $this->faker->numberBetween(25, 100),
        ]);
    }
}
