<?php

namespace App\Services\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Records HOW and FROM WHAT each account signs in, so the admin can see statistics per provider
 * (google / apple / email) and per device platform (android / ios / web).
 *
 * The apps send `platform` in the auth request (or an `X-App-Platform` header); if absent it is
 * inferred from the User-Agent. Tracking is best-effort: it must never break a sign-in.
 */
class AuthTracker
{
    public const PLATFORMS = ['android', 'ios', 'web'];
    public const METHODS = ['google', 'apple', 'email', 'review'];

    public static function platform(Request $request): string
    {
        $declared = strtolower(trim((string) ($request->input('platform') ?: $request->header('X-App-Platform', ''))));
        if (in_array($declared, self::PLATFORMS, true)) {
            return $declared;
        }
        $ua = strtolower((string) $request->userAgent());
        if (str_contains($ua, 'okhttp') || str_contains($ua, 'android')) {
            return 'android';
        }
        if (str_contains($ua, 'cfnetwork') || str_contains($ua, 'darwin') || str_contains($ua, 'iphone') || str_contains($ua, 'ipad')) {
            return 'ios';
        }
        if ($ua !== '' && preg_match('/mozilla|chrome|safari|firefox|edg\//', $ua)) {
            return 'web';
        }

        return 'unknown';
    }

    /** Call right after a successful sign-in / sign-up. $isSignup = the account was created by this request. */
    public static function record(int $userId, string $method, Request $request, bool $isSignup = false): void
    {
        try {
            $platform = self::platform($request);
            $now = now();
            $set = ['last_login_method' => $method, 'last_login_platform' => $platform, 'last_login_at' => $now];
            if ($isSignup) {
                $set['sign_up_method'] = $method;
                $set['sign_up_platform'] = $platform;
            }
            DB::table('users')->where('id', $userId)->update($set);
            DB::table('auth_events')->insert([
                'user_id' => $userId, 'method' => $method, 'platform' => $platform,
                'is_signup' => $isSignup, 'created_at' => $now,
            ]);
        } catch (\Throwable $e) {
            Log::warning('auth.track failed', ['user_id' => $userId, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Aggregate numbers for the admin dashboard.
     * @return array{total:int, by_signup_method:array, by_last_login_platform:array, by_last_login_method:array, linked:array, new_7d:array, active_7d_by_platform:array}
     */
    public static function stats(): array
    {
        $count = fn (string $col) => DB::table('users')->select($col, DB::raw('count(*) as n'))->groupBy($col)->pluck('n', $col)
            ->mapWithKeys(fn ($n, $k) => [($k ?? 'unknown') === '' ? 'unknown' : ($k ?? 'unknown') => (int) $n])->all();
        $since = now()->subDays(7);

        return [
            'total' => (int) DB::table('users')->count(),
            'by_signup_method' => $count('sign_up_method'),
            'by_last_login_platform' => $count('last_login_platform'),
            'by_last_login_method' => $count('last_login_method'),
            'linked' => [
                'google' => (int) DB::table('users')->whereNotNull('google_id')->count(),
                'apple' => (int) DB::table('users')->whereNotNull('apple_id')->count(),
                'both' => (int) DB::table('users')->whereNotNull('google_id')->whereNotNull('apple_id')->count(),
            ],
            'new_7d' => DB::table('auth_events')->where('is_signup', true)->where('created_at', '>=', $since)
                ->select('platform', DB::raw('count(*) as n'))->groupBy('platform')->pluck('n', 'platform')->map(fn ($n) => (int) $n)->all(),
            'active_7d_by_platform' => DB::table('auth_events')->where('created_at', '>=', $since)
                ->select('platform', DB::raw('count(distinct user_id) as n'))->groupBy('platform')->pluck('n', 'platform')->map(fn ($n) => (int) $n)->all(),
        ];
    }
}
