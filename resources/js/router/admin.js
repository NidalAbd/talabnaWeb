import { createRouter, createWebHistory } from 'vue-router'

// Import components
import ModernDashboard from '../views/admin/ModernDashboard.vue'
import UsersList from '../views/admin/UsersList.vue'
import RolesList from '../views/admin/RolesList.vue'
import PermissionsList from '../views/admin/PermissionsList.vue'
import CategoriesList from '../views/admin/categories/CategoriesList.vue'
import SubCategoriesList from '../views/admin/subcategories/SubCategoriesList.vue'
import CountriesList from '../views/admin/countries/CountriesList.vue'
import CitiesList from '../views/admin/cities/CitiesList.vue'
import BadgeTypesList from '../views/admin/badge-types/BadgeTypesList.vue'
import ServicePostsList from '../views/admin/ServicePostsList.vue'
import ReportsList from '../views/admin/ReportsList.vue'
import BannedUsersList from '../views/admin/BannedUsersList.vue'
import BannedDevicesList from '../views/admin/BannedDevicesList.vue'
import PointPackagesList from '../views/admin/PointPackagesList.vue'
import CountryPricingList from '../views/admin/CountryPricingList.vue'
import PointPurchaseRequestsList from '../views/admin/PointPurchaseRequestsList.vue'
import PointTransactionsList from '../views/admin/PointTransactionsList.vue'
import MarketingNotificationsList from '../views/admin/MarketingNotificationsList.vue'
import Analytics from '../views/admin/Analytics.vue'
import StatisticsDashboard from '../views/admin/StatisticsDashboard.vue'
import RoleAssignmentsList from '../views/admin/RoleAssignmentsList.vue'
import PalServicePointsOverview from '../views/admin/PalServicePointsOverview.vue'
import SeoDashboard from '../views/admin/seo/SeoDashboard.vue'
import LanguagesList from '../views/admin/languages/LanguagesList.vue'
import TranslationsList from '../views/admin/translations/TranslationsList.vue'
import CommandMonitor from '../views/admin/CommandMonitor.vue'
import AiContentDashboard from '../views/admin/AiContentDashboard.vue'
import SubscriptionPlansList from '../views/admin/SubscriptionPlansList.vue'
import AutoTranslateDashboard from '../views/admin/AutoTranslateDashboard.vue'

const routes = [
  { path: '/dashboard', name: 'admin.dashboard', component: ModernDashboard, meta: { title: 'Dashboard' } },
  { path: '/users', name: 'users.index', component: UsersList, meta: { title: 'Users Management' } },
  { path: '/roles', name: 'roles.index', component: RolesList, meta: { title: 'Roles Management' } },
  { path: '/permissions', name: 'permissions.index', component: PermissionsList, meta: { title: 'Permissions Management' } },
  { path: '/categories', name: 'categories.index', component: CategoriesList, meta: { title: 'Categories Management' } },
  { path: '/subcategories', name: 'subcategories.index', component: SubCategoriesList, meta: { title: 'Sub-Categories Management' } },
  { path: '/countries', name: 'countries.index', component: CountriesList, meta: { title: 'Countries Management' } },
  { path: '/cities', name: 'cities.index', component: CitiesList, meta: { title: 'Cities Management' } },
  { path: '/badge-types', name: 'badge-types.index', component: BadgeTypesList, meta: { title: 'Badge Types Management' } },
  { path: '/service_posts', name: 'service_posts.index', component: ServicePostsList, meta: { title: 'Service Posts Management' } },
  { path: '/reports', name: 'reports.index', component: ReportsList, meta: { title: 'Reports Management' } },
  { path: '/admin/users/banned', name: 'users.banned', component: BannedUsersList, meta: { title: 'Banned Users' } },
  { path: '/admin/devices/banned', name: 'devices.banned', component: BannedDevicesList, meta: { title: 'Banned Devices' } },
  { path: '/admin/point-packages', name: 'point_packages.index', component: PointPackagesList, meta: { title: 'Point Packages' } },
  { path: '/admin/country-pricing', name: 'country_pricing.index', component: CountryPricingList, meta: { title: 'Country Pricing' } },
  { path: '/admin/point-purchase-requests', name: 'point_purchase_requests.index', component: PointPurchaseRequestsList, meta: { title: 'Purchase Requests' } },
  { path: '/admin/point-transactions', name: 'point_transactions.index', component: PointTransactionsList, meta: { title: 'Point Transactions' } },
  { path: '/admin/marketing-notifications', name: 'marketing_notifications.index', component: MarketingNotificationsList, meta: { title: 'Marketing Notifications' } },
  { path: '/admin/analytics', name: 'analytics.index', component: Analytics, meta: { title: 'Analytics' } },
  { path: '/admin/statistics', name: 'statistics.index', component: StatisticsDashboard, meta: { title: 'Statistics' } },
  { path: '/admin/role-assignments', name: 'role_assignments.index', component: RoleAssignmentsList, meta: { title: 'Role Assignments' } },
  { path: '/admin/palservice-points', name: 'palservice_points.index', component: PalServicePointsOverview, meta: { title: 'Points Overview' } },
  { path: '/admin/seo', name: 'seo.index', component: SeoDashboard, meta: { title: 'SEO Analytics' } },
  { path: '/admin/languages', name: 'languages.index', component: LanguagesList, meta: { title: 'Languages' } },
  { path: '/admin/translations', name: 'translations.index', component: TranslationsList, meta: { title: 'Translations' } },
  { path: '/admin/command-monitor', name: 'command_monitor.index', component: CommandMonitor, meta: { title: 'Command Monitor' } },
  { path: '/admin/ai-content', name: 'ai_content.index', component: AiContentDashboard, meta: { title: 'AI Content Generator' } },
  { path: '/admin/subscription-plans', name: 'subscription_plans.index', component: SubscriptionPlansList, meta: { title: 'Subscription Plans' } },
  { path: '/admin/auto-translate', name: 'auto_translate.index', component: AutoTranslateDashboard, meta: { title: 'Auto-Translation' } },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Update page title on route change
router.afterEach((to) => {
  if (to.meta.title) {
    document.title = `${to.meta.title} - ${window.appName || 'Admin'}`
  }
})

export default router
