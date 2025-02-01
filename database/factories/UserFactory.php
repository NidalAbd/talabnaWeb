<?php

namespace Database\Factories;

use App\Models\cities;
use App\Models\countries;
use App\Models\Photos;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $country = countries::inRandomOrder()->first();
        $city = cities::where('country_id', $country->id)->inRandomOrder()->first();
        Log::info($city);
        return [
            'user_name' => fake()->userName(),
            'name' => fake()->name(),
            'gender' => $this->faker->randomElement(['ذكر' ,'انثى']),
            'country_id' => $country->id,
            'city_id' => $city->id,
            'date_of_birth' => fake()->dateTime(),
            'location_latitudes' => fake()->latitude(31.2, 32.5),
            'location_longitudes' => fake()->longitude(34.2, 35.5),
            'phones' => '0097'.fake()->randomElement([0, 2]).fake()->randomElement([56, 59]).fake()->randomNumber(7),
            'WatsNumber' => '0097'.fake()->randomElement([0, 2]).fake()->randomElement([56, 59]).fake()->randomNumber(7),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'is_active' => $this->faker->randomElement(['active', 'banned','inactive ']),
            'remember_token' => Str::random(10),
        ];

    }
    public function configure(): UserFactory
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::where('name', 'user')->first();
            $permissions = $role->permissions;
            $user->syncPermissions($permissions);
            $user->attachRole($role);
            $usersFollowing = User::inRandomOrder()->take(rand(10,22))->get();
            foreach ($usersFollowing as $following) {
                $user->following()->attach($following->id, ['created_at' => now()]);
            }
            $usersFollower = User::inRandomOrder()->take(rand(10,22))->get();
            foreach ($usersFollower as $follower) {
                $user->followers()->attach($follower->id, ['created_at' => now()]);
            }
            $photo = new Photos([
                'src' => fake()->randomElement(['photos/avatar1.png', 'photos/avatar2.png', 'photos/avatar3.png', 'photos/avatar4.png', 'photos/avatar5.png']),
            ]);
            $user->photos()->save($photo);
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return $this
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
