<template>
  <div class="categories-management-advanced">
    <!-- Advanced Search & Filters -->
    <div class="search-filter-bar">
      <div class="search-box">
        <i class="fas fa-search search-icon"></i>
        <input
          type="text"
          v-model="filters.search"
          class="search-input"
          placeholder="Search categories by name (AR/EN)..."
        >
        <span v-if="filters.search" class="clear-search" @click="filters.search = ''">
          <i class="fas fa-times"></i>
        </span>
      </div>

      <div class="filter-controls">
        <select v-model="filters.status" class="role-select">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="suspended">Suspended</option>
        </select>

        <select v-model="filters.featured" class="role-select">
          <option value="all">All Categories</option>
          <option value="true">Featured Only</option>
          <option value="false">Not Featured</option>
        </select>

        <select v-model="filters.popular" class="role-select">
          <option value="all">All Categories</option>
          <option value="true">Popular Only</option>
          <option value="false">Not Popular</option>
        </select>

        <button class="action-btn primary" @click="resetFilters">
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
        Showing {{ categories.data.length }} of {{ categories.total }} categories
      </div>

      <button class="action-btn success" @click="showCreateModal = true">
        <i class="fas fa-plus-circle"></i>
        Add Category
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="loader-advanced"></div>
      <p>Loading categories...</p>
    </div>

    <!-- Categories Grid View -->
    <div v-else-if="viewMode === 'grid'" class="users-grid">
      <div
        v-for="category in categories.data"
        :key="category.id"
        class="user-card"
        :class="{ 'card-suspended': category.is_suspended, 'card-featured': category.is_featured }"
      >
        <div class="card-header-custom">
          <div class="user-avatar-wrapper">
            <img
              :src="category.image_url || '/images/placeholder.png'"
              :alt="category.name.en"
              class="user-avatar"
              style="border-radius: 8px;"
              @error="handleImageError"
            >
          </div>
          <div class="card-menu">
            <button class="menu-btn" @click="toggleMenu(category.id)">
              <i class="fas fa-ellipsis-v"></i>
            </button>
            <div v-if="activeMenu === category.id" class="dropdown-menu">
              <button @click="editCategory(category)" class="menu-item">
                <i class="fas fa-edit"></i>
                Edit Category
              </button>
              <button @click="handleToggleStatus(category)" class="menu-item">
                <i :class="category.is_suspended ? 'fas fa-check' : 'fas fa-ban'"></i>
                {{ category.is_suspended ? 'Activate' : 'Suspend' }}
              </button>
              <button @click="handleToggleFeatured(category)" class="menu-item">
                <i class="fas fa-star"></i>
                {{ category.is_featured ? 'Unfeature' : 'Feature' }}
              </button>
              <button @click="handleTogglePopular(category)" class="menu-item">
                <i class="fas fa-fire"></i>
                {{ category.is_popular ? 'Unpopular' : 'Popular' }}
              </button>
              <button @click="handleDelete(category)" class="menu-item danger">
                <i class="fas fa-trash"></i>
                Delete
              </button>
            </div>
          </div>
        </div>

        <div class="card-body-custom">
          <h3 class="user-name">{{ category.name.en }}</h3>
          <p class="user-id">{{ category.name.ar }}</p>

          <div class="user-roles">
            <span v-if="category.is_featured" class="role-badge badge-warning">
              <i class="fas fa-star"></i> Featured
            </span>
            <span v-if="category.is_popular" class="role-badge badge-danger">
              <i class="fas fa-fire"></i> Popular
            </span>
            <span class="role-badge" :class="category.is_suspended ? 'badge-danger' : 'badge-success'">
              {{ category.is_suspended ? 'Suspended' : 'Active' }}
            </span>
          </div>

          <div class="spacer"></div>

          <div class="user-stats">
            <div class="stat-item">
              <i class="fas fa-layer-group"></i>
              <span>{{ category.subcategories_count || 0 }} Subcategories</span>
            </div>
            <div class="stat-item">
              <i class="fas fa-file-alt"></i>
              <span>{{ category.posts_count || 0 }} Posts</span>
            </div>
          </div>
        </div>

        <div class="card-footer-custom">
          <button @click="editCategory(category)" class="action-btn primary">
            <i class="fas fa-edit"></i>
            Edit
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="categories.data.length === 0" class="empty-state-advanced">
        <div class="empty-icon">
          <i class="fas fa-folder"></i>
        </div>
        <h3>No Categories Found</h3>
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
            <th style="width: 80px;">Image</th>
            <th>Category Name</th>
            <th style="width: 180px;">Status</th>
            <th style="width: 150px;">Stats</th>
            <th style="width: 180px; text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in categories.data" :key="category.id">
            <!-- Image -->
            <td>
              <img
                :src="category.image_url || '/images/placeholder.png'"
                :alt="category.name.en"
                class="table-avatar"
                @error="handleImageError"
              >
            </td>

            <!-- Category Name -->
            <td>
              <div class="table-name">{{ category.name.en }}</div>
              <div class="table-subtitle">{{ category.name.ar }}</div>
            </td>

            <!-- Status -->
            <td>
              <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                <span class="table-badge" :class="category.is_suspended ? 'badge-danger' : 'badge-success'">
                  {{ category.is_suspended ? 'Suspended' : 'Active' }}
                </span>
                <span v-if="category.is_featured" class="table-badge badge-warning">
                  <i class="fas fa-star"></i>
                </span>
                <span v-if="category.is_popular" class="table-badge badge-danger">
                  <i class="fas fa-fire"></i>
                </span>
              </div>
            </td>

            <!-- Stats -->
            <td>
              <div class="table-meta">
                <span title="Subcategories">
                  <i class="fas fa-layer-group"></i> {{ category.subcategories_count || 0 }}
                </span>
                <span title="Posts">
                  <i class="fas fa-file-alt"></i> {{ category.posts_count || 0 }}
                </span>
              </div>
            </td>

            <!-- Actions -->
            <td>
              <div class="table-actions">
                <button
                  @click="editCategory(category)"
                  class="table-action-btn btn-edit"
                  title="Edit"
                >
                  <i class="fas fa-edit"></i>
                </button>
                <button
                  @click="handleToggleStatus(category)"
                  class="table-action-btn btn-ban"
                  :title="category.is_suspended ? 'Activate' : 'Suspend'"
                >
                  <i :class="category.is_suspended ? 'fas fa-check' : 'fas fa-ban'"></i>
                </button>
                <button
                  @click="handleToggleFeatured(category)"
                  class="table-action-btn"
                  :class="category.is_featured ? 'btn-featured' : 'btn-edit'"
                  title="Toggle Featured"
                >
                  <i class="fas fa-star"></i>
                </button>
                <button
                  @click="handleDelete(category)"
                  class="table-action-btn btn-delete"
                  title="Delete"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>

          <!-- Empty State Row -->
          <tr v-if="categories.data.length === 0">
            <td colspan="5" class="table-empty-state">
              <i class="fas fa-folder"></i>
              <h3>No Categories Found</h3>
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
    <div v-if="categories.data.length > 0" class="pagination-advanced">
      <button
        class="page-btn"
        :disabled="categories.current_page === 1"
        @click="loadCategories(categories.current_page - 1)"
      >
        <i class="fas fa-chevron-left"></i>
        Previous
      </button>

      <div class="page-numbers">
        <button
          v-for="page in visiblePages"
          :key="page"
          class="page-number"
          :class="{ active: page === categories.current_page }"
          @click="loadCategories(page)"
        >
          {{ page }}
        </button>
      </div>

      <button
        class="page-btn"
        :disabled="categories.current_page === categories.last_page"
        @click="loadCategories(categories.current_page + 1)"
      >
        Next
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>

    <!-- Create/Edit Modal -->
    <CategoryFormModal
      v-if="showCreateModal || showEditModal"
      :category="selectedCategory"
      :mode="showCreateModal ? 'create' : 'edit'"
      @close="closeModal"
      @saved="handleCategorySaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, reactive, onBeforeUnmount } from 'vue'
import { useCategories } from '../../../composables/useCategories'
import CategoryFormModal from '../../../components/admin/categories/CategoryFormModal.vue'

const { categories, loading, fetchCategories, deleteCategory, toggleStatus, toggleFeatured, togglePopular } = useCategories()

const viewMode = ref('list')
const activeMenu = ref(null)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedCategory = ref(null)

const filters = reactive({
  search: '',
  status: '',
  featured: 'all',
  popular: 'all',
  sort_by: 'id',
  sort_direction: 'desc',
  per_page: 15
})

const visiblePages = computed(() => {
  const pages = []
  const current = categories.value.current_page
  const last = categories.value.last_page

  let start = Math.max(1, current - 2)
  let end = Math.min(last, current + 2)

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

onMounted(async () => {
  await loadCategories()
  document.addEventListener('click', closeMenus)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', closeMenus)
})

// Watch filters with debounce for search
let searchTimeout = null
watch(() => filters.search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadCategories(1), 300)
})

watch(() => [filters.status, filters.featured, filters.popular, filters.sort_by, filters.sort_direction], () => {
  loadCategories(1)
})

const loadCategories = async (page = 1) => {
  await fetchCategories({ ...filters, page })
}

const resetFilters = () => {
  filters.search = ''
  filters.status = ''
  filters.featured = 'all'
  filters.popular = 'all'
  filters.sort_by = 'id'
  filters.sort_direction = 'desc'
  loadCategories(1)
}

const toggleMenu = (categoryId) => {
  activeMenu.value = activeMenu.value === categoryId ? null : categoryId
}

const closeMenus = () => {
  activeMenu.value = null
}

const editCategory = (category) => {
  closeMenus()
  selectedCategory.value = category
  showEditModal.value = true
}

const handleToggleStatus = async (category) => {
  closeMenus()

  if (!confirm(`Are you sure you want to ${category.is_suspended ? 'activate' : 'suspend'} "${category.name.en}"?`)) {
    return
  }

  try {
    await toggleStatus(category.id)
    await loadCategories(categories.value.current_page)
  } catch (error) {
    alert(error.message || 'Failed to toggle status')
  }
}

const handleToggleFeatured = async (category) => {
  closeMenus()

  try {
    await toggleFeatured(category.id)
    await loadCategories(categories.value.current_page)
  } catch (error) {
    alert(error.message || 'Failed to toggle featured status')
  }
}

const handleTogglePopular = async (category) => {
  closeMenus()

  try {
    await togglePopular(category.id)
    await loadCategories(categories.value.current_page)
  } catch (error) {
    alert(error.message || 'Failed to toggle popular status')
  }
}

const handleDelete = async (category) => {
  closeMenus()

  if (!confirm(`Are you sure you want to delete "${category.name.en}"? This action cannot be undone.`)) {
    return
  }

  try {
    await deleteCategory(category.id)
    await loadCategories(categories.value.current_page)
  } catch (error) {
    alert('Error deleting category: ' + (error.message || 'Unknown error'))
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  selectedCategory.value = null
}

const handleCategorySaved = () => {
  closeModal()
  loadCategories(categories.value.current_page)
}

const handleImageError = (event) => {
  event.target.src = '/images/placeholder.png'
}
</script>

<style scoped>
.categories-management-advanced {
  padding: 0;
  background: #f5f7fa;
  min-height: 100vh;
}

.card-featured {
  border-top: 4px solid #ffc107;
}

.card-suspended {
  opacity: 0.7;
  border-top: 4px solid #dc3545;
}

.role-badge {
  padding: 0.375rem 0.875rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge-success {
  background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
  color: white;
}

.badge-danger {
  background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
  color: white;
}

.badge-warning {
  background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
  color: #212529;
}

.btn-featured {
  background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
  color: #212529;
}

.btn-featured:hover {
  background: linear-gradient(135deg, #e0a800 0%, #c79100 100%);
}

/* Reuse styles from other components */
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

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

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
  min-height: 380px;
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
  border-radius: 8px;
  border: 4px solid white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  object-fit: cover;
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

.user-roles {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}

.spacer {
  flex: 1;
}

.user-stats {
  display: flex;
  gap: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e9ecef;
  flex-wrap: wrap;
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
}
</style>
