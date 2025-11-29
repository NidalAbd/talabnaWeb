<template>
  <div class="subcategories-management-advanced">
    <!-- Section Header -->
    <div class="section-header-advanced">
      <div class="header-left">
        <h1 class="section-title-advanced">
          <i class="fas fa-folder-tree"></i>
          Sub-Categories Management
        </h1>
        <p class="section-subtitle">Manage and organize sub-categories</p>
      </div>
      <button class="btn-create-advanced" @click="openCreateModal">
        <i class="fas fa-plus-circle"></i>
        Create Sub-Category
      </button>
    </div>

    <!-- Filters & Search Bar -->
    <div class="filters-bar-advanced">
      <div class="search-box-advanced">
        <i class="fas fa-search"></i>
        <input
          v-model="filters.search"
          type="text"
          placeholder="Search sub-categories..."
          @input="debounceSearch"
        >
      </div>

      <select v-model="filters.category_id" class="filter-select-advanced" @change="applyFilters">
        <option value="">All Categories</option>
        <option v-for="category in parentCategories" :key="category.id" :value="category.id">
          {{ category.name.en }}
        </option>
      </select>

      <select v-model="filters.featured" class="filter-select-advanced" @change="applyFilters">
        <option value="">All Status</option>
        <option value="1">Featured Only</option>
        <option value="0">Not Featured</option>
      </select>

      <select v-model="filters.popular" class="filter-select-advanced" @change="applyFilters">
        <option value="">All Types</option>
        <option value="1">Popular Only</option>
        <option value="0">Not Popular</option>
      </select>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state-advanced">
      <div class="loader-advanced"></div>
      <p>Loading sub-categories...</p>
    </div>

    <!-- Sub-Categories Grid -->
    <div v-else-if="subcategories.data.length > 0" class="categories-grid-advanced">
      <div
        v-for="subcategory in subcategories.data"
        :key="subcategory.id"
        class="category-card-advanced"
      >
        <!-- Category Image -->
        <div class="category-image-wrapper">
          <img
            :src="subcategory.image_url ? `/storage/${subcategory.image_url}` : '/images/placeholder-category.png'"
            :alt="subcategory.name.en"
            class="category-image"
            @error="handleImageError"
          >
          <div class="category-badges">
            <span v-if="subcategory.is_featured" class="badge-advanced badge-warning">
              <i class="fas fa-star"></i> Featured
            </span>
            <span v-if="subcategory.is_popular" class="badge-advanced badge-danger">
              <i class="fas fa-fire"></i> Popular
            </span>
          </div>
        </div>

        <!-- Category Info -->
        <div class="category-info">
          <h3 class="category-name">{{ subcategory.name.en }}</h3>
          <p class="category-name-ar">{{ subcategory.name.ar }}</p>

          <div class="category-meta">
            <span class="meta-item">
              <i class="fas fa-folder"></i>
              {{ subcategory.category?.name?.en || 'No Category' }}
            </span>
            <span class="meta-item">
              <i class="fas fa-file-alt"></i>
              {{ subcategory.service_posts_count || 0 }} Posts
            </span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="category-actions">
          <button
            class="action-btn-modern featured"
            :class="{ active: subcategory.is_featured }"
            @click="handleToggleFeatured(subcategory)"
            title="Toggle Featured"
          >
            <i class="fas fa-star"></i>
          </button>

          <button
            class="action-btn-modern popular"
            :class="{ active: subcategory.is_popular }"
            @click="handleTogglePopular(subcategory)"
            title="Toggle Popular"
          >
            <i class="fas fa-fire"></i>
          </button>

          <div class="dropdown-modern">
            <button class="action-btn-modern more">
              <i class="fas fa-ellipsis-v"></i>
            </button>
            <div class="dropdown-menu-modern">
              <button @click="openEditModal(subcategory)">
                <i class="fas fa-edit"></i> Edit
              </button>
              <button @click="handleDelete(subcategory)" class="delete-btn">
                <i class="fas fa-trash"></i> Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="empty-state-advanced">
      <i class="fas fa-folder-open"></i>
      <h3>No Sub-Categories Found</h3>
      <p>Create your first sub-category to get started</p>
      <button class="btn-create-advanced" @click="openCreateModal">
        <i class="fas fa-plus-circle"></i>
        Create Sub-Category
      </button>
    </div>

    <!-- Pagination -->
    <div v-if="subcategories.last_page > 1" class="pagination-advanced">
      <button
        class="pagination-btn"
        :disabled="subcategories.current_page === 1"
        @click="changePage(subcategories.current_page - 1)"
      >
        <i class="fas fa-chevron-left"></i>
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
import { ref, computed, onMounted } from 'vue'
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
let searchTimeout = null

onMounted(async () => {
  await loadSubCategories()
  await loadParentCategories()
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
  }, 500)
}

const applyFilters = () => {
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

const openCreateModal = () => {
  selectedSubCategory.value = null
  modalMode.value = 'create'
  showModal.value = true
}

const openEditModal = (subcategory) => {
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
  try {
    await toggleFeatured(subcategory.id)
    await loadSubCategories()
  } catch (error) {
    alert('Failed to toggle featured status')
  }
}

const handleTogglePopular = async (subcategory) => {
  try {
    await togglePopular(subcategory.id)
    await loadSubCategories()
  } catch (error) {
    alert('Failed to toggle popular status')
  }
}

const handleDelete = async (subcategory) => {
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

