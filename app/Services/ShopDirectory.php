<?php

namespace App\Services;

use App\Models\Shop;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Which shops are open for business (2026-10-08). A shop shows (its page, its name on the owner's posts, "shops near
 * you", the sitemap) only while the owner has Business or a shop unlock. There are few shops, so the whole map is
 * cached and every post can carry its shop's name and logo without a query.
 */
class ShopDirectory
{
    private const CACHE = 'shops_active_v1';

    private static ?array $memo = null;

    private static int $memoAt = 0;

    /** @return array<int, array{user_id:int, name:string, logo:?string, slug:string, path:string}> */
    public static function active(): array
    {
        if (self::$memo !== null && time() - self::$memoAt < 60) { // queue workers live long: refresh now and then
            return self::$memo;
        }
        self::$memoAt = time();
        try {
            return self::$memo = self::load();
        } catch (\Throwable $e) {
            // Every post asks for its shop: a problem here (a deploy before its migration) must never break posts.
            \Log::warning('shops.directory_failed', ['message' => $e->getMessage()]);

            return self::$memo = [];
        }
    }

    private static function load(): array
    {
        return Cache::remember(self::CACHE, 300, function () {
            $map = [];
            foreach (Shop::query()->get(['user_id', 'name', 'logo', 'slug']) as $shop) {
                if (FeatureUnlocks::has((int) $shop->user_id, 'shop_page')) {
                    $map[(int) $shop->user_id] = [
                        'user_id' => (int) $shop->user_id,
                        'name' => $shop->name,
                        'logo' => $shop->logo,
                        'slug' => $shop->slug ?: (string) $shop->user_id,
                        'path' => $shop->path(),
                    ];
                }
            }

            return $map;
        });
    }

    /** The shop to show on this user's posts, or null. */
    public static function badge(?int $userId): ?array
    {
        return $userId ? (self::active()[$userId] ?? null) : null;
    }

    public static function isActive(int $userId): bool
    {
        return isset(self::active()[$userId]);
    }

    public static function forget(): void
    {
        self::$memo = null;
        Cache::forget(self::CACHE);
    }

    /** A free link name: the wanted one, else from the shop name (Latin letters), else "shop-{id}"; "-2"... if taken. */
    public static function uniqueSlug(string $name, int $userId, ?string $wanted = null): string
    {
        $base = self::cleanSlug($wanted ?? '') ?: self::cleanSlug(Str::slug(Str::ascii($name))) ?: 'shop-' . $userId;
        if (in_array($base, self::RESERVED, true) || ctype_digit($base)) {
            $base = 'shop-' . $base;
        }
        $slug = $base;
        for ($i = 2; Shop::where('slug', $slug)->where('user_id', '!=', $userId)->exists(); $i++) {
            $slug = $base . '-' . $i;
        }

        return $slug;
    }

    public static function cleanSlug(string $s): string
    {
        $s = trim(preg_replace('/-+/', '-', preg_replace('/[^a-z0-9-]/', '-', strtolower($s))), '-');

        $s = rtrim(substr($s, 0, 40), '-');

        return strlen($s) >= 3 ? $s : '';
    }

    private const RESERVED = ['admin', 'api', 'new', 'near', 'edit', 'settings', 'talabna', 'shop', 'shops'];
}
