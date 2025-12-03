import { ref } from 'vue'

export function useSubCategories() {
  const subcategories = ref({
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
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

      const data = await response.json()
      subcategories.value = data
    } catch (err) {
      error.value = err.message
      console.error('Error fetching sub-categories:', err)
    } finally {
      loading.value = false
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
    console.log('🔄 Toggle Featured called for subcategory ID:', id)
    try {
      const url = `/api/admin/subcategories/${id}/toggle-featured`
      console.log('📡 Calling:', url)

      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
      })

      console.log('📡 Response status:', response.status)

      if (!response.ok) {
        const errorText = await response.text()
        console.error('❌ Response error:', errorText)
        throw new Error('Failed to toggle featured status')
      }

      const data = await response.json()
      console.log('✅ Toggle Featured success:', data)
      return data
    } catch (err) {
      error.value = err.message
      console.error('❌ Error toggling featured status:', err)
      throw err
    }
  }

  const togglePopular = async (id) => {
    console.log('🔄 Toggle Popular called for subcategory ID:', id)
    try {
      const url = `/api/admin/subcategories/${id}/toggle-popular`
      console.log('📡 Calling:', url)

      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
      })

      console.log('📡 Response status:', response.status)

      if (!response.ok) {
        const errorText = await response.text()
        console.error('❌ Response error:', errorText)
        throw new Error('Failed to toggle popular status')
      }

      const data = await response.json()
      console.log('✅ Toggle Popular success:', data)
      return data
    } catch (err) {
      error.value = err.message
      console.error('❌ Error toggling popular status:', err)
      throw err
    }
  }

  return {
    subcategories,
    loading,
    error,
    fetchSubCategories,
    getSubCategory,
    createSubCategory,
    updateSubCategory,
    deleteSubCategory,
    toggleFeatured,
    togglePopular
  }
}
