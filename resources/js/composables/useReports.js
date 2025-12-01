import { ref } from 'vue'

export function useReports() {
    const reports = ref({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
    })
    const reportDetails = ref({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
    })
    const reportable = ref(null)
    const reportType = ref(null)
    const loading = ref(false)
    const error = ref(null)

    const fetchReports = async (filters = {}) => {
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

            const url = `/api/admin/reports?${params.toString()}`
            console.log('🔍 Fetching reports from:', url)

            const response = await fetch(url)
            console.log('📡 Response status:', response.status, response.statusText)

            if (!response.ok) {
                const errorText = await response.text()
                console.error('❌ Response error:', errorText)
                throw new Error('Failed to fetch reports')
            }

            const data = await response.json()
            console.log('✅ Reports data received:', data)

            if (data.reports) {
                reports.value = data.reports
            } else {
                reports.value = data
            }
        } catch (err) {
            error.value = err.message
            console.error('❌ Error fetching reports:', err)
        } finally {
            loading.value = false
            console.log('⏱️ Loading complete. Reports count:', reports.value.data?.length || 0)
        }
    }

    const fetchReportDetails = async (type, id, page = 1) => {
        loading.value = true
        error.value = null

        try {
            const url = `/api/admin/reports/${type}/${id}?page=${page}`
            console.log('🔍 Fetching report details from:', url)

            const response = await fetch(url)

            if (!response.ok) {
                throw new Error('Failed to fetch report details')
            }

            const data = await response.json()
            console.log('✅ Report details received:', data)

            reportDetails.value = data.reports
            reportable.value = data.reportable
            reportType.value = data.type
        } catch (err) {
            error.value = err.message
            console.error('❌ Error fetching report details:', err)
            throw err
        } finally {
            loading.value = false
        }
    }

    const handleReport = async (type, id, action, reason) => {
        try {
            const response = await fetch(`/api/admin/reports/handle/${type}/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ action, reason })
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to handle report')
            }

            return await response.json()
        } catch (err) {
            console.error('Error handling report:', err)
            throw err
        }
    }

    const deleteReport = async (reportId) => {
        try {
            const response = await fetch(`/api/admin/reports/${reportId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to delete report')
            }

            return await response.json()
        } catch (err) {
            console.error('Error deleting report:', err)
            throw err
        }
    }

    return {
        reports,
        reportDetails,
        reportable,
        reportType,
        loading,
        error,
        fetchReports,
        fetchReportDetails,
        handleReport,
        deleteReport
    }
}
