<template>
  <div class="users-management-advanced">
    <!-- Advanced Search & Filters -->
    <div class="search-filter-bar">
      <div class="search-box">
        <i class="fas fa-search search-icon"></i>
        <input
          type="text"
          v-model="filters.search"
          class="search-input"
          placeholder="Search users by name, email, or phone..."
        >
        <span v-if="filters.search" class="clear-search" @click="filters.search = ''">
          <i class="fas fa-times"></i>
        </span>
      </div>

      <div class="filter-controls">
        <div class="filter-group">
          <button
            class="filter-btn"
            :class="{ active: filters.status === '' }"
            @click="filters.status = ''"
          >
            <i class="fas fa-users"></i>
            All
          </button>
          <button
            class="filter-btn"
            :class="{ active: filters.status === 'active' }"
            @click="filters.status = 'active'"
          >
            <i class="fas fa-check-circle"></i>
            Active
          </button>
          <button
            class="filter-btn"
            :class="{ active: filters.status === 'banned' }"
            @click="filters.status = 'banned'"
          >
            <i class="fas fa-ban"></i>
            Banned
          </button>
        </div>

        <select v-model="filters.role" class="role-select">
          <option value="">All Roles</option>
          <option v-for="role in roles" :key="role.id" :value="role.name">
            {{ role.display_name || role.name }}
          </option>
        </select>

        <button class="action-btn primary" @click="resetFilters">
          <i class="fas fa-redo"></i>
        </button>
      </div>
    </div>

    <!-- View Toggle -->
    <div class="view-controls mb-4">
      <div class="view-toggle">
        <button
          class="toggle-btn"
          :class="{ active: viewMode === 'grid' }"
          @click="viewMode = 'grid'"
        >
          <i class="fas fa-th"></i>
          Grid
        </button>
        <button
          class="toggle-btn"
          :class="{ active: viewMode === 'list' }"
          @click="viewMode = 'list'"
        >
          <i class="fas fa-list"></i>
          List
        </button>
      </div>

      <div class="results-info">
        Showing {{ users.data.length }} of {{ users.total }} users
      </div>

      <a href="/users/create" class="action-btn success">
        <i class="fas fa-plus-circle"></i>
        Add User
      </a>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="loader-advanced"></div>
      <p>Loading users...</p>
    </div>

    <!-- Users Grid View -->
    <div v-else-if="viewMode === 'grid'" class="users-grid">
      <div
        v-for="user in users.data"
        :key="user.id"
        class="user-card"
        :class="{ banned: user.is_active === 'banned' }"
      >
        <div class="card-header-custom">
          <div class="user-avatar-wrapper">
            <img :src="user.avatar_url" :alt="user.user_name" class="user-avatar">
            <span class="status-indicator" :class="user.is_active"></span>
          </div>
          <div class="card-menu">
            <button class="menu-btn" @click="toggleMenu(user.id)">
              <i class="fas fa-ellipsis-v"></i>
            </button>
            <div v-if="activeMenu === user.id" class="dropdown-menu">
              <a :href="`/users/${user.id}`" class="menu-item">
                <i class="fas fa-eye"></i>
                View Details
              </a>
              <a :href="`/users/${user.id}/edit`" class="menu-item">
                <i class="fas fa-edit"></i>
                Edit User
              </a>
              <button @click="handleToggleBan(user)" class="menu-item">
                <i :class="user.is_active === 'active' ? 'fas fa-ban' : 'fas fa-check'"></i>
                {{ user.is_active === 'active' ? 'Ban User' : 'Unban User' }}
              </button>
              <button @click="handleDelete(user)" class="menu-item danger">
                <i class="fas fa-trash"></i>
                Delete
              </button>
            </div>
          </div>
        </div>

        <div class="card-body-custom">
          <h3 class="user-name">{{ user.user_name }}</h3>
          <p class="user-id">#{{ user.id }}</p>

          <div class="user-contact">
            <div class="contact-item" v-if="user.email">
              <i class="fas fa-envelope"></i>
              <span>{{ truncate(user.email, 25) }}</span>
            </div>
            <div class="contact-item" v-if="user.phones">
              <i class="fas fa-phone"></i>
              <span>{{ user.phones }}</span>
            </div>
          </div>

          <div class="user-roles">
            <span
              v-for="role in user.roles"
              :key="role"
              class="role-badge"
              :class="getRoleBadgeClass(role)"
            >
              {{ role }}
            </span>
          </div>

          <div class="spacer"></div>

          <div class="user-stats">
            <div class="stat-item">
              <i class="fas fa-box"></i>
              <span>{{ user.service_posts_count }} Posts</span>
            </div>
            <div class="stat-item warning">
              <i class="fas fa-exclamation-triangle"></i>
              <span>{{ user.reports_count }} Reports</span>
            </div>
          </div>
        </div>

        <div class="card-footer-custom">
          <button
            @click="handleToggleBan(user)"
            :disabled="togglingBan[user.id]"
            class="action-btn"
            :class="user.is_active === 'active' ? 'danger' : 'success'"
          >
            <i v-if="togglingBan[user.id]" class="fas fa-spinner fa-spin"></i>
            <i v-else :class="user.is_active === 'active' ? 'fas fa-ban' : 'fas fa-check'"></i>
            {{ user.is_active === 'active' ? 'Ban' : 'Unban' }}
          </button>
          <a :href="`/users/${user.id}`" class="action-btn primary">
            <i class="fas fa-arrow-right"></i>
            View
          </a>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="users.data.length === 0" class="empty-state-advanced">
        <div class="empty-icon">
          <i class="fas fa-users"></i>
        </div>
        <h3>No Users Found</h3>
        <p>Try adjusting your filters or search criteria</p>
        <button @click="resetFilters" class="action-btn primary">
          <i class="fas fa-redo"></i>
          Reset Filters
        </button>
      </div>
    </div>

    <!-- Modern Table View -->
    <div v-else class="modern-table-container">
      <table class="modern-table">
        <thead>
          <tr>
            <th style="width: 60px;"></th>
            <th>User</th>
            <th>Contact</th>
            <th>Roles</th>
            <th>Status</th>
            <th style="width: 100px;">Stats</th>
            <th style="width: 160px; text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users.data" :key="user.id">
            <!-- Avatar -->
            <td>
              <img :src="user.avatar_url" :alt="user.user_name" class="table-avatar">
            </td>

            <!-- User Info -->
            <td>
              <div class="table-name">{{ user.user_name }}</div>
              <div class="table-subtitle">#{{ user.id }} • {{ user.name }}</div>
            </td>

            <!-- Contact -->
            <td>
              <div class="table-subtitle">
                <i class="fas fa-envelope"></i> {{ user.email || 'N/A' }}
              </div>
              <div class="table-subtitle" v-if="user.phones">
                <i class="fas fa-phone"></i> {{ user.phones }}
              </div>
            </td>

            <!-- Roles -->
            <td>
              <span
                v-for="role in user.roles"
                :key="role"
                class="table-badge"
                :class="getRoleBadgeClass(role)"
              >
                {{ role }}
              </span>
            </td>

            <!-- Status -->
            <td>
              <span class="table-badge" :class="user.is_active === 'active' ? 'badge-success' : 'badge-danger'">
                <span class="status-dot" :class="user.is_active"></span>
                {{ user.is_active === 'active' ? 'Active' : 'Banned' }}
              </span>
            </td>

            <!-- Stats -->
            <td>
              <div class="table-meta">
                <span title="Service Posts">
                  <i class="fas fa-box"></i> {{ user.service_posts_count }}
                </span>
                <span title="Reports" :class="user.reports_count > 0 ? 'text-danger' : ''">
                  <i class="fas fa-exclamation-triangle"></i> {{ user.reports_count }}
                </span>
              </div>
            </td>

            <!-- Actions -->
            <td>
              <div class="table-actions">
                <a :href="`/users/${user.id}`" class="table-action-btn btn-view" title="View">
                  <i class="fas fa-eye"></i>
                </a>
                <a :href="`/users/${user.id}/edit`" class="table-action-btn btn-edit" title="Edit">
                  <i class="fas fa-edit"></i>
                </a>
                <button
                  @click="handleToggleBan(user)"
                  class="table-action-btn btn-ban"
                  :title="user.is_active === 'active' ? 'Ban User' : 'Unban User'"
                  :disabled="togglingBan[user.id]"
                >
                  <i v-if="togglingBan[user.id]" class="fas fa-spinner fa-spin"></i>
                  <i v-else :class="user.is_active === 'active' ? 'fas fa-ban' : 'fas fa-check'"></i>
                </button>
                <button
                  @click="handleDelete(user)"
                  class="table-action-btn btn-delete"
                  title="Delete"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>

          <!-- Empty State Row -->
          <tr v-if="users.data.length === 0">
            <td colspan="7" class="table-empty-state">
              <i class="fas fa-users"></i>
              <h3>No Users Found</h3>
              <p>Try adjusting your filters or search criteria</p>
              <button @click="resetFilters" class="action-btn primary">
                <i class="fas fa-redo"></i>
                Reset Filters
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Advanced Pagination -->
    <div v-if="users.data.length > 0" class="pagination-advanced">
      <button
        class="page-btn"
        :disabled="users.current_page === 1"
        @click="loadUsers(users.current_page - 1)"
      >
        <i class="fas fa-chevron-left"></i>
        Previous
      </button>

      <div class="page-numbers">
        <button
          v-for="page in visiblePages"
          :key="page"
          class="page-number"
          :class="{ active: page === users.current_page }"
          @click="loadUsers(page)"
        >
          {{ page }}
        </button>
      </div>

      <button
        class="page-btn"
        :disabled="users.current_page === users.last_page"
        @click="loadUsers(users.current_page + 1)"
      >
        Next
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, reactive, computed, onBeforeUnmount } from 'vue'
import { useUsers } from '../../composables/useUsers'

const { users, loading, fetchUsers, toggleBan, deleteUser } = useUsers()

const roles = ref([])
const togglingBan = reactive({})
const viewMode = ref('list')
const activeMenu = ref(null)

const filters = reactive({
  status: '',
  role: '',
  search: ''
})

const visiblePages = computed(() => {
  const pages = []
  const current = users.value.current_page
  const last = users.value.last_page

  let start = Math.max(1, current - 2)
  let end = Math.min(last, current + 2)

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

onMounted(async () => {
  await loadData()
  document.addEventListener('click', closeMenus)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', closeMenus)
})

// Watch filters with debounce for search
let searchTimeout = null
watch(() => filters.search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadUsers(1), 300)
})

watch(() => [filters.status, filters.role], () => {
  loadUsers(1)
})

const loadData = async () => {
  await Promise.all([
    loadUsers(),
    loadRoles()
  ])
}

const loadUsers = async (page = 1) => {
  await fetchUsers({ ...filters, page })
}

const loadRoles = async () => {
  try {
    const response = await fetch('/api/admin/users/roles')
    const data = await response.json()
    roles.value = data.roles
  } catch (error) {
    console.error('Error loading roles:', error)
  }
}

const resetFilters = () => {
  filters.status = ''
  filters.role = ''
  filters.search = ''
  loadUsers(1)
}

const getRoleBadgeClass = (roleName) => {
  const lowerRole = roleName.toLowerCase()
  const classes = {
    'superadmin': 'badge-danger',
    'admin': 'badge-warning',
    'user': 'badge-primary'
  }
  return classes[lowerRole] || 'badge-secondary'
}

const truncate = (text, length) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

const toggleMenu = (userId) => {
  activeMenu.value = activeMenu.value === userId ? null : userId
}

const closeMenus = () => {
  activeMenu.value = null
}

const handleToggleBan = async (user) => {
  togglingBan[user.id] = true
  closeMenus()

  try {
    const result = await toggleBan(user.id)
    const userIndex = users.value.data.findIndex(u => u.id === user.id)
    if (userIndex !== -1) {
      users.value.data[userIndex].is_active = result.status
    }
  } catch (error) {
    alert('Error toggling ban status: ' + (error.message || 'Unknown error'))
  } finally {
    delete togglingBan[user.id]
  }
}

const handleDelete = async (user) => {
  closeMenus()

  if (!confirm(`Are you sure you want to delete user "${user.user_name}"? This action cannot be undone.`)) {
    return
  }

  try {
    await deleteUser(user.id)
    await loadUsers(users.value.current_page)
  } catch (error) {
    alert('Error deleting user: ' + (error.message || 'Unknown error'))
  }
}
</script>

<style scoped>
.users-management-advanced {
  padding: 0;
  background: #f5f7fa;
  min-height: 100vh;
}

/* Advanced Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.stat-card-advanced {
  background: white;
  border-radius: 16px;
  padding: 1.75rem;
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card-advanced::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--stat-color-start), var(--stat-color-end));
}

.stat-card-advanced.stat-info {
  --stat-color-start: #17a2b8;
  --stat-color-end: #138496;
}

.stat-card-advanced.stat-success {
  --stat-color-start: #28a745;
  --stat-color-end: #1e7e34;
}

.stat-card-advanced.stat-danger {
  --stat-color-start: #dc3545;
  --stat-color-end: #bd2130;
}

.stat-card-advanced.stat-warning {
  --stat-color-start: #ffc107;
  --stat-color-end: #e0a800;
}

.stat-card-advanced:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.stat-icon-wrapper {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.stat-icon {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.75rem;
  background: linear-gradient(135deg, var(--stat-color-start), var(--stat-color-end));
  color: white;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.stat-badge {
  background: #e8f5e9;
  color: #2e7d32;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}

.stat-number {
  font-size: 2.25rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0 0 0.25rem 0;
}

.stat-label {
  color: #6c757d;
  font-size: 0.95rem;
  margin: 0;
  font-weight: 500;
}

/* Search & Filter Bar */
.search-filter-bar {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 300px;
  position: relative;
}

.search-icon {
  position: absolute;
  left: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
  font-size: 1.1rem;
}

.search-input {
  width: 100%;
  padding: 0.875rem 3rem 0.875rem 3.5rem;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.search-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.clear-search {
  position: absolute;
  right: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
  cursor: pointer;
  padding: 0.25rem;
}

.filter-controls {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.filter-group {
  display: flex;
  gap: 0.5rem;
  background: #f8f9fa;
  padding: 0.375rem;
  border-radius: 12px;
}

.filter-btn {
  padding: 0.625rem 1.25rem;
  border: none;
  background: transparent;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  color: #6c757d;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-btn:hover {
  background: white;
  color: #495057;
}

.filter-btn.active {
  background: white;
  color: #667eea;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.role-select {
  padding: 0.625rem 1.25rem;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.role-select:focus {
  outline: none;
  border-color: #667eea;
}

.action-btn {
  padding: 0.625rem 1.5rem;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
}

.action-btn.primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.action-btn.success {
  background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
  color: white;
}

.action-btn.danger {
  background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
  color: white;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* View Controls */
.view-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.view-toggle {
  display: flex;
  gap: 0.5rem;
  background: white;
  padding: 0.375rem;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.07);
}

.toggle-btn {
  padding: 0.625rem 1.25rem;
  border: none;
  background: transparent;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  color: #6c757d;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.toggle-btn.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.results-info {
  color: #6c757d;
  font-weight: 500;
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 16px;
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

/* Users Grid */
.users-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.user-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  display: flex;
  flex-direction: column;
  min-height: 420px;
}

.user-card.banned {
  opacity: 0.7;
  border: 2px solid #dc3545;
}

.user-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.card-header-custom {
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.user-avatar-wrapper {
  position: relative;
}

.user-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  border: 4px solid white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  object-fit: cover;
}

.status-indicator {
  position: absolute;
  bottom: 5px;
  right: 5px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 3px solid white;
}

.status-indicator.active {
  background: #28a745;
  animation: pulse 2s infinite;
}

.status-indicator.banned {
  background: #dc3545;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.card-menu {
  position: relative;
}

.menu-btn {
  background: white;
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
}

.menu-btn:hover {
  background: #f8f9fa;
  transform: scale(1.1);
}

.dropdown-menu {
  position: absolute;
  right: 0;
  top: 45px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  min-width: 200px;
  z-index: 100;
  overflow: hidden;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.25rem;
  border: none;
  background: transparent;
  width: 100%;
  text-align: left;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #495057;
  text-decoration: none;
  font-weight: 500;
}

.menu-item:hover {
  background: #f8f9fa;
}

.menu-item.danger {
  color: #dc3545;
}

.menu-item.danger:hover {
  background: #fee;
}

.card-body-custom {
  padding: 1.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0 0 0.25rem 0;
  color: #2c3e50;
}

.user-id {
  color: #6c757d;
  font-size: 0.875rem;
  margin: 0 0 1rem 0;
}

.user-contact {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #6c757d;
  font-size: 0.875rem;
}

.contact-item i {
  width: 16px;
  color: #adb5bd;
}

.user-roles {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}

.role-badge {
  padding: 0.375rem 0.875rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.role-badge.superadmin {
  background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
  color: white;
}

.role-badge.admin {
  background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
  color: #212529;
}

.role-badge.user {
  background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
  color: white;
}

.role-badge.default {
  background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
  color: white;
}

.spacer {
  flex: 1;
}

.user-stats {
  display: flex;
  gap: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e9ecef;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #6c757d;
  font-size: 0.875rem;
  font-weight: 500;
}

.stat-item i {
  color: #667eea;
}

.stat-item.warning i {
  color: #ffc107;
}

.card-footer-custom {
  padding: 1rem 1.5rem;
  background: #f8f9fa;
  display: flex;
  gap: 0.75rem;
  border-top: 1px solid #e9ecef;
}

.card-footer-custom .action-btn {
  flex: 1;
  justify-content: center;
  padding: 0.75rem 1rem;
  font-size: 0.875rem;
}

/* List View */
.users-list-view {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.user-list-item {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
  transition: all 0.3s ease;
}

.user-list-item:hover {
  transform: translateX(8px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.user-list-item.banned {
  border-left: 4px solid #dc3545;
}

.list-item-avatar {
  position: relative;
}

.list-item-avatar img {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  object-fit: cover;
}

.list-item-info {
  flex: 0 0 200px;
}

.list-item-info h4 {
  margin: 0 0 0.25rem 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c3e50;
}

.list-item-contact {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #6c757d;
}

.list-item-contact i {
  margin-right: 0.5rem;
  color: #adb5bd;
}

.list-item-roles {
  flex: 0 0 auto;
  display: flex;
  gap: 0.5rem;
}

.list-item-stats {
  flex: 0 0 auto;
  display: flex;
  gap: 0.75rem;
}

.stat-pill {
  background: #e9ecef;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.stat-pill.warning {
  background: #fff3cd;
  color: #856404;
}

.list-item-actions {
  flex: 0 0 auto;
  display: flex;
  gap: 0.5rem;
}

.icon-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: none;
  background: #f8f9fa;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  text-decoration: none;
  color: #495057;
}

.icon-btn:hover {
  background: #e9ecef;
  transform: scale(1.1);
}

.icon-btn.danger {
  color: #dc3545;
}

.icon-btn.success {
  color: #28a745;
}

/* Empty State */
.empty-state-advanced {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 16px;
  grid-column: 1 / -1;
}

.empty-icon {
  width: 120px;
  height: 120px;
  margin: 0 auto 2rem;
  border-radius: 50%;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  color: #adb5bd;
}

.empty-state-advanced h3 {
  font-size: 1.5rem;
  margin: 0 0 0.5rem 0;
  color: #2c3e50;
}

.empty-state-advanced p {
  color: #6c757d;
  margin: 0 0 2rem 0;
}

/* Pagination */
.pagination-advanced {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
  padding: 1.5rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
}

.page-btn {
  padding: 0.75rem 1.5rem;
  border: 2px solid #e9ecef;
  background: white;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #495057;
}

.page-btn:hover:not(:disabled) {
  background: #667eea;
  color: white;
  border-color: #667eea;
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  gap: 0.5rem;
}

.page-number {
  width: 44px;
  height: 44px;
  border: 2px solid #e9ecef;
  background: white;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s ease;
  color: #495057;
}

.page-number.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-color: #667eea;
}

.page-number:hover:not(.active) {
  border-color: #667eea;
  color: #667eea;
}

/* Responsive */
@media (max-width: 768px) {
  .search-filter-bar {
    flex-direction: column;
  }

  .search-box {
    min-width: 100%;
  }

  .filter-controls {
    flex-wrap: wrap;
    width: 100%;
  }

  .users-grid {
    grid-template-columns: 1fr;
  }

  .user-list-item {
    flex-wrap: wrap;
  }

  .list-item-info,
  .list-item-contact,
  .list-item-stats {
    flex: 1 1 100%;
  }
}
</style>
