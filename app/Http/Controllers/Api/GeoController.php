<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\cities;
use App\Models\countries;
use App\Services\Geo\LocationResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** The user's country and city from what the phone knows, and confirming them (2026-10-10). */
class GeoController extends Controller
{
    public function __construct(private LocationResolver $resolver) {}

    /** POST /api/geo/resolve {iso: "PS", names: ["Khan Younis", "Khan Yunis Governorate"]} */
    public function resolve(Request $request): JsonResponse
    {
        $d = $request->validate([
            'iso' => 'nullable|string|max:2',
            'names' => 'nullable|array|max:6',
            'names.*' => 'nullable|string|max:120',
        ]);
        $r = $this->resolver->resolve($d['iso'] ?? null, $d['names'] ?? []);

        return response()->json([
            'country' => $r['country'] ? self::country($r['country']) : null,
            'city' => $r['city'] ? self::city($r['city']) : null,
        ]);
    }

    /** POST /api/user/location {country_id, city_id?, lat?, lng?}: the user confirms (or corrects) their place. */
    public function confirm(Request $request): JsonResponse
    {
        $user = $request->user();
        $d = $request->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'city_id' => 'nullable|integer',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
        ]);
        $city = isset($d['city_id']) ? cities::where('id', $d['city_id'])->where('country_id', $d['country_id'])->first() : null;
        if (isset($d['city_id']) && ! $city) {
            return response()->json(['error_type' => 'validation', 'field' => 'city', 'message' => 'This city is not in that country.'], 422);
        }

        $changing = (int) $d['country_id'] !== (int) $user->country_id;
        if ($changing && ($days = $user->countryChangeDaysLeft()) > 0) {
            return response()->json([
                'error_type' => 'country_locked', 'field' => 'country', 'days_left' => $days,
                'message' => json_encode([
                    'en' => "You can't change your country now. You can change it again in {$days} days.",
                    'ar' => "لا يمكنك تغيير دولتك الآن. يمكنك تغييرها بعد {$days} يومًا.",
                ], JSON_UNESCAPED_UNICODE),
            ], 422);
        }
        // Confirming the place for the first time is not a "change" (the old default was never the user's choice)
        if ($changing && $user->location_confirmed_at && $user->phone_verified_at) {
            $user->country_changed_at = now();
        }
        $user->country_id = (int) $d['country_id'];
        $user->city_id = $city?->id;
        if (isset($d['lat'], $d['lng'])) {
            $user->location_latitudes = $d['lat'];
            $user->location_longitudes = $d['lng'];
        }
        $user->location_confirmed_at = now();
        $user->save();

        return response()->json([
            'status' => 'success',
            'country' => self::country(countries::find($user->country_id)),
            'city' => $city ? self::city($city) : null,
        ]);
    }

    private static function country(countries $c): array
    {
        $a = $c->toArray();

        return ['id' => $c->id, 'name' => $a['name'], 'country_code' => $c->country_code, 'iso_code' => $c->iso_code];
    }

    private static function city(cities $c): array
    {
        $a = $c->toArray();

        return ['id' => $c->id, 'name' => $a['name'], 'country_id' => $c->country_id];
    }
}
