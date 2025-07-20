<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Permission;
use App\Models\Role;

echo "Starting permission assignment...\n";

// Get the admin user
$admin = User::find(100100100100);

if (!$admin) {
    echo "Admin user not found. Creating admin user...\n";
    $admin = User::create([
        'id' => 100100100100,
        'name' => 'Admin User',
        'email' => 'kol.eljra7.90@gmail.com',
        'password' => bcrypt('nedal135'),
        'is_active' => 'active'
    ]);
    echo "Admin user created successfully.\n";
}

// Define all the permissions needed for the BUSINESS section and other features
$permissions = [
    // Business/Investor permissions
    'investor_view',
    'investor_profile.view',
    'investment_funding.index',
    'strategic_plan.view',
    'monthly_budget.index',
    'expense_approvals.index',
    'budget_limits.index',
    
    // Financial permissions
    'financial_revenue',
    'financial_point_sales',
    'financial_golden_post_revenue',
    'financial_payment_reports',
    'financial_expenses',
    'financial_advertisement_costs',
    'financial_server_hosting_costs',
    'financial_monthly_profit_loss',
    'financial_cash_flow_projections',
    'financial_income_statement',
    
    // Analytics permissions
    'analytics_user_analytics',
    'analytics_point_analytics',
    'analytics_marketing_dashboard',
    'analytics_notification_history',
    
    // System permissions
    'system_health',
    'system_logs',
    'system_api_management',
    
    // Management permissions
    'management_database',
    'management_backup_restore',
    
    // Business permissions
    'business_investor_relations',
    'business_investment_tracking',
    'business_strategic_planning',
    'business_monthly_budget_planning',
    'business_expense_approvals',
    'business_budget_limits',
    
    // Additional permissions that might be needed
    'revenue_view',
    'expense_view',
    'view_statistics',
    'view_service',
    'user_index',
    'service_posts_index',
    'service_posts_create',
    'service_posts_edit',
    'service_posts_destroy',
    'service_posts_approve',
    'service_posts_reject',
    'levels_index',
    'levels_create',
    'levels_edit',
    'levels_destroy',
    'categories_index',
    'categories_create',
    'categories_edit',
    'categories_destroy',
    'sub_categories_index',
    'sub_categories_create',
    'sub_categories_edit',
    'sub_categories_destroy',
    'countries_index',
    'countries_create',
    'countries_edit',
    'countries_destroy',
    'cities_index',
    'cities_create',
    'cities_edit',
    'cities_destroy',
    'reports_index',
    'reports_show',
    'reports_destroy',
    'reports_manage',
    'devices_index',
    'devices_banned',
    'devices_ban',
    'devices_unban',
    'notifications_index',
    'notifications_create',
    'notifications_send',
    'notifications_marketing',
    'point_packages_index',
    'point_packages_create',
    'point_packages_edit',
    'point_packages_destroy',
    'premium_features_index',
    'premium_features_create',
    'premium_features_edit',
    'premium_features_destroy',
    'purchase_requests_index',
    'purchase_requests_approve',
    'purchase_requests_reject',
    'point_transactions_index',
    'point_transactions_create',
    'role_assignments_index',
    'role_assignments_edit',
    'users_banned',
    'users_ban',
    'users_unban',
    'users_export',
    'users_import',
    'comments_index',
    'comments_create',
    'comments_edit',
    'comments_destroy',
    'favorites_index',
    'favorites_create',
    'favorites_destroy',
    'make_favorite',
    'make_report',
    'report_index',
    'make_purchase',
    'confirm_purchase',
    'purchase_index',
    'purchase_view',
    'purchase_store',
    'make_transaction',
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
    'user_view',
    'user_edit',
    'user_update',
    'user_destroy'
];

echo "Adding permissions to admin user...\n";

$addedCount = 0;
$existingCount = 0;

foreach ($permissions as $permissionName) {
    // Find or create the permission
    $permission = Permission::firstOrCreate(
        ['name' => $permissionName],
        [
            'display_name' => ucwords(str_replace('_', ' ', $permissionName)),
            'description' => 'Permission for ' . str_replace('_', ' ', $permissionName)
        ]
    );
    
    // Add permission to admin user if not already assigned
    if (!$admin->hasPermission($permissionName)) {
        $admin->attachPermission($permission);
        echo "Added permission: {$permissionName}\n";
        $addedCount++;
    } else {
        echo "Permission already exists: {$permissionName}\n";
        $existingCount++;
    }
}

// Ensure admin has admin role
$adminRole = Role::firstOrCreate(
    ['name' => 'admin'],
    [
        'display_name' => 'Administrator',
        'description' => 'Full system administrator'
    ]
);

if (!$admin->hasRole('admin')) {
    $admin->attachRole($adminRole);
    echo "Added admin role to user.\n";
}

echo "\n=== SUMMARY ===\n";
echo "Total permissions processed: " . count($permissions) . "\n";
echo "New permissions added: {$addedCount}\n";
echo "Existing permissions: {$existingCount}\n";
echo "Admin user ID: {$admin->id}\n";
echo "Admin user email: {$admin->email}\n";

// Verify permissions
$userPermissions = $admin->permissions()->pluck('name')->toArray();
echo "\nTotal permissions assigned to admin: " . count($userPermissions) . "\n";

echo "\nPermission assignment completed successfully!\n"; 