import { ref } from 'vue'

export function useAnalytics() {
    const overview = ref([])
    const tabData = ref(null)
    const activeTab = ref('users')
    const activeDays = ref(30)
    const loading = ref(false)
    const error = ref(null)

    const fetchAnalytics = async (tab = null) => {
        const t = tab || activeTab.value
        loading.value = true
        error.value = null

        try {
            const params = new URLSearchParams({
                days: activeDays.value,
                tab: t,
            })

            const response = await fetch(`/api/admin/analytics?${params.toString()}`)

            if (!response.ok) {
                throw new Error('Failed to fetch analytics')
            }

            const data = await response.json()
            overview.value = data.overview
            tabData.value = data.data
            activeTab.value = data.tab
        } catch (err) {
            error.value = err.message
            console.error('Error fetching analytics:', err)
        } finally {
            loading.value = false
        }
    }

    const changeRange = async (days) => {
        activeDays.value = days
        await fetchAnalytics()
    }

    const changeTab = async (tab) => {
        activeTab.value = tab
        await fetchAnalytics(tab)
    }

    return {
        overview,
        tabData,
        activeTab,
        activeDays,
        loading,
        error,
        fetchAnalytics,
        changeRange,
        changeTab,
    }
}
