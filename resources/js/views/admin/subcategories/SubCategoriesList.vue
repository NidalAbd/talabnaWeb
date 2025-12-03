<template>
  <div class="subcategories-modern">
    <!-- Stats Cards -->
    <div class="stats-grid mb-4">
      <div class="stat-card blue">
        <div class="stat-icon">
          <i class="fas fa-layer-group"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ subcategories.total }}</h3>
          <p class="stat-label">Total Subcategories</p>
        </div>
      </div>
      <div class="stat-card green">
        <div class="stat-icon">
          <i class="fas fa-folder"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-value">{{ parentCategories.length }}</h3>
          <p class="stat-label">Parent Categories</p>
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
          placeholder="Search sub-categories by name..."
          @input="debounceSearch"
        >
        <span v-if="filters.search" class="clear-search" @click="filters.search = ''; applyFilters()">
          <i class="fas fa-times"></i>
        </span>
      </div>

      <div class="filter-group">
        <div class="status-filters">
          <button
            class="filter-btn"
            :class="{ active: filters.featured === '' && filters.popular === '' }"
            @click="filters.featured = ''; filters.popular = ''; applyFilters()"
          >
            <i class="fas fa-th-large"></i> All
          </button>
          <button
            class="filter-btn"
            :class="{ active: filters.featured === '1' }"
            @click="filters.featured = filters.featured === '1' ? '' : '1'; applyFilters()"
          >
            <i class="fas fa-star"></i> Featured
          </button>
          <button
            class="filter-btn"
            :class="{ active: filters.popular === '1' }"
            @click="filters.popular = filters.popular === '1' ? '' : '1'; applyFilters()"
          >
            <i class="fas fa-fire"></i> Popular
          </button>
        </div>

        <select v-model="filters.category_id" class="filter-select" @change="applyFilters">
          <option value="">All Categories</option>
          <option v-for="category in parentCategories" :key="category.id" :value="category.id">
            {{ category.name.en }}
          </option>
        </select>
      </div>

      <div class="action-buttons">
        <button class="action-btn secondary" @click="resetFilters">
          <i class="fas fa-redo"></i>
        </button>
        <button class="action-btn primary" @click="openCreateModal">
          <i class="fas fa-plus"></i> Add Subcategory
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
        Showing {{ subcategories.data.length }} of {{ subcategories.total }} sub-categories
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading sub-categories...</p>
    </div>

    <!-- Grid View -->
    <div v-else-if="viewMode === 'grid'" class="subcategories-grid">
      <div
        v-for="subcategory in subcategories.data"
        :key="subcategory.id"
        class="subcategory-card"
      >
        <div class="card-header-custom">
          <div class="subcategory-image-wrapper">
            <img
              :src="getImageUrl(subcategory.image_url)"
              :alt="subcategory.name.en"
              class="subcategory-image"
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
            <div v-if="activeMenu === subcategory.id" class="dropdown-menu" @click.stop>
              <button @click="handleToggleFeatured(subcategory)" class="menu-item">
                <i class="fas fa-star"></i>
                {{ subcategory.is_featured ? 'Remove Featured' : 'Set Featured' }}
              </button>
              <button @click="handleTogglePopular(subcategory)" class="menu-item">
                <i class="fas fa-fire"></i>
                {{ subcategory.is_popular ? 'Remove Popular' : 'Set Popular' }}
              </button>
              <button @click="openEditModal(subcategory)" class="menu-item">
                <i class="fas fa-edit"></i> Edit
              </button>
              <button @click="handleDelete(subcategory)" class="menu-item danger">
                <i class="fas fa-trash"></i> Delete
              </button>
            </div>
          </div>
        </div>

        <div class="card-body-custom">
          <h3 class="subcategory-name">{{ subcategory.name.en }}</h3>
          <p class="subcategory-name-ar">{{ subcategory.name.ar }}</p>

          <div class="parent-category">
            <i class="fas fa-folder"></i>
            <span>{{ subcategory.category?.name?.en || 'No Category' }}</span>
          </div>

          <div class="subcategory-badges">
            <span v-if="subcategory.is_featured" class="badge warning">
              <i class="fas fa-star"></i> Featured
            </span>
            <span v-if="subcategory.is_popular" class="badge fire">
              <i class="fas fa-fire"></i> Popular
            </span>
            <span v-if="!subcategory.is_featured && !subcategory.is_popular" class="badge secondary">
              Standard
            </span>
          </div>

          <div class="spacer"></div>

          <div class="subcategory-stats">
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
            <i class="fas fa-edit"></i> Edit
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="subcategories.data.length === 0" class="empty-state-grid">
        <i class="fas fa-layer-group"></i>
        <h3>No Sub-Categories Found</h3>
        <p>Try adjusting your filters or create a new sub-category</p>
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
            <th>Name</th>
            <th>Category</th>
            <th>Status</th>
            <th>Posts</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody v-if="subcategories.data.length > 0">
          <tr v-for="subcategory in subcategories.data" :key="subcategory.id">
            <td>
              <div class="media-cell">
                <img
                  :src="getImageUrl(subcategory.image_url)"
                  :alt="subcategory.name.en"
                  class="table-image"
                  @error="handleImageError"
                >
              </div>
            </td>
            <td>
              <div class="name-cell">
                <strong>{{ subcategory.name.en }}</strong>
                <span class="name-ar">{{ subcategory.name.ar }}</span>
              </div>
            </td>
            <td>
              <span class="badge info">
                <i class="fas fa-folder"></i> {{ subcategory.category?.name?.en || 'No Category' }}
              </span>
            </td>
            <td>
              <div class="status-badges">
                <span v-if="subcategory.is_featured" class="badge warning">
                  <i class="fas fa-star"></i> Featured
                </span>
                <span v-if="subcategory.is_popular" class="badge fire">
                  <i class="fas fa-fire"></i> Popular
                </span>
                <span v-if="!subcategory.is_featured && !subcategory.is_popular" class="badge secondary">
                  Standard
                </span>
              </div>
            </td>
            <td>
              <span class="badge info">
                <i class="fas fa-file-alt"></i> {{ subcategory.service_posts_count || 0 }}
              </span>
            </td>
            <td>
              <div class="table-actions">
                <button
                  @click="handleToggleFeatured(subcategory)"
                  class="action-btn-small"
                  :class="subcategory.is_featured ? 'featured-active' : 'featured'"
                  title="Toggle Featured"
                >
                  <i class="fas fa-star"></i>
                </button>
                <button
                  @click="handleTogglePopular(subcategory)"
                  class="action-btn-small"
                  :class="subcategory.is_popular ? 'popular-active' : 'popular'"
                  title="Toggle Popular"
                >
                  <i class="fas fa-fire"></i>
                </button>
                <button @click="openEditModal(subcategory)" class="action-btn-small edit" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="handleDelete(subcategory)" class="action-btn-small delete" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="6" class="empty-state">
              <i class="fas fa-layer-group"></i>
              <p>No sub-categories found</p>
              <button @click="resetFilters" class="action-btn primary">
                <i class="fas fa-redo"></i> Reset Filters
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-container" v-if="subcategories.last_page > 1">
      <div class="pagination-info">
        Showing {{ subcategories.data.length }} of {{ subcategories.total }} sub-categories
      </div>
      <div class="pagination-controls">
        <button
          class="pagination-btn"
          :disabled="subcategories.current_page === 1"
          @click="changePage(subcategories.current_page - 1)"
        >
          <i class="fas fa-chevron-left"></i> Previous
        </button>
        <button
          v-for="page in paginationPages"
          :key="page"
          class="pagination-btn"
          :class="{ active: page === subcategories.current_page }"
          @click="changePage(page)"
        >
          {{ page }}
        </button>
        <button
          class="pagination-btn"
          :disabled="subcategories.current_page === subcategories.last_page"
          @click="changePage(subcategories.current_page + 1)"
        >
          Next <i class="fas fa-chevron-right"></i>
        </button>
      </div>
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

const { subcategories, stats, loading, fetchSubCategories, fetchStats, deleteSubCategory, toggleFeatured, togglePopular } = useSubCategories()

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
const placeholderImage = '/storage/countryFlag/placeholder-flag.jpg'
let searchTimeout = null

const featuredCount = computed(() => {
  return stats.value.featured
})

const popularCount = computed(() => {
  return stats.value.popular
})

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

onMounted(async () => {
  await Promise.all([loadSubCategories(), loadParentCategories(), fetchStats()])
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
    await Promise.all([
      fetchSubCategories({ ...filters.value, page: subcategories.value.current_page }),
      fetchStats()
    ])
  } catch (err) {
    alert('Failed to toggle featured status')
  }
}

const handleTogglePopular = async (subcategory) => {
  closeMenus()
  try {
    await togglePopular(subcategory.id)
    await Promise.all([
      fetchSubCategories({ ...filters.value, page: subcategories.value.current_page }),
      fetchStats()
    ])
  } catch (err) {
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

const getImageUrl = (url) => {
  if (!url) return placeholderImage
  if (url.startsWith('storage/')) return `/${url}`
  if (url.startsWith('/')) return url
  return `/storage/${url}`
}

const handleImageError = (event) => {
  event.target.src = placeholderImage
}
</script>

<style scoped>
.subcategories-modern {
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
  gap: 0.75rem;
  align-items: center;
  flex-wrap: wrap;
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

.action-btn.secondary {
  background: #6c757d;
  color: white;
}

.action-btn.warning {
  background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
  color: #333;
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

/* Subcategories Grid */
.subcategories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.subcategory-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  min-height: 380px;
}

.subcategory-card:hover {
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

.subcategory-image-wrapper {
  position: relative;
}

.subcategory-image {
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

.subcategory-name {
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0 0 0.25rem 0;
  color: #2c3e50;
}

.subcategory-name-ar {
  color: #888;
  font-size: 0.9rem;
  margin: 0 0 0.75rem 0;
}

.parent-category {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #666;
  font-size: 0.85rem;
  margin-bottom: 0.75rem;
}

.parent-category i {
  color: #667eea;
}

.subcategory-badges {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
  margin-bottom: 0.75rem;
}

.spacer {
  flex: 1;
}

.subcategory-stats {
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

  .status-filters {
    width: 100%;
    justify-content: center;
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

  .subcategories-grid {
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
