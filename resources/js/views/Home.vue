<template>
  <div class="home-page">
    <!-- Hero Section -->
    <section class="hero-section">
      <v-container>
        <v-row align="center" justify="center">
          <v-col cols="12" md="6">
            <div class="hero-content">
              <h1 class="text-h3 text-md-h2 font-weight-bold text-white mb-4">
                {{ locale === 'ar' ? 'اعثر على الخدمات وقدمها مع طلبنا' : 'Find & Offer Services with Talabna' }}
              </h1>
              <p class="text-h6 text-white-darken-1 mb-8">
                {{ locale === 'ar'
                  ? 'تواصل مع مزودي الخدمات المحليين أو قدم خدماتك لآلاف المستخدمين. منصتك الشاملة لجميع احتياجات الخدمات.'
                  : 'Connect with local service providers or offer your services to thousands of users. Your one-stop platform for all service needs.'
                }}
              </p>

              <!-- Search Box -->
              <v-card class="search-card" elevation="8">
                <v-card-text class="pa-4">
                  <v-row dense>
                    <v-col cols="12" sm="8">
                      <v-text-field
                        v-model="searchQuery"
                        :placeholder="locale === 'ar' ? 'ابحث عن الخدمات...' : 'Find services...'"
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
                        {{ locale === 'ar' ? 'بحث' : 'Search' }}
                      </v-btn>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-card>

              <div class="hero-buttons mt-6">
                <v-btn color="white" size="large" variant="flat" class="text-primary me-3" href="#categories">
                  {{ locale === 'ar' ? 'استكشف الخدمات' : 'Explore Services' }}
                </v-btn>
                <v-btn color="white" size="large" variant="outlined" href="/register">
                  {{ locale === 'ar' ? 'انضم الآن' : 'Join Now' }}
                </v-btn>
              </div>
            </div>
          </v-col>
          <v-col cols="12" md="6" class="text-center d-none d-md-block">
            <!-- Hero SVG Illustration -->
            <div class="hero-illustration">
              <v-icon size="200" color="white" class="opacity-20">mdi-handshake</v-icon>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Stats Section -->
    <section class="stats-section py-10">
      <v-container>
        <v-row>
          <v-col v-for="stat in statsItems" :key="stat.key" cols="6" md="3">
            <v-card class="stat-card text-center pa-6" variant="flat">
              <div class="text-h3 font-weight-bold text-primary mb-2">{{ formatNumber(stats[stat.key] || 0) }}</div>
              <div class="text-body-1 text-medium-emphasis">{{ stat.label }}</div>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Categories Section -->
    <section class="categories-section py-12" id="categories">
      <v-container>
        <div class="section-header mb-8">
          <h2 class="text-h4 font-weight-bold mb-2">
            {{ locale === 'ar' ? 'تصفح تصنيفات الخدمات' : 'Browse Service Categories' }}
          </h2>
          <p class="text-body-1 text-medium-emphasis">
            {{ locale === 'ar' ? 'استكشف مجموعتنا الواسعة من تصنيفات الخدمات واعثر على ما تبحث عنه بالضبط.' : 'Explore our wide range of service categories and find exactly what you\'re looking for.' }}
          </p>
        </div>

        <v-row>
          <v-col v-for="cat in categories" :key="cat.id" cols="6" sm="4" md="2">
            <v-card
              class="category-card text-center pa-4 h-100"
              variant="outlined"
              :to="`/category/${cat.id}/${cat.slug}`"
            >
              <v-avatar :color="getCategoryColor(cat.id)" size="70" class="mb-3">
                <v-icon size="35" color="white">{{ getCategoryIcon(cat.id) }}</v-icon>
              </v-avatar>
              <h3 class="text-subtitle-1 font-weight-bold mb-1">
                {{ locale === 'ar' ? cat.name : cat.name_en }}
              </h3>
              <p class="text-caption text-medium-emphasis">
                {{ formatNumber(cat.posts_count) }} {{ locale === 'ar' ? 'خدمة' : 'Services' }}
              </p>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Featured Services -->
    <section class="featured-section py-12 bg-surface-light">
      <v-container>
        <div class="section-header d-flex justify-space-between align-center mb-6">
          <div>
            <h2 class="text-h5 font-weight-bold">
              <v-icon color="purple" class="mr-2">mdi-diamond-stone</v-icon>
              {{ locale === 'ar' ? 'الخدمات المميزة' : 'Featured Services' }}
            </h2>
            <p class="text-body-2 text-medium-emphasis mt-1">
              {{ locale === 'ar' ? 'اكتشف أفضل الخدمات المميزة من مستخدمينا' : 'Discover our top-rated premium services with excellent reviews' }}
            </p>
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

    <!-- Latest Services by Category -->
    <section class="latest-section py-12">
      <v-container>
        <div class="section-header d-flex justify-space-between align-center mb-6">
          <div>
            <h2 class="text-h5 font-weight-bold">
              <v-icon color="primary" class="mr-2">mdi-clock-outline</v-icon>
              {{ locale === 'ar' ? 'أحدث الخدمات' : 'Latest Services' }}
            </h2>
            <p class="text-body-2 text-medium-emphasis mt-1">
              {{ locale === 'ar' ? 'تحقق من أحدث الخدمات المضافة إلى منصتنا' : 'Check out the newest services added to our platform' }}
            </p>
          </div>
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

    <!-- Popular Services -->
    <section class="popular-section py-12 bg-surface-light">
      <v-container>
        <div class="section-header d-flex justify-space-between align-center mb-6">
          <div>
            <h2 class="text-h5 font-weight-bold">
              <v-icon color="error" class="mr-2">mdi-fire</v-icon>
              {{ locale === 'ar' ? 'الخدمات الأكثر مشاهدة' : 'Popular Services' }}
            </h2>
            <p class="text-body-2 text-medium-emphasis mt-1">
              {{ locale === 'ar' ? 'اكتشف الخدمات الأكثر مشاهدة على منصتنا' : 'Discover the most viewed services on our platform' }}
            </p>
          </div>
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

    <!-- App Download Banner -->
    <section class="app-banner py-16">
      <v-container>
        <v-row align="center" justify="center">
          <v-col cols="12" md="8" class="text-center">
            <h2 class="text-h4 font-weight-bold text-white mb-4">
              {{ locale === 'ar' ? 'حمل تطبيق طلبنا' : 'Download Talabna App' }}
            </h2>
            <p class="text-h6 text-white-darken-1 mb-8">
              {{ locale === 'ar'
                ? 'احصل على التجربة الكاملة مع تطبيقنا. انشر الخدمات، أدر إعلاناتك، استقبل الإشعارات، وتواصل مع مزودي الخدمات أينما كنت.'
                : 'Get the full experience with our mobile app. Post services, manage your listings, receive notifications, and connect with service providers on the go.'
              }}
            </p>
            <v-btn color="white" size="x-large" href="https://play.google.com/store/apps/details?id=com.talabna.talabna" target="_blank" class="text-primary">
              <v-icon start size="28">mdi-google-play</v-icon>
              {{ locale === 'ar' ? 'حمل من Google Play' : 'Download on Google Play' }}
            </v-btn>
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
  { key: 'total_listings', label: locale.value === 'ar' ? 'خدمات نشطة' : 'Active Services' },
  { key: 'total_users', label: locale.value === 'ar' ? 'مستخدمين مسجلين' : 'Registered Users' },
  { key: 'total_categories', label: locale.value === 'ar' ? 'خدمات مميزة' : 'Premium Services' },
  { key: 'listings_today', label: locale.value === 'ar' ? 'معاملات' : 'Transactions' },
])

const categoryIcons = {
  1: 'mdi-cellphone',      // Mobile & Devices
  2: 'mdi-car',            // Vehicles
  3: 'mdi-briefcase',      // Jobs
  4: 'mdi-home-city',      // Real Estate
  5: 'mdi-tools',          // General Services
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
    title: 'طلبنا - Talabna | منصة الخدمات الشاملة',
    description: 'طلبنا - منصتك الشاملة للعثور على الخدمات وتقديمها. تواصل مع مزودي الخدمات أو قدم خدماتك لآلاف المستخدمين.',
    keywords: 'خدمات, إعلانات مبوبة, هواتف, سيارات, عقارات, وظائف, طلبنا, talabna, services',
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
  background: linear-gradient(135deg, rgb(var(--v-theme-primary)) 0%, rgb(var(--v-theme-primary-darken-1)) 100%);
  padding: 100px 0;
  min-height: 600px;
  display: flex;
  align-items: center;
  position: relative;
  overflow: hidden;
}

.hero-section::before {
  content: '';
  position: absolute;
  top: -150px;
  right: -100px;
  width: 400px;
  height: 400px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.08);
  animation: float 6s ease-in-out infinite;
}

.hero-section::after {
  content: '';
  position: absolute;
  bottom: -150px;
  left: -100px;
  width: 500px;
  height: 500px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.05);
  animation: float 8s ease-in-out infinite reverse;
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-20px); }
}

.hero-content {
  position: relative;
  z-index: 2;
}

.search-card {
  border-radius: 16px !important;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15) !important;
  backdrop-filter: blur(10px);
}

.stats-section {
  background: transparent;
}

.stat-card {
  border-radius: 16px !important;
  background: rgb(var(--v-theme-surface));
  border: 1px solid rgba(var(--v-theme-primary), 0.1);
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08) !important;
  border-color: rgb(var(--v-theme-primary));
}

.category-card {
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 16px !important;
  border: 2px solid transparent;
  background: rgb(var(--v-theme-surface));
}

.category-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1) !important;
  border-color: rgb(var(--v-theme-primary)) !important;
}

.bg-surface-light {
  background: transparent;
}

.app-banner {
  background: linear-gradient(135deg, rgb(var(--v-theme-primary)) 0%, rgb(var(--v-theme-primary-darken-1)) 100%);
  position: relative;
  overflow: hidden;
}

.app-banner::before {
  content: '';
  position: absolute;
  top: -200px;
  right: -200px;
  width: 500px;
  height: 500px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.08);
}

.app-banner::after {
  content: '';
  position: absolute;
  bottom: -200px;
  left: -200px;
  width: 500px;
  height: 500px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.05);
}

.section-header {
  text-align: center;
  max-width: 700px;
  margin: 0 auto;
}

.home-page {
  background: rgb(var(--v-theme-background));
}
</style>
