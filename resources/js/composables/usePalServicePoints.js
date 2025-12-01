import { ref } from 'vue'

export function usePalServicePoints() {
    const pointsData = ref([])
    const stats = ref(null)
    const topUsers = ref([])
    const loading = ref(false)
    const error = ref(null)
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
    })

    const fetchPointsData = async (page = 1, filters = {}) => {
        loading.value = true
        error.value = null

        try {
            const params = new URLSearchParams({
                page,
                per_page: pagination.value.per_page,
                ...filters
            })

            const response = await fetch(`/api/admin/palservice-points?${params}`)

            if (!response.ok) {
                throw new Error('Failed to fetch points data')
            }

            const data = await response.json()
            pointsData.value = data.data
            pagination.value = {
                current_page: data.current_page,
                last_page: data.last_page,
                per_page: data.per_page,
                total: data.total
            }
        } catch (err) {
            error.value = err.message
            console.error('❌ Error fetching points data:', err)
        } finally {
            loading.value = false
        }
    }

    const fetchStats = async () => {
        try {
            const response = await fetch('/api/admin/palservice-points/stats')

            if (!response.ok) {
                throw new Error('Failed to fetch stats')
            }

            const data = await response.json()
            stats.value = data
        } catch (err) {
            console.error('❌ Error fetching stats:', err)
        }
    }

    const fetchTopUsers = async (limit = 10) => {
        try {
            const response = await fetch(`/api/admin/palservice-points/top-users?limit=${limit}`)

            if (!response.ok) {
                throw new Error('Failed to fetch top users')
            }

            const data = await response.json()
            topUsers.value = data
        } catch (err) {
            console.error('❌ Error fetching top users:', err)
        }
    }

    return {
        pointsData,
        stats,
        topUsers,
        loading,
        error,
        pagination,
        fetchPointsData,
        fetchStats,
        fetchTopUsers
    }
}
