<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class FixUsersWithoutRoles extends Command
{
    protected $signature = 'users:fix-roles';
    protected $description = 'Assign default "user" role to all users who have no roles assigned';

    public function handle()
    {
        $role = Role::where('name', 'user')->first();

        if (!$role) {
            $this->error('Default "user" role not found in the database.');
            return 1;
        }

        $usersWithoutRoles = User::whereDoesntHave('roles')->get();
        $count = $usersWithoutRoles->count();

        if ($count === 0) {
            $this->info('All users already have roles assigned. Nothing to fix.');
            return 0;
        }

        $this->info("Found {$count} users without roles. Fixing...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $permissions = $role->permissions;
        $permissionIds = $permissions->pluck('id')->toArray();

        $fixed = 0;
        foreach ($usersWithoutRoles as $user) {
            // Attach role with user_type (required by Laratrust)
            $user->roles()->attach($role->id, ['user_type' => get_class($user)]);

            // Sync permissions with user_type
            $syncData = [];
            foreach ($permissionIds as $pid) {
                $syncData[$pid] = ['user_type' => get_class($user)];
            }
            $user->permissions()->sync($syncData);

            $fixed++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done! Assigned 'user' role and permissions to {$fixed} users.");

        return 0;
    }
}
