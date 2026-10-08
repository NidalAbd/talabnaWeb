<?php

namespace App\Services\Feed;

use App\Models\ServicePost;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * How far each post spreads, and why (2026-10-08, Nidal: "like Facebook: shown to 10,000, 2,000 stopped on it, 1,000
 * read it", and how posts get spread).
 *
 * The funnel, each counted once per person:
 *   reach   - saw it in the feed (on screen 1 s, feed_seen)
 *   stopped - stayed on it 2 s or more (post_engagements dwell)
 *   opened  - opened the post page (post_views)
 *   read    - stayed 8 s on the page or reached the end of the description (post_engagements read)
 *   actions - saved, chatted, made an offer, tapped call / WhatsApp / share
 *
 * Spreading, in stages: 1 the post's city (and the owner's followers), 2 its country, 3 nearby countries,
 * 4 everywhere. A new post is tested in stage 1 until it has a real audience; then a post that does clearly better than
 * similar posts (same category) moves out one stage, and one that does clearly worse moves back. Paid badges start
 * a stage further. Posts outside a viewer's stage are not hidden: they come after the posts meant for that viewer.
 */
class PostDistribution
{
    /** Weight of each step of the funnel in the score. */
    public const WEIGHTS = ['stopped' => 1, 'opened' => 2, 'read' => 3, 'actions' => 5];

    /** How many views of "an average post" the score starts from, so a few lucky views do not decide anything. */
    public const PRIOR_VIEWS = 20;

    /** Below this many people reached, and for the first two days, a post is in its test round. */
    public const TEST_REACH = 30;

    public const TEST_HOURS = 48;

    public const EXPAND_AT = 1.2;

    public const SHRINK_AT = 0.6;

    public const DEFAULT_RATE = 0.4;

    /** @return array{reach:int, stopped:int, opened:int, read:int, actions:int, saves:int, chats:int, offers:int, calls:int, whatsapp:int, shares:int} */
    public function funnel(int $postId): array
    {
        return $this->counts(collect([$postId]))[$postId];
    }

    /**
     * Recomputes the stage of every post that is live (or changed in the last 60 days). Returns how many changed stage.
     * Runs every 10 minutes (posts:distribute).
     */
    public function recompute(): int
    {
        $posts = ServicePost::where('state', 'published')
            ->get(['id', 'categories_id', 'created_at', 'have_badge', 'badge_expires_at']);
        if ($posts->isEmpty()) {
            return 0;
        }
        $counts = $this->counts($posts->pluck('id'));
        $current = DB::table('post_distribution')->whereIn('service_post_id', $posts->pluck('id'))->get()->keyBy('service_post_id');

        // The average engagement per person reached, per category (posts with a real audience only).
        $rates = [];
        foreach ($posts as $p) {
            $c = $counts[$p->id];
            if ($c['reach'] >= 10) {
                $rates[$p->categories_id][] = $this->weighted($c) / $c['reach'];
            }
        }
        $all = array_merge(...array_values($rates ?: [[]]));
        $global = $all ? array_sum($all) / count($all) : self::DEFAULT_RATE;
        $prior = fn ($cat) => isset($rates[$cat]) && count($rates[$cat]) >= 3 ? array_sum($rates[$cat]) / count($rates[$cat]) : $global;

        $changed = 0;
        $now = now();
        foreach ($posts as $p) {
            $c = $counts[$p->id];
            $was = $current->get($p->id);
            $d = $this->decide($c, $prior($p->categories_id) ?: self::DEFAULT_RATE, $was ? (int) $was->stage : null,
                $p->created_at, $this->hasBadge($p));
            if (! $was || (int) $was->stage !== $d['stage']) {
                $changed++;
            }
            DB::table('post_distribution')->updateOrInsert(['service_post_id' => $p->id], $d + [
                'reach' => $c['reach'], 'stopped' => $c['stopped'], 'opened' => $c['opened'], 'read' => $c['read'],
                'actions' => $c['actions'], 'updated_at' => $now,
            ]);
        }

        return $changed;
    }

    /**
     * The stage for one post from its funnel and the average rate of similar posts.
     * @return array{stage:int, testing:bool, score:float, relative:float}
     */
    public function decide(array $c, float $priorRate, ?int $was, $createdAt, bool $badge): array
    {
        $score = ($this->weighted($c) + $priorRate * self::PRIOR_VIEWS) / ($c['reach'] + self::PRIOR_VIEWS);
        $relative = $priorRate > 0 ? $score / $priorRate : 1.0;
        $floor = $badge ? 2 : 1;
        $testing = $c['reach'] < self::TEST_REACH && $createdAt && $createdAt->gt(now()->subHours(self::TEST_HOURS));

        $stage = max($floor, $was ?? $floor);
        if (! $testing) {
            // One stage at a time, and only after the post has had a fair audience at its current stage.
            if ($relative >= self::EXPAND_AT && $c['reach'] >= self::TEST_REACH * $stage) {
                $stage++;
            } elseif ($relative < self::SHRINK_AT) {
                $stage--;
            } elseif ($was === null) {
                $stage = max($floor, 2); // an older post seen for the first time: its country
            }
        }

        return ['stage' => max($floor, min(4, $stage)), 'testing' => (bool) $testing, 'score' => round($score, 4), 'relative' => round($relative, 4)];
    }

    public function weighted(array $c): float
    {
        $sum = 0;
        foreach (self::WEIGHTS as $k => $w) {
            $sum += $w * ($c[$k] ?? 0);
        }

        return (float) $sum;
    }

    private function hasBadge(ServicePost $p): bool
    {
        return $p->have_badge !== null && $p->have_badge !== 'عادي'
            && ($p->badge_expires_at === null || $p->badge_expires_at > now());
    }

    /** All funnel counts for many posts in a handful of grouped queries. */
    private function counts(Collection $ids): array
    {
        $ids = $ids->map(fn ($id) => (int) $id)->values();
        $by = fn ($table, $col = 'service_post_id', ?\Closure $where = null) => DB::table($table)->whereIn($col, $ids)
            ->when($where, $where)->selectRaw("$col as pid, COUNT(*) n")->groupBy($col)->pluck('n', 'pid');
        $reach = $by('feed_seen');
        $opened = $by('post_views');
        $eng = DB::table('post_engagements')->whereIn('service_post_id', $ids)
            ->selectRaw('service_post_id pid, kind, COUNT(*) n')->groupBy('service_post_id', 'kind')->get()
            ->groupBy('pid')->map(fn ($rows) => $rows->pluck('n', 'kind'));
        $saves = $by('favorites', 'favoritable_id', fn ($q) => $q->where('favoritable_type', ServicePost::class));
        $chats = $by('conversations');
        $offers = $by('offers', 'service_post_id', fn ($q) => $q->whereNull('parent_id'));

        $out = [];
        foreach ($ids as $id) {
            $e = $eng->get($id, collect());
            $row = [
                'reach' => (int) ($reach[$id] ?? 0),
                'stopped' => (int) ($e['dwell'] ?? 0),
                'opened' => (int) ($opened[$id] ?? 0),
                'read' => (int) ($e['read'] ?? 0),
                'saves' => (int) ($saves[$id] ?? 0),
                'chats' => (int) ($chats[$id] ?? 0),
                'offers' => (int) ($offers[$id] ?? 0),
                'calls' => (int) ($e['call'] ?? 0),
                'whatsapp' => (int) ($e['whatsapp'] ?? 0),
                'shares' => (int) ($e['share'] ?? 0),
            ];
            $row['actions'] = $row['saves'] + $row['chats'] + $row['offers'] + $row['calls'] + $row['whatsapp'] + $row['shares'];
            // A person can open a post from a link without it reaching them in the feed: reach is at least the opens.
            $row['reach'] = max($row['reach'], $row['opened'], $row['stopped']);
            $out[$id] = $row;
        }

        return $out;
    }
}
