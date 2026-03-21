<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BadgeType;
use App\Models\Categories;
use App\Models\Sub_categories;
use App\Models\ServicePost;
use App\Models\User;
use App\Models\cities;
use App\Models\countries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PublicController extends Controller
{
    /**
     * Helper to decode JSON name field (handles double-encoded JSON)
     */
    private function decodeName($name)
    {
        if (is_array($name)) {
            return $name;
        }
        if (is_string($name)) {
            $decoded = json_decode($name, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // If decoded result is still a string, it was double-encoded
                if (is_string($decoded)) {
                    $decoded2 = json_decode($decoded, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded2)) {
                        return $decoded2;
                    }
                }
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }
        return ['ar' => $name, 'en' => $name];
    }

    /**
     * Get localized name from a JSON name field.
     * Returns the name in the current app locale with fallbacks.
     */
    private function localizedNameFor($name, string $locale): string
    {
        $decoded = $this->decodeName($name);
        return $decoded[$locale] ?? $decoded['en'] ?? $decoded['ar'] ?? array_values(array_filter($decoded))[0] ?? '';
    }

    private function localizedName($name): string
    {
        $locale = app()->getLocale();
        $decoded = $this->decodeName($name);
        return $decoded[$locale] ?? $decoded['en'] ?? $decoded['ar'] ?? array_values(array_filter($decoded))[0] ?? '';
    }

    /**
     * Map language codes to their primary countries (ISO codes → country IDs).
     */
    private static array $languageCountryMap = [
        'ar' => ['PS','EG','SA','AE','IQ','JO','KW','BH','QA','YE','SY','LB','LY','TN','DZ','MA','SD','OM','MR','SO','DJ','KM'],
        'en' => ['US','GB','CA','AU','NZ','IE','ZA','KE','NG','GH','PH','SG'],
        'hi' => ['IN'],
        'tr' => ['TR','CY'],
        'fr' => ['FR','BE','SN','CI','CM','CD','MG'],
        'es' => ['ES','MX','CO','AR','PE','VE','CL'],
        'ur' => ['PK'],
        'bn' => ['BD'],
        'pt' => ['BR','PT'],
        'ru' => ['RU','KZ','BY'],
        'id' => ['ID'],
        'de' => ['DE','AT','CH'],
        'zh' => ['CN','TW','HK'],
        'ku' => ['IQ','TR','SY','IR'],
        'fa' => ['IR','AF','TJ'],
        'sw' => ['TZ','KE','UG'],
        'ms' => ['MY','BN','SG'],
    ];

    /**
     * Get country IDs that share the same language as the current locale.
     */
    private function getSameLanguageCountryIds(): array
    {
        $locale = app()->getLocale();
        $isoCodes = self::$languageCountryMap[$locale] ?? [];
        if (empty($isoCodes)) return [];

        return countries::whereIn('iso_code', $isoCodes)->pluck('id')->toArray();
    }

    /**
     * Apply location-aware sorting to a query.
     * Priority: 1) User's city  2) User's country  3) Same language countries  4) Rest
     */
    private function applyLocationSort($query, Request $request)
    {
        $userCountryId = $request->query('user_country_id');
        $userCityId = $request->query('user_city_id');
        $sameLanguageIds = $this->getSameLanguageCountryIds();

        if ($userCountryId || !empty($sameLanguageIds)) {
            $orderParts = [];
            $bindings = [];

            if ($userCountryId && $userCityId) {
                $orderParts[] = "WHEN country_id = ? AND city_id = ? THEN 1";
                $bindings[] = $userCountryId;
                $bindings[] = $userCityId;
            }

            if ($userCountryId) {
                $orderParts[] = "WHEN country_id = ? THEN 2";
                $bindings[] = $userCountryId;
            }

            if (!empty($sameLanguageIds)) {
                $placeholders = implode(',', array_fill(0, count($sameLanguageIds), '?'));
                $orderParts[] = "WHEN country_id IN ({$placeholders}) THEN 3";
                $bindings = array_merge($bindings, $sameLanguageIds);
            }

            if (!empty($orderParts)) {
                $caseStatement = "CASE " . implode(' ', $orderParts) . " ELSE 4 END";
                $query->orderByRaw($caseStatement, $bindings);
            }
        }

        // Always sort by badge priority within each location group
        $query->orderByRaw(BadgeType::getLegacyOrderByClause());

        return $query;
    }

    /**
     * Transform listing data to fix JSON name fields
     */
    private function transformListing($listing)
    {
        if (!$listing) return null;

        $data = $listing->toArray();
        $locale = app()->getLocale();

        // Resolve title/description to current locale
        if (isset($data['title']) && is_array($data['title'])) {
            $data['title'] = $data['title'][$locale] ?? $data['title']['en'] ?? $data['title']['ar'] ?? '';
        }
        if (isset($data['description']) && is_array($data['description'])) {
            $data['description'] = $data['description'][$locale] ?? $data['description']['en'] ?? $data['description']['ar'] ?? '';
        }

        // Fix category name — resolve to current locale + keep all translations
        if (isset($data['category']['name'])) {
            $name = $this->decodeName($data['category']['name']);
            $data['category']['name_localized'] = $name[$locale] ?? $name['en'] ?? $name['ar'] ?? '';
            $data['category']['name'] = $name['ar'] ?? '';
            $data['category']['name_en'] = $name['en'] ?? '';
        }

        // Fix sub_category name
        if (isset($data['sub_category']['name'])) {
            $name = $this->decodeName($data['sub_category']['name']);
            $data['sub_category']['name_localized'] = $name[$locale] ?? $name['en'] ?? $name['ar'] ?? '';
            $data['sub_category']['name'] = $name['ar'] ?? '';
            $data['sub_category']['name_en'] = $name['en'] ?? '';
        }

        // Fix city name
        if (isset($data['city']['name'])) {
            $name = $this->decodeName($data['city']['name']);
            $data['city']['name_localized'] = $name[$locale] ?? $name['en'] ?? $name['ar'] ?? '';
            $data['city']['name'] = $name;
        }

        // Fix country name
        if (isset($data['country']['name'])) {
            $name = $this->decodeName($data['country']['name']);
            $data['country']['name_localized'] = $name[$locale] ?? $name['en'] ?? $name['ar'] ?? '';
            $data['country']['name'] = $name;
        }

        // Add badge info from badgeType relationship or fallback
        if ($listing->badgeType) {
            $data['badge'] = [
                'id' => $listing->badgeType->id,
                'name' => $listing->badgeType->name,
                'name_ar' => $listing->badgeType->name_ar,
                'name_en' => $listing->badgeType->name_en,
                'slug' => $listing->badgeType->slug,
                'color' => $listing->badgeType->color,
                'secondary_color' => $listing->badgeType->secondary_color,
                'icon' => $listing->badgeType->icon,
                'is_default' => $listing->badgeType->is_default,
            ];
        } else {
            // Fallback for old data using have_badge
            $haveBadge = $data['have_badge'] ?? 'عادي';
            $fallbackBadge = \App\Models\BadgeType::getByOldBadgeName($haveBadge);
            $data['badge'] = [
                'id' => $fallbackBadge?->id,
                'name' => $fallbackBadge ? $fallbackBadge->name : ['ar' => $haveBadge, 'en' => $haveBadge],
                'name_ar' => $fallbackBadge?->name_ar ?? $haveBadge,
                'name_en' => $fallbackBadge?->name_en ?? $haveBadge,
                'slug' => $fallbackBadge?->slug,
                'color' => $fallbackBadge?->color ?? '#808080',
                'secondary_color' => $fallbackBadge?->secondary_color,
                'icon' => $fallbackBadge?->icon ?? 'mdi-tag',
                'is_default' => $fallbackBadge?->is_default ?? ($haveBadge === 'عادي'),
            ];
        }

        return $data;
    }

    /**
     * Get all categories with counts
     */
    public function categories(Request $request): JsonResponse
    {
        $locale = app()->getLocale();
        $categories = Cache::remember("public_categories_v2_{$locale}", 3600, function () use ($locale) {
            // Get special counts for Near and Reels categories
            // Near: posts with valid location coordinates
            $nearCount = ServicePost::where('state', 'published')
                ->whereNotNull('location_latitudes')
                ->whereNotNull('location_longitudes')
                ->where('location_latitudes', '!=', 0)
                ->where('location_longitudes', '!=', 0)
                ->count();

            // Reels: posts with video files
            $reelsCount = ServicePost::where('state', 'published')
                ->whereHas('photos', function ($q) {
                    $q->where('isVideo', true)
                        ->orWhere('src', 'like', '%.mp4')
                        ->orWhere('src', 'like', '%.webm')
                        ->orWhere('src', 'like', '%.mov');
                })
                ->count();

            return Categories::withCount(['servicePosts' => function ($query) {
                $query->where('state', 'published')
                      ->whereHas('user', function ($q) {
                          $q->where('is_active', 'active');
                      });
            }])
                ->where('isSuspended', false)
                ->orderBy('id')
                ->get()
                ->map(function ($cat) use ($nearCount, $reelsCount, $locale) {
                    $nameData = is_array($cat->name) ? $cat->name : json_decode($cat->name, true) ?? [];
                    $nameLocalized = $nameData[$locale] ?? $nameData['en'] ?? $nameData['ar'] ?? '';
                    $nameAr = $nameData['ar'] ?? '';
                    $nameEn = $nameData['en'] ?? $nameAr;

                    $postsCount = $cat->service_posts_count;
                    if ($cat->id == self::CATEGORY_NEAR) {
                        $postsCount = $nearCount;
                    } elseif ($cat->id == self::CATEGORY_REELS) {
                        $postsCount = $reelsCount;
                    }

                    return [
                        'id' => $cat->id,
                        'name' => $nameAr,
                        'name_en' => $nameEn,
                        'name_localized' => $nameLocalized,
                        'icon' => $cat->icon ?? null,
                        'color' => $cat->color ?? null,
                        'slug' => \Str::slug($nameEn),
                        'posts_count' => $postsCount,
                    ];
                });
        });

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * Get subcategories for a category
     */
    public function subcategories(Request $request, $categoryId): JsonResponse
    {
        $locale = app()->getLocale();
        $subcategories = Cache::remember("subcategories_{$categoryId}_{$locale}", 3600, function () use ($categoryId, $locale) {
            return Sub_categories::withCount(['servicePosts' => function ($query) {
                $query->where('state', 'published')
                      ->whereHas('user', function ($q) {
                          $q->where('is_active', 'active');
                      });
            }])
                ->where('categories_id', $categoryId)
                ->where('isSuspended', false)
                ->orderBy('id')
                ->get()
                ->map(function ($sub) use ($locale) {
                    $nameData = is_array($sub->name) ? $sub->name : json_decode($sub->name, true) ?? [];
                    return [
                        'id' => $sub->id,
                        'name' => $nameData['ar'] ?? '',
                        'name_en' => $nameData['en'] ?? '',
                        'name_localized' => $nameData[$locale] ?? $nameData['en'] ?? $nameData['ar'] ?? '',
                        'category_id' => $sub->categories_id,
                        'posts_count' => $sub->service_posts_count,
                    ];
                });
        });

        return response()->json([
            'subcategories' => $subcategories,
        ]);
    }

    /**
     * Special category IDs
     */
    const CATEGORY_NEAR = 6;   // قربي - Near me (location-based)
    const CATEGORY_REELS = 7;  // فيديو - Reels (video posts only)

    /**
     * Get listings with filters
     */
    public function listings(Request $request): JsonResponse
    {
        $query = ServicePost::with(['photos', 'user.photos', 'category', 'subCategory', 'city', 'country', 'badgeType'])
            ->withCount(['favorites', 'comments'])
            ->where('state', 'published');

        $categoryId = $request->get('category_id');
        $isNearCategory = $categoryId == self::CATEGORY_NEAR;
        $isReelsCategory = $categoryId == self::CATEGORY_REELS;

        // Special handling for "Near" category (ID: 6)
        if ($isNearCategory) {
            $lat = $request->get('lat');
            $lng = $request->get('lng');
            $radius = $request->get('radius', 50); // Default 50 km radius

            if ($lat && $lng) {
                // Filter listings that have location data and are within radius
                $query->whereNotNull('location_latitudes')
                    ->whereNotNull('location_longitudes')
                    ->where('location_latitudes', '!=', 0)
                    ->where('location_longitudes', '!=', 0);

                // Calculate distance using Haversine formula and order by nearest
                $query->selectRaw("
                    *,
                    (6371 * acos(
                        cos(radians(?)) *
                        cos(radians(location_latitudes)) *
                        cos(radians(location_longitudes) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(location_latitudes))
                    )) AS distance
                ", [$lat, $lng, $lat])
                ->having('distance', '<=', $radius)
                ->orderBy('distance', 'asc');
            } else {
                // No location provided - just show listings with location data
                $query->whereNotNull('location_latitudes')
                    ->whereNotNull('location_longitudes')
                    ->where('location_latitudes', '!=', 0)
                    ->where('location_longitudes', '!=', 0);
            }
        }
        // Special handling for "Reels" category (ID: 7)
        elseif ($isReelsCategory) {
            // Only show listings that have video files
            $query->whereHas('photos', function ($q) {
                $q->where('isVideo', true)
                    ->orWhere('src', 'like', '%.mp4')
                    ->orWhere('src', 'like', '%.webm')
                    ->orWhere('src', 'like', '%.mov');
            });
        }
        // Normal category filter
        elseif ($request->filled('category_id')) {
            $query->where('categories_id', $request->category_id);
        }

        // Apply subcategory filter (not for special categories)
        if ($request->filled('subcategory_id') && !$isNearCategory && !$isReelsCategory) {
            $query->where('sub_categories_id', $request->subcategory_id);
        }

        // Apply country filter
        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        // Apply city filter
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        // Apply search filter (search across all language versions)
        if ($request->filled('search')) {
            $search = $request->search;
            $locale = app()->getLocale();
            $query->where(function ($q) use ($search, $locale) {
                // Search in current locale
                $q->whereRaw("JSON_EXTRACT(title, '$.{$locale}') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_EXTRACT(description, '$.{$locale}') LIKE ?", ["%{$search}%"])
                    // Also search in Arabic and English as fallback
                    ->orWhereRaw("JSON_EXTRACT(title, '$.ar') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_EXTRACT(title, '$.en') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_EXTRACT(description, '$.ar') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_EXTRACT(description, '$.en') LIKE ?", ["%{$search}%"]);
            });
        }

        // Apply badge filter
        if ($request->filled('badge')) {
            $query->where('have_badge', $request->badge);
        }

        // Apply price filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Apply sorting (skip for Near category which sorts by distance)
        if (!$isNearCategory || !$request->filled('lat')) {
            // Location-aware sorting first
            $this->applyLocationSort($query, $request);

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $allowedSortFields = ['created_at', 'price', 'view_count', 'favorites_count'];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }
        }

        // Pagination
        $perPage = min($request->get('per_page', 20), 50);
        $listings = $query->paginate($perPage);

        // Transform listings to fix JSON name fields
        $transformedListings = collect($listings->items())->map(function ($listing) use ($isNearCategory) {
            $data = $this->transformListing($listing);
            // Add distance info for Near category
            if ($isNearCategory && isset($listing->distance)) {
                $data['distance'] = round($listing->distance, 2);
            }
            return $data;
        });

        return response()->json([
            'listings' => $transformedListings,
            'pagination' => [
                'total' => $listings->total(),
                'per_page' => $listings->perPage(),
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
                'from' => $listings->firstItem(),
                'to' => $listings->lastItem(),
            ],
            'is_special_category' => $isNearCategory || $isReelsCategory,
            'category_type' => $isNearCategory ? 'near' : ($isReelsCategory ? 'reels' : 'normal'),
        ]);
    }

    /**
     * Get a single listing
     */
    public function listing(Request $request, $id): JsonResponse
    {
        $listing = ServicePost::with(['photos', 'user.photos', 'category', 'subCategory', 'city', 'country', 'badgeType'])
            ->withCount(['favorites', 'comments'])
            ->where('state', 'published')
            ->find($id);

        if (!$listing) {
            return response()->json(['error' => 'Listing not found'], 404);
        }

        // Get related listings
        $related = ServicePost::with(['photos', 'user.photos', 'category', 'subCategory', 'city', 'country', 'badgeType'])
            ->withCount(['favorites', 'comments'])
            ->where('state', 'published')
            ->where('id', '!=', $listing->id)
            ->where(function ($q) use ($listing) {
                $q->where('categories_id', $listing->categories_id)
                    ->orWhere('sub_categories_id', $listing->sub_categories_id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Increment view count
        $listing->increment('view_count');

        // Transform listings to fix JSON name fields
        $transformedRelated = $related->map(function ($item) {
            return $this->transformListing($item);
        });

        return response()->json([
            'listing' => $this->transformListing($listing),
            'related' => $transformedRelated,
        ]);
    }

    /**
     * Get featured listings
     */
    public function featured(Request $request): JsonResponse
    {
        $locale = app()->getLocale();
        $userCountryId = $request->query('user_country_id');

        $query = ServicePost::with(['photos', 'user.photos', 'category', 'subCategory', 'city', 'country', 'badgeType'])
            ->withCount(['favorites', 'comments'])
            ->where('state', 'published')
            ->whereIn('have_badge', ['ماسي', 'ذهبي'])
            ->whereHas('photos');

        // Apply location-aware sorting
        $this->applyLocationSort($query, $request);
        $query->orderByRaw(BadgeType::getLegacyOrderByClause());
        $query->orderBy('view_count', 'desc');

        $listings = $query->limit(12)->get();

        $featured = $listings->map(function ($listing) {
            return $this->transformListing($listing);
        });

        return response()->json([
            'featured' => $featured,
        ]);
    }

    /**
     * Get latest listings
     */
    public function latest(Request $request): JsonResponse
    {
        $categoryId = $request->get('category_id');

        $query = ServicePost::with(['photos', 'user.photos', 'category', 'subCategory', 'city', 'country', 'badgeType'])
            ->withCount(['favorites', 'comments'])
            ->where('state', 'published')
            ->whereHas('photos');

        if ($categoryId) {
            $query->where('categories_id', $categoryId);
        }

        $this->applyLocationSort($query, $request);
        $query->orderBy('created_at', 'desc');

        $listings = $query->limit(12)->get();

        $latest = $listings->map(function ($listing) {
            return $this->transformListing($listing);
        });

        return response()->json([
            'latest' => $latest,
        ]);
    }

    /**
     * Get popular listings
     */
    public function popular(Request $request): JsonResponse
    {
        $query = ServicePost::with(['photos', 'user.photos', 'category', 'subCategory', 'city', 'country', 'badgeType'])
            ->withCount(['favorites', 'comments'])
            ->where('state', 'published')
            ->whereHas('photos');

        $this->applyLocationSort($query, $request);
        $query->orderBy('view_count', 'desc');

        $listings = $query->limit(12)->get();

        $popular = $listings->map(function ($listing) {
            return $this->transformListing($listing);
        });

        return response()->json([
            'popular' => $popular,
        ]);
    }

    /**
     * Get home page stats
     */
    public function stats(): JsonResponse
    {
        $stats = Cache::remember('home_stats', 300, function () {
            return [
                'total_listings' => ServicePost::where('state', 'published')->count(),
                'total_users' => User::count(),
                'total_categories' => Categories::where('isSuspended', false)->count(),
                'listings_today' => ServicePost::where('state', 'published')
                    ->whereDate('created_at', Carbon::today())
                    ->count(),
            ];
        });

        return response()->json($stats);
    }

    /**
     * Get countries list with listings count and top cities
     */
    public function countries(): JsonResponse
    {
        $locale = app()->getLocale();
        $countries = Cache::remember("countries_list_v5_{$locale}", 3600, function () use ($locale) {
            return countries::withCount(['cities'])
                ->with(['photos', 'cities' => function ($query) {
                    $query->withCount(['servicePosts' => function ($q) {
                        $q->where('state', 'published')
                          ->whereHas('user', function ($u) {
                              $u->where('is_active', 'active');
                          });
                    }])
                    ->having('service_posts_count', '>', 0)
                    ->orderByDesc('service_posts_count')
                    ->limit(5);
                }])
                ->orderBy('id')
                ->get()
                ->map(function ($country) use ($locale) {
                    $name = $country->name;
                    $nameAr = is_array($name) ? ($name['ar'] ?? '') : (string)$name;
                    $nameEn = is_array($name) ? ($name['en'] ?? $nameAr) : (string)$name;
                    $nameLocalized = is_array($name) ? ($name[$locale] ?? $nameEn) : (string)$name;

                    // Count total listings in this country
                    $listingsCount = ServicePost::where('state', 'published')
                        ->where('country_id', $country->id)
                        ->count();

                    // Get top cities with their names (model accessor already decodes)
                    $topCities = $country->cities->map(function ($city) {
                        // The model's accessor already returns name as array
                        $cityName = $city->name;
                        $cityNameAr = is_array($cityName) ? ($cityName['ar'] ?? '') : (string)$cityName;
                        $cityNameEn = is_array($cityName) ? ($cityName['en'] ?? $cityNameAr) : (string)$cityName;

                        return [
                            'id' => $city->id,
                            'name' => $cityNameAr,
                            'name_en' => $cityNameEn,
                            'slug' => \Str::slug($cityNameEn ?: $cityNameAr) ?: rawurlencode($cityNameAr),
                            'listings_count' => $city->service_posts_count,
                        ];
                    });

                    // Flag: check column (external URL) → Photos → flagcdn fallback
                    $flagUrl = null;
                    if ($country->flag && str_starts_with($country->flag, 'http')) {
                        $flagUrl = $country->flag;
                    } elseif ($country->photos->isNotEmpty()) {
                        $src = $country->photos->first()->src;
                        $flagUrl = '/storage/' . preg_replace('#^storage/#', '', $src);
                    } elseif ($country->iso_code) {
                        $flagUrl = 'https://flagcdn.com/w80/' . strtolower($country->iso_code) . '.png';
                    }

                    return [
                        'id' => $country->id,
                        'name' => $nameAr,
                        'name_en' => $nameEn,
                        'name_localized' => $nameLocalized,
                        'iso_code' => $country->iso_code,
                        'slug' => \Str::slug($nameEn ?: $nameAr) ?: rawurlencode($nameAr),
                        'code' => $country->code ?? $country->country_code,
                        'flag' => $flagUrl,
                        'cities_count' => $country->cities_count,
                        'listings_count' => $listingsCount,
                        'top_cities' => $topCities,
                    ];
                })
                // Show all countries, sorted by listings count (countries with posts first)
                ->sortByDesc('listings_count')
                ->values();
        });

        return response()->json([
            'countries' => $countries,
        ]);
    }

    /**
     * Get cities for a country
     */
    public function cities(Request $request, $countryId): JsonResponse
    {
        $cities = Cache::remember("cities_{$countryId}", 86400, function () use ($countryId) {
            return cities::where('country_id', $countryId)
                ->orderBy('id')
                ->get()
                ->map(function ($city) {
                    // Handle name - could be array, JSON string, or plain string
                    $name = $city->name;
                    if (is_string($name)) {
                        $decoded = json_decode($name, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $name = $decoded;
                        }
                    }

                    $nameAr = is_array($name) ? ($name['ar'] ?? '') : $name;
                    $nameEn = is_array($name) ? ($name['en'] ?? $nameAr) : $name;

                    return [
                        'id' => $city->id,
                        'name' => $nameAr,
                        'name_en' => $nameEn,
                        'country_id' => $city->country_id,
                    ];
                });
        });

        return response()->json([
            'cities' => $cities,
        ]);
    }

    /**
     * Search listings
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'listings' => [],
                'pagination' => ['total' => 0],
            ]);
        }

        $listings = ServicePost::with(['photos', 'user.photos', 'category', 'subCategory', 'city', 'country', 'badgeType'])
            ->withCount(['favorites', 'comments'])
            ->where('state', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderByRaw(BadgeType::getLegacyOrderByClause())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Transform listings to fix JSON name fields
        $transformedListings = collect($listings->items())->map(function ($listing) {
            return $this->transformListing($listing);
        });

        return response()->json([
            'listings' => $transformedListings,
            'pagination' => [
                'total' => $listings->total(),
                'per_page' => $listings->perPage(),
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
            ],
            'query' => $query,
        ]);
    }

    /**
     * Get user public profile
     */
    public function userProfile(Request $request, $id): JsonResponse
    {
        $user = User::with('photos')
            ->withCount(['servicePosts' => function ($query) {
                $query->where('state', 'published')
                      ->whereHas('user', function ($q) {
                          $q->where('is_active', 'active');
                      });
            }])
            ->find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Get user's listings
        $listings = ServicePost::with(['photos', 'category', 'subCategory', 'city', 'country', 'badgeType'])
            ->withCount(['favorites', 'comments'])
            ->where('user_id', $id)
            ->where('state', 'published')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // Transform listings to fix JSON name fields
        $transformedListings = collect($listings->items())->map(function ($listing) {
            return $this->transformListing($listing);
        });

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'photo' => $user->photos->first(),
                'listings_count' => $user->service_posts_count,
                'created_at' => $user->created_at,
            ],
            'listings' => $transformedListings,
            'pagination' => [
                'total' => $listings->total(),
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
            ],
        ]);
    }

    /**
     * Get services by location (country/city/category) for SEO pages
     */
    public function services(Request $request): JsonResponse
    {
        $countryId = $request->input('country_id');
        $cityId = $request->input('city_id');
        $categoryId = $request->input('category_id');
        $page = $request->input('page', 1);
        $perPage = 12;
        $locale = app()->getLocale();

        // Build location info
        $location = [];

        if ($countryId) {
            $country = countries::find($countryId);
            if ($country) {
                $countryName = $this->decodeName($country->name);
                $location['country'] = [
                    'id' => $country->id,
                    'name' => $countryName[$locale] ?? $countryName['ar'] ?? $countryName['en'] ?? '',
                    'name_ar' => $countryName['ar'] ?? '',
                    'name_en' => $countryName['en'] ?? '',
                ];
            }
        }

        if ($cityId) {
            $city = cities::with('country')->find($cityId);
            if ($city) {
                $cityName = $this->decodeName($city->name);
                $location['city'] = [
                    'id' => $city->id,
                    'name' => $cityName[$locale] ?? $cityName['ar'] ?? $cityName['en'] ?? '',
                    'name_ar' => $cityName['ar'] ?? '',
                    'name_en' => $cityName['en'] ?? '',
                ];
            }
        }

        if ($categoryId) {
            $category = Categories::find($categoryId);
            if ($category) {
                $categoryName = $this->decodeName($category->name);
                $location['category'] = [
                    'id' => $category->id,
                    'name' => $categoryName[$locale] ?? $categoryName['ar'] ?? $categoryName['en'] ?? '',
                    'name_ar' => $categoryName['ar'] ?? '',
                    'name_en' => $categoryName['en'] ?? '',
                ];
            }
        }

        // Build listings query
        $query = ServicePost::where('state', 'published')
            ->with(['photos', 'city', 'country', 'category']);

        if ($countryId) {
            $query->where('country_id', $countryId);
        }
        if ($cityId) {
            $query->where('city_id', $cityId);
        }
        if ($categoryId) {
            $query->where('categories_id', $categoryId);
        }

        $query->orderByDesc('created_at');
        $listings = $query->paginate($perPage, ['*'], 'page', $page);

        // Transform listings
        $transformedListings = $listings->getCollection()->map(function ($listing) use ($locale) {
            $cityName = $listing->city ? $this->decodeName($listing->city->name) : null;

            $title = $listing->translate('title', $locale) ?? $listing->translate('title') ?? '';
            $desc = $listing->translate('description', $locale) ?? $listing->translate('description') ?? '';

            $firstPhoto = $listing->photos->first();
            $imageUrl = null;
            if ($firstPhoto) {
                $imageUrl = $firstPhoto->is_external
                    ? $firstPhoto->src
                    : '/' . ltrim($firstPhoto->src, '/');
            }

            return [
                'id' => $listing->id,
                'title' => $title,
                'description' => \Str::limit($desc, 100),
                'price' => $listing->price,
                'price_currency_code' => $listing->price_currency_code,
                'currency' => $listing->price_currency_code,
                'image' => $imageUrl,
                'photos' => $listing->photos->toArray(),
                'category' => $listing->category ? [
                    'id' => $listing->category->id,
                    'name' => $this->localizedNameFor($listing->category->name, 'ar'),
                    'name_en' => $this->localizedNameFor($listing->category->name, 'en'),
                    'name_localized' => $this->localizedNameFor($listing->category->name, $locale),
                ] : null,
                'city' => $listing->city ? [
                    'name' => $cityName,
                    'name_localized' => $cityName[$locale] ?? $cityName['ar'] ?? '',
                ] : null,
                'city_name' => $cityName ? ($cityName[$locale] ?? $cityName['en'] ?? $cityName['ar'] ?? '') : '',
                'have_badge' => $listing->have_badge,
                'badge' => $listing->have_badge !== 'عادي' ? $listing->have_badge : null,
                'view_count' => $listing->view_count ?? 0,
                'favorites_count' => $listing->favorites_count ?? 0,
                'created_at' => $listing->created_at->toIso8601String(),
            ];
        });

        $listings->setCollection($transformedListings);

        // Get sub-locations (cities if viewing country, categories if viewing city)
        $subLocations = [];

        if ($countryId && !$cityId) {
            // Show cities in this country with service count
            $citiesData = cities::where('country_id', $countryId)
                ->withCount(['servicePosts' => function($q) {
                    $q->where('state', 'published')
                      ->whereHas('user', function ($u) {
                          $u->where('is_active', 'active');
                      });
                }])
                ->having('service_posts_count', '>', 0)
                ->orderByDesc('service_posts_count')
                ->limit(10)
                ->get();

            foreach ($citiesData as $city) {
                $cityName = $this->decodeName($city->name);
                $subLocations[] = [
                    'id' => $city->id,
                    'name' => $cityName[$locale] ?? $cityName['en'] ?? $cityName['ar'] ?? array_values(array_filter($cityName))[0] ?? '',
                    'count' => $city->service_posts_count,
                    'icon' => 'mdi-city',
                    'color' => 'success',
                    'route' => "/services/{$countryId}/" . urlencode($location['country']['name_ar'] ?? '') . "/{$city->id}/" . urlencode($cityName['ar'] ?? ''),
                ];
            }
        } elseif ($cityId && !$categoryId) {
            // Show categories available in this city
            $categoriesData = Categories::where('isSuspended', false)
                ->withCount(['servicePosts' => function($q) use ($cityId) {
                    $q->where('state', 'published')
                      ->where('city_id', $cityId)
                      ->whereHas('user', function ($u) {
                          $u->where('is_active', 'active');
                      });
                }])
                ->having('service_posts_count', '>', 0)
                ->orderByDesc('service_posts_count')
                ->get();

            $colors = ['primary', 'success', 'warning', 'info', 'error'];
            $icons = ['mdi-car', 'mdi-home', 'mdi-briefcase', 'mdi-cellphone', 'mdi-shape'];

            foreach ($categoriesData as $index => $cat) {
                $catName = $this->decodeName($cat->name);
                $subLocations[] = [
                    'id' => $cat->id,
                    'name' => $catName[$locale] ?? $catName['ar'] ?? '',
                    'count' => $cat->service_posts_count,
                    'icon' => $icons[$index % count($icons)],
                    'color' => $colors[$index % count($colors)],
                    'route' => "/services/{$countryId}/" . urlencode($location['country']['name_ar'] ?? '') . "/{$cityId}/" . urlencode($location['city']['name_ar'] ?? '') . "/{$cat->id}/" . urlencode($catName['ar'] ?? ''),
                ];
            }
        }

        // Get stats
        $statsQuery = ServicePost::where('state', 'published');
        if ($countryId) $statsQuery->where('country_id', $countryId);
        if ($cityId) $statsQuery->where('city_id', $cityId);
        if ($categoryId) $statsQuery->where('categories_id', $categoryId);

        $stats = [
            'totalListings' => $statsQuery->count(),
            'totalCategories' => $categoryId ? 1 : Categories::where('isSuspended', false)->count(),
            'totalCities' => $cityId ? 1 : ($countryId
                ? cities::where('country_id', $countryId)->count()
                : cities::count()),
            'totalUsers' => (clone $statsQuery)->distinct('user_id')->count('user_id'),
        ];

        return response()->json([
            'location' => $location,
            'listings' => $listings,
            'subLocations' => $subLocations,
            'stats' => $stats,
        ]);
    }

    /**
     * Get available badge types for public display
     */
    public function badgeTypes(): JsonResponse
    {
        $badges = Cache::remember('public_badge_types', 3600, function () {
            return \App\Models\BadgeType::active()
                ->ordered()
                ->get()
                ->map(function ($badge) {
                    return [
                        'id' => $badge->id,
                        'name' => $badge->name,
                        'name_ar' => $badge->name_ar,
                        'name_en' => $badge->name_en,
                        'slug' => $badge->slug,
                        'color' => $badge->color,
                        'secondary_color' => $badge->secondary_color,
                        'icon' => $badge->icon,
                        'points_per_day' => $badge->points_per_day,
                        'view_boost_percent' => $badge->view_boost_percent,
                        'is_default' => $badge->is_default,
                    ];
                });
        });

        return response()->json([
            'badges' => $badges,
        ]);
    }

    /**
     * Search/paginate cities for a country (web frontend only).
     * GET /api/public/country-cities?country_id=X&search=Y&page=1&per_page=18
     */
    public function countryCities(Request $request): JsonResponse
    {
        $countryId = $request->input('country_id');
        if (!$countryId) return response()->json(['cities' => [], 'has_more' => false]);

        $search = $request->input('search', '');
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 18);
        $locale = app()->getLocale();

        $query = \App\Models\cities::where('country_id', $countryId)
            ->withCount(['servicePosts' => function($q) {
                $q->where('state', 'published');
            }])
            ->having('service_posts_count', '>', 0);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("name LIKE ?", ["%{$search}%"]);
            });
        }

        $total = $query->count();
        $cities = $query->orderByDesc('service_posts_count')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $result = $cities->map(function($city) use ($locale, $countryId) {
            $name = $city->name;
            $cityName = is_array($name) 
                ? ($name[$locale] ?? $name['en'] ?? $name['ar'] ?? array_values(array_filter($name))[0] ?? '') 
                : ($name ?? '');
            $slug = urlencode($cityName);
            return [
                'id' => $city->id,
                'name' => $cityName,
                'count' => $city->service_posts_count,
                'route' => "/services/{$countryId}/_/{$city->id}/{$slug}",
            ];
        });

        return response()->json([
            'cities' => $result,
            'has_more' => ($page * $perPage) < $total,
            'total' => $total,
            'page' => $page,
        ]);
    }

}
