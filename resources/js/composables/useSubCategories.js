import { ref } from 'vue'

export function useSubCategories() {
  const subcategories = ref({
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })
  const stats = ref({
    total: 0,
    featured: 0,
    popular: 0,
    parentCategories: 0
  })
  const loading = ref(false)
  const error = ref(null)

  const fetchSubCategories = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const queryString = new URLSearchParams(params).toString()
      const response = await fetch(`/api/admin/subcategories?${queryString}`)

      if (!response.ok) {
        throw new Error('Failed to fetch sub-categories')
      }

      subcategories.value = await response.json()
    } catch (err) {
      error.value = err.message
      console.error('Error fetching sub-categories:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchStats = async () => {
    try {
      const response = await fetch('/api/admin/subcategories/stats')
      if (!response.ok) {
        throw new Error('Failed to fetch stats')
      }
      const data = await response.json()
      // Extract values from the stats array
      stats.value = {
        total: data.stats?.find(s => s.label === 'Total Sub-Categories')?.value || 0,
        featured: data.stats?.find(s => s.label === 'Featured Sub-Categories')?.value || 0,
        popular: data.stats?.find(s => s.label === 'Popular Sub-Categories')?.value || 0,
        parentCategories: data.top_categories?.length || 0
      }
    } catch (err) {
      console.error('Error fetching stats:', err)
    }
  }

  const getSubCategory = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/admin/subcategories/${id}`)

      if (!response.ok) {
        throw new Error('Failed to fetch sub-category')
      }

      return await response.json()
    } catch (err) {
      error.value = err.message
      console.error('Error fetching sub-category:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const createSubCategory = async (formData) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch('/api/admin/subcategories', {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Failed to create sub-category')
      }

      return await response.json()
    } catch (err) {
      error.value = err.message
      console.error('Error creating sub-category:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateSubCategory = async (id, formData) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/admin/subcategories/${id}`, {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Failed to update sub-category')
      }

      return await response.json()
    } catch (err) {
      error.value = err.message
      console.error('Error updating sub-category:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteSubCategory = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/admin/subcategories/${id}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Failed to delete sub-category')
      }

      return await response.json()
    } catch (err) {
      error.value = err.message
      console.error('Error deleting sub-category:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const toggleFeatured = async (id) => {
    try {
      const response = await fetch(`/api/admin/subcategories/${id}/toggle-featured`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
      })

      if (!response.ok) {
        throw new Error('Failed to toggle featured status')
      }

      return await response.json()
    } catch (err) {
      error.value = err.message
      console.error('Error toggling featured status:', err)
      throw err
    }
  }

  const togglePopular = async (id) => {
    try {
      const response = await fetch(`/api/admin/subcategories/${id}/toggle-popular`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
      })

      if (!response.ok) {
        throw new Error('Failed to toggle popular status')
      }

      return await response.json()
    } catch (err) {
      error.value = err.message
      console.error('Error toggling popular status:', err)
      throw err
    }
  }

  return {
    subcategories,
    stats,
    loading,
    error,
    fetchSubCategories,
    fetchStats,
    getSubCategory,
    createSubCategory,
    updateSubCategory,
    deleteSubCategory,
    toggleFeatured,
    togglePopular
  }
}
