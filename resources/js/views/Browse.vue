<template>
  <div class="browse-page">
    <!-- Hero -->
    <section class="hero-gradient py-12">
      <v-container>
        <h1 class="text-h3 font-weight-bold text-white mb-2">
          {{ locale === 'ar' ? 'تصفح الإعلانات' : 'Browse Listings' }}
        </h1>
        <p class="text-h6 text-white-darken-1">
          {{ pagination.total }} {{ locale === 'ar' ? 'إعلان متاح' : 'listings available' }}
        </p>
      </v-container>
    </section>

    <v-container class="py-8">
      <v-row>
        <!-- Filters Sidebar -->
        <v-col cols="12" md="3">
          <v-card class="filters-card sticky-filters" variant="outlined">
            <v-card-title class="d-flex align-center">
              <v-icon start>mdi-filter</v-icon>
              {{ locale === 'ar' ? 'الفلاتر' : 'Filters' }}
              <v-spacer />
              <v-btn variant="text" size="small" @click="clearFilters">
                {{ locale === 'ar' ? 'مسح' : 'Clear' }}
              </v-btn>
            </v-card-title>
            <v-divider />
            <v-card-text>
              <!-- Category -->
              <div class="mb-4">
                <label class="text-subtitle-2 font-weight-bold mb-2 d-block">
                  {{ locale === 'ar' ? 'التصنيف' : 'Category' }}
                </label>
                <v-select
                  v-model="filters.category_id"
                  :items="categoryOptions"
                  item-title="title"
                  item-value="value"
                  variant="outlined"
                  density="compact"
                  hide-details
                  clearable
                  :loading="loadingCategories"
                  @update:model-value="onCategoryChange"
                />
              </div>

              <!-- Subcategory -->
              <div class="mb-4">
                <label class="text-subtitle-2 font-weight-bold mb-2 d-block">
                  {{ locale === 'ar' ? 'التصنيف الفرعي' : 'Subcategory' }}
                </label>
                <v-select
                  v-model="filters.subcategory_id"
                  :items="subcategoryOptions"
                  item-title="title"
                  item-value="value"
                  variant="outlined"
                  density="compact"
                  hide-details
                  clearable
                  :disabled="!filters.category_id"
                  :loading="loadingSubcategories"
                  @update:model-value="fetchListings"
                />
              </div>

              <!-- Country -->
              <div class="mb-4">
                <label class="text-subtitle-2 font-weight-bold mb-2 d-block">
                  {{ locale === 'ar' ? 'الدولة' : 'Country' }}
                </label>
                <v-select
                  v-model="filters.country_id"
                  :items="countryOptions"
                  item-title="title"
                  item-value="value"
                  variant="outlined"
                  density="compact"
                  hide-details
                  clearable
                  :loading="loadingCountries"
                  @update:model-value="onCountryChange"
                />
              </div>

              <!-- City -->
              <div class="mb-4">
                <label class="text-subtitle-2 font-weight-bold mb-2 d-block">
                  {{ locale === 'ar' ? 'المدينة' : 'City' }}
                </label>
                <v-select
                  v-model="filters.city_id"
                  :items="cityOptions"
                  item-title="title"
                  item-value="value"
                  variant="outlined"
                  density="compact"
                  hide-details
                  clearable
                  :disabled="!filters.country_id"
                  :loading="loadingCities"
                  @update:model-value="fetchListings"
                />
              </div>

              <!-- Price Range -->
              <div class="mb-4">
                <label class="text-subtitle-2 font-weight-bold mb-2 d-block">
                  {{ locale === 'ar' ? 'نطاق السعر' : 'Price Range' }}
                </label>
                <v-row dense>
                  <v-col cols="6">
                    <v-text-field
                      v-model="filters.min_price"
                      :label="locale === 'ar' ? 'من' : 'Min'"
                      type="number"
                      variant="outlined"
                      density="compact"
                      hide-details
                      @change="fetchListings"
                    />
                  </v-col>
                  <v-col cols="6">
                    <v-text-field
                      v-model="filters.max_price"
                      :label="locale === 'ar' ? 'إلى' : 'Max'"
                      type="number"
                      variant="outlined"
                      density="compact"
                      hide-details
                      @change="fetchListings"
                    />
                  </v-col>
                </v-row>
              </div>

              <!-- Badge -->
              <div class="mb-4">
                <label class="text-subtitle-2 font-weight-bold mb-2 d-block">
                  {{ locale === 'ar' ? 'نوع الإعلان' : 'Listing Type' }}
                </label>
                <v-chip-group v-model="filters.badge" column @update:model-value="fetchListings">
                  <v-chip filter value="ماسي" color="purple">
                    <v-icon start size="16">mdi-diamond-stone</v-icon>
                    {{ locale === 'ar' ? 'ماسي' : 'Diamond' }}
                  </v-chip>
                  <v-chip filter value="ذهبي" color="amber">
                    <v-icon start size="16">mdi-star</v-icon>
                    {{ locale === 'ar' ? 'ذهبي' : 'Gold' }}
                  </v-chip>
                </v-chip-group>
              </div>

              <v-btn color="primary" block @click="fetchListings">
                <v-icon start>mdi-filter</v-icon>
                {{ locale === 'ar' ? 'تطبيق' : 'Apply' }}
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Listings -->
        <v-col cols="12" md="9">
          <!-- Sort & View -->
          <div class="d-flex flex-wrap justify-space-between align-center mb-6 gap-4">
            <v-select
              v-model="filters.sort_by"
              :items="sortOptions"
              :label="locale === 'ar' ? 'ترتيب حسب' : 'Sort by'"
              variant="outlined"
              density="compact"
              hide-details
              style="max-width: 200px"
              @update:model-value="fetchListings"
            />
            <v-btn-toggle v-model="viewMode" mandatory variant="outlined" divided>
              <v-btn value="grid" icon="mdi-view-grid" />
              <v-btn value="list" icon="mdi-view-list" />
            </v-btn-toggle>
          </div>

          <!-- Loading -->
          <div v-if="loading" class="text-center py-16">
            <v-progress-circular indeterminate color="primary" size="64" />
            <p class="mt-4 text-medium-emphasis">{{ locale === 'ar' ? 'جاري التحميل...' : 'Loading...' }}</p>
          </div>

          <!-- Grid View -->
          <v-row v-else-if="viewMode === 'grid' && listings.length > 0">
            <v-col v-for="listing in listings" :key="listing.id" cols="12" sm="6" lg="4">
              <listing-card :listing="listing" :locale="locale" />
            </v-col>
          </v-row>

          <!-- List View -->
          <div v-else-if="viewMode === 'list' && listings.length > 0">
            <v-card v-for="listing in listings" :key="listing.id" class="mb-4 listing-list-card" variant="outlined" :to="`/listing/${listing.id}`">
              <v-row no-gutters>
                <v-col cols="4" md="3">
                  <v-img :src="getPhotoUrl(listing)" height="180" cover>
                    <template v-slot:error>
                      <v-row class="fill-height ma-0 bg-grey-lighten-3" align="center" justify="center">
                        <v-icon size="48" color="grey">mdi-image-off</v-icon>
                      </v-row>
                    </template>
                  </v-img>
                </v-col>
                <v-col cols="8" md="9">
                  <v-card-text>
                    <div class="d-flex align-center gap-2 mb-2">
                      <v-chip v-if="listing.have_badge !== 'عادي'" :color="listing.have_badge === 'ماسي' ? 'purple' : 'amber'" size="x-small">
                        {{ listing.have_badge }}
                      </v-chip>
                      <v-chip size="x-small" variant="tonal">{{ getLocalizedName(listing.category) }}</v-chip>
                    </div>
                    <h3 class="text-h6 font-weight-bold mb-2">{{ listing.title }}</h3>
                    <p class="text-body-2 text-medium-emphasis mb-2 listing-description">{{ listing.description }}</p>
                    <div class="d-flex justify-space-between align-center">
                      <span v-if="listing.price" class="text-h6 font-weight-bold text-primary">${{ listing.price }}</span>
                      <span v-else class="text-body-2 text-medium-emphasis">{{ locale === 'ar' ? 'السعر عند الاتصال' : 'Contact for price' }}</span>
                      <span class="text-caption text-medium-emphasis">{{ getLocalizedName(listing.city) }}</span>
                    </div>
                  </v-card-text>
                </v-col>
              </v-row>
            </v-card>
          </div>

          <!-- No Results -->
          <v-card v-else-if="!loading && listings.length === 0" class="text-center py-16" variant="flat">
            <v-icon size="80" color="grey">mdi-magnify</v-icon>
            <h3 class="text-h5 text-medium-emphasis mt-4">{{ locale === 'ar' ? 'لا توجد نتائج' : 'No results found' }}</h3>
            <p class="text-body-2 text-medium-emphasis">{{ locale === 'ar' ? 'جرب تعديل الفلاتر' : 'Try adjusting your filters' }}</p>
            <v-btn color="primary" class="mt-4" @click="clearFilters">{{ locale === 'ar' ? 'مسح الفلاتر' : 'Clear Filters' }}</v-btn>
          </v-card>

          <!-- Infinite Scroll Trigger -->
          <div v-if="listings.length > 0" ref="scrollTrigger" class="scroll-trigger">
            <div v-if="loadingMore" class="text-center py-8">
              <v-progress-circular indeterminate color="primary" size="32" />
              <p class="mt-2 text-caption text-medium-emphasis">{{ locale === 'ar' ? 'جاري تحميل المزيد...' : 'Loading more...' }}</p>
            </div>
            <div v-else-if="hasMore" class="text-center py-4">
              <v-btn variant="text" color="primary" @click="loadMore">
                {{ locale === 'ar' ? 'تحميل المزيد' : 'Load More' }}
              </v-btn>
            </div>
            <div v-else class="text-center py-4 text-caption text-medium-emphasis">
              {{ locale === 'ar' ? 'تم عرض جميع النتائج' : 'All results loaded' }}
            </div>
          </div>
        </v-col>
      </v-row>
    </v-container>
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
const loading = ref(false)
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

// Get localized name from category/subcategory/city/country object
const getLocalizedName = (item) => {
  if (!item) return ''
  // If name is an object with ar/en keys
  if (item.name && typeof item.name === 'object') {
    return locale.value === 'ar' ? (item.name.ar || item.name.en || '') : (item.name.en || item.name.ar || '')
  }
  // If name is a string directly
  if (typeof item.name === 'string') {
    return locale.value === 'ar' ? item.name : (item.name_en || item.name)
  }
  return ''
}

// Ensure URL starts with / for absolute path
const ensureAbsoluteUrl = (url) => {
  if (!url) return null
  if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/')) {
    return url
  }
  // Handle paths stored as 'photos/posts/...' (from public disk) - need /storage prefix
  if (url.startsWith('photos/')) {
    return '/storage/' + url
  }
  // Handle paths stored as 'storage/photos/...' - just add leading slash
  return '/' + url
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
    title: 'تصفح الإعلانات - طلبنا | Browse Listings',
    description: 'تصفح جميع الإعلانات المبوبة. سيارات، عقارات، هواتف، وظائف والمزيد.',
  })

  // Parse query params
  if (route.query.category_id) filters.value.category_id = parseInt(route.query.category_id)
  if (route.query.badge) filters.value.badge = route.query.badge

  fetchCategories()
  fetchCountries()
  fetchListings()
})

onUnmounted(() => {
  if (observer) observer.disconnect()
})
</script>

<style scoped>
.hero-gradient {
  background: linear-gradient(135deg, #5035FF 0%, #7C6AFF 100%);
}

.filters-card {
  border-radius: 16px !important;
}

.sticky-filters {
  position: sticky;
  top: 80px;
}

.listing-list-card {
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 16px !important;
  overflow: hidden;
}

.listing-list-card:hover {
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
}

.listing-description {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
