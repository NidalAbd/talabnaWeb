<template>
  <div class="subcategories-management-advanced">
    <!-- Section Header -->
    <div class="section-header-advanced">
      <div class="header-left">
        <h1 class="section-title-advanced">
          <i class="fas fa-certificate"></i>
          Badge Types Management
        </h1>
        <p class="section-subtitle">Manage badge types for service posts</p>
      </div>
      <button class="btn-create-advanced" @click="openCreateModal">
        <i class="fas fa-plus-circle"></i>
        Create Badge Type
      </button>
    </div>

    <!-- Filters & Search Bar -->
    <div class="filters-bar-advanced">
      <div class="search-box-advanced">
        <i class="fas fa-search"></i>
        <input
          v-model="filters.search"
          type="text"
          placeholder="Search badge types..."
          @input="debounceSearch"
        >
      </div>

      <select v-model="filters.status" class="filter-select-advanced" @change="applyFilters">
        <option value="">All Status</option>
        <option value="1">Active Only</option>
        <option value="0">Inactive Only</option>
      </select>

      <select v-model="filters.sort_by" class="filter-select-advanced" @change="applyFilters">
        <option value="priority">Sort by: Priority</option>
        <option value="name">Sort by: Name</option>
        <option value="posts">Sort by: Posts Count</option>
        <option value="created_at">Sort by: Created Date</option>
      </select>

      <select v-model="filters.sort_order" class="filter-select-advanced" @change="applyFilters">
        <option value="asc">Ascending</option>
        <option value="desc">Descending</option>
      </select>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state-advanced">
      <div class="loader-advanced"></div>
      <p>Loading badge types...</p>
    </div>

    <!-- Badge Types Grid -->
    <div v-else-if="badgeTypes.data.length > 0" class="categories-grid-advanced">
      <div
        v-for="badge in badgeTypes.data"
        :key="badge.id"
        class="category-card-advanced"
        :style="{ borderLeft: `4px solid ${badge.color}` }"
      >
        <!-- Badge Display -->
        <div class="category-image-wrapper" :style="{ background: `linear-gradient(135deg, ${badge.color}, ${badge.secondary_color || badge.color})` }">
          <div class="badge-icon-display">
            <i :class="badge.icon || 'fas fa-certificate'" style="font-size: 3rem; color: white;"></i>
          </div>
          <div class="category-badges">
            <span v-if="badge.is_default" class="badge-advanced badge-warning">
              <i class="fas fa-star"></i> Default
            </span>
            <span v-if="badge.is_active" class="badge-advanced badge-success">
              <i class="fas fa-check-circle"></i> Active
            </span>
            <span v-else class="badge-advanced badge-secondary">
              <i class="fas fa-times-circle"></i> Inactive
            </span>
          </div>
        </div>

        <!-- Badge Info -->
        <div class="category-info">
          <h3 class="category-name">{{ badge.name.en }}</h3>
          <p class="category-name-ar">{{ badge.name.ar }}</p>

          <div class="category-meta">
            <span class="meta-item">
              <i class="fas fa-coins"></i>
              {{ badge.points_per_day }} pts/day
            </span>
            <span class="meta-item">
              <i class="fas fa-layer-group"></i>
              Priority: {{ badge.priority }}
            </span>
            <span class="meta-item">
              <i class="fas fa-eye"></i>
              +{{ badge.view_boost_percent }}% boost
            </span>
            <span class="meta-item">
              <i class="fas fa-file-alt"></i>
              {{ badge.service_posts_count || 0 }} Posts
            </span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="category-actions">
          <button
            v-if="!badge.is_active"
            class="action-btn-modern active"
            :class="{ active: badge.is_active }"
            @click="handleToggleStatus(badge)"
            title="Toggle Active"
          >
            <i class="fas fa-power-off"></i>
          </button>

          <button
            v-if="badge.is_active"
            class="action-btn-modern active"
            :class="{ active: badge.is_active }"
            @click="handleToggleStatus(badge)"
            title="Toggle Active"
          >
            <i class="fas fa-check-circle"></i>
          </button>

          <button
            v-if="!badge.is_default"
            class="action-btn-modern default"
            @click="handleSetDefault(badge)"
            title="Set as Default"
          >
            <i class="fas fa-star"></i>
          </button>

          <div class="dropdown-modern">
            <button class="action-btn-modern more">
              <i class="fas fa-ellipsis-v"></i>
            </button>
            <div class="dropdown-menu-modern">
              <button @click="openEditModal(badge)">
                <i class="fas fa-edit"></i> Edit
              </button>
              <button @click="handleDelete(badge)" class="delete-btn">
                <i class="fas fa-trash"></i> Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="empty-state-advanced">
      <i class="fas fa-certificate"></i>
      <h3>No Badge Types Found</h3>
      <p>Create your first badge type to get started</p>
      <button class="btn-create-advanced" @click="openCreateModal">
        <i class="fas fa-plus-circle"></i>
        Create Badge Type
      </button>
    </div>

    <!-- Pagination -->
    <div v-if="badgeTypes.last_page > 1" class="pagination-advanced">
      <button
        class="pagination-btn"
        :disabled="badgeTypes.current_page === 1"
        @click="changePage(badgeTypes.current_page - 1)"
      >
        <i class="fas fa-chevron-left"></i>
      </button>

      <button
        v-for="page in paginationPages"
        :key="page"
        class="pagination-btn"
        :class="{ active: page === badgeTypes.current_page }"
        @click="changePage(page)"
      >
        {{ page }}
      </button>

      <button
        class="pagination-btn"
        :disabled="badgeTypes.current_page === badgeTypes.last_page"
        @click="changePage(badgeTypes.current_page + 1)"
      >
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>

    <!-- Form Modal -->
    <BadgeTypeFormModal
      v-if="showModal"
      :badgeType="selectedBadgeType"
      :mode="modalMode"
      @close="closeModal"
      @saved="handleSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useBadgeTypes } from '../../../composables/useBadgeTypes'
import BadgeTypeFormModal from '../../../components/admin/badge-types/BadgeTypeFormModal.vue'

const { badgeTypes, loading, fetchBadgeTypes, deleteBadgeType, toggleStatus, setDefault } = useBadgeTypes()

const filters = ref({
  search: '',
  status: '',
  sort_by: 'priority',
  sort_order: 'asc',
  page: 1
})

const showModal = ref(false)
const modalMode = ref('create')
const selectedBadgeType = ref(null)
let searchTimeout = null

onMounted(async () => {
  await loadBadgeTypes()
})

const loadBadgeTypes = async () => {
  await fetchBadgeTypes(filters.value)
}

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const applyFilters = () => {
  filters.value.page = 1
  loadBadgeTypes()
}

const changePage = (page) => {
  filters.value.page = page
  loadBadgeTypes()
}

const paginationPages = computed(() => {
  const pages = []
  const currentPage = badgeTypes.value.current_page
  const lastPage = badgeTypes.value.last_page

  let startPage = Math.max(1, currentPage - 2)
  let endPage = Math.min(lastPage, currentPage + 2)

  for (let i = startPage; i <= endPage; i++) {
    pages.push(i)
  }

  return pages
})

const openCreateModal = () => {
  selectedBadgeType.value = null
  modalMode.value = 'create'
  showModal.value = true
}

const openEditModal = (badge) => {
  selectedBadgeType.value = badge
  modalMode.value = 'edit'
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedBadgeType.value = null
}

const handleSaved = () => {
  closeModal()
  loadBadgeTypes()
}

const handleToggleStatus = async (badge) => {
  try {
    await toggleStatus(badge.id)
    await loadBadgeTypes()
  } catch (error) {
    alert(error.message || 'Failed to toggle status')
  }
}

const handleSetDefault = async (badge) => {
  if (!confirm(`Set "${badge.name.en}" as the default badge type?`)) {
    return
  }

  try {
    await setDefault(badge.id)
    await loadBadgeTypes()
  } catch (error) {
    alert(error.message || 'Failed to set as default')
  }
}

const handleDelete = async (badge) => {
  if (!confirm(`Are you sure you want to delete "${badge.name.en}"?`)) {
    return
  }

  try {
    await deleteBadgeType(badge.id)
    await loadBadgeTypes()
  } catch (error) {
    alert(error.message || 'Failed to delete badge type')
  }
}
</script>

<style scoped>
.badge-icon-display {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}

.action-btn-modern.default {
  background: linear-gradient(135deg, #f39c12, #f1c40f);
  color: white;
}

.action-btn-modern.default:hover {
  transform: scale(1.1);
}
</style>
