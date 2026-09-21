<?php

namespace App\Support;

use App\Models\UserBlock;
use Illuminate\Support\Facades\Auth;

/** Block-list lookups used to hide blocked users' content and to stop messaging (guideline 1.2). */
class Blocks
{
    /** @var array<int, int[]> per-request memo of "users I blocked" */
    private static array $memo = [];

    /** Ids of the users $userId has blocked. */
    public static function blockedBy(int $userId): array
    {
        // Never let a missing table (migration not run yet on this server) break feeds or chat.
        return self::$memo[$userId] ??= self::safely(fn () => UserBlock::where('blocker_id', $userId)->pluck('blocked_id')->all(), []);
    }

    /** True if either user has blocked the other. */
    public static function exists(int $a, int $b): bool
    {
        return self::safely(fn () => UserBlock::where(fn ($q) => $q->where('blocker_id', $a)->where('blocked_id', $b))
            ->orWhere(fn ($q) => $q->where('blocker_id', $b)->where('blocked_id', $a))
            ->exists(), false);
    }

    private static function safely(callable $query, mixed $fallback): mixed
    {
        try {
            return $query();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('blocks.unavailable', ['message' => $e->getMessage()]);

            return $fallback;
        }
    }

    /** Ids hidden from the signed-in API user (empty for guests, admin sessions and jobs). */
    public static function hiddenFromCurrentUser(): array
    {
        $id = Auth::guard('api')->id();

        return $id ? self::blockedBy((int) $id) : [];
    }

    public static function forget(?int $userId = null): void
    {
        if ($userId === null) {
            self::$memo = [];
        } else {
            unset(self::$memo[$userId]);
        }
    }
}
