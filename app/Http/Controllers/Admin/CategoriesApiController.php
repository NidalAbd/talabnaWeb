<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\Photos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoriesApiController extends Controller
{
    /**
     * Get categories list with filters, search, and pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Categories::with(['photos', 'sub_categories'])
                ->withCount(['servicePosts', 'sub_categories']);

            // Search filter (searches in both AR and EN names)
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"]);
                });
            }

            // Status filter
            if ($request->has('status')) {
                if ($request->status === 'active') {
                    $query->where('isSuspended', false);
                } elseif ($request->status === 'suspended') {
                    $query->where('isSuspended', true);
                }
            }

            // Featured filter
            if ($request->has('featured') && $request->featured !== 'all') {
                $query->where('is_featured', $request->featured === 'true');
            }

            // Popular filter
            if ($request->has('popular') && $request->popular !== 'all') {
                $query->where('is_popular', $request->popular === 'true');
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'id');
            $sortDirection = $request->get('sort_direction', 'desc');

            switch ($sortBy) {
                case 'name':
                    $query->orderByRaw("JSON_EXTRACT(name, '$.en') {$sortDirection}");
                    break;
                case 'posts_count':
                    $query->orderBy('service_posts_count', $sortDirection);
                    break;
                case 'subcategories_count':
                    $query->orderBy('sub_categories_count', $sortDirection);
                    break;
                case 'created_at':
                    $query->orderBy('created_at', $sortDirection);
                    break;
                default:
                    $query->orderBy('id', $sortDirection);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $categories = $query->paginate($perPage);

            // Transform data
            $categories->getCollection()->transform(function ($category) {
                return $this->transformCategory($category);
            });

            return response()->json($categories);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load categories',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get category statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $totalCategories = Categories::count();
            $featuredCategories = Categories::where('is_featured', true)->count();
            $popularCategories = Categories::where('is_popular', true)->count();
            $suspendedCategories = Categories::where('isSuspended', true)->count();
            $activeCategories = Categories::where('isSuspended', false)->count();
            $totalSubCategories = DB::table('sub_categories')->count();
            $totalServicePosts = DB::table('service_posts')->count();

            $stats = [
                [
                    'label' => 'Total Categories',
                    'value' => $totalCategories,
                    'icon' => 'fas fa-folder',
                    'color' => 'info'
                ],
                [
                    'label' => 'Active Categories',
                    'value' => $activeCategories,
                    'icon' => 'fas fa-check-circle',
                    'color' => 'success'
                ],
                [
                    'label' => 'Featured Categories',
                    'value' => $featuredCategories,
                    'icon' => 'fas fa-star',
                    'color' => 'warning'
                ],
                [
                    'label' => 'Popular Categories',
                    'value' => $popularCategories,
                    'icon' => 'fas fa-fire',
                    'color' => 'danger'
                ],
                [
                    'label' => 'Suspended Categories',
                    'value' => $suspendedCategories,
                    'icon' => 'fas fa-ban',
                    'color' => 'danger'
                ],
                [
                    'label' => 'Total Subcategories',
                    'value' => $totalSubCategories,
                    'icon' => 'fas fa-folder-open',
                    'color' => 'info'
                ],
                [
                    'label' => 'Total Service Posts',
                    'value' => $totalServicePosts,
                    'icon' => 'fas fa-clipboard-list',
                    'color' => 'primary'
                ]
            ];

            return response()->json(['stats' => $stats]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load stats',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single category details
     */
    public function show($id): JsonResponse
    {
        try {
            $category = Categories::with(['photos', 'sub_categories.photos'])
                ->withCount(['servicePosts', 'sub_categories'])
                ->findOrFail($id);

            return response()->json([
                'category' => $this->transformCategory($category, true)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Category not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create new category
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_featured' => 'boolean',
            'is_popular' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create category
            $category = new Categories([
                'name' => [
                    'ar' => $request->input('name.ar'),
                    'en' => $request->input('name.en')
                ],
                'is_featured' => $request->input('is_featured', false),
                'is_popular' => $request->input('is_popular', false),
                'isSuspended' => false
            ]);

            $category->save();

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $this->uploadCategoryPhoto($category, $request->file('photo'));
            }

            DB::commit();

            return response()->json([
                'message' => 'Category created successfully',
                'category' => $this->transformCategory($category->load(['photos']))
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create category',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update category
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_featured' => 'boolean',
            'is_popular' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $category = Categories::findOrFail($id);

            DB::beginTransaction();

            // Update category
            $category->name = [
                'ar' => $request->input('name.ar'),
                'en' => $request->input('name.en')
            ];
            $category->is_featured = $request->input('is_featured', false);
            $category->is_popular = $request->input('is_popular', false);

            $category->save();

            // Handle gallery image selection
            if ($request->has('gallery_image') && $request->gallery_image) {
                $galleryPath = $request->gallery_image;
                if (Storage::disk('public')->exists($galleryPath)) {
                    // Delete old photo record
                    $oldPhoto = $category->photos()->first();
                    if ($oldPhoto) {
                        $this->deletePhoto($oldPhoto);
                        $oldPhoto->delete();
                    }

                    // Copy gallery image to active category folder
                    $fileName = \Illuminate\Support\Str::uuid() . '.png';
                    $newPath = "category/{$fileName}";
                    Storage::disk('public')->copy($galleryPath, $newPath);

                    $photo = new Photos([
                        'src' => 'storage/' . $newPath,
                    ]);
                    $category->photos()->save($photo);
                }
            }
            // Handle photo upload if new photo provided
            elseif ($request->hasFile('photo')) {
                // Delete old photo
                $oldPhoto = $category->photos()->first();
                if ($oldPhoto) {
                    $this->deletePhoto($oldPhoto);
                    $oldPhoto->delete();
                }

                // Upload new photo
                $this->uploadCategoryPhoto($category, $request->file('photo'));
            }

            DB::commit();

            return response()->json([
                'message' => 'Category updated successfully',
                'category' => $this->transformCategory($category->load(['photos']))
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to update category',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete category
     */
    public function destroy($id): JsonResponse
    {
        try {
            $category = Categories::findOrFail($id);

            // Check if category has subcategories
            if ($category->sub_categories()->count() > 0) {
                return response()->json([
                    'error' => 'Cannot delete category',
                    'message' => 'This category has subcategories. Please delete them first.'
                ], 400);
            }

            // Check if category has service posts
            if ($category->servicePosts()->count() > 0) {
                return response()->json([
                    'error' => 'Cannot delete category',
                    'message' => 'This category has service posts. Please reassign or delete them first.'
                ], 400);
            }

            DB::beginTransaction();

            // Delete photos
            foreach ($category->photos as $photo) {
                $this->deletePhoto($photo);
                $photo->delete();
            }

            // Delete category
            $category->delete();

            DB::commit();

            return response()->json([
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to delete category',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle category suspended status
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $category = Categories::findOrFail($id);
            $category->isSuspended = !$category->isSuspended;
            $category->save();

            return response()->json([
                'message' => 'Category status updated successfully',
                'is_suspended' => $category->isSuspended
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured($id): JsonResponse
    {
        try {
            $category = Categories::findOrFail($id);
            $category->is_featured = !$category->is_featured;
            $category->save();

            return response()->json([
                'message' => 'Featured status updated successfully',
                'is_featured' => $category->is_featured
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update featured status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle popular status
     */
    public function togglePopular($id): JsonResponse
    {
        try {
            $category = Categories::findOrFail($id);
            $category->is_popular = !$category->is_popular;
            $category->save();

            return response()->json([
                'message' => 'Popular status updated successfully',
                'is_popular' => $category->is_popular
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update popular status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transform category data for API response
     */
    private function transformCategory($category, $detailed = false): array
    {
        $photo = $category->photos->first();

        $data = [
            'id' => $category->id,
            'name' => $category->name,
            'image_url' => $photo ? url($photo->src) : null,
            'is_featured' => (bool) $category->is_featured,
            'is_popular' => (bool) $category->is_popular,
            'is_suspended' => (bool) $category->isSuspended,
            'service_posts_count' => $category->service_posts_count ?? 0,
            'sub_categories_count' => $category->sub_categories_count ?? 0,
            'created_at' => $category->created_at?->toISOString(),
            'updated_at' => $category->updated_at?->toISOString(),
        ];

        if ($detailed) {
            $data['sub_categories'] = $category->sub_categories->map(function ($subcat) {
                $photo = $subcat->photos->first();
                return [
                    'id' => $subcat->id,
                    'name' => $subcat->name,
                    'image_url' => $photo ? url($photo->src) : null,
                    'is_suspended' => (bool) $subcat->isSuspended,
                ];
            });
        }

        return $data;
    }

    /**
     * Upload category photo
     */
    private function uploadCategoryPhoto($category, $file): void
    {
        $fileName = $file->hashName();
        $storeImage = $file->storeAs("category", $fileName, 'public');
        $photoPath = 'storage/' . $storeImage;

        $photo = new Photos([
            'src' => $photoPath,
        ]);

        $category->photos()->save($photo);
    }

    /**
     * Delete photo from storage
     */
    private function deletePhoto($photo): void
    {
        $path = str_replace('storage/', '', $photo->src);
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
