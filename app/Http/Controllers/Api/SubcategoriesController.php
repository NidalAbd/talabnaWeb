<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sub_categories;
use Database\Seeders\subcategories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SubcategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request , $id)
    {
        // Get the category ID from the request
        $category_id = $request->input('categories_id');
        // Get the subcategories for the selected category
        $subcategories = Sub_categories::where('categories_id', $category_id)
            ->whereHas('servicePosts', function ($query) {
                $query->where('state', 'published');
            })
            ->get();
        // Return the subcategories as JSON
        return response()->json(['subcategories' => $subcategories]);
    }
    public function CategoriesSelected($categories): JsonResponse
    {
        $subcategories = Sub_categories::where('categories_id', $categories)
            ->withCount(['servicePosts' => function ($query) {
                $query->where('state', 'published')
                      ->whereHas('user', function ($q) {
                          $q->where('is_active', 'active');
                      });
            }])
            ->with('photos')
            ->orderByDesc('service_posts_count') // Order by most service posts
            ->get();

        return response()->json(['subcategories' => $subcategories]);
    }


    /**
     * Show the form for creating a new resource.
     */


    public function toggleFollowCategory(Sub_categories $subcategory): JsonResponse
    {
        $user = auth()->user();
        // Check if user is already following the subcategory
        $isFollowing = $user->subcategories()->where('sub_categories_id', $subcategory->id)->exists();

        if ($isFollowing) {
            // Unfollow the subcategory
            $user->subcategories()->detach($subcategory);
            $isFollower = false;
        } else {
            // Follow the subcategory
            $user->subcategories()->attach($subcategory);
            $isFollower = true;
        }

        return response()->json(['isFollowSubcategory' => $isFollower]);

    }
    public function isFollowingCategory(Sub_categories $subcategory): JsonResponse
    {
        $user = auth()->user();
        $isFollowingCategory =  $user->subcategories()->where('sub_categories.id', $subcategory->id)->exists();
        $isFavorited = (bool)$isFollowingCategory;
        return response()->json([
            'isFollowSubcategory' => $isFavorited,
        ]);
    }

    /**
     * Get featured subcategories
     *
     * @return JsonResponse
     */
    public function featured(): JsonResponse
    {
        $subcategories = Sub_categories::featured()
            ->where('isSuspended', false)
            ->withCount(['servicePosts' => function ($query) {
                $query->where('state', 'published')
                      ->whereHas('user', function ($q) {
                          $q->where('is_active', 'active');
                      });
            }])
            ->with('photos')
            ->orderByDesc('service_posts_count')
            ->get();

        return response()->json(compact('subcategories'));
    }

    /**
     * Get popular subcategories
     *
     * @return JsonResponse
     */
    public function popular(): JsonResponse
    {
        $subcategories = Sub_categories::popular()
            ->where('isSuspended', false)
            ->withCount(['servicePosts' => function ($query) {
                $query->where('state', 'published')
                      ->whereHas('user', function ($q) {
                          $q->where('is_active', 'active');
                      });
            }])
            ->with('photos')
            ->orderByDesc('service_posts_count')
            ->get();

        return response()->json(compact('subcategories'));
    }

    public function create(): Response
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Sub_categories $subcategories): Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sub_categories $subcategories): Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sub_categories $subcategories): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sub_categories $subcategories): RedirectResponse
    {
        //
    }
}
