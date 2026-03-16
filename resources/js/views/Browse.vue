<template>
  <div class="browse-page">
    <!-- Hero -->
    <section class="hero-gradient py-12">
      <div class="container">
        <h1 class="text-h3 font-weight-bold text-white mb-2">
          {{ locale === 'ar' ? 'تصفح الإعلانات' : 'Browse Listings' }}
        </h1>
        <p class="text-h6 text-white-darken-1">
          {{ pagination.total }} {{ locale === 'ar' ? 'إعلان متاح' : 'listings available' }}
        </p>
      </div>
    </section>

    <div class="container py-8">
      <div class="row">
        <!-- Mobile Filter Button -->
        <div class="col-12 d-md-none mb-4">
          <button class="btn btn-outline btn-block" @click="showMobileFilters = !showMobileFilters">
            <i class="mdi mdi-filter"></i>
            {{ locale === 'ar' ? 'الفلاتر' : 'Filters' }}
            <i :class="showMobileFilters ? 'mdi mdi-chevron-up' : 'mdi mdi-chevron-down'"></i>
          </button>
        </div>

        <!-- Filters Sidebar -->
        <div class="col-12 col-md-3" :class="{ 'd-none d-md-block': !showMobileFilters }">
          <div class="card filters-card sticky-filters">
            <div class="card-header d-flex align-center">
              <i class="mdi mdi-filter"></i>
              {{ locale === 'ar' ? 'الفلاتر' : 'Filters' }}
              <div class="flex-1"></div>
              <button class="btn btn-text" @click="clearFilters">
                {{ locale === 'ar' ? 'مسح' : 'Clear' }}
              </button>
            </div>
            <hr>
            <div class="card-body">
              <!-- Category -->
              <div class="mb-4">
                <label class="text-subtitle-2 font-weight-bold mb-2 d-block">
                  {{ locale === 'ar' ? 'التصنيف' : 'Category' }}
                </label>
                <select class="form-select" v-model="filters.category_id" @change="onCategoryChange(filters.category_id)">
                  <option value="">{{ locale === 'ar' ? 'الكل' : 'All' }}</option>
                  <option v-for="opt in categoryOptions" :key="opt.value" :value="opt.value">{{ opt.title }}</option>
                </select>
              </div>

              <!-- Subcategory -->
              <div class="mb-4">
                <label class="text-subtitle-2 font-weight-bold mb-2 d-block">
                  {{ locale === 'ar' ? 'التصنيف الفرعي' : 'Subcategory' }}
                </label>
                <select class="form-select" v-model="filters.subcategory_id" :disabled="!filters.category_id" @change="fetchListings()">
                  <option value="">{{ locale === 'ar' ? 'الكل' : 'All' }}</option>
                  <option v-for="opt in subcategoryOptions" :key="opt.value" :value="opt.value">{{ opt.title }}</option>
                </select>
              </div>

              <!-- Country -->
              <div class="mb-4">
                <label class="text-subtitle-2 font-weight-bold mb-2 d-block">
                  {{ locale === 'ar' ? 'الدولة' : 'Country' }}
                </label>
                <select class="form-select" v-model="filters.country_id" @change="onCountryChange(filters.country_id)">
                  <option value="">{{ locale === 'ar' ? 'الكل' : 'All' }}</option>
                  <option v-for="opt in countryOptions" :key="opt.value" :value="opt.value">{{ opt.title }}</option>
                </select>
              </div>

              <!-- City -->
              <div class="mb-4">
                <label class="text-subtitle-2 font-weight-bold mb-2 d-block">
                  {{ locale === 'ar' ? 'المدينة' : 'City' }}
                </label>
                <select class="form-select" v-model="filters.city_id" :disabled="!filters.country_id" @change="fetchListings()">
                  <option value="">{{ locale === 'ar' ? 'الكل' : 'All' }}</option>
                  <option v-for="opt in cityOptions" :key="opt.value" :value="opt.value">{{ opt.title }}</option>
                </select>
              </div>

              <!-- Price Range -->
              <div class="mb-4">
                <label class="text-subtitle-2 font-weight-bold mb-2 d-block">
                  {{ locale === 'ar' ? 'نطاق السعر' : 'Price Range' }}
                </label>
                <div class="row">
                  <div class="col-6">
                    <input
                      class="form-input"
                      v-model="filters.min_price"
                      :placeholder="locale === 'ar' ? 'من' : 'Min'"
                      type="number"
                      @change="fetchListings()"
                    />
                  </div>
                  <div class="col-6">
                    <input
                      class="form-input"
                      v-model="filters.max_price"
                      :placeholder="locale === 'ar' ? 'إلى' : 'Max'"
                      type="number"
                      @change="fetchListings()"
                    />
                  </div>
                </div>
              </div>

              <!-- Badge -->
              <div class="mb-4">
                <label class="text-subtitle-2 font-weight-bold mb-2 d-block">
                  {{ locale === 'ar' ? 'نوع الإعلان' : 'Listing Type' }}
                </label>
                <div class="chip-group">
                  <button
                    v-for="badge in badgeTypes.filter(b => !b.is_default)"
                    :key="badge.id"
                    class="chip chip-filter"
                    :class="{ active: filters.badge === badge.name_ar }"
                    @click="filters.badge = filters.badge === badge.name_ar ? null : badge.name_ar; fetchListings()"
                  >
                    <i :class="badge.icon" style="font-size: 16px;"></i>
                    {{ locale === 'ar' ? badge.name_ar : badge.name_en }}
                  </button>
                </div>
              </div>

              <button class="btn btn-primary btn-block" @click="fetchListings()">
                <i class="mdi mdi-filter"></i>
                {{ locale === 'ar' ? 'تطبيق' : 'Apply' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Listings -->
        <div class="col-12 col-md-9">
          <!-- Sort & View -->
          <div class="d-flex flex-wrap justify-space-between align-center mb-6 gap-4">
            <select
              class="form-select"
              v-model="filters.sort_by"
              style="max-width: 200px"
              @change="fetchListings()"
            >
              <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.title }}</option>
            </select>
            <div class="btn-toggle">
              <button :class="['btn', viewMode === 'grid' ? 'active' : '']" @click="viewMode = 'grid'">
                <i class="mdi mdi-view-grid"></i>
              </button>
              <button :class="['btn', viewMode === 'list' ? 'active' : '']" @click="viewMode = 'list'">
                <i class="mdi mdi-view-list"></i>
              </button>
            </div>
          </div>

          <!-- Loading -->
          <div v-if="loading" class="text-center py-16">
            <div class="spinner spinner-lg"></div>
            <p class="mt-4 text-medium-emphasis">{{ locale === 'ar' ? 'جاري التحميل...' : 'Loading...' }}</p>
          </div>

          <!-- Grid View -->
          <div v-else-if="viewMode === 'grid' && listings.length > 0" class="row">
            <div v-for="listing in listings" :key="listing.id" class="col-12 col-sm-6 col-lg-4">
              <listing-card :listing="listing" :locale="locale" />
            </div>
          </div>

          <!-- List View -->
          <div v-else-if="viewMode === 'list' && listings.length > 0">
            <router-link v-for="listing in listings" :key="listing.id" :to="`/listing/${listing.id}`" class="card mb-4 listing-list-card">
              <div class="row no-gutters">
                <div class="col-4 col-md-3">
                  <img :src="getPhotoUrl(listing)" loading="lazy" class="img-cover" style="height: 180px; width: 100%; object-fit: cover;">
                  <div v-if="!getPhotoUrl(listing)" class="fill-height bg-grey-lighten-3 d-flex align-center justify-center" style="height: 180px;">
                    <i class="mdi mdi-image-off" style="font-size: 48px; color: grey;"></i>
                  </div>
                </div>
                <div class="col-8 col-md-9">
                  <div class="card-body">
                    <div class="d-flex align-center gap-2 mb-2">
                      <span
                        v-if="listing.badge && !listing.badge.is_default"
                        class="chip"
                        :style="{ backgroundColor: listing.badge.color, color: '#fff' }"
                      >
                        <i :class="listing.badge.icon" style="font-size: 12px;"></i>
                        {{ locale === 'ar' ? listing.badge.name_ar : listing.badge.name_en }}
                      </span>
                      <span class="chip">{{ getLocalizedName(listing.category) }}</span>
                    </div>
                    <h3 class="text-h6 font-weight-bold mb-2">{{ listing.title }}</h3>
                    <p class="text-body-2 text-medium-emphasis mb-2 listing-description">{{ listing.description }}</p>
                    <div class="d-flex justify-space-between align-center">
                      <span v-if="listing.price" class="text-h6 font-weight-bold text-primary">${{ listing.price }}</span>
                      <span v-else class="text-body-2 text-medium-emphasis">{{ locale === 'ar' ? 'السعر عند الاتصال' : 'Contact for price' }}</span>
                      <span class="text-caption text-medium-emphasis">{{ getLocalizedName(listing.city) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </router-link>
          </div>

          <!-- No Results -->
          <div v-else-if="!loading && listings.length === 0" class="card text-center py-16">
            <i class="mdi mdi-magnify" style="font-size: 80px; color: grey;"></i>
            <h3 class="text-h5 text-medium-emphasis mt-4">{{ locale === 'ar' ? 'لا توجد نتائج' : 'No results found' }}</h3>
            <p class="text-body-2 text-medium-emphasis">{{ locale === 'ar' ? 'جرب تعديل الفلاتر' : 'Try adjusting your filters' }}</p>
            <button class="btn btn-primary mt-4" @click="clearFilters">{{ locale === 'ar' ? 'مسح الفلاتر' : 'Clear Filters' }}</button>
          </div>

          <!-- Infinite Scroll Trigger -->
          <div v-if="listings.length > 0" ref="scrollTrigger" class="scroll-trigger">
            <div v-if="loadingMore" class="text-center py-8">
              <div class="spinner spinner-lg"></div>
              <p class="mt-2 text-caption text-medium-emphasis">{{ locale === 'ar' ? 'جاري تحميل المزيد...' : 'Loading more...' }}</p>
            </div>
            <div v-else-if="hasMore" class="text-center py-4">
              <button class="btn btn-text" @click="loadMore">
                {{ locale === 'ar' ? 'تحميل المزيد' : 'Load More' }}
              </button>
            </div>
            <div v-else class="text-center py-4 text-caption text-medium-emphasis">
              {{ locale === 'ar' ? 'تم عرض جميع النتائج' : 'All results loaded' }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { useSeo } from '@/composables/useSeo'
import ListingCard from '@/components/ListingCard.vue'

const route = useRoute()
const router = useRouter()
const appStore = useAppStore()
const { updateMeta } = useSeo()

// State
const listings = ref([])
const categories = ref([])
const subcategories = ref([])
const countries = ref([])
const cities = ref([])
const badgeTypes = ref([])
const loading = ref(false)
const showMobileFilters = ref(false)
const loadingMore = ref(false)
const loadingCategories = ref(false)
const loadingSubcategories = ref(false)
const loadingCountries = ref(false)
const loadingCities = ref(false)
const viewMode = ref('grid')
const currentPage = ref(1)
const scrollTrigger = ref(null)
let observer = null

const pagination = ref({
  total: 0,
  per_page: 20,
  current_page: 1,
  last_page: 1,
})

const filters = ref({
  category_id: null,
  subcategory_id: null,
  country_id: null,
  city_id: null,
  min_price: null,
  max_price: null,
  badge: null,
  sort_by: 'created_at',
})

const locale = computed(() => appStore.locale)

import { getLocalizedName as _getLocalizedName, ensureAbsoluteUrl } from '@/utils/helpers'

const getLocalizedName = (item) => {
  if (!item) return ''
  if (item.name && typeof item.name === 'object') return _getLocalizedName(item.name, locale.value)
  return locale.value === 'ar' ? item.name : (item.name_en || item.name || '')
}

const getPhotoUrl = (listing) => {
  if (listing.photos && listing.photos.length > 0) {
    const src = listing.photos[0].src || listing.photos[0].url
    return ensureAbsoluteUrl(src)
  }
  return null
}

const hasMore = computed(() => {
  return pagination.value.current_page < pagination.value.last_page
})

const categoryOptions = computed(() => {
  if (!Array.isArray(categories.value)) return []
  return categories.value.map(c => ({
    title: `${locale.value === 'ar' ? c.name : c.name_en} (${c.posts_count})`,
    value: c.id,
  }))
})

const subcategoryOptions = computed(() => {
  if (!Array.isArray(subcategories.value)) return []
  return subcategories.value.map(s => ({
    title: `${locale.value === 'ar' ? s.name : s.name_en} (${s.posts_count})`,
    value: s.id,
  }))
})

const countryOptions = computed(() => {
  if (!Array.isArray(countries.value)) return []
  return countries.value.map(c => {
    // Handle both string and object name formats
    let nameAr = c.name
    let nameEn = c.name_en || c.name
    if (c.name && typeof c.name === 'object') {
      nameAr = c.name.ar || c.name.en || ''
      nameEn = c.name.en || c.name.ar || ''
    }
    return {
      title: locale.value === 'ar' ? nameAr : nameEn,
      value: c.id,
    }
  })
})

const cityOptions = computed(() => {
  if (!Array.isArray(cities.value)) return []
  return cities.value.map(c => {
    // Handle both string and object name formats
    let nameAr = c.name
    let nameEn = c.name_en || c.name
    if (c.name && typeof c.name === 'object') {
      nameAr = c.name.ar || c.name.en || ''
      nameEn = c.name.en || c.name.ar || ''
    }
    return {
      title: locale.value === 'ar' ? nameAr : nameEn,
      value: c.id,
    }
  })
})

const sortOptions = computed(() => [
  { title: locale.value === 'ar' ? 'الأحدث' : 'Newest', value: 'created_at' },
  { title: locale.value === 'ar' ? 'السعر: من الأقل' : 'Price: Low to High', value: 'price_asc' },
  { title: locale.value === 'ar' ? 'السعر: من الأعلى' : 'Price: High to Low', value: 'price_desc' },
  { title: locale.value === 'ar' ? 'الأكثر مشاهدة' : 'Most Viewed', value: 'view_count' },
])

// Fetch functions
const fetchListings = async (append = false) => {
  if (append) {
    loadingMore.value = true
  } else {
    loading.value = true
    currentPage.value = 1
  }

  // Sync filters to URL (for shareable links)
  const queryParams = {}
  if (filters.value.category_id) queryParams.category_id = filters.value.category_id
  if (filters.value.country_id) queryParams.country_id = filters.value.country_id
  if (filters.value.city_id) queryParams.city_id = filters.value.city_id
  if (filters.value.badge) queryParams.badge = filters.value.badge
  if (filters.value.min_price) queryParams.min_price = filters.value.min_price
  if (filters.value.max_price) queryParams.max_price = filters.value.max_price
  if (filters.value.sort_by !== 'created_at') queryParams.sort_by = filters.value.sort_by
  router.replace({ query: queryParams }).catch(() => {})

  try {
    const params = new URLSearchParams({
      page: currentPage.value,
      per_page: 20,
    })

    if (filters.value.category_id) params.append('category_id', filters.value.category_id)
    if (filters.value.subcategory_id) params.append('subcategory_id', filters.value.subcategory_id)
    if (filters.value.country_id) params.append('country_id', filters.value.country_id)
    if (filters.value.city_id) params.append('city_id', filters.value.city_id)
    if (filters.value.min_price) params.append('min_price', filters.value.min_price)
    if (filters.value.max_price) params.append('max_price', filters.value.max_price)
    if (filters.value.badge) params.append('badge', filters.value.badge)

    if (filters.value.sort_by === 'price_asc') {
      params.append('sort_by', 'price')
      params.append('sort_order', 'asc')
    } else if (filters.value.sort_by === 'price_desc') {
      params.append('sort_by', 'price')
      params.append('sort_order', 'desc')
    } else {
      params.append('sort_by', filters.value.sort_by)
    }

    const response = await fetch(`/api/public/listings?${params}`)
    if (!response.ok) throw new Error('Failed to fetch')
    const data = await response.json()

    const newListings = Array.isArray(data.listings) ? data.listings : []

    if (append) {
      listings.value = [...listings.value, ...newListings]
    } else {
      listings.value = newListings
    }

    pagination.value = data.pagination || pagination.value
  } catch (error) {
    console.error('Error:', error)
    if (!append) listings.value = []
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const loadMore = async () => {
  if (loadingMore.value || !hasMore.value) return
  currentPage.value++
  await fetchListings(true)
}

// Intersection Observer for infinite scroll
const setupIntersectionObserver = () => {
  if (observer) observer.disconnect()

  observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting && hasMore.value && !loadingMore.value && !loading.value) {
        loadMore()
      }
    },
    { rootMargin: '100px' }
  )

  if (scrollTrigger.value) {
    observer.observe(scrollTrigger.value)
  }
}

watch(scrollTrigger, (newVal) => {
  if (newVal) setupIntersectionObserver()
})

const fetchCategories = async () => {
  loadingCategories.value = true
  try {
    const response = await fetch('/api/public/categories')
    if (!response.ok) return
    const data = await response.json()
    categories.value = Array.isArray(data.categories) ? data.categories : []
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loadingCategories.value = false
  }
}

const fetchSubcategories = async (categoryId) => {
  if (!categoryId) {
    subcategories.value = []
    return
  }
  loadingSubcategories.value = true
  try {
    const response = await fetch(`/api/public/categories/${categoryId}/subcategories`)
    if (!response.ok) return
    const data = await response.json()
    subcategories.value = Array.isArray(data.subcategories) ? data.subcategories : []
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loadingSubcategories.value = false
  }
}

const fetchCountries = async () => {
  loadingCountries.value = true
  try {
    const response = await fetch('/api/public/countries')
    if (!response.ok) return
    const data = await response.json()
    countries.value = Array.isArray(data.countries) ? data.countries : []
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loadingCountries.value = false
  }
}

const fetchBadgeTypes = async () => {
  try {
    const response = await fetch('/api/public/badge-types')
    if (!response.ok) return
    const data = await response.json()
    badgeTypes.value = Array.isArray(data.badges) ? data.badges : []
  } catch (error) {
    console.error('Error fetching badge types:', error)
  }
}

const fetchCities = async (countryId) => {
  if (!countryId) {
    cities.value = []
    return
  }
  loadingCities.value = true
  try {
    const response = await fetch(`/api/public/countries/${countryId}/cities`)
    if (!response.ok) return
    const data = await response.json()
    cities.value = Array.isArray(data.cities) ? data.cities : []
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loadingCities.value = false
  }
}

const onCategoryChange = (categoryId) => {
  filters.value.subcategory_id = null
  fetchSubcategories(categoryId)
  fetchListings()
}

const onCountryChange = (countryId) => {
  filters.value.city_id = null
  fetchCities(countryId)
  fetchListings()
}

const clearFilters = () => {
  filters.value = {
    category_id: null,
    subcategory_id: null,
    country_id: null,
    city_id: null,
    min_price: null,
    max_price: null,
    badge: null,
    sort_by: 'created_at',
  }
  subcategories.value = []
  cities.value = []
  currentPage.value = 1
  fetchListings()
}

onMounted(() => {
  updateMeta({
    title: locale.value === 'ar' ? 'تصفح الإعلانات - طلبنا' : 'Browse Listings - Talabna',
    description: locale.value === 'ar'
      ? 'تصفح جميع الإعلانات المبوبة. سيارات، عقارات، هواتف، وظائف والمزيد.'
      : 'Browse all classified ads. Cars, real estate, phones, jobs and more.',
  })

  // Parse URL query params for shareable/bookmarkable filters
  if (route.query.category_id) filters.value.category_id = parseInt(route.query.category_id)
  if (route.query.country_id) filters.value.country_id = parseInt(route.query.country_id)
  if (route.query.city_id) filters.value.city_id = parseInt(route.query.city_id)
  if (route.query.badge) filters.value.badge = route.query.badge
  if (route.query.min_price) filters.value.min_price = route.query.min_price
  if (route.query.max_price) filters.value.max_price = route.query.max_price
  if (route.query.sort_by) filters.value.sort_by = route.query.sort_by

  fetchCategories()
  fetchCountries()
  fetchBadgeTypes()
  fetchListings()
})

onUnmounted(() => {
  if (observer) observer.disconnect()
})
</script>

<style scoped>
.browse-page {
  background: rgb(var(--v-theme-background));
  min-height: 100vh;
}

.hero-gradient {
  background: linear-gradient(160deg, #0a1628 0%, #1a3a5c 40%, #1565c0 100%);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.filters-card {
  border-radius: 20px !important;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  background: rgb(var(--v-theme-surface));
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04) !important;
}

.sticky-filters {
  position: sticky;
  top: 90px;
}

.listing-list-card {
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 20px !important;
  overflow: hidden;
  background: rgb(var(--v-theme-surface));
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  text-decoration: none;
  color: inherit;
  display: block;
}

.listing-list-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12) !important;
  border-color: rgb(var(--v-theme-primary)) !important;
}

.listing-description {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.5;
}

.scroll-trigger {
  margin-top: 40px;
}
</style>
