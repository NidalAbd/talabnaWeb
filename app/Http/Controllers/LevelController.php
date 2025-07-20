<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LevelController extends Controller
{
    /**
     * Display a listing of the levels.
     */
    public function index()
    {
        Log::info('LevelController@index called', ['is_ajax' => request()->ajax()]);
        
        $levels = Level::orderBy('display_order')->get();
        
        if (request()->ajax()) {
            Log::info('Returning AJAX response for levels index');
            return response()->json([
                'success' => true,
                'levels' => $levels
            ]);
        }
        
        return view('admin.levels.index', compact('levels'));
    }

    /**
     * Show the form for creating a new level.
     */
    public function create()
    {
        Log::info('LevelController@create called');
        return view('admin.levels.create');
    }

    /**
     * Store a newly created level in storage.
     */
    public function store(Request $request)
    {
        Log::info('LevelController@store called', [
            'request_data' => $request->all(),
            'is_ajax' => $request->ajax(),
            'method' => $request->method()
        ]);

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
            // Handle JSON fields properly
            $data = $request->all();
            Log::info('Processing level data', ['data' => $data]);
            
            if (is_string($data['name'])) {
                $data['name'] = json_decode($data['name'], true);
                Log::info('Decoded name from JSON', ['name' => $data['name']]);
            }
            if (isset($data['description']) && is_string($data['description'])) {
                $data['description'] = json_decode($data['description'], true);
                Log::info('Decoded description from JSON', ['description' => $data['description']]);
            }
            if (isset($data['features']) && is_string($data['features'])) {
                $data['features'] = json_decode($data['features'], true);
                Log::info('Decoded features from JSON', ['features' => $data['features']]);
            }

            $level = Level::create($data);
            Log::info('Level created successfully', ['level_id' => $level->id]);

            if ($request->ajax()) {
                Log::info('Returning AJAX response for store');
                return response()->json([
                    'success' => true,
                    'message' => 'Level created successfully.',
                    'level' => $level
                ]);
            }

            return redirect()->route('levels.index')
                ->with('success', 'Level created successfully.');
        } catch (\Exception $e) {
            Log::error("Error creating level: " . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to create level.'], 500);
            }
            
            return back()->with('error', 'Failed to create level.');
        }
    }

    /**
     * Display the specified level.
     */
    public function show(Level $level)
    {
        Log::info('LevelController@show called', ['level_id' => $level->id]);
        return view('admin.levels.show', compact('level'));
    }

    /**
     * Show the form for editing the specified level.
     */
    public function edit(Level $level)
    {
        Log::info('LevelController@edit called', ['level_id' => $level->id]);
        return view('admin.levels.edit', compact('level'));
    }

    /**
     * Update the specified level in storage.
     */
    public function update(Request $request, Level $level)
    {
        Log::info('LevelController@update called', [
            'level_id' => $level->id,
            'request_data' => $request->all(),
            'is_ajax' => $request->ajax(),
            'method' => $request->method()
        ]);

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
            // Convert features to JSON
            if (isset($validated['features'])) {
                $validated['features'] = json_encode($validated['features']);
            }

            $level->update($validated);
            Log::info('Level updated successfully', ['level_id' => $level->id]);

            if ($request->ajax()) {
                Log::info('Returning AJAX response for update');
                return response()->json([
                    'success' => true,
                    'message' => 'Level updated successfully.',
                    'level' => $level->fresh()
                ]);
            }

            return redirect()->route('levels.index')
                ->with('success', 'Level updated successfully.');
        } catch (\Exception $e) {
            Log::error("Error updating level: " . $e->getMessage(), [
                'exception' => $e,
                'level_id' => $level->id,
                'request_data' => $request->all()
            ]);
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to update level.'], 500);
            }
            
            return back()->with('error', 'Failed to update level.');
        }
    }

    /**
     * Remove the specified level from storage.
     */
    public function destroy(Level $level)
    {
        Log::info('LevelController@destroy called', [
            'level_id' => $level->id,
            'is_ajax' => request()->ajax()
        ]);

        try {
            // Check if level is being used
            if ($level->servicePosts()->count() > 0) {
                Log::warning('Attempted to delete level that is being used', ['level_id' => $level->id]);
                
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete level that is being used by service posts.'
                    ], 422);
                }
                
                return redirect()->route('levels.index')
                    ->with('error', 'Cannot delete level that is being used by service posts.');
            }

            $level->delete();
            Log::info('Level deleted successfully', ['level_id' => $level->id]);

            if (request()->ajax()) {
                Log::info('Returning AJAX response for destroy');
                return response()->json([
                    'success' => true,
                    'message' => 'Level deleted successfully.'
                ]);
            }

            return redirect()->route('levels.index')
                ->with('success', 'Level deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Error deleting level: " . $e->getMessage(), [
                'exception' => $e,
                'level_id' => $level->id
            ]);
            
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete level.'], 500);
            }
            
            return back()->with('error', 'Failed to delete level.');
        }
    }

    /**
     * Update the display order of levels.
     */
    public function updateOrder(Request $request)
    {
        Log::info('LevelController@updateOrder called', [
            'request_data' => $request->all(),
            'is_ajax' => $request->ajax()
        ]);

        $request->validate([
            'levels' => 'required|array',
            'levels.*.id' => 'required|exists:levels,id',
            'levels.*.display_order' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->levels as $levelData) {
                    Level::where('id', $levelData['id'])
                        ->update(['display_order' => $levelData['display_order']]);
                }
            });
            
            Log::info('Level order updated successfully');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("Error updating level order: " . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            return response()->json(['success' => false, 'message' => 'Failed to update level order.'], 500);
        }
    }

    /**
     * Toggle the active status of a level.
     */
    public function toggleActive(Level $level)
    {
        Log::info('LevelController@toggleActive called', [
            'level_id' => $level->id,
            'current_status' => $level->is_active
        ]);

        try {
            $level->update(['is_active' => !$level->is_active]);
            Log::info('Level status toggled successfully', [
                'level_id' => $level->id,
                'new_status' => $level->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Level status updated successfully.',
                'is_active' => $level->is_active
            ]);
        } catch (\Exception $e) {
            Log::error("Error toggling level status: " . $e->getMessage(), [
                'exception' => $e,
                'level_id' => $level->id
            ]);
            
            return response()->json(['success' => false, 'message' => 'Failed to update level status.'], 500);
        }
    }

    /**
     * Get all active levels for API.
     */
    public function getActiveLevels()
    {
        $levels = Level::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return response()->json([
            'success' => true,
            'levels' => $levels
        ]);
    }

    /**
     * Bulk activate all levels.
     */
    public function bulkActivate()
    {
        try {
            Level::where('is_active', false)->update(['is_active' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'All levels activated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate levels.'
            ], 500);
        }
    }

    /**
     * Bulk deactivate all levels.
     */
    public function bulkDeactivate()
    {
        try {
            Level::where('is_active', true)->update(['is_active' => false]);
            
            return response()->json([
                'success' => true,
                'message' => 'All levels deactivated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate levels.'
            ], 500);
        }
    }

    /**
     * Duplicate a level.
     */
    public function duplicate(Level $level)
    {
        try {
            $newLevel = $level->replicate();
            $newLevel->name = [
                'ar' => $level->name['ar'] . ' (Copy)',
                'en' => $level->name['en'] . ' (Copy)'
            ];
            $newLevel->is_active = false;
            $newLevel->display_order = Level::max('display_order') + 1;
            $newLevel->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Level duplicated successfully.',
                'level' => $newLevel
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate level.'
            ], 500);
        }
    }

    /**
     * Export levels data.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'excel');
        $levels = Level::orderBy('display_order')->get();
        
        if ($format === 'pdf') {
            // PDF export logic
            return response()->json([
                'success' => true,
                'message' => 'PDF export functionality will be implemented.'
            ]);
        } else {
            // Excel export logic
            return response()->json([
                'success' => true,
                'message' => 'Excel export functionality will be implemented.'
            ]);
        }
    }
} 