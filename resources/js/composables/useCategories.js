import { ref } from 'vue'

export function useCategories() {
  const categories = ref({
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })
  const loading = ref(false)
  const error = ref(null)

  /**
   * Fetch categories with filters
   */
  const fetchCategories = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const queryParams = new URLSearchParams()

      if (params.search) queryParams.append('search', params.search)
      if (params.status) queryParams.append('status', params.status)
      if (params.featured) queryParams.append('featured', params.featured)
      if (params.popular) queryParams.append('popular', params.popular)
      if (params.sort_by) queryParams.append('sort_by', params.sort_by)
      if (params.sort_direction) queryParams.append('sort_direction', params.sort_direction)
      if (params.per_page) queryParams.append('per_page', params.per_page)
      if (params.page) queryParams.append('page', params.page)

      const response = await fetch(`/api/admin/categories?${queryParams.toString()}`)

      if (!response.ok) {
        throw new Error('Failed to fetch categories')
      }

      const data = await response.json()
      categories.value = data
    } catch (err) {
      error.value = err.message
      console.error('Error fetching categories:', err)
    } finally {
      loading.value = false
    }
  }

  /**
   * Get single category by ID
   */
  const getCategory = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/admin/categories/${id}`)

      if (!response.ok) {
        throw new Error('Failed to fetch category')
      }

      const data = await response.json()
      return data.category
    } catch (err) {
      error.value = err.message
      console.error('Error fetching category:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Create new category
   */
  const createCategory = async (formData) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch('/api/admin/categories', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData // FormData for file upload
      })

      if (!response.ok) {
        const data = await response.json()
        throw new Error(data.message || 'Failed to create category')
      }

      const data = await response.json()
      return data.category
    } catch (err) {
      error.value = err.message
      console.error('Error creating category:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Update category
   */
  const updateCategory = async (id, formData) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/admin/categories/${id}`, {
        method: 'POST', // Using POST because FormData doesn't support PUT
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
      })

      if (!response.ok) {
        const data = await response.json()
        throw new Error(data.message || 'Failed to update category')
      }

      const data = await response.json()
      return data.category
    } catch (err) {
      error.value = err.message
      console.error('Error updating category:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Delete category
   */
  const deleteCategory = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/admin/categories/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Content-Type': 'application/json'
        }
      })

      if (!response.ok) {
        const data = await response.json()
        throw new Error(data.message || 'Failed to delete category')
      }

      const data = await response.json()
      return data
    } catch (err) {
      error.value = err.message
      console.error('Error deleting category:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Toggle category status (suspended)
   */
  const toggleStatus = async (id) => {
    try {
      const response = await fetch(`/api/admin/categories/${id}/toggle-status`, {
        method: 'POST',
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
   * Toggle featured status
   */
  const toggleFeatured = async (id) => {
    try {
      const response = await fetch(`/api/admin/categories/${id}/toggle-featured`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Content-Type': 'application/json'
        }
      })

      if (!response.ok) {
        const data = await response.json()
        throw new Error(data.message || 'Failed to toggle featured')
      }

      const data = await response.json()
      return data
    } catch (err) {
      error.value = err.message
      console.error('Error toggling featured:', err)
      throw err
    }
  }

  /**
   * Toggle popular status
   */
  const togglePopular = async (id) => {
    try {
      const response = await fetch(`/api/admin/categories/${id}/toggle-popular`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Content-Type': 'application/json'
        }
      })

      if (!response.ok) {
        const data = await response.json()
        throw new Error(data.message || 'Failed to toggle popular')
      }

      const data = await response.json()
      return data
    } catch (err) {
      error.value = err.message
      console.error('Error toggling popular:', err)
      throw err
    }
  }

  return {
    categories,
    loading,
    error,
    fetchCategories,
    getCategory,
    createCategory,
    updateCategory,
    deleteCategory,
    toggleStatus,
    toggleFeatured,
    togglePopular
  }
}
