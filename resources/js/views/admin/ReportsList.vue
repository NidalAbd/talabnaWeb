<template>
  <div class="reports-management">

    <!-- Filters -->
    <div class="filters-card">
      <div class="filters-grid">
        <!-- Type Filter -->
        <div class="filter-item">
          <select v-model="filters.type" class="form-control" @change="loadData">
            <option value="">All Types</option>
            <option value="user">User Reports</option>
            <option value="post">Post Reports</option>
          </select>
        </div>

        <!-- Sort Filter -->
        <div class="filter-item">
          <select v-model="filters.sort_by" class="form-control" @change="loadData">
            <option value="total">Most Reported</option>
            <option value="latest">Latest Reports</option>
          </select>
        </div>

        <!-- Per Page -->
        <div class="filter-item">
          <select v-model="filters.per_page" class="form-control" @change="loadData">
            <option :value="15">15 per page</option>
            <option :value="25">25 per page</option>
            <option :value="50">50 per page</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading reports...</p>
    </div>

    <!-- Reports Table -->
    <div v-else-if="!loading && reports.data && reports.data.length > 0" class="table-card">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Type</th>
              <th>Reported Item</th>
              <th>Status</th>
              <th>Total Reports</th>
              <th>Latest Report</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="report in reports.data" :key="`${report.reportable_type}-${report.reportable_id}`">
              <td>
                <span
                  class="badge"
                  :class="report.reportable_type === 'user' ? 'badge-warning' : 'badge-info'"
                >
                  <i :class="report.reportable_type === 'user' ? 'fas fa-user' : 'fas fa-file-alt'"></i>
                  {{ report.reportable_type === 'user' ? 'User' : 'Post' }}
                </span>
              </td>
              <td>
                <div v-if="report.reportable" class="reportable-info">
                  <div v-if="report.reportable_type === 'post' && report.reportable.image" class="reportable-image">
                    <img :src="`/storage/${report.reportable.image}`" alt="Post image">
                  </div>
                  <div>
                    <div class="reportable-title">{{ report.reportable.title }}</div>
                    <small class="text-muted">ID: #{{ report.reportable.id }}</small>
                  </div>
                </div>
                <span v-else class="text-muted">Item not found</span>
              </td>
              <td>
                <span
                  class="badge"
                  :class="{
                    'badge-success': report.reportable?.status === 'active' || report.reportable?.status === 'published',
                    'badge-danger': report.reportable?.status === 'banned' || report.reportable?.status === 'archive',
                    'badge-secondary': report.reportable?.status === 'not published'
                  }"
                >
                  {{ getStatusLabel(report.reportable?.status) }}
                </span>
              </td>
              <td>
                <span class="badge badge-danger badge-lg">
                  {{ report.total_reports }}
                </span>
              </td>
              <td>
                <small class="text-muted">{{ formatDate(report.latest_report) }}</small>
              </td>
              <td>
                <div class="action-buttons">
                  <button
                    @click="viewReportDetails(report.reportable_type, report.reportable_id)"
                    class="btn btn-sm btn-info"
                    title="View Details"
                  >
                    <i class="fas fa-eye"></i>
                    View
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
          @click="changePage(reports.current_page - 1)"
          :disabled="reports.current_page === 1"
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
            :class="page === reports.current_page ? 'btn-primary' : 'btn-outline-secondary'"
          >
            {{ page }}
          </button>
        </div>

        <button
          @click="changePage(reports.current_page + 1)"
          :disabled="reports.current_page === reports.last_page"
          class="btn btn-secondary"
        >
          Next
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!loading && reports.data && reports.data.length === 0" class="empty-state">
      <i class="fas fa-flag fa-3x text-muted mb-3"></i>
      <h3>No Reports Found</h3>
      <p class="text-muted">There are no reports to review at this time</p>
    </div>

    <!-- Report Details Modal -->
    <div v-if="showDetailsModal" class="modal-overlay" @click="closeDetailsModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>
            <i class="fas fa-flag"></i>
            Report Details
          </h3>
          <button @click="closeDetailsModal" class="btn-close">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div v-if="reportable" class="modal-body">
          <!-- Reportable Info -->
          <div class="reportable-detail-card">
            <h4>{{ reportType === 'user' ? 'Reported User' : 'Reported Post' }}</h4>
            <div class="reportable-detail-content">
              <div v-if="reportType === 'post' && reportable.image">
                <img :src="`/storage/${reportable.image}`" alt="Post image" class="detail-image">
              </div>
              <div class="detail-info">
                <p><strong>ID:</strong> #{{ reportable.id }}</p>
                <p v-if="reportType === 'user'">
                  <strong>Name:</strong> {{ reportable.name }}
                </p>
                <p v-if="reportType === 'user'">
                  <strong>Email:</strong> {{ reportable.email }}
                </p>
                <p v-if="reportType === 'post'">
                  <strong>Title:</strong> {{ reportable.title }}
                </p>
                <p v-if="reportType === 'post'">
                  <strong>Description:</strong> {{ reportable.description }}
                </p>
                <p>
                  <strong>Status:</strong>
                  <span class="badge badge-info">{{ reportable.state || reportable.status }}</span>
                </p>
                <p v-if="reportType === 'post'">
                  <strong>Report Count:</strong> {{ reportable.report_count || 0 }}
                </p>
              </div>
            </div>
          </div>

          <!-- Individual Reports List -->
          <div class="reports-list">
            <h4>Individual Reports ({{ reportDetails.total || 0 }})</h4>
            <div v-if="reportDetails.data && reportDetails.data.length > 0">
              <div v-for="detail in reportDetails.data" :key="detail.id" class="report-item">
                <div class="report-header">
                  <span v-if="detail.reporter">
                    <i class="fas fa-user"></i>
                    Reported by: <strong>{{ detail.reporter.name }}</strong>
                  </span>
                  <span class="text-muted">
                    {{ formatDate(detail.created_at) }}
                  </span>
                </div>
                <div class="report-reason">
                  <i class="fas fa-comment"></i>
                  {{ detail.reason }}
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="action-section">
            <h4>Take Action</h4>
            <div class="action-buttons-grid">
              <button
                @click="handleAction('warning')"
                class="btn btn-warning"
                :disabled="actionLoading"
              >
                <i class="fas fa-exclamation-triangle"></i>
                Issue Warning
              </button>
              <button
                @click="handleAction('suspend')"
                class="btn btn-danger"
                :disabled="actionLoading"
              >
                <i class="fas fa-ban"></i>
                Suspend
              </button>
              <button
                v-if="reportType === 'post'"
                @click="handleAction('delete')"
                class="btn btn-dark"
                :disabled="actionLoading"
              >
                <i class="fas fa-trash"></i>
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue'
import { useReports } from '../../composables/useReports'

const {
  reports,
  reportDetails,
  reportable,
  reportType,
  loading,
  error,
  fetchReports,
  fetchReportDetails,
  handleReport
} = useReports()

const filters = reactive({
  type: '',
  sort_by: 'total',
  sort_direction: 'desc',
  page: 1,
  per_page: 15
})

const showDetailsModal = ref(false)
const actionLoading = ref(false)
const currentReportableType = ref(null)
const currentReportableId = ref(null)

const visiblePages = computed(() => {
  const pages = []
  const current = reports.value.current_page
  const last = reports.value.last_page

  let start = Math.max(1, current - 2)
  let end = Math.min(last, current + 2)

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

onMounted(async () => {
  await loadData()
})

async function loadData() {
  filters.page = 1
  await fetchReports(filters)
}

function changePage(page) {
  if (page < 1 || page > reports.value.last_page) return
  filters.page = page
  fetchReports(filters)
}

async function viewReportDetails(type, id) {
  currentReportableType.value = type
  currentReportableId.value = id
  await fetchReportDetails(type, id)
  showDetailsModal.value = true
}

function closeDetailsModal() {
  showDetailsModal.value = false
  currentReportableType.value = null
  currentReportableId.value = null
}

async function handleAction(action) {
  const actionNames = {
    warning: 'issue a warning',
    suspend: 'suspend',
    delete: 'delete'
  }

  if (!confirm(`Are you sure you want to ${actionNames[action]} this ${reportType.value}?`)) {
    return
  }

  const reason = prompt('Please provide a reason for this action:')
  if (!reason) return

  actionLoading.value = true

  try {
    await handleReport(currentReportableType.value, currentReportableId.value, action, reason)
    alert(`${reportType.value} ${actionNames[action]}d successfully`)
    closeDetailsModal()
    await loadData()
  } catch (err) {
    alert('Failed to handle report: ' + err.message)
  } finally {
    actionLoading.value = false
  }
}

function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function getStatusLabel(status) {
  const labels = {
    'active': 'Active',
    'banned': 'Banned',
    'published': 'Published',
    'archive': 'Archived',
    'not published': 'Draft'
  }
  return labels[status] || status || 'Unknown'
}
</script>

<style scoped>
.reports-management {
  padding: 1.5rem;
}

.page-header {
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
  color: #e74c3c;
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
}

.loading-container {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 8px;
}

.spinner {
  border: 3px solid #f3f3f3;
  border-top: 3px solid #e74c3c;
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

.reportable-info {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.reportable-image {
  width: 50px;
  height: 50px;
  border-radius: 4px;
  overflow: hidden;
}

.reportable-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.reportable-title {
  font-weight: 500;
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.badge-lg {
  font-size: 1rem;
  padding: 0.5rem 0.75rem;
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

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  max-width: 800px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #dee2e6;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.5rem;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #7f8c8d;
}

.btn-close:hover {
  color: #2c3e50;
}

.modal-body {
  padding: 1.5rem;
}

.reportable-detail-card {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}

.reportable-detail-card h4 {
  margin-top: 0;
  margin-bottom: 1rem;
}

.reportable-detail-content {
  display: flex;
  gap: 1.5rem;
}

.detail-image {
  width: 200px;
  height: 200px;
  object-fit: cover;
  border-radius: 8px;
}

.detail-info p {
  margin: 0.5rem 0;
}

.reports-list {
  margin-bottom: 1.5rem;
}

.reports-list h4 {
  margin-bottom: 1rem;
}

.report-item {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 4px;
  margin-bottom: 0.75rem;
}

.report-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.report-reason {
  color: #2c3e50;
}

.action-section h4 {
  margin-bottom: 1rem;
}

.action-buttons-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
}

.badge {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
}
</style>
