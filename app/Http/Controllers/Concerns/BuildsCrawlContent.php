<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Categories;
use App\Models\ServicePost;
use App\Models\Sub_categories;
use App\Models\cities;
use App\Models\countries;
use App\Services\SlugResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Real HTML for crawlers inside the SPA mount point (2026-10-08). The site sent Google an empty <div id="app"> on every
 * page, so 99k location/category pages had no text and no links until JavaScript ran, and most sat in "Discovered -
 * currently not indexed". Each page now carries its heading, its listings as links, and links to the same country's
 * cities and to categories. A place with no listings yet is never empty: it suggests the same category in other
 * cities, other categories here, and the newest listings in the country. Vue replaces this block when it mounts.
 */
trait BuildsCrawlContent
{
    /** @return array{h1:string, intro:string, listings:array, empty:bool, sections:array}|null */
    public function getCrawlContent(string $path, string $locale): ?array
    {
        $path = '/' . ltrim(urldecode($path), '/');

        return Cache::remember('seo_crawl_v1_' . md5($path . $locale), 1800, function () use ($path, $locale) {
            try {
                return $this->buildCrawlContent($path, $locale);
            } catch (\Throwable $e) {
                \Log::warning('seo.crawl_content_failed', ['path' => $path, 'message' => $e->getMessage()]);

                return null;
            }
        });
    }

    private function buildCrawlContent(string $path, string $locale): ?array
    {
        $country = $city = $category = $sub = null;
        $post = null;

        if (preg_match('#^/services/(\d+)/[^/]+(?:/(\d+)/[^/]+)?(?:/(\d+)/[^/]+)?/?$#u', $path, $m)) {
            $country = countries::find($m[1]);
            $city = !empty($m[2]) ? cities::find($m[2]) : null;
            $category = !empty($m[3]) ? Categories::find($m[3]) : null;
        } elseif (preg_match('#^/services/([^/]+)(?:/([^/]+))?(?:/([^/]+))?(?:/([^/]+))?(?:/([^/]+-(\d+)))?/?$#u', $path, $m)) {
            if (!empty($m[6])) {
                $post = ServicePost::find((int) $m[6]);
            } else {
                $country = SlugResolver::resolveCountry($m[1]);
                $city = $country && !empty($m[2]) ? SlugResolver::resolveCity($m[2], $country->id) : null;
                $category = !empty($m[3]) ? SlugResolver::resolveCategory($m[3]) : null;
                $sub = $category && !empty($m[4]) ? SlugResolver::resolveSubcategory($m[4], $category->id) : null;
            }
        } elseif (preg_match('#^/listing/(\d+)#', $path, $m)) {
            $post = ServicePost::find((int) $m[1]);
        } elseif (preg_match('#^/category/(\d+)#', $path, $m)) {
            $category = Categories::find($m[1]);
        } elseif ($path === '/' || $path === '/browse') {
            return $this->homeCrawl($locale);
        } else {
            return null;
        }

        if ($post) {
            return $post->state === 'published' ? $this->postCrawl($post, $locale) : null;
        }
        if (!$country && !$category) {
            return null;
        }

        return $this->placeCrawl($country, $city, $category, $sub, $locale);
    }

    private function homeCrawl(string $locale): array
    {
        $sections = [];
        $byCountry = $this->countsBy('country_id', fn ($q) => $q);
        $countries = countries::whereIn('id', array_keys($byCountry))->get()->sortByDesc(fn ($c) => $byCountry[$c->id] ?? 0);
        $sections[] = $this->section($this->t('crawl_by_country', $locale, 'تصفح حسب الدولة', 'Browse by country'),
            $countries->take(30)->map(fn ($c) => $this->link($this->name($c->name, $locale), $this->placeUrl($c, null, null, $locale), $byCountry[$c->id] ?? 0))->values()->all());
        $sections[] = $this->categoriesSection(null, null, $locale);

        return [
            'h1' => $this->t('crawl_home_h1', $locale, 'طلبنا - إعلانات مبوبة في كل الدول والمدن', 'Talabna - classified ads in every country and city'),
            'intro' => '',
            'listings' => $this->listingItems($this->published()->latest('id')->limit(24)->get(), $locale),
            'empty' => false,
            'sections' => array_values(array_filter($sections)),
        ];
    }

    private function placeCrawl(?countries $country, ?cities $city, ?Categories $category, ?Sub_categories $sub, string $locale): array
    {
        $scope = function ($q) use ($country, $city, $category, $sub) {
            if ($country) $q->where('country_id', $country->id);
            if ($city) $q->where('city_id', $city->id);
            if ($category) $q->where('categories_id', $category->id);
            if ($sub) $q->where('sub_categories_id', $sub->id);

            return $q;
        };
        $listings = $scope($this->published())->latest('id')->limit(24)->get();

        $place = implode(', ', array_filter([$city ? $this->name($city->name, $locale) : null, $country ? $this->name($country->name, $locale) : null]));
        $what = $sub ? $this->name($sub->name, $locale) : ($category ? $this->name($category->name, $locale) : null);
        $h1 = $what && $place ? ($locale === 'ar' ? "{$what} في {$place}" : "{$what} - {$place}") : ($what ?: $place);

        $sections = [];
        $empty = $listings->isEmpty();
        if ($empty) {
            // Nothing here yet: point to the closest things that do exist.
            if ($category && $country) {
                $sameCat = $this->countsBy('city_id', fn ($q) => $q->where('country_id', $country->id)->where('categories_id', $category->id));
                unset($sameCat[$city?->id]);
                $sections[] = $this->citiesSection($country, $sameCat, $category, $locale,
                    $this->t('crawl_same_category_elsewhere', $locale, 'نفس التصنيف في مدن أخرى', 'The same category in other cities'));
            }
            if ($country) {
                $latest = $this->published()->where('country_id', $country->id)->latest('id')->limit(12)->get();
                if ($latest->isNotEmpty()) {
                    $sections[] = $this->section($this->t('crawl_latest_in_country', $locale, 'أحدث الإعلانات في الدولة', 'Latest listings in this country'),
                        array_map(fn ($i) => $this->link($i['title'], $i['url']), $this->listingItems($latest, $locale)));
                }
            }
            if ($country && !$latest->count()) {
                $homeLatest = $this->published()->latest('id')->limit(12)->get();
                $sections[] = $this->section($this->t('crawl_latest', $locale, 'أحدث الإعلانات', 'Latest listings'),
                    array_map(fn ($i) => $this->link($i['title'], $i['url']), $this->listingItems($homeLatest, $locale)));
            }
        }
        if ($country) {
            $sections[] = $this->categoriesSection($country, $city, $locale);
            $cityCounts = $this->countsBy('city_id', fn ($q) => $q->where('country_id', $country->id));
            unset($cityCounts[$city?->id]);
            $sections[] = $this->citiesSection($country, $cityCounts, null, $locale,
                $this->t('crawl_other_cities', $locale, 'مدن أخرى في الدولة', 'Other cities in this country'));
        } else {
            $sections[] = $this->categoriesSection(null, null, $locale);
        }

        return [
            'h1' => $h1,
            'intro' => $empty
                ? $this->t('crawl_empty', $locale, 'لا توجد إعلانات هنا حتى الآن. جرّب هذه الاقتراحات أو كن أول من ينشر.', 'No listings here yet. Try these suggestions, or be the first to post.')
                : '',
            'listings' => $this->listingItems($listings, $locale),
            'empty' => $empty,
            'sections' => array_values(array_filter($sections)),
        ];
    }

    private function postCrawl(ServicePost $post, string $locale): array
    {
        $similar = $this->published()->where('id', '!=', $post->id)
            ->where('categories_id', $post->categories_id)->where('country_id', $post->country_id)
            ->latest('id')->limit(12)->get();
        $country = $post->country_id ? countries::find($post->country_id) : null;
        $city = $post->city_id ? cities::find($post->city_id) : null;
        $category = $post->categories_id ? Categories::find($post->categories_id) : null;

        $links = array_filter([
            $country && $city && $category ? $this->link($this->name($category->name, $locale) . ' - ' . $this->name($city->name, $locale), $this->placeUrl($country, $city, $category, $locale)) : null,
            $country && $city ? $this->link($this->name($city->name, $locale), $this->placeUrl($country, $city, null, $locale)) : null,
            $country ? $this->link($this->name($country->name, $locale), $this->placeUrl($country, null, null, $locale)) : null,
        ]);

        return [
            'h1' => (string) ($post->translate('title', $locale) ?? $post->translate('title', 'ar') ?? ''),
            'intro' => mb_substr(strip_tags((string) ($post->translate('description', $locale) ?? $post->translate('description', 'ar') ?? '')), 0, 600),
            'listings' => [],
            'empty' => false,
            'sections' => array_values(array_filter([
                $this->section($this->t('crawl_browse_more', $locale, 'تصفح المزيد', 'Browse more'), array_values($links)),
                $this->section($this->t('crawl_similar', $locale, 'إعلانات مشابهة', 'Similar listings'),
                    array_map(fn ($i) => $this->link($i['title'], $i['url']), $this->listingItems($similar, $locale))),
            ])),
        ];
    }

    private function categoriesSection(?countries $country, ?cities $city, string $locale): ?array
    {
        $counts = $this->countsBy('categories_id', function ($q) use ($country, $city) {
            if ($country) $q->where('country_id', $country->id);
            if ($city) $q->where('city_id', $city->id);

            return $q;
        });
        $cats = Categories::where('isSuspended', false)->get();
        $links = [];
        foreach ($cats as $cat) {
            $url = $country ? $this->placeUrl($country, $city, $cat, $locale) : $this->categoryUrl($cat, $locale);
            $links[] = $this->link($this->name($cat->name, $locale), $url, $counts[$cat->id] ?? 0);
        }
        // With listings first, so the strongest links lead.
        usort($links, fn ($a, $b) => ($b['count'] ?? 0) <=> ($a['count'] ?? 0));

        return $this->section($this->t('crawl_categories', $locale, 'التصنيفات', 'Categories'), $links);
    }

    private function citiesSection(countries $country, array $counts, ?Categories $category, string $locale, string $title): ?array
    {
        arsort($counts);
        $ids = array_slice(array_keys($counts), 0, 30);
        $cities = cities::whereIn('id', $ids)->get()->keyBy('id');
        $links = [];
        foreach ($ids as $id) {
            if ($c = $cities->get($id)) {
                $links[] = $this->link($this->name($c->name, $locale), $this->placeUrl($country, $c, $category, $locale), $counts[$id]);
            }
        }

        return $this->section($title, $links);
    }

    /** @return array<int,int> id => published listing count */
    private function countsBy(string $column, \Closure $scope): array
    {
        return $scope($this->published())->whereNotNull($column)
            ->select($column, DB::raw('count(*) as n'))->groupBy($column)->pluck('n', $column)->map(fn ($n) => (int) $n)->all();
    }

    private function published()
    {
        return ServicePost::query()->where('state', 'published');
    }

    private function listingItems($posts, string $locale): array
    {
        return $posts->map(function (ServicePost $p) use ($locale) {
            $title = (string) ($p->translate('title', $locale) ?? $p->translate('title', 'ar') ?? '');

            return [
                'title' => $title !== '' ? $title : '#' . $p->id,
                'url' => $this->localizedUrl(url('/'), SlugResolver::buildPostUrl($p, $locale), $locale, $this->defaultLocale()),
            ];
        })->all();
    }

    /** Same URL shape as the location canonical (getServicesSeo) and the sitemap. */
    private function placeUrl(countries $country, ?cities $city, ?Categories $category, string $locale): string
    {
        $segments = [(string) $country->id, $this->slugify($this->name($country->name, $locale))];
        if ($city) {
            $segments[] = (string) $city->id;
            $segments[] = $this->slugify($this->name($city->name, $locale));
        }
        if ($category) {
            $segments[] = (string) $category->id;
            $segments[] = $this->slugify($this->name($category->name, $locale));
        }

        return $this->localizedUrl(url('/'), '/services/' . implode('/', $segments), $locale, $this->defaultLocale());
    }

    private function categoryUrl(Categories $category, string $locale): string
    {
        return $this->localizedUrl(url('/'), '/category/' . $category->id . '/' . $this->slugify($this->name($category->name, $locale)), $locale, $this->defaultLocale());
    }

    private function name($value, string $locale): string
    {
        return $this->getLocalizedName($value, $locale);
    }

    private function t(string $key, string $locale, string $ar, string $en): string
    {
        return $this->getSeoTranslation('seo.' . $key, $locale, $ar, $en);
    }

    private function link(string $label, string $url, ?int $count = null): array
    {
        return ['label' => $label, 'url' => $url, 'count' => $count];
    }

    private function section(string $title, array $links): ?array
    {
        return $links ? ['title' => $title, 'links' => $links] : null;
    }
}
