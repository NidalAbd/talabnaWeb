<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BadgeType;
use App\Models\Categories;
use App\Services\BadgeService;
use App\Models\cities;
use App\Models\countries;
use App\Models\Notification;
use App\Models\palservice_points;
use App\Models\Photos;
use App\Models\point_transactions;
use App\Models\ServicePost;
use App\Models\Sub_categories;
use App\Models\User;
use App\Notifications\BadgeChangeNotification;
use App\Notifications\new_servicepost_notification;
use Carbon\Carbon;
use Google\Service\Dfareporting\City;
use Google\Service\Dfareporting\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Exception;

class ServicePostController extends Controller
{

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            // Check if the user has the required permissions to view all service posts
            if ($user->hasPermission('view_service')) {
                $categories = Categories::withCount('servicePosts')
                    ->orderBy('name')
                    ->get();
                $servicePosts = $user->servicePosts()->with('photos')
                    ->withCount('favorites')
                    ->withCount('comments')
                    ->orderByRaw(BadgeType::getLegacyOrderByClause() . ", id DESC")
                    ->orderBy('id', 'desc')
                    ->paginate(10);
                return response()->json(compact('servicePosts'));
            } else {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, ServicePost $servicePost): JsonResponse
    {
        $user = Auth::user();

        // Check if the user has permission to view the service post
        if (!$user->hasPermission('view_service')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Load service post with related data
        $servicePostShow = ServicePost::with(['photos', 'subCategory', 'category'])
            ->withCount(['favorites', 'comments'])
            ->findOrFail($servicePost->id);

        // Increment view count
        $servicePostShow->increment('view_count');

        // Fetch post owner details
        $postUser = User::with('photos')->find($servicePostShow->user_id);

        // Append user details
        $servicePostShow->user_photo = $postUser->photos->first() ?? null;
        $servicePostShow->user_name = $postUser->user_name;
        $servicePostShow->email = $postUser->email;
        $servicePostShow->WatsNumber = $postUser->WatsNumber;
        $servicePostShow->phones = $postUser->phones;

        // Check if current user has favorited this post
        $servicePostShow->is_favorited = (bool)$servicePostShow->favorites()->where('user_id', Auth::id())->exists();

        return response()->json(compact('servicePostShow'));
    }

    public function showFromReel(Request $request): JsonResponse
    {
        $currentUser = Auth::user();
        $postId = $request->input('post_id');

        // If a specific post ID is requested, check if it exists
        if ($postId) {
            $specificPost = ServicePost::where('id', $postId)
                ->where('state', 'published')
                ->whereHas('photos') // Only include posts with photos
                ->first();

            if (!$specificPost) {
                return response()->json([
                    'error' => 'Post not found',
                    'message' => "The requested post #{$postId} does not exist or is not available"
                ], 404);
            }

            // Load the specific post with all required data
            $specificPost->load(['photos' => function ($query) {
                $query->has('photoable');
            }, 'subCategory', 'category']);

            $specificPost->loadCount(['favorites', 'comments']);

            $postUser = User::with('photos')->find($specificPost->user_id);
            $specificPost->user_photo = $postUser->photos->first();
            $specificPost->user_name = $postUser->user_name;
            $specificPost->email = $postUser->email;
            $specificPost->WatsNumber = $postUser->WatsNumber;
            $specificPost->phones = $postUser->phones;

            $specificPost->is_favorited = (bool)$specificPost->favorites()
                ->where('user_id', Auth::id())
                ->exists();

            $specificPost->distance = round(ServicePost::distance(
                $currentUser->location_latitudes,
                $currentUser->location_longitudes,
                $specificPost->location_latitudes,
                $specificPost->location_longitudes
            ), 2);

            $follow = $currentUser->followers()
                ->where('follower_id', $postUser->id)
                ->first();

            $specificPost->is_followed = (bool)$follow;

            // Return only the specific post
            $servicePosts = collect([$specificPost]);
            return response()->json(compact('servicePosts'));
        }

        // Default behavior for getting general reels - only posts with photos
        $servicePosts = ServicePost::where('state', 'published')
            ->whereHas('photos') // Only include posts with photos
            ->with(['photos' => function ($query) {
                $query->has('photoable');
            }])
            ->withCount('favorites')
            ->withCount('comments')
            ->with('subCategory')
            ->with('category')
            ->orderByRaw(BadgeType::getLegacyOrderByClause() . ", id DESC")
            ->paginate(10);

        foreach ($servicePosts as $servicePost) {
            $postUser = User::with('photos')->find($servicePost->user_id);
            $servicePost->user_photo = $postUser->photos->first();
            $servicePost->user_name = $postUser->user_name;
            $servicePost->email = $postUser->email;
            $servicePost->WatsNumber = $postUser->WatsNumber;
            $servicePost->phones = $postUser->phones;

            $favorite = $servicePost->favorites()->where('user_id', Auth::id())->first();
            $servicePost->is_favorited = (bool)$favorite;

            $servicePost->distance = round(ServicePost::distance(
                $currentUser->location_latitudes,
                $currentUser->location_longitudes,
                $servicePost->location_latitudes,
                $servicePost->location_longitudes
            ), 2);

            $follow = $currentUser->followers()->where('follower_id', $postUser->id)->first();
            $servicePost->is_followed = (bool)$follow;
        }

        return response()->json(compact('servicePosts'));
    }

    public function viewAdd($servicePost): JsonResponse
    {
        $servicePostShow = ServicePost::find($servicePost);
        // Increment the view count of the service post
        $servicePostShow->increment('view_count');
        return response()->json(['view' => $servicePostShow->view_count]);
    }

    public function updatePhoto(Request $request, $servicePost): JsonResponse
    {

        $servicePosts = ServicePost::find($servicePost);
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $photo) {
                $photoPath = $photo->store('storage/photos/posts');
                // Check if the file is an MP4 video
                $isVideo = $photo->getClientOriginalExtension() === 'mp4';
                $servicePosts->photos()->create([
                    'src' => $photoPath,
                    'isVideo' => $isVideo,
                ]);
            }
        }
        return response()->json(['message' => 'Service Post Photo has been updated!', 'id' => $servicePost]);
    }

    public function store(Request $request)
    {
        Log::info('Incoming Request Data:', $request->all());
        $defaultLatitude = 31.9539;
        $defaultLongitude = 35.2376;
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'categories_id' => 'required',
            'sub_categories_id' => 'required|exists:sub_categories,id',
            'title' => 'required|max:255',
            'description' => 'required',
            'price' => 'nullable|numeric',
            'priceCurrency' => 'nullable', // Optional override
            'locationLatitudes' => 'required|numeric',
            'locationLongitudes' => 'required|numeric',
            'type' => 'required',
            'badge_type_id' => 'nullable|integer|exists:badge_types,id', // New dynamic badge system
            'haveBadge' => 'nullable|string', // Legacy support - now accepts any badge name
            'badgeDuration' => 'nullable|integer|min:0',
            'state' => 'nullable',
            'images.*' => 'nullable|file|mimes:jpeg,jpg,png,mp4',
        ]);

        // Check if validation failed
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Continue with the rest of the logic if validation is successful
        $user = Auth::user();
        Log::info('user: ' . $user->city_id);

        // Get the user's country currency information
        $currencyCode = 'USD'; // Default fallback
        $currencyName = ['ar' => 'دولار امريكي', 'en' => 'US Dollar']; // Default fallback

        if ($user->country_id) {
            $country = DB::table('countries')->find($user->country_id);
            if ($country && $country->currency_code) {
                $currencyCode = $country->currency_code;
                $currencyName = json_decode($country->currency_name, true) ?: $currencyName;
            }
        }

        // Allow override if the user specifically requests a different currency
        if ($request->has('priceCurrency') && !empty($request->priceCurrency)) {
            $customCurrency = $request->priceCurrency;
            // Find the matching country to get proper currency info
            $matchingCountry = DB::table('countries')
                ->where('currency_code', $customCurrency)
                ->first();

            if ($matchingCountry && $matchingCountry->currency_name) {
                $currencyCode = $customCurrency;
                $currencyName = json_decode($matchingCountry->currency_name, true);
            }
        }

        // Pre-check badge parameters before entering transaction
        $badgeDuration = $request->badgeDuration ?? 0;
        $badgeTypeModel = null;
        $pointCost = 0;

        // Dynamic badge system: prefer badge_type_id, fallback to haveBadge for legacy
        if ($request->badge_type_id) {
            $badgeTypeModel = BadgeType::find($request->badge_type_id);
            if (!$badgeTypeModel) {
                return response()->json(['error' => 'Invalid badge type ID'], 400);
            }
            if (!$badgeTypeModel->is_active) {
                return response()->json(['error' => 'Badge type is not active'], 400);
            }
        } elseif ($request->haveBadge && $request->haveBadge !== 'عادي') {
            // Legacy support: lookup badge by Arabic name
            $badgeTypeModel = BadgeType::getByOldBadgeName($request->haveBadge);
            if (!$badgeTypeModel) {
                return response()->json(['error' => 'Invalid badge type: ' . $request->haveBadge], 400);
            }
        }

        // Calculate cost and check points if a premium badge is selected
        if ($badgeTypeModel && !$badgeTypeModel->is_default && $badgeDuration > 0) {
            $pointCost = $badgeTypeModel->calculateCost($badgeDuration);

            // Check if user has enough points
            if ($user->pointsBalance < $pointCost) {
                return response()->json([
                    'error' => "Needed $pointCost points for {$badgeTypeModel->name_ar}, but your balance is not enough."
                ], 400);
            }
        }

        // Start transaction
        DB::beginTransaction();
        Log::info('Transaction started for service post creation');

        try {
            // Get default badge if no premium badge selected
            $defaultBadge = BadgeType::getDefault();
            $badgeExpiresAt = null;
            $badgeName = $defaultBadge?->name_ar ?? 'عادي';
            $badgeTypeId = $defaultBadge?->id;
            $finalBadgeDuration = 0;

            // Handle badge logic if a premium badge is selected
            if ($badgeTypeModel && !$badgeTypeModel->is_default && $badgeDuration > 0) {
                Log::info('Deducting points for badge', [
                    'userId' => $user->id,
                    'badge_type_id' => $badgeTypeModel->id,
                    'badge_name' => $badgeTypeModel->name_ar,
                    'duration' => $badgeDuration,
                    'cost' => $pointCost
                ]);

                // Deduct points within the transaction
                palservice_points::where('user_id', $user->id)->decrement('point', $pointCost);

                // Create transaction record
                $pointTransaction = point_transactions::create([
                    'to_user_id' => $user->id,
                    'from_user_id' => $user->id,
                    'type' => 'used',
                    'point' => $pointCost,
                ]);

                Log::info('Point transaction created', ['transaction_id' => $pointTransaction->id]);

                // Set badge details from the dynamic badge model
                $badgeName = $badgeTypeModel->name_ar;
                $badgeTypeId = $badgeTypeModel->id;
                $finalBadgeDuration = $badgeDuration;
                $badgeExpiresAt = Carbon::now()->addDays($badgeDuration);
            }

            // Create service post
            Log::info('Creating service post');
            $servicePost = ServicePost::create([
                'user_id' => $user->id,
                'country_id' => $user->country_id,
                'city_id' => $user->city_id,
                'categories_id' => $request->categories_id,
                'sub_categories_id' => $request->sub_categories_id,
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price ?? 0,
                'price_currency_code' => $currencyCode,
                'price_currency_name' => $currencyName,
                'location_latitudes' => $request->locationLatitudes ?? $defaultLatitude,
                'location_longitudes' => $request->locationLongitudes ?? $defaultLongitude,
                'type' => $request->type,
                'badge_type_id' => $badgeTypeId,
                'have_badge' => $badgeName,
                'badge_duration' => $finalBadgeDuration,
                'badge_expires_at' => $badgeExpiresAt,
                'state' => $request->state ?? 'published',
            ]);

            Log::info('Service Post Created', ['servicePost' => $servicePost->id]);

            // Handle image upload
            if ($request->hasFile('images')) {
                Log::info('Processing image uploads', ['count' => count($request->file('images'))]);

                foreach ($request->file('images') as $index => $photo) {
                    // Store without 'storage/' in the path
                    $photoPath = $photo->store('photos/posts', 'public');

                    // Create the full path for database storage
                    $databasePath = 'storage/' . $photoPath;

                    // Check if the file is a video (MP4)
                    $isVideo = $photo->getClientOriginalExtension() === 'mp4';

                    // Create photo record
                    $photoRecord = $servicePost->photos()->create([
                        'src' => $databasePath,
                        'isVideo' => $isVideo ? 1 : 0,
                    ]);

                    Log::info('Photo record created', [
                        'photo_id' => $photoRecord->id,
                        'index' => $index,
                        'isVideo' => $isVideo
                    ]);
                }
            }

            // Process notifications

            // 1. Service post notification for followers
            Log::info('Creating service post notifications for followers');
            $this->servicePostNotification($servicePost, $user, $request->all());

            // 2. Badge benefit notification if premium badge
            if ($badgeTypeModel && !$badgeTypeModel->is_default && $badgeExpiresAt) {
                Log::info('Creating badge benefit notification');

                // Use dynamic view_boost_percent from badge type
                $viewBoost = $badgeTypeModel->view_boost_percent ?? 0;
                $expiresAt = $badgeExpiresAt->format('Y-m-d H:i:s');

                // Create localized message with benefits information
                $message = json_encode([
                    'ar' => "تم تطبيق شارة {$badgeTypeModel->name_ar} على منشورك بعنوان: {$servicePost->title}. ستحصل على زيادة تقريبية بنسبة {$viewBoost}٪ في المشاهدات وسيتم عرض منشورك في مكان مميز. ستنتهي الشارة في {$expiresAt}. [post_id:{$servicePost->id}]",
                    'en' => "Applied {$badgeTypeModel->name_en} badge to your post titled: {$servicePost->title}. You will get approximately {$viewBoost}% more views and your post will be displayed in premium positions. Badge will expire at {$expiresAt}. [post_id:{$servicePost->id}]"
                ]);

                // Create new notification record directly
                $notification = new Notification();
                $notification->message = $message;
                $notification->user_id = $user->id;
                $notification->type = 'badge';
                $notification->save();

                Log::info('Badge benefit notification created', ['notification_id' => $notification->id]);
            }

            // If we've reached this point, commit the transaction
            DB::commit();
            Log::info('Transaction committed successfully');

            // Return success response with 201 status code
            $servicePostResponse = $this->servicePostUserId(new Request(), $user->id);
            return response()->json([
                'message' => 'Service Post created successfully',
                'data' => $servicePostResponse->original['servicePosts']->first()
            ], 201);

        } catch (\Exception $e) {
            // An error occurred, rollback the transaction
            DB::rollBack();

            Log::error('Error Creating Service Post, transaction rolled back', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return error response with 500 status code
            return response()->json([
                'error' => 'Failed to create service post',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function servicePostNotification($servicePost, $user, $validatedData): void
    {
        try {
            // Retrieve followers of the user who created the post
            $followers = $user->followers()->get();

            // Retrieve followers of the subcategory
            $subcategory = Sub_categories::find($validatedData['sub_categories_id']);
            if (!$subcategory) {
                Log::warning("Subcategory not found for ID: " . $validatedData['sub_categories_id']);
                return;
            }

            $subcategoryFollowers = $subcategory->users()->get();

            // Merge the two follower lists (unique followers)
            $allFollowers = $followers->merge($subcategoryFollowers)->unique('id');

            // Determine the notification type based on service post type
            $type = ($servicePost->type === 'عرض') ? 'offer' : 'request';

            $message = json_encode([
                'ar' => "{$user->user_name} نشر خدمة جديدة من نوع {$type} بعنوان: {$servicePost->title}. [post_id:{$servicePost->id}]",
                'en' => "{$user->user_name} has posted a new {$type} service titled: {$servicePost->title}. [post_id:{$servicePost->id}]"
            ]);

            // Loop through each follower and create/send a notification
            // Add a throttling mechanism to prevent notification spam
            $throttleCount = 0;
            foreach ($allFollowers as $follower) {
                // Check if this user already got a notification for this post in the last 4 hours
                $cacheKey = "post_notif_{$servicePost->id}_{$follower->id}";
                if (Cache::has($cacheKey)) {
                    $throttleCount++;
                    continue; // Skip - already notified recently
                }

                // Set cache to prevent duplicate notifications (4 hour window)
                Cache::put($cacheKey, true, now()->addHours(4));

                // Create a new notification record for the follower
                $notification = new Notification([
                    'message' => $message,
                    'user_id' => $follower->id,
                    'type' => 'post'
                ]);
                $notification->save();

                // Send push notification if the follower has an FCM token
                if ($follower->fcm_token) {
                    $follower->notify(new new_servicepost_notification($servicePost, $follower, $follower->fcm_token));
                }
            }

            Log::info("Sent notifications for post {$servicePost->id}, throttled: $throttleCount");
        } catch (\Exception $e) {
            Log::error('Error sending notifications for service post: ' . $e->getMessage());
        }
    }

// Helper method to deduct points

    private function translateBadgeType(string $badgeType, string $language): string
    {
        $translations = [
            'عادي' => [
                'ar' => 'عادي',
                'en' => 'normal'
            ],
            'ذهبي' => [
                'ar' => 'ذهبي',
                'en' => 'gold'
            ],
            'ماسي' => [
                'ar' => 'ماسي',
                'en' => 'diamond'
            ]
        ];

        if (isset($translations[$badgeType][$language])) {
            return $translations[$badgeType][$language];
        }

        // Fallback to original if translation not found
        return $badgeType;
    }

// Helper method to create notification

    public function servicePostUserId(Request $request, $user): JsonResponse
    {
        $currentUser = Auth::user();
        $servicePosts = ServicePost::where('user_id', $user)
            ->where('state', 'published')
            ->withCount('favorites')
            ->withCount('comments')
            ->with('subCategory')
            ->with('category')
            ->orderByRaw(BadgeType::getLegacyOrderByClause() . ", id DESC");

        // Only load service post photos if data saver is disabled
        if (!$currentUser->data_saver_enabled) {
            $servicePosts->with('photos');
        }

        $servicePosts = $servicePosts->paginate(10);

        // Fetch user photos and names for each service post
        foreach ($servicePosts as $servicePost) {
            // Always load user photos regardless of data saver setting
            $postUser = User::with('photos')->find($servicePost->user_id);
            $servicePost->user_photo = $postUser->photos->first();
            $servicePost->user_name = $postUser->user_name;

            $favorite = $servicePost->favorites()->where('user_id', Auth::id())->first();
            $servicePost->is_favorited = (bool)$favorite;

            // Get the distance between the service post location and the current user
            $servicePost->distance = round(ServicePost::distance(
                $currentUser->location_latitudes,
                $currentUser->location_longitudes,
                $servicePost->location_latitudes,
                $servicePost->location_longitudes
            ), 2);

            // Check if the current user follows the service post user
            $follow = $currentUser->followers()->where('follower_id', $postUser->id)->first();
            $servicePost->is_followed = (bool)$follow;
        }

        return response()->json(compact('servicePosts'));
    }

    public function changeCategory(Request $request, $servicePost): JsonResponse
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'category' => 'required|exists:categories,id',
            'subCategory' => 'required|exists:sub_categories,id',
        ]);

        Log::error('Request data', $request->all());

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        try {
            $servicePost = ServicePost::findOrFail($servicePost);

            $category = Categories::find($validatedData['category']);
            Log::error('Category details', [
                'id' => $category->id,
                'name' => is_array($category->name) ? $category->name : ['ar' => $category->name, 'en' => $category->name]
            ]);

            $subCategory = Sub_categories::find($validatedData['subCategory']);
            Log::error('Subcategory details', [
                'id' => $subCategory->id,
                'name' => is_array($subCategory->name) ? $subCategory->name : ['ar' => $subCategory->name, 'en' => $subCategory->name]
            ]);

            $servicePost->categories_id = $category->id;
            $servicePost->sub_categories_id = $subCategory->id;
            $servicePost->save();

            // Safely handle multilingual names
            $categoryNameAr = is_array($category->name) ? $category->name['ar'] : $category->name;
            $categoryNameEn = is_array($category->name) ? $category->name['en'] : $category->name;
            $subCategoryNameAr = is_array($subCategory->name) ? $subCategory->name['ar'] : $subCategory->name;
            $subCategoryNameEn = is_array($subCategory->name) ? $subCategory->name['en'] : $subCategory->name;

            $message = json_encode([
                'ar' => "تم تغيير تصنيف منشورك بعنوان: {$servicePost->title} إلى: {$categoryNameAr} - {$subCategoryNameAr}. [post_id:{$servicePost->id}]",
                'en' => "Your Post titled: {$servicePost->title} category is changed to: {$categoryNameEn} - {$subCategoryNameEn}. [post_id:{$servicePost->id}]"
            ]);

            // Create a new notification record with the multilingual message
            $notification = new Notification([
                'message' => $message,
                'user_id' => Auth::id(),
                'type' => 'sub_category'
            ]);

            $user->notifications()->save($notification);

            return response()->json(['message' => 'Service Post has been updated!']);
        } catch (Exception $e) {
            Log::error('Error updating service post', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'An error occurred while updating the service post.'], 500);
        }
    }

    public function deletePhoto($servicePostImageId)
    {
        $photo = Photos::find($servicePostImageId);
        if (Storage::disk('public')->exists($photo->src) && !in_array($photo->src, ['storage/photos/servicepost1.jpg', 'storage/photos/servicepost2.jpg', 'storage/photos/servicepost3.jpg', 'storage/photos/servicepost4.jpg', 'storage/photos/servicepost5.jpg'])) {
            Storage::delete($photo->src);
        }
        $photo->delete();
        return response()->json(['success' => 'Service Post image!', 'id' => $servicePostImageId]);
    }

    public function destroy(ServicePost $servicePost): JsonResponse
    {
        try {
            $user = auth()->user();
            // Check if the user has the required permissions to delete a service post
            if (!$user->hasPermission('destroy_service')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            // Delete the associated photos with the post
            foreach ($servicePost->photos as $photo) {
                if (Storage::disk('public')->exists($photo->src) && !in_array($photo->src, ['storage/photos/servicepost1.jpg', 'storage/photos/servicepost2.jpg', 'storage/photos/servicepost3.jpg', 'storage/photos/servicepost4.jpg', 'storage/photos/servicepost5.jpg'])) {
                    Storage::delete($photo->src);
                    $photo->delete();
                }
            }
            // Delete the service post
            $servicePost->delete();
            return response()->json(['message' => 'Service post deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e], 500);
        }
    }

    public function servicePostCategory($category, Request $request): JsonResponse
    {
        $user = Auth::id();
        $currentUser = User::find($user);

        // Check if the user has the required permissions to view all service posts
        if (!$currentUser->hasPermission('view_service')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $userCountryId = $currentUser->country_id;
        $userCityId = $currentUser->city_id;

        $servicePosts = ServicePost::where('state', 'published')
            ->withCount('comments')
            ->withCount('favorites')
            ->with('subCategory')
            ->with('category')
            ->whereHas('user', function($query) {
                $query->where('is_active', 'active');
            });

        // Only load service post photos if data saver is disabled
        if (!$currentUser->data_saver_enabled) {
            $servicePosts->with('photos');
        }

        // Apply type filter if provided (عرض or طلب)
        if ($request->has('type') && in_array($request->type, ['عرض', 'طلب'])) {
            $servicePosts->where('type', $request->type);
        }

        // Apply price range filters if provided
        if ($request->has('min_price') && is_numeric($request->min_price)) {
            $servicePosts->where('price', '>=', (float)$request->min_price);
        }

        if ($request->has('max_price') && is_numeric($request->max_price)) {
            $servicePosts->where('price', '<=', (float)$request->max_price);
        }

        // ADD COUNTRY AND CITY FILTERS
        if ($request->has('country_id') && is_numeric($request->country_id)) {
            $servicePosts->where('country_id', (int)$request->country_id);
        }

        if ($request->has('city_id') && is_numeric($request->city_id)) {
            $servicePosts->where('city_id', (int)$request->city_id);
        }

        if ($category == 6) {
            $userLatitude = $currentUser->location_latitudes;
            $userLongitude = $currentUser->location_longitudes;
            $distanceExpression = (new ServicePost)->distanceExpression($userLatitude, $userLongitude);
            $servicePosts->orderByRaw($distanceExpression);
        } else {
            // First sort by badge type (Diamond → Gold → Normal)
            // Then within each badge type, sort by location relevance
            // Finally sort by creation date (newest first)
            $servicePosts->orderByRaw("
        CASE
            WHEN have_badge = 'ماسي' THEN 1
            WHEN have_badge = 'ذهبي' THEN 2
            ELSE 3
        END,
        CASE
            WHEN country_id = ? AND city_id = ? THEN 1
            WHEN country_id = ? THEN 2
            ELSE 3
        END",
                [$userCountryId, $userCityId, $userCountryId]
            )
                ->orderBy('created_at', 'DESC');

            $servicePosts->where('categories_id', $category);
        }

        // Paginate results
        $servicePosts = $servicePosts->paginate(10);

        // Log filter parameters for debugging
        Log::info('Service Post Category Filters', [
            'category' => $category,
            'type' => $request->type ?? 'not specified',
            'min_price' => $request->min_price ?? 'not specified',
            'max_price' => $request->max_price ?? 'not specified',
            'country_id' => $request->country_id ?? 'not specified',
            'city_id' => $request->city_id ?? 'not specified',
            'count' => $servicePosts->count()
        ]);

        // Loop through each service post to enrich with extra data
        foreach ($servicePosts as $servicePost) {
            // Load user (guaranteed to exist due to whereHas filter above)
            $postUser = User::with('photos')->find($servicePost->user_id);

            $servicePost->user_photo = $postUser->photos->first();
            $servicePost->user_name = $postUser->user_name;

            // Check if the post is favorited by the current user
            $favorite = $servicePost->favorites()->where('user_id', $currentUser->id)->first();
            $servicePost->is_favorited = (bool) $favorite;

            // Calculate the distance between the current user and the service post
            $distance = ServicePost::distance(
                $currentUser->location_latitudes,
                $currentUser->location_longitudes,
                $servicePost->location_latitudes,
                $servicePost->location_longitudes
            );
            $servicePost->distance = round($distance, 3);

            // Check if the current user follows the post owner
            $follow = $currentUser->followers()->where('follower_id', $postUser->id)->first();
            $servicePost->is_followed = (bool) $follow;
        }

        return response()->json(compact('servicePosts'));
    }

    public function servicePostCategorySubCategory($categories, $sub_categories, Request $request): JsonResponse
    {
        $user = Auth::id();
        $currentUser = User::find($user);

        // Check if the user has the required permissions to view all service posts
        if (!$currentUser->hasPermission('view_service')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $userCountryId = $currentUser->country_id;
        $userCityId = $currentUser->city_id;

        // Start building the query
        $servicePosts = ServicePost::where('categories_id', $categories)
            ->where('sub_categories_id', $sub_categories)
            ->where('state', 'published')
            ->withCount('comments')
            ->with('subCategory')
            ->with('category')
            ->withCount('favorites')
            ->whereHas('user', function($query) {
                $query->where('is_active', 'active');
            });

        // Apply type filter if provided (عرض or طلب)
        if ($request->has('type') && in_array($request->type, ['عرض', 'طلب'])) {
            $servicePosts->where('type', $request->type);
        }

        // Apply price range filters if provided
        if ($request->has('min_price') && is_numeric($request->min_price)) {
            $servicePosts->where('price', '>=', (float)$request->min_price);
        }

        if ($request->has('max_price') && is_numeric($request->max_price)) {
            $servicePosts->where('price', '<=', (float)$request->max_price);
        }

        // ADD COUNTRY AND CITY FILTERS
        if ($request->has('country_id') && is_numeric($request->country_id)) {
            $servicePosts->where('country_id', (int)$request->country_id);
        }

        if ($request->has('city_id') && is_numeric($request->city_id)) {
            $servicePosts->where('city_id', (int)$request->city_id);
        }

        // First sort by badge type (Diamond → Gold → Normal)
        // Then within each badge type, sort by location relevance
        // Finally sort by creation date (newest first)
        $servicePosts->orderByRaw("
    CASE
        WHEN have_badge = 'ماسي' THEN 1
        WHEN have_badge = 'ذهبي' THEN 2
        ELSE 3
    END,
    CASE
        WHEN country_id = ? AND city_id = ? THEN 1
        WHEN country_id = ? THEN 2
        ELSE 3
    END",
            [$userCountryId, $userCityId, $userCountryId]
        )
            ->orderBy('created_at', 'DESC');

        // Only load service post photos if data saver is disabled
        if (!$currentUser->data_saver_enabled) {
            $servicePosts->with('photos');
        }

        // Paginate the results
        $servicePosts = $servicePosts->paginate(10);

        // Log filter parameters for debugging
        Log::info('Service Post SubCategory Filters', [
            'category' => $categories,
            'subcategory' => $sub_categories,
            'type' => $request->type ?? 'not specified',
            'min_price' => $request->min_price ?? 'not specified',
            'max_price' => $request->max_price ?? 'not specified',
            'country_id' => $request->country_id ?? 'not specified',
            'city_id' => $request->city_id ?? 'not specified',
            'count' => $servicePosts->count()
        ]);

        // Fetch user photos and names for each service post
        foreach ($servicePosts as $servicePost) {
            // Load user (guaranteed to exist due to whereHas filter above)
            $postUser = User::with('photos')->find($servicePost->user_id);

            $servicePost->user_photo = $postUser->photos->first();
            $servicePost->user_name = $postUser->user_name;

            $favorite = $servicePost->favorites()->where('user_id', $currentUser->id)->first();
            $servicePost->is_favorited = (bool)$favorite;

            // Get the distance between the service post location and the current user
            $servicePost->distance = round(ServicePost::distance(
                $currentUser->location_latitudes,
                $currentUser->location_longitudes,
                $servicePost->location_latitudes,
                $servicePost->location_longitudes
            ), 2);

            // Check if the current user follows the service post user
            $follow = $currentUser->followers()->where('follower_id', $postUser->id)->first();
            $servicePost->is_followed = (bool)$follow;
        }

        return response()->json(compact('servicePosts'));
    }

// ADD THESE NEW METHODS FOR COUNTRIES AND CITIES

    /**
     * Get all countries
     */
    public function getCountries(): JsonResponse
    {
        try {
            $countries = countries::orderBy('name->en')->get();

            return response()->json([
                'data' => $countries
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching countries: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch countries'], 500);
        }
    }

    /**
     * Get cities (optionally filtered by country)
     */
    public function getCities(Request $request): JsonResponse
    {
        try {
            $cities = cities::query();

            // Filter by country if provided
            if ($request->has('country_id') && is_numeric($request->country_id)) {
                $cities->where('country_id', (int)$request->country_id);
            }

            $cities = $cities->orderBy('name->en')->get();

            return response()->json([
                'data' => $cities
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching cities: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch cities'], 500);
        }
    }

// Also update the servicePostUserId method for consistency

    public function servicePostFavorite(): JsonResponse
    {
        $user = Auth::id();
        $currentUser = User::find($user);

        if (!$currentUser->hasPermission('view_service')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $servicePosts = ServicePost::whereHas('favorites', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })
            ->withCount('favorites')
            ->withCount('comments')
            ->with('subCategory')
            ->with('category')
            ->orderByRaw(BadgeType::getLegacyOrderByClause() . ", id DESC");

        // Only load service post photos if data saver is disabled
        if (!$currentUser->data_saver_enabled) {
            $servicePosts->with('photos');
        }

        $servicePosts = $servicePosts->paginate(10);

        // Fetch user photos and names for each service post
        foreach ($servicePosts as $servicePost) {
            // Load user (guaranteed to exist due to whereHas filter above)
            $postUser = User::with('photos')->find($servicePost->user_id);

            $servicePost->user_photo = $postUser->photos->first();
            $servicePost->user_name = $postUser->user_name;

            $favorite = $servicePost->favorites()->where('user_id', $currentUser->id)->first();
            $servicePost->is_favorited = (bool)$favorite;

            // Get the distance between the service post location and the current user
            $servicePost->distance = round(ServicePost::distance(
                $currentUser->location_latitudes,
                $currentUser->location_longitudes,
                $servicePost->location_latitudes,
                $servicePost->location_longitudes
            ), 2);

            // Check if the current user follows the service post user
            $follow = $currentUser->followers()->where('follower_id', $postUser->id)->first();
            $servicePost->is_followed = (bool)$follow;
        }

        return response()->json(compact('servicePosts'));
    }

    public function update(Request $request, ServicePost $servicePost): JsonResponse
    {
        Log::info('Incoming Request Data servicePost: ' . $servicePost);
        Log::info('Incoming Request Data:', $request->all());

        // Check if badge has expired before processing updates
        $this->checkAndUpdateBadgeExpiration($servicePost);
        $defaultLatitude = 31.9539;
        $defaultLongitude = 35.2376;

        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'description' => 'required',
            'categories_id' => 'required',
            'sub_categories_id' => 'required|exists:sub_categories,id',
            'price' => 'nullable|numeric',
            'priceCurrency' => 'nullable',
            'locationLatitudes' => 'required|numeric',
            'locationLongitudes' => 'required|numeric',
            'type' => 'required',
            'badge_type_id' => 'nullable|integer|exists:badge_types,id', // New dynamic badge system
            'haveBadge' => 'nullable|string', // Legacy support - now accepts any badge name
            'badgeDuration' => 'nullable|integer|min:0',
            'images.*' => 'nullable|file|mimes:jpeg,jpg,png,mp4',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $user = Auth::user();
            $validatedData = $validator->validated();

            // Determine currency - either keep existing, use specified, or update from user's country
            $currencyCode = $servicePost->price_currency_code;
            $currencyName = is_array($servicePost->price_currency_name) ?
                $servicePost->price_currency_name :
                json_decode($servicePost->price_currency_name, true);

            // If user requested a currency change
            if (!empty($validatedData['priceCurrency'])) {
                $requestedCurrency = $validatedData['priceCurrency'];
                $matchingCountry = DB::table('countries')
                    ->where('currency_code', $requestedCurrency)
                    ->first();

                if ($matchingCountry && $matchingCountry->currency_name) {
                    $currencyCode = $requestedCurrency;
                    $currencyName = json_decode($matchingCountry->currency_name, true);
                }
            }

            // Handle badge updates if any - using dynamic badge system
            $badgeDuration = $validatedData['badgeDuration'] ?? 0;
            $badgeTypeModel = null;

            // Dynamic badge system: prefer badge_type_id, fallback to haveBadge for legacy
            if ($request->badge_type_id) {
                $badgeTypeModel = BadgeType::find($request->badge_type_id);
            } elseif (isset($validatedData['haveBadge']) && $validatedData['haveBadge'] !== 'عادي') {
                // Legacy support: lookup badge by Arabic name
                $badgeTypeModel = BadgeType::getByOldBadgeName($validatedData['haveBadge']);
            }

            // Check if badge is changing
            $isBadgeChanging = false;
            if ($badgeTypeModel && !$badgeTypeModel->is_default) {
                $isBadgeChanging = $servicePost->badge_type_id !== $badgeTypeModel->id ||
                                   $servicePost->badge_duration !== $badgeDuration;
            } elseif (!$badgeTypeModel || $badgeTypeModel->is_default) {
                // Changing to default/normal badge
                $isBadgeChanging = $servicePost->badge_type_id !== null &&
                                   $servicePost->have_badge !== 'عادي';
            }

            if ($isBadgeChanging && $badgeDuration > 0) {
                $badgeResult = $this->updateServicePostBadgeDynamic(
                    $servicePost,
                    $user,
                    $badgeTypeModel,
                    $badgeDuration
                );

                if (!$badgeResult['success']) {
                    return response()->json(['error' => $badgeResult['message']], 400);
                }
            }

            // Update service post details
            $updateData = [
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'categories_id' => $validatedData['categories_id'],
                'sub_categories_id' => $validatedData['sub_categories_id'],
                'price' => $validatedData['price'] ?? $servicePost->price,
                'price_currency_code' => $currencyCode,
                'price_currency_name' => $currencyName,
                'location_latitudes' => $validatedData['locationLatitudes'] ?? $defaultLatitude,
                'location_longitudes' => $validatedData['locationLongitudes'] ?? $defaultLongitude,
                'type' => $validatedData['type'],
            ];

            $servicePost->update($updateData);

            // Handle image upload
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $photo) {
                    // Store without 'storage/' in the path
                    $photoPath = $photo->store('photos/posts', 'public');

                    // Create the full path for database storage
                    $databasePath = 'storage/' . $photoPath;

                    // Check if the file is a video (MP4)
                    if ($photo->getClientOriginalExtension() === 'mp4') {
                        $servicePost->photos()->create([
                            'src' => $databasePath,
                            'isVideo' => 1,
                        ]);
                    } else {
                        $servicePost->photos()->create([
                            'src' => $databasePath,
                            'isVideo' => 0,
                        ]);
                    }
                }
            }

            // Send notification
            $message = json_encode([
                'ar' => "تم تحديث منشور الخدمة بعنوان: {$servicePost->title}. [post_id:{$servicePost->id}]",
                'en' => "Service Post titled: {$servicePost->title} has been updated. [post_id:{$servicePost->id}]"
            ]);

            $notification = new Notification([
                'message' => $message,
                'user_id' => Auth::id(),
                'type' => 'post'
            ]);
            $user->notifications()->save($notification);

            return response()->json([
                'message' => 'Service Post has been updated!',
                'id' => $servicePost->id
            ]);

        } catch (Exception $e) {
            Log::error('Error Updating Service Post:', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function checkAndUpdateBadgeExpiration(ServicePost $servicePost): bool
    {
        // If badge is already standard or no expiration mechanism, nothing to do
        if ($servicePost->have_badge === 'عادي') {
            return false;
        }

        // Method 1: Check badge_expires_at if available
        if ($servicePost->badge_expires_at) {
            if (Carbon::now()->greaterThanOrEqualTo($servicePost->badge_expires_at)) {
                $this->expireBadge($servicePost);
                return true;
            }
            return false;
        }

        // Method 2: Fallback - check based on duration and created_at
        if ($servicePost->badge_duration > 0) {
            $expirationDate = Carbon::parse($servicePost->created_at)->addDays($servicePost->badge_duration);

            if (Carbon::now()->greaterThanOrEqualTo($expirationDate)) {
                $this->expireBadge($servicePost);
                return true;
            }

            // Update the badge_expires_at field for future checks
            $servicePost->badge_expires_at = $expirationDate;
            $servicePost->save();
        }

        return false;
    }

    private function expireBadge(ServicePost $servicePost): void
    {
        // Reset badge to standard
        $servicePost->have_badge = 'عادي';
        $servicePost->badge_duration = 0;
        $servicePost->badge_expires_at = null;
        $servicePost->save();

        // Create expiration notification with post ID
        $message = json_encode([
            'ar' => "تم تغيير شارة منشور الخدمة بعنوان: {$servicePost->title} إلى " . $this->translateBadgeType('عادي', 'ar') . " بسبب انتهاء المدة. [post_id:{$servicePost->id}]",
            'en' => "Service Post titled: {$servicePost->title} badge changed to " . $this->translateBadgeType('عادي', 'en') . " due to duration expiration. [post_id:{$servicePost->id}]"
        ]);

        Notification::create([
            'message' => $message,
            'user_id' => $servicePost->user_id,
            'type' => 'badge'
        ]);

        try {
            $user = User::find($servicePost->user_id);
            if ($user && !empty($user->fcm_token)) {
                $user->notify(new BadgeChangeNotification('expired', 'الشارة', $servicePost->id));
            }
        } catch (\Exception $e) {
            Log::warning('FCM badge expiry notification failed: ' . $e->getMessage());
        }

        Log::info("Badge expired for service post #{$servicePost->id}");
    }

    private function updateServicePostBadge(ServicePost $servicePost, User $user, string $badgeType, int $duration): array
    {
        // Badge prices per day
        $badgePrices = ['ذهبي' => 2, 'ماسي' => 10, 'عادي' => 0];

        // If changing to normal badge, just update without points
        if ($badgeType === 'عادي') {
            $servicePost->have_badge = 'عادي';
            $servicePost->badge_duration = 0;
            $servicePost->badge_expires_at = null;
            $servicePost->save();

            return ['success' => true];
        }

        // Validate badge type
        if (!isset($badgePrices[$badgeType])) {
            return [
                'success' => false,
                'message' => 'Invalid badge type'
            ];
        }

        // Calculate point cost
        $pointCost = $duration * $badgePrices[$badgeType];

        // Check if user has enough points
        if ($user->pointsBalance < $pointCost) {
            return [
                'success' => false,
                'message' => "Needed $pointCost Points for $badgeType, Your Balance is not enough."
            ];
        }

        // Deduct points
        palservice_points::where('user_id', $user->id)->decrement('point', $pointCost);

        // Create transaction record
        point_transactions::create([
            'to_user_id' => $user->id,
            'from_user_id' => $user->id,
            'type' => 'used',
            'point' => $pointCost,
        ]);

        // Set exact expiration time (current time + duration days)
        $expirationDate = Carbon::now()->addDays($duration);

        // Calculate expected expiration time for notification
        $expirationTimeString = $expirationDate->format('Y-m-d H:i:s');

        // Update service post badge
        $servicePost->have_badge = $badgeType;
        $servicePost->badge_duration = $duration;
        $servicePost->badge_expires_at = $expirationDate;
        $servicePost->save();

        // Create notification with post ID embedded in message
        $message = json_encode([
            'ar' => "تم تغيير شارة منشورك بعنوان: {$servicePost->title} إلى " . $this->translateBadgeType($badgeType, 'ar') . " لمدة $duration يوم. ستنتهي الشارة في $expirationTimeString. [post_id:{$servicePost->id}]",
            'en' => "Your Post titled: {$servicePost->title} badge is changed to " . $this->translateBadgeType($badgeType, 'en') . " for $duration days. Badge will expire at $expirationTimeString. [post_id:{$servicePost->id}]"
        ]);


        Notification::create([
            'message' => $message,
            'user_id' => $user->id,
            'type' => 'badge'
        ]);

        try {
            if (!empty($user->fcm_token)) {
                $badgeNameAr = $this->translateBadgeType($badgeType, 'ar');
                $user->notify(new BadgeChangeNotification('applied', $badgeNameAr, $servicePost->id));
            }
        } catch (\Exception $e) {
            Log::warning('FCM badge applied notification failed: ' . $e->getMessage());
        }

        return ['success' => true];
    }

    /**
     * Dynamic badge update method - uses BadgeType model instead of hardcoded values
     */
    private function updateServicePostBadgeDynamic(ServicePost $servicePost, User $user, ?BadgeType $badgeTypeModel, int $duration): array
    {
        // Get default badge for resetting
        $defaultBadge = BadgeType::getDefault();

        // If no badge model or it's the default, reset to normal
        if (!$badgeTypeModel || $badgeTypeModel->is_default) {
            $servicePost->badge_type_id = $defaultBadge?->id;
            $servicePost->have_badge = $defaultBadge?->name_ar ?? 'عادي';
            $servicePost->badge_duration = 0;
            $servicePost->badge_expires_at = null;
            $servicePost->save();

            return ['success' => true];
        }

        // Calculate point cost dynamically from badge model
        $pointCost = $badgeTypeModel->calculateCost($duration);

        // Check if user has enough points
        if ($user->pointsBalance < $pointCost) {
            return [
                'success' => false,
                'message' => "Needed $pointCost Points for {$badgeTypeModel->name_ar}, Your Balance is not enough."
            ];
        }

        // Deduct points
        palservice_points::where('user_id', $user->id)->decrement('point', $pointCost);

        // Create transaction record
        point_transactions::create([
            'to_user_id' => $user->id,
            'from_user_id' => $user->id,
            'type' => 'used',
            'point' => $pointCost,
        ]);

        // Set exact expiration time (current time + duration days)
        $expirationDate = Carbon::now()->addDays($duration);
        $expirationTimeString = $expirationDate->format('Y-m-d H:i:s');

        // Update service post badge with dynamic values
        $servicePost->badge_type_id = $badgeTypeModel->id;
        $servicePost->have_badge = $badgeTypeModel->name_ar;
        $servicePost->badge_duration = $duration;
        $servicePost->badge_expires_at = $expirationDate;
        $servicePost->save();

        // Create notification with dynamic badge names
        $message = json_encode([
            'ar' => "تم تغيير شارة منشورك بعنوان: {$servicePost->title} إلى {$badgeTypeModel->name_ar} لمدة $duration يوم. ستنتهي الشارة في $expirationTimeString. [post_id:{$servicePost->id}]",
            'en' => "Your Post titled: {$servicePost->title} badge is changed to {$badgeTypeModel->name_en} for $duration days. Badge will expire at $expirationTimeString. [post_id:{$servicePost->id}]"
        ]);

        Notification::create([
            'message' => $message,
            'user_id' => $user->id,
            'type' => 'badge'
        ]);

        try {
            if (!empty($user->fcm_token)) {
                $user->notify(new BadgeChangeNotification('applied', $badgeTypeModel->name_ar, $servicePost->id));
            }
        } catch (\Exception $e) {
            Log::warning('FCM badge dynamic notification failed: ' . $e->getMessage());
        }

        return ['success' => true];
    }

    public function changeBadge(Request $request, $servicePost): JsonResponse
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'haveBadge' => 'required|in:ذهبي,ماسي,عادي',
            'badgeDuration' => 'required|integer|min:0',
        ]);

        // If validation fails, return the errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $user = Auth::user();
            $servicePost = ServicePost::findOrFail($servicePost);

            // Check if badge has expired before updating
            $this->checkAndUpdateBadgeExpiration($servicePost);

            // Get the validated data
            $validatedData = $validator->validated();
            $badgeType = $validatedData['haveBadge'];
            $duration = $validatedData['badgeDuration'];

            // Update the badge
            $badgeResult = $this->updateServicePostBadge($servicePost, $user, $badgeType, $duration);

            if (!$badgeResult['success']) {
                return response()->json(['error' => $badgeResult['message']], 400);
            }

            return response()->json(['message' => 'Service Post badge updated successfully!']);

        } catch (Exception $e) {
            Log::error('Badge change error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'An error occurred while updating the service post badge.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function updateServicePostBadgeStatus(): JsonResponse
    {
        // Find posts with badge_expires_at in the past
        $expiredByExpirationDate = ServicePost::whereIn('have_badge', ['ذهبي', 'ماسي'])
            ->whereNotNull('badge_expires_at')
            ->where('badge_expires_at', '<', Carbon::now())
            ->get();

        $count = $expiredByExpirationDate->count();

        foreach ($expiredByExpirationDate as $post) {
            $this->expireBadge($post);
        }

        // Fallback: check posts without badge_expires_at but with duration
        $potentiallyExpired = ServicePost::whereIn('have_badge', ['ذهبي', 'ماسي'])
            ->where('badge_duration', '>', 0)
            ->whereNull('badge_expires_at')
            ->get();

        foreach ($potentiallyExpired as $post) {
            $expirationDate = Carbon::parse($post->created_at)->addDays($post->badge_duration);

            if (Carbon::now()->greaterThanOrEqualTo($expirationDate)) {
                $this->expireBadge($post);
                $count++;
            } else {
                // Update the badge_expires_at field for future checks
                $post->badge_expires_at = $expirationDate;
                $post->save();
            }
        }

        return response()->json(['message' => "Service post badge status updated successfully. Expired $count badges."]);
    }

    private function deductPoints($user, $points)
    {
        palservice_points::where('user_id', $user->id)->decrement('point', $points);

        // Create a transaction record
        point_transactions::create([
            'to_user_id' => $user->id,
            'from_user_id' => $user->id,
            'type' => 'used',
            'point' => $points
        ]);
    }

    private function createBadgeChangeNotification($user, $badgeType, $servicePost = null)
    {
        $postId = $servicePost->id ?? 0;
        $message = json_encode([
            'ar' => "تم تغيير شارة منشورك إلى " . $this->translateBadgeType($badgeType, 'ar') . ". [post_id:{$postId}]",
            'en' => "Your Post badge is changed to " . $this->translateBadgeType($badgeType, 'en') . ". [post_id:{$postId}]"
        ]);

        Notification::create([
            'message' => $message,
            'user_id' => $user->id,
            'type' => 'badge'
        ]);

        try {
            if (!empty($user->fcm_token)) {
                $badgeNameAr = $this->translateBadgeType($badgeType, 'ar');
                $user->notify(new BadgeChangeNotification('applied', $badgeNameAr, $postId));
            }
        } catch (\Exception $e) {
            Log::warning('FCM badge change notification failed: ' . $e->getMessage());
        }
    }

    private function handleBadge($user, $badgeType, $badgeDuration): array
    {
        if (!$badgeType || $badgeType === 'عادي') {
            return ['badge' => 'عادي', 'duration' => 0, 'error' => false];
        }

        // Badge prices per day
        $badgePrices = ['ذهبي' => 2, 'ماسي' => 10];

        // Ensure the badge type is valid
        if (!isset($badgePrices[$badgeType])) {
            return [
                'error' => true,
                'message' => "Invalid badge type."
            ];
        }

        // Calculate total cost based on duration
        $cost = $badgeDuration * $badgePrices[$badgeType];

        // Check if user has enough points
        if ($user->pointsBalance < $cost) {
            return [
                'error' => true,
                'message' => "Needed $cost points for $badgeType, but your balance is not enough."
            ];
        }

        // Deduct points and log transaction
        palservice_points::where('user_id', $user->id)->decrement('point', $cost);
        point_transactions::create([
            'to_user_id' => $user->id,
            'from_user_id' => $user->id,
            'type' => 'used',
            'point' => $cost,
        ]);

        return [
            'badge' => $badgeType,
            'duration' => $badgeDuration,
            'expires_at' => Carbon::now()->addDays($badgeDuration), // Use exact current time
            'error' => false
        ];
    }
}
