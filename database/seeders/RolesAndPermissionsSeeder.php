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
        // Clear existing permissions and roles to avoid conflicts
        DB::table('permission_role')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();

        $permissions = [
            // Existing permissions
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
            ['name' => 'add_news', 'display_name' => 'add اخبار','created_at' => Carbon::now()],
            
            // New Financial permissions
            ['name' => 'financial_revenue', 'display_name' => 'View Revenue','created_at' => Carbon::now()],
            ['name' => 'financial_point_sales', 'display_name' => 'View Point Sales','created_at' => Carbon::now()],
            ['name' => 'financial_golden_post_revenue', 'display_name' => 'View Golden Post Revenue','created_at' => Carbon::now()],
            ['name' => 'financial_payment_reports', 'display_name' => 'View Payment Reports','created_at' => Carbon::now()],
            ['name' => 'financial_expenses', 'display_name' => 'View Expenses','created_at' => Carbon::now()],
            ['name' => 'financial_advertisement_costs', 'display_name' => 'View Advertisement Costs','created_at' => Carbon::now()],
            ['name' => 'financial_server_hosting_costs', 'display_name' => 'View Server Hosting Costs','created_at' => Carbon::now()],
            ['name' => 'financial_monthly_profit_loss', 'display_name' => 'View Monthly Profit Loss','created_at' => Carbon::now()],
            ['name' => 'financial_cash_flow_projections', 'display_name' => 'View Cash Flow Projections','created_at' => Carbon::now()],
            ['name' => 'financial_income_statement', 'display_name' => 'View Income Statement','created_at' => Carbon::now()],
            
            // Analytics permissions
            ['name' => 'analytics_user_analytics', 'display_name' => 'View User Analytics','created_at' => Carbon::now()],
            ['name' => 'analytics_point_analytics', 'display_name' => 'View Point Analytics','created_at' => Carbon::now()],
            ['name' => 'analytics_marketing_dashboard', 'display_name' => 'View Marketing Dashboard','created_at' => Carbon::now()],
            ['name' => 'analytics_notification_history', 'display_name' => 'View Notification History','created_at' => Carbon::now()],
            
            // System permissions
            ['name' => 'system_health', 'display_name' => 'View System Health','created_at' => Carbon::now()],
            ['name' => 'system_logs', 'display_name' => 'View System Logs','created_at' => Carbon::now()],
            ['name' => 'system_api_management', 'display_name' => 'Manage API','created_at' => Carbon::now()],
            
            // Management permissions
            ['name' => 'management_database', 'display_name' => 'Manage Database','created_at' => Carbon::now()],
            ['name' => 'management_backup_restore', 'display_name' => 'Backup & Restore','created_at' => Carbon::now()],
            
            // Business permissions
            ['name' => 'business_dashboard', 'display_name' => 'View Business Dashboard','created_at' => Carbon::now()],
            ['name' => 'business_investor_relations', 'display_name' => 'View Investor Relations','created_at' => Carbon::now()],
            ['name' => 'business_investment_tracking', 'display_name' => 'View Investment Tracking','created_at' => Carbon::now()],
            ['name' => 'business_strategic_planning', 'display_name' => 'View Strategic Planning','created_at' => Carbon::now()],
            ['name' => 'business_monthly_budget_planning', 'display_name' => 'View Monthly Budget Planning','created_at' => Carbon::now()],
            ['name' => 'business_expense_approvals', 'display_name' => 'View Expense Approvals','created_at' => Carbon::now()],
            ['name' => 'business_budget_limits', 'display_name' => 'View Budget Limits','created_at' => Carbon::now()],
            ['name' => 'business_revenue_analysis', 'display_name' => 'View Revenue Analysis','created_at' => Carbon::now()],
            ['name' => 'business_expense_analysis', 'display_name' => 'View Expense Analysis','created_at' => Carbon::now()],
            ['name' => 'business_profit_loss', 'display_name' => 'View Profit & Loss','created_at' => Carbon::now()],
            ['name' => 'business_growth_metrics', 'display_name' => 'View Growth Metrics','created_at' => Carbon::now()],
            
            // Accountant permissions
            ['name' => 'accountant_dashboard', 'display_name' => 'View Accountant Dashboard','created_at' => Carbon::now()],
            ['name' => 'accountant_expenses', 'display_name' => 'Manage Expenses','created_at' => Carbon::now()],
            ['name' => 'accountant_revenues', 'display_name' => 'Manage Revenues','created_at' => Carbon::now()],
            ['name' => 'accountant_budgets', 'display_name' => 'Manage Budgets','created_at' => Carbon::now()],
            ['name' => 'accountant_investments', 'display_name' => 'View Investments','created_at' => Carbon::now()],
            ['name' => 'accountant_approve_expenses', 'display_name' => 'Approve Expenses','created_at' => Carbon::now()],
            ['name' => 'accountant_financial_reports', 'display_name' => 'Generate Financial Reports','created_at' => Carbon::now()],
            ['name' => 'accountant_tax_reports', 'display_name' => 'Generate Tax Reports','created_at' => Carbon::now()],
            ['name' => 'accountant_audit_trail', 'display_name' => 'View Audit Trail','created_at' => Carbon::now()],
            
            // Categories and Subcategories
            ['name' => 'categories_index', 'display_name' => 'View Categories','created_at' => Carbon::now()],
            ['name' => 'categories_create', 'display_name' => 'Create Category','created_at' => Carbon::now()],
            ['name' => 'categories_edit', 'display_name' => 'Edit Category','created_at' => Carbon::now()],
            ['name' => 'categories_destroy', 'display_name' => 'Delete Category','created_at' => Carbon::now()],
            ['name' => 'sub_categories_index', 'display_name' => 'View Sub Categories','created_at' => Carbon::now()],
            ['name' => 'sub_categories_create', 'display_name' => 'Create Sub Category','created_at' => Carbon::now()],
            ['name' => 'sub_categories_edit', 'display_name' => 'Edit Sub Category','created_at' => Carbon::now()],
            ['name' => 'sub_categories_destroy', 'display_name' => 'Delete Sub Category','created_at' => Carbon::now()],
            
            // Locations
            ['name' => 'countries_index', 'display_name' => 'View Countries','created_at' => Carbon::now()],
            ['name' => 'countries_create', 'display_name' => 'Create Country','created_at' => Carbon::now()],
            ['name' => 'countries_edit', 'display_name' => 'Edit Country','created_at' => Carbon::now()],
            ['name' => 'countries_destroy', 'display_name' => 'Delete Country','created_at' => Carbon::now()],
            ['name' => 'cities_index', 'display_name' => 'View Cities','created_at' => Carbon::now()],
            ['name' => 'cities_create', 'display_name' => 'Create City','created_at' => Carbon::now()],
            ['name' => 'cities_edit', 'display_name' => 'Edit City','created_at' => Carbon::now()],
            ['name' => 'cities_destroy', 'display_name' => 'Delete City','created_at' => Carbon::now()],
            
            // Reports
            ['name' => 'reports_index', 'display_name' => 'View Reports','created_at' => Carbon::now()],
            ['name' => 'reports_show', 'display_name' => 'Show Report','created_at' => Carbon::now()],
            ['name' => 'reports_destroy', 'display_name' => 'Delete Report','created_at' => Carbon::now()],
            ['name' => 'reports_manage', 'display_name' => 'Manage Reports','created_at' => Carbon::now()],
            
            // Devices
            ['name' => 'devices_index', 'display_name' => 'View Devices','created_at' => Carbon::now()],
            ['name' => 'devices_banned', 'display_name' => 'View Banned Devices','created_at' => Carbon::now()],
            ['name' => 'devices_ban', 'display_name' => 'Ban Device','created_at' => Carbon::now()],
            ['name' => 'devices_unban', 'display_name' => 'Unban Device','created_at' => Carbon::now()],
            
            // Notifications
            ['name' => 'notifications_index', 'display_name' => 'View Notifications','created_at' => Carbon::now()],
            ['name' => 'notifications_create', 'display_name' => 'Create Notification','created_at' => Carbon::now()],
            ['name' => 'notifications_send', 'display_name' => 'Send Notification','created_at' => Carbon::now()],
            ['name' => 'notifications_marketing', 'display_name' => 'Marketing Notifications','created_at' => Carbon::now()],
            
            // Point Packages
            ['name' => 'point_packages_index', 'display_name' => 'View Point Packages','created_at' => Carbon::now()],
            ['name' => 'point_packages_create', 'display_name' => 'Create Point Package','created_at' => Carbon::now()],
            ['name' => 'point_packages_edit', 'display_name' => 'Edit Point Package','created_at' => Carbon::now()],
            ['name' => 'point_packages_destroy', 'display_name' => 'Delete Point Package','created_at' => Carbon::now()],
            
            // Premium Features
            ['name' => 'premium_features_index', 'display_name' => 'View Premium Features','created_at' => Carbon::now()],
            ['name' => 'premium_features_create', 'display_name' => 'Create Premium Feature','created_at' => Carbon::now()],
            ['name' => 'premium_features_edit', 'display_name' => 'Edit Premium Feature','created_at' => Carbon::now()],
            ['name' => 'premium_features_destroy', 'display_name' => 'Delete Premium Feature','created_at' => Carbon::now()],
            
            // Levels
            ['name' => 'levels_index', 'display_name' => 'View Levels','created_at' => Carbon::now()],
            ['name' => 'levels_create', 'display_name' => 'Create Level','created_at' => Carbon::now()],
            ['name' => 'levels_edit', 'display_name' => 'Edit Level','created_at' => Carbon::now()],
            ['name' => 'levels_destroy', 'display_name' => 'Delete Level','created_at' => Carbon::now()],
            
            // Service Posts
            ['name' => 'service_posts_index', 'display_name' => 'View Service Posts','created_at' => Carbon::now()],
            ['name' => 'service_posts_create', 'display_name' => 'Create Service Post','created_at' => Carbon::now()],
            ['name' => 'service_posts_edit', 'display_name' => 'Edit Service Post','created_at' => Carbon::now()],
            ['name' => 'service_posts_destroy', 'display_name' => 'Delete Service Post','created_at' => Carbon::now()],
            ['name' => 'service_posts_approve', 'display_name' => 'Approve Service Post','created_at' => Carbon::now()],
            ['name' => 'service_posts_reject', 'display_name' => 'Reject Service Post','created_at' => Carbon::now()],
            
            // Purchase Requests
            ['name' => 'purchase_requests_index', 'display_name' => 'View Purchase Requests','created_at' => Carbon::now()],
            ['name' => 'purchase_requests_approve', 'display_name' => 'Approve Purchase Request','created_at' => Carbon::now()],
            ['name' => 'purchase_requests_reject', 'display_name' => 'Reject Purchase Request','created_at' => Carbon::now()],
            
            // Point Transactions
            ['name' => 'point_transactions_index', 'display_name' => 'View Point Transactions','created_at' => Carbon::now()],
            ['name' => 'point_transactions_create', 'display_name' => 'Create Point Transaction','created_at' => Carbon::now()],
            
            // Role Assignments
            ['name' => 'role_assignments_index', 'display_name' => 'View Role Assignments','created_at' => Carbon::now()],
            ['name' => 'role_assignments_edit', 'display_name' => 'Edit Role Assignment','created_at' => Carbon::now()],
            
            // Users additional permissions
            ['name' => 'users_banned', 'display_name' => 'View Banned Users','created_at' => Carbon::now()],
            ['name' => 'users_ban', 'display_name' => 'Ban User','created_at' => Carbon::now()],
            ['name' => 'users_unban', 'display_name' => 'Unban User','created_at' => Carbon::now()],
            ['name' => 'users_export', 'display_name' => 'Export Users','created_at' => Carbon::now()],
            ['name' => 'users_import', 'display_name' => 'Import Users','created_at' => Carbon::now()],
            
            // Comments
            ['name' => 'comments_index', 'display_name' => 'View Comments','created_at' => Carbon::now()],
            ['name' => 'comments_create', 'display_name' => 'Create Comment','created_at' => Carbon::now()],
            ['name' => 'comments_edit', 'display_name' => 'Edit Comment','created_at' => Carbon::now()],
            ['name' => 'comments_destroy', 'display_name' => 'Delete Comment','created_at' => Carbon::now()],
            
            // Favorites
            ['name' => 'favorites_index', 'display_name' => 'View Favorites','created_at' => Carbon::now()],
            ['name' => 'favorites_create', 'display_name' => 'Create Favorite','created_at' => Carbon::now()],
            ['name' => 'favorites_destroy', 'display_name' => 'Delete Favorite','created_at' => Carbon::now()],
        ];

        DB::table('permissions')->insert($permissions);

        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Full system administrator with all permissions'
        ]);

        $managerRole = Role::create([
            'name' => 'manager',
            'display_name' => 'Manager',
            'description' => 'Department manager with limited administrative access'
        ]);

        $moderatorRole = Role::create([
            'name' => 'moderator',
            'display_name' => 'Moderator',
            'description' => 'Content moderator with user and content management permissions'
        ]);

        $accountantRole = Role::create([
            'name' => 'accountant',
            'display_name' => 'Accountant',
            'description' => 'Financial accountant with business and financial management permissions'
        ]);

        $userRole = Role::create([
            'name' => 'user',
            'display_name' => 'User',
            'description' => 'Regular user with basic permissions'
        ]);

        // Get all permissions for admin
        $adminPermissions = Permission::all();

        // Manager permissions - limited administrative access
        $managerPermissions = Permission::whereIn('name', [
            // User management
            'user_index', 'user_view', 'user_edit', 'user_update',
            'users_banned', 'users_ban', 'users_unban',
            
            // Content management
            'service_posts_index', 'service_posts_edit', 'service_posts_approve', 'service_posts_reject',
            'categories_index', 'categories_edit',
            'sub_categories_index', 'sub_categories_edit',
            
            // Reports and moderation
            'reports_index', 'reports_show', 'reports_manage',
            'comments_index', 'comments_edit', 'comments_destroy',
            
            // Points and transactions
            'point_transactions_index', 'point_transactions_create',
            'purchase_requests_index', 'purchase_requests_approve', 'purchase_requests_reject',
            'point_transfer', 'grant_points',
            
            // Analytics (limited)
            'analytics_user_analytics', 'analytics_point_analytics',
            
            // Basic system access
            'view_statistics',
            
            // Notifications
            'notifications_index', 'notifications_create', 'notifications_send',
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
            'user_view',
            'user_edit',
            'user_update',
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
            'service_posts_index',
            'service_posts_edit',
            'service_posts_approve',
            'service_posts_reject',
            'purchase_requests_index',
            'purchase_requests_approve',
            'purchase_requests_reject',
            'reports_index',
            'reports_show',
            'categories_index',
            'sub_categories_index',
            'countries_index',
            'cities_index',
        ])->get();

        $accountantPermissions = Permission::whereIn('name', [
            // Business dashboard access
            'business_dashboard', 'business_revenue_analysis', 'business_expense_analysis',
            'business_profit_loss', 'business_growth_metrics',
            
            // Accountant specific permissions
            'accountant_dashboard', 'accountant_expenses', 'accountant_revenues',
            'accountant_budgets', 'accountant_investments', 'accountant_approve_expenses',
            'accountant_financial_reports', 'accountant_tax_reports', 'accountant_audit_trail',
            
            // Financial permissions
            'financial_revenue', 'financial_point_sales', 'financial_expenses',
            'financial_monthly_profit_loss', 'financial_cash_flow_projections',
            'financial_income_statement',
            
            // Analytics for financial insights
            'analytics_user_analytics', 'analytics_point_analytics',
            
            // Basic system access
            'view_statistics',
            
            // Reports
            'reports_index', 'reports_show',
        ])->get();

        $userPermissions = Permission::whereIn('name', [
            'user_update_profile',
            'user_profile_show',
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
        
        // Create or update admin user
        $adminUser = User::updateOrCreate(
            ['id' => 100100100100],
            [
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
            ]
        );
        
        // Assign roles and permissions using pivot with user_type
        $adminUser->roles()->sync([$adminRole->id => ['user_type' => User::class]]);
        $adminUser->permissions()->sync($adminPermissions->pluck('id')->mapWithKeys(function($id) {
            return [$id => ['user_type' => User::class]];
        })->toArray());
        if (!$adminUser->photos()->exists()) {
            $photo = new Photos([
                'src' => fake()->randomElement(['storage/photos/avatar1.png', 'storage/photos/avatar2.png', 'storage/photos/avatar3.png', 'storage/photos/avatar4.png', 'storage/photos/avatar5.png']),
            ]);
            $adminUser->photos()->save($photo);
        }
        
        // Create or update manager user
        $managerUser = User::updateOrCreate(
            ['id' => 100100100102],
            [
                'user_name' => 'manager',
                'name' => 'System Manager',
                'gender' => 'ذكر',
                'country_id' => '1',
                'city_id' => '1',
                'date_of_birth' => fake()->dateTime(),
                'location_latitudes' => 31.317908,
                'location_longitudes' => 34.345558,
                'phones' => '00970598826057',
                'WatsNumber' => '00970598826057',
                'email' => 'manager@talabna.com',
                'email_verified_at' => now(),
                'password' =>  bcrypt('manager123'),
                'is_active' => 'active',
                'remember_token' => Str::random(10),
            ]
        );
        
        $managerUser->roles()->sync([$managerRole->id => ['user_type' => User::class]]);
        $managerUser->permissions()->sync($managerPermissions->pluck('id')->mapWithKeys(function($id) {
            return [$id => ['user_type' => User::class]];
        })->toArray());
        if (!$managerUser->photos()->exists()) {
            $photo = new Photos([
                'src' => fake()->randomElement(['storage/photos/avatar1.png', 'storage/photos/avatar2.png', 'storage/photos/avatar3.png', 'storage/photos/avatar4.png', 'storage/photos/avatar5.png']),
            ]);
            $managerUser->photos()->save($photo);
        }
        
        // Create or update moderator user
        $ModeratorUser = User::updateOrCreate(
            ['id' => 100100100101],
            [
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
            ]
        );
        
        $ModeratorUser->roles()->sync([$moderatorRole->id => ['user_type' => User::class]]);
        $ModeratorUser->permissions()->sync($moderatorPermissions->pluck('id')->mapWithKeys(function($id) {
            return [$id => ['user_type' => User::class]];
        })->toArray());
        if (!$ModeratorUser->photos()->exists()) {
            $photo = new Photos([
                'src' => fake()->randomElement(['storage/photos/avatar1.png', 'storage/photos/avatar2.png', 'storage/photos/avatar3.png', 'storage/photos/avatar4.png', 'storage/photos/avatar5.png']),
            ]);
            $ModeratorUser->photos()->save($photo);
        }
        
        // Create or update accountant user
        $accountantUser = User::updateOrCreate(
            ['id' => 100100100103],
            [
                'user_name' => 'accountant',
                'name' => 'Financial Accountant',
                'gender' => 'ذكر',
                'country_id' => '1',
                'city_id' => '1',
                'date_of_birth' => fake()->dateTime(),
                'location_latitudes' => 31.317908,
                'location_longitudes' => 34.345558,
                'phones' => '00970598826058',
                'WatsNumber' => '00970598826058',
                'email' => 'accountant@talabna.com',
                'email_verified_at' => now(),
                'password' =>  bcrypt('accountant123'),
                'is_active' => 'active',
                'remember_token' => Str::random(10),
            ]
        );
        
        $accountantUser->roles()->sync([$accountantRole->id => ['user_type' => User::class]]);
        $accountantUser->permissions()->sync($accountantPermissions->pluck('id')->mapWithKeys(function($id) {
            return [$id => ['user_type' => User::class]];
        })->toArray());
        if (!$accountantUser->photos()->exists()) {
            $photo = new Photos([
                'src' => fake()->randomElement(['storage/photos/avatar1.png', 'storage/photos/avatar2.png', 'storage/photos/avatar3.png', 'storage/photos/avatar4.png', 'storage/photos/avatar5.png']),
            ]);
            $accountantUser->photos()->save($photo);
        }
    }
}
