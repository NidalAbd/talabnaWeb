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

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [App\Http\Controllers\HomePageController::class, 'index'])->name('landing');
Route::get('/policy', [PolicyController::class, 'index'])->name('policy.index');

Auth::routes();
Route::group(['middleware' => 'auth'], function() {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('service_posts/user/{user}',[App\Http\Controllers\ServicePostController::class, 'indexProfile'])->name('user.profile');
    Route::get('service_posts/postIndex/{user}',[App\Http\Controllers\ServicePostController::class, 'postProfile'])->name('post.profile');
    Route::resource('comments', CommentController::class);

    Route::get('jobsIndex}',[App\Http\Controllers\ServicePostController::class, 'jobIndex'])->name('job.index');
    Route::get('carIndex}',[App\Http\Controllers\ServicePostController::class, 'carIndex'])->name('car.index');
    Route::get('phoneIndex}',[App\Http\Controllers\ServicePostController::class, 'phoneIndex'])->name('phone.index');
    Route::get('realStatIndex}',[App\Http\Controllers\ServicePostController::class, 'realStatIndex'])->name('realStat.index');
    Route::get('generalIndex}',[App\Http\Controllers\ServicePostController::class, 'generalIndex'])->name('generals.index');
    Route::get('user_favorites/{user}}',[App\Http\Controllers\ServicePostController::class, 'favoritesIndex'])->name('user_favorites.index');

    Route::post('inViewCount/{servicePost}', [App\Http\Controllers\ServicePostController::class, 'inViewCount'])->name('inViewCount.view');

    Route::get('/service-posts/{category}', [App\Http\Controllers\ServicePostController::class, 'getServicePosts'])->name('service-posts.category');
    Route::get('/sub-categories/{category}', [App\Http\Controllers\ServicePostController::class, 'getSubCategories'])->name('sub-categories.category');


    Route::get('userAllServiceIndex',[App\Http\Controllers\ServicePostController::class, 'userIndex'])->name('user_all_service.index');
    Route::get('ServiceCategory/{category}',[App\Http\Controllers\ServicePostController::class, 'userJobIndex'])->name('user_job.index');
    Route::get('userCarIndex/{category}',[App\Http\Controllers\ServicePostController::class, 'userCarIndex'])->name('user_car.index');
    Route::get('userPhoneIndex/{category}',[App\Http\Controllers\ServicePostController::class, 'userPhoneIndex'])->name('user_phone.index');
    Route::get('userRealStatIndex/{category}',[App\Http\Controllers\ServicePostController::class, 'userRealStatIndex'])->name('user_realStat.index');
    Route::get('userGeneralIndex/{category}',[App\Http\Controllers\ServicePostController::class, 'userGeneralIndex'])->name('user_general.index');
    Route::get('service_posts/categories/{categories}/sub_categories/{sub_categories}', [App\Http\Controllers\ServicePostController::class, 'servicePostCategorySubCategory'])
        ->name('servicePostCategorySubCategory');
    Route::get('/fetch-subcategories', [App\Http\Controllers\ServicePostController::class, 'fetchSubcategories'])->name('fetchSubcategories');

    Route::post('{reported}/{reportedId}/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('reports.index', [ReportController::class, 'index'])->name('reports.index');

    Route::resource('favorites', FavoriteController::class);
    Route::get('statistics}',[App\Http\Controllers\dashboard::class, 'index'])->name('statistics.index');
    Route::resource('service_posts',ServicePostController::class);
    Route::resource('users',UserController::class);
    Route::get('users/data', [UserController::class, 'data'])->name('users.data');
    Route::resource('roles', RolesController::class);
    Route::resource('permissions', PermissionsController::class);
    Route::resource('purchase_points', PointPurchaseRequestsController::class);
    Route::resource('subcategories', SubcategoriesController::class);
    Route::get('indexSubCategory', [SubcategoriesController::class, 'indexSubCategory'])->name('indexSubCategory.index');
    Route::resource('categories', CategoriesController::class);
    Route::get('MainComponent}',[CategoriesController::class, 'UserFrontIndex'])->name('UserFrontIndex');

    Route::put('/purchase_points/{purchaseRequest}/approved', [PointPurchaseRequestsController::class, 'approved'])->name('purchase_points.approved');
    Route::put('/purchase_points/{purchaseRequest}/cancel', [PointPurchaseRequestsController::class, 'cancel'])->name('purchase_points.cancel');
    Route::get('/purchaseRequest/search', [PointPurchaseRequestsController::class, 'search'])->name('purchase_points.search');


    Route::get('palservice_points', [PalservicePointsController::class, 'index'])->name('palservice_points.index');
    Route::get('palservice_points/{user_id}', [PalservicePointsController::class, 'create'])->name('palservice_points.create');
    Route::post('palservice_points', [PalservicePointsController::class, 'store'])->name('palservice_points.store');
    Route::get('palservice_points/{palservice_point}', [PalservicePointsController::class, 'show'])->name('palservice_points.show');
    Route::get('palservice_points/{palservice_point}/edit', [PalservicePointsController::class, 'edit'])->name('palservice_points.edit');
    Route::put('palservice_points/{palservice_point}', [PalservicePointsController::class, 'update'])->name('palservice_points.update');
    Route::delete('palservice_points/{palservice_point}', [PalservicePointsController::class, 'destroy'])->name('palservice_points.destroy');

    Route::resource('point_transactions',PointTransactionsController::class);

    Route::get('/app', function () {
        return view('vendor/laratrust/panel/layout');
    });
});
