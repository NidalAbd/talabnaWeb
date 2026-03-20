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
  'nav.countries': { ar: 'الدول', en: 'Countries' },
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

  // Browse page
  'browse.title': { ar: 'تصفح الإعلانات', en: 'Browse Listings' },
  'browse.available': { ar: 'إعلان متاح', en: 'listings available' },
  'browse.filters': { ar: 'الفلاتر', en: 'Filters' },
  'browse.clear': { ar: 'مسح', en: 'Clear' },
  'browse.category': { ar: 'التصنيف', en: 'Category' },
  'browse.subcategory': { ar: 'التصنيف الفرعي', en: 'Subcategory' },
  'browse.country': { ar: 'الدولة', en: 'Country' },
  'browse.city': { ar: 'المدينة', en: 'City' },
  'browse.price_range': { ar: 'نطاق السعر', en: 'Price Range' },
  'browse.price_min': { ar: 'من', en: 'Min' },
  'browse.price_max': { ar: 'إلى', en: 'Max' },
  'browse.listing_type': { ar: 'نوع الإعلان', en: 'Listing Type' },
  'browse.apply': { ar: 'تطبيق', en: 'Apply' },
  'browse.all': { ar: 'الكل', en: 'All' },
  'browse.loading': { ar: 'جاري التحميل...', en: 'Loading...' },
  'browse.loading_more': { ar: 'جاري تحميل المزيد...', en: 'Loading more...' },
  'browse.load_more': { ar: 'تحميل المزيد', en: 'Load More' },
  'browse.all_loaded': { ar: 'تم عرض جميع النتائج', en: 'All results loaded' },
  'browse.no_results': { ar: 'لا توجد نتائج', en: 'No results found' },
  'browse.adjust_filters': { ar: 'جرب تعديل الفلاتر', en: 'Try adjusting your filters' },
  'browse.clear_filters': { ar: 'مسح الفلاتر', en: 'Clear Filters' },
  'browse.sort_newest': { ar: 'الأحدث', en: 'Newest' },
  'browse.sort_price_low': { ar: 'السعر: من الأقل', en: 'Price: Low to High' },
  'browse.sort_price_high': { ar: 'السعر: من الأعلى', en: 'Price: High to Low' },
  'browse.sort_most_viewed': { ar: 'الأكثر مشاهدة', en: 'Most Viewed' },

  // Search page
  'search.title': { ar: 'نتائج البحث', en: 'Search Results' },
  'search.results_for': { ar: 'نتيجة لـ', en: 'results for' },
  'search.no_results': { ar: 'لا توجد نتائج', en: 'No results found' },
  'search.try_different': { ar: 'جرب كلمات بحث مختلفة', en: 'Try different search terms' },
  'search.placeholder': { ar: 'ابحث...', en: 'Search...' },

  // Listing details
  'listing.views': { ar: 'مشاهدة', en: 'views' },
  'listing.description': { ar: 'الوصف', en: 'Description' },
  'listing.price_contact': { ar: 'السعر عند الاتصال', en: 'Contact for price' },
  'listing.member_since': { ar: 'عضو منذ', en: 'Member since' },
  'listing.no_listings': { ar: 'لا توجد إعلانات', en: 'No listings' },
  'listing.user_listings': { ar: 'إعلانات المستخدم', en: 'User Listings' },
  'listing.not_found': { ar: 'الإعلان غير موجود', en: 'Listing Not Found' },
  'listing.call_now': { ar: 'اتصل الآن', en: 'Call Now' },
  'listing.whatsapp': { ar: 'واتساب', en: 'WhatsApp' },
  'listing.save': { ar: 'حفظ الإعلان', en: 'Save Listing' },
  'listing.saved': { ar: 'تم حفظ الإعلان', en: 'Listing saved' },
  'listing.removed': { ar: 'تم إزالة الإعلان', en: 'Listing removed' },
  'listing.share_listing': { ar: 'مشاركة الإعلان', en: 'Share Listing' },
  'listing.link_copied': { ar: 'تم نسخ الرابط', en: 'Link copied' },
  'listing.listings': { ar: 'إعلان', en: 'listings' },

  // Home page
  'home.search_placeholder': { ar: 'ابحث عن سيارة، شقة، وظيفة...', en: 'Search for car, apartment, job...' },
  'home.trusted': { ar: 'منصة موثوقة للإعلانات المبوبة', en: 'Trusted Classified Ads Platform' },
  'home.find': { ar: 'اعثر على ما تحتاجه', en: 'Find What You Need' },
  'home.easily': { ar: 'بسهولة وأمان', en: 'Easily & Safely' },
  'home.hero_desc': { ar: 'وظائف، سيارات، عقارات، أجهزة وخدمات متنوعة. تواصل مع آلاف المستخدمين في أكثر من 22 دولة عربية.', en: 'Jobs, cars, real estate, electronics and services. Connect with thousands of users across 22+ Arab countries.' },
  'home.search_btn': { ar: 'بحث', en: 'Search' },
  'home.get_app': { ar: 'حمل التطبيق', en: 'Get the App' },
  'home.active_services': { ar: 'خدمات نشطة', en: 'Active Services' },
  'home.registered_users': { ar: 'مستخدمين مسجلين', en: 'Registered Users' },
  'home.premium_services': { ar: 'خدمات مميزة', en: 'Premium Services' },
  'home.transactions': { ar: 'معاملات', en: 'Transactions' },
  'home.browse_categories': { ar: 'تصفح التصنيفات', en: 'Browse Categories' },
  'home.find_exactly': { ar: 'اكتشف ما تبحث عنه بالضبط', en: 'Find exactly what you\'re looking for' },
  'home.featured_listings': { ar: 'الإعلانات المميزة', en: 'Featured Listings' },
  'home.handpicked': { ar: 'إعلانات مختارة من مستخدمينا', en: 'Handpicked listings from our users' },
  'home.latest_listings': { ar: 'أحدث الإعلانات', en: 'Latest Listings' },
  'home.recently_added': { ar: 'أحدث ما أُضيف إلى المنصة', en: 'Recently added to the platform' },
  'home.browse_location': { ar: 'تصفح حسب الموقع', en: 'Browse by Location' },
  'home.find_area': { ar: 'اعثر على الخدمات في منطقتك', en: 'Find services in your area' },
  'home.most_viewed': { ar: 'الأكثر مشاهدة', en: 'Most Viewed' },
  'home.most_popular': { ar: 'الإعلانات الأكثر شعبية', en: 'The most popular listings' },
  'home.download_app': { ar: 'حمل تطبيق طلبنا', en: 'Download Talabna App' },
  'home.download_desc': { ar: 'انشر إعلاناتك، تواصل مع المستخدمين، واستقبل الإشعارات أينما كنت.', en: 'Post listings, connect with users, and receive notifications on the go.' },
  'home.free': { ar: 'مجاني', en: 'Free' },
  'home.easy_use': { ar: 'سهل الاستخدام', en: 'Easy to use' },
  'home.instant_notif': { ar: 'إشعارات فورية', en: 'Instant notifications' },

  // Category page
  'category.subcategories': { ar: 'التصنيفات الفرعية', en: 'Subcategories' },
  'category.in': { ar: 'في', en: 'in' },
  'category.search_radius': { ar: 'نطاق البحث', en: 'Search Radius' },
  'category.km': { ar: 'كم', en: 'km' },
  'category.enable_location': { ar: 'تفعيل الموقع', en: 'Enable Location' },
  'category.your_location': { ar: 'موقعك الحالي', en: 'Your current location' },
  'category.no_near': { ar: 'لا توجد إعلانات قريبة منك', en: 'No listings near you' },
  'category.no_videos': { ar: 'لا توجد فيديوهات', en: 'No video listings' },
  'category.no_listings': { ar: 'لا توجد إعلانات', en: 'No listings found' },
  'category.enable_for_near': { ar: 'قم بتفعيل الموقع لعرض الإعلانات القريبة منك', en: 'Enable location to see nearby listings' },
  'category.getting_location': { ar: 'جاري تحديد موقعك...', en: 'Getting your location...' },
  'category.view_all_sub': { ar: 'عرض كل التصنيفات الفرعية', en: 'View all subcategories' },
  'category.geo_unsupported': { ar: 'المتصفح لا يدعم تحديد الموقع', en: 'Geolocation is not supported by your browser' },
  'category.geo_denied': { ar: 'تم رفض إذن الموقع. يرجى السماح بالوصول إلى موقعك.', en: 'Location permission denied. Please allow access to your location.' },
  'category.geo_unavailable': { ar: 'معلومات الموقع غير متوفرة.', en: 'Location information is unavailable.' },
  'category.geo_timeout': { ar: 'انتهت مهلة طلب الموقع.', en: 'Location request timed out.' },
  'category.geo_error': { ar: 'حدث خطأ غير معروف.', en: 'An unknown error occurred.' },

  // Services page
  'services.title': { ar: 'خدمات', en: 'Services' },
  'services.title_full': { ar: 'خدمات وإعلانات', en: 'Services & Listings' },
  'services.loading': { ar: 'جاري التحميل...', en: 'Loading...' },
  'services.no_listings': { ar: 'لا توجد إعلانات', en: 'No listings found' },
  'services.try_change': { ar: 'جرب تغيير الموقع أو التصنيف', en: 'Try changing location or category' },
  'services.contact_price': { ar: 'اتصل للسعر', en: 'Contact for price' },
  'services.ads': { ar: 'إعلان', en: 'ads' },
  'services.advertisers': { ar: 'معلن', en: 'Advertisers' },

  // About page
  'about.title': { ar: 'من نحن', en: 'About Us' },
  'about.subtitle': { ar: 'تعرف على قصة طلبنا', en: 'Learn about Talabna story' },
  'about.mission': { ar: 'مهمتنا', en: 'Our Mission' },
  'about.mission_p1': { ar: 'طلبنا هي منصة رائدة للإعلانات المبوبة تخدم المنطقة العربية. نربط بين البائعين والمشترين بطريقة سهلة وآمنة وسريعة. من الوظائف إلى العقارات، ومن السيارات إلى الأجهزة الإلكترونية — نوفر لك كل ما تحتاجه في مكان واحد.', en: 'Talabna is a leading classified ads platform serving the Arab region. We connect sellers and buyers in an easy, secure, and fast way. From jobs to real estate, cars to electronics — we provide everything you need in one place.' },
  'about.mission_p2': { ar: 'نسعى لبناء مجتمع رقمي موثوق يُمكّن الجميع من البيع والشراء والتواصل بثقة وأمان.', en: 'We strive to build a trusted digital community that empowers everyone to buy, sell, and connect with confidence and security.' },
  'about.values': { ar: 'قيمنا', en: 'Our Values' },
  'about.by_numbers': { ar: 'بالأرقام', en: 'By the Numbers' },
  'about.ready': { ar: 'جاهز للبدء؟', en: 'Ready to get started?' },
  'about.download_app': { ar: 'حمل التطبيق', en: 'Download App' },

  // Contact page
  'contact.title': { ar: 'اتصل بنا', en: 'Contact Us' },
  'contact.subtitle': { ar: 'نحن هنا لمساعدتك', en: 'We are here to help' },
  'contact.send_message': { ar: 'أرسل لنا رسالة', en: 'Send us a message' },
  'contact.success': { ar: 'تم إرسال رسالتك بنجاح! سنرد عليك في أقرب وقت.', en: 'Message sent successfully! We will reply soon.' },
  'contact.name': { ar: 'الاسم', en: 'Name' },
  'contact.email': { ar: 'البريد الإلكتروني', en: 'Email' },
  'contact.message': { ar: 'الرسالة', en: 'Message' },
  'contact.send': { ar: 'إرسال', en: 'Send' },
  'contact.website': { ar: 'الموقع', en: 'Website' },
  'contact.app': { ar: 'التطبيق', en: 'App' },
  'contact.error': { ar: 'حدث خطأ. حاول مرة أخرى.', en: 'An error occurred. Please try again.' },
  'contact.connection_error': { ar: 'فشل الاتصال بالخادم.', en: 'Failed to connect to server.' },

  // Terms page
  'terms.title': { ar: 'شروط الاستخدام', en: 'Terms of Service' },
  'terms.updated': { ar: 'آخر تحديث: مارس 2026', en: 'Last updated: March 2026' },

  // Privacy page
  'privacy.title': { ar: 'سياسة الخصوصية', en: 'Privacy Policy' },
  'privacy.updated': { ar: 'آخر تحديث: مارس 2026', en: 'Last updated: March 2026' },

  // 404 page
  'notfound.title': { ar: 'الصفحة غير موجودة', en: 'Page Not Found' },
  'notfound.desc': { ar: 'عذراً، الصفحة التي تبحث عنها غير موجودة أو تم نقلها.', en: 'Sorry, the page you are looking for does not exist or has been moved.' },
  'notfound.back': { ar: 'العودة للرئيسية', en: 'Back to Home' },
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
