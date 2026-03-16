import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAppStore = defineStore('app', () => {
  // State
  const locale = ref(localStorage.getItem('locale') || 'ar')
  const theme = ref(localStorage.getItem('theme') || 'light')
  const user = ref(null)
  const categories = ref([])
  const countries = ref([])
  const loading = ref(false)

  // Getters
  const isRTL = computed(() => locale.value === 'ar')
  const isAuthenticated = computed(() => !!user.value)
  const isDark = computed(() => theme.value === 'dark')

  // Actions
  function setLocale(newLocale) {
    locale.value = newLocale
    localStorage.setItem('locale', newLocale)
    document.documentElement.lang = newLocale
    document.documentElement.dir = newLocale === 'ar' ? 'rtl' : 'ltr'
  }

  function toggleTheme() {
    theme.value = theme.value === 'light' ? 'dark' : 'light'
    localStorage.setItem('theme', theme.value)
    document.documentElement.setAttribute('data-theme', theme.value)
  }

  function setTheme(newTheme) {
    theme.value = newTheme
    localStorage.setItem('theme', newTheme)
    document.documentElement.setAttribute('data-theme', newTheme)
  }

  function setUser(userData) {
    user.value = userData
  }

  function setCategories(data) {
    categories.value = Array.isArray(data) ? data : []
  }

  function setCountries(data) {
    countries.value = Array.isArray(data) ? data : []
  }

  function setLoading(state) {
    loading.value = state
  }

  // Initialize
  function init() {
    const savedLocale = localStorage.getItem('locale') || 'ar'
    const savedTheme = localStorage.getItem('theme') || 'light'
    setLocale(savedLocale)
    setTheme(savedTheme)
  }

  return {
    // State
    locale,
    theme,
    user,
    categories,
    countries,
    loading,
    // Getters
    isRTL,
    isAuthenticated,
    isDark,
    // Actions
    setLocale,
    toggleTheme,
    setTheme,
    setUser,
    setCategories,
    setCountries,
    setLoading,
    init,
  }
})
