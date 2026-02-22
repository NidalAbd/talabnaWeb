<template>
  <div class="permissions-modern">
    <!-- Stats Cards -->
    <div class="stats-dashboard">
      <div class="stats-grid">
        <div class="stat-card-compact stat-blue">
          <div class="stat-icon"><i class="fas fa-key"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(permissionsTotal) }}</div>
            <div class="stat-label-compact">Total Permissions</div>
          </div>
        </div>
        <div class="stat-card-compact stat-green">
          <div class="stat-icon"><i class="fas fa-lock"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(systemPermissionsCount) }}</div>
            <div class="stat-label-compact">System Permissions</div>
          </div>
        </div>
        <div class="stat-card-compact stat-orange">
          <div class="stat-icon"><i class="fas fa-user-edit"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(customPermissionsCount) }}</div>
            <div class="stat-label-compact">Custom Permissions</div>
          </div>
        </div>
        <div class="stat-card-compact stat-purple">
          <div class="stat-icon"><i class="fas fa-folder"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(categories.length) }}</div>
            <div class="stat-label-compact">Categories</div>
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
          placeholder="Search permissions by name..."
          v-model="filters.search"
        >
        <span v-if="filters.search" class="clear-search" @click="filters.search = ''">
          <i class="fas fa-times"></i>
        </span>
      </div>
      <div class="filter-group">
        <select class="filter-select" v-model="filters.category">
          <option value="">All Categories</option>
          <option v-for="cat in categories" :key="cat" :value="cat">
            {{ cat }}
          </option>
        </select>
        <select class="filter-select" v-model="filters.per_page">
          <option :value="15">15 per page</option>
          <option :value="30">30 per page</option>
          <option :value="50">50 per page</option>
          <option :value="100">100 per page</option>
        </select>
      </div>
      <div class="action-buttons">
        <router-link to="/admin/roles" class="action-btn info">
          <i class="fas fa-user-tag"></i> Roles
        </router-link>
        <button class="action-btn success" @click="showCreateModal = true">
          <i class="fas fa-plus"></i> Create
        </button>
        <button class="action-btn primary" @click="showGenerateModal = true">
          <i class="fas fa-cogs"></i> Generate
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading permissions...</p>
    </div>

    <!-- Table -->
    <div v-else class="data-table-container">
      <table class="modern-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Permission</th>
            <th>Category</th>
            <th>Roles</th>
            <th>Type</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody v-if="permissionsData.length > 0">
          <tr v-for="permission in permissionsData" :key="permission.id">
            <td><span class="id-badge">#{{ permission.id }}</span></td>
            <td>
              <div class="permission-cell">
                <div class="permission-icon-box" :class="getCategoryColorClass(permission.category)">
                  <i :class="getCategoryIcon(permission.category)"></i>
                </div>
                <div class="permission-info">
                  <strong>{{ permission.display_name }}</strong>
                  <span class="permission-slug">{{ permission.name }}</span>
                </div>
              </div>
            </td>
            <td>
              <span class="badge" :class="getCategoryBadgeClass(permission.category)">
                {{ permission.category }}
              </span>
            </td>
            <td>
              <span class="badge info">
                <i class="fas fa-user-tag"></i> {{ permission.role_count }} roles
              </span>
            </td>
            <td>
              <span v-if="permission.is_system" class="badge warning">
                <i class="fas fa-lock"></i> System
              </span>
              <span v-else class="badge success">
                <i class="fas fa-user"></i> Custom
              </span>
            </td>
            <td>
              <div class="table-actions">
                <button
                  v-if="permission.is_deletable"
                  @click="handleDelete(permission)"
                  class="action-btn-small delete"
                  title="Delete"
                >
                  <i class="fas fa-trash"></i>
                </button>
                <span v-else class="lock-indicator" title="Cannot delete - assigned to roles or system permission">
                  <i class="fas fa-lock"></i>
                </span>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="6" class="empty-state">
              <i class="fas fa-key"></i>
              <p>No permissions found</p>
              <button @click="resetFilters" class="action-btn primary">
                <i class="fas fa-redo"></i> Reset Filters
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-container" v-if="lastPage > 1">
      <div class="pagination-info">
        Showing {{ permissionsData.length }} of {{ permissionsTotal }} permissions
      </div>
      <div class="pagination-controls">
        <button
          class="pagination-btn"
          :disabled="currentPage === 1"
          @click="loadPermissions(currentPage - 1)"
        >
          <i class="fas fa-chevron-left"></i> Previous
        </button>
        <button
          v-for="page in visiblePages"
          :key="page"
          class="pagination-btn"
          :class="{ active: page === currentPage }"
          @click="loadPermissions(page)"
        >
          {{ page }}
        </button>
        <button
          class="pagination-btn"
          :disabled="currentPage === lastPage"
          @click="loadPermissions(currentPage + 1)"
        >
          Next <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Generate Permissions Modal -->
    <div class="modal-overlay" v-if="showGenerateModal" @click="showGenerateModal = false">
      <div class="modern-modal" @click.stop>
        <div class="modal-header">
          <h3><i class="fas fa-plus-circle text-success"></i> Generate Permissions</h3>
          <button class="close-btn" @click="showGenerateModal = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Module Name</label>
            <input
              type="text"
              v-model="moduleName"
              class="form-input"
              placeholder="e.g., posts, comments, orders"
            >
            <p class="form-help">
              This will create: create_[module], view_[module], edit_[module], update_[module], destroy_[module]
            </p>
          </div>
        </div>
        <div class="modal-footer">
          <button class="action-btn secondary" @click="showGenerateModal = false">
            <i class="fas fa-times"></i> Cancel
          </button>
          <button class="action-btn success" @click="generatePermissions" :disabled="isGenerating || !moduleName">
            <i v-if="isGenerating" class="fas fa-spinner fa-spin"></i>
            <i v-else class="fas fa-plus-circle"></i> Generate
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" v-if="showDeleteModal" @click="showDeleteModal = false">
      <div class="modern-modal" @click.stop>
        <div class="modal-header">
          <h3><i class="fas fa-exclamation-triangle text-danger"></i> Delete Permission</h3>
          <button class="close-btn" @click="showDeleteModal = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete the permission <strong>{{ permissionToDelete?.display_name }}</strong>?</p>
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

    <!-- Create Permission Modal -->
    <PermissionFormModal
      v-if="showCreateModal"
      @close="showCreateModal = false"
      @saved="handlePermissionSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch, reactive, computed } from 'vue'
import PermissionFormModal from '../../components/admin/permissions/PermissionFormModal.vue'

const formatNumber = (value) => {
  if (value === null || value === undefined) return '0'
  return new Intl.NumberFormat().format(value)
}

// Local state
const permissionsData = ref([])
const permissionsTotal = ref(0)
const currentPage = ref(1)
const lastPage = ref(1)
const isLoading = ref(true)
const categories = ref([])
const showGenerateModal = ref(false)
const showDeleteModal = ref(false)
const showCreateModal = ref(false)
const permissionToDelete = ref(null)
const isDeleting = ref(false)
const isGenerating = ref(false)
const moduleName = ref('')

const filters = reactive({
  search: '',
  category: '',
  per_page: 15
})

const systemPermissionsCount = computed(() => {
  return permissionsData.value.filter(p => p.is_system).length
})

const customPermissionsCount = computed(() => {
  return permissionsData.value.filter(p => !p.is_system).length
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
  loadPermissions(1)
  loadCategories()
})

// Watch filters with debounce for search
let searchTimeout = null
watch(() => filters.search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadPermissions(1), 300)
})

watch(() => [filters.category, filters.per_page], () => {
  loadPermissions(1)
})

const loadPermissions = async (page = 1) => {
  isLoading.value = true

  try {
    const params = new URLSearchParams()
    if (filters.search) params.append('search', filters.search)
    if (filters.category) params.append('category', filters.category)
    if (filters.per_page) params.append('per_page', filters.per_page)
    params.append('page', page)

    const response = await fetch(`/api/admin/permissions?${params.toString()}`)

    if (!response.ok) {
      throw new Error('Failed to fetch permissions')
    }

    const data = await response.json()

    if (data.permissions) {
      permissionsData.value = data.permissions.data || []
      permissionsTotal.value = data.permissions.total || 0
      currentPage.value = data.permissions.current_page || 1
      lastPage.value = data.permissions.last_page || 1
    }
  } catch (error) {
    console.error('Error loading permissions:', error)
  } finally {
    isLoading.value = false
  }
}

const loadCategories = async () => {
  try {
    const response = await fetch('/api/admin/permissions/categories')
    if (response.ok) {
      const data = await response.json()
      categories.value = data.categories || []
    }
  } catch (error) {
    console.error('Error loading categories:', error)
  }
}

const resetFilters = () => {
  filters.search = ''
  filters.category = ''
  filters.per_page = 15
  loadPermissions(1)
}

const getCategoryIcon = (category) => {
  const icons = {
    users: 'fas fa-users',
    roles: 'fas fa-user-tag',
    permissions: 'fas fa-key',
    posts: 'fas fa-file-alt',
    categories: 'fas fa-folder',
    settings: 'fas fa-cog',
    reports: 'fas fa-flag'
  }
  return icons[category] || 'fas fa-shield-alt'
}

const getCategoryColorClass = (category) => {
  const classes = {
    users: 'blue',
    roles: 'gold',
    permissions: 'purple',
    posts: 'green',
    categories: 'orange',
    settings: 'teal',
    reports: 'red'
  }
  return classes[category] || 'blue'
}

const getCategoryBadgeClass = (category) => {
  const classes = {
    users: 'primary',
    roles: 'warning',
    permissions: 'info',
    posts: 'success',
    categories: 'purple',
    settings: 'secondary',
    reports: 'danger'
  }
  return classes[category] || 'secondary'
}

const handleDelete = (permission) => {
  permissionToDelete.value = permission
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!permissionToDelete.value) return

  isDeleting.value = true

  try {
    const response = await fetch(`/api/admin/permissions/${permissionToDelete.value.id}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    })

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.error || 'Failed to delete permission')
    }

    showDeleteModal.value = false
    permissionToDelete.value = null
    loadPermissions(currentPage.value)
  } catch (error) {
    console.error('Error deleting permission:', error)
    alert(error.message)
  } finally {
    isDeleting.value = false
  }
}

const generatePermissions = async () => {
  if (!moduleName.value) return

  isGenerating.value = true

  try {
    const response = await fetch('/api/admin/permissions/generate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ module_name: moduleName.value })
    })

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Failed to generate permissions')
    }

    showGenerateModal.value = false
    moduleName.value = ''
    loadPermissions(1)
    loadCategories()
  } catch (error) {
    console.error('Error generating permissions:', error)
    alert(error.message)
  } finally {
    isGenerating.value = false
  }
}

const handlePermissionSaved = () => {
  showCreateModal.value = false
  loadPermissions(1)
  loadCategories()
}
</script>

<style scoped>
.permissions-modern {
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

.empty-state p {
  margin-bottom: 1.5rem;
}

/* Permission Cell */
.permission-cell {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.permission-icon-box {
  width: 45px;
  height: 45px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  color: white;
}

.permission-icon-box.blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.permission-icon-box.gold { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); }
.permission-icon-box.purple { background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%); }
.permission-icon-box.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.permission-icon-box.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.permission-icon-box.teal { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.permission-icon-box.red { background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); }

.permission-info {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.permission-slug {
  font-size: 0.8rem;
  color: #888;
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
.badge.secondary { background: #f5f5f5; color: #616161; }
.badge.purple { background: #f3e5f5; color: #7b1fa2; }

/* Lock Indicator */
.lock-indicator {
  color: #999;
  font-size: 0.9rem;
}

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

/* Form Elements */
.form-group {
  margin-bottom: 1rem;
}

.form-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #333;
}

.form-input {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 2px solid #eef2f7;
  border-radius: 12px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.form-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.form-help {
  margin-top: 0.5rem;
  font-size: 0.85rem;
  color: #888;
}

.text-danger { color: #dc3545; }
.text-success { color: #28a745; }
.text-muted { color: #888; font-size: 0.9rem; }

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
