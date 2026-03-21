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
          <span>{{ appStore.locale === 'ar' ? 'طلبنا' : 'Talabna' }}</span>
        </router-link>

        <div class="flex-1"></div>

        <!-- Desktop Navigation -->
        <div class="navbar-nav hide-mobile">
          <router-link to="/" class="nav-link" :class="{ active: $route.name === 'home' }">
            <i class="mdi mdi-home"></i>
            {{ appStore.locale === 'ar' ? 'الرئيسية' : 'Home' }}
          </router-link>
          <router-link to="/browse" class="nav-link" :class="{ active: $route.name === 'browse' }">
            <i class="mdi mdi-view-grid"></i>
            {{ appStore.locale === 'ar' ? 'تصفح' : 'Browse' }}
          </router-link>

          <!-- Categories Dropdown -->
          <div class="dropdown" @mouseenter="catMenuOpen = true" @mouseleave="catMenuOpen = false">
            <button class="nav-link">
              <i class="mdi mdi-shape"></i>
              {{ appStore.locale === 'ar' ? 'التصنيفات' : 'Categories' }}
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
                {{ appStore.locale === 'ar' ? cat.name : cat.name_en }}
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
            :placeholder="appStore.locale === 'ar' ? 'ابحث...' : 'Search...'"
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

          <!-- Language Toggle -->
          <button class="btn btn-icon btn-text" @click="toggleLocale">
            <span style="font-weight: 700; font-size: 0.875rem;">{{ appStore.locale === 'ar' ? 'EN' : 'ع' }}</span>
          </button>

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
                {{ appStore.locale === 'ar' ? 'لوحة التحكم' : 'Dashboard' }}
              </a>
              <hr v-if="isAdmin" style="margin: 0.25rem 0;" />
              <!-- Logout -->
              <button class="dropdown-item dropdown-item-error" @click="logout">
                <i class="mdi mdi-logout"></i>
                {{ appStore.locale === 'ar' ? 'تسجيل الخروج' : 'Logout' }}
              </button>
            </div>
          </div>

          <!-- Google Sign In (when not logged in) -->
          <div v-else class="d-none d-sm-flex align-items-center gap-2">
            <button class="google-signin-btn" @click="triggerGoogleSignIn" :disabled="googleLoading">
              <svg v-if="!googleLoading" width="18" height="18" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
              <i v-else class="mdi mdi-loading mdi-spin" style="font-size: 18px;"></i>
              <span>{{ appStore.locale === 'ar' ? 'تسجيل بجوجل' : 'Sign in' }}</span>
            </button>
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
          {{ appStore.locale === 'ar' ? 'لوحة التحكم' : 'Dashboard' }}
        </a>
      </template>

      <!-- Mobile Search -->
      <div class="pa-4 py-2">
        <div class="search-wrapper">
          <i class="mdi mdi-magnify search-input-icon"></i>
          <input
            v-model="searchQuery"
            :placeholder="appStore.locale === 'ar' ? 'ابحث...' : 'Search...'"
            class="form-input form-input-search"
            @keyup.enter="doSearch(); mobileDrawer = false"
          />
        </div>
      </div>
      <div class="drawer-divider"></div>

      <router-link to="/" class="drawer-item" @click="mobileDrawer = false">
        <i class="mdi mdi-home"></i>
        {{ appStore.locale === 'ar' ? 'الرئيسية' : 'Home' }}
      </router-link>
      <router-link to="/browse" class="drawer-item" @click="mobileDrawer = false">
        <i class="mdi mdi-view-grid"></i>
        {{ appStore.locale === 'ar' ? 'تصفح' : 'Browse' }}
      </router-link>
      <div class="drawer-divider"></div>
      <div class="drawer-subheader">{{ appStore.locale === 'ar' ? 'التصنيفات' : 'Categories' }}</div>
      <router-link
        v-for="cat in appStore.categories"
        :key="cat.id"
        :to="`/category/${cat.id}`"
        class="drawer-item"
        @click="mobileDrawer = false"
      >
        <i class="mdi" :class="getCategoryIcon(cat.id)" :style="{ color: getCategoryColor(cat.id) }"></i>
        {{ appStore.locale === 'ar' ? cat.name : cat.name_en }}
      </router-link>
      <div class="drawer-divider"></div>
      <!-- Google Sign In (when not logged in) -->
      <button v-if="!isLoggedIn" class="drawer-item google-drawer-btn" @click="triggerGoogleSignIn" :disabled="googleLoading">
        <svg width="18" height="18" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
        {{ appStore.locale === 'ar' ? 'تسجيل الدخول بجوجل' : 'Sign in with Google' }}
      </button>
      <!-- Logout (when logged in) -->
      <button v-else class="drawer-item" style="color: var(--color-error);" @click="logout">
        <i class="mdi mdi-logout"></i>
        {{ appStore.locale === 'ar' ? 'تسجيل الخروج' : 'Logout' }}
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
                <h3 class="text-h6 font-weight-bold">{{ appStore.locale === 'ar' ? 'طلبنا' : 'Talabna' }}</h3>
                <p class="text-caption text-muted" style="margin:0;">{{ appStore.locale === 'ar' ? 'منصة الإعلانات المبوبة' : 'Classified Ads Platform' }}</p>
              </div>
            </div>
            <p class="text-body-2 text-muted">
              {{ appStore.locale === 'ar'
                ? 'أكبر منصة للإعلانات المبوبة. بيع واشتري بسهولة وأمان.'
                : 'The largest classified ads marketplace. Buy and sell easily and safely.'
              }}
            </p>
          </div>

          <!-- Quick Links -->
          <div class="col-6 col-md-2">
            <h4 class="text-subtitle-1 font-weight-bold mb-3">{{ appStore.locale === 'ar' ? 'روابط سريعة' : 'Quick Links' }}</h4>
            <div class="footer-links">
              <router-link to="/">{{ appStore.locale === 'ar' ? 'الرئيسية' : 'Home' }}</router-link>
              <router-link to="/browse">{{ appStore.locale === 'ar' ? 'تصفح' : 'Browse' }}</router-link>
              <router-link to="/about">{{ appStore.locale === 'ar' ? 'من نحن' : 'About' }}</router-link>
              <router-link to="/contact">{{ appStore.locale === 'ar' ? 'اتصل بنا' : 'Contact' }}</router-link>
            </div>
          </div>

          <!-- Categories -->
          <div class="col-6 col-md-2">
            <h4 class="text-subtitle-1 font-weight-bold mb-3">{{ appStore.locale === 'ar' ? 'التصنيفات' : 'Categories' }}</h4>
            <div class="footer-links">
              <router-link
                v-for="cat in appStore.categories.slice(0, 5)"
                :key="cat.id"
                :to="`/category/${cat.id}`"
              >
                {{ appStore.locale === 'ar' ? cat.name : cat.name_en }}
              </router-link>
            </div>
          </div>

          <!-- Legal & SEO -->
          <div class="col-6 col-md-2">
            <h4 class="text-subtitle-1 font-weight-bold mb-3">{{ appStore.locale === 'ar' ? 'قانوني' : 'Legal' }}</h4>
            <div class="footer-links">
              <router-link to="/privacy">{{ appStore.locale === 'ar' ? 'سياسة الخصوصية' : 'Privacy Policy' }}</router-link>
              <router-link to="/terms">{{ appStore.locale === 'ar' ? 'شروط الاستخدام' : 'Terms of Service' }}</router-link>
              <a href="/sitemap.xml" target="_blank">{{ appStore.locale === 'ar' ? 'خريطة الموقع' : 'Sitemap' }}</a>
            </div>
          </div>

          <!-- Download App -->
          <div class="col-6 col-md-2">
            <h4 class="text-subtitle-1 font-weight-bold mb-3">{{ appStore.locale === 'ar' ? 'حمل التطبيق' : 'Download App' }}</h4>
            <div class="d-flex flex-column gap-2">
              <a href="https://play.google.com/store/apps/details?id=com.talabna.talabna" target="_blank" class="btn btn-outline btn-sm justify-start">
                <i class="mdi mdi-google-play"></i>
                Google Play
              </a>
            </div>
            <div class="mt-4">
              <h4 class="text-subtitle-2 font-weight-bold mb-2">{{ appStore.locale === 'ar' ? 'تواصل معنا' : 'Contact Us' }}</h4>
              <p class="text-caption text-muted" style="margin:0;">
                <i class="mdi mdi-email mr-1"></i> support@talbna.cloud
              </p>
            </div>
          </div>
        </div>

        <div class="footer-bottom">
          <p class="text-caption text-muted" style="margin:0;">
            © {{ new Date().getFullYear() }} Talabna. {{ appStore.locale === 'ar' ? 'جميع الحقوق محفوظة' : 'All rights reserved' }}.
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
const googleClientId = document.querySelector('meta[name="google-client-id"]')?.content || ''
const catMenuOpen = ref(false)

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

const toggleLocale = () => {
  appStore.setLocale(appStore.locale === 'ar' ? 'en' : 'ar')
}

const doSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ name: 'search', query: { q: searchQuery.value } })
  }
}

const fetchCategories = async () => {
  try {
    const response = await fetch('/api/public/categories')
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

const triggerGoogleSignIn = () => {
  if (window.google && googleClientId) {
    window.google.accounts.id.prompt()
  }
}

const initGoogleOneTap = () => {
  if (window.google && googleClientId && !isLoggedIn.value) {
    try {
      window.google.accounts.id.initialize({
        client_id: googleClientId,
        callback: handleGoogleCredential,
        auto_select: false,
        cancel_on_tap_outside: true,
        itp_support: true,
      })
      // Show One Tap prompt automatically for guests
      window.google.accounts.id.prompt()
    } catch (e) {
      console.error('Google One Tap init error:', e)
    }
  }
}

onMounted(() => {
  appStore.init()
  // Apply theme to root element
  document.documentElement.setAttribute('data-theme', appStore.theme)
  fetchCategories()
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
/* Google Sign In Button */
.google-signin-btn {
  display: flex; align-items: center; gap: 8px; padding: 8px 16px;
  background: white; border: 1px solid #dadce0; border-radius: 20px;
  cursor: pointer; font-size: 14px; font-weight: 500; color: #3c4043;
  transition: all 0.2s; white-space: nowrap;
}
.google-signin-btn:hover { background: #f7f8f8; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.google-signin-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.google-drawer-btn { gap: 8px; }

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
