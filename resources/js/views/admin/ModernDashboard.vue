<template>
  <div class="dashboard-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header mb-4">
      <div>
        <h1 class="dashboard-title">Dashboard Overview</h1>
        <p class="dashboard-subtitle">Comprehensive statistics and analytics for all platform sections</p>
      </div>
      <div class="header-meta">
        <small class="text-muted">Last updated</small>
        <strong>{{ formatDate(new Date()) }}</strong>
      </div>
    </div>

    <!-- Statistics Tabs -->
    <div class="stats-tabs-container mb-4">
      <div class="stats-tabs">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          class="stats-tab-btn"
          :class="{ active: activeTab === tab.id }"
          @click="activeTab = tab.id"
        >
          <i :class="tab.icon"></i>
          {{ tab.label }}
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="loader-advanced"></div>
      <p>Loading statistics...</p>
    </div>

    <!-- Tab Content -->
    <div v-else>
      <!-- Overview Tab -->
      <div v-if="activeTab === 'overview'" class="tab-content-panel">
        <div class="stats-grid mb-4">
          <div class="stat-card-advanced stat-info">
            <div class="stat-icon-wrapper">
              <div class="stat-icon">
                <i class="fas fa-users"></i>
              </div>
            </div>
            <div class="stat-details">
              <h2 class="stat-number">{{ overviewStats.total_users || 0 }}</h2>
              <p class="stat-label">Total Users</p>
            </div>
          </div>

          <div class="stat-card-advanced stat-success">
            <div class="stat-icon-wrapper">
              <div class="stat-icon">
                <i class="fas fa-user-shield"></i>
              </div>
            </div>
            <div class="stat-details">
              <h2 class="stat-number">{{ overviewStats.total_roles || 0 }}</h2>
              <p class="stat-label">Total Roles</p>
            </div>
          </div>

          <div class="stat-card-advanced stat-warning">
            <div class="stat-icon-wrapper">
              <div class="stat-icon">
                <i class="fas fa-key"></i>
              </div>
            </div>
            <div class="stat-details">
              <h2 class="stat-number">{{ overviewStats.total_permissions || 0 }}</h2>
              <p class="stat-label">Total Permissions</p>
            </div>
          </div>

          <div class="stat-card-advanced stat-danger">
            <div class="stat-icon-wrapper">
              <div class="stat-icon">
                <i class="fas fa-folder"></i>
              </div>
            </div>
            <div class="stat-details">
              <h2 class="stat-number">{{ overviewStats.total_categories || 0 }}</h2>
              <p class="stat-label">Total Categories</p>
            </div>
          </div>

          <div class="stat-card-advanced stat-secondary">
            <div class="stat-icon-wrapper">
              <div class="stat-icon">
                <i class="fas fa-folder-tree"></i>
              </div>
            </div>
            <div class="stat-details">
              <h2 class="stat-number">{{ overviewStats.total_subcategories || 0 }}</h2>
              <p class="stat-label">Total Sub-Categories</p>
            </div>
          </div>

          <div class="stat-card-advanced stat-info">
            <div class="stat-icon-wrapper">
              <div class="stat-icon">
                <i class="fas fa-globe"></i>
              </div>
            </div>
            <div class="stat-details">
              <h2 class="stat-number">{{ overviewStats.total_countries || 0 }}</h2>
              <p class="stat-label">Total Countries</p>
            </div>
          </div>

          <div class="stat-card-advanced stat-success">
            <div class="stat-icon-wrapper">
              <div class="stat-icon">
                <i class="fas fa-city"></i>
              </div>
            </div>
            <div class="stat-details">
              <h2 class="stat-number">{{ overviewStats.total_cities || 0 }}</h2>
              <p class="stat-label">Total Cities</p>
            </div>
          </div>

          <div class="stat-card-advanced stat-primary">
            <div class="stat-icon-wrapper">
              <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
              </div>
            </div>
            <div class="stat-details">
              <h2 class="stat-number">{{ overviewStats.system_health || '100%' }}</h2>
              <p class="stat-label">System Health</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Users Tab -->
      <div v-if="activeTab === 'users'" class="tab-content-panel">
        <div class="stats-grid">
          <div
            v-for="stat in usersStats"
            :key="stat.label"
            class="stat-card-advanced"
            :class="`stat-${stat.color}`"
          >
            <div class="stat-icon-wrapper">
              <div class="stat-icon">
                <i :class="stat.icon"></i>
              </div>
            </div>
            <div class="stat-details">
              <h2 class="stat-number">{{ stat.value }}</h2>
              <p class="stat-label">{{ stat.label }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Roles Tab -->
      <div v-if="activeTab === 'roles'" class="tab-content-panel">
        <div class="stats-grid">
          <div
            v-for="stat in rolesStats"
            :key="stat.label"
            class="stat-card-advanced"
            :class="`stat-${stat.color}`"
          >
            <div class="stat-icon-wrapper">
              <div class="stat-icon">
                <i :class="stat.icon"></i>
              </div>
            </div>
            <div class="stat-details">
              <h2 class="stat-number">{{ stat.value }}</h2>
              <p class="stat-label">{{ stat.label }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Permissions Tab -->
      <div v-if="activeTab === 'permissions'" class="tab-content-panel">
        <div class="stats-grid">
          <div
            v-for="stat in permissionsStats"
            :key="stat.label"
            class="stat-card-advanced"
            :class="`stat-${stat.color}`"
          >
            <div class="stat-icon-wrapper">
              <div class="stat-icon">
                <i :class="stat.icon"></i>
              </div>
            </div>
            <div class="stat-details">
              <h2 class="stat-number">{{ stat.value }}</h2>
              <p class="stat-label">{{ stat.label }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Categories Tab -->
      <div v-if="activeTab === 'categories'" class="tab-content-panel">
        <div class="stats-grid">
          <div
            v-for="stat in categoriesStats"
            :key="stat.label"
            class="stat-card-advanced"
            :class="`stat-${stat.color}`"
          >
            <div class="stat-icon-wrapper">
              <div class="stat-icon">
                <i :class="stat.icon"></i>
              </div>
            </div>
            <div class="stat-details">
              <h2 class="stat-number">{{ stat.value }}</h2>
              <p class="stat-label">{{ stat.label }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Sub-Categories Tab -->
      <div v-if="activeTab === 'subcategories'" class="tab-content-panel">
        <div class="stats-grid">
          <div
            v-for="stat in subcategoriesStats"
            :key="stat.label"
            class="stat-card-advanced"
            :class="`stat-${stat.color}`"
          >
            <div class="stat-icon-wrapper">
              <div class="stat-icon">
                <i :class="stat.icon"></i>
              </div>
            </div>
            <div class="stat-details">
              <h2 class="stat-number">{{ stat.value }}</h2>
              <p class="stat-label">{{ stat.label }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Countries Tab -->
      <div v-if="activeTab === 'countries'" class="tab-content-panel">
        <div class="stats-grid">
          <div
            v-for="stat in countriesStats"
            :key="stat.label"
            class="stat-card-advanced"
            :class="`stat-${stat.color}`"
          >
            <div class="stat-icon-wrapper">
              <div class="stat-icon">
                <i :class="stat.icon"></i>
              </div>
            </div>
            <div class="stat-details">
              <h2 class="stat-number">{{ stat.value }}</h2>
              <p class="stat-label">{{ stat.label }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Cities Tab -->
      <div v-if="activeTab === 'cities'" class="tab-content-panel">
        <div class="stats-grid">
          <div
            v-for="stat in citiesStats"
            :key="stat.label"
            class="stat-card-advanced"
            :class="`stat-${stat.color}`"
          >
            <div class="stat-icon-wrapper">
              <div class="stat-icon">
                <i :class="stat.icon"></i>
              </div>
            </div>
            <div class="stat-details">
              <h2 class="stat-number">{{ stat.value }}</h2>
              <p class="stat-label">{{ stat.label }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Badge Types Tab -->
      <div v-if="activeTab === 'badges'" class="tab-content-panel">
        <div class="stats-grid">
          <div
            v-for="stat in badgesStats"
            :key="stat.label"
            class="stat-card-advanced"
            :class="`stat-${stat.color}`"
          >
            <div class="stat-icon-wrapper">
              <div class="stat-icon">
                <i :class="stat.icon"></i>
              </div>
            </div>
            <div class="stat-details">
              <h2 class="stat-number">{{ stat.value }}</h2>
              <p class="stat-label">{{ stat.label }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions mt-4">
      <h3 class="section-subtitle mb-3">
        <i class="fas fa-bolt"></i>
        Quick Actions
      </h3>
      <div class="actions-grid">
        <router-link to="/users" class="action-card">
          <div class="action-icon bg-info">
            <i class="fas fa-users"></i>
          </div>
          <div class="action-content">
            <h4>Manage Users</h4>
            <p>View and manage all users</p>
          </div>
          <i class="fas fa-arrow-right action-arrow"></i>
        </router-link>

        <router-link to="/roles" class="action-card">
          <div class="action-icon bg-success">
            <i class="fas fa-user-shield"></i>
          </div>
          <div class="action-content">
            <h4>Manage Roles</h4>
            <p>Configure user roles</p>
          </div>
          <i class="fas fa-arrow-right action-arrow"></i>
        </router-link>

        <router-link to="/permissions" class="action-card">
          <div class="action-icon bg-warning">
            <i class="fas fa-key"></i>
          </div>
          <div class="action-content">
            <h4>Manage Permissions</h4>
            <p>Set access permissions</p>
          </div>
          <i class="fas fa-arrow-right action-arrow"></i>
        </router-link>

        <router-link to="/categories" class="action-card">
          <div class="action-icon bg-danger">
            <i class="fas fa-folder"></i>
          </div>
          <div class="action-content">
            <h4>Manage Categories</h4>
            <p>Organize content categories</p>
          </div>
          <i class="fas fa-arrow-right action-arrow"></i>
        </router-link>

        <router-link to="/subcategories" class="action-card">
          <div class="action-icon bg-secondary">
            <i class="fas fa-folder-tree"></i>
          </div>
          <div class="action-content">
            <h4>Manage Sub-Categories</h4>
            <p>Organize sub-categories</p>
          </div>
          <i class="fas fa-arrow-right action-arrow"></i>
        </router-link>

        <router-link to="/countries" class="action-card">
          <div class="action-icon bg-info">
            <i class="fas fa-globe"></i>
          </div>
          <div class="action-content">
            <h4>Manage Countries</h4>
            <p>Manage countries and locations</p>
          </div>
          <i class="fas fa-arrow-right action-arrow"></i>
        </router-link>

        <router-link to="/cities" class="action-card">
          <div class="action-icon bg-success">
            <i class="fas fa-city"></i>
          </div>
          <div class="action-content">
            <h4>Manage Cities</h4>
            <p>Organize cities by country</p>
          </div>
          <i class="fas fa-arrow-right action-arrow"></i>
        </router-link>

        <router-link to="/badge-types" class="action-card">
          <div class="action-icon bg-warning">
            <i class="fas fa-certificate"></i>
          </div>
          <div class="action-content">
            <h4>Manage Badge Types</h4>
            <p>Configure service post badges</p>
          </div>
          <i class="fas fa-arrow-right action-arrow"></i>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const loading = ref(true)
const activeTab = ref('overview')

const tabs = [
  { id: 'overview', label: 'Overview', icon: 'fas fa-chart-pie' },
  { id: 'users', label: 'Users', icon: 'fas fa-users' },
  { id: 'roles', label: 'Roles', icon: 'fas fa-user-shield' },
  { id: 'permissions', label: 'Permissions', icon: 'fas fa-key' },
  { id: 'categories', label: 'Categories', icon: 'fas fa-folder' },
  { id: 'subcategories', label: 'Sub-Categories', icon: 'fas fa-folder-tree' },
  { id: 'countries', label: 'Countries', icon: 'fas fa-globe' },
  { id: 'cities', label: 'Cities', icon: 'fas fa-city' },
  { id: 'badges', label: 'Badge Types', icon: 'fas fa-certificate' }
]

const overviewStats = ref({
  total_users: 0,
  total_roles: 0,
  total_permissions: 0,
  total_categories: 0,
  total_subcategories: 0,
  total_countries: 0,
  total_cities: 0,
  system_health: '100%'
})

const usersStats = ref([])
const rolesStats = ref([])
const permissionsStats = ref([])
const categoriesStats = ref([])
const subcategoriesStats = ref([])
const countriesStats = ref([])
const citiesStats = ref([])
const badgesStats = ref([])

onMounted(async () => {
  await loadAllStats()
})

const loadAllStats = async () => {
  loading.value = true
  try {
    await Promise.all([
      loadOverviewStats(),
      loadUsersStats(),
      loadRolesStats(),
      loadPermissionsStats(),
      loadCategoriesStats(),
      loadSubCategoriesStats(),
      loadCountriesStats(),
      loadCitiesStats(),
      loadBadgesStats()
    ])
  } catch (error) {
    console.error('Error loading dashboard stats:', error)
  } finally {
    loading.value = false
  }
}

const loadOverviewStats = async () => {
  try {
    // Load users count
    const usersResponse = await fetch('/api/admin/users/stats')
    const usersData = await usersResponse.json()

    // Load roles count
    const rolesResponse = await fetch('/api/admin/roles/stats')
    const rolesData = await rolesResponse.json()

    // Load permissions count
    const permissionsResponse = await fetch('/api/admin/permissions/stats')
    const permissionsData = await permissionsResponse.json()

    // Load categories count
    const categoriesResponse = await fetch('/api/admin/categories/stats')
    const categoriesData = await categoriesResponse.json()

    // Load subcategories count
    const subcategoriesResponse = await fetch('/api/admin/subcategories/stats')
    const subcategoriesData = await subcategoriesResponse.json()

    // Load countries count
    const countriesResponse = await fetch('/api/admin/countries/stats')
    const countriesData = await countriesResponse.json()

    // Load cities count
    const citiesResponse = await fetch('/api/admin/cities/stats')
    const citiesData = await citiesResponse.json()

    // Find total stats
    const totalUsers = usersData.stats?.find(s => s.label === 'Total Users')?.value || 0
    const totalRoles = rolesData.stats?.find(s => s.label === 'Total Roles')?.value || 0
    const totalPermissions = permissionsData.stats?.find(s => s.label === 'Total Permissions')?.value || 0
    const totalCategories = categoriesData.stats?.find(s => s.label === 'Total Categories')?.value || 0
    const totalSubCategories = subcategoriesData.stats?.find(s => s.label === 'Total Sub-Categories')?.value || 0
    const totalCountries = countriesData.stats?.find(s => s.label === 'Total Countries')?.value || 0
    const totalCities = citiesData.stats?.find(s => s.label === 'Total Cities')?.value || 0

    overviewStats.value = {
      total_users: totalUsers,
      total_roles: totalRoles,
      total_permissions: totalPermissions,
      total_categories: totalCategories,
      total_subcategories: totalSubCategories,
      total_countries: totalCountries,
      total_cities: totalCities,
      system_health: '100%'
    }
  } catch (error) {
    console.error('Error loading overview stats:', error)
  }
}

const loadUsersStats = async () => {
  try {
    const response = await fetch('/api/admin/users/stats')
    const data = await response.json()
    usersStats.value = data.stats || []
  } catch (error) {
    console.error('Error loading users stats:', error)
  }
}

const loadRolesStats = async () => {
  try {
    const response = await fetch('/api/admin/roles/stats')
    const data = await response.json()
    rolesStats.value = data.stats || []
  } catch (error) {
    console.error('Error loading roles stats:', error)
  }
}

const loadPermissionsStats = async () => {
  try {
    const response = await fetch('/api/admin/permissions/stats')
    const data = await response.json()
    permissionsStats.value = data.stats || []
  } catch (error) {
    console.error('Error loading permissions stats:', error)
  }
}

const loadCategoriesStats = async () => {
  try {
    const response = await fetch('/api/admin/categories/stats')
    const data = await response.json()
    categoriesStats.value = data.stats || []
  } catch (error) {
    console.error('Error loading categories stats:', error)
  }
}

const loadSubCategoriesStats = async () => {
  try {
    const response = await fetch('/api/admin/subcategories/stats')
    const data = await response.json()
    subcategoriesStats.value = data.stats || []
  } catch (error) {
    console.error('Error loading subcategories stats:', error)
  }
}

const loadCountriesStats = async () => {
  try {
    const response = await fetch('/api/admin/countries/stats')
    const data = await response.json()
    countriesStats.value = data.stats || []
  } catch (error) {
    console.error('Error loading countries stats:', error)
  }
}

const loadCitiesStats = async () => {
  try {
    const response = await fetch('/api/admin/cities/stats')
    const data = await response.json()
    citiesStats.value = data.stats || []
  } catch (error) {
    console.error('Error loading cities stats:', error)
  }
}

const loadBadgesStats = async () => {
  try {
    const response = await fetch('/api/admin/badge-types/stats')
    const data = await response.json()
    badgesStats.value = data.stats || []
  } catch (error) {
    console.error('Error loading badges stats:', error)
  }
}

const formatDate = (date) => {
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}
</script>

<style scoped>
.dashboard-container {
  padding: 0;
  background: #f5f7fa;
}

/* Dashboard Header */
.dashboard-header {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dashboard-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0 0 0.25rem 0;
}

.dashboard-subtitle {
  color: #6c757d;
  margin: 0;
  font-size: 0.9rem;
}

.header-meta {
  text-align: right;
}

.header-meta small {
  display: block;
  margin-bottom: 0.25rem;
}

/* Statistics Tabs */
.stats-tabs-container {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
}

.stats-tabs {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
}

.stats-tab-btn {
  padding: 0.75rem 1.5rem;
  border: none;
  background: transparent;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  color: #6c757d;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  white-space: nowrap;
}

.stats-tab-btn:hover {
  background: #f8f9fa;
  color: #495057;
}

.stats-tab-btn.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

/* Tab Content */
.tab-content-panel {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 12px;
}

.loader-advanced {
  width: 60px;
  height: 60px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  margin: 0 auto 1rem;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Quick Actions */
.quick-actions {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
}

.section-subtitle {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
}

.action-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  border-radius: 12px;
  background: #f8f9fa;
  border: 2px solid transparent;
  transition: all 0.2s ease;
  text-decoration: none;
  color: inherit;
}

.action-card:hover {
  border-color: #667eea;
  background: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.action-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  color: white;
  flex-shrink: 0;
}

.action-icon.bg-info {
  background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}

.action-icon.bg-success {
  background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
}

.action-icon.bg-warning {
  background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
}

.action-content {
  flex: 1;
}

.action-content h4 {
  margin: 0 0 0.25rem 0;
  font-size: 1rem;
  font-weight: 600;
  color: #2c3e50;
}

.action-content p {
  margin: 0;
  font-size: 0.85rem;
  color: #6c757d;
}

.action-arrow {
  color: #6c757d;
  transition: transform 0.2s ease;
}

.action-card:hover .action-arrow {
  transform: translateX(4px);
  color: #667eea;
}

/* Responsive */
@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .header-meta {
    text-align: left;
  }

  .stats-tabs {
    flex-wrap: wrap;
  }

  .actions-grid {
    grid-template-columns: 1fr;
  }
}
</style>
