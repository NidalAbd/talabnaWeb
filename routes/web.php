<?php

use App\Http\Controllers\Admin\BanController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\SitemapController;
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
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RolesAssignmentController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\ServicePostController;
use App\Http\Controllers\SubcategoriesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleAssignmentController;
use App\Http\Controllers\BadgeTypeController;
use App\Http\Controllers\Admin\DashboardApiController;
use App\Http\Controllers\Admin\AnalyticsApiController;
use App\Http\Controllers\Admin\StatisticsApiController;
use App\Http\Controllers\Admin\RoleAssignmentsApiController;
use App\Http\Controllers\Admin\PalServicePointsApiController;
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

// Facebook Data Deletion (keep these before SPA routes)
Route::post('/facebook/data-deletion', [App\Http\Controllers\FacebookController::class, 'handleDataDeletion']);
Route::get('/facebook/deletion-status', [App\Http\Controllers\FacebookController::class, 'getDeletionStatus']);

/*
|--------------------------------------------------------------------------
| SEO Routes - Sitemap & Robots
|--------------------------------------------------------------------------
*/
Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/sitemap-pages.xml', [SitemapController::class, 'pages']);
Route::get('/sitemap-categories.xml', [SitemapController::class, 'categories']);
Route::get('/sitemap-listings-{page}.xml', [SitemapController::class, 'listings'])->where('page', '[0-9]+');
Route::get('/robots.txt', [SitemapController::class, 'robots']);

// Legacy Policy Route (redirect to SPA)
Route::get('/policy', function() {
    return view('spa');
})->name('policy.index');

/*
|--------------------------------------------------------------------------
| Vue SPA Routes (Public)
|--------------------------------------------------------------------------
*/
Route::get('/', function() {
    return view('spa');
})->name('landing');

Route::get('/browse', function() {
    return view('spa');
})->name('browse');

Route::get('/category/{id}/{slug?}', function() {
    return view('spa');
})->name('category.show');

Route::get('/listing/{id}/{slug?}', function() {
    return view('spa');
})->name('listing.show');

Route::get('/search', function() {
    return view('spa');
})->name('search');

Route::get('/user/{id}', function() {
    return view('spa');
})->where('id', '[0-9]+')->name('user.public.profile');

Route::get('/about', function() {
    return view('spa');
})->name('about');

Route::get('/contact', function() {
    return view('spa');
})->name('contact');

Route::get('/privacy', function() {
    return view('spa');
})->name('privacy');

Route::get('/terms', function() {
    return view('spa');
})->name('terms');


// Deep Link Routes
Route::get('api/deep-link/{route}/{id?}', [DeepLinkController::class, 'redirect'])->name('deep.link');
Route::get('/api/validate-deep-link/{route}/{id}', [DeepLinkController::class, 'validateDeepLinkResource'])
    ->middleware('auth:api');

// Authentication Routes
Auth::routes();

/*
|--------------------------------------------------------------------------
| Admin & Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'admin']], function() {
    // Dashboard
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('statistics', [App\Http\Controllers\dashboard::class, 'index'])->name('statistics.index');

    /*
    |--------------------------------------------------------------------------
    | Vue SPA Routes (List Pages)
    |--------------------------------------------------------------------------
    */
    // Users - Vue SPA
    Route::get('users', [HomeController::class, 'index'])->name('users.index');

    // Categories & Subcategories - Vue SPA
    Route::get('categories', [HomeController::class, 'index'])->name('categories.index');
    Route::get('subcategories', [HomeController::class, 'index'])->name('subcategories.index');

    /*
    |--------------------------------------------------------------------------
    | Service Posts Routes
    |--------------------------------------------------------------------------
    */
    // Service Posts Index - Vue SPA
    Route::get('service_posts', [HomeController::class, 'index'])->name('service_posts.index');

    // Service Posts Resources and Actions (except index)
    Route::resource('service_posts', ServicePostController::class)->except(['index']);
    Route::post('service_posts/{servicePost}/apply-badge', [ServicePostController::class, 'applyBadge'])
        ->name('service_posts.apply_badge');
    Route::post('service_posts/{servicePost}/remove-badge', [ServicePostController::class, 'removeBadge'])
        ->name('service_posts.remove_badge');
    Route::post('service_posts/{servicePost}/calculate-refund-preview', [ServicePostController::class, 'calculateRefundPreview'])
        ->name('service_posts.calculate_refund_preview');
    Route::delete('service-posts/bulk-destroy', [ServicePostController::class, 'bulkDestroy'])
        ->name('service_posts.bulk-destroy');
    Route::post('inViewCount/{servicePost}', [ServicePostController::class, 'inViewCount'])
        ->name('inViewCount.view');

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
    // Categories Resource (index moved to Vue SPA)
    Route::resource('categories', CategoriesController::class)->except(['index']);
    Route::put('categories/{category}/toggle-suspend', [CategoriesController::class, 'toggleSuspend'])
        ->name('categories.toggle-suspend');
    Route::get('MainComponent', [CategoriesController::class, 'UserFrontIndex'])
        ->name('UserFrontIndex');

    // Subcategories Resource (index moved to Vue SPA)
    Route::resource('subcategories', SubcategoriesController::class)->except(['index']);
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
    // Countries - Vue SPA
    Route::get('countries', [HomeController::class, 'index'])->name('countries.index');
    Route::get('countries-selected/{country}', [CountriesController::class, 'CountriesSelected'])
        ->name('countries.selected');
    Route::get('country-list', [CountriesController::class, 'countryList'])
        ->name('countries.list');

    // Cities - Vue SPA
    Route::get('cities', [HomeController::class, 'index'])->name('cities.index');
    Route::get('/form-cities/{countryId}', function($countryId) {
        $cities = App\Models\cities::where('country_id', $countryId)->get();
        return response()->json($cities);
    });
    Route::get('/get-cities-for-form/{countryId}', [ServicePostController::class, 'getCitiesForForm']);

    /*
    |--------------------------------------------------------------------------
    | User Management Routes
    |--------------------------------------------------------------------------
    */
    // Users Resource (index moved to Vue SPA)
    Route::resource('users', UserController::class)->except(['index']);
    Route::get('users/data', [UserController::class, 'data'])->name('users.data');
    Route::post('users/{id}/update-status', [UserController::class, 'updateStatus'])
        ->name('users.update.status');

    // Vue.js Users Management (Test Route)
    Route::get('users-vue', function () {
        return view('users.users-vue');
    })->name('users.vue');

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
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

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

    // Pal Service Points - Old Blade routes (deprecated - use Vue at /admin/palservice-points)
    // Keeping these for backward compatibility with existing forms/links
    Route::prefix('palservice_points')->group(function() {
        Route::get('/', [PalservicePointsController::class, 'index'])
            ->name('palservice_points.blade.index');  // Renamed to avoid conflict
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
    | Badge Types Management Routes
    |--------------------------------------------------------------------------
    */
    // Badge Types - Vue SPA
    Route::get('badge-types', [HomeController::class, 'index'])->name('badge_types.index');
    Route::post('/badge_types/{badgeType}/toggle-active', [BadgeTypeController::class, 'toggleActive'])
        ->name('badge_types.toggle_active');
    Route::post('/badge_types/{badgeType}/set-default', [BadgeTypeController::class, 'setDefault'])
        ->name('badge_types.set_default');
    Route::post('/badge_types/migrate-old-badges', [BadgeTypeController::class, 'migrateOldBadges'])
        ->name('badge_types.migrate');

    /*
    |--------------------------------------------------------------------------
    | Roles & Permissions Routes
    |--------------------------------------------------------------------------
    */
    // Roles & Permissions - Vue SPA Routes
    Route::get('roles', [HomeController::class, 'index'])->name('roles.index');
    Route::get('permissions', [HomeController::class, 'index'])->name('permissions.index');

    // Keep clone route for API functionality
    Route::post('roles/{role}/clone', [CustomRolesController::class, 'clone'])
        ->name('roles.clone');

    // Vue.js Roles Management (Test Route)
    Route::get('roles-vue', function () {
        return view('admin.roles.roles-vue');
    })->name('roles.vue');
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
});

/*
|--------------------------------------------------------------------------
| Admin Ban Management Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->group(function () {
    // Ban Management - Vue SPA
    Route::get('users/banned', [HomeController::class, 'index'])
        ->name('users.banned');
    Route::get('devices/banned', [HomeController::class, 'index'])
        ->name('devices.banned');
    Route::get('bans/history', [HomeController::class, 'index'])
        ->name('bans.history');

    // Reports Management - Vue SPA
    Route::get('/reports', [HomeController::class, 'index'])
        ->name('reports.index');

    // Point Packages - Vue SPA
    Route::get('/point-packages', [HomeController::class, 'index'])
        ->name('point_packages.index');

    // Premium Features - Vue SPA
    Route::get('/premium-features', [HomeController::class, 'index'])
        ->name('premium_features.index');

    // User Levels - Vue SPA
    Route::get('/levels', [HomeController::class, 'index'])
        ->name('levels.index');

    // Point Purchase Requests - Vue SPA
    Route::get('/point-purchase-requests', [HomeController::class, 'index'])
        ->name('point_purchase_requests.index');

    // Point Transactions - Vue SPA
    Route::get('/point-transactions', [HomeController::class, 'index'])
        ->name('point_transactions.index');

    // Marketing Notifications - Vue SPA
    Route::get('/marketing-notifications', [HomeController::class, 'index'])
        ->name('marketing_notifications.index');

    // Analytics - Vue SPA
    Route::get('/analytics', [HomeController::class, 'index'])
        ->name('analytics.index');

    // Statistics - Vue SPA
    Route::get('/statistics', [HomeController::class, 'index'])
        ->name('statistics.index');

    // Role Assignments - Vue SPA
    Route::get('/role-assignments', [HomeController::class, 'index'])
        ->name('role_assignments.index');

    // Pal Service Points Overview - Vue SPA
    Route::get('/palservice-points', [HomeController::class, 'index'])
        ->name('palservice_points.index');

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

Route::middleware(['auth', 'admin'])->group(function () {
    // Marketing Notification routes
    Route::get('/notifications/marketing', [NotificationMarketingController::class, 'index'])->name('notifications.marketing.index');
    Route::post('/notifications/marketing/send-all', [NotificationMarketingController::class, 'sendToAll'])->name('notifications.marketing.send-all');
    Route::post('/notifications/marketing/send-specific', [NotificationMarketingController::class, 'sendToSpecific'])->name('notifications.marketing.send-specific');
    Route::get('/notifications/marketing/history', [NotificationMarketingController::class, 'history'])->name('notifications.marketing.history');

    // API routes for the notification system and admin dashboard
    Route::prefix('api')->group(function () {
        Route::get('/users/search', [ApiController::class, 'searchUsers'])->name('users.search');
        Route::get('/users/filter', [ApiController::class, 'filterUsers'])->name('users.filter');
        Route::get('/roles/list', [ApiController::class, 'getRoles'])->name('roles.list');
        Route::get('/countries/list', [ApiController::class, 'getCountries'])->name('countries.list');

        // Dashboard API (separate from mobile API)
        Route::get('/dashboard', [DashboardApiController::class, 'index'])->name('dashboard');

        // Users API (for Vue.js admin dashboard)
        Route::prefix('admin')->group(function () {
            // Users
            Route::get('/users/stats', [\App\Http\Controllers\Admin\UsersApiController::class, 'getStats'])->name('api.admin.users.stats');
            Route::get('/users/roles', [\App\Http\Controllers\Admin\UsersApiController::class, 'getRoles'])->name('api.admin.users.roles');
            Route::get('/users', [\App\Http\Controllers\Admin\UsersApiController::class, 'index'])->name('api.admin.users.index');
            Route::get('/users/{id}', [\App\Http\Controllers\Admin\UsersApiController::class, 'show'])->name('api.admin.users.show');
            Route::post('/users/{id}/toggle-ban', [\App\Http\Controllers\Admin\UsersApiController::class, 'toggleBan'])->name('api.admin.users.toggle-ban');
            Route::delete('/users/{id}', [\App\Http\Controllers\Admin\UsersApiController::class, 'destroy'])->name('api.admin.users.destroy');

            // Roles
            Route::get('/roles/stats', [\App\Http\Controllers\Admin\RolesApiController::class, 'getStats'])->name('api.admin.roles.stats');
            Route::get('/roles', [\App\Http\Controllers\Admin\RolesApiController::class, 'index'])->name('api.admin.roles.index');
            Route::get('/roles/{id}', [\App\Http\Controllers\Admin\RolesApiController::class, 'show'])->name('api.admin.roles.show');
            Route::get('/roles/{id}/users', [\App\Http\Controllers\Admin\RolesApiController::class, 'getUsersWithRole'])->name('api.admin.roles.users');
            Route::delete('/roles/{id}', [\App\Http\Controllers\Admin\RolesApiController::class, 'destroy'])->name('api.admin.roles.destroy');

            // Permissions
            Route::get('/permissions/stats', [\App\Http\Controllers\Admin\PermissionsApiController::class, 'getStats'])->name('api.admin.permissions.stats');
            Route::get('/permissions/categories', [\App\Http\Controllers\Admin\PermissionsApiController::class, 'getCategories'])->name('api.admin.permissions.categories');
            Route::get('/permissions', [\App\Http\Controllers\Admin\PermissionsApiController::class, 'index'])->name('api.admin.permissions.index');
            Route::post('/permissions/generate', [\App\Http\Controllers\Admin\PermissionsApiController::class, 'generate'])->name('api.admin.permissions.generate');
            Route::delete('/permissions/{id}', [\App\Http\Controllers\Admin\PermissionsApiController::class, 'destroy'])->name('api.admin.permissions.destroy');

            // Categories
            Route::get('/categories/stats', [\App\Http\Controllers\Admin\CategoriesApiController::class, 'getStats'])->name('api.admin.categories.stats');
            Route::get('/categories', [\App\Http\Controllers\Admin\CategoriesApiController::class, 'index'])->name('api.admin.categories.index');
            Route::get('/categories/{id}', [\App\Http\Controllers\Admin\CategoriesApiController::class, 'show'])->name('api.admin.categories.show');
            Route::post('/categories', [\App\Http\Controllers\Admin\CategoriesApiController::class, 'store'])->name('api.admin.categories.store');
            Route::post('/categories/{id}', [\App\Http\Controllers\Admin\CategoriesApiController::class, 'update'])->name('api.admin.categories.update');
            Route::delete('/categories/{id}', [\App\Http\Controllers\Admin\CategoriesApiController::class, 'destroy'])->name('api.admin.categories.destroy');
            Route::post('/categories/{id}/toggle-status', [\App\Http\Controllers\Admin\CategoriesApiController::class, 'toggleStatus'])->name('api.admin.categories.toggle-status');
            Route::post('/categories/{id}/toggle-featured', [\App\Http\Controllers\Admin\CategoriesApiController::class, 'toggleFeatured'])->name('api.admin.categories.toggle-featured');
            Route::post('/categories/{id}/toggle-popular', [\App\Http\Controllers\Admin\CategoriesApiController::class, 'togglePopular'])->name('api.admin.categories.toggle-popular');

            // Sub-Categories
            Route::get('/subcategories/stats', [\App\Http\Controllers\Admin\SubCategoriesApiController::class, 'getStats'])->name('api.admin.subcategories.stats');
            Route::get('/subcategories', [\App\Http\Controllers\Admin\SubCategoriesApiController::class, 'index'])->name('api.admin.subcategories.index');
            Route::get('/subcategories/{id}', [\App\Http\Controllers\Admin\SubCategoriesApiController::class, 'show'])->name('api.admin.subcategories.show');
            Route::post('/subcategories', [\App\Http\Controllers\Admin\SubCategoriesApiController::class, 'store'])->name('api.admin.subcategories.store');
            Route::post('/subcategories/{id}', [\App\Http\Controllers\Admin\SubCategoriesApiController::class, 'update'])->name('api.admin.subcategories.update');
            Route::delete('/subcategories/{id}', [\App\Http\Controllers\Admin\SubCategoriesApiController::class, 'destroy'])->name('api.admin.subcategories.destroy');
            Route::post('/subcategories/{id}/toggle-featured', [\App\Http\Controllers\Admin\SubCategoriesApiController::class, 'toggleFeatured'])->name('api.admin.subcategories.toggle-featured');
            Route::post('/subcategories/{id}/toggle-popular', [\App\Http\Controllers\Admin\SubCategoriesApiController::class, 'togglePopular'])->name('api.admin.subcategories.toggle-popular');

            // Countries
            Route::get('/countries/stats', [\App\Http\Controllers\Admin\CountriesApiController::class, 'getStats'])->name('api.admin.countries.stats');
            Route::get('/countries', [\App\Http\Controllers\Admin\CountriesApiController::class, 'index'])->name('api.admin.countries.index');
            Route::get('/countries/{id}', [\App\Http\Controllers\Admin\CountriesApiController::class, 'show'])->name('api.admin.countries.show');
            Route::post('/countries', [\App\Http\Controllers\Admin\CountriesApiController::class, 'store'])->name('api.admin.countries.store');
            Route::post('/countries/{id}', [\App\Http\Controllers\Admin\CountriesApiController::class, 'update'])->name('api.admin.countries.update');
            Route::delete('/countries/{id}', [\App\Http\Controllers\Admin\CountriesApiController::class, 'destroy'])->name('api.admin.countries.destroy');

            // Cities
            Route::get('/cities/stats', [\App\Http\Controllers\Admin\CitiesApiController::class, 'getStats'])->name('api.admin.cities.stats');
            Route::get('/cities', [\App\Http\Controllers\Admin\CitiesApiController::class, 'index'])->name('api.admin.cities.index');
            Route::get('/cities/{id}', [\App\Http\Controllers\Admin\CitiesApiController::class, 'show'])->name('api.admin.cities.show');
            Route::post('/cities', [\App\Http\Controllers\Admin\CitiesApiController::class, 'store'])->name('api.admin.cities.store');
            Route::post('/cities/{id}', [\App\Http\Controllers\Admin\CitiesApiController::class, 'update'])->name('api.admin.cities.update');
            Route::delete('/cities/{id}', [\App\Http\Controllers\Admin\CitiesApiController::class, 'destroy'])->name('api.admin.cities.destroy');

            // Badge Types
            Route::get('/badge-types/stats', [\App\Http\Controllers\Admin\BadgeTypesApiController::class, 'getStats'])->name('api.admin.badge-types.stats');
            Route::get('/badge-types', [\App\Http\Controllers\Admin\BadgeTypesApiController::class, 'index'])->name('api.admin.badge-types.index');
            Route::get('/badge-types/{id}', [\App\Http\Controllers\Admin\BadgeTypesApiController::class, 'show'])->name('api.admin.badge-types.show');
            Route::post('/badge-types', [\App\Http\Controllers\Admin\BadgeTypesApiController::class, 'store'])->name('api.admin.badge-types.store');
            Route::post('/badge-types/{id}', [\App\Http\Controllers\Admin\BadgeTypesApiController::class, 'update'])->name('api.admin.badge-types.update');
            Route::delete('/badge-types/{id}', [\App\Http\Controllers\Admin\BadgeTypesApiController::class, 'destroy'])->name('api.admin.badge-types.destroy');
            Route::post('/badge-types/{id}/toggle-status', [\App\Http\Controllers\Admin\BadgeTypesApiController::class, 'toggleStatus'])->name('api.admin.badge-types.toggle-status');
            Route::post('/badge-types/{id}/set-default', [\App\Http\Controllers\Admin\BadgeTypesApiController::class, 'setDefault'])->name('api.admin.badge-types.set-default');

            // Service Posts
            Route::get('/service-posts/stats', [\App\Http\Controllers\Admin\ServicePostsApiController::class, 'getStats'])->name('api.admin.service-posts.stats');
            Route::get('/service-posts/categories', [\App\Http\Controllers\Admin\ServicePostsApiController::class, 'getCategories'])->name('api.admin.service-posts.categories');
            Route::get('/service-posts/subcategories', [\App\Http\Controllers\Admin\ServicePostsApiController::class, 'getSubcategories'])->name('api.admin.service-posts.subcategories');
            Route::get('/service-posts', [\App\Http\Controllers\Admin\ServicePostsApiController::class, 'index'])->name('api.admin.service-posts.index');
            Route::delete('/service-posts/bulk-destroy', [\App\Http\Controllers\Admin\ServicePostsApiController::class, 'bulkDestroy'])->name('api.admin.service-posts.bulk-destroy');
            Route::delete('/service-posts/{id}', [\App\Http\Controllers\Admin\ServicePostsApiController::class, 'destroy'])->name('api.admin.service-posts.destroy');

            // Reports
            Route::get('/reports/stats', [\App\Http\Controllers\Admin\ReportsApiController::class, 'getStats'])->name('api.admin.reports.stats');
            Route::get('/reports', [\App\Http\Controllers\Admin\ReportsApiController::class, 'index'])->name('api.admin.reports.index');
            Route::get('/reports/{type}/{id}', [\App\Http\Controllers\Admin\ReportsApiController::class, 'show'])->name('api.admin.reports.show');
            Route::post('/reports/handle/{type}/{id}', [\App\Http\Controllers\Admin\ReportsApiController::class, 'handleReported'])->name('api.admin.reports.handle');
            Route::delete('/reports/{id}', [\App\Http\Controllers\Admin\ReportsApiController::class, 'destroy'])->name('api.admin.reports.destroy');

            // Bans
            Route::get('/bans/stats', [\App\Http\Controllers\Admin\BansApiController::class, 'getStats'])->name('api.admin.bans.stats');
            Route::get('/bans/users', [\App\Http\Controllers\Admin\BansApiController::class, 'getBannedUsers'])->name('api.admin.bans.users');
            Route::get('/bans/devices', [\App\Http\Controllers\Admin\BansApiController::class, 'getBannedDevices'])->name('api.admin.bans.devices');
            Route::post('/bans/users/{userId}/toggle', [\App\Http\Controllers\Admin\BansApiController::class, 'toggleUserBan'])->name('api.admin.bans.users.toggle');
            Route::post('/bans/devices/{deviceId}/unban', [\App\Http\Controllers\Admin\BansApiController::class, 'unbanDevice'])->name('api.admin.bans.devices.unban');

            // Point Packages
            Route::get('/point-packages/stats', [\App\Http\Controllers\Admin\PointPackagesApiController::class, 'getStats'])->name('api.admin.point-packages.stats');
            Route::get('/point-packages', [\App\Http\Controllers\Admin\PointPackagesApiController::class, 'index'])->name('api.admin.point-packages.index');
            Route::post('/point-packages', [\App\Http\Controllers\Admin\PointPackagesApiController::class, 'store'])->name('api.admin.point-packages.store');
            Route::put('/point-packages/{id}', [\App\Http\Controllers\Admin\PointPackagesApiController::class, 'update'])->name('api.admin.point-packages.update');
            Route::delete('/point-packages/{id}', [\App\Http\Controllers\Admin\PointPackagesApiController::class, 'destroy'])->name('api.admin.point-packages.destroy');
            Route::post('/point-packages/{id}/toggle-status', [\App\Http\Controllers\Admin\PointPackagesApiController::class, 'toggleStatus'])->name('api.admin.point-packages.toggle-status');
            Route::post('/point-packages/{id}/toggle-popular', [\App\Http\Controllers\Admin\PointPackagesApiController::class, 'togglePopular'])->name('api.admin.point-packages.toggle-popular');
            Route::post('/point-packages/{id}/duplicate', [\App\Http\Controllers\Admin\PointPackagesApiController::class, 'duplicate'])->name('api.admin.point-packages.duplicate');
            Route::post('/point-packages/bulk-activate', [\App\Http\Controllers\Admin\PointPackagesApiController::class, 'bulkActivate'])->name('api.admin.point-packages.bulk-activate');
            Route::post('/point-packages/bulk-deactivate', [\App\Http\Controllers\Admin\PointPackagesApiController::class, 'bulkDeactivate'])->name('api.admin.point-packages.bulk-deactivate');

            // Premium Features
            Route::get('/premium-features/stats', [\App\Http\Controllers\Admin\PremiumFeaturesApiController::class, 'getStats'])->name('api.admin.premium-features.stats');
            Route::get('/premium-features/types', [\App\Http\Controllers\Admin\PremiumFeaturesApiController::class, 'getTypes'])->name('api.admin.premium-features.types');
            Route::get('/premium-features', [\App\Http\Controllers\Admin\PremiumFeaturesApiController::class, 'index'])->name('api.admin.premium-features.index');
            Route::post('/premium-features', [\App\Http\Controllers\Admin\PremiumFeaturesApiController::class, 'store'])->name('api.admin.premium-features.store');
            Route::put('/premium-features/{id}', [\App\Http\Controllers\Admin\PremiumFeaturesApiController::class, 'update'])->name('api.admin.premium-features.update');
            Route::delete('/premium-features/{id}', [\App\Http\Controllers\Admin\PremiumFeaturesApiController::class, 'destroy'])->name('api.admin.premium-features.destroy');
            Route::post('/premium-features/{id}/toggle-status', [\App\Http\Controllers\Admin\PremiumFeaturesApiController::class, 'toggleStatus'])->name('api.admin.premium-features.toggle-status');

            // User Levels
            Route::get('/levels/stats', [\App\Http\Controllers\Admin\LevelsApiController::class, 'getStats'])->name('api.admin.levels.stats');
            Route::get('/levels', [\App\Http\Controllers\Admin\LevelsApiController::class, 'index'])->name('api.admin.levels.index');
            Route::post('/levels', [\App\Http\Controllers\Admin\LevelsApiController::class, 'store'])->name('api.admin.levels.store');
            Route::put('/levels/{id}', [\App\Http\Controllers\Admin\LevelsApiController::class, 'update'])->name('api.admin.levels.update');
            Route::delete('/levels/{id}', [\App\Http\Controllers\Admin\LevelsApiController::class, 'destroy'])->name('api.admin.levels.destroy');
            Route::post('/levels/{id}/toggle-status', [\App\Http\Controllers\Admin\LevelsApiController::class, 'toggleStatus'])->name('api.admin.levels.toggle-status');
            Route::post('/levels/{id}/duplicate', [\App\Http\Controllers\Admin\LevelsApiController::class, 'duplicate'])->name('api.admin.levels.duplicate');
            Route::post('/levels/update-order', [\App\Http\Controllers\Admin\LevelsApiController::class, 'updateOrder'])->name('api.admin.levels.update-order');
            Route::post('/levels/bulk-activate', [\App\Http\Controllers\Admin\LevelsApiController::class, 'bulkActivate'])->name('api.admin.levels.bulk-activate');
            Route::post('/levels/bulk-deactivate', [\App\Http\Controllers\Admin\LevelsApiController::class, 'bulkDeactivate'])->name('api.admin.levels.bulk-deactivate');

            // Point Purchase Requests
            Route::get('/point-purchase-requests/stats', [\App\Http\Controllers\Admin\PointPurchaseRequestsApiController::class, 'getStats'])->name('api.admin.point-purchase-requests.stats');
            Route::get('/point-purchase-requests', [\App\Http\Controllers\Admin\PointPurchaseRequestsApiController::class, 'index'])->name('api.admin.point-purchase-requests.index');
            Route::post('/point-purchase-requests/{id}/approve', [\App\Http\Controllers\Admin\PointPurchaseRequestsApiController::class, 'approve'])->name('api.admin.point-purchase-requests.approve');
            Route::post('/point-purchase-requests/{id}/cancel', [\App\Http\Controllers\Admin\PointPurchaseRequestsApiController::class, 'cancel'])->name('api.admin.point-purchase-requests.cancel');
            Route::delete('/point-purchase-requests/{id}', [\App\Http\Controllers\Admin\PointPurchaseRequestsApiController::class, 'destroy'])->name('api.admin.point-purchase-requests.destroy');
            Route::post('/point-purchase-requests/bulk-approve', [\App\Http\Controllers\Admin\PointPurchaseRequestsApiController::class, 'bulkApprove'])->name('api.admin.point-purchase-requests.bulk-approve');

            // Point Transactions
            Route::get('/point-transactions/stats', [\App\Http\Controllers\Admin\PointTransactionsApiController::class, 'getStats'])->name('api.admin.point-transactions.stats');
            Route::get('/point-transactions/types', [\App\Http\Controllers\Admin\PointTransactionsApiController::class, 'getTypes'])->name('api.admin.point-transactions.types');
            Route::get('/point-transactions', [\App\Http\Controllers\Admin\PointTransactionsApiController::class, 'index'])->name('api.admin.point-transactions.index');
            Route::get('/point-transactions/{id}', [\App\Http\Controllers\Admin\PointTransactionsApiController::class, 'show'])->name('api.admin.point-transactions.show');
            Route::delete('/point-transactions/{id}', [\App\Http\Controllers\Admin\PointTransactionsApiController::class, 'destroy'])->name('api.admin.point-transactions.destroy');

            // Marketing Notifications
            Route::get('/marketing-notifications/stats', [\App\Http\Controllers\Admin\MarketingNotificationsApiController::class, 'getStats'])->name('api.admin.marketing-notifications.stats');
            Route::get('/marketing-notifications/active-users', [\App\Http\Controllers\Admin\MarketingNotificationsApiController::class, 'getActiveUsers'])->name('api.admin.marketing-notifications.active-users');
            Route::get('/marketing-notifications', [\App\Http\Controllers\Admin\MarketingNotificationsApiController::class, 'index'])->name('api.admin.marketing-notifications.index');
            Route::post('/marketing-notifications/send-all', [\App\Http\Controllers\Admin\MarketingNotificationsApiController::class, 'sendToAll'])->name('api.admin.marketing-notifications.send-all');
            Route::post('/marketing-notifications/send-test', [\App\Http\Controllers\Admin\MarketingNotificationsApiController::class, 'sendTest'])->name('api.admin.marketing-notifications.send-test');
            Route::delete('/marketing-notifications/{id}', [\App\Http\Controllers\Admin\MarketingNotificationsApiController::class, 'destroy'])->name('api.admin.marketing-notifications.destroy');

            // Analytics
            Route::get('/analytics/overview', [AnalyticsApiController::class, 'getOverview'])->name('api.admin.analytics.overview');
            Route::get('/analytics/users', [AnalyticsApiController::class, 'getUserAnalytics'])->name('api.admin.analytics.users');
            Route::get('/analytics/points', [AnalyticsApiController::class, 'getPointAnalytics'])->name('api.admin.analytics.points');
            Route::get('/analytics/posts', [AnalyticsApiController::class, 'getPostAnalytics'])->name('api.admin.analytics.posts');

            // Statistics
            Route::get('/statistics', [StatisticsApiController::class, 'index'])->name('api.admin.statistics.index');

            // Role Assignments
            Route::get('/role-assignments/stats', [RoleAssignmentsApiController::class, 'getStats'])->name('api.admin.role-assignments.stats');
            Route::get('/role-assignments/roles', [RoleAssignmentsApiController::class, 'getRoles'])->name('api.admin.role-assignments.roles');
            Route::get('/role-assignments/permissions', [RoleAssignmentsApiController::class, 'getPermissions'])->name('api.admin.role-assignments.permissions');
            Route::get('/role-assignments', [RoleAssignmentsApiController::class, 'index'])->name('api.admin.role-assignments.index');
            Route::get('/role-assignments/{id}', [RoleAssignmentsApiController::class, 'show'])->name('api.admin.role-assignments.show');
            Route::put('/role-assignments/{id}', [RoleAssignmentsApiController::class, 'update'])->name('api.admin.role-assignments.update');

            // Pal Service Points Overview
            Route::get('/palservice-points/stats', [PalServicePointsApiController::class, 'getStats'])->name('api.admin.palservice-points.stats');
            Route::get('/palservice-points/top-users', [PalServicePointsApiController::class, 'getTopUsers'])->name('api.admin.palservice-points.top-users');
            Route::get('/palservice-points', [PalServicePointsApiController::class, 'index'])->name('api.admin.palservice-points.index');
        });
    });
});
