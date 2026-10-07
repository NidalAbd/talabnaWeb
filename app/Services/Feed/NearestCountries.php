<?php

namespace App\Services\Feed;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Countries ordered by distance from a user's country (centroids in config/country_centroids.php), so a feed shows the
 * nearest posts first and a country with no posts falls back to its nearest neighbour with some (2026-10-08).
 */
class NearestCountries
{
    /**
     * Country ids that have published posts, nearest first (the given country first when it has posts).
     * @return int[]
     */
    public static function ids(int $countryId): array
    {
        return Cache::remember("feed:countries-by-distance:{$countryId}", 600, function () use ($countryId) {
            $centroids = config('country_centroids', []);
            $iso = DB::table('countries')->pluck('iso_code', 'id')->map(fn ($c) => strtoupper((string) $c));
            $from = $centroids[$iso[$countryId] ?? ''] ?? null;
            $withPosts = DB::table('service_posts')->where('state', 'published')->distinct()->pluck('country_id')->filter()->map(fn ($id) => (int) $id)->all();

            $dist = [];
            foreach ($withPosts as $id) {
                $to = $centroids[$iso[$id] ?? ''] ?? null;
                $dist[$id] = $id === $countryId ? -1 : (($from && $to) ? self::km($from, $to) : PHP_INT_MAX);
            }
            asort($dist);

            return array_keys($dist);
        });
    }


    /** Great-circle distance in km between two [lat, lng] points. */
    public static function km(array $a, array $b): int
    {
        [$lat1, $lng1, $lat2, $lng2] = array_map('deg2rad', [$a[0], $a[1], $b[0], $b[1]]);
        $h = sin(($lat2 - $lat1) / 2) ** 2 + cos($lat1) * cos($lat2) * sin(($lng2 - $lng1) / 2) ** 2;

        return (int) round(6371 * 2 * asin(min(1, sqrt($h))));
    }
}
