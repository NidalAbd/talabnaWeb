<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class FixUsersWithoutRoles extends Command
{
    protected $signature = 'users:fix-roles';
    protected $description = 'Fix users: assign missing roles and sync permissions for "user" role';

    public function handle()
    {
        $role = Role::where('name', 'user')->first();

        if (!$role) {
            $this->error('Default "user" role not found in the database.');
            return 1;
        }

        $permissions = $role->permissions;
        $permissionIds = $permissions->pluck('id')->toArray();
        $userType = 'App\Models\User';

        $permSync = [];
        foreach ($permissionIds as $pid) {
            $permSync[$pid] = ['user_type' => $userType];
        }

        // Step 1: Fix users without any roles
        $usersWithoutRoles = User::whereDoesntHave('roles')->get();
        if ($usersWithoutRoles->count() > 0) {
            $this->info("Fixing {$usersWithoutRoles->count()} users without roles...");
            foreach ($usersWithoutRoles as $user) {
                $user->roles()->attach($role->id, ['user_type' => $userType]);
                $user->permissions()->sync($permSync);
            }
            $this->info("  Assigned 'user' role + {$permissions->count()} permissions.");
        } else {
            $this->info('All users have roles assigned.');
        }

        // Step 2: Fix users with 'user' role but missing permissions
        $usersWithRole = User::whereHas('roles', function ($q) use ($role) {
            $q->where('roles.id', $role->id);
        })->whereDoesntHave('permissions')->get();

        if ($usersWithRole->count() > 0) {
            $this->info("Fixing {$usersWithRole->count()} users with 'user' role but 0 permissions...");
            foreach ($usersWithRole as $user) {
                $user->permissions()->sync($permSync);
            }
            $this->info("  Synced {$permissions->count()} permissions.");
        } else {
            $this->info('All users with "user" role have permissions.');
        }

        $total = $usersWithoutRoles->count() + $usersWithRole->count();
        $this->info("Done! Fixed {$total} users total.");

        return 0;
    }
}
