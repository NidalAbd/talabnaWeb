<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\palservice_points;
use App\Models\point_transactions;
use Illuminate\Support\Facades\DB;

/**
 * Release C (2026-10-07): Pro/Business features bought one at a time with points. Prices sit next to what Talabna
 * already charges (an extra photo is 1 point, +3 featured posts 10, Business 100 a month); admins can change them with
 * the setting unlock.<feature>.points. The server always checks: plan first, then an unlock.
 */
class FeatureUnlocks
{
    /** feature => [points, days (null = as long as the post exists), per post?, plans that include it] */
    public const CATALOG = [
        'insights_post' => [1, null, true, ['pro', 'business']],
        'insights_all' => [5, 30, false, ['pro', 'business']],
        'saved_searches_pack' => [2, 30, false, []],
        'spin_post' => [2, null, true, ['pro', 'business']],
        'shop_page' => [20, 30, false, ['business']],
        'advanced_insights' => [10, 30, false, ['business']],
    ];

    public static function price(string $feature): int
    {
        return (int) AppSetting::get("unlock.{$feature}.points", self::CATALOG[$feature][0] ?? 0);
    }

    public static function planSlug(int $userId): ?string
    {
        try {
            return app(SubscriptionService::class)->getActiveSubscription($userId)?->plan?->slug;
        } catch (\Throwable) {
            return null;
        }
    }

    /** Included in the plan, or unlocked (for this post when it is a per-post feature). */
    public static function has(int $userId, string $feature, ?int $targetId = null): bool
    {
        $def = self::CATALOG[$feature] ?? null;
        if (! $def) {
            return false;
        }
        if (in_array(self::planSlug($userId), $def[3], true)) {
            return true;
        }

        return self::activeQuery($userId, $feature, $def[2] ? $targetId : null)->exists();
    }

    public static function activeCount(int $userId, string $feature): int
    {
        return self::activeQuery($userId, $feature, null)->count();
    }

    private static function activeQuery(int $userId, string $feature, ?int $targetId)
    {
        return DB::table('feature_unlocks')->where('user_id', $userId)->where('feature', $feature)
            ->when($targetId !== null, fn ($q) => $q->where('target_id', $targetId))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    /**
     * Charges the points and records the unlock. ['ok'=>true] or ['ok'=>false,'code'=>...]. With $renew, a time-limited
     * feature that is still running (bought, not from the plan) is extended from its current end date (2026-10-08:
     * a shop owner could not renew before the 30 days ran out).
     */
    public static function buy(int $userId, string $feature, ?int $targetId = null, bool $renew = false): array
    {
        $def = self::CATALOG[$feature] ?? null;
        if (! $def) {
            return ['ok' => false, 'code' => 'unknown'];
        }
        if ($def[2] && ! $targetId) {
            return ['ok' => false, 'code' => 'target_required'];
        }
        $extendFrom = null;
        if (! $def[2] && self::has($userId, $feature) && $feature !== 'saved_searches_pack') {
            $viaPlan = in_array(self::planSlug($userId), $def[3], true);
            if (! $renew || $viaPlan || ! $def[1]) {
                return ['ok' => true, 'already' => true];
            }
            $extendFrom = self::activeQuery($userId, $feature, null)->max('expires_at');
        }
        if ($def[2] && self::has($userId, $feature, $targetId)) {
            return ['ok' => true, 'already' => true];
        }
        $points = self::price($feature);

        $result = DB::transaction(function () use ($userId, $feature, $targetId, $points, $def, $extendFrom) {
            $balance = palservice_points::where('user_id', $userId)->lockForUpdate()->first();
            $have = (int) ($balance?->point ?? 0);
            if ($have < $points) {
                return ['ok' => false, 'code' => 'insufficient_points', 'required' => $points, 'balance' => $have];
            }
            if ($points > 0) {
                $balance->decrement('point', $points);
                point_transactions::create(['to_user_id' => $userId, 'from_user_id' => $userId, 'type' => 'used', 'point' => $points]);
            }
            DB::table('feature_unlocks')->insert([
                'user_id' => $userId, 'feature' => $feature, 'target_id' => $def[2] ? $targetId : null, 'points' => $points,
                'expires_at' => $def[1] ? ($extendFrom ? \Illuminate\Support\Carbon::parse($extendFrom) : now())->addDays($def[1]) : null,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            return ['ok' => true, 'points' => $points, 'balance' => $have - $points];
        });
        if ($feature === 'shop_page' && $result['ok']) {
            ShopDirectory::forget(); // the shop shows at once
        }

        return $result;
    }
}
