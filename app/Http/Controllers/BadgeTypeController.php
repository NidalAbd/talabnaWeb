<?php

namespace App\Http\Controllers;

use App\Models\BadgeType;
use App\Services\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BadgeTypeController extends Controller
{
    protected BadgeService $badgeService;

    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    /**
     * Display a listing of badge types
     */
    public function index()
    {
        $badgeTypes = BadgeType::withCount('servicePosts')
            ->orderBy('priority', 'asc')
            ->get();

        $statistics = $this->badgeService->getStatistics();

        return view('admin.badge_types.index', compact('badgeTypes', 'statistics'));
    }

    /**
     * Show the form for creating a new badge type
     */
    public function create()
    {
        return view('admin.badge_types.create');
    }

    /**
     * Store a newly created badge type
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:50',
            'name_en' => 'required|string|max:50',
            'slug' => 'nullable|string|max:50|unique:badge_types,slug',
            'color' => 'required|string|max:20',
            'icon' => 'required|string|max:50',
            'points_per_day' => 'required|integer|min:0',
            'priority' => 'required|integer|min:1',
            'view_boost_percent' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name_en']);
        }

        BadgeType::create([
            'name' => [
                'ar' => $validated['name_ar'],
                'en' => $validated['name_en'],
            ],
            'slug' => $validated['slug'],
            'color' => $validated['color'],
            'icon' => $validated['icon'],
            'points_per_day' => $validated['points_per_day'],
            'priority' => $validated['priority'],
            'view_boost_percent' => $validated['view_boost_percent'] ?? 0,
            'is_default' => false,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('badge_types.index')
            ->with('success', 'Badge type created successfully.');
    }

    /**
     * Display the specified badge type
     */
    public function show(BadgeType $badgeType)
    {
        $badgeType->loadCount('servicePosts');

        return view('admin.badge_types.show', compact('badgeType'));
    }

    /**
     * Show the form for editing the specified badge type
     */
    public function edit(BadgeType $badgeType)
    {
        return view('admin.badge_types.edit', compact('badgeType'));
    }

    /**
     * Update the specified badge type
     */
    public function update(Request $request, BadgeType $badgeType)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:50',
            'name_en' => 'required|string|max:50',
            'slug' => "required|string|max:50|unique:badge_types,slug,{$badgeType->id}",
            'color' => 'required|string|max:20',
            'icon' => 'required|string|max:50',
            'points_per_day' => 'required|integer|min:0',
            'priority' => 'required|integer|min:1',
            'view_boost_percent' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $badgeType->update([
            'name' => [
                'ar' => $validated['name_ar'],
                'en' => $validated['name_en'],
            ],
            'slug' => $validated['slug'],
            'color' => $validated['color'],
            'icon' => $validated['icon'],
            'points_per_day' => $validated['points_per_day'],
            'priority' => $validated['priority'],
            'view_boost_percent' => $validated['view_boost_percent'] ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('badge_types.index')
            ->with('success', 'Badge type updated successfully.');
    }

    /**
     * Remove the specified badge type
     */
    public function destroy(BadgeType $badgeType)
    {
        // Prevent deleting default badge
        if ($badgeType->is_default) {
            return redirect()->route('badge_types.index')
                ->with('error', 'Cannot delete the default badge type.');
        }

        // Prevent deleting if posts are using it
        if ($badgeType->servicePosts()->count() > 0) {
            return redirect()->route('badge_types.index')
                ->with('error', 'Cannot delete badge type that has active posts.');
        }

        $badgeType->delete();

        return redirect()->route('badge_types.index')
            ->with('success', 'Badge type deleted successfully.');
    }

    /**
     * Toggle badge active status
     */
    public function toggleActive(BadgeType $badgeType)
    {
        // Prevent deactivating default badge
        if ($badgeType->is_default && $badgeType->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot deactivate the default badge type.'
            ], 403);
        }

        $badgeType->is_active = !$badgeType->is_active;
        $badgeType->save();

        return response()->json([
            'success' => true,
            'message' => 'Badge status updated successfully.',
            'is_active' => $badgeType->is_active
        ]);
    }

    /**
     * Set badge as default
     */
    public function setDefault(BadgeType $badgeType)
    {
        // Remove default from all other badges
        BadgeType::where('is_default', true)->update(['is_default' => false]);

        // Set this one as default
        $badgeType->is_default = true;
        $badgeType->is_active = true; // Default must be active
        $badgeType->save();

        return redirect()->route('badge_types.index')
            ->with('success', 'Default badge type updated successfully.');
    }

    /**
     * Migrate old badges to new system
     */
    public function migrateOldBadges()
    {
        $count = $this->badgeService->migrateOldBadges();

        return redirect()->route('badge_types.index')
            ->with('success', "Migrated {$count} posts to new badge system.");
    }
}
