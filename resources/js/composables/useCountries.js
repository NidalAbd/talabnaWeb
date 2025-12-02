import { ref } from 'vue'

export function useCountries() {
  const countries = ref({
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })
  const loading = ref(false)
  const error = ref(null)

  const fetchCountries = async (filters = {}) => {
    loading.value = true
    error.value = null

    try {
      const queryParams = new URLSearchParams()

      if (filters.search) queryParams.append('search', filters.search)
      if (filters.sort_by) queryParams.append('sort_by', filters.sort_by)
      if (filters.sort_order) queryParams.append('sort_order', filters.sort_order)
      if (filters.per_page) queryParams.append('per_page', filters.per_page)
      if (filters.page) queryParams.append('page', filters.page)

      const response = await fetch(`/api/admin/countries?${queryParams}`)

      if (!response.ok) {
        throw new Error('Failed to fetch countries')
      }

      const data = await response.json()
      countries.value = data
    } catch (err) {
      error.value = err.message
      console.error('Error fetching countries:', err)
    } finally {
      loading.value = false
    }
  }

  const getCountry = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/admin/countries/${id}`)

      if (!response.ok) {
        throw new Error('Failed to fetch country')
      }

      return await response.json()
    } catch (err) {
      error.value = err.message
      console.error('Error fetching country:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const createCountry = async (formData) => {
    loading.value = true
    error.value = null

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

      const response = await fetch('/api/admin/countries', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken
        },
        body: formData
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Failed to create country')
      }

      return data
    } catch (err) {
      error.value = err.message
      console.error('Error creating country:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateCountry = async (id, formData) => {
    loading.value = true
    error.value = null

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

      const response = await fetch(`/api/admin/countries/${id}`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: formData
      })

      const data = await response.json()

      if (!response.ok) {
        // Log detailed validation errors
        console.error('Validation errors:', data.errors)
        const errorMessages = data.errors
          ? Object.values(data.errors).flat().join(', ')
          : data.message
        throw new Error(errorMessages || 'Failed to update country')
      }

      return data
    } catch (err) {
      error.value = err.message
      console.error('Error updating country:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteCountry = async (id) => {
    loading.value = true
    error.value = null

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

      const response = await fetch(`/api/admin/countries/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Content-Type': 'application/json'
        }
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Failed to delete country')
      }

      return data
    } catch (err) {
      error.value = err.message
      console.error('Error deleting country:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    countries,
    loading,
    error,
    fetchCountries,
    getCountry,
    createCountry,
    updateCountry,
    deleteCountry
  }
}
