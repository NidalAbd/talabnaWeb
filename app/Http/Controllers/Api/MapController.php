<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServicePost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET /api/feed/map?lat&lng&radius_km&category_id&type&q (Release C, 2026-10-07; q = words in the title/description): live posts with a real location inside
 * the visible area, for the map view. Small pins only (id, position, title, price, first photo); up to 200, nearest
 * first. Posts on the app's default placeholder location are left out.
 */
class MapController extends Controller
{
    private const DEFAULT_LAT = 31.9539;
    private const DEFAULT_LNG = 35.2376;

    public function index(Request $request): JsonResponse
    {
        $d = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius_km' => 'nullable|numeric|min:1|max:200',
            'category_id' => 'nullable|integer',
            'type' => 'nullable|in:عرض,طلب',
            'q' => 'nullable|string|max:80',
        ]);
        $lat = (float) $d['lat'];
        $lng = (float) $d['lng'];
        $r = (float) ($d['radius_km'] ?? 25);
        // Bounding box first (uses plain comparisons), then exact distance for the order.
        $dLat = $r / 111.0;
        $dLng = $r / (111.0 * max(0.2, cos(deg2rad($lat))));

        // Where a post sits on the map: its own location, or (2026-10-08) its owner's location rounded to about 1 km
        // when the post has none. Only 52 of 336 live posts had their own, so the map showed three or four pins;
        // rounding keeps the owner's exact address private (an area, like other marketplaces show).
        $own = '(service_posts.location_latitudes IS NOT NULL AND service_posts.location_latitudes != 0 AND NOT (service_posts.location_latitudes = '.self::DEFAULT_LAT.' AND service_posts.location_longitudes = '.self::DEFAULT_LNG.'))';
        $userOk = '(users.location_latitudes IS NOT NULL AND users.location_latitudes != 0 AND NOT (users.location_latitudes = '.self::DEFAULT_LAT.' AND users.location_longitudes = '.self::DEFAULT_LNG.'))';
        $latExpr = "CASE WHEN $own THEN service_posts.location_latitudes WHEN $userOk THEN ROUND(users.location_latitudes, 2) END";
        $lngExpr = "CASE WHEN $own THEN service_posts.location_longitudes WHEN $userOk THEN ROUND(users.location_longitudes, 2) END";

        $posts = ServicePost::query()
            ->join('users', 'users.id', '=', 'service_posts.user_id')
            ->where('service_posts.state', 'published')
            ->whereRaw("($latExpr) BETWEEN ? AND ?", [$lat - $dLat, $lat + $dLat])
            ->whereRaw("($lngExpr) BETWEEN ? AND ?", [$lng - $dLng, $lng + $dLng])
            ->whereNotIn('service_posts.categories_id', [6, 7])
            ->when(! empty($d['category_id']), fn ($q) => $q->where('service_posts.categories_id', (int) $d['category_id']))
            ->when(! empty($d['type']), fn ($q) => $q->where('service_posts.type', $d['type']))
            ->when(trim((string) ($d['q'] ?? '')) !== '', function ($q) use ($d) {
                // Same matching as search: plain text, or JSON-escaped (Arabic stored as \u....).
                $text = trim((string) $d['q']);
                $plain = '%'.addcslashes($text, '%_\\').'%';
                $escaped = '%'.addcslashes(trim(json_encode($text), '"'), '%_\\').'%';
                $q->where(fn ($w) => $w->where('service_posts.title', 'LIKE', $plain)->orWhere('service_posts.description', 'LIKE', $plain)
                    ->orWhere('service_posts.title', 'LIKE', $escaped)->orWhere('service_posts.description', 'LIKE', $escaped));
            })
            ->with('photos')
            ->latest('service_posts.created_at')
            ->limit(400)
            ->select(['service_posts.id', 'service_posts.title', 'service_posts.price', 'service_posts.price_type', 'service_posts.price_max',
                'service_posts.price_currency_code', 'service_posts.type', 'service_posts.categories_id', 'service_posts.sub_categories_id',
                'service_posts.reserved_at', 'service_posts.state', 'service_posts.have_badge'])
            ->selectRaw("($latExpr) as location_latitudes, ($lngExpr) as location_longitudes, NOT $own as approximate_location")
            ->get();

        $pins = $posts->map(function ($p) use ($lat, $lng) {
            $p->distance = round(ServicePost::distance($lat, $lng, $p->location_latitudes, $p->location_longitudes), 2);

            return $p;
        })->filter(fn ($p) => $p->distance <= $r * 1.05)->sortBy('distance')->take(200)->values();

        return response()->json(['posts' => $pins]);
    }
}
