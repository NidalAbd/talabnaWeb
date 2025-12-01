import { ref } from 'vue'

export function useServicePosts() {
    const servicePosts = ref({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 25,
        total: 0
    })
    const loading = ref(false)
    const error = ref(null)
    const categories = ref([])
    const subcategories = ref([])

    const fetchServicePosts = async (filters = {}) => {
        loading.value = true
        error.value = null

        try {
            const params = new URLSearchParams()

            if (filters.search) params.append('search', filters.search)
            if (filters.category) params.append('category', filters.category)
            if (filters.subcategory) params.append('subcategory', filters.subcategory)
            if (filters.status) params.append('status', filters.status)
            if (filters.type) params.append('type', filters.type)
            if (filters.sort_by) params.append('sort_by', filters.sort_by)
            if (filters.sort_direction) params.append('sort_direction', filters.sort_direction)
            if (filters.page) params.append('page', filters.page)
            if (filters.per_page) params.append('per_page', filters.per_page)

            const url = `/api/admin/service-posts?${params.toString()}`
            console.log('🔍 Fetching service posts from:', url)

            const response = await fetch(url)
            console.log('📡 Response status:', response.status, response.statusText)

            if (!response.ok) {
                const errorText = await response.text()
                console.error('❌ Response error:', errorText)
                throw new Error('Failed to fetch service posts')
            }

            const data = await response.json()
            console.log('✅ Service posts data received:', data)

            // Check if data is wrapped in 'service_posts' key or is direct pagination
            if (data.service_posts) {
                servicePosts.value = data.service_posts
            } else {
                servicePosts.value = data
            }
        } catch (err) {
            error.value = err.message
            console.error('❌ Error fetching service posts:', err)
        } finally {
            loading.value = false
            console.log('⏱️ Loading complete. Service posts count:', servicePosts.value.data?.length || 0)
        }
    }

    const fetchCategories = async () => {
        try {
            const response = await fetch('/api/admin/service-posts/categories')

            if (!response.ok) {
                throw new Error('Failed to fetch categories')
            }

            const data = await response.json()
            categories.value = data.categories
        } catch (err) {
            console.error('Error fetching categories:', err)
            throw err
        }
    }

    const fetchSubcategories = async (categoryId) => {
        if (!categoryId) {
            subcategories.value = []
            return
        }

        try {
            const response = await fetch(`/api/admin/service-posts/subcategories?category_id=${categoryId}`)

            if (!response.ok) {
                throw new Error('Failed to fetch subcategories')
            }

            const data = await response.json()
            subcategories.value = data.subcategories
        } catch (err) {
            console.error('Error fetching subcategories:', err)
            subcategories.value = []
        }
    }

    const deleteServicePost = async (postId) => {
        try {
            const response = await fetch(`/api/admin/service-posts/${postId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to delete service post')
            }

            return await response.json()
        } catch (err) {
            console.error('Error deleting service post:', err)
            throw err
        }
    }

    const bulkDeleteServicePosts = async (postIds) => {
        try {
            const response = await fetch('/api/admin/service-posts/bulk-destroy', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ post_ids: postIds })
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to delete service posts')
            }

            return await response.json()
        } catch (err) {
            console.error('Error bulk deleting service posts:', err)
            throw err
        }
    }

    return {
        servicePosts,
        loading,
        error,
        categories,
        subcategories,
        fetchServicePosts,
        fetchCategories,
        fetchSubcategories,
        deleteServicePost,
        bulkDeleteServicePosts
    }
}
