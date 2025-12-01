<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LevelsApiController extends Controller
{
    /**
     * Get paginated levels
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $status = $request->input('status'); // all, active, inactive
            $premium = $request->input('premium'); // all, premium, regular
            $sortBy = $request->input('sort_by', 'display_order');
            $sortDirection = $request->input('sort_direction', 'asc');
            $perPage = $request->input('per_page', 15);

            $query = Level::query();

            // Apply search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->whereRaw('JSON_EXTRACT(name, "$.ar") LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('JSON_EXTRACT(name, "$.en") LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('JSON_EXTRACT(description, "$.ar") LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('JSON_EXTRACT(description, "$.en") LIKE ?', ["%{$search}%"]);
                });
            }

            // Apply status filter
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }

            // Apply premium filter
            if ($premium === 'premium') {
                $query->where('is_premium', true);
            } elseif ($premium === 'regular') {
                $query->where('is_premium', false);
            }

            // Apply sorting
            $allowedSortFields = ['id', 'display_order', 'points_per_day', 'view_boost_percentage', 'created_at'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortDirection);
            }

            $levels = $query->paginate($perPage);

            // Transform data
            $transformedLevels = $levels->getCollection()->map(function ($level) {
                return [
                    'id' => $level->id,
                    'name' => $level->name,
                    'description' => $level->description,
                    'icon' => $level->icon,
                    'color' => $level->color,
                    'points_per_day' => $level->points_per_day,
                    'view_boost_percentage' => $level->view_boost_percentage,
                    'display_order' => $level->display_order,
                    'is_active' => $level->is_active,
                    'is_premium' => $level->is_premium,
                    'features' => $level->features,
                    'service_posts_count' => $level->servicePosts()->count(),
                    'created_at' => $level->created_at->toISOString(),
                ];
            });

            $levels->setCollection($transformedLevels);

            return response()->json([
                'levels' => [
                    'data' => $levels->items(),
                    'current_page' => $levels->currentPage(),
                    'last_page' => $levels->lastPage(),
                    'per_page' => $levels->perPage(),
                    'total' => $levels->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Levels API Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to load levels',
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
                    'label' => 'Total Levels',
                    'value' => Level::count(),
                    'icon' => 'fas fa-layer-group',
                    'color' => 'primary'
                ],
                [
                    'label' => 'Active Levels',
                    'value' => Level::where('is_active', true)->count(),
                    'icon' => 'fas fa-check-circle',
                    'color' => 'success'
                ],
                [
                    'label' => 'Premium Levels',
                    'value' => Level::where('is_premium', true)->count(),
                    'icon' => 'fas fa-crown',
                    'color' => 'warning'
                ],
                [
                    'label' => 'Inactive Levels',
                    'value' => Level::where('is_active', false)->count(),
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
     * Create a new level
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'required|string|max:7',
            'points_per_day' => 'required|integer|min:0',
            'view_boost_percentage' => 'required|integer|min:0',
            'display_order' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'is_premium' => 'boolean',
            'features.ar' => 'nullable|array',
            'features.en' => 'nullable|array',
        ]);

        try {
            $levelData = [
                'name' => [
                    'ar' => $validated['name']['ar'],
                    'en' => $validated['name']['en']
                ],
                'description' => [
                    'ar' => $validated['description']['ar'] ?? '',
                    'en' => $validated['description']['en'] ?? ''
                ],
                'icon' => $validated['icon'] ?? null,
                'color' => $validated['color'],
                'points_per_day' => $validated['points_per_day'],
                'view_boost_percentage' => $validated['view_boost_percentage'],
                'display_order' => $validated['display_order'],
                'is_active' => $validated['is_active'] ?? true,
                'is_premium' => $validated['is_premium'] ?? false,
                'features' => [
                    'ar' => $validated['features']['ar'] ?? [],
                    'en' => $validated['features']['en'] ?? []
                ],
            ];

            $level = Level::create($levelData);

            return response()->json([
                'message' => 'Level created successfully',
                'level' => $level
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating level: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to create level',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a level
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'required|string|max:7',
            'points_per_day' => 'required|integer|min:0',
            'view_boost_percentage' => 'required|integer|min:0',
            'display_order' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'is_premium' => 'boolean',
            'features.ar' => 'nullable|array',
            'features.en' => 'nullable|array',
        ]);

        try {
            $level = Level::findOrFail($id);

            $updateData = [
                'name' => [
                    'ar' => $validated['name']['ar'],
                    'en' => $validated['name']['en']
                ],
                'description' => [
                    'ar' => $validated['description']['ar'] ?? '',
                    'en' => $validated['description']['en'] ?? ''
                ],
                'icon' => $validated['icon'] ?? null,
                'color' => $validated['color'],
                'points_per_day' => $validated['points_per_day'],
                'view_boost_percentage' => $validated['view_boost_percentage'],
                'display_order' => $validated['display_order'],
                'is_active' => $validated['is_active'] ?? false,
                'is_premium' => $validated['is_premium'] ?? false,
                'features' => [
                    'ar' => $validated['features']['ar'] ?? [],
                    'en' => $validated['features']['en'] ?? []
                ],
            ];

            $level->update($updateData);

            return response()->json([
                'message' => 'Level updated successfully',
                'level' => $level->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating level: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to update level',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a level
     */
    public function destroy($id): JsonResponse
    {
        try {
            $level = Level::findOrFail($id);

            // Check if level is being used
            if ($level->servicePosts()->count() > 0) {
                return response()->json([
                    'error' => 'Cannot delete level that is being used by service posts',
                    'message' => 'This level is currently assigned to ' . $level->servicePosts()->count() . ' service posts.'
                ], 422);
            }

            $level->delete();

            return response()->json([
                'message' => 'Level deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting level: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to delete level',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle level status
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $level = Level::findOrFail($id);
            $level->update(['is_active' => !$level->is_active]);

            return response()->json([
                'message' => 'Level status updated successfully',
                'is_active' => $level->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling level status: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to toggle level status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate a level
     */
    public function duplicate($id): JsonResponse
    {
        try {
            $level = Level::findOrFail($id);

            $newLevel = $level->replicate();
            $newLevel->name = [
                'ar' => ($level->name['ar'] ?? '') . ' (نسخة)',
                'en' => ($level->name['en'] ?? '') . ' (Copy)'
            ];
            $newLevel->is_active = false;
            $newLevel->display_order = Level::max('display_order') + 1;
            $newLevel->save();

            return response()->json([
                'message' => 'Level duplicated successfully',
                'level' => $newLevel
            ]);
        } catch (\Exception $e) {
            Log::error('Error duplicating level: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to duplicate level',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update level order
     */
    public function updateOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'levels' => 'required|array',
            'levels.*.id' => 'required|exists:levels,id',
            'levels.*.display_order' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                foreach ($validated['levels'] as $levelData) {
                    Level::where('id', $levelData['id'])
                        ->update(['display_order' => $levelData['display_order']]);
                }
            });

            return response()->json([
                'message' => 'Level order updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating level order: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to update level order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk activate levels
     */
    public function bulkActivate(): JsonResponse
    {
        try {
            Level::where('is_active', false)->update(['is_active' => true]);

            return response()->json([
                'message' => 'All levels activated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error bulk activating levels: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to activate levels',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk deactivate levels
     */
    public function bulkDeactivate(): JsonResponse
    {
        try {
            Level::where('is_active', true)->update(['is_active' => false]);

            return response()->json([
                'message' => 'All levels deactivated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error bulk deactivating levels: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to deactivate levels',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
