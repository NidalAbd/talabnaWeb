<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointPackagesApiController extends Controller
{
    /**
     * Get paginated point packages
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $status = $request->input('status'); // all, active, inactive
            $popular = $request->input('popular'); // true, false, all
            $sortBy = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $perPage = $request->input('per_page', 15);

            $query = PointPackage::query();

            // Apply search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->whereRaw('JSON_EXTRACT(name, "$.ar") LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('JSON_EXTRACT(name, "$.en") LIKE ?', ["%{$search}%"])
                        ->orWhere('points_amount', 'like', "%{$search}%")
                        ->orWhere('price', 'like', "%{$search}%");
                });
            }

            // Apply status filter
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }

            // Apply popular filter
            if ($popular === 'true') {
                $query->where('is_popular', true);
            } elseif ($popular === 'false') {
                $query->where('is_popular', false);
            }

            // Apply sorting
            $allowedSortFields = ['id', 'points_amount', 'price', 'display_order', 'created_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortDirection);
            }

            $packages = $query->paginate($perPage);

            // Transform data
            $transformedPackages = $packages->getCollection()->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'description' => $package->description,
                    'points_amount' => $package->points_amount,
                    'price' => $package->price,
                    'currency_code' => $package->currency_code,
                    'validity_days' => $package->validity_days,
                    'discount_percentage' => $package->discount_percentage ?? 0,
                    'is_active' => $package->is_active,
                    'is_popular' => $package->is_popular,
                    'display_order' => $package->display_order,
                    'features' => $package->features,
                    'icon' => $package->icon,
                    'color' => $package->color,
                    'created_at' => $package->created_at->toISOString(),
                ];
            });

            $packages->setCollection($transformedPackages);

            return response()->json([
                'packages' => [
                    'data' => $packages->items(),
                    'current_page' => $packages->currentPage(),
                    'last_page' => $packages->lastPage(),
                    'per_page' => $packages->perPage(),
                    'total' => $packages->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Point Packages API Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load point packages',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                [
                    'label' => 'Total Packages',
                    'value' => PointPackage::count(),
                    'icon' => 'fas fa-box',
                    'color' => 'primary'
                ],
                [
                    'label' => 'Active Packages',
                    'value' => PointPackage::where('is_active', true)->count(),
                    'icon' => 'fas fa-check-circle',
                    'color' => 'success'
                ],
                [
                    'label' => 'Popular Packages',
                    'value' => PointPackage::where('is_popular', true)->count(),
                    'icon' => 'fas fa-star',
                    'color' => 'warning'
                ],
                [
                    'label' => 'Inactive Packages',
                    'value' => PointPackage::where('is_active', false)->count(),
                    'icon' => 'fas fa-times-circle',
                    'color' => 'danger'
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
     * Create a new point package
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'points' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'duration_days' => 'required|integer|min:1',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            'features.ar' => 'nullable|string',
            'features.en' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'display_order' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_popular' => 'boolean'
        ]);

        try {
            $packageData = [
                'name' => [
                    'ar' => $validated['name']['ar'],
                    'en' => $validated['name']['en']
                ],
                'description' => [
                    'ar' => $validated['description']['ar'] ?? '',
                    'en' => $validated['description']['en'] ?? ''
                ],
                'points_amount' => $validated['points'],
                'price' => $validated['price'],
                'currency_code' => $validated['currency'],
                'validity_days' => $validated['duration_days'],
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'is_active' => $validated['is_active'] ?? true,
                'is_popular' => $validated['is_popular'] ?? false,
                'display_order' => $validated['display_order'] ?? 0,
                'features' => [
                    'ar' => $validated['features']['ar'] ?? '',
                    'en' => $validated['features']['en'] ?? ''
                ],
                'icon' => $validated['icon'] ?? null,
                'color' => $validated['color'] ?? null,
            ];

            $package = PointPackage::create($packageData);

            return response()->json([
                'message' => 'Point package created successfully',
                'package' => $package
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating point package: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to create point package',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a point package
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'points' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'duration_days' => 'required|integer|min:1',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            'features.ar' => 'nullable',  // Accept both string and array
            'features.en' => 'nullable',  // Accept both string and array
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_popular' => 'boolean'
        ]);

        try {
            $package = PointPackage::findOrFail($id);

            // Handle features - convert arrays to newline-separated strings
            $featuresAr = $validated['features']['ar'] ?? '';
            $featuresEn = $validated['features']['en'] ?? '';
            if (is_array($featuresAr)) {
                $featuresAr = implode("\n", $featuresAr);
            }
            if (is_array($featuresEn)) {
                $featuresEn = implode("\n", $featuresEn);
            }

            $updateData = [
                'name' => [
                    'ar' => $validated['name']['ar'],
                    'en' => $validated['name']['en']
                ],
                'description' => [
                    'ar' => $validated['description']['ar'] ?? '',
                    'en' => $validated['description']['en'] ?? ''
                ],
                'points_amount' => $validated['points'],
                'price' => $validated['price'],
                'currency_code' => $validated['currency'],
                'validity_days' => $validated['duration_days'],
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'is_active' => $validated['is_active'] ?? false,
                'is_popular' => $validated['is_popular'] ?? false,
                'display_order' => $validated['display_order'] ?? 0,
                'features' => [
                    'ar' => $featuresAr,
                    'en' => $featuresEn
                ],
                'icon' => $validated['icon'] ?? null,
                'color' => $validated['color'] ?? null,
            ];

            $package->update($updateData);

            return response()->json([
                'message' => 'Point package updated successfully',
                'package' => $package->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating point package: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to update point package',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a point package
     */
    public function destroy($id): JsonResponse
    {
        try {
            $package = PointPackage::findOrFail($id);
            $package->delete();

            return response()->json([
                'message' => 'Point package deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting point package: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to delete point package',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle package status
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $package = PointPackage::findOrFail($id);
            $package->update(['is_active' => !$package->is_active]);

            return response()->json([
                'message' => 'Package status updated successfully',
                'is_active' => $package->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling package status: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to toggle package status',
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
            $package = PointPackage::findOrFail($id);
            $package->update(['is_popular' => !$package->is_popular]);

            return response()->json([
                'message' => 'Package popular status updated successfully',
                'is_popular' => $package->is_popular
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling popular status: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to toggle popular status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate a package
     */
    public function duplicate($id): JsonResponse
    {
        try {
            $package = PointPackage::findOrFail($id);

            $newPackage = $package->replicate();
            $newPackage->name = [
                'ar' => ($package->name['ar'] ?? '') . ' (نسخة)',
                'en' => ($package->name['en'] ?? '') . ' (Copy)'
            ];
            $newPackage->is_popular = false;
            $newPackage->save();

            return response()->json([
                'message' => 'Package duplicated successfully',
                'package' => $newPackage
            ]);
        } catch (\Exception $e) {
            Log::error('Error duplicating package: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to duplicate package',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk activate packages
     */
    public function bulkActivate(): JsonResponse
    {
        try {
            PointPackage::where('is_active', false)->update(['is_active' => true]);

            return response()->json([
                'message' => 'All packages activated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error bulk activating packages: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to activate packages',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk deactivate packages
     */
    public function bulkDeactivate(): JsonResponse
    {
        try {
            PointPackage::where('is_active', true)->update(['is_active' => false]);

            return response()->json([
                'message' => 'All packages deactivated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error bulk deactivating packages: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to deactivate packages',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
