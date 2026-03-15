<template>
  <v-app>
    <!-- App Bar -->
    <v-app-bar
      app
      :elevation="1"
      color="surface"
    >
      <v-app-bar-nav-icon @click="drawer = !drawer" />

      <v-toolbar-title class="text-h6">
        <v-icon class="mr-2" size="small">mdi-view-dashboard</v-icon>
        Admin Dashboard
      </v-toolbar-title>

      <v-spacer />

      <!-- Search -->
      <v-text-field
        v-model="search"
        density="compact"
        variant="solo"
        label="Search..."
        prepend-inner-icon="mdi-magnify"
        single-line
        hide-details
        class="mr-4 d-none d-md-flex"
        style="max-width: 300px;"
      />

      <!-- Notifications -->
      <v-btn icon>
        <v-badge
          color="error"
          content="3"
          overlap
        >
          <v-icon>mdi-bell-outline</v-icon>
        </v-badge>
      </v-btn>

      <!-- User Menu -->
      <v-menu>
        <template #activator="{ props }">
          <v-btn
            v-bind="props"
            class="ml-2"
          >
            <v-icon class="mr-2">mdi-account-circle</v-icon>
            <span class="d-none d-sm-inline">{{ userName }}</span>
            <v-icon class="ml-1">mdi-chevron-down</v-icon>
          </v-btn>
        </template>
        <v-list>
          <v-list-item @click="logout">
            <template #prepend>
              <v-icon>mdi-logout</v-icon>
            </template>
            <v-list-item-title>Logout</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-app-bar>

    <!-- Navigation Drawer -->
    <v-navigation-drawer
      v-model="drawer"
      app
      :width="260"
    >
      <!-- Logo -->
      <v-list-item
        :to="{ name: 'admin.dashboard' }"
        class="py-4"
      >
        <template #prepend>
          <v-avatar size="40">
            <v-img src="/img/logo.ico" alt="Logo" />
          </v-avatar>
        </template>
        <v-list-item-title class="text-h6 font-weight-bold">
          Talabna Admin
        </v-list-item-title>
      </v-list-item>

      <v-divider />

      <!-- Navigation Menu -->
      <v-list
        nav
        density="compact"
      >
        <v-list-item
          v-for="item in menuItems"
          :key="item.title"
          :to="item.to"
          :prepend-icon="item.icon"
          :title="item.title"
          :value="item.value"
          color="primary"
        />
      </v-list>
    </v-navigation-drawer>

    <!-- Main Content -->
    <v-main>
      <v-container fluid>
        <router-view />
      </v-container>
    </v-main>

    <!-- Footer -->
    <v-footer app class="bg-surface">
      <v-row justify="center" no-gutters>
        <v-col class="text-center py-2" cols="12">
          <strong>&copy; {{ currentYear }} Talabna.</strong> All rights reserved.
        </v-col>
      </v-row>
    </v-footer>
  </v-app>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const drawer = ref(true)
const search = ref('')

const userName = computed(() => {
  // Get from auth store or props
  return 'Admin User'
})

const currentYear = computed(() => new Date().getFullYear())

const menuItems = [
  { title: 'Dashboard', icon: 'mdi-view-dashboard', to: { name: 'admin.dashboard' }, value: 'dashboard' },
  { title: 'Users', icon: 'mdi-account-group', to: { name: 'users.index' }, value: 'users' },
  { title: 'Categories', icon: 'mdi-shape', to: { name: 'categories.index' }, value: 'categories' },
  { title: 'Subcategories', icon: 'mdi-sitemap', to: { name: 'subcategories.index' }, value: 'subcategories' },
  { title: 'Countries', icon: 'mdi-earth', to: { name: 'countries.index' }, value: 'countries' },
  { title: 'Cities', icon: 'mdi-city', to: { name: 'cities.index' }, value: 'cities' },
  { title: 'Badge Types', icon: 'mdi-certificate', to: { name: 'badge-types.index' }, value: 'badge-types' },
  { title: 'Service Posts', icon: 'mdi-briefcase', to: { name: 'service_posts.index' }, value: 'service-posts' },
  { title: 'Reports', icon: 'mdi-flag', to: { name: 'reports.index' }, value: 'reports' },
  { title: 'Banned Users', icon: 'mdi-account-cancel', to: { name: 'users.banned' }, value: 'banned-users' },
  { title: 'Banned Devices', icon: 'mdi-cellphone-off', to: { name: 'devices.banned' }, value: 'banned-devices' },
  { title: 'Point Packages', icon: 'mdi-diamond-stone', to: { name: 'point_packages.index' }, value: 'point_packages' },
  { title: 'Country Pricing', icon: 'mdi-currency-usd', to: { name: 'country_pricing.index' }, value: 'country_pricing' },
  { title: 'Purchase Requests', icon: 'mdi-cart', to: { name: 'point_purchase_requests.index' }, value: 'purchase_requests' },
  { title: 'Point Transactions', icon: 'mdi-swap-horizontal', to: { name: 'point_transactions.index' }, value: 'point_transactions' },
  { title: 'Points Overview', icon: 'mdi-star-circle', to: { name: 'palservice_points.index' }, value: 'palservice_points' },
  { title: 'Marketing', icon: 'mdi-bullhorn', to: { name: 'marketing_notifications.index' }, value: 'marketing' },
  { title: 'Analytics', icon: 'mdi-chart-bar', to: { name: 'analytics.index' }, value: 'analytics' },
  { title: 'Statistics', icon: 'mdi-chart-pie', to: { name: 'statistics.index' }, value: 'statistics' },
  { title: 'Roles', icon: 'mdi-shield-account', to: { name: 'roles.index' }, value: 'roles' },
  { title: 'Permissions', icon: 'mdi-key', to: { name: 'permissions.index' }, value: 'permissions' },
  { title: 'Role Assignments', icon: 'mdi-account-key', to: { name: 'role_assignments.index' }, value: 'role_assignments' },
  { title: 'SEO Analytics', icon: 'mdi-google-analytics', to: { name: 'seo.index' }, value: 'seo' },
  { title: 'Languages', icon: 'mdi-translate', to: { name: 'languages.index' }, value: 'languages' },
  { title: 'Translations', icon: 'mdi-file-document-edit', to: { name: 'translations.index' }, value: 'translations' },
  { title: 'Command Monitor', icon: 'mdi-console', to: { name: 'command_monitor.index' }, value: 'command_monitor' },
  { title: 'AI Content', icon: 'mdi-robot', to: { name: 'ai_content.index' }, value: 'ai_content' },
]

const logout = async () => {
  try {
    await fetch('/logout', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
      }
    })
    window.location.href = '/login'
  } catch (error) {
    console.error('Logout error:', error)
  }
}
</script>

<style scoped>
.v-app-bar {
  backdrop-filter: blur(10px);
}

.v-navigation-drawer {
  border-right: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
}
</style>
