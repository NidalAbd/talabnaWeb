import { ref } from 'vue'

export function useSeoAdmin() {
  const loading = ref(false)
  const error = ref(null)

  const dashboard = ref(null)
  const stats = ref([])
  const keywords = ref({ data: [], current_page: 1, last_page: 1, total: 0 })
  const pages = ref({ data: [], current_page: 1, last_page: 1, total: 0 })
  const issues = ref({ data: [], current_page: 1, last_page: 1, total: 0 })
  const logs = ref({ data: [], current_page: 1, last_page: 1, total: 0 })
  const dailyStats = ref([])
  const performanceByType = ref([])
  const countries = ref([])
  const config = ref(null)
  const gscEnabled = ref(false)

  const fetchDashboard = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch('/api/admin/seo/dashboard')
      if (!response.ok) throw new Error('Failed to fetch SEO dashboard')

      const data = await response.json()
      dashboard.value = data
      gscEnabled.value = data.gsc_enabled
    } catch (err) {
      error.value = err.message
      console.error('Error fetching SEO dashboard:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchStats = async () => {
    try {
      const response = await fetch('/api/admin/seo/stats')
      if (!response.ok) throw new Error('Failed to fetch SEO stats')

      const data = await response.json()
      stats.value = data.stats
      gscEnabled.value = data.gsc_enabled
    } catch (err) {
      console.error('Error fetching SEO stats:', err)
    }
  }

  const fetchKeywords = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const queryString = new URLSearchParams(params).toString()
      const response = await fetch(`/api/admin/seo/keywords?${queryString}`)
      if (!response.ok) throw new Error('Failed to fetch keywords')

      keywords.value = await response.json()
    } catch (err) {
      error.value = err.message
      console.error('Error fetching keywords:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchPages = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const queryString = new URLSearchParams(params).toString()
      const response = await fetch(`/api/admin/seo/pages?${queryString}`)
      if (!response.ok) throw new Error('Failed to fetch pages')

      pages.value = await response.json()
    } catch (err) {
      error.value = err.message
      console.error('Error fetching pages:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchPageDetails = async (url, days = 28) => {
    try {
      const response = await fetch(`/api/admin/seo/pages/details?url=${encodeURIComponent(url)}&days=${days}`)
      if (!response.ok) throw new Error('Failed to fetch page details')

      return await response.json()
    } catch (err) {
      console.error('Error fetching page details:', err)
      throw err
    }
  }

  const fetchDailyStats = async (days = 28) => {
    try {
      const response = await fetch(`/api/admin/seo/daily-stats?days=${days}`)
      if (!response.ok) throw new Error('Failed to fetch daily stats')

      dailyStats.value = await response.json()
    } catch (err) {
      console.error('Error fetching daily stats:', err)
    }
  }

  const fetchPerformanceByType = async (days = 28) => {
    try {
      const response = await fetch(`/api/admin/seo/performance-by-type?days=${days}`)
      if (!response.ok) throw new Error('Failed to fetch performance by type')

      performanceByType.value = await response.json()
    } catch (err) {
      console.error('Error fetching performance by type:', err)
    }
  }

  const fetchCountries = async (days = 28) => {
    try {
      const response = await fetch(`/api/admin/seo/countries?days=${days}`)
      if (!response.ok) throw new Error('Failed to fetch countries')

      countries.value = await response.json()
    } catch (err) {
      console.error('Error fetching countries:', err)
    }
  }

  const fetchIssues = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const queryString = new URLSearchParams(params).toString()
      const response = await fetch(`/api/admin/seo/issues?${queryString}`)
      if (!response.ok) throw new Error('Failed to fetch issues')

      issues.value = await response.json()
    } catch (err) {
      error.value = err.message
      console.error('Error fetching issues:', err)
    } finally {
      loading.value = false
    }
  }

  const updateIssue = async (id, action, notes = null) => {
    try {
      const response = await fetch(`/api/admin/seo/issues/${id}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ action, notes })
      })

      if (!response.ok) throw new Error('Failed to update issue')

      return await response.json()
    } catch (err) {
      console.error('Error updating issue:', err)
      throw err
    }
  }

  const fetchLogs = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const queryString = new URLSearchParams(params).toString()
      const response = await fetch(`/api/admin/seo/logs?${queryString}`)
      if (!response.ok) throw new Error('Failed to fetch logs')

      logs.value = await response.json()
    } catch (err) {
      error.value = err.message
      console.error('Error fetching logs:', err)
    } finally {
      loading.value = false
    }
  }

  const syncData = async (days = 28) => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch('/api/admin/seo/sync', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ days })
      })

      if (!response.ok) throw new Error('Failed to sync data')

      return await response.json()
    } catch (err) {
      error.value = err.message
      console.error('Error syncing data:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const testConnection = async () => {
    try {
      const response = await fetch('/api/admin/seo/test-connection')
      if (!response.ok) throw new Error('Failed to test connection')

      return await response.json()
    } catch (err) {
      console.error('Error testing connection:', err)
      throw err
    }
  }

  const cleanup = async () => {
    try {
      const response = await fetch('/api/admin/seo/cleanup', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
      })

      if (!response.ok) throw new Error('Failed to cleanup data')

      return await response.json()
    } catch (err) {
      console.error('Error cleaning up data:', err)
      throw err
    }
  }

  const fetchConfig = async () => {
    try {
      const response = await fetch('/api/admin/seo/config')
      if (!response.ok) throw new Error('Failed to fetch config')

      config.value = await response.json()
      gscEnabled.value = config.value.gsc_enabled
    } catch (err) {
      console.error('Error fetching config:', err)
    }
  }

  return {
    loading,
    error,
    dashboard,
    stats,
    keywords,
    pages,
    issues,
    logs,
    dailyStats,
    performanceByType,
    countries,
    config,
    gscEnabled,
    fetchDashboard,
    fetchStats,
    fetchKeywords,
    fetchPages,
    fetchPageDetails,
    fetchDailyStats,
    fetchPerformanceByType,
    fetchCountries,
    fetchIssues,
    updateIssue,
    fetchLogs,
    syncData,
    testConnection,
    cleanup,
    fetchConfig
  }
}
