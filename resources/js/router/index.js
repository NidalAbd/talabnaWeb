import { createRouter, createWebHistory } from 'vue-router'

// Lazy load views for better performance
const Home = () => import('@/views/Home.vue')
const Browse = () => import('@/views/Browse.vue')
const ListingDetails = () => import('@/views/ListingDetails.vue')
const Category = () => import('@/views/Category.vue')
const Search = () => import('@/views/Search.vue')
const UserProfile = () => import('@/views/UserProfile.vue')
const Services = () => import('@/views/Services.vue')
const About = () => import('@/views/About.vue')
const Contact = () => import('@/views/Contact.vue')
const Privacy = () => import('@/views/Privacy.vue')
const Terms = () => import('@/views/Terms.vue')
const NotFound = () => import('@/views/NotFound.vue')

// Admin views
const AdminLayout = () => import('@/layouts/AdminLayout.vue')
const AdminDashboard = () => import('@/views/admin/Dashboard.vue')

const routes = [
  {
    path: '/',
    name: 'home',
    component: Home,
    meta: {
      title: 'طلبنا - Talabna | الصفحة الرئيسية',
      description: 'أكبر منصة للإعلانات المبوبة في الوطن العربي',
    },
  },
  {
    path: '/browse',
    name: 'browse',
    component: Browse,
    meta: {
      title: 'تصفح الإعلانات - طلبنا',
      description: 'تصفح جميع الإعلانات المبوبة',
    },
  },
  {
    path: '/category/:id/:slug?',
    name: 'category',
    component: Category,
    meta: {
      title: 'التصنيف - طلبنا',
    },
  },
  {
    path: '/listing/:id/:slug?',
    name: 'listing',
    component: ListingDetails,
    meta: {
      title: 'تفاصيل الإعلان - طلبنا',
    },
  },
  {
    path: '/search',
    name: 'search',
    component: Search,
    meta: {
      title: 'البحث - طلبنا',
      description: 'ابحث في الإعلانات المبوبة',
    },
  },
  {
    path: '/user/:id',
    name: 'user-profile',
    component: UserProfile,
    meta: {
      title: 'الملف الشخصي - طلبنا',
    },
  },
  {
    path: '/about',
    name: 'about',
    component: About,
    meta: {
      title: 'من نحن - طلبنا',
      description: 'تعرف على منصة طلبنا للإعلانات المبوبة',
    },
  },
  {
    path: '/contact',
    name: 'contact',
    component: Contact,
    meta: {
      title: 'اتصل بنا - طلبنا',
      description: 'تواصل معنا للاستفسارات والدعم',
    },
  },
  {
    path: '/privacy',
    name: 'privacy',
    component: Privacy,
    meta: {
      title: 'سياسة الخصوصية - طلبنا',
    },
  },
  {
    path: '/terms',
    name: 'terms',
    component: Terms,
    meta: {
      title: 'شروط الاستخدام - طلبنا',
    },
  },
  // Location-based services pages (SEO friendly)
  // /services/:countryId/:countrySlug - Services in country
  // /services/:countryId/:countrySlug/:cityId/:citySlug - Services in city
  // /services/:countryId/:countrySlug/:cityId/:citySlug/:categoryId/:categorySlug - Category services in city
  {
    path: '/services/:countryId/:countrySlug?',
    name: 'services-country',
    component: Services,
    meta: {
      title: 'خدمات - طلبنا',
      description: 'تصفح الخدمات والإعلانات حسب الموقع',
    },
  },
  {
    path: '/services/:countryId/:countrySlug/:cityId/:citySlug?',
    name: 'services-city',
    component: Services,
    meta: {
      title: 'خدمات - طلبنا',
      description: 'تصفح الخدمات والإعلانات في مدينتك',
    },
  },
  {
    path: '/services/:countryId/:countrySlug/:cityId/:citySlug/:categoryId/:categorySlug?',
    name: 'services-category',
    component: Services,
    meta: {
      title: 'خدمات - طلبنا',
      description: 'تصفح الخدمات والإعلانات حسب التصنيف والموقع',
    },
  },
  // Redirect /dashboard to /admin/dashboard (for AdminLTE sidebar)
  {
    path: '/dashboard',
    redirect: '/admin/dashboard',
  },
  // Admin routes
  {
    path: '/admin',
    component: AdminLayout,
    meta: {
      requiresAuth: true,
      requiresAdmin: true,
    },
    children: [
      {
        path: '',
        redirect: '/admin/dashboard',
      },
      {
        path: 'dashboard',
        name: 'admin-dashboard',
        component: AdminDashboard,
        meta: {
          title: 'لوحة التحكم - طلبنا',
        },
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFound,
    meta: {
      title: 'الصفحة غير موجودة - طلبنا',
    },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  },
})

// Update page title on navigation
router.beforeEach((to, from, next) => {
  document.title = to.meta.title || 'طلبنا - Talabna'
  next()
})

export default router
