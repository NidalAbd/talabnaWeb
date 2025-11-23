<?php

use App\Http\Controllers\Admin\BanController;
use App\Http\Controllers\Api\BanCheckController;
use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\PalservicePointsController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\ServicePostController;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\SubcategoriesController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\CountriesController;
use App\Http\Controllers\PointTransactionsController;
use App\Http\Controllers\UserController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/
Route::prefix('public')->group(function () {
    // Categories & Subcategories
    Route::get('/categories', [PublicController::class, 'categories']);
    Route::get('/categories/{categoryId}/subcategories', [PublicController::class, 'subcategories']);

    // Listings
    Route::get('/listings', [PublicController::class, 'listings']);
    Route::get('/listings/{id}', [PublicController::class, 'listing']);
    Route::get('/featured', [PublicController::class, 'featured']);
    Route::get('/latest', [PublicController::class, 'latest']);
    Route::get('/popular', [PublicController::class, 'popular']);

    // Stats
    Route::get('/stats', [PublicController::class, 'stats']);

    // Location
    Route::get('/countries', [PublicController::class, 'countries']);
    Route::get('/countries/{countryId}/cities', [PublicController::class, 'cities']);

    // Search
    Route::get('/search', [PublicController::class, 'search']);

    // User Profile
    Route::get('/users/{id}', [PublicController::class, 'userProfile']);
});

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

// Add this route to your api.php file
Route::middleware('auth:api')->get('/user/status', function (Request $request) {
    $user = Auth::user();

    return response()->json([
        'is_banned' => $user->is_active === 'banned',
        'ban_reason' => $user->is_active === 'banned' ? 'Your account has been suspended' : null
    ]);
});

Route::post('login', [App\Http\Controllers\Api\UserController::class,'login']);
Route::post('/facebook/login', [App\Http\Controllers\Api\UserController::class, 'FaceBookSignIn']);
Route::post('register', [App\Http\Controllers\Api\UserController::class,'register']);
Route::get('password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class,'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [App\Http\Controllers\Auth\ForgotPasswordController::class,'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class,'showResetForm'])->name('password.reset');
Route::post('password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class,'reset'])->name('password.update');

Route::get('check-ban-status', [BanCheckController::class, 'checkBanStatus'])->name('api.check-ban-status');
Route::post('register-device', [BanCheckController::class, 'registerDevice'])->name('api.register-device');

// Google authentication routes
Route::post('auth/google', [GoogleAuthController::class, 'handleGoogleAuth']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('user', [UserController::class, 'show']);
    Route::get('user/check_token', [UserController::class, 'check_token']);
});

Route::middleware(['auth:api'])->group(function () {
    Route::post('data-saver/toggle', [GoogleAuthController::class, 'toggleDataSaver']);
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
    Route::post('users/{userId}/ban', [App\Http\Controllers\Api\UserController::class, 'banUser']);
    Route::post('/update-device-token', [App\Http\Controllers\Api\UserController::class, 'updateDeviceToken']);

    Route::post('/users/{id}/toggle-ban', [App\Http\Controllers\Api\BanController::class, 'toggleBan']);
    Route::get('/users/{id}/ban-status', [App\Http\Controllers\Api\BanController::class, 'getBanStatus']);

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
    Route::get('/user-transactions/{userId}', [PointTransactionsController::class, 'getUserTransactions']);
    Route::get('/transactions-between/{userId1}/{userId2}', [PointTransactionsController::class, 'getTransactionsBetweenUsers']);

    // Point Transaction Routes
    Route::get('/transactions/user/{userId}', [App\Http\Controllers\Api\PointTransactionController::class, 'getUserTransactions']);
    Route::get('/transactions/between/{fromUserId}/{toUserId}', [App\Http\Controllers\Api\PointTransactionController::class, 'getTransactionsBetweenUsers']);
    Route::get('/transactions/last/{userId}/{limit?}', [App\Http\Controllers\Api\PointTransactionController::class, 'getLastTransactions']);

    Route::get('users/{user}/notifications', [NotificationController::class, 'index']);
    Route::get('users/{user}/CountNotifications', [NotificationController::class, 'countNotification']);
    Route::get('users/{user}/notifications/{notification}/mark_read/', [NotificationController::class, 'markAsRead']);
    Route::get('users/{user}/notifications/mark_All_read/', [NotificationController::class, 'markAllAsRead']);

    Route::get('update-service-post-badge-status', [ServicePostController::class, 'updateServicePostBadgeStatus']);

    // Service Post Routes
    Route::get('service_posts/users/{user}/favorite', [ServicePostController::class, 'servicePostFavorite']);
    Route::get('service_posts/user/{user}', [ServicePostController::class, 'servicePostUserId']);
    Route::get('service_posts/categories/{categories}', [ServicePostController::class, 'servicePostCategory']);
    Route::get('service_posts/reels', [ServicePostController::class, 'showFromReel']);
    Route::apiResource('service_posts',ServicePostController::class);

    Route::get('service_posts/categories/{categories}/sub_categories/{sub_categories}', [ServicePostController::class, 'servicePostCategorySubCategory']);
    Route::get('service_posts',[ServicePostController::class , 'index']);
    Route::post('service_posts',[ServicePostController::class , 'store']);
    Route::put('service_posts',[ServicePostController::class , 'update']);

    Route::delete('service_posts/deletePhoto/{servicePostImageId}', [ServicePostController::class,'deletePhoto']);
    Route::post('service_posts/updatePhoto/{service_posts}', [ServicePostController::class,'updatePhoto']);
    Route::delete('service_posts/{servicePost}', [ServicePostController::class,'destroy']);

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

    // EXISTING COUNTRY AND CITY ROUTES
    Route::get('countries_list',[CountriesController::class, 'countryList']);
    Route::get('countries_list/{countries}/cities/',[CountriesController::class, 'CountriesSelected']);

    // ADD NEW ROUTES FOR FILTERING FUNCTIONALITY
    // These routes will be used by the Flutter app for country/city filtering
    Route::get('countries', [ServicePostController::class, 'getCountries']);
    Route::get('cities', [ServicePostController::class, 'getCities']);
});
