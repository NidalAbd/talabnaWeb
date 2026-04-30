<?php

use App\Http\Controllers\Admin\AiImageController;
use App\Http\Controllers\Admin\AiPostController;
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
use App\Http\Controllers\ImageController;
use App\Http\Controllers\UserRoleAssignmentController;
use App\Http\Controllers\BadgeTypeController;
use App\Http\Controllers\Admin\CommandMonitorController;
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

// Contact form (public, no auth required)
Route::post('/api/contact', [\App\Http\Controllers\Api\ContactController::class, 'submit'])->middleware('throttle:5,1');

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
Route::get('/sitemap-locations.xml', [SitemapController::class, 'locations']);
Route::get('/sitemap-location-categories.xml', [SitemapController::class, 'locationCategories']);
Route::get('/sitemap-listings-{page}.xml', [SitemapController::class, 'listings'])->where('page', '[0-9]+');
Route::get('/sitemap-users-{page}.xml', [SitemapController::class, 'users'])->where('page', '[0-9]+');
Route::get('/robots.txt', [SitemapController::class, 'robots']);

// Image optimization proxy (serves WebP for web frontend, originals untouched for Flutter)
Route::get('/img/{path}', [ImageController::class, 'serve'])->where('path', '.*');

// Legacy Policy Route (redirect to SPA)
Route::get('/policy', function() {
    return view('spa');
})->name('policy.index');

// Test routes for comment replies (REMOVE AFTER TESTING)
Route::get('/test-comment-replies/{postId}', function($postId) {
    $comments = \App\Models\Comment::with(['user:id,user_name,name,email', 'user.photos', 'replies.user:id,user_name,name,email', 'replies.user.photos'])
        ->where('service_post_id', $postId)
        ->whereNull('parent_id')
        ->withCount('replies')
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();

    return response()->json([
        'success' => true,
        'total_comments' => $comments->count(),
        'comments' => $comments->map(function($comment) {
            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'user' => $comment->user ? $comment->user->user_name : 'Unknown',
                'replies_count' => $comment->replies_count,
                'replies' => $comment->replies->map(function($reply) {
                    return [
                        'id' => $reply->id,
                        'content' => $reply->content,
                        'user' => $reply->user ? $reply->user->user_name : 'Unknown',
                        'parent_id' => $reply->parent_id,
                    ];
                }),
            ];
        }),
    ]);
});

// Test adding a reply (REMOVE AFTER TESTING)
Route::get('/test-add-reply/{parentCommentId}', function($parentCommentId) {
    $parentComment = \App\Models\Comment::find($parentCommentId);
    if (!$parentComment) {
        return response()->json(['error' => 'Parent comment not found'], 404);
    }

    $reply = \App\Models\Comment::create([
        'user_id' => $parentComment->user_id,
        'service_post_id' => $parentComment->service_post_id,
        'parent_id' => $parentCommentId,
        'content' => 'Test reply at ' . now()->toDateTimeString(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Reply created successfully',
        'reply' => [
            'id' => $reply->id,
            'content' => $reply->content,
            'parent_id' => $reply->parent_id,
        ],
        'view_replies_url' => url('/test-comment-replies/' . $parentComment->service_post_id),
    ]);
});

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

/*
|--------------------------------------------------------------------------
| Services/Location Routes (SEO Pages)
|--------------------------------------------------------------------------
*/
// Services by country
Route::get('/services/{countryId}/{countrySlug?}', function() {
    return view('spa');
})->where('countryId', '[0-9]+')->name('services.country');

// Services by country and city
Route::get('/services/{countryId}/{countrySlug}/{cityId}/{citySlug?}', function() {
    return view('spa');
})->where(['countryId' => '[0-9]+', 'cityId' => '[0-9]+'])->name('services.city');

// Services by country, city, and category
Route::get('/services/{countryId}/{countrySlug}/{cityId}/{citySlug}/{categoryId}/{categorySlug?}', function() {
    return view('spa');
})->where(['countryId' => '[0-9]+', 'cityId' => '[0-9]+', 'categoryId' => '[0-9]+'])->name('services.category');

// Slug-based SEO URLs (no numeric IDs). The sitemap submits these — without
// these routes Laravel returns 404 before SeoController can handle them.
// Constraint: first segment must NOT be purely numeric (numeric routes above
// catch those). Pattern matches up to 5 path segments.
Route::get('/services/{country}', function() {
    return view('spa');
})->where('country', '(?!\d+$)[A-Za-z0-9%\-]+')->name('services.slug.country');

Route::get('/services/{country}/{city}', function() {
    return view('spa');
})->where(['country' => '(?!\d+$)[A-Za-z0-9%\-]+', 'city' => '(?!\d+$)[A-Za-z0-9%\-]+'])->name('services.slug.city');

Route::get('/services/{country}/{city}/{category}', function() {
    return view('spa');
})->where(['country' => '(?!\d+$)[A-Za-z0-9%\-]+'])->name('services.slug.category');

Route::get('/services/{country}/{city}/{category}/{subcategory}', function() {
    return view('spa');
})->where(['country' => '(?!\d+$)[A-Za-z0-9%\-]+'])->name('services.slug.subcategory');

Route::get('/services/{country}/{city}/{category}/{subcategory}/{post}', function() {
    return view('spa');
})->where([
    'country' => '(?!\d+$)[A-Za-z0-9%\-]+',
    'post' => '[A-Za-z0-9%\-]+-\d+',
])->name('services.slug.post');

// Deep Link Routes
Route::get('api/deep-link/{route}/{id?}', [DeepLinkController::class, 'redirect'])->name('deep.link');
Route::get('/api/validate-deep-link/{route}/{id}', [DeepLinkController::class, 'validateDeepLinkResource'])
    ->middleware('auth:api');

// Authentication Routes — wrapped in the 'noindex' alias (see Kernel.php
// $routeMiddleware) so /login, /register, /password/* respond with
// X-Robots-Tag: noindex, nofollow regardless of the underlying view.
Route::middleware('noindex')->group(function () {
    Auth::routes();
});

// API route to get current authenticated user (for SPA)
Route::get('/api/user', function () {
    if (Auth::check()) {
        $user = Auth::user()->load(['roles', 'photos']);
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'user_name' => $user->user_name,
                'email' => $user->email,
                'avatar' => $user->photos && $user->photos->count() > 0
                    ? ($user->photos->first()->is_external
                        ? $user->photos->first()->src
                        : '/' . ltrim($user->photos->first()->src, '/'))
                    : null,
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => $role->display_name,
                    ];
                }),
            ]
        ]);
    }
    return response()->json(['user' => null], 401);
})->middleware('web');

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

    /*
    |--------------------------------------------------------------------------
    | AI Image Generation Routes
    |--------------------------------------------------------------------------
    */
    Route::post('ai-image/generate-category/{id}', [AiImageController::class, 'generateCategory'])
        ->name('ai-image.generate-category');
    Route::post('ai-image/generate-subcategory/{id}', [AiImageController::class, 'generateSubcategory'])
        ->name('ai-image.generate-subcategory');
    Route::post('ai-image/generate-all-categories', [AiImageController::class, 'generateAllCategories'])
        ->name('ai-image.generate-all-categories');
    Route::post('ai-image/generate-all-subcategories', [AiImageController::class, 'generateAllSubcategories'])
        ->name('ai-image.generate-all-subcategories');
    Route::get('ai-image/status', [AiImageController::class, 'status'])
        ->name('ai-image.status');
    Route::get('ai-image/gallery/{type}/{id}', [AiImageController::class, 'galleryImages'])
        ->name('ai-image.gallery');

    /*
    |--------------------------------------------------------------------------
    | AI Post Generation Routes
    |--------------------------------------------------------------------------
    */
    Route::post('ai-posts/generate', [AiPostController::class, 'generate'])
        ->name('ai-posts.generate');
    Route::get('ai-posts/status', [AiPostController::class, 'status'])
        ->name('ai-posts.status');

    /*
    |--------------------------------------------------------------------------
    | AI User Seed Routes
    |--------------------------------------------------------------------------
    */
    Route::post('api/admin/ai/seed', [AiPostController::class, 'seed'])
        ->name('ai-seed.generate');
    Route::get('api/admin/ai/seed-status', [AiPostController::class, 'seedStatus'])
        ->name('ai-seed.status');

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

    // Point Purchase Requests - Vue SPA
    Route::get('/point-purchase-requests', [HomeController::class, 'index'])
        ->name('point_purchase_requests.index');

    // Point Transactions - Vue SPA
    Route::get('/point-transactions', [HomeController::class, 'index'])
        ->name('point_transactions.index');

    // Country Pricing - Vue SPA
    Route::get('/country-pricing', [HomeController::class, 'index'])
        ->name('country_pricing.index');

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

    // SEO Management - Vue SPA
    Route::get('/seo', [HomeController::class, 'index'])
        ->name('seo.index');

    // Localization - Vue SPA
    Route::get('/languages', [HomeController::class, 'index'])
        ->name('languages.index');
    Route::get('/translations', [HomeController::class, 'index'])
        ->name('translations.index');

    // Command Monitor - Vue SPA
    Route::get('/command-monitor', [HomeController::class, 'index'])
        ->name('command_monitor.index');

    // AI Content - Vue SPA
    Route::get('/ai-content', [HomeController::class, 'index'])
        ->name('ai_content.index');

    // Banned Users/Devices without /admin prefix
    Route::get('/banned-users', [HomeController::class, 'index'])
        ->name('banned_users.index');
    Route::get('/banned-devices', [HomeController::class, 'index'])
        ->name('banned_devices.index');

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

        // Users API (for Vue.js admin dashboard)
        Route::prefix('admin')->group(function () {
            // Dashboard API (separate from mobile API)
            Route::get('/dashboard', [DashboardApiController::class, 'index'])->name('api.admin.dashboard');
            // Users
            Route::get('/users/stats', [\App\Http\Controllers\Admin\UsersApiController::class, 'getStats'])->name('api.admin.users.stats');
            Route::get('/users/roles', [\App\Http\Controllers\Admin\UsersApiController::class, 'getRoles'])->name('api.admin.users.roles');
            Route::get('/users', [\App\Http\Controllers\Admin\UsersApiController::class, 'index'])->name('api.admin.users.index');
            Route::get('/users/{id}', [\App\Http\Controllers\Admin\UsersApiController::class, 'show'])->name('api.admin.users.show');
            Route::put('/users/{id}', [\App\Http\Controllers\Admin\UsersApiController::class, 'update'])->name('api.admin.users.update');
            Route::post('/users/{id}/toggle-ban', [\App\Http\Controllers\Admin\UsersApiController::class, 'toggleBan'])->name('api.admin.users.toggle-ban');
            Route::post('/users/{id}/adjust-points', [\App\Http\Controllers\Admin\UsersApiController::class, 'adjustPoints'])->name('api.admin.users.adjust-points');
            Route::delete('/users/{id}', [\App\Http\Controllers\Admin\UsersApiController::class, 'destroy'])->name('api.admin.users.destroy');

            // Roles
            Route::get('/roles/stats', [\App\Http\Controllers\Admin\RolesApiController::class, 'getStats'])->name('api.admin.roles.stats');
            Route::get('/roles/permissions', [\App\Http\Controllers\Admin\RolesApiController::class, 'getPermissions'])->name('api.admin.roles.permissions');
            Route::get('/roles', [\App\Http\Controllers\Admin\RolesApiController::class, 'index'])->name('api.admin.roles.index');
            Route::post('/roles', [\App\Http\Controllers\Admin\RolesApiController::class, 'store'])->name('api.admin.roles.store');
            Route::get('/roles/{id}', [\App\Http\Controllers\Admin\RolesApiController::class, 'show'])->name('api.admin.roles.show');
            Route::put('/roles/{id}', [\App\Http\Controllers\Admin\RolesApiController::class, 'update'])->name('api.admin.roles.update');
            Route::get('/roles/{id}/users', [\App\Http\Controllers\Admin\RolesApiController::class, 'getUsersWithRole'])->name('api.admin.roles.users');
            Route::delete('/roles/{id}', [\App\Http\Controllers\Admin\RolesApiController::class, 'destroy'])->name('api.admin.roles.destroy');

            // Permissions
            Route::get('/permissions/stats', [\App\Http\Controllers\Admin\PermissionsApiController::class, 'getStats'])->name('api.admin.permissions.stats');
            Route::get('/permissions/categories', [\App\Http\Controllers\Admin\PermissionsApiController::class, 'getCategories'])->name('api.admin.permissions.categories');
            Route::get('/permissions', [\App\Http\Controllers\Admin\PermissionsApiController::class, 'index'])->name('api.admin.permissions.index');
            Route::post('/permissions', [\App\Http\Controllers\Admin\PermissionsApiController::class, 'store'])->name('api.admin.permissions.store');
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
            Route::get('/countries/{id}/cities', [\App\Http\Controllers\Admin\CountriesApiController::class, 'getCities'])->name('api.admin.countries.cities');
            Route::post('/countries/{id}/cities', [\App\Http\Controllers\Admin\CitiesApiController::class, 'storeForCountry'])->name('api.admin.countries.cities.store');
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
            Route::get('/badge-types-stats', [\App\Http\Controllers\Admin\BadgeTypesApiController::class, 'getStats'])->name('api.admin.badge-types.stats');
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
            Route::get('/service-posts/{id}', [\App\Http\Controllers\Admin\ServicePostsApiController::class, 'show'])->name('api.admin.service-posts.show');
            Route::put('/service-posts/{id}', [\App\Http\Controllers\Admin\ServicePostsApiController::class, 'update'])->name('api.admin.service-posts.update');
            Route::post('/service-posts/{id}/change-badge', [\App\Http\Controllers\Admin\ServicePostsApiController::class, 'changeBadge'])->name('api.admin.service-posts.change-badge');
            Route::delete('/service-posts/{id}', [\App\Http\Controllers\Admin\ServicePostsApiController::class, 'destroy'])->name('api.admin.service-posts.destroy');
            Route::get('/service-posts/{id}/viewers', [\App\Http\Controllers\Admin\ServicePostsApiController::class, 'viewers'])->name('api.admin.service-posts.viewers');
            Route::get('/service-posts/{id}/likers', [\App\Http\Controllers\Admin\ServicePostsApiController::class, 'likers'])->name('api.admin.service-posts.likers');

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
            Route::post('/point-packages/{id}', [\App\Http\Controllers\Admin\PointPackagesApiController::class, 'update'])->name('api.admin.point-packages.update');
            Route::delete('/point-packages/{id}', [\App\Http\Controllers\Admin\PointPackagesApiController::class, 'destroy'])->name('api.admin.point-packages.destroy');
            Route::post('/point-packages/{id}/toggle-status', [\App\Http\Controllers\Admin\PointPackagesApiController::class, 'toggleStatus'])->name('api.admin.point-packages.toggle-status');
            Route::post('/point-packages/{id}/toggle-popular', [\App\Http\Controllers\Admin\PointPackagesApiController::class, 'togglePopular'])->name('api.admin.point-packages.toggle-popular');
            Route::post('/point-packages/{id}/duplicate', [\App\Http\Controllers\Admin\PointPackagesApiController::class, 'duplicate'])->name('api.admin.point-packages.duplicate');
            Route::post('/point-packages/bulk-activate', [\App\Http\Controllers\Admin\PointPackagesApiController::class, 'bulkActivate'])->name('api.admin.point-packages.bulk-activate');
            Route::post('/point-packages/bulk-deactivate', [\App\Http\Controllers\Admin\PointPackagesApiController::class, 'bulkDeactivate'])->name('api.admin.point-packages.bulk-deactivate');

            // Country Pricing
            Route::get('/country-pricing', [\App\Http\Controllers\Admin\CountryPricingApiController::class, 'index'])->name('api.admin.country-pricing.index');
            Route::post('/country-pricing/apply-base-price', [\App\Http\Controllers\Admin\CountryPricingApiController::class, 'applyBasePrice'])->name('api.admin.country-pricing.apply-base-price');
            Route::post('/country-pricing/{id}', [\App\Http\Controllers\Admin\CountryPricingApiController::class, 'update'])->name('api.admin.country-pricing.update');
            Route::post('/country-pricing/{id}/toggle-transfers', [\App\Http\Controllers\Admin\CountryPricingApiController::class, 'toggleTransfers'])->name('api.admin.country-pricing.toggle-transfers');
            Route::post('/country-pricing/{id}/exchange-rate', [\App\Http\Controllers\Admin\CountryPricingApiController::class, 'updateExchangeRate'])->name('api.admin.country-pricing.exchange-rate');
            Route::post('/country-pricing/{id}/custom-price', [\App\Http\Controllers\Admin\CountryPricingApiController::class, 'setCustomPrice'])->name('api.admin.country-pricing.custom-price');

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

            // Analytics (unified endpoint)
            Route::get('/analytics', [AnalyticsApiController::class, 'getAnalytics'])->name('api.admin.analytics');

            // Statistics
            Route::get('/statistics', [StatisticsApiController::class, 'index'])->name('api.admin.statistics.index');

            // Role Assignments
            Route::get('/role-assignments/stats', [RoleAssignmentsApiController::class, 'getStats'])->name('api.admin.role-assignments.stats');
            Route::get('/role-assignments/roles', [RoleAssignmentsApiController::class, 'getRoles'])->name('api.admin.role-assignments.roles');
            Route::get('/role-assignments/permissions', [RoleAssignmentsApiController::class, 'getPermissions'])->name('api.admin.role-assignments.permissions');
            Route::post('/role-assignments/fix-without-roles', [RoleAssignmentsApiController::class, 'fixUsersWithoutRoles'])->name('api.admin.role-assignments.fix-without-roles');
            Route::get('/role-assignments', [RoleAssignmentsApiController::class, 'index'])->name('api.admin.role-assignments.index');
            Route::get('/role-assignments/{id}', [RoleAssignmentsApiController::class, 'show'])->name('api.admin.role-assignments.show');
            Route::put('/role-assignments/{id}', [RoleAssignmentsApiController::class, 'update'])->name('api.admin.role-assignments.update');

            // Pal Service Points Overview
            Route::get('/palservice-points/stats', [PalServicePointsApiController::class, 'getStats'])->name('api.admin.palservice-points.stats');
            Route::get('/palservice-points/top-users', [PalServicePointsApiController::class, 'getTopUsers'])->name('api.admin.palservice-points.top-users');
            Route::get('/palservice-points', [PalServicePointsApiController::class, 'index'])->name('api.admin.palservice-points.index');

            // SEO Management
            Route::prefix('seo')->group(function () {
                Route::get('/dashboard', [\App\Http\Controllers\Admin\SeoApiController::class, 'dashboard'])->name('api.admin.seo.dashboard');
                Route::get('/stats', [\App\Http\Controllers\Admin\SeoApiController::class, 'getStats'])->name('api.admin.seo.stats');
                Route::get('/config', [\App\Http\Controllers\Admin\SeoApiController::class, 'getConfig'])->name('api.admin.seo.config');
                Route::get('/keywords', [\App\Http\Controllers\Admin\SeoApiController::class, 'keywords'])->name('api.admin.seo.keywords');
                Route::get('/keywords/trending', [\App\Http\Controllers\Admin\SeoApiController::class, 'getTrendingKeywords'])->name('api.admin.seo.keywords.trending');
                Route::get('/pages', [\App\Http\Controllers\Admin\SeoApiController::class, 'pages'])->name('api.admin.seo.pages');
                Route::get('/pages/details', [\App\Http\Controllers\Admin\SeoApiController::class, 'pageDetails'])->name('api.admin.seo.pages.details');
                Route::get('/pages/declining', [\App\Http\Controllers\Admin\SeoApiController::class, 'getDecliningPages'])->name('api.admin.seo.pages.declining');
                Route::get('/daily-stats', [\App\Http\Controllers\Admin\SeoApiController::class, 'dailyStats'])->name('api.admin.seo.daily-stats');
                Route::get('/performance-by-type', [\App\Http\Controllers\Admin\SeoApiController::class, 'performanceByType'])->name('api.admin.seo.performance-by-type');
                Route::get('/countries', [\App\Http\Controllers\Admin\SeoApiController::class, 'getCountries'])->name('api.admin.seo.countries');
                Route::get('/issues', [\App\Http\Controllers\Admin\SeoApiController::class, 'issues'])->name('api.admin.seo.issues');
                Route::post('/issues/{id}', [\App\Http\Controllers\Admin\SeoApiController::class, 'updateIssue'])->name('api.admin.seo.issues.update');
                Route::get('/logs', [\App\Http\Controllers\Admin\SeoApiController::class, 'logs'])->name('api.admin.seo.logs');
                Route::post('/sync', [\App\Http\Controllers\Admin\SeoApiController::class, 'sync'])->name('api.admin.seo.sync');
                Route::get('/test-connection', [\App\Http\Controllers\Admin\SeoApiController::class, 'testConnection'])->name('api.admin.seo.test-connection');
                Route::post('/cleanup', [\App\Http\Controllers\Admin\SeoApiController::class, 'cleanup'])->name('api.admin.seo.cleanup');
            });

            // Languages Management (using API controller for web admin)
            Route::prefix('languages')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\LanguageManagementController::class, 'index'])->name('api.admin.languages.index');
                Route::post('/', [\App\Http\Controllers\Admin\LanguageManagementController::class, 'store'])->name('api.admin.languages.store');
                Route::get('/stats', [\App\Http\Controllers\Admin\LanguageManagementController::class, 'stats'])->name('api.admin.languages.stats');
                Route::post('/reorder', [\App\Http\Controllers\Admin\LanguageManagementController::class, 'reorder'])->name('api.admin.languages.reorder');
                Route::get('/{id}', [\App\Http\Controllers\Admin\LanguageManagementController::class, 'show'])->name('api.admin.languages.show');
                Route::put('/{id}', [\App\Http\Controllers\Admin\LanguageManagementController::class, 'update'])->name('api.admin.languages.update');
                Route::delete('/{id}', [\App\Http\Controllers\Admin\LanguageManagementController::class, 'destroy'])->name('api.admin.languages.destroy');
                Route::post('/{id}/toggle', [\App\Http\Controllers\Admin\LanguageManagementController::class, 'toggle'])->name('api.admin.languages.toggle');
                Route::post('/{id}/set-default', [\App\Http\Controllers\Admin\LanguageManagementController::class, 'setDefault'])->name('api.admin.languages.set-default');
                Route::get('/{code}/content-translations', [\App\Http\Controllers\Admin\ContentTranslationController::class, 'index'])->name('api.admin.languages.content-translations');
                Route::post('/{code}/content-translations', [\App\Http\Controllers\Admin\ContentTranslationController::class, 'store'])->name('api.admin.languages.content-translations.store');
            });

            // Command Monitor
            Route::get('/command-monitor', [CommandMonitorController::class, 'index'])->name('api.admin.command-monitor.index');
            Route::get('/command-monitor/progress', [CommandMonitorController::class, 'progress'])->name('api.admin.command-monitor.progress');

            // Auto-translate (AI translation)
            Route::post('/auto-translate/{locale}', [\App\Http\Controllers\Api\AutoTranslateController::class, 'start'])->name('api.admin.auto-translate.start');
            Route::get('/auto-translate/{locale}/progress', [\App\Http\Controllers\Api\AutoTranslateController::class, 'progress'])->name('api.admin.auto-translate.progress');
            Route::post('/auto-translate/{locale}/stop', [\App\Http\Controllers\Api\AutoTranslateController::class, 'stop'])->name('api.admin.auto-translate.stop');

            // Translations Management (using API controller for web admin)
            Route::prefix('translations')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\TranslationManagementController::class, 'index'])->name('api.admin.translations.index');
                Route::post('/', [\App\Http\Controllers\Admin\TranslationManagementController::class, 'store'])->name('api.admin.translations.store');
                Route::get('/stats', [\App\Http\Controllers\Admin\TranslationManagementController::class, 'stats'])->name('api.admin.translations.stats');
                Route::get('/groups', [\App\Http\Controllers\Admin\TranslationManagementController::class, 'groups'])->name('api.admin.translations.groups');
                Route::get('/export', [\App\Http\Controllers\Admin\TranslationManagementController::class, 'export'])->name('api.admin.translations.export');
                Route::post('/import', [\App\Http\Controllers\Admin\TranslationManagementController::class, 'import'])->name('api.admin.translations.import');
                Route::post('/bulk', [\App\Http\Controllers\Admin\TranslationManagementController::class, 'bulk'])->name('api.admin.translations.bulk');
                Route::get('/missing/{locale}', [\App\Http\Controllers\Admin\TranslationManagementController::class, 'missing'])->name('api.admin.translations.missing');
                Route::put('/{id}', [\App\Http\Controllers\Admin\TranslationManagementController::class, 'update'])->name('api.admin.translations.update');
                Route::delete('/{id}', [\App\Http\Controllers\Admin\TranslationManagementController::class, 'destroy'])->name('api.admin.translations.destroy');
            });
        });
    });
});

// Referral link — stores code in cookie, redirects to Play Store or home
Route::get('/r/{code}', function (string $code) {
    $valid = \App\Models\User::where('referral_code', $code)->exists();
    if ($valid) {
        // Store in cookie for 30 days — picked up by Google auth on first sign-in
        cookie()->queue('referral_code', $code, 60 * 24 * 30);
    }
    // If on mobile, redirect to Play Store. Otherwise, redirect to home.
    $userAgent = request()->header('User-Agent', '');
    $isMobile = preg_match('/Android|iPhone|iPad/i', $userAgent);
    if ($isMobile) {
        return redirect('https://play.google.com/store/apps/details?id=com.talabna.talabna&referrer=' . urlencode('ref=' . $code));
    }
    return redirect('/')->cookie('referral_code', $code, 60 * 24 * 30);
})->name('referral.link');

// Google One Tap web login
Route::post('/auth/google/one-tap', [\App\Http\Controllers\Api\GoogleAuthController::class, 'handleWebGoogleAuth'])->name('google.onetap');
