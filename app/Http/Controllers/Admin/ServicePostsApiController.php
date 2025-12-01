<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServicePost;
use App\Models\Categories;
use App\Models\Sub_categories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServicePostsApiController extends Controller
{
    /**
     * Get paginated service posts with filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $category = $request->input('category');
            $subcategory = $request->input('subcategory');
            $status = $request->input('status');
            $type = $request->input('type');
            $sortBy = $request->input('sort_by', 'id');
            $sortDirection = $request->input('sort_direction', 'desc');
            $perPage = $request->input('per_page', 25);

            // Base query with relationships
            $query = ServicePost::with(['photos', 'user', 'category', 'subCategory', 'country', 'city']);

            // Apply category filter
            if ($category) {
                $query->where('categories_id', $category);
            }

            // Apply subcategory filter
            if ($subcategory) {
                $query->where('sub_categories_id', $subcategory);
            }

            // Apply status filter
            if ($status) {
                $query->where('state', $status);
            }

            // Apply type filter
            if ($type) {
                $query->where('type', $type);
            }

            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('user', function($userQuery) use ($search) {
                            $userQuery->where('user_name', 'like', "%{$search}%");
                        });
                });
            }

            // Apply sorting
            $allowedSortFields = ['id', 'title', 'created_at', 'view_count', 'state'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Get paginated posts
            $servicePosts = $query->paginate($perPage);

            // Transform data for Vue
            $transformedPosts = $servicePosts->getCollection()->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'description' => $post->description,
                    'price' => $post->price,
                    'price_currency_code' => $post->price_currency_code,
                    'type' => $post->type,
                    'state' => $post->state,
                    'have_badge' => $post->have_badge,
                    'view_count' => $post->view_count ?? 0,
                    'favorites_count' => $post->favorites_count ?? 0,
                    'user' => $post->user ? [
                        'id' => $post->user->id,
                        'user_name' => $post->user->user_name,
                    ] : null,
                    'category' => $post->category ? [
                        'id' => $post->category->id,
                        'name' => $post->category->name,
                    ] : null,
                    'sub_category' => $post->subCategory ? [
                        'id' => $post->subCategory->id,
                        'name' => $post->subCategory->name,
                    ] : null,
                    'country' => $post->country ? [
                        'id' => $post->country->id,
                        'name' => $post->country->name,
                    ] : null,
                    'city' => $post->city ? [
                        'id' => $post->city->id,
                        'name' => $post->city->name,
                    ] : null,
                    'image_url' => $post->photos->first()?->src ?? null,
                    'photos_count' => $post->photos->count(),
                    'created_at' => $post->created_at->toISOString(),
                    'updated_at' => $post->updated_at->toISOString(),
                ];
            });

            // Set the transformed collection back
            $servicePosts->setCollection($transformedPosts);

            return response()->json([
                'service_posts' => [
                    'data' => $servicePosts->items(),
                    'current_page' => $servicePosts->currentPage(),
                    'last_page' => $servicePosts->lastPage(),
                    'per_page' => $servicePosts->perPage(),
                    'total' => $servicePosts->total(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Service Posts API Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'error' => 'Failed to load service posts',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get service post statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                [
                    'label' => 'Total Posts',
                    'value' => ServicePost::count(),
                    'icon' => 'fas fa-clipboard-list',
                    'color' => 'info'
                ],
                [
                    'label' => 'Published',
                    'value' => ServicePost::where('state', 'published')->count(),
                    'icon' => 'fas fa-check-circle',
                    'color' => 'success'
                ],
                [
                    'label' => 'Pending',
                    'value' => ServicePost::where('state', 'not published')->count(),
                    'icon' => 'fas fa-clock',
                    'color' => 'warning'
                ],
                [
                    'label' => 'With Badges',
                    'value' => ServicePost::whereIn('have_badge', ['ذهبي', 'ماسي'])->count(),
                    'icon' => 'fas fa-award',
                    'color' => 'primary'
                ]
            ];

            return response()->json(['stats' => $stats]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a service post
     */
    public function destroy($id): JsonResponse
    {
        try {
            $post = ServicePost::findOrFail($id);

            // Delete associated photos
            foreach ($post->photos as $photo) {
                if (\Storage::disk('public')->exists($photo->src)) {
                    \Storage::disk('public')->delete($photo->src);
                }
                $photo->delete();
            }

            $post->delete();

            return response()->json([
                'message' => 'Service post deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete service post',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete service posts
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        try {
            $postIds = $request->input('post_ids', []);

            if (empty($postIds)) {
                return response()->json([
                    'error' => 'No posts selected'
                ], 400);
            }

            $posts = ServicePost::whereIn('id', $postIds)->get();

            foreach ($posts as $post) {
                // Delete associated photos
                foreach ($post->photos as $photo) {
                    if (\Storage::disk('public')->exists($photo->src)) {
                        \Storage::disk('public')->delete($photo->src);
                    }
                    $photo->delete();
                }

                $post->delete();
            }

            return response()->json([
                'message' => count($postIds) . ' service posts deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete service posts',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories for filter dropdown
     */
    public function getCategories(): JsonResponse
    {
        try {
            $categories = Categories::select('id', 'name')->get();

            return response()->json([
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load categories',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subcategories for a category
     */
    public function getSubcategories(Request $request): JsonResponse
    {
        try {
            $categoryId = $request->input('category_id');

            if (!$categoryId) {
                return response()->json([
                    'subcategories' => []
                ]);
            }

            $subcategories = Sub_categories::where('categories_id', $categoryId)
                ->select('id', 'name')
                ->get();

            return response()->json([
                'subcategories' => $subcategories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load subcategories',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
