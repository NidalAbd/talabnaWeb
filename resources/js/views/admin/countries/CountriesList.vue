<template>
  <div class="subcategories-management-advanced">
    <!-- Section Header -->
    <div class="section-header-advanced">
      <div class="header-left">
        <h1 class="section-title-advanced">
          <i class="fas fa-globe"></i>
          Countries Management
        </h1>
        <p class="section-subtitle">Manage countries and their information</p>
      </div>
      <button class="btn-create-advanced" @click="openCreateModal">
        <i class="fas fa-plus-circle"></i>
        Create Country
      </button>
    </div>

    <!-- Filters & Search Bar -->
    <div class="filters-bar-advanced">
      <div class="search-box-advanced">
        <i class="fas fa-search"></i>
        <input
          v-model="filters.search"
          type="text"
          placeholder="Search countries..."
          @input="debounceSearch"
        >
      </div>

      <select v-model="filters.sort_by" class="filter-select-advanced" @change="applyFilters">
        <option value="created_at">Sort by: Created Date</option>
        <option value="name">Sort by: Name</option>
        <option value="cities">Sort by: Cities Count</option>
      </select>

      <select v-model="filters.sort_order" class="filter-select-advanced" @change="applyFilters">
        <option value="desc">Descending</option>
        <option value="asc">Ascending</option>
      </select>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state-advanced">
      <div class="loader-advanced"></div>
      <p>Loading countries...</p>
    </div>

    <!-- Countries Grid -->
    <div v-else-if="countries.data.length > 0" class="categories-grid-advanced">
      <div
        v-for="country in countries.data"
        :key="country.id"
        class="category-card-advanced"
      >
        <!-- Country Flag -->
        <div class="category-image-wrapper">
          <img
            :src="country.flag_url ? `/storage/${country.flag_url}` : '/images/placeholder-flag.png'"
            :alt="country.name.en"
            class="category-image"
            @error="handleImageError"
          >
          <div class="category-badges">
            <span class="badge-advanced badge-info">
              {{ country.country_code }}
            </span>
          </div>
        </div>

        <!-- Country Info -->
        <div class="category-info">
          <h3 class="category-name">{{ country.name.en }}</h3>
          <p class="category-name-ar">{{ country.name.ar }}</p>

          <div class="category-meta">
            <span class="meta-item">
              <i class="fas fa-city"></i>
              {{ country.cities_count || 0 }} Cities
            </span>
            <span v-if="country.currency_code" class="meta-item">
              <i class="fas fa-money-bill"></i>
              {{ country.currency_code }}
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
              <button @click="openEditModal(country)">
                <i class="fas fa-edit"></i> Edit
              </button>
              <button @click="handleDelete(country)" class="delete-btn">
                <i class="fas fa-trash"></i> Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="empty-state-advanced">
      <i class="fas fa-globe"></i>
      <h3>No Countries Found</h3>
      <p>Create your first country to get started</p>
      <button class="btn-create-advanced" @click="openCreateModal">
        <i class="fas fa-plus-circle"></i>
        Create Country
      </button>
    </div>

    <!-- Pagination -->
    <div v-if="countries.last_page > 1" class="pagination-advanced">
      <button
        class="pagination-btn"
        :disabled="countries.current_page === 1"
        @click="changePage(countries.current_page - 1)"
      >
        <i class="fas fa-chevron-left"></i>
      </button>

      <button
        v-for="page in paginationPages"
        :key="page"
        class="pagination-btn"
        :class="{ active: page === countries.current_page }"
        @click="changePage(page)"
      >
        {{ page }}
      </button>

      <button
        class="pagination-btn"
        :disabled="countries.current_page === countries.last_page"
        @click="changePage(countries.current_page + 1)"
      >
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>

    <!-- Form Modal -->
    <CountryFormModal
      v-if="showModal"
      :country="selectedCountry"
      :mode="modalMode"
      @close="closeModal"
      @saved="handleSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCountries } from '../../../composables/useCountries'
import CountryFormModal from '../../../components/admin/countries/CountryFormModal.vue'

const { countries, loading, fetchCountries, deleteCountry } = useCountries()

const filters = ref({
  search: '',
  sort_by: 'created_at',
  sort_order: 'desc',
  page: 1
})

const showModal = ref(false)
const modalMode = ref('create')
const selectedCountry = ref(null)
let searchTimeout = null

onMounted(async () => {
  await loadCountries()
})

const loadCountries = async () => {
  await fetchCountries(filters.value)
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const applyFilters = () => {
  filters.value.page = 1
  loadCountries()
}

const changePage = (page) => {
  filters.value.page = page
  loadCountries()
}

const paginationPages = computed(() => {
  const pages = []
  const currentPage = countries.value.current_page
  const lastPage = countries.value.last_page

  let startPage = Math.max(1, currentPage - 2)
  let endPage = Math.min(lastPage, currentPage + 2)

  for (let i = startPage; i <= endPage; i++) {
    pages.push(i)
  }

  return pages
})

const openCreateModal = () => {
  selectedCountry.value = null
  modalMode.value = 'create'
  showModal.value = true
}

const openEditModal = (country) => {
  selectedCountry.value = country
  modalMode.value = 'edit'
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedCountry.value = null
}

const handleSaved = () => {
  closeModal()
  loadCountries()
}

const handleDelete = async (country) => {
  if (!confirm(`Are you sure you want to delete "${country.name.en}"?`)) {
    return
  }

  try {
    await deleteCountry(country.id)
    await loadCountries()
  } catch (error) {
    alert(error.message || 'Failed to delete country')
  }
}

const handleImageError = (event) => {
  event.target.src = '/images/placeholder-flag.png'
}
</script>
