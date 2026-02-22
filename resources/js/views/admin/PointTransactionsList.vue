<template>
    <div class="transactions-modern">

        <!-- Stats Cards -->
        <div class="stats-grid mb-4">
            <div class="stat-card" v-for="stat in stats" :key="stat.label" :class="stat.color">
                <div class="stat-icon">
                    <i :class="stat.icon"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">{{ stat.value }}</h3>
                    <p class="stat-label">{{ stat.label }}</p>
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
                    placeholder="Search by ID, user, points..."
                >
            </div>
            <div class="filter-group">
                <select class="filter-select" v-model="filters.type" @change="loadTransactions">
                    <option value="">All Types</option>
                    <option v-for="type in types" :key="type.value" :value="type.value">
                        {{ type.label }}
                    </option>
                </select>
                <select class="filter-select" v-model="filters.sort_by" @change="loadTransactions">
                    <option value="created_at">Sort by Date</option>
                    <option value="point">Sort by Points</option>
                    <option value="type">Sort by Type</option>
                </select>
                <select class="filter-select" v-model="filters.sort_direction" @change="loadTransactions">
                    <option value="desc">Descending</option>
                    <option value="asc">Ascending</option>
                </select>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading transactions...</p>
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
                        <th>From User</th>
                        <th>To User</th>
                        <th>Points</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="transactions.data.length === 0">
                        <td colspan="8" class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No transactions found</p>
                        </td>
                    </tr>
                    <tr v-for="transaction in transactions.data" :key="transaction.id">
                        <td><span class="id-badge">{{ transaction.id }}</span></td>
                        <td>
                            <div v-if="transaction.from_user" class="user-cell">
                                <strong>{{ transaction.from_user.name }}</strong>
                                <span class="user-email">{{ transaction.from_user.email }}</span>
                            </div>
                            <span v-else class="text-muted">-</span>
                        </td>
                        <td>
                            <div v-if="transaction.to_user" class="user-cell">
                                <strong>{{ transaction.to_user.name }}</strong>
                                <span class="user-email">{{ transaction.to_user.email }}</span>
                            </div>
                            <span v-else class="text-muted">-</span>
                        </td>
                        <td>
                            <span class="points-badge">{{ transaction.point }} pts</span>
                        </td>
                        <td>
                            <span v-if="transaction.type === 'purchased'" class="badge success">
                                <i class="fas fa-shopping-cart"></i> Purchased
                            </span>
                            <span v-else-if="transaction.type === 'used'" class="badge warning">
                                <i class="fas fa-coins"></i> Used
                            </span>
                            <span v-else-if="transaction.type === 'transferred'" class="badge info">
                                <i class="fas fa-arrow-right"></i> Transferred
                            </span>
                            <span v-else-if="transaction.type === 'bonus'" class="badge primary">
                                <i class="fas fa-gift"></i> Bonus
                            </span>
                            <span v-else-if="transaction.type === 'refund'" class="badge secondary">
                                <i class="fas fa-undo"></i> Refund
                            </span>
                            <span v-else class="badge secondary">{{ transaction.type }}</span>
                        </td>
                        <td>
                            <span class="description-text">{{ transaction.description || '-' }}</span>
                        </td>
                        <td>
                            <span class="date-text">{{ formatDate(transaction.created_at) }}</span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button
                                    @click="openDetailsModal(transaction)"
                                    class="action-btn info small"
                                    title="View Details"
                                >
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button
                                    @click="openDeleteModal(transaction)"
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
        <div class="pagination-container" v-if="transactions.last_page > 1">
            <div class="pagination-info">
                Showing {{ ((transactions.current_page - 1) * transactions.per_page) + 1 }}
                to {{ Math.min(transactions.current_page * transactions.per_page, transactions.total) }}
                of {{ transactions.total }} entries
            </div>
            <div class="pagination-controls">
                <button
                    class="pagination-btn"
                    :disabled="transactions.current_page === 1"
                    @click="changePage(transactions.current_page - 1)"
                >
                    <i class="fas fa-chevron-left"></i>
                    Previous
                </button>
                <button
                    v-for="page in displayPages"
                    :key="page"
                    class="pagination-btn"
                    :class="{ active: page === transactions.current_page, dots: page === '...' }"
                    @click="page !== '...' && changePage(page)"
                    :disabled="page === '...'"
                >
                    {{ page }}
                </button>
                <button
                    class="pagination-btn"
                    :disabled="transactions.current_page === transactions.last_page"
                    @click="changePage(transactions.current_page + 1)"
                >
                    Next
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Details Modal -->
        <div class="modal-overlay" v-if="showDetailsModal" @click="closeDetailsModal">
            <div class="modern-modal" @click.stop>
                <div class="modal-header">
                    <h3><i class="fas fa-info-circle"></i> Transaction Details</h3>
                    <button class="close-btn" @click="closeDetailsModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body" v-if="selectedTransaction">
                    <div class="details-table">
                        <div class="detail-row">
                            <span class="detail-label">Transaction ID</span>
                            <span class="detail-value">{{ selectedTransaction.id }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">From User</span>
                            <span class="detail-value">
                                <div v-if="selectedTransaction.from_user">
                                    {{ selectedTransaction.from_user.name }}<br>
                                    <small class="text-muted">{{ selectedTransaction.from_user.email }}</small>
                                </div>
                                <span v-else class="text-muted">System</span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">To User</span>
                            <span class="detail-value">
                                <div v-if="selectedTransaction.to_user">
                                    {{ selectedTransaction.to_user.name }}<br>
                                    <small class="text-muted">{{ selectedTransaction.to_user.email }}</small>
                                </div>
                                <span v-else class="text-muted">System</span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Points</span>
                            <span class="detail-value"><strong>{{ selectedTransaction.point }} pts</strong></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Type</span>
                            <span class="detail-value">
                                <span :class="getTypeBadgeClass(selectedTransaction.type)">
                                    {{ getTypeLabel(selectedTransaction.type) }}
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Description</span>
                            <span class="detail-value">{{ selectedTransaction.description || '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Date</span>
                            <span class="detail-value">{{ formatDate(selectedTransaction.created_at) }}</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="action-btn secondary" @click="closeDetailsModal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal-overlay" v-if="showDeleteModal" @click="closeDeleteModal">
            <div class="modern-modal" @click.stop>
                <div class="modal-header danger">
                    <h3><i class="fas fa-exclamation-triangle"></i> Delete Transaction</h3>
                    <button class="close-btn" @click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="warning-text">Are you sure you want to delete this transaction?</p>
                    <div v-if="selectedTransaction" class="warning-box">
                        <div><strong>Transaction ID:</strong> {{ selectedTransaction.id }}</div>
                        <div><strong>Points:</strong> {{ selectedTransaction.point }} pts</div>
                        <div><strong>Type:</strong> {{ selectedTransaction.type }}</div>
                    </div>
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
import { usePointTransactions } from '../../composables/usePointTransactions'

const {
    transactions,
    stats,
    types,
    loading,
    error,
    fetchTransactions,
    fetchStats,
    fetchTypes,
    deleteTransaction
} = usePointTransactions()

const filters = ref({
    search: '',
    type: '',
    sort_by: 'created_at',
    sort_direction: 'desc',
    page: 1,
    per_page: 15
})

const selectedTransaction = ref(null)
const processing = ref(false)
const showDetailsModal = ref(false)
const showDeleteModal = ref(false)

let searchTimeout = null

const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        filters.value.page = 1
        loadTransactions()
    }, 500)
}

const loadTransactions = async () => {
    await fetchTransactions(filters.value)
}

const changePage = (page) => {
    if (page < 1 || page > transactions.value.last_page) return
    filters.value.page = page
    loadTransactions()
}

const displayPages = computed(() => {
    const current = transactions.value.current_page
    const last = transactions.value.last_page
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

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString()
}

const getTypeLabel = (type) => {
    const typeObj = types.value.find(t => t.value === type)
    return typeObj ? typeObj.label : type
}

const getTypeBadgeClass = (type) => {
    const classes = {
        'purchased': 'badge success',
        'used': 'badge warning',
        'transferred': 'badge info',
        'bonus': 'badge primary',
        'refund': 'badge secondary'
    }
    return classes[type] || 'badge secondary'
}

const openDetailsModal = (transaction) => {
    selectedTransaction.value = transaction
    showDetailsModal.value = true
}

const closeDetailsModal = () => {
    showDetailsModal.value = false
    selectedTransaction.value = null
}

const openDeleteModal = (transaction) => {
    selectedTransaction.value = transaction
    showDeleteModal.value = true
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
    selectedTransaction.value = null
}

const handleDelete = async () => {
    if (!selectedTransaction.value) return

    processing.value = true
    try {
        await deleteTransaction(selectedTransaction.value.id)
        closeDeleteModal()

        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'Transaction deleted successfully'
        })

        await loadTransactions()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to delete transaction'
        })
    } finally {
        processing.value = false
    }
}

onMounted(async () => {
    console.log('📋 PointTransactionsList component mounted')
    await fetchTypes()
    await loadTransactions()
    await fetchStats()
})
</script>

<style scoped>
.transactions-modern {
    padding: 0;
}

/* Header Section */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
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
    color: #3498db;
}

.section-subtitle {
    color: #666;
    font-size: 0.95rem;
    margin: 0;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.stat-card {
    border-radius: 12px;
    padding: 1.5rem;
    color: white;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
}

.stat-card.blue, .stat-card.primary {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.stat-card.green, .stat-card.success {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
}

.stat-card.orange, .stat-card.warning {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
}

.stat-card.purple {
    background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
}

.stat-card.red, .stat-card.danger {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.stat-card.info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}

.stat-card.secondary {
    background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 0.25rem 0;
    color: #ffffff;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.95;
    margin: 0;
    color: #ffffff;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
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
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
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
    border-color: #3498db;
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
    border-top: 4px solid #3498db;
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

.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.75rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge.success {
    background: #d4edda;
    color: #28a745;
}

.badge.warning {
    background: #fff3cd;
    color: #f39c12;
}

.badge.info {
    background: #d1ecf1;
    color: #17a2b8;
}

.badge.primary {
    background: #cfe2ff;
    color: #0d6efd;
}

.badge.secondary {
    background: #e2e3e5;
    color: #6c757d;
}

.description-text {
    font-size: 0.85rem;
    color: #666;
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

.action-btn.info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

.action-btn.info:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
}

.action-btn.danger {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
}

.action-btn.danger:hover {
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
    border-color: #3498db;
    color: #3498db;
}

.pagination-btn.active {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    border-color: #3498db;
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
    max-width: 600px;
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

.modal-header.danger {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
}

.modal-header.danger h3 {
    color: white;
}

.modal-header.danger .close-btn {
    color: white;
}

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

.details-table {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 6px;
}

.detail-label {
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
}

.detail-value {
    color: #666;
    font-size: 0.9rem;
    text-align: right;
}

.warning-text {
    font-size: 1rem;
    color: #333;
    margin-bottom: 1rem;
}

.warning-box {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.warning-box div {
    padding: 0.25rem 0;
}

.danger-text {
    color: #e74c3c;
    font-weight: 600;
    margin-top: 1rem;
    margin-bottom: 0;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.5rem;
    border-top: 1px solid #e8ecef;
}

.text-muted {
    color: #999;
}

/* Responsive */
@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
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
