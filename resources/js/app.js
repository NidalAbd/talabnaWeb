/**
 * Talabna - Vue 3 SPA (No Vuetify — lightweight CSS framework)
 */

import './bootstrap';
import '../css/talabna.css';
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

// Load MDI icons asynchronously
if (typeof window !== 'undefined') {
  const link = document.createElement('link')
  link.rel = 'stylesheet'
  link.href = 'https://cdn.jsdelivr.net/npm/@mdi/font@7/css/materialdesignicons.min.css'
  link.media = 'print'
  link.onload = function() { this.media = 'all' }
  document.head.appendChild(link)
}

// Create Vue app
const app = createApp(App)

// Use plugins
app.use(createPinia())
app.use(router)

// Global error handler
app.config.errorHandler = (err, vm, info) => {
  console.error('Vue Error:', err, info)
}

// Mount app
app.mount('#app')
