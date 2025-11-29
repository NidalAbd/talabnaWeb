<template>
  <div class="categories-management-advanced">
    <!-- Section Header -->
    <div class="section-header mb-4">
      <div class="header-content">
        <h1 class="section-title">
          <i class="fas fa-folder"></i>
          Categories Management
        </h1>
        <p class="section-subtitle">Manage main categories and their properties</p>
      </div>
      <div class="header-actions">
        <button @click="showCreateModal = true" class="action-btn success">
          <i class="fas fa-plus-circle"></i>
          Create Category
        </button>
      </div>
    </div>

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
        <select v-model="filters.status" class="filter-select">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="suspended">Suspended</option>
        </select>

        <select v-model="filters.featured" class="filter-select">
          <option value="all">All Categories</option>
          <option value="true">Featured Only</option>
          <option value="false">Not Featured</option>
        </select>

        <select v-model="filters.popular" class="filter-select">
          <option value="all">All Categories</option>
          <option value="true">Popular Only</option>
          <option value="false">Not Popular</option>
        </select>

        <div class="sort-group">
          <select v-model="filters.sort_by" class="sort-select">
            <option value="id">Sort by ID</option>
            <option value="name">Sort by Name</option>
            <option value="created_at">Sort by Date</option>
            <option value="posts_count">Sort by Posts</option>
            <option value="subcategories_count">Sort by Subcategories</option>
          </select>
          <button
            class="sort-direction-btn"
            @click="toggleSortDirection"
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
      <div class="view-info">
        <span class="results-count">
          Showing {{ categories.data.length }} of {{ categories.total }} categories
        </span>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="loader-advanced"></div>
      <p>Loading categories...</p>
    </div>

    <!-- Categories Grid -->
    <div v-else class="categories-grid">
      <div
        v-for="category in categories.data"
        :key="category.id"
        class="category-card"
        :class="getCategoryCardClass(category)"
      >
        <div class="card-header-custom">
          <div class="category-image">
            <img
              v-if="category.image_url"
              :src="category.image_url"
              :alt="category.name.en"
              @error="handleImageError"
            >
            <div v-else class="image-placeholder">
              <i class="fas fa-image"></i>
            </div>
          </div>
          <div class="category-menu">
            <button class="menu-btn" @click="toggleMenu(category.id)">
              <i class="fas fa-ellipsis-v"></i>
            </button>
            <div v-if="activeMenu === category.id" class="dropdown-menu">
              <button @click="viewCategory(category)" class="menu-item">
                <i class="fas fa-eye"></i>
                View Details
              </button>
              <button @click="editCategory(category)" class="menu-item">
                <i class="fas fa-edit"></i>
                Edit Category
              </button>
              <button @click="handleToggleStatus(category)" class="menu-item">
                <i :class="category.is_suspended ? 'fas fa-check-circle' : 'fas fa-ban'"></i>
                {{ category.is_suspended ? 'Activate' : 'Suspend' }}
              </button>
              <button @click="handleToggleFeatured(category)" class="menu-item">
                <i :class="category.is_featured ? 'far fa-star' : 'fas fa-star'"></i>
                {{ category.is_featured ? 'Unmark Featured' : 'Mark Featured' }}
              </button>
              <button @click="handleTogglePopular(category)" class="menu-item">
                <i :class="category.is_popular ? 'fas fa-fire-alt' : 'far fa-fire-alt'"></i>
                {{ category.is_popular ? 'Unmark Popular' : 'Mark Popular' }}
              </button>
              <button @click="handleDelete(category)" class="menu-item danger">
                <i class="fas fa-trash"></i>
                Delete
              </button>
            </div>
          </div>
        </div>

        <div class="card-body-custom">
          <div class="category-header-info">
            <h3 class="category-name">{{ category.name.en }}</h3>
            <span class="category-name-ar">{{ category.name.ar }}</span>
            <span class="category-id">#{{ category.id }}</span>
          </div>

          <div class="category-badges">
            <span
              class="status-badge"
              :class="category.is_suspended ? 'badge-danger' : 'badge-success'"
            >
              <i :class="category.is_suspended ? 'fas fa-ban' : 'fas fa-check-circle'"></i>
              {{ category.is_suspended ? 'Suspended' : 'Active' }}
            </span>
            <span v-if="category.is_featured" class="badge badge-warning">
              <i class="fas fa-star"></i>
              Featured
            </span>
            <span v-if="category.is_popular" class="badge badge-danger">
              <i class="fas fa-fire"></i>
              Popular
            </span>
          </div>

          <div class="spacer"></div>

          <div class="category-stats">
            <div class="stat-item">
              <i class="fas fa-folder-open text-info"></i>
              <span>{{ category.sub_categories_count }} Subcategories</span>
            </div>
            <div class="stat-item">
              <i class="fas fa-clipboard-list text-primary"></i>
              <span>{{ category.service_posts_count }} Posts</span>
            </div>
          </div>
        </div>

        <div class="card-footer-custom">
          <button @click="viewCategory(category)" class="action-btn outline-primary flex-1">
            <i class="fas fa-eye"></i>
            View
          </button>
          <button @click="editCategory(category)" class="action-btn outline-warning flex-1">
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
        <p>Try adjusting your search criteria or create a new category</p>
        <button @click="showCreateModal = true" class="action-btn primary">
          <i class="fas fa-plus-circle"></i>
          Create First Category
        </button>
      </div>
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
import { ref, onMounted, watch, reactive, computed, onBeforeUnmount } from 'vue'
import { useCategories } from '../../../composables/useCategories'
import CategoryFormModal from '../../../components/admin/categories/CategoryFormModal.vue'

const { categories, loading, fetchCategories, deleteCategory, toggleStatus, toggleFeatured, togglePopular } = useCategories()

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

const toggleSortDirection = () => {
  filters.sort_direction = filters.sort_direction === 'asc' ? 'desc' : 'asc'
}

const getCategoryCardClass = (category) => {
  if (category.is_suspended) return 'card-suspended'
  if (category.is_featured) return 'card-featured'
  return ''
}

const toggleMenu = (categoryId) => {
  activeMenu.value = activeMenu.value === categoryId ? null : categoryId
}

const closeMenus = () => {
  activeMenu.value = null
}

const viewCategory = (category) => {
  window.location.href = `/categories/${category.id}`
}

const editCategory = (category) => {
  selectedCategory.value = category
  showEditModal.value = true
  closeMenus()
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
    alert(error.message || 'Failed to delete category')
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
  event.target.style.display = 'none'
  event.target.parentElement.innerHTML = '<div class="image-placeholder"><i class="fas fa-image"></i></div>'
}
</script>

<style scoped>
.categories-management-advanced {
  padding: 0;
  background: #f5f7fa;
  min-height: 100vh;
}

/* Categories Grid */
.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}

.category-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  display: flex;
  flex-direction: column;
  min-height: 380px;
}

.category-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.category-card.card-featured {
  border-top: 4px solid #ffc107;
}

.category-card.card-suspended {
  opacity: 0.7;
  border-top: 4px solid #dc3545;
}

.card-header-custom {
  position: relative;
  padding: 0;
}

.category-image {
  width: 100%;
  height: 160px;
  overflow: hidden;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.category-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-placeholder {
  font-size: 3rem;
  color: #adb5bd;
}

.category-menu {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
}

.menu-btn {
  background: white;
  border: none;
  width: 32px;
  height: 32px;
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
  top: 40px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
  min-width: 200px;
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
  font-size: 0.875rem;
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
  padding: 1rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.category-header-info {
  margin-bottom: 0.75rem;
}

.category-name {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0 0 0.25rem 0;
  color: #2c3e50;
}

.category-name-ar {
  display: block;
  font-size: 0.9rem;
  color: #6c757d;
  margin-bottom: 0.25rem;
}

.category-id {
  color: #adb5bd;
  font-size: 0.75rem;
  font-weight: 600;
}

.category-badges {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin-bottom: 0.75rem;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.badge-success {
  background: #d4edda;
  color: #155724;
}

.badge-danger {
  background: #f8d7da;
  color: #721c24;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.badge-warning {
  background: #fff3cd;
  color: #856404;
}

.spacer {
  flex: 1;
}

.category-stats {
  padding-top: 0.75rem;
  margin-top: 0.75rem;
  border-top: 1px solid #e9ecef;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: #6c757d;
}

.card-footer-custom {
  padding: 0.75rem;
  background: #f8f9fa;
  display: flex;
  gap: 0.5rem;
  border-top: 1px solid #e9ecef;
}

/* Responsive */
@media (max-width: 768px) {
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
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

  .categories-grid {
    grid-template-columns: 1fr;
  }
}
</style>
