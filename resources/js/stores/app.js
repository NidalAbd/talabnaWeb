import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { t as translate, loadTranslations } from '@/utils/translate'

export const useAppStore = defineStore('app', () => {
  // State
  const locale = ref(localStorage.getItem('locale') || 'ar')
  const theme = ref(localStorage.getItem('theme') || 'light')
  const user = ref(null)
  const categories = ref([])
  const countries = ref([])
  const loading = ref(false)
  const translationsLoaded = ref(false)

  // Getters
  const rtlLocales = ['ar', 'he', 'fa', 'ur', 'ps', 'ku', 'sd']
  const isRTL = computed(() => rtlLocales.includes(locale.value))
  const isAuthenticated = computed(() => !!user.value)
  const isDark = computed(() => theme.value === 'dark')

  // Translation helper
  function t(key) {
    return translate(key)
  }

  // Actions
  async function setLocale(newLocale) {
    locale.value = newLocale
    localStorage.setItem('locale', newLocale)
    document.documentElement.lang = newLocale
    const rtl = ['ar', 'he', 'fa', 'ur', 'ps', 'ku', 'sd']
    document.documentElement.dir = rtl.includes(newLocale) ? 'rtl' : 'ltr'
    await loadTranslations(newLocale)
    translationsLoaded.value = true
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
  async function init() {
    const savedLocale = localStorage.getItem('locale') || 'ar'
    const savedTheme = localStorage.getItem('theme') || 'light'
    await setLocale(savedLocale)
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
    translationsLoaded,
    // Getters
    isRTL,
    isAuthenticated,
    isDark,
    // Actions
    t,
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
