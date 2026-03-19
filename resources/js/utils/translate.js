/**
 * Frontend translation helper.
 * Fetches UI strings from /api/translations/{locale} and caches them.
 */

const cache = {}
let currentLocale = localStorage.getItem('locale') || 'ar'

// Hardcoded fallbacks for critical UI strings (always available)
const fallbacks = {
  'app.name': { ar: 'طلبنا', en: 'Talabna' },
  'app.tagline': { ar: 'منصة الإعلانات المبوبة', en: 'Classified Ads Platform' },
  'nav.home': { ar: 'الرئيسية', en: 'Home' },
  'nav.browse': { ar: 'تصفح', en: 'Browse' },
  'nav.categories': { ar: 'التصنيفات', en: 'Categories' },
  'nav.search': { ar: 'ابحث...', en: 'Search...' },
  'nav.login': { ar: 'تسجيل الدخول', en: 'Login' },
  'nav.logout': { ar: 'تسجيل الخروج', en: 'Logout' },
  'nav.dashboard': { ar: 'لوحة التحكم', en: 'Dashboard' },
  'nav.quick_links': { ar: 'روابط سريعة', en: 'Quick Links' },
  'nav.about': { ar: 'عن طلبنا', en: 'About Us' },
  'nav.contact': { ar: 'اتصل بنا', en: 'Contact Us' },
  'nav.privacy': { ar: 'سياسة الخصوصية', en: 'Privacy Policy' },
  'nav.terms': { ar: 'شروط الاستخدام', en: 'Terms of Service' },
  'home.hero_title': { ar: 'اكتشف الخدمات والإعلانات', en: 'Discover Services & Ads' },
  'home.hero_subtitle': { ar: 'آلاف الإعلانات في انتظارك', en: 'Thousands of ads waiting for you' },
  'home.featured': { ar: 'إعلانات مميزة', en: 'Featured Ads' },
  'home.latest': { ar: 'أحدث الإعلانات', en: 'Latest Ads' },
  'home.popular': { ar: 'الأكثر شعبية', en: 'Most Popular' },
  'home.all_categories': { ar: 'جميع التصنيفات', en: 'All Categories' },
  'home.view_all': { ar: 'عرض الكل', en: 'View All' },
  'home.no_results': { ar: 'لا توجد نتائج', en: 'No results found' },
  'listing.price': { ar: 'السعر', en: 'Price' },
  'listing.location': { ar: 'الموقع', en: 'Location' },
  'listing.contact': { ar: 'تواصل', en: 'Contact' },
  'listing.share': { ar: 'مشاركة', en: 'Share' },
  'listing.report': { ar: 'إبلاغ', en: 'Report' },
  'listing.related': { ar: 'إعلانات مشابهة', en: 'Related Listings' },
  'footer.rights': { ar: 'جميع الحقوق محفوظة', en: 'All rights reserved' },
  'footer.follow': { ar: 'تابعنا', en: 'Follow Us' },
  'footer.download': { ar: 'حمل التطبيق', en: 'Download App' },
}

/**
 * Load translations for a locale from API.
 */
export async function loadTranslations(locale) {
  if (cache[locale]) return cache[locale]

  try {
    const response = await fetch(`/api/translations/${locale}`)
    if (response.ok) {
      const data = await response.json()
      // API returns { success: true, translations: { "group.key": "value", ... } }
      cache[locale] = data.translations || data || {}
      currentLocale = locale
      return cache[locale]
    }
  } catch (_) {}

  return {}
}

/**
 * Translate a key. Returns the translated string or fallback.
 * Usage: t('nav.home') → 'الرئيسية' (when locale is ar)
 */
export function t(key) {
  const locale = localStorage.getItem('locale') || 'ar'

  // Try cached API translations first
  if (cache[locale]) {
    const value = cache[locale][key]
    if (value) return value
  }

  // Fallback to hardcoded strings
  if (fallbacks[key]) {
    return fallbacks[key][locale] || fallbacks[key]['en'] || key
  }

  return key
}

/**
 * Initialize translations for the current locale.
 */
export async function initTranslations() {
  const locale = localStorage.getItem('locale') || 'ar'
  await loadTranslations(locale)
}

export default { t, loadTranslations, initTranslations }
