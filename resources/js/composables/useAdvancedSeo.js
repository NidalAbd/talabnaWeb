import { ref, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAppStore } from '@/stores/app'

/**
 * Advanced SEO composable for dynamic meta tags, JSON-LD, and structured data
 */
export function useAdvancedSeo() {
    const route = useRoute()
    const appStore = useAppStore()

    const seoData = ref(null)
    const loading = ref(false)

    /**
     * Fetch SEO data from API
     */
    async function fetchSeoData(path = null) {
        loading.value = true
        try {
            const currentPath = path || route.fullPath
            const locale = appStore.locale || 'ar'

            const response = await fetch(`/api/public/seo?path=${encodeURIComponent(currentPath)}&locale=${locale}`)
            if (response.ok) {
                seoData.value = await response.json()
                applySeoData(seoData.value)
            }
        } catch (error) {
            console.error('Error fetching SEO data:', error)
        } finally {
            loading.value = false
        }
    }

    /**
     * Apply SEO data to the document
     */
    function applySeoData(data) {
        if (!data) return

        // Update document title
        if (data.title) {
            document.title = data.title
        }

        // Update or create meta tags
        updateMetaTag('description', data.description)
        updateMetaTag('keywords', data.keywords)

        // Canonical URL
        updateLinkTag('canonical', data.canonical)

        // Alternate languages (hreflang)
        removeExistingHreflang()
        if (data.alternates) {
            data.alternates.forEach(alt => {
                createHreflangLink(alt.hreflang, alt.href)
            })
        }

        // Open Graph meta tags
        if (data.og) {
            updateMetaTag('og:type', data.og.type, 'property')
            updateMetaTag('og:title', data.title, 'property')
            updateMetaTag('og:description', data.description, 'property')
            updateMetaTag('og:url', data.canonical, 'property')
            updateMetaTag('og:site_name', data.og.site_name, 'property')
            updateMetaTag('og:locale', data.og.locale, 'property')
            updateMetaTag('og:image', data.og.image, 'property')
            updateMetaTag('og:image:width', data.og.image_width, 'property')
            updateMetaTag('og:image:height', data.og.image_height, 'property')
        }

        // Twitter Card meta tags
        if (data.twitter) {
            updateMetaTag('twitter:card', data.twitter.card, 'name')
            updateMetaTag('twitter:site', data.twitter.site, 'name')
            updateMetaTag('twitter:title', data.twitter.title || data.title, 'name')
            updateMetaTag('twitter:description', data.twitter.description || data.description, 'name')
            updateMetaTag('twitter:image', data.twitter.image || data.og?.image, 'name')
        }

        // JSON-LD structured data
        if (data.jsonLd && data.jsonLd.length > 0) {
            removeExistingJsonLd()
            data.jsonLd.forEach(schema => {
                createJsonLdScript(schema)
            })
        }
    }

    /**
     * Update or create a meta tag
     */
    function updateMetaTag(name, content, attribute = 'name') {
        if (!content) return

        let meta = document.querySelector(`meta[${attribute}="${name}"]`)
        if (!meta) {
            meta = document.createElement('meta')
            meta.setAttribute(attribute, name)
            document.head.appendChild(meta)
        }
        meta.setAttribute('content', content)
    }

    /**
     * Update or create a link tag
     */
    function updateLinkTag(rel, href) {
        if (!href) return

        let link = document.querySelector(`link[rel="${rel}"]`)
        if (!link) {
            link = document.createElement('link')
            link.setAttribute('rel', rel)
            document.head.appendChild(link)
        }
        link.setAttribute('href', href)
    }

    /**
     * Create hreflang link tag
     */
    function createHreflangLink(hreflang, href) {
        const link = document.createElement('link')
        link.setAttribute('rel', 'alternate')
        link.setAttribute('hreflang', hreflang)
        link.setAttribute('href', href)
        link.setAttribute('data-seo-hreflang', 'true')
        document.head.appendChild(link)
    }

    /**
     * Remove existing hreflang links
     */
    function removeExistingHreflang() {
        document.querySelectorAll('link[data-seo-hreflang]').forEach(el => el.remove())
    }

    /**
     * Create JSON-LD script tag
     */
    function createJsonLdScript(schema) {
        const script = document.createElement('script')
        script.type = 'application/ld+json'
        script.setAttribute('data-seo-jsonld', 'true')
        script.textContent = JSON.stringify(schema)
        document.head.appendChild(script)
    }

    /**
     * Remove existing JSON-LD scripts
     */
    function removeExistingJsonLd() {
        document.querySelectorAll('script[data-seo-jsonld]').forEach(el => el.remove())
    }

    /**
     * Set custom SEO data manually (for pages that don't use API)
     */
    function setSeo(customData) {
        const defaultData = {
            title: 'طلبنا - Talabna',
            description: 'أكبر منصة للإعلانات المبوبة',
            og: {
                type: 'website',
                site_name: 'Talabna - طلبنا',
                locale: appStore.locale === 'ar' ? 'ar_SA' : 'en_US',
                image: '/storage/photos/og-image.jpg',
            },
            twitter: {
                card: 'summary_large_image',
                site: '@talabna',
            },
        }

        const mergedData = { ...defaultData, ...customData }
        applySeoData(mergedData)
    }

    /**
     * Set SEO for a listing
     */
    function setListingSeo(listing, locale = 'ar') {
        if (!listing) return

        const cityName = getLocalizedName(listing.city?.name, locale)
        const countryName = getLocalizedName(listing.country?.name, locale)
        const categoryName = getLocalizedName(listing.category?.name, locale)
        const location = cityName ? `${cityName}, ${countryName}` : countryName

        const title = locale === 'ar'
            ? `${listing.title} | ${categoryName} في ${location} - طلبنا`
            : `${listing.title} | ${categoryName} in ${location} - Talabna`

        const description = locale === 'ar'
            ? `${listing.description?.substring(0, 155)}... | السعر: ${listing.price} ${listing.price_currency_code} | الموقع: ${location}`
            : `${listing.description?.substring(0, 155)}... | Price: ${listing.price} ${listing.price_currency_code} | Location: ${location}`

        const image = listing.photos?.[0]
            ? (listing.photos[0].is_external ? listing.photos[0].src : `/storage/${listing.photos[0].src}`)
            : '/storage/photos/og-image.jpg'

        setSeo({
            title,
            description,
            keywords: `${listing.title}, ${categoryName}, ${cityName}, ${countryName}, للبيع, for sale`,
            canonical: `${window.location.origin}/listing/${listing.id}`,
            og: {
                type: 'product',
                title,
                description,
                image,
            },
            twitter: {
                title,
                description,
                image,
            },
            jsonLd: [
                {
                    '@context': 'https://schema.org',
                    '@type': 'Product',
                    'name': listing.title,
                    'description': listing.description?.substring(0, 500),
                    'image': image,
                    'offers': {
                        '@type': 'Offer',
                        'price': listing.price || 0,
                        'priceCurrency': listing.price_currency_code || 'USD',
                        'availability': 'https://schema.org/InStock',
                    },
                },
            ],
        })
    }

    /**
     * Set SEO for location/services page
     */
    function setLocationSeo(locationInfo, listingCount, locale = 'ar') {
        const countryName = locationInfo.country?.name || ''
        const cityName = locationInfo.city?.name || ''
        const categoryName = locationInfo.category?.name || ''

        let location = cityName ? `${cityName}, ${countryName}` : countryName
        let title, description

        if (categoryName && location) {
            title = locale === 'ar'
                ? `${categoryName} في ${location} | ${listingCount} إعلان - طلبنا`
                : `${categoryName} in ${location} | ${listingCount} Ads - Talabna`
            description = locale === 'ar'
                ? `اكتشف أفضل ${categoryName} في ${location}. تصفح ${listingCount} إعلان مع صور وأسعار على طلبنا.`
                : `Discover the best ${categoryName} in ${location}. Browse ${listingCount} listings with photos and prices on Talabna.`
        } else if (location) {
            title = locale === 'ar'
                ? `إعلانات ${location} | ${listingCount} إعلان مبوب - طلبنا`
                : `${location} Listings | ${listingCount} Classified Ads - Talabna`
            description = locale === 'ar'
                ? `تصفح جميع الإعلانات المبوبة في ${location}. ${listingCount} إعلان متاح الآن.`
                : `Browse all classified ads in ${location}. ${listingCount} listings available now.`
        }

        setSeo({
            title,
            description,
            keywords: `${categoryName}, ${cityName}, ${countryName}, إعلانات مبوبة, classified ads, للبيع`,
            jsonLd: [
                {
                    '@context': 'https://schema.org',
                    '@type': 'CollectionPage',
                    'name': title,
                    'description': description,
                    'numberOfItems': listingCount,
                },
            ],
        })
    }

    /**
     * Helper to get localized name
     */
    function getLocalizedName(name, locale) {
        if (!name) return ''
        if (typeof name === 'string') return name
        if (typeof name === 'object') {
            return name[locale] || name['ar'] || name['en'] || ''
        }
        return ''
    }

    // Watch for route changes
    watch(
        () => route.fullPath,
        (newPath) => {
            // Auto-fetch SEO data on route change (optional - can be disabled for manual control)
            // fetchSeoData(newPath)
        }
    )

    return {
        seoData,
        loading,
        fetchSeoData,
        setSeo,
        setListingSeo,
        setLocationSeo,
        applySeoData,
    }
}
