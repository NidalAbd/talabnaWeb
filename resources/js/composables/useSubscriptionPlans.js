import { ref } from 'vue'

export function useSubscriptionPlans() {
  const plans = ref({ data: [], current_page: 1, last_page: 1, per_page: 15, total: 0 })
  const loading = ref(false)
  const error = ref(null)

  const csrfHeaders = () => ({
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  })

  const fetchPlans = async (params = {}) => {
    loading.value = true
    error.value = null
    try {
      const qp = new URLSearchParams()
      if (params.search) qp.append('search', params.search)
      if (params.status) qp.append('status', params.status)
      if (params.page) qp.append('page', params.page)
      if (params.per_page) qp.append('per_page', params.per_page)

      const response = await fetch(`/api/admin/subscription-plans?${qp.toString()}`, { credentials: 'same-origin' })
      if (!response.ok) throw new Error('Failed to fetch plans')
      const data = await response.json()
      plans.value = data
    } catch (err) {
      error.value = err.message
    } finally {
      loading.value = false
    }
  }

  const createPlan = async (formData) => {
    const response = await fetch('/api/admin/subscription-plans', {
      method: 'POST', credentials: 'same-origin',
      headers: csrfHeaders(),
      body: JSON.stringify(formData)
    })
    if (!response.ok) {
      const data = await response.json()
      throw new Error(data.message || 'Failed to create plan')
    }
    return (await response.json()).plan
  }

  const updatePlan = async (id, formData) => {
    const response = await fetch(`/api/admin/subscription-plans/${id}`, {
      method: 'PUT', credentials: 'same-origin',
      headers: csrfHeaders(),
      body: JSON.stringify(formData)
    })
    if (!response.ok) {
      const data = await response.json()
      throw new Error(data.message || 'Failed to update plan')
    }
    return (await response.json()).plan
  }

  const deletePlan = async (id) => {
    const response = await fetch(`/api/admin/subscription-plans/${id}`, {
      method: 'DELETE', credentials: 'same-origin',
      headers: csrfHeaders()
    })
    if (!response.ok) {
      const data = await response.json()
      throw new Error(data.message || 'Failed to delete plan')
    }
    return await response.json()
  }

  const toggleActive = async (id) => {
    const response = await fetch(`/api/admin/subscription-plans/${id}/toggle`, {
      method: 'POST', credentials: 'same-origin',
      headers: csrfHeaders()
    })
    if (!response.ok) throw new Error('Failed to toggle status')
    return await response.json()
  }

  const fetchStats = async () => {
    const response = await fetch('/api/admin/subscription-plans/stats', { credentials: 'same-origin' })
    if (!response.ok) throw new Error('Failed to fetch stats')
    return await response.json()
  }

  return { plans, loading, error, fetchPlans, createPlan, updatePlan, deletePlan, toggleActive, fetchStats }
}
