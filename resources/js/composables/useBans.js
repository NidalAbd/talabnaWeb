import { ref } from 'vue'

export function useBans() {
    const bannedUsers = ref({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
    })
    const bannedDevices = ref({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
    })
    const stats = ref([])
    const loading = ref(false)
    const error = ref(null)

    const fetchBannedUsers = async (filters = {}) => {
        loading.value = true
        error.value = null

        try {
            const params = new URLSearchParams()

            if (filters.search) params.append('search', filters.search)
            if (filters.sort_by) params.append('sort_by', filters.sort_by)
            if (filters.sort_direction) params.append('sort_direction', filters.sort_direction)
            if (filters.page) params.append('page', filters.page)
            if (filters.per_page) params.append('per_page', filters.per_page)

            const url = `/api/admin/bans/users?${params.toString()}`
            console.log('🔍 Fetching banned users from:', url)

            const response = await fetch(url)
            console.log('📡 Response status:', response.status, response.statusText)

            if (!response.ok) {
                const errorText = await response.text()
                console.error('❌ Response error:', errorText)
                throw new Error('Failed to fetch banned users')
            }

            const data = await response.json()
            console.log('✅ Banned users data received:', data)

            if (data.banned_users) {
                bannedUsers.value = data.banned_users
            } else {
                bannedUsers.value = data
            }
        } catch (err) {
            error.value = err.message
            console.error('❌ Error fetching banned users:', err)
        } finally {
            loading.value = false
            console.log('⏱️ Loading complete. Banned users count:', bannedUsers.value.data?.length || 0)
        }
    }

    const fetchBannedDevices = async (filters = {}) => {
        loading.value = true
        error.value = null

        try {
            const params = new URLSearchParams()

            if (filters.search) params.append('search', filters.search)
            if (filters.active_only !== undefined) params.append('active_only', filters.active_only)
            if (filters.sort_by) params.append('sort_by', filters.sort_by)
            if (filters.sort_direction) params.append('sort_direction', filters.sort_direction)
            if (filters.page) params.append('page', filters.page)
            if (filters.per_page) params.append('per_page', filters.per_page)

            const url = `/api/admin/bans/devices?${params.toString()}`
            console.log('🔍 Fetching banned devices from:', url)

            const response = await fetch(url)
            console.log('📡 Response status:', response.status, response.statusText)

            if (!response.ok) {
                const errorText = await response.text()
                console.error('❌ Response error:', errorText)
                throw new Error('Failed to fetch banned devices')
            }

            const data = await response.json()
            console.log('✅ Banned devices data received:', data)

            if (data.banned_devices) {
                bannedDevices.value = data.banned_devices
            } else {
                bannedDevices.value = data
            }
        } catch (err) {
            error.value = err.message
            console.error('❌ Error fetching banned devices:', err)
        } finally {
            loading.value = false
            console.log('⏱️ Loading complete. Banned devices count:', bannedDevices.value.data?.length || 0)
        }
    }

    const fetchStats = async () => {
        try {
            const response = await fetch('/api/admin/bans/stats')

            if (!response.ok) {
                throw new Error('Failed to fetch stats')
            }

            const data = await response.json()
            stats.value = data.stats
        } catch (err) {
            console.error('Error fetching ban stats:', err)
        }
    }

    const toggleUserBan = async (userId, action, reason = null) => {
        try {
            const response = await fetch(`/api/admin/bans/users/${userId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ action, reason })
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to toggle user ban')
            }

            return await response.json()
        } catch (err) {
            console.error('Error toggling user ban:', err)
            throw err
        }
    }

    const unbanDevice = async (deviceId, reason = null) => {
        try {
            const response = await fetch(`/api/admin/bans/devices/${deviceId}/unban`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ reason })
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to unban device')
            }

            return await response.json()
        } catch (err) {
            console.error('Error unbanning device:', err)
            throw err
        }
    }

    return {
        bannedUsers,
        bannedDevices,
        stats,
        loading,
        error,
        fetchBannedUsers,
        fetchBannedDevices,
        fetchStats,
        toggleUserBan,
        unbanDevice
    }
}
