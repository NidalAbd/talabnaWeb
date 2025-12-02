<template>
  <v-container class="py-8">
    <!-- SEO Breadcrumbs -->
    <v-breadcrumbs :items="breadcrumbs" class="px-0 mb-4">
      <template v-slot:divider>
        <v-icon>mdi-chevron-right</v-icon>
      </template>
    </v-breadcrumbs>

    <!-- Page Header with SEO-friendly title -->
    <div class="text-center mb-8">
      <h1 class="text-h4 text-md-h3 font-weight-bold mb-3">
        {{ pageTitle }}
      </h1>
      <p class="text-body-1 text-medium-emphasis max-width-600 mx-auto">
        {{ pageDescription }}
      </p>
    </div>

    <!-- Location Info Cards -->
    <v-row v-if="locationInfo" class="mb-6">
      <v-col cols="12" md="4" v-if="locationInfo.country">
        <v-card variant="outlined" class="pa-4">
          <div class="d-flex align-center">
            <v-avatar color="primary" size="48" class="mr-3">
              <v-icon color="white">mdi-earth</v-icon>
            </v-avatar>
            <div>
              <div class="text-caption text-medium-emphasis">{{ isArabic ? 'الدولة' : 'Country' }}</div>
              <div class="text-h6 font-weight-bold">{{ locationInfo.country.name }}</div>
            </div>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" md="4" v-if="locationInfo.city">
        <v-card variant="outlined" class="pa-4">
          <div class="d-flex align-center">
            <v-avatar color="success" size="48" class="mr-3">
              <v-icon color="white">mdi-city</v-icon>
            </v-avatar>
            <div>
              <div class="text-caption text-medium-emphasis">{{ isArabic ? 'المدينة' : 'City' }}</div>
              <div class="text-h6 font-weight-bold">{{ locationInfo.city.name }}</div>
            </div>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" md="4" v-if="locationInfo.category">
        <v-card variant="outlined" class="pa-4">
          <div class="d-flex align-center">
            <v-avatar color="warning" size="48" class="mr-3">
              <v-icon color="white">mdi-shape</v-icon>
            </v-avatar>
            <div>
              <div class="text-caption text-medium-emphasis">{{ isArabic ? 'التصنيف' : 'Category' }}</div>
              <div class="text-h6 font-weight-bold">{{ locationInfo.category.name }}</div>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Stats -->
    <v-row class="mb-6">
      <v-col cols="6" md="3">
        <v-card color="primary" variant="flat" class="pa-4 text-center text-white">
          <div class="text-h4 font-weight-bold">{{ stats.totalListings }}</div>
          <div class="text-body-2">{{ isArabic ? 'إعلان' : 'Listings' }}</div>
        </v-card>
      </v-col>
      <v-col cols="6" md="3">
        <v-card color="success" variant="flat" class="pa-4 text-center text-white">
          <div class="text-h4 font-weight-bold">{{ stats.totalCategories }}</div>
          <div class="text-body-2">{{ isArabic ? 'تصنيف' : 'Categories' }}</div>
        </v-card>
      </v-col>
      <v-col cols="6" md="3">
        <v-card color="info" variant="flat" class="pa-4 text-center text-white">
          <div class="text-h4 font-weight-bold">{{ stats.totalCities }}</div>
          <div class="text-body-2">{{ isArabic ? 'مدينة' : 'Cities' }}</div>
        </v-card>
      </v-col>
      <v-col cols="6" md="3">
        <v-card color="warning" variant="flat" class="pa-4 text-center text-white">
          <div class="text-h4 font-weight-bold">{{ stats.totalUsers }}</div>
          <div class="text-body-2">{{ isArabic ? 'معلن' : 'Advertisers' }}</div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Sub-locations (Cities if viewing country, Categories if viewing city) -->
    <div v-if="subLocations.length > 0" class="mb-8">
      <h2 class="text-h5 font-weight-bold mb-4">
        {{ subLocationsTitle }}
      </h2>
      <v-row>
        <v-col v-for="loc in subLocations" :key="loc.id" cols="6" sm="4" md="3" lg="2">
          <v-card
            :to="loc.route"
            variant="outlined"
            class="pa-3 text-center hover-card"
            hover
          >
            <v-avatar :color="loc.color || 'primary'" size="40" class="mb-2">
              <v-icon color="white" size="20">{{ loc.icon || 'mdi-folder' }}</v-icon>
            </v-avatar>
            <div class="text-body-2 font-weight-medium text-truncate">{{ loc.name }}</div>
            <div class="text-caption text-medium-emphasis">{{ loc.count }} {{ isArabic ? 'إعلان' : 'ads' }}</div>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Listings -->
    <div class="mb-4">
      <div class="d-flex align-center justify-space-between mb-4">
        <h2 class="text-h5 font-weight-bold">
          {{ isArabic ? 'الإعلانات' : 'Listings' }}
          <span class="text-body-2 text-medium-emphasis">({{ pagination.total }})</span>
        </h2>
        <v-btn-toggle v-model="viewMode" mandatory density="compact">
          <v-btn value="grid" icon size="small"><v-icon>mdi-view-grid</v-icon></v-btn>
          <v-btn value="list" icon size="small"><v-icon>mdi-view-list</v-icon></v-btn>
        </v-btn-toggle>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-8">
        <v-progress-circular indeterminate color="primary" size="48" />
        <p class="mt-4 text-medium-emphasis">{{ isArabic ? 'جاري التحميل...' : 'Loading...' }}</p>
      </div>

      <!-- No Results -->
      <v-card v-else-if="listings.length === 0" variant="outlined" class="pa-8 text-center">
        <v-icon size="64" color="grey">mdi-magnify-close</v-icon>
        <h3 class="text-h6 mt-4">{{ isArabic ? 'لا توجد إعلانات' : 'No listings found' }}</h3>
        <p class="text-medium-emphasis">{{ isArabic ? 'جرب تغيير الموقع أو التصنيف' : 'Try changing location or category' }}</p>
      </v-card>

      <!-- Grid View -->
      <v-row v-else-if="viewMode === 'grid'">
        <v-col v-for="listing in listings" :key="listing.id" cols="12" sm="6" md="4" lg="3">
          <v-card
            :to="`/listing/${listing.id}/${slugify(listing.title)}`"
            class="listing-card h-100"
            hover
          >
            <v-img
              :src="listing.image || placeholderImage"
              height="180"
              cover
              class="bg-grey-lighten-3"
            >
              <template v-slot:error>
                <v-img :src="placeholderImage" height="180" cover />
              </template>
              <div v-if="listing.badge" class="listing-badge">
                <v-chip :color="getBadgeColor(listing.badge)" size="small" label>
                  {{ listing.badge }}
                </v-chip>
              </div>
            </v-img>
            <v-card-text>
              <h3 class="text-body-1 font-weight-medium text-truncate-2 mb-2">{{ listing.title }}</h3>
              <div class="d-flex align-center justify-space-between">
                <span class="text-primary font-weight-bold">{{ formatPrice(listing.price, listing.currency) }}</span>
                <span class="text-caption text-medium-emphasis">
                  <v-icon size="12">mdi-map-marker</v-icon>
                  {{ listing.city_name }}
                </span>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- List View -->
      <div v-else>
        <v-card
          v-for="listing in listings"
          :key="listing.id"
          :to="`/listing/${listing.id}/${slugify(listing.title)}`"
          class="mb-3 listing-card"
          hover
        >
          <div class="d-flex">
            <v-img
              :src="listing.image || placeholderImage"
              width="150"
              height="120"
              cover
              class="bg-grey-lighten-3 flex-shrink-0"
            >
              <template v-slot:error>
                <v-img :src="placeholderImage" width="150" height="120" cover />
              </template>
            </v-img>
            <v-card-text class="flex-grow-1">
              <h3 class="text-body-1 font-weight-medium mb-2">{{ listing.title }}</h3>
              <p class="text-caption text-medium-emphasis text-truncate-2 mb-2">{{ listing.description }}</p>
              <div class="d-flex align-center justify-space-between">
                <span class="text-primary font-weight-bold">{{ formatPrice(listing.price, listing.currency) }}</span>
                <span class="text-caption text-medium-emphasis">
                  <v-icon size="12">mdi-map-marker</v-icon>
                  {{ listing.city_name }}
                </span>
              </div>
            </v-card-text>
          </div>
        </v-card>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.lastPage > 1" class="d-flex justify-center mt-6">
        <v-pagination
          v-model="pagination.currentPage"
          :length="pagination.lastPage"
          :total-visible="5"
          rounded
          @update:model-value="loadListings"
        />
      </div>
    </div>

    <!-- SEO Content Section -->
    <v-card variant="outlined" class="mt-8 pa-6">
      <h2 class="text-h5 font-weight-bold mb-4">{{ seoSection.title }}</h2>
      <div class="text-body-2 text-medium-emphasis" v-html="seoSection.content"></div>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAppStore } from '@/stores/app'

const route = useRoute()
const appStore = useAppStore()

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

function getBadgeColor(badge) {
  const colors = {
    'premium': 'amber',
    'featured': 'primary',
    'urgent': 'error',
  }
  return colors[badge?.toLowerCase()] || 'grey'
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

      // Update page title for SEO
      if (locationInfo.value) {
        document.title = `${pageTitle.value} - طلبنا | Talabna`
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
.max-width-600 {
  max-width: 600px;
}

.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.listing-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.listing-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.hover-card {
  transition: all 0.2s ease;
}

.hover-card:hover {
  border-color: rgb(var(--v-theme-primary));
  background: rgba(var(--v-theme-primary), 0.05);
}

.listing-badge {
  position: absolute;
  top: 8px;
  left: 8px;
}

.rtl .listing-badge {
  left: auto;
  right: 8px;
}
</style>
