<template>
  <div :class="['app-root', { 'rtl': appStore.isRTL }]" :data-theme="appStore.theme">
    <!-- Navigation -->
    <nav class="navbar">
      <div class="navbar-inner">
        <!-- Logo -->
        <router-link to="/" class="navbar-brand">
          <div class="avatar avatar-40">
            <img src="/storage/photos/profiles/45LcdzxednC495FtKeue7eUTRpyFN2YYK1Ij58U0.png" alt="Talabna" width="40" height="40" fetchpriority="high" />
          </div>
          <span>{{ appStore.t('app.name') }}</span>
        </router-link>

        <div class="flex-1"></div>

        <!-- Desktop Navigation -->
        <div class="navbar-nav hide-mobile">
          <router-link to="/" class="nav-link" :class="{ active: $route.name === 'home' }">
            <i class="mdi mdi-home"></i>
            {{ appStore.t('nav.home') }}
          </router-link>
          <router-link to="/browse" class="nav-link" :class="{ active: $route.name === 'browse' }">
            <i class="mdi mdi-view-grid"></i>
            {{ appStore.t('nav.browse') }}
          </router-link>

          <!-- Categories Dropdown -->
          <div class="dropdown" @mouseenter="catMenuOpen = true" @mouseleave="catMenuOpen = false">
            <button class="nav-link">
              <i class="mdi mdi-shape"></i>
              {{ appStore.t('nav.categories') }}
              <i class="mdi mdi-chevron-down" style="font-size: 18px;"></i>
            </button>
            <div class="dropdown-menu" :class="{ open: catMenuOpen }">
              <router-link
                v-for="cat in appStore.categories"
                :key="cat.id"
                :to="`/category/${cat.id}/${cat.slug || ''}`"
                class="dropdown-item"
                @click="catMenuOpen = false"
              >
                <i class="mdi" :class="getCategoryIcon(cat.id)" :style="{ color: getCategoryColor(cat.id) }"></i>
                {{ cat.name_localized || cat.name_en || cat.name }}
              </router-link>
            </div>
          </div>

          <!-- Countries Dropdown -->
          <div class="dropdown">
            <button class="nav-link d-flex align-center gap-1" @click="countryMenuOpen = !countryMenuOpen">
              <i class="mdi mdi-earth"></i>
              {{ appStore.t('nav.countries') }}
              <i class="mdi mdi-chevron-down" style="font-size: 18px;"></i>
            </button>
            <div class="dropdown-menu country-dropdown" :class="{ open: countryMenuOpen }">
              <router-link
                v-for="country in navCountries"
                :key="country.id"
                :to="`/services/${country.id}/${country.slug || country.id}`"
                class="dropdown-item"
                @click="countryMenuOpen = false"
              >
                <img v-if="country.flag" :src="country.flag" style="width: 20px; height: 14px; object-fit: cover; border-radius: 2px;" @error="$event.target.style.display='none'">
                <i v-else class="mdi mdi-flag-variant-outline" style="font-size: 16px;"></i>
                {{ country.name_localized || country.name_en || country.name }}
              </router-link>
            </div>
          </div>
        </div>

        <div class="flex-1"></div>

        <!-- Search -->
        <div class="search-wrapper d-none d-sm-flex">
          <i class="mdi mdi-magnify search-input-icon"></i>
          <input
            v-model="searchQuery"
            :placeholder="appStore.t('nav.search')"
            class="form-input form-input-search"
            @keyup.enter="doSearch"
          />
        </div>

        <!-- Actions -->
        <div class="d-flex align-center gap-1">
          <!-- Theme Toggle -->
          <button class="btn btn-icon btn-text" @click="appStore.toggleTheme" :title="appStore.isDark ? 'Light mode' : 'Dark mode'">
            <i class="mdi" :class="appStore.isDark ? 'mdi-weather-sunny' : 'mdi-weather-night'" style="font-size: 20px;"></i>
          </button>

          <!-- Language Selector -->
          <div class="dropdown">
            <button class="btn btn-icon btn-text" @click="langMenuOpen = !langMenuOpen" style="font-weight: 700; font-size: 0.8rem; min-width: 36px;">
              {{ appStore.locale.toUpperCase() }}
            </button>
            <div class="dropdown-menu lang-dropdown" :class="{ open: langMenuOpen }" @click="langMenuOpen = false">
              <button v-for="lang in availableLanguages" :key="lang.code" class="dropdown-item"
                :class="{ active: appStore.locale === lang.code }" @click="changeLanguage(lang.code)">
                {{ lang.native_name || lang.name }}
              </button>
            </div>
          </div>

          <!-- User Menu (when logged in) -->
          <div v-if="isLoggedIn" class="dropdown d-none d-sm-block">
            <button class="nav-link d-flex align-center gap-2" @click="userMenuOpen = !userMenuOpen">
              <div class="avatar avatar-32" :class="{ 'avatar-primary': !userAvatar }">
                <img v-if="userAvatar" :src="userAvatar" :alt="userName" width="32" height="32" loading="lazy" decoding="async" />
                <i v-else class="mdi mdi-account" style="font-size: 18px; color: #fff;"></i>
              </div>
              <span class="text-body-2 font-weight-medium">{{ userName }}</span>
              <i class="mdi mdi-chevron-down" style="font-size: 16px;"></i>
            </button>
            <div class="dropdown-menu" :class="{ open: userMenuOpen }" @click="userMenuOpen = false">
              <!-- Dashboard (Admin Only) -->
              <a v-if="isAdmin" href="/dashboard" class="dropdown-item">
                <i class="mdi mdi-view-dashboard" style="color: var(--color-primary);"></i>
                {{ appStore.t('nav.dashboard') }}
              </a>
              <hr v-if="isAdmin" style="margin: 0.25rem 0;" />
              <!-- Logout -->
              <button class="dropdown-item dropdown-item-error" @click="logout">
                <i class="mdi mdi-logout"></i>
                {{ appStore.t('nav.logout') }}
              </button>
            </div>
          </div>

          <!-- Google Sign In (when not logged in) -->
          <div v-else class="d-none d-sm-flex align-items-center">
            <div ref="googleBtnDesktop" class="google-btn-container"></div>
          </div>

          <!-- Mobile Menu Button -->
          <button class="btn btn-icon btn-text show-mobile" @click="mobileDrawer = true">
            <i class="mdi mdi-menu" style="font-size: 24px;"></i>
          </button>
        </div>
      </div>
    </nav>

    <!-- Mobile Drawer Overlay -->
    <div class="drawer-overlay" :class="{ open: mobileDrawer }" @click="mobileDrawer = false"></div>

    <!-- Mobile Navigation Drawer -->
    <div class="drawer" :class="{ open: mobileDrawer }">
      <!-- User Info (when logged in) -->
      <template v-if="isLoggedIn">
        <div class="d-flex align-center gap-3 pa-4">
          <div class="avatar avatar-40" :class="{ 'avatar-primary': !userAvatar }">
            <img v-if="userAvatar" :src="userAvatar" :alt="userName" width="40" height="40" loading="lazy" decoding="async" />
            <i v-else class="mdi mdi-account" style="color: #fff;"></i>
          </div>
          <div>
            <div class="font-weight-bold">{{ userName }}</div>
            <div class="text-caption text-muted">{{ appStore.user?.email }}</div>
          </div>
        </div>
        <div class="drawer-divider"></div>
        <!-- Dashboard (Admin Only) -->
        <a v-if="isAdmin" href="/dashboard" class="drawer-item" @click="mobileDrawer = false">
          <i class="mdi mdi-view-dashboard" style="color: var(--color-primary);"></i>
          {{ appStore.t('nav.dashboard') }}
        </a>
      </template>

      <!-- Mobile Search -->
      <div class="pa-4 py-2">
        <div class="search-wrapper">
          <i class="mdi mdi-magnify search-input-icon"></i>
          <input
            v-model="searchQuery"
            :placeholder="appStore.t('nav.search')"
            class="form-input form-input-search"
            @keyup.enter="doSearch(); mobileDrawer = false"
          />
        </div>
      </div>
      <div class="drawer-divider"></div>

      <router-link to="/" class="drawer-item" @click="mobileDrawer = false">
        <i class="mdi mdi-home"></i>
        {{ appStore.t('nav.home') }}
      </router-link>
      <router-link to="/browse" class="drawer-item" @click="mobileDrawer = false">
        <i class="mdi mdi-view-grid"></i>
        {{ appStore.t('nav.browse') }}
      </router-link>
      <div class="drawer-divider"></div>
      <div class="drawer-subheader">{{ appStore.t('nav.categories') }}</div>
      <router-link
        v-for="cat in appStore.categories"
        :key="cat.id"
        :to="`/category/${cat.id}`"
        class="drawer-item"
        @click="mobileDrawer = false"
      >
        <i class="mdi" :class="getCategoryIcon(cat.id)" :style="{ color: getCategoryColor(cat.id) }"></i>
        {{ cat.name_localized || cat.name_en || cat.name }}
      </router-link>
      <div class="drawer-divider"></div>
      <!-- Google Sign In (when not logged in) -->
      <div v-if="!isLoggedIn" class="drawer-item" style="padding: 0.75rem 1rem;">
        <div ref="googleBtnMobile" class="google-btn-container"></div>
      </div>
      <!-- Logout (when logged in) -->
      <button v-else class="drawer-item" style="color: var(--color-error);" @click="logout">
        <i class="mdi mdi-logout"></i>
        {{ appStore.t('nav.logout') }}
      </button>
    </div>

    <!-- Main Content -->
    <main style="min-height: calc(100vh - 64px);">
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
      <div class="container">
        <div class="row">
          <!-- About -->
          <div class="col-12 col-md-4">
            <div class="d-flex align-center mb-4">
              <div class="avatar avatar-48 mr-3">
                <img src="/storage/photos/profiles/45LcdzxednC495FtKeue7eUTRpyFN2YYK1Ij58U0.png" alt="Talabna" width="48" height="48" loading="lazy" />
              </div>
              <div>
                <h3 class="text-h6 font-weight-bold">{{ appStore.t('app.name') }}</h3>
                <p class="text-caption text-muted" style="margin:0;">{{ appStore.t('app.tagline') }}</p>
              </div>
            </div>
            <p class="text-body-2 text-muted">
              {{ appStore.t('footer.about_desc') }}
            </p>
          </div>

          <!-- Quick Links -->
          <div class="col-6 col-md-2">
            <h4 class="text-subtitle-1 font-weight-bold mb-3">{{ appStore.t('nav.quick_links') }}</h4>
            <div class="footer-links">
              <router-link to="/">{{ appStore.t('nav.home') }}</router-link>
              <router-link to="/browse">{{ appStore.t('nav.browse') }}</router-link>
              <router-link to="/about">{{ appStore.t('nav.about') }}</router-link>
              <router-link to="/contact">{{ appStore.t('nav.contact') }}</router-link>
            </div>
          </div>

          <!-- Categories -->
          <div class="col-6 col-md-2">
            <h4 class="text-subtitle-1 font-weight-bold mb-3">{{ appStore.t('nav.categories') }}</h4>
            <div class="footer-links">
              <router-link
                v-for="cat in appStore.categories.slice(0, 5)"
                :key="cat.id"
                :to="`/category/${cat.id}`"
              >
                {{ cat.name_localized || cat.name_en || cat.name }}
              </router-link>
            </div>
          </div>

          <!-- Legal & SEO -->
          <div class="col-6 col-md-2">
            <h4 class="text-subtitle-1 font-weight-bold mb-3">{{ appStore.t('nav.legal') }}</h4>
            <div class="footer-links">
              <router-link to="/privacy">{{ appStore.t('nav.privacy') }}</router-link>
              <router-link to="/terms">{{ appStore.t('nav.terms') }}</router-link>
              <a href="/sitemap.xml" target="_blank">{{ appStore.t('nav.sitemap') }}</a>
            </div>
          </div>

          <!-- Download App -->
          <div class="col-6 col-md-2">
            <h4 class="text-subtitle-1 font-weight-bold mb-3">{{ appStore.t('footer.download') }}</h4>
            <div class="d-flex flex-column gap-2">
              <a href="https://play.google.com/store/apps/details?id=com.talabna.talabna" target="_blank" class="btn btn-outline btn-sm justify-start">
                <i class="mdi mdi-google-play"></i>
                Google Play
              </a>
            </div>
            <div class="mt-4">
              <h4 class="text-subtitle-2 font-weight-bold mb-2">{{ appStore.t('nav.contact') }}</h4>
              <p class="text-caption text-muted" style="margin:0;">
                <i class="mdi mdi-email mr-1"></i> support@talbna.cloud
              </p>
            </div>
          </div>
        </div>

        <div class="footer-bottom">
          <p class="text-caption text-muted" style="margin:0;">
            © {{ new Date().getFullYear() }} Talabna. {{ appStore.t('footer.rights') }}.
          </p>
          <div class="d-flex gap-2">
            <a href="https://www.facebook.com/talabna" target="_blank" class="btn btn-icon-sm btn-text"><i class="mdi mdi-facebook" style="font-size: 20px;"></i></a>
            <a href="https://www.instagram.com/talabna" target="_blank" class="btn btn-icon-sm btn-text"><i class="mdi mdi-instagram" style="font-size: 20px;"></i></a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAppStore } from '@/stores/app'

const router = useRouter()
const appStore = useAppStore()

const mobileDrawer = ref(false)
const searchQuery = ref('')
const userMenuOpen = ref(false)
const googleLoading = ref(false)
const googleBtnDesktop = ref(null)
const googleBtnMobile = ref(null)
const googleClientId = document.querySelector('meta[name="google-client-id"]')?.content || ''
const catMenuOpen = ref(false)
const countryMenuOpen = ref(false)
const navCountries = ref([])

// Computed properties for user
const isLoggedIn = computed(() => !!appStore.user)
const isAdmin = computed(() => {
  if (!appStore.user) return false
  const roles = appStore.user.roles || []
  return roles.some(role => ['admin', 'superadmin'].includes(role.name || role))
})
const userName = computed(() => appStore.user?.name || appStore.user?.user_name || '')
const userAvatar = computed(() => {
  if (!appStore.user) return null
  if (appStore.user.avatar) return appStore.user.avatar
  if (appStore.user.photos && appStore.user.photos.length > 0) {
    const photo = appStore.user.photos[0]
    if (photo.is_external) return photo.src
    const src = photo.src
    return src.startsWith('/') || src.startsWith('http') ? src : (src.startsWith('storage/') ? `/${src}` : `/storage/${src}`)
  }
  return null
})

import { getCategoryIcon, getCategoryColor } from '@/utils/helpers'
import { apiFetch } from '@/utils/api'

const langMenuOpen = ref(false)
const availableLanguages = ref([
  { code: 'ar', name: 'Arabic', native_name: 'العربية' },
  { code: 'en', name: 'English', native_name: 'English' },
  { code: 'hi', name: 'Hindi', native_name: 'हिन्दी' },
  { code: 'tr', name: 'Turkish', native_name: 'Türkçe' },
  { code: 'fr', name: 'French', native_name: 'Français' },
  { code: 'es', name: 'Spanish', native_name: 'Español' },
  { code: 'ur', name: 'Urdu', native_name: 'اردو' },
  { code: 'bn', name: 'Bengali', native_name: 'বাংলা' },
  { code: 'pt', name: 'Portuguese', native_name: 'Português' },
  { code: 'ru', name: 'Russian', native_name: 'Русский' },
  { code: 'id', name: 'Indonesian', native_name: 'Bahasa Indonesia' },
  { code: 'de', name: 'German', native_name: 'Deutsch' },
  { code: 'zh', name: 'Chinese', native_name: '中文' },
  { code: 'ku', name: 'Kurdish', native_name: 'کوردی' },
  { code: 'fa', name: 'Persian', native_name: 'فارسی' },
  { code: 'sw', name: 'Swahili', native_name: 'Kiswahili' },
  { code: 'ms', name: 'Malay', native_name: 'Bahasa Melayu' },
])

const changeLanguage = (code) => {
  appStore.setLocale(code)
  langMenuOpen.value = false
  // Navigate to the path-prefixed locale URL with a full page load so Laravel
  // re-renders SSR meta (canonical, hreflang) for the new locale. Default
  // locale (ar) is unprefixed; all others sit under /{code}/.
  const defaultLocale = 'ar'
  const localeCodes = ['en','tr','fr','es','hi','ur','bn','pt','ru','id','de','zh','ku','fa','sw','ms']
  const url = new URL(window.location.href)
  // Strip any existing locale prefix from the path.
  const segments = url.pathname.split('/').filter(Boolean)
  if (segments.length > 0 && localeCodes.includes(segments[0])) {
    segments.shift()
  }
  const basePath = '/' + segments.join('/')
  // Strip the legacy ?lang= query param if present so the new URL is clean.
  url.searchParams.delete('lang')
  url.pathname = code === defaultLocale ? basePath : `/${code}${basePath}`
  if (url.pathname === '') url.pathname = '/'
  window.location.assign(url.toString())
}

const doSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ name: 'search', query: { q: searchQuery.value } })
  }
}

const fetchNavCountries = async () => {
  try {
    const response = await apiFetch('/api/public/countries')
    if (response.ok) {
      const data = await response.json()
      navCountries.value = data.countries || []
    }
  } catch (e) {
    console.error('Error fetching countries:', e)
  }
}

const fetchCategories = async () => {
  try {
    const response = await apiFetch('/api/public/categories')
    if (response.ok) {
      const data = await response.json()
      appStore.setCategories(data.categories || data)
    }
  } catch (error) {
    console.error('Error fetching categories:', error)
  }
}

const fetchCurrentUser = async () => {
  try {
    const response = await fetch('/api/user', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    })
    if (response.ok) {
      const data = await response.json()
      appStore.setUser(data.user || data)
    }
  } catch (error) {
    // User not logged in or error - that's okay
    console.log('User not authenticated')
  }
}

const logout = async () => {
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
    await fetch('/logout', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    })
    appStore.setUser(null)
    window.location.href = '/'
  } catch (error) {
    console.error('Error logging out:', error)
    window.location.href = '/logout'
  }
}

// Close dropdown when clicking outside
const handleClickOutside = (e) => {
  if (userMenuOpen.value && !e.target.closest('.dropdown')) {
    userMenuOpen.value = false
  }
  if (langMenuOpen.value && !e.target.closest('.dropdown')) {
    langMenuOpen.value = false
  }
  if (countryMenuOpen.value && !e.target.closest('.dropdown')) {
    countryMenuOpen.value = false
  }
}

// Google One Tap callback
const handleGoogleCredential = async (response) => {
  googleLoading.value = true
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
    const res = await fetch('/auth/google/one-tap', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
      body: JSON.stringify({ credential: response.credential }),
    })
    const data = await res.json()
    if (data.success && data.user) {
      appStore.setUser(data.user)
      // Cancel One Tap prompt to prevent loop
      if (window.google) {
        window.google.accounts.id.cancel()
      }
    } else {
      console.error('Google auth failed:', data.error)
      alert(data.error || 'Login failed')
    }
  } catch (error) {
    console.error('Google auth error:', error)
    alert('Login failed. Please try again.')
  } finally {
    googleLoading.value = false
  }
}

// Expose callback globally for Google One Tap
window.handleGoogleCredential = handleGoogleCredential

const initGoogleOneTap = () => {
  if (window.google && googleClientId && !isLoggedIn.value) {
    try {
      window.google.accounts.id.initialize({
        client_id: googleClientId,
        callback: handleGoogleCredential,
        auto_select: false,
        cancel_on_tap_outside: true,
      })
      // Render Google button in desktop navbar
      if (googleBtnDesktop.value) {
        window.google.accounts.id.renderButton(googleBtnDesktop.value, {
          type: 'standard',
          theme: 'outline',
          size: 'medium',
          shape: 'pill',
          text: 'signin_with',
          logo_alignment: 'left',
        })
      }
      // Render Google button in mobile drawer
      if (googleBtnMobile.value) {
        window.google.accounts.id.renderButton(googleBtnMobile.value, {
          type: 'standard',
          theme: 'outline',
          size: 'large',
          shape: 'rectangular',
          text: 'signin_with',
          width: 250,
        })
      }
      // Show One Tap prompt
      window.google.accounts.id.prompt()
    } catch (e) {
      console.error('Google init error:', e)
    }
  }
}

onMounted(() => {
  appStore.init()
  // Apply theme to root element
  document.documentElement.setAttribute('data-theme', appStore.theme)
  fetchCategories()
  fetchNavCountries()
  fetchCurrentUser().then(() => {
    if (!isLoggedIn.value) {
      // Only init Google One Tap if not logged in
      const checkGoogle = setInterval(() => {
        if (window.google) {
          clearInterval(checkGoogle)
          initGoogleOneTap()
        }
      }, 500)
      setTimeout(() => clearInterval(checkGoogle), 10000)
    } else {
      // User is logged in - cancel any One Tap prompt
      const checkGoogle2 = setInterval(() => {
        if (window.google) {
          clearInterval(checkGoogle2)
          window.google.accounts.id.cancel()
        }
      }, 500)
      setTimeout(() => clearInterval(checkGoogle2), 5000)
    }
  })
  document.addEventListener('click', handleClickOutside)
})
</script>

<style>
/* Country Dropdown */
.country-dropdown {
  max-height: 400px; overflow-y: auto; min-width: 200px;
  display: grid; grid-template-columns: 1fr;
}
.country-dropdown .dropdown-item {
  display: flex; align-items: center; gap: 8px;
}

/* Language Dropdown */
.lang-dropdown {
  max-height: 400px; overflow-y: auto; min-width: 160px;
}
.lang-dropdown .dropdown-item.active {
  background: rgba(var(--v-theme-primary), 0.1);
  color: rgb(var(--v-theme-primary));
  font-weight: 700;
}

/* Global styles for non-Vuetify app */

/* Apply theme reactively */
.app-root {
  min-height: 100vh;
  color: rgb(var(--v-theme-on-background));
  background: rgb(var(--v-theme-background));
}

/* RTL icon flip */
.rtl .mdi-chevron-right::before { content: "\F0141"; /* mdi-chevron-left */ }

/* Search wrapper */
.search-wrapper {
  position: relative;
  max-width: 300px;
  width: 100%;
}

.search-input-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--color-text-muted);
  font-size: 20px;
  pointer-events: none;
}

.rtl .search-input-icon {
  left: auto;
  right: 12px;
}

.rtl .form-input-search {
  padding-left: 0.875rem;
  padding-right: 2.5rem;
}

/* Page transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Responsive mobile-only helpers */
.show-mobile { display: flex !important; }
@media (min-width: 960px) {
  .show-mobile { display: none !important; }
}
</style>
