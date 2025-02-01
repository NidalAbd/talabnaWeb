<?php

use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\PalservicePointsController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\ServicePostController;

use App\Http\Controllers\Api\Sub;
use App\Http\Controllers\Api\SubcategoriesController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\CountriesController;
use App\Http\Controllers\UserController;

use App\Models\Sub_categories;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('login', [App\Http\Controllers\Api\UserController::class,'login']);
Route::post('googleSignIn', [App\Http\Controllers\Api\GoogleController::class, 'callback']);
Route::post('/facebook/login', [App\Http\Controllers\Api\UserController::class, 'FaceBookSignIn']);
Route::post('register', [App\Http\Controllers\Api\UserController::class,'register']);
Route::get('password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class,'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [App\Http\Controllers\Auth\ForgotPasswordController::class,'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class,'showResetForm'])->name('password.reset');
Route::post('password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class,'reset'])->name('password.update');


Route::middleware(['auth:api'])->group(function () {
    Route::post('logout', [App\Http\Controllers\Api\UserController::class,'logout']);
    Route::apiResource('users', App\Http\Controllers\Api\UserController::class);
    Route::put('user/{userId}/change-email', [App\Http\Controllers\Api\UserController::class, 'changeEmail']);
    Route::put('user/{userId}/change-password', [App\Http\Controllers\Api\UserController::class, 'changePassword']);
    Route::post('user/{userId}/update-profile-photo', [App\Http\Controllers\Api\UserController::class, 'updateProfilePhoto']);
    Route::get('user/profile/{user}', [App\Http\Controllers\Api\UserController::class,'UserProfile'])->name('getUserData');
    Route::get('user/check_token', [App\Http\Controllers\Api\UserController::class,'check_token'])->name('check_token');
    Route::get('user/follower/{user}', [App\Http\Controllers\Api\UserController::class,'UserFollower'])->name('getUserFollower');
    Route::get('user/following/{user}', [App\Http\Controllers\Api\UserController::class,'UserFollowing'])->name('getUserFollowing');
    Route::get('user/UserSeller', [App\Http\Controllers\Api\UserController::class,'UserSeller'])->name('UserSeller');
    Route::get('user/favorite/{user}', [App\Http\Controllers\Api\UserController::class,'UserFavorite'])->name('getUserFavorite');
    Route::get('user/point/{user}', [App\Http\Controllers\Api\UserController::class,'UserPointBalance'])->name('getUserPointBalance');
    Route::post('users/{user}/follow', [App\Http\Controllers\Api\UserController::class,'follow'])->name('users.follow');
    Route::delete('users/{user}/unfollow', [App\Http\Controllers\Api\UserController::class,'unfollow'])->name('users.unfollow');
    Route::get('users/{user}/follower', [App\Http\Controllers\Api\UserController::class,'doFollowUnFollow'])->name('users.doFollowUnFollow');
    Route::get('users/{user}/is-following', [App\Http\Controllers\Api\UserController::class, 'isFollowingUser'])->name('users.isFollowing');

    Route::post('subcategories/{subcategory}/toggle-follow', [SubcategoriesController::class, 'toggleFollowCategory'])->middleware('auth:api');
    Route::get('subcategories/{subcategory}/is-following', [SubcategoriesController::class, 'isFollowingCategory'])->name('subcategories.isFollowing');
    Route::post('search', [SearchController::class, 'search']);
    Route::resource('comments', CommentController::class);
    Route::get('commentsForPost/{postId}', [CommentController::class, 'index']);

    Route::get('/purchase-requests/user/{userId}', [App\Http\Controllers\Api\PointPurchaseRequestsController::class, 'index']);
    Route::post('/purchase-requests', [App\Http\Controllers\Api\PointPurchaseRequestsController::class, 'store']);
    Route::delete('/purchase-requests/{point_purchase_requests}', [App\Http\Controllers\Api\PointPurchaseRequestsController::class, 'destroy']);
    Route::get('talbna_points/transfer/{pointsRequested}/fromUser/{fromUser}/toUser/{toUser}', [PalservicePointsController::class, 'store'])->name('talbna_points.store');
    Route::post('/stripe/webhook', [App\Http\Controllers\Api\StripeWebhookController::class, 'handleWebhook']);

    Route::get('users/{user}/notifications', [NotificationController::class, 'index']);
    Route::get('users/{user}/CountNotifications', [NotificationController::class, 'countNotification']);
    Route::get('users/{user}/notifications/{notification}/mark_read/', [NotificationController::class, 'markAsRead']);

    Route::get('users/{user}/notifications/mark_All_read/', [NotificationController::class, 'markAllAsRead']);

    Route::get('update-service-post-badge-status', [ServicePostController::class, 'updateServicePostBadgeStatus']);

    Route::get('service_posts/users/{user}/favorite', [ServicePostController::class, 'servicePostFavorite']);
    Route::get('service_posts/user/{user}', [ServicePostController::class, 'servicePostUserId']);
    Route::get('service_posts/categories/{categories}', [ServicePostController::class, 'servicePostCategory']);
    Route::get('service_posts/reels', [ServicePostController::class, 'showFromReel']);

    Route::get('service_posts/categories/{categories}/sub_categories/{sub_categories}', [ServicePostController::class, 'servicePostCategorySubCategory']);
    Route::apiResource('service_posts',ServicePostController::class);
    Route::delete('service_posts/deletePhoto/{servicePostImageId}', [ServicePostController::class,'deletePhoto']);
    Route::post('service_posts/updatePhoto/{service_posts}', [ServicePostController::class,'updatePhoto']);

    Route::put('service_posts/ChangeCategories/{service_posts}', [ServicePostController::class, 'changeCategory']);
    Route::put('service_posts/ChangeBadge/{service_posts}', [ServicePostController::class, 'changeBadge']);
    Route::put('service_posts/incrementView/{service_posts}', [ServicePostController::class, 'viewAdd']);
    Route::post('reports/reported/{reported}/reportedId/{reportedId}/reason/{reason}', [App\Http\Controllers\Api\ReportController::class, 'store']);

    Route::apiResource('favorites', FavoriteController::class);
    Route::get('getFavourite/{service_posts}',[FavoriteController::class, 'getFavourite']);
    Route::post('doFavourite/{service_posts}',[FavoriteController::class, 'doFavourite']);

    Route::get('statistics}',[App\Http\Controllers\dashboard::class, 'index']);

    Route::apiResource('subcategories', SubcategoriesController::class);
    Route::post('subcategories/{id}', [SubcategoriesController::class,'index']);
    Route::apiResource('categories', CategoriesController::class);
    Route::get('categories_list',[CategoriesController::class, 'categoryList']);
    Route::get('categories_menu',[CategoriesController::class, 'categoryMenu']);

    Route::get('categories_list/{categories}/sub_categories/',[SubcategoriesController::class, 'CategoriesSelected']);

    Route::get('countries_list',[CountriesController::class, 'countryList']);
    Route::get('countries_list/{countries}/cities/',[CountriesController::class, 'CountriesSelected']);

});
