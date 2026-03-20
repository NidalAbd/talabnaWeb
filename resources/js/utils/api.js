/**
 * API helper that automatically includes the current locale in all requests.
 * Wraps native fetch to add Accept-Language header and ?lang= parameter.
 */

export function apiFetch(url, options = {}) {
  const locale = localStorage.getItem('locale') || 'ar'
  const userCountryId = localStorage.getItem('user_country_id')
  const userCityId = localStorage.getItem('user_city_id')

  // Add locale + location as query parameters (skip empty/null values)
  const separator = url.includes('?') ? '&' : '?'
  let params = `lang=${locale}`
  if (userCountryId && userCountryId !== 'null' && userCountryId !== '') {
    params += `&user_country_id=${userCountryId}`
  }
  if (userCityId && userCityId !== 'null' && userCityId !== '') {
    params += `&user_city_id=${userCityId}`
  }

  const localizedUrl = `${url}${separator}${params}`

  // Add Accept-Language header
  const headers = {
    'Accept-Language': locale,
    'Accept': 'application/json',
    ...(options.headers || {}),
  }

  return fetch(localizedUrl, { ...options, headers })
}

export default apiFetch
