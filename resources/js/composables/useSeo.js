import { ref, watch, onMounted } from 'vue'

/**
 * SEO Composable for managing meta tags, OpenGraph, Twitter Cards, and JSON-LD
 */
export function useSeo() {
  const defaultTitle = 'طلبنا - Talabna | سوق الإعلانات المبوبة'
  const defaultDescription = 'طلبنا - أكبر منصة للإعلانات المبوبة. بيع واشتري السيارات، العقارات، الهواتف، الوظائف والمزيد. Talabna - The largest classified ads marketplace.'
  const defaultImage = '/storage/photos/og-image.jpg'
  const siteUrl = window.location.origin

  /**
   * Update page meta tags
   */
  function updateMeta(options = {}) {
    const {
      title = defaultTitle,
      description = defaultDescription,
      image = defaultImage,
      url = window.location.href,
      type = 'website',
      locale = 'ar_SA',
      keywords = 'إعلانات مبوبة, سيارات, عقارات, وظائف, هواتف, بيع, شراء, classified ads, cars, real estate, jobs',
    } = options

    // Update title
    document.title = title

    // Helper function to update or create meta tag
    const setMeta = (name, content, property = false) => {
      const attr = property ? 'property' : 'name'
      let tag = document.querySelector(`meta[${attr}="${name}"]`)
      if (!tag) {
        tag = document.createElement('meta')
        tag.setAttribute(attr, name)
        document.head.appendChild(tag)
      }
      tag.setAttribute('content', content)
    }

    // Standard meta tags
    setMeta('description', description)
    setMeta('keywords', keywords)
    setMeta('author', 'Talabna')
    setMeta('robots', 'index, follow')

    // Helper to get full image URL
    const getImageUrl = (img) => {
      if (!img) return siteUrl + defaultImage
      return img.startsWith('http') ? img : siteUrl + img
    }

    // OpenGraph tags
    setMeta('og:title', title, true)
    setMeta('og:description', description, true)
    setMeta('og:image', getImageUrl(image), true)
    setMeta('og:url', url, true)
    setMeta('og:type', type, true)
    setMeta('og:locale', locale, true)
    setMeta('og:site_name', 'Talabna - طلبنا', true)

    // Twitter Card tags
    setMeta('twitter:card', 'summary_large_image')
    setMeta('twitter:title', title)
    setMeta('twitter:description', description)
    setMeta('twitter:image', getImageUrl(image))

    // Canonical URL
    let canonical = document.querySelector('link[rel="canonical"]')
    if (!canonical) {
      canonical = document.createElement('link')
      canonical.setAttribute('rel', 'canonical')
      document.head.appendChild(canonical)
    }
    canonical.setAttribute('href', url)
  }

  /**
   * Add JSON-LD structured data for a listing/product
   */
  function setListingSchema(listing) {
    // Remove existing listing schema
    const existingSchema = document.querySelector('#listing-schema')
    if (existingSchema) existingSchema.remove()

    if (!listing) return

    const schema = {
      '@context': 'https://schema.org',
      '@type': 'Product',
      name: listing.title || listing.name,
      description: listing.description || '',
      image: listing.photos?.map(p => p.url || p.src) || [],
      offers: {
        '@type': 'Offer',
        price: listing.price || 0,
        priceCurrency: 'USD',
        availability: 'https://schema.org/InStock',
        seller: {
          '@type': 'Person',
          name: listing.user?.name || 'Seller',
        },
      },
      category: listing.category?.name || listing.category_name || '',
      datePosted: listing.created_at,
      locationCreated: {
        '@type': 'Place',
        address: {
          '@type': 'PostalAddress',
          addressLocality: listing.city?.name || '',
          addressCountry: listing.country?.name || '',
        },
      },
    }

    const script = document.createElement('script')
    script.id = 'listing-schema'
    script.type = 'application/ld+json'
    script.textContent = JSON.stringify(schema)
    document.head.appendChild(script)
  }

  /**
   * Add JSON-LD structured data for organization
   */
  function setOrganizationSchema() {
    const existingSchema = document.querySelector('#org-schema')
    if (existingSchema) return

    const schema = {
      '@context': 'https://schema.org',
      '@type': 'Organization',
      name: 'Talabna - طلبنا',
      url: siteUrl,
      logo: siteUrl + '/storage/photos/logo.png',
      description: defaultDescription,
      sameAs: [
        'https://facebook.com/talabna',
        'https://twitter.com/talabna',
        'https://instagram.com/talabna',
      ],
      contactPoint: {
        '@type': 'ContactPoint',
        contactType: 'customer service',
        availableLanguage: ['Arabic', 'English'],
      },
    }

    const script = document.createElement('script')
    script.id = 'org-schema'
    script.type = 'application/ld+json'
    script.textContent = JSON.stringify(schema)
    document.head.appendChild(script)
  }

  /**
   * Add JSON-LD breadcrumb schema
   */
  function setBreadcrumbSchema(items) {
    const existingSchema = document.querySelector('#breadcrumb-schema')
    if (existingSchema) existingSchema.remove()

    if (!items || items.length === 0) return

    const schema = {
      '@context': 'https://schema.org',
      '@type': 'BreadcrumbList',
      itemListElement: items.map((item, index) => ({
        '@type': 'ListItem',
        position: index + 1,
        name: item.title,
        item: item.url ? siteUrl + item.url : undefined,
      })),
    }

    const script = document.createElement('script')
    script.id = 'breadcrumb-schema'
    script.type = 'application/ld+json'
    script.textContent = JSON.stringify(schema)
    document.head.appendChild(script)
  }

  /**
   * Add JSON-LD WebSite schema with search action
   */
  function setWebsiteSchema() {
    const existingSchema = document.querySelector('#website-schema')
    if (existingSchema) return

    const schema = {
      '@context': 'https://schema.org',
      '@type': 'WebSite',
      name: 'Talabna - طلبنا',
      url: siteUrl,
      potentialAction: {
        '@type': 'SearchAction',
        target: {
          '@type': 'EntryPoint',
          urlTemplate: siteUrl + '/search?q={search_term_string}',
        },
        'query-input': 'required name=search_term_string',
      },
    }

    const script = document.createElement('script')
    script.id = 'website-schema'
    script.type = 'application/ld+json'
    script.textContent = JSON.stringify(schema)
    document.head.appendChild(script)
  }

  // Initialize organization and website schemas
  onMounted(() => {
    setOrganizationSchema()
    setWebsiteSchema()
  })

  return {
    updateMeta,
    setListingSchema,
    setOrganizationSchema,
    setBreadcrumbSchema,
    setWebsiteSchema,
  }
}
