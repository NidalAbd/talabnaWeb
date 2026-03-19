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
    /**
     * Generate the main sitemap index
     */
    public function index()
    {
        try {
        $content = Cache::remember('sitemap-index-v2', 3600, function () {
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
            $listingPages = ceil($totalListings / 1000);

            for ($i = 1; $i <= max(1, $listingPages); $i++) {
                $xml .= '<sitemap>';
                $xml .= '<loc>' . url("/sitemap-listings-{$i}.xml") . '</loc>';
                $xml .= '<lastmod>' . now()->toIso8601String() . '</lastmod>';
                $xml .= '</sitemap>';
            }

            // Users sitemap (paginated) - public profiles
            $totalUsers = User::where('is_active', '!=', 'banned')->count();
            $userPages = ceil($totalUsers / 1000);

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
        $content = Cache::remember('sitemap-pages', 3600, function () {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';

            $pages = [
                ['url' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
                ['url' => '/browse', 'priority' => '0.9', 'changefreq' => 'hourly'],
                ['url' => '/search', 'priority' => '0.8', 'changefreq' => 'daily'],
                ['url' => '/about', 'priority' => '0.5', 'changefreq' => 'monthly'],
                ['url' => '/contact', 'priority' => '0.5', 'changefreq' => 'monthly'],
                ['url' => '/privacy', 'priority' => '0.3', 'changefreq' => 'yearly'],
                ['url' => '/terms', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ];

            foreach ($pages as $page) {
                $xml .= '<url>';
                $xml .= '<loc>' . url($page['url']) . '</loc>';
                $xml .= '<lastmod>' . now()->toIso8601String() . '</lastmod>';
                $xml .= '<changefreq>' . $page['changefreq'] . '</changefreq>';
                $xml .= '<priority>' . $page['priority'] . '</priority>';
                $xml .= '</url>';
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
        $content = Cache::remember('sitemap-categories-v2', 3600, function () {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            // Main categories
            $categories = Categories::where('isSuspended', false)->get();

            foreach ($categories as $category) {
                $slugAr = $this->slugify($category->name, 'ar');
                $slugEn = $this->slugify($category->name, 'en');

                // Arabic URL
                $xml .= '<url>';
                $xml .= '<loc>' . url("/category/{$category->id}/{$slugAr}") . '</loc>';
                $xml .= '<lastmod>' . ($category->updated_at ?? now())->toIso8601String() . '</lastmod>';
                $xml .= '<changefreq>daily</changefreq>';
                $xml .= '<priority>0.8</priority>';
                $xml .= '</url>';
            }

            // Subcategories - SEO friendly URLs: /category/{id}/{slug}/subcategory/{subId}/{subSlug}
            $subcategories = Sub_categories::where('isSuspended', false)
                ->with('category')
                ->get();

            foreach ($subcategories as $sub) {
                if ($sub->category && !$sub->category->isSuspended) {
                    $categorySlug = $this->slugify($sub->category->name, 'ar');
                    $subcategorySlug = $this->slugify($sub->name, 'ar');

                    // SEO-friendly path-based URL
                    $xml .= '<url>';
                    $xml .= '<loc>' . url("/category/{$sub->categories_id}/{$categorySlug}/subcategory/{$sub->id}/{$subcategorySlug}") . '</loc>';
                    $xml .= '<lastmod>' . ($sub->updated_at ?? now())->toIso8601String() . '</lastmod>';
                    $xml .= '<changefreq>daily</changefreq>';
                    $xml .= '<priority>0.7</priority>';
                    $xml .= '</url>';
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
        $content = Cache::remember('sitemap-locations', 3600, function () {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            // Get countries that have services
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

            foreach ($countriesWithServices as $country) {
                $countrySlugAr = $this->slugify($country->name, 'ar');
                $countrySlugEn = $this->slugify($country->name, 'en');

                // Country page - Arabic
                $xml .= '<url>';
                $xml .= '<loc>' . url("/services/{$country->id}/{$countrySlugAr}") . '</loc>';
                $xml .= '<lastmod>' . now()->toIso8601String() . '</lastmod>';
                $xml .= '<changefreq>daily</changefreq>';
                $xml .= '<priority>0.8</priority>';
                $xml .= '</url>';

                // Country page - English
                $xml .= '<url>';
                $xml .= '<loc>' . url("/services/{$country->id}/{$countrySlugEn}") . '</loc>';
                $xml .= '<lastmod>' . now()->toIso8601String() . '</lastmod>';
                $xml .= '<changefreq>daily</changefreq>';
                $xml .= '<priority>0.8</priority>';
                $xml .= '</url>';

                // Cities in this country
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
                    $citySlugEn = $this->slugify($city->name, 'en');

                    // City page - Arabic
                    // URL: /services/country-id/country-slug/city-id/city-slug
                    $xml .= '<url>';
                    $xml .= '<loc>' . url("/services/{$country->id}/{$countrySlugAr}/{$city->id}/{$citySlugAr}") . '</loc>';
                    $xml .= '<lastmod>' . now()->toIso8601String() . '</lastmod>';
                    $xml .= '<changefreq>daily</changefreq>';
                    $xml .= '<priority>0.7</priority>';
                    $xml .= '</url>';

                    // City page - English
                    $xml .= '<url>';
                    $xml .= '<loc>' . url("/services/{$country->id}/{$countrySlugEn}/{$city->id}/{$citySlugEn}") . '</loc>';
                    $xml .= '<lastmod>' . now()->toIso8601String() . '</lastmod>';
                    $xml .= '<changefreq>daily</changefreq>';
                    $xml .= '<priority>0.7</priority>';
                    $xml .= '</url>';
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
        $content = Cache::remember('sitemap-location-categories', 3600, function () {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            $categories = Categories::where('isSuspended', false)->get();

            // Get cities that have services
            $citiesWithServices = cities::whereIn('id', function($query) {
                $query->select('city_id')
                    ->from('service_posts')
                    ->where('state', 'published')
                    ->whereNotNull('city_id')
                    ->distinct();
            })->with('country')->get();

            foreach ($citiesWithServices as $city) {
                if (!$city->country) continue;

                $countrySlugAr = $this->slugify($city->country->name, 'ar');
                $citySlugAr = $this->slugify($city->name, 'ar');

                foreach ($categories as $category) {
                    // Check if this city has services in this category
                    $hasServices = ServicePost::where('state', 'published')
                        ->where('city_id', $city->id)
                        ->where('categories_id', $category->id)
                        ->exists();

                    if ($hasServices) {
                        $categorySlugAr = $this->slugify($category->name, 'ar');
                        $categorySlugEn = $this->slugify($category->name, 'en');

                        // Arabic: /services/country/city/category
                        $xml .= '<url>';
                        $xml .= '<loc>' . url("/services/{$city->country->id}/{$countrySlugAr}/{$city->id}/{$citySlugAr}/{$category->id}/{$categorySlugAr}") . '</loc>';
                        $xml .= '<lastmod>' . now()->toIso8601String() . '</lastmod>';
                        $xml .= '<changefreq>daily</changefreq>';
                        $xml .= '<priority>0.6</priority>';
                        $xml .= '</url>';
                    }
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
        $cacheKey = "sitemap-listings-{$page}";

        $content = Cache::remember($cacheKey, 1800, function () use ($page) {
            $perPage = 1000;
            $offset = ($page - 1) * $perPage;

            $listings = ServicePost::where('state', 'published')
                ->orderBy('id')
                ->skip($offset)
                ->take($perPage)
                ->get(['id', 'title', 'updated_at']);

            $activeLanguages = \App\Models\Language::getActiveOrdered();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';

            foreach ($listings as $listing) {
                // Build SEO-friendly slug URL
                $seoUrl = SlugResolver::buildPostUrl($listing, 'en');
                $baseUrl = url($seoUrl);

                $xml .= '<url>';
                $xml .= '<loc>' . $baseUrl . '</loc>';
                $xml .= '<lastmod>' . ($listing->updated_at ?? now())->toIso8601String() . '</lastmod>';
                $xml .= '<changefreq>weekly</changefreq>';
                $xml .= '<priority>0.6</priority>';
                // Add hreflang alternates for each active language
                foreach ($activeLanguages as $lang) {
                    $langUrl = $baseUrl . '?lang=' . $lang->code;
                    $xml .= '<xhtml:link rel="alternate" hreflang="' . $lang->code . '" href="' . $langUrl . '"/>';
                }
                $xml .= '<xhtml:link rel="alternate" hreflang="x-default" href="' . $baseUrl . '"/>';
                $xml .= '</url>';
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
        $cacheKey = "sitemap-users-{$page}";

        $content = Cache::remember($cacheKey, 1800, function () use ($page) {
            $perPage = 1000;
            $offset = ($page - 1) * $perPage;

            $users = User::where('is_active', '!=', 'banned')
                ->orderBy('id')
                ->skip($offset)
                ->take($perPage)
                ->get(['id', 'user_name', 'updated_at']);

            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            foreach ($users as $user) {
                $xml .= '<url>';
                $xml .= '<loc>' . url("/user/{$user->id}") . '</loc>';
                $xml .= '<lastmod>' . ($user->updated_at ?? now())->toIso8601String() . '</lastmod>';
                $xml .= '<changefreq>weekly</changefreq>';
                $xml .= '<priority>0.5</priority>';
                $xml .= '</url>';
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
