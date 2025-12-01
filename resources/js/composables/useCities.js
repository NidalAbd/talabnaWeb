import { ref } from 'vue'

export function useCities() {
  const cities = ref({
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })
  const loading = ref(false)
  const error = ref(null)

  const fetchCities = async (filters = {}) => {
    loading.value = true
    error.value = null

    try {
      const queryParams = new URLSearchParams()

      if (filters.search) queryParams.append('search', filters.search)
      if (filters.country_id) queryParams.append('country_id', filters.country_id)
      if (filters.sort_by) queryParams.append('sort_by', filters.sort_by)
      if (filters.sort_order) queryParams.append('sort_order', filters.sort_order)
      if (filters.per_page) queryParams.append('per_page', filters.per_page)
      if (filters.page) queryParams.append('page', filters.page)

      const url = `/api/admin/cities?${queryParams}`
      console.log('🔍 Fetching cities from:', url)

      const response = await fetch(url)
      console.log('📡 Response status:', response.status, response.statusText)

      if (!response.ok) {
        const errorText = await response.text()
        console.error('❌ Response error:', errorText)
        throw new Error('Failed to fetch cities')
      }

      const data = await response.json()
      console.log('✅ Cities data received:', data)
      console.log('📊 Cities array:', data.data)
      console.log('🏙️ First city sample:', data.data?.[0])
      cities.value = data
    } catch (err) {
      error.value = err.message
      console.error('❌ Error fetching cities:', err)
    } finally {
      loading.value = false
      console.log('⏱️ Loading complete. Cities count:', cities.value.data?.length || 0)
    }
  }

  const getCity = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/admin/cities/${id}`)

      if (!response.ok) {
        throw new Error('Failed to fetch city')
      }

      return await response.json()
    } catch (err) {
      error.value = err.message
      console.error('Error fetching city:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const createCity = async (formData) => {
    loading.value = true
    error.value = null

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

      const response = await fetch('/api/admin/cities', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken
        },
        body: formData
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Failed to create city')
      }

      return data
    } catch (err) {
      error.value = err.message
      console.error('Error creating city:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateCity = async (id, formData) => {
    loading.value = true
    error.value = null

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

      const response = await fetch(`/api/admin/cities/${id}`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken
        },
        body: formData
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Failed to update city')
      }

      return data
    } catch (err) {
      error.value = err.message
      console.error('Error updating city:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteCity = async (id) => {
    loading.value = true
    error.value = null

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

      const response = await fetch(`/api/admin/cities/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Content-Type': 'application/json'
        }
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Failed to delete city')
      }

      return data
    } catch (err) {
      error.value = err.message
      console.error('Error deleting city:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    cities,
    loading,
    error,
    fetchCities,
    getCity,
    createCity,
    updateCity,
    deleteCity
  }
}
