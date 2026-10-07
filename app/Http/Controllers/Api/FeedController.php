<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServicePost;
use App\Services\Feed\NearestCountries;
use App\Services\Feed\SponsoredPicker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * GET /api/feed - the Home "All" feed in ONE request (the app used to ask every category separately, about ten requests
 * per scroll). Fresh posts come first; featured (badge) posts are spread through the pages instead of pinned on top.
 * Filters: type, min_price, max_price, country_id, city_id, categories[].
 *
 * Seen posts (2026-10-06): the app reports posts that stayed on screen (POST /api/feed/seen); the feed puts posts the
 * user has not seen yet first and the seen ones after them, so every visit starts with something new. `seen_before`
 * (unix time, sent by the app with every page of one scroll session) freezes what counts as seen, so posts marked
 * while scrolling don't reshuffle the next pages.
 *
 * Nearest country (2026-10-08): without a country filter the user's own country comes first, then the other countries
 * by distance. A country filter on a country with no posts shows the nearest country that has some instead of an
 * empty feed (`fallback_country_id` in the answer says which).
 */
class FeedController extends Controller
{
    private const PER_PAGE = 10;

    /** Seen rows older than this are deleted (feed:prune-seen), so old posts can come back eventually. */
    public const SEEN_DAYS = 30;

    public function __construct(private SponsoredPicker $picker)
    {
    }

    public function all(Request $request): JsonResponse
    {
        $d = $request->validate([
            'page' => 'nullable|integer|min:1',
            'type' => 'nullable|in:عرض,طلب',
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric',
            'country_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',
            'categories' => 'nullable|array',
            'categories.*' => 'integer',
            'seen_before' => 'nullable|integer|min:0',
        ]);
        $me = $request->user();
        $page = (int) ($d['page'] ?? 1);
        // App versions before 1.5.7 neither report what was on screen nor send seen_before. For them the server keeps
        // the scroll session (it starts on page 1) and counts the posts it sends as seen, so they get the same
        // "new posts first" feed. Newer apps report posts that really stayed on screen.
        $reportsSeen = isset($d['seen_before']);
        $sessionKey = "feed:session:{$me->id}";
        if (! $reportsSeen && $page === 1) {
            Cache::put($sessionKey, now()->timestamp, now()->addHours(6));
        }
        $since = $reportsSeen ? (int) $d['seen_before'] : (int) Cache::get($sessionKey, now()->timestamp);
        $seenBefore = now()->setTimestamp(min($since, now()->timestamp));

        // A chosen country with no matching posts: use the nearest country that has some (not for a city filter).
        $fallbackCountry = null;
        if (isset($d['country_id']) && ! isset($d['city_id'])) {
            $fallbackCountry = $this->nearestCountryWithPosts((int) $d['country_id'], $d);
            if ($fallbackCountry !== null) {
                $d['country_id'] = $fallbackCountry;
            }
        }

        $filters = function ($q) use ($d) {
            $q->where('state', 'published')
                ->whereHas('user', fn ($u) => $u->where('is_active', 'active'));
            empty($d['categories']) ? $q->whereNotIn('categories_id', [6, 7]) : $q->whereIn('categories_id', $d['categories']);
            isset($d['type']) && $q->where('type', $d['type']);
            isset($d['min_price']) && $q->where('price', '>=', (float) $d['min_price']);
            isset($d['max_price']) && $q->where('price', '<=', (float) $d['max_price']);
            isset($d['country_id']) && $q->where('country_id', (int) $d['country_id']);
            isset($d['city_id']) && $q->where('city_id', (int) $d['city_id']);

            return $q;
        };

        // Featured posts that match the filters. Cached for a minute and shared by everyone with the same filters.
        $pool = Cache::remember('feed:sponsored:'.md5(json_encode($d + ['v' => 2], JSON_UNESCAPED_UNICODE)), 60, function () use ($filters) {
            return $filters(ServicePost::query())
                ->where('have_badge', '!=', 'عادي')
                ->where(fn ($q) => $q->whereNull('badge_expires_at')->orWhere('badge_expires_at', '>', now()))
                ->limit(300)->get(['id', 'have_badge', 'country_id', 'city_id'])
                ->map(fn ($p) => ['id' => $p->id, 'have_badge' => $p->have_badge, 'country_id' => $p->country_id, 'city_id' => $p->city_id])->all();
        });
        // Each page picks from the featured posts this user may still see today (frequency cap), so a visit
        // doesn't open on the same ones as the last.
        $picked = $this->picker->pick($pool, count(SponsoredPicker::SLOTS), SponsoredPicker::sessionSeed($me->id).'|'.$page, [
            'country_id' => $me->country_id, 'city_id' => $me->city_id,
            'seen' => $this->picker->seenToday($me->id),
            'shown' => $this->picker->impressionsToday(array_column($pool, 'id')),
        ]);

        $with = ['subCategory', 'category', 'user.photos'];
        if (! $me->data_saver_enabled) {
            $with[] = 'photos';
        }
        $load = fn ($q) => $q->withCount(['comments', 'favorites'])->with($with);

        // Featured posts only come through their slots here (keeps pages stable while they rotate).
        $poolIds = array_column($pool, 'id');
        $organic = $load($filters(ServicePost::query()))
            ->when($poolIds, fn ($q) => $q->whereNotIn('id', $poolIds))
            ->orderByRaw('EXISTS (SELECT 1 FROM feed_seen fs WHERE fs.user_id = ? AND fs.service_post_id = service_posts.id AND fs.seen_at < ?) ASC',
                [$me->id, $seenBefore])
            ->when(! isset($d['country_id']) && $me->country_id, function ($q) use ($me) {
                // Own country first, then the nearest ones.
                $order = NearestCountries::ids((int) $me->country_id);
                if ($order) {
                    $q->orderByRaw('FIELD(service_posts.country_id, ' . implode(',', array_reverse($order)) . ') DESC');
                }
            })
            ->orderByDesc('created_at')->orderByDesc('id')
            ->paginate(self::PER_PAGE, ['*'], 'page', $page);

        $sponsored = $picked ? $load(ServicePost::query())->whereIn('id', $picked)->get()->keyBy('id')->all() : [];

        $items = $this->picker->mix($organic->items(), $sponsored, $picked, 1);
        $this->picker->recordShown($me->id, array_keys($sponsored));
        $this->enrich($items, $me);
        $organic->setCollection(collect($items));
        if (! $reportsSeen && $items) {
            $now = now();
            DB::table('feed_seen')->insertOrIgnore(array_map(fn ($p) => ['user_id' => $me->id, 'service_post_id' => $p->id, 'seen_at' => $now], $items));
        }

        return response()->json(['servicePosts' => $organic] + ($fallbackCountry ? ['fallback_country_id' => $fallbackCountry] : []));
    }

    /** The nearest country (not $countryId) with posts matching the other filters, or null when $countryId has some. */
    private function nearestCountryWithPosts(int $countryId, array $d): ?int
    {
        $match = function (int $id) use ($d) {
            $q = ServicePost::query()->where('state', 'published')->where('country_id', $id);
            empty($d['categories']) ? $q->whereNotIn('categories_id', [6, 7]) : $q->whereIn('categories_id', $d['categories']);
            isset($d['type']) && $q->where('type', $d['type']);
            isset($d['min_price']) && $q->where('price', '>=', (float) $d['min_price']);
            isset($d['max_price']) && $q->where('price', '<=', (float) $d['max_price']);

            return $q->exists();
        };
        if ($match($countryId)) {
            return null;
        }
        foreach (NearestCountries::ids($countryId) as $id) {
            if ($id !== $countryId && $match($id)) {
                return $id;
            }
        }

        return null;
    }


    /** POST /api/feed/seen {ids: [..]} - posts that stayed on screen in the feed. The first time counts (stable order). */
    public function seen(Request $request): JsonResponse
    {
        $d = $request->validate(['ids' => 'required|array|max:200', 'ids.*' => 'integer|min:1']);
        $uid = $request->user()->id;
        $ids = ServicePost::whereIn('id', array_unique($d['ids']))->pluck('id');
        $now = now();
        DB::table('feed_seen')->insertOrIgnore($ids->map(fn ($id) => ['user_id' => $uid, 'service_post_id' => $id, 'seen_at' => $now])->all());

        return response()->json(['ok' => true, 'count' => $ids->count()]);
    }

    /** The per-post extras the app shows, computed for the whole page with a few queries instead of a few per post. */
    private function enrich(array $posts, $me): void
    {
        if (! $posts) {
            return;
        }
        $ids = array_map(fn ($p) => $p->id, $posts);
        $owners = array_unique(array_map(fn ($p) => $p->user_id, $posts));

        $favorited = DB::table('favorites')->where('user_id', $me->id)->where('favoritable_type', ServicePost::class)
            ->whereIn('favoritable_id', $ids)->pluck('favoritable_id')->flip();
        $followed = $me->followers()->whereIn('follower_id', $owners)->pluck('follower_id')->flip();

        foreach ($posts as $p) {
            $p->user_photo = $p->user?->photos?->first();
            $p->user_name = $p->user?->user_name;
            $p->is_favorited = $favorited->has($p->id);
            $p->is_followed = $followed->has($p->user_id);
            $p->distance = round(ServicePost::distance($me->location_latitudes, $me->location_longitudes, $p->location_latitudes, $p->location_longitudes), 2);
            $p->makeHidden('user');
        }
    }
}
