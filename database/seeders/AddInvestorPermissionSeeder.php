<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class AddInvestorPermissionSeeder extends Seeder
{
    public function run()
    {
        $admin = User::find(100100100100);
        $investorPermission = Permission::where('name', 'investor_view')->first();
        
        if ($admin && $investorPermission) {
            $admin->permissions()->attach($investorPermission->id, ['user_type' => User::class]);
            $this->command->info('Investor permission added to admin user');
        } else {
            $this->command->error('Admin user or investor permission not found');
        }
    }
} 