import { ref, onMounted, onUnmounted } from 'vue'

export function useCommandMonitor() {
    const commandData = ref(null)
    const activeTimeframe = ref(24)
    const loading = ref(false)
    const error = ref(null)
    let pollInterval = null

    const fetchData = async (hours = null) => {
        const h = hours ?? activeTimeframe.value
        loading.value = true
        error.value = null

        try {
            const response = await fetch(`/api/admin/command-monitor?hours=${h}`)

            if (!response.ok) {
                throw new Error('Failed to fetch command monitor data')
            }

            commandData.value = await response.json()
        } catch (err) {
            error.value = err.message
            console.error('Error fetching command monitor:', err)
        } finally {
            loading.value = false
        }
    }

    const fetchProgress = async () => {
        try {
            const response = await fetch('/api/admin/command-monitor/progress')
            if (response.ok) {
                const data = await response.json()
                if (commandData.value) {
                    commandData.value.ai_progress = data.ai_progress
                }
            }
        } catch (err) {
            console.error('Error fetching progress:', err)
        }
    }

    const changeTimeframe = async (hours) => {
        activeTimeframe.value = hours
        await fetchData(hours)
    }

    const startPolling = () => {
        pollInterval = setInterval(fetchProgress, 30000)
    }

    const stopPolling = () => {
        if (pollInterval) {
            clearInterval(pollInterval)
            pollInterval = null
        }
    }

    onMounted(() => {
        fetchData()
        startPolling()
    })

    onUnmounted(() => {
        stopPolling()
    })

    return {
        commandData,
        activeTimeframe,
        loading,
        error,
        fetchData,
        changeTimeframe,
    }
}
