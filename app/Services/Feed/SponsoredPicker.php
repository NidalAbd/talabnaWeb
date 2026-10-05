<?php

namespace App\Services\Feed;

use Illuminate\Support\Facades\Cache;

/**
 * Badge (featured) posts are spread through the feed instead of being pinned above everything.
 *
 * Owner request (2026-10-05): with only a handful of featured posts, the old hourly rotation put the same ones in front
 * of every user on every visit. Now, like current promoted-post feeds:
 * - frequency cap: one user sees the same featured post at most CAP_PER_DAY times a day, at least COOLDOWN_HOURS apart
 *   (Home feed; inside a category the user is browsing on purpose, so no cap there);
 * - pacing: posts already shown a lot today step back for those shown less, so each paid post gets its share across its
 *   whole badge period instead of all at once; the tier sets the share (WEIGHTS);
 * - relevance: same city / same country are more likely;
 * - a scroll session keeps one order (seed per user and SESSION_MINUTES), so pages don't repeat or jump.
 * mix() puts them at SLOTS of each page, never as the first cards.
 */
class SponsoredPicker
{
    /** Higher tier = bigger share. Any other non-normal badge (silver) counts as 1. */
    public const WEIGHTS = ['ماسي' => 4, 'ذهبي' => 2];

    /** Positions (0-based) inside a page where a featured post is placed (3rd and 8th card). */
    public const SLOTS = [2, 7];

    public const CAP_PER_DAY = 2;
    public const COOLDOWN_HOURS = 3;
    public const SESSION_MINUTES = 20;

    /** Same impressions-per-post as the day's average halves a post's chance. */
    private const PACING_SOFTNESS = 1.0;

    /**
     * @param iterable<array{id:int,have_badge:string,country_id?:int|null,city_id?:int|null}|object> $candidates
     * @param array{country_id?:int|null,city_id?:int|null,seen?:array<int,array{0:int,1:int}>,shown?:array<int,int>,now?:int} $viewer
     *        seen: this user's [times, last unix time] per post today (from seenToday()) - applies the frequency cap;
     *        shown: impressions per post today, all users (from impressionsToday()) - pacing
     * @return int[] ids, best first
     */
    public function pick(iterable $candidates, int $limit, string $seed, array $viewer = []): array
    {
        $seen = $viewer['seen'] ?? [];
        $capped = array_key_exists('seen', $viewer);
        $now = (int) ($viewer['now'] ?? time());

        $rows = [];
        foreach ($candidates as $c) {
            $c = (array) $c;
            $id = (int) $c['id'];
            if ($capped && isset($seen[$id])) {
                [$count, $last] = $seen[$id];
                if ($count >= self::CAP_PER_DAY || $now - $last < self::COOLDOWN_HOURS * 3600) {
                    continue;
                }
            }
            $rows[$id] = $c;
        }
        if (! $rows || $limit <= 0) {
            return [];
        }

        $shown = $viewer['shown'] ?? [];
        $avg = max(1, array_sum($shown) / max(1, count($rows)));

        $keyed = [];
        foreach ($rows as $id => $c) {
            $weight = self::WEIGHTS[(string) ($c['have_badge'] ?? '')] ?? 1;
            $weight /= 1 + self::PACING_SOFTNESS * (($shown[$id] ?? 0) / $avg);
            if (! empty($viewer['city_id']) && (int) ($c['city_id'] ?? 0) === (int) $viewer['city_id']) {
                $weight *= 1.6;
            } elseif (! empty($viewer['country_id']) && (int) ($c['country_id'] ?? 0) === (int) $viewer['country_id']) {
                $weight *= 1.3;
            }
            // Efraimidis-Spirakis: key = u^(1/w) with u in (0,1) derived from the seed and the id (order independent).
            $u = (hexdec(substr(hash('sha256', $seed.'|'.$id), 0, 12)) + 1) / (16 ** 12 + 2);
            $keyed[$id] = $u ** (1 / $weight);
        }
        arsort($keyed);

        return array_slice(array_keys($keyed), 0, max(0, $limit));
    }

    /** Seed that stays the same while a user scrolls (pages agree) and changes between visits. */
    public static function sessionSeed(int $userId): string
    {
        return $userId.'|'.intdiv(time(), self::SESSION_MINUTES * 60);
    }

    /**
     * Remember that [userId] was shown these featured posts (for the frequency cap) and count them for pacing.
     *
     * @param int[] $ids
     */
    public function recordShown(int $userId, array $ids): void
    {
        if (! $ids) {
            return;
        }
        $day = date('Ymd');
        $key = "feat:seen:{$userId}:{$day}";
        $seen = Cache::get($key, []);
        $now = time();
        foreach ($ids as $id) {
            [$count, $last] = $seen[$id] ?? [0, 0];
            // A page fetched again within the session (pull to refresh) isn't a new impression.
            if ($now - $last < self::SESSION_MINUTES * 60 && $count > 0) {
                continue;
            }
            $seen[$id] = [$count + 1, $now];
            $counter = "feat:imp:{$day}:{$id}";
            Cache::add($counter, 0, 172800);
            Cache::increment($counter);
        }
        Cache::put($key, $seen, 172800);
    }

    /** @return array<int, array{0:int,1:int}> post id => [times shown today, last shown unix time] */
    public function seenToday(int $userId): array
    {
        return Cache::get("feat:seen:{$userId}:".date('Ymd'), []);
    }

    /**
     * @param int[] $ids
     * @return array<int,int> impressions today, all users
     */
    public function impressionsToday(array $ids): array
    {
        $day = date('Ymd');
        $out = [];
        foreach ($ids as $id) {
            $out[$id] = (int) Cache::get("feat:imp:{$day}:{$id}", 0);
        }

        return $out;
    }

    /**
     * Page $page (1-based) gets the next slice of the picked list, inserted at SLOTS.
     *
     * @template T
     * @param array<int,T> $organic the page's organic items
     * @param array<int,T> $sponsoredById picked items keyed by id, in the order of $pickedIds
     * @param int[] $pickedIds
     * @return array<int,T>
     */
    public function mix(array $organic, array $sponsoredById, array $pickedIds, int $page): array
    {
        $per = count(self::SLOTS);
        $slice = array_slice($pickedIds, max(0, ($page - 1) * $per), $per);
        $out = array_values($organic);
        $offset = 0;
        foreach ($slice as $i => $id) {
            if (! isset($sponsoredById[$id])) {
                continue;
            }
            $at = min(self::SLOTS[$i] + $offset, count($out));
            array_splice($out, $at, 0, [$sponsoredById[$id]]);
            $offset++;
        }

        return $out;
    }
}
