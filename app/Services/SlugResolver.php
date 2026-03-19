<?php

namespace App\Services;

use App\Models\Categories;
use App\Models\cities;
use App\Models\countries;
use App\Models\ServicePost;
use App\Models\Sub_categories;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SlugResolver
{
    /**
     * Generate a URL-safe slug from a JSON name field.
     */
    public static function makeSlug($name, string $locale = 'en'): string
    {
        $text = '';
        if (is_array($name)) {
            $text = $name[$locale] ?? $name['en'] ?? array_values(array_filter($name))[0] ?? '';
        } elseif (is_string($name)) {
            $decoded = json_decode($name, true);
            if (is_array($decoded)) {
                $text = $decoded[$locale] ?? $decoded['en'] ?? array_values(array_filter($decoded))[0] ?? '';
            } else {
                $text = $name;
            }
        }

        $slug = Str::slug($text);
        return !empty($slug) ? $slug : 'item';
    }

    /**
     * Resolve a country by slug.
     */
    public static function resolveCountry(string $slug): ?countries
    {
        return Cache::remember("slug_country_{$slug}", 3600, function () use ($slug) {
            // Try exact match on English name slug
            $countries = countries::all();
            foreach ($countries as $country) {
                if (self::makeSlug($country->name) === $slug) {
                    return $country;
                }
                // Also try Arabic slug
                if (self::makeSlug($country->name, 'ar') === $slug) {
                    return $country;
                }
            }
            // Try ISO code
            $byIso = countries::where('iso_code', strtoupper($slug))->first();
            if ($byIso) return $byIso;

            return null;
        });
    }

    /**
     * Resolve a city by slug within a country.
     */
    public static function resolveCity(string $slug, int $countryId): ?cities
    {
        return Cache::remember("slug_city_{$countryId}_{$slug}", 3600, function () use ($slug, $countryId) {
            $allCities = cities::where('country_id', $countryId)->get();
            foreach ($allCities as $city) {
                if (self::makeSlug($city->name) === $slug) {
                    return $city;
                }
                if (self::makeSlug($city->name, 'ar') === $slug) {
                    return $city;
                }
            }
            return null;
        });
    }

    /**
     * Resolve a category by slug.
     */
    public static function resolveCategory(string $slug): ?Categories
    {
        return Cache::remember("slug_category_{$slug}", 3600, function () use ($slug) {
            $categories = Categories::all();
            foreach ($categories as $cat) {
                if (self::makeSlug($cat->name) === $slug) return $cat;
                if (self::makeSlug($cat->name, 'ar') === $slug) return $cat;
            }
            return null;
        });
    }

    /**
     * Resolve a subcategory by slug within a category.
     */
    public static function resolveSubcategory(string $slug, int $categoryId): ?Sub_categories
    {
        return Cache::remember("slug_subcategory_{$categoryId}_{$slug}", 3600, function () use ($slug, $categoryId) {
            $subs = Sub_categories::where('categories_id', $categoryId)->get();
            foreach ($subs as $sub) {
                if (self::makeSlug($sub->name) === $slug) return $sub;
                if (self::makeSlug($sub->name, 'ar') === $slug) return $sub;
            }
            return null;
        });
    }

    /**
     * Resolve a post by slug-id pattern (e.g., "senior-manager-1234").
     * The last number is the post ID.
     */
    public static function resolvePost(string $slug): ?ServicePost
    {
        // Extract ID from end of slug
        if (preg_match('/(\d+)$/', $slug, $matches)) {
            return ServicePost::find((int) $matches[1]);
        }
        return null;
    }

    /**
     * Build a full SEO URL for a service post.
     */
    public static function buildPostUrl(ServicePost $post, string $locale = 'en'): string
    {
        $parts = ['/services'];

        // Country
        if ($post->country_id) {
            $country = countries::find($post->country_id);
            if ($country) $parts[] = self::makeSlug($country->name, $locale);
        }

        // City
        if ($post->city_id) {
            $city = cities::find($post->city_id);
            if ($city) $parts[] = self::makeSlug($city->name, $locale);
        }

        // Category
        if ($post->categories_id) {
            $cat = Categories::find($post->categories_id);
            if ($cat) $parts[] = self::makeSlug($cat->name, $locale);
        }

        // Subcategory
        if ($post->sub_categories_id) {
            $sub = Sub_categories::find($post->sub_categories_id);
            if ($sub) $parts[] = self::makeSlug($sub->name, $locale);
        }

        // Post slug + ID
        $titleSlug = self::makeSlug($post->title, $locale);
        $parts[] = $titleSlug . '-' . $post->id;

        return implode('/', $parts);
    }
}
