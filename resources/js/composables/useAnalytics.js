import { ref } from 'vue'

export function useAnalytics() {
    const userAnalytics = ref(null)
    const pointAnalytics = ref(null)
    const postAnalytics = ref(null)
    const overview = ref([])
    const loading = ref(false)
    const error = ref(null)

    const fetchUserAnalytics = async () => {
        loading.value = true
        error.value = null

        try {
            const response = await fetch('/api/admin/analytics/users')

            if (!response.ok) {
                throw new Error('Failed to fetch user analytics')
            }

            const data = await response.json()
            userAnalytics.value = data
        } catch (err) {
            error.value = err.message
            console.error('❌ Error fetching user analytics:', err)
        } finally {
            loading.value = false
        }
    }

    const fetchPointAnalytics = async () => {
        loading.value = true
        error.value = null

        try {
            const response = await fetch('/api/admin/analytics/points')

            if (!response.ok) {
                throw new Error('Failed to fetch point analytics')
            }

            const data = await response.json()
            pointAnalytics.value = data
        } catch (err) {
            error.value = err.message
            console.error('❌ Error fetching point analytics:', err)
        } finally {
            loading.value = false
        }
    }

    const fetchPostAnalytics = async () => {
        loading.value = true
        error.value = null

        try {
            const response = await fetch('/api/admin/analytics/posts')

            if (!response.ok) {
                throw new Error('Failed to fetch post analytics')
            }

            const data = await response.json()
            postAnalytics.value = data
        } catch (err) {
            error.value = err.message
            console.error('❌ Error fetching post analytics:', err)
        } finally {
            loading.value = false
        }
    }

    const fetchOverview = async () => {
        try {
            const response = await fetch('/api/admin/analytics/overview')

            if (!response.ok) {
                throw new Error('Failed to fetch overview')
            }

            const data = await response.json()
            overview.value = data.stats
        } catch (err) {
            console.error('❌ Error fetching overview:', err)
        }
    }

    return {
        userAnalytics,
        pointAnalytics,
        postAnalytics,
        overview,
        loading,
        error,
        fetchUserAnalytics,
        fetchPointAnalytics,
        fetchPostAnalytics,
        fetchOverview
    }
}
