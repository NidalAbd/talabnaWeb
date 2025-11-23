<template>
  <div class="home-page">
    <!-- Hero Section -->
    <section class="hero-section">
      <v-container>
        <v-row align="center" justify="center">
          <v-col cols="12" md="8" class="text-center">
            <h1 class="text-h2 text-md-h1 font-weight-bold text-white mb-4">
              {{ locale === 'ar' ? 'أكبر سوق للإعلانات المبوبة' : 'The Largest Classified Marketplace' }}
            </h1>
            <p class="text-h6 text-white-darken-1 mb-8">
              {{ locale === 'ar'
                ? 'بيع واشتري السيارات، العقارات، الهواتف، والمزيد بسهولة وأمان'
                : 'Buy and sell cars, real estate, phones, and more easily and safely'
              }}
            </p>

            <!-- Search Box -->
            <v-card class="search-card mx-auto" max-width="700" elevation="8">
              <v-card-text class="pa-4">
                <v-row dense>
                  <v-col cols="12" sm="8">
                    <v-text-field
                      v-model="searchQuery"
                      :placeholder="locale === 'ar' ? 'ابحث عن سيارات، هواتف، وظائف...' : 'Search for cars, phones, jobs...'"
                      prepend-inner-icon="mdi-magnify"
                      variant="solo-filled"
                      density="comfortable"
                      hide-details
                      single-line
                      @keyup.enter="doSearch"
                    />
                  </v-col>
                  <v-col cols="12" sm="4">
                    <v-btn
                      color="primary"
                      size="large"
                      block
                      @click="doSearch"
                      class="h-100"
                    >
                      <v-icon start>mdi-magnify</v-icon>
                      {{ locale === 'ar' ? 'بحث' : 'Search' }}
                    </v-btn>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Stats Section -->
    <section class="stats-section py-8">
      <v-container>
        <v-row>
          <v-col v-for="stat in statsItems" :key="stat.key" cols="6" md="3">
            <v-card class="stat-card text-center pa-6" variant="flat">
              <v-icon :color="stat.color" size="48" class="mb-3">{{ stat.icon }}</v-icon>
              <div class="text-h4 font-weight-bold mb-1">{{ formatNumber(stats[stat.key] || 0) }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ stat.label }}</div>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Categories Section -->
    <section class="categories-section py-12">
      <v-container>
        <div class="section-header text-center mb-8">
          <h2 class="text-h4 font-weight-bold mb-2">
            {{ locale === 'ar' ? 'تصفح حسب التصنيف' : 'Browse by Category' }}
          </h2>
          <p class="text-body-1 text-medium-emphasis">
            {{ locale === 'ar' ? 'اختر التصنيف الذي يناسبك' : 'Choose the category that suits you' }}
          </p>
        </div>

        <v-row>
          <v-col v-for="cat in categories" :key="cat.id" cols="6" sm="4" md="2">
            <v-card
              class="category-card text-center pa-4 h-100"
              variant="outlined"
              :to="`/category/${cat.id}/${cat.slug}`"
            >
              <v-avatar :color="getCategoryColor(cat.id)" size="64" class="mb-3">
                <v-icon size="32" color="white">{{ getCategoryIcon(cat.id) }}</v-icon>
              </v-avatar>
              <h3 class="text-subtitle-1 font-weight-bold mb-1">
                {{ locale === 'ar' ? cat.name : cat.name_en }}
              </h3>
              <p class="text-caption text-medium-emphasis">
                {{ formatNumber(cat.posts_count) }} {{ locale === 'ar' ? 'إعلان' : 'ads' }}
              </p>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Featured Listings -->
    <section class="featured-section py-12 bg-surface-variant">
      <v-container>
        <div class="section-header d-flex justify-space-between align-center mb-6">
          <div>
            <h2 class="text-h5 font-weight-bold">
              <v-icon color="amber" class="mr-2">mdi-star</v-icon>
              {{ locale === 'ar' ? 'الإعلانات المميزة' : 'Featured Listings' }}
            </h2>
          </div>
          <v-btn variant="text" color="primary" to="/browse?badge=premium">
            {{ locale === 'ar' ? 'عرض الكل' : 'View All' }}
            <v-icon end>mdi-arrow-left</v-icon>
          </v-btn>
        </div>

        <v-row v-if="!loadingFeatured">
          <v-col v-for="listing in featured" :key="listing.id" cols="12" sm="6" md="4" lg="3">
            <listing-card :listing="listing" :locale="locale" />
          </v-col>
        </v-row>
        <v-row v-else>
          <v-col v-for="n in 4" :key="n" cols="12" sm="6" md="4" lg="3">
            <v-skeleton-loader type="card" />
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Latest Listings by Category -->
    <section class="latest-section py-12">
      <v-container>
        <div class="section-header d-flex justify-space-between align-center mb-6">
          <h2 class="text-h5 font-weight-bold">
            <v-icon color="primary" class="mr-2">mdi-clock-outline</v-icon>
            {{ locale === 'ar' ? 'أحدث الإعلانات' : 'Latest Listings' }}
          </h2>
          <v-btn variant="text" color="primary" to="/browse">
            {{ locale === 'ar' ? 'عرض الكل' : 'View All' }}
            <v-icon end>mdi-arrow-left</v-icon>
          </v-btn>
        </div>

        <!-- Category Tabs -->
        <v-tabs v-model="activeTab" color="primary" class="mb-6">
          <v-tab value="all">{{ locale === 'ar' ? 'الكل' : 'All' }}</v-tab>
          <v-tab v-for="cat in categories.slice(0, 5)" :key="cat.id" :value="cat.id">
            {{ locale === 'ar' ? cat.name : cat.name_en }}
          </v-tab>
        </v-tabs>

        <v-row v-if="!loadingLatest">
          <v-col v-for="listing in latest" :key="listing.id" cols="12" sm="6" md="4" lg="3">
            <listing-card :listing="listing" :locale="locale" />
          </v-col>
        </v-row>
        <v-row v-else>
          <v-col v-for="n in 8" :key="n" cols="12" sm="6" md="4" lg="3">
            <v-skeleton-loader type="card" />
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Popular Listings -->
    <section class="popular-section py-12 bg-surface-variant">
      <v-container>
        <div class="section-header d-flex justify-space-between align-center mb-6">
          <h2 class="text-h5 font-weight-bold">
            <v-icon color="error" class="mr-2">mdi-fire</v-icon>
            {{ locale === 'ar' ? 'الأكثر مشاهدة' : 'Most Viewed' }}
          </h2>
          <v-btn variant="text" color="primary" to="/browse?sort_by=view_count">
            {{ locale === 'ar' ? 'عرض الكل' : 'View All' }}
            <v-icon end>mdi-arrow-left</v-icon>
          </v-btn>
        </div>

        <v-row v-if="!loadingPopular">
          <v-col v-for="listing in popular" :key="listing.id" cols="12" sm="6" md="4" lg="3">
            <listing-card :listing="listing" :locale="locale" />
          </v-col>
        </v-row>
        <v-row v-else>
          <v-col v-for="n in 4" :key="n" cols="12" sm="6" md="4" lg="3">
            <v-skeleton-loader type="card" />
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-16">
      <v-container>
        <v-row justify="center">
          <v-col cols="12" md="8" class="text-center">
            <h2 class="text-h4 font-weight-bold text-white mb-4">
              {{ locale === 'ar' ? 'هل لديك شيء للبيع؟' : 'Have something to sell?' }}
            </h2>
            <p class="text-h6 text-white-darken-1 mb-8">
              {{ locale === 'ar'
                ? 'أضف إعلانك الآن ووصل لآلاف المشترين'
                : 'Post your ad now and reach thousands of buyers'
              }}
            </p>
            <v-btn color="white" size="x-large" href="/login" class="text-primary">
              <v-icon start>mdi-plus</v-icon>
              {{ locale === 'ar' ? 'أضف إعلان مجاني' : 'Post Free Ad' }}
            </v-btn>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Download App Section -->
    <section class="app-section py-12">
      <v-container>
        <v-row align="center">
          <v-col cols="12" md="6">
            <h2 class="text-h4 font-weight-bold mb-4">
              {{ locale === 'ar' ? 'حمل تطبيق طلبنا' : 'Download Talabna App' }}
            </h2>
            <p class="text-body-1 text-medium-emphasis mb-6">
              {{ locale === 'ar'
                ? 'تصفح الإعلانات وتواصل مع البائعين من هاتفك في أي وقت'
                : 'Browse ads and connect with sellers from your phone anytime'
              }}
            </p>
            <div class="d-flex gap-4 flex-wrap">
              <v-btn variant="outlined" size="large" href="#">
                <v-icon start size="28">mdi-google-play</v-icon>
                <div class="text-left">
                  <div class="text-caption">{{ locale === 'ar' ? 'متوفر على' : 'Get it on' }}</div>
                  <div class="font-weight-bold">Google Play</div>
                </div>
              </v-btn>
              <v-btn variant="outlined" size="large" href="#">
                <v-icon start size="28">mdi-apple</v-icon>
                <div class="text-left">
                  <div class="text-caption">{{ locale === 'ar' ? 'حمل من' : 'Download on' }}</div>
                  <div class="font-weight-bold">App Store</div>
                </div>
              </v-btn>
            </div>
          </v-col>
          <v-col cols="12" md="6" class="text-center">
            <v-img src="/storage/photos/app-mockup.png" max-height="400" contain />
          </v-col>
        </v-row>
      </v-container>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { useSeo } from '@/composables/useSeo'
import ListingCard from '@/components/ListingCard.vue'

const router = useRouter()
const appStore = useAppStore()
const { updateMeta, setOrganizationSchema } = useSeo()

// State
const searchQuery = ref('')
const categories = ref([])
const featured = ref([])
const latest = ref([])
const popular = ref([])
const stats = ref({})
const activeTab = ref('all')
const loadingFeatured = ref(true)
const loadingLatest = ref(true)
const loadingPopular = ref(true)

const locale = computed(() => appStore.locale)

const statsItems = computed(() => [
  { key: 'total_listings', icon: 'mdi-bullhorn', color: 'primary', label: locale.value === 'ar' ? 'إعلان' : 'Listings' },
  { key: 'total_users', icon: 'mdi-account-group', color: 'success', label: locale.value === 'ar' ? 'مستخدم' : 'Users' },
  { key: 'total_categories', icon: 'mdi-shape', color: 'warning', label: locale.value === 'ar' ? 'تصنيف' : 'Categories' },
  { key: 'listings_today', icon: 'mdi-clock-outline', color: 'info', label: locale.value === 'ar' ? 'إعلان اليوم' : 'Today' },
])

const categoryIcons = {
  1: 'mdi-cellphone',
  2: 'mdi-car',
  3: 'mdi-briefcase',
  4: 'mdi-home-city',
  5: 'mdi-shape',
}

const categoryColors = {
  1: 'blue',
  2: 'red',
  3: 'green',
  4: 'purple',
  5: 'orange',
}

const getCategoryIcon = (id) => categoryIcons[id] || 'mdi-folder'
const getCategoryColor = (id) => categoryColors[id] || 'grey'

const formatNumber = (num) => new Intl.NumberFormat().format(num)

const doSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ name: 'search', query: { q: searchQuery.value } })
  }
}

// Fetch functions with defensive coding
const fetchCategories = async () => {
  try {
    const response = await fetch('/api/public/categories')
    if (!response.ok) return
    const data = await response.json()
    categories.value = Array.isArray(data.categories) ? data.categories : []
    appStore.setCategories(categories.value)
  } catch (error) {
    console.error('Error fetching categories:', error)
    categories.value = []
  }
}

const fetchFeatured = async () => {
  loadingFeatured.value = true
  try {
    const response = await fetch('/api/public/featured')
    if (!response.ok) return
    const data = await response.json()
    featured.value = Array.isArray(data.featured) ? data.featured : []
  } catch (error) {
    console.error('Error fetching featured:', error)
    featured.value = []
  } finally {
    loadingFeatured.value = false
  }
}

const fetchLatest = async (categoryId = null) => {
  loadingLatest.value = true
  try {
    let url = '/api/public/latest'
    if (categoryId && categoryId !== 'all') {
      url += `?category_id=${categoryId}`
    }
    const response = await fetch(url)
    if (!response.ok) return
    const data = await response.json()
    latest.value = Array.isArray(data.latest) ? data.latest : []
  } catch (error) {
    console.error('Error fetching latest:', error)
    latest.value = []
  } finally {
    loadingLatest.value = false
  }
}

const fetchPopular = async () => {
  loadingPopular.value = true
  try {
    const response = await fetch('/api/public/popular')
    if (!response.ok) return
    const data = await response.json()
    popular.value = Array.isArray(data.popular) ? data.popular : []
  } catch (error) {
    console.error('Error fetching popular:', error)
    popular.value = []
  } finally {
    loadingPopular.value = false
  }
}

const fetchStats = async () => {
  try {
    const response = await fetch('/api/public/stats')
    if (!response.ok) return
    const data = await response.json()
    stats.value = data || {}
  } catch (error) {
    console.error('Error fetching stats:', error)
    stats.value = {}
  }
}

// Watch tab changes
watch(activeTab, (newTab) => {
  fetchLatest(newTab)
})

// Initialize
onMounted(() => {
  // Set SEO
  updateMeta({
    title: 'طلبنا - Talabna | أكبر سوق للإعلانات المبوبة',
    description: 'أكبر منصة للإعلانات المبوبة في الوطن العربي. بيع واشتري السيارات، العقارات، الهواتف، الوظائف والمزيد بسهولة وأمان.',
    keywords: 'إعلانات مبوبة, سيارات للبيع, عقارات, وظائف, هواتف, بيع وشراء, classified ads, cars for sale',
  })
  setOrganizationSchema()

  // Fetch data
  fetchCategories()
  fetchFeatured()
  fetchLatest()
  fetchPopular()
  fetchStats()
})
</script>

<style scoped>
.hero-section {
  background: linear-gradient(135deg, #5035FF 0%, #7C6AFF 100%);
  padding: 80px 0;
  min-height: 500px;
  display: flex;
  align-items: center;
}

.search-card {
  border-radius: 16px !important;
}

.stat-card {
  border-radius: 16px !important;
  background: rgb(var(--v-theme-surface));
  transition: transform 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
}

.category-card {
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 16px !important;
}

.category-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
  border-color: rgb(var(--v-theme-primary)) !important;
}

.bg-surface-variant {
  background: rgba(var(--v-theme-on-surface), 0.03);
}

.cta-section {
  background: linear-gradient(135deg, #5035FF 0%, #7C6AFF 100%);
}

.app-section {
  background: rgb(var(--v-theme-surface));
}
</style>
