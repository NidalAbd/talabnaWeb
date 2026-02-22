import { ref } from 'vue'

export function useBadgeTypes() {
  const badgeTypes = ref({
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })
  const loading = ref(false)
  const error = ref(null)
  const stats = ref(null)
  const statsLoading = ref(false)

  const fetchBadgeTypes = async (filters = {}) => {
    loading.value = true
    error.value = null

    try {
      const queryParams = new URLSearchParams()

      if (filters.search) queryParams.append('search', filters.search)
      if (filters.status !== undefined && filters.status !== '') queryParams.append('status', filters.status)
      if (filters.sort_by) queryParams.append('sort_by', filters.sort_by)
      if (filters.sort_order) queryParams.append('sort_order', filters.sort_order)
      if (filters.per_page) queryParams.append('per_page', filters.per_page)
      if (filters.page) queryParams.append('page', filters.page)

      const response = await fetch(`/api/admin/badge-types?${queryParams}`)

      if (!response.ok) {
        throw new Error('Failed to fetch badge types')
      }

      const data = await response.json()
      badgeTypes.value = data
    } catch (err) {
      error.value = err.message
      console.error('Error fetching badge types:', err)
    } finally {
      loading.value = false
    }
  }

  const getBadgeType = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/admin/badge-types/${id}`)

      if (!response.ok) {
        throw new Error('Failed to fetch badge type')
      }

      return await response.json()
    } catch (err) {
      error.value = err.message
      console.error('Error fetching badge type:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const createBadgeType = async (badgeData) => {
    loading.value = true
    error.value = null

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

      const response = await fetch('/api/admin/badge-types', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(badgeData)
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Failed to create badge type')
      }

      return data
    } catch (err) {
      error.value = err.message
      console.error('Error creating badge type:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateBadgeType = async (id, badgeData) => {
    loading.value = true
    error.value = null

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

      const response = await fetch(`/api/admin/badge-types/${id}`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(badgeData)
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Failed to update badge type')
      }

      return data
    } catch (err) {
      error.value = err.message
      console.error('Error updating badge type:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteBadgeType = async (id) => {
    loading.value = true
    error.value = null

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

      const response = await fetch(`/api/admin/badge-types/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Content-Type': 'application/json'
        }
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Failed to delete badge type')
      }

      return data
    } catch (err) {
      error.value = err.message
      console.error('Error deleting badge type:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const toggleStatus = async (id) => {
    loading.value = true
    error.value = null

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

      const response = await fetch(`/api/admin/badge-types/${id}/toggle-status`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Content-Type': 'application/json'
        }
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Failed to toggle status')
      }

      return data
    } catch (err) {
      error.value = err.message
      console.error('Error toggling status:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const setDefault = async (id) => {
    loading.value = true
    error.value = null

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

      const response = await fetch(`/api/admin/badge-types/${id}/set-default`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Content-Type': 'application/json'
        }
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.message || 'Failed to set as default')
      }

      return data
    } catch (err) {
      error.value = err.message
      console.error('Error setting default:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchStats = async () => {
    statsLoading.value = true

    try {
      const response = await fetch('/api/admin/badge-types/stats')

      if (!response.ok) {
        throw new Error('Failed to fetch stats')
      }

      const data = await response.json()
      stats.value = data
    } catch (err) {
      console.error('Error fetching badge stats:', err)
    } finally {
      statsLoading.value = false
    }
  }

  return {
    badgeTypes,
    loading,
    error,
    stats,
    statsLoading,
    fetchBadgeTypes,
    getBadgeType,
    createBadgeType,
    updateBadgeType,
    deleteBadgeType,
    toggleStatus,
    setDefault,
    fetchStats
  }
}
