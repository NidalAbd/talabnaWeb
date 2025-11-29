<template>
  <div class="roles-management-advanced">
    <!-- Advanced Search & Filters -->
    <div class="search-filter-bar">
      <div class="search-box">
        <i class="fas fa-search search-icon"></i>
        <input
          type="text"
          v-model="filters.search"
          class="search-input"
          placeholder="Search roles by name or description..."
        >
        <span v-if="filters.search" class="clear-search" @click="filters.search = ''">
          <i class="fas fa-times"></i>
        </span>
      </div>

      <div class="filter-controls">
        <select v-model="filters.sort_by" class="sort-select">
          <option value="id">Sort by ID</option>
          <option value="name">Sort by Name</option>
          <option value="created_at">Sort by Date</option>
          <option value="permissions_count">Sort by Permissions</option>
        </select>

        <div class="sort-direction-toggle">
          <button
            class="direction-btn"
            :class="{ active: filters.sort_direction === 'asc' }"
            @click="filters.sort_direction = 'asc'"
            title="Ascending"
          >
            <i class="fas fa-arrow-up"></i>
          </button>
          <button
            class="direction-btn"
            :class="{ active: filters.sort_direction === 'desc' }"
            @click="filters.sort_direction = 'desc'"
            title="Descending"
          >
            <i class="fas fa-arrow-down"></i>
          </button>
        </div>

        <button class="action-btn primary" @click="resetFilters">
          <i class="fas fa-redo"></i>
        </button>
      </div>
    </div>

    <!-- View Controls -->
    <div class="view-controls mb-4">
      <div class="view-info">
        <h3 class="section-title">
          <i class="fas fa-user-tag me-2"></i>
          Roles Management
        </h3>
        <p class="section-subtitle">Manage user roles and permissions</p>
      </div>

      <div class="action-buttons">
        <a href="/permissions" class="action-btn success">
          <i class="fas fa-key"></i>
          Manage Permissions
        </a>
        <a href="/roles/create" class="action-btn primary">
          <i class="fas fa-plus-circle"></i>
          Create Role
        </a>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="loader-advanced"></div>
      <p>Loading roles...</p>
    </div>

    <!-- Roles Grid -->
    <div v-else class="roles-grid">
      <div
        v-for="role in roles.data"
        :key="role.id"
        class="role-card"
        :class="getRoleCardClass(role.name)"
      >
        <div class="role-card-header">
          <div class="role-icon-large" :class="getRoleIconClass(role.name)">
            <i :class="getRoleIcon(role.name)"></i>
          </div>
          <div class="role-menu">
            <button class="menu-btn" @click="toggleMenu(role.id)">
              <i class="fas fa-ellipsis-v"></i>
            </button>
            <div v-if="activeMenu === role.id" class="dropdown-menu">
              <a :href="`/roles/${role.id}`" class="menu-item">
                <i class="fas fa-eye"></i>
                View Details
              </a>
              <a :href="`/role-assignments/users-with-role/${role.id}`" class="menu-item">
                <i class="fas fa-users"></i>
                View Users
              </a>
              <a v-if="role.is_editable" :href="`/roles/${role.id}/edit`" class="menu-item">
                <i class="fas fa-edit"></i>
                Edit Role
              </a>
              <button v-if="role.is_deletable" @click="handleDelete(role)" class="menu-item danger">
                <i class="fas fa-trash"></i>
                Delete
              </button>
            </div>
          </div>
        </div>

        <div class="role-card-body">
          <div class="role-header-info">
            <h3 class="role-name">{{ role.display_name }}</h3>
            <span class="role-id">#{{ role.id }}</span>
          </div>

          <div class="role-badge-container">
            <span class="role-badge" :class="getRoleBadgeClass(role.name)">
              {{ role.name }}
            </span>
            <span v-if="!role.is_editable || !role.is_deletable" class="protected-badge">
              <i class="fas fa-lock"></i>
              Protected
            </span>
          </div>

          <p class="role-description">{{ truncate(role.description, 100) || 'No description available' }}</p>

          <div class="role-stats">
            <div class="stat-box">
              <i class="fas fa-key"></i>
              <div class="stat-info">
                <span class="stat-value">{{ role.permissions_count }}</span>
                <span class="stat-label">Permissions</span>
              </div>
            </div>
          </div>
        </div>

        <div class="role-card-footer">
          <a :href="`/roles/${role.id}`" class="action-btn outline-primary flex-1">
            <i class="fas fa-eye"></i>
            View
          </a>
          <a
            v-if="role.is_editable"
            :href="`/roles/${role.id}/edit`"
            class="action-btn outline-warning flex-1"
          >
            <i class="fas fa-edit"></i>
            Edit
          </a>
          <button
            v-if="role.is_deletable"
            @click="handleDelete(role)"
            class="action-btn outline-danger flex-1"
          >
            <i class="fas fa-trash"></i>
            Delete
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="roles.data.length === 0" class="empty-state-advanced">
        <div class="empty-icon">
          <i class="fas fa-user-tag"></i>
        </div>
        <h3>No Roles Found</h3>
        <p>Try adjusting your search criteria or create a new role</p>
        <a href="/roles/create" class="action-btn primary">
          <i class="fas fa-plus-circle"></i>
          Create First Role
        </a>
      </div>
    </div>

    <!-- Advanced Pagination -->
    <div v-if="roles.data.length > 0" class="pagination-advanced">
      <button
        class="page-btn"
        :disabled="roles.current_page === 1"
        @click="loadRoles(roles.current_page - 1)"
      >
        <i class="fas fa-chevron-left"></i>
        Previous
      </button>

      <div class="page-numbers">
        <button
          v-for="page in visiblePages"
          :key="page"
          class="page-number"
          :class="{ active: page === roles.current_page }"
          @click="loadRoles(page)"
        >
          {{ page }}
        </button>
      </div>

      <button
        class="page-btn"
        :disabled="roles.current_page === roles.last_page"
        @click="loadRoles(roles.current_page + 1)"
      >
        Next
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, reactive, computed, onBeforeUnmount } from 'vue'
import { useRoles } from '../../composables/useRoles'

const { roles, loading, fetchRoles, deleteRole } = useRoles()

const activeMenu = ref(null)

const filters = reactive({
  search: '',
  sort_by: 'id',
  sort_direction: 'desc'
})

const visiblePages = computed(() => {
  const pages = []
  const current = roles.value.current_page
  const last = roles.value.last_page

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
  searchTimeout = setTimeout(() => loadRoles(1), 300)
})

watch(() => [filters.sort_by, filters.sort_direction], () => {
  loadRoles(1)
})

const loadData = async () => {
  await loadRoles()
}

const loadRoles = async (page = 1) => {
  await fetchRoles({ ...filters, page })
}

const resetFilters = () => {
  filters.search = ''
  filters.sort_by = 'id'
  filters.sort_direction = 'desc'
  loadRoles(1)
}

const getRoleBadgeClass = (name) => {
  const classes = {
    'superadmin': 'superadmin',
    'admin': 'admin',
    'user': 'user'
  }
  return classes[name] || 'default'
}

const getRoleIcon = (name) => {
  const icons = {
    'superadmin': 'fas fa-crown',
    'admin': 'fas fa-user-shield',
    'user': 'fas fa-user'
  }
  return icons[name] || 'fas fa-user-tag'
}

const getRoleIconClass = (name) => {
  const classes = {
    'superadmin': 'icon-danger',
    'admin': 'icon-warning',
    'user': 'icon-primary'
  }
  return classes[name] || 'icon-info'
}

const getRoleCardClass = (name) => {
  const classes = {
    'superadmin': 'card-danger',
    'admin': 'card-warning',
    'user': 'card-primary'
  }
  return classes[name] || 'card-info'
}

const truncate = (text, length) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

const toggleMenu = (roleId) => {
  activeMenu.value = activeMenu.value === roleId ? null : roleId
}

const closeMenus = () => {
  activeMenu.value = null
}

const handleDelete = async (role) => {
  closeMenus()

  if (!confirm(`Are you sure you want to delete the role "${role.display_name}"? This action cannot be undone.`)) {
    return
  }

  try {
    await deleteRole(role.id)
    await loadRoles(roles.value.current_page)
  } catch (error) {
    alert(error.message || 'Failed to delete role')
  }
}
</script>

<style scoped>
.roles-management-advanced {
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

.sort-select {
  padding: 0.625rem 1.25rem;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.sort-select:focus {
  outline: none;
  border-color: #667eea;
}

.sort-direction-toggle {
  display: flex;
  gap: 0.5rem;
  background: #f8f9fa;
  padding: 0.375rem;
  border-radius: 12px;
}

.direction-btn {
  padding: 0.625rem 1rem;
  border: none;
  background: transparent;
  border-radius: 8px;
  cursor: pointer;
  color: #6c757d;
  transition: all 0.2s ease;
}

.direction-btn:hover {
  background: white;
}

.direction-btn.active {
  background: white;
  color: #667eea;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
  justify-content: center;
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

.action-btn.outline-primary {
  background: transparent;
  border: 2px solid #667eea;
  color: #667eea;
}

.action-btn.outline-warning {
  background: transparent;
  border: 2px solid #ffc107;
  color: #ffc107;
}

.action-btn.outline-danger {
  background: transparent;
  border: 2px solid #dc3545;
  color: #dc3545;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.flex-1 {
  flex: 1;
}

/* View Controls */
.view-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.view-info {
  flex: 1;
}

.section-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0 0 0.5rem 0;
  display: flex;
  align-items: center;
}

.section-subtitle {
  color: #6c757d;
  margin: 0;
  font-size: 1rem;
}

.action-buttons {
  display: flex;
  gap: 1rem;
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

/* Roles Grid */
.roles-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
}

.role-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  border-top: 4px solid #667eea;
}

.role-card.card-danger {
  border-top-color: #dc3545;
}

.role-card.card-warning {
  border-top-color: #ffc107;
}

.role-card.card-primary {
  border-top-color: #007bff;
}

.role-card.card-info {
  border-top-color: #17a2b8;
}

.role-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.role-card-header {
  padding: 1.5rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.role-icon-large {
  width: 80px;
  height: 80px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  color: white;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.icon-danger {
  background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
}

.icon-warning {
  background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
}

.icon-primary {
  background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.icon-info {
  background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}

.role-menu {
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

.role-card-body {
  padding: 1.5rem;
}

.role-header-info {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.role-name {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
  color: #2c3e50;
  flex: 1;
}

.role-id {
  color: #6c757d;
  font-size: 0.875rem;
  font-weight: 600;
}

.role-badge-container {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}

.role-badge {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.8rem;
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

.protected-badge {
  background: #e9ecef;
  color: #495057;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.role-description {
  color: #6c757d;
  font-size: 0.95rem;
  line-height: 1.6;
  margin-bottom: 1.5rem;
  min-height: 3rem;
}

.role-stats {
  display: grid;
  gap: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e9ecef;
}

.stat-box {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 12px;
}

.stat-box i {
  font-size: 1.5rem;
  color: #667eea;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2c3e50;
}

.stat-label {
  color: #6c757d;
  font-size: 0.875rem;
  font-weight: 500;
}

.role-card-footer {
  padding: 1rem 1.5rem;
  background: #f8f9fa;
  display: flex;
  gap: 0.75rem;
  border-top: 1px solid #e9ecef;
}

.role-card-footer .action-btn {
  padding: 0.75rem 1rem;
  font-size: 0.875rem;
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

  .roles-grid {
    grid-template-columns: 1fr;
  }

  .view-controls {
    flex-direction: column;
    align-items: flex-start;
  }

  .action-buttons {
    width: 100%;
    flex-direction: column;
  }

  .action-buttons .action-btn {
    width: 100%;
  }
}
</style>
