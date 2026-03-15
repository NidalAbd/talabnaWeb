<?php

namespace App\Services;

use App\Models\BadgeType;
use App\Models\ServicePost;
use App\Models\User;
use App\Models\palservice_points;
use App\Models\point_transactions;
use App\Models\Notification;
use App\Notifications\BadgeChangeNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class BadgeService
{
    /**
     * Get all active badge types for display
     */
    public function getAvailableBadges(): \Illuminate\Database\Eloquent\Collection
    {
        return BadgeType::active()->ordered()->get();
    }

    /**
     * Get badge options for forms (excluding default if needed)
     */
    public function getBadgeOptionsForForm(bool $includeDefault = true): array
    {
        $badges = $this->getAvailableBadges();

        if (!$includeDefault) {
            $badges = $badges->where('is_default', false);
        }

        return $badges->map(function ($badge) {
            return [
                'id' => $badge->id,
                'slug' => $badge->slug,
                'name' => $badge->name,
                'name_ar' => $badge->name_ar,
                'name_en' => $badge->name_en,
                'color' => $badge->color,
                'secondary_color' => $badge->secondary_color,
                'icon' => $badge->icon,
                'points_per_day' => $badge->points_per_day,
                'priority' => $badge->priority,
                'view_boost_percent' => $badge->view_boost_percent,
            ];
        })->toArray();
    }

    /**
     * Calculate badge cost
     */
    public function calculateCost(int $badgeTypeId, int $days): int
    {
        $badge = BadgeType::find($badgeTypeId);

        if (!$badge) {
            return 0;
        }

        return $badge->calculateCost($days);
    }

    /**
     * Check if user has enough points for badge
     */
    public function userCanAffordBadge(User $user, int $badgeTypeId, int $days): bool
    {
        $cost = $this->calculateCost($badgeTypeId, $days);
        return $user->pointsBalance >= $cost;
    }

    /**
     * Apply badge to a service post
     *
     * @throws Exception
     */
    public function applyBadge(
        ServicePost $servicePost,
        int $badgeTypeId,
        int $days,
        bool $deductPoints = true
    ): ServicePost {
        $badge = BadgeType::find($badgeTypeId);

        if (!$badge) {
            throw new Exception('Badge type not found');
        }

        if (!$badge->is_active) {
            throw new Exception('Badge type is not active');
        }

        $cost = $badge->calculateCost($days);
        $user = $servicePost->user;

        // Check points if needed
        if ($deductPoints && $cost > 0) {
            if ($user->pointsBalance < $cost) {
                throw new Exception('Insufficient points');
            }
        }

        DB::beginTransaction();

        try {
            // Deduct points if needed
            if ($deductPoints && $cost > 0) {
                $this->deductPoints($user, $cost, $servicePost, $badge);
            }

            // Update service post
            $servicePost->badge_type_id = $badge->id;
            $servicePost->have_badge = $badge->name_ar; // Keep for backward compatibility
            $servicePost->badge_duration = $days;
            $servicePost->badge_expires_at = Carbon::now()->addDays($days);
            $servicePost->save();

            // Create notification
            $this->createBadgeNotification($servicePost, $badge, $days);

            DB::commit();

            return $servicePost->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Upgrade badge ONLY (no downgrade allowed)
     * Calculates refund based on remaining time and applies to new badge
     *
     * @throws Exception
     */
    public function upgradeBadge(
        ServicePost $servicePost,
        int $newBadgeTypeId
    ): array {
        $currentBadge = $servicePost->badgeType;
        $newBadge = BadgeType::find($newBadgeTypeId);
        $user = $servicePost->user;

        if (!$newBadge) {
            throw new Exception('New badge type not found');
        }

        if (!$newBadge->is_active) {
            throw new Exception('New badge type is not active');
        }

        // Check if post has current badge
        if (!$currentBadge || $currentBadge->is_default) {
            throw new Exception('Post does not have an active premium badge to upgrade from');
        }

        // PREVENT DOWNGRADE: Check if new badge has higher priority (lower number = higher priority)
        if ($newBadge->priority >= $currentBadge->priority) {
            throw new Exception('You can only upgrade to a higher tier badge. Downgrade is not allowed.');
        }

        // Calculate refund for remaining time on current badge
        $refundInfo = $this->calculateRefund($servicePost, $currentBadge);
        $refundAmount = $refundInfo['refund_amount'];
        $remainingDays = $refundInfo['remaining_days'];

        if ($remainingDays <= 0) {
            throw new Exception('Current badge has expired. Cannot upgrade.');
        }

        // Calculate new badge cost for the SAME remaining time
        $newCost = $newBadge->calculateCost($remainingDays);

        // Calculate net amount to charge
        $netAmount = $newCost - $refundAmount;

        // Check if user has enough points for the additional cost
        if ($netAmount > 0 && $user->pointsBalance < $netAmount) {
            throw new Exception("Insufficient points. You need {$netAmount} additional points but you have {$user->pointsBalance}");
        }

        DB::beginTransaction();

        try {
            // Refund remaining value from old badge
            if ($refundAmount > 0) {
                $this->refundPoints($user, $refundAmount, $servicePost, $currentBadge, $refundInfo);
            }

            // Charge for new badge (only the difference)
            if ($netAmount > 0) {
                $this->deductPoints($user, $netAmount, $servicePost, $newBadge);
            }

            // Update service post with new badge keeping same expiry time
            $servicePost->badge_type_id = $newBadge->id;
            $servicePost->have_badge = $newBadge->name_ar;
            $servicePost->badge_duration = $remainingDays;
            // Keep the same expiry time
            $servicePost->save();

            // Create notification
            $this->createBadgeUpgradeNotification(
                $servicePost,
                $currentBadge,
                $newBadge,
                $remainingDays,
                $refundAmount,
                $netAmount
            );

            DB::commit();

            return [
                'post' => $servicePost->fresh(),
                'old_badge' => [
                    'id' => $currentBadge->id,
                    'name' => $currentBadge->name,
                    'points_per_day' => $currentBadge->points_per_day,
                ],
                'new_badge' => [
                    'id' => $newBadge->id,
                    'name' => $newBadge->name,
                    'points_per_day' => $newBadge->points_per_day,
                ],
                'refund' => $refundAmount,
                'additional_cost' => max(0, $netAmount),
                'net_cost' => $netAmount,
                'remaining_days' => $remainingDays,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Change badge on existing post with refund logic
     *
     * @throws Exception
     */
    public function changeBadge(
        ServicePost $servicePost,
        int $newBadgeTypeId,
        int $days
    ): ServicePost {
        $currentBadge = $servicePost->badgeType;
        $newBadge = BadgeType::find($newBadgeTypeId);
        $user = $servicePost->user;

        if (!$newBadge) {
            throw new Exception('New badge type not found');
        }

        if (!$newBadge->is_active) {
            throw new Exception('New badge type is not active');
        }

        $refundAmount = 0;
        $refundInfo = null;

        // Calculate refund if there's an active badge
        if ($currentBadge && !$currentBadge->is_default && $servicePost->badge_expires_at) {
            $refundInfo = $this->calculateRefund($servicePost, $currentBadge);
            $refundAmount = $refundInfo['refund_amount'];
        }

        // Calculate new badge cost
        $newCost = $newBadge->calculateCost($days);

        // Calculate net amount to charge/refund
        $netAmount = $newCost - $refundAmount;

        // Check if user has enough points (only if net charge is positive)
        if ($netAmount > 0 && $user->pointsBalance < $netAmount) {
            throw new Exception("Insufficient points. Need {$netAmount} points but you have {$user->pointsBalance}");
        }

        DB::beginTransaction();

        try {
            // Process refund if applicable
            if ($refundAmount > 0) {
                $this->refundPoints($user, $refundAmount, $servicePost, $currentBadge, $refundInfo);
            }

            // Process new badge charge (if net positive)
            if ($netAmount > 0) {
                $this->deductPoints($user, $netAmount, $servicePost, $newBadge);
            } elseif ($netAmount < 0) {
                // Net refund (downgrade case where refund > new cost)
                $this->refundPoints($user, abs($netAmount), $servicePost, $newBadge, [
                    'reason' => 'downgrade_excess_refund'
                ]);
            }

            // Update service post
            $servicePost->badge_type_id = $newBadge->id;
            $servicePost->have_badge = $newBadge->name_ar;
            $servicePost->badge_duration = $days;
            $servicePost->badge_expires_at = Carbon::now()->addDays($days);
            $servicePost->save();

            // Create notification with refund info
            $this->createBadgeSwitchNotification($servicePost, $currentBadge, $newBadge, $days, $refundAmount, $netAmount);

            DB::commit();

            return $servicePost->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate refund amount for current badge
     */
    public function calculateRefund(ServicePost $servicePost, BadgeType $currentBadge): array
    {
        if (!$servicePost->badge_expires_at) {
            return [
                'used_days' => 0,
                'remaining_days' => 0,
                'refund_amount' => 0,
                'total_paid' => 0,
            ];
        }

        $now = Carbon::now();
        $expiresAt = Carbon::parse($servicePost->badge_expires_at);
        $createdAt = $expiresAt->copy()->subDays($servicePost->badge_duration ?? 0);

        // Calculate used days (round up - if you used any part of a day, it counts)
        $usedDays = max(1, $now->diffInDays($createdAt, false) + 1);

        // Calculate remaining days
        $remainingDays = max(0, $expiresAt->diffInDays($now, false));

        // Original cost
        $totalPaid = $currentBadge->points_per_day * $servicePost->badge_duration;

        // Refund for unused days only
        $refundAmount = $currentBadge->points_per_day * $remainingDays;

        return [
            'used_days' => $usedDays,
            'remaining_days' => $remainingDays,
            'refund_amount' => $refundAmount,
            'total_paid' => $totalPaid,
            'points_per_day' => $currentBadge->points_per_day,
        ];
    }

    /**
     * Remove/expire badge from service post
     */
    public function removeBadge(ServicePost $servicePost): ServicePost
    {
        $defaultBadge = BadgeType::getDefault();

        $servicePost->badge_type_id = $defaultBadge?->id;
        $servicePost->have_badge = $defaultBadge?->name_ar ?? 'عادي';
        $servicePost->badge_duration = 0;
        $servicePost->badge_expires_at = null;
        $servicePost->save();

        return $servicePost;
    }

    /**
     * Check and expire badges
     */
    public function expireExpiredBadges(): int
    {
        $expiredCount = 0;
        $defaultBadge = BadgeType::getDefault();

        // Find posts with expired badges
        $expiredPosts = ServicePost::where('badge_expires_at', '<', Carbon::now())
            ->where(function ($query) use ($defaultBadge) {
                $query->whereNull('badge_type_id')
                    ->orWhere('badge_type_id', '!=', $defaultBadge?->id ?? 0);
            })
            ->get();

        foreach ($expiredPosts as $post) {
            $this->removeBadge($post);
            $this->createExpirationNotification($post);
            $expiredCount++;
        }

        return $expiredCount;
    }

    /**
     * Get remaining days for a badge
     */
    public function getRemainingDays(ServicePost $servicePost): ?int
    {
        if (!$servicePost->badge_expires_at) {
            return null;
        }

        $now = Carbon::now();
        $expiresAt = Carbon::parse($servicePost->badge_expires_at);

        if ($expiresAt->lte($now)) {
            return 0;
        }

        return $now->diffInDays($expiresAt);
    }

    /**
     * Deduct points from user
     */
    protected function deductPoints(User $user, int $amount, ServicePost $post, BadgeType $badge): void
    {
        // Find or create user's point balance record
        $pointsRecord = palservice_points::firstOrCreate(
            ['user_id' => $user->id],
            ['point' => 0]
        );

        // Deduct points (ensure balance doesn't go negative)
        $currentBalance = $pointsRecord->point;
        $newBalance = max(0, $currentBalance - $amount);

        $pointsRecord->point = $newBalance;
        $pointsRecord->save();

        // Create transaction record
        point_transactions::create([
            'from_user_id' => $user->id,
            'to_user_id' => $user->id,
            'point' => $amount,
            'type' => 'used',
        ]);
    }

    /**
     * Refund points to user
     */
    protected function refundPoints(User $user, int $amount, ServicePost $post, BadgeType $badge, ?array $refundInfo = null): void
    {
        // Find or create user's point balance record
        $pointsRecord = palservice_points::firstOrCreate(
            ['user_id' => $user->id],
            ['point' => 0]
        );

        // Add points back
        $pointsRecord->point += $amount;
        $pointsRecord->save();

        // Create transaction record for refund
        point_transactions::create([
            'from_user_id' => $user->id,
            'to_user_id' => $user->id,
            'point' => $amount,
            'type' => 'refund',
        ]);
    }

    /**
     * Create badge applied notification
     */
    protected function createBadgeNotification(ServicePost $post, BadgeType $badge, int $days): void
    {
        $postTitle = $post->title ?? "#{$post->id}";
        $message = json_encode([
            'ar' => "تم تفعيل شارة {$badge->name_ar} على إعلانك \"{$postTitle}\" لمدة {$days} يوم [post_id:{$post->id}]",
            'en' => "Badge {$badge->name_en} activated on your post \"{$postTitle}\" for {$days} days [post_id:{$post->id}]",
        ], JSON_UNESCAPED_UNICODE);

        Notification::create([
            'user_id' => $post->user_id,
            'type' => 'badge_applied',
            'message' => $message,
        ]);

        try {
            $user = User::find($post->user_id);
            if ($user && !empty($user->fcm_token)) {
                $user->notify(new BadgeChangeNotification('applied', $badge->name_ar, $post->id, "تم تفعيل شارة {$badge->name_ar} على إعلانك لمدة {$days} يوم"));
            }
        } catch (\Exception $e) {
            Log::warning('FCM badge applied notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Create badge upgrade notification
     */
    protected function createBadgeUpgradeNotification(
        ServicePost $post,
        BadgeType $oldBadge,
        BadgeType $newBadge,
        int $remainingDays,
        int $refundAmount,
        int $netAmount
    ): void {
        $postTitle = $post->title ?? "#{$post->id}";

        $messageAr = "تم ترقية الشارة من {$oldBadge->name_ar} إلى {$newBadge->name_ar} على إعلانك \"{$postTitle}\"";
        $messageEn = "Badge upgraded from {$oldBadge->name_en} to {$newBadge->name_en} on your post \"{$postTitle}\"";

        if ($refundAmount > 0) {
            $messageAr .= "\nتم استرجاع {$refundAmount} نقطة";
            $messageEn .= "\n{$refundAmount} points refunded";
        }

        $message = json_encode([
            'ar' => "{$messageAr} [post_id:{$post->id}]",
            'en' => "{$messageEn} [post_id:{$post->id}]",
        ], JSON_UNESCAPED_UNICODE);

        Notification::create([
            'user_id' => $post->user_id,
            'type' => 'badge_upgraded',
            'message' => $message,
        ]);

        try {
            $user = User::find($post->user_id);
            if ($user && !empty($user->fcm_token)) {
                $user->notify(new BadgeChangeNotification('upgraded', $newBadge->name_ar, $post->id, $message));
            }
        } catch (\Exception $e) {
            Log::warning('FCM badge upgraded notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Create badge switch notification with refund info
     */
    protected function createBadgeSwitchNotification(
        ServicePost $post,
        ?BadgeType $oldBadge,
        BadgeType $newBadge,
        int $days,
        int $refundAmount,
        int $netAmount
    ): void {
        $oldBadgeNameAr = $oldBadge ? $oldBadge->name_ar : 'عادي';
        $oldBadgeNameEn = $oldBadge ? $oldBadge->name_en : 'Normal';
        $postTitle = $post->title ?? "#{$post->id}";

        $messageAr = "تم تغيير الشارة من {$oldBadgeNameAr} إلى {$newBadge->name_ar} على إعلانك \"{$postTitle}\" لمدة {$days} يوم";
        $messageEn = "Badge changed from {$oldBadgeNameEn} to {$newBadge->name_en} on your post \"{$postTitle}\" for {$days} days";

        if ($refundAmount > 0) {
            $messageAr .= "\nتم استرجاع {$refundAmount} نقطة";
            $messageEn .= "\n{$refundAmount} points refunded";
        }

        $message = json_encode([
            'ar' => "{$messageAr} [post_id:{$post->id}]",
            'en' => "{$messageEn} [post_id:{$post->id}]",
        ], JSON_UNESCAPED_UNICODE);

        Notification::create([
            'user_id' => $post->user_id,
            'type' => 'badge_switched',
            'message' => $message,
        ]);

        try {
            $user = User::find($post->user_id);
            if ($user && !empty($user->fcm_token)) {
                $user->notify(new BadgeChangeNotification('switched', $newBadge->name_ar, $post->id, $message));
            }
        } catch (\Exception $e) {
            Log::warning('FCM badge switched notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Create badge expiration notification
     */
    protected function createExpirationNotification(ServicePost $post): void
    {
        $postTitle = $post->title ?? "#{$post->id}";
        $message = json_encode([
            'ar' => "انتهت صلاحية الشارة على إعلانك \"{$postTitle}\" [post_id:{$post->id}]",
            'en' => "Badge expired on your post \"{$postTitle}\" [post_id:{$post->id}]",
        ], JSON_UNESCAPED_UNICODE);

        Notification::create([
            'user_id' => $post->user_id,
            'type' => 'badge_expired',
            'message' => $message,
        ]);

        try {
            $user = User::find($post->user_id);
            if ($user && !empty($user->fcm_token)) {
                $user->notify(new BadgeChangeNotification('expired', 'الشارة', $post->id, "انتهت صلاحية الشارة على إعلانك: {$post->title}"));
            }
        } catch (\Exception $e) {
            Log::warning('FCM badge expired notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Get badge statistics for admin dashboard
     */
    public function getStatistics(): array
    {
        $badges = BadgeType::withCount('servicePosts')->get();
        $defaultBadge = BadgeType::getDefault();

        $stats = [
            'badges' => [],
            'total_badged_posts' => 0,
            'total_points_used' => 0,
        ];

        foreach ($badges as $badge) {
            $stats['badges'][$badge->slug] = [
                'id' => $badge->id,
                'name' => $badge->name,
                'color' => $badge->color,
                'count' => $badge->service_posts_count,
                'revenue' => $this->calculateBadgeRevenue($badge->id),
            ];

            // Count non-default badges as "badged posts"
            if (!$badge->is_default) {
                $stats['total_badged_posts'] += $badge->service_posts_count;
            }
        }

        // Calculate total points used from badge transactions
        $stats['total_points_used'] = point_transactions::where('type', 'used')
            ->sum('point');

        return $stats;
    }

    /**
     * Calculate revenue generated by a badge type
     */
    protected function calculateBadgeRevenue(int $badgeTypeId): int
    {
        // Sum points used for posts that currently have this badge type
        return point_transactions::where('type', 'used')
            ->whereHas('toUser.servicePosts', function ($query) use ($badgeTypeId) {
                $query->where('badge_type_id', $badgeTypeId);
            })
            ->sum('point');
    }

    /**
     * Migrate old badge data to new system
     */
    public function migrateOldBadges(): int
    {
        $migratedCount = 0;

        // Get posts with old badge names but no badge_type_id
        $posts = ServicePost::whereNull('badge_type_id')
            ->whereNotNull('have_badge')
            ->get();

        foreach ($posts as $post) {
            $badge = BadgeType::getByOldBadgeName($post->have_badge);

            if ($badge) {
                $post->badge_type_id = $badge->id;
                $post->save();
                $migratedCount++;
            }
        }

        return $migratedCount;
    }
}
