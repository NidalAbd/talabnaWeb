/**
 * Admin Panel - Vue 3 components for AdminLTE
 */

import './bootstrap';
import { createApp } from 'vue'

// Import Vue components
import ModernDashboard from './views/admin/ModernDashboard.vue'
import PieChart from './components/admin/charts/PieChart.vue'
import LineChart from './components/admin/charts/LineChart.vue'
import BarChart from './components/admin/charts/BarChart.vue'
import MixedChart from './components/admin/charts/MixedChart.vue'

// Create Vue app
const app = createApp({})

// Register components globally
app.component('AdminDashboard', ModernDashboard)
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
}
