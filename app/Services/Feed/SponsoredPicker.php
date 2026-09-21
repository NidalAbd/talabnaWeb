<?php

namespace App\Services\Feed;

/**
 * Badge (featured) posts are spread through the feed instead of being pinned above everything.
 *
 * - pick(): a weighted random choice among the active badge posts. It is seeded (user + hour), so one user sees a stable
 *   set while scrolling and pulling to refresh, but different users, and the same user next hour, see different ones:
 *   every featured post gets its turn, not only whichever sorts first.
 * - mix(): puts a couple of them into each page at fixed positions, between fresh organic posts.
 */
class SponsoredPicker
{
    /** Higher tier = more likely to be picked. Any other non-normal badge counts as 1. */
    public const WEIGHTS = ['ماسي' => 4, 'ذهبي' => 2];

    /** Positions (0-based) inside a page where a featured post is placed. */
    public const SLOTS = [1, 6];

    /**
     * @param iterable<array{id:int,have_badge:string}|object> $candidates
     * @return int[] ids, best first
     */
    public function pick(iterable $candidates, int $limit, string $seed): array
    {
        $keyed = [];
        foreach ($candidates as $c) {
            $id = (int) (is_array($c) ? $c['id'] : $c->id);
            $badge = (string) (is_array($c) ? $c['have_badge'] : $c->have_badge);
            $weight = self::WEIGHTS[$badge] ?? 1;
            // Efraimidis-Spirakis: key = u^(1/w) with u in (0,1) derived from the seed and the id (order independent).
            $u = (hexdec(substr(hash('sha256', $seed.'|'.$id), 0, 12)) + 1) / (16 ** 12 + 2);
            $keyed[$id] = $u ** (1 / $weight);
        }
        arsort($keyed);

        return array_slice(array_keys($keyed), 0, max(0, $limit));
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
