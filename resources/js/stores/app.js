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

  // User's detected/saved location
  const userCountry = ref(localStorage.getItem('user_country') || null) // ISO code e.g. 'PS'
  const userCity = ref(localStorage.getItem('user_city') || null)
  const userCountryId = ref(localStorage.getItem('user_country_id') || null)
  const userCityId = ref(localStorage.getItem('user_city_id') || null)

  // Available languages (loaded from API)
  const languages = ref(JSON.parse(localStorage.getItem('languages') || '[]'))

  // RTL language codes
  const rtlCodes = ['ar', 'he', 'fa', 'ur', 'ps', 'ku', 'yi', 'sd']

  // Getters
  const isRTL = computed(() => rtlCodes.includes(locale.value))
  const isAuthenticated = computed(() => !!user.value)
  const isDark = computed(() => theme.value === 'dark')

  // Actions
  function setLocale(newLocale) {
    locale.value = newLocale
    localStorage.setItem('locale', newLocale)
    document.documentElement.lang = newLocale
    document.documentElement.dir = rtlCodes.includes(newLocale) ? 'rtl' : 'ltr'
  }

  async function fetchLanguages() {
    try {
      const response = await fetch('/api/languages')
      if (response.ok) {
        const data = await response.json()
        const langs = data.languages || []
        languages.value = langs
        localStorage.setItem('languages', JSON.stringify(langs))
      }
    } catch (_) {}
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

  // Detect user's country from IP (for guests)
  async function detectLocation() {
    // Skip if already detected or user is logged in with country
    if (userCountryId.value && userCountryId.value !== 'null') return

    try {
      // Use ipapi.co (free, HTTPS, no key needed, 1000/day)
      const response = await fetch('https://ipapi.co/json/')
      if (response.ok) {
        const data = await response.json()
        const cc = data.country_code || data.country
        if (cc) {
          userCountry.value = cc
          userCity.value = data.city || null
          localStorage.setItem('user_country', cc)
          localStorage.setItem('user_city', data.city || '')

          // Resolve to our DB country ID
          const countryResponse = await fetch('/api/public/countries')
          if (countryResponse.ok) {
            const countryData = await countryResponse.json()
            const countriesList = countryData.countries || countryData || []
            const matched = countriesList.find(c =>
              c.iso_code === cc || c.country_code === cc || c.code === cc
            )
            if (matched) {
              userCountryId.value = matched.id
              localStorage.setItem('user_country_id', matched.id)
            }
          }
        }
      }
    } catch (_) {}
  }

  function setUserLocation(countryId, cityId) {
    userCountryId.value = countryId
    userCityId.value = cityId
    localStorage.setItem('user_country_id', countryId || '')
    localStorage.setItem('user_city_id', cityId || '')
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
    languages,
    loading,
    userCountry,
    userCity,
    userCountryId,
    userCityId,
    // Getters
    isRTL,
    isAuthenticated,
    isDark,
    // Actions
    setLocale,
    fetchLanguages,
    detectLocation,
    setUserLocation,
    toggleTheme,
    setTheme,
    setUser,
    setCategories,
    setCountries,
    setLoading,
    init,
  }
})
