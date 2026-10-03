<?php

namespace App\Services;

use App\Models\BadgeType;

/**
 * What a subscription plan actually gives (2026-10-03 — these features were
 * advertised but never applied):
 *  - featured_posts:        each one takes 7 days of a Silver badge off a badge purchase
 *  - badge_discount_percent: % off every badge purchase/upgrade
 *  - bonus_points_percent:   extra points on store point purchases
 *  - max_photos_per_post:    photo/video slots per post that are free
 * Values come from the plan as it was when the user subscribed.
 */
class PlanPerks
{
    public const FEATURED_DAYS = 7;

    private static function sub(?int $userId)
    {
        return $userId ? app(SubscriptionService::class)->getActiveSubscription($userId) : null;
    }

    /**
     * Badge price after plan perks.
     * @return array{0:int,1:bool} [points to charge, whether a featured-post use applies]
     */
    public static function badgePrice(int $userId, int $baseCost, int $days): array
    {
        $sub = self::sub($userId);
        if (!$sub || $baseCost <= 0) return [$baseCost, false];
        $cost = $baseCost;
        $featured = false;
        $limit = (int) $sub->getFeature('featured_posts', 0);
        $used = (int) (($sub->usage ?? [])['featured_posts_used'] ?? 0);
        if ($limit > $used) {
            $silverPerDay = (int) (BadgeType::where('slug', 'silver')->value('points_per_day') ?? 2);
            $off = min($cost, $silverPerDay * min($days, self::FEATURED_DAYS));
            if ($off > 0) {
                $cost -= $off;
                $featured = true;
            }
        }
        $discount = (int) $sub->getFeature('badge_discount_percent', 0);
        if ($discount > 0 && $cost > 0) {
            $cost = (int) floor($cost * (100 - min(90, $discount)) / 100);
        }
        return [max(0, $cost), $featured];
    }

    /** Only the discount (used for upgrades of an existing badge). */
    public static function discounted(int $userId, int $cost): int
    {
        $sub = self::sub($userId);
        $discount = $sub ? (int) $sub->getFeature('badge_discount_percent', 0) : 0;
        return $discount > 0 && $cost > 0 ? (int) floor($cost * (100 - min(90, $discount)) / 100) : $cost;
    }

    public static function consumeFeatured(int $userId): void
    {
        app(SubscriptionService::class)->useFeature($userId, 'featured_posts_used', 1);
    }

    /** Extra points granted on a store purchase of $points. */
    public static function bonusPoints(int $userId, int $points): int
    {
        $sub = self::sub($userId);
        $pct = $sub ? (int) $sub->getFeature('bonus_points_percent', 0) : 0;
        return $pct > 0 ? (int) floor($points * min(100, $pct) / 100) : 0;
    }

    /** Free photo/video slots per post from the plan (0 = none). */
    public static function freePhotos(?int $userId): int
    {
        $sub = self::sub($userId);
        return $sub ? (int) $sub->getFeature('max_photos_per_post', 0) : 0;
    }
}
