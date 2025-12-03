<template>
  <div class="countries-management-advanced">
    <!-- Advanced Search & Filters -->
    <div class="search-filter-bar">
      <div class="search-box">
        <i class="fas fa-search search-icon"></i>
        <input
          type="text"
          v-model="filters.search"
          class="search-input"
          placeholder="Search countries by name or country code..."
        >
        <span v-if="filters.search" class="clear-search" @click="filters.search = ''">
          <i class="fas fa-times"></i>
        </span>
      </div>

      <div class="filter-controls">
        <select v-model="filters.sort_by" class="role-select">
          <option value="created_at">Sort by: Created Date</option>
          <option value="name">Sort by: Name</option>
          <option value="cities">Sort by: Cities Count</option>
        </select>

        <select v-model="filters.sort_order" class="role-select">
          <option value="desc">Descending</option>
          <option value="asc">Ascending</option>
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
        Showing {{ countries.data.length }} of {{ countries.total }} countries
      </div>

      <button class="action-btn success" @click="openCreateModal">
        <i class="fas fa-plus-circle"></i>
        Add Country
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="loader-advanced"></div>
      <p>Loading countries...</p>
    </div>

    <!-- Countries Grid View -->
    <div v-else-if="viewMode === 'grid'" class="users-grid">
      <div
        v-for="country in countries.data"
        :key="country.id"
        class="user-card"
      >
        <div class="card-header-custom">
          <div class="user-avatar-wrapper">
            <img
              :src="country.flag_url ? `/storage/${country.flag_url}` : '/storage/countryFlag/placeholder-flag.jpg'"
              :alt="country.name?.en || 'Country'"
              class="user-avatar"
              @error="handleImageError"
            >
          </div>
          <div class="card-menu">
            <button class="menu-btn" @click="toggleMenu(country.id)">
              <i class="fas fa-ellipsis-v"></i>
            </button>
            <div v-if="activeMenu === country.id" class="dropdown-menu">
              <button @click="openEditModal(country)" class="menu-item">
                <i class="fas fa-edit"></i>
                Edit Country
              </button>
              <button @click="openCitiesModal(country)" class="menu-item">
                <i class="fas fa-city"></i>
                Manage Cities ({{ country.cities_count || 0 }})
              </button>
              <button @click="handleDelete(country)" class="menu-item danger">
                <i class="fas fa-trash"></i>
                Delete
              </button>
            </div>
          </div>
        </div>

        <div class="card-body-custom">
          <h3 class="user-name">{{ country.name.en }}</h3>
          <p class="user-id">{{ country.name.ar }}</p>

          <div class="user-contact">
            <div class="contact-item">
              <i class="fas fa-flag"></i>
              <span>{{ country.country_code }}</span>
            </div>
            <div class="contact-item" v-if="country.currency_code">
              <i class="fas fa-money-bill"></i>
              <span>{{ country.currency_code }}</span>
            </div>
          </div>

          <div class="spacer"></div>

          <div class="user-stats">
            <div class="stat-item">
              <i class="fas fa-city"></i>
              <span>{{ country.cities_count || 0 }} Cities</span>
            </div>
          </div>
        </div>

        <div class="card-footer-custom">
          <button @click="openEditModal(country)" class="action-btn primary">
            <i class="fas fa-edit"></i>
            Edit
          </button>
          <button @click="openCitiesModal(country)" class="action-btn info">
            <i class="fas fa-city"></i>
            Cities
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="countries.data.length === 0" class="empty-state-advanced">
        <div class="empty-icon">
          <i class="fas fa-globe"></i>
        </div>
        <h3>No Countries Found</h3>
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
            <th style="width: 80px;">Flag</th>
            <th>Country Name</th>
            <th style="width: 120px;">Code</th>
            <th style="width: 120px;">Currency</th>
            <th style="width: 100px;">Cities</th>
            <th style="width: 120px; text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="country in countries.data" :key="country.id">
            <!-- Flag -->
            <td>
              <img
                :src="country.flag_url ? `/storage/${country.flag_url}` : '/storage/countryFlag/placeholder-flag.jpg'"
                :alt="country.name?.en || 'Country'"
                class="table-avatar"
                style="border-radius: 4px;"
                @error="handleImageError"
              >
            </td>

            <!-- Country Name -->
            <td>
              <div class="table-name">{{ country.name.en }}</div>
              <div class="table-subtitle">{{ country.name.ar }}</div>
            </td>

            <!-- Country Code -->
            <td>
              <span class="table-badge badge-info">
                {{ country.country_code }}
              </span>
            </td>

            <!-- Currency -->
            <td>
              <div class="table-subtitle" v-if="country.currency_code">
                <i class="fas fa-money-bill"></i> {{ country.currency_code }}
              </div>
              <div class="table-subtitle" v-else>
                N/A
              </div>
            </td>

            <!-- Cities Count -->
            <td>
              <div class="table-meta">
                <span>
                  <i class="fas fa-city"></i> {{ country.cities_count || 0 }}
                </span>
              </div>
            </td>

            <!-- Actions -->
            <td>
              <div class="table-actions">
                <button
                  @click="openEditModal(country)"
                  class="table-action-btn btn-edit"
                  title="Edit"
                >
                  <i class="fas fa-edit"></i>
                </button>
                <button
                  @click="openCitiesModal(country)"
                  class="table-action-btn btn-info"
                  title="Manage Cities"
                >
                  <i class="fas fa-city"></i>
                </button>
                <button
                  @click="handleDelete(country)"
                  class="table-action-btn btn-delete"
                  title="Delete"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>

          <!-- Empty State Row -->
          <tr v-if="countries.data.length === 0">
            <td colspan="6" class="table-empty-state">
              <i class="fas fa-globe"></i>
              <h3>No Countries Found</h3>
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
    <div v-if="countries.data.length > 0" class="pagination-advanced">
      <button
        class="page-btn"
        :disabled="countries.current_page === 1"
        @click="loadCountries(countries.current_page - 1)"
      >
        <i class="fas fa-chevron-left"></i>
        Previous
      </button>

      <div class="page-numbers">
        <button
          v-for="page in visiblePages"
          :key="page"
          class="page-number"
          :class="{ active: page === countries.current_page }"
          @click="loadCountries(page)"
        >
          {{ page }}
        </button>
      </div>

      <button
        class="page-btn"
        :disabled="countries.current_page === countries.last_page"
        @click="loadCountries(countries.current_page + 1)"
      >
        Next
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

    <!-- Cities Modal -->
    <CitiesModal
      v-if="showCitiesModal"
      :country="selectedCountry"
      @close="closeCitiesModal"
      @saved="handleCitiesSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, onBeforeUnmount } from 'vue'
import { useCountries } from '../../../composables/useCountries'
import CountryFormModal from '../../../components/admin/countries/CountryFormModal.vue'
import CitiesModal from '../../../components/admin/countries/CitiesModal.vue'

const { countries, loading, fetchCountries, deleteCountry } = useCountries()

const viewMode = ref('list')
const activeMenu = ref(null)

const filters = ref({
  search: '',
  sort_by: 'created_at',
  sort_order: 'desc'
})

const showModal = ref(false)
const modalMode = ref('create')
const selectedCountry = ref(null)
const showCitiesModal = ref(false)

const visiblePages = computed(() => {
  const pages = []
  const current = countries.value.current_page
  const last = countries.value.last_page

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
watch(() => filters.value.search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadCountries(1), 300)
})

watch(() => [filters.value.sort_by, filters.value.sort_order], () => {
  loadCountries(1)
})

const loadData = async () => {
  await loadCountries()
}

const loadCountries = async (page = 1) => {
  await fetchCountries({ ...filters.value, page })
}

const resetFilters = () => {
  filters.value.search = ''
  filters.value.sort_by = 'created_at'
  filters.value.sort_order = 'desc'
  loadCountries(1)
}

const toggleMenu = (countryId) => {
  activeMenu.value = activeMenu.value === countryId ? null : countryId
}

const closeMenus = () => {
  activeMenu.value = null
}

const openCreateModal = () => {
  selectedCountry.value = null
  modalMode.value = 'create'
  showModal.value = true
}

const openEditModal = (country) => {
  closeMenus()
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
  loadCountries(countries.value.current_page)
}

const handleDelete = async (country) => {
  closeMenus()

  if (!confirm(`Are you sure you want to delete "${country.name.en}"? This action cannot be undone.`)) {
    return
  }

  try {
    await deleteCountry(country.id)
    await loadCountries(countries.value.current_page)
  } catch (error) {
    alert('Error deleting country: ' + (error.message || 'Unknown error'))
  }
}

const handleImageError = (event) => {
  event.target.src = '/storage/countryFlag/placeholder-flag.jpg'
}

const openCitiesModal = (country) => {
  closeMenus()
  selectedCountry.value = country
  showCitiesModal.value = true
}

const closeCitiesModal = () => {
  showCitiesModal.value = false
}

const handleCitiesSaved = () => {
  loadCountries(countries.value.current_page)
}
</script>

<style scoped>
.countries-management-advanced {
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

.action-btn.info {
  background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
  color: white;
}

.table-action-btn.btn-info {
  color: #17a2b8;
}

.table-action-btn.btn-info:hover {
  background: #17a2b8;
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

/* Grid View */
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
  min-height: 350px;
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

.user-contact {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #6c757d;
  font-size: 0.875rem;
}

.contact-item i {
  width: 16px;
  color: #adb5bd;
}

.spacer {
  flex: 1;
}

.user-stats {
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

  .users-grid {
    grid-template-columns: 1fr;
  }
}
</style>
