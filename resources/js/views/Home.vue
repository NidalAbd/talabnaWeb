<template>
  <div class="home-page">
    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
      </div>
      <v-container>
        <div class="hero-content">
          <div class="hero-badge">
            <v-icon size="16" class="mr-1">mdi-shield-check</v-icon>
            {{ locale === 'ar' ? 'منصة موثوقة للإعلانات المبوبة' : 'Trusted Classified Ads Platform' }}
          </div>
          <h1 class="hero-title">
            {{ locale === 'ar' ? 'اعثر على ما تحتاجه' : 'Find What You Need' }}
            <br><span class="text-gradient">{{ locale === 'ar' ? 'بسهولة وأمان' : 'Easily & Safely' }}</span>
          </h1>
          <p class="hero-subtitle">
            {{ locale === 'ar'
              ? 'وظائف، سيارات، عقارات، أجهزة وخدمات متنوعة. تواصل مع آلاف المستخدمين في أكثر من 22 دولة عربية.'
              : 'Jobs, cars, real estate, electronics and services. Connect with thousands of users across 22+ Arab countries.'
            }}
          </p>
          <div class="hero-search">
            <div class="search-box">
              <v-icon class="search-icon">mdi-magnify</v-icon>
              <input
                v-model="searchQuery"
                :placeholder="locale === 'ar' ? 'ابحث عن سيارة، شقة، وظيفة...' : 'Search for car, apartment, job...'"
                @keyup.enter="doSearch"
              >
              <button class="search-btn" @click="doSearch">
                {{ locale === 'ar' ? 'بحث' : 'Search' }}
              </button>
            </div>
          </div>
          <div class="hero-buttons">
            <router-link to="/browse" class="btn-hero-primary">
              <v-icon size="20" class="mr-2">mdi-view-grid</v-icon>
              {{ locale === 'ar' ? 'تصفح الإعلانات' : 'Browse Listings' }}
            </router-link>
            <a href="https://play.google.com/store/apps/details?id=com.talabna.talabna" target="_blank" class="btn-hero-secondary">
              <v-icon size="20" class="mr-2">mdi-google-play</v-icon>
              {{ locale === 'ar' ? 'حمل التطبيق' : 'Get the App' }}
            </a>
          </div>
        </div>
        <div class="hero-stats">
          <div class="stat-item" v-for="stat in statsItems" :key="stat.key">
            <div class="stat-icon"><v-icon>{{ stat.icon }}</v-icon></div>
            <h3>{{ formatNumber(stats[stat.key] || 0) }}+</h3>
            <p>{{ stat.label }}</p>
          </div>
        </div>
      </v-container>
      <div class="hero-wave">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none"><path d="M0 40C240 80 480 0 720 40C960 80 1200 0 1440 40V80H0Z" fill="rgb(var(--v-theme-background))"/></svg>
      </div>
    </section>

    <!-- Categories Section -->
    <section class="section-categories py-12" id="categories">
      <v-container>
        <div class="section-header">
          <div>
            <h2 class="section-title">{{ locale === 'ar' ? 'تصفح التصنيفات' : 'Browse Categories' }}</h2>
            <p class="section-subtitle">{{ locale === 'ar' ? 'اكتشف ما تبحث عنه بالضبط' : 'Find exactly what you\'re looking for' }}</p>
          </div>
        </div>
        <v-row>
          <v-col v-for="cat in categories" :key="cat.id" cols="6" sm="4" md="3" lg="2">
            <router-link :to="`/category/${cat.id}/${cat.slug || ''}`" class="category-card-home text-decoration-none">
              <div class="cat-icon" :style="{ background: getCategoryColor(cat.id) + '18', color: getCategoryColor(cat.id) }">
                <v-icon :color="getCategoryColor(cat.id)">{{ getCategoryIcon(cat.id) }}</v-icon>
              </div>
              <h5>{{ locale === 'ar' ? cat.name : cat.name_en }}</h5>
              <span class="cat-count">{{ formatNumber(cat.posts_count || 0) }} {{ locale === 'ar' ? 'إعلان' : 'listings' }}</span>
            </router-link>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Featured Services -->
    <section class="section-featured py-12">
      <v-container>
        <div class="section-header">
          <div>
            <h2 class="section-title">{{ locale === 'ar' ? 'الإعلانات المميزة' : 'Featured Listings' }}</h2>
            <p class="section-subtitle">{{ locale === 'ar' ? 'إعلانات مختارة من مستخدمينا' : 'Handpicked listings from our users' }}</p>
          </div>
          <router-link to="/browse" class="btn-view-all">
            {{ locale === 'ar' ? 'عرض الكل' : 'View All' }} <v-icon size="18">mdi-arrow-right</v-icon>
          </router-link>
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
    <section class="section-latest py-12 bg-section">
      <v-container>
        <div class="section-header">
          <div>
            <h2 class="section-title">{{ locale === 'ar' ? 'أحدث الإعلانات' : 'Latest Listings' }}</h2>
            <p class="section-subtitle">{{ locale === 'ar' ? 'أحدث ما أُضيف إلى المنصة' : 'Recently added to the platform' }}</p>
          </div>
        </div>
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

    <!-- Browse by Location -->
    <section class="section-locations py-12" id="locations">
      <v-container>
        <div class="section-header">
          <div>
            <h2 class="section-title">{{ locale === 'ar' ? 'تصفح حسب الموقع' : 'Browse by Location' }}</h2>
            <p class="section-subtitle">{{ locale === 'ar' ? 'اعثر على الخدمات في منطقتك' : 'Find services in your area' }}</p>
          </div>
        </div>
        <v-row v-if="!loadingLocations">
          <v-col v-for="country in countries" :key="country.id" cols="6" sm="4" md="3" lg="2">
            <router-link :to="`/services/${country.id}/${country.slug}`" class="location-card-home text-decoration-none">
              <v-img :src="country.flag || '/storage/countryFlag/placeholder-flag.jpg'" height="80" cover class="location-flag-img">
                <template v-slot:error>
                  <div class="d-flex align-center justify-center h-100" style="background:#f0f4f8">
                    <v-icon size="32" color="grey">mdi-earth</v-icon>
                  </div>
                </template>
              </v-img>
              <div class="location-info">
                <h5>{{ locale === 'ar' ? country.name : country.name_en }}</h5>
                <span>{{ formatNumber(country.listings_count || 0) }} {{ locale === 'ar' ? 'إعلان' : 'listings' }}</span>
              </div>
            </router-link>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Popular Services -->
    <section class="section-popular py-12 bg-section">
      <v-container>
        <div class="section-header">
          <div>
            <h2 class="section-title">{{ locale === 'ar' ? 'الأكثر مشاهدة' : 'Most Viewed' }}</h2>
            <p class="section-subtitle">{{ locale === 'ar' ? 'الإعلانات الأكثر شعبية' : 'The most popular listings' }}</p>
          </div>
          <router-link to="/browse?sort_by=view_count" class="btn-view-all">
            {{ locale === 'ar' ? 'عرض الكل' : 'View All' }} <v-icon size="18">mdi-arrow-right</v-icon>
          </router-link>
        </div>
        <v-row v-if="!loadingPopular">
          <v-col v-for="listing in popular" :key="listing.id" cols="12" sm="6" md="4" lg="3">
            <listing-card :listing="listing" :locale="locale" />
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Download App CTA -->
    <section class="download-cta py-12">
      <v-container>
        <div class="cta-card">
          <v-row align="center">
            <v-col cols="12" md="7">
              <h2>{{ locale === 'ar' ? 'حمل تطبيق طلبنا' : 'Download Talabna App' }}</h2>
              <p>{{ locale === 'ar'
                ? 'انشر إعلاناتك، تواصل مع المستخدمين، واستقبل الإشعارات أينما كنت.'
                : 'Post listings, connect with users, and receive notifications on the go.'
              }}</p>
              <div class="cta-features">
                <span><v-icon size="16" class="mr-1">mdi-check-circle</v-icon>{{ locale === 'ar' ? 'مجاني' : 'Free' }}</span>
                <span><v-icon size="16" class="mr-1">mdi-check-circle</v-icon>{{ locale === 'ar' ? 'سهل الاستخدام' : 'Easy to use' }}</span>
                <span><v-icon size="16" class="mr-1">mdi-check-circle</v-icon>{{ locale === 'ar' ? 'إشعارات فورية' : 'Instant notifications' }}</span>
              </div>
              <a href="https://play.google.com/store/apps/details?id=com.talabna.talabna" target="_blank" class="btn-download">
                <v-icon class="mr-2">mdi-google-play</v-icon>
                Google Play
              </a>
            </v-col>
            <v-col cols="12" md="5" class="text-center d-none d-md-block">
              <v-icon size="140" style="opacity:0.2;color:#fff">mdi-cellphone-arrow-down</v-icon>
            </v-col>
          </v-row>
        </div>
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
import { getCategoryIcon, getCategoryColor, formatNumber } from '@/utils/helpers'

const router = useRouter()
const appStore = useAppStore()
const { updateMeta, setOrganizationSchema, setWebsiteSchema, setItemListSchema } = useSeo()

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
  { key: 'total_listings', icon: 'mdi-clipboard-list', label: locale.value === 'ar' ? 'خدمات نشطة' : 'Active Services' },
  { key: 'total_users', icon: 'mdi-account-group', label: locale.value === 'ar' ? 'مستخدمين مسجلين' : 'Registered Users' },
  { key: 'total_categories', icon: 'mdi-star-circle', label: locale.value === 'ar' ? 'خدمات مميزة' : 'Premium Services' },
  { key: 'listings_today', icon: 'mdi-trending-up', label: locale.value === 'ar' ? 'معاملات' : 'Transactions' },
])

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
    if (featured.value.length > 0) setItemListSchema(featured.value, 'Featured Services')
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
    title: 'طلبنا - Talabna | سوق الإعلانات المبوبة',
    description: 'طلبنا - منصتك الشاملة للعثور على الخدمات وتقديمها. وظائف، سيارات، عقارات، أجهزة وخدمات متنوعة. تواصل مع مزودي الخدمات أو قدم خدماتك لآلاف المستخدمين.',
    keywords: 'طلبنا, talabna, إعلانات مبوبة, وظائف, سيارات, عقارات, أجهزة, خدمات, classified ads, jobs, cars, real estate',
    url: 'https://talbna.cloud',
    type: 'website',
  })
  setOrganizationSchema()
  setWebsiteSchema()

  // Fetch all data in parallel
  Promise.all([
    fetchCategories(),
    fetchCountries(),
    fetchFeatured(),
    fetchLatest(),
    fetchPopular(),
    fetchStats(),
  ])
})
</script>

<style scoped>
/* Hero - Dark gradient with shapes */
.hero {
  background: linear-gradient(160deg, #0a1628 0%, #1a3a5c 40%, #1565c0 100%);
  color: #fff;
  position: relative;
  overflow: hidden;
  padding: 6rem 0 5rem;
}

.hero-bg-shapes { position: absolute; inset: 0; pointer-events: none; }
.shape { position: absolute; border-radius: 50%; }
.shape-1 { width: 500px; height: 500px; top: -200px; right: -100px; background: rgba(96,165,250,0.06); }
.shape-2 { width: 350px; height: 350px; bottom: -100px; left: -80px; background: rgba(167,139,250,0.05); }
.shape-3 { width: 200px; height: 200px; top: 40%; left: 60%; background: rgba(96,165,250,0.04); }

.hero-content { text-align: center; max-width: 720px; margin: 0 auto 3rem; position: relative; z-index: 1; }

.hero-badge {
  display: inline-flex; align-items: center; padding: 0.5rem 1.25rem;
  background: rgba(96,165,250,0.15); border: 1px solid rgba(96,165,250,0.25);
  border-radius: 100px; font-size: 0.85rem; font-weight: 500; color: #93c5fd;
  margin-bottom: 2rem;
}

.hero-title { font-size: 3.5rem; font-weight: 800; line-height: 1.15; margin-bottom: 1.5rem; letter-spacing: -0.03em; }
.text-gradient { background: linear-gradient(135deg, #60a5fa, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.hero-subtitle { font-size: 1.15rem; opacity: 0.75; margin-bottom: 2.5rem; max-width: 560px; margin-left: auto; margin-right: auto; line-height: 1.7; }

.hero-search {
  max-width: 600px; margin: 0 auto 2rem;
}

.search-box {
  display: flex; align-items: center; background: rgba(255,255,255,0.1);
  backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.15);
  border-radius: 16px; padding: 0.35rem 0.35rem 0.35rem 1.25rem; transition: all 0.3s;
}
.search-box:focus-within { background: rgba(255,255,255,0.18); border-color: rgba(255,255,255,0.3); }
.search-icon { color: rgba(255,255,255,0.5); margin-right: 0.75rem; }
.search-box input {
  flex: 1; background: none; border: none; outline: none; color: #fff; font-size: 1rem;
  padding: 0.75rem 0; min-width: 0;
}
.search-box input::placeholder { color: rgba(255,255,255,0.45); }
.search-btn {
  background: linear-gradient(135deg, #2563eb, #3b82f6); color: #fff;
  border: none; border-radius: 12px; padding: 0.75rem 1.75rem; font-weight: 600;
  font-size: 0.95rem; cursor: pointer; white-space: nowrap; transition: all 0.2s;
}
.search-btn:hover { box-shadow: 0 4px 20px rgba(37,99,235,0.4); transform: translateY(-1px); }

.hero-buttons { display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center; }

.btn-hero-primary {
  display: inline-flex; align-items: center; padding: 0.85rem 2rem;
  background: linear-gradient(135deg, #2563eb, #3b82f6); color: #fff !important;
  border-radius: 12px; font-weight: 600; font-size: 1rem; text-decoration: none;
  transition: all 0.2s; box-shadow: 0 4px 20px rgba(37,99,235,0.4);
}
.btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 28px rgba(37,99,235,0.5); }

.btn-hero-secondary {
  display: inline-flex; align-items: center; padding: 0.85rem 2rem;
  background: rgba(255,255,255,0.08); border: 1.5px solid rgba(255,255,255,0.2);
  color: #fff !important; border-radius: 12px; font-weight: 600; font-size: 1rem;
  text-decoration: none; transition: all 0.2s;
}
.btn-hero-secondary:hover { background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.35); transform: translateY(-2px); }

.hero-stats { display: flex; gap: 1.25rem; justify-content: center; position: relative; z-index: 1; flex-wrap: wrap; }

.stat-item {
  background: rgba(255,255,255,0.08); backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,0.12); border-radius: 16px;
  padding: 1.25rem 1.75rem; text-align: center; min-width: 120px;
  transition: transform 0.2s, background 0.2s;
}
.stat-item:hover { background: rgba(255,255,255,0.15); transform: translateY(-3px); }
.stat-icon { color: #60a5fa; margin-bottom: 0.5rem; }
.stat-item h3 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.15rem; color: #fff; }
.stat-item p { opacity: 0.7; margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 500; }

.hero-wave { position: absolute; bottom: -1px; left: 0; right: 0; line-height: 0; }
.hero-wave svg { width: 100%; height: 80px; }

/* Section Headers */
.section-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
.section-title { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; }
.section-subtitle { color: rgba(var(--v-theme-on-background), 0.6); font-size: 1rem; margin: 0; }
.bg-section { background: rgba(var(--v-theme-on-background), 0.02); }

.btn-view-all {
  display: inline-flex; align-items: center; gap: 0.5rem; color: rgb(var(--v-theme-primary));
  font-weight: 600; text-decoration: none; font-size: 0.95rem; transition: gap 0.2s;
}
.btn-view-all:hover { gap: 0.75rem; }

/* Category Cards */
.category-card-home {
  display: flex; flex-direction: column; align-items: center; text-align: center;
  background: rgb(var(--v-theme-surface)); border-radius: 16px; padding: 1.5rem 1rem;
  transition: all 0.3s; box-shadow: 0 2px 12px rgba(0,0,0,0.04); height: 100%;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.06);
}
.category-card-home:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(0,0,0,0.1); }

.cat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.75rem; }
.category-card-home h5 { font-size: 0.9rem; font-weight: 600; margin: 0 0 0.25rem; color: rgb(var(--v-theme-on-surface)); }
.cat-count { font-size: 0.75rem; color: rgba(var(--v-theme-on-surface), 0.5); }

/* Location Cards */
.location-card-home {
  display: block; background: rgb(var(--v-theme-surface)); border-radius: 14px;
  overflow: hidden; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.06); height: 100%;
}
.location-card-home:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
.location-flag-img { border-bottom: 1px solid rgba(var(--v-theme-on-surface), 0.06); }
.location-info { padding: 0.75rem; text-align: center; }
.location-info h5 { font-size: 0.85rem; font-weight: 600; margin: 0 0 0.2rem; color: rgb(var(--v-theme-on-surface)); }
.location-info span { font-size: 0.7rem; color: rgba(var(--v-theme-on-surface), 0.5); }

/* Download CTA */
.cta-card {
  background: linear-gradient(135deg, #1565c0, #0d47a1); border-radius: 24px;
  padding: 3rem; color: #fff;
}
.cta-card h2 { font-size: 2rem; font-weight: 700; margin-bottom: 0.75rem; }
.cta-card p { opacity: 0.85; font-size: 1.05rem; line-height: 1.7; margin-bottom: 0.5rem; }
.cta-features { display: flex; flex-wrap: wrap; gap: 1.25rem; font-size: 0.9rem; opacity: 0.9; margin-bottom: 1.5rem; }
.btn-download {
  display: inline-flex; align-items: center; background: #fff; color: #1565c0;
  padding: 0.75rem 1.5rem; border-radius: 12px; text-decoration: none;
  font-weight: 600; transition: transform 0.3s;
}
.btn-download:hover { transform: translateY(-3px); color: #1565c0; }

/* Responsive */
@media (max-width: 768px) {
  .hero { padding: 4rem 0 4rem; }
  .hero-title { font-size: 2.25rem; }
  .hero-badge { font-size: 0.75rem; padding: 0.4rem 1rem; }
  .hero-stats { gap: 0.75rem; }
  .stat-item { min-width: 42%; padding: 1rem; }
  .stat-item h3 { font-size: 1.35rem; }
  .section-title { font-size: 1.35rem; }
  .section-header { flex-direction: column; align-items: flex-start; }
  .cta-card { padding: 2rem; }
  .cta-card h2 { font-size: 1.5rem; }
  .search-btn { padding: 0.75rem 1.25rem; font-size: 0.85rem; }
}
</style>
