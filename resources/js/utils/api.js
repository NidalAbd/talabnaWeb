/**
 * API helper that automatically includes the current locale in all requests.
 * Wraps native fetch to add Accept-Language header and ?lang= parameter.
 */

export function apiFetch(url, options = {}) {
  const locale = localStorage.getItem('locale') || 'ar'

  // Add locale as query parameter
  const separator = url.includes('?') ? '&' : '?'
  const localizedUrl = `${url}${separator}lang=${locale}`

  // Add Accept-Language header
  const headers = {
    'Accept-Language': locale,
    'Accept': 'application/json',
    ...(options.headers || {}),
  }

  return fetch(localizedUrl, { ...options, headers })
}

export default apiFetch
