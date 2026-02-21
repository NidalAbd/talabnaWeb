import { ref } from 'vue'

export function useLanguages() {
  const languages = ref({
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })
  const loading = ref(false)
  const error = ref(null)

  /**
   * Fetch languages with filters
   */
  const fetchLanguages = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const queryParams = new URLSearchParams()

      if (params.search) queryParams.append('search', params.search)
      if (params.status) queryParams.append('status', params.status)
      if (params.direction) queryParams.append('direction', params.direction)
      if (params.sort_by) queryParams.append('sort_by', params.sort_by)
      if (params.sort_direction) queryParams.append('sort_direction', params.sort_direction)
      if (params.per_page) queryParams.append('per_page', params.per_page)
      if (params.page) queryParams.append('page', params.page)

      const response = await fetch(`/api/admin/languages?${queryParams.toString()}`, {
        credentials: 'same-origin'
      })

      if (!response.ok) {
        throw new Error('Failed to fetch languages')
      }

      const data = await response.json()
      languages.value = data
    } catch (err) {
      error.value = err.message
      console.error('Error fetching languages:', err)
    } finally {
      loading.value = false
    }
  }

  /**
   * Get single language by ID
   */
  const getLanguage = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/admin/languages/${id}`, {
        credentials: 'same-origin'
      })

      if (!response.ok) {
        throw new Error('Failed to fetch language')
      }

      const data = await response.json()
      return data.language
    } catch (err) {
      error.value = err.message
      console.error('Error fetching language:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Create new language
   */
  const createLanguage = async (formData) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch('/api/admin/languages', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
      })

      if (!response.ok) {
        const data = await response.json()
        throw new Error(data.message || 'Failed to create language')
      }

      const data = await response.json()
      return data.language
    } catch (err) {
      error.value = err.message
      console.error('Error creating language:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Update language
   */
  const updateLanguage = async (id, formData) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/admin/languages/${id}`, {
        method: 'PUT',
        credentials: 'same-origin',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
      })

      if (!response.ok) {
        const data = await response.json()
        throw new Error(data.message || 'Failed to update language')
      }

      const data = await response.json()
      return data.language
    } catch (err) {
      error.value = err.message
      console.error('Error updating language:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Delete language
   */
  const deleteLanguage = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/admin/languages/${id}`, {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Content-Type': 'application/json'
        }
      })

      if (!response.ok) {
        const data = await response.json()
        throw new Error(data.message || 'Failed to delete language')
      }

      const data = await response.json()
      return data
    } catch (err) {
      error.value = err.message
      console.error('Error deleting language:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Toggle language active status
   */
  const toggleActive = async (id) => {
    try {
      const response = await fetch(`/api/admin/languages/${id}/toggle`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Content-Type': 'application/json'
        }
      })

      if (!response.ok) {
        const data = await response.json()
        throw new Error(data.message || 'Failed to toggle status')
      }

      const data = await response.json()
      return data
    } catch (err) {
      error.value = err.message
      console.error('Error toggling status:', err)
      throw err
    }
  }

  /**
   * Set language as default
   */
  const setDefault = async (id) => {
    try {
      const response = await fetch(`/api/admin/languages/${id}/set-default`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Content-Type': 'application/json'
        }
      })

      if (!response.ok) {
        const data = await response.json()
        throw new Error(data.message || 'Failed to set default language')
      }

      const data = await response.json()
      return data
    } catch (err) {
      error.value = err.message
      console.error('Error setting default:', err)
      throw err
    }
  }

  return {
    languages,
    loading,
    error,
    fetchLanguages,
    getLanguage,
    createLanguage,
    updateLanguage,
    deleteLanguage,
    toggleActive,
    setDefault
  }
}
