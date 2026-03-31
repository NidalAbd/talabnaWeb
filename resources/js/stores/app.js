import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAppStore = defineStore('app', () => {
  // State
  const locale = ref(localStorage.getItem('locale') || 'ar')
  const theme = ref(localStorage.getItem('theme') || 'light')
  const user = ref(null)
  const categories = ref([])
  const countries = ref([])
  const loading = ref(false)
  const translations = ref({}) // Reactive translations cache
  const translationsLoaded = ref(false)

  // Getters
  const rtlLocales = ['ar', 'he', 'fa', 'ur', 'ps', 'ku', 'sd']
  const isRTL = computed(() => rtlLocales.includes(locale.value))
  const isAuthenticated = computed(() => !!user.value)
  const isDark = computed(() => theme.value === 'dark')

  // Hardcoded fallbacks for ar/en (always available, no API needed)
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
    'nav.legal': { ar: 'قانوني', en: 'Legal' },
    'nav.sitemap': { ar: 'خريطة الموقع', en: 'Sitemap' },
    'home.hero_title': { ar: 'اكتشف الخدمات والإعلانات', en: 'Discover Services & Ads' },
    'home.hero_subtitle': { ar: 'آلاف الإعلانات في انتظارك', en: 'Thousands of ads waiting for you' },
    'home.featured': { ar: 'إعلانات مميزة', en: 'Featured Ads' },
    'home.latest': { ar: 'أحدث الإعلانات', en: 'Latest Ads' },
    'home.popular': { ar: 'الأكثر شعبية', en: 'Most Popular' },
    'home.all_categories': { ar: 'جميع التصنيفات', en: 'All Categories' },
    'home.view_all': { ar: 'عرض الكل', en: 'View All' },
    'home.no_results': { ar: 'لا توجد نتائج', en: 'No results found' },
    'home.hero_desc': { ar: 'وظائف، سيارات، عقارات، أجهزة وخدمات متنوعة. تواصل مع آلاف المستخدمين.', en: 'Jobs, cars, real estate, electronics and services. Connect with thousands of users.' },
    'home.search_placeholder': { ar: 'ابحث عن سيارة، شقة، وظيفة...', en: 'Search for car, apartment, job...' },
    'home.search_btn': { ar: 'بحث', en: 'Search' },
    'home.get_app': { ar: 'حمل التطبيق', en: 'Get the App' },
    'home.browse_categories': { ar: 'تصفح التصنيفات', en: 'Browse Categories' },
    'home.find_exactly': { ar: 'اكتشف ما تبحث عنه', en: 'Find exactly what you\'re looking for' },
    'home.featured_listings': { ar: 'الإعلانات المميزة', en: 'Featured Listings' },
    'home.handpicked': { ar: 'إعلانات مختارة', en: 'Handpicked listings' },
    'home.latest_listings': { ar: 'أحدث الإعلانات', en: 'Latest Listings' },
    'home.recently_added': { ar: 'أحدث ما أُضيف', en: 'Recently added' },
    'home.browse_location': { ar: 'تصفح حسب الموقع', en: 'Browse by Location' },
    'home.find_area': { ar: 'اعثر على الخدمات في منطقتك', en: 'Find services in your area' },
    'home.most_viewed': { ar: 'الأكثر مشاهدة', en: 'Most Viewed' },
    'home.most_popular': { ar: 'الإعلانات الأكثر شعبية', en: 'Most popular listings' },
    'home.download_app': { ar: 'حمل تطبيق طلبنا', en: 'Download Talabna App' },
    'home.download_desc': { ar: 'انشر إعلاناتك وتواصل مع المستخدمين', en: 'Post listings and connect with users' },
    'home.free': { ar: 'مجاني', en: 'Free' },
    'home.easy_use': { ar: 'سهل الاستخدام', en: 'Easy to use' },
    'home.instant_notif': { ar: 'إشعارات فورية', en: 'Instant notifications' },
    'home.trusted': { ar: 'منصة موثوقة', en: 'Trusted Platform' },
    'home.find': { ar: 'اعثر على ما تحتاجه', en: 'Find What You Need' },
    'home.easily': { ar: 'بسهولة وأمان', en: 'Easily & Safely' },
    'home.active_services': { ar: 'خدمات نشطة', en: 'Active Services' },
    'home.registered_users': { ar: 'مستخدمين مسجلين', en: 'Registered Users' },
    'home.premium_services': { ar: 'خدمات مميزة', en: 'Premium Services' },
    'home.transactions': { ar: 'معاملات', en: 'Transactions' },
    'listing.price': { ar: 'السعر', en: 'Price' },
    'listing.location': { ar: 'الموقع', en: 'Location' },
    'listing.contact': { ar: 'تواصل', en: 'Contact' },
    'listing.share': { ar: 'مشاركة', en: 'Share' },
    'listing.report': { ar: 'إبلاغ', en: 'Report' },
    'listing.related': { ar: 'إعلانات مشابهة', en: 'Related Listings' },
    'listing.views': { ar: 'مشاهدة', en: 'views' },
    'listing.listings': { ar: 'إعلان', en: 'listings' },
    'listing.description': { ar: 'الوصف', en: 'Description' },
    'listing.member_since': { ar: 'عضو منذ', en: 'Member since' },
    'listing.call_now': { ar: 'اتصل الآن', en: 'Call Now' },
    'listing.whatsapp': { ar: 'واتساب', en: 'WhatsApp' },
    'listing.save': { ar: 'حفظ الإعلان', en: 'Save Listing' },
    'footer.rights': { ar: 'جميع الحقوق محفوظة', en: 'All rights reserved' },
    'footer.follow': { ar: 'تابعنا', en: 'Follow Us' },
    'footer.download': { ar: 'حمل التطبيق', en: 'Download App' },
    'footer.about_desc': { ar: 'أكبر منصة للإعلانات المبوبة.', en: 'The largest classified ads marketplace.' },
    'browse.title': { ar: 'تصفح الإعلانات', en: 'Browse Listings' },
    'browse.filters': { ar: 'الفلاتر', en: 'Filters' },
    'browse.no_results': { ar: 'لا توجد نتائج', en: 'No results found' },
    'browse.loading': { ar: 'جاري التحميل...', en: 'Loading...' },
    'browse.load_more': { ar: 'تحميل المزيد', en: 'Load More' },
    'browse.sort_newest': { ar: 'الأحدث', en: 'Newest' },
    'search.title': { ar: 'نتائج البحث', en: 'Search Results' },
    'search.results_for': { ar: 'نتيجة لـ', en: 'results for' },
    'search.no_results': { ar: 'لا توجد نتائج', en: 'No results found' },
    'about.title': { ar: 'من نحن', en: 'About Us' },
    'about.mission': { ar: 'مهمتنا', en: 'Our Mission' },
    'about.mission_p1': { ar: 'طلبنا منصة رائدة للإعلانات المبوبة تخدم المنطقة العربية.', en: 'Talabna is a leading classified ads platform serving the Arab region.' },
    'about.values': { ar: 'قيمنا', en: 'Our Values' },
    'about.by_numbers': { ar: 'بالأرقام', en: 'By the Numbers' },
    'about.ready': { ar: 'جاهز للبدء؟', en: 'Ready to get started?' },
    'about.value_security': { ar: 'الأمان', en: 'Security' },
    'about.value_security_desc': { ar: 'نحمي بياناتك بأحدث تقنيات الحماية', en: 'We protect your data with the latest security' },
    'about.value_speed': { ar: 'السرعة', en: 'Speed' },
    'about.value_speed_desc': { ar: 'منصة سريعة وسهلة الاستخدام', en: 'Fast and easy to use platform' },
    'about.value_trust': { ar: 'الثقة', en: 'Trust' },
    'about.value_trust_desc': { ar: 'مجتمع موثوق من المستخدمين', en: 'Trusted community of users' },
    'about.value_coverage': { ar: 'التغطية', en: 'Coverage' },
    'about.value_coverage_desc': { ar: 'نخدم أكثر من 22 دولة', en: 'Serving over 22 countries' },
    'about.stat_countries': { ar: 'دولة', en: 'Countries' },
    'about.stat_users': { ar: 'مستخدم', en: 'Users' },
    'about.stat_listings': { ar: 'إعلان', en: 'Listings' },
    'about.stat_categories': { ar: 'تصنيف', en: 'Categories' },
    'contact.title': { ar: 'اتصل بنا', en: 'Contact Us' },
    'contact.subtitle': { ar: 'نحن هنا لمساعدتك', en: 'We are here to help' },
    'privacy.title': { ar: 'سياسة الخصوصية', en: 'Privacy Policy' },
    'terms.title': { ar: 'شروط الاستخدام', en: 'Terms of Service' },
    'notfound.title': { ar: 'الصفحة غير موجودة', en: 'Page Not Found' },
    'notfound.desc': { ar: 'الصفحة التي تبحث عنها غير موجودة.', en: 'The page you are looking for does not exist.' },
    'notfound.back': { ar: 'العودة للرئيسية', en: 'Back to Home' },
    'category.subcategories': { ar: 'التصنيفات الفرعية', en: 'Subcategories' },
    'category.in': { ar: 'في', en: 'in' },
    'services.title': { ar: 'خدمات', en: 'Services' },
    'services.loading': { ar: 'جاري التحميل...', en: 'Loading...' },
    'services.no_listings': { ar: 'لا توجد إعلانات', en: 'No listings found' },
    'faq.what_is': { ar: 'ما هي طلبنا؟', en: 'What is Talabna?' },
    'faq.what_is_answer': { ar: 'منصة إعلانات مبوبة رائدة تخدم المنطقة العربية.', en: 'A leading classified ads platform serving the Arab region.' },
    'faq.is_free': { ar: 'هل طلبنا مجانية؟', en: 'Is Talabna free?' },
    'faq.is_free_answer': { ar: 'نعم، إنشاء الحساب ونشر الإعلانات مجاني.', en: 'Yes, creating an account and posting ads is free.' },
    'faq.contact_seller': { ar: 'كيف أتواصل مع البائع؟', en: 'How do I contact a seller?' },
    'faq.contact_seller_answer': { ar: 'عبر الاتصال المباشر أو واتساب.', en: 'Via direct call or WhatsApp from the listing page.' },
    'faq.countries': { ar: 'ما هي الدول المدعومة؟', en: 'What countries are supported?' },
    'faq.countries_answer': { ar: 'أكثر من 22 دولة عربية.', en: 'Over 22 Arab countries.' },
  }

  /**
   * Reactive translation function.
   * Reads from API cache first, then ar/en fallbacks.
   * Vue re-renders when translations ref changes.
   */
  function t(key) {
    // API translations (reactive - Vue tracks this)
    const apiVal = translations.value[key]
    if (apiVal) return apiVal

    // Fallback for ar/en
    const fb = fallbacks[key]
    if (fb) return fb[locale.value] || fb['en'] || key

    return key
  }

  // Actions
  async function setLocale(newLocale) {
    locale.value = newLocale
    localStorage.setItem('locale', newLocale)
    document.documentElement.lang = newLocale
    const rtl = ['ar', 'he', 'fa', 'ur', 'ps', 'ku', 'sd']
    document.documentElement.dir = rtl.includes(newLocale) ? 'rtl' : 'ltr'

    // Load translations from API and update reactive ref
    try {
      const response = await fetch(`/api/translations/${newLocale}`)
      if (response.ok) {
        const data = await response.json()
        translations.value = data.translations || {}
      }
    } catch (e) {
      console.error('Failed to load translations:', e)
    }
    translationsLoaded.value = true
  }

  function toggleTheme() {
    theme.value = theme.value === 'light' ? 'dark' : 'light'
    localStorage.setItem('theme', theme.value)
    document.documentElement.setAttribute('data-theme', theme.value)
  }

  function setTheme(newTheme) {
    theme.value = newTheme
    localStorage.setItem('theme', newTheme)
    document.documentElement.setAttribute('data-theme', newTheme)
  }

  function setUser(userData) {
    user.value = userData
  }

  function setCategories(data) {
    categories.value = Array.isArray(data) ? data : []
  }

  function setCountries(data) {
    countries.value = Array.isArray(data) ? data : []
  }

  function setLoading(state) {
    loading.value = state
  }

  // Initialize
  async function init() {
    const savedLocale = localStorage.getItem('locale') || 'ar'
    const savedTheme = localStorage.getItem('theme') || 'light'
    await setLocale(savedLocale)
    setTheme(savedTheme)
  }

  return {
    // State
    locale,
    theme,
    user,
    categories,
    countries,
    loading,
    translations,
    translationsLoaded,
    // Getters
    isRTL,
    isAuthenticated,
    isDark,
    // Actions
    t,
    setLocale,
    toggleTheme,
    setTheme,
    setUser,
    setCategories,
    setCountries,
    setLoading,
    init,
  }
})
