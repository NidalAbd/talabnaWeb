<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BadgeType;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BadgeTypesApiController extends Controller
{
    /**
     * Get all badge types with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = BadgeType::query();

        // Search in both AR and EN names
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'priority');
        $sortOrder = $request->get('sort_order', 'asc');

        switch ($sortBy) {
            case 'name':
                $query->orderByRaw("JSON_EXTRACT(name, '$.en') {$sortOrder}");
                break;
            case 'posts':
                $query->withCount('servicePosts')->orderBy('service_posts_count', $sortOrder);
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
                break;
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $badgeTypes = $query->withCount('servicePosts')->paginate($perPage);

        return response()->json($badgeTypes);
    }

    /**
     * Get statistics for badge types, points, and badges
     */
    public function getStats(): JsonResponse
    {
        $today = Carbon::today();
        $endOfWeek = Carbon::today()->endOfWeek();

        // Badge type stats
        $total = BadgeType::count();
        $active = BadgeType::where('is_active', true)->count();

        // Points stats
        $totalPoints = DB::table('palservice_points')->sum('point');
        $pointsPurchased = DB::table('point_transactions')->where('type', 'purchase')->sum('point');
        $pointsUsed = DB::table('point_transactions')->where('type', 'used')->sum('point');
        $pointsTransferred = DB::table('point_transactions')->where('type', 'transfer')->sum('point');

        // Badge expiry stats
        $activeBadgePosts = DB::table('service_posts')
            ->whereNotNull('badge_type_id')
            ->where('badge_expires_at', '>', $today)
            ->count();

        $expiringToday = DB::table('service_posts')
            ->whereNotNull('badge_type_id')
            ->whereDate('badge_expires_at', $today)
            ->count();

        $expiringThisWeek = DB::table('service_posts')
            ->whereNotNull('badge_type_id')
            ->whereBetween('badge_expires_at', [$today, $endOfWeek])
            ->count();

        $pendingPurchaseRequests = DB::table('point_purchase_requests')
            ->where('status', 'pending')
            ->count();

        return response()->json([
            'stats' => [
                [
                    'label' => 'Total Badge Types',
                    'value' => $total,
                    'icon' => 'fas fa-certificate',
                    'color' => 'info'
                ],
                [
                    'label' => 'Active Badges',
                    'value' => $active,
                    'icon' => 'fas fa-check-circle',
                    'color' => 'success'
                ],
                [
                    'label' => 'Active Badge Posts',
                    'value' => $activeBadgePosts,
                    'icon' => 'fas fa-award',
                    'color' => 'warning'
                ],
                [
                    'label' => 'Inactive Badges',
                    'value' => $total - $active,
                    'icon' => 'fas fa-times-circle',
                    'color' => 'secondary'
                ]
            ],
            'points_stats' => [
                'total_points' => (int) $totalPoints,
                'points_purchased' => (int) $pointsPurchased,
                'points_used' => (int) abs($pointsUsed),
                'points_transferred' => (int) abs($pointsTransferred),
            ],
            'badge_stats' => [
                'active_badge_posts' => $activeBadgePosts,
                'expiring_today' => $expiringToday,
                'expiring_this_week' => $expiringThisWeek,
                'pending_purchase_requests' => $pendingPurchaseRequests,
            ],
        ]);
    }

    /**
     * Get single badge type by ID
     */
    public function show($id): JsonResponse
    {
        $badgeType = BadgeType::withCount('servicePosts')->findOrFail($id);

        return response()->json($badgeType);
    }

    /**
     * Create new badge type
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:badge_types,slug',
            'color' => 'required|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:255',
            'points_per_day' => 'required|integer|min:0',
            'priority' => 'required|integer|min:0',
            'view_boost_percent' => 'required|integer|min:0|max:100',
            'is_active' => 'boolean',
            'is_default' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate slug if not provided
        $slug = $request->slug ?: Str::slug($request->input('name.en'));

        // If this is set as default, remove default from others
        if ($request->is_default) {
            BadgeType::where('is_default', true)->update(['is_default' => false]);
        }

        $badgeType = BadgeType::create([
            'name' => [
                'ar' => $request->input('name.ar'),
                'en' => $request->input('name.en')
            ],
            'slug' => $slug,
            'color' => $request->color,
            'secondary_color' => $request->secondary_color,
            'icon' => $request->icon,
            'points_per_day' => $request->points_per_day,
            'priority' => $request->priority,
            'view_boost_percent' => $request->view_boost_percent,
            'is_active' => $request->is_active ?? true,
            'is_default' => $request->is_default ?? false
        ]);

        return response()->json([
            'message' => 'Badge type created successfully',
            'badge_type' => $badgeType
        ], 201);
    }

    /**
     * Update existing badge type
     */
    public function update(Request $request, $id): JsonResponse
    {
        $badgeType = BadgeType::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name.ar' => 'sometimes|required|string|max:255',
            'name.en' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:badge_types,slug,' . $id,
            'color' => 'sometimes|required|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:255',
            'points_per_day' => 'sometimes|required|integer|min:0',
            'priority' => 'sometimes|required|integer|min:0',
            'view_boost_percent' => 'sometimes|required|integer|min:0|max:100',
            'is_active' => 'boolean',
            'is_default' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // If this is set as default, remove default from others
        if ($request->has('is_default') && $request->is_default) {
            BadgeType::where('is_default', true)->where('id', '!=', $id)->update(['is_default' => false]);
        }

        // Update name if provided
        if ($request->has('name')) {
            $badgeType->name = [
                'ar' => $request->input('name.ar'),
                'en' => $request->input('name.en')
            ];
        }

        // Update other fields
        if ($request->has('slug')) $badgeType->slug = $request->slug;
        if ($request->has('color')) $badgeType->color = $request->color;
        if ($request->has('secondary_color')) $badgeType->secondary_color = $request->secondary_color;
        if ($request->has('icon')) $badgeType->icon = $request->icon;
        if ($request->has('points_per_day')) $badgeType->points_per_day = $request->points_per_day;
        if ($request->has('priority')) $badgeType->priority = $request->priority;
        if ($request->has('view_boost_percent')) $badgeType->view_boost_percent = $request->view_boost_percent;
        if ($request->has('is_active')) $badgeType->is_active = $request->is_active;
        if ($request->has('is_default')) $badgeType->is_default = $request->is_default;

        $badgeType->save();

        return response()->json([
            'message' => 'Badge type updated successfully',
            'badge_type' => $badgeType
        ]);
    }

    /**
     * Delete badge type
     */
    public function destroy($id): JsonResponse
    {
        $badgeType = BadgeType::findOrFail($id);

        // Check if badge type has associated service posts
        if ($badgeType->servicePosts()->exists()) {
            return response()->json([
                'message' => 'Cannot delete badge type with existing service posts'
            ], 400);
        }

        // Prevent deletion of default badge
        if ($badgeType->is_default) {
            return response()->json([
                'message' => 'Cannot delete the default badge type'
            ], 400);
        }

        $badgeType->delete();

        return response()->json([
            'message' => 'Badge type deleted successfully'
        ]);
    }

    /**
     * Toggle active status
     */
    public function toggleStatus($id): JsonResponse
    {
        $badgeType = BadgeType::findOrFail($id);
        $badgeType->is_active = !$badgeType->is_active;
        $badgeType->save();

        return response()->json([
            'message' => 'Badge type status updated successfully',
            'is_active' => $badgeType->is_active
        ]);
    }

    /**
     * Set as default
     */
    public function setDefault($id): JsonResponse
    {
        $badgeType = BadgeType::findOrFail($id);

        // Remove default from all others
        BadgeType::where('is_default', true)->update(['is_default' => false]);

        // Set this one as default
        $badgeType->is_default = true;
        $badgeType->save();

        return response()->json([
            'message' => 'Badge type set as default successfully'
        ]);
    }
}
