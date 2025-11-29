<template>
  <div class="subcategories-management-advanced">
    <!-- Section Header -->
    <div class="section-header-advanced">
      <div class="header-left">
        <h1 class="section-title-advanced">
          <i class="fas fa-city"></i>
          Cities Management
        </h1>
        <p class="section-subtitle">Manage cities across all countries</p>
      </div>
      <button class="btn-create-advanced" @click="openCreateModal">
        <i class="fas fa-plus-circle"></i>
        Create City
      </button>
    </div>

    <!-- Filters & Search Bar -->
    <div class="filters-bar-advanced">
      <div class="search-box-advanced">
        <i class="fas fa-search"></i>
        <input
          v-model="filters.search"
          type="text"
          placeholder="Search cities..."
          @input="debounceSearch"
        >
      </div>

      <select v-model="filters.country_id" class="filter-select-advanced" @change="applyFilters">
        <option value="">All Countries</option>
        <option v-for="country in countries" :key="country.id" :value="country.id">
          {{ country.name.en }}
        </option>
      </select>

      <select v-model="filters.sort_by" class="filter-select-advanced" @change="applyFilters">
        <option value="created_at">Sort by: Created Date</option>
        <option value="name">Sort by: Name</option>
        <option value="country">Sort by: Country</option>
      </select>

      <select v-model="filters.sort_order" class="filter-select-advanced" @change="applyFilters">
        <option value="desc">Descending</option>
        <option value="asc">Ascending</option>
      </select>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state-advanced">
      <div class="loader-advanced"></div>
      <p>Loading cities...</p>
    </div>

    <!-- Cities Grid -->
    <div v-else-if="cities.data.length > 0" class="categories-grid-advanced">
      <div
        v-for="city in cities.data"
        :key="city.id"
        class="category-card-advanced"
      >
        <!-- City Image -->
        <div class="category-image-wrapper">
          <img
            :src="city.image_url ? `/storage/${city.image_url}` : '/images/placeholder-city.png'"
            :alt="city.name.en"
            class="category-image"
            @error="handleImageError"
          >
        </div>

        <!-- City Info -->
        <div class="category-info">
          <h3 class="category-name">{{ city.name.en }}</h3>
          <p class="category-name-ar">{{ city.name.ar }}</p>

          <div class="category-meta">
            <span class="meta-item">
              <i class="fas fa-globe"></i>
              {{ city.country?.name?.en || 'No Country' }}
            </span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="category-actions">
          <div class="dropdown-modern">
            <button class="action-btn-modern more">
              <i class="fas fa-ellipsis-v"></i>
            </button>
            <div class="dropdown-menu-modern">
              <button @click="openEditModal(city)">
                <i class="fas fa-edit"></i> Edit
              </button>
              <button @click="handleDelete(city)" class="delete-btn">
                <i class="fas fa-trash"></i> Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="empty-state-advanced">
      <i class="fas fa-city"></i>
      <h3>No Cities Found</h3>
      <p>Create your first city to get started</p>
      <button class="btn-create-advanced" @click="openCreateModal">
        <i class="fas fa-plus-circle"></i>
        Create City
      </button>
    </div>

    <!-- Pagination -->
    <div v-if="cities.last_page > 1" class="pagination-advanced">
      <button
        class="pagination-btn"
        :disabled="cities.current_page === 1"
        @click="changePage(cities.current_page - 1)"
      >
        <i class="fas fa-chevron-left"></i>
      </button>

      <button
        v-for="page in paginationPages"
        :key="page"
        class="pagination-btn"
        :class="{ active: page === cities.current_page }"
        @click="changePage(page)"
      >
        {{ page }}
      </button>

      <button
        class="pagination-btn"
        :disabled="cities.current_page === cities.last_page"
        @click="changePage(cities.current_page + 1)"
      >
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>

    <!-- Form Modal -->
    <CityFormModal
      v-if="showModal"
      :city="selectedCity"
      :mode="modalMode"
      @close="closeModal"
      @saved="handleSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCities } from '../../../composables/useCities'
import CityFormModal from '../../../components/admin/cities/CityFormModal.vue'

const { cities, loading, fetchCities, deleteCity } = useCities()

const filters = ref({
  search: '',
  country_id: '',
  sort_by: 'created_at',
  sort_order: 'desc',
  page: 1
})

const countries = ref([])
const showModal = ref(false)
const modalMode = ref('create')
const selectedCity = ref(null)
let searchTimeout = null

onMounted(async () => {
  await loadCities()
  await loadCountries()
})

const loadCities = async () => {
  await fetchCities(filters.value)
}

const loadCountries = async () => {
  try {
    const response = await fetch('/api/admin/countries?per_page=1000')
    const data = await response.json()
    countries.value = data.data || []
  } catch (error) {
    console.error('Error loading countries:', error)
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
  loadCities()
}

const changePage = (page) => {
  filters.value.page = page
  loadCities()
}

const paginationPages = computed(() => {
  const pages = []
  const currentPage = cities.value.current_page
  const lastPage = cities.value.last_page

  let startPage = Math.max(1, currentPage - 2)
  let endPage = Math.min(lastPage, currentPage + 2)

  for (let i = startPage; i <= endPage; i++) {
    pages.push(i)
  }

  return pages
})

const openCreateModal = () => {
  selectedCity.value = null
  modalMode.value = 'create'
  showModal.value = true
}

const openEditModal = (city) => {
  selectedCity.value = city
  modalMode.value = 'edit'
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedCity.value = null
}

const handleSaved = () => {
  closeModal()
  loadCities()
}

const handleDelete = async (city) => {
  if (!confirm(`Are you sure you want to delete "${city.name.en}"?`)) {
    return
  }

  try {
    await deleteCity(city.id)
    await loadCities()
  } catch (error) {
    alert(error.message || 'Failed to delete city')
  }
}

const handleImageError = (event) => {
  event.target.src = '/images/placeholder-city.png'
}
</script>
