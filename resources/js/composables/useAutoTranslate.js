import { ref } from 'vue'

export function useAutoTranslate() {
  const progress = ref(null)
  const loading = ref(false)
  const error = ref(null)

  const csrfHeaders = () => ({
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  })

  const startTranslation = async (locale, tier = 'all', limit = null) => {
    loading.value = true
    error.value = null
    try {
      const qp = new URLSearchParams()
      qp.append('tier', tier)
      if (limit) qp.append('limit', limit)

      const response = await fetch(`/api/admin/auto-translate/${locale}?${qp.toString()}`, {
        method: 'POST', credentials: 'same-origin',
        headers: csrfHeaders()
      })
      if (!response.ok) {
        const data = await response.json()
        throw new Error(data.message || 'Failed to start translation')
      }
      return await response.json()
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  const checkProgress = async (locale) => {
    try {
      const response = await fetch(`/api/admin/auto-translate/${locale}/progress`, { credentials: 'same-origin' })
      if (!response.ok) throw new Error('Failed to check progress')
      const data = await response.json()
      progress.value = data.progress
      return data.progress
    } catch (err) {
      error.value = err.message
      return null
    }
  }

  const translateOnDemand = async (postId, locale) => {
    loading.value = true
    error.value = null
    try {
      const response = await fetch('/api/admin/auto-translate/on-demand', {
        method: 'POST', credentials: 'same-origin',
        headers: csrfHeaders(),
        body: JSON.stringify({ post_id: postId, locale })
      })
      if (!response.ok) throw new Error('Failed to translate')
      return await response.json()
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  const stopTranslation = async (locale) => {
    try {
      const response = await fetch(`/api/admin/auto-translate/${locale}/stop`, {
        method: 'POST', credentials: 'same-origin',
        headers: csrfHeaders()
      })
      if (!response.ok) throw new Error('Failed to stop translation')
      progress.value = null
      return await response.json()
    } catch (err) {
      error.value = err.message
      throw err
    }
  }

  return { progress, loading, error, startTranslation, checkProgress, translateOnDemand, stopTranslation }
}
