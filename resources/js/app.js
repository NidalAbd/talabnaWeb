/**
 * Talabna - Vue 3 SPA (No Vuetify — lightweight CSS framework)
 */

import './bootstrap';
import '../css/talabna.css';
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

// Load MDI icons: inject @font-face with font-display:swap, then load CSS classes
if (typeof window !== 'undefined') {
  // 1. Inject font-face with swap BEFORE loading the CSS
  const style = document.createElement('style')
  style.textContent = `@font-face{font-family:"Material Design Icons";src:url("https://cdn.jsdelivr.net/npm/@mdi/font@7/fonts/materialdesignicons-webfont.woff2?v=7.4.47") format("woff2");font-display:swap;font-weight:400;font-style:normal}`
  document.head.appendChild(style)

  // 2. Load the icon class definitions (non-render-blocking)
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
