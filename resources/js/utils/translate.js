/**
 * Frontend translation helper.
 * For reactive translations in Vue components, use appStore.t() instead.
 * This module is kept for backward compatibility with components that import { t }.
 */

let storeRef = null

/**
 * Set the Pinia store reference so t() can read reactive translations.
 */
export function setStore(store) {
  storeRef = store
}

/**
 * Translate a key. Delegates to the store's reactive t() if available.
 */
export function t(key) {
  if (storeRef) return storeRef.t(key)
  return key
}

/**
 * Load translations (delegates to store).
 */
export async function loadTranslations(locale) {
  if (storeRef) {
    await storeRef.setLocale(locale)
  }
}

export async function initTranslations() {
  // No-op — store.init() handles this now
}

export default { t, loadTranslations, initTranslations, setStore }
