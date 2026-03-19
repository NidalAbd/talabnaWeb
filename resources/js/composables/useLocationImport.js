import { ref } from 'vue'

export function useLocationImport() {
  const loading = ref(false)
  const error = ref(null)
  const progress = ref(null)

  const csrfHeaders = () => ({
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  })

  const getRegions = async () => {
    const response = await fetch('/api/admin/location-import/regions', { credentials: 'same-origin' })
    if (!response.ok) throw new Error('Failed to fetch regions')
    return (await response.json()).regions
  }

  const importCountries = async (region = null) => {
    loading.value = true
    error.value = null
    try {
      const response = await fetch('/api/admin/location-import/countries', {
        method: 'POST', credentials: 'same-origin',
        headers: csrfHeaders(),
        body: JSON.stringify({ region })
      })
      if (!response.ok) throw new Error('Failed to start import')
      return await response.json()
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  const generateCities = async (countryId, count = 30) => {
    loading.value = true
    error.value = null
    try {
      const response = await fetch(`/api/admin/location-import/countries/${countryId}/generate-cities`, {
        method: 'POST', credentials: 'same-origin',
        headers: csrfHeaders(),
        body: JSON.stringify({ count })
      })
      if (!response.ok) throw new Error('Failed to start city generation')
      return await response.json()
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  const translateNames = async (countryId) => {
    loading.value = true
    error.value = null
    try {
      const response = await fetch(`/api/admin/location-import/countries/${countryId}/translate`, {
        method: 'POST', credentials: 'same-origin',
        headers: csrfHeaders()
      })
      if (!response.ok) throw new Error('Failed to translate')
      return await response.json()
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  const checkProgress = async () => {
    try {
      const response = await fetch('/api/admin/location-import/progress', { credentials: 'same-origin' })
      if (!response.ok) return null
      const data = await response.json()
      progress.value = data.progress
      return data.progress
    } catch (_) {
      return null
    }
  }

  return { loading, error, progress, getRegions, importCountries, generateCities, translateNames, checkProgress }
}
