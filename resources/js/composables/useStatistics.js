import { ref, computed } from 'vue'

export function useStatistics() {
    const statistics = ref(null)
    const loading = ref(false)
    const error = ref(null)

    const fetchStatistics = async () => {
        loading.value = true
        error.value = null

        try {
            const response = await fetch('/api/admin/statistics')

            if (!response.ok) {
                throw new Error('Failed to fetch statistics')
            }

            const data = await response.json()
            statistics.value = data
        } catch (err) {
            error.value = err.message
            console.error('Error fetching statistics:', err)
        } finally {
            loading.value = false
        }
    }

    const todaysPulse = computed(() => statistics.value?.todays_pulse || [])
    const actionRequired = computed(() => statistics.value?.action_required || [])
    const charts = computed(() => statistics.value?.charts || {})
    const systemRates = computed(() => statistics.value?.system_rates || [])

    return {
        statistics,
        loading,
        error,
        fetchStatistics,
        todaysPulse,
        actionRequired,
        charts,
        systemRates,
    }
}
