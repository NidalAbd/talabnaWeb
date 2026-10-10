<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriesController extends Controller
{
    // Category IDs - replace these with the actual IDs from your database
    const EMERGENCY_CATEGORY_ID = 8; // ID for Emergency (طوارئ) category
    const SERVICES_CATEGORY_ID = 5;  // ID for Services (خدمات) category

    // Country IDs from the database
    const PALESTINE_COUNTRY_ID = 1; // ID for Palestine from the seeds

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $user = Auth::user();

        // Start query for categories and relationships
        $baseQuery = Categories::with(['sub_categories' => function($query) {
            $query->withCount(['servicePosts' => function ($q) {
                $q->where('state', 'published')
                  ->whereHas('user', function ($u) {
                      $u->where('is_active', 'active');
                  });
            }])->with('photos');
        }, 'photos'])
            ->withCount(['servicePosts' => function ($q) {
                $q->where('state', 'published')
                  ->whereHas('user', function ($u) {
                      $u->where('is_active', 'active');
                  });
            }, 'sub_categories as sub_categories_with_service_posts_count' => function ($query) {
                $query->has('servicePosts');
            }]);

        // Apply direct filtering without relying on dynamic lookups
        $query = $this->directCategoryFilter($baseQuery, $user);

        $categories = $query->paginate(10);
        return response()->json(compact('categories'));
    }

    /**
     * Get category listing
     *
     * @return JsonResponse
     */
    public function categoryList(): JsonResponse
    {
        $user = Auth::user();

        // Start with base query - exclude suspended
        $baseQuery = Categories::where('isSuspended', false);

        // Apply direct filtering
        $query = $this->directCategoryFilter($baseQuery, $user);

        // A "News" category (اخبار) is only for users allowed to post news (filtered here, not with MySQL-only
        // JSON functions, so the tests run it too)
        $canNews = $user->hasPermission('add_news');
        $categories = $query->get()->reject(function ($c) use ($canNews) {
            $name = is_array($c->name) ? $c->name : (json_decode((string) $c->getRawOriginal('name'), true) ?: []);

            return ! $canNews && ($name['ar'] ?? null) === 'اخبار';
        })->values();
        return response()->json(compact('categories'));
    }

    /**
     * Get category menu
     *
     * @return JsonResponse
     */
    public function categoryMenu(): JsonResponse
    {
        $user = Auth::user();

        // posts_count counts what the category feed actually lists: published posts by
        // active users in every country (the feed shows the user's own country first,
        // then others). It used to count only the user's country, so a category said
        // "4 listings" while its subcategories showed 24 (2026-10-04).
        $baseQuery = Categories::with('photos')
            ->where('isSuspended', false)
            ->withCount(['servicePosts as posts_count' => function ($q) {
                $q->where('state', 'published')
                  ->whereHas('user', fn ($u) => $u->where('is_active', 'active'));
            }]);

        // Apply direct filtering
        $query = $this->directCategoryFilter($baseQuery, $user);

        $categories = $query->get();
        return response()->json(compact('categories'));
    }

    /**
     * Apply direct category filtering based on user's country
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function directCategoryFilter($query, $user)
    {
        // Every category in every country (2026-10-10). Palestine used to lose Services and every other country
        // Urgent; the owner wants both everywhere. Kept as the one place a per-country rule would go.
        return $query;
    }

    /**
     * Get featured categories
     *
     * @return JsonResponse
     */
    public function featured(): JsonResponse
    {
        $user = Auth::user();

        $baseQuery = Categories::with(['sub_categories' => function($query) {
            $query->withCount(['servicePosts' => function ($q) {
                $q->where('state', 'published')
                  ->whereHas('user', function ($u) {
                      $u->where('is_active', 'active');
                  });
            }])->with('photos');
        }, 'photos'])
            ->featured()
            ->where('isSuspended', false)
            ->withCount(['servicePosts' => function ($q) {
                $q->where('state', 'published')
                  ->whereHas('user', function ($u) {
                      $u->where('is_active', 'active');
                  });
            }]);

        $query = $this->directCategoryFilter($baseQuery, $user);
        $categories = $query->get();

        return response()->json(compact('categories'));
    }

    /**
     * Get popular categories
     *
     * @return JsonResponse
     */
    public function popular(): JsonResponse
    {
        $user = Auth::user();

        $baseQuery = Categories::with(['sub_categories' => function($query) {
            $query->withCount(['servicePosts' => function ($q) {
                $q->where('state', 'published')
                  ->whereHas('user', function ($u) {
                      $u->where('is_active', 'active');
                  });
            }])->with('photos');
        }, 'photos'])
            ->popular()
            ->where('isSuspended', false)
            ->withCount(['servicePosts' => function ($q) {
                $q->where('state', 'published')
                  ->whereHas('user', function ($u) {
                      $u->where('is_active', 'active');
                  });
            }]);

        $query = $this->directCategoryFilter($baseQuery, $user);
        $categories = $query->get();

        return response()->json(compact('categories'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        try {
            // Check if the user is logged in
            if (!Auth::check()) {
                return response()->json(['error' => 'You need to log in'], 401);
            }
            $user = Auth::user();
            // Check if the user has the required permissions to view all service posts
            if (!$user->hasPermission('view_service')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return response()->json(compact('id'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Other methods unchanged
    public function create() { /* ... */ }
    public function store(Request $request) { /* ... */ }
    public function edit($id) { /* ... */ }
    public function update(Request $request, $id) { /* ... */ }
    public function destroy($id) { /* ... */ }
}
