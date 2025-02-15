<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\Notification;
use App\Models\palservice_points;
use App\Models\Photos;
use App\Models\point_transactions;
use App\Models\Sub_categories;
use App\Models\User;
use App\Models\ServicePost;
use App\Notifications\new_servicepost_notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Exception;

class ServicePostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
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
                    ->orderByRaw("FIELD(have_badge, 'D', 'G', 'N'), id DESC")
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

    /**
     * Show the form for creating a new resource.
     *
     * @return JsonResponse
     */

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        Log::info('Request' , [$request->all()]);
        $user = Auth::user();
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'category' => 'required|exists:categories,id',
            'subCategory' => 'required|exists:sub_categories,id',
            'price' => 'nullable|numeric',
            'priceCurrency' => 'nullable',
            'locationLatitudes' => 'required|numeric',
            'locationLongitudes' => 'required|numeric',
            'type' => 'required',
            'haveBadge' => 'nullable',
            'badgeDuration' => 'nullable',
            'images.*' => 'file|mimes:jpeg,jpg,mp4',
        ]);


        $category = Categories::find($validatedData['category']);
        $subCategory = Sub_categories::find($validatedData['subCategory']);
        $ServicePostFinalBadge = '';
        $ServicePostFinalDuration = 0;
        $servicePost = new \App\Models\ServicePost;
        if ($validatedData['haveBadge'] !== null) {

            if ($validatedData['haveBadge'] == "ذهبي") {
                $GoldenDurationPrice = $validatedData['badgeDuration'] * 2;
                if ($user->pointsBalance >= $GoldenDurationPrice) {
                    $ServicePostFinalBadge = $validatedData['haveBadge'];
                    $ServicePostFinalDuration = $validatedData['badgeDuration'];
                    palservice_points::where('user_id', $user->id)->decrement('point', $GoldenDurationPrice);

                    // Create a transaction record
                    $transaction = new point_transactions();
                    $transaction->to_user_id = $user->id;
                    $transaction->from_user_id = $user->id;
                    $transaction->type = 'used';
                    $transaction->point = $GoldenDurationPrice;
                    $transaction->save();

                } else {
                    return response()->json(['error' => 'Needed ' . $ServicePostFinalBadge . ' Point, Your Balance Point not enough.'], 400);
                };
            } elseif ($validatedData['haveBadge'] == "ماسي") {

                $DiamondeDurationPrice = $validatedData['badgeDuration'] * 10;
                if ($user->pointsBalance >= $DiamondeDurationPrice) {
                    $ServicePostFinalBadge = $validatedData['haveBadge'];
                    $ServicePostFinalDuration = $validatedData['badgeDuration'];
                    palservice_points::where('user_id', $user->id)->decrement('point', $DiamondeDurationPrice);

                    // Create a transaction record
                    $transaction = new point_transactions();
                    $transaction->to_user_id = $user->id;
                    $transaction->from_user_id = $user->id;
                    $transaction->type = 'used';
                    $transaction->point = $DiamondeDurationPrice;
                    $transaction->save();
                } else {
                    return response()->json(['error' => 'Needed ' . $DiamondeDurationPrice . ' Point, Your Balance Point not enough.'], 400);
                };
            } elseif ($validatedData['haveBadge'] == "عادي") {
                $ServicePostFinalBadge = $validatedData['haveBadge'];
                $ServicePostFinalDuration = $validatedData['badgeDuration'];
            } else {

                return response()->json(['error' => 'Service Post Type not Correct.'], 400);
            }
        }
        $servicePost->title = $validatedData['title'];
        $servicePost->description = $validatedData['description'];
        $servicePost->category = $category->name;
        $servicePost->sub_category = $subCategory->name;
        $servicePost->price = $validatedData['price'];
        $servicePost->price_currency = $validatedData['priceCurrency'];
        $servicePost->location_latitudes = $validatedData['locationLatitudes'];
        $servicePost->location_longitudes = $validatedData['locationLongitudes'];
        $servicePost->type = $validatedData['type'];
        $servicePost->have_badge = $ServicePostFinalBadge;
        $servicePost->badge_duration = $ServicePostFinalDuration;
        $servicePost->user_id = $user->id;
        $servicePost->categories_id = $validatedData['category'];
        $servicePost->sub_categories_id = $validatedData['subCategory'];
        $servicePost->save();
        // Add logging for the number of received files
        if ($request->hasFile('images')) {
            $fileCount = count($request->file('images'));
        } else {
            Log::info("No images received for create new service post");
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $photo) {
                $photoPath = $photo->store('photos/posts');
                // Check if the file is an MP4 video
                if ( $photo->getClientOriginalExtension() === 'mp4') {
                    Log::info("Is Video : true");
                    $servicePost->photos()->create([
                        'src' => $photoPath,
                        'isVideo' => 1,
                    ]);
                } else {
                    Log::info("Is not Video : false");
                    $servicePost->photos()->create([
                        'src' => $photoPath,
                        'isVideo' => 0,
                    ]);
                }
            }
        }


        $this->servicePostNotification($servicePost, $user, $validatedData);
        return response()->json([
            'message' => 'Service post created successfully',
        ]);
    }

    public function servicePostNotification($servicePost, $user, $validatedData): void
    {
        // Retrieve followers of the user who created the post
        $followers = $user->followers()->get();
        // Retrieve followers of the subcategory
        $subcategory = Sub_categories::find($validatedData['subCategory']);
        $subcategoryFollowers = $subcategory->users()->get();
        // Merge the two follower lists
        $allFollowers = $followers->merge($subcategoryFollowers);
        // Loop through each follower and create and send a notification
        foreach ($allFollowers as $follower) {
            $type = ($servicePost->type === 'عرض') ? 'offer' : 'request';
            $message = "{$user->user_name} {$type} the following service";
            $notification = new Notification([
                'message' => $message,
                'user_id' => $follower->id,
                'type'    => 'post'
            ]);
            $notification->save();
            if ($follower->fcm_token) {
                $follower->notify(new new_servicepost_notification($servicePost, $follower, $follower->fcm_token));
            }
        }
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, ServicePost $servicePost): JsonResponse
    {
        $user = Auth::user();
        // Check if the user has the required permissions to view the service post
        if ($user->hasPermission('view_service')) {
            // Load the service post's details, photos, and user information
            // Get the service post with the specified ID
            $servicePostShow = \App\Models\ServicePost::with('photos')
                ->withCount('favorites')
                ->withCount('comments')

                ->find($servicePost->id);
            // Increment the view count of the service post
            $servicePostShow->increment('view_count');

            return response()->json(compact('servicePostShow'));
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }

    public function showFromReel(Request $request): JsonResponse
    {
        $CurrentUser = Auth::user();
        $servicePosts = ServicePost::where('state', 'published')
            ->with(['photos' => function ($query) {
                $query->has('photoable');
            }])
            ->withCount('favorites')
            ->withCount('comments')
            ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
            ->paginate(10);

        foreach ($servicePosts as $servicePost) {
            $postUser = User::with('photos')->find($servicePost->user_id);
            $servicePost->user_photo = $postUser->photos->first() ? $postUser->photos->first()->src : null;
            $servicePost->user_name = $postUser->user_name; // Add the user's name to the response
            $servicePost->email = $postUser->email; // Add the user's name to the response
            $servicePost->WatsNumber = $postUser->WatsNumber; // Add the user's name to the response
            $servicePost->phones = $postUser->phones; // Add the user's name to the response
            $favorite = $servicePost->favorites()->where('user_id', Auth::id())->first();
            $servicePost->is_favorited = (bool)$favorite;
            $servicePost->distance = round(ServicePost::distance($CurrentUser->location_latitudes, $CurrentUser->location_longitudes, $servicePost->location_latitudes, $servicePost->location_longitudes), 2);
            $follow = $CurrentUser->followers()->where('follower_id',  $postUser->id)->first();
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

    public function update(Request $request, ServicePost $servicePost): JsonResponse
    {
//        Log::info('Request received', ['request' => $request->all()]);
//        Log::info('servicePost received', ['servicePost' => $servicePost]);

        $user = Auth::user();
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'category' => 'required|exists:categories,id',
            'subCategory' => 'required|exists:sub_categories,id',
            'price' => 'nullable|numeric',
            'priceCurrency' => 'nullable',
            'locationLatitudes' => 'required|numeric',
            'locationLongitudes' => 'required|numeric',
            'type' => 'required',
            'haveBadge' => 'nullable',
            'badgeDuration' => 'nullable',
            'images.*' => 'image|mimes:jpeg,png,jpg',
        ]);

        $category = Categories::find($validatedData['category']);
        $subCategory = Sub_categories::find($validatedData['subCategory']);
        $servicePost->title = $validatedData['title'] ?? $servicePost->title;
        $servicePost->description = $validatedData['description'] ?? $servicePost->description;
        $servicePost->price = $validatedData['price'] ?? $servicePost->price;
        $servicePost->price_currency = $validatedData['priceCurrency'] ?? $servicePost->price_currency;
        $servicePost->location_latitudes = $validatedData['locationLatitudes'] ?? $servicePost->location_latitudes;
        $servicePost->location_longitudes = $validatedData['locationLongitudes'] ?? $servicePost->location_longitudes;
        $servicePost->type = $validatedData['type'] ?? $servicePost->type;
        $servicePost->category = $category->name;
        $servicePost->sub_category = $subCategory->name;
        $servicePost->categories_id = $category ? $category->id : $servicePost->categories_id;
        $servicePost->sub_categories_id = $subCategory ? $subCategory->id : $servicePost->sub_categories_id;
        $servicePost->save();
        // Delete photos


        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $photo) {
                // Log the original file name
                Log::info("Processing file: {$photo->getClientOriginalName()}");
                $photoPath = $photo->store('photos/posts');
                $servicePost->photos()->create([
                    'src' => $photoPath,
                ]);
                // Log the stored file path
                Log::info("Stored file: {$photoPath}");
            }
        }
        $message = "Service Post has been updated.";
        $notification = new Notification([
            'message' => $message,
            'user_id' => Auth::id(),
            'type'    => 'post'
        ]);
        $user->notifications()->save($notification);

        return response()->json(['message' => 'Service Post has been updated!', 'id' => $servicePost->id]);

    }

    public function updatePhoto(Request $request,  $servicePost): JsonResponse
    {

        $servicePosts = ServicePost::find($servicePost);
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $photo) {
                $photoPath = $photo->store('photos/posts');
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
    public function changeCategory(Request $request, $servicePost)
    {
        $user = Auth::user();

        try {
            $validatedData = $request->validate([
                'category' => 'required|exists:categories,id',
                'subCategory' => 'required|exists:sub_categories,id',
            ]);
            $servicePost = ServicePost::findOrFail($servicePost);
            $category = Categories::find($validatedData['category']);
            $subCategory = Sub_categories::find($validatedData['subCategory']);
            $servicePost->categories_id = $category ? $category->id : $servicePost->categories_id;
            $servicePost->sub_categories_id = $subCategory ? $subCategory->id : $servicePost->sub_categories_id;
            $servicePost->category = $category->name;
            $servicePost->sub_category = $subCategory->name;
            $servicePost->save();
            $message = "Your Post Category is changed.";
            $notification = new Notification([
                'message' => $message,
                'user_id' => Auth::id(),
                'type'    => 'sub_category'
            ]);
            $user->notifications()->save($notification);
            return response()->json(['message' => 'Service Post has been updated!']);
        } catch (Exception $e) {
            return response()->json(['error' => $e]);
        }
    }

    public function changeBadge(Request $request, $servicePost){
        $validatedData = $request->validate([
            'haveBadge' => 'nullable',
            'badgeDuration' => 'nullable',
        ]);

        try {

            $user = Auth::user();
            $ServicePostFinalBadge = '';
            $ServicePostFinalDuration = 0;
            $servicePost = ServicePost::findOrFail($servicePost);
            if ($validatedData['haveBadge'] !== null) {

                if ($validatedData['haveBadge'] == "ذهبي") {
                    $GoldenDurationPrice = $validatedData['badgeDuration'] * 2;
                    if ($user->pointsBalance >= $GoldenDurationPrice) {
                        $ServicePostFinalBadge = $validatedData['haveBadge'];
                        $ServicePostFinalDuration = $validatedData['badgeDuration'];
                        palservice_points::where('user_id', $user->id)->decrement('point', $GoldenDurationPrice);

                        // Create a transaction record
                        $transaction = new point_transactions();
                        $transaction->to_user_id = $user->id;
                        $transaction->from_user_id = $user->id;
                        $transaction->type = 'used';
                        $transaction->point = $GoldenDurationPrice;
                        $transaction->save();

                    } else {
                        return response()->json(['error' => 'Needed ' . $ServicePostFinalBadge . ' Point, Your Balance Point not enough.'], 400);
                    };
                } elseif ($validatedData['haveBadge'] == "ماسي") {

                    $DiamondeDurationPrice = $validatedData['badgeDuration'] * 10;
                    if ($user->pointsBalance >= $DiamondeDurationPrice) {
                        $ServicePostFinalBadge = $validatedData['haveBadge'];
                        $ServicePostFinalDuration = $validatedData['badgeDuration'];
                        palservice_points::where('user_id', $user->id)->decrement('point', $DiamondeDurationPrice);
                        // Create a transaction record
                        $transaction = new point_transactions();
                        $transaction->to_user_id = $user->id;
                        $transaction->from_user_id = $user->id;
                        $transaction->type = 'used';
                        $transaction->point = $DiamondeDurationPrice;
                        $transaction->save();
                    } else {
                        return response()->json(['error' => 'Needed ' . $DiamondeDurationPrice . ' Point, Your Balance Point not enough.'], 400);
                    };
                } elseif ($validatedData['haveBadge'] == "عادي") {
                    $ServicePostFinalBadge = $validatedData['haveBadge'];
                    $ServicePostFinalDuration = $validatedData['badgeDuration'];
                } else {
                    return response()->json(['error' => 'Service Post Type not Correct.'], 400);
                }
            }
            $servicePost->have_badge = $ServicePostFinalBadge;
            $servicePost->badge_duration = $ServicePostFinalDuration;
            $servicePost->save();
            $message = "Your Post badge is changed to $ServicePostFinalBadge.";
            $notification = new Notification([
                'message' => $message,
                'user_id' => Auth::id(),
                'type'    => 'badge'
            ]);
            $user->notifications()->save($notification);
            return response()->json(['message' => 'Service Post has been updated!']);
        } catch (Exception $e) {
            return response()->json(['error' => $e]);
        }
    }
    public  function deletePhoto($servicePostImageId){
        $photo = Photos::find($servicePostImageId);
        if (Storage::disk('public')->exists($photo->src) && !in_array($photo->src, ['photos/servicepost1.jpg', 'photos/servicepost2.jpg', 'photos/servicepost3.jpg', 'photos/servicepost4.jpg', 'photos/servicepost5.jpg'])) {
            Storage::delete($photo->src);
        }
        $photo->delete();
        return response()->json(['success' => 'Service Post image!', 'id' => $servicePostImageId]);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
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
                if (Storage::disk('public')->exists($photo->src) && !in_array($photo->src, ['photos/servicepost1.jpg', 'photos/servicepost2.jpg', 'photos/servicepost3.jpg', 'photos/servicepost4.jpg', 'photos/servicepost5.jpg'])) {
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

    public function servicePostUserId(Request $request, $user): JsonResponse
    {
        $CurrentUser = Auth::user();
        $servicePosts = ServicePost::where('user_id', $user)
            ->where('state', 'published')
            ->with('photos')
            ->withCount('favorites')
            ->withCount('comments')

            ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
            ->paginate(10);
        // Fetch user photos and names for each service post
        // Fetch user photos and names for each service post
        foreach ($servicePosts as $servicePost) {
            $postUser = User::with('photos')->find($servicePost->user_id);
            $servicePost->user_photo = $postUser->photos->first() ? $postUser->photos->first()->src : null;
            $servicePost->user_name = $postUser->user_name; // Add the user's name to the response

            $favorite = $servicePost->favorites()->where('user_id', Auth::id())->first();
            $servicePost->is_favorited = (bool)$favorite;
            // Get the distance between the service post location and the current user
            $servicePost->distance = round(ServicePost::distance($CurrentUser->location_latitudes, $CurrentUser->location_longitudes, $servicePost->location_latitudes, $servicePost->location_longitudes), 2);

            // Check if the current user follows the service post user
            $follow = $CurrentUser->followers()->where('follower_id',  $postUser->id)->first();
            $servicePost->is_followed = (bool)$follow;
        }
        return response()->json(compact('servicePosts'));
    }

    public function servicePostCategory($category): JsonResponse {
        $user = Auth::id();
        $currentUser = User::find($user);
        // Check if the user has the required permissions to view all service posts
        if (!$currentUser->hasPermission('view_service')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $servicePosts = ServicePost::where('state', 'published')
            ->with('photos')
            ->withCount('comments')

            ->withCount('favorites');
        if ($category == 6) {
            $userLatitude = $currentUser->location_latitudes;
            $userLongitude = $currentUser->location_longitudes;
            $distanceExpression = (new ServicePost)->distanceExpression($userLatitude, $userLongitude);
            $servicePosts->orderByRaw($distanceExpression);
        } else {
            $servicePosts->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC");
            $servicePosts->where('categories_id', $category);
        }
        $servicePosts = $servicePosts->paginate(10);
        foreach ($servicePosts as $servicePost) {
            $postUser = User::with('photos')->find($servicePost->user_id);
            $servicePost->user_photo = $postUser->photos->first() ? $postUser->photos->first()->src : null;
            $servicePost->user_name = $postUser->user_name;
            $favorite = $servicePost->favorites()->where('user_id', $currentUser->id)->first();
            $servicePost->is_favorited = (bool) $favorite;
            $distance = ServicePost::distance($currentUser->location_latitudes, $currentUser->location_longitudes, $servicePost->location_latitudes, $servicePost->location_longitudes);
            $servicePost->distance = round($distance, 3);
            $follow = $currentUser->followers()->where('follower_id', $postUser->id)->first();
            $servicePost->is_followed = (bool) $follow;
        }
        return response()->json(compact('servicePosts'));
    }


    public function servicePostCategorySubCategory($categories, $sub_categories): JsonResponse
    {
        $user = Auth::id();
        $CurrentUser = User::find($user);
        $user = Auth::user();
        // Check if the user has the required permissions to view all service posts
        if ($user->hasPermission('view_service')) {
            // Get all categories with their corresponding subcategories and service posts count
            $servicePosts = ServicePost::where('categories_id', $categories)
                ->where('sub_categories_id', $sub_categories)
                ->where('state', 'published')
                ->with('photos', 'subCategory')
                ->withCount('comments')

                ->withCount('favorites')
                ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
                ->paginate(10);
            // Fetch user photos and names for each service post
            foreach ($servicePosts as $servicePost) {
                $postUser = User::with('photos')->find($servicePost->user_id);
                $servicePost->user_photo = $postUser->photos->first() ? $postUser->photos->first()->src : null;
                $servicePost->user_name = $postUser->user_name; // Add the user's name to the response

                $favorite = $servicePost->favorites()->where('user_id', Auth::id())->first();
                $servicePost->is_favorited = (bool)$favorite;
                // Get the distance between the service post location and the current user
                $servicePost->distance = round(ServicePost::distance($CurrentUser->location_latitudes, $CurrentUser->location_longitudes, $servicePost->location_latitudes, $servicePost->location_longitudes), 2);

                // Check if the current user follows the service post user
                $follow = $CurrentUser->followers()->where('follower_id',  $postUser->id)->first();
                $servicePost->is_followed = (bool)$follow;
            }
            return response()->json(compact('servicePosts'));
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }


    public function servicePostFavorite(User $user): JsonResponse
    {
        $user = Auth::id();
        $CurrentUser = User::find($user);
        if ($CurrentUser->hasPermission('view_service')) {
            Categories::withCount('servicePosts')
                ->orderBy('name')
                ->get();
            $servicePosts = ServicePost::whereHas('favorites', function ($query) use ($CurrentUser) {
                $query->where('user_id', $CurrentUser->id);
            })
                ->with('photos')
                ->withCount('favorites')
                ->withCount('comments')
                ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
                ->paginate(10);

            // Fetch user photos and names for each service post
            foreach ($servicePosts as $servicePost) {
                $postUser = User::with('photos')->find($servicePost->user_id);
                $servicePost->user_photo = $postUser->photos->first() ? $postUser->photos->first()->src : null;
                $servicePost->user_name = $postUser->user_name; // Add the user's name to the response
                $favorite = $servicePost->favorites()->where('user_id', Auth::id())->first();
                $servicePost->is_favorited = (bool)$favorite;
                // Get the distance between the service post location and the current user
                $servicePost->distance = round(ServicePost::distance($CurrentUser->location_latitudes, $CurrentUser->location_longitudes, $servicePost->location_latitudes, $servicePost->location_longitudes), 2);

                // Check if the current user follows the service post user
                $follow = $CurrentUser->followers()->where('follower_id',  $postUser->id)->first();
                $servicePost->is_followed = (bool)$follow;
            }
            return response()->json(compact('servicePosts'));
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }

    public function updateServicePostBadgeStatus(): JsonResponse
    {
        $servicePosts = ServicePost::whereIn('have_badge', ['ذهبي', 'ماسي'])
            ->where('created_at', '<=', now()->subHours(24))
            ->get();
        $servicePostsCount = ServicePost::whereIn('have_badge', ['ذهبي', 'ماسي'])
            ->where('created_at', '<=', now()->subHours(24))
            ->count();
        foreach ($servicePosts as $post) {
            $newDuration = $post->badge_duration - 1;
            if ($newDuration <= 0) {
                $post->have_badge = 'عادي';
                $message = "Service Post Badge changed to normal due to duration times up.";
                $notification = new Notification([
                    'message' => $message,
                    'user_id' => $post->user_id,
                    'type'    => 'badge'
                ]);
                $notification->save();
            }
            $post->badge_duration = $newDuration;
            $post->save();
        }

        return response()->json(['message' => 'Service post badge status updated successfully '.$servicePostsCount]);
    }

}
