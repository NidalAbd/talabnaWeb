import { ref } from 'vue'

export function usePointPurchaseRequests() {
    const requests = ref({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
    })
    const stats = ref([])
    const loading = ref(false)
    const error = ref(null)

    const fetchRequests = async (filters = {}) => {
        loading.value = true
        error.value = null

        try {
            const params = new URLSearchParams()

            if (filters.search) params.append('search', filters.search)
            if (filters.status) params.append('status', filters.status)
            if (filters.sort_by) params.append('sort_by', filters.sort_by)
            if (filters.sort_direction) params.append('sort_direction', filters.sort_direction)
            if (filters.page) params.append('page', filters.page)
            if (filters.per_page) params.append('per_page', filters.per_page)

            const url = `/api/admin/point-purchase-requests?${params.toString()}`
            console.log('🔍 Fetching purchase requests from:', url)

            const response = await fetch(url)
            console.log('📡 Response status:', response.status, response.statusText)

            if (!response.ok) {
                const errorText = await response.text()
                console.error('❌ Response error:', errorText)
                throw new Error('Failed to fetch purchase requests')
            }

            const data = await response.json()
            console.log('✅ Purchase requests data received:', data)

            if (data.requests) {
                requests.value = data.requests
            } else {
                requests.value = data
            }
        } catch (err) {
            error.value = err.message
            console.error('❌ Error fetching purchase requests:', err)
        } finally {
            loading.value = false
            console.log('⏱️ Loading complete. Requests count:', requests.value.data?.length || 0)
        }
    }

    const fetchStats = async () => {
        try {
            const response = await fetch('/api/admin/point-purchase-requests/stats')

            if (!response.ok) {
                throw new Error('Failed to fetch stats')
            }

            const data = await response.json()
            stats.value = data.stats
        } catch (err) {
            console.error('Error fetching purchase request stats:', err)
        }
    }

    const approveRequest = async (requestId) => {
        try {
            const response = await fetch(`/api/admin/point-purchase-requests/${requestId}/approve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to approve request')
            }

            return await response.json()
        } catch (err) {
            console.error('Error approving request:', err)
            throw err
        }
    }

    const cancelRequest = async (requestId) => {
        try {
            const response = await fetch(`/api/admin/point-purchase-requests/${requestId}/cancel`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to cancel request')
            }

            return await response.json()
        } catch (err) {
            console.error('Error cancelling request:', err)
            throw err
        }
    }

    const deleteRequest = async (requestId) => {
        try {
            const response = await fetch(`/api/admin/point-purchase-requests/${requestId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to delete request')
            }

            return await response.json()
        } catch (err) {
            console.error('Error deleting request:', err)
            throw err
        }
    }

    const bulkApprove = async () => {
        try {
            const response = await fetch('/api/admin/point-purchase-requests/bulk-approve', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to approve requests')
            }

            return await response.json()
        } catch (err) {
            console.error('Error bulk approving:', err)
            throw err
        }
    }

    return {
        requests,
        stats,
        loading,
        error,
        fetchRequests,
        fetchStats,
        approveRequest,
        cancelRequest,
        deleteRequest,
        bulkApprove
    }
}
