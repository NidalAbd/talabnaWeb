<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\Sub_categories;
use App\Models\ServicePost;
use App\Models\User;
use App\Models\cities;
use App\Models\countries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PublicController extends Controller
{
    /**
     * Get all categories with counts
     */
    public function categories(Request $request): JsonResponse
    {
        $categories = Cache::remember('public_categories', 3600, function () {
            return Categories::withCount(['servicePosts' => function ($query) {
                $query->where('state', 'published');
            }])
                ->where('isSuspended', false)
                ->orderBy('id')
                ->get()
                ->map(function ($cat) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'name_en' => $cat->name_en ?? $cat->name,
                        'icon' => $cat->icon,
                        'color' => $cat->color,
                        'slug' => \Str::slug($cat->name_en ?? $cat->name),
                        'posts_count' => $cat->service_posts_count,
                    ];
                });
        });

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * Get subcategories for a category
     */
    public function subcategories(Request $request, $categoryId): JsonResponse
    {
        $subcategories = Cache::remember("subcategories_{$categoryId}", 3600, function () use ($categoryId) {
            return Sub_categories::withCount(['servicePosts' => function ($query) {
                $query->where('state', 'published');
            }])
                ->where('categories_id', $categoryId)
                ->where('isSuspended', false)
                ->orderBy('name')
                ->get()
                ->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'name' => $sub->name,
                        'name_en' => $sub->name_en ?? $sub->name,
                        'category_id' => $sub->categories_id,
                        'posts_count' => $sub->service_posts_count,
                    ];
                });
        });

        return response()->json([
            'subcategories' => $subcategories,
        ]);
    }

    /**
     * Get listings with filters
     */
    public function listings(Request $request): JsonResponse
    {
        $query = ServicePost::with(['photos', 'user.photos', 'category', 'subCategory', 'city', 'country'])
            ->withCount(['favorites', 'comments'])
            ->where('state', 'published');

        // Apply category filter
        if ($request->filled('category_id')) {
            $query->where('categories_id', $request->category_id);
        }

        // Apply subcategory filter
        if ($request->filled('subcategory_id')) {
            $query->where('sub_categories_id', $request->subcategory_id);
        }

        // Apply country filter
        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        // Apply city filter
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply badge filter
        if ($request->filled('badge')) {
            $query->where('have_badge', $request->badge);
        }

        // Apply price filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSortFields = ['created_at', 'price', 'view_count', 'favorites_count'];

        if (in_array($sortBy, $allowedSortFields)) {
            // Premium listings first
            $query->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي')");
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي')");
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = min($request->get('per_page', 20), 50);
        $listings = $query->paginate($perPage);

        return response()->json([
            'listings' => $listings->items(),
            'pagination' => [
                'total' => $listings->total(),
                'per_page' => $listings->perPage(),
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
                'from' => $listings->firstItem(),
                'to' => $listings->lastItem(),
            ],
        ]);
    }

    /**
     * Get a single listing
     */
    public function listing(Request $request, $id): JsonResponse
    {
        $listing = ServicePost::with(['photos', 'user.photos', 'category', 'subCategory', 'city', 'country'])
            ->withCount(['favorites', 'comments'])
            ->where('state', 'published')
            ->find($id);

        if (!$listing) {
            return response()->json(['error' => 'Listing not found'], 404);
        }

        // Get related listings
        $related = ServicePost::with(['photos', 'user.photos'])
            ->withCount(['favorites', 'comments'])
            ->where('state', 'published')
            ->where('id', '!=', $listing->id)
            ->where(function ($q) use ($listing) {
                $q->where('categories_id', $listing->categories_id)
                    ->orWhere('sub_categories_id', $listing->sub_categories_id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Increment view count
        $listing->increment('view_count');

        return response()->json([
            'listing' => $listing,
            'related' => $related,
        ]);
    }

    /**
     * Get featured listings
     */
    public function featured(Request $request): JsonResponse
    {
        $featured = Cache::remember('featured_listings', 900, function () {
            return ServicePost::with(['photos', 'user.photos', 'category', 'subCategory'])
                ->withCount(['favorites', 'comments'])
                ->where('state', 'published')
                ->whereIn('have_badge', ['ماسي', 'ذهبي'])
                ->whereHas('photos')
                ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي')")
                ->orderBy('view_count', 'desc')
                ->limit(8)
                ->get();
        });

        return response()->json([
            'featured' => $featured,
        ]);
    }

    /**
     * Get latest listings
     */
    public function latest(Request $request): JsonResponse
    {
        $categoryId = $request->get('category_id');

        $cacheKey = $categoryId ? "latest_listings_{$categoryId}" : 'latest_listings';

        $latest = Cache::remember($cacheKey, 600, function () use ($categoryId) {
            $query = ServicePost::with(['photos', 'user.photos', 'category', 'subCategory'])
                ->withCount(['favorites', 'comments'])
                ->where('state', 'published')
                ->whereHas('photos');

            if ($categoryId) {
                $query->where('categories_id', $categoryId);
            }

            return $query->orderBy('created_at', 'desc')
                ->limit(12)
                ->get();
        });

        return response()->json([
            'latest' => $latest,
        ]);
    }

    /**
     * Get popular listings
     */
    public function popular(Request $request): JsonResponse
    {
        $popular = Cache::remember('popular_listings', 1800, function () {
            return ServicePost::with(['photos', 'user.photos', 'category', 'subCategory'])
                ->withCount(['favorites', 'comments'])
                ->where('state', 'published')
                ->whereHas('photos')
                ->orderBy('view_count', 'desc')
                ->limit(8)
                ->get();
        });

        return response()->json([
            'popular' => $popular,
        ]);
    }

    /**
     * Get home page stats
     */
    public function stats(): JsonResponse
    {
        $stats = Cache::remember('home_stats', 300, function () {
            return [
                'total_listings' => ServicePost::where('state', 'published')->count(),
                'total_users' => User::count(),
                'total_categories' => Categories::where('isSuspended', false)->count(),
                'listings_today' => ServicePost::where('state', 'published')
                    ->whereDate('created_at', Carbon::today())
                    ->count(),
            ];
        });

        return response()->json($stats);
    }

    /**
     * Get countries list
     */
    public function countries(): JsonResponse
    {
        $countries = Cache::remember('countries_list', 86400, function () {
            return countries::withCount(['cities'])
                ->orderBy('name')
                ->get()
                ->map(function ($country) {
                    return [
                        'id' => $country->id,
                        'name' => $country->name,
                        'name_en' => $country->name_en ?? $country->name,
                        'code' => $country->code,
                        'cities_count' => $country->cities_count,
                    ];
                });
        });

        return response()->json([
            'countries' => $countries,
        ]);
    }

    /**
     * Get cities for a country
     */
    public function cities(Request $request, $countryId): JsonResponse
    {
        $cities = Cache::remember("cities_{$countryId}", 86400, function () use ($countryId) {
            return cities::where('country_id', $countryId)
                ->orderBy('name')
                ->get()
                ->map(function ($city) {
                    return [
                        'id' => $city->id,
                        'name' => $city->name,
                        'name_en' => $city->name_en ?? $city->name,
                        'country_id' => $city->country_id,
                    ];
                });
        });

        return response()->json([
            'cities' => $cities,
        ]);
    }

    /**
     * Search listings
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'listings' => [],
                'pagination' => ['total' => 0],
            ]);
        }

        $listings = ServicePost::with(['photos', 'user.photos', 'category', 'subCategory'])
            ->withCount(['favorites', 'comments'])
            ->where('state', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderByRaw("FIELD(have_badge, 'ماسي', 'ذهبي', 'عادي')")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'listings' => $listings->items(),
            'pagination' => [
                'total' => $listings->total(),
                'per_page' => $listings->perPage(),
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
            ],
            'query' => $query,
        ]);
    }

    /**
     * Get user public profile
     */
    public function userProfile(Request $request, $id): JsonResponse
    {
        $user = User::with('photos')
            ->withCount(['servicePosts' => function ($query) {
                $query->where('state', 'published');
            }])
            ->find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Get user's listings
        $listings = ServicePost::with(['photos', 'category', 'subCategory'])
            ->withCount(['favorites', 'comments'])
            ->where('user_id', $id)
            ->where('state', 'published')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'photo' => $user->photos->first(),
                'listings_count' => $user->service_posts_count,
                'created_at' => $user->created_at,
            ],
            'listings' => $listings->items(),
            'pagination' => [
                'total' => $listings->total(),
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
            ],
        ]);
    }
}
