<?php

use App\Http\Controllers\Admin\BanController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CitiesController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CountriesController;
use App\Http\Controllers\CustomPermissionsController;
use App\Http\Controllers\CustomRolesController;
use App\Http\Controllers\DeepLinkController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\NotificationMarketingController;
use App\Http\Controllers\PalservicePointsController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\PointPurchaseRequestsController;
use App\Http\Controllers\PointTransactionsController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\PointPackageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\RolesAssignmentController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\ServicePostController;
use App\Http\Controllers\SubcategoriesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleAssignmentController;
use App\Models\ServicePost;
use App\Models\Sub_categories;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::get('/', [HomePageController::class, 'index'])->name('landing');
Route::get('/policy', [PolicyController::class, 'index'])->name('policy.index');
Route::post('/facebook/data-deletion', [App\Http\Controllers\FacebookController::class, 'handleDataDeletion']);
Route::get('/facebook/deletion-status', [App\Http\Controllers\FacebookController::class, 'getDeletionStatus']);

// Test route for debugging
Route::get('/test-users', function() {
    return response()->json([
        'status' => 'success',
        'message' => 'User routes are working',
        'routes' => [
            'users.index' => route('users.index'),
            'users.ban' => route('users.ban', 1),
            'users.unban' => route('users.unban', 1),
            'users.reset_password' => route('users.reset_password', 1),
            'users.send_notification' => route('users.send_notification', 1),
            'users.impersonate' => route('users.impersonate', 1),
        ]
    ]);
})->name('test.users');


// Deep Link Routes
Route::get('api/deep-link/{route}/{id?}', [DeepLinkController::class, 'redirect'])->name('deep.link');
Route::get('/api/validate-deep-link/{route}/{id}', [DeepLinkController::class, 'validateDeepLinkResource'])
    ->middleware('auth:api');

// Authentication Routes
Auth::routes();

// Google OAuth routes for web login
Route::get('login/google', [App\Http\Controllers\Auth\LoginController::class, 'redirectToGoogle'])->name('login.google');
Route::get('login/google/callback', [App\Http\Controllers\Auth\LoginController::class, 'handleGoogleCallback']);

// API-style Google callback route (for existing Google OAuth configuration)
Route::get('api/auth/google/callback', [App\Http\Controllers\Auth\LoginController::class, 'handleGoogleCallback']);

// Test route to verify Google login setup
Route::get('test/google-login', function() {
    return response()->json([
        'status' => 'success',
        'message' => 'Google login routes are working',
        'routes' => [
            'redirect' => route('login.google'),
            'callback' => url('api/auth/google/callback'),
            'config' => [
                'client_id' => config('services.google.client_id') ? 'Set' : 'Not Set',
                'redirect_uri' => config('services.google.redirect') ? 'Set' : 'Not Set'
            ]
        ]
    ]);
})->name('test.google.login');

// Test route to simulate Google login
Route::get('test/google-simulation', function() {
    try {
        // Simulate a Google user
        $user = \App\Models\User::first();
        if ($user) {
            auth()->login($user);
            return response()->json([
                'status' => 'success',
                'message' => 'User logged in successfully',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'auth_type' => $user->auth_type
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No users found in database'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
})->name('test.google.simulation');

// Redirect regular users trying to access dashboard
Route::get('/home', function() {
    return redirect('/');
})->middleware(['auth'])->name('home.redirect');

/*
|--------------------------------------------------------------------------
| Admin & Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'admin']], function() {
    // Dashboard
    Route::get('/dashboard', [HomeController::class, 'index'])->name('admin.dashboard');
    Route::get('statistics', [App\Http\Controllers\dashboard::class, 'index'])->name('statistics.index');

    // Point Packages & Premium Features
    Route::group(['middleware' => ['auth', 'admin']], function() {
        // Point Packages Routes with explicit naming
        Route::get('point-packages', [PointPackageController::class, 'index'])->name('admin.point_packages.index');
        Route::get('point-packages/create', [PointPackageController::class, 'create'])->name('admin.point_packages.create');
        Route::post('point-packages', [PointPackageController::class, 'store'])->name('admin.point_packages.store');
        Route::get('point-packages/{pointPackage}/edit', [PointPackageController::class, 'edit'])->name('admin.point_packages.edit');
        Route::put('point-packages/{pointPackage}', [PointPackageController::class, 'update'])->name('admin.point_packages.update');
        Route::delete('point-packages/{pointPackage}', [PointPackageController::class, 'destroy'])->name('admin.point_packages.destroy');
        Route::get('point-packages/{pointPackage}', [PointPackageController::class, 'show'])->name('admin.point_packages.show');
        
        // Point Package AJAX Actions
        Route::patch('point-packages/{pointPackage}/toggle-status', [PointPackageController::class, 'toggleStatus'])->name('admin.point_packages.toggle-status');
        Route::patch('point-packages/{pointPackage}/toggle-popular', [PointPackageController::class, 'togglePopular'])->name('admin.point_packages.toggle-popular');
        Route::post('point-packages/{pointPackage}/duplicate', [PointPackageController::class, 'duplicate'])->name('admin.point_packages.duplicate');
        Route::post('point-packages/bulk-activate', [PointPackageController::class, 'bulkActivate'])->name('admin.point_packages.bulk-activate');
        Route::post('point-packages/bulk-deactivate', [PointPackageController::class, 'bulkDeactivate'])->name('admin.point_packages.bulk-deactivate');
        Route::post('point-packages/set-popular', [PointPackageController::class, 'setPopular'])->name('admin.point_packages.set-popular');
        Route::get('point-packages/list', [PointPackageController::class, 'list'])->name('admin.point_packages.list');
        Route::get('point-packages/stats', [PointPackageController::class, 'stats'])->name('admin.point_packages.stats');
        Route::get('point-packages/export', [PointPackageController::class, 'export'])->name('admin.point_packages.export');
        
        // Ensure DELETE route is properly registered
        Route::delete('point-packages/{pointPackage}', [PointPackageController::class, 'destroy'])->name('admin.point_packages.destroy');
        
        Route::get('premium-features', [PointPackageController::class, 'features'])->name('admin.premium-features.index');
        Route::get('premium-features/create', [PointPackageController::class, 'createFeature'])->name('admin.premium-features.create');
        Route::post('premium-features', [PointPackageController::class, 'storeFeature'])->name('admin.premium-features.store');
        Route::get('premium-features/{feature}/edit', [PointPackageController::class, 'editFeature'])->name('admin.premium-features.edit');
        Route::put('premium-features/{feature}', [PointPackageController::class, 'updateFeature'])->name('admin.premium-features.update');
        Route::delete('premium-features/{feature}', [PointPackageController::class, 'destroyFeature'])->name('admin.premium-features.destroy');
        Route::get('premium-features/{feature}', [PointPackageController::class, 'showFeature'])->name('admin.premium-features.show');
        Route::get('point-analytics', [PointPackageController::class, 'analytics'])->name('point-analytics');
        
        // Investor Dashboard Routes
        Route::get('investor-dashboard', [App\Http\Controllers\InvestorDashboardController::class, 'index'])->name('investor-dashboard');
        
        // Marketing Dashboard Routes
        Route::get('marketing-dashboard', [App\Http\Controllers\MarketingController::class, 'index'])->name('marketing-dashboard');
        Route::post('marketing/send-notification', [App\Http\Controllers\MarketingController::class, 'sendNotification'])->name('admin.marketing.send-notification');
        Route::get('marketing/export-data', [App\Http\Controllers\MarketingController::class, 'exportData'])->name('admin.marketing.export-data');
        Route::get('marketing/refresh-metrics', [App\Http\Controllers\MarketingController::class, 'refreshMetrics'])->name('admin.marketing.refresh-metrics');
        Route::get('marketing/refresh-activities', [App\Http\Controllers\MarketingController::class, 'refreshActivities'])->name('admin.marketing.refresh-activities');
        Route::get('marketing/export', [App\Http\Controllers\MarketingController::class, 'export'])->name('admin.marketing.export');
        
        // System Health Routes
        Route::get('system-health', [App\Http\Controllers\SystemHealthController::class, 'index'])->name('system-health');
    });

    // Level Management Routes
    Route::group(['middleware' => ['auth', 'admin'], 'prefix' => 'admin'], function() {
        Route::resource('levels', LevelController::class);
        Route::post('levels/update-order', [LevelController::class, 'updateOrder'])->name('admin.levels.update-order');
        Route::post('levels/{level}/toggle-active', [LevelController::class, 'toggleActive'])->name('admin.levels.toggleActive');
        Route::post('levels/bulk-activate', [LevelController::class, 'bulkActivate'])->name('admin.levels.bulk-activate');
        Route::post('levels/bulk-deactivate', [LevelController::class, 'bulkDeactivate'])->name('admin.levels.bulk-deactivate');
        Route::post('levels/{level}/duplicate', [LevelController::class, 'duplicate'])->name('admin.levels.duplicate');
        Route::get('levels/export', [LevelController::class, 'export'])->name('admin.levels.export');
    });

    // Investor Dashboard Routes
    Route::group(['middleware' => ['auth', 'investor'], 'prefix' => 'investor'], function() {
        Route::get('dashboard', [App\Http\Controllers\InvestorDashboardController::class, 'index'])->name('investor.dashboard');
        Route::get('financial-report', [App\Http\Controllers\InvestorDashboardController::class, 'financialReport'])->name('investor.financial-report');
        Route::get('business-metrics', [App\Http\Controllers\InvestorDashboardController::class, 'businessMetrics'])->name('investor.business-metrics');
    });

    /*
    |--------------------------------------------------------------------------
    | Service Posts Routes
    |--------------------------------------------------------------------------
    */
    // Service Posts Resources and Actions
    Route::resource('service_posts', ServicePostController::class);
    Route::post('service_posts/bulk-destroy', [ServicePostController::class, 'bulkDestroy'])
        ->name('service_posts.bulk-destroy');
    Route::patch('service_posts/{servicePost}/approve', [ServicePostController::class, 'approve'])
        ->name('service_posts.approve');
    Route::patch('service_posts/{servicePost}/reject', [ServicePostController::class, 'reject'])
        ->name('service_posts.reject');
    Route::patch('service_posts/{servicePost}/toggle-premium', [ServicePostController::class, 'togglePremium'])
        ->name('service_posts.toggle-premium');
    
    // Enhanced Service Posts Routes
    Route::post('service_posts/bulk-action', [ServicePostController::class, 'bulkAction'])
        ->name('service_posts.bulk-action');
    Route::get('service_posts/export', [ServicePostController::class, 'export'])
        ->name('service_posts.export');
    Route::get('service_posts/statistics', [ServicePostController::class, 'statistics'])
        ->name('service_posts.statistics');
    Route::post('service_posts/{servicePost}/duplicate', [ServicePostController::class, 'duplicate'])
        ->name('service_posts.duplicate');
    Route::patch('service_posts/{servicePost}/archive', [ServicePostController::class, 'archive'])
        ->name('service_posts.archive');
    Route::patch('service_posts/{servicePost}/feature', [ServicePostController::class, 'feature'])
        ->name('service_posts.feature');
    Route::patch('service_posts/{servicePost}/unarchive', [ServicePostController::class, 'unarchive'])
        ->name('service_posts.unarchive');
    Route::patch('service_posts/{servicePost}/unfeature', [ServicePostController::class, 'unfeature'])
        ->name('service_posts.unfeature');
    
    // Dynamic Level Management Routes
    Route::patch('service_posts/{servicePost}/update-level', [ServicePostController::class, 'updateLevel'])
        ->name('service_posts.update-level');
    Route::get('service_posts/{servicePost}/available-levels', [ServicePostController::class, 'getAvailableLevels'])
        ->name('service_posts.available-levels');
    Route::get('service_posts/point-packages', [ServicePostController::class, 'getPointPackages'])
        ->name('service_posts.point-packages');

    // User Profile Service Posts
    Route::get('service_posts/user/{user}', [ServicePostController::class, 'indexProfile'])
        ->name('user.profile');
    Route::get('service_posts/postIndex/{user}', [ServicePostController::class, 'postProfile'])
        ->name('post.profile');

    // Specialized Service Post Indexes
    Route::prefix('service-posts')->group(function() {
        Route::get('userAllServiceIndex', [ServicePostController::class, 'userIndex'])
            ->name('user_all_service.index');
        Route::get('jobsIndex', [ServicePostController::class, 'jobIndex'])
            ->name('job.index');
        Route::get('carIndex', [ServicePostController::class, 'carIndex'])
            ->name('car.index');
        Route::get('phoneIndex', [ServicePostController::class, 'phoneIndex'])
            ->name('phone.index');
        Route::get('realStatIndex', [ServicePostController::class, 'realStatIndex'])
            ->name('realStat.index');
        Route::get('generalIndex', [ServicePostController::class, 'generalIndex'])
            ->name('generals.index');
    });

    // Service Posts Category/Subcategory Filtering
    Route::get('/service-posts/{category}', [ServicePostController::class, 'getServicePosts'])
        ->name('service-posts.category');
    Route::get('/sub-categories/{category}', [ServicePostController::class, 'getSubCategories'])
        ->name('sub-categories.category');
    Route::get('service_posts/categories/{categories}/sub_categories/{sub_categories}',
        [ServicePostController::class, 'servicePostCategorySubCategory'])
        ->name('servicePostCategorySubCategory');
    Route::get('/get-subcategories/{category}', [ServicePostController::class, 'getSubCategories'])
        ->name('get.subcategories');
    Route::get('/fetch-subcategories', [ServicePostController::class, 'fetchSubcategories'])
        ->name('fetchSubcategories');

    // User-specific Category Views
    Route::prefix('user-categories')->group(function() {
        Route::get('ServiceCategory/{category}', [ServicePostController::class, 'userJobIndex'])
            ->name('user_job.index');
        Route::get('userCarIndex/{category}', [ServicePostController::class, 'userCarIndex'])
            ->name('user_car.index');
        Route::get('userPhoneIndex/{category}', [ServicePostController::class, 'userPhoneIndex'])
            ->name('user_phone.index');
        Route::get('userRealStatIndex/{category}', [ServicePostController::class, 'userRealStatIndex'])
            ->name('user_realStat.index');
        Route::get('userGeneralIndex/{category}', [ServicePostController::class, 'userGeneralIndex'])
            ->name('user_general.index');
        Route::get('user_favorites/{user}', [ServicePostController::class, 'favoritesIndex'])
            ->name('user_favorites.index');
    });

    /*
    |--------------------------------------------------------------------------
    | Categories & Subcategories Routes
    |--------------------------------------------------------------------------
    */
    // Categories Resource
    Route::resource('categories', CategoriesController::class);
    Route::put('categories/{category}/toggle-suspend', [CategoriesController::class, 'toggleSuspend'])
        ->name('categories.toggle-suspend');
    Route::get('MainComponent', [CategoriesController::class, 'UserFrontIndex'])
        ->name('UserFrontIndex');

    // Subcategories Resource
    Route::resource('subcategories', SubcategoriesController::class);
    Route::put('subcategories/{subcategory}/toggle-suspend', [SubcategoriesController::class, 'toggleSuspend'])
        ->name('subcategories.toggle-suspend');
    Route::get('indexSubCategory', [SubcategoriesController::class, 'indexSubCategory'])
        ->name('indexSubCategory.index');

    // Subcategories API
    Route::get('/sub-categories/{categoryId}', function($categoryId) {
        $subcategories = Sub_categories::where('categories_id', $categoryId)
            ->where('isSuspended', false)
            ->withCount('servicePosts')
            ->get();

        return response()->json([
            'subcategories' => $subcategories
        ]);
    })->name('sub-categories');

    /*
    |--------------------------------------------------------------------------
    | Location & Geography Routes
    |--------------------------------------------------------------------------
    */
    // Countries
    Route::resource('countries', CountriesController::class);
    Route::get('countries-selected/{country}', [CountriesController::class, 'CountriesSelected'])
        ->name('countries.selected');
    Route::get('country-list', [CountriesController::class, 'countryList'])
        ->name('countries.list');

    // Cities
    Route::resource('cities', CitiesController::class);
    Route::get('/form-cities/{countryId}', function($countryId) {
        $cities = App\Models\cities::where('country_id', $countryId)->get();
        return response()->json($cities);
    });
    Route::get('/get-cities/{countryId}', function($countryId) {
        $cities = App\Models\cities::where('country_id', $countryId)->get();
        return response()->json($cities);
    });
    Route::get('/get-cities-for-form/{countryId}', [ServicePostController::class, 'getCitiesForForm']);

    /*
    |--------------------------------------------------------------------------
    | User Management Routes
    |--------------------------------------------------------------------------
    */
    // Users Resource
    Route::resource('users', UserController::class);
    Route::get('users/data', [UserController::class, 'data'])->name('users.data');
    Route::post('users/{id}/update-status', [UserController::class, 'updateStatus'])
        ->name('users.update.status');
    Route::post('users/{user}/reset-password', [App\Http\Controllers\UserController::class, 'resetPassword'])->name('users.reset_password');
    Route::post('users/{user}/send-notification', [App\Http\Controllers\UserController::class, 'sendNotification'])->name('users.send_notification');
    Route::post('users/{user}/impersonate', [App\Http\Controllers\UserController::class, 'impersonate'])->name('users.impersonate');
    Route::post('users/stop-impersonation', [App\Http\Controllers\UserController::class, 'stopImpersonation'])->name('users.stop_impersonation');
    Route::get('users/{user}/login-history', [App\Http\Controllers\UserController::class, 'loginHistory'])->name('users.login_history');
    
    // Balance management routes
    Route::post('users/{user}/add-points', [App\Http\Controllers\UserController::class, 'addPoints'])->name('users.add_points');
    Route::post('users/{user}/deduct-points', [App\Http\Controllers\UserController::class, 'deductPoints'])->name('users.deduct_points');
    


    // Simple ban/unban routes
    Route::post('users/{user}/ban', [UserController::class, 'ban'])->name('users.ban');
    Route::post('users/{user}/unban', [UserController::class, 'unban'])->name('users.unban');

    // AJAX Ban/Unban action for users
    Route::post('/users/{user}/toggle-ban', [BanController::class, 'toggleBan'])
        ->name('users.toggle-ban');

    /*
    |--------------------------------------------------------------------------
    | User Interactions Routes
    |--------------------------------------------------------------------------
    */
    // Comments, Favorites, Reports
    Route::resource('comments', CommentController::class);
    Route::resource('favorites', FavoriteController::class);
    Route::post('{reported}/{reportedId}/reports', [ReportController::class, 'store'])
        ->name('reports.store');

    /*
    |--------------------------------------------------------------------------
    | Points System Routes
    |--------------------------------------------------------------------------
    */
    // Points Purchase Requests
    Route::resource('purchase_points', PointPurchaseRequestsController::class);
    Route::put('/purchase_points/{purchaseRequest}/approved', [PointPurchaseRequestsController::class, 'approved'])
        ->name('purchase_points.approved');
    Route::put('/purchase_points/{purchaseRequest}/cancel', [PointPurchaseRequestsController::class, 'cancel'])
        ->name('purchase_points.cancel');
    Route::get('/purchaseRequest/search', [PointPurchaseRequestsController::class, 'search'])
        ->name('purchase_points.search');
    Route::delete('/purchase_points/cleanup-orphaned', [PointPurchaseRequestsController::class, 'cleanupOrphanedRequests'])
        ->name('purchase_points.cleanup_orphaned')
        ->middleware(['auth', 'admin']);

    // Point Transactions
    Route::resource('point_transactions', PointTransactionsController::class);

    // Pal Service Points
    Route::prefix('palservice_points')->group(function() {
        Route::get('/', [PalservicePointsController::class, 'index'])
            ->name('palservice_points.index');
        Route::get('/{user_id}', [PalservicePointsController::class, 'create'])
            ->name('palservice_points.create');
        Route::post('/', [PalservicePointsController::class, 'store'])
            ->name('palservice_points.store');
        Route::get('/{palservice_point}', [PalservicePointsController::class, 'show'])
            ->name('palservice_points.show');
        Route::get('/{palservice_point}/edit', [PalservicePointsController::class, 'edit'])
            ->name('palservice_points.edit');
        Route::put('/{palservice_point}', [PalservicePointsController::class, 'update'])
            ->name('palservice_points.update');
        Route::delete('/{palservice_point}', [PalservicePointsController::class, 'destroy'])
            ->name('palservice_points.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Roles & Permissions Routes
    |--------------------------------------------------------------------------
    */
    // Roles & Permissions
    Route::resource('roles', CustomRolesController::class);
    Route::resource('permissions', CustomPermissionsController::class);
    Route::post('roles/{role}/clone', [CustomRolesController::class, 'clone'])
        ->name('roles.clone');
    Route::post('permissions/generate', [CustomPermissionsController::class, 'generateForModule'])
        ->name('permissions.generate');
    Route::post('/role-assignments/default-permissions',
        [UserRoleAssignmentController::class, 'getDefaultPermissions'])
        ->name('role-assignments.default-permissions');
    // Role Assignments
    Route::prefix('role-assignments')->group(function() {
        Route::get('/', [UserRoleAssignmentController::class, 'index'])
            ->name('role-assignments.index');
        Route::get('/{id}/edit', [UserRoleAssignmentController::class, 'edit'])
            ->name('role-assignments.edit');
        Route::put('/{id}', [UserRoleAssignmentController::class, 'update'])
            ->name('role-assignments.update');
        Route::get('/role/{roleId}', [UserRoleAssignmentController::class, 'usersWithRole'])
            ->name('role-assignments.users-with-role');
    });

    // Laratrust Panel
    Route::get('/app', function () {
        return view('vendor/laratrust/panel/layout');
    });
    Route::get('user-analytics', [App\Http\Controllers\AnalyticsController::class, 'userAnalytics'])->name('analytics.user_analytics');
    Route::get('point-analytics', [App\Http\Controllers\AnalyticsController::class, 'pointAnalytics'])->name('analytics.point_analytics');
    Route::get('database-management', [App\Http\Controllers\ManagementController::class, 'databaseManagement'])->name('management.database_management');
    Route::get('backup-restore', [App\Http\Controllers\ManagementController::class, 'backupRestore'])->name('management.backup_restore');

    // Business Operations & Planning
    Route::get('investor-relations', [App\Http\Controllers\BusinessController::class, 'investorRelations'])->name('business.investor_relations');
    Route::get('investment-tracking', [App\Http\Controllers\BusinessController::class, 'investmentTracking'])->name('business.investment_tracking');
    Route::get('strategic-planning', [App\Http\Controllers\BusinessController::class, 'strategicPlanning'])->name('business.strategic_planning');
    Route::get('monthly-budget-planning', [App\Http\Controllers\BusinessController::class, 'monthlyBudgetPlanning'])->name('business.monthly_budget_planning');
    Route::get('expense-approvals', [App\Http\Controllers\BusinessController::class, 'expenseApprovals'])->name('business.expense_approvals');
    Route::get('budget-limits', [App\Http\Controllers\BusinessController::class, 'budgetLimits'])->name('business.budget_limits');

    // System Management
    Route::get('system-logs', [App\Http\Controllers\SystemController::class, 'systemLogs'])->name('system.logs');
    Route::get('api-management', [App\Http\Controllers\SystemController::class, 'apiManagement'])->name('system.api_management');
});

/*
|--------------------------------------------------------------------------
| Admin Ban Management Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->group(function () {
    // Ban Management
    Route::get('users/banned', [BanController::class, 'index'])
        ->name('admin.users.banned');
    Route::get('users/{userId}/ban', [BanController::class, 'banForm'])
        ->name('admin.users.ban.form');
    Route::post('users/{userId}/ban', [BanController::class, 'banUser'])
        ->name('admin.users.ban');
    Route::post('users/{userId}/unban', [BanController::class, 'unbanUser'])
        ->name('admin.users.unban');

    // Banned Devices
    Route::get('devices/banned', [BanController::class, 'devices'])
        ->name('devices.banned');
    Route::get('devices/ban', [BanController::class, 'banDeviceForm'])
        ->name('devices.ban.form');

    // Ban History
    Route::get('bans/history', [BanController::class, 'history'])
        ->name('bans.history');

    // Reports Management
    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');
    Route::get('/reports/{type}/{id}', [ReportController::class, 'showDetails'])
        ->name('reports.details');
    Route::get('/reports-statistics', [ReportController::class, 'statistics'])
        ->name('reports.statistics');
    Route::post('/reports/handle/{type}/{id}', [ReportController::class, 'handleReported'])
        ->name('reports.handle-reported');
    Route::delete('/reports/{id}', [ReportController::class, 'destroy'])
        ->name('reports.destroy');
    
    // Report actions
    Route::post('/reports/ban-user/{user}', [ReportController::class, 'banUser'])->name('reports.ban-user');
    Route::post('/reports/unban-user/{user}', [ReportController::class, 'unbanUser'])->name('reports.unban-user');
    Route::delete('/reports/delete-post/{post}', [ReportController::class, 'deletePost'])->name('reports.delete-post');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    
    // Test route for debugging
    Route::get('/reports/test', function() {
        return response()->json(['success' => true, 'message' => 'Reports test route working']);
    })->name('reports.test');

    // Point Transactions Fix
    Route::get('/point-transactions/fix', [PointTransactionsController::class, 'fixTransactionRecords'])
        ->name('point_transactions.fix');

    // Unsuspend Routes
    Route::patch('/users/{id}/unsuspend', function($id) {
        $user = User::findOrFail($id);
        $user->isSuspended = false;
        $user->save();
        return back()->with('success', 'User has been unsuspended successfully.');
    })->name('user.unsuspend');

    Route::patch('/posts/{id}/unsuspend', function($id) {
        $post = ServicePost::findOrFail($id);
        $post->isSuspended = false;
        $post->save();
        return back()->with('success', 'Post has been unsuspended successfully.');
    })->name('post.unsuspend');
});

/*
|--------------------------------------------------------------------------
| Front-end Reporting Routes (For Authenticated Users)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Submit a report for a user
    Route::post('/report/user/{reportedId}', [ReportController::class, 'store'])
        ->name('report.user')
        ->defaults('reported', 'user');

    // Submit a report for a service post
    Route::post('/report/service_post/{reportedId}', [ReportController::class, 'store'])
        ->name('report.service_post')
        ->defaults('reported', 'service_post');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Marketing Notification routes
    Route::get('/notifications/marketing', [NotificationMarketingController::class, 'index'])->name('notifications.marketing.index');
    Route::post('/notifications/marketing/send-all', [NotificationMarketingController::class, 'sendToAll'])->name('notifications.marketing.send-all');
    Route::post('/notifications/marketing/send-specific', [NotificationMarketingController::class, 'sendToSpecific'])->name('notifications.marketing.send-specific');
    Route::get('/notifications/marketing/history', [NotificationMarketingController::class, 'history'])->name('notifications.marketing.history');

    // API routes for the notification system
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/users/search', [ApiController::class, 'searchUsers'])->name('users.search');
        Route::get('/users/filter', [ApiController::class, 'filterUsers'])->name('users.filter');
        Route::get('/roles/list', [ApiController::class, 'getRoles'])->name('roles.list');
        Route::get('/countries/list', [ApiController::class, 'getCountries'])->name('countries.list');
    });
});

// Financial Management
Route::middleware(['auth', 'admin'])->group(function() {
    Route::get('financial/revenue', [App\Http\Controllers\FinancialController::class, 'revenue'])->name('financial.revenue');
    Route::get('point-sales', [App\Http\Controllers\FinancialController::class, 'pointSales'])->name('financial.point_sales');
    Route::get('golden-post-revenue', [App\Http\Controllers\FinancialController::class, 'goldenPostRevenue'])->name('financial.golden_post_revenue');
    Route::get('payment-reports', [App\Http\Controllers\FinancialController::class, 'paymentReports'])->name('financial.payment_reports');
    Route::get('financial/expenses', [App\Http\Controllers\FinancialController::class, 'expenses'])->name('financial.expenses');
    Route::get('advertisement-costs', [App\Http\Controllers\FinancialController::class, 'advertisementCosts'])->name('financial.advertisement_costs');
    Route::get('server-hosting-costs', [App\Http\Controllers\FinancialController::class, 'serverHostingCosts'])->name('financial.server_hosting_costs');
    Route::get('monthly-profit-loss', [App\Http\Controllers\FinancialController::class, 'monthlyProfitLoss'])->name('financial.monthly_profit_loss');
    Route::get('cash-flow-projections', [App\Http\Controllers\FinancialController::class, 'cashFlowProjections'])->name('financial.cash_flow_projections');
    Route::get('income-statement', [App\Http\Controllers\FinancialController::class, 'incomeStatement'])->name('financial.income_statement');
});
