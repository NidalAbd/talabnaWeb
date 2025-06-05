<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Notification;
use App\Models\palservice_points;
use App\Models\Photos;
use App\Models\point_transactions;
use App\Models\ServicePost;
use App\Models\Sub_categories;
use App\Models\User;
use App\Models\countries;
use App\Models\cities;
use App\Notifications\new_servicepost_notification;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ServicePostController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if the user has the required permissions to view all service posts
        if ($user->hasPermission('view_service')) {
            // Start with the base query
            $query = ServicePost::with('photos', 'user', 'category', 'subCategory', 'country', 'city');

            // Apply category filter
            if ($request->has('category') && $request->category) {
                $query->where('categories_id', $request->category);
            }

            // Apply subcategory filter
            if ($request->has('subcategory') && $request->subcategory) {
                $query->where('sub_categories_id', $request->subcategory);
            }

            // Apply status filter
            if ($request->has('status') && $request->status) {
                $query->where('state', $request->status);
            }

            // Apply type filter
            if ($request->has('type') && $request->type) {
                $query->where('type', $request->type);
            }

            // Apply search filter
            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                        ->orWhereHas('user', function($userQuery) use ($searchTerm) {
                            $userQuery->where('user_name', 'like', "%{$searchTerm}%");
                        })
                        ->orWhereHas('category', function($categoryQuery) use ($searchTerm) {
                            $categoryQuery->where('name->'.app()->getLocale(), 'like', "%{$searchTerm}%")
                                ->orWhere('name->en', 'like', "%{$searchTerm}%");
                        });
                });
            }

            // Order and paginate
            $servicePosts = $query->orderBy('id', 'desc')
                ->paginate(100)
                // Preserve query parameters in pagination links
                ->appends($request->query());

            // Fetch categories and subcategories for dropdowns
            $categories = Categories::all();
            $subcategories = $request->has('category')
                ? Sub_categories::where('categories_id', $request->category)->get()
                : collect();

            return view('service_posts.index', compact('servicePosts', 'user', 'categories', 'subcategories'));
        } else {
            return redirect()->route('errors.403');
        }
    }

    public function create()
    {
        $user = Auth::user();
        // Check if the user has the required permissions to create a service post
        if (!$user->hasPermission('create_service')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $categories = Categories::where('isSuspended', false)->get();
        $subcategories = Sub_categories::where('isSuspended', false)->get();
        $countries = countries::all();
        $cities = cities::all();

        return view('service_posts.create', compact('categories', 'subcategories', 'countries', 'cities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Define default coordinates
        $defaultLatitude = 31.9539;
        $defaultLongitude = 35.2376;

        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'categories_id' => 'required|exists:categories,id',
            'sub_categories_id' => 'required|exists:sub_categories,id',
            'price' => 'nullable|numeric',
            'price_currency_code' => 'nullable',
            'country_id' => 'nullable|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
            'location_latitudes' => 'nullable|numeric', // Changed from required to nullable
            'location_longitudes' => 'nullable|numeric', // Changed from required to nullable
            'type' => 'required|in:عرض,طلب',
            'have_badge' => 'nullable|in:عادي,ذهبي,ماسي',
            'badge_duration' => 'nullable|integer',
            'state' => 'nullable|in:published,archive,not published,rejected',
            'images.*' => 'nullable|file|mimes:jpeg,jpg,png,mp4',
        ]);

        // Get category and subcategory
        $category = Categories::findOrFail($validatedData['categories_id']);
        $subCategory = Sub_categories::findOrFail($validatedData['sub_categories_id']);

        // Initialize badge variables
        $ServicePostFinalBadge = 'عادي';
        $ServicePostFinalDuration = 0;

        // Handle badge and points if selected
        if (isset($validatedData['have_badge']) && $validatedData['have_badge'] !== 'عادي') {
            // Golden badge
            if ($validatedData['have_badge'] === "ذهبي") {
                $GoldenDurationPrice = $validatedData['badge_duration'] * 1;
                if ($user->pointsBalance >= $GoldenDurationPrice) {
                    $ServicePostFinalBadge = $validatedData['have_badge'];
                    $ServicePostFinalDuration = $validatedData['badge_duration'];

                    // Decrement user points
                    palservice_points::where('user_id', $user->id)->decrement('point', $GoldenDurationPrice);

                    // Create a transaction record
                    $transaction = new point_transactions();
                    $transaction->to_user_id = $user->id;
                    $transaction->from_user_id = $user->id;
                    $transaction->type = 'used';
                    $transaction->point = $GoldenDurationPrice;
                    $transaction->save();
                } else {
                    return redirect()->route('service_posts.create')
                        ->with('error', 'Needed ' . $GoldenDurationPrice . ' Point, Your Balance Point not enough.');
                }
            }
            // Diamond badge
            elseif ($validatedData['have_badge'] === "ماسي") {
                $DiamondDurationPrice = $validatedData['badge_duration'] * 3;
                if ($user->pointsBalance >= $DiamondDurationPrice) {
                    $ServicePostFinalBadge = $validatedData['have_badge'];
                    $ServicePostFinalDuration = $validatedData['badge_duration'];

                    // Decrement user points
                    palservice_points::where('user_id', $user->id)->decrement('point', $DiamondDurationPrice);

                    // Create a transaction record
                    $transaction = new point_transactions();
                    $transaction->to_user_id = $user->id;
                    $transaction->from_user_id = $user->id;
                    $transaction->type = 'used';
                    $transaction->point = $DiamondDurationPrice;
                    $transaction->save();
                } else {
                    return redirect()->route('service_posts.create')
                        ->with('error', 'Needed ' . $DiamondDurationPrice . ' Point, Your Balance Point not enough.');
                }
            }
        } elseif (isset($validatedData['have_badge']) && $validatedData['have_badge'] === "عادي") {
            $ServicePostFinalBadge = $validatedData['have_badge'];
            $ServicePostFinalDuration = $validatedData['badge_duration'] ?? 0;
        }

        // Create the service post
        $servicePost = new ServicePost();
        $servicePost->title = $validatedData['title'];
        $servicePost->description = $validatedData['description'];
        $servicePost->price = $validatedData['price'] ?? 0;
        $servicePost->price_currency_code = $validatedData['price_currency_code'] ?? 'USD';

        // Set currency name based on country
        if (isset($validatedData['country_id'])) {
            $country = countries::find($validatedData['country_id']);
            if ($country) {
                $servicePost->price_currency_name = [
                    'ar' => $country->currency_name_ar ?? 'دولار امريكي',
                    'en' => $country->currency_name_en ?? 'US Dollar'
                ];
            }
        }

        $servicePost->country_id = $validatedData['country_id'] ?? null;
        $servicePost->city_id = $validatedData['city_id'] ?? null;

        // Use default values if location coordinates are null or not provided
        $servicePost->location_latitudes = $user->location_latitudes ?? $defaultLatitude;
        $servicePost->location_longitudes = $user->location_longitudes ?? $defaultLongitude;


        // If you want to try using user's stored location when coordinates aren't provided
        if (!isset($validatedData['location_latitudes']) || $validatedData['location_latitudes'] === null) {
            // Try to get user's saved location first, fall back to default if not available
            $servicePost->location_latitudes = $user->location_latitudes ?? $defaultLatitude;
        }

        if (!isset($validatedData['location_longitudes']) || $validatedData['location_longitudes'] === null) {
            // Try to get user's saved location first, fall back to default if not available
            $servicePost->location_longitudes = $user->location_longitudes ?? $defaultLongitude;
        }

        $servicePost->type = $validatedData['type'];
        $servicePost->have_badge = $ServicePostFinalBadge;
        $servicePost->badge_duration = $ServicePostFinalDuration;
        $servicePost->state = $validatedData['state'] ?? 'published';
        $servicePost->user_id = $user->id;
        $servicePost->categories_id = $validatedData['categories_id'];
        $servicePost->sub_categories_id = $validatedData['sub_categories_id'];
        $servicePost->save();

        // Rest of your function for handling images and notifications remains the same
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $photo) {
                // Generate a unique filename using hash
                $fileName = $photo->hashName();

                // Determine the storage path for the file (without 'storage/')
                $storagePath = 'photos/posts';

                // Store the file in the public disk
                $photoPath = $photo->storeAs($storagePath, $fileName, 'public');

                // Create the full path for database storage
                $databasePath = 'storage/' . $photoPath;

                // Check if the file is a video (MP4)
                $isVideo = $photo->getClientOriginalExtension() === 'mp4';

                // Log the file type
                Log::info($isVideo ? "Is Video: true" : "Is not Video: false");

                // Create the photo/video entry
                $servicePost->photos()->create([
                    'src' => $databasePath,
                    'isVideo' => $isVideo ? 1 : 0,
                ]);
            }
        }

        // Send notifications about the new service post
        $this->servicePostNotification($servicePost, $user);

        return redirect()->route('service_posts.create')
            ->with('success', 'Service post created successfully.');
    }
    public function servicePostNotification(ServicePost $servicePost, User $user): void
    {
        // Retrieve followers of the user who created the post
        $followers = $user->followers()->get();

        // Retrieve followers of the subcategory
        $subcategory = Sub_categories::find($servicePost->sub_categories_id);
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

    public function show(Request $request, ServicePost $servicePost)
    {
        // Only admin and moderator can view all service posts
        $user = Auth::user();

        // Check if the user has the required permissions to view a service post
        if ($user->hasPermission('show_service')) {
            $servicePost->load('photos', 'user', 'category', 'subCategory', 'country', 'city');
            return view('service_posts.show', compact('servicePost'));
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }


    public function edit(ServicePost $servicePost)
    {
        $user = Auth::user();

        // Check if the user has the required permissions to edit a service post
        if ($user->hasPermission('edit_service')) {
            $servicePost->load('photos', 'user', 'country', 'city');
            $categories = Categories::where('isSuspended', false)->get();
            $subcategories = Sub_categories::where('isSuspended', false)->get();
            $countries = countries::all();
            $cities = cities::all();

            return view('service_posts.edit', compact('servicePost', 'categories', 'subcategories', 'countries', 'cities'));
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $user = Auth::user();
        $servicePost = ServicePost::findOrFail($id);
        $defaultLatitude = 31.9539;
        $defaultLongitude = 35.2376;
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'categories_id' => 'nullable|exists:categories,id',
            'sub_categories_id' => 'nullable|exists:sub_categories,id',
            'price' => 'nullable|numeric',
            'price_currency_code' => 'nullable',
            'country_id' => 'nullable|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
            'location_latitudes' => 'nullable|numeric',
            'location_longitudes' => 'nullable|numeric',
            'type' => 'nullable|in:عرض,طلب',
            'view_count' => 'nullable|integer',
            'state' => 'nullable|in:published,archive,not published,rejected',
            'images.*' => 'nullable|file|mimes:jpeg,jpg,png,mp4',
            'photos.*' => 'nullable|file|mimetypes:image/jpeg,image/png,image/heic,image/heif,audio/mpeg,audio/mpeg3,audio/mp3,video/mp4,video/*',
            'delete_photos.*' => 'nullable|integer|exists:photos,id',
        ]);

        if (isset($validatedData['categories_id'])) {
            $servicePost->categories_id = $validatedData['categories_id'];
            // Remove the category name assignment or add the column to your database
        }

        if (isset($validatedData['sub_categories_id'])) {
            $servicePost->sub_categories_id = $validatedData['sub_categories_id'];
            // Remove the sub_category name assignment or add the column to your database
        }

        $servicePost->title = $validatedData['title'] ?? $servicePost->title;
        $servicePost->description = $validatedData['description'] ?? $servicePost->description;
        $servicePost->price = $validatedData['price'] ?? $servicePost->price;

        if (isset($validatedData['price_currency_code'])) {
            $servicePost->price_currency_code = $validatedData['price_currency_code'];
        }

        if (isset($validatedData['country_id'])) {
            $servicePost->country_id = $validatedData['country_id'];
            $country = countries::find($validatedData['country_id']);
            if ($country) {
                $servicePost->price_currency_name = [
                    'ar' => $country->currency_name_ar ?? 'دولار امريكي',
                    'en' => $country->currency_name_en ?? 'US Dollar'
                ];
            }
        }

        $servicePost->city_id = $validatedData['city_id'] ?? $servicePost->city_id;
        // Use values from the request if provided, otherwise keep existing values
        $servicePost->location_latitudes = $validatedData['location_latitudes'] ?? $servicePost->location_latitudes;
        $servicePost->location_longitudes = $validatedData['location_longitudes'] ?? $servicePost->location_longitudes;
        $servicePost->type = $validatedData['type'] ?? $servicePost->type;
        $servicePost->state = $validatedData['state'] ?? $servicePost->state;

        if (isset($validatedData['view_count'])) {
            $servicePost->view_count = $validatedData['view_count'];
        }

        $servicePost->save();

        // Delete photos if requested
        if ($request->has('delete_photos')) {
            foreach ($request->delete_photos as $photoId) {
                $photo = Photos::find($photoId);
                if ($photo) {
                    Storage::delete($photo->src);
                    $photo->delete();
                }
            }
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $photo) {
                // Generate a unique filename using hash
                $fileName = $photo->hashName();

                // Determine the storage path for the file (without 'storage/')
                $storagePath = 'photos/posts';

                // Store the file in the public disk
                $photoPath = $photo->storeAs($storagePath, $fileName, 'public');

                // Create the full path for database storage
                $databasePath = 'storage/' . $photoPath;

                // Check if the file is a video (MP4)
                $isVideo = $photo->getClientOriginalExtension() === 'mp4';

                // Log the file type
                Log::info($isVideo ? "Is Video: true" : "Is not Video: false");

                // Create the photo/video entry
                $servicePost->photos()->create([
                    'src' => $databasePath,
                    'isVideo' => $isVideo ? 1 : 0,
                ]);
            }
        }
        return redirect()->route('service_posts.edit', $id)
            ->with('success', 'Service Post has been updated!');
    }

    public function destroy(ServicePost $servicePost): RedirectResponse
    {
        $user = Auth::user();

        // Check if the user has the required permissions to delete a service post
        if (!$user->hasPermission('destroy_service')) {
            return redirect()->route('errors.403');
        }

        // Delete the associated photos with the post
        foreach ($servicePost->photos as $photo) {
            if (Storage::disk('public')->exists($photo->src) &&
                !in_array($photo->src, [
                    'storage/photos/servicepost1.jpg',
                    'storage/photos/servicepost2.jpg',
                    'storage/photos/servicepost3.jpg',
                    'storage/photos/servicepost4.jpg',
                    'storage/photos/servicepost5.jpg'
                ])) {
                Storage::delete($photo->src);
                $photo->delete();
            }
        }

        // Delete all favorites and reports for this service post
        $servicePost->favorites()->delete();
        $servicePost->reports()->delete();

        // Delete the service post
        $servicePost->delete();

        return redirect()->back()->with('success', 'Selected Service Post has been deleted!');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Check if the user has the required permissions to delete service posts
        if (!$user->hasPermission('destroy_service')) {
            return redirect()->route('errors.403');
        }

        $postIds = json_decode($request->post_ids, true);

        if (empty($postIds)) {
            return redirect()->back()->with('error', 'No posts selected for deletion.');
        }

        // Retrieve all posts that will be deleted
        $posts = ServicePost::whereIn('id', $postIds)->get();
        $count = $posts->count();

        foreach ($posts as $post) {
            // Delete the associated photos with the post
            foreach ($post->photos as $photo) {
                if (Storage::disk('public')->exists(str_replace('storage/', '', $photo->src)) &&
                    !in_array($photo->src, [
                        'storage/photos/servicepost1.jpg',
                        'storage/photos/servicepost2.jpg',
                        'storage/photos/servicepost3.jpg',
                        'storage/photos/servicepost4.jpg',
                        'storage/photos/servicepost5.jpg'
                    ])) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $photo->src));
                }
                $photo->delete();
            }

            // Delete all favorites and reports for this service post
            $post->favorites()->delete();
            $post->reports()->delete();

            // Delete the service post
            $post->delete();
        }

        return redirect()->back()->with('success', $count . ' service posts have been deleted successfully!');
    }
    public function inViewCount(ServicePost $servicePost): JsonResponse
    {
        // Increment the view count for this service post
        $servicePost->incrementViewCount();
        return response()->json('Post Viewed Counted');
    }

    public function jobIndex(Request $request)
    {
        $user = Auth::user();

        // Check if the user has the required permissions to view service posts
        if ($user->hasPermission('view_service')) {
            $category = Categories::where(function($query) {
                $query->where('name->ar', 'وظائف')
                    ->orWhere('name->en', 'Jobs');
            })->first();

            if ($category) {
                $servicePosts = ServicePost::where('categories_id', $category->id)
                    ->with('photos', 'user', 'country', 'city')
                    ->paginate(10);

                return view('service_posts.job_index', compact('servicePosts', 'user', 'category'));
            } else {
                return redirect()->back()->with('error', 'Job category not found');
            }
        } else {
            return redirect()->route('errors.403');
        }
    }

    public function carIndex(Request $request)
    {
        $user = Auth::user();

        // Check if the user has the required permissions to view service posts
        if ($user->hasPermission('view_service')) {
            $category = Categories::where(function($query) {
                $query->where('name->ar', 'سيارات')
                    ->orWhere('name->en', 'Cars');
            })->first();

            if ($category) {
                $servicePosts = ServicePost::where('categories_id', $category->id)
                    ->with('photos', 'user', 'country', 'city')
                    ->paginate(10);

                return view('service_posts.car_index', compact('servicePosts', 'user', 'category'));
            } else {
                return redirect()->back()->with('error', 'Car category not found');
            }
        } else {
            return redirect()->route('errors.403');
        }
    }

    public function phoneIndex(Request $request)
    {
        $user = Auth::user();

        // Check if the user has the required permissions to view service posts
        if ($user->hasPermission('view_service')) {
            $category = Categories::where(function($query) {
                $query->where('name->ar', 'اجهزة')
                    ->orWhere('name->en', 'Devices');
            })->first();

            if ($category) {
                $servicePosts = ServicePost::where('categories_id', $category->id)
                    ->with('photos', 'user', 'country', 'city')
                    ->paginate(10);

                return view('service_posts.phone_index', compact('servicePosts', 'user', 'category'));
            } else {
                return redirect()->back()->with('error', 'Device category not found');
            }
        } else {
            return redirect()->route('errors.403');
        }
    }

    public function realStatIndex(Request $request)
    {
        $user = Auth::user();

        // Check if the user has the required permissions to view service posts
        if ($user->hasPermission('view_service')) {
            $category = Categories::where(function($query) {
                $query->where('name->ar', 'عقارات')
                    ->orWhere('name->en', 'Houses');
            })->first();

            if ($category) {
                $servicePosts = ServicePost::where('categories_id', $category->id)
                    ->with('photos', 'user', 'country', 'city')
                    ->paginate(10);

                return view('service_posts.real_state_index', compact('servicePosts', 'user', 'category'));
            } else {
                return redirect()->back()->with('error', 'Real Estate category not found');
            }
        } else {
            return redirect()->route('errors.403');
        }
    }

    public function generalIndex(Request $request)
    {
        $user = Auth::user();

        // Check if the user has the required permissions to view service posts
        if ($user->hasPermission('view_service')) {
            $category = Categories::where(function($query) {
                $query->where('name->ar', 'خدمات')
                    ->orWhere('name->en', 'Services');
            })->first();

            if ($category) {
                $servicePosts = ServicePost::where('categories_id', $category->id)
                    ->with('photos', 'user', 'country', 'city')
                    ->paginate(10);

                return view('service_posts.general_index', compact('servicePosts', 'user', 'category'));
            } else {
                return redirect()->back()->with('error', 'Services category not found');
            }
        } else {
            return redirect()->route('errors.403');
        }
    }

    public function userIndex(Request $request)
    {
        $user = Auth::user();

        // Check if the user has the required permissions to view service posts
        if ($user->hasPermission('view_service')) {
            // Base query
            $query = ServicePost::with('photos', 'user', 'category', 'subCategory', 'country', 'city')
                ->where('state', 'published')
                ->withCount('favorites');

            // Category filter
            if ($request->filled('category')) {
                $categoryModel = Categories::findOrFail($request->category);
                $query->where('categories_id', $categoryModel->id);
            }

            // Subcategory filter (only if category is selected)
            if ($request->filled('category') && $request->filled('subcategory')) {
                $query->where('sub_categories_id', $request->subcategory);
            }

            // Type filter
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            // Price range filter
            if ($request->filled('min_price') || $request->filled('max_price')) {
                if ($request->filled('min_price')) {
                    $query->where('price', '>=', $request->min_price);
                }
                if ($request->filled('max_price')) {
                    $query->where('price', '<=', $request->max_price);
                }
            }

            // Search filter
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                        ->orWhere('description', 'like', "%{$searchTerm}%")
                        ->orWhereHas('user', function($userQuery) use ($searchTerm) {
                            $userQuery->where('user_name', 'like', "%{$searchTerm}%");
                        })
                        ->orWhereHas('category', function($categoryQuery) use ($searchTerm) {
                            $categoryQuery->where('name->'.app()->getLocale(), 'like', "%{$searchTerm}%")
                                ->orWhere('name->en', 'like', "%{$searchTerm}%");
                        });
                });
            }

            // Country filter
            if ($request->filled('country')) {
                $query->where('country_id', $request->country);
            }

            // City filter (only if country is selected)
            if ($request->filled('country') && $request->filled('city')) {
                $query->where('city_id', $request->city);
            }

            // Sorting
            switch ($request->input('sort')) {
                case 'newest':
                    $query->orderByDesc('created_at');
                    break;
                case 'oldest':
                    $query->orderBy('created_at');
                    break;
                case 'price_low':
                    $query->orderBy('price');
                    break;
                case 'price_high':
                    $query->orderByDesc('price');
                    break;
                case 'most_viewed':
                    $query->orderByDesc('view_count');
                    break;
                default:
                    // Default sorting with badge priority
                    $query->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC");
            }

            // Pagination
            $servicePosts = $query->paginate(10)
                ->appends($request->query());

            // Fetch categories for dropdown
            $categories = Categories::withCount(['sub_categories', 'servicePosts'])
                ->where('isSuspended', false)
                ->get();

            // Fetch subcategories for dropdown
            $subcategories = $request->filled('category')
                ? Sub_categories::where('categories_id', $request->category)
                    ->where('isSuspended', false)
                    ->get()
                : collect();

            return view('service_posts.post_page', compact('servicePosts', 'user', 'categories', 'subcategories'));
        } else {
            return redirect()->route('errors.403');
        }
    }
    public function indexProfile(User $user)
    {
        $categories = Categories::withCount('servicePosts')
            ->where('isSuspended', false)
            ->orderBy('id', 'desc')
            ->get();

        $servicePosts = $user->servicePosts()
            ->with('photos', 'user', 'category', 'subCategory', 'country', 'city')
            ->withCount('favorites')
            ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('service_posts.post_page', compact('servicePosts', 'user', 'categories'));
    }

    public function postProfile(User $user)
    {
        $servicePosts = $user->servicePosts()
            ->with('photos', 'user', 'category', 'subCategory', 'country', 'city')
            ->withCount('favorites')
            ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('service_posts.index_post_profile', compact('servicePosts', 'user'));
    }

    public function favoritesIndex(User $user)
    {
        // Check if the user has the required permissions to view service posts
        if ($user->hasPermission('view_service')) {
            $categories = Categories::withCount('servicePosts')
                ->where('isSuspended', false)
                ->orderBy('id', 'desc')
                ->get();

            $servicePosts = ServicePost::whereHas('favorites', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->with('photos', 'user', 'category', 'subCategory', 'country', 'city')
                ->withCount('favorites')
                ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
                ->paginate(10);

            return view('service_posts.post_page', compact('servicePosts', 'user', 'categories'));
        } else {
            return redirect()->route('errors.403');
        }
    }

    public function userCarIndex(Request $request, $category)
    {
        $user = Auth::user();

        // Check if the user has the required permissions to view service posts
        if ($user->hasPermission('view_service')) {
            $categoryModel = Categories::where(function($query) use ($category) {
                $query->where('name->ar', $category)
                    ->orWhere('name->en', $category);
            })->first();

            if (!$categoryModel) {
                return redirect()->back()->with('error', 'Category not found');
            }

            $servicePosts = ServicePost::where('categories_id', $categoryModel->id)
                ->where('state', 'published')
                ->with('photos', 'user', 'category', 'subCategory', 'country', 'city')
                ->withCount('favorites')
                ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
                ->paginate(10);

            $subcategories = Sub_categories::where('categories_id', $categoryModel->id)
                ->where('isSuspended', false)
                ->get();

            $categories = Categories::where('isSuspended', false)->get();

            return view('service_posts.post_page', compact('servicePosts', 'user', 'categories', 'category', 'subcategories'));
        } else {
            return redirect()->route('errors.403');
        }
    }

    public function userPhoneIndex(Request $request, $category)
    {
        $user = Auth::user();

        // Check if the user has the required permissions to view service posts
        if ($user->hasPermission('view_service')) {
            $categoryModel = Categories::where(function($query) use ($category) {
                $query->where('name->ar', $category)
                    ->orWhere('name->en', $category);
            })->first();

            if (!$categoryModel) {
                return redirect()->back()->with('error', 'Category not found');
            }

            $servicePosts = ServicePost::where('categories_id', $categoryModel->id)
                ->where('state', 'published')
                ->with('photos', 'user', 'category', 'subCategory', 'country', 'city')
                ->withCount('favorites')
                ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
                ->paginate(10);

            $subcategories = Sub_categories::where('categories_id', $categoryModel->id)
                ->where('isSuspended', false)
                ->get();

            $categories = Categories::where('isSuspended', false)->get();

            return view('service_posts.post_page', compact('servicePosts', 'user', 'categories', 'category', 'subcategories'));
        } else {
            return redirect()->route('errors.403');
        }
    }

    public function userRealStatIndex(Request $request, $category)
    {
        $user = Auth::user();

        // Check if the user has the required permissions to view service posts
        if ($user->hasPermission('view_service')) {
            $categoryModel = Categories::where(function($query) use ($category) {
                $query->where('name->ar', $category)
                    ->orWhere('name->en', $category);
            })->first();

            if (!$categoryModel) {
                return redirect()->back()->with('error', 'Category not found');
            }

            $servicePosts = ServicePost::where('categories_id', $categoryModel->id)
                ->where('state', 'published')
                ->with('photos', 'user', 'category', 'subCategory', 'country', 'city')
                ->withCount('favorites')
                ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
                ->paginate(10);

            $subcategories = Sub_categories::where('categories_id', $categoryModel->id)
                ->where('isSuspended', false)
                ->get();

            $categories = Categories::where('isSuspended', false)->get();

            return view('service_posts.post_page', compact('servicePosts', 'user', 'categories', 'category', 'subcategories'));
        } else {
            return redirect()->route('errors.403');
        }
    }

    public function userGeneralIndex(Request $request, $category)
    {
        $user = Auth::user();

        // Check if the user has the required permissions to view service posts
        if ($user->hasPermission('view_service')) {
            $categoryModel = Categories::where(function($query) use ($category) {
                $query->where('name->ar', $category)
                    ->orWhere('name->en', $category);
            })->first();

            if (!$categoryModel) {
                return redirect()->back()->with('error', 'Category not found');
            }

            $servicePosts = ServicePost::where('categories_id', $categoryModel->id)
                ->where('state', 'published')
                ->with('photos', 'user', 'category', 'subCategory', 'country', 'city')
                ->withCount('favorites')
                ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
                ->paginate(10);

            $subcategories = Sub_categories::where('categories_id', $categoryModel->id)
                ->where('isSuspended', false)
                ->get();

            $categories = Categories::where('isSuspended', false)->get();

            return view('service_posts.post_page', compact('servicePosts', 'user', 'categories', 'category', 'subcategories'));
        } else {
            return redirect()->route('errors.403');
        }
    }

    public function userJobIndex(Request $request, $category)
    {
        $user = Auth::user();

        // Check if the user has the required permissions to view service posts
        if ($user->hasPermission('view_service')) {
            $categoryModel = Categories::where(function($query) use ($category) {
                $query->where('name->ar', $category)
                    ->orWhere('name->en', $category);
            })->first();

            if (!$categoryModel) {
                return redirect()->back()->with('error', 'Category not found');
            }

            $servicePosts = ServicePost::where('categories_id', $categoryModel->id)
                ->where('state', 'published')
                ->with('photos', 'user', 'category', 'subCategory', 'country', 'city')
                ->withCount('favorites')
                ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
                ->paginate(10);

            $subcategories = Sub_categories::where('categories_id', $categoryModel->id)
                ->where('isSuspended', false)
                ->get();

            $categories = Categories::where('isSuspended', false)->get();

            return view('service_posts.post_page', compact('servicePosts', 'user', 'categories', 'category', 'subcategories'));
        } else {
            return redirect()->route('errors.403');
        }
    }

    public function servicePostCategorySubCategory(Request $request, $category, $sub_categories)
    {
        $categoriesInput = $request->input('categories');
        $subCategoriesInput = $request->input('sub_categories');

        $categoryModel = Categories::where(function($query) use ($categoriesInput) {
            $query->where('name->ar', $categoriesInput)
                ->orWhere('name->en', $categoriesInput);
        })->first();

        $subcategoryModel = Sub_categories::where(function($query) use ($subCategoriesInput) {
            $query->where('name->ar', $subCategoriesInput)
                ->orWhere('name->en', $subCategoriesInput);
        })->first();

        $currentUser = Auth::user();

        // Check if the user has the required permissions to view service posts
        if ($currentUser->hasPermission('view_service')) {
            $servicePosts = ServicePost::where('categories_id', $categoryModel->id)
                ->where('sub_categories_id', $subcategoryModel->id)
                ->where('state', 'published')
                ->with('photos', 'user', 'category', 'subCategory', 'country', 'city')
                ->withCount('favorites')
                ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
                ->paginate(10);

            // Fetch user photos and names for each service post
            foreach ($servicePosts as $servicePost) {
                $postUser = User::with('photos')->find($servicePost->user_id);
                $servicePost->user_photo = $postUser->photos->first() ? $postUser->photos->first()->src : null;
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

            $categories = Categories::where('isSuspended', false)->get();

            return view('service_posts.post_page', compact('servicePosts', 'currentUser', 'categories'));
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }

    public function getSubCategories(Request $request, $category)
    {
        $categoryModel = Categories::where(function($query) use ($category) {
            $query->where('name->ar', $category)
                ->orWhere('name->en', $category);
        })->first();

        if ($categoryModel) {
            $subCategories = Sub_categories::where('categories_id', $categoryModel->id)
                ->where('isSuspended', false)
                ->withCount('servicePosts')
                ->get();

            return response()->json($subCategories);
        } else {
            return response()->json(['error' => 'No data found in sub category']);
        }
    }
    public function getServicePosts(Request $request, $subCategory)
    {
        $subcategoryModel = Sub_categories::where(function($query) use ($subCategory) {
            $query->where('name->ar', $subCategory)
                ->orWhere('name->en', $subCategory);
        })->first();

        if (!$subcategoryModel) {
            return response()->json(['error' => 'Subcategory not found']);
        }

        $servicePosts = ServicePost::where('sub_categories_id', $subcategoryModel->id)
            ->with('photos', 'user', 'category', 'subCategory', 'country', 'city')
            ->withCount('favorites')
            ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
            ->paginate(10);

        return response()->json($servicePosts);
    }

    public function fetchSubcategories(Request $request)
    {
        $categoryId = $request->input('category_id');

        if (!$categoryId) {
            return response()->json([]);
        }

        $subcategories = Sub_categories::where('categories_id', $categoryId)
            ->where('isSuspended', false)
            ->get();

        // Transform the data to include localized names if needed
        $subcategories = $subcategories->map(function($subcategory) {
            return [
                'id' => $subcategory->id,
                'name' => $subcategory->name,
                // Add any other fields you need here
            ];
        });

        return response()->json($subcategories);
    }
    public function CountriesSelected($countryId)
    {
        try {
            $cities = cities::where('country_id', $countryId)->get();
            return response()->json($cities);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getCitiesForForm($countryId)
    {
        $cities = cities::where('country_id', $countryId)->get();

        // Transform the cities data to decode JSON name strings if needed
        $cities = $cities->map(function($city) {
            // If name is a JSON string, decode it
            if (is_string($city->name) && json_decode($city->name) !== null) {
                $city->name = json_decode($city->name, true);
            }
            return $city;
        });

        return response()->json($cities);
    }
}
