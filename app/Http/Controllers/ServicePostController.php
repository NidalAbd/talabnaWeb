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
use App\Notifications\new_servicepost_notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ServicePostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        // Check if the user has the required permissions to view all service posts
        if ($user->hasPermission('view_service')) {
            $servicePosts = ServicePost::paginate(7);
            return view('service_posts.index', compact('servicePosts', 'user'));
        } else {
            return view('errors.403');
        }
        // Get all service posts and return them in a view
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        // Check if the user has the required permissions to create a service post
        if (!$user->hasPermission('create_service')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $categories = Categories::all();
        $subcategories = Sub_categories::all();
        return view('service_posts.create', compact('categories', 'subcategories'));

    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $user = Auth::user();
    $validatedData = $request->validate([
        'title' => 'required|max:255',
        'description' => 'required',
        'category' => 'required|exists:categories,id',
        'sub_category' => 'nullable|exists:sub_categories,id',
        'price' => 'nullable|numeric',
        'price_currency' => 'nullable',
        'location_latitudes' => 'required|numeric',
        'location_longitudes' => 'required|numeric',
        'type' => 'required',
        'have_badge' => 'nullable',
        'badge_duration' => 'nullable',
        'state' => 'nullable',
        'photos.*' => 'nullable|file|mimetypes:image/jpeg,image/png,image/heic,image/heif,audio/mpeg,audio/mpeg3,audio/mp3,video/mp4,video/*',
    ]);

    Log::info("Request: " . $validatedData['sub_category']);
    $category = Categories::find($validatedData['category']);
    $subCategory = $validatedData['sub_category'] ? Sub_categories::find($validatedData['sub_category']) : null;
    $ServicePostFinalBadge = '';
    $ServicePostFinalDuration = '';

    $servicePost = new ServicePost;
    if ($validatedData['have_badge'] !== null) {
        if ($validatedData['have_badge'] === "ذهبي") {
            $GoldenDurationPrice = $validatedData['badge_duration'] * 1;
            if ($user->pointsBalance >= $GoldenDurationPrice) {
                $ServicePostFinalBadge = $validatedData['have_badge'];
                $ServicePostFinalDuration = $validatedData['badge_duration'];
                palservice_points::where('user_id', $user->id)->decrement('point', $GoldenDurationPrice);

                // Create a transaction record
                $transaction = new point_transactions();
                $transaction->to_user_id = $user->id;
                $transaction->from_user_id = $user->id;
                $transaction->type = 'used';
                $transaction->point = $GoldenDurationPrice;
                $transaction->save();
            } else {
                return redirect()->route('service_posts.create')->with('error', 'Needed ' . $GoldenDurationPrice . ' Point, Your Balance Point not enough.');
            }
        } elseif ($validatedData['have_badge'] === "ماسي") {
            $DiamondeDurationPrice = $validatedData['badge_duration'] * 3;
            if ($user->pointsBalance >= $DiamondeDurationPrice) {
                $ServicePostFinalBadge = $validatedData['have_badge'];
                $ServicePostFinalDuration = $validatedData['badge_duration'];
                palservice_points::where('user_id', $user->id)->decrement('point', $DiamondeDurationPrice);

                // Create a transaction record
                $transaction = new point_transactions();
                $transaction->to_user_id = $user->id;
                $transaction->from_user_id = $user->id;
                $transaction->type = 'used';
                $transaction->point = $DiamondeDurationPrice;
                $transaction->save();
            } else {
                return redirect()->route('service_posts.create')->with('error', 'Needed ' . $DiamondeDurationPrice . ' Point, Your Balance Point not enough.');
            }
        } elseif ($validatedData['have_badge'] === "عادي") {
            $ServicePostFinalBadge = $validatedData['have_badge'];
            $ServicePostFinalDuration = $validatedData['badge_duration'];
        } else {
            return redirect()->route('service_posts.create')->with('error', 'badge type not correct.');
        }
    }

    $servicePost->title = $validatedData['title'];
    $servicePost->description = $validatedData['description'];
    $servicePost->category = $category->name;
    $servicePost->sub_category = $subCategory ? $subCategory->name : null;
    $servicePost->price = $validatedData['price'];
    $servicePost->price_currency = $validatedData['price_currency'];
    $servicePost->location_latitudes = $validatedData['location_latitudes'];
    $servicePost->location_longitudes = $validatedData['location_longitudes'];
    $servicePost->type = $validatedData['type'];
    $servicePost->have_badge = $ServicePostFinalBadge;
    $servicePost->badge_duration = $ServicePostFinalDuration;
    $servicePost->state = $validatedData['state'];
    $servicePost->user_id = $user->id;
    $servicePost->categories_id = $validatedData['category'];
    $servicePost->sub_categories_id = $subCategory ? $subCategory->id : null;
    $servicePost->save();

    if ($request->hasFile('photos')) {
        foreach ($request->file('photos') as $photo) {
            $photoPath = $photo->store('photos/posts');
            $servicePost->photos()->create([
                'src' => $photoPath,
            ]);
        }
    } else {
        return 'no file selected';
    }

    $this->servicePostNotification($servicePost, $user);
    return redirect()->route('service_posts.create', $servicePost->id)->with('success', 'Service post created successfully.');
}

    public function servicePostNotification($servicePost, $user): void
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
    /**
     * Display the specified resource.
     */
    public function show(Request $request, ServicePost $servicePost)
    {
        // Only admin and moderator can view all service posts
        $user = Auth::user();
        // Check if the user has the required permissions to view a service post
        if ($user->hasPermission('show_service')) {
            $servicePost->load('photos');
            return view('service_posts.show', compact('servicePost'));
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        // Load the photos for the service post using the morph relationship
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServicePost $servicePost)
    {
        $user = Auth::user();
        // Check if the user has the required permissions to edit a service post
        if ($user->hasPermission('edit_service')) {
            $servicePost->load('photos');
            $categories = Categories::all();
            $subcategories = Sub_categories::all();
            return view('service_posts.edit', compact('servicePost' , 'categories', 'subcategories'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $servicePost = ServicePost::findOrFail($id);
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'category' => 'nullable',
            'sub_category' => 'nullable',
            'price' => 'nullable|integer',
            'price_currency' => 'nullable',
            'location_latitudes' => 'nullable|numeric',
            'location_longitudes' => 'nullable|numeric',
            'type' => 'nullable',
            'view_num' => 'nullable|integer',
            'state' => 'nullable',
            'photos' => 'nullable|array',
            'photos.*' => 'nullable|file|mimetypes:image/jpeg,image/png,image/heic,image/heif,audio/mpeg,audio/mpeg3,audio/mp3,video/mp4,video/*',
            'delete_photos.*' => 'nullable|integer|exists:photos,id',
        ]);
        $category = Categories::where('name' , $validatedData['category'])->first();
        $subCategory = Sub_categories::where('name' ,$validatedData['sub_category'])->first();
        $servicePost->title = $validatedData['title'] ?? $servicePost->title;
        $servicePost->description = $validatedData['description'] ?? $servicePost->description;
        $servicePost->category = $category->name ?? $servicePost->category;
        $servicePost->sub_category = $subCategory->name ?? $servicePost->sub_category;
        $servicePost->price = $validatedData['price'] ?? $servicePost->price;
        $servicePost->price_currency = $validatedData['price_currency'] ?? $servicePost->price_currency;
        $servicePost->location_latitudes = $validatedData['location_latitudes'] ?? $servicePost->location_latitudes;
        $servicePost->location_longitudes = $validatedData['location_longitudes'] ?? $servicePost->location_longitudes;
        $servicePost->type = $validatedData['type' ?? $servicePost->type];
        $servicePost->state = $validatedData['state'] ?? $servicePost->state;
        $servicePost->categories_id = $category->id ?? $servicePost->categories_id;
        $servicePost->sub_categories_id = $subCategory->id ?? $servicePost->sub_categories_id;
        $servicePost->user_id = $user->id;
        $servicePost->save();
        // Delete photos
        if ($request->has('delete_photos')) {
            foreach ($request->delete_photos as $photoId) {
                $photo = Photos::find($photoId);
                Storage::delete($photo->src);
                $photo->delete();
            }
        }
        // Store new photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPath = $photo->store('photos/posts');
                $servicePost->photos()->create([
                    'src' => $photoPath,
                ]);
            }
        }
        return redirect()->route('service_posts.edit', $id)->with('success', 'Service Post has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServicePost $servicePost)
    {
        $user = Auth::user();
        // Check if the user has the required permissions to delete a service post
        if (!$user->hasPermission('destroy_service')) {
            return view('errors.403');
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
        return redirect()->back()->with('success', 'Selected Service Posts have been deleted!');
    }

    public function inViewCount(ServicePost $servicePost)
    {
        // Increment the view count for this service post
        $servicePost->incrementViewCount();
        return response()->json('Post Viewed Counted');
    }


    public function jobIndex(Request $request)
    {
        $user = Auth::user();
        // Check if the user has the required permissions to view all service posts
        if ($user->hasPermission('view_service')) {
            $servicePosts = ServicePost::where('category', 'وظائف')
                ->paginate(7);
            return view('service_posts.job_index', compact('servicePosts', 'user'));
        } else {

            return view('errors.403');
        }
    }

    public function carIndex(Request $request)
    {
        $user = Auth::user();
        // Check if the user has the required permissions to view all service posts
        if ($user->hasPermission('view_service')) {
            $servicePosts = ServicePost::where('category', 'سيارات')->paginate(7);
            return view('service_posts.car_index', compact('servicePosts', 'user'));
        } else {
            return view('errors.403');
        }
    }

    public function phoneIndex(Request $request)
    {
        $user = Auth::user();
        // Check if the user has the required permissions to view all service posts
        if ($user->hasPermission('view_service')) {
            $servicePosts = ServicePost::where('category', 'اجهزة')->paginate(7);
            return view('service_posts.phone_index', compact('servicePosts', 'user'));
        } else {
            return view('errors.403');
        }
    }

    public function realStatIndex(Request $request)
    {
        $user = Auth::user();
        // Check if the user has the required permissions to view all service posts
        if ($user->hasPermission('view_service')) {
            $servicePosts = ServicePost::where('category', 'عقارات')->paginate(7);
            return view('service_posts.real_state_index', compact('servicePosts', 'user'));
        } else {
            return view('errors.403');
        }
    }

    public function generalIndex(Request $request)
    {
        $user = Auth::user();
        // Check if the user has the required permissions to view all service posts
        if ($user->hasPermission('view_service')) {
            $servicePosts = ServicePost::where('category', 'خدمات')->paginate(7);
            return view('service_posts.general_index', compact('servicePosts', 'user'));
        } else {

            return view('errors.403');
        }
    }

    public function userIndex()
    {
        $user = Auth::user();
        // Check if the user has the required permissions to view all service posts
        if ($user->hasPermission('view_service')) {
            $categories = Categories::withCount(['sub_categories', 'servicePosts'])->get();
            $servicePosts = ServicePost::with('photos')
                ->where('state', 'published')
                ->withCount('favorites')
                ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
                ->paginate(7);
            // Get subcategories based on the selected category (You may need to adapt this based on your data structure)
            $category = Categories::all();
            $subcategories = Sub_categories::all();

            return view('service_posts.post_page', compact('servicePosts', 'user', 'categories', 'category', 'subcategories'));
        } else {
            return view('errors.403');
        }
        // Get all service posts and return them in a view
    }

    public function indexProfile(User $user): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $categories = Categories::withCount('servicePosts')
            ->orderBy('name')
            ->get();
        $servicePosts = $user->servicePosts()->with('photos')
            ->withCount('favorites')
            ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
            ->orderBy('id', 'desc')
            ->paginate(7);
        return view('service_posts.post_page', compact('servicePosts', 'user','categories'));
    }

    public function postProfile(User $user): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $categories = Categories::withCount('servicePosts')
            ->orderBy('name')
            ->get();
        $servicePosts = $user->servicePosts()->with('photos')
            ->withCount('favorites')
            ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
            ->orderBy('id', 'desc')
            ->paginate(7);
        return view('service_posts.index_post_profile', compact('servicePosts', 'user'));
    }
    public function favoritesIndex(User $user)
    {
        // Check if the user has the required permissions to view all service posts
        if ($user->hasPermission('view_service')) {
            $categories = Categories::withCount('servicePosts')
                ->orderBy('name')
                ->get();
            $servicePosts = ServicePost::whereHas('favorites', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->with('photos')
                ->withCount('favorites')
                ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
                ->paginate(7);
            return view('service_posts.post_page', compact('servicePosts', 'user','categories'));
        } else {
            return view('errors.403');
        }
    }


    public function userCarIndex(Request $request, $category)
    {
        $user = Auth::user();
        // Check if the user has the required permissions to view all service posts
        if ($user->hasPermission('view_service')) {
            $servicePosts = ServicePost::where('category', $category)
                ->where('state', 'published')
                ->with('photos')
                ->withCount('favorites')
                ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
                ->paginate(7);
            // Get subcategories based on the selected category (You may need to adapt this based on your data structure)
            $categories = Categories::where('name' , $category)->first();

            $subcategories = Sub_categories::where('categories_id', $categories->id)->get();

            return view('service_posts.post_page', compact('servicePosts', 'user', 'categories', 'category', 'subcategories'));
        } else {
            return view('errors.403');
        }
    }

    public function userPhoneIndex(Request $request, $category)
    {
        $user = Auth::user();
        // Check if the user has the required permissions to view all service posts
        if ($user->hasPermission('view_service')) {

            $servicePosts = ServicePost::where('category', $category)
                ->where('state', 'published')
                ->with('photos')
                ->withCount('favorites')
                ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
                ->paginate(7);
            // Get subcategories based on the selected category (You may need to adapt this based on your data structure)
            $categories = Categories::where('name' , $category)->first();

            $subcategories = Sub_categories::where('categories_id', $categories->id)->get();

            return view('service_posts.post_page', compact('servicePosts', 'user', 'categories', 'category', 'subcategories'));
        } else {
            return view('errors.403');
        }
    }

    public function userRealStatIndex(Request $request, $category)
    {
        $user = Auth::user();
        // Check if the user has the required permissions to view all service posts
        if ($user->hasPermission('view_service')) {

            $servicePosts = ServicePost::where('category', $category)
                ->where('state', 'published')
                ->with('photos')
                ->withCount('favorites')
                ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
                ->paginate(7);
            // Get subcategories based on the selected category (You may need to adapt this based on your data structure)
            $categories = Categories::where('name' , $category)->first();

            $subcategories = Sub_categories::where('categories_id', $categories->id)->get();

            return view('service_posts.post_page', compact('servicePosts', 'user', 'categories', 'category', 'subcategories'));
        } else {
            return view('errors.403');
        }
    }

    public function userGeneralIndex(Request $request, $category)
    {
        $user = Auth::user();
        // Check if the user has the required permissions to view all service posts
        if ($user->hasPermission('view_service')) {
            $servicePosts = ServicePost::where('category', $category)
                ->where('state', 'published')
                ->with('photos')
                ->withCount('favorites')
                ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
                ->paginate(7);
            // Get subcategories based on the selected category (You may need to adapt this based on your data structure)
            $categories = Categories::where('name' , $category)->first();

            $subcategories = Sub_categories::where('categories_id', $categories->id)->get();

            return view('service_posts.post_page', compact('servicePosts', 'user', 'categories', 'category', 'subcategories'));
        } else {
            return view('errors.403');
        }
    }

    public function userJobIndex(Request $request, $category)
    {
        $user = Auth::user();

        // Check if the user has the required permissions to view all service posts
        if ($user->hasPermission('view_service')) {
            // Get all categories with their corresponding subcategories and service posts count

            // Get the service posts for the selected category
            $servicePosts = ServicePost::where('category', $category)
                ->where('state', 'published')
                ->with('photos')
                ->withCount('favorites')
                ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
                ->paginate(7);
            // Get subcategories based on the selected category (You may need to adapt this based on your data structure)
            $categories = Categories::where('name' , $category)->first();

            $subcategories = Sub_categories::where('categories_id', $categories->id)->get();

            return view('service_posts.post_page', compact('servicePosts', 'user', 'categories', 'category', 'subcategories'));

        } else {
            return view('errors.403');
        }
    }

    public function servicePostCategorySubCategory(Request $request,$category, $sub_categories)
    {
        $categoriesInput = $request->input('categories');
        $subCategoriesInput = $request->input('sub_categories');
        $categories = Categories::where('name' , $categoriesInput)->first();

        $subcategories = Sub_categories::where('name', $subCategoriesInput)->first();
        $user = Auth::id();
        $CurrentUser = User::find($user);
        $user = Auth::user();
        // Check if the user has the required permissions to view all service posts
        if ($user->hasPermission('view_service')) {
            // Get all categories with their corresponding subcategories and service posts count
            $servicePosts = ServicePost::where('categories_id', $categories)
                ->where('sub_categories_id', $subcategories)
                ->where('state', 'published')
                ->with('photos')
                ->withCount('favorites')
                ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
                ->paginate(7);
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
            $categories = Categories::all();

            return view('service_posts.post_page', compact('servicePosts', 'user', 'categories'));
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }
    public function getSubCategories(Request $request, $category)
    {
        $categoryModel = Categories::where('name', $category)->first();
        if ($categoryModel) {
            $subCategories = Sub_categories::where('categories_id', $categoryModel->id)
                ->withCount('servicePosts')
                ->get();
            return response()->json($subCategories);
        } else {
            return response()->json([' no data found in sub category']);
        }
    }

    public function getServicePosts(Request $request, $subCategory)
    {
        $servicePosts = ServicePost::where('sub_category', $subCategory)
            ->with('photos')
            ->withCount('favorites')
            ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي'), id DESC")
            ->paginate(7);
        return response()->json($servicePosts);
    }

    public function fetchSubcategories(Request $request)
    {
        $category = $request->input('category');
        $categories = Categories::where('name' , $category)->first();

        $subcategories = Sub_categories::where('categories_id', $categories->id)->get();

        return response()->json($subcategories);
    }

}
