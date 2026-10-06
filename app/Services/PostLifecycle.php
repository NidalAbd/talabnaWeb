<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\ServicePost;
use App\Models\User;
use App\Notifications\PostActivityNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Release A (2026-10-07): posts end after N days (admin setting posts.expiry_days, default 30) and are renewed in one tap;
 * Pro/Business renew automatically. Owners can mark a post reserved or sold. Ended and sold posts leave the `published`
 * state, so every public list hides them.
 */
class PostLifecycle
{
    public const REMIND_DAYS = 3;

    public static function expiryDays(): int
    {
        return max(1, (int) AppSetting::get('posts.expiry_days', 30));
    }

    /** Renew allowed when ended, or within a week of ending (keeps "renew" from being a free daily bump). */
    public static function canRenew(ServicePost $post): bool
    {
        if ($post->state === 'expired') {
            return true;
        }

        return $post->state === 'published' && ($post->expires_at === null || $post->expires_at->lte(now()->addDays(7)));
    }

    public static function renew(ServicePost $post): ServicePost
    {
        $post->forceFill([
            'state' => 'published',
            'expires_at' => now()->addDays(self::expiryDays()),
            'expiry_reminded_at' => null,
            'sold_at' => null,
        ])->save();

        return $post;
    }

    /** status: available | reserved | sold */
    public static function setSaleStatus(ServicePost $post, string $status): ServicePost
    {
        $fill = match ($status) {
            'sold' => ['state' => 'sold', 'sold_at' => now(), 'reserved_at' => null],
            'reserved' => ['state' => 'published', 'reserved_at' => now(), 'sold_at' => null],
            default => ['state' => 'published', 'reserved_at' => null, 'sold_at' => null],
        };
        $post->forceFill($fill);
        // Back to "available" after it already ended: give it a fresh period.
        if ($status !== 'sold' && ($post->expires_at === null || $post->expires_at->isPast())) {
            $post->expires_at = now()->addDays(self::expiryDays());
            $post->expiry_reminded_at = null;
        }
        $post->save();

        return $post;
    }

    public static function ownerAutoRenews(int $userId): bool
    {
        try {
            return app(SubscriptionService::class)->hasFeature($userId, 'auto_renew_posts');
        } catch (\Throwable) {
            return false;
        }
    }

    /** Daily: remind before the end, renew plan posts, end the rest. Returns counts for the log. */
    public static function runDaily(): array
    {
        $counts = ['reminded' => 0, 'renewed' => 0, 'expired' => 0];

        ServicePost::where('state', 'published')->whereNull('expiry_reminded_at')
            ->whereBetween('expires_at', [now(), now()->addDays(self::REMIND_DAYS)])
            ->chunkById(200, function ($posts) use (&$counts) {
                foreach ($posts as $post) {
                    $post->forceFill(['expiry_reminded_at' => now()])->save();
                    if (self::ownerAutoRenews($post->user_id)) {
                        continue; // renewed when it ends; no need to worry them
                    }
                    self::notify($post, 'expiring', ['days' => (string) max(1, now()->diffInDays($post->expires_at, false) + 1)]);
                    $counts['reminded']++;
                }
            });

        ServicePost::where('state', 'published')->where('expires_at', '<', now())
            ->chunkById(200, function ($posts) use (&$counts) {
                foreach ($posts as $post) {
                    if (self::ownerAutoRenews($post->user_id)) {
                        self::renew($post);
                        self::notify($post, 'renewed');
                        $counts['renewed']++;
                    } else {
                        $post->forceFill(['state' => 'expired'])->save();
                        self::notify($post, 'expired');
                        $counts['expired']++;
                    }
                }
            });

        return $counts;
    }

    /** A lower price on a saved post: tell the people who saved it (not the owner). */
    public static function notifyPriceDrop(ServicePost $post, float $oldPrice): void
    {
        $new = (float) $post->price;
        if ($new <= 0 || $new >= $oldPrice || ! in_array($post->price_type ?? 'fixed', ['fixed', 'negotiable'], true)) {
            return;
        }
        $userIds = DB::table('favorites')->where('favoritable_type', ServicePost::class)
            ->where('favoritable_id', $post->id)->where('user_id', '!=', $post->user_id)->pluck('user_id');
        $price = rtrim(rtrim(number_format($new, 2, '.', ','), '0'), '.').' '.($post->price_currency_code ?? '');
        foreach (User::whereIn('id', $userIds)->get() as $user) {
            try {
                $user->notify(new PostActivityNotification('price_drop', $post->id, ['title' => self::title($post, $user->locale ?? 'ar'), 'price' => trim($price)]));
            } catch (\Throwable $e) {
                Log::warning('price drop push failed', ['post' => $post->id, 'user' => $user->id, 'error' => $e->getMessage()]);
            }
        }
    }

    private static function notify(ServicePost $post, string $kind, array $params = []): void
    {
        try {
            $owner = User::find($post->user_id);
            $owner?->notify(new PostActivityNotification($kind, $post->id, $params + ['title' => self::title($post, $owner->locale ?? 'ar')]));
        } catch (\Throwable $e) {
            Log::warning('post lifecycle push failed', ['post' => $post->id, 'kind' => $kind, 'error' => $e->getMessage()]);
        }
    }

    public static function title(ServicePost $post, string $locale): string
    {
        $t = $post->title;
        if (is_array($t)) {
            $t = $t[$locale] ?? $t['ar'] ?? $t['en'] ?? (reset($t) ?: '');
        }

        return mb_strimwidth((string) $t, 0, 60, '…');
    }
}
