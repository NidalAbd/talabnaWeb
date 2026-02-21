import { ref } from 'vue'

export function useTranslations() {
  const translations = ref({
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: 25,
    total: 0
  })
  const groups = ref([])
  const loading = ref(false)
  const error = ref(null)

  /**
   * Fetch translations with filters
   */
  const fetchTranslations = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const queryParams = new URLSearchParams()

      if (params.search) queryParams.append('search', params.search)
      if (params.locale) queryParams.append('locale', params.locale)
      if (params.group) queryParams.append('group', params.group)
      if (params.sort_by) queryParams.append('sort_by', params.sort_by)
      if (params.sort_direction) queryParams.append('sort_direction', params.sort_direction)
      if (params.per_page) queryParams.append('per_page', params.per_page)
      if (params.page) queryParams.append('page', params.page)

      const response = await fetch(`/api/admin/translations?${queryParams.toString()}`, {
        credentials: 'same-origin'
      })

      if (!response.ok) {
        throw new Error('Failed to fetch translations')
      }

      const data = await response.json()
      translations.value = data
    } catch (err) {
      error.value = err.message
      console.error('Error fetching translations:', err)
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch available groups
   */
  const fetchGroups = async () => {
    try {
      const response = await fetch('/api/admin/translations/groups', {
        credentials: 'same-origin'
      })

      if (!response.ok) {
        throw new Error('Failed to fetch groups')
      }

      const data = await response.json()
      groups.value = data.groups || []
    } catch (err) {
      error.value = err.message
      console.error('Error fetching groups:', err)
    }
  }

  /**
   * Get single translation by ID
   */
  const getTranslation = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/admin/translations/${id}`, {
        credentials: 'same-origin'
      })

      if (!response.ok) {
        throw new Error('Failed to fetch translation')
      }

      const data = await response.json()
      return data.translation
    } catch (err) {
      error.value = err.message
      console.error('Error fetching translation:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Create new translation
   */
  const createTranslation = async (formData) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch('/api/admin/translations', {
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
        throw new Error(data.message || 'Failed to create translation')
      }

      const data = await response.json()
      return data.translation
    } catch (err) {
      error.value = err.message
      console.error('Error creating translation:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Update translation
   */
  const updateTranslation = async (id, formData) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/admin/translations/${id}`, {
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
        throw new Error(data.message || 'Failed to update translation')
      }

      const data = await response.json()
      return data.translation
    } catch (err) {
      error.value = err.message
      console.error('Error updating translation:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Delete translation
   */
  const deleteTranslation = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/admin/translations/${id}`, {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Content-Type': 'application/json'
        }
      })

      if (!response.ok) {
        const data = await response.json()
        throw new Error(data.message || 'Failed to delete translation')
      }

      const data = await response.json()
      return data
    } catch (err) {
      error.value = err.message
      console.error('Error deleting translation:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Export translations
   */
  const exportTranslations = async (locale = null) => {
    try {
      const url = locale
        ? `/api/admin/translations/export?locale=${locale}`
        : '/api/admin/translations/export'

      const response = await fetch(url, {
        credentials: 'same-origin'
      })

      if (!response.ok) {
        throw new Error('Failed to export translations')
      }

      const data = await response.json()
      return data
    } catch (err) {
      error.value = err.message
      console.error('Error exporting translations:', err)
      throw err
    }
  }

  /**
   * Import translations
   */
  const importTranslations = async (file, locale) => {
    loading.value = true
    error.value = null

    try {
      const formData = new FormData()
      formData.append('file', file)
      formData.append('locale', locale)

      const response = await fetch('/api/admin/translations/import', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
      })

      if (!response.ok) {
        const data = await response.json()
        throw new Error(data.message || 'Failed to import translations')
      }

      const data = await response.json()
      return data
    } catch (err) {
      error.value = err.message
      console.error('Error importing translations:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Bulk update translations
   */
  const bulkUpdate = async (translationsData) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch('/api/admin/translations/bulk', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ translations: translationsData })
      })

      if (!response.ok) {
        const data = await response.json()
        throw new Error(data.message || 'Failed to bulk update translations')
      }

      const data = await response.json()
      return data
    } catch (err) {
      error.value = err.message
      console.error('Error bulk updating translations:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    translations,
    groups,
    loading,
    error,
    fetchTranslations,
    fetchGroups,
    getTranslation,
    createTranslation,
    updateTranslation,
    deleteTranslation,
    exportTranslations,
    importTranslations,
    bulkUpdate
  }
}
