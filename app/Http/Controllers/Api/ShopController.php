<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\cities;
use App\Models\countries;
use App\Models\ServicePost;
use App\Models\Shop;
use App\Models\Sub_categories;
use App\Services\Feed\NearestCountries;
use App\Services\FeatureUnlocks;
use App\Services\ShopDirectory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Shops (Release C 2026-10-07, storefronts 2026-10-08). Business includes a shop; others unlock it with points for 30
 * days. While active, a shop has its own page (app and talbna.cloud/shop/{slug}, indexed by Google), its name and logo
 * on all the owner's posts, a place in "shops near you", and daily visit/contact counts for the owner. When the plan or
 * unlock ends, all of that disappears by itself; the shop's details are kept for when it comes back.
 *
 * Opening hours are stored per day; "open now" is worked out on the phone or in the browser, in local time.
 */
class ShopController extends Controller
{
    private const POST_COLS = ['id', 'user_id', 'title', 'description', 'price', 'price_type', 'price_max', 'price_currency_code',
        'type', 'have_badge', 'badge_type_id', 'state', 'reserved_at', 'categories_id', 'sub_categories_id', 'country_id',
        'city_id', 'view_count', 'created_at'];

    // ---------------------------------------------------------------- public

    /** GET /api/public/shops/{key}: the shop page (key = link name or owner id). Counts one visit per viewer a day. */
    public function page(Request $request, string $key): JsonResponse
    {
        $shop = $this->findActive($key);
        if (! $shop) {
            return response()->json(['message' => 'Shop not found', 'code' => 'not_found'], 404);
        }
        $viewer = auth('api')->user();
        $isOwner = $viewer && (int) $viewer->id === (int) $shop->user_id;
        if (! $isOwner) {
            $who = $viewer ? 'u' . $viewer->id : 'ip' . sha1((string) $request->ip());
            if (Cache::add("shop_visit:{$shop->user_id}:{$who}:" . now()->toDateString(), 1, 86400)) {
                $this->bump($shop->user_id, 'visits');
            }
        }

        $live = $this->livePosts($shop->user_id);
        $shelves = (clone $live)->select('sub_categories_id', DB::raw('count(*) as n'))->groupBy('sub_categories_id')
            ->orderByDesc('n')->pluck('n', 'sub_categories_id');
        $subs = Sub_categories::whereIn('id', $shelves->keys())->get()->keyBy('id');
        $locale = app()->getLocale();

        $featuredIds = array_slice(array_map('intval', $shop->featured_post_ids ?? []), 0, 4);
        $featured = collect();
        if ($featuredIds) {
            $fq = (clone $live)->whereIn('id', $featuredIds);
            $featured = ($request->boolean('web')
                ? $fq->with(['photos', 'category', 'subCategory', 'city', 'country', 'badgeType'])->get()
                : $this->withCard($fq)->get(self::POST_COLS))
                ->sortBy(fn ($p) => array_search($p->id, $featuredIds))->values();
            if ($request->boolean('web')) {
                $public = app(PublicController::class);
                $featured = $featured->map(fn ($p) => $public->transformListing($p))->values();
            }
        }

        return response()->json([
            'shop' => $this->present($shop, $locale),
            'stats' => [
                'products' => (clone $live)->count(),
                'followers' => DB::table('followers')->where('user_id', $shop->user_id)->count(),
                'sold' => ServicePost::where('user_id', $shop->user_id)->where(fn ($q) => $q->where('state', 'sold')->orWhereNotNull('sold_at'))->count(),
            ],
            'shelves' => $shelves->map(fn ($n, $id) => ['id' => (int) $id, 'name' => $this->localName($subs->get($id)?->name, $locale), 'count' => (int) $n])
                ->filter(fn ($s) => $s['name'] !== '')->values(),
            'featured' => $featured,
            'is_owner' => $isOwner,
            'is_following' => $viewer && ! $isOwner
                ? DB::table('followers')->where('user_id', $shop->user_id)->where('follower_id', $viewer->id)->exists() : false,
        ]);
    }

    /** GET /api/public/shops/{key}/posts?shelf=&q=&page=: the shop's products, newest first (?web=1: web card format). */
    public function posts(Request $request, string $key): JsonResponse
    {
        $shop = $this->findActive($key);
        if (! $shop) {
            return response()->json(['message' => 'Shop not found', 'code' => 'not_found'], 404);
        }
        $q = $this->livePosts($shop->user_id)
            ->when($request->integer('shelf'), fn ($q, $sub) => $q->where('sub_categories_id', $sub))
            ->when(trim((string) $request->query('q')) !== '', function ($q) use ($request) {
                $term = '%' . addcslashes(mb_substr(trim((string) $request->query('q')), 0, 60), '%_\\') . '%';
                $q->where(fn ($w) => $w->where('title', 'like', $term)->orWhere('description', 'like', $term));
            })
            ->latest();

        if ($request->boolean('web')) {
            $page = $q->with(['photos', 'category', 'subCategory', 'city', 'country', 'badgeType'])->paginate(24);
            $public = app(PublicController::class);

            return response()->json([
                'listings' => collect($page->items())->map(fn ($p) => $public->transformListing($p))->values(),
                'pagination' => ['total' => $page->total(), 'current_page' => $page->currentPage(), 'last_page' => $page->lastPage()],
            ]);
        }
        $page = $this->withCard($q)->paginate(20, self::POST_COLS);

        return response()->json(['data' => $page->items(), 'current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()]);
    }

    /**
     * GET /api/public/shops?lat=&lng=&country_id=&category_id=&q=&limit=: shops near the viewer (by distance when the shop
     * has a map point, else same city/country first, then the nearest countries), plus shop counts per category.
     */
    public function near(Request $request): JsonResponse
    {
        $active = array_keys(ShopDirectory::active());
        if (! $active) {
            return response()->json(['shops' => [], 'categories' => []]);
        }
        $lat = $request->filled('lat') ? (float) $request->query('lat') : null;
        $lng = $request->filled('lng') ? (float) $request->query('lng') : null;
        $countryId = $request->integer('country_id') ?: null;
        $limit = min(60, max(1, $request->integer('limit', 12)));
        $q = trim(mb_substr((string) $request->query('q', ''), 0, 60));
        $locale = app()->getLocale();

        $shops = Shop::whereIn('user_id', $active)
            ->when($request->integer('category_id'), fn ($w, $c) => $w->where('category_id', $c))
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', '%' . addcslashes($q, '%_\\') . '%')
                ->orWhere('about', 'like', '%' . addcslashes($q, '%_\\') . '%')))
            ->get();
        $counts = ServicePost::where('state', 'published')->whereIn('user_id', $shops->pluck('user_id'))
            ->select('user_id', DB::raw('count(*) as n'))->groupBy('user_id')->pluck('n', 'user_id');
        $shops = $shops->filter(fn ($s) => ($counts[$s->user_id] ?? 0) > 0); // a shop with nothing on sale is not shown

        $near = $countryId ? array_flip(NearestCountries::ids($countryId)) : [];
        $shops = $shops->map(function (Shop $s) use ($lat, $lng, $countryId, $near) {
            $s->distance_km = ($lat !== null && $lng !== null && $s->lat !== null && $s->lng !== null)
                ? round(self::km($lat, $lng, $s->lat, $s->lng), 1) : null;
            $s->rank = $s->distance_km ?? (($countryId && (int) $s->country_id === $countryId ? 5000 : 20000) + ($near[$s->country_id] ?? 999) * 10);

            return $s;
        })->sortBy(fn ($s) => [$s->rank, -($counts[$s->user_id] ?? 0)])->values();

        $previews = ServicePost::where('state', 'published')->whereIn('user_id', $shops->take($limit)->pluck('user_id'))
            ->with('photos')->latest()->get(['id', 'user_id'])->groupBy('user_id');

        $categoryCounts = $shops->whereNotNull('category_id')->countBy('category_id');
        $cats = Categories::whereIn('id', $categoryCounts->keys())->get()->keyBy('id');

        return response()->json([
            'shops' => $shops->take($limit)->map(fn (Shop $s) => $this->present($s, $locale) + [
                'products' => (int) ($counts[$s->user_id] ?? 0),
                'distance_km' => $s->distance_km,
                'previews' => ($previews->get($s->user_id) ?? collect())->take(3)
                    ->map(fn ($p) => optional($p->photos->first())->src)->filter()->values(),
            ])->values(),
            'categories' => $categoryCounts->map(fn ($n, $id) => ['id' => (int) $id, 'name' => $this->localName($cats->get($id)?->name, $locale), 'count' => $n])
                ->sortByDesc('count')->values(),
        ]);
    }

    /** POST /api/public/shops/{key}/contact {channel: call|whatsapp|chat|map}: counts a contact for the owner's insights. */
    public function contact(Request $request, string $key): JsonResponse
    {
        $shop = $this->findActive($key);
        if ($shop && in_array($request->input('channel'), ['call', 'whatsapp', 'chat', 'map'], true)) {
            $viewer = auth('api')->user();
            if (! $viewer || (int) $viewer->id !== (int) $shop->user_id) {
                $this->bump($shop->user_id, 'contacts');
            }
        }

        return response()->json(['ok' => true]);
    }

    /** GET /api/users/{id}/shop: the shop if active, else null (kept for app versions already installed). */
    public function show(int $id): JsonResponse
    {
        $shop = ShopDirectory::isActive($id) ? Shop::where('user_id', $id)->first() : null;

        return response()->json(['shop' => $shop ? $this->present($shop, app()->getLocale()) : null]);
    }

    // ---------------------------------------------------------------- owner

    /** GET /api/me/shop: mine, whether it is active (and until when), the price, and the last 30 days. */
    public function mine(Request $request): JsonResponse
    {
        $uid = (int) $request->user()->id;
        $shop = Shop::where('user_id', $uid)->first();
        $active = FeatureUnlocks::has($uid, 'shop_page');
        $plan = in_array(FeatureUnlocks::planSlug($uid), FeatureUnlocks::CATALOG['shop_page'][3], true);

        return response()->json([
            'shop' => $shop ? $this->present($shop, app()->getLocale(), true) : null,
            'active' => $active,
            'via_plan' => $plan,
            'expires_at' => $active && ! $plan ? DB::table('feature_unlocks')->where('user_id', $uid)->where('feature', 'shop_page')
                ->max('expires_at') : null,
            'price' => FeatureUnlocks::price('shop_page'),
            'insights' => $shop ? $this->insights($uid) : null,
            'products' => ServicePost::where('user_id', $uid)->where('state', 'published')->count(),
        ]);
    }

    /**
     * POST /api/me/shop (multipart): name, slug, about, hours, week_hours (JSON), phone, whatsapp, address, lat, lng,
     * country_id, city_id, category_id, offer, offer_until, featured_post_ids (JSON), logo, cover. Fields not sent
     * stay as they are.
     */
    public function save(Request $request): JsonResponse
    {
        $uid = (int) $request->user()->id;
        if (! FeatureUnlocks::has($uid, 'shop_page')) {
            return response()->json(['message' => 'Shop pages come with Business or can be unlocked with points.', 'code' => 'locked',
                'price' => FeatureUnlocks::price('shop_page')], 403);
        }
        $d = $request->validate([
            'name' => 'required|string|min:2|max:80',
            'slug' => 'nullable|string|max:60',
            'about' => 'nullable|string|max:1000',
            'hours' => 'nullable|string|max:200',
            'week_hours' => 'nullable|string|max:2000',
            'phone' => 'nullable|string|max:30',
            'whatsapp' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:200',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'country_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',
            'category_id' => 'nullable|integer',
            'offer' => 'nullable|string|max:160',
            'offer_until' => 'nullable|date',
            'featured_post_ids' => 'nullable|string|max:200',
            'logo' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:4096',
            'cover' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:8192',
        ]);
        $shop = Shop::firstOrNew(['user_id' => $uid]);
        $shop->name = trim($d['name']);
        foreach (['about', 'hours', 'phone', 'whatsapp', 'address', 'offer'] as $f) {
            if ($request->has($f)) {
                $shop->{$f} = ($v = trim((string) ($d[$f] ?? ''))) === '' ? null : $v;
            }
        }
        foreach (['lat', 'lng'] as $f) {
            if ($request->has($f)) {
                $shop->{$f} = $request->filled($f) ? (float) $d[$f] : null;
            }
        }
        if ($request->has('offer_until')) {
            $shop->offer_until = $d['offer_until'] ?? null;
        }
        if ($request->filled('country_id')) {
            $shop->country_id = countries::whereKey($d['country_id'])->exists() ? (int) $d['country_id'] : $shop->country_id;
        }
        if ($request->filled('city_id')) {
            $shop->city_id = cities::whereKey($d['city_id'])->exists() ? (int) $d['city_id'] : $shop->city_id;
        }
        if ($request->filled('category_id')) {
            $shop->category_id = Categories::whereKey($d['category_id'])->exists() ? (int) $d['category_id'] : $shop->category_id;
        }
        if ($request->has('week_hours')) {
            $shop->week_hours = $this->cleanWeek(json_decode((string) ($d['week_hours'] ?? ''), true));
        }
        if ($request->has('featured_post_ids')) {
            $ids = array_slice(array_values(array_unique(array_map('intval', (array) json_decode((string) ($d['featured_post_ids'] ?? ''), true)))), 0, 4);
            $own = ServicePost::where('user_id', $uid)->whereIn('id', $ids)->pluck('id')->all();
            $shop->featured_post_ids = array_values(array_filter($ids, fn ($id) => in_array($id, $own, true)));
        }
        if ($request->has('slug') || ! $shop->slug) {
            $shop->slug = ShopDirectory::uniqueSlug($shop->name, $uid, $d['slug'] ?? null);
        }
        $this->fillDefaults($shop);
        foreach (['logo', 'cover'] as $f) {
            if ($request->hasFile($f)) {
                $old = $shop->{$f};
                $shop->{$f} = 'storage/' . $request->file($f)->store('shops', 'public');
                if ($old && str_starts_with($old, 'storage/shops/')) {
                    Storage::disk('public')->delete(substr($old, strlen('storage/')));
                }
            }
        }
        $shop->save();
        ShopDirectory::forget();
        Cache::forget('seo_v6_' . md5($shop->path() . 'ar'));
        Cache::forget('seo_crawl_v3_' . md5($shop->path() . 'ar'));
        Cache::forget('seo_thin_v2_' . md5($shop->path()));

        return response()->json(['shop' => $this->present($shop->fresh(), app()->getLocale(), true)]);
    }

    // ---------------------------------------------------------------- helpers

    private function findActive(string $key): ?Shop
    {
        $shop = ctype_digit($key) ? Shop::where('user_id', (int) $key)->first() : Shop::where('slug', strtolower($key))->first();

        return $shop && ShopDirectory::isActive((int) $shop->user_id) ? $shop : null;
    }

    private function livePosts(int $userId)
    {
        return ServicePost::where('user_id', $userId)->where('state', 'published');
    }

    private function withCard($q)
    {
        return $q->with(['photos', 'category', 'subCategory']);
    }

    /** Where it is and what it sells, from the owner's posts, until the owner sets them. */
    private function fillDefaults(Shop $shop): void
    {
        if ($shop->country_id && $shop->city_id && $shop->category_id) {
            return;
        }
        $posts = ServicePost::where('user_id', $shop->user_id)->whereIn('state', ['published', 'sold'])->latest()->limit(50)
            ->get(['country_id', 'city_id', 'categories_id']);
        $top = fn (string $col) => $posts->whereNotNull($col)->countBy($col)->sortDesc()->keys()->first();
        $shop->country_id ??= $top('country_id');
        $shop->city_id ??= $top('city_id');
        $shop->category_id ??= $top('categories_id');
    }

    /** Opening days as [{d: 0-6 (Sunday = 0), open: "HH:MM", close: "HH:MM"}]; a close before open runs past midnight. */
    private function cleanWeek($week): ?array
    {
        if (! is_array($week)) {
            return null;
        }
        $out = [];
        foreach ($week as $row) {
            $d = $row['d'] ?? null;
            $open = (string) ($row['open'] ?? '');
            $close = (string) ($row['close'] ?? '');
            if (is_numeric($d) && $d >= 0 && $d <= 6 && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $open) && preg_match('/^([01]\d|2[0-4]):[0-5]\d$/', $close)) {
                $out[] = ['d' => (int) $d, 'open' => $open, 'close' => $close];
            }
        }
        usort($out, fn ($a, $b) => [$a['d'], $a['open']] <=> [$b['d'], $b['open']]);

        return array_slice($out, 0, 14);
    }

    private static function km(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        [$a, $b, $c, $d] = array_map('deg2rad', [$lat1, $lng1, $lat2, $lng2]);
        $h = sin(($c - $a) / 2) ** 2 + cos($a) * cos($c) * sin(($d - $b) / 2) ** 2;

        return 6371 * 2 * asin(min(1, sqrt($h)));
    }

    private function bump(int $userId, string $col): void
    {
        DB::table('shop_daily_stats')->upsert(
            ['shop_user_id' => $userId, 'day' => now()->toDateString(), $col => 1],
            ['shop_user_id', 'day'],
            [$col => DB::raw("{$col} + 1")]
        );
    }

    private function insights(int $uid): array
    {
        $from = now()->subDays(59)->toDateString();
        $rows = DB::table('shop_daily_stats')->where('shop_user_id', $uid)->where('day', '>=', $from)->get()->keyBy(fn ($r) => (string) $r->day);
        $days = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $days[] = ['day' => $day, 'visits' => (int) ($rows[$day]->visits ?? 0), 'contacts' => (int) ($rows[$day]->contacts ?? 0)];
        }
        $prev = $rows->filter(fn ($r, $day) => $day < now()->subDays(29)->toDateString());
        $followers = DB::table('followers')->where('user_id', $uid);

        return [
            'days' => $days,
            'visits' => array_sum(array_column($days, 'visits')),
            'contacts' => array_sum(array_column($days, 'contacts')),
            'visits_prev' => (int) $prev->sum('visits'),
            'contacts_prev' => (int) $prev->sum('contacts'),
            'followers' => (clone $followers)->count(),
            'followers_new' => (clone $followers)->where('created_at', '>=', now()->subDays(30))->count(),
            'followers_new_prev' => (clone $followers)->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->count(),
        ];
    }

    public function present(Shop $shop, string $locale, bool $forOwner = false): array
    {
        $city = $shop->city_id ? cities::find($shop->city_id) : null;
        $country = $shop->country_id ? countries::find($shop->country_id) : null;
        $category = $shop->category_id ? Categories::find($shop->category_id) : null;

        return [
            'user_id' => (int) $shop->user_id,
            'slug' => $shop->slug ?: (string) $shop->user_id,
            'url' => rtrim(config('app.url', 'https://talbna.cloud'), '/') . $shop->path(),
            'name' => $shop->name,
            'logo' => $shop->logo,
            'cover' => $shop->cover,
            'about' => $shop->about,
            'hours' => $shop->hours,
            'week_hours' => $shop->week_hours ?? [],
            'phone' => $shop->phone,
            'whatsapp' => $shop->whatsapp,
            'address' => $shop->address,
            'lat' => $shop->lat,
            'lng' => $shop->lng,
            'city' => $city ? ['id' => $city->id, 'name' => $this->localName($city->name, $locale)] : null,
            'country' => $country ? ['id' => $country->id, 'name' => $this->localName($country->name, $locale)] : null,
            'category' => $category ? ['id' => $category->id, 'name' => $this->localName($category->name, $locale)] : null,
            'offer' => $forOwner ? $shop->offer : $shop->currentOffer(),
            'offer_until' => $shop->offer_until?->toDateString(),
            'featured_post_ids' => $shop->featured_post_ids ?? [],
            'since' => $shop->created_at?->toDateString(),
        ];
    }

    private function localName($name, string $locale): string
    {
        if (is_string($name)) {
            $d = json_decode($name, true);
            $name = is_array($d) ? $d : $name;
        }
        if (is_array($name)) {
            return (string) ($name[$locale] ?? $name['ar'] ?? $name['en'] ?? reset($name) ?: '');
        }

        return (string) $name;
    }
}
