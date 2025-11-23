import { createRouter, createWebHistory } from 'vue-router'

// Lazy load views for better performance
const Home = () => import('@/views/Home.vue')
const Browse = () => import('@/views/Browse.vue')
const ListingDetails = () => import('@/views/ListingDetails.vue')
const Category = () => import('@/views/Category.vue')
const Search = () => import('@/views/Search.vue')
const UserProfile = () => import('@/views/UserProfile.vue')
const About = () => import('@/views/About.vue')
const Contact = () => import('@/views/Contact.vue')
const Privacy = () => import('@/views/Privacy.vue')
const Terms = () => import('@/views/Terms.vue')
const NotFound = () => import('@/views/NotFound.vue')

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
