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

        $posts = ServicePost::query()
            ->where('state', 'published')
            ->whereBetween('location_latitudes', [$lat - $dLat, $lat + $dLat])
            ->whereBetween('location_longitudes', [$lng - $dLng, $lng + $dLng])
            ->where(fn ($q) => $q->where('location_latitudes', '!=', self::DEFAULT_LAT)->orWhere('location_longitudes', '!=', self::DEFAULT_LNG))
            ->whereNotIn('categories_id', [6, 7])
            ->when(! empty($d['category_id']), fn ($q) => $q->where('categories_id', (int) $d['category_id']))
            ->when(! empty($d['type']), fn ($q) => $q->where('type', $d['type']))
            ->when(trim((string) ($d['q'] ?? '')) !== '', function ($q) use ($d) {
                // Same matching as search: plain text, or JSON-escaped (Arabic stored as \u....).
                $text = trim((string) $d['q']);
                $plain = '%'.addcslashes($text, '%_\\').'%';
                $escaped = '%'.addcslashes(trim(json_encode($text), '"'), '%_\\').'%';
                $q->where(fn ($w) => $w->where('title', 'LIKE', $plain)->orWhere('description', 'LIKE', $plain)
                    ->orWhere('title', 'LIKE', $escaped)->orWhere('description', 'LIKE', $escaped));
            })
            ->with('photos')
            ->latest()
            ->limit(400)
            ->get(['id', 'title', 'price', 'price_type', 'price_max', 'price_currency_code', 'type', 'location_latitudes',
                'location_longitudes', 'categories_id', 'sub_categories_id', 'reserved_at', 'state', 'have_badge']);

        $pins = $posts->map(function ($p) use ($lat, $lng) {
            $p->distance = round(ServicePost::distance($lat, $lng, $p->location_latitudes, $p->location_longitudes), 2);

            return $p;
        })->filter(fn ($p) => $p->distance <= $r * 1.05)->sortBy('distance')->take(200)->values();

        return response()->json(['posts' => $pins]);
    }
}
