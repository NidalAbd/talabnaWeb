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
use App\Models\Level;
use App\Models\PointPackage;
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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ServicePostController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:service_posts_index')->only(['index']);
        $this->middleware('permission:service_posts_create')->only(['create', 'store']);
        $this->middleware('permission:service_posts_edit')->only(['edit', 'update']);
        $this->middleware('permission:service_posts_destroy')->only(['destroy']);
        $this->middleware('permission:service_posts_approve')->only(['approve']);
        $this->middleware('permission:service_posts_reject')->only(['reject']);
        $this->middleware('permission:service_posts_index')->only(['show']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if the user has the required permissions to view all service posts
        if ($user->hasPermission('service_posts_index')) {
            // Start with the base query
            $query = ServicePost::with('photos', 'user', 'category', 'subCategory', 'country', 'city', 'level');

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

            // Apply premium filter
            if ($request->has('premium') && $request->premium !== '') {
                $query->where('is_premium', $request->premium);
            }

            // Apply level filter
            if ($request->has('level') && $request->level) {
                $query->where('level_id', $request->level);
            }

            // Apply user filter
            if ($request->has('user') && $request->user) {
                $query->where('user_id', $request->user);
            }

            // Apply city filter
            if ($request->has('city') && $request->city) {
                $query->where('city_id', $request->city);
            }

            // Apply country filter
            if ($request->has('country') && $request->country) {
                $query->where('country_id', $request->country);
            }

            // Apply minimum views filter
            if ($request->has('min_views') && $request->min_views) {
                $query->where('view_count', '>=', $request->min_views);
            }

            // Apply date range filter
            if ($request->has('date_range') && $request->date_range) {
                $dates = explode(' - ', $request->date_range);
                if (count($dates) === 2) {
                    $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($dates[0]))->startOfDay();
                    $endDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($dates[1]))->endOfDay();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            }

            // Apply search filter
            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                        ->orWhere('description', 'like', "%{$searchTerm}%")
                        ->orWhere('price', 'like', "%{$searchTerm}%")
                        ->orWhere('type', 'like', "%{$searchTerm}%")
                        ->orWhereHas('user', function($userQuery) use ($searchTerm) {
                            $userQuery->where('name', 'like', "%{$searchTerm}%")
                                ->orWhere('user_name', 'like', "%{$searchTerm}%")
                                ->orWhere('email', 'like', "%{$searchTerm}%");
                        })
                        ->orWhereHas('category', function($categoryQuery) use ($searchTerm) {
                            $categoryQuery->where('name->'.app()->getLocale(), 'like', "%{$searchTerm}%")
                                ->orWhere('name->en', 'like', "%{$searchTerm}%")
                                ->orWhere('name->ar', 'like', "%{$searchTerm}%");
                        })
                        ->orWhereHas('subCategory', function($subCategoryQuery) use ($searchTerm) {
                            $subCategoryQuery->where('name->'.app()->getLocale(), 'like', "%{$searchTerm}%")
                                ->orWhere('name->en', 'like', "%{$searchTerm}%")
                                ->orWhere('name->ar', 'like', "%{$searchTerm}%");
                        })
                        ->orWhereHas('city', function($cityQuery) use ($searchTerm) {
                            $cityQuery->where('name->'.app()->getLocale(), 'like', "%{$searchTerm}%")
                                ->orWhere('name->en', 'like', "%{$searchTerm}%")
                                ->orWhere('name->ar', 'like', "%{$searchTerm}%");
                        })
                        ->orWhereHas('country', function($countryQuery) use ($searchTerm) {
                            $countryQuery->where('name->'.app()->getLocale(), 'like', "%{$searchTerm}%")
                                ->orWhere('name->en', 'like', "%{$searchTerm}%")
                                ->orWhere('name->ar', 'like', "%{$searchTerm}%");
                        });
                });
            }

            // Get statistics before pagination
            $totalCount = $query->count();
            $publishedCount = ServicePost::where('state', 'published')->count();
            $pendingCount = ServicePost::where('state', 'not published')->count();
            $premiumCount = ServicePost::where('level_id', '>', 0)->count();

            // Order by level_id (high to low) then by creation date
            $perPage = $request->get('per_page', 15);
            $servicePosts = $query->orderBy('level_id', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->appends($request->query());

            // Fetch categories and subcategories for dropdowns
            $categories = Categories::all();
            $subcategories = $request->has('category')
                ? Sub_categories::where('categories_id', $request->category)->get()
                : collect();

            // Fetch additional data for advanced filters
            $users = User::select('id', 'name', 'user_name', 'email')
                ->whereHas('servicePosts')
                ->orderBy('name')
                ->get();

            $cities = cities::select('id', 'name')
                ->whereHas('servicePosts')
                ->orderBy('name')
                ->get();

            $countries = countries::select('id', 'name')
                ->whereHas('servicePosts')
                ->orderBy('name')
                ->get();

            // Fetch levels for filtering
            try {
                $levels = Level::active()->ordered()->get();
            } catch (\Exception $e) {
                Log::error('Failed to load levels: ' . $e->getMessage());
                $levels = collect(); // Empty collection as fallback
            }

            // Check if featured functionality is available
            $hasFeaturedColumn = Schema::hasColumn('service_posts', 'is_featured');

            return view('service_posts.index', compact(
                'servicePosts', 
                'user', 
                'categories', 
                'subcategories',
                'users',
                'cities', 
                'countries',
                'levels',
                'totalCount',
                'publishedCount',
                'pendingCount',
                'premiumCount',
                'hasFeaturedColumn'
            ));
        } else {
            return redirect()->route('errors.403');
        }
    }

    public function create()
    {
        $user = Auth::user();
        // Check if the user has the required permissions to create a service post
        if (!$user->hasPermission('service_posts_create')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $categories = Categories::where('isSuspended', false)->get();
        $subcategories = Sub_categories::where('isSuspended', false)->get();
        $countries = countries::all();
        $cities = cities::all();

        return view('service_posts.create', compact('categories', 'subcategories', 'countries', 'cities'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Check if the user has the required permissions to create a service post
        if (!$user->hasPermission('service_posts_create')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'price_currency_code' => 'required|string|max:3',
            'type' => 'required|in:عرض,طلب',
            'categories_id' => 'required|exists:categories,id',
            'sub_categories_id' => 'required|exists:sub_categories,id',
            'country_id' => 'nullable|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
            'location_latitudes' => 'nullable|numeric',
            'location_longitudes' => 'nullable|numeric',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $servicePost = ServicePost::create([
                'user_id' => $user->id,
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'price' => $validatedData['price'],
                'price_currency_code' => $validatedData['price_currency_code'],
                'type' => $validatedData['type'],
                'categories_id' => $validatedData['categories_id'],
                'sub_categories_id' => $validatedData['sub_categories_id'],
                'country_id' => $validatedData['country_id'],
                'city_id' => $validatedData['city_id'],
                'location_latitudes' => $validatedData['location_latitudes'],
                'location_longitudes' => $validatedData['location_longitudes'],
                'state' => 'published',
            ]);

            // Handle photo uploads
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $fileName = $photo->hashName();
                    $photoPath = $photo->storeAs('photos/serviceposts', $fileName);

                    $photoModel = new Photos([
                        'src' => 'storage/'.$photoPath,
                        'is_external' => false
                    ]);

                    $servicePost->photos()->save($photoModel);
                }
            }

            // Send notifications to followers
            $this->sendNotificationsToFollowers($servicePost);

            DB::commit();

            return redirect()->route('service_posts.show', $servicePost)
                ->with('success', 'Service post created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create service post: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create service post');
        }
    }

    public function show(Request $request, ServicePost $servicePost)
    {
        // Only admin and moderator can view all service posts
        $user = Auth::user();

        // Check if the user has the required permissions to view a service post
        if ($user->hasPermission('service_posts_index')) {
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
        if ($user->hasPermission('service_posts_edit')) {
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

    public function update(Request $request, ServicePost $servicePost)
    {
        $user = Auth::user();

        // Check if the user has the required permissions to update a service post
        if (!$user->hasPermission('service_posts_edit')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'price_currency_code' => 'required|string|max:3',
            'type' => 'required|in:عرض,طلب',
            'categories_id' => 'required|exists:categories,id',
            'sub_categories_id' => 'required|exists:sub_categories,id',
            'country_id' => 'nullable|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
            'location_latitudes' => 'nullable|numeric',
            'location_longitudes' => 'nullable|numeric',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $servicePost->update($validatedData);

            // Handle photo uploads
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $fileName = $photo->hashName();
                    $photoPath = $photo->storeAs('photos/serviceposts', $fileName);

                    $photoModel = new Photos([
                        'src' => 'storage/'.$photoPath,
                        'is_external' => false
                    ]);

                    $servicePost->photos()->save($photoModel);
                }
            }

            DB::commit();

            return redirect()->route('service_posts.show', $servicePost)
                ->with('success', 'Service post updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update service post: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update service post');
        }
    }

    public function destroy(ServicePost $servicePost)
    {
        $user = Auth::user();

        if (!$user->hasPermission('service_posts_destroy')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $servicePost->delete();
            return response()->json(['success' => true, 'message' => 'Service post deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to delete service post: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete service post'], 500);
        }
    }

    public function approve(ServicePost $servicePost)
    {
        $user = Auth::user();

        if (!$user->hasPermission('service_posts_approve')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $servicePost->update(['state' => 'published']);
            return response()->json(['success' => true, 'message' => 'Service post approved successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to approve service post: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to approve service post'], 500);
        }
    }

    public function reject(ServicePost $servicePost)
    {
        $user = Auth::user();

        if (!$user->hasPermission('service_posts_reject')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $servicePost->update(['state' => 'rejected']);
            return response()->json(['success' => true, 'message' => 'Service post rejected successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to reject service post: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to reject service post'], 500);
        }
    }

    public function togglePremium(ServicePost $servicePost)
    {
        $user = Auth::user();

        if (!$user->hasPermission('service_posts_edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $servicePost->update(['is_premium' => !$servicePost->is_premium]);
            $status = $servicePost->is_premium ? 'premium' : 'regular';

            return response()->json([
                'success' => true,
                'message' => "Service post status changed to {$status}",
                'data' => [
                    'id' => $servicePost->id,
                    'is_premium' => $servicePost->is_premium
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to toggle premium status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update premium status'], 500);
        }
    }

    public function bulkDestroy(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasPermission('service_posts_destroy')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'No posts selected');
        }

        try {
            $servicePosts = ServicePost::whereIn('id', $ids)->get();

            foreach ($servicePosts as $servicePost) {
                // Delete associated photos
                foreach ($servicePost->photos as $photo) {
                    if (Storage::exists($photo->src)) {
                        Storage::delete($photo->src);
                    }
                    $photo->delete();
                }
                $servicePost->delete();
            }

            return back()->with('success', count($servicePosts) . ' service posts deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to bulk delete service posts: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete service posts');
        }
    }

    public function inViewCount(ServicePost $servicePost)
    {
        $servicePost->increment('view_count');
        return response()->json(['success' => true]);
    }

    /**
     * Bulk action for service posts
     */
    public function bulkAction(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasPermission('service_posts_edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $action = $request->input('action');
        $postIds = $request->input('post_ids', []);
        
        if (empty($postIds)) {
            return response()->json(['success' => false, 'message' => 'No posts selected'], 400);
        }

        $posts = ServicePost::whereIn('id', $postIds)->get();
        $updatedCount = 0;

        try {
            DB::beginTransaction();

            foreach ($posts as $post) {
                switch ($action) {
                    case 'approve':
                        if ($post->state !== 'published') {
                            $post->state = 'published';
                            $post->save();
                            $updatedCount++;
                        }
                        break;
                        
                    case 'reject':
                        if ($post->state !== 'rejected') {
                            $post->state = 'rejected';
                            $post->save();
                            $updatedCount++;
                        }
                        break;
                        
                    case 'archive':
                        if ($post->state !== 'archive') {
                            $post->state = 'archive';
                            $post->save();
                            $updatedCount++;
                        }
                        break;
                        
                    case 'make_premium':
                        if (!$post->is_premium) {
                            $post->is_premium = true;
                            $post->save();
                            $updatedCount++;
                        }
                        break;
                        
                    case 'remove_premium':
                        if ($post->is_premium) {
                            $post->is_premium = false;
                            $post->save();
                            $updatedCount++;
                        }
                        break;
                        
                    case 'delete':
                        $post->delete();
                        $updatedCount++;
                        break;
                }
            }

            DB::commit();

            $actionText = ucfirst(str_replace('_', ' ', $action));
            return response()->json([
                'success' => true,
                'message' => "Successfully {$actionText} {$updatedCount} posts"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the bulk action'
            ], 500);
        }
    }

    /**
     * Export service posts
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasPermission('service_posts_index')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $format = $request->get('format', 'csv');
        $includePhotos = $request->get('include_photos', false);
        $includeUserInfo = $request->get('include_user_info', false);
        $includeStats = $request->get('include_stats', false);

        // Build query based on current filters
        $query = ServicePost::with('photos', 'user', 'category', 'subCategory', 'country', 'city');

        // Apply the same filters as the index method
        if ($request->has('category') && $request->category) {
            $query->where('categories_id', $request->category);
        }
        if ($request->has('subcategory') && $request->subcategory) {
            $query->where('sub_categories_id', $request->subcategory);
        }
        if ($request->has('status') && $request->status) {
            $query->where('state', $request->status);
        }
        if ($request->has('premium') && $request->premium !== '') {
            $query->where('is_premium', $request->premium);
        }

        $posts = $query->orderBy('id', 'desc')->get();

        $filename = 'service_posts_' . date('Y-m-d_H-i-s') . '.' . $format;

        if ($format === 'csv') {
            return $this->exportToCsv($posts, $filename, $includePhotos, $includeUserInfo, $includeStats);
        } elseif ($format === 'excel') {
            return $this->exportToExcel($posts, $filename, $includePhotos, $includeUserInfo, $includeStats);
        } else {
            return $this->exportToPdf($posts, $filename, $includePhotos, $includeUserInfo, $includeStats);
        }
    }

    /**
     * Get statistics for service posts
     */
    public function statistics()
    {
        $user = Auth::user();
        
        if (!$user->hasPermission('service_posts_index')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $stats = [
            'total' => ServicePost::count(),
            'published' => ServicePost::where('state', 'published')->count(),
            'pending' => ServicePost::where('state', 'not published')->count(),
            'rejected' => ServicePost::where('state', 'rejected')->count(),
            'archived' => ServicePost::where('state', 'archive')->count(),
            'premium' => ServicePost::where('is_premium', true)->count(),
            'regular' => ServicePost::where('is_premium', false)->count(),
            'total_views' => ServicePost::sum('view_count'),
            'avg_views' => ServicePost::avg('view_count'),
            'recent_posts' => ServicePost::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Duplicate a service post
     */
    public function duplicate(ServicePost $servicePost)
    {
        $user = Auth::user();
        
        if (!$user->hasPermission('service_posts_create')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            $newPost = $servicePost->replicate();
            $newPost->title = $servicePost->title . ' (Copy)';
            $newPost->state = 'not published';
            $newPost->view_count = 0;
            $newPost->created_at = now();
            $newPost->updated_at = now();
            $newPost->save();

            // Duplicate photos
            foreach ($servicePost->photos as $photo) {
                $newPhoto = $photo->replicate();
                $newPhoto->service_post_id = $newPost->id;
                $newPhoto->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post duplicated successfully',
                'data' => ['id' => $newPost->id]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while duplicating the post'
            ], 500);
        }
    }

    /**
     * Archive a service post
     */
    public function archive(ServicePost $servicePost)
    {
        $user = Auth::user();
        
        if (!$user->hasPermission('service_posts_edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $servicePost->state = 'archive';
        $servicePost->save();

        return response()->json([
            'success' => true,
            'message' => 'Post archived successfully',
            'data' => [
                'id' => $servicePost->id,
                'state' => $servicePost->state
            ]
        ]);
    }

    /**
     * Unarchive a service post
     */
    public function unarchive(ServicePost $servicePost)
    {
        $user = Auth::user();
        
        if (!$user->hasPermission('service_posts_edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $servicePost->state = 'not published';
        $servicePost->save();

        return response()->json([
            'success' => true,
            'message' => 'Post unarchived successfully',
            'data' => [
                'id' => $servicePost->id,
                'state' => $servicePost->state
            ]
        ]);
    }

    /**
     * Feature a service post
     */
    public function feature(ServicePost $servicePost)
    {
        $user = Auth::user();
        
        if (!$user->hasPermission('service_posts_edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Check if the is_featured column exists
        if (!Schema::hasColumn('service_posts', 'is_featured')) {
            return response()->json([
                'success' => false,
                'message' => 'Featured functionality is not available. Please run database migrations.'
            ], 400);
        }

        $servicePost->is_featured = true;
        $servicePost->save();

        return response()->json([
            'success' => true,
            'message' => 'Post featured successfully',
            'data' => [
                'id' => $servicePost->id,
                'is_featured' => $servicePost->is_featured
            ]
        ]);
    }

    /**
     * Unfeature a service post
     */
    public function unfeature(ServicePost $servicePost)
    {
        $user = Auth::user();
        
        if (!$user->hasPermission('service_posts_edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Check if the is_featured column exists
        if (!Schema::hasColumn('service_posts', 'is_featured')) {
            return response()->json([
                'success' => false,
                'message' => 'Featured functionality is not available. Please run database migrations.'
            ], 400);
        }

        $servicePost->is_featured = false;
        $servicePost->save();

        return response()->json([
            'success' => true,
            'message' => 'Post unfeatured successfully',
            'data' => [
                'id' => $servicePost->id,
                'is_featured' => $servicePost->is_featured
            ]
        ]);
    }

    /**
     * Update service post level
     */
    public function updateLevel(Request $request, ServicePost $servicePost)
    {
        $user = Auth::user();
        
        if (!$user->hasPermission('service_posts_edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'level_id' => 'required|exists:levels,id',
            'duration' => 'required|integer|min:1'
        ]);

        $level = Level::findOrFail($request->level_id);
        $duration = $request->duration;
        $pointsCost = $level->calculatePointsCost($duration);

        // Check if user is admin (has admin role or super admin permissions)
        $isAdmin = $user->hasRole('admin') || $user->hasRole('super_admin') || $user->hasPermission('admin_access');

        // Check if user has enough points (skip for admin users)
        if (!$isAdmin && $user->points < $pointsCost) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient points. Required: {$pointsCost}, Available: {$user->points}"
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Deduct points from user (skip for admin users)
            if (!$isAdmin) {
                $user->points -= $pointsCost;
                $user->save();
            }

            // Update service post level
            $servicePost->level_id = $level->id;
            $servicePost->badge_duration = $duration;
            $servicePost->badge_expires_at = Carbon::now()->addDays($duration);
            $servicePost->save();

            // Create point transaction record (skip for admin users)
            if (!$isAdmin) {
                point_transactions::create([
                    'user_id' => $user->id,
                    'points' => -$pointsCost,
                    'type' => 'level_upgrade',
                    'description' => "Upgraded service post #{$servicePost->id} to {$level->localized_name} for {$duration} days",
                    'service_post_id' => $servicePost->id,
                    'level_id' => $level->id
                ]);
            }

            DB::commit();

            $adminMessage = $isAdmin ? ' (Admin action - no points deducted)' : '';
            return response()->json([
                'success' => true,
                'message' => "Service post upgraded to {$level->localized_name} successfully{$adminMessage}",
                'data' => [
                    'id' => $servicePost->id,
                    'level_id' => $servicePost->level_id,
                    'level_name' => $level->localized_name,
                    'duration' => $duration,
                    'expires_at' => $servicePost->badge_expires_at->format('Y-m-d H:i:s'),
                    'is_admin_action' => $isAdmin
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while upgrading the service post'
            ], 500);
        }
    }

    /**
     * Get available levels for service post upgrade
     */
    public function getAvailableLevels(ServicePost $servicePost)
    {
        $user = Auth::user();
        
        if (!$user->hasPermission('service_posts_edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Check if user is admin (has admin role or super admin permissions)
        $isAdmin = $user->hasRole('admin') || $user->hasRole('super_admin') || $user->hasPermission('admin_access');

        $levels = Level::active()->ordered()->get();
        $availableLevels = [];

        foreach ($levels as $level) {
            // For admin users, all levels are affordable
            $canAfford = $isAdmin ? true : ($user->points_balance >= $level->points_per_day);
            $maxDuration = $isAdmin ? 365 : ($level->points_per_day > 0 ? floor($user->points_balance / $level->points_per_day) : 365);
            
            $availableLevels[] = [
                'id' => $level->id,
                'name' => $level->localized_name,
                'description' => $level->localized_description,
                'icon' => $level->icon,
                'color' => $level->color,
                'points_per_day' => $level->points_per_day,
                'view_boost_percentage' => $level->view_boost_percentage,
                'features' => $level->localized_features,
                'can_afford' => $canAfford,
                'max_duration' => $maxDuration
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'levels' => $availableLevels,
                'user_points' => $isAdmin ? '∞ (Admin)' : $user->points_balance,
                'is_admin' => $isAdmin,
                'current_level' => $servicePost->level ? [
                    'id' => $servicePost->level->id,
                    'name' => $servicePost->level->localized_name,
                    'expires_at' => $servicePost->badge_expires_at ? $servicePost->badge_expires_at->format('Y-m-d H:i:s') : null,
                    'remaining_days' => $servicePost->getLevelRemainingDays()
                ] : null
            ]
        ]);
    }

    /**
     * Get point packages for purchase
     */
    public function getPointPackages()
    {
        $user = Auth::user();
        
        if (!$user->hasPermission('service_posts_edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $packages = PointPackage::active()->get();
        $availablePackages = [];

        foreach ($packages as $package) {
            $availablePackages[] = [
                'id' => $package->id,
                'name' => $package->name,
                'points' => $package->points,
                'price' => $package->price,
                'formatted_price' => $package->formatted_price,
                'formatted_points' => $package->formatted_points,
                'description' => $package->description,
                'features' => $package->features
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'packages' => $availablePackages,
                'user_points' => $user->points
            ]
        ]);
    }

    /**
     * Export to CSV
     */
    private function exportToCsv($posts, $filename, $includePhotos, $includeUserInfo, $includeStats)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($posts, $includePhotos, $includeUserInfo, $includeStats) {
            $file = fopen('php://output', 'w');
            
            // Headers
            $headers = ['ID', 'Title', 'Description', 'Price', 'Type', 'Status', 'Premium', 'Views', 'Created At'];
            if ($includeUserInfo) {
                $headers = array_merge($headers, ['User Name', 'User Email']);
            }
            fputcsv($file, $headers);

            // Data
            foreach ($posts as $post) {
                $row = [
                    $post->id,
                    is_array($post->title) ? ($post->title['en'] ?? 'N/A') : $post->title,
                    is_array($post->description) ? ($post->description['en'] ?? 'N/A') : $post->description,
                    $post->price,
                    $post->type,
                    $post->state,
                    $post->is_premium ? 'Yes' : 'No',
                    $post->view_count ?? 0,
                    $post->created_at->format('Y-m-d H:i:s')
                ];

                if ($includeUserInfo && $post->user) {
                    $row[] = $post->user->name ?? $post->user->user_name ?? 'N/A';
                    $row[] = $post->user->email ?? 'N/A';
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to Excel
     */
    private function exportToExcel($posts, $filename, $includePhotos, $includeUserInfo, $includeStats)
    {
        // This would require a package like Maatwebsite Excel
        // For now, return CSV as Excel
        return $this->exportToCsv($posts, str_replace('.csv', '.xlsx', $filename), $includePhotos, $includeUserInfo, $includeStats);
    }

    /**
     * Export to PDF
     */
    private function exportToPdf($posts, $filename, $includePhotos, $includeUserInfo, $includeStats)
    {
        // This would require a package like DomPDF
        // For now, return CSV as PDF
        return $this->exportToCsv($posts, str_replace('.pdf', '.csv', $filename), $includePhotos, $includeUserInfo, $includeStats);
    }

    // Additional methods for different views
    public function indexProfile(User $user)
    {
        $user->load(['photos', 'roles', 'country', 'city']);
        return view('users.profile', compact('user'));
    }

    public function postProfile(User $user)
    {
        $servicePosts = $user->servicePosts()->with('photos')->paginate(10);
        return view('users.posts', compact('user', 'servicePosts'));
    }

    public function userIndex()
    {
        $user = Auth::user();
        $servicePosts = $user->servicePosts()->with('photos')->paginate(10);
        return view('service_posts.user_index', compact('servicePosts'));
    }

    public function jobIndex()
    {
        $servicePosts = ServicePost::whereHas('category', function($q) {
            $q->where('name->en', 'Jobs');
        })->with('photos', 'user')->paginate(10);
        return view('service_posts.jobs', compact('servicePosts'));
    }

    public function carIndex()
    {
        $servicePosts = ServicePost::whereHas('category', function($q) {
            $q->where('name->en', 'Cars');
        })->with('photos', 'user')->paginate(10);
        return view('service_posts.cars', compact('servicePosts'));
    }

    public function phoneIndex()
    {
        $servicePosts = ServicePost::whereHas('category', function($q) {
            $q->where('name->en', 'Devices');
        })->with('photos', 'user')->paginate(10);
        return view('service_posts.devices', compact('servicePosts'));
    }

    public function realStatIndex()
    {
        $servicePosts = ServicePost::whereHas('category', function($q) {
            $q->where('name->en', 'Houses');
        })->with('photos', 'user')->paginate(10);
        return view('service_posts.realestate', compact('servicePosts'));
    }

    public function generalIndex()
    {
        $servicePosts = ServicePost::whereHas('category', function($q) {
            $q->where('name->en', 'Services');
        })->with('photos', 'user')->paginate(10);
        return view('service_posts.services', compact('servicePosts'));
    }

    public function getServicePosts(Categories $category)
    {
        $servicePosts = $category->servicePosts()->with('photos', 'user')->paginate(10);
        return view('service_posts.category', compact('servicePosts', 'category'));
    }

    public function getSubCategories(Categories $category)
    {
        $subcategories = $category->subCategories()->withCount('servicePosts')->get();
        return response()->json($subcategories);
    }

    public function servicePostCategorySubCategory(Categories $categories, Sub_categories $sub_categories)
    {
        $servicePosts = ServicePost::where('categories_id', $categories->id)
            ->where('sub_categories_id', $sub_categories->id)
            ->with('photos', 'user')
            ->paginate(10);
        return view('service_posts.category_subcategory', compact('servicePosts', 'categories', 'sub_categories'));
    }

    public function fetchSubcategories(Request $request)
    {
        $categoryId = $request->input('category_id');
        $subcategories = Sub_categories::where('categories_id', $categoryId)->get();
        return response()->json($subcategories);
    }

    public function getCitiesForForm($countryId)
    {
        $cities = cities::where('country_id', $countryId)->get();
        return response()->json($cities);
    }

    // User-specific category views
    public function userJobIndex(Categories $category)
    {
        $user = Auth::user();
        $servicePosts = $user->servicePosts()
            ->where('categories_id', $category->id)
            ->with('photos')
            ->paginate(10);
        return view('service_posts.user_jobs', compact('servicePosts', 'category'));
    }

    public function userCarIndex(Categories $category)
    {
        $user = Auth::user();
        $servicePosts = $user->servicePosts()
            ->where('categories_id', $category->id)
            ->with('photos')
            ->paginate(10);
        return view('service_posts.user_cars', compact('servicePosts', 'category'));
    }

    public function userPhoneIndex(Categories $category)
    {
        $user = Auth::user();
        $servicePosts = $user->servicePosts()
            ->where('categories_id', $category->id)
            ->with('photos')
            ->paginate(10);
        return view('service_posts.user_devices', compact('servicePosts', 'category'));
    }

    public function userRealStatIndex(Categories $category)
    {
        $user = Auth::user();
        $servicePosts = $user->servicePosts()
            ->where('categories_id', $category->id)
            ->with('photos')
            ->paginate(10);
        return view('service_posts.user_realestate', compact('servicePosts', 'category'));
    }

    public function userGeneralIndex(Categories $category)
    {
        $user = Auth::user();
        $servicePosts = $user->servicePosts()
            ->where('categories_id', $category->id)
            ->with('photos')
            ->paginate(10);
        return view('service_posts.user_services', compact('servicePosts', 'category'));
    }

    public function favoritesIndex(User $user)
    {
        $favorites = $user->favorites()->with('servicePost.photos')->paginate(10);
        return view('service_posts.favorites', compact('favorites', 'user'));
    }

    private function sendNotificationsToFollowers($servicePost)
    {
        $followers = $servicePost->user->followers;

        foreach ($followers as $follower) {
            $message = $servicePost->user->user_name . ' has posted a new service: ' . $servicePost->title;

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
}
