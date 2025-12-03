<template>
  <div class="category-page">
    <!-- Hero -->
    <section class="hero-gradient py-12">
      <v-container>
        <v-avatar :color="getCategoryColor(category?.id)" size="80" class="mb-4">
          <v-icon size="40" color="white">{{ getCategoryIcon(category?.id) }}</v-icon>
        </v-avatar>
        <h1 class="text-h3 font-weight-bold text-white mb-2">
          {{ locale === 'ar' ? category?.name : category?.name_en }}
        </h1>
        <p class="text-h6 text-white-darken-1">
          {{ pagination.total }} {{ locale === 'ar' ? 'إعلان' : 'listings' }}
        </p>

        <!-- Location info for Near category -->
        <div v-if="isNearCategory && userLocation" class="mt-2">
          <v-chip color="white" variant="flat" size="small">
            <v-icon start size="16">mdi-crosshairs-gps</v-icon>
            {{ locale === 'ar' ? 'موقعك الحالي' : 'Your current location' }}
          </v-chip>
        </div>

        <!-- Location permission needed for Near category -->
        <div v-if="isNearCategory && !userLocation && !locationError" class="mt-4">
          <v-btn color="white" variant="flat" @click="requestLocation" :loading="requestingLocation">
            <v-icon start>mdi-crosshairs-gps</v-icon>
            {{ locale === 'ar' ? 'تفعيل الموقع' : 'Enable Location' }}
          </v-btn>
        </div>

        <!-- Location error -->
        <v-alert v-if="locationError" type="warning" variant="tonal" class="mt-4" density="compact">
          {{ locationError }}
        </v-alert>
      </v-container>
    </section>

    <v-container class="py-8">
      <!-- Radius selector for Near category -->
      <div v-if="isNearCategory && userLocation" class="mb-6">
        <h2 class="text-h6 font-weight-bold mb-3">{{ locale === 'ar' ? 'نطاق البحث' : 'Search Radius' }}</h2>
        <v-chip-group v-model="searchRadius" mandatory @update:model-value="fetchListings">
          <v-chip filter :value="5">5 {{ locale === 'ar' ? 'كم' : 'km' }}</v-chip>
          <v-chip filter :value="10">10 {{ locale === 'ar' ? 'كم' : 'km' }}</v-chip>
          <v-chip filter :value="25">25 {{ locale === 'ar' ? 'كم' : 'km' }}</v-chip>
          <v-chip filter :value="50">50 {{ locale === 'ar' ? 'كم' : 'km' }}</v-chip>
          <v-chip filter :value="100">100 {{ locale === 'ar' ? 'كم' : 'km' }}</v-chip>
        </v-chip-group>
      </div>

      <!-- Subcategories (hidden for special categories) -->
      <div v-if="subcategories.length > 0 && !isSpecialCategory" class="mb-8">
        <h2 class="text-h6 font-weight-bold mb-4">{{ locale === 'ar' ? 'التصنيفات الفرعية' : 'Subcategories' }}</h2>
        <v-chip-group v-model="selectedSubcategory" column @update:model-value="onSubcategoryChange">
          <v-chip filter :value="null">{{ locale === 'ar' ? 'الكل' : 'All' }}</v-chip>
          <v-chip v-for="sub in subcategories" :key="sub.id" filter :value="sub.id">
            {{ locale === 'ar' ? sub.name : sub.name_en }} ({{ sub.posts_count }})
          </v-chip>
        </v-chip-group>
      </div>

      <!-- Listings -->
      <v-row v-if="!loading && listings.length > 0">
        <v-col v-for="listing in listings" :key="listing.id" cols="12" sm="6" md="4" lg="3">
          <listing-card :listing="listing" :locale="locale">
            <!-- Show distance for Near category -->
            <template v-if="isNearCategory && listing.distance" #extra>
              <v-chip size="x-small" color="teal" class="mt-1">
                <v-icon start size="12">mdi-map-marker-distance</v-icon>
                {{ listing.distance }} {{ locale === 'ar' ? 'كم' : 'km' }}
              </v-chip>
            </template>
          </listing-card>
        </v-col>
      </v-row>

      <!-- Loading -->
      <div v-else-if="loading" class="text-center py-16">
        <v-progress-circular indeterminate color="primary" size="64" />
        <p v-if="isNearCategory && requestingLocation" class="mt-4 text-medium-emphasis">
          {{ locale === 'ar' ? 'جاري تحديد موقعك...' : 'Getting your location...' }}
        </p>
      </div>

      <!-- No Results -->
      <v-card v-else class="text-center py-16" variant="flat">
        <v-icon size="80" color="grey">{{ isNearCategory ? 'mdi-map-marker-off' : (isReelsCategory ? 'mdi-video-off' : 'mdi-folder-open') }}</v-icon>
        <h3 class="text-h5 mt-4">
          {{ isNearCategory
              ? (locale === 'ar' ? 'لا توجد إعلانات قريبة منك' : 'No listings near you')
              : isReelsCategory
                ? (locale === 'ar' ? 'لا توجد فيديوهات' : 'No video listings')
                : (locale === 'ar' ? 'لا توجد إعلانات' : 'No listings found')
          }}
        </h3>
        <p v-if="isNearCategory && !userLocation" class="text-medium-emphasis mt-2">
          {{ locale === 'ar' ? 'قم بتفعيل الموقع لعرض الإعلانات القريبة منك' : 'Enable location to see nearby listings' }}
        </p>
      </v-card>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="d-flex justify-center mt-8">
        <v-pagination v-model="currentPage" :length="pagination.last_page" rounded="circle" @update:model-value="fetchListings" />
      </div>
    </v-container>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { useSeo } from '@/composables/useSeo'
import ListingCard from '@/components/ListingCard.vue'

// Special category IDs
const CATEGORY_NEAR = 6   // قربي - Near me
const CATEGORY_REELS = 7  // فيديو - Reels

const route = useRoute()
const router = useRouter()
const appStore = useAppStore()
const { updateMeta, setBreadcrumbSchema } = useSeo()

const category = ref(null)
const subcategories = ref([])
const listings = ref([])
const loading = ref(true)
const selectedSubcategory = ref(null)
const currentPage = ref(1)
const pagination = ref({ total: 0, last_page: 1 })

// Location-related state for Near category
const userLocation = ref(null)
const locationError = ref(null)
const requestingLocation = ref(false)
const searchRadius = ref(25) // Default 25km

const locale = computed(() => appStore.locale)
const categoryId = computed(() => parseInt(route.params.id))
const isNearCategory = computed(() => categoryId.value === CATEGORY_NEAR)
const isReelsCategory = computed(() => categoryId.value === CATEGORY_REELS)
const isSpecialCategory = computed(() => isNearCategory.value || isReelsCategory.value)

// Updated icons and colors for all 8 categories
const categoryIcons = {
  1: 'mdi-briefcase',      // Jobs (وظائف)
  2: 'mdi-cellphone',      // Devices (اجهزة)
  3: 'mdi-home-city',      // Houses (عقارات)
  4: 'mdi-car',            // Cars (سيارات)
  5: 'mdi-tools',          // Services (خدمات)
  6: 'mdi-map-marker-radius', // Near (قربي)
  7: 'mdi-video',          // Reels (فيديو)
  8: 'mdi-alert-circle',   // Emergency (طوارئ)
}
const categoryColors = {
  1: 'green',              // Jobs
  2: 'blue',               // Devices
  3: 'purple',             // Houses
  4: 'red',                // Cars
  5: 'orange',             // Services
  6: 'teal',               // Near
  7: 'pink',               // Reels
  8: 'deep-orange',        // Emergency
}
const getCategoryIcon = (id) => categoryIcons[id] || 'mdi-folder'
const getCategoryColor = (id) => categoryColors[id] || 'grey'

// Request user's location
const requestLocation = () => {
  if (!navigator.geolocation) {
    locationError.value = locale.value === 'ar'
      ? 'المتصفح لا يدعم تحديد الموقع'
      : 'Geolocation is not supported by your browser'
    return
  }

  requestingLocation.value = true
  locationError.value = null

  navigator.geolocation.getCurrentPosition(
    (position) => {
      userLocation.value = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
      }
      requestingLocation.value = false
      fetchListings()
    },
    (error) => {
      requestingLocation.value = false
      switch (error.code) {
        case error.PERMISSION_DENIED:
          locationError.value = locale.value === 'ar'
            ? 'تم رفض إذن الموقع. يرجى السماح بالوصول إلى موقعك.'
            : 'Location permission denied. Please allow access to your location.'
          break
        case error.POSITION_UNAVAILABLE:
          locationError.value = locale.value === 'ar'
            ? 'معلومات الموقع غير متوفرة.'
            : 'Location information is unavailable.'
          break
        case error.TIMEOUT:
          locationError.value = locale.value === 'ar'
            ? 'انتهت مهلة طلب الموقع.'
            : 'Location request timed out.'
          break
        default:
          locationError.value = locale.value === 'ar'
            ? 'حدث خطأ غير معروف.'
            : 'An unknown error occurred.'
      }
    },
    {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 300000 // Cache for 5 minutes
    }
  )
}

const fetchCategory = async () => {
  try {
    const response = await fetch('/api/public/categories')
    if (!response.ok) return
    const data = await response.json()
    const cats = Array.isArray(data.categories) ? data.categories : []
    category.value = cats.find(c => c.id === parseInt(route.params.id))

    if (category.value) {
      updateMeta({
        title: `${locale.value === 'ar' ? category.value.name : category.value.name_en} - طلبنا`,
        description: `تصفح إعلانات ${category.value.name} على طلبنا`,
      })
    }
  } catch (error) {
    console.error('Error:', error)
  }
}

const fetchSubcategories = async () => {
  // Don't fetch subcategories for special categories
  if (isSpecialCategory.value) {
    subcategories.value = []
    return
  }

  try {
    const response = await fetch(`/api/public/categories/${route.params.id}/subcategories`)
    if (!response.ok) return
    const data = await response.json()
    subcategories.value = Array.isArray(data.subcategories) ? data.subcategories : []

    // Check if subcategory is specified in URL query and pre-select it
    const subcatFromUrl = route.query.subcategory
    if (subcatFromUrl) {
      selectedSubcategory.value = parseInt(subcatFromUrl)
    }
  } catch (error) {
    console.error('Error:', error)
  }
}

const fetchListings = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      category_id: route.params.id,
      page: currentPage.value,
    })

    // Add location params for Near category
    if (isNearCategory.value && userLocation.value) {
      params.append('lat', userLocation.value.lat)
      params.append('lng', userLocation.value.lng)
      params.append('radius', searchRadius.value)
    }

    if (selectedSubcategory.value && !isSpecialCategory.value) {
      params.append('subcategory_id', selectedSubcategory.value)
    }

    const response = await fetch(`/api/public/listings?${params}`)
    if (!response.ok) return
    const data = await response.json()
    listings.value = Array.isArray(data.listings) ? data.listings : []
    pagination.value = data.pagination || pagination.value
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loading.value = false
  }
}

const onSubcategoryChange = () => {
  currentPage.value = 1
  // Update URL query when subcategory changes
  if (selectedSubcategory.value) {
    router.replace({ query: { subcategory: selectedSubcategory.value } })
  } else {
    router.replace({ query: {} })
  }
  fetchListings()
}

onMounted(() => {
  // Check for subcategory in URL query on mount
  const subcatFromUrl = route.query.subcategory
  if (subcatFromUrl) {
    selectedSubcategory.value = parseInt(subcatFromUrl)
  }

  fetchCategory()
  fetchSubcategories()

  // For Near category, try to get location automatically
  if (isNearCategory.value) {
    requestLocation()
  } else {
    fetchListings()
  }
})

watch(() => route.params.id, () => {
  selectedSubcategory.value = null
  currentPage.value = 1
  userLocation.value = null
  locationError.value = null

  fetchCategory()
  fetchSubcategories()

  // For Near category, request location
  if (isNearCategory.value) {
    requestLocation()
  } else {
    fetchListings()
  }
})

// Watch for URL query changes (e.g., when user navigates via sitemap link)
watch(() => route.query.subcategory, (newVal) => {
  if (isSpecialCategory.value) return

  if (newVal) {
    selectedSubcategory.value = parseInt(newVal)
  } else {
    selectedSubcategory.value = null
  }
  currentPage.value = 1
  fetchListings()
})
</script>

<style scoped>
.hero-gradient {
  background: linear-gradient(135deg, #5035FF 0%, #7C6AFF 100%);
}
</style>
