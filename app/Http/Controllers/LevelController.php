<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LevelController extends Controller
{
    /**
     * Display a listing of the levels.
     */
    public function index()
    {
        $levels = Level::orderBy('display_order')->get();
        
        return view('admin.levels.index', compact('levels'));
    }

    /**
     * Show the form for creating a new level.
     */
    public function create()
    {
        return view('admin.levels.create');
    }

    /**
     * Store a newly created level in storage.
     */
    public function store(Request $request)
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

        // Convert features to JSON
        if (isset($validated['features'])) {
            $validated['features'] = json_encode($validated['features']);
        }

        Level::create($validated);

        return redirect()->route('admin.levels.index')
            ->with('success', 'Level created successfully.');
    }

    /**
     * Display the specified level.
     */
    public function show(Level $level)
    {
        return view('admin.levels.show', compact('level'));
    }

    /**
     * Show the form for editing the specified level.
     */
    public function edit(Level $level)
    {
        return view('admin.levels.edit', compact('level'));
    }

    /**
     * Update the specified level in storage.
     */
    public function update(Request $request, Level $level)
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

        // Convert features to JSON
        if (isset($validated['features'])) {
            $validated['features'] = json_encode($validated['features']);
        }

        $level->update($validated);

        return redirect()->route('admin.levels.index')
            ->with('success', 'Level updated successfully.');
    }

    /**
     * Remove the specified level from storage.
     */
    public function destroy(Level $level)
    {
        // Check if level is being used
        if ($level->servicePosts()->count() > 0) {
            return redirect()->route('admin.levels.index')
                ->with('error', 'Cannot delete level that is being used by service posts.');
        }

        $level->delete();

        return redirect()->route('admin.levels.index')
            ->with('success', 'Level deleted successfully.');
    }

    /**
     * Update the display order of levels.
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'levels' => 'required|array',
            'levels.*.id' => 'required|exists:levels,id',
            'levels.*.display_order' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->levels as $levelData) {
                Level::where('id', $levelData['id'])
                    ->update(['display_order' => $levelData['display_order']]);
            }
        });

        return response()->json(['success' => true]);
    }

    /**
     * Toggle the active status of a level.
     */
    public function toggleActive(Level $level)
    {
        $level->update(['is_active' => !$level->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $level->is_active
        ]);
    }

    /**
     * Get all active levels for API.
     */
    public function getActiveLevels()
    {
        $levels = Level::active()->ordered()->get();
        
        return response()->json([
            'success' => true,
            'levels' => $levels
        ]);
    }
} 