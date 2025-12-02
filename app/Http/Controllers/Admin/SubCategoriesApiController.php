<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sub_categories;
use App\Models\Categories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubCategoriesApiController extends Controller
{
    /**
     * Get all sub-categories with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = Sub_categories::with(['photos', 'category'])
            ->withCount('servicePosts');

        // Search in both AR and EN names
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"]);
            });
        }

        // Filter by parent category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('categories_id', $request->category_id);
        }

        // Filter by featured status
        if ($request->has('featured') && $request->featured !== '') {
            $query->where('is_featured', (bool) $request->featured);
        }

        // Filter by popular status
        if ($request->has('popular') && $request->popular !== '') {
            $query->where('is_popular', (bool) $request->popular);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'name':
                $query->orderByRaw("JSON_EXTRACT(name, '$.en') {$sortOrder}");
                break;
            case 'posts':
                $query->orderBy('service_posts_count', $sortOrder);
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
                break;
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $subcategories = $query->paginate($perPage);

        // Transform data to include image URL
        $subcategories->getCollection()->transform(function ($subcategory) {
            $subcategory->image_url = $subcategory->photos->first()?->src ?? null;
            unset($subcategory->photos);
            return $subcategory;
        });

        return response()->json($subcategories);
    }

    /**
     * Get statistics for sub-categories
     */
    public function getStats(): JsonResponse
    {
        $total = Sub_categories::count();
        $featured = Sub_categories::where('is_featured', true)->count();
        $popular = Sub_categories::where('is_popular', true)->count();
        $withPosts = Sub_categories::has('servicePosts')->count();

        // Get top 5 parent categories by sub-category count
        $topCategories = Categories::withCount('sub_categories')
            ->orderBy('sub_categories_count', 'desc')
            ->take(5)
            ->get(['id', 'name']);

        $stats = [
            [
                'label' => 'Total Sub-Categories',
                'value' => $total,
                'icon' => 'fas fa-folder-tree',
                'color' => 'info'
            ],
            [
                'label' => 'Featured Sub-Categories',
                'value' => $featured,
                'icon' => 'fas fa-star',
                'color' => 'warning'
            ],
            [
                'label' => 'Popular Sub-Categories',
                'value' => $popular,
                'icon' => 'fas fa-fire',
                'color' => 'danger'
            ],
            [
                'label' => 'With Posts',
                'value' => $withPosts,
                'icon' => 'fas fa-file-alt',
                'color' => 'success'
            ],
            [
                'label' => 'Empty Sub-Categories',
                'value' => $total - $withPosts,
                'icon' => 'fas fa-folder-open',
                'color' => 'secondary'
            ]
        ];

        return response()->json([
            'stats' => $stats,
            'top_categories' => $topCategories
        ]);
    }

    /**
     * Get single sub-category by ID
     */
    public function show($id): JsonResponse
    {
        $subcategory = Sub_categories::with(['photos', 'category'])
            ->withCount('servicePosts')
            ->findOrFail($id);

        $subcategory->image_url = $subcategory->photos->first()?->src ?? null;

        return response()->json($subcategory);
    }

    /**
     * Create new sub-category
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'categories_id' => 'required|exists:categories,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_featured' => 'boolean',
            'is_popular' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $subcategory = Sub_categories::create([
            'name' => [
                'ar' => $request->input('name.ar'),
                'en' => $request->input('name.en')
            ],
            'categories_id' => $request->categories_id,
            'is_featured' => $request->boolean('is_featured'),
            'is_popular' => $request->boolean('is_popular')
        ]);

        // Handle image upload
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $path = $photo->store('subcategories', 'public');

            $subcategory->photos()->create([
                'url' => $path,
                'is_default' => true
            ]);
        }

        $subcategory->load(['photos', 'category']);
        $subcategory->image_url = $subcategory->photos->first()?->src ?? null;

        return response()->json([
            'message' => 'Sub-category created successfully',
            'subcategory' => $subcategory
        ], 201);
    }

    /**
     * Update existing sub-category
     */
    public function update(Request $request, $id): JsonResponse
    {
        $subcategory = Sub_categories::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name.ar' => 'sometimes|required|string|max:255',
            'name.en' => 'sometimes|required|string|max:255',
            'categories_id' => 'sometimes|required|exists:categories,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_featured' => 'boolean',
            'is_popular' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update name if provided
        if ($request->has('name')) {
            $subcategory->name = [
                'ar' => $request->input('name.ar'),
                'en' => $request->input('name.en')
            ];
        }

        // Update other fields
        if ($request->has('categories_id')) {
            $subcategory->categories_id = $request->categories_id;
        }

        if ($request->has('is_featured')) {
            $subcategory->is_featured = $request->boolean('is_featured');
        }

        if ($request->has('is_popular')) {
            $subcategory->is_popular = $request->boolean('is_popular');
        }

        $subcategory->save();

        // Handle image upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($subcategory->photos()->exists()) {
                $oldPhoto = $subcategory->photos()->first();
                if ($oldPhoto && \Storage::disk('public')->exists($oldPhoto->url)) {
                    \Storage::disk('public')->delete($oldPhoto->url);
                }
                $oldPhoto->delete();
            }

            // Upload new photo
            $photo = $request->file('photo');
            $path = $photo->store('subcategories', 'public');

            $subcategory->photos()->create([
                'url' => $path,
                'is_default' => true
            ]);
        }

        $subcategory->load(['photos', 'category']);
        $subcategory->image_url = $subcategory->photos->first()?->src ?? null;

        return response()->json([
            'message' => 'Sub-category updated successfully',
            'subcategory' => $subcategory
        ]);
    }

    /**
     * Delete sub-category
     */
    public function destroy($id): JsonResponse
    {
        $subcategory = Sub_categories::findOrFail($id);

        // Check if sub-category has service posts
        if ($subcategory->servicePosts()->exists()) {
            return response()->json([
                'message' => 'Cannot delete sub-category with existing service posts'
            ], 400);
        }

        // Delete associated photo
        if ($subcategory->photos()->exists()) {
            $photo = $subcategory->photos()->first();
            if ($photo && \Storage::disk('public')->exists($photo->url)) {
                \Storage::disk('public')->delete($photo->url);
            }
            $photo->delete();
        }

        $subcategory->delete();

        return response()->json([
            'message' => 'Sub-category deleted successfully'
        ]);
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured($id): JsonResponse
    {
        $subcategory = Sub_categories::findOrFail($id);
        $subcategory->is_featured = !$subcategory->is_featured;
        $subcategory->save();

        return response()->json([
            'message' => 'Featured status updated',
            'is_featured' => $subcategory->is_featured
        ]);
    }

    /**
     * Toggle popular status
     */
    public function togglePopular($id): JsonResponse
    {
        $subcategory = Sub_categories::findOrFail($id);
        $subcategory->is_popular = !$subcategory->is_popular;
        $subcategory->save();

        return response()->json([
            'message' => 'Popular status updated',
            'is_popular' => $subcategory->is_popular
        ]);
    }
}
