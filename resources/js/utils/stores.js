// Store links for the "get the app" buttons. The iPhone app is not on the App Store yet (2026-10-08: apps.apple.com
// answers 404 for id6814376173), so the App Store button shows "coming soon" without a link; set APP_STORE_URL when it
// goes live and every button becomes a real link.
export const PLAY_URL = 'https://play.google.com/store/apps/details?id=com.talabna.talabna'
export const APP_STORE_URL = ''

export function comingSoon(locale) {
  return locale === 'ar' ? 'قريباً' : 'Coming soon'
}
