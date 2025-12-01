import { ref } from 'vue'

export function usePointTransactions() {
    const transactions = ref({
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

    const fetchTransactions = async (filters = {}) => {
        loading.value = true
        error.value = null

        try {
            const params = new URLSearchParams()

            if (filters.search) params.append('search', filters.search)
            if (filters.type) params.append('type', filters.type)
            if (filters.sort_by) params.append('sort_by', filters.sort_by)
            if (filters.sort_direction) params.append('sort_direction', filters.sort_direction)
            if (filters.page) params.append('page', filters.page)
            if (filters.per_page) params.append('per_page', filters.per_page)

            const url = `/api/admin/point-transactions?${params.toString()}`
            console.log('🔍 Fetching transactions from:', url)

            const response = await fetch(url)
            console.log('📡 Response status:', response.status, response.statusText)

            if (!response.ok) {
                const errorText = await response.text()
                console.error('❌ Response error:', errorText)
                throw new Error('Failed to fetch transactions')
            }

            const data = await response.json()
            console.log('✅ Transactions data received:', data)

            if (data.transactions) {
                transactions.value = data.transactions
            } else {
                transactions.value = data
            }
        } catch (err) {
            error.value = err.message
            console.error('❌ Error fetching transactions:', err)
        } finally {
            loading.value = false
            console.log('⏱️ Loading complete. Transactions count:', transactions.value.data?.length || 0)
        }
    }

    const fetchStats = async () => {
        try {
            const response = await fetch('/api/admin/point-transactions/stats')

            if (!response.ok) {
                throw new Error('Failed to fetch stats')
            }

            const data = await response.json()
            stats.value = data.stats
        } catch (err) {
            console.error('Error fetching transaction stats:', err)
        }
    }

    const fetchTypes = async () => {
        try {
            const response = await fetch('/api/admin/point-transactions/types')

            if (!response.ok) {
                throw new Error('Failed to fetch types')
            }

            const data = await response.json()
            types.value = data.types
        } catch (err) {
            console.error('Error fetching transaction types:', err)
        }
    }

    const getTransaction = async (transactionId) => {
        try {
            const response = await fetch(`/api/admin/point-transactions/${transactionId}`)

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to fetch transaction')
            }

            return await response.json()
        } catch (err) {
            console.error('Error fetching transaction:', err)
            throw err
        }
    }

    const deleteTransaction = async (transactionId) => {
        try {
            const response = await fetch(`/api/admin/point-transactions/${transactionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to delete transaction')
            }

            return await response.json()
        } catch (err) {
            console.error('Error deleting transaction:', err)
            throw err
        }
    }

    return {
        transactions,
        stats,
        types,
        loading,
        error,
        fetchTransactions,
        fetchStats,
        fetchTypes,
        getTransaction,
        deleteTransaction
    }
}
