<template>
  <div class="subcategories-management-advanced">
    <!-- Advanced Search & Filters -->
    <div class="search-filter-bar">
      <div class="search-box">
        <i class="fas fa-search search-icon"></i>
        <input
          type="text"
          v-model="filters.search"
          class="search-input"
          placeholder="Search sub-categories by name..."
          @input="debounceSearch"
        >
        <span v-if="filters.search" class="clear-search" @click="filters.search = ''; applyFilters()">
          <i class="fas fa-times"></i>
        </span>
      </div>

      <div class="filter-controls">
        <div class="filter-group">
          <button
            class="filter-btn"
            :class="{ active: filters.featured === '' && filters.popular === '' }"
            @click="filters.featured = ''; filters.popular = ''; applyFilters()"
          >
            <i class="fas fa-th-large"></i>
            All
          </button>
          <button
            class="filter-btn"
            :class="{ active: filters.featured === '1' }"
            @click="filters.featured = filters.featured === '1' ? '' : '1'; applyFilters()"
          >
            <i class="fas fa-star"></i>
            Featured
          </button>
          <button
            class="filter-btn"
            :class="{ active: filters.popular === '1' }"
            @click="filters.popular = filters.popular === '1' ? '' : '1'; applyFilters()"
          >
            <i class="fas fa-fire"></i>
            Popular
          </button>
        </div>

        <select v-model="filters.category_id" class="role-select" @change="applyFilters">
          <option value="">All Categories</option>
          <option v-for="category in parentCategories" :key="category.id" :value="category.id">
            {{ category.name.en }}
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
        Showing {{ subcategories.data.length }} of {{ subcategories.total }} sub-categories
      </div>

      <button class="action-btn success" @click="openCreateModal">
        <i class="fas fa-plus-circle"></i>
        Add Sub-Category
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="loader-advanced"></div>
      <p>Loading sub-categories...</p>
    </div>

    <!-- Sub-Categories Grid View -->
    <div v-else-if="viewMode === 'grid'" class="items-grid">
      <div
        v-for="subcategory in subcategories.data"
        :key="subcategory.id"
        class="item-card"
      >
        <div class="card-header-custom">
          <div class="item-image-wrapper">
            <img
              :src="subcategory.image_url ? `/storage/${subcategory.image_url}` : '/images/placeholder-category.png'"
              :alt="subcategory.name.en"
              class="item-image"
              @error="handleImageError"
            >
            <div class="overlay-badges">
              <span v-if="subcategory.is_featured" class="status-indicator featured">
                <i class="fas fa-star"></i>
              </span>
              <span v-if="subcategory.is_popular" class="status-indicator popular">
                <i class="fas fa-fire"></i>
              </span>
            </div>
          </div>
          <div class="card-menu">
            <button class="menu-btn" @click.stop="toggleMenu(subcategory.id)">
              <i class="fas fa-ellipsis-v"></i>
            </button>
            <div v-if="activeMenu === subcategory.id" class="dropdown-menu">
              <button @click="handleToggleFeatured(subcategory)" class="menu-item">
                <i class="fas fa-star"></i>
                {{ subcategory.is_featured ? 'Remove Featured' : 'Set Featured' }}
              </button>
              <button @click="handleTogglePopular(subcategory)" class="menu-item">
                <i class="fas fa-fire"></i>
                {{ subcategory.is_popular ? 'Remove Popular' : 'Set Popular' }}
              </button>
              <button @click="openEditModal(subcategory)" class="menu-item">
                <i class="fas fa-edit"></i>
                Edit
              </button>
              <button @click="handleDelete(subcategory)" class="menu-item danger">
                <i class="fas fa-trash"></i>
                Delete
              </button>
            </div>
          </div>
        </div>

        <div class="card-body-custom">
          <h3 class="item-name">{{ subcategory.name.en }}</h3>
          <p class="item-subtitle">{{ subcategory.name.ar }}</p>

          <div class="item-meta">
            <div class="meta-item">
              <i class="fas fa-folder"></i>
              <span>{{ subcategory.category?.name?.en || 'No Category' }}</span>
            </div>
          </div>

          <div class="item-badges">
            <span v-if="subcategory.is_featured" class="role-badge badge-warning">
              <i class="fas fa-star"></i> Featured
            </span>
            <span v-if="subcategory.is_popular" class="role-badge badge-danger">
              <i class="fas fa-fire"></i> Popular
            </span>
          </div>

          <div class="spacer"></div>

          <div class="item-stats">
            <div class="stat-item">
              <i class="fas fa-file-alt"></i>
              <span>{{ subcategory.service_posts_count || 0 }} Posts</span>
            </div>
          </div>
        </div>

        <div class="card-footer-custom">
          <button
            @click="handleToggleFeatured(subcategory)"
            class="action-btn"
            :class="subcategory.is_featured ? 'warning' : 'secondary'"
          >
            <i class="fas fa-star"></i>
            {{ subcategory.is_featured ? 'Featured' : 'Feature' }}
          </button>
          <button @click="openEditModal(subcategory)" class="action-btn primary">
            <i class="fas fa-edit"></i>
            Edit
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="subcategories.data.length === 0" class="empty-state-advanced">
        <div class="empty-icon">
          <i class="fas fa-folder-open"></i>
        </div>
        <h3>No Sub-Categories Found</h3>
        <p>Try adjusting your filters or create a new sub-category</p>
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
            <th>Name</th>
            <th>Category</th>
            <th>Status</th>
            <th style="width: 100px;">Posts</th>
            <th style="width: 180px; text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="subcategory in subcategories.data" :key="subcategory.id">
            <!-- Image -->
            <td>
              <img
                :src="subcategory.image_url ? `/storage/${subcategory.image_url}` : '/images/placeholder-category.png'"
                :alt="subcategory.name.en"
                class="table-avatar"
                @error="handleImageError"
              >
            </td>

            <!-- Name -->
            <td>
              <div class="table-name">{{ subcategory.name.en }}</div>
              <div class="table-subtitle">{{ subcategory.name.ar }}</div>
            </td>

            <!-- Category -->
            <td>
              <div class="table-subtitle">
                <i class="fas fa-folder"></i> {{ subcategory.category?.name?.en || 'No Category' }}
              </div>
            </td>

            <!-- Status -->
            <td>
              <span v-if="subcategory.is_featured" class="table-badge badge-warning">
                <i class="fas fa-star"></i> Featured
              </span>
              <span v-if="subcategory.is_popular" class="table-badge badge-danger">
                <i class="fas fa-fire"></i> Popular
              </span>
              <span v-if="!subcategory.is_featured && !subcategory.is_popular" class="table-badge badge-secondary">
                Standard
              </span>
            </td>

            <!-- Posts -->
            <td>
              <div class="table-meta">
                <span title="Posts">
                  <i class="fas fa-file-alt"></i> {{ subcategory.service_posts_count || 0 }}
                </span>
              </div>
            </td>

            <!-- Actions -->
            <td>
              <div class="table-actions">
                <button
                  @click="handleToggleFeatured(subcategory)"
                  class="table-action-btn"
                  :class="subcategory.is_featured ? 'btn-featured-active' : 'btn-featured'"
                  title="Toggle Featured"
                >
                  <i class="fas fa-star"></i>
                </button>
                <button
                  @click="handleTogglePopular(subcategory)"
                  class="table-action-btn"
                  :class="subcategory.is_popular ? 'btn-popular-active' : 'btn-popular'"
                  title="Toggle Popular"
                >
                  <i class="fas fa-fire"></i>
                </button>
                <button
                  @click="openEditModal(subcategory)"
                  class="table-action-btn btn-edit"
                  title="Edit"
                >
                  <i class="fas fa-edit"></i>
                </button>
                <button
                  @click="handleDelete(subcategory)"
                  class="table-action-btn btn-delete"
                  title="Delete"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>

          <!-- Empty State Row -->
          <tr v-if="subcategories.data.length === 0">
            <td colspan="6" class="table-empty-state">
              <i class="fas fa-folder-open"></i>
              <h3>No Sub-Categories Found</h3>
              <p>Try adjusting your filters or create a new sub-category</p>
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
    <div v-if="subcategories.data.length > 0" class="pagination-advanced">
      <button
        class="page-btn"
        :disabled="subcategories.current_page === 1"
        @click="changePage(subcategories.current_page - 1)"
      >
        <i class="fas fa-chevron-left"></i>
        Previous
      </button>

      <div class="page-numbers">
        <button
          v-for="page in paginationPages"
          :key="page"
          class="page-number"
          :class="{ active: page === subcategories.current_page }"
          @click="changePage(page)"
        >
          {{ page }}
        </button>
      </div>

      <button
        class="page-btn"
        :disabled="subcategories.current_page === subcategories.last_page"
        @click="changePage(subcategories.current_page + 1)"
      >
        Next
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>

    <!-- Form Modal -->
    <SubCategoryFormModal
      v-if="showModal"
      :subcategory="selectedSubCategory"
      :mode="modalMode"
      @close="closeModal"
      @saved="handleSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useSubCategories } from '../../../composables/useSubCategories'
import SubCategoryFormModal from '../../../components/admin/subcategories/SubCategoryFormModal.vue'

const { subcategories, loading, fetchSubCategories, deleteSubCategory, toggleFeatured, togglePopular } = useSubCategories()

const filters = ref({
  search: '',
  category_id: '',
  featured: '',
  popular: '',
  page: 1
})

const parentCategories = ref([])
const showModal = ref(false)
const modalMode = ref('create')
const selectedSubCategory = ref(null)
const viewMode = ref('list')
const activeMenu = ref(null)
let searchTimeout = null

onMounted(async () => {
  await loadSubCategories()
  await loadParentCategories()
  document.addEventListener('click', closeMenus)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', closeMenus)
})

const loadSubCategories = async () => {
  await fetchSubCategories(filters.value)
}

const loadParentCategories = async () => {
  try {
    const response = await fetch('/api/admin/categories?per_page=1000')
    const data = await response.json()
    parentCategories.value = data.data || []
  } catch (error) {
    console.error('Error loading parent categories:', error)
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
}

const applyFilters = () => {
  filters.value.page = 1
  loadSubCategories()
}

const resetFilters = () => {
  filters.value.search = ''
  filters.value.category_id = ''
  filters.value.featured = ''
  filters.value.popular = ''
  filters.value.page = 1
  loadSubCategories()
}

const changePage = (page) => {
  filters.value.page = page
  loadSubCategories()
}

const paginationPages = computed(() => {
  const pages = []
  const currentPage = subcategories.value.current_page
  const lastPage = subcategories.value.last_page

  let startPage = Math.max(1, currentPage - 2)
  let endPage = Math.min(lastPage, currentPage + 2)

  for (let i = startPage; i <= endPage; i++) {
    pages.push(i)
  }

  return pages
})

const toggleMenu = (id) => {
  activeMenu.value = activeMenu.value === id ? null : id
}

const closeMenus = () => {
  activeMenu.value = null
}

const openCreateModal = () => {
  selectedSubCategory.value = null
  modalMode.value = 'create'
  showModal.value = true
}

const openEditModal = (subcategory) => {
  closeMenus()
  selectedSubCategory.value = subcategory
  modalMode.value = 'edit'
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedSubCategory.value = null
}

const handleSaved = () => {
  closeModal()
  loadSubCategories()
}

const handleToggleFeatured = async (subcategory) => {
  closeMenus()
  try {
    await toggleFeatured(subcategory.id)
    await loadSubCategories()
  } catch (error) {
    alert('Failed to toggle featured status')
  }
}

const handleTogglePopular = async (subcategory) => {
  closeMenus()
  try {
    await togglePopular(subcategory.id)
    await loadSubCategories()
  } catch (error) {
    alert('Failed to toggle popular status')
  }
}

const handleDelete = async (subcategory) => {
  closeMenus()
  if (!confirm(`Are you sure you want to delete "${subcategory.name.en}"?`)) {
    return
  }

  try {
    await deleteSubCategory(subcategory.id)
    await loadSubCategories()
  } catch (error) {
    alert(error.message || 'Failed to delete sub-category')
  }
}

const handleImageError = (event) => {
  event.target.src = '/images/placeholder-category.png'
}
</script>

<style scoped>
.subcategories-management-advanced {
  padding: 0;
  background: #f5f7fa;
  min-height: 100vh;
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

.action-btn.warning {
  background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
  color: #212529;
}

.action-btn.secondary {
  background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
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

/* Items Grid */
.items-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.item-card {
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

.item-card:hover {
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

.item-image-wrapper {
  position: relative;
  width: 100px;
  height: 100px;
}

.item-image {
  width: 100px;
  height: 100px;
  border-radius: 16px;
  border: 4px solid white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  object-fit: cover;
}

.overlay-badges {
  position: absolute;
  top: -8px;
  right: -8px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.status-indicator {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 3px solid white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  color: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.status-indicator.featured {
  background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
}

.status-indicator.popular {
  background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
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

.item-name {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0 0 0.25rem 0;
  color: #2c3e50;
}

.item-subtitle {
  color: #6c757d;
  font-size: 0.875rem;
  margin: 0 0 1rem 0;
}

.item-meta {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #6c757d;
  font-size: 0.875rem;
}

.meta-item i {
  width: 16px;
  color: #667eea;
}

.item-badges {
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
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}

.role-badge.badge-warning {
  background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
  color: #212529;
}

.role-badge.badge-danger {
  background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
  color: white;
}

.role-badge.badge-primary {
  background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
  color: white;
}

.spacer {
  flex: 1;
}

.item-stats {
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

/* Modern Table */
.modern-table-container {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
}

.modern-table {
  width: 100%;
  border-collapse: collapse;
}

.modern-table thead {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.modern-table th {
  padding: 1rem 1.5rem;
  text-align: left;
  font-weight: 600;
  color: #495057;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.modern-table td {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e9ecef;
  vertical-align: middle;
}

.modern-table tbody tr:hover {
  background: #f8f9fa;
}

.table-avatar {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  object-fit: cover;
}

.table-name {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.25rem;
}

.table-subtitle {
  color: #6c757d;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.table-subtitle i {
  color: #adb5bd;
}

.table-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  margin-right: 0.5rem;
}

.table-badge.badge-warning {
  background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
  color: #212529;
}

.table-badge.badge-danger {
  background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
  color: white;
}

.table-badge.badge-secondary {
  background: #e9ecef;
  color: #6c757d;
}

.table-meta {
  display: flex;
  gap: 1rem;
  color: #6c757d;
  font-size: 0.875rem;
}

.table-meta i {
  color: #667eea;
  margin-right: 0.25rem;
}

.table-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

.table-action-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  color: white;
}

.table-action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.table-action-btn.btn-featured {
  background: #e9ecef;
  color: #6c757d;
}

.table-action-btn.btn-featured-active {
  background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
  color: #212529;
}

.table-action-btn.btn-popular {
  background: #e9ecef;
  color: #6c757d;
}

.table-action-btn.btn-popular-active {
  background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
}

.table-action-btn.btn-edit {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.table-action-btn.btn-delete {
  background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
}

.table-empty-state {
  text-align: center;
  padding: 4rem 2rem;
}

.table-empty-state i {
  font-size: 3rem;
  color: #adb5bd;
  margin-bottom: 1rem;
  display: block;
}

.table-empty-state h3 {
  font-size: 1.25rem;
  color: #2c3e50;
  margin: 0 0 0.5rem 0;
}

.table-empty-state p {
  color: #6c757d;
  margin: 0 0 1.5rem 0;
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

  .items-grid {
    grid-template-columns: 1fr;
  }

  .view-controls {
    flex-direction: column;
    align-items: stretch;
  }

  .view-toggle {
    justify-content: center;
  }

  .results-info {
    text-align: center;
  }

  .pagination-advanced {
    flex-direction: column;
  }
}
</style>
