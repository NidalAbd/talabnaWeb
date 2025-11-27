<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BadgeType;
use App\Models\ServicePost;
use App\Services\BadgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Exception;

class BadgeTypeController extends Controller
{
    protected BadgeService $badgeService;

    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    /**
     * Get all badge types (for admin)
     */
    public function index(): JsonResponse
    {
        $badges = BadgeType::withCount('servicePosts')
            ->orderBy('priority', 'asc')
            ->get()
            ->map(function ($badge) {
                return [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'name_ar' => $badge->name_ar,
                    'name_en' => $badge->name_en,
                    'slug' => $badge->slug,
                    'color' => $badge->color,
                    'secondary_color' => $badge->secondary_color,
                    'icon' => $badge->icon,
                    'points_per_day' => $badge->points_per_day,
                    'priority' => $badge->priority,
                    'view_boost_percent' => $badge->view_boost_percent,
                    'is_default' => $badge->is_default,
                    'is_active' => $badge->is_active,
                    'posts_count' => $badge->service_posts_count,
                    'created_at' => $badge->created_at,
                ];
            });

        return response()->json([
            'badges' => $badges,
            'statistics' => $this->badgeService->getStatistics(),
        ]);
    }

    /**
     * Get active badge types (for public/forms)
     */
    public function active(): JsonResponse
    {
        $badges = $this->badgeService->getBadgeOptionsForForm(true);

        return response()->json([
            'badges' => $badges,
        ]);
    }

    /**
     * Get premium badge types only (excluding default)
     */
    public function premium(): JsonResponse
    {
        $badges = $this->badgeService->getBadgeOptionsForForm(false);

        return response()->json([
            'badges' => $badges,
        ]);
    }

    /**
     * Get single badge type
     */
    public function show(int $id): JsonResponse
    {
        $badge = BadgeType::withCount('servicePosts')->find($id);

        if (!$badge) {
            return response()->json(['error' => 'Badge type not found'], 404);
        }

        return response()->json([
            'badge' => [
                'id' => $badge->id,
                'name' => $badge->name,
                'name_ar' => $badge->name_ar,
                'name_en' => $badge->name_en,
                'slug' => $badge->slug,
                'color' => $badge->color,
                'secondary_color' => $badge->secondary_color,
                'icon' => $badge->icon,
                'points_per_day' => $badge->points_per_day,
                'priority' => $badge->priority,
                'view_boost_percent' => $badge->view_boost_percent,
                'is_default' => $badge->is_default,
                'is_active' => $badge->is_active,
                'posts_count' => $badge->service_posts_count,
            ],
        ]);
    }

    /**
     * Create new badge type
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name_en']);
        }

        // Check slug uniqueness
        if (BadgeType::where('slug', $data['slug'])->exists()) {
            return response()->json(['errors' => ['slug' => ['Slug already exists']]], 422);
        }

        $badge = BadgeType::create([
            'name' => [
                'ar' => $data['name_ar'],
                'en' => $data['name_en'],
            ],
            'slug' => $data['slug'],
            'color' => $data['color'],
            'icon' => $data['icon'],
            'points_per_day' => $data['points_per_day'],
            'priority' => $data['priority'],
            'view_boost_percent' => $data['view_boost_percent'] ?? 0,
            'is_default' => false,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Badge type created successfully',
            'badge' => $badge,
        ], 201);
    }

    /**
     * Update badge type
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $badge = BadgeType::find($id);

        if (!$badge) {
            return response()->json(['error' => 'Badge type not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => 'sometimes|required|string|max:50',
            'name_en' => 'sometimes|required|string|max:50',
            'slug' => "sometimes|required|string|max:50|unique:badge_types,slug,{$id}",
            'color' => 'sometimes|required|string|max:20',
            'icon' => 'sometimes|required|string|max:50',
            'points_per_day' => 'sometimes|required|integer|min:0',
            'priority' => 'sometimes|required|integer|min:1',
            'view_boost_percent' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Update name if provided
        if (isset($data['name_ar']) || isset($data['name_en'])) {
            $name = $badge->name;
            if (isset($data['name_ar'])) {
                $name['ar'] = $data['name_ar'];
            }
            if (isset($data['name_en'])) {
                $name['en'] = $data['name_en'];
            }
            $badge->name = $name;
        }

        // Update other fields
        if (isset($data['slug'])) $badge->slug = $data['slug'];
        if (isset($data['color'])) $badge->color = $data['color'];
        if (isset($data['icon'])) $badge->icon = $data['icon'];
        if (isset($data['points_per_day'])) $badge->points_per_day = $data['points_per_day'];
        if (isset($data['priority'])) $badge->priority = $data['priority'];
        if (isset($data['view_boost_percent'])) $badge->view_boost_percent = $data['view_boost_percent'];
        if (isset($data['is_active'])) $badge->is_active = $data['is_active'];

        $badge->save();

        return response()->json([
            'message' => 'Badge type updated successfully',
            'badge' => $badge->fresh(),
        ]);
    }

    /**
     * Delete badge type
     */
    public function destroy(int $id): JsonResponse
    {
        $badge = BadgeType::withCount('servicePosts')->find($id);

        if (!$badge) {
            return response()->json(['error' => 'Badge type not found'], 404);
        }

        // Prevent deleting default badge
        if ($badge->is_default) {
            return response()->json(['error' => 'Cannot delete default badge type'], 403);
        }

        // Prevent deleting if posts are using it
        if ($badge->service_posts_count > 0) {
            return response()->json([
                'error' => 'Cannot delete badge type with active posts',
                'posts_count' => $badge->service_posts_count,
            ], 403);
        }

        $badge->delete();

        return response()->json([
            'message' => 'Badge type deleted successfully',
        ]);
    }

    /**
     * Reorder badge types
     */
    public function reorder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order' => 'required|array',
            'order.*.id' => 'required|integer|exists:badge_types,id',
            'order.*.priority' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        foreach ($request->order as $item) {
            BadgeType::where('id', $item['id'])->update(['priority' => $item['priority']]);
        }

        BadgeType::clearCache();

        return response()->json([
            'message' => 'Badge order updated successfully',
            'badges' => BadgeType::orderBy('priority', 'asc')->get(),
        ]);
    }

    /**
     * Set default badge type
     */
    public function setDefault(int $id): JsonResponse
    {
        $badge = BadgeType::find($id);

        if (!$badge) {
            return response()->json(['error' => 'Badge type not found'], 404);
        }

        // Remove default from all other badges
        BadgeType::where('is_default', true)->update(['is_default' => false]);

        // Set this one as default
        $badge->is_default = true;
        $badge->save();

        return response()->json([
            'message' => 'Default badge type updated successfully',
            'badge' => $badge,
        ]);
    }

    /**
     * Toggle badge active status
     */
    public function toggleActive(int $id): JsonResponse
    {
        $badge = BadgeType::find($id);

        if (!$badge) {
            return response()->json(['error' => 'Badge type not found'], 404);
        }

        // Prevent deactivating default badge
        if ($badge->is_default && $badge->is_active) {
            return response()->json(['error' => 'Cannot deactivate default badge type'], 403);
        }

        $badge->is_active = !$badge->is_active;
        $badge->save();

        return response()->json([
            'message' => 'Badge status updated successfully',
            'badge' => $badge,
        ]);
    }

    /**
     * Calculate badge cost preview
     */
    public function calculateCost(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'badge_type_id' => 'required|integer|exists:badge_types,id',
            'days' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cost = $this->badgeService->calculateCost(
            $request->badge_type_id,
            $request->days
        );

        $badge = BadgeType::find($request->badge_type_id);

        return response()->json([
            'badge' => [
                'id' => $badge->id,
                'name' => $badge->name,
                'points_per_day' => $badge->points_per_day,
            ],
            'days' => $request->days,
            'total_cost' => $cost,
        ]);
    }

    /**
     * Get badge statistics
     */
    public function statistics(): JsonResponse
    {
        return response()->json([
            'statistics' => $this->badgeService->getStatistics(),
        ]);
    }

    /**
     * Migrate old badges to new system
     */
    public function migrateOldBadges(): JsonResponse
    {
        $count = $this->badgeService->migrateOldBadges();

        return response()->json([
            'message' => "Migrated {$count} posts to new badge system",
            'migrated_count' => $count,
        ]);
    }

    /**
     * Apply badge to a service post
     */
    public function applyBadgeToPost(Request $request, int $servicePostId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'badge_type_id' => 'required|integer|exists:badge_types,id',
            'days' => 'required|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $servicePost = ServicePost::find($servicePostId);

        if (!$servicePost) {
            return response()->json(['error' => 'Service post not found'], 404);
        }

        try {
            $post = $this->badgeService->applyBadge(
                $servicePost,
                $request->badge_type_id,
                $request->days
            );

            $badge = BadgeType::find($request->badge_type_id);
            $pointsDeducted = $badge ? $badge->calculateCost($request->days) : 0;

            return response()->json([
                'message' => 'Badge applied successfully',
                'post' => $post,
                'points_deducted' => $pointsDeducted,
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove badge from a service post
     */
    public function removeBadgeFromPost(int $servicePostId): JsonResponse
    {
        $servicePost = ServicePost::find($servicePostId);

        if (!$servicePost) {
            return response()->json(['error' => 'Service post not found'], 404);
        }

        try {
            $post = $this->badgeService->removeBadge($servicePost);

            return response()->json([
                'message' => 'Badge removed successfully',
                'post' => $post,
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Upgrade badge for a service post
     * User can only upgrade (higher priority), not downgrade
     * Calculates remaining time value from current badge and applies to new badge
     */
    public function upgradeBadge(Request $request, int $servicePostId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'new_badge_type_id' => 'required|integer|exists:badge_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $servicePost = ServicePost::find($servicePostId);

        if (!$servicePost) {
            return response()->json(['error' => 'Service post not found'], 404);
        }

        try {
            $result = $this->badgeService->upgradeBadge(
                $servicePost,
                $request->new_badge_type_id
            );

            return response()->json([
                'message' => 'Badge upgraded successfully',
                'post' => $result['post'],
                'old_badge' => $result['old_badge'],
                'new_badge' => $result['new_badge'],
                'refund' => $result['refund'],
                'additional_cost' => $result['additional_cost'],
                'net_cost' => $result['net_cost'],
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
