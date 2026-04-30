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
    /** Records per page in the listings sitemap. Each record emits up to
     *  ~17 locale URLs each with hreflang for ~17 alternates, so per-record
     *  XML is ~6 KB. 200 records/page = ~24 MB chunk, well under Google's
     *  50 MB / 50K-URL limits. */
    private const LISTINGS_PER_PAGE = 200;
    /** Same idea for users — 200 records × 1 URL each = small, but keeps
     *  pagination consistent. */
    private const USERS_PER_PAGE = 1000;

    /**
     * Generate the main sitemap index
     */
    public function index()
    {
        try {
        $content = Cache::remember('sitemap-index-v3', 3600, function () {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            // Main pages sitemap
            $xml .= '<sitemap>';
            $xml .= '<loc>' . url('/sitemap-pages.xml') . '</loc>';
            $xml .= '<lastmod>' . now()->toIso8601String() . '</lastmod>';
            $xml .= '</sitemap>';

            // Categories sitemap
            $xml .= '<sitemap>';
            $xml .= '<loc>' . url('/sitemap-categories.xml') . '</loc>';
            $xml .= '<lastmod>' . now()->toIso8601String() . '</lastmod>';
            $xml .= '</sitemap>';

            // Locations sitemap (countries, cities, services by location)
            $xml .= '<sitemap>';
            $xml .= '<loc>' . url('/sitemap-locations.xml') . '</loc>';
            $xml .= '<lastmod>' . now()->toIso8601String() . '</lastmod>';
            $xml .= '</sitemap>';

            // Location + Category combinations sitemap
            $xml .= '<sitemap>';
            $xml .= '<loc>' . url('/sitemap-location-categories.xml') . '</loc>';
            $xml .= '<lastmod>' . now()->toIso8601String() . '</lastmod>';
            $xml .= '</sitemap>';

            // Listings sitemap (paginated)
            $totalListings = ServicePost::where('state', 'published')->count();
            $listingPages = ceil($totalListings / self::LISTINGS_PER_PAGE);

            for ($i = 1; $i <= max(1, $listingPages); $i++) {
                $xml .= '<sitemap>';
                $xml .= '<loc>' . url("/sitemap-listings-{$i}.xml") . '</loc>';
                $xml .= '<lastmod>' . now()->toIso8601String() . '</lastmod>';
                $xml .= '</sitemap>';
            }

            // Users sitemap (paginated) — only profiles with published listings.
            // Empty profiles are thin content; submitting them causes Google
            // to flag them as "Crawled - currently not indexed".
            $totalUsers = User::where('is_active', '!=', 'banned')
                ->whereHas('servicePosts', function ($q) {
                    $q->where('state', 'published');
                })
                ->count();
            $userPages = ceil($totalUsers / self::USERS_PER_PAGE);

            for ($i = 1; $i <= max(1, $userPages); $i++) {
                $xml .= '<sitemap>';
                $xml .= '<loc>' . url("/sitemap-users-{$i}.xml") . '</loc>';
                $xml .= '<lastmod>' . now()->toIso8601String() . '</lastmod>';
                $xml .= '</sitemap>';
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
                $xml .= $this->multiLocaleUrlBlock(
                    $page['url'], $allLocales, $activeLanguages, $defaultLocale,
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
                $slugAr = $this->slugify($category->name, 'ar');
                $path = "/category/{$category->id}/{$slugAr}";
                $xml .= $this->multiLocaleUrlBlock(
                    $path, $allLocales, $activeLanguages, $defaultLocale,
                    ($category->updated_at ?? now())->toIso8601String(),
                    'daily', '0.8'
                );
            }

            $subcategories = Sub_categories::where('isSuspended', false)
                ->with('category')->get();
            foreach ($subcategories as $sub) {
                if ($sub->category && !$sub->category->isSuspended) {
                    $categorySlug = $this->slugify($sub->category->name, 'ar');
                    $subcategorySlug = $this->slugify($sub->name, 'ar');
                    $path = "/category/{$sub->categories_id}/{$categorySlug}/subcategory/{$sub->id}/{$subcategorySlug}";
                    $xml .= $this->multiLocaleUrlBlock(
                        $path, $allLocales, $activeLanguages, $defaultLocale,
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
    public function locations()
    {
        $content = Cache::remember('sitemap-locations-v4', 3600, function () {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';

            $activeLanguages = \App\Models\Language::getActiveOrdered();
            $defaultLocale = \App\Models\Language::getDefault()?->code ?? 'ar';
            $allLocales = $activeLanguages->pluck('code')->all();

            $countriesWithServices = countries::whereHas('cities', function($q) {
                $q->whereHas('servicePosts', function($sq) {
                    $sq->where('state', 'published');
                });
            })->orWhereIn('id', function($query) {
                $query->select('country_id')
                    ->from('service_posts')
                    ->where('state', 'published')
                    ->distinct();
            })->get();

            $now = now()->toIso8601String();
            foreach ($countriesWithServices as $country) {
                $countrySlugAr = $this->slugify($country->name, 'ar');
                $xml .= $this->multiLocaleUrlBlock(
                    "/services/{$country->id}/{$countrySlugAr}",
                    $allLocales, $activeLanguages, $defaultLocale,
                    $now, 'daily', '0.8'
                );

                $citiesWithServices = cities::where('country_id', $country->id)
                    ->whereIn('id', function($query) {
                        $query->select('city_id')
                            ->from('service_posts')
                            ->where('state', 'published')
                            ->whereNotNull('city_id')
                            ->distinct();
                    })->get();

                foreach ($citiesWithServices as $city) {
                    $citySlugAr = $this->slugify($city->name, 'ar');
                    $xml .= $this->multiLocaleUrlBlock(
                        "/services/{$country->id}/{$countrySlugAr}/{$city->id}/{$citySlugAr}",
                        $allLocales, $activeLanguages, $defaultLocale,
                        $now, 'daily', '0.7'
                    );
                }
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate sitemap for location + category combinations
     * URLs like: /services/palestine/gaza/cars - سيارات للبيع في غزة
     */
    public function locationCategories()
    {
        $content = Cache::remember('sitemap-location-categories-v3', 3600, function () {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';

            $activeLanguages = \App\Models\Language::getActiveOrdered();
            $defaultLocale = \App\Models\Language::getDefault()?->code ?? 'ar';
            $allLocales = $activeLanguages->pluck('code')->all();

            $categories = Categories::where('isSuspended', false)->get();
            $citiesWithServices = cities::whereIn('id', function($query) {
                $query->select('city_id')
                    ->from('service_posts')
                    ->where('state', 'published')
                    ->whereNotNull('city_id')
                    ->distinct();
            })->with('country')->get();

            $now = now()->toIso8601String();
            foreach ($citiesWithServices as $city) {
                if (!$city->country) continue;
                $countrySlugAr = $this->slugify($city->country->name, 'ar');
                $citySlugAr = $this->slugify($city->name, 'ar');

                foreach ($categories as $category) {
                    $hasServices = ServicePost::where('state', 'published')
                        ->where('city_id', $city->id)
                        ->where('categories_id', $category->id)
                        ->exists();
                    if (!$hasServices) continue;

                    $categorySlugAr = $this->slugify($category->name, 'ar');
                    $xml .= $this->multiLocaleUrlBlock(
                        "/services/{$city->country->id}/{$countrySlugAr}/{$city->id}/{$citySlugAr}/{$category->id}/{$categorySlugAr}",
                        $allLocales, $activeLanguages, $defaultLocale,
                        $now, 'daily', '0.6'
                    );
                }
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate sitemap for listings (paginated)
     */
    public function listings($page = 1)
    {
        $cacheKey = "sitemap-listings-v4-{$page}";

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
                // Locale-agnostic path; multiLocaleUrlBlock prefixes per locale.
                $path = SlugResolver::buildPostUrl($listing, 'en');

                // Per-listing translation gate. multiLocaleUrlBlock auto-adds
                // default + 'en' fallbacks even if missing.
                $completedLocales = $listing->getCompletedLocales();

                $xml .= $this->multiLocaleUrlBlock(
                    $path, $completedLocales, $activeLanguages, $defaultLocale,
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
        $cacheKey = "sitemap-users-v2-{$page}";

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
                // Profile UI is fully translated via the i18n bundle, so all
                // active locales are valid variants.
                $xml .= $this->multiLocaleUrlBlock(
                    "/user/{$user->id}", $allLocales, $activeLanguages, $defaultLocale,
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

        // Handle array/JSON names (multilingual)
        if (is_array($text)) {
            // Get preferred locale, fallback to other locale, then first available
            if ($preferredLocale === 'ar') {
                $text = $text['ar'] ?? $text['en'] ?? (count($text) > 0 ? array_values($text)[0] : 'item');
            } else {
                $text = $text['en'] ?? $text['ar'] ?? (count($text) > 0 ? array_values($text)[0] : 'item');
            }
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

        // Convert text to a safe format - keep only letters, numbers, spaces and hyphens
        $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
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
     * content. Each block has its own self-canonical <loc> (the locale-prefixed
     * URL) and lists every other completed locale as <xhtml:link hreflang>.
     * This is what gets each language variant indexed as a distinct page.
     *
     * @param string $path  locale-agnostic site path (e.g. /listing/123)
     * @param array  $completedLocales codes the record has translations for
     * @param iterable $activeLanguages full active-language list (for ordering)
     * @param string $defaultLocale  unprefixed locale (e.g. 'ar')
     * @param string $lastmod  ISO-8601 timestamp
     * @param string $changefreq
     * @param string $priority
     */
    private function multiLocaleUrlBlock(
        string $path,
        array $completedLocales,
        $activeLanguages,
        string $defaultLocale,
        string $lastmod,
        string $changefreq,
        string $priority
    ): string {
        // Always include the default locale and 'en' as common fallbacks even
        // if the record lacks them — matches the visible page behaviour.
        $emitLocales = array_values(array_unique(array_merge(
            $completedLocales,
            [$defaultLocale, 'en']
        )));

        // Pre-compute every alternate URL once per record.
        $alternates = '';
        foreach ($activeLanguages as $lang) {
            if (!in_array($lang->code, $emitLocales, true)) continue;
            $alt = $this->localizedUrl($path, $lang->code, $defaultLocale);
            $alternates .= '<xhtml:link rel="alternate" hreflang="' . $lang->code . '" href="' . htmlspecialchars($alt, ENT_XML1) . '"/>';
        }
        $defaultUrl = $this->localizedUrl($path, $defaultLocale, $defaultLocale);
        $alternates .= '<xhtml:link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($defaultUrl, ENT_XML1) . '"/>';

        // One <url> block per completed locale — each self-canonicalizes.
        $xml = '';
        foreach ($activeLanguages as $lang) {
            if (!in_array($lang->code, $emitLocales, true)) continue;
            $loc = $this->localizedUrl($path, $lang->code, $defaultLocale);
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>';
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
