<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\ServicePost;
use App\Models\Sub_categories;
use App\Models\User;
use App\Models\countries;
use App\Models\cities;
use App\Services\SlugResolver;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    /** Records per page across the paginated sitemap sections.
     *
     *  Each record emits ~17 locale URLs × ~3.5 KB hreflang block ≈ 60 KB
     *  per record. We're under Google's 50 MB / 50K-URL caps in either
     *  case, but Google's "Couldn't fetch" rate climbs sharply once chunks
     *  exceed ~12 MB — even though docs say 50 MB is allowed. Listings at
     *  8-9 MB never fail; locations at 20+ MB fail on ~40% of fetches.
     *  200 records ≈ 10-11 MB which matches the listings reliability.
     */
    private const LISTINGS_PER_PAGE = 200;
    private const LOCATIONS_PER_PAGE = 200;
    private const LOC_CAT_PER_PAGE = 200;
    private const USERS_PER_PAGE = 500;

    /**
     * Generate the main sitemap index
     */
    public function index()
    {
        try {
        $content = Cache::remember('sitemap-index-v5', 3600, function () {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            $now = now()->toIso8601String();

            $xml .= '<sitemap><loc>' . url('/sitemap-pages.xml') . '</loc><lastmod>' . $now . '</lastmod></sitemap>';
            $xml .= '<sitemap><loc>' . url('/sitemap-categories.xml') . '</loc><lastmod>' . $now . '</lastmod></sitemap>';

            // Locations sitemap (paginated)
            $locationCount = count($this->locationRecords());
            $locationPages = max(1, (int) ceil($locationCount / self::LOCATIONS_PER_PAGE));
            for ($i = 1; $i <= $locationPages; $i++) {
                $xml .= '<sitemap><loc>' . url("/sitemap-locations-{$i}.xml") . '</loc><lastmod>' . $now . '</lastmod></sitemap>';
            }

            // Location-categories sitemap (paginated)
            $locCatCount = count($this->locationCategoryRecords());
            $locCatPages = max(1, (int) ceil($locCatCount / self::LOC_CAT_PER_PAGE));
            for ($i = 1; $i <= $locCatPages; $i++) {
                $xml .= '<sitemap><loc>' . url("/sitemap-location-categories-{$i}.xml") . '</loc><lastmod>' . $now . '</lastmod></sitemap>';
            }

            // Listings sitemap (paginated)
            $totalListings = ServicePost::where('state', 'published')->count();
            $listingPages = max(1, (int) ceil($totalListings / self::LISTINGS_PER_PAGE));
            for ($i = 1; $i <= $listingPages; $i++) {
                $xml .= '<sitemap><loc>' . url("/sitemap-listings-{$i}.xml") . '</loc><lastmod>' . $now . '</lastmod></sitemap>';
            }

            // Users sitemap (paginated) — only profiles with published listings.
            // Empty profiles are thin content; submitting them causes Google
            // to flag them as "Crawled - currently not indexed".
            $totalUsers = User::where('is_active', '!=', 'banned')
                ->whereHas('servicePosts', function ($q) {
                    $q->where('state', 'published');
                })
                ->count();
            $userPages = max(1, (int) ceil($totalUsers / self::USERS_PER_PAGE));
            for ($i = 1; $i <= $userPages; $i++) {
                $xml .= '<sitemap><loc>' . url("/sitemap-users-{$i}.xml") . '</loc><lastmod>' . $now . '</lastmod></sitemap>';
            }

            $xml .= '</sitemapindex>';

            return $xml;
        });

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
        } catch (\Exception $e) {
            \Log::error('Sitemap index error: ' . $e->getMessage());
            return response($this->emptyIndex(), 200)->header('Content-Type', 'application/xml');
        }
    }

    /**
     * Generate sitemap for static pages
     */
    public function pages()
    {
        $content = Cache::remember('sitemap-pages-v4', 3600, function () {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';

            $activeLanguages = \App\Models\Language::getActiveOrdered();
            $defaultLocale = \App\Models\Language::getDefault()?->code ?? 'ar';
            $allLocales = $activeLanguages->pluck('code')->all();

            $pages = [
                ['url' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
                ['url' => '/browse', 'priority' => '0.9', 'changefreq' => 'hourly'],
                ['url' => '/search', 'priority' => '0.8', 'changefreq' => 'daily'],
                ['url' => '/about', 'priority' => '0.5', 'changefreq' => 'monthly'],
                ['url' => '/contact', 'priority' => '0.5', 'changefreq' => 'monthly'],
                ['url' => '/privacy', 'priority' => '0.3', 'changefreq' => 'yearly'],
                ['url' => '/terms', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ];

            $now = now()->toIso8601String();
            foreach ($pages as $page) {
                // Static pages have no locale-specific path; same path everywhere.
                $xml .= $this->multiLocaleUrlBlock(
                    fn(string $loc) => $page['url'],
                    $allLocales, $activeLanguages, $defaultLocale,
                    $now, $page['changefreq'], $page['priority']
                );
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate sitemap for categories
     */
    public function categories()
    {
        try {
        $content = Cache::remember('sitemap-categories-v4', 3600, function () {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';

            $activeLanguages = \App\Models\Language::getActiveOrdered();
            $defaultLocale = \App\Models\Language::getDefault()?->code ?? 'ar';
            // Categories' name JSON is fully populated for all 17 active
            // locales (verified) — no per-record gating needed.
            $allLocales = $activeLanguages->pluck('code')->all();

            $categories = Categories::where('isSuspended', false)->get();
            foreach ($categories as $category) {
                $xml .= $this->multiLocaleUrlBlock(
                    fn(string $loc) => "/category/{$category->id}/" . $this->slugify($category->name, $loc),
                    $allLocales, $activeLanguages, $defaultLocale,
                    ($category->updated_at ?? now())->toIso8601String(),
                    'daily', '0.8'
                );
            }

            $subcategories = Sub_categories::where('isSuspended', false)
                ->with('category')->get();
            foreach ($subcategories as $sub) {
                if ($sub->category && !$sub->category->isSuspended) {
                    $xml .= $this->multiLocaleUrlBlock(
                        fn(string $loc) => "/category/{$sub->categories_id}/"
                            . $this->slugify($sub->category->name, $loc)
                            . "/subcategory/{$sub->id}/"
                            . $this->slugify($sub->name, $loc),
                        $allLocales, $activeLanguages, $defaultLocale,
                        ($sub->updated_at ?? now())->toIso8601String(),
                        'daily', '0.7'
                    );
                }
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
        } catch (\Exception $e) {
            \Log::error('Sitemap categories error: ' . $e->getMessage());
            return response($this->emptyUrlset(), 200)->header('Content-Type', 'application/xml');
        }
    }

    /**
     * Generate sitemap for locations (countries and cities)
     * URLs like: /services/palestine/gaza - خدمات في غزة، فلسطين
     */
    public function locations($page = 1)
    {
        $page = max(1, (int) $page);
        $cacheKey = "sitemap-locations-v6-{$page}";
        $content = Cache::remember($cacheKey, 3600, function () use ($page) {
            $records = $this->locationRecords();
            $offset = ($page - 1) * self::LOCATIONS_PER_PAGE;
            $slice = array_slice($records, $offset, self::LOCATIONS_PER_PAGE);

            $activeLanguages = \App\Models\Language::getActiveOrdered();
            $defaultLocale = \App\Models\Language::getDefault()?->code ?? 'ar';
            $allLocales = $activeLanguages->pluck('code')->all();
            $now = now()->toIso8601String();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
            foreach ($slice as $r) {
                // $r is ['type'=>'country'|'city', 'country_id'=>..., 'country_name'=>JSON,
                //        'city_id'=>?, 'city_name'=>?, 'priority'=>...]
                $xml .= $this->multiLocaleUrlBlock(
                    function (string $loc) use ($r) {
                        $countrySlug = $this->slugify($r['country_name'], $loc);
                        if ($r['type'] === 'country') {
                            return "/services/{$r['country_id']}/{$countrySlug}";
                        }
                        $citySlug = $this->slugify($r['city_name'], $loc);
                        return "/services/{$r['country_id']}/{$countrySlug}/{$r['city_id']}/{$citySlug}";
                    },
                    $allLocales, $activeLanguages, $defaultLocale,
                    $now, 'daily', $r['priority']
                );
            }
            $xml .= '</urlset>';
            return $xml;
        });
        return response($content, 200)->header('Content-Type', 'application/xml');
    }

    /**
     * Build the flat list of locale-agnostic location paths (countries + cities
     * with services). Cached separately so paginated requests don't re-run the
     * country-cities-services query graph.
     */
    private function locationRecords(): array
    {
        return Cache::remember('location-records-v2', 3600, function () {
            $records = [];
            $countries = countries::whereHas('cities', function($q) {
                $q->whereHas('servicePosts', fn($sq) => $sq->where('state', 'published'));
            })->orWhereIn('id', function($q) {
                $q->select('country_id')->from('service_posts')->where('state', 'published')->distinct();
            })->get();

            foreach ($countries as $country) {
                // Store the raw multilingual JSON name so per-locale slugs
                // can be computed at chunk-render time.
                $rawCountryName = $country->getAttributes()['name'] ?? $country->name;
                $records[] = [
                    'type' => 'country',
                    'country_id' => $country->id,
                    'country_name' => $rawCountryName,
                    'priority' => '0.8',
                ];
                $cities = cities::where('country_id', $country->id)
                    ->whereIn('id', function($q) {
                        $q->select('city_id')->from('service_posts')
                            ->where('state', 'published')->whereNotNull('city_id')->distinct();
                    })->get();
                foreach ($cities as $city) {
                    $records[] = [
                        'type' => 'city',
                        'country_id' => $country->id,
                        'country_name' => $rawCountryName,
                        'city_id' => $city->id,
                        'city_name' => $city->getAttributes()['name'] ?? $city->name,
                        'priority' => '0.7',
                    ];
                }
            }
            return $records;
        });
    }

    /**
     * Generate sitemap for location + category combinations (paginated).
     */
    public function locationCategories($page = 1)
    {
        $page = max(1, (int) $page);
        $cacheKey = "sitemap-location-categories-v5-{$page}";
        $content = Cache::remember($cacheKey, 3600, function () use ($page) {
            $records = $this->locationCategoryRecords();
            $offset = ($page - 1) * self::LOC_CAT_PER_PAGE;
            $slice = array_slice($records, $offset, self::LOC_CAT_PER_PAGE);

            $activeLanguages = \App\Models\Language::getActiveOrdered();
            $defaultLocale = \App\Models\Language::getDefault()?->code ?? 'ar';
            $allLocales = $activeLanguages->pluck('code')->all();
            $now = now()->toIso8601String();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
            foreach ($slice as $r) {
                // $r is ['country_id'=>..., 'country_name'=>JSON, 'city_id'=>...,
                //        'city_name'=>JSON, 'cat_id'=>..., 'cat_name'=>JSON]
                $xml .= $this->multiLocaleUrlBlock(
                    fn(string $loc) => "/services/{$r['country_id']}/"
                        . $this->slugify($r['country_name'], $loc)
                        . "/{$r['city_id']}/" . $this->slugify($r['city_name'], $loc)
                        . "/{$r['cat_id']}/" . $this->slugify($r['cat_name'], $loc),
                    $allLocales, $activeLanguages, $defaultLocale,
                    $now, 'daily', '0.6'
                );
            }
            $xml .= '</urlset>';
            return $xml;
        });
        return response($content, 200)->header('Content-Type', 'application/xml');
    }

    private function locationCategoryRecords(): array
    {
        return Cache::remember('location-category-records-v2', 3600, function () {
            $records = [];
            $categories = Categories::where('isSuspended', false)->get();
            $cities = cities::whereIn('id', function($q) {
                $q->select('city_id')->from('service_posts')
                    ->where('state', 'published')->whereNotNull('city_id')->distinct();
            })->with('country')->get();

            foreach ($cities as $city) {
                if (!$city->country) continue;
                $countryRaw = $city->country->getAttributes()['name'] ?? $city->country->name;
                $cityRaw = $city->getAttributes()['name'] ?? $city->name;
                foreach ($categories as $cat) {
                    $hasServices = ServicePost::where('state', 'published')
                        ->where('city_id', $city->id)
                        ->where('categories_id', $cat->id)
                        ->exists();
                    if (!$hasServices) continue;
                    $records[] = [
                        'country_id' => $city->country->id,
                        'country_name' => $countryRaw,
                        'city_id' => $city->id,
                        'city_name' => $cityRaw,
                        'cat_id' => $cat->id,
                        'cat_name' => $cat->getAttributes()['name'] ?? $cat->name,
                    ];
                }
            }
            return $records;
        });
    }

    /**
     * Generate sitemap for listings (paginated)
     */
    public function listings($page = 1)
    {
        $cacheKey = "sitemap-listings-v5-{$page}";

        $content = Cache::remember($cacheKey, 1800, function () use ($page) {
            $perPage = self::LISTINGS_PER_PAGE;
            $offset = ($page - 1) * $perPage;

            $listings = ServicePost::where('state', 'published')
                ->orderBy('id')
                ->skip($offset)
                ->take($perPage)
                ->get([
                    'id', 'title', 'description', 'updated_at',
                    'country_id', 'city_id', 'categories_id', 'sub_categories_id',
                ]);

            $activeLanguages = \App\Models\Language::getActiveOrdered();
            $defaultLocale = \App\Models\Language::getDefault()?->code ?? 'ar';

            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';

            foreach ($listings as $listing) {
                $completedLocales = $listing->getCompletedLocales();
                $xml .= $this->multiLocaleUrlBlock(
                    // Per-locale path: country/city/cat/sub/title-id all in
                    // the requested locale's slug.
                    fn(string $loc) => SlugResolver::buildPostUrl($listing, $loc),
                    $completedLocales, $activeLanguages, $defaultLocale,
                    ($listing->updated_at ?? now())->toIso8601String(),
                    'weekly', '0.6'
                );
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate sitemap for users (public profiles - paginated)
     */
    public function users($page = 1)
    {
        $cacheKey = "sitemap-users-v4-{$page}";

        $content = Cache::remember($cacheKey, 1800, function () use ($page) {
            $perPage = self::USERS_PER_PAGE;
            $offset = ($page - 1) * $perPage;

            $users = User::where('is_active', '!=', 'banned')
                ->whereHas('servicePosts', function ($q) {
                    $q->where('state', 'published');
                })
                ->orderBy('id')
                ->skip($offset)
                ->take($perPage)
                ->get(['id', 'user_name', 'updated_at']);

            $activeLanguages = \App\Models\Language::getActiveOrdered();
            $defaultLocale = \App\Models\Language::getDefault()?->code ?? 'ar';
            $allLocales = $activeLanguages->pluck('code')->all();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';

            foreach ($users as $user) {
                // Profile path is just /user/{id} — no locale-specific slug.
                $xml .= $this->multiLocaleUrlBlock(
                    fn(string $loc) => "/user/{$user->id}",
                    $allLocales, $activeLanguages, $defaultLocale,
                    ($user->updated_at ?? now())->toIso8601String(),
                    'weekly', '0.5'
                );
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate robots.txt
     */
    public function robots()
    {
        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Allow: /browse\n";
        $content .= "Allow: /category/\n";
        $content .= "Allow: /listing/\n";
        $content .= "Allow: /user/\n";
        $content .= "Allow: /services/\n";
        $content .= "Allow: /search\n";
        $content .= "Allow: /about\n";
        $content .= "Allow: /contact\n";
        $content .= "Allow: /privacy\n";
        $content .= "Allow: /terms\n";
        $content .= "\n";
        $content .= "# Disallow admin and authenticated routes\n";
        $content .= "Disallow: /dashboard\n";
        $content .= "Disallow: /admin\n";
        $content .= "Disallow: /login\n";
        $content .= "Disallow: /register\n";
        $content .= "Disallow: /password\n";
        $content .= "Disallow: /api/\n";
        $content .= "Disallow: /users\n";
        $content .= "Disallow: /categories\n";
        $content .= "Disallow: /subcategories\n";
        $content .= "Disallow: /countries\n";
        $content .= "Disallow: /cities\n";
        $content .= "Disallow: /roles\n";
        $content .= "Disallow: /permissions\n";
        $content .= "Disallow: /service_posts\n";
        $content .= "Disallow: /reports\n";
        $content .= "Disallow: /badge-types\n";
        $content .= "Disallow: /role-assignments\n";
        $content .= "Disallow: /palservice_points\n";
        $content .= "Disallow: /purchase_points\n";
        $content .= "Disallow: /point_transactions\n";
        $content .= "Disallow: /notifications\n";
        $content .= "\n";
        $content .= "# Crawl delay for politeness\n";
        $content .= "Crawl-delay: 1\n";
        $content .= "\n";
        $content .= "Sitemap: " . url('/sitemap.xml') . "\n";

        return response($content, 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Generate URL-friendly slug from multilingual text
     * Handles double-encoded JSON (e.g., '"{\"ar\":\"...\",\"en\":\"...\"}"')
     */
    private function slugify($text, $preferredLocale = 'ar')
    {
        // Handle JSON string (e.g., '{"ar":"فلسطين","en":"Palestine"}')
        // Also handles double-encoded JSON
        if (is_string($text)) {
            $decoded = json_decode($text, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // If decoded result is still a string, it was double-encoded
                if (is_string($decoded)) {
                    $decoded2 = json_decode($decoded, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded2)) {
                        $text = $decoded2;
                    }
                } elseif (is_array($decoded)) {
                    $text = $decoded;
                }
            }
        }

        // Handle array/JSON names (multilingual). Pick the requested locale
        // first, then fall through to ar → en → first available. Previously
        // this was hardcoded to special-case 'ar' and treat every other
        // locale as 'en' — that's why /hi/services/38/Pakistan/... had the
        // English country slug instead of the Hindi translation.
        if (is_array($text)) {
            $text = $text[$preferredLocale]
                ?? $text['ar']
                ?? $text['en']
                ?? (count($text) > 0 ? array_values($text)[0] : 'item');
        }

        // Ensure we have a string
        if (!is_string($text)) {
            $text = 'item';
        }

        // Trim and handle empty
        $text = trim($text);
        if (empty($text)) {
            return 'item';
        }

        // Keep letters (\p{L}), numbers (\p{N}), combining marks (\p{M} —
        // vowel signs / diacritics like Hindi matras and Arabic harakat that
        // are essential to script integrity), spaces, and hyphens.
        $text = preg_replace('/[^\p{L}\p{N}\p{M}\s-]/u', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        $text = trim($text, '-');

        // URL encode for safe transport
        return rawurlencode($text) ?: 'item';
    }

    /**
     * Build a localized URL: locale-agnostic site path prefixed with /{locale}/
     * for non-default locales, unprefixed for the default locale.
     */
    private function localizedUrl(string $path, string $locale, string $defaultLocale): string
    {
        $base = rtrim(url('/'), '/');
        $cleanPath = '/' . ltrim($path, '/');
        return $locale === $defaultLocale
            ? $base . $cleanPath
            : $base . '/' . $locale . $cleanPath;
    }

    /**
     * Emit one full <url> block per completed locale for a single piece of
     * content. Each block has its own self-canonical <loc> (the locale-
     * prefixed URL with locale-translated slug) and lists every other
     * completed locale as <xhtml:link hreflang>. This is what gets each
     * language variant indexed as a distinct page with locale-appropriate
     * keywords in the URL.
     *
     * @param callable $pathBuilder  fn(string $locale): string returning the
     *                               locale-agnostic site path for that locale
     *                               (e.g. /services/{country-slug-in-locale}/...)
     * @param array $completedLocales codes the record has translations for
     * @param iterable $activeLanguages
     * @param string $defaultLocale  unprefixed locale (e.g. 'ar')
     * @param string $lastmod  ISO-8601 timestamp
     */
    private function multiLocaleUrlBlock(
        callable $pathBuilder,
        array $completedLocales,
        $activeLanguages,
        string $defaultLocale,
        string $lastmod,
        string $changefreq,
        string $priority
    ): string {
        // Always include default + 'en' as fallbacks (matches page behaviour).
        $emitLocales = array_values(array_unique(array_merge(
            $completedLocales,
            [$defaultLocale, 'en']
        )));

        // Pre-compute every alternate URL once per record.
        $urls = [];  // code => full URL
        foreach ($activeLanguages as $lang) {
            if (!in_array($lang->code, $emitLocales, true)) continue;
            $urls[$lang->code] = $this->localizedUrl($pathBuilder($lang->code), $lang->code, $defaultLocale);
        }

        $alternates = '';
        foreach ($urls as $code => $url) {
            $alternates .= '<xhtml:link rel="alternate" hreflang="' . $code . '" href="' . htmlspecialchars($url, ENT_XML1) . '"/>';
        }
        $defaultUrl = $urls[$defaultLocale] ?? (array_values($urls)[0] ?? '');
        $alternates .= '<xhtml:link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($defaultUrl, ENT_XML1) . '"/>';

        // One <url> block per locale — each self-canonicalizes to its own URL.
        $xml = '';
        foreach ($urls as $code => $url) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($url, ENT_XML1) . '</loc>';
            $xml .= '<lastmod>' . $lastmod . '</lastmod>';
            $xml .= '<changefreq>' . $changefreq . '</changefreq>';
            $xml .= '<priority>' . $priority . '</priority>';
            $xml .= $alternates;
            $xml .= '</url>';
        }
        return $xml;
    }

    /**
     * Return empty sitemap index for error cases
     */
    private function emptyIndex(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $xml .= '</sitemapindex>';
        return $xml;
    }

    /**
     * Return empty urlset for error cases
     */
    private function emptyUrlset(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $xml .= '</urlset>';
        return $xml;
    }
}
