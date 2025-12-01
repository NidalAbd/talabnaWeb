<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PremiumFeature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PremiumFeaturesApiController extends Controller
{
    /**
     * Get paginated premium features
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $type = $request->input('type'); // post_enhancement, user_benefit, system_feature
            $status = $request->input('status'); // all, active, inactive
            $sortBy = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');
            $perPage = $request->input('per_page', 15);

            $query = PremiumFeature::query();

            // Apply search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('feature_type', 'like', "%{$search}%");
                });
            }

            // Apply type filter
            if ($type) {
                $query->where('feature_type', $type);
            }

            // Apply status filter
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }

            // Apply sorting
            $allowedSortFields = ['id', 'name', 'point_cost', 'feature_type', 'created_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortDirection);
            }

            $features = $query->paginate($perPage);

            // Transform data
            $transformedFeatures = $features->getCollection()->map(function ($feature) {
                return [
                    'id' => $feature->id,
                    'name' => $feature->name,
                    'description' => $feature->description,
                    'point_cost' => $feature->point_cost,
                    'feature_type' => $feature->feature_type,
                    'type_label' => $feature->type_label,
                    'is_active' => $feature->is_active,
                    'icon' => $feature->icon,
                    'color' => $feature->color,
                    'created_at' => $feature->created_at->toISOString(),
                ];
            });

            $features->setCollection($transformedFeatures);

            return response()->json([
                'features' => [
                    'data' => $features->items(),
                    'current_page' => $features->currentPage(),
                    'last_page' => $features->lastPage(),
                    'per_page' => $features->perPage(),
                    'total' => $features->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Premium Features API Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load premium features',
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
                    'label' => 'Total Features',
                    'value' => PremiumFeature::count(),
                    'icon' => 'fas fa-star',
                    'color' => 'primary'
                ],
                [
                    'label' => 'Active Features',
                    'value' => PremiumFeature::where('is_active', true)->count(),
                    'icon' => 'fas fa-check-circle',
                    'color' => 'success'
                ],
                [
                    'label' => 'Post Enhancements',
                    'value' => PremiumFeature::where('feature_type', 'post_enhancement')->count(),
                    'icon' => 'fas fa-image',
                    'color' => 'info'
                ],
                [
                    'label' => 'User Benefits',
                    'value' => PremiumFeature::where('feature_type', 'user_benefit')->count(),
                    'icon' => 'fas fa-user-crown',
                    'color' => 'warning'
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
     * Create a new premium feature
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'point_cost' => 'required|integer|min:1',
            'feature_type' => 'required|string|in:post_enhancement,user_benefit,system_feature',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean'
        ]);

        try {
            $featureData = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'point_cost' => $validated['point_cost'],
                'feature_type' => $validated['feature_type'],
                'icon' => $validated['icon'] ?? null,
                'color' => $validated['color'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ];

            $feature = PremiumFeature::create($featureData);

            return response()->json([
                'message' => 'Premium feature created successfully',
                'feature' => $feature
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating premium feature: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to create premium feature',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a premium feature
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'point_cost' => 'required|integer|min:1',
            'feature_type' => 'required|string|in:post_enhancement,user_benefit,system_feature',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean'
        ]);

        try {
            $feature = PremiumFeature::findOrFail($id);

            $updateData = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'point_cost' => $validated['point_cost'],
                'feature_type' => $validated['feature_type'],
                'icon' => $validated['icon'] ?? null,
                'color' => $validated['color'] ?? null,
                'is_active' => $validated['is_active'] ?? false,
            ];

            $feature->update($updateData);

            return response()->json([
                'message' => 'Premium feature updated successfully',
                'feature' => $feature->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating premium feature: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to update premium feature',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a premium feature
     */
    public function destroy($id): JsonResponse
    {
        try {
            $feature = PremiumFeature::findOrFail($id);
            $feature->delete();

            return response()->json([
                'message' => 'Premium feature deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting premium feature: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to delete premium feature',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle feature status
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $feature = PremiumFeature::findOrFail($id);
            $feature->update(['is_active' => !$feature->is_active]);

            return response()->json([
                'message' => 'Feature status updated successfully',
                'is_active' => $feature->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling feature status: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to toggle feature status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get feature types for dropdown
     */
    public function getTypes(): JsonResponse
    {
        try {
            $types = [
                ['value' => 'post_enhancement', 'label' => 'Post Enhancement'],
                ['value' => 'user_benefit', 'label' => 'User Benefit'],
                ['value' => 'system_feature', 'label' => 'System Feature'],
            ];

            return response()->json(['types' => $types]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load feature types',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
