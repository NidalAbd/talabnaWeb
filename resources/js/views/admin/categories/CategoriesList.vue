<template>
  <div class="categories-modern">
    <!-- Stats Cards -->
    <div class="stats-grid mb-4">
      <div class="stat-card blue">
        <div class="stat-icon">
          <i class="fas fa-folder"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ categories.total }}</h3>
          <p class="stat-label">Total Categories</p>
        </div>
      </div>
      <div class="stat-card green">
        <div class="stat-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ activeCount }}</h3>
          <p class="stat-label">Active</p>
        </div>
      </div>
      <div class="stat-card orange">
        <div class="stat-icon">
          <i class="fas fa-star"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ featuredCount }}</h3>
          <p class="stat-label">Featured</p>
        </div>
      </div>
      <div class="stat-card purple">
        <div class="stat-icon">
          <i class="fas fa-fire"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ popularCount }}</h3>
          <p class="stat-label">Popular</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
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

      <div class="filter-group">
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
      </div>

      <div class="action-buttons">
        <button class="action-btn secondary" @click="resetFilters">
          <i class="fas fa-redo"></i>
        </button>
        <button class="action-btn primary" @click="showCreateModal = true">
          <i class="fas fa-plus"></i> Add Category
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
        Showing {{ categories.data.length }} of {{ categories.total }} categories
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading categories...</p>
    </div>

    <!-- Grid View -->
    <div v-else-if="viewMode === 'grid'" class="categories-grid">
      <div
        v-for="category in categories.data"
        :key="category.id"
        class="category-card"
        :class="{ 'card-suspended': category.is_suspended, 'card-featured': category.is_featured }"
      >
        <div class="card-header-custom">
          <div class="category-image-wrapper">
            <img
              :src="category.image_url || placeholderImage"
              :alt="category.name.en"
              class="category-image"
              @error="handleImageError"
            >
            <div class="overlay-badges">
              <span v-if="category.is_featured" class="status-indicator featured">
                <i class="fas fa-star"></i>
              </span>
              <span v-if="category.is_popular" class="status-indicator popular">
                <i class="fas fa-fire"></i>
              </span>
            </div>
          </div>
          <div class="card-menu">
            <button class="menu-btn" @click.stop="toggleMenu(category.id)">
              <i class="fas fa-ellipsis-v"></i>
            </button>
            <div v-if="activeMenu === category.id" class="dropdown-menu" @click.stop>
              <button @click="editCategory(category)" class="menu-item">
                <i class="fas fa-edit"></i> Edit Category
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
                <i class="fas fa-trash"></i> Delete
              </button>
            </div>
          </div>
        </div>

        <div class="card-body-custom">
          <h3 class="category-name">{{ category.name.en }}</h3>
          <p class="category-name-ar">{{ category.name.ar }}</p>

          <div class="category-badges">
            <span class="badge" :class="category.is_suspended ? 'danger' : 'success'">
              <i :class="category.is_suspended ? 'fas fa-ban' : 'fas fa-check-circle'"></i>
              {{ category.is_suspended ? 'Suspended' : 'Active' }}
            </span>
            <span v-if="category.is_featured" class="badge warning">
              <i class="fas fa-star"></i> Featured
            </span>
            <span v-if="category.is_popular" class="badge fire">
              <i class="fas fa-fire"></i> Popular
            </span>
          </div>

          <div class="spacer"></div>

          <div class="category-stats">
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
            <i class="fas fa-edit"></i> Edit
          </button>
          <a :href="`/admin/subcategories?category=${category.id}`" class="action-btn info">
            <i class="fas fa-layer-group"></i> Subs
          </a>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="categories.data.length === 0" class="empty-state-grid">
        <i class="fas fa-folder"></i>
        <h3>No Categories Found</h3>
        <p>Try adjusting your filters or create a new category</p>
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
            <th style="width: 80px;">Image</th>
            <th>Category Name</th>
            <th>Status</th>
            <th>Stats</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody v-if="categories.data.length > 0">
          <tr v-for="category in categories.data" :key="category.id" :class="{ 'row-suspended': category.is_suspended }">
            <td>
              <div class="media-cell">
                <img
                  :src="category.image_url || placeholderImage"
                  :alt="category.name.en"
                  class="table-image"
                  @error="handleImageError"
                >
              </div>
            </td>
            <td>
              <div class="name-cell">
                <strong>{{ category.name.en }}</strong>
                <span class="name-ar">{{ category.name.ar }}</span>
              </div>
            </td>
            <td>
              <div class="status-badges">
                <span class="badge" :class="category.is_suspended ? 'danger' : 'success'">
                  <i :class="category.is_suspended ? 'fas fa-ban' : 'fas fa-check-circle'"></i>
                  {{ category.is_suspended ? 'Suspended' : 'Active' }}
                </span>
                <span v-if="category.is_featured" class="badge warning">
                  <i class="fas fa-star"></i>
                </span>
                <span v-if="category.is_popular" class="badge fire">
                  <i class="fas fa-fire"></i>
                </span>
              </div>
            </td>
            <td>
              <div class="stats-cell">
                <span class="badge info">
                  <i class="fas fa-layer-group"></i> {{ category.subcategories_count || 0 }}
                </span>
                <span class="badge secondary">
                  <i class="fas fa-file-alt"></i> {{ category.posts_count || 0 }}
                </span>
              </div>
            </td>
            <td>
              <div class="table-actions">
                <button @click="editCategory(category)" class="action-btn-small edit" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button
                  @click="handleToggleStatus(category)"
                  class="action-btn-small"
                  :class="category.is_suspended ? 'unban' : 'ban'"
                  :title="category.is_suspended ? 'Activate' : 'Suspend'"
                >
                  <i :class="category.is_suspended ? 'fas fa-check' : 'fas fa-ban'"></i>
                </button>
                <button
                  @click="handleToggleFeatured(category)"
                  class="action-btn-small"
                  :class="category.is_featured ? 'featured-active' : 'featured'"
                  title="Toggle Featured"
                >
                  <i class="fas fa-star"></i>
                </button>
                <button
                  @click="handleTogglePopular(category)"
                  class="action-btn-small"
                  :class="category.is_popular ? 'popular-active' : 'popular'"
                  title="Toggle Popular"
                >
                  <i class="fas fa-fire"></i>
                </button>
                <button @click="handleDelete(category)" class="action-btn-small delete" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="5" class="empty-state">
              <i class="fas fa-folder"></i>
              <p>No categories found</p>
              <button @click="resetFilters" class="action-btn primary">
                <i class="fas fa-redo"></i> Reset Filters
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-container" v-if="categories.last_page > 1">
      <div class="pagination-info">
        Showing {{ categories.data.length }} of {{ categories.total }} categories
      </div>
      <div class="pagination-controls">
        <button
          class="pagination-btn"
          :disabled="categories.current_page === 1"
          @click="loadCategories(categories.current_page - 1)"
        >
          <i class="fas fa-chevron-left"></i> Previous
        </button>
        <button
          v-for="page in visiblePages"
          :key="page"
          class="pagination-btn"
          :class="{ active: page === categories.current_page }"
          @click="loadCategories(page)"
        >
          {{ page }}
        </button>
        <button
          class="pagination-btn"
          :disabled="categories.current_page === categories.last_page"
          @click="loadCategories(categories.current_page + 1)"
        >
          Next <i class="fas fa-chevron-right"></i>
        </button>
      </div>
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
const placeholderImage = '/storage/countryFlag/placeholder-flag.jpg'

const filters = reactive({
  search: '',
  status: '',
  featured: 'all',
  popular: 'all',
  sort_by: 'id',
  sort_direction: 'desc',
  per_page: 15
})

const activeCount = computed(() => {
  return categories.value.data.filter(c => !c.is_suspended).length
})

const featuredCount = computed(() => {
  return categories.value.data.filter(c => c.is_featured).length
})

const popularCount = computed(() => {
  return categories.value.data.filter(c => c.is_popular).length
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

let searchTimeout = null
watch(() => filters.search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadCategories(1), 300)
})

watch(() => [filters.status, filters.featured, filters.popular], () => {
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
  event.target.src = placeholderImage
}
</script>

<style scoped>
.categories-modern {
  padding: 0;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.25rem;
}

.stat-card {
  border-radius: 16px;
  padding: 1.5rem;
  color: white;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.stat-card.blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-card.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.stat-card.orange { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); }
.stat-card.purple { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }

.stat-icon {
  width: 65px;
  height: 65px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.75rem;
}

.stat-content { flex: 1; }
.stat-value { font-size: 2rem; font-weight: 700; margin: 0; }
.stat-label { font-size: 0.9rem; opacity: 0.9; margin: 0.25rem 0 0 0; }

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
}

.filter-group {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
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

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
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

/* Categories Grid */
.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.category-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  min-height: 380px;
}

.category-card.card-suspended {
  opacity: 0.7;
  border-left: 4px solid #dc3545;
}

.category-card.card-featured {
  border-left: 4px solid #ffc107;
}

.category-card:hover {
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

.category-image-wrapper {
  position: relative;
}

.category-image {
  width: 80px;
  height: 80px;
  border-radius: 12px;
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
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 2px solid white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.6rem;
  color: white;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.status-indicator.featured {
  background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
}

.status-indicator.popular {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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

.category-name {
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0 0 0.25rem 0;
  color: #2c3e50;
}

.category-name-ar {
  color: #888;
  font-size: 0.9rem;
  margin: 0 0 0.75rem 0;
}

.category-badges {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
  margin-bottom: 0.75rem;
}

.spacer {
  flex: 1;
}

.category-stats {
  display: flex;
  gap: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #eef2f7;
  flex-wrap: wrap;
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

/* Badges */
.badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.35rem 0.7rem;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge.success { background: #e8f5e9; color: #2e7d32; }
.badge.danger { background: #ffebee; color: #c62828; }
.badge.warning { background: #fff8e1; color: #ff8f00; }
.badge.fire { background: #fce4ec; color: #c2185b; }
.badge.info { background: #e0f7fa; color: #00838f; }
.badge.secondary { background: #f5f5f5; color: #616161; }

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

.modern-table tbody tr.row-suspended {
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
.media-cell {
  display: inline-block;
}

.table-image {
  width: 55px;
  height: 55px;
  border-radius: 10px;
  object-fit: cover;
}

.name-cell {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.name-ar {
  font-size: 0.85rem;
  color: #888;
}

.status-badges {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
}

.stats-cell {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
}

/* Table Actions */
.table-actions {
  display: flex;
  gap: 0.4rem;
}

.action-btn-small {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
}

.action-btn-small.edit {
  background: #e3f2fd;
  color: #1976d2;
}

.action-btn-small.ban {
  background: #ffebee;
  color: #c62828;
}

.action-btn-small.unban {
  background: #e8f5e9;
  color: #2e7d32;
}

.action-btn-small.featured {
  background: #f5f5f5;
  color: #999;
}

.action-btn-small.featured-active {
  background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
  color: white;
}

.action-btn-small.popular {
  background: #f5f5f5;
  color: #999;
}

.action-btn-small.popular-active {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
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

  .filter-select {
    width: 100%;
  }

  .action-buttons {
    justify-content: stretch;
  }

  .action-btn {
    flex: 1;
    justify-content: center;
  }

  .categories-grid {
    grid-template-columns: 1fr;
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
