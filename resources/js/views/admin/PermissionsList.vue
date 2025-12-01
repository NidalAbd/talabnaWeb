<template>
  <div class="permissions-management-advanced">
    <!-- Action Bar -->
    <div class="action-bar mb-4">
      <button @click="showGenerateModal = true" class="action-btn success">
        <i class="fas fa-magic"></i>
        Generate CRUD
      </button>
      <a href="/permissions/create" class="action-btn primary">
        <i class="fas fa-plus-circle"></i>
        Create Permission
      </a>
    </div>

    <!-- Advanced Search & Filters -->
    <div class="search-filter-bar">
      <div class="search-box">
        <i class="fas fa-search search-icon"></i>
        <input
          type="text"
          v-model="filters.search"
          class="search-input"
          placeholder="Search permissions by name or description..."
        >
        <span v-if="filters.search" class="clear-search" @click="filters.search = ''">
          <i class="fas fa-times"></i>
        </span>
      </div>

      <div class="filter-controls">
        <select v-model="filters.category" class="category-select">
          <option value="">All Categories</option>
          <option v-for="category in categories" :key="category.value" :value="category.value">
            {{ category.label }}
          </option>
        </select>

        <div class="sort-group">
          <select v-model="filters.sort_by" class="sort-select">
            <option value="id">Sort by ID</option>
            <option value="name">Sort by Name</option>
            <option value="created_at">Sort by Date</option>
          </select>
          <button
            class="sort-direction-btn"
            @click="filters.sort_direction = filters.sort_direction === 'asc' ? 'desc' : 'asc'"
            :title="filters.sort_direction === 'asc' ? 'Ascending' : 'Descending'"
          >
            <i :class="filters.sort_direction === 'asc' ? 'fas fa-sort-amount-up' : 'fas fa-sort-amount-down'"></i>
          </button>
        </div>

        <button class="action-btn secondary" @click="resetFilters">
          <i class="fas fa-redo"></i>
        </button>
      </div>
    </div>

    <!-- View Controls -->
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
        Showing {{ permissions.data.length }} of {{ permissions.total }} permissions
      </div>

      <select v-model="filters.per_page" class="per-page-select">
        <option :value="15">15 per page</option>
        <option :value="25">25 per page</option>
        <option :value="50">50 per page</option>
        <option :value="100">100 per page</option>
      </select>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="loader-advanced"></div>
      <p>Loading permissions...</p>
    </div>

    <!-- Permissions Grid View -->
    <div v-else-if="viewMode === 'grid'" class="permissions-grid">
      <div
        v-for="permission in permissions.data"
        :key="permission.id"
        class="permission-card"
        :class="{ system: permission.is_system }"
      >
        <div class="card-header-custom">
          <div class="permission-icon-wrapper">
            <div class="permission-icon" :class="getCategoryIconClass(permission.category)">
              <i :class="getCategoryIcon(permission.category)"></i>
            </div>
            <span v-if="permission.is_system" class="system-badge">
              <i class="fas fa-shield-alt"></i>
              System
            </span>
          </div>
          <div class="card-menu">
            <button class="menu-btn" @click="toggleMenu(permission.id)">
              <i class="fas fa-ellipsis-v"></i>
            </button>
            <div v-if="activeMenu === permission.id" class="dropdown-menu">
              <a :href="`/permissions/${permission.id}`" class="menu-item">
                <i class="fas fa-eye"></i>
                View Details
              </a>
              <a :href="`/permissions/${permission.id}/edit`" class="menu-item">
                <i class="fas fa-edit"></i>
                Edit Permission
              </a>
              <button
                v-if="permission.is_deletable"
                @click="handleDelete(permission)"
                class="menu-item danger"
              >
                <i class="fas fa-trash"></i>
                Delete
              </button>
            </div>
          </div>
        </div>

        <div class="card-body-custom">
          <h3 class="permission-name">{{ permission.name }}</h3>
          <p class="permission-id">#{{ permission.id }}</p>

          <div class="permission-display">
            <label>Display Name</label>
            <p>{{ permission.display_name || 'N/A' }}</p>
          </div>

          <div class="permission-description">
            <label>Description</label>
            <p>{{ truncate(permission.description, 80) || 'No description' }}</p>
          </div>

          <div class="spacer"></div>

          <div class="permission-meta">
            <div class="meta-item">
              <i class="fas fa-user-tag"></i>
              <span>{{ permission.role_count }} Roles</span>
            </div>
            <div class="meta-item" v-if="permission.category">
              <i class="fas fa-tag"></i>
              <span>{{ formatCategory(permission.category) }}</span>
            </div>
          </div>
        </div>

        <div class="card-footer-custom">
          <a :href="`/permissions/${permission.id}`" class="action-btn primary">
            <i class="fas fa-eye"></i>
            View
          </a>
          <a :href="`/permissions/${permission.id}/edit`" class="action-btn secondary">
            <i class="fas fa-edit"></i>
            Edit
          </a>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="permissions.data.length === 0" class="empty-state-advanced">
        <div class="empty-icon">
          <i class="fas fa-key"></i>
        </div>
        <h3>No Permissions Found</h3>
        <p>Try adjusting your filters or search criteria</p>
        <button @click="resetFilters" class="action-btn primary">
          <i class="fas fa-redo"></i>
          Reset Filters
        </button>
      </div>
    </div>

    <!-- Permissions List View -->
    <div v-else class="permissions-list-view">
      <div
        v-for="permission in permissions.data"
        :key="permission.id"
        class="permission-list-item"
        :class="{ system: permission.is_system }"
      >
        <div class="list-item-icon">
          <div class="permission-icon" :class="getCategoryIconClass(permission.category)">
            <i :class="getCategoryIcon(permission.category)"></i>
          </div>
        </div>

        <div class="list-item-info">
          <h4>{{ permission.name }}</h4>
          <p class="permission-id">#{{ permission.id }}</p>
        </div>

        <div class="list-item-display">
          <label>Display Name</label>
          <p>{{ permission.display_name || 'N/A' }}</p>
        </div>

        <div class="list-item-description">
          <label>Description</label>
          <p>{{ truncate(permission.description, 60) || 'No description' }}</p>
        </div>

        <div class="list-item-meta">
          <div class="meta-pill">
            <i class="fas fa-user-tag"></i>
            {{ permission.role_count }}
          </div>
          <span v-if="permission.is_system" class="system-badge small">
            <i class="fas fa-shield-alt"></i>
          </span>
        </div>

        <div class="list-item-actions">
          <a :href="`/permissions/${permission.id}`" class="icon-btn" title="View">
            <i class="fas fa-eye"></i>
          </a>
          <a :href="`/permissions/${permission.id}/edit`" class="icon-btn" title="Edit">
            <i class="fas fa-edit"></i>
          </a>
          <button
            v-if="permission.is_deletable"
            @click="handleDelete(permission)"
            class="icon-btn danger"
            title="Delete"
          >
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>

      <!-- Empty State for List View -->
      <div v-if="permissions.data.length === 0" class="empty-state-advanced">
        <div class="empty-icon">
          <i class="fas fa-key"></i>
        </div>
        <h3>No Permissions Found</h3>
        <p>Try adjusting your filters or search criteria</p>
        <button @click="resetFilters" class="action-btn primary">
          <i class="fas fa-redo"></i>
          Reset Filters
        </button>
      </div>
    </div>

    <!-- Advanced Pagination -->
    <div v-if="permissions.data.length > 0" class="pagination-advanced">
      <button
        class="page-btn"
        :disabled="permissions.current_page === 1"
        @click="loadPermissions(permissions.current_page - 1)"
      >
        <i class="fas fa-chevron-left"></i>
        Previous
      </button>

      <div class="page-numbers">
        <button
          v-for="page in visiblePages"
          :key="page"
          class="page-number"
          :class="{ active: page === permissions.current_page }"
          @click="loadPermissions(page)"
        >
          {{ page }}
        </button>
      </div>

      <button
        class="page-btn"
        :disabled="permissions.current_page === permissions.last_page"
        @click="loadPermissions(permissions.current_page + 1)"
      >
        Next
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>

    <!-- Generate Permissions Modal -->
    <div v-if="showGenerateModal" class="modal-overlay" @click="closeGenerateModal">
      <div class="modal-dialog-advanced" @click.stop>
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-magic text-success"></i>
              Generate CRUD Permissions
            </h5>
            <button type="button" class="btn-close" @click="closeGenerateModal">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="modal-body">
            <p class="text-muted mb-3">
              This will generate standard CRUD permissions for a module (view, create, edit, delete).
            </p>

            <div class="form-group mb-3">
              <label class="form-label">
                Module Name <span class="text-danger">*</span>
              </label>
              <input
                type="text"
                v-model="moduleName"
                class="form-control-modern"
                placeholder="Enter module name (e.g. product, blog_post)"
                @input="updatePreview"
              >
              <small class="form-text">
                Use lowercase letters, underscores instead of spaces
              </small>
            </div>

            <div class="alert-info">
              <i class="fas fa-info-circle"></i>
              <strong>The following permissions will be generated:</strong>
              <ul class="mb-0 mt-2">
                <li v-for="perm in previewPermissions" :key="perm">{{ perm }}</li>
              </ul>
            </div>

            <div v-if="generateError" class="alert-danger mt-3">
              <i class="fas fa-exclamation-triangle"></i>
              {{ generateError }}
            </div>

            <div v-if="generateSuccess" class="alert-success mt-3">
              <i class="fas fa-check-circle"></i>
              {{ generateSuccess }}
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="action-btn secondary" @click="closeGenerateModal">
              Cancel
            </button>
            <button
              type="button"
              class="action-btn success"
              @click="handleGenerate"
              :disabled="!moduleName || generating"
            >
              <i v-if="generating" class="fas fa-spinner fa-spin"></i>
              <i v-else class="fas fa-magic"></i>
              {{ generating ? 'Generating...' : 'Generate Permissions' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, reactive, computed, onBeforeUnmount } from 'vue'
import { usePermissions } from '../../composables/usePermissions'

const { permissions, loading, fetchPermissions, generatePermissions, deletePermission } = usePermissions()

const categories = ref([])
const showGenerateModal = ref(false)
const moduleName = ref('')
const generating = ref(false)
const generateError = ref('')
const generateSuccess = ref('')
const viewMode = ref('grid')
const activeMenu = ref(null)

const filters = reactive({
  search: '',
  category: '',
  per_page: 15,
  sort_by: 'id',
  sort_direction: 'desc'
})

const previewPermissions = computed(() => {
  const module = moduleName.value || '[module]'
  return [
    `view_${module}`,
    `create_${module}`,
    `edit_${module}`,
    `delete_${module}`
  ]
})

const visiblePages = computed(() => {
  const pages = []
  const current = permissions.value.current_page
  const last = permissions.value.last_page

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
  searchTimeout = setTimeout(() => loadPermissions(1), 300)
})

watch(() => [filters.category, filters.per_page, filters.sort_by, filters.sort_direction], () => {
  loadPermissions(1)
})

const loadData = async () => {
  await Promise.all([
    loadPermissions(),
    loadCategories()
  ])
}

const loadPermissions = async (page = 1) => {
  await fetchPermissions({ ...filters, page })
}

const loadCategories = async () => {
  try {
    const response = await fetch('/api/admin/permissions/categories')
    const data = await response.json()
    categories.value = data.categories
  } catch (error) {
    console.error('Error loading categories:', error)
  }
}

const resetFilters = () => {
  filters.search = ''
  filters.category = ''
  filters.per_page = 15
  filters.sort_by = 'id'
  filters.sort_direction = 'desc'
  loadPermissions(1)
}

const getCategoryIcon = (category) => {
  const icons = {
    'view': 'fas fa-eye',
    'create': 'fas fa-plus-circle',
    'edit': 'fas fa-edit',
    'delete': 'fas fa-trash'
  }
  return icons[category] || 'fas fa-key'
}

const getCategoryIconClass = (category) => {
  const classes = {
    'view': 'icon-info',
    'create': 'icon-success',
    'edit': 'icon-warning',
    'delete': 'icon-danger'
  }
  return classes[category] || 'icon-primary'
}

const formatCategory = (category) => {
  return category ? category.charAt(0).toUpperCase() + category.slice(1) : 'General'
}

const truncate = (text, length) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

const toggleMenu = (permissionId) => {
  activeMenu.value = activeMenu.value === permissionId ? null : permissionId
}

const closeMenus = () => {
  activeMenu.value = null
}

const closeGenerateModal = () => {
  showGenerateModal.value = false
  moduleName.value = ''
  generateError.value = ''
  generateSuccess.value = ''
}

const updatePreview = () => {
  generateError.value = ''
  generateSuccess.value = ''
}

const handleGenerate = async () => {
  if (!moduleName.value) return

  generating.value = true
  generateError.value = ''
  generateSuccess.value = ''

  try {
    const result = await generatePermissions(moduleName.value)

    if (result.success) {
      generateSuccess.value = result.message
      if (result.existing && result.existing.length > 0) {
        generateSuccess.value += ` (${result.existing.length} already existed)`
      }

      // Reload permissions after 1.5 seconds
      setTimeout(async () => {
        await loadPermissions(1)
        await loadCategories()
        closeGenerateModal()
      }, 1500)
    }
  } catch (error) {
    generateError.value = error.message || 'Failed to generate permissions'
  } finally {
    generating.value = false
  }
}

const handleDelete = async (permission) => {
  closeMenus()

  if (!confirm(`Are you sure you want to delete the permission "${permission.display_name}"? This action cannot be undone.`)) {
    return
  }

  try {
    await deletePermission(permission.id)
    alert('Permission deleted successfully')
    await loadPermissions(permissions.value.current_page)
  } catch (error) {
    alert(error.message || 'Failed to delete permission')
  }
}
</script>

<style scoped>
.permissions-management-advanced {
  padding: 0;
  background: #f5f7fa;
}

/* Section Header */
.section-header {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.header-content {
  flex: 1;
}

.section-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0 0 0.5rem 0;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.section-title i {
  color: #667eea;
}

.section-subtitle {
  color: #6c757d;
  margin: 0;
  font-size: 0.95rem;
}

.header-actions {
  display: flex;
  gap: 1rem;
}

/* Modern Stats Cards */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
}

.stat-card-advanced {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.stat-card-advanced::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: var(--stat-color-start, #667eea);
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
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
}

.stat-icon-wrapper {
  position: relative;
}

.stat-icon {
  width: 64px;
  height: 64px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  background: linear-gradient(135deg, var(--stat-color-start, #667eea) 0%, var(--stat-color-end, #764ba2) 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-details {
  flex: 1;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0 0 0.25rem 0;
}

.stat-label {
  color: #6c757d;
  font-size: 0.9rem;
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
  transition: color 0.2s ease;
}

.clear-search:hover {
  color: #dc3545;
}

.filter-controls {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.category-select,
.sort-select,
.per-page-select {
  padding: 0.625rem 1.25rem;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  background: white;
}

.category-select:focus,
.sort-select:focus,
.per-page-select:focus {
  outline: none;
  border-color: #667eea;
}

.sort-group {
  display: flex;
  gap: 0.5rem;
}

.sort-direction-btn {
  padding: 0.625rem 0.875rem;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  background: white;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #6c757d;
}

.sort-direction-btn:hover {
  border-color: #667eea;
  color: #667eea;
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
  white-space: nowrap;
}

.action-btn.primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.action-btn.secondary {
  background: #6c757d;
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
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
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
  background: white;
  padding: 1rem 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.07);
}

.view-toggle {
  display: flex;
  gap: 0.5rem;
  background: #f8f9fa;
  padding: 0.375rem;
  border-radius: 12px;
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

/* Permissions Grid */
.permissions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 1.5rem;
}

.permission-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  display: flex;
  flex-direction: column;
  min-height: 400px;
}

.permission-card.system {
  border: 2px solid #ffc107;
}

.permission-card:hover {
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

.permission-icon-wrapper {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.permission-icon {
  width: 70px;
  height: 70px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.75rem;
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.icon-danger {
  background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
}

.icon-warning {
  background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
  color: #212529;
}

.icon-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.icon-info {
  background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}

.icon-success {
  background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
}

.system-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.75rem;
  background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
  color: #212529;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.system-badge.small {
  padding: 0.25rem 0.5rem;
  font-size: 0.65rem;
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

.permission-name {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0 0 0.25rem 0;
  color: #2c3e50;
  word-break: break-word;
}

.permission-id {
  color: #6c757d;
  font-size: 0.875rem;
  margin: 0 0 1rem 0;
}

.permission-display,
.permission-description {
  margin-bottom: 1rem;
}

.permission-display label,
.permission-description label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0 0 0.25rem 0;
  display: block;
}

.permission-display p,
.permission-description p {
  margin: 0;
  color: #495057;
  font-size: 0.9rem;
}

.spacer {
  flex: 1;
}

.permission-meta {
  display: flex;
  gap: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e9ecef;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #6c757d;
  font-size: 0.875rem;
  font-weight: 500;
}

.meta-item i {
  color: #667eea;
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
.permissions-list-view {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.permission-list-item {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
  transition: all 0.3s ease;
}

.permission-list-item:hover {
  transform: translateX(8px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.permission-list-item.system {
  border-left: 4px solid #ffc107;
}

.list-item-icon .permission-icon {
  width: 56px;
  height: 56px;
  font-size: 1.25rem;
}

.list-item-info {
  flex: 0 0 220px;
}

.list-item-info h4 {
  margin: 0 0 0.25rem 0;
  font-size: 1rem;
  font-weight: 600;
  color: #2c3e50;
}

.list-item-display {
  flex: 0 0 180px;
}

.list-item-description {
  flex: 1;
  min-width: 200px;
}

.list-item-display label,
.list-item-description label {
  font-size: 0.7rem;
  font-weight: 600;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0 0 0.25rem 0;
  display: block;
}

.list-item-display p,
.list-item-description p {
  margin: 0;
  color: #495057;
  font-size: 0.875rem;
}

.list-item-meta {
  flex: 0 0 120px;
  display: flex;
  gap: 0.5rem;
  align-items: center;
  justify-content: flex-end;
}

.meta-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.75rem;
  background: #e9ecef;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
  color: #495057;
}

.meta-pill i {
  color: #667eea;
}

.list-item-actions {
  display: flex;
  gap: 0.5rem;
}

.icon-btn {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  border: 2px solid #e9ecef;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #6c757d;
  text-decoration: none;
}

.icon-btn:hover {
  background: #f8f9fa;
  border-color: #667eea;
  color: #667eea;
  transform: translateY(-2px);
}

.icon-btn.danger:hover {
  border-color: #dc3545;
  color: #dc3545;
  background: #fee;
}

/* Empty State */
.empty-state-advanced {
  grid-column: 1 / -1;
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 16px;
}

.empty-icon {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
}

.empty-icon i {
  font-size: 3rem;
  color: white;
}

.empty-state-advanced h3 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.empty-state-advanced p {
  color: #6c757d;
  margin-bottom: 1.5rem;
}

/* Advanced Pagination */
.pagination-advanced {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.75rem;
  margin-top: 2rem;
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.07);
}

.page-btn {
  padding: 0.75rem 1.25rem;
  border: 2px solid #e9ecef;
  background: white;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  color: #495057;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.page-btn:hover:not(:disabled) {
  border-color: #667eea;
  color: #667eea;
  transform: translateY(-2px);
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  gap: 0.5rem;
}

.page-number {
  width: 42px;
  height: 42px;
  border: 2px solid #e9ecef;
  background: white;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  color: #495057;
  transition: all 0.2s ease;
}

.page-number:hover {
  border-color: #667eea;
  color: #667eea;
}

.page-number.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-color: #667eea;
  color: white;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 1rem;
}

.modal-dialog-advanced {
  background: white;
  border-radius: 16px;
  max-width: 600px;
  width: 100%;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  animation: modalFadeIn 0.3s ease;
}

@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.modal-content {
  border: none;
  border-radius: 16px;
}

.modal-header {
  border-bottom: 1px solid #e9ecef;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  font-weight: 600;
  font-size: 1.25rem;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-close {
  background: transparent;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: #6c757d;
  padding: 0;
  transition: color 0.2s ease;
}

.btn-close:hover {
  color: #dc3545;
}

.modal-body {
  padding: 1.5rem;
}

.text-muted {
  color: #6c757d;
}

.form-group {
  margin-bottom: 1rem;
}

.form-label {
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.5rem;
  display: block;
}

.text-danger {
  color: #dc3545;
}

.form-control-modern {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e9ecef;
  border-radius: 10px;
  font-size: 0.95rem;
  transition: all 0.2s ease;
}

.form-control-modern:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.form-text {
  font-size: 0.85rem;
  color: #6c757d;
  margin-top: 0.25rem;
  display: block;
}

.alert-info {
  background: #d1ecf1;
  border: 1px solid #bee5eb;
  border-radius: 10px;
  padding: 1rem;
  color: #0c5460;
}

.alert-danger {
  background: #f8d7da;
  border: 1px solid #f5c6cb;
  border-radius: 10px;
  padding: 1rem;
  color: #721c24;
}

.alert-success {
  background: #d4edda;
  border: 1px solid #c3e6cb;
  border-radius: 10px;
  padding: 1rem;
  color: #155724;
}

.alert-info ul,
.alert-danger ul,
.alert-success ul {
  margin: 0.5rem 0 0 1.5rem;
  padding: 0;
}

.modal-footer {
  border-top: 1px solid #e9ecef;
  padding: 1.5rem;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
  .section-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .header-actions {
    width: 100%;
    flex-direction: column;
  }

  .header-actions .action-btn {
    width: 100%;
    justify-content: center;
  }

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

  .category-select,
  .sort-select {
    flex: 1;
  }

  .permissions-grid {
    grid-template-columns: 1fr;
  }

  .permission-list-item {
    flex-wrap: wrap;
  }

  .list-item-info,
  .list-item-display,
  .list-item-description {
    flex: 1 1 100%;
  }

  .permission-icon {
    width: 50px;
    height: 50px;
    font-size: 1.25rem;
  }
}
</style>
