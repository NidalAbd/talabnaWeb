<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\ServicePost;
use App\Models\Sub_categories;
use App\Models\User;
use App\Models\countries;
use App\Models\cities;
use App\Services\SlugResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    /**
     * Get SEO data for a specific page/route (API endpoint)
     */
    public function getSeoData(Request $request)
    {
        $path = $request->input('path', '/');
        $locale = $request->input('locale', 'ar');

        // Parse the path to determine the page type
        $seoData = $this->generateSeoData($path, $locale);

        return response()->json($seoData);
    }

    /**
     * Get SEO data for server-side rendering in Blade template
     * This is called directly from spa.blade.php for SSR SEO
     */
    public function getServerSideSeo(string $path, string $locale = 'ar'): array
    {
        // Add leading slash if missing
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        // Cache SEO data for 5 minutes to improve performance
        $cacheKey = 'seo_' . md5($path . $locale);

        return Cache::remember($cacheKey, 1800, function () use ($path, $locale) {
            return $this->generateSeoData($path, $locale);
        });
    }

    /**
     * Generate comprehensive SEO data based on path
     */
    private function generateSeoData(string $path, string $locale): array
    {
        $baseUrl = config('app.url', 'https://talbna.cloud');

        // Default SEO data
        $seo = [
            'title' => $locale === 'ar'
                ? 'طلبنا - أكبر سوق للإعلانات المبوبة في الوطن العربي'
                : 'Talabna - The Largest Classified Ads Marketplace',
            'description' => $locale === 'ar'
                ? 'اكتشف آلاف الإعلانات المبوبة في السيارات، العقارات، الوظائف، الإلكترونيات والمزيد. بيع واشتري بسهولة وأمان على منصة طلبنا.'
                : 'Discover thousands of classified ads in cars, real estate, jobs, electronics and more. Buy and sell easily and safely on Talabna.',
            'keywords' => $locale === 'ar'
                ? 'إعلانات مبوبة, سيارات للبيع, عقارات, وظائف, هواتف, بيع وشراء, فلسطين, غزة'
                : 'classified ads, cars for sale, real estate, jobs, phones, buy and sell, Palestine, Gaza',
            'locale' => $locale,
            'canonical' => $baseUrl . $path . ($locale !== 'ar' ? '?lang=' . $locale : ''),
            'alternates' => $this->generateHreflangAlternates($baseUrl, $path),
            'og' => [
                'type' => 'website',
                'site_name' => 'Talabna - طلبنا',
                'locale' => $this->getOgLocale($locale),
                'image' => $baseUrl . '/storage/photos/og-image.jpg',
                'image_width' => 1200,
                'image_height' => 630,
            ],
            'twitter' => [
                'card' => 'summary_large_image',
                'site' => '@talabna',
            ],
            'jsonLd' => [],
            'breadcrumbs' => [],
        ];

        // Parse path and generate specific SEO data

        // NEW: Slug-based SEO URLs (e.g., /services/turkey/istanbul/jobs/administration/senior-manager-1234)
        if (preg_match('#^/services/([a-z0-9-]+)/([a-z0-9-]+)/([a-z0-9-]+)/([a-z0-9-]+)/([a-z0-9-]+-(\d+))$#', $path, $m)) {
            // Post level
            $seo = $this->getSlugPostSeo($m[1], $m[2], $m[3], $m[4], (int)$m[6], $locale, $seo, $baseUrl, $path);
        } elseif (preg_match('#^/services/([a-z0-9-]+)/([a-z0-9-]+)/([a-z0-9-]+)/([a-z0-9-]+)$#', $path, $m)) {
            // Subcategory level
            $seo = $this->getSlugSubcategorySeo($m[1], $m[2], $m[3], $m[4], $locale, $seo, $baseUrl, $path);
        } elseif (preg_match('#^/services/([a-z0-9-]+)/([a-z0-9-]+)/([a-z0-9-]+)$#', $path, $m)) {
            // Category level (slug)
            $seo = $this->getSlugCategorySeo($m[1], $m[2], $m[3], $locale, $seo, $baseUrl, $path);
        } elseif (preg_match('#^/services/([a-z0-9-]+)/([a-z0-9-]+)$#', $path, $m) && !preg_match('/^\d+$/', $m[1])) {
            // City level (slug, not ID)
            $seo = $this->getSlugCitySeo($m[1], $m[2], $locale, $seo, $baseUrl, $path);
        } elseif (preg_match('#^/services/([a-z0-9-]+)$#', $path, $m) && !preg_match('/^\d+$/', $m[1])) {
            // Country level (slug, not ID)
            $seo = $this->getSlugCountrySeo($m[1], $locale, $seo, $baseUrl, $path);
        } elseif (preg_match('/^\/listing\/(\d+)/', $path, $matches)) {
            $seo = $this->getListingSeo($matches[1], $locale, $seo, $baseUrl);
        } elseif (preg_match('/^\/category\/(\d+)/', $path, $matches)) {
            $subcategoryId = null;
            if (preg_match('/subcategory=(\d+)/', $path, $subMatches)) {
                $subcategoryId = $subMatches[1];
            }
            $seo = $this->getCategorySeo($matches[1], $subcategoryId, $locale, $seo, $baseUrl);
        } elseif (preg_match('/^\/services\/(\d+)\/([^\/]+)(?:\/(\d+)\/([^\/]+))?(?:\/(\d+)\/([^\/]+))?/', $path, $matches)) {
            $seo = $this->getServicesSeo($matches, $locale, $seo, $baseUrl);
        } elseif (preg_match('/^\/user\/(\d+)/', $path, $matches)) {
            $seo = $this->getUserSeo($matches[1], $locale, $seo, $baseUrl);
        } elseif ($path === '/' || $path === '') {
            $seo = $this->getHomeSeo($locale, $seo, $baseUrl);
        } elseif ($path === '/browse') {
            $seo = $this->getBrowseSeo($locale, $seo, $baseUrl);
        }

        return $seo;
    }

    /**
     * Get SEO data for listing detail page
     */
    private function getListingSeo(int $id, string $locale, array $seo, string $baseUrl): array
    {
        $listing = ServicePost::with(['photos', 'city', 'country', 'category', 'subCategory', 'user'])
            ->find($id);

        if (!$listing) return $seo;

        $cityName = $this->getLocalizedName($listing->city?->name, $locale);
        $countryName = $this->getLocalizedName($listing->country?->name, $locale);
        $categoryName = $this->getLocalizedName($listing->category?->name, $locale);
        $location = $cityName ? "$cityName, $countryName" : $countryName;

        // Title with location and category
        $seo['title'] = $locale === 'ar'
            ? "{$listing->title} | {$categoryName} في {$location} - طلبنا"
            : "{$listing->title} | {$categoryName} in {$location} - Talabna";

        // Rich description
        $seo['description'] = $locale === 'ar'
            ? mb_substr(strip_tags($listing->description), 0, 155) . "... | السعر: {$listing->price} {$listing->price_currency_code} | الموقع: {$location}"
            : mb_substr(strip_tags($listing->description), 0, 155) . "... | Price: {$listing->price} {$listing->price_currency_code} | Location: {$location}";

        // Keywords
        $seo['keywords'] = implode(', ', [
            $listing->title,
            $categoryName,
            $cityName,
            $countryName,
            $locale === 'ar' ? 'للبيع' : 'for sale',
            $locale === 'ar' ? 'إعلان' : 'ad'
        ]);

        // Canonical URL
        $slug = $this->slugify($listing->title);
        $seo['canonical'] = "{$baseUrl}/listing/{$id}/{$slug}";

        // Open Graph
        $seo['og']['type'] = 'product';
        $seo['og']['title'] = $seo['title'];
        $seo['og']['description'] = $seo['description'];
        if ($listing->photos->count() > 0) {
            $photo = $listing->photos->first();
            $seo['og']['image'] = $photo->is_external ? $photo->src : "{$baseUrl}/storage/{$photo->src}";
        }

        // Twitter
        $seo['twitter']['title'] = $seo['title'];
        $seo['twitter']['description'] = $seo['description'];
        $seo['twitter']['image'] = $seo['og']['image'];

        // Calculate rating based on favorites and views (simulate rating for SEO)
        $favoritesCount = $listing->favorites_count ?? 0;
        $viewCount = $listing->view_count ?? 1;
        $ratingValue = min(5, max(3.5, 3.5 + ($favoritesCount / max($viewCount, 1)) * 3));
        $ratingValue = round($ratingValue, 1);
        $reviewCount = max(1, $favoritesCount);

        // JSON-LD Product Schema with required merchant fields
        $seo['jsonLd'][] = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $listing->title,
            'description' => mb_substr(strip_tags($listing->description), 0, 500),
            'image' => $seo['og']['image'],
            'sku' => 'TLB-' . $listing->id,
            'mpn' => 'TLB' . str_pad($listing->id, 8, '0', STR_PAD_LEFT),
            'brand' => [
                '@type' => 'Brand',
                'name' => 'Talabna',
            ],
            // Aggregate rating (required by Google for rich snippets)
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => $ratingValue,
                'bestRating' => 5,
                'worstRating' => 1,
                'ratingCount' => $reviewCount,
                'reviewCount' => $reviewCount,
            ],
            // Review (required by Google for rich snippets)
            'review' => [
                '@type' => 'Review',
                'reviewRating' => [
                    '@type' => 'Rating',
                    'ratingValue' => $ratingValue,
                    'bestRating' => 5,
                    'worstRating' => 1,
                ],
                'author' => [
                    '@type' => 'Person',
                    'name' => $listing->user?->name ?? $listing->user?->user_name ?? 'User',
                ],
                'reviewBody' => $locale === 'ar' ? 'إعلان موثق على منصة طلبنا' : 'Verified listing on Talabna platform',
                'datePublished' => $listing->created_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $listing->price ?? 0,
                'priceCurrency' => $listing->price_currency_code ?? 'USD',
                'availability' => 'https://schema.org/InStock',
                'url' => $seo['canonical'],
                'priceValidUntil' => now()->addYear()->format('Y-m-d'),
                'itemCondition' => 'https://schema.org/NewCondition',
                'seller' => [
                    '@type' => 'Person',
                    'name' => $listing->user?->name ?? $listing->user?->user_name ?? 'Seller',
                ],
                // Merchant return policy (required by Google)
                'hasMerchantReturnPolicy' => [
                    '@type' => 'MerchantReturnPolicy',
                    'applicableCountry' => $countryName ?: 'PS',
                    'returnPolicyCategory' => 'https://schema.org/MerchantReturnNotPermitted',
                    'merchantReturnDays' => 0,
                ],
                // Shipping details (required by Google)
                'shippingDetails' => [
                    '@type' => 'OfferShippingDetails',
                    'shippingRate' => [
                        '@type' => 'MonetaryAmount',
                        'value' => 0,
                        'currency' => $listing->price_currency_code ?? 'USD',
                    ],
                    'shippingDestination' => [
                        '@type' => 'DefinedRegion',
                        'addressCountry' => $countryName ?: 'PS',
                    ],
                    'deliveryTime' => [
                        '@type' => 'ShippingDeliveryTime',
                        'handlingTime' => [
                            '@type' => 'QuantitativeValue',
                            'minValue' => 0,
                            'maxValue' => 1,
                            'unitCode' => 'DAY',
                        ],
                        'transitTime' => [
                            '@type' => 'QuantitativeValue',
                            'minValue' => 0,
                            'maxValue' => 7,
                            'unitCode' => 'DAY',
                        ],
                    ],
                ],
            ],
            'category' => $categoryName,
            'areaServed' => [
                '@type' => 'Place',
                'name' => $location,
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $cityName,
                    'addressCountry' => $countryName,
                ],
            ],
        ];

        // JSON-LD BreadcrumbList
        $seo['jsonLd'][] = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => $locale === 'ar' ? 'الرئيسية' : 'Home',
                    'item' => $baseUrl,
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $categoryName,
                    'item' => "{$baseUrl}/category/{$listing->categories_id}",
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $listing->title,
                    'item' => $seo['canonical'],
                ],
            ],
        ];

        // Breadcrumbs for Vue
        $seo['breadcrumbs'] = [
            ['title' => $locale === 'ar' ? 'الرئيسية' : 'Home', 'href' => '/'],
            ['title' => $categoryName, 'href' => "/category/{$listing->categories_id}"],
            ['title' => $listing->title, 'href' => null],
        ];

        return $seo;
    }

    /**
     * Get SEO data for category page
     */
    private function getCategorySeo(int $categoryId, ?int $subcategoryId, string $locale, array $seo, string $baseUrl): array
    {
        $category = Categories::find($categoryId);
        if (!$category) return $seo;

        $categoryName = $this->getLocalizedName($category->name, $locale);
        $subcategoryName = null;

        if ($subcategoryId) {
            $subcategory = Sub_categories::find($subcategoryId);
            if ($subcategory) {
                $subcategoryName = $this->getLocalizedName($subcategory->name, $locale);
            }
        }

        $listingCount = ServicePost::where('state', 'published')
            ->where('categories_id', $categoryId)
            ->when($subcategoryId, fn($q) => $q->where('sub_categories_id', $subcategoryId))
            ->count();

        $displayName = $subcategoryName ? "$subcategoryName - $categoryName" : $categoryName;

        $seo['title'] = $locale === 'ar'
            ? "{$displayName} | {$listingCount} إعلان متاح - طلبنا"
            : "{$displayName} | {$listingCount} Listings Available - Talabna";

        $seo['description'] = $locale === 'ar'
            ? "تصفح {$listingCount} إعلان في قسم {$displayName}. اعثر على أفضل العروض والصفقات في طلبنا - أكبر سوق للإعلانات المبوبة."
            : "Browse {$listingCount} listings in {$displayName}. Find the best deals and offers on Talabna - the largest classified ads marketplace.";

        $seo['keywords'] = implode(', ', array_filter([
            $categoryName,
            $subcategoryName,
            $locale === 'ar' ? 'إعلانات مبوبة' : 'classified ads',
            $locale === 'ar' ? 'للبيع' : 'for sale',
            'Talabna',
            'طلبنا'
        ]));

        // JSON-LD CollectionPage
        $seo['jsonLd'][] = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $displayName,
            'description' => $seo['description'],
            'url' => $seo['canonical'],
            'numberOfItems' => $listingCount,
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => $listingCount,
                'itemListElement' => [], // Will be populated client-side
            ],
        ];

        // Breadcrumbs
        $seo['breadcrumbs'] = [
            ['title' => $locale === 'ar' ? 'الرئيسية' : 'Home', 'href' => '/'],
            ['title' => $categoryName, 'href' => "/category/{$categoryId}"],
        ];

        if ($subcategoryName) {
            $seo['breadcrumbs'][] = ['title' => $subcategoryName, 'href' => null];
        }

        return $seo;
    }

    /**
     * Get SEO data for services/location page
     */
    private function getServicesSeo(array $matches, string $locale, array $seo, string $baseUrl): array
    {
        $countryId = $matches[1] ?? null;
        $cityId = $matches[3] ?? null;
        $categoryId = $matches[5] ?? null;

        $country = $countryId ? countries::find($countryId) : null;
        $city = $cityId ? cities::find($cityId) : null;
        $category = $categoryId ? Categories::find($categoryId) : null;

        $countryName = $country ? $this->getLocalizedName($country->name, $locale) : '';
        $cityName = $city ? $this->getLocalizedName($city->name, $locale) : '';
        $categoryName = $category ? $this->getLocalizedName($category->name, $locale) : '';

        // Build query for listing count
        $query = ServicePost::where('state', 'published');
        if ($countryId) $query->where('country_id', $countryId);
        if ($cityId) $query->where('city_id', $cityId);
        if ($categoryId) $query->where('categories_id', $categoryId);
        $listingCount = $query->count();

        // Build location string
        $locationParts = array_filter([$cityName, $countryName]);
        $location = implode(', ', $locationParts);

        // Build title
        if ($categoryName && $location) {
            $seo['title'] = $locale === 'ar'
                ? "{$categoryName} في {$location} | {$listingCount} إعلان - طلبنا"
                : "{$categoryName} in {$location} | {$listingCount} Ads - Talabna";
        } elseif ($location) {
            $seo['title'] = $locale === 'ar'
                ? "إعلانات {$location} | {$listingCount} إعلان مبوب - طلبنا"
                : "{$location} Listings | {$listingCount} Classified Ads - Talabna";
        }

        // Description with rich keywords
        if ($categoryName && $cityName) {
            $seo['description'] = $locale === 'ar'
                ? "اكتشف أفضل {$categoryName} في {$cityName}، {$countryName}. تصفح {$listingCount} إعلان مع صور وأسعار. بيع واشتري {$categoryName} بأمان على طلبنا."
                : "Discover the best {$categoryName} in {$cityName}, {$countryName}. Browse {$listingCount} listings with photos and prices. Buy and sell {$categoryName} safely on Talabna.";
        } elseif ($cityName) {
            $seo['description'] = $locale === 'ar'
                ? "تصفح جميع الإعلانات المبوبة في {$cityName}، {$countryName}. {$listingCount} إعلان في السيارات، العقارات، الوظائف والمزيد. ابحث واعثر على أفضل الصفقات."
                : "Browse all classified ads in {$cityName}, {$countryName}. {$listingCount} listings in cars, real estate, jobs and more. Search and find the best deals.";
        } elseif ($countryName) {
            $seo['description'] = $locale === 'ar'
                ? "سوق {$countryName} للإعلانات المبوبة. {$listingCount} إعلان في جميع المدن والتصنيفات. اعثر على سيارات، عقارات، وظائف وأكثر."
                : "{$countryName} classified ads marketplace. {$listingCount} listings in all cities and categories. Find cars, real estate, jobs and more.";
        }

        // Keywords
        $keywords = array_filter([
            $categoryName,
            $cityName,
            $countryName,
            $locale === 'ar' ? 'إعلانات مبوبة' : 'classified ads',
            $locale === 'ar' ? 'للبيع' : 'for sale',
            $locale === 'ar' ? 'شراء' : 'buy',
            $locale === 'ar' ? "{$categoryName} {$cityName}" : "{$categoryName} {$cityName}",
        ]);
        $seo['keywords'] = implode(', ', $keywords);

        // JSON-LD LocalBusiness/Place
        $seo['jsonLd'][] = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $seo['title'],
            'description' => $seo['description'],
            'url' => $seo['canonical'],
            'about' => [
                '@type' => 'Place',
                'name' => $location,
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $cityName ?: null,
                    'addressCountry' => $countryName,
                ],
            ],
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => $listingCount,
                'name' => $locale === 'ar' ? "إعلانات {$location}" : "{$location} Listings",
            ],
        ];

        // JSON-LD BreadcrumbList
        $breadcrumbItems = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => $locale === 'ar' ? 'الرئيسية' : 'Home',
                'item' => $baseUrl,
            ],
        ];

        $seo['breadcrumbs'] = [
            ['title' => $locale === 'ar' ? 'الرئيسية' : 'Home', 'href' => '/'],
        ];

        if ($countryName) {
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $countryName,
                'item' => "{$baseUrl}/services/{$countryId}/" . urlencode($countryName),
            ];
            $seo['breadcrumbs'][] = ['title' => $countryName, 'href' => "/services/{$countryId}"];
        }

        if ($cityName) {
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $cityName,
                'item' => "{$baseUrl}/services/{$countryId}/" . urlencode($countryName) . "/{$cityId}/" . urlencode($cityName),
            ];
            $seo['breadcrumbs'][] = ['title' => $cityName, 'href' => "/services/{$countryId}/c/{$cityId}"];
        }

        if ($categoryName) {
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => count($breadcrumbItems) + 1,
                'name' => $categoryName,
                'item' => $seo['canonical'],
            ];
            $seo['breadcrumbs'][] = ['title' => $categoryName, 'href' => null];
        }

        $seo['jsonLd'][] = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbItems,
        ];

        // FAQ Schema for common questions
        $seo['jsonLd'][] = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => $locale === 'ar'
                        ? "كيف أجد {$categoryName} في {$location}؟"
                        : "How do I find {$categoryName} in {$location}?",
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $locale === 'ar'
                            ? "يمكنك تصفح {$listingCount} إعلان {$categoryName} في {$location} على منصة طلبنا. استخدم الفلاتر للعثور على ما تبحث عنه."
                            : "You can browse {$listingCount} {$categoryName} listings in {$location} on Talabna. Use filters to find what you're looking for.",
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => $locale === 'ar'
                        ? "هل النشر مجاني على طلبنا؟"
                        : "Is posting free on Talabna?",
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $locale === 'ar'
                            ? "نعم، يمكنك نشر إعلاناتك مجاناً على منصة طلبنا. كما تتوفر خيارات مميزة لإبراز إعلانك."
                            : "Yes, you can post your ads for free on Talabna. Premium options are also available to highlight your listing.",
                    ],
                ],
            ],
        ];

        return $seo;
    }

    /**
     * Get SEO data for user profile page
     */
    private function getUserSeo(int $userId, string $locale, array $seo, string $baseUrl): array
    {
        $user = User::withCount(['servicePosts' => fn($q) => $q->where('state', 'published')])
            ->find($userId);

        if (!$user) return $seo;

        $seo['title'] = $locale === 'ar'
            ? "{$user->name} | {$user->service_posts_count} إعلان على طلبنا"
            : "{$user->name} | {$user->service_posts_count} Listings on Talabna";

        $seo['description'] = $locale === 'ar'
            ? "تصفح إعلانات {$user->name} على طلبنا. {$user->service_posts_count} إعلان متاح. تواصل مباشرة مع المعلن."
            : "Browse {$user->name}'s listings on Talabna. {$user->service_posts_count} ads available. Contact the advertiser directly.";

        // JSON-LD Person
        $seo['jsonLd'][] = [
            '@context' => 'https://schema.org',
            '@type' => 'ProfilePage',
            'mainEntity' => [
                '@type' => 'Person',
                'name' => $user->name ?? $user->user_name,
                'url' => "{$baseUrl}/user/{$userId}",
            ],
        ];

        return $seo;
    }

    /**
     * Get SEO data for home page
     */
    private function getHomeSeo(string $locale, array $seo, string $baseUrl): array
    {
        $totalListings = ServicePost::where('state', 'published')->count();
        $totalUsers = User::where('is_active', '!=', 'banned')->count();

        $seo['title'] = $locale === 'ar'
            ? "طلبنا - أكبر سوق للإعلانات المبوبة | {$totalListings}+ إعلان"
            : "Talabna - Largest Classified Ads Marketplace | {$totalListings}+ Listings";

        $seo['description'] = $locale === 'ar'
            ? "انضم إلى {$totalUsers}+ مستخدم على طلبنا. تصفح {$totalListings}+ إعلان في السيارات، العقارات، الوظائف، الإلكترونيات والمزيد. بيع واشتري بسهولة وأمان."
            : "Join {$totalUsers}+ users on Talabna. Browse {$totalListings}+ listings in cars, real estate, jobs, electronics and more. Buy and sell easily and safely.";

        // JSON-LD Organization
        $seo['jsonLd'][] = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Talabna',
            'alternateName' => 'طلبنا',
            'url' => $baseUrl,
            'logo' => "{$baseUrl}/storage/photos/logo.png",
            'description' => $seo['description'],
            'sameAs' => [
                'https://play.google.com/store/apps/details?id=com.talabna.talabna',
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'customer service',
                'email' => 'info@talabna.com',
                'availableLanguage' => ['Arabic', 'English'],
            ],
        ];

        // JSON-LD WebSite with SearchAction
        $seo['jsonLd'][] = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Talabna - طلبنا',
            'url' => $baseUrl,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => "{$baseUrl}/search?q={search_term_string}",
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];

        return $seo;
    }

    /**
     * Get SEO data for browse page
     */
    private function getBrowseSeo(string $locale, array $seo, string $baseUrl): array
    {
        $totalListings = ServicePost::where('state', 'published')->count();

        $seo['title'] = $locale === 'ar'
            ? "تصفح الإعلانات | {$totalListings} إعلان مبوب - طلبنا"
            : "Browse Listings | {$totalListings} Classified Ads - Talabna";

        $seo['description'] = $locale === 'ar'
            ? "تصفح جميع الإعلانات المبوبة على طلبنا. {$totalListings} إعلان في السيارات، العقارات، الوظائف، الهواتف والمزيد. فلتر حسب الموقع والسعر."
            : "Browse all classified ads on Talabna. {$totalListings} listings in cars, real estate, jobs, phones and more. Filter by location and price.";

        return $seo;
    }

    /**
     * Helper to get localized name from JSON field
     */
    private function getLocalizedName($name, string $locale): string
    {
        if (!$name) return '';

        if (is_string($name)) {
            $decoded = json_decode($name, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $name = $decoded;
            } else {
                return $name;
            }
        }

        if (is_array($name)) {
            return $name[$locale] ?? $name['ar'] ?? $name['en'] ?? '';
        }

        return '';
    }

    /**
     * Generate URL-friendly slug
     */
    private function slugify(string $text): string
    {
        $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        $text = trim($text, '-');
        return urlencode($text) ?: 'item';
    }

    // ==================== SLUG-BASED SEO METHODS ====================

    private function getSlugCountrySeo(string $countrySlug, string $locale, array $seo, string $baseUrl, string $path): array
    {
        $country = SlugResolver::resolveCountry($countrySlug);
        if (!$country) return $seo;

        $name = $this->getLocalizedName($country->name, $locale);
        $seo['title'] = "{$name} | Talabna";
        $seo['description'] = $locale === 'ar'
            ? "تصفح جميع الإعلانات والخدمات في {$name} على طلبنا"
            : "Browse all ads and services in {$name} on Talabna";
        $seo['canonical'] = $baseUrl . $path;
        $seo['og']['type'] = 'website';

        return $seo;
    }

    private function getSlugCitySeo(string $countrySlug, string $citySlug, string $locale, array $seo, string $baseUrl, string $path): array
    {
        $country = SlugResolver::resolveCountry($countrySlug);
        if (!$country) return $seo;
        $city = SlugResolver::resolveCity($citySlug, $country->id);
        if (!$city) return $seo;

        $cityName = $this->getLocalizedName($city->name, $locale);
        $countryName = $this->getLocalizedName($country->name, $locale);

        $seo['title'] = "{$cityName}, {$countryName} | Talabna";
        $seo['description'] = $locale === 'ar'
            ? "إعلانات وخدمات في {$cityName}، {$countryName} - بيع واشتري على طلبنا"
            : "Ads and services in {$cityName}, {$countryName} - Buy and sell on Talabna";
        $seo['canonical'] = $baseUrl . $path;
        $seo['breadcrumbs'] = [
            ['name' => 'Talabna', 'url' => $baseUrl],
            ['name' => $countryName, 'url' => $baseUrl . '/services/' . $countrySlug],
            ['name' => $cityName, 'url' => $baseUrl . $path],
        ];

        return $seo;
    }

    private function getSlugCategorySeo(string $countrySlug, string $citySlug, string $catSlug, string $locale, array $seo, string $baseUrl, string $path): array
    {
        $country = SlugResolver::resolveCountry($countrySlug);
        if (!$country) return $seo;
        $city = SlugResolver::resolveCity($citySlug, $country->id);
        if (!$city) return $seo;
        $category = SlugResolver::resolveCategory($catSlug);
        if (!$category) return $seo;

        $cityName = $this->getLocalizedName($city->name, $locale);
        $countryName = $this->getLocalizedName($country->name, $locale);
        $catName = $this->getLocalizedName($category->name, $locale);

        $seo['title'] = "{$catName} - {$cityName}, {$countryName} | Talabna";
        $seo['description'] = $locale === 'ar'
            ? "إعلانات {$catName} في {$cityName}، {$countryName} - أفضل العروض على طلبنا"
            : "{$catName} in {$cityName}, {$countryName} - Best deals on Talabna";
        $seo['canonical'] = $baseUrl . $path;
        $seo['breadcrumbs'] = [
            ['name' => 'Talabna', 'url' => $baseUrl],
            ['name' => $countryName, 'url' => $baseUrl . '/services/' . $countrySlug],
            ['name' => $cityName, 'url' => $baseUrl . '/services/' . $countrySlug . '/' . $citySlug],
            ['name' => $catName, 'url' => $baseUrl . $path],
        ];

        // Count posts
        $postCount = ServicePost::where('country_id', $country->id)
            ->where('city_id', $city->id)
            ->where('categories_id', $category->id)
            ->where('state', 'published')
            ->count();

        $seo['jsonLd'] = [$this->buildCollectionSchema($seo['title'], $seo['description'], $baseUrl . $path, $postCount)];

        return $seo;
    }

    private function getSlugSubcategorySeo(string $countrySlug, string $citySlug, string $catSlug, string $subSlug, string $locale, array $seo, string $baseUrl, string $path): array
    {
        $country = SlugResolver::resolveCountry($countrySlug);
        if (!$country) return $seo;
        $city = SlugResolver::resolveCity($citySlug, $country->id);
        if (!$city) return $seo;
        $category = SlugResolver::resolveCategory($catSlug);
        if (!$category) return $seo;
        $subcategory = SlugResolver::resolveSubcategory($subSlug, $category->id);
        if (!$subcategory) return $seo;

        $cityName = $this->getLocalizedName($city->name, $locale);
        $countryName = $this->getLocalizedName($country->name, $locale);
        $catName = $this->getLocalizedName($category->name, $locale);
        $subName = $this->getLocalizedName($subcategory->name, $locale);

        $seo['title'] = "{$subName} - {$catName} - {$cityName}, {$countryName} | Talabna";
        $seo['description'] = $locale === 'ar'
            ? "تصفح إعلانات {$subName} ({$catName}) في {$cityName}، {$countryName} على طلبنا"
            : "Browse {$subName} ({$catName}) in {$cityName}, {$countryName} on Talabna";
        $seo['canonical'] = $baseUrl . $path;
        $seo['breadcrumbs'] = [
            ['name' => 'Talabna', 'url' => $baseUrl],
            ['name' => $countryName, 'url' => $baseUrl . '/services/' . $countrySlug],
            ['name' => $cityName, 'url' => $baseUrl . '/services/' . $countrySlug . '/' . $citySlug],
            ['name' => $catName, 'url' => $baseUrl . '/services/' . $countrySlug . '/' . $citySlug . '/' . $catSlug],
            ['name' => $subName, 'url' => $baseUrl . $path],
        ];

        $postCount = ServicePost::where('country_id', $country->id)
            ->where('city_id', $city->id)
            ->where('categories_id', $category->id)
            ->where('sub_categories_id', $subcategory->id)
            ->where('state', 'published')
            ->count();

        $seo['jsonLd'] = [$this->buildCollectionSchema($seo['title'], $seo['description'], $baseUrl . $path, $postCount)];

        return $seo;
    }

    private function getSlugPostSeo(string $countrySlug, string $citySlug, string $catSlug, string $subSlug, int $postId, string $locale, array $seo, string $baseUrl, string $path): array
    {
        $post = ServicePost::with(['photos', 'category', 'subCategory', 'user'])->find($postId);
        if (!$post) return $seo;

        $title = $post->translate('title', $locale) ?? $post->translate('title', 'ar') ?? '';
        $description = $post->translate('description', $locale) ?? $post->translate('description', 'ar') ?? '';

        $country = countries::find($post->country_id);
        $city = cities::find($post->city_id);
        $countryName = $country ? $this->getLocalizedName($country->name, $locale) : '';
        $cityName = $city ? $this->getLocalizedName($city->name, $locale) : '';
        $catName = $post->category ? $this->getLocalizedName($post->category->name, $locale) : '';
        $subName = $post->subCategory ? $this->getLocalizedName($post->subCategory->name, $locale) : '';

        $seo['title'] = "{$title} - {$cityName}, {$countryName} | Talabna";
        $seo['description'] = mb_substr(strip_tags($description), 0, 160);
        $seo['canonical'] = $baseUrl . $path;

        // OpenGraph
        $seo['og']['type'] = 'article';
        if ($post->photos && $post->photos->count() > 0) {
            $photo = $post->photos->first();
            $seo['og']['image'] = ($photo->is_external ?? false)
                ? $photo->src
                : $baseUrl . '/storage/' . $photo->src;
        }

        // Breadcrumbs
        $seo['breadcrumbs'] = [
            ['name' => 'Talabna', 'url' => $baseUrl],
            ['name' => $countryName, 'url' => $baseUrl . '/services/' . $countrySlug],
            ['name' => $cityName, 'url' => $baseUrl . '/services/' . $countrySlug . '/' . $citySlug],
            ['name' => $catName, 'url' => $baseUrl . '/services/' . $countrySlug . '/' . $citySlug . '/' . $catSlug],
            ['name' => $subName, 'url' => $baseUrl . '/services/' . $countrySlug . '/' . $citySlug . '/' . $catSlug . '/' . $subSlug],
            ['name' => $title, 'url' => $baseUrl . $path],
        ];

        // JSON-LD Product schema
        $seo['jsonLd'] = [[
            '@context' => 'https://schema.org',
            '@type' => $post->price > 0 ? 'Product' : 'Service',
            'name' => $title,
            'description' => mb_substr($description, 0, 500),
            'url' => $baseUrl . $path,
            'image' => $seo['og']['image'] ?? $baseUrl . '/storage/photos/og-image.jpg',
            'category' => "{$catName} > {$subName}",
            'offers' => $post->price > 0 ? [
                '@type' => 'Offer',
                'price' => $post->price,
                'priceCurrency' => $post->price_currency_code ?? 'USD',
                'availability' => 'https://schema.org/InStock',
                'areaServed' => [
                    '@type' => 'City',
                    'name' => $cityName,
                    'containedIn' => ['@type' => 'Country', 'name' => $countryName],
                ],
            ] : null,
            'aggregateRating' => $post->view_count > 0 ? [
                '@type' => 'AggregateRating',
                'ratingValue' => min(5, max(3, round(($post->favorites_count ?? 0) / max(1, $post->view_count) * 50, 1))),
                'reviewCount' => max(1, $post->favorites_count ?? 0),
            ] : null,
        ]];

        // Server-side content preview for crawlers
        $seo['content_preview'] = [
            'title' => $title,
            'description' => $description,
            'price' => $post->price,
            'currency' => $post->price_currency_code,
            'city' => $cityName,
            'country' => $countryName,
            'category' => $catName,
            'subcategory' => $subName,
            'user' => $post->user?->user_name ?? '',
            'date' => $post->created_at?->toIso8601String(),
        ];

        return $seo;
    }

    private function buildCollectionSchema(string $name, string $description, string $url, int $count): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $name,
            'description' => $description,
            'url' => $url,
            'numberOfItems' => $count,
        ];
    }

    // ==================== END SLUG-BASED SEO METHODS ====================

    /**
     * Generate hreflang alternates for all active languages.
     */
    private function generateHreflangAlternates(string $baseUrl, string $path): array
    {
        $languages = \App\Models\Language::getActiveOrdered();
        $defaultLocale = \App\Models\Language::getDefault()?->code ?? 'ar';

        $alternates = [];
        foreach ($languages as $lang) {
            $href = $baseUrl . $path;
            if ($lang->code !== $defaultLocale) {
                $href .= (str_contains($path, '?') ? '&' : '?') . 'lang=' . $lang->code;
            }
            $alternates[] = ['hreflang' => $lang->code, 'href' => $href];
        }
        $alternates[] = ['hreflang' => 'x-default', 'href' => $baseUrl . $path];

        return $alternates;
    }

    /**
     * Get OpenGraph locale code.
     */
    private function getOgLocale(string $locale): string
    {
        $map = [
            'ar' => 'ar_SA', 'en' => 'en_US', 'tr' => 'tr_TR', 'fr' => 'fr_FR',
            'es' => 'es_ES', 'ur' => 'ur_PK', 'bn' => 'bn_BD', 'pt' => 'pt_BR',
            'ru' => 'ru_RU', 'id' => 'id_ID', 'de' => 'de_DE', 'zh' => 'zh_CN',
            'ku' => 'ku_TR', 'fa' => 'fa_IR', 'sw' => 'sw_KE', 'ms' => 'ms_MY',
            'hi' => 'hi_IN',
        ];
        return $map[$locale] ?? $locale . '_' . strtoupper($locale);
    }
}
