<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

/** PUT /api/users/{id}: only the owner (or staff) may edit; username/name rules; nothing else is disturbed. */
class ProfileUpdateTest extends TestCase
{
    use MigratesTolerantly;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateTolerantly();
        DB::statement('PRAGMA foreign_keys = OFF');
    }

    private function makeUser(string $userName, array $extra = []): User
    {
        $id = DB::table('users')->insertGetId(array_merge([
            'name' => ucfirst($userName), 'user_name' => $userName, 'email' => "$userName@example.com", 'gender' => 'ذكر', 'password' => 'x',
            'auth_type' => 'email', 'is_active' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ], $extra));

        return User::find($id);
    }

    public function test_the_owner_can_update_name_username_and_contact_details(): void
    {
        $me = $this->makeUser('sara1');
        Passport::actingAs($me);

        $this->putJson("/api/users/{$me->id}", ['name' => 'Sara Ali', 'user_name' => 'sara.ali', 'phones' => '00201001234567'])
            ->assertOk()->assertJsonPath('status', 'success');

        $me->refresh();
        $this->assertSame('Sara Ali', $me->name);
        $this->assertSame('sara.ali', $me->user_name);
        $this->assertSame('00201001234567', $me->phones);
    }

    public function test_someone_else_cannot_edit_a_profile(): void
    {
        $victim = $this->makeUser('victim1', ['phones' => '0011']);
        Passport::actingAs($this->makeUser('intruder1'));

        $this->putJson("/api/users/{$victim->id}", ['name' => 'Hacked', 'user_name' => 'hacked', 'phones' => '9999'])->assertStatus(403);

        $victim->refresh();
        $this->assertSame('Victim1', $victim->name);
        $this->assertSame('victim1', $victim->user_name);
        $this->assertSame('0011', $victim->phones);
    }

    public function test_an_admin_can_edit_any_profile(): void
    {
        $target = $this->makeUser('target1');
        $admin = $this->makeUser('boss1');
        $role = config('laratrust.models.role')::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
        $admin->attachRole($role);
        Passport::actingAs($admin);

        $this->putJson("/api/users/{$target->id}", ['name' => 'Renamed By Staff'])->assertOk();
        $this->assertSame('Renamed By Staff', $target->fresh()->name);
    }

    public function test_a_taken_username_is_a_field_error_on_user_name(): void
    {
        $this->makeUser('taken1');
        $me = $this->makeUser('sara2');
        Passport::actingAs($me);

        $this->putJson("/api/users/{$me->id}", ['user_name' => 'taken1'])
            ->assertStatus(422)->assertJsonPath('error_type', 'unique_constraint')->assertJsonPath('field', 'user_name');
        $this->assertSame('sara2', $me->fresh()->user_name);
    }

    public function test_a_malformed_username_is_refused(): void
    {
        $me = $this->makeUser('sara3');
        Passport::actingAs($me);

        foreach (['ab', 'has space', 'bad-char!', str_repeat('a', 31)] as $bad) {
            $this->putJson("/api/users/{$me->id}", ['user_name' => $bad])->assertStatus(422)->assertJsonPath('field', 'user_name');
        }
        $this->assertSame('sara3', $me->fresh()->user_name);
    }

    public function test_an_older_username_in_another_format_does_not_block_saving_other_details(): void
    {
        $legacy = $this->makeUser('محمد-قديم');
        Passport::actingAs($legacy);

        $this->putJson("/api/users/{$legacy->id}", ['user_name' => 'محمد-قديم', 'phones' => '00201007654321'])->assertOk();
        $this->assertSame('00201007654321', $legacy->fresh()->phones);
        $this->assertSame('محمد-قديم', $legacy->fresh()->user_name);
    }

    public function test_a_too_short_name_is_refused_and_a_missing_name_leaves_it_alone(): void
    {
        $me = $this->makeUser('sara4');
        Passport::actingAs($me);

        $this->putJson("/api/users/{$me->id}", ['name' => 'A'])->assertStatus(422);
        $this->putJson("/api/users/{$me->id}", ['phones' => '00201000000001'])->assertOk();
        $this->assertSame('Sara4', $me->fresh()->name);
    }

    public function test_it_requires_a_login(): void
    {
        $u = $this->makeUser('sara5');
        $this->putJson("/api/users/{$u->id}", ['name' => 'X Y'])->assertStatus(401);
    }
}
