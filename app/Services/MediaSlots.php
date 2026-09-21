<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Models\palservice_points;
use App\Models\point_transactions;
use Illuminate\Support\Facades\DB;

/**
 * Photos/videos per post. The first few are free; more cost points each. The price is only ever taken when the post is
 * saved (never for slots the user opens and abandons), and inside the caller's transaction so a failed save takes nothing.
 */
class MediaSlots
{
    public static function free(): int
    {
        return (int) config('ai.media.free', 4);
    }

    public static function max(): int
    {
        return (int) config('ai.media.max', 10);
    }

    public static function pointsEach(): int
    {
        return (int) config('ai.media.extra_points', 1);
    }

    /** Only NEW slots beyond what the post already had (or the free amount) are charged. */
    public static function extraCost(int $alreadyOnPost, int $newFiles): int
    {
        if ($newFiles <= 0) {
            return 0;
        }
        $after = $alreadyOnPost + $newFiles;
        $paidFor = max(self::free(), $alreadyOnPost);

        return max(0, $after - $paidFor) * self::pointsEach();
    }

    public static function exceedsMax(int $alreadyOnPost, int $newFiles): bool
    {
        return $alreadyOnPost + $newFiles > self::max();
    }

    /**
     * Take the points now. Call inside the transaction that saves the post.
     *
     * @throws InsufficientBalanceException
     */
    public static function charge(int $userId, int $points, ?int $postId = null): void
    {
        if ($points <= 0) {
            return;
        }
        $balance = palservice_points::where('user_id', $userId)->lockForUpdate()->first();
        $current = (int) ($balance?->point ?? 0);
        if ($current < $points) {
            throw new InsufficientBalanceException($current, $points);
        }
        $balance->decrement('point', $points);
        point_transactions::create([
            'from_user_id' => $userId,
            'to_user_id' => $userId,
            'type' => 'used',
            'point' => $points,
            'status' => 'completed',
            'metadata' => json_encode(['reason' => 'extra_media', 'post_id' => $postId]),
        ]);
    }
}
