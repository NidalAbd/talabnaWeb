/**
 * Admin Panel - Vue 3 SPA with Vue Router
 */

import './bootstrap';
import '../css/admin-compact.css'; // Compact styles for all admin sections
import '../css/admin-table.css'; // Modern table design for all admin sections
import { createApp } from 'vue'
import router from './router/admin'

// Import Vue components
import AdminLayout from './views/admin/AdminLayout.vue'
import ModernDashboard from './views/admin/ModernDashboard.vue'
import UsersList from './views/admin/UsersList.vue'
import RolesList from './views/admin/RolesList.vue'
import PermissionsList from './views/admin/PermissionsList.vue'
import CategoriesList from './views/admin/categories/CategoriesList.vue'
import SubCategoriesList from './views/admin/subcategories/SubCategoriesList.vue'
import CountriesList from './views/admin/countries/CountriesList.vue'
import CitiesList from './views/admin/cities/CitiesList.vue'
import BadgeTypesList from './views/admin/badge-types/BadgeTypesList.vue'
import PieChart from './components/admin/charts/PieChart.vue'
import LineChart from './components/admin/charts/LineChart.vue'
import BarChart from './components/admin/charts/BarChart.vue'
import MixedChart from './components/admin/charts/MixedChart.vue'

// Create Vue app
const app = createApp(AdminLayout)

// Use Vue Router
app.use(router)

// Register components globally
app.component('AdminDashboard', ModernDashboard)
app.component('UsersList', UsersList)
app.component('RolesList', RolesList)
app.component('PermissionsList', PermissionsList)
app.component('CategoriesList', CategoriesList)
app.component('SubCategoriesList', SubCategoriesList)
app.component('CountriesList', CountriesList)
app.component('CitiesList', CitiesList)
app.component('BadgeTypesList', BadgeTypesList)
app.component('PieChart', PieChart)
app.component('LineChart', LineChart)
app.component('BarChart', BarChart)
app.component('MixedChart', MixedChart)

// Global error handler
app.config.errorHandler = (err, vm, info) => {
  console.error('Vue Error:', err, info)
}

// Mount app if element exists
const mountEl = document.getElementById('admin-app')
if (mountEl) {
  app.mount('#admin-app')

  // Make app name available globally for router
  window.appName = document.querySelector('meta[name="app-name"]')?.content || 'Admin Dashboard'
}

// Handle sidebar navigation
document.addEventListener('DOMContentLoaded', () => {
  // Intercept all sidebar links
  const sidebarLinks = document.querySelectorAll('.nav-sidebar .nav-link')

  sidebarLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      const href = link.getAttribute('href')

      // Check if this is an admin route that should use Vue Router
      if (href && (
        href.includes('/users') ||
        href.includes('/roles') ||
        href.includes('/permissions') ||
        href.includes('/categories') ||
        href.includes('/subcategories') ||
        href.includes('/countries') ||
        href.includes('/cities') ||
        href.includes('/badge-types') ||
        href.includes('/service_posts') ||
        href.includes('/reports') ||
        href.includes('/admin/users/banned') ||
        href.includes('/admin/devices/banned') ||
        href.includes('/admin/point-packages') ||
        href.includes('/admin/premium-features') ||
        href.includes('/admin/levels') ||
        href.includes('/admin/point-purchase-requests') ||
        href.includes('/admin/point-transactions') ||
        href.includes('/admin/marketing-notifications') ||
        href.includes('/admin/analytics') ||
        href.includes('/admin/statistics') ||
        href.includes('/admin/role-assignments') ||
        href.includes('/admin/palservice-points') ||
        href.includes('/admin/seo') ||
        href.includes('/admin/command-monitor') ||
        href.includes('/admin/ai-content') ||
        href.includes('/admin/subscription-plans') ||
        href.includes('/admin/auto-translate') ||
        href.includes('/dashboard')
      )) {
        e.preventDefault()

        // Navigate using Vue Router
        const path = href.startsWith('http') ? new URL(href).pathname : href
        router.push(path)

        // Update active state
        sidebarLinks.forEach(l => l.classList.remove('active'))
        link.classList.add('active')
      }
    })
  })

  // Set initial active state based on current route
  const currentPath = window.location.pathname
  sidebarLinks.forEach(link => {
    const href = link.getAttribute('href')
    if (href && currentPath.includes(href)) {
      link.classList.add('active')
    }
  })
})
