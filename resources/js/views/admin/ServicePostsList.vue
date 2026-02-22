<template>
  <div class="service-posts-modern">
    <!-- Stats Cards -->
    <div class="stats-dashboard">
      <div class="stats-grid">
        <div class="stat-card-compact stat-blue">
          <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(servicePosts.total) }}</div>
            <div class="stat-label-compact">Total Posts</div>
          </div>
        </div>
        <div class="stat-card-compact stat-green">
          <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(publishedCount) }}</div>
            <div class="stat-label-compact">Published</div>
          </div>
        </div>
        <div class="stat-card-compact stat-orange">
          <div class="stat-icon"><i class="fas fa-clock"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(pendingCount) }}</div>
            <div class="stat-label-compact">Pending</div>
          </div>
        </div>
        <div class="stat-card-compact stat-purple">
          <div class="stat-icon"><i class="fas fa-award"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(badgedCount) }}</div>
            <div class="stat-label-compact">With Badges</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="search-filter-bar">
      <div class="search-box">
        <i class="fas fa-search search-icon"></i>
        <input
          type="text"
          class="search-input"
          placeholder="Search by title, description, or user..."
          v-model="filters.search"
        >
        <span v-if="filters.search" class="clear-search" @click="filters.search = ''">
          <i class="fas fa-times"></i>
        </span>
      </div>
      <div class="filter-group">
        <select class="filter-select" v-model="filters.category" @change="onCategoryChange">
          <option value="">All Categories</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.name.en || cat.name.ar || 'Unknown' }}
          </option>
        </select>
        <select
          class="filter-select"
          v-model="filters.subcategory"
          :disabled="!filters.category || subcategories.length === 0"
        >
          <option value="">All Subcategories</option>
          <option v-for="subcat in subcategories" :key="subcat.id" :value="subcat.id">
            {{ subcat.name.en || subcat.name.ar || 'Unknown' }}
          </option>
        </select>
        <select class="filter-select" v-model="filters.status">
          <option value="">All Statuses</option>
          <option value="published">Published</option>
          <option value="not published">Draft</option>
          <option value="archive">Archived</option>
          <option value="rejected">Rejected</option>
        </select>
        <select class="filter-select" v-model="filters.type">
          <option value="">All Types</option>
          <option value="عرض">Offer</option>
          <option value="طلب">Request</option>
        </select>
      </div>
      <div class="action-buttons">
        <button class="action-btn secondary" @click="resetFilters">
          <i class="fas fa-redo"></i>
        </button>
        <a href="/service_posts/create" class="action-btn primary">
          <i class="fas fa-plus"></i> Add Post
        </a>
      </div>
    </div>

    <!-- Bulk Actions -->
    <div class="bulk-actions-bar" v-if="selectedPosts.length > 0">
      <span class="selection-count">
        <i class="fas fa-check-square"></i>
        {{ selectedPosts.length }} selected
      </span>
      <button @click="handleBulkDelete" class="action-btn danger">
        <i class="fas fa-trash"></i> Delete Selected
      </button>
      <button @click="clearSelection" class="action-btn secondary">
        <i class="fas fa-times"></i> Clear
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading service posts...</p>
    </div>

    <!-- Table -->
    <div v-else class="data-table-container">
      <table class="modern-table">
        <thead>
          <tr>
            <th style="width: 40px;">
              <input
                type="checkbox"
                :checked="allSelected"
                @change="toggleSelectAll"
                class="custom-checkbox"
              >
            </th>
            <th style="width: 50px;">ID</th>
            <th style="width: 80px;">Media</th>
            <th>Title</th>
            <th>Category</th>
            <th>User</th>
            <th>Type</th>
            <th>Status</th>
            <th>Badge</th>
            <th>Stats</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody v-if="servicePosts.data && servicePosts.data.length > 0">
          <tr v-for="post in servicePosts.data" :key="post.id">
            <td>
              <input
                type="checkbox"
                :checked="selectedPosts.includes(post.id)"
                @change="togglePostSelection(post.id)"
                class="custom-checkbox"
              >
            </td>
            <td>
              <span class="id-badge">#{{ post.id }}</span>
            </td>
            <td>
              <div class="media-cell">
                <!-- Video thumbnail -->
                <div v-if="post.media && post.media.is_video" class="media-thumbnail video">
                  <img
                    :src="getVideoThumbnail(post.media)"
                    :alt="post.title"
                    @error="handleImageError($event)"
                  >
                  <div class="video-overlay">
                    <i class="fas fa-play"></i>
                  </div>
                </div>
                <!-- Image -->
                <div v-else-if="post.media && post.media.src" class="media-thumbnail">
                  <img
                    :src="getMediaUrl(post.media)"
                    :alt="post.title"
                    @error="handleImageError($event)"
                  >
                </div>
                <!-- Placeholder -->
                <div v-else class="media-placeholder">
                  <i class="fas fa-image"></i>
                </div>
                <span v-if="post.photos_count > 1" class="media-count">
                  +{{ post.photos_count - 1 }}
                </span>
              </div>
            </td>
            <td>
              <div class="title-cell">
                <strong>{{ truncate(post.title, 35) }}</strong>
                <span class="date-meta">{{ formatDate(post.created_at) }}</span>
              </div>
            </td>
            <td>
              <div class="category-cell">
                <span class="badge info">
                  {{ post.category?.name?.en || post.category?.name?.ar || 'N/A' }}
                </span>
                <span v-if="post.sub_category" class="badge secondary small">
                  {{ post.sub_category?.name?.en || post.sub_category?.name?.ar }}
                </span>
              </div>
            </td>
            <td>
              <a v-if="post.user" :href="`/users/${post.user.id}`" class="user-link">
                {{ post.user.user_name }}
              </a>
              <span v-else class="text-muted">Unknown</span>
            </td>
            <td>
              <span class="badge" :class="post.type === 'عرض' ? 'primary' : 'secondary'">
                <i :class="post.type === 'عرض' ? 'fas fa-tag' : 'fas fa-hand-paper'"></i>
                {{ post.type === 'عرض' ? 'Offer' : 'Request' }}
              </span>
            </td>
            <td>
              <span class="badge" :class="getStatusBadgeClass(post.state)">
                <i :class="getStatusIcon(post.state)"></i>
                {{ getStatusLabel(post.state) }}
              </span>
            </td>
            <td>
              <span class="badge" :class="getBadgeBadgeClass(post.have_badge)">
                <i :class="getBadgeIcon(post.have_badge)"></i>
                {{ getBadgeLabel(post.have_badge) }}
              </span>
            </td>
            <td>
              <div class="stats-cell">
                <span class="stat-item" title="Views">
                  <i class="fas fa-eye"></i> {{ post.view_count || 0 }}
                </span>
                <span class="stat-item" title="Favorites">
                  <i class="fas fa-heart"></i> {{ post.favorites_count || 0 }}
                </span>
              </div>
            </td>
            <td>
              <div class="table-actions">
                <a :href="`/service_posts/${post.id}`" class="action-btn-small view" title="View">
                  <i class="fas fa-eye"></i>
                </a>
                <a :href="`/service_posts/${post.id}/edit`" class="action-btn-small edit" title="Edit">
                  <i class="fas fa-edit"></i>
                </a>
                <button @click="handleDelete(post)" class="action-btn-small delete" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="11" class="empty-state">
              <i class="fas fa-clipboard"></i>
              <p>No service posts found</p>
              <button @click="resetFilters" class="action-btn primary">
                <i class="fas fa-redo"></i> Reset Filters
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-container" v-if="servicePosts.last_page > 1">
      <div class="pagination-info">
        Showing {{ servicePosts.data?.length || 0 }} of {{ servicePosts.total }} posts
      </div>
      <div class="pagination-controls">
        <button
          class="pagination-btn"
          :disabled="servicePosts.current_page === 1"
          @click="changePage(servicePosts.current_page - 1)"
        >
          <i class="fas fa-chevron-left"></i> Previous
        </button>
        <button
          v-for="page in visiblePages"
          :key="page"
          class="pagination-btn"
          :class="{ active: page === servicePosts.current_page }"
          @click="changePage(page)"
        >
          {{ page }}
        </button>
        <button
          class="pagination-btn"
          :disabled="servicePosts.current_page === servicePosts.last_page"
          @click="changePage(servicePosts.current_page + 1)"
        >
          Next <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal-overlay" v-if="showDeleteModal" @click="showDeleteModal = false">
      <div class="modern-modal" @click.stop>
        <div class="modal-header">
          <h3><i class="fas fa-exclamation-triangle text-danger"></i> Delete Post</h3>
          <button class="close-btn" @click="showDeleteModal = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete <strong>{{ postToDelete?.title }}</strong>?</p>
          <p class="text-muted">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button class="action-btn secondary" @click="showDeleteModal = false">
            <i class="fas fa-times"></i> Cancel
          </button>
          <button class="action-btn danger" @click="confirmDelete" :disabled="isDeleting">
            <i v-if="isDeleting" class="fas fa-spinner fa-spin"></i>
            <i v-else class="fas fa-trash"></i> Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive, watch } from 'vue'
import { useServicePosts } from '../../composables/useServicePosts'

const {
  servicePosts,
  loading,
  categories,
  subcategories,
  fetchServicePosts,
  fetchCategories,
  fetchSubcategories,
  deleteServicePost,
  bulkDeleteServicePosts
} = useServicePosts()

const formatNumber = (value) => {
  if (value === null || value === undefined) return '0'
  return new Intl.NumberFormat().format(value)
}

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
const showDeleteModal = ref(false)
const postToDelete = ref(null)
const isDeleting = ref(false)

const placeholderImage = '/storage/countryFlag/placeholder-flag.jpg'

const publishedCount = computed(() => {
  return servicePosts.value.data?.filter(p => p.state === 'published').length || 0
})

const pendingCount = computed(() => {
  return servicePosts.value.data?.filter(p => p.state === 'not published').length || 0
})

const badgedCount = computed(() => {
  return servicePosts.value.data?.filter(p => p.have_badge && p.have_badge !== 'عادي').length || 0
})

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

// Watch filters with debounce for search
let searchTimeout = null
watch(() => filters.search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadData(), 500)
})

watch(() => [filters.status, filters.type, filters.subcategory], () => {
  loadData()
})

async function loadData() {
  filters.page = 1
  await fetchServicePosts(filters)
  clearSelection()
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

function resetFilters() {
  filters.search = ''
  filters.category = ''
  filters.subcategory = ''
  filters.status = ''
  filters.type = ''
  subcategories.value = []
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

function handleDelete(post) {
  postToDelete.value = post
  showDeleteModal.value = true
}

async function confirmDelete() {
  if (!postToDelete.value) return

  isDeleting.value = true

  try {
    await deleteServicePost(postToDelete.value.id)
    showDeleteModal.value = false
    postToDelete.value = null
    await loadData()
  } catch (err) {
    alert('Failed to delete service post: ' + err.message)
  } finally {
    isDeleting.value = false
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

function getMediaUrl(media) {
  if (!media || !media.src) return placeholderImage
  if (media.is_external) return media.src
  return `/storage/${media.src}`
}

function getVideoThumbnail(media) {
  // For videos, we return placeholder since we can't generate thumbnails client-side
  // In a real app, you'd have server-generated thumbnails
  return placeholderImage
}

function handleImageError(event) {
  event.target.src = placeholderImage
}

function truncate(text, length) {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
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

function getStatusBadgeClass(state) {
  const classes = {
    'published': 'success',
    'archive': 'warning',
    'not published': 'secondary',
    'rejected': 'danger'
  }
  return classes[state] || 'secondary'
}

function getStatusIcon(state) {
  const icons = {
    'published': 'fas fa-check-circle',
    'archive': 'fas fa-archive',
    'not published': 'fas fa-clock',
    'rejected': 'fas fa-times-circle'
  }
  return icons[state] || 'fas fa-question-circle'
}

function getBadgeLabel(badge) {
  const labels = {
    'ماسي': 'Diamond',
    'ذهبي': 'Gold',
    'عادي': 'Standard'
  }
  return labels[badge] || 'Standard'
}

function getBadgeBadgeClass(badge) {
  const classes = {
    'ماسي': 'diamond',
    'ذهبي': 'gold'
  }
  return classes[badge] || 'secondary'
}

function getBadgeIcon(badge) {
  const icons = {
    'ماسي': 'fas fa-gem',
    'ذهبي': 'fas fa-crown'
  }
  return icons[badge] || 'fas fa-tag'
}
</script>

<style scoped>
.service-posts-modern {
  padding: 0;
}

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
  transition: color 0.2s;
}

.clear-search:hover {
  color: #333;
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
  font-size: 0.85rem;
  background: white;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 140px;
}

.filter-select:focus {
  outline: none;
  border-color: #667eea;
}

.filter-select:disabled {
  opacity: 0.5;
  cursor: not-allowed;
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

.action-btn.danger {
  background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
  color: white;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.action-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Bulk Actions */
.bulk-actions-bar {
  display: flex;
  gap: 1rem;
  align-items: center;
  padding: 1rem 1.25rem;
  background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
  border-radius: 12px;
  margin-bottom: 1.5rem;
}

.selection-count {
  font-weight: 600;
  color: #ef6c00;
  display: flex;
  align-items: center;
  gap: 0.5rem;
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
  padding: 1rem 0.75rem;
  text-align: left;
  font-weight: 600;
  color: white;
  font-size: 0.8rem;
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
  padding: 0.875rem 0.75rem;
  font-size: 0.85rem;
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

/* Custom Checkbox */
.custom-checkbox {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #667eea;
}

/* ID Badge */
.id-badge {
  background: linear-gradient(135deg, #e8ecef 0%, #d1d8e0 100%);
  padding: 0.3rem 0.6rem;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.8rem;
  color: #555;
}

/* Media Cell */
.media-cell {
  position: relative;
  display: inline-block;
}

.media-thumbnail {
  width: 55px;
  height: 55px;
  border-radius: 10px;
  overflow: hidden;
  position: relative;
}

.media-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.media-thumbnail.video .video-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.25rem;
}

.media-placeholder {
  width: 55px;
  height: 55px;
  border-radius: 10px;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #adb5bd;
  font-size: 1.25rem;
}

.media-count {
  position: absolute;
  bottom: -5px;
  right: -5px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-size: 0.65rem;
  font-weight: 600;
  padding: 0.15rem 0.4rem;
  border-radius: 6px;
}

/* Title Cell */
.title-cell {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.title-cell strong {
  color: #2c3e50;
}

.date-meta {
  font-size: 0.75rem;
  color: #888;
}

/* Category Cell */
.category-cell {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

/* User Link */
.user-link {
  color: #667eea;
  text-decoration: none;
  font-weight: 500;
}

.user-link:hover {
  text-decoration: underline;
}

/* Stats Cell */
.stats-cell {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.8rem;
  color: #666;
}

.stat-item i {
  color: #999;
  width: 14px;
}

/* Badges */
.badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.35rem 0.7rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge.small {
  font-size: 0.7rem;
  padding: 0.25rem 0.5rem;
}

.badge.primary { background: #e3f2fd; color: #1976d2; }
.badge.info { background: #e0f7fa; color: #00838f; }
.badge.success { background: #e8f5e9; color: #2e7d32; }
.badge.warning { background: #fff3e0; color: #ef6c00; }
.badge.danger { background: #ffebee; color: #c62828; }
.badge.secondary { background: #f5f5f5; color: #616161; }

.badge.diamond {
  background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
  color: #1565c0;
}

.badge.gold {
  background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);
  color: #ff8f00;
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
  text-decoration: none;
  font-size: 0.85rem;
}

.action-btn-small.view {
  background: #e3f2fd;
  color: #1976d2;
}

.action-btn-small.edit {
  background: #fff3e0;
  color: #ef6c00;
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

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 1rem;
  backdrop-filter: blur(4px);
}

.modern-modal {
  background: white;
  border-radius: 20px;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
  max-width: 500px;
  width: 100%;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #eef2f7;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  color: #999;
  cursor: pointer;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: #f8f9fa;
  color: #333;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem;
  border-top: 1px solid #eef2f7;
}

.text-danger { color: #dc3545; }
.text-muted { color: #888; font-size: 0.9rem; }

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

  .data-table-container {
    overflow-x: auto;
  }

  .modern-table {
    min-width: 1100px;
  }

  .pagination-container {
    flex-direction: column;
  }
}
</style>
