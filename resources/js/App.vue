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
                <img v-if="userAvatar" :src="userAvatar" :alt="userName" />
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

          <!-- Login/Register (when not logged in) -->
          <a v-else href="/login" class="btn btn-primary btn-sm d-none d-sm-flex">
            <i class="mdi mdi-login"></i>
            {{ appStore.locale === 'ar' ? 'تسجيل الدخول' : 'Login' }}
          </a>

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
            <img v-if="userAvatar" :src="userAvatar" :alt="userName" />
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
      <!-- Login (when not logged in) -->
      <a v-if="!isLoggedIn" href="/login" class="drawer-item">
        <i class="mdi mdi-login"></i>
        {{ appStore.locale === 'ar' ? 'تسجيل الدخول' : 'Login' }}
      </a>
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

onMounted(() => {
  appStore.init()
  // Apply theme to root element
  document.documentElement.setAttribute('data-theme', appStore.theme)
  fetchCategories()
  fetchCurrentUser()
  document.addEventListener('click', handleClickOutside)
})
</script>

<style>
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
