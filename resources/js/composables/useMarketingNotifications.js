import { ref } from 'vue'

export function useMarketingNotifications() {
    const logs = ref({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
    })
    const stats = ref([])
    const deliveryRate = ref(0)
    const campaignChart = ref(null)
    const weekTrend = ref(null)
    const users = ref([])
    const loading = ref(false)
    const error = ref(null)

    const fetchLogs = async (filters = {}) => {
        loading.value = true
        error.value = null

        try {
            const params = new URLSearchParams()

            if (filters.search) params.append('search', filters.search)
            if (filters.sort_by) params.append('sort_by', filters.sort_by)
            if (filters.sort_direction) params.append('sort_direction', filters.sort_direction)
            if (filters.page) params.append('page', filters.page)
            if (filters.per_page) params.append('per_page', filters.per_page)

            const response = await fetch(`/api/admin/marketing-notifications?${params.toString()}`)

            if (!response.ok) {
                const errorText = await response.text()
                console.error('Fetch logs error response:', errorText)
                throw new Error('Failed to fetch notification logs')
            }

            const data = await response.json()

            if (data.logs) {
                logs.value = data.logs
            } else {
                logs.value = data
            }
        } catch (err) {
            error.value = err.message
            console.error('Error fetching notification logs:', err)
        } finally {
            loading.value = false
        }
    }

    const fetchStats = async () => {
        try {
            const response = await fetch('/api/admin/marketing-notifications/stats')

            if (!response.ok) {
                throw new Error('Failed to fetch stats')
            }

            const data = await response.json()
            stats.value = data.stats
            deliveryRate.value = data.delivery_rate || 0
            campaignChart.value = data.campaign_chart || null
            weekTrend.value = data.week_trend || null
        } catch (err) {
            console.error('Error fetching notification stats:', err)
        }
    }

    const fetchActiveUsers = async (search = '') => {
        try {
            const url = `/api/admin/marketing-notifications/active-users?search=${encodeURIComponent(search)}`
            const response = await fetch(url)

            if (!response.ok) {
                throw new Error('Failed to fetch users')
            }

            const data = await response.json()
            users.value = data.users
        } catch (err) {
            console.error('Error fetching active users:', err)
        }
    }

    const sendToAll = async (notificationData) => {
        try {
            const response = await fetch('/api/admin/marketing-notifications/send-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(notificationData)
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || errorData.message || 'Failed to send notification')
            }

            return await response.json()
        } catch (err) {
            console.error('Error sending notification:', err)
            throw err
        }
    }

    const sendTest = async (notificationData) => {
        try {
            const response = await fetch('/api/admin/marketing-notifications/send-test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(notificationData)
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || errorData.message || 'Failed to send test notification')
            }

            return await response.json()
        } catch (err) {
            console.error('Error sending test notification:', err)
            throw err
        }
    }

    const deleteLog = async (logId) => {
        try {
            const response = await fetch(`/api/admin/marketing-notifications/${logId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || errorData.message || 'Failed to delete log')
            }

            return await response.json()
        } catch (err) {
            console.error('Error deleting log:', err)
            throw err
        }
    }

    return {
        logs,
        stats,
        deliveryRate,
        campaignChart,
        weekTrend,
        users,
        loading,
        error,
        fetchLogs,
        fetchStats,
        fetchActiveUsers,
        sendToAll,
        sendTest,
        deleteLog
    }
}
