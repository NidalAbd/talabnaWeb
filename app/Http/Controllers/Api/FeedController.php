<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServicePost;
use App\Services\Feed\SponsoredPicker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * GET /api/feed - the Home "All" feed in ONE request (the app used to ask every category separately, about ten requests
 * per scroll). Fresh posts come first; featured (badge) posts are spread through the pages instead of pinned on top.
 * Filters: type, min_price, max_price, country_id, city_id, categories[].
 */
class FeedController extends Controller
{
    private const PER_PAGE = 10;
    private const SPONSORED_POOL = 10;

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
        ]);
        $me = $request->user();
        $page = (int) ($d['page'] ?? 1);

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
        $pool = Cache::remember('feed:sponsored:'.md5(json_encode($d + ['v' => 1], JSON_UNESCAPED_UNICODE)), 60, function () use ($filters) {
            return $filters(ServicePost::query())
                ->where('have_badge', '!=', 'عادي')
                ->where(fn ($q) => $q->whereNull('badge_expires_at')->orWhere('badge_expires_at', '>', now()))
                ->limit(300)->get(['id', 'have_badge'])->map(fn ($p) => ['id' => $p->id, 'have_badge' => $p->have_badge])->all();
        });
        $picked = $this->picker->pick($pool, self::SPONSORED_POOL, $me->id.'|'.now()->format('YmdH'));

        $with = ['subCategory', 'category', 'user.photos'];
        if (! $me->data_saver_enabled) {
            $with[] = 'photos';
        }
        $load = fn ($q) => $q->withCount(['comments', 'favorites'])->with($with);

        $organic = $load($filters(ServicePost::query()))
            ->when($picked, fn ($q) => $q->whereNotIn('id', $picked))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->paginate(self::PER_PAGE, ['*'], 'page', $page);

        $slice = array_slice($picked, max(0, ($page - 1) * count(SponsoredPicker::SLOTS)), count(SponsoredPicker::SLOTS));
        $sponsored = $slice ? $load(ServicePost::query())->whereIn('id', $slice)->get()->keyBy('id')->all() : [];

        $items = $this->picker->mix($organic->items(), $sponsored, $picked, $page);
        $this->enrich($items, $me);
        $organic->setCollection(collect($items));

        return response()->json(['servicePosts' => $organic]);
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
