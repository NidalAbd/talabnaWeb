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
            $query->withCount(['servicePosts'])->with('photos');
        }, 'photos'])
            ->withCount(['servicePosts', 'sub_categories as sub_categories_with_service_posts_count' => function ($query) {
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

        // Apply news category permission filtering
        if (!$user->hasPermission('add_news')) {
            $baseQuery->whereRaw("NOT JSON_CONTAINS_PATH(name, 'one', '$.ar') OR JSON_EXTRACT(name, '$.ar') != '\"اخبار\"'");
        }

        // Apply direct filtering
        $query = $this->directCategoryFilter($baseQuery, $user);

        $categories = $query->get();
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

        // Base query with photos, non-suspended
        $baseQuery = Categories::with('photos')
            ->where('isSuspended', false);

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
        $isPalestineUser = $user->country_id == self::PALESTINE_COUNTRY_ID;

        if ($isPalestineUser) {
            // Palestine users: exclude Services category explicitly
            return $query->where('id', '!=', self::SERVICES_CATEGORY_ID);
        } else {
            // Non-Palestine users: exclude Emergency category explicitly
            return $query->where('id', '!=', self::EMERGENCY_CATEGORY_ID);
        }
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
            $query->withCount(['servicePosts'])->with('photos');
        }, 'photos'])
            ->featured()
            ->where('isSuspended', false)
            ->withCount(['servicePosts']);

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
            $query->withCount(['servicePosts'])->with('photos');
        }, 'photos'])
            ->popular()
            ->where('isSuspended', false)
            ->withCount(['servicePosts']);

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
