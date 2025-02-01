<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Photos;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            ['name' => 'create_role', 'display_name' => 'Create Role','created_at' => Carbon::now()],
            ['name' => 'view_role', 'display_name' => 'View Role','created_at' => Carbon::now()],
            ['name' => 'edit_role', 'display_name' => 'Edit Role','created_at' => Carbon::now()],
            ['name' => 'update_role', 'display_name' => 'Update Role','created_at' => Carbon::now()],
            ['name' => 'destroy_role', 'display_name' => 'Destroy Role','created_at' => Carbon::now()],
            ['name' => 'create_permission', 'display_name' => 'Create Permission','created_at' => Carbon::now()],
            ['name' => 'view_permission', 'display_name' => 'View Permission','created_at' => Carbon::now()],
            ['name' => 'edit_permission', 'display_name' => 'Edit Permission','created_at' => Carbon::now()],
            ['name' => 'update_permission', 'display_name' => 'Update Permission','created_at' => Carbon::now()],
            ['name' => 'destroy_permission', 'display_name' => 'Destroy Permission','created_at' => Carbon::now()],
            ['name' => 'create_service', 'display_name' => 'Create Service','created_at' => Carbon::now()],
            ['name' => 'show_service', 'display_name' => 'show Service','created_at' => Carbon::now()],
            ['name' => 'store_service', 'display_name' => 'Store Service','created_at' => Carbon::now()],
            ['name' => 'view_service', 'display_name' => 'View Service','created_at' => Carbon::now()],
            ['name' => 'view_all_service', 'display_name' => 'View All Service','created_at' => Carbon::now()],
            ['name' => 'edit_service', 'display_name' => 'Edit Service','created_at' => Carbon::now()],
            ['name' => 'update_service', 'display_name' => 'Update Service','created_at' => Carbon::now()],
            ['name' => 'destroy_service', 'display_name' => 'Destroy Service','created_at' => Carbon::now()],
            ['name' => 'user_update_profile', 'display_name' => 'update profile','created_at' => Carbon::now()],
            ['name' => 'user_profile_show', 'display_name' => 'show profile','created_at' => Carbon::now()],
            ['name' => 'user_index', 'display_name' => 'View User','created_at' => Carbon::now()],
            ['name' => 'user_view', 'display_name' => 'View User','created_at' => Carbon::now()],
            ['name' => 'user_edit', 'display_name' => 'Edit User','created_at' => Carbon::now()],
            ['name' => 'user_update', 'display_name' => 'Update User','created_at' => Carbon::now()],
            ['name' => 'user_destroy', 'display_name' => 'Destroy User','created_at' => Carbon::now()],
            ['name' => 'make_favorite', 'display_name' => 'Do Favorite','created_at' => Carbon::now()],
            ['name' => 'make_report', 'display_name' => 'make Report','created_at' => Carbon::now()],
            ['name' => 'report_index', 'display_name' => 'report index','created_at' => Carbon::now()],
            ['name' => 'make_purchase', 'display_name' => 'make Purchase','created_at' => Carbon::now()],
            ['name' => 'confirm_purchase', 'display_name' => 'confirm purchase','created_at' => Carbon::now()],
            ['name' => 'purchase_index', 'display_name' => 'purchase index','created_at' => Carbon::now()],
            ['name' => 'purchase_view', 'display_name' => 'purchase view','created_at' => Carbon::now()],
            ['name' => 'purchase_store', 'display_name' => 'purchase store','created_at' => Carbon::now()],
            ['name' => 'make_transaction', 'display_name' => 'make transaction','created_at' => Carbon::now()],
            ['name' => 'view_statistics', 'display_name' => 'view statistics','created_at' => Carbon::now()],
            ['name' => 'view_siteSetting', 'display_name' => 'view site Setting','created_at' => Carbon::now()],
            ['name' => 'view_siteMap', 'display_name' => 'view site map','created_at' => Carbon::now()],
            ['name' => 'point_transactions.index', 'display_name' => 'view point transactions','created_at' => Carbon::now()],
            ['name' => 'point_transactions.show', 'display_name' => 'show point transactions','created_at' => Carbon::now()],
            ['name' => 'point_index', 'display_name' => 'view point index','created_at' => Carbon::now()],
            ['name' => 'point_view', 'display_name' => 'view point','created_at' => Carbon::now()],
            ['name' => 'point_store', 'display_name' => 'store point','created_at' => Carbon::now()],
            ['name' => 'point_edit', 'display_name' => 'edit point','created_at' => Carbon::now()],
            ['name' => 'point_transfer', 'display_name' => 'transfer point','created_at' => Carbon::now()],
            ['name' => 'grant_points', 'display_name' => 'grant point','created_at' => Carbon::now()],
            ['name' => 'add_news', 'display_name' => 'add news','created_at' => Carbon::now()],
        ];


        DB::table('permissions')->insert($permissions);

        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'admin',
            'description' => 'This role has full access to the application'
        ]);

        $moderatorRole = Role::create([
            'name' => 'moderator',
            'display_name' => 'Moderator',
            'description' => 'This role can manage content and users'
        ]);

        $userRole = Role::create([
            'name' => 'user',
            'display_name' => 'User',
            'description' => 'This role can read and edit their own data'
        ]);

        $adminPermissions = Permission::whereIn('name', [
            'create_role',
            'view_role',
            'edit_role',
            'update_role',
            'destroy_role',
            'create_permission',
            'view_permission',
            'edit_permission',
            'update_permission',
            'destroy_permission',
            'create_service',
            'show_service',
            'store_service',
            'view_service',
            'view_all_service',
            'edit_service',
            'update_service',
            'destroy_service',
            'user_update_profile',
            'user_profile_show',
            'user_index',
            'user_view',
            'user_edit',
            'user_update',
            'user_destroy',
            'make_favorite',
            'make_report',
            'report_index',
            'make_purchase',
            'confirm_purchase',
            'purchase_index',
            'purchase_view',
            'purchase_store',
            'make_transaction',
            'view_statistics',
            'view_siteSetting',
            'view_siteMap',
            'point_transactions.index',
            'point_transactions.show',
            'point_index',
            'point_view',
            'point_store',
            'point_edit',
            'point_transfer',
            'grant_points',
            'add_news',
        ])->get();

        $moderatorPermissions = Permission::whereIn('name', [
            'create_service',
            'show_service',
            'store_service',
            'view_service',
            'view_all_service',
            'edit_service',
            'update_service',
            'destroy_service',
            'user_index',
            'view_user',
            'update_profile',
            'show_profile',
            'make_favorite',
            'make_report',
            'purchase_view',
            'purchase_store',
            'make_transaction',
            'point_transfer',
            'point_transactions.index',
            'point_transactions.show',
            'point_index',
            'point_view',
            'point_store',
            'point_edit',
            'point_transfer',
        ])->get();

        $userPermissions = Permission::whereIn('name', [
            'update_profile',
            'show_profile',
            'create_service',
            'show_service',
            'store_service',
            'view_service',
            'view_all_service',
            'edit_service',
            'update_service',
            'destroy_service',
            'make_favorite',
            'make_report',
            'make_purchase',
            'purchase_view',
            'purchase_store',
        ])->get();
        $adminRole->syncPermissions($adminPermissions);
        $moderatorRole->syncPermissions($moderatorPermissions);
        $userRole->syncPermissions($userPermissions);
        $adminUser = User::create( [
            'id' => 100100100100,
            'user_name' => 'kol.eljra7',
            'name' => 'Nidal Abd',
            'gender' => 'ذكر',
            'country_id' => '1',
            'city_id' => '1',
            'date_of_birth' => fake()->dateTime(),
            'location_latitudes' => 31.317908,
            'location_longitudes' => 34.345558,
            'phones' => '00970598826056',
            'WatsNumber' => '00970598826056',
            'email' => 'kol.eljra7.90@gmail.com',
            'email_verified_at' => now(),
            'password' =>  bcrypt('nedal135'),
            'is_active' => 'active',
            'remember_token' => Str::random(10),
        ])->attachRole($adminRole)->syncPermissions($adminPermissions);
        $photo = new Photos([
            'src' => fake()->randomElement(['photos/avatar1.png', 'photos/avatar2.png', 'photos/avatar3.png', 'photos/avatar4.png', 'photos/avatar5.png']),
        ]);
        $adminUser->photos()->save($photo);
        $ModeratorUser = User::create( [
            'id' => 100100100101,
            'user_name' => 'Nidal abd',
            'name' => 'Nidal Abd',
            'gender' => 'ذكر',
            'country_id' => '1',
            'city_id' => '1',
            'date_of_birth' => fake()->dateTime(),
            'location_latitudes' => 31.317908,
            'location_longitudes' => 34.345558,
            'phones' => '00972598826056',
            'WatsNumber' => '00972598826056',
            'email' => 'kol.eljra7.90@hotmail.com',
            'email_verified_at' => now(),
            'password' =>  bcrypt('nedal135'),
            'is_active' => 'active',
            'remember_token' => Str::random(10),
        ])->attachRole($moderatorRole)->syncPermissions($moderatorPermissions);
        $photo = new Photos([
            'src' => fake()->randomElement(['photos/avatar1.png', 'photos/avatar2.png', 'photos/avatar3.png', 'photos/avatar4.png', 'photos/avatar5.png']),
        ]);
        $ModeratorUser->photos()->save($photo);
    }
}
