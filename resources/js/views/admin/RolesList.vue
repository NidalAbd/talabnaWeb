<template>
  <div class="roles-modern">
    <!-- Stats Cards -->
    <div class="stats-dashboard">
      <div class="stats-grid">
        <div class="stat-card-compact stat-blue">
          <div class="stat-icon"><i class="fas fa-user-tag"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(rolesTotal) }}</div>
            <div class="stat-label-compact">Total Roles</div>
          </div>
        </div>
        <div class="stat-card-compact stat-green">
          <div class="stat-icon"><i class="fas fa-shield-alt"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(systemRolesCount) }}</div>
            <div class="stat-label-compact">System Roles</div>
          </div>
        </div>
        <div class="stat-card-compact stat-orange">
          <div class="stat-icon"><i class="fas fa-user-cog"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(customRolesCount) }}</div>
            <div class="stat-label-compact">Custom Roles</div>
          </div>
        </div>
        <div class="stat-card-compact stat-purple">
          <div class="stat-icon"><i class="fas fa-key"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(totalPermissions) }}</div>
            <div class="stat-label-compact">Total Permissions</div>
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
          placeholder="Search roles by name or description..."
          v-model="filters.search"
        >
      </div>
      <div class="filter-group">
        <select class="filter-select" v-model="filters.sort_by" @change="loadRoles(1)">
          <option value="id">Sort by ID</option>
          <option value="name">Sort by Name</option>
          <option value="created_at">Sort by Date</option>
          <option value="permissions_count">Sort by Permissions</option>
        </select>
        <button
          class="sort-btn"
          @click="toggleSortDirection"
          :title="filters.sort_direction === 'asc' ? 'Ascending' : 'Descending'"
        >
          <i :class="filters.sort_direction === 'asc' ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
        </button>
      </div>
      <div class="action-buttons">
        <router-link to="/admin/permissions" class="action-btn info">
          <i class="fas fa-key"></i> Permissions
        </router-link>
        <button class="action-btn primary" @click="openCreateModal">
          <i class="fas fa-plus"></i> Create Role
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading roles...</p>
    </div>

    <!-- Table -->
    <div v-else class="data-table-container">
      <table class="modern-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Role</th>
            <th>Description</th>
            <th>Permissions</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody v-if="rolesData.length > 0">
          <tr v-for="role in rolesData" :key="role.id">
            <td><span class="id-badge">#{{ role.id }}</span></td>
            <td>
              <div class="role-cell">
                <div class="role-icon-box" :class="getRoleColorClass(role.name)">
                  <i :class="getRoleIcon(role.name)"></i>
                </div>
                <div class="role-info">
                  <strong>{{ role.display_name }}</strong>
                  <span class="role-slug">{{ role.name }}</span>
                </div>
              </div>
            </td>
            <td>
              <span class="description-text">{{ role.description || 'No description' }}</span>
            </td>
            <td>
              <span class="badge info">
                <i class="fas fa-key"></i> {{ role.permissions_count }}
              </span>
            </td>
            <td>
              <span v-if="!role.is_editable || !role.is_deletable" class="badge warning">
                <i class="fas fa-lock"></i> Protected
              </span>
              <span v-else class="badge success">
                <i class="fas fa-check"></i> Editable
              </span>
            </td>
            <td>
              <div class="table-actions">
                <button @click="viewRole(role)" class="action-btn-small view" title="View">
                  <i class="fas fa-eye"></i>
                </button>
                <button v-if="role.is_editable" @click="openEditModal(role)" class="action-btn-small edit" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button v-if="role.is_deletable" @click="handleDelete(role)" class="action-btn-small delete" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="6" class="empty-state">
              <i class="fas fa-user-tag"></i>
              <p>No roles found</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-container" v-if="lastPage > 1">
      <div class="pagination-info">
        Showing {{ rolesData.length }} of {{ rolesTotal }} roles
      </div>
      <div class="pagination-controls">
        <button
          class="pagination-btn"
          :disabled="currentPage === 1"
          @click="loadRoles(currentPage - 1)"
        >
          <i class="fas fa-chevron-left"></i> Previous
        </button>
        <button
          v-for="page in visiblePages"
          :key="page"
          class="pagination-btn"
          :class="{ active: page === currentPage }"
          @click="loadRoles(page)"
        >
          {{ page }}
        </button>
        <button
          class="pagination-btn"
          :disabled="currentPage === lastPage"
          @click="loadRoles(currentPage + 1)"
        >
          Next <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal-overlay" v-if="showDeleteModal" @click="showDeleteModal = false">
      <div class="modern-modal" @click.stop>
        <div class="modal-header">
          <h3><i class="fas fa-exclamation-triangle text-danger"></i> Delete Role</h3>
          <button class="close-btn" @click="showDeleteModal = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete the role <strong>{{ roleToDelete?.display_name }}</strong>?</p>
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

    <!-- View Role Modal -->
    <div class="modal-overlay" v-if="showViewModal" @click="showViewModal = false">
      <div class="modern-modal modal-lg" @click.stop>
        <div class="modal-header">
          <h3><i class="fas fa-user-tag text-info"></i> Role Details</h3>
          <button class="close-btn" @click="showViewModal = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <div v-if="loadingView" class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Loading role details...</p>
          </div>
          <div v-else-if="viewRoleData">
            <div class="role-view-header">
              <div class="role-icon-box" :class="getRoleColorClass(viewRoleData.name)">
                <i :class="getRoleIcon(viewRoleData.name)"></i>
              </div>
              <div class="role-view-info">
                <h4>{{ viewRoleData.display_name }}</h4>
                <span class="role-slug">{{ viewRoleData.name }}</span>
              </div>
            </div>
            <p class="role-description">{{ viewRoleData.description || 'No description provided' }}</p>
            <div class="permissions-section">
              <h5><i class="fas fa-key"></i> Permissions ({{ viewRoleData.permissions?.length || 0 }})</h5>
              <div class="permissions-list">
                <span
                  v-for="perm in viewRoleData.permissions"
                  :key="perm.id"
                  class="permission-badge"
                >
                  {{ perm.display_name || perm.name }}
                </span>
                <span v-if="!viewRoleData.permissions?.length" class="text-muted">No permissions assigned</span>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="action-btn secondary" @click="showViewModal = false">
            <i class="fas fa-times"></i> Close
          </button>
          <button v-if="viewRoleData?.is_editable" class="action-btn primary" @click="openEditFromView">
            <i class="fas fa-edit"></i> Edit Role
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Role Modal -->
    <RoleFormModal
      v-if="showFormModal"
      :mode="formMode"
      :role="selectedRole"
      @close="showFormModal = false"
      @saved="handleRoleSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch, reactive, computed } from 'vue'
import RoleFormModal from '../../components/admin/roles/RoleFormModal.vue'
import { useRoles } from '../../composables/useRoles'

const { fetchRole } = useRoles()

const formatNumber = (value) => {
  if (value === null || value === undefined) return '0'
  return new Intl.NumberFormat().format(value)
}

// Local state
const rolesData = ref([])
const rolesTotal = ref(0)
const currentPage = ref(1)
const lastPage = ref(1)
const isLoading = ref(true)
const showDeleteModal = ref(false)
const roleToDelete = ref(null)
const isDeleting = ref(false)
const totalPermissions = ref(0)

// Form modal state
const showFormModal = ref(false)
const formMode = ref('create')
const selectedRole = ref(null)

// View modal state
const showViewModal = ref(false)
const viewRoleData = ref(null)
const loadingView = ref(false)

const filters = reactive({
  search: '',
  sort_by: 'id',
  sort_direction: 'desc'
})

const systemRolesCount = computed(() => {
  return rolesData.value.filter(r => !r.is_editable || !r.is_deletable).length
})

const customRolesCount = computed(() => {
  return rolesData.value.filter(r => r.is_editable && r.is_deletable).length
})

const visiblePages = computed(() => {
  const pages = []
  const current = currentPage.value
  const last = lastPage.value

  let start = Math.max(1, current - 2)
  let end = Math.min(last, current + 2)

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

onMounted(() => {
  loadRoles(1)
  loadStats()
})

// Watch filters with debounce for search
let searchTimeout = null
watch(() => filters.search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadRoles(1), 300)
})

const loadRoles = async (page = 1) => {
  isLoading.value = true

  try {
    const params = new URLSearchParams()
    if (filters.search) params.append('search', filters.search)
    if (filters.sort_by) params.append('sort_by', filters.sort_by)
    if (filters.sort_direction) params.append('sort_direction', filters.sort_direction)
    params.append('page', page)

    const response = await fetch(`/api/admin/roles?${params.toString()}`)

    if (!response.ok) throw new Error('Failed to fetch roles')

    const data = await response.json()

    if (data.roles) {
      rolesData.value = data.roles.data || []
      rolesTotal.value = data.roles.total || 0
      currentPage.value = data.roles.current_page || 1
      lastPage.value = data.roles.last_page || 1
    }
  } catch (error) {
    console.error('Error loading roles:', error)
  } finally {
    isLoading.value = false
  }
}

const loadStats = async () => {
  try {
    const response = await fetch('/api/admin/permissions?per_page=1')
    if (response.ok) {
      const data = await response.json()
      totalPermissions.value = data.permissions?.total || 0
    }
  } catch (error) {
    console.error('Error loading stats:', error)
  }
}

const toggleSortDirection = () => {
  filters.sort_direction = filters.sort_direction === 'asc' ? 'desc' : 'asc'
  loadRoles(1)
}

const getRoleIcon = (name) => {
  const icons = {
    admin: 'fas fa-user-shield',
    superadmin: 'fas fa-crown',
    moderator: 'fas fa-user-cog',
    manager: 'fas fa-user-tie',
    user: 'fas fa-user',
    investor: 'fas fa-hand-holding-usd'
  }
  return icons[name] || 'fas fa-user-tag'
}

const getRoleColorClass = (name) => {
  const classes = {
    admin: 'red',
    superadmin: 'gold',
    moderator: 'blue',
    manager: 'purple',
    user: 'green',
    investor: 'orange'
  }
  return classes[name] || 'blue'
}

const handleDelete = (role) => {
  roleToDelete.value = role
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!roleToDelete.value) return

  isDeleting.value = true

  try {
    const response = await fetch(`/api/admin/roles/${roleToDelete.value.id}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    })

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.error || 'Failed to delete role')
    }

    showDeleteModal.value = false
    roleToDelete.value = null
    loadRoles(currentPage.value)
  } catch (error) {
    console.error('Error deleting role:', error)
    alert(error.message)
  } finally {
    isDeleting.value = false
  }
}

// Modal handlers
const openCreateModal = () => {
  formMode.value = 'create'
  selectedRole.value = null
  showFormModal.value = true
}

const openEditModal = async (role) => {
  formMode.value = 'edit'
  try {
    const data = await fetchRole(role.id)
    selectedRole.value = { ...data.role, permissions: data.permissions }
    showFormModal.value = true
  } catch (error) {
    console.error('Error loading role:', error)
    alert('Failed to load role details')
  }
}

const viewRole = async (role) => {
  loadingView.value = true
  showViewModal.value = true
  try {
    const data = await fetchRole(role.id)
    viewRoleData.value = { ...data.role, permissions: data.permissions }
  } catch (error) {
    console.error('Error loading role:', error)
    showViewModal.value = false
    alert('Failed to load role details')
  } finally {
    loadingView.value = false
  }
}

const openEditFromView = () => {
  showViewModal.value = false
  selectedRole.value = viewRoleData.value
  formMode.value = 'edit'
  showFormModal.value = true
}

const handleRoleSaved = () => {
  showFormModal.value = false
  loadRoles(currentPage.value)
  loadStats()
}
</script>

<style scoped>
.roles-modern {
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
  padding: 0.875rem 1rem 0.875rem 2.75rem;
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

.filter-group {
  display: flex;
  gap: 0.5rem;
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

.sort-btn {
  width: 46px;
  height: 46px;
  border: 2px solid #eef2f7;
  border-radius: 12px;
  background: white;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #667eea;
}

.sort-btn:hover {
  background: #667eea;
  color: white;
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

.action-btn.danger {
  background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
  color: white;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
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

/* Role Cell */
.role-cell {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.role-icon-box {
  width: 45px;
  height: 45px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  color: white;
}

.role-icon-box.red { background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); }
.role-icon-box.gold { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); }
.role-icon-box.blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.role-icon-box.purple { background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%); }
.role-icon-box.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.role-icon-box.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }

.role-info {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.role-slug {
  font-size: 0.8rem;
  color: #888;
}

.description-text {
  color: #666;
  max-width: 250px;
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Badges */
.id-badge {
  background: linear-gradient(135deg, #e8ecef 0%, #d1d8e0 100%);
  padding: 0.35rem 0.75rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.85rem;
  color: #555;
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.4rem 0.85rem;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 600;
}

.badge.primary { background: #e3f2fd; color: #1976d2; }
.badge.info { background: #e0f7fa; color: #00838f; }
.badge.success { background: #e8f5e9; color: #2e7d32; }
.badge.warning { background: #fff3e0; color: #ef6c00; }
.badge.danger { background: #ffebee; color: #c62828; }

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
.text-info { color: #17a2b8; }
.text-center { text-align: center; }
.py-4 { padding: 1.5rem 0; }
.mt-2 { margin-top: 0.5rem; }

/* View Modal Enhancements */
.modal-lg {
  max-width: 700px;
}

.role-view-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #eef2f7;
}

.role-view-info h4 {
  margin: 0;
  font-size: 1.25rem;
}

.role-description {
  color: #666;
  margin-bottom: 1.5rem;
}

.permissions-section h5 {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  color: #333;
}

.permissions-section h5 i {
  color: #667eea;
}

.permissions-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.permission-badge {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  background: #e3f2fd;
  color: #1976d2;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
  .search-filter-bar {
    flex-direction: column;
  }

  .search-box, .filter-group, .action-buttons {
    width: 100%;
  }

  .action-buttons {
    justify-content: stretch;
  }

  .action-btn {
    flex: 1;
    justify-content: center;
  }

  .data-table-container {
    overflow-x: auto;
  }

  .modern-table {
    min-width: 800px;
  }

  .pagination-container {
    flex-direction: column;
  }
}
</style>
