<?php

namespace Tests\Feature\Categories;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

/** Services and Urgent are shown in every country (2026-10-10; Palestine lost Services, the rest lost Urgent). */
class CategoriesEverywhereTest extends TestCase
{
    use MigratesTolerantly;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateTolerantly();
        DB::statement('PRAGMA foreign_keys = OFF');
        foreach ([1 => 'Jobs', 5 => 'Services', 8 => 'Urgent'] as $id => $en) {
            DB::table('categories')->insert(['id' => $id, 'name' => json_encode(['en' => $en, 'ar' => $en]), 'isSuspended' => 0]);
        }
    }

    private function userIn(int $countryId): User
    {
        $id = DB::table('users')->insertGetId([
            'name' => 'U', 'user_name' => 'u'.rand(1, 999999), 'email' => rand(1, 999999).'@example.com', 'gender' => 'ذكر',
            'password' => 'x', 'auth_type' => 'email', 'is_active' => 'active', 'country_id' => $countryId,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return User::find($id);
    }

    public function test_every_country_sees_services_and_urgent(): void
    {
        foreach ([1, 6] as $country) { // Palestine, and another country
            Passport::actingAs($this->userIn($country));
            foreach (['/api/categories_menu', '/api/categories_list'] as $url) {
                $ids = collect($this->getJson($url)->assertOk()->json('categories'))->pluck('id')->sort()->values()->all();
                $this->assertSame([1, 5, 8], $ids, "$url for country $country");
            }
        }
    }
}
