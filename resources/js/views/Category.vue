<template>
  <div class="category-page">
    <!-- Hero -->
    <section class="hero-gradient py-12">
      <div class="container">
        <!-- Breadcrumb for subcategory -->
        <nav v-if="isSubcategoryRoute && category" class="breadcrumbs mb-2">
          <router-link :to="{ name: 'home' }" class="breadcrumb-link text-white-darken-2">
            {{ locale === 'ar' ? 'الرئيسية' : 'Home' }}
          </router-link>
          <span class="breadcrumb-separator text-white-darken-2">/</span>
          <router-link :to="{ name: 'category', params: { id: category.id, slug: category.slug } }" class="breadcrumb-link text-white-darken-2">
            {{ locale === 'ar' ? category.name : category.name_en }}
          </router-link>
          <span class="breadcrumb-separator text-white-darken-2">/</span>
          <span class="breadcrumb-current text-white">
            {{ locale === 'ar' ? currentSubcategory?.name : currentSubcategory?.name_en }}
          </span>
        </nav>

        <div class="avatar avatar-80 mb-4" :style="{ background: getCategoryColor(category?.id) }">
          <i class="mdi" :class="getCategoryIcon(category?.id)" style="font-size: 40px; color: #fff;"></i>
        </div>
        <h1 class="text-h3 font-weight-bold text-white mb-2">
          <!-- Show subcategory name if on subcategory route -->
          {{ isSubcategoryRoute && currentSubcategory
              ? (locale === 'ar' ? currentSubcategory.name : currentSubcategory.name_en)
              : (locale === 'ar' ? category?.name : category?.name_en)
          }}
        </h1>
        <p v-if="isSubcategoryRoute && category" class="text-subtitle-1 text-white-darken-2 mb-2">
          {{ locale === 'ar' ? 'في' : 'in' }} {{ locale === 'ar' ? category.name : category.name_en }}
        </p>
        <p class="text-h6 text-white-darken-1">
          {{ pagination.total }} {{ locale === 'ar' ? 'إعلان' : 'listings' }}
        </p>

        <!-- Location info for Near category -->
        <div v-if="isNearCategory && userLocation" class="mt-2">
          <span class="chip chip-flat-white">
            <i class="mdi mdi-crosshairs-gps" style="font-size: 16px; margin-inline-end: 4px;"></i>
            {{ locale === 'ar' ? 'موقعك الحالي' : 'Your current location' }}
          </span>
        </div>

        <!-- Location permission needed for Near category -->
        <div v-if="isNearCategory && !userLocation && !locationError" class="mt-4">
          <button class="btn btn-white" @click="requestLocation" :disabled="requestingLocation">
            <i class="mdi mdi-crosshairs-gps" style="margin-inline-end: 6px;"></i>
            {{ locale === 'ar' ? 'تفعيل الموقع' : 'Enable Location' }}
          </button>
        </div>

        <!-- Location error -->
        <div v-if="locationError" class="alert alert-warning mt-4">
          {{ locationError }}
        </div>
      </div>
    </section>

    <div class="container py-8">
      <!-- Radius selector for Near category -->
      <div v-if="isNearCategory && userLocation" class="mb-6">
        <h2 class="text-h6 font-weight-bold mb-3">{{ locale === 'ar' ? 'نطاق البحث' : 'Search Radius' }}</h2>
        <div class="d-flex flex-wrap gap-2">
          <button class="chip chip-filter" :class="{ active: searchRadius === 5 }" @click="searchRadius = 5; fetchListings()">5 {{ locale === 'ar' ? 'كم' : 'km' }}</button>
          <button class="chip chip-filter" :class="{ active: searchRadius === 10 }" @click="searchRadius = 10; fetchListings()">10 {{ locale === 'ar' ? 'كم' : 'km' }}</button>
          <button class="chip chip-filter" :class="{ active: searchRadius === 25 }" @click="searchRadius = 25; fetchListings()">25 {{ locale === 'ar' ? 'كم' : 'km' }}</button>
          <button class="chip chip-filter" :class="{ active: searchRadius === 50 }" @click="searchRadius = 50; fetchListings()">50 {{ locale === 'ar' ? 'كم' : 'km' }}</button>
          <button class="chip chip-filter" :class="{ active: searchRadius === 100 }" @click="searchRadius = 100; fetchListings()">100 {{ locale === 'ar' ? 'كم' : 'km' }}</button>
        </div>
      </div>

      <!-- Subcategories (hidden for special categories and subcategory route) -->
      <div v-if="subcategories.length > 0 && !isSpecialCategory && !isSubcategoryRoute" class="mb-8">
        <h2 class="text-h6 font-weight-bold mb-4">{{ locale === 'ar' ? 'التصنيفات الفرعية' : 'Subcategories' }}</h2>
        <div class="d-flex flex-wrap gap-2">
          <button class="chip chip-filter" :class="{ active: selectedSubcategory === null }" @click="selectedSubcategory = null; onSubcategoryChange()">
            {{ locale === 'ar' ? 'الكل' : 'All' }}
          </button>
          <button v-for="sub in subcategories" :key="sub.id" class="chip chip-filter" :class="{ active: selectedSubcategory === sub.id }" @click="selectedSubcategory = sub.id; onSubcategoryChange()">
            {{ locale === 'ar' ? sub.name : sub.name_en }} ({{ sub.posts_count }})
          </button>
        </div>
      </div>

      <!-- Back to category button when on subcategory route -->
      <div v-if="isSubcategoryRoute && category" class="mb-6">
        <router-link
          class="btn btn-outlined"
          :to="{ name: 'category', params: { id: category.id, slug: category.slug } }"
        >
          <i class="mdi mdi-arrow-left" style="margin-inline-end: 6px;"></i>
          {{ locale === 'ar' ? 'عرض كل التصنيفات الفرعية' : 'View all subcategories' }}
        </router-link>
      </div>

      <!-- Listings -->
      <div v-if="!loading && listings.length > 0" class="row">
        <div v-for="listing in listings" :key="listing.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
          <listing-card :listing="listing" :locale="locale">
            <!-- Show distance for Near category -->
            <template v-if="isNearCategory && listing.distance" #extra>
              <span class="chip chip-xs chip-teal mt-1">
                <i class="mdi mdi-map-marker-distance" style="font-size: 12px; margin-inline-end: 4px;"></i>
                {{ listing.distance }} {{ locale === 'ar' ? 'كم' : 'km' }}
              </span>
            </template>
          </listing-card>
        </div>
      </div>

      <!-- Loading -->
      <div v-else-if="loading" class="text-center py-16">
        <div class="spinner spinner-lg"></div>
        <p v-if="isNearCategory && requestingLocation" class="mt-4 text-medium-emphasis">
          {{ locale === 'ar' ? 'جاري تحديد موقعك...' : 'Getting your location...' }}
        </p>
      </div>

      <!-- No Results -->
      <div v-else class="card-flat text-center py-16">
        <i class="mdi" :class="isNearCategory ? 'mdi-map-marker-off' : (isReelsCategory ? 'mdi-video-off' : 'mdi-folder-open')" style="font-size: 80px; color: grey;"></i>
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
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="d-flex justify-center mt-8">
        <div class="pagination">
          <button class="pagination-btn" :disabled="currentPage <= 1" @click="currentPage--; fetchListings()">
            <i class="mdi mdi-chevron-left"></i>
          </button>
          <button v-for="p in paginationPages" :key="p" class="pagination-btn" :class="{ active: currentPage === p }" @click="currentPage = p; fetchListings()">
            {{ p }}
          </button>
          <button class="pagination-btn" :disabled="currentPage >= pagination.last_page" @click="currentPage++; fetchListings()">
            <i class="mdi mdi-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
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
const { updateMeta, setBreadcrumbSchema, setItemListSchema } = useSeo()

const category = ref(null)
const subcategories = ref([])
const currentSubcategory = ref(null)
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

// Check if we're on the subcategory route
const isSubcategoryRoute = computed(() => route.name === 'subcategory' && route.params.subcategoryId)
const subcategoryIdFromRoute = computed(() => route.params.subcategoryId ? parseInt(route.params.subcategoryId) : null)

import { getCategoryIcon, getCategoryColor } from '@/utils/helpers'

const paginationPages = computed(() => {
  const pages = []
  const total = pagination.value.last_page
  const current = currentPage.value
  const start = Math.max(1, current - 2)
  const end = Math.min(total, current + 2)
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

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
      // Update SEO meta
      const title = isSubcategoryRoute.value && currentSubcategory.value
        ? `${locale.value === 'ar' ? currentSubcategory.value.name : currentSubcategory.value.name_en} - ${locale.value === 'ar' ? category.value.name : category.value.name_en} - طلبنا`
        : `${locale.value === 'ar' ? category.value.name : category.value.name_en} - طلبنا`

      updateMeta({
        title,
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

    // Set current subcategory if on subcategory route
    if (isSubcategoryRoute.value && subcategoryIdFromRoute.value) {
      currentSubcategory.value = subcategories.value.find(s => s.id === subcategoryIdFromRoute.value)
      selectedSubcategory.value = subcategoryIdFromRoute.value
    }
    // Check if subcategory is specified in URL query (legacy support)
    else if (route.query.subcategory) {
      selectedSubcategory.value = parseInt(route.query.subcategory)
      currentSubcategory.value = subcategories.value.find(s => s.id === selectedSubcategory.value)
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

    // Use subcategory from route or selected
    const subcatId = isSubcategoryRoute.value ? subcategoryIdFromRoute.value : selectedSubcategory.value
    if (subcatId && !isSpecialCategory.value) {
      params.append('subcategory_id', subcatId)
    }

    const response = await fetch(`/api/public/listings?${params}`)
    if (!response.ok) return
    const data = await response.json()
    listings.value = Array.isArray(data.listings) ? data.listings : []
    pagination.value = data.pagination || pagination.value
    if (listings.value.length > 0) {
      setItemListSchema(listings.value, category.value?.name || 'Category')
    }
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loading.value = false
  }
}

const onSubcategoryChange = () => {
  currentPage.value = 1

  if (selectedSubcategory.value) {
    // Navigate to SEO-friendly subcategory URL
    const subcat = subcategories.value.find(s => s.id === selectedSubcategory.value)
    if (subcat && category.value) {
      router.push({
        name: 'subcategory',
        params: {
          id: category.value.id,
          slug: category.value.slug || encodeURIComponent(locale.value === 'ar' ? category.value.name : category.value.name_en),
          subcategoryId: subcat.id,
          subcategorySlug: subcat.slug || encodeURIComponent(locale.value === 'ar' ? subcat.name : subcat.name_en)
        }
      })
      return
    }
  }

  // Navigate back to category (all subcategories)
  if (category.value) {
    router.push({
      name: 'category',
      params: {
        id: category.value.id,
        slug: category.value.slug
      }
    })
  }
}

const initPage = () => {
  // Reset state
  currentSubcategory.value = null

  // Set selected subcategory from route
  if (isSubcategoryRoute.value && subcategoryIdFromRoute.value) {
    selectedSubcategory.value = subcategoryIdFromRoute.value
  } else if (route.query.subcategory) {
    selectedSubcategory.value = parseInt(route.query.subcategory)
  } else {
    selectedSubcategory.value = null
  }

  fetchCategory()
  fetchSubcategories()

  // For Near category, try to get location automatically
  if (isNearCategory.value) {
    requestLocation()
  } else {
    fetchListings()
  }
}

onMounted(() => {
  initPage()
})

// Watch for route changes (category ID or subcategory ID)
watch([() => route.params.id, () => route.params.subcategoryId], () => {
  currentPage.value = 1
  userLocation.value = null
  locationError.value = null
  initPage()
})

// Watch for URL query changes (legacy support for ?subcategory=)
watch(() => route.query.subcategory, (newVal) => {
  if (isSpecialCategory.value || isSubcategoryRoute.value) return

  if (newVal) {
    selectedSubcategory.value = parseInt(newVal)
    currentSubcategory.value = subcategories.value.find(s => s.id === selectedSubcategory.value)
  } else {
    selectedSubcategory.value = null
    currentSubcategory.value = null
  }
  currentPage.value = 1
  fetchListings()
})
</script>

<style scoped>
.hero-gradient {
  background: linear-gradient(160deg, #0a1628 0%, #1a3a5c 40%, #1565c0 100%);
}
</style>
