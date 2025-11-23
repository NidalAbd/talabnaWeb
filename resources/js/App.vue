<template>
  <v-app :theme="appStore.theme" :class="{ 'rtl': appStore.isRTL }">
    <!-- Navigation -->
    <v-app-bar elevation="2" class="px-2">
      <v-container class="d-flex align-center py-0">
        <!-- Logo -->
        <router-link to="/" class="d-flex align-center text-decoration-none">
          <v-avatar size="40" class="mr-2" color="primary">
            <v-icon color="white">mdi-store</v-icon>
          </v-avatar>
          <span class="text-h5 font-weight-bold text-primary">طلبنا</span>
        </router-link>

        <v-spacer />

        <!-- Desktop Navigation -->
        <div class="d-none d-md-flex align-center gap-2">
          <v-btn variant="text" to="/" :active="$route.name === 'home'">
            <v-icon start>mdi-home</v-icon>
            {{ appStore.locale === 'ar' ? 'الرئيسية' : 'Home' }}
          </v-btn>
          <v-btn variant="text" to="/browse" :active="$route.name === 'browse'">
            <v-icon start>mdi-view-grid</v-icon>
            {{ appStore.locale === 'ar' ? 'تصفح' : 'Browse' }}
          </v-btn>

          <!-- Categories Menu -->
          <v-menu open-on-hover>
            <template v-slot:activator="{ props }">
              <v-btn variant="text" v-bind="props">
                <v-icon start>mdi-shape</v-icon>
                {{ appStore.locale === 'ar' ? 'التصنيفات' : 'Categories' }}
                <v-icon end>mdi-chevron-down</v-icon>
              </v-btn>
            </template>
            <v-list>
              <v-list-item
                v-for="cat in appStore.categories"
                :key="cat.id"
                :to="`/category/${cat.id}/${cat.slug || ''}`"
              >
                <template v-slot:prepend>
                  <v-icon :color="getCategoryColor(cat.id)">{{ getCategoryIcon(cat.id) }}</v-icon>
                </template>
                <v-list-item-title>{{ appStore.locale === 'ar' ? cat.name : cat.name_en }}</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </div>

        <v-spacer />

        <!-- Search -->
        <v-text-field
          v-model="searchQuery"
          :placeholder="appStore.locale === 'ar' ? 'ابحث...' : 'Search...'"
          prepend-inner-icon="mdi-magnify"
          variant="solo-filled"
          density="compact"
          hide-details
          single-line
          class="search-field mx-4 d-none d-sm-flex"
          style="max-width: 300px"
          @keyup.enter="doSearch"
        />

        <!-- Actions -->
        <div class="d-flex align-center gap-1">
          <!-- Theme Toggle -->
          <v-btn icon variant="text" @click="appStore.toggleTheme">
            <v-icon>{{ appStore.isDark ? 'mdi-weather-sunny' : 'mdi-weather-night' }}</v-icon>
          </v-btn>

          <!-- Language Toggle -->
          <v-btn icon variant="text" @click="toggleLocale">
            <span class="text-body-2 font-weight-bold">{{ appStore.locale === 'ar' ? 'EN' : 'ع' }}</span>
          </v-btn>

          <!-- Login/Register -->
          <v-btn color="primary" variant="flat" href="/login" class="d-none d-sm-flex">
            <v-icon start>mdi-login</v-icon>
            {{ appStore.locale === 'ar' ? 'تسجيل الدخول' : 'Login' }}
          </v-btn>

          <!-- Mobile Menu -->
          <v-btn icon variant="text" class="d-md-none" @click="mobileDrawer = true">
            <v-icon>mdi-menu</v-icon>
          </v-btn>
        </div>
      </v-container>
    </v-app-bar>

    <!-- Mobile Navigation Drawer -->
    <v-navigation-drawer v-model="mobileDrawer" temporary location="right">
      <v-list>
        <v-list-item to="/" @click="mobileDrawer = false">
          <template v-slot:prepend><v-icon>mdi-home</v-icon></template>
          <v-list-item-title>{{ appStore.locale === 'ar' ? 'الرئيسية' : 'Home' }}</v-list-item-title>
        </v-list-item>
        <v-list-item to="/browse" @click="mobileDrawer = false">
          <template v-slot:prepend><v-icon>mdi-view-grid</v-icon></template>
          <v-list-item-title>{{ appStore.locale === 'ar' ? 'تصفح' : 'Browse' }}</v-list-item-title>
        </v-list-item>
        <v-divider class="my-2" />
        <v-list-subheader>{{ appStore.locale === 'ar' ? 'التصنيفات' : 'Categories' }}</v-list-subheader>
        <v-list-item
          v-for="cat in appStore.categories"
          :key="cat.id"
          :to="`/category/${cat.id}`"
          @click="mobileDrawer = false"
        >
          <template v-slot:prepend>
            <v-icon :color="getCategoryColor(cat.id)">{{ getCategoryIcon(cat.id) }}</v-icon>
          </template>
          <v-list-item-title>{{ appStore.locale === 'ar' ? cat.name : cat.name_en }}</v-list-item-title>
        </v-list-item>
        <v-divider class="my-2" />
        <v-list-item href="/login">
          <template v-slot:prepend><v-icon>mdi-login</v-icon></template>
          <v-list-item-title>{{ appStore.locale === 'ar' ? 'تسجيل الدخول' : 'Login' }}</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <!-- Main Content -->
    <v-main>
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </v-main>

    <!-- Footer -->
    <v-footer class="bg-surface mt-12">
      <v-container>
        <v-row>
          <!-- About -->
          <v-col cols="12" md="4">
            <div class="d-flex align-center mb-4">
              <v-avatar size="48" class="mr-3" color="primary">
                <v-icon color="white" size="28">mdi-store</v-icon>
              </v-avatar>
              <div>
                <h3 class="text-h6 font-weight-bold">طلبنا - Talabna</h3>
                <p class="text-caption text-medium-emphasis">{{ appStore.locale === 'ar' ? 'منصة الإعلانات المبوبة' : 'Classified Ads Platform' }}</p>
              </div>
            </div>
            <p class="text-body-2 text-medium-emphasis">
              {{ appStore.locale === 'ar'
                ? 'أكبر منصة للإعلانات المبوبة. بيع واشتري بسهولة وأمان.'
                : 'The largest classified ads marketplace. Buy and sell easily and safely.'
              }}
            </p>
          </v-col>

          <!-- Quick Links -->
          <v-col cols="6" md="2">
            <h4 class="text-subtitle-1 font-weight-bold mb-3">{{ appStore.locale === 'ar' ? 'روابط سريعة' : 'Quick Links' }}</h4>
            <div class="d-flex flex-column gap-2">
              <router-link to="/" class="text-decoration-none text-medium-emphasis">{{ appStore.locale === 'ar' ? 'الرئيسية' : 'Home' }}</router-link>
              <router-link to="/browse" class="text-decoration-none text-medium-emphasis">{{ appStore.locale === 'ar' ? 'تصفح' : 'Browse' }}</router-link>
              <router-link to="/about" class="text-decoration-none text-medium-emphasis">{{ appStore.locale === 'ar' ? 'من نحن' : 'About' }}</router-link>
              <router-link to="/contact" class="text-decoration-none text-medium-emphasis">{{ appStore.locale === 'ar' ? 'اتصل بنا' : 'Contact' }}</router-link>
            </div>
          </v-col>

          <!-- Categories -->
          <v-col cols="6" md="2">
            <h4 class="text-subtitle-1 font-weight-bold mb-3">{{ appStore.locale === 'ar' ? 'التصنيفات' : 'Categories' }}</h4>
            <div class="d-flex flex-column gap-2">
              <router-link
                v-for="cat in appStore.categories.slice(0, 5)"
                :key="cat.id"
                :to="`/category/${cat.id}`"
                class="text-decoration-none text-medium-emphasis"
              >
                {{ appStore.locale === 'ar' ? cat.name : cat.name_en }}
              </router-link>
            </div>
          </v-col>

          <!-- Legal -->
          <v-col cols="6" md="2">
            <h4 class="text-subtitle-1 font-weight-bold mb-3">{{ appStore.locale === 'ar' ? 'قانوني' : 'Legal' }}</h4>
            <div class="d-flex flex-column gap-2">
              <router-link to="/privacy" class="text-decoration-none text-medium-emphasis">{{ appStore.locale === 'ar' ? 'سياسة الخصوصية' : 'Privacy Policy' }}</router-link>
              <router-link to="/terms" class="text-decoration-none text-medium-emphasis">{{ appStore.locale === 'ar' ? 'شروط الاستخدام' : 'Terms of Service' }}</router-link>
            </div>
          </v-col>

          <!-- Download App -->
          <v-col cols="6" md="2">
            <h4 class="text-subtitle-1 font-weight-bold mb-3">{{ appStore.locale === 'ar' ? 'حمل التطبيق' : 'Download App' }}</h4>
            <div class="d-flex flex-column gap-2">
              <v-btn variant="outlined" size="small" href="https://play.google.com/store/apps/details?id=com.talabna.talabna" target="_blank" class="justify-start">
                <v-icon start>mdi-google-play</v-icon>
                Google Play
              </v-btn>
            </div>
            <div class="mt-4">
              <h4 class="text-subtitle-2 font-weight-bold mb-2">{{ appStore.locale === 'ar' ? 'تواصل معنا' : 'Contact Us' }}</h4>
              <p class="text-caption text-medium-emphasis mb-1">
                <v-icon size="small" class="mr-1">mdi-email</v-icon> info@talabna.com
              </p>
            </div>
          </v-col>
        </v-row>

        <v-divider class="my-6" />

        <div class="d-flex flex-wrap justify-space-between align-center">
          <p class="text-caption text-medium-emphasis">
            © {{ new Date().getFullYear() }} Talabna. {{ appStore.locale === 'ar' ? 'جميع الحقوق محفوظة' : 'All rights reserved' }}.
          </p>
          <div class="d-flex gap-2">
            <v-btn icon variant="text" size="small" href="#"><v-icon>mdi-facebook</v-icon></v-btn>
            <v-btn icon variant="text" size="small" href="#"><v-icon>mdi-twitter</v-icon></v-btn>
            <v-btn icon variant="text" size="small" href="#"><v-icon>mdi-instagram</v-icon></v-btn>
          </div>
        </div>
      </v-container>
    </v-footer>
  </v-app>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAppStore } from '@/stores/app'

const router = useRouter()
const appStore = useAppStore()

const mobileDrawer = ref(false)
const searchQuery = ref('')

const categoryIcons = {
  1: 'mdi-cellphone',      // Phones
  2: 'mdi-car',            // Cars
  3: 'mdi-briefcase',      // Jobs
  4: 'mdi-home-city',      // Real Estate
  5: 'mdi-shape',          // General
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

onMounted(() => {
  appStore.init()
  fetchCategories()
})
</script>

<style>
/* RTL Support */
.rtl {
  direction: rtl;
  text-align: right;
}

.rtl .v-btn .v-icon--start {
  margin-left: 8px;
  margin-right: 0;
}

.rtl .v-btn .v-icon--end {
  margin-right: 8px;
  margin-left: 0;
}

/* Page transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Search field */
.search-field .v-field {
  border-radius: 24px !important;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
}

::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.3);
}
</style>
