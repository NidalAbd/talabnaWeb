<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use App\Services\Geo\LocationResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

/** The user's country and city from what the phone knows (2026-10-10), matched to our own rows. */
class LocationTest extends TestCase
{
    use MigratesTolerantly;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateTolerantly();
        DB::statement('PRAGMA foreign_keys = OFF');
        Cache::flush();
        DB::table('countries')->insert([
            ['id' => 1, 'name' => json_encode(['en' => 'Palestine', 'ar' => 'فلسطين']), 'country_code' => '00970', 'iso_code' => 'PS'],
            ['id' => 6, 'name' => json_encode(['en' => 'Jordan', 'ar' => 'الأردن']), 'country_code' => '00962', 'iso_code' => 'JO'],
        ]);
        $c = fn ($id, $country, $en, $ar) => ['id' => $id, 'country_id' => $country, 'name' => json_encode(['en' => $en, 'ar' => $ar], JSON_UNESCAPED_UNICODE)];
        DB::table('cities')->insert([
            $c(10, 1, 'Gaza City', 'مدينة غزة'),
            $c(11, 1, 'Khan Yunis', 'خان يونس'),
            $c(12, 1, 'Ramallah', 'رام الله'),
            $c(13, 1, 'Deir al-Balah', 'دير البلح'),
            $c(60, 6, 'Amman', 'عمّان'),
            $c(61, 6, 'Az-Zarqa', 'الزرقاء'),
        ]);
    }

    private function city(string $iso, array $names): ?int
    {
        return app(LocationResolver::class)->resolve($iso, $names)['city']?->id;
    }

    public function test_the_country_comes_from_the_iso_code(): void
    {
        $r = app(LocationResolver::class)->resolve('jo', []);
        $this->assertSame(6, $r['country']->id);
        $this->assertNull(app(LocationResolver::class)->resolve('XX', [])['country']);
        $this->assertNull(app(LocationResolver::class)->resolve('Jordan', [])['country']);
    }

    public function test_city_names_from_the_phone_match_ours_despite_spelling(): void
    {
        $this->assertSame(10, $this->city('PS', ['Gaza']), '"Gaza" is Gaza City');
        $this->assertSame(10, $this->city('PS', ['غزة']));
        $this->assertSame(11, $this->city('PS', ['Khan Younis']), 'vowels differ');
        $this->assertSame(11, $this->city('PS', ['خان يونس']));
        $this->assertSame(13, $this->city('PS', ['Deir el-Balah']));
        $this->assertSame(60, $this->city('JO', ['عمان']), 'shadda');
        $this->assertSame(61, $this->city('JO', ['Zarqa']));
        $this->assertSame(61, $this->city('JO', ['الزرقاء']));
        $this->assertSame(12, $this->city('PS', ['', 'Ramallah and al-Bireh Governorate', 'Ramallah']), 'the exact one wins');
    }

    public function test_no_guessing_without_a_real_match_or_in_another_country(): void
    {
        $this->assertNull($this->city('PS', ['Nablus']));
        $this->assertNull($this->city('PS', ['Amman']), 'only cities of that country');
        $this->assertNull($this->city('PS', ['ab']));
    }

    public function test_the_resolve_endpoint(): void
    {
        $this->postJson('/api/geo/resolve', ['iso' => 'PS', 'names' => ['Khan Younis']])
            ->assertOk()->assertJsonPath('country.id', 1)->assertJsonPath('city.id', 11)->assertJsonPath('city.name.en', 'Khan Yunis');
        $this->postJson('/api/geo/resolve', ['iso' => 'PS', 'names' => ['Nowhere']])
            ->assertOk()->assertJsonPath('country.iso_code', 'PS')->assertJsonPath('city', null);
    }

    private function makeUser(array $extra = []): User
    {
        $id = DB::table('users')->insertGetId(array_merge([
            'name' => 'Sam', 'user_name' => 'sam'.rand(1, 99999), 'email' => rand(1, 99999).'@example.com', 'gender' => 'ذكر', 'password' => 'x',
            'auth_type' => 'google', 'is_active' => 'active', 'created_at' => now(), 'updated_at' => now(), 'country_id' => 1, 'city_id' => 10,
        ], $extra));

        return User::find($id);
    }

    public function test_confirming_the_first_time_is_not_a_country_change(): void
    {
        $u = $this->makeUser(['phone_verified_at' => now()]);
        Passport::actingAs($u);
        $this->getJson("/api/user/profile/{$u->id}")->assertJsonPath('userData.location_confirmed', false);

        $this->postJson('/api/user/location', ['country_id' => 6, 'city_id' => 60, 'lat' => 31.95, 'lng' => 35.93])
            ->assertOk()->assertJsonPath('country.id', 6)->assertJsonPath('city.id', 60);
        $u->refresh();
        $this->assertSame(6, (int) $u->country_id);
        $this->assertNotNull($u->location_confirmed_at);
        $this->assertNull($u->country_changed_at, 'the old default was never their choice');
        $this->assertEquals(31.95, (float) $u->location_latitudes);
        $this->getJson("/api/user/profile/{$u->id}")->assertJsonPath('userData.location_confirmed', true);

        // A later change with a verified phone starts the wait
        $this->postJson('/api/user/location', ['country_id' => 1, 'city_id' => 11])->assertOk();
        $this->assertNotNull($u->fresh()->country_changed_at);
        $this->postJson('/api/user/location', ['country_id' => 6])->assertStatus(422)->assertJsonPath('error_type', 'country_locked');
    }

    public function test_a_city_of_another_country_is_refused(): void
    {
        Passport::actingAs($this->makeUser());
        $this->postJson('/api/user/location', ['country_id' => 1, 'city_id' => 60])->assertStatus(422)->assertJsonPath('field', 'city');
        $this->postJson('/api/user/location', ['country_id' => 1])->assertOk()->assertJsonPath('city', null);
    }

    public function test_confirming_needs_a_login(): void
    {
        $this->postJson('/api/user/location', ['country_id' => 1])->assertStatus(401);
    }
}
