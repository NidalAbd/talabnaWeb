<template>
  <div class="service-posts-management">
    <!-- Action Bar -->
    <div class="action-bar mb-3">
      <a href="/service_posts/create" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Add Service Post
      </a>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filters-grid">
        <!-- Search -->
        <div class="filter-item">
          <input
            v-model="filters.search"
            type="text"
            class="form-control"
            placeholder="Search by title, description, or user..."
            @input="debouncedSearch"
          >
        </div>

        <!-- Category Filter -->
        <div class="filter-item">
          <select v-model="filters.category" class="form-control" @change="onCategoryChange">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name.en || cat.name.ar || 'Unknown' }}
            </option>
          </select>
        </div>

        <!-- Subcategory Filter -->
        <div class="filter-item">
          <select
            v-model="filters.subcategory"
            class="form-control"
            :disabled="!filters.category || subcategories.length === 0"
            @change="loadData"
          >
            <option value="">All Subcategories</option>
            <option v-for="subcat in subcategories" :key="subcat.id" :value="subcat.id">
              {{ subcat.name.en || subcat.name.ar || 'Unknown' }}
            </option>
          </select>
        </div>

        <!-- Status Filter -->
        <div class="filter-item">
          <select v-model="filters.status" class="form-control" @change="loadData">
            <option value="">All Statuses</option>
            <option value="published">Published</option>
            <option value="not published">Draft</option>
            <option value="archive">Archived</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>

        <!-- Type Filter -->
        <div class="filter-item">
          <select v-model="filters.type" class="form-control" @change="loadData">
            <option value="">All Types</option>
            <option value="عرض">Offer (عرض)</option>
            <option value="طلب">Request (طلب)</option>
          </select>
        </div>

        <!-- Per Page -->
        <div class="filter-item">
          <select v-model="filters.per_page" class="form-control" @change="loadData">
            <option :value="25">25 per page</option>
            <option :value="50">50 per page</option>
            <option :value="100">100 per page</option>
          </select>
        </div>
      </div>

      <!-- Bulk Actions -->
      <div class="bulk-actions" v-if="selectedPosts.length > 0">
        <span class="selection-count">{{ selectedPosts.length }} selected</span>
        <button @click="handleBulkDelete" class="btn btn-danger btn-sm">
          <i class="fas fa-trash"></i>
          Delete Selected
        </button>
        <button @click="clearSelection" class="btn btn-secondary btn-sm">
          Clear Selection
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading service posts...</p>
    </div>

    <!-- Service Posts Table -->
    <div v-else-if="!loading && servicePosts.data && servicePosts.data.length > 0" class="table-card">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th style="width: 40px;">
                <input
                  type="checkbox"
                  :checked="allSelected"
                  @change="toggleSelectAll"
                >
              </th>
              <th @click="sort('id')" class="sortable">
                ID
                <i class="fas fa-sort"></i>
              </th>
              <th>Image</th>
              <th @click="sort('title')" class="sortable">
                Title
                <i class="fas fa-sort"></i>
              </th>
              <th>Category</th>
              <th>User</th>
              <th>Type</th>
              <th>Status</th>
              <th>Badge</th>
              <th>Stats</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="post in servicePosts.data" :key="post.id">
              <td>
                <input
                  type="checkbox"
                  :checked="selectedPosts.includes(post.id)"
                  @change="togglePostSelection(post.id)"
                >
              </td>
              <td class="text-muted">#{{ post.id }}</td>
              <td>
                <img
                  v-if="post.image_url"
                  :src="`/storage/${post.image_url}`"
                  class="post-thumbnail"
                  :alt="post.title"
                >
                <span v-else class="badge badge-secondary">No Image</span>
              </td>
              <td>
                <div class="post-title">{{ post.title }}</div>
                <small class="text-muted">{{ formatDate(post.created_at) }}</small>
              </td>
              <td>
                <div class="category-badges">
                  <span class="badge badge-info">
                    {{ post.category?.name?.en || post.category?.name?.ar || 'N/A' }}
                  </span>
                  <span v-if="post.sub_category" class="badge badge-light">
                    {{ post.sub_category?.name?.en || post.sub_category?.name?.ar }}
                  </span>
                </div>
              </td>
              <td>
                <a v-if="post.user" :href="`/admin/users/${post.user.id}`" class="user-link">
                  {{ post.user.user_name }}
                </a>
                <span v-else class="text-muted">Unknown</span>
              </td>
              <td>
                <span v-if="post.type === 'عرض'" class="badge badge-primary">Offer</span>
                <span v-else-if="post.type === 'طلب'" class="badge badge-secondary">Request</span>
                <span v-else class="badge badge-light">{{ post.type }}</span>
              </td>
              <td>
                <span
                  class="badge"
                  :class="{
                    'badge-success': post.state === 'published',
                    'badge-warning': post.state === 'archive',
                    'badge-secondary': post.state === 'not published',
                    'badge-danger': post.state === 'rejected'
                  }"
                >
                  {{ getStatusLabel(post.state) }}
                </span>
              </td>
              <td>
                <span
                  class="badge"
                  :class="{
                    'badge-primary': post.have_badge === 'ماسي',
                    'badge-warning': post.have_badge === 'ذهبي',
                    'badge-light': !post.have_badge || post.have_badge === 'عادي'
                  }"
                >
                  {{ getBadgeLabel(post.have_badge) }}
                </span>
              </td>
              <td>
                <div class="stats-col">
                  <small><i class="fas fa-eye"></i> {{ post.view_count || 0 }}</small>
                  <small><i class="fas fa-heart"></i> {{ post.favorites_count || 0 }}</small>
                </div>
              </td>
              <td>
                <div class="action-buttons">
                  <a
                    :href="`/service_posts/${post.id}`"
                    class="btn btn-sm btn-info"
                    title="View"
                  >
                    <i class="fas fa-eye"></i>
                  </a>
                  <a
                    :href="`/service_posts/${post.id}/edit`"
                    class="btn btn-sm btn-warning"
                    title="Edit"
                  >
                    <i class="fas fa-edit"></i>
                  </a>
                  <button
                    @click="handleDelete(post.id)"
                    class="btn btn-sm btn-danger"
                    title="Delete"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination-container">
        <button
          @click="changePage(servicePosts.current_page - 1)"
          :disabled="servicePosts.current_page === 1"
          class="btn btn-secondary"
        >
          <i class="fas fa-chevron-left"></i>
          Previous
        </button>

        <div class="page-numbers">
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="changePage(page)"
            class="btn"
            :class="page === servicePosts.current_page ? 'btn-primary' : 'btn-outline-secondary'"
          >
            {{ page }}
          </button>
        </div>

        <button
          @click="changePage(servicePosts.current_page + 1)"
          :disabled="servicePosts.current_page === servicePosts.last_page"
          class="btn btn-secondary"
        >
          Next
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!loading && servicePosts.data && servicePosts.data.length === 0" class="empty-state">
      <i class="fas fa-clipboard fa-3x text-muted mb-3"></i>
      <h3>No Service Posts Found</h3>
      <p class="text-muted">Try adjusting your filters or create a new service post</p>
      <a href="/service_posts/create" class="btn btn-primary mt-3">
        <i class="fas fa-plus"></i>
        Create First Service Post
      </a>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue'
import { useServicePosts } from '../../composables/useServicePosts'

const {
  servicePosts,
  loading,
  error,
  categories,
  subcategories,
  fetchServicePosts,
  fetchCategories,
  fetchSubcategories,
  deleteServicePost,
  bulkDeleteServicePosts
} = useServicePosts()

const filters = reactive({
  search: '',
  category: '',
  subcategory: '',
  status: '',
  type: '',
  sort_by: 'id',
  sort_direction: 'desc',
  page: 1,
  per_page: 25
})

const selectedPosts = ref([])
let searchTimeout = null

const visiblePages = computed(() => {
  const pages = []
  const current = servicePosts.value.current_page
  const last = servicePosts.value.last_page

  let start = Math.max(1, current - 2)
  let end = Math.min(last, current + 2)

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

const allSelected = computed(() => {
  return servicePosts.value.data?.length > 0 &&
         servicePosts.value.data.every(post => selectedPosts.value.includes(post.id))
})

onMounted(async () => {
  await fetchCategories()
  await loadData()
})

async function loadData() {
  filters.page = 1
  await fetchServicePosts(filters)
  clearSelection()
}

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadData()
  }, 500)
}

async function onCategoryChange() {
  filters.subcategory = ''
  if (filters.category) {
    await fetchSubcategories(filters.category)
  } else {
    subcategories.value = []
  }
  loadData()
}

function sort(field) {
  if (filters.sort_by === field) {
    filters.sort_direction = filters.sort_direction === 'asc' ? 'desc' : 'asc'
  } else {
    filters.sort_by = field
    filters.sort_direction = 'asc'
  }
  loadData()
}

function changePage(page) {
  if (page < 1 || page > servicePosts.value.last_page) return
  filters.page = page
  fetchServicePosts(filters)
}

function togglePostSelection(postId) {
  const index = selectedPosts.value.indexOf(postId)
  if (index > -1) {
    selectedPosts.value.splice(index, 1)
  } else {
    selectedPosts.value.push(postId)
  }
}

function toggleSelectAll() {
  if (allSelected.value) {
    selectedPosts.value = []
  } else {
    selectedPosts.value = servicePosts.value.data.map(post => post.id)
  }
}

function clearSelection() {
  selectedPosts.value = []
}

async function handleDelete(postId) {
  if (!confirm('Are you sure you want to delete this service post? This action cannot be undone.')) {
    return
  }

  try {
    await deleteServicePost(postId)
    await loadData()
  } catch (err) {
    alert('Failed to delete service post: ' + err.message)
  }
}

async function handleBulkDelete() {
  if (!confirm(`Are you sure you want to delete ${selectedPosts.value.length} service posts? This action cannot be undone.`)) {
    return
  }

  try {
    await bulkDeleteServicePosts(selectedPosts.value)
    await loadData()
  } catch (err) {
    alert('Failed to delete service posts: ' + err.message)
  }
}

function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

function getStatusLabel(state) {
  const labels = {
    'published': 'Published',
    'archive': 'Archived',
    'not published': 'Draft',
    'rejected': 'Rejected'
  }
  return labels[state] || state
}

function getBadgeLabel(badge) {
  const labels = {
    'ماسي': 'Diamond',
    'ذهبي': 'Gold',
    'عادي': 'Standard'
  }
  return labels[badge] || 'Standard'
}
</script>

<style scoped>
.service-posts-management {
  padding: 1.5rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 600;
  margin: 0;
  color: #2c3e50;
}

.page-title i {
  margin-right: 0.5rem;
  color: #3498db;
}

.page-subtitle {
  color: #7f8c8d;
  margin: 0.25rem 0 0 0;
}

.filters-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 1.5rem;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.filter-item .form-control {
  width: 100%;
}

.bulk-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid #ecf0f1;
}

.selection-count {
  font-weight: 600;
  color: #3498db;
}

.loading-container {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 8px;
}

.spinner {
  border: 3px solid #f3f3f3;
  border-top: 3px solid #3498db;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.table-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  overflow: hidden;
}

.table {
  margin: 0;
  width: 100%;
}

.table thead th {
  background: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
  font-weight: 600;
  padding: 1rem;
}

.table tbody td {
  padding: 1rem;
  vertical-align: middle;
}

.sortable {
  cursor: pointer;
  user-select: none;
}

.sortable:hover {
  background: #e9ecef;
}

.post-thumbnail {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 4px;
}

.post-title {
  font-weight: 500;
  max-width: 250px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.category-badges {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.user-link {
  color: #3498db;
  text-decoration: none;
}

.user-link:hover {
  text-decoration: underline;
}

.stats-col {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.stats-col small {
  color: #7f8c8d;
}

.stats-col i {
  width: 20px;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.pagination-container {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  padding: 1.5rem;
  border-top: 1px solid #dee2e6;
}

.page-numbers {
  display: flex;
  gap: 0.25rem;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 8px;
}

.badge {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
}
</style>
