import { ref } from 'vue'

export function usePremiumFeatures() {
    const features = ref({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
    })
    const stats = ref([])
    const types = ref([])
    const loading = ref(false)
    const error = ref(null)

    const fetchFeatures = async (filters = {}) => {
        loading.value = true
        error.value = null

        try {
            const params = new URLSearchParams()

            if (filters.search) params.append('search', filters.search)
            if (filters.type) params.append('type', filters.type)
            if (filters.status) params.append('status', filters.status)
            if (filters.sort_by) params.append('sort_by', filters.sort_by)
            if (filters.sort_direction) params.append('sort_direction', filters.sort_direction)
            if (filters.page) params.append('page', filters.page)
            if (filters.per_page) params.append('per_page', filters.per_page)

            const url = `/api/admin/premium-features?${params.toString()}`
            console.log('🔍 Fetching premium features from:', url)

            const response = await fetch(url)
            console.log('📡 Response status:', response.status, response.statusText)

            if (!response.ok) {
                const errorText = await response.text()
                console.error('❌ Response error:', errorText)
                throw new Error('Failed to fetch premium features')
            }

            const data = await response.json()
            console.log('✅ Premium features data received:', data)

            if (data.features) {
                features.value = data.features
            } else {
                features.value = data
            }
        } catch (err) {
            error.value = err.message
            console.error('❌ Error fetching premium features:', err)
        } finally {
            loading.value = false
            console.log('⏱️ Loading complete. Features count:', features.value.data?.length || 0)
        }
    }

    const fetchStats = async () => {
        try {
            const response = await fetch('/api/admin/premium-features/stats')

            if (!response.ok) {
                throw new Error('Failed to fetch stats')
            }

            const data = await response.json()
            stats.value = data.stats
        } catch (err) {
            console.error('Error fetching feature stats:', err)
        }
    }

    const fetchTypes = async () => {
        try {
            const response = await fetch('/api/admin/premium-features/types')

            if (!response.ok) {
                throw new Error('Failed to fetch types')
            }

            const data = await response.json()
            types.value = data.types
        } catch (err) {
            console.error('Error fetching feature types:', err)
        }
    }

    const createFeature = async (featureData) => {
        try {
            const response = await fetch('/api/admin/premium-features', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(featureData)
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to create feature')
            }

            return await response.json()
        } catch (err) {
            console.error('Error creating feature:', err)
            throw err
        }
    }

    const updateFeature = async (featureId, featureData) => {
        try {
            const response = await fetch(`/api/admin/premium-features/${featureId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(featureData)
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to update feature')
            }

            return await response.json()
        } catch (err) {
            console.error('Error updating feature:', err)
            throw err
        }
    }

    const deleteFeature = async (featureId) => {
        try {
            const response = await fetch(`/api/admin/premium-features/${featureId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to delete feature')
            }

            return await response.json()
        } catch (err) {
            console.error('Error deleting feature:', err)
            throw err
        }
    }

    const toggleStatus = async (featureId) => {
        try {
            const response = await fetch(`/api/admin/premium-features/${featureId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to toggle status')
            }

            return await response.json()
        } catch (err) {
            console.error('Error toggling status:', err)
            throw err
        }
    }

    return {
        features,
        stats,
        types,
        loading,
        error,
        fetchFeatures,
        fetchStats,
        fetchTypes,
        createFeature,
        updateFeature,
        deleteFeature,
        toggleStatus
    }
}
