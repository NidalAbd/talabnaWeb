<template>
    <div class="purchase-requests-modern">
        <!-- Action Bar -->
        <div class="action-bar mb-4">
            <button class="action-btn success" @click="handleBulkApprove" :disabled="processing">
                <i class="fas fa-check-double"></i> Approve All Pending
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="stats-dashboard">
          <div class="stats-grid">
            <div class="stat-card-compact" :class="stat.color" v-for="stat in stats" :key="stat.label">
              <div class="stat-icon"><i :class="stat.icon"></i></div>
              <div class="stat-info">
                <div class="stat-value-compact">{{ formatNumber(stat.value) }}</div>
                <div class="stat-label-compact">{{ stat.label }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Search & Filters -->
        <div class="search-filter-bar">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input
                    type="text"
                    v-model="filters.search"
                    @input="debouncedSearch"
                    class="search-input"
                    placeholder="Search by ID, user name, email..."
                >
            </div>
            <div class="filter-group">
                <select class="filter-select" v-model="filters.status" @change="loadRequests">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select class="filter-select" v-model="filters.sort_by" @change="loadRequests">
                    <option value="created_at">Sort by Date</option>
                    <option value="status">Sort by Status</option>
                    <option value="points_requested">Sort by Points</option>
                    <option value="total_price">Sort by Price</option>
                </select>
                <select class="filter-select" v-model="filters.sort_direction" @change="loadRequests">
                    <option value="desc">Descending</option>
                    <option value="asc">Ascending</option>
                </select>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading purchase requests...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="error-state">
            <i class="fas fa-exclamation-circle"></i>
            <p>{{ error }}</p>
        </div>

        <!-- Table -->
        <div v-else class="data-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Points Requested</th>
                        <th>Price Per Point</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="requests.data.length === 0">
                        <td colspan="8" class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No purchase requests found</p>
                        </td>
                    </tr>
                    <tr v-for="request in requests.data" :key="request.id">
                        <td><span class="id-badge">{{ request.id }}</span></td>
                        <td>
                            <div v-if="request.user.id" class="user-cell">
                                <strong>{{ request.user.name }}</strong>
                                <span class="user-email">{{ request.user.email }}</span>
                                <span v-if="request.user.is_active === 'active'" class="badge success small">Active</span>
                                <span v-else class="badge danger small">{{ request.user.is_active }}</span>
                            </div>
                            <span v-else class="text-muted">User Deleted</span>
                        </td>
                        <td>
                            <span class="points-badge">{{ request.points_requested }} pts</span>
                        </td>
                        <td>{{ request.price_per_point }} ILS</td>
                        <td><strong class="price-text">{{ request.total_price }} ILS</strong></td>
                        <td>
                            <span v-if="request.status === 'pending'" class="badge warning">
                                <i class="fas fa-clock"></i> Pending
                            </span>
                            <span v-else-if="request.status === 'approved'" class="badge success">
                                <i class="fas fa-check-circle"></i> Approved
                            </span>
                            <span v-else-if="request.status === 'cancelled'" class="badge danger">
                                <i class="fas fa-times-circle"></i> Cancelled
                            </span>
                        </td>
                        <td>
                            <span class="date-text">{{ formatDate(request.created_at) }}</span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button
                                    v-if="request.status === 'pending'"
                                    @click="openApproveModal(request)"
                                    class="action-btn success small"
                                    title="Approve"
                                >
                                    <i class="fas fa-check"></i>
                                </button>
                                <button
                                    v-if="request.status === 'pending'"
                                    @click="openCancelModal(request)"
                                    class="action-btn warning small"
                                    title="Cancel"
                                >
                                    <i class="fas fa-ban"></i>
                                </button>
                                <button
                                    v-if="request.status !== 'approved'"
                                    @click="openDeleteModal(request)"
                                    class="action-btn danger small"
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
        <div class="pagination-container" v-if="requests.last_page > 1">
            <div class="pagination-info">
                Showing {{ ((requests.current_page - 1) * requests.per_page) + 1 }}
                to {{ Math.min(requests.current_page * requests.per_page, requests.total) }}
                of {{ requests.total }} entries
            </div>
            <div class="pagination-controls">
                <button
                    class="pagination-btn"
                    :disabled="requests.current_page === 1"
                    @click="changePage(requests.current_page - 1)"
                >
                    <i class="fas fa-chevron-left"></i>
                    Previous
                </button>
                <button
                    v-for="page in displayPages"
                    :key="page"
                    class="pagination-btn"
                    :class="{ active: page === requests.current_page, dots: page === '...' }"
                    @click="page !== '...' && changePage(page)"
                    :disabled="page === '...'"
                >
                    {{ page }}
                </button>
                <button
                    class="pagination-btn"
                    :disabled="requests.current_page === requests.last_page"
                    @click="changePage(requests.current_page + 1)"
                >
                    Next
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Approve Modal -->
        <div class="modal-overlay" v-if="showApproveModal" @click="closeApproveModal">
            <div class="modern-modal" @click.stop>
                <div class="modal-header success">
                    <h3><i class="fas fa-check-circle"></i> Approve Purchase Request</h3>
                    <button class="close-btn" @click="closeApproveModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="modal-text">Are you sure you want to approve this purchase request?</p>
                    <div v-if="selectedRequest" class="info-box">
                        <div class="info-row">
                            <strong>User:</strong>
                            <span>{{ selectedRequest.user?.name }}</span>
                        </div>
                        <div class="info-row">
                            <strong>Points:</strong>
                            <span>{{ selectedRequest.points_requested }} pts</span>
                        </div>
                        <div class="info-row">
                            <strong>Total Price:</strong>
                            <span>{{ selectedRequest.total_price }} ILS</span>
                        </div>
                    </div>
                    <p class="text-muted">This will add the points to the user's account.</p>
                </div>
                <div class="modal-footer">
                    <button class="action-btn secondary" @click="closeApproveModal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button class="action-btn success" @click="handleApprove" :disabled="processing">
                        <i :class="processing ? 'fas fa-spinner fa-spin' : 'fas fa-check'"></i>
                        {{ processing ? 'Processing...' : 'Approve' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Cancel Modal -->
        <div class="modal-overlay" v-if="showCancelModal" @click="closeCancelModal">
            <div class="modern-modal" @click.stop>
                <div class="modal-header warning">
                    <h3><i class="fas fa-ban"></i> Cancel Purchase Request</h3>
                    <button class="close-btn" @click="closeCancelModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="modal-text">Are you sure you want to cancel this purchase request?</p>
                    <div v-if="selectedRequest" class="info-box">
                        <div class="info-row">
                            <strong>User:</strong>
                            <span>{{ selectedRequest.user?.name }}</span>
                        </div>
                        <div class="info-row">
                            <strong>Points:</strong>
                            <span>{{ selectedRequest.points_requested }} pts</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="action-btn secondary" @click="closeCancelModal">
                        <i class="fas fa-times"></i> No
                    </button>
                    <button class="action-btn warning" @click="handleCancel" :disabled="processing">
                        <i :class="processing ? 'fas fa-spinner fa-spin' : 'fas fa-ban'"></i>
                        {{ processing ? 'Processing...' : 'Cancel Request' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal-overlay" v-if="showDeleteModal" @click="closeDeleteModal">
            <div class="modern-modal" @click.stop>
                <div class="modal-header danger">
                    <h3><i class="fas fa-exclamation-triangle"></i> Delete Purchase Request</h3>
                    <button class="close-btn" @click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="modal-text">Are you sure you want to delete this purchase request?</p>
                    <p class="danger-text">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button class="action-btn secondary" @click="closeDeleteModal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button class="action-btn danger" @click="handleDelete" :disabled="processing">
                        <i :class="processing ? 'fas fa-spinner fa-spin' : 'fas fa-trash'"></i>
                        {{ processing ? 'Deleting...' : 'Delete' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePointPurchaseRequests } from '../../composables/usePointPurchaseRequests'

const {
    requests,
    stats,
    loading,
    error,
    fetchRequests,
    fetchStats,
    approveRequest,
    cancelRequest,
    deleteRequest,
    bulkApprove
} = usePointPurchaseRequests()

const filters = ref({
    search: '',
    status: '',
    sort_by: 'created_at',
    sort_direction: 'desc',
    page: 1,
    per_page: 15
})

const selectedRequest = ref(null)
const processing = ref(false)
const showApproveModal = ref(false)
const showCancelModal = ref(false)
const showDeleteModal = ref(false)

let searchTimeout = null

const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        filters.value.page = 1
        loadRequests()
    }, 500)
}

const loadRequests = async () => {
    await fetchRequests(filters.value)
}

const changePage = (page) => {
    if (page < 1 || page > requests.value.last_page) return
    filters.value.page = page
    loadRequests()
}

const displayPages = computed(() => {
    const current = requests.value.current_page
    const last = requests.value.last_page
    const delta = 2
    const range = []
    const rangeWithDots = []

    for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
        range.push(i)
    }

    if (current - delta > 2) {
        rangeWithDots.push(1, '...')
    } else {
        rangeWithDots.push(1)
    }

    rangeWithDots.push(...range)

    if (current + delta < last - 1) {
        rangeWithDots.push('...', last)
    } else if (last > 1) {
        rangeWithDots.push(last)
    }

    return rangeWithDots.filter(p => p !== '...' || rangeWithDots.indexOf(p) === rangeWithDots.lastIndexOf(p))
})

const formatNumber = (value) => {
  if (value === null || value === undefined) return '0'
  return new Intl.NumberFormat().format(value)
}

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString()
}

const openApproveModal = (request) => {
    selectedRequest.value = request
    showApproveModal.value = true
}

const closeApproveModal = () => {
    showApproveModal.value = false
    selectedRequest.value = null
}

const openCancelModal = (request) => {
    selectedRequest.value = request
    showCancelModal.value = true
}

const closeCancelModal = () => {
    showCancelModal.value = false
    selectedRequest.value = null
}

const openDeleteModal = (request) => {
    selectedRequest.value = request
    showDeleteModal.value = true
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
    selectedRequest.value = null
}

const handleApprove = async () => {
    if (!selectedRequest.value) return

    processing.value = true
    try {
        await approveRequest(selectedRequest.value.id)
        closeApproveModal()

        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'Purchase request approved successfully'
        })

        await loadRequests()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to approve purchase request'
        })
    } finally {
        processing.value = false
    }
}

const handleCancel = async () => {
    if (!selectedRequest.value) return

    processing.value = true
    try {
        await cancelRequest(selectedRequest.value.id)
        closeCancelModal()

        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'Purchase request cancelled successfully'
        })

        await loadRequests()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to cancel purchase request'
        })
    } finally {
        processing.value = false
    }
}

const handleDelete = async () => {
    if (!selectedRequest.value) return

    processing.value = true
    try {
        await deleteRequest(selectedRequest.value.id)
        closeDeleteModal()

        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'Purchase request deleted successfully'
        })

        await loadRequests()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to delete purchase request'
        })
    } finally {
        processing.value = false
    }
}

const handleBulkApprove = async () => {
    if (!confirm('Are you sure you want to approve all pending purchase requests? This will add points to all users with pending requests.')) {
        return
    }

    processing.value = true
    try {
        const result = await bulkApprove()

        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: result.message
        })

        await loadRequests()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to approve requests'
        })
    } finally {
        processing.value = false
    }
}

onMounted(async () => {
    console.log('📋 PointPurchaseRequestsList component mounted')
    await loadRequests()
    await fetchStats()
})
</script>

<style scoped>
.purchase-requests-modern {
    padding: 0;
}

/* Header Section */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.header-content {
    flex: 1;
}

.section-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0 0 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-title i {
    color: #27ae60;
}

.section-subtitle {
    color: #666;
    font-size: 0.95rem;
    margin: 0;
}

/* Search & Filters */
.search-filter-bar {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    margin-bottom: 1.5rem;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
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
    font-size: 1rem;
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.75rem;
    border: 2px solid #e8ecef;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #27ae60;
    box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1);
}

.filter-group {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.filter-select {
    padding: 0.75rem 1rem;
    border: 2px solid #e8ecef;
    border-radius: 8px;
    font-size: 0.9rem;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #27ae60;
}

/* Loading & Error States */
.loading-state,
.error-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.spinner {
    width: 50px;
    height: 50px;
    margin: 0 auto 1rem;
    border: 4px solid #f3f4f6;
    border-top: 4px solid #27ae60;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.error-state i {
    font-size: 3rem;
    color: #e74c3c;
    margin-bottom: 1rem;
}

/* Data Table */
.data-table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-table thead {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
}

.modern-table th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: white;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.modern-table tbody tr {
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s ease;
}

.modern-table tbody tr:hover {
    background-color: #f8f9fa;
}

.modern-table td {
    padding: 1rem;
    font-size: 0.9rem;
    color: #333;
}

.empty-state {
    text-align: center;
    padding: 3rem !important;
    color: #999;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
    opacity: 0.5;
}

.id-badge {
    background: #e8ecef;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.85rem;
}

.user-cell {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.user-email {
    font-size: 0.8rem;
    color: #999;
}

.points-badge {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-block;
}

.price-text {
    color: #27ae60;
    font-size: 1rem;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.75rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge.small {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.badge.success {
    background: #d4edda;
    color: #28a745;
}

.badge.warning {
    background: #fff3cd;
    color: #f39c12;
}

.badge.danger {
    background: #fee;
    color: #e74c3c;
}

.date-text {
    font-size: 0.85rem;
    color: #666;
    white-space: nowrap;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.action-btn.small {
    padding: 0.4rem 0.75rem;
    font-size: 0.85rem;
}

.action-btn.success {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
}

.action-btn.success:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(39, 174, 96, 0.3);
}

.action-btn.warning {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.action-btn.warning:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(243, 156, 18, 0.3);
}

.action-btn.danger {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
}

.action-btn.danger:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
}

.action-btn.secondary {
    background: #6c757d;
    color: white;
}

.action-btn.secondary:hover {
    background: #5a6268;
}

.action-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.5rem;
    padding: 1rem 1.5rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
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
    flex-wrap: wrap;
}

.pagination-btn {
    padding: 0.5rem 0.75rem;
    border: 2px solid #e8ecef;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.pagination-btn:hover:not(:disabled):not(.dots) {
    border-color: #27ae60;
    color: #27ae60;
}

.pagination-btn.active {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    border-color: #27ae60;
    color: white;
}

.pagination-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.pagination-btn.dots {
    border: none;
    cursor: default;
}

/* Modal */
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
    z-index: 1050;
    padding: 1rem;
}

.modern-modal {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    max-width: 500px;
    width: 100%;
    max-height: 90vh;
    overflow: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e8ecef;
}

.modal-header.success {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
}

.modal-header.warning {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.modal-header.danger {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
}

.modal-header.success h3,
.modal-header.warning h3,
.modal-header.danger h3 {
    color: white;
}

.modal-header.success .close-btn,
.modal-header.warning .close-btn,
.modal-header.danger .close-btn {
    color: white;
}

.modal-header.success .close-btn:hover,
.modal-header.warning .close-btn:hover,
.modal-header.danger .close-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #999;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.close-btn:hover {
    background: #f8f9fa;
    color: #333;
}

.modal-body {
    padding: 1.5rem;
}

.modal-text {
    font-size: 1rem;
    color: #333;
    margin-bottom: 1rem;
}

.info-box {
    background: #e3f2fd;
    border: 1px solid #2196f3;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(33, 150, 243, 0.2);
}

.info-row:last-child {
    border-bottom: none;
}

.info-row strong {
    color: #333;
}

.info-row span {
    color: #666;
}

.text-muted {
    color: #999;
    font-size: 0.9rem;
}

.danger-text {
    color: #e74c3c;
    font-weight: 600;
    margin-bottom: 0;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.5rem;
    border-top: 1px solid #e8ecef;
}

.mb-4 {
    margin-bottom: 1.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .search-filter-bar {
        flex-direction: column;
    }

    .search-box {
        min-width: 100%;
    }

    .filter-group {
        width: 100%;
    }

    .filter-select {
        flex: 1;
    }

    .data-table-container {
        overflow-x: auto;
    }

    .modern-table {
        min-width: 1100px;
    }

    .pagination-container {
        flex-direction: column;
        align-items: flex-start;
    }

    .pagination-controls {
        width: 100%;
        justify-content: center;
    }
}
</style>
