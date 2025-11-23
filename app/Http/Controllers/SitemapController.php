<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\ServicePost;
use App\Models\Sub_categories;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Generate the main sitemap index
     */
    public function index()
    {
        $content = Cache::remember('sitemap-index', 3600, function () {
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

            // Listings sitemap (paginated)
            $totalListings = ServicePost::where('isSuspended', false)
                ->where('status', 'active')
                ->count();
            $pages = ceil($totalListings / 1000);

            for ($i = 1; $i <= $pages; $i++) {
                $xml .= '<sitemap>';
                $xml .= '<loc>' . url("/sitemap-listings-{$i}.xml") . '</loc>';
                $xml .= '<lastmod>' . now()->toIso8601String() . '</lastmod>';
                $xml .= '</sitemap>';
            }

            $xml .= '</sitemapindex>';

            return $xml;
        });

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate sitemap for static pages
     */
    public function pages()
    {
        $content = Cache::remember('sitemap-pages', 3600, function () {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

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
        $content = Cache::remember('sitemap-categories', 3600, function () {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            // Main categories
            $categories = Categories::where('isSuspended', false)->get();

            foreach ($categories as $category) {
                $slug = $this->slugify($category->name);
                $xml .= '<url>';
                $xml .= '<loc>' . url("/category/{$category->id}/{$slug}") . '</loc>';
                $xml .= '<lastmod>' . ($category->updated_at ?? now())->toIso8601String() . '</lastmod>';
                $xml .= '<changefreq>daily</changefreq>';
                $xml .= '<priority>0.8</priority>';
                $xml .= '</url>';
            }

            // Subcategories
            $subcategories = Sub_categories::where('isSuspended', false)
                ->with('category')
                ->get();

            foreach ($subcategories as $sub) {
                if ($sub->category && !$sub->category->isSuspended) {
                    $categorySlug = $this->slugify($sub->category->name);
                    $subSlug = $this->slugify($sub->name);
                    $xml .= '<url>';
                    $xml .= '<loc>' . url("/category/{$sub->categories_id}/{$categorySlug}?subcategory={$sub->id}") . '</loc>';
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

            $listings = ServicePost::where('isSuspended', false)
                ->where('status', 'active')
                ->orderBy('id')
                ->skip($offset)
                ->take($perPage)
                ->get(['id', 'title', 'updated_at']);

            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            foreach ($listings as $listing) {
                $slug = $this->slugify($listing->title);
                $xml .= '<url>';
                $xml .= '<loc>' . url("/listing/{$listing->id}/{$slug}") . '</loc>';
                $xml .= '<lastmod>' . ($listing->updated_at ?? now())->toIso8601String() . '</lastmod>';
                $xml .= '<changefreq>weekly</changefreq>';
                $xml .= '<priority>0.6</priority>';
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
        $content .= "Disallow: /dashboard\n";
        $content .= "Disallow: /admin\n";
        $content .= "Disallow: /login\n";
        $content .= "Disallow: /register\n";
        $content .= "Disallow: /password\n";
        $content .= "Disallow: /api/\n";
        $content .= "\n";
        $content .= "Sitemap: " . url('/sitemap.xml') . "\n";

        return response($content, 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Generate URL-friendly slug
     */
    private function slugify($text)
    {
        // Convert Arabic text to a safe format
        $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        $text = trim($text, '-');
        return urlencode($text) ?: 'item';
    }
}
