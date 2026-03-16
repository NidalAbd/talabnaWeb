<template>
  <div class="container py-8">
    <!-- SEO Breadcrumbs -->
    <nav class="breadcrumbs px-0 mb-4">
      <router-link v-for="(item, idx) in breadcrumbs" :key="idx" :to="item.to || '#'" :class="{ active: item.disabled }">
        {{ item.title }}
      </router-link>
    </nav>

    <!-- Page Header -->
    <div class="text-center mb-8">
      <h1 class="text-h4 font-weight-bold mb-3">{{ pageTitle }}</h1>
      <p class="text-body-1 text-muted" style="max-width: 600px; margin: 0 auto;">{{ pageDescription }}</p>
    </div>

    <!-- Location Info Cards -->
    <div v-if="locationInfo" class="row mb-6">
      <div v-if="locationInfo.country" class="col-12 col-md-4">
        <div class="card pa-4">
          <div class="d-flex align-center">
            <div class="avatar avatar-48 avatar-primary mr-3">
              <i class="mdi mdi-earth" style="font-size: 24px; color: #fff;"></i>
            </div>
            <div>
              <div class="text-caption text-muted">{{ isArabic ? 'الدولة' : 'Country' }}</div>
              <div class="text-h6 font-weight-bold">{{ locationInfo.country.name }}</div>
            </div>
          </div>
        </div>
      </div>
      <div v-if="locationInfo.city" class="col-12 col-md-4">
        <div class="card pa-4">
          <div class="d-flex align-center">
            <div class="avatar avatar-48 avatar-success mr-3">
              <i class="mdi mdi-city" style="font-size: 24px; color: #fff;"></i>
            </div>
            <div>
              <div class="text-caption text-muted">{{ isArabic ? 'المدينة' : 'City' }}</div>
              <div class="text-h6 font-weight-bold">{{ locationInfo.city.name }}</div>
            </div>
          </div>
        </div>
      </div>
      <div v-if="locationInfo.category" class="col-12 col-md-4">
        <div class="card pa-4">
          <div class="d-flex align-center">
            <div class="avatar avatar-48 avatar-warning mr-3">
              <i class="mdi mdi-shape" style="font-size: 24px; color: #fff;"></i>
            </div>
            <div>
              <div class="text-caption text-muted">{{ isArabic ? 'التصنيف' : 'Category' }}</div>
              <div class="text-h6 font-weight-bold">{{ locationInfo.category.name }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="row mb-6">
      <div class="col-6 col-md-3">
        <div class="card card-color-primary pa-4 text-center">
          <div class="text-h4 font-weight-bold">{{ stats.totalListings }}</div>
          <div class="text-body-2">{{ isArabic ? 'إعلان' : 'Listings' }}</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card card-color-success pa-4 text-center">
          <div class="text-h4 font-weight-bold">{{ stats.totalCategories }}</div>
          <div class="text-body-2">{{ isArabic ? 'تصنيف' : 'Categories' }}</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card card-color-info pa-4 text-center">
          <div class="text-h4 font-weight-bold">{{ stats.totalCities }}</div>
          <div class="text-body-2">{{ isArabic ? 'مدينة' : 'Cities' }}</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card card-color-warning pa-4 text-center">
          <div class="text-h4 font-weight-bold">{{ stats.totalUsers }}</div>
          <div class="text-body-2">{{ isArabic ? 'معلن' : 'Advertisers' }}</div>
        </div>
      </div>
    </div>

    <!-- Sub-locations -->
    <div v-if="subLocations.length > 0" class="mb-8">
      <h2 class="text-h5 font-weight-bold mb-4">{{ subLocationsTitle }}</h2>
      <div class="row">
        <div v-for="loc in subLocations" :key="loc.id" class="col-6 col-sm-4 col-md-3 col-lg-2">
          <router-link :to="loc.route" class="card card-hover pa-3 text-center h-100" style="text-decoration: none; display: block;">
            <div class="avatar avatar-40 mx-auto mb-2" :style="{ background: loc.color || 'var(--color-primary)' }">
              <i class="mdi" :class="loc.icon || 'mdi-folder'" style="font-size: 20px; color: #fff;"></i>
            </div>
            <div class="text-body-2 font-weight-medium text-truncate">{{ loc.name }}</div>
            <div class="text-caption text-muted">{{ loc.count }} {{ isArabic ? 'إعلان' : 'ads' }}</div>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Listings -->
    <div class="mb-4">
      <div class="d-flex align-center justify-between mb-4">
        <h2 class="text-h5 font-weight-bold">
          {{ isArabic ? 'الإعلانات' : 'Listings' }}
          <span class="text-body-2 text-muted">({{ pagination.total }})</span>
        </h2>
        <div class="btn-toggle">
          <button class="btn btn-sm" :class="{ active: viewMode === 'grid' }" @click="viewMode = 'grid'">
            <i class="mdi mdi-view-grid"></i>
          </button>
          <button class="btn btn-sm" :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'">
            <i class="mdi mdi-view-list"></i>
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-8">
        <div class="spinner spinner-md"></div>
        <p class="mt-4 text-muted">{{ isArabic ? 'جاري التحميل...' : 'Loading...' }}</p>
      </div>

      <!-- No Results -->
      <div v-else-if="listings.length === 0" class="card pa-8 text-center">
        <i class="mdi mdi-magnify-close" style="font-size: 64px; color: var(--color-text-muted);"></i>
        <h3 class="text-h6 mt-4">{{ isArabic ? 'لا توجد إعلانات' : 'No listings found' }}</h3>
        <p class="text-muted">{{ isArabic ? 'جرب تغيير الموقع أو التصنيف' : 'Try changing location or category' }}</p>
      </div>

      <!-- Grid View -->
      <div v-else-if="viewMode === 'grid'" class="row">
        <div v-for="listing in listings" :key="listing.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
          <listing-card :listing="listing" :locale="isArabic ? 'ar' : 'en'" />
        </div>
      </div>

      <!-- List View -->
      <div v-else>
        <router-link
          v-for="listing in listings"
          :key="listing.id"
          :to="`/listing/${listing.id}/${slugify(listing.title)}`"
          class="card card-hover mb-3"
          style="text-decoration: none; display: block;"
        >
          <div class="d-flex">
            <img
              :src="listing.image || placeholderImage"
              loading="lazy"
              style="width: 150px; height: 120px; object-fit: cover; flex-shrink: 0;"
              class="lazy-img"
              @error="$event.target.src = placeholderImage"
            />
            <div class="card-body flex-grow-1">
              <h3 class="text-body-1 font-weight-medium mb-2">{{ listing.title }}</h3>
              <p class="text-caption text-muted text-clamp-2 mb-2">{{ listing.description }}</p>
              <div class="d-flex align-center justify-between">
                <span class="text-primary font-weight-bold">{{ formatPrice(listing.price, listing.currency) }}</span>
                <span class="text-caption text-muted">
                  <i class="mdi mdi-map-marker" style="font-size: 12px;"></i>
                  {{ listing.city_name }}
                </span>
              </div>
            </div>
          </div>
        </router-link>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.lastPage > 1" class="d-flex justify-center mt-6">
        <div class="pagination">
          <button class="pagination-btn" :disabled="pagination.currentPage <= 1" @click="pagination.currentPage--; loadListings()">
            <i class="mdi mdi-chevron-left"></i>
          </button>
          <button v-for="p in paginationPages" :key="p" class="pagination-btn" :class="{ active: pagination.currentPage === p }" @click="pagination.currentPage = p; loadListings()">
            {{ p }}
          </button>
          <button class="pagination-btn" :disabled="pagination.currentPage >= pagination.lastPage" @click="pagination.currentPage++; loadListings()">
            <i class="mdi mdi-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- SEO Content Section -->
    <div class="card mt-8 pa-6">
      <h2 class="text-h5 font-weight-bold mb-4">{{ seoSection.title }}</h2>
      <div class="text-body-2 text-muted" v-html="seoSection.content"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { useAdvancedSeo } from '@/composables/useAdvancedSeo'
import ListingCard from '@/components/ListingCard.vue'

const route = useRoute()
const appStore = useAppStore()
const { setLocationSeo, setSeo } = useAdvancedSeo()

const loading = ref(true)
const viewMode = ref('grid')
const placeholderImage = '/storage/countryFlag/placeholder-flag.jpg'

// Data
const locationInfo = ref(null)
const listings = ref([])
const subLocations = ref([])
const stats = ref({
  totalListings: 0,
  totalCategories: 0,
  totalCities: 0,
  totalUsers: 0
})
const pagination = ref({
  currentPage: 1,
  lastPage: 1,
  total: 0
})

// Computed
const isArabic = computed(() => appStore.locale === 'ar')

const paginationPages = computed(() => {
  const pages = []
  const total = pagination.value.lastPage
  const current = pagination.value.currentPage
  const start = Math.max(1, current - 2)
  const end = Math.min(total, current + 2)
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

const pageTitle = computed(() => {
  if (!locationInfo.value) return isArabic.value ? 'خدمات' : 'Services'

  const parts = []
  if (locationInfo.value.category) {
    parts.push(locationInfo.value.category.name)
  }
  if (locationInfo.value.city) {
    parts.push(isArabic.value ? `في ${locationInfo.value.city.name}` : `in ${locationInfo.value.city.name}`)
  }
  if (locationInfo.value.country && !locationInfo.value.city) {
    parts.push(isArabic.value ? `في ${locationInfo.value.country.name}` : `in ${locationInfo.value.country.name}`)
  }

  if (parts.length === 0) {
    return isArabic.value ? 'خدمات وإعلانات' : 'Services & Listings'
  }

  return parts.join(' ')
})

const pageDescription = computed(() => {
  if (!locationInfo.value) {
    return isArabic.value
      ? 'تصفح أحدث الإعلانات والخدمات المتاحة في منطقتك'
      : 'Browse the latest listings and services available in your area'
  }

  const loc = locationInfo.value.city?.name || locationInfo.value.country?.name || ''
  const cat = locationInfo.value.category?.name || ''

  if (cat && loc) {
    return isArabic.value
      ? `اكتشف أفضل ${cat} في ${loc}. تصفح ${stats.value.totalListings} إعلان متاح الآن.`
      : `Discover the best ${cat} in ${loc}. Browse ${stats.value.totalListings} listings available now.`
  } else if (loc) {
    return isArabic.value
      ? `تصفح جميع الإعلانات والخدمات المتاحة في ${loc}. ${stats.value.totalListings} إعلان متاح.`
      : `Browse all listings and services available in ${loc}. ${stats.value.totalListings} listings available.`
  }

  return isArabic.value
    ? 'تصفح أحدث الإعلانات والخدمات المتاحة'
    : 'Browse the latest listings and services available'
})

const subLocationsTitle = computed(() => {
  if (route.params.cityId) {
    return isArabic.value ? 'التصنيفات المتاحة' : 'Available Categories'
  } else if (route.params.countryId) {
    return isArabic.value ? 'المدن المتاحة' : 'Available Cities'
  }
  return isArabic.value ? 'المواقع' : 'Locations'
})

const breadcrumbs = computed(() => {
  const items = [
    { title: isArabic.value ? 'الرئيسية' : 'Home', to: '/', disabled: false }
  ]

  if (locationInfo.value?.country) {
    items.push({
      title: locationInfo.value.country.name,
      to: `/services/${route.params.countryId}/${route.params.countrySlug}`,
      disabled: !route.params.cityId
    })
  }

  if (locationInfo.value?.city) {
    items.push({
      title: locationInfo.value.city.name,
      to: `/services/${route.params.countryId}/${route.params.countrySlug}/${route.params.cityId}/${route.params.citySlug}`,
      disabled: !route.params.categoryId
    })
  }

  if (locationInfo.value?.category) {
    items.push({
      title: locationInfo.value.category.name,
      disabled: true
    })
  }

  return items
})

const seoSection = computed(() => {
  const loc = locationInfo.value?.city?.name || locationInfo.value?.country?.name || ''
  const cat = locationInfo.value?.category?.name || ''

  if (isArabic.value) {
    return {
      title: `عن ${cat || 'الخدمات'} ${loc ? `في ${loc}` : ''}`,
      content: `
        <p>مرحباً بك في صفحة ${cat || 'الخدمات والإعلانات'} ${loc ? `في ${loc}` : ''} على منصة طلبنا.</p>
        <p>نوفر لك أفضل الإعلانات والخدمات المتاحة من بائعين موثوقين. يمكنك التصفح والبحث والتواصل مباشرة مع المعلنين.</p>
        <p>استخدم فلاتر البحث للعثور على ما تبحث عنه بسرعة وسهولة.</p>
      `
    }
  }

  return {
    title: `About ${cat || 'Services'} ${loc ? `in ${loc}` : ''}`,
    content: `
      <p>Welcome to the ${cat || 'services and listings'} page ${loc ? `in ${loc}` : ''} on Talabna.</p>
      <p>We provide you with the best listings and services available from trusted sellers. You can browse, search, and contact advertisers directly.</p>
      <p>Use the search filters to find what you're looking for quickly and easily.</p>
    `
  }
})

// Methods
function slugify(text) {
  if (!text) return 'item'
  return encodeURIComponent(
    text.replace(/[^\p{L}\p{N}\s-]/gu, '')
        .replace(/[\s-]+/g, '-')
        .trim()
  ) || 'item'
}

function formatPrice(price, currency) {
  if (!price) return isArabic.value ? 'اتصل للسعر' : 'Contact for price'
  return `${price.toLocaleString()} ${currency || ''}`
}

async function loadData() {
  loading.value = true

  try {
    const params = new URLSearchParams()
    params.append('page', pagination.value.currentPage)

    if (route.params.countryId) params.append('country_id', route.params.countryId)
    if (route.params.cityId) params.append('city_id', route.params.cityId)
    if (route.params.categoryId) params.append('category_id', route.params.categoryId)

    const response = await fetch(`/api/public/services?${params.toString()}`)

    if (response.ok) {
      const data = await response.json()

      locationInfo.value = data.location || null
      listings.value = data.listings?.data || []
      subLocations.value = data.subLocations || []
      stats.value = data.stats || stats.value
      pagination.value = {
        currentPage: data.listings?.current_page || 1,
        lastPage: data.listings?.last_page || 1,
        total: data.listings?.total || 0
      }

      if (locationInfo.value) {
        setLocationSeo(locationInfo.value, stats.value.totalListings, appStore.locale)
      }
    }
  } catch (error) {
    console.error('Error loading services:', error)
  } finally {
    loading.value = false
  }
}

async function loadListings() {
  await loadData()
}

// Watch route changes
watch(
  () => route.params,
  () => {
    pagination.value.currentPage = 1
    loadData()
  },
  { deep: true }
)

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.text-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Breadcrumbs separator */
.breadcrumbs a + a::before {
  content: '/';
  margin: 0 0.5rem;
  opacity: 0.5;
}
</style>
