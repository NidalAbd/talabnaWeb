<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-device push tokens (2026-10-03). Every device a user signs in on is
 * kept in user_push_tokens; users.fcm_token is just the latest one.
 */
class PushTokens
{
    private static ?bool $ready = null;

    private static function ready(): bool
    {
        return self::$ready ??= Schema::hasTable('user_push_tokens');
    }

    public static function remember(int $userId, string $token): void
    {
        if ($token === '' || !self::ready()) return;
        DB::table('user_push_tokens')->updateOrInsert(['token_hash' => hash('sha256', $token)], [
            'user_id' => $userId, 'token' => $token,
            'last_seen_at' => now(), 'updated_at' => now(), 'created_at' => now(),
        ]);
        // A device belongs to one account at a time.
        DB::table('users')->where('fcm_token', $token)->where('id', '!=', $userId)->update(['fcm_token' => null]);
    }

    public static function forget(string $token): void
    {
        if ($token === '') return;
        if (self::ready()) {
            DB::table('user_push_tokens')->where('token_hash', hash('sha256', $token))->delete();
        }
        DB::table('users')->where('fcm_token', $token)->update(['fcm_token' => null]);
    }

    /** All live tokens for a user, latest first (max 10). */
    public static function forUser(int $userId, ?string $latest): array
    {
        $tokens = self::ready()
            ? DB::table('user_push_tokens')->where('user_id', $userId)
                ->where('last_seen_at', '>', now()->subDays(90))
                ->orderByDesc('last_seen_at')->limit(10)->pluck('token')->all()
            : [];
        if ($latest && !in_array($latest, $tokens, true)) array_unshift($tokens, $latest);
        return $tokens;
    }
}
