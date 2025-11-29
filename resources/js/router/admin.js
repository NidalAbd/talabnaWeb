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

const routes = [
  {
    path: '/dashboard',
    name: 'admin.dashboard',
    component: ModernDashboard,
    meta: { title: 'Dashboard' }
  },
  {
    path: '/users',
    name: 'users.index',
    component: UsersList,
    meta: { title: 'Users Management' }
  },
  {
    path: '/roles',
    name: 'roles.index',
    component: RolesList,
    meta: { title: 'Roles Management' }
  },
  {
    path: '/permissions',
    name: 'permissions.index',
    component: PermissionsList,
    meta: { title: 'Permissions Management' }
  },
  {
    path: '/categories',
    name: 'categories.index',
    component: CategoriesList,
    meta: { title: 'Categories Management' }
  },
  {
    path: '/subcategories',
    name: 'subcategories.index',
    component: SubCategoriesList,
    meta: { title: 'Sub-Categories Management' }
  },
  {
    path: '/countries',
    name: 'countries.index',
    component: CountriesList,
    meta: { title: 'Countries Management' }
  },
  {
    path: '/cities',
    name: 'cities.index',
    component: CitiesList,
    meta: { title: 'Cities Management' }
  },
  {
    path: '/badge-types',
    name: 'badge-types.index',
    component: BadgeTypesList,
    meta: { title: 'Badge Types Management' }
  }
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
