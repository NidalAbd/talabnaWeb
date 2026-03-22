<template>
  <div class="users-modern">
    <!-- Stats Cards -->
    <div class="stats-dashboard">
      <div class="stats-grid">
        <div class="stat-card-compact stat-blue">
          <div class="stat-icon"><i class="fas fa-users"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(users.total) }}</div>
            <div class="stat-label-compact">Total Users</div>
          </div>
        </div>
        <div class="stat-card-compact stat-green">
          <div class="stat-icon"><i class="fas fa-user-check"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(activeUsersCount) }}</div>
            <div class="stat-label-compact">Active Users</div>
          </div>
        </div>
        <div class="stat-card-compact stat-orange">
          <div class="stat-icon"><i class="fas fa-user-slash"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(bannedUsersCount) }}</div>
            <div class="stat-label-compact">Banned Users</div>
          </div>
        </div>
        <div class="stat-card-compact stat-purple">
          <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(adminUsersCount) }}</div>
            <div class="stat-label-compact">Admin Users</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="search-filter-bar">
      <div class="search-box">
        <i class="fas fa-search search-icon"></i>
        <input
          type="text"
          class="search-input"
          placeholder="Search users by name, email, or phone..."
          v-model="filters.search"
        >
        <span v-if="filters.search" class="clear-search" @click="filters.search = ''">
          <i class="fas fa-times"></i>
        </span>
      </div>
      <div class="filter-group">
        <div class="status-filters">
          <button
            class="filter-btn"
            :class="{ active: filters.status === '' }"
            @click="filters.status = ''"
          >
            <i class="fas fa-users"></i> All
          </button>
          <button
            class="filter-btn"
            :class="{ active: filters.status === 'active' }"
            @click="filters.status = 'active'"
          >
            <i class="fas fa-check-circle"></i> Active
          </button>
          <button
            class="filter-btn"
            :class="{ active: filters.status === 'banned' }"
            @click="filters.status = 'banned'"
          >
            <i class="fas fa-ban"></i> Banned
          </button>
        </div>
        <select class="filter-select" v-model="filters.role">
          <option value="">All Roles</option>
          <option v-for="role in roles" :key="role.id" :value="role.name">
            {{ role.display_name || role.name }}
          </option>
        </select>
      </div>
      <div class="action-buttons">
        <button class="action-btn secondary" @click="resetFilters">
          <i class="fas fa-redo"></i>
        </button>
        <a href="/users/create" class="action-btn primary">
          <i class="fas fa-plus"></i> Add User
        </a>
      </div>
    </div>

    <!-- View Toggle & Info -->
    <div class="view-controls mb-4">
      <div class="view-toggle">
        <button
          class="toggle-btn"
          :class="{ active: viewMode === 'grid' }"
          @click="viewMode = 'grid'"
        >
          <i class="fas fa-th"></i> Grid
        </button>
        <button
          class="toggle-btn"
          :class="{ active: viewMode === 'list' }"
          @click="viewMode = 'list'"
        >
          <i class="fas fa-list"></i> List
        </button>
      </div>
      <div class="results-info">
        Showing {{ users.data.length }} of {{ users.total }} users
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading users...</p>
    </div>

    <!-- Grid View -->
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
            <span class="status-dot" :class="user.is_active"></span>
          </div>
          <div class="card-menu">
            <button class="menu-btn" @click.stop="toggleMenu(user.id)">
              <i class="fas fa-ellipsis-v"></i>
            </button>
            <div v-if="activeMenu === user.id" class="dropdown-menu" @click.stop>
              <a :href="`/users/${user.id}`" class="menu-item">
                <i class="fas fa-eye"></i> View Details
              </a>
              <button @click="openEditModal(user)" class="menu-item">
                <i class="fas fa-edit"></i> Edit User
              </button>
              <button @click="handleToggleBan(user)" class="menu-item">
                <i :class="user.is_active === 'active' ? 'fas fa-ban' : 'fas fa-check'"></i>
                {{ user.is_active === 'active' ? 'Ban User' : 'Unban User' }}
              </button>
              <button @click="handleDelete(user)" class="menu-item danger">
                <i class="fas fa-trash"></i> Delete
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
            <div class="stat-item" v-if="user.viewed_posts_count > 0">
              <i class="fas fa-eye"></i>
              <span>{{ user.viewed_posts_count }} Views</span>
            </div>
            <div class="stat-item warning" v-if="user.reports_count > 0">
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
          <a :href="`/users/${user.id}`" class="action-btn info">
            <i class="fas fa-arrow-right"></i> View
          </a>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="users.data.length === 0" class="empty-state-grid">
        <i class="fas fa-users"></i>
        <h3>No Users Found</h3>
        <p>Try adjusting your filters or search criteria</p>
        <button @click="resetFilters" class="action-btn primary">
          <i class="fas fa-redo"></i> Reset Filters
        </button>
      </div>
    </div>

    <!-- Table View -->
    <div v-else class="data-table-container">
      <table class="modern-table">
        <thead>
          <tr>
            <th style="width: 60px;"></th>
            <th>User</th>
            <th>Contact</th>
            <th>Roles</th>
            <th>Status</th>
            <th>Stats</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody v-if="users.data.length > 0">
          <tr v-for="user in users.data" :key="user.id" :class="{ 'row-banned': user.is_active === 'banned' }">
            <td>
              <div class="avatar-cell">
                <img :src="user.avatar_url" :alt="user.user_name" class="table-avatar">
                <span class="status-dot" :class="user.is_active"></span>
              </div>
            </td>
            <td>
              <div class="user-cell">
                <strong>{{ user.user_name }}</strong>
                <span class="user-meta">#{{ user.id }} &bull; {{ user.name }}</span>
              </div>
            </td>
            <td>
              <div class="contact-cell">
                <span v-if="user.email"><i class="fas fa-envelope"></i> {{ truncate(user.email, 25) }}</span>
                <span v-if="user.phones"><i class="fas fa-phone"></i> {{ user.phones }}</span>
              </div>
            </td>
            <td>
              <div class="roles-cell">
                <span
                  v-for="role in user.roles"
                  :key="role"
                  class="badge"
                  :class="getRoleBadgeClass(role)"
                >
                  {{ role }}
                </span>
              </div>
            </td>
            <td>
              <span class="badge" :class="user.is_active === 'active' ? 'success' : 'danger'">
                <i :class="user.is_active === 'active' ? 'fas fa-check-circle' : 'fas fa-ban'"></i>
                {{ user.is_active === 'active' ? 'Active' : 'Banned' }}
              </span>
            </td>
            <td>
              <div class="stats-cell">
                <span class="badge info"><i class="fas fa-box"></i> {{ user.service_posts_count }}</span>
                <span v-if="user.viewed_posts_count > 0" class="badge secondary">
                  <i class="fas fa-eye"></i> {{ user.viewed_posts_count }}
                </span>
                <span v-if="user.reports_count > 0" class="badge warning">
                  <i class="fas fa-flag"></i> {{ user.reports_count }}
                </span>
              </div>
            </td>
            <td>
              <div class="table-actions">
                <a :href="`/users/${user.id}`" class="action-btn-small view" title="View">
                  <i class="fas fa-eye"></i>
                </a>
                <button @click="openEditModal(user)" class="action-btn-small edit" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button
                  @click="handleToggleBan(user)"
                  class="action-btn-small"
                  :class="user.is_active === 'active' ? 'ban' : 'unban'"
                  :title="user.is_active === 'active' ? 'Ban User' : 'Unban User'"
                  :disabled="togglingBan[user.id]"
                >
                  <i v-if="togglingBan[user.id]" class="fas fa-spinner fa-spin"></i>
                  <i v-else :class="user.is_active === 'active' ? 'fas fa-ban' : 'fas fa-check'"></i>
                </button>
                <button
                  @click="handleDelete(user)"
                  class="action-btn-small delete"
                  title="Delete"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="7" class="empty-state">
              <i class="fas fa-users"></i>
              <p>No users found</p>
              <button @click="resetFilters" class="action-btn primary">
                <i class="fas fa-redo"></i> Reset Filters
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-container" v-if="users.last_page > 1">
      <div class="pagination-info">
        Showing {{ users.data.length }} of {{ users.total }} users
      </div>
      <div class="pagination-controls">
        <button
          class="pagination-btn"
          :disabled="users.current_page === 1"
          @click="loadUsers(users.current_page - 1)"
        >
          <i class="fas fa-chevron-left"></i> Previous
        </button>
        <button
          v-for="page in visiblePages"
          :key="page"
          class="pagination-btn"
          :class="{ active: page === users.current_page }"
          @click="loadUsers(page)"
        >
          {{ page }}
        </button>
        <button
          class="pagination-btn"
          :disabled="users.current_page === users.last_page"
          @click="loadUsers(users.current_page + 1)"
        >
          Next <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal-overlay" v-if="showDeleteModal" @click="showDeleteModal = false">
      <div class="modern-modal" @click.stop>
        <div class="modal-header">
          <h3><i class="fas fa-exclamation-triangle text-danger"></i> Delete User</h3>
          <button class="close-btn" @click="showDeleteModal = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete the user <strong>{{ userToDelete?.user_name }}</strong>?</p>
          <p class="text-muted">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button class="action-btn secondary" @click="showDeleteModal = false">
            <i class="fas fa-times"></i> Cancel
          </button>
          <button class="action-btn danger" @click="confirmDelete" :disabled="isDeleting">
            <i v-if="isDeleting" class="fas fa-spinner fa-spin"></i>
            <i v-else class="fas fa-trash"></i> Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Edit User Modal -->
    <UserEditModal
      v-if="showEditModal"
      :userId="selectedUserId"
      @close="showEditModal = false"
      @saved="handleUserSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch, reactive, computed, onBeforeUnmount } from 'vue'
import { useUsers } from '../../composables/useUsers'
import UserEditModal from '../../components/admin/users/UserEditModal.vue'

const { users, loading, fetchUsers, toggleBan, deleteUser } = useUsers()

const formatNumber = (value) => {
  if (value === null || value === undefined) return '0'
  return new Intl.NumberFormat().format(value)
}

const roles = ref([])
const togglingBan = reactive({})
const viewMode = ref('list')
const activeMenu = ref(null)
const showDeleteModal = ref(false)
const userToDelete = ref(null)
const isDeleting = ref(false)

// Edit modal state
const showEditModal = ref(false)
const selectedUserId = ref(null)

const filters = reactive({
  status: '',
  role: '',
  search: ''
})

const activeUsersCount = computed(() => {
  return users.value.data.filter(u => u.is_active === 'active').length
})

const bannedUsersCount = computed(() => {
  return users.value.data.filter(u => u.is_active === 'banned').length
})

const adminUsersCount = computed(() => {
  return users.value.data.filter(u =>
    u.roles && u.roles.some(r => ['admin', 'superadmin'].includes(r.toLowerCase()))
  ).length
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
    'superadmin': 'danger',
    'admin': 'warning',
    'user': 'primary'
  }
  return classes[lowerRole] || 'secondary'
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

const handleDelete = (user) => {
  closeMenus()
  userToDelete.value = user
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!userToDelete.value) return

  isDeleting.value = true

  try {
    await deleteUser(userToDelete.value.id)
    showDeleteModal.value = false
    userToDelete.value = null
    await loadUsers(users.value.current_page)
  } catch (error) {
    alert('Error deleting user: ' + (error.message || 'Unknown error'))
  } finally {
    isDeleting.value = false
  }
}

// Edit modal handlers
const openEditModal = (user) => {
  closeMenus()
  selectedUserId.value = user.id
  showEditModal.value = true
}

const handleUserSaved = () => {
  showEditModal.value = false
  selectedUserId.value = null
  loadUsers(users.value.current_page)
}
</script>

<style scoped>
.users-modern {
  padding: 0;
}

/* Search & Filters */
.search-filter-bar {
  background: white;
  padding: 1.25rem;
  border-radius: 16px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
  margin-bottom: 1.5rem;
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  align-items: center;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 280px;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #999;
}

.search-input {
  width: 100%;
  padding: 0.875rem 2.5rem 0.875rem 2.75rem;
  border: 2px solid #eef2f7;
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
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #999;
  cursor: pointer;
  transition: color 0.2s;
}

.clear-search:hover {
  color: #333;
}

.filter-group {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.status-filters {
  display: flex;
  gap: 0.25rem;
  background: #f8f9fa;
  padding: 0.25rem;
  border-radius: 10px;
}

.filter-btn {
  padding: 0.6rem 1rem;
  border: none;
  background: transparent;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  color: #6c757d;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.85rem;
}

.filter-btn:hover {
  background: white;
  color: #495057;
}

.filter-btn.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.filter-select {
  padding: 0.875rem 1rem;
  border: 2px solid #eef2f7;
  border-radius: 12px;
  font-size: 0.9rem;
  background: white;
  cursor: pointer;
  transition: all 0.3s ease;
}

.filter-select:focus {
  outline: none;
  border-color: #667eea;
}

.action-buttons {
  display: flex;
  gap: 0.75rem;
}

.action-btn {
  padding: 0.75rem 1.25rem;
  border: none;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
}

.action-btn.primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.action-btn.info {
  background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
  color: white;
}

.action-btn.secondary {
  background: #6c757d;
  color: white;
}

.action-btn.success {
  background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
  color: white;
}

.action-btn.danger {
  background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
  color: white;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.action-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
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
  gap: 0.25rem;
  background: white;
  padding: 0.25rem;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.toggle-btn {
  padding: 0.6rem 1rem;
  border: none;
  background: transparent;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  color: #6c757d;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.85rem;
}

.toggle-btn.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.results-info {
  color: #666;
  font-size: 0.9rem;
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
}

.spinner {
  width: 50px;
  height: 50px;
  margin: 0 auto 1rem;
  border: 4px solid #f3f4f6;
  border-top: 4px solid #667eea;
  border-radius: 50%;
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
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  min-height: 380px;
}

.user-card.banned {
  border: 2px solid #ff4b2b;
  opacity: 0.85;
}

.user-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
}

.card-header-custom {
  padding: 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  background: linear-gradient(135deg, #f8f9ff 0%, #eef2f7 100%);
}

.user-avatar-wrapper {
  position: relative;
}

.user-avatar {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  border: 4px solid white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  object-fit: cover;
}

.status-dot {
  position: absolute;
  bottom: 4px;
  right: 4px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 3px solid white;
}

.status-dot.active {
  background: #28a745;
  animation: pulse 2s infinite;
}

.status-dot.banned {
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
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
}

.menu-btn:hover {
  background: #f8f9fa;
  transform: scale(1.1);
}

.dropdown-menu {
  position: absolute;
  right: 0;
  top: 42px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  min-width: 180px;
  z-index: 100;
  overflow: hidden;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border: none;
  background: transparent;
  width: 100%;
  text-align: left;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #495057;
  text-decoration: none;
  font-size: 0.9rem;
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
  padding: 1.25rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0 0 0.25rem 0;
  color: #2c3e50;
}

.user-id {
  color: #888;
  font-size: 0.8rem;
  margin: 0 0 0.75rem 0;
}

.user-contact {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  margin-bottom: 0.75rem;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #666;
  font-size: 0.85rem;
}

.contact-item i {
  width: 14px;
  color: #999;
}

.user-roles {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
  margin-bottom: 0.75rem;
}

.role-badge {
  padding: 0.3rem 0.7rem;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.role-badge.danger {
  background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
  color: white;
}

.role-badge.warning {
  background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
  color: #333;
}

.role-badge.primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.role-badge.secondary {
  background: #e9ecef;
  color: #495057;
}

.spacer {
  flex: 1;
}

.user-stats {
  display: flex;
  gap: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #eef2f7;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  color: #666;
  font-size: 0.8rem;
}

.stat-item i {
  color: #667eea;
}

.stat-item.warning i {
  color: #f7971e;
}

.card-footer-custom {
  padding: 1rem 1.25rem;
  background: #f8f9ff;
  display: flex;
  gap: 0.75rem;
  border-top: 1px solid #eef2f7;
}

.card-footer-custom .action-btn {
  flex: 1;
  justify-content: center;
  padding: 0.65rem 1rem;
  font-size: 0.85rem;
}

/* Empty State Grid */
.empty-state-grid {
  grid-column: 1 / -1;
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 16px;
}

.empty-state-grid i {
  font-size: 4rem;
  color: #ddd;
  margin-bottom: 1rem;
}

/* Data Table */
.data-table-container {
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.modern-table {
  width: 100%;
  border-collapse: collapse;
}

.modern-table thead {
  background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
}

.modern-table th {
  padding: 1.125rem 1rem;
  text-align: left;
  font-weight: 600;
  color: white;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.modern-table tbody tr {
  border-bottom: 1px solid #f0f4f8;
  transition: all 0.2s ease;
}

.modern-table tbody tr:hover {
  background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
}

.modern-table tbody tr.row-banned {
  background: #fff5f5;
}

.modern-table td {
  padding: 1rem;
  font-size: 0.9rem;
  color: #333;
  vertical-align: middle;
}

.empty-state {
  text-align: center;
  padding: 4rem !important;
  color: #999;
}

.empty-state i {
  font-size: 3.5rem;
  margin-bottom: 1rem;
  display: block;
  opacity: 0.3;
}

.empty-state p {
  margin-bottom: 1.5rem;
}

/* Table Cells */
.avatar-cell {
  position: relative;
  display: inline-block;
}

.table-avatar {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  object-fit: cover;
}

.avatar-cell .status-dot {
  bottom: 2px;
  right: 2px;
  width: 12px;
  height: 12px;
  border-width: 2px;
}

.user-cell {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.user-meta {
  font-size: 0.8rem;
  color: #888;
}

.contact-cell {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
  font-size: 0.85rem;
  color: #666;
}

.contact-cell i {
  color: #999;
  margin-right: 0.4rem;
  width: 14px;
}

.roles-cell {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
}

.stats-cell {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
}

/* Badges */
.badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.4rem 0.85rem;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge.primary { background: #e3f2fd; color: #1976d2; }
.badge.info { background: #e0f7fa; color: #00838f; }
.badge.success { background: #e8f5e9; color: #2e7d32; }
.badge.warning { background: #fff3e0; color: #ef6c00; }
.badge.danger { background: #ffebee; color: #c62828; }
.badge.secondary { background: #f5f5f5; color: #616161; }

/* Table Actions */
.table-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn-small {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  font-size: 0.9rem;
}

.action-btn-small.view {
  background: #e3f2fd;
  color: #1976d2;
}

.action-btn-small.edit {
  background: #fff3e0;
  color: #ef6c00;
}

.action-btn-small.ban {
  background: #ffebee;
  color: #c62828;
}

.action-btn-small.unban {
  background: #e8f5e9;
  color: #2e7d32;
}

.action-btn-small.delete {
  background: #ffebee;
  color: #c62828;
}

.action-btn-small:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Pagination */
.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1.5rem;
  padding: 1.25rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
  flex-wrap: wrap;
  gap: 1rem;
}

.pagination-info {
  color: #666;
  font-size: 0.9rem;
}

.pagination-controls {
  display: flex;
  gap: 0.5rem;
}

.pagination-btn {
  padding: 0.6rem 1rem;
  border: 2px solid #eef2f7;
  background: white;
  border-radius: 10px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.pagination-btn:hover:not(:disabled) {
  border-color: #667eea;
  color: #667eea;
}

.pagination-btn.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-color: transparent;
  color: white;
}

.pagination-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 1rem;
  backdrop-filter: blur(4px);
}

.modern-modal {
  background: white;
  border-radius: 20px;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
  max-width: 500px;
  width: 100%;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #eef2f7;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  color: #999;
  cursor: pointer;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: #f8f9fa;
  color: #333;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem;
  border-top: 1px solid #eef2f7;
}

.text-danger { color: #dc3545; }
.text-muted { color: #888; font-size: 0.9rem; }

/* Responsive */
@media (max-width: 768px) {
  .search-filter-bar {
    flex-direction: column;
  }

  .search-box, .filter-group, .action-buttons {
    width: 100%;
  }

  .filter-group {
    flex-direction: column;
  }

  .status-filters {
    width: 100%;
  }

  .action-buttons {
    justify-content: stretch;
  }

  .action-btn {
    flex: 1;
    justify-content: center;
  }

  .users-grid {
    grid-template-columns: 1fr;
  }

  .data-table-container {
    overflow-x: auto;
  }

  .modern-table {
    min-width: 900px;
  }

  .pagination-container {
    flex-direction: column;
  }
}
</style>
