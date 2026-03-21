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

    private function localizedName($nameField): string
    {
        if (is_string($nameField)) return $nameField;
        if (is_array($nameField)) {
            $locale = app()->getLocale();
            return $nameField[$locale] ?? $nameField['ar'] ?? $nameField['en'] ?? array_values(array_filter($nameField))[0] ?? '';
        }
        return '';
    }

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
                    'title' => $post->translate('title') ?? $this->localizedName($post->title),
                    'description' => $post->translate('description') ?? $this->localizedName($post->description),
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
                        'name' => $this->localizedName($post->category->name),
                    ] : null,
                    'sub_category' => $post->subCategory ? [
                        'id' => $post->subCategory->id,
                        'name' => $this->localizedName($post->subCategory->name),
                    ] : null,
                    'country' => $post->country ? [
                        'id' => $post->country->id,
                        'name' => $this->localizedName($post->country->name),
                    ] : null,
                    'city' => $post->city ? [
                        'id' => $post->city->id,
                        'name' => $this->localizedName($post->city->name),
                    ] : null,
                    'media' => $post->photos->first() ? [
                        'src' => $post->photos->first()->src,
                        'is_video' => (bool) $post->photos->first()->isVideo,
                        'is_external' => (bool) $post->photos->first()->is_external,
                    ] : null,
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
     * Get a single service post with all details for view/edit
     */
    public function show($id): JsonResponse
    {
        try {
            $post = ServicePost::with(['photos', 'user', 'category', 'subCategory', 'country', 'city', 'badgeType'])
                ->findOrFail($id);

            $titleRaw = $post->getRawTranslations('title') ?? ['ar' => $post->title];
            $descRaw = $post->getRawTranslations('description') ?? ['ar' => $post->description];

            return response()->json([
                'post' => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'title_translations' => $titleRaw,
                    'description' => $post->description,
                    'description_translations' => $descRaw,
                    'price' => $post->price,
                    'price_currency_code' => $post->price_currency_code,
                    'price_currency_name' => $post->price_currency_name,
                    'type' => $post->type,
                    'state' => $post->state,
                    'have_badge' => $post->have_badge,
                    'badge_type_id' => $post->badge_type_id,
                    'badge_duration' => $post->badge_duration,
                    'badge_expires_at' => $post->badge_expires_at?->toISOString(),
                    'view_count' => $post->view_count ?? 0,
                    'favorites_count' => $post->favorites_count ?? 0,
                    'report_count' => $post->report_count ?? 0,
                    'location_latitudes' => $post->location_latitudes,
                    'location_longitudes' => $post->location_longitudes,
                    'categories_id' => $post->categories_id,
                    'sub_categories_id' => $post->sub_categories_id,
                    'country_id' => $post->country_id,
                    'city_id' => $post->city_id,
                    'user' => $post->user ? [
                        'id' => $post->user->id,
                        'user_name' => $post->user->user_name,
                        'email' => $post->user->email,
                    ] : null,
                    'category' => $post->category ? [
                        'id' => $post->category->id,
                        'name' => $this->localizedName($post->category->name),
                    ] : null,
                    'sub_category' => $post->subCategory ? [
                        'id' => $post->subCategory->id,
                        'name' => $this->localizedName($post->subCategory->name),
                    ] : null,
                    'country' => $post->country ? [
                        'id' => $post->country->id,
                        'name' => $this->localizedName($post->country->name),
                    ] : null,
                    'city' => $post->city ? [
                        'id' => $post->city->id,
                        'name' => $this->localizedName($post->city->name),
                    ] : null,
                    'badge' => $post->badgeType ? [
                        'id' => $post->badgeType->id,
                        'name_ar' => $post->badgeType->name_ar,
                        'name_en' => $post->badgeType->name_en,
                        'color' => $post->badgeType->color,
                    ] : null,
                    'photos' => $post->photos->map(fn($p) => [
                        'id' => $p->id,
                        'src' => $p->is_external ? $p->src : '/storage/' . ltrim($p->src, '/'),
                        'is_external' => (bool) $p->is_external,
                        'is_video' => (bool) $p->isVideo,
                    ]),
                    'created_at' => $post->created_at->toISOString(),
                    'updated_at' => $post->updated_at->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Post not found', 'message' => $e->getMessage()], 404);
        }
    }

    /**
     * Update a service post from admin panel
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $post = ServicePost::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|array',
                'description' => 'sometimes|array',
                'price' => 'sometimes|numeric|min:0',
                'type' => 'sometimes|string',
                'state' => 'sometimes|string|in:published,not published',
                'categories_id' => 'sometimes|integer|exists:categories,id',
                'sub_categories_id' => 'sometimes|integer|exists:sub_categories,id',
                'country_id' => 'sometimes|integer|exists:countries,id',
                'city_id' => 'sometimes|integer|exists:cities,id',
            ]);

            // Handle title translations
            if ($request->has('title')) {
                $post->setAttribute('title', $request->input('title'));
            }
            if ($request->has('description')) {
                $post->setAttribute('description', $request->input('description'));
            }

            // Update simple fields
            foreach (['price', 'type', 'state', 'categories_id', 'sub_categories_id', 'country_id', 'city_id'] as $field) {
                if ($request->has($field)) {
                    $post->$field = $request->input($field);
                }
            }

            $post->save();

            return response()->json([
                'message' => 'Service post updated successfully',
                'post' => $post->fresh()->toArray(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update post', 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * Admin: Change post badge type and duration (including unlimited)
     */
    public function changeBadge(Request $request, $id): JsonResponse
    {
        try {
            $post = ServicePost::findOrFail($id);

            $validated = $request->validate([
                'badge_type_id' => 'required|integer|exists:badge_types,id',
                'duration' => 'required',
            ]);

            $badgeType = \App\Models\BadgeType::findOrFail($validated['badge_type_id']);
            $duration = $validated['duration'];

            $post->badge_type_id = $badgeType->id;
            $post->have_badge = $badgeType->slug ?? $badgeType->name_ar ?? $badgeType->name;

            if ($duration === 'unlimited' || $duration === 0 || $duration === '0') {
                $post->badge_duration = 36500;
                $post->badge_expires_at = now()->addYears(100);
            } else {
                $days = (int) $duration;
                $post->badge_duration = $days;
                $post->badge_expires_at = now()->addDays($days);
            }

            $post->save();

            return response()->json([
                'message' => "Badge changed to {$badgeType->name_en}",
                'post' => [
                    'id' => $post->id,
                    'have_badge' => $post->have_badge,
                    'badge_type_id' => $post->badge_type_id,
                    'badge_duration' => $post->badge_duration,
                    'badge_expires_at' => $post->badge_expires_at?->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to change badge', 'message' => $e->getMessage()], 500);
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
