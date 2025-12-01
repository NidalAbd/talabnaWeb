import { ref } from 'vue'

export function useLevels() {
    const levels = ref({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
    })
    const stats = ref([])
    const loading = ref(false)
    const error = ref(null)

    const fetchLevels = async (filters = {}) => {
        loading.value = true
        error.value = null

        try {
            const params = new URLSearchParams()

            if (filters.search) params.append('search', filters.search)
            if (filters.status) params.append('status', filters.status)
            if (filters.premium) params.append('premium', filters.premium)
            if (filters.sort_by) params.append('sort_by', filters.sort_by)
            if (filters.sort_direction) params.append('sort_direction', filters.sort_direction)
            if (filters.page) params.append('page', filters.page)
            if (filters.per_page) params.append('per_page', filters.per_page)

            const url = `/api/admin/levels?${params.toString()}`
            console.log('🔍 Fetching levels from:', url)

            const response = await fetch(url)
            console.log('📡 Response status:', response.status, response.statusText)

            if (!response.ok) {
                const errorText = await response.text()
                console.error('❌ Response error:', errorText)
                throw new Error('Failed to fetch levels')
            }

            const data = await response.json()
            console.log('✅ Levels data received:', data)

            if (data.levels) {
                levels.value = data.levels
            } else {
                levels.value = data
            }
        } catch (err) {
            error.value = err.message
            console.error('❌ Error fetching levels:', err)
        } finally {
            loading.value = false
            console.log('⏱️ Loading complete. Levels count:', levels.value.data?.length || 0)
        }
    }

    const fetchStats = async () => {
        try {
            const response = await fetch('/api/admin/levels/stats')

            if (!response.ok) {
                throw new Error('Failed to fetch stats')
            }

            const data = await response.json()
            stats.value = data.stats
        } catch (err) {
            console.error('Error fetching level stats:', err)
        }
    }

    const createLevel = async (levelData) => {
        try {
            const response = await fetch('/api/admin/levels', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(levelData)
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to create level')
            }

            return await response.json()
        } catch (err) {
            console.error('Error creating level:', err)
            throw err
        }
    }

    const updateLevel = async (levelId, levelData) => {
        try {
            const response = await fetch(`/api/admin/levels/${levelId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(levelData)
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to update level')
            }

            return await response.json()
        } catch (err) {
            console.error('Error updating level:', err)
            throw err
        }
    }

    const deleteLevel = async (levelId) => {
        try {
            const response = await fetch(`/api/admin/levels/${levelId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to delete level')
            }

            return await response.json()
        } catch (err) {
            console.error('Error deleting level:', err)
            throw err
        }
    }

    const toggleStatus = async (levelId) => {
        try {
            const response = await fetch(`/api/admin/levels/${levelId}/toggle-status`, {
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

    const duplicateLevel = async (levelId) => {
        try {
            const response = await fetch(`/api/admin/levels/${levelId}/duplicate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to duplicate level')
            }

            return await response.json()
        } catch (err) {
            console.error('Error duplicating level:', err)
            throw err
        }
    }

    const updateOrder = async (levelsOrder) => {
        try {
            const response = await fetch('/api/admin/levels/update-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ levels: levelsOrder })
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to update order')
            }

            return await response.json()
        } catch (err) {
            console.error('Error updating order:', err)
            throw err
        }
    }

    const bulkActivate = async () => {
        try {
            const response = await fetch('/api/admin/levels/bulk-activate', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to activate levels')
            }

            return await response.json()
        } catch (err) {
            console.error('Error bulk activating:', err)
            throw err
        }
    }

    const bulkDeactivate = async () => {
        try {
            const response = await fetch('/api/admin/levels/bulk-deactivate', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to deactivate levels')
            }

            return await response.json()
        } catch (err) {
            console.error('Error bulk deactivating:', err)
            throw err
        }
    }

    return {
        levels,
        stats,
        loading,
        error,
        fetchLevels,
        fetchStats,
        createLevel,
        updateLevel,
        deleteLevel,
        toggleStatus,
        duplicateLevel,
        updateOrder,
        bulkActivate,
        bulkDeactivate
    }
}
