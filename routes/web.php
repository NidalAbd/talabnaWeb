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
    | Service Posts Routes
    |--------------------------------------------------------------------------
    */
    // Service Posts Resources and Actions
    Route::resource('service_posts', ServicePostController::class);
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
    | Badge Types Management Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('badge_types', BadgeTypeController::class);
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
    // Roles & Permissions
    Route::resource('roles', CustomRolesController::class);
    Route::resource('permissions', CustomPermissionsController::class);
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
    // Ban Management
    Route::get('users/banned', [BanController::class, 'index'])
        ->name('users.banned');
    Route::get('users/{userId}/ban', [BanController::class, 'banForm'])
        ->name('users.ban.form');
    Route::post('users/{userId}/ban', [BanController::class, 'banUser'])
        ->name('users.ban');
    Route::post('users/{userId}/unban', [BanController::class, 'unbanUser'])
        ->name('users.unban');

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
        });
    });
});
