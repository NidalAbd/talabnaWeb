import { ref } from 'vue'

// Module-level state so every component/router hook shares one progress bar.
const isLoading = ref(false)
const progress = ref(0)

let trickleTimer = null
let hideTimer = null
let activeRequests = 0

const clearTimers = () => {
  if (trickleTimer) {
    clearInterval(trickleTimer)
    trickleTimer = null
  }
  if (hideTimer) {
    clearTimeout(hideTimer)
    hideTimer = null
  }
}

const start = () => {
  activeRequests++
  if (activeRequests > 1) return

  clearTimers()
  isLoading.value = true
  progress.value = 15

  trickleTimer = setInterval(() => {
    if (progress.value < 90) {
      progress.value += (90 - progress.value) * 0.15
    }
  }, 250)
}

const finish = () => {
  activeRequests = Math.max(0, activeRequests - 1)
  if (activeRequests > 0) return

  clearTimers()
  progress.value = 100
  hideTimer = setTimeout(() => {
    isLoading.value = false
    progress.value = 0
  }, 200)
}

export function useTopProgress() {
  return { isLoading, progress, start, finish }
}
