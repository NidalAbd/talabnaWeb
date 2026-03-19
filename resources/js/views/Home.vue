<template>
  <div class="home-page">
    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
      </div>
      <div class="container">
        <div class="hero-content">
          <div class="hero-badge">
            <i class="mdi mdi-shield-check" :style="{ fontSize: '16px' }"></i>
            {{ t('home.trusted') }}
          </div>
          <h1 class="hero-title">
            {{ t('home.find') }}
            <br><span class="text-gradient">{{ t('home.easily') }}</span>
          </h1>
          <p class="hero-subtitle">
            {{ t('home.hero_desc') }}
          </p>
          <div class="hero-search">
            <div class="search-box">
              <i class="mdi mdi-magnify search-icon"></i>
              <input
                v-model="searchQuery"
                :placeholder="t('home.search_placeholder')"
                @keyup.enter="doSearch"
              >
              <button class="search-btn" @click="doSearch">
                {{ t('home.search_btn') }}
              </button>
            </div>
          </div>
          <div class="hero-buttons">
            <router-link to="/browse" class="btn-hero-primary">
              <i class="mdi mdi-view-grid mr-2" :style="{ fontSize: '20px' }"></i>
              {{ t('browse.title') }}
            </router-link>
            <a href="https://play.google.com/store/apps/details?id=com.talabna.talabna" target="_blank" class="btn-hero-secondary">
              <i class="mdi mdi-google-play mr-2" :style="{ fontSize: '20px' }"></i>
              {{ t('home.get_app') }}
            </a>
          </div>
        </div>
        <div class="hero-stats">
          <div class="stat-item" v-for="stat in statsItems" :key="stat.key">
            <div class="stat-icon"><i class="mdi" :class="stat.icon"></i></div>
            <h3>{{ formatNumber(stats[stat.key] || 0) }}+</h3>
            <p>{{ stat.label }}</p>
          </div>
        </div>
      </div>
      <div class="hero-wave">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none"><path d="M0 40C240 80 480 0 720 40C960 80 1200 0 1440 40V80H0Z" fill="rgb(var(--v-theme-background))"/></svg>
      </div>
    </section>

    <!-- Categories Section -->
    <section class="section-categories py-12" id="categories">
      <div class="container">
        <div class="section-header">
          <div>
            <h2 class="section-title">{{ t('home.browse_categories') }}</h2>
            <p class="section-subtitle">{{ t('home.find_exactly') }}</p>
          </div>
        </div>
        <div class="row">
          <div v-for="cat in categories" :key="cat.id" class="col-6 col-sm-4 col-md-3 col-lg-2">
            <router-link :to="`/category/${cat.id}/${cat.slug || ''}`" class="category-card-home text-decoration-none">
              <div class="cat-icon" :style="{ background: getCategoryColor(cat.id) + '18', color: getCategoryColor(cat.id) }">
                <i class="mdi" :class="getCategoryIcon(cat.id)" :style="{ color: getCategoryColor(cat.id) }"></i>
              </div>
              <h5>{{ cat.name_localized || cat.name_en || cat.name }}</h5>
              <span class="cat-count">{{ formatNumber(cat.posts_count || 0) }} {{ t('listing.listings') }}</span>
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Services -->
    <section class="section-featured py-12">
      <div class="container">
        <div class="section-header">
          <div>
            <h2 class="section-title">{{ t('home.featured_listings') }}</h2>
            <p class="section-subtitle">{{ t('home.handpicked') }}</p>
          </div>
          <router-link to="/browse" class="btn-view-all">
            {{ t('home.view_all') }} <i class="mdi mdi-arrow-right" :style="{ fontSize: '18px' }"></i>
          </router-link>
        </div>
        <div class="row" v-if="!loadingFeatured">
          <div v-for="listing in featured" :key="listing.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
            <listing-card :listing="listing" :locale="locale" />
          </div>
        </div>
        <div class="row" v-else>
          <div v-for="n in 4" :key="n" class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="skeleton skeleton-card"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Latest Services by Category -->
    <section class="section-latest py-12 bg-section">
      <div class="container">
        <div class="section-header">
          <div>
            <h2 class="section-title">{{ t('home.latest_listings') }}</h2>
            <p class="section-subtitle">{{ t('home.recently_added') }}</p>
          </div>
        </div>
        <div class="tabs mb-6">
          <button class="tab" :class="{ active: activeTab === 'all' }" @click="activeTab = 'all'">{{ t('browse.all') }}</button>
          <button v-for="cat in categories.slice(0, 5)" :key="cat.id" class="tab" :class="{ active: activeTab === cat.id }" @click="activeTab = cat.id">
            {{ cat.name_localized || cat.name_en || cat.name }}
          </button>
        </div>
        <div class="row" v-if="!loadingLatest">
          <div v-for="listing in latest" :key="listing.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
            <listing-card :listing="listing" :locale="locale" />
          </div>
        </div>
        <div class="row" v-else>
          <div v-for="n in 8" :key="n" class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="skeleton skeleton-card"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Browse by Location -->
    <section class="section-locations py-12" id="locations">
      <div class="container">
        <div class="section-header">
          <div>
            <h2 class="section-title">{{ t('home.browse_location') }}</h2>
            <p class="section-subtitle">{{ t('home.find_area') }}</p>
          </div>
        </div>
        <div class="row" v-if="!loadingLocations">
          <div v-for="country in countries" :key="country.id" class="col-6 col-sm-4 col-md-3 col-lg-2">
            <router-link :to="`/services/${country.id}/${country.slug}`" class="location-card-home text-decoration-none">
              <img :src="country.flag || '/storage/countryFlag/placeholder-flag.jpg'" loading="lazy" decoding="async" width="200" height="80" class="img-cover location-flag-img" style="height: 80px; width: 100%;" @error="handleImgError($event)">
              <div class="location-info">
                <h5>{{ country.name_localized || country.name_en || country.name }}</h5>
                <span>{{ formatNumber(country.listings_count || 0) }} {{ t('listing.listings') }}</span>
              </div>
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Popular Services -->
    <section class="section-popular py-12 bg-section">
      <div class="container">
        <div class="section-header">
          <div>
            <h2 class="section-title">{{ t('home.most_viewed') }}</h2>
            <p class="section-subtitle">{{ t('home.most_popular') }}</p>
          </div>
          <router-link to="/browse?sort_by=view_count" class="btn-view-all">
            {{ t('home.view_all') }} <i class="mdi mdi-arrow-right" :style="{ fontSize: '18px' }"></i>
          </router-link>
        </div>
        <div class="row" v-if="!loadingPopular">
          <div v-for="listing in popular" :key="listing.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
            <listing-card :listing="listing" :locale="locale" />
          </div>
        </div>
      </div>
    </section>

    <!-- Download App CTA -->
    <section class="download-cta py-12">
      <div class="container">
        <div class="cta-card">
          <div class="row align-center">
            <div class="col-12 col-md-7">
              <h2>{{ t('home.download_app') }}</h2>
              <p>{{ t('home.download_desc') }}</p>
              <div class="cta-features">
                <span><i class="mdi mdi-check-circle mr-1" :style="{ fontSize: '16px' }"></i>{{ t('home.free') }}</span>
                <span><i class="mdi mdi-check-circle mr-1" :style="{ fontSize: '16px' }"></i>{{ t('home.easy_use') }}</span>
                <span><i class="mdi mdi-check-circle mr-1" :style="{ fontSize: '16px' }"></i>{{ t('home.instant_notif') }}</span>
              </div>
              <a href="https://play.google.com/store/apps/details?id=com.talabna.talabna" target="_blank" class="btn-download">
                <i class="mdi mdi-google-play mr-2"></i>
                Google Play
              </a>
            </div>
            <div class="col-12 col-md-5 text-center d-none d-md-block">
              <i class="mdi mdi-cellphone-arrow-down" :style="{ fontSize: '140px', opacity: 0.2, color: '#fff' }"></i>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { apiFetch } from "@/utils/api"
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { useSeo } from '@/composables/useSeo'
import ListingCard from '@/components/ListingCard.vue'
import { getCategoryIcon, getCategoryColor, formatNumber } from '@/utils/helpers'
import { t } from '@/utils/translate'

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
  { key: 'total_listings', icon: 'mdi-clipboard-list', label: t('home.active_services') },
  { key: 'total_users', icon: 'mdi-account-group', label: t('home.registered_users') },
  { key: 'total_categories', icon: 'mdi-star-circle', label: t('home.premium_services') },
  { key: 'listings_today', icon: 'mdi-trending-up', label: t('home.transactions') },
])

const doSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ name: 'search', query: { q: searchQuery.value } })
  }
}

const handleImgError = (event) => {
  event.target.src = '/storage/countryFlag/placeholder-flag.jpg'
}

// Fetch functions with defensive coding
const fetchCategories = async () => {
  try {
    const response = await apiFetch('/api/public/categories')
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
    const response = await apiFetch('/api/public/featured')
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
    const response = await apiFetch(url)
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
    const response = await apiFetch('/api/public/popular')
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
    const response = await apiFetch('/api/public/stats')
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
    const response = await apiFetch('/api/public/countries')
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

/* Tabs */
.tabs {
  display: flex; gap: 0.5rem; flex-wrap: wrap; border-bottom: 2px solid rgba(var(--v-theme-on-background), 0.08);
  padding-bottom: 0;
}
.tab {
  padding: 0.6rem 1.25rem; border: none; background: none; cursor: pointer;
  font-size: 0.95rem; font-weight: 500; color: rgba(var(--v-theme-on-background), 0.6);
  border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s;
  border-radius: 0;
}
.tab:hover { color: rgba(var(--v-theme-on-background), 0.85); }
.tab.active {
  color: rgb(var(--v-theme-primary)); border-bottom-color: rgb(var(--v-theme-primary)); font-weight: 600;
}

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
