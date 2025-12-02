<template>
  <div class="home-page">
    <!-- Hero Section - Modern Minimal Design -->
    <section class="hero-section">
      <v-container>
        <v-row align="center" class="hero-row">
          <v-col cols="12" md="7" lg="6" class="hero-content-col">
            <div class="hero-content">
              <!-- Main Heading - SEO Optimized -->
              <h1 class="hero-title">
                {{ locale === 'ar' ? 'اعثر على أفضل الخدمات' : 'Find the Best Services' }}
                <span class="hero-highlight">{{ locale === 'ar' ? 'بسهولة وأمان' : 'Easily & Safely' }}</span>
              </h1>

              <!-- Subtitle with semantic markup -->
              <p class="hero-subtitle">
                {{ locale === 'ar'
                  ? 'منصة طلبنا الشاملة تربطك بآلاف مزودي الخدمات المحليين. ابحث، تواصل، واحصل على ما تحتاجه في دقائق.'
                  : 'Talabna platform connects you with thousands of local service providers. Search, connect, and get what you need in minutes.'
                }}
              </p>

              <!-- Search Box with Schema Markup -->
              <div class="hero-search-wrapper">
                <v-card class="search-card" elevation="4" rounded="xl">
                  <v-card-text class="pa-2">
                    <v-row dense align="center">
                      <v-col cols="12" sm="8">
                        <v-text-field
                          v-model="searchQuery"
                          :placeholder="locale === 'ar' ? 'ابحث عن خدمة...' : 'Search for services...'"
                          prepend-inner-icon="mdi-magnify"
                          variant="solo-filled"
                          flat
                          density="comfortable"
                          hide-details
                          single-line
                          class="search-input"
                          @keyup.enter="doSearch"
                        />
                      </v-col>
                      <v-col cols="12" sm="4">
                        <v-btn
                          color="primary"
                          size="large"
                          block
                          elevation="0"
                          rounded="xl"
                          class="search-btn"
                          @click="doSearch"
                        >
                          <v-icon start>mdi-magnify</v-icon>
                          {{ locale === 'ar' ? 'بحث' : 'Search' }}
                        </v-btn>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </v-card>
              </div>

              <!-- Trust Indicators -->
              <div class="hero-trust-indicators">
                <div class="trust-item">
                  <v-icon size="20" color="success">mdi-shield-check</v-icon>
                  <span>{{ locale === 'ar' ? 'آمن وموثوق' : 'Safe & Secure' }}</span>
                </div>
                <div class="trust-item">
                  <v-icon size="20" color="success">mdi-check-circle</v-icon>
                  <span>{{ locale === 'ar' ? 'مستخدمين مُوثّقين' : 'Verified Users' }}</span>
                </div>
                <div class="trust-item">
                  <v-icon size="20" color="success">mdi-lightning-bolt</v-icon>
                  <span>{{ locale === 'ar' ? 'استجابة سريعة' : 'Fast Response' }}</span>
                </div>
              </div>
            </div>
          </v-col>

          <!-- Hero Visual - Modern Illustration -->
          <v-col cols="12" md="5" lg="6" class="d-none d-md-flex hero-visual-col">
            <div class="hero-visual">
              <div class="visual-card visual-card-1">
                <v-icon size="40" color="primary">mdi-account-group</v-icon>
                <div class="visual-text">
                  <div class="visual-number">{{ formatNumber(stats.total_users || 15000) }}+</div>
                  <div class="visual-label">{{ locale === 'ar' ? 'مستخدم نشط' : 'Active Users' }}</div>
                </div>
              </div>
              <div class="visual-card visual-card-2">
                <v-icon size="40" color="success">mdi-clipboard-check</v-icon>
                <div class="visual-text">
                  <div class="visual-number">{{ formatNumber(stats.total_listings || 8500) }}+</div>
                  <div class="visual-label">{{ locale === 'ar' ? 'خدمة متاحة' : 'Services' }}</div>
                </div>
              </div>
              <div class="visual-card visual-card-3">
                <v-icon size="40" color="warning">mdi-star</v-icon>
                <div class="visual-text">
                  <div class="visual-number">4.8</div>
                  <div class="visual-label">{{ locale === 'ar' ? 'تقييم المستخدمين' : 'User Rating' }}</div>
                </div>
              </div>
              <div class="hero-graphic">
                <v-icon size="200" color="primary" class="graphic-icon">mdi-handshake-outline</v-icon>
              </div>
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

    <!-- Browse by Location Section -->
    <section class="locations-section py-12" id="locations">
      <v-container>
        <div class="section-header mb-8">
          <h2 class="text-h4 font-weight-bold mb-2">
            {{ locale === 'ar' ? 'تصفح حسب الموقع' : 'Browse by Location' }}
          </h2>
          <p class="text-body-1 text-medium-emphasis">
            {{ locale === 'ar' ? 'اعثر على الخدمات المتاحة في منطقتك' : 'Find services available in your area' }}
          </p>
        </div>

        <v-row v-if="!loadingLocations">
          <v-col v-for="country in countries" :key="country.id" cols="12" sm="6" md="4" lg="3">
            <v-card
              class="location-card h-100"
              variant="outlined"
              :to="`/services/${country.id}/${country.slug}`"
            >
              <v-img
                :src="country.flag || '/storage/countryFlag/placeholder-flag.jpg'"
                height="120"
                cover
                class="location-flag"
              >
                <template v-slot:error>
                  <div class="d-flex align-center justify-center h-100 bg-grey-lighten-3">
                    <v-icon size="48" color="grey">mdi-earth</v-icon>
                  </div>
                </template>
              </v-img>
              <v-card-text class="text-center">
                <h3 class="text-subtitle-1 font-weight-bold mb-1">
                  {{ locale === 'ar' ? country.name : country.name_en }}
                </h3>
                <p class="text-caption text-medium-emphasis mb-2">
                  {{ formatNumber(country.listings_count) }} {{ locale === 'ar' ? 'إعلان' : 'Listings' }}
                </p>
                <div class="d-flex flex-wrap justify-center gap-1">
                  <v-chip
                    v-for="city in (country.top_cities || []).slice(0, 3)"
                    :key="city.id"
                    size="x-small"
                    :to="`/services/${country.id}/${country.slug}/${city.id}/${city.slug}`"
                    class="city-chip"
                  >
                    {{ locale === 'ar' ? city.name : city.name_en }}
                  </v-chip>
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
        <v-row v-else>
          <v-col v-for="n in 4" :key="n" cols="12" sm="6" md="4" lg="3">
            <v-skeleton-loader type="card" />
          </v-col>
        </v-row>

        <!-- View All Locations Button -->
        <div class="text-center mt-6">
          <v-btn color="primary" variant="outlined" size="large" to="/browse">
            {{ locale === 'ar' ? 'عرض جميع المواقع' : 'View All Locations' }}
            <v-icon end>mdi-arrow-left</v-icon>
          </v-btn>
        </div>
      </v-container>
    </section>

    <!-- Featured Services -->
    <section class="featured-section py-12">
      <v-container>
        <div class="section-header mb-8">
          <h2 class="text-h4 font-weight-bold text-center mb-2">
            {{ locale === 'ar' ? 'الخدمات المميزة' : 'Featured Services' }}
          </h2>
          <p class="text-body-1 text-medium-emphasis text-center">
            {{ locale === 'ar' ? 'اكتشف أفضل الخدمات المميزة من مستخدمينا' : 'Discover our top-rated premium services with excellent reviews' }}
          </p>
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
        <div class="section-header mb-8">
          <h2 class="text-h4 font-weight-bold text-center mb-2">
            {{ locale === 'ar' ? 'أحدث الخدمات' : 'Latest Services' }}
          </h2>
          <p class="text-body-1 text-medium-emphasis text-center">
            {{ locale === 'ar' ? 'تحقق من أحدث الخدمات المضافة إلى منصتنا' : 'Check out the newest services added to our platform' }}
          </p>
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
    <section class="popular-section py-12">
      <v-container>
        <div class="section-header mb-8">
          <h2 class="text-h4 font-weight-bold text-center mb-2">
            {{ locale === 'ar' ? 'الخدمات الأكثر مشاهدة' : 'Popular Services' }}
          </h2>
          <p class="text-body-1 text-medium-emphasis text-center">
            {{ locale === 'ar' ? 'اكتشف الخدمات الأكثر مشاهدة على منصتنا' : 'Discover the most viewed services on our platform' }}
          </p>
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
        <v-card class="app-download-card pa-12 text-center" elevation="0">
          <v-row align="center" justify="center">
            <v-col cols="12" md="10" lg="8">
              <v-icon size="80" color="primary" class="mb-6">mdi-cellphone-download</v-icon>
              <h2 class="text-h3 font-weight-bold mb-4">
                {{ locale === 'ar' ? 'حمل تطبيق طلبنا' : 'Download Talabna App' }}
              </h2>
              <p class="text-h6 text-medium-emphasis mb-8">
                {{ locale === 'ar'
                  ? 'احصل على التجربة الكاملة مع تطبيقنا. انشر الخدمات، أدر إعلاناتك، استقبل الإشعارات، وتواصل مع مزودي الخدمات أينما كنت.'
                  : 'Get the full experience with our mobile app. Post services, manage your listings, receive notifications, and connect with service providers on the go.'
                }}
              </p>
              <v-btn color="primary" size="x-large" href="https://play.google.com/store/apps/details?id=com.talabna.talabna" target="_blank">
                <v-icon start size="28">mdi-google-play</v-icon>
                {{ locale === 'ar' ? 'حمل من Google Play' : 'Download on Google Play' }}
              </v-btn>
            </v-col>
          </v-row>
        </v-card>
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
const countries = ref([])
const featured = ref([])
const latest = ref([])
const popular = ref([])
const stats = ref({})
const activeTab = ref('all')
const loadingFeatured = ref(true)
const loadingLatest = ref(true)
const loadingPopular = ref(true)
const loadingLocations = ref(true)

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

const fetchCountries = async () => {
  loadingLocations.value = true
  try {
    const response = await fetch('/api/public/countries')
    if (!response.ok) return
    const data = await response.json()
    countries.value = Array.isArray(data.countries) ? data.countries : []
  } catch (error) {
    console.error('Error fetching countries:', error)
    countries.value = []
  } finally {
    loadingLocations.value = false
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
  fetchCountries()
  fetchFeatured()
  fetchLatest()
  fetchPopular()
  fetchStats()
})
</script>

<style scoped>
/* Modern Hero Section - No Gradient, Clean Design */
.hero-section {
  background: rgb(var(--v-theme-background));
  padding: 80px 0 60px;
  min-height: 500px;
  position: relative;
  overflow: hidden;
}

.hero-row {
  min-height: 500px;
}

.hero-content-col {
  display: flex;
  align-items: center;
}

.hero-content {
  width: 100%;
  padding: 20px 0;
}

/* Typography - SEO Optimized */
.hero-title {
  font-size: clamp(2rem, 5vw, 3.5rem);
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 1.5rem;
  color: rgb(var(--v-theme-on-background));
  letter-spacing: -0.02em;
}

.hero-highlight {
  display: block;
  color: rgb(var(--v-theme-primary));
  margin-top: 0.25rem;
}

.hero-subtitle {
  font-size: 1.125rem;
  line-height: 1.7;
  color: rgba(var(--v-theme-on-background), 0.7);
  margin-bottom: 2.5rem;
  max-width: 560px;
}

/* Search Card - Modern Clean Style */
.hero-search-wrapper {
  margin-bottom: 2rem;
}

.search-card {
  background: rgb(var(--v-theme-surface)) !important;
  border: 2px solid rgba(var(--v-theme-primary), 0.1);
  box-shadow: 0 8px 32px rgba(var(--v-theme-primary), 0.08) !important;
  transition: all 0.3s ease;
}

.search-card:hover {
  border-color: rgba(var(--v-theme-primary), 0.3);
  box-shadow: 0 12px 48px rgba(var(--v-theme-primary), 0.12) !important;
}

.search-input :deep(.v-field) {
  background: transparent !important;
  box-shadow: none !important;
}

.search-btn {
  height: 48px !important;
  font-weight: 600;
}

/* Trust Indicators */
.hero-trust-indicators {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.trust-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: rgba(var(--v-theme-on-background), 0.7);
  font-size: 0.875rem;
  font-weight: 500;
}

/* Hero Visual Cards - Floating Stats */
.hero-visual-col {
  align-items: center;
  justify-content: center;
}

.hero-visual {
  position: relative;
  width: 100%;
  height: 500px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.visual-card {
  position: absolute;
  background: rgb(var(--v-theme-surface));
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  border-radius: 20px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  animation: float-card 6s ease-in-out infinite;
}

.visual-card:hover {
  transform: translateY(-8px) !important;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
}

.visual-card-1 {
  top: 10%;
  left: 10%;
  z-index: 3;
  animation-delay: 0s;
}

.visual-card-2 {
  top: 45%;
  right: 5%;
  z-index: 2;
  animation-delay: 1s;
}

.visual-card-3 {
  bottom: 15%;
  left: 15%;
  z-index: 3;
  animation-delay: 2s;
}

@keyframes float-card {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-15px); }
}

.visual-text {
  display: flex;
  flex-direction: column;
}

.visual-number {
  font-size: 1.75rem;
  font-weight: 800;
  color: rgb(var(--v-theme-on-background));
  line-height: 1;
}

.visual-label {
  font-size: 0.75rem;
  color: rgba(var(--v-theme-on-background), 0.6);
  margin-top: 0.25rem;
  font-weight: 500;
}

.hero-graphic {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 1;
  opacity: 0.06;
}

.graphic-icon {
  filter: blur(2px);
}

/* Responsive Design */
@media (max-width: 960px) {
  .hero-section {
    padding: 60px 0 40px;
  }

  .hero-row {
    min-height: auto;
  }

  .hero-title {
    font-size: 2rem;
  }

  .hero-subtitle {
    font-size: 1rem;
  }

  .trust-item {
    font-size: 0.8rem;
  }
}

.stats-section {
  background: transparent;
}

.stat-card {
  border-radius: 16px !important;
  background: rgb(var(--v-theme-surface));
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
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
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  background: rgb(var(--v-theme-surface));
}

.category-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1) !important;
  border-color: rgb(var(--v-theme-primary)) !important;
}

.app-banner {
  background: transparent;
}

.app-download-card {
  background: rgb(var(--v-theme-surface)) !important;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  border-radius: 24px !important;
  transition: all 0.3s ease;
}

.app-download-card:hover {
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1) !important;
  border-color: rgb(var(--v-theme-primary));
}

.section-header {
  text-align: center;
  max-width: 700px;
  margin: 0 auto;
}

.home-page {
  background: rgb(var(--v-theme-background));
}

/* Location Cards */
.locations-section {
  background: rgba(var(--v-theme-primary), 0.02);
}

.location-card {
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 16px !important;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  background: rgb(var(--v-theme-surface));
  overflow: hidden;
}

.location-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1) !important;
  border-color: rgb(var(--v-theme-primary)) !important;
}

.location-flag {
  border-bottom: 1px solid rgba(var(--v-theme-on-surface), 0.08);
}

.city-chip {
  cursor: pointer;
  transition: all 0.2s ease;
}

.city-chip:hover {
  background: rgb(var(--v-theme-primary)) !important;
  color: white !important;
}
</style>
