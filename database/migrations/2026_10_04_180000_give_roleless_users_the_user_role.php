<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 559 accounts (imported or hand-made, e.g. the App Review demo) had no role, so every
 * permission check (view_service, ...) failed and their category feeds returned 403.
 * Give each role-less account the standard "user" role — what registration does.
 * Accounts that already have a role are untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        $roleId = DB::table('roles')->where('name', 'user')->value('id');
        if (! $roleId) {
            return;
        }
        DB::table('users')
            ->whereNotIn('id', DB::table('role_user')->select('user_id'))
            ->orderBy('id')
            ->chunkById(500, function ($users) use ($roleId) {
                DB::table('role_user')->insert($users->map(fn ($u) => [
                    'role_id' => $roleId,
                    'user_id' => $u->id,
                    'user_type' => 'App\\Models\\User',
                ])->all());
            });
    }

    public function down(): void
    {
        // Not safely reversible: these rows can't be told apart from ones added later.
    }
};
