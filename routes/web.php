<?php

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PalservicePointsController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\PointPurchaseRequestsController;
use App\Http\Controllers\PointTransactionsController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\ServicePostController;
use App\Http\Controllers\SubcategoriesController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [App\Http\Controllers\HomePageController::class, 'index'])->name('landing');
Route::get('/policy', [PolicyController::class, 'index'])->name('policy.index');
Route::get('api/deep-link/{route}/{id?}', [App\Http\Controllers\DeepLinkController::class,'redirect'])->name('deep.link');

// Authentication Routes
Auth::routes();

// Authenticated Routes Group
Route::group(['middleware' => 'auth'], function() {
    // Dashboard
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    // Service Posts Related Routes
    Route::get('service_posts/user/{user}', [ServicePostController::class, 'indexProfile'])->name('user.profile');
    Route::get('service_posts/postIndex/{user}', [ServicePostController::class, 'postProfile'])->name('post.profile');
    Route::resource('service_posts', ServicePostController::class);

    // Specialized Service Post Indexes
    Route::get('userAllServiceIndex', [ServicePostController::class, 'userIndex'])->name('user_all_service.index');
    Route::get('jobsIndex', [ServicePostController::class, 'jobIndex'])->name('job.index');
    Route::get('carIndex', [ServicePostController::class, 'carIndex'])->name('car.index');
    Route::get('phoneIndex', [ServicePostController::class, 'phoneIndex'])->name('phone.index');
    Route::get('realStatIndex', [ServicePostController::class, 'realStatIndex'])->name('realStat.index');
    Route::get('generalIndex', [ServicePostController::class, 'generalIndex'])->name('generals.index');

    // Service Posts Filtering Routes
    Route::get('/service-posts/{category}', [ServicePostController::class, 'getServicePosts'])->name('service-posts.category');
    Route::put('categories/{category}/toggle-suspend', [CategoriesController::class, 'toggleSuspend'])
        ->name('categories.toggle-suspend');
    Route::put('subcategories/{subcategory}/toggle-suspend', [SubcategoriesController::class, 'toggleSuspend'])
        ->name('subcategories.toggle-suspend');
    Route::get('/sub-categories/{category}', [ServicePostController::class, 'getSubCategories'])->name('sub-categories.category');
    Route::get('service_posts/categories/{categories}/sub_categories/{sub_categories}', [ServicePostController::class, 'servicePostCategorySubCategory'])
        ->name('servicePostCategorySubCategory');

    Route::get('/fetch-subcategories', [ServicePostController::class, 'fetchSubcategories'])->name('fetchSubcategories');

    // User-specific Routes
    Route::get('user_favorites/{user}', [ServicePostController::class, 'favoritesIndex'])->name('user_favorites.index');
    Route::get('ServiceCategory/{category}', [ServicePostController::class, 'userJobIndex'])->name('user_job.index');
    Route::get('userCarIndex/{category}', [ServicePostController::class, 'userCarIndex'])->name('user_car.index');
    Route::get('userPhoneIndex/{category}', [ServicePostController::class, 'userPhoneIndex'])->name('user_phone.index');
    Route::get('userRealStatIndex/{category}', [ServicePostController::class, 'userRealStatIndex'])->name('user_realStat.index');
    Route::get('userGeneralIndex/{category}', [ServicePostController::class, 'userGeneralIndex'])->name('user_general.index');

    // Resource Routes
    Route::resource('comments', CommentController::class);
    Route::resource('favorites', FavoriteController::class);
    Route::resource('users', UserController::class);
    Route::resource('roles', RolesController::class);
    Route::resource('permissions', PermissionsController::class);
    Route::resource('categories', CategoriesController::class);
    Route::resource('subcategories', SubcategoriesController::class);
    Route::resource('purchase_points', PointPurchaseRequestsController::class);
    Route::resource('point_transactions', PointTransactionsController::class);

    // Additional User Routes
    Route::get('users/data', [UserController::class, 'data'])->name('users.data');

    // Point Purchase Request Additional Routes
    Route::put('/purchase_points/{purchaseRequest}/approved', [PointPurchaseRequestsController::class, 'approved'])->name('purchase_points.approved');
    Route::put('/purchase_points/{purchaseRequest}/cancel', [PointPurchaseRequestsController::class, 'cancel'])->name('purchase_points.cancel');
    Route::get('/purchaseRequest/search', [PointPurchaseRequestsController::class, 'search'])->name('purchase_points.search');

    // Pal Service Points Routes
    Route::get('palservice_points', [PalservicePointsController::class, 'index'])->name('palservice_points.index');
    Route::get('palservice_points/{user_id}', [PalservicePointsController::class, 'create'])->name('palservice_points.create');
    Route::post('palservice_points', [PalservicePointsController::class, 'store'])->name('palservice_points.store');
    Route::get('palservice_points/{palservice_point}', [PalservicePointsController::class, 'show'])->name('palservice_points.show');
    Route::get('palservice_points/{palservice_point}/edit', [PalservicePointsController::class, 'edit'])->name('palservice_points.edit');
    Route::put('palservice_points/{palservice_point}', [PalservicePointsController::class, 'update'])->name('palservice_points.update');
    Route::delete('palservice_points/{palservice_point}', [PalservicePointsController::class, 'destroy'])->name('palservice_points.destroy');

    // Other Specific Routes
    Route::post('inViewCount/{servicePost}', [ServicePostController::class, 'inViewCount'])->name('inViewCount.view');
    Route::post('{reported}/{reportedId}/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('reports.index', [ReportController::class, 'index'])->name('reports.index');
    Route::get('indexSubCategory', [SubcategoriesController::class, 'indexSubCategory'])->name('indexSubCategory.index');
    Route::get('MainComponent', [CategoriesController::class, 'UserFrontIndex'])->name('UserFrontIndex');
    Route::get('statistics', [App\Http\Controllers\dashboard::class, 'index'])->name('statistics.index');

    // Laratrust Panel Layout
    Route::get('/app', function () {
        return view('vendor/laratrust/panel/layout');
    });
});
