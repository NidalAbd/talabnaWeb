<template>
    <div class="banned-devices-modern">
        <!-- Header Section -->
        <div class="section-header mb-4">
            <div class="header-content">
                <h1 class="section-title">
                    <i class="fas fa-mobile-alt"></i>
                    Banned Devices Management
                </h1>
                <p class="section-subtitle">Monitor and manage banned device identifiers</p>
            </div>
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
                    placeholder="Search by device ID, name, brand, model, or user..."
                >
            </div>
            <div class="filter-group">
                <select class="filter-select" v-model="filters.active_only" @change="loadBannedDevices">
                    <option :value="false">All Devices</option>
                    <option :value="true">Active Bans Only</option>
                </select>
                <select class="filter-select" v-model="filters.sort_by" @change="loadBannedDevices">
                    <option value="created_at">Sort by Ban Date</option>
                    <option value="device_id">Sort by Device ID</option>
                    <option value="unban_at">Sort by Unban Date</option>
                </select>
                <select class="filter-select" v-model="filters.sort_direction" @change="loadBannedDevices">
                    <option value="desc">Descending</option>
                    <option value="asc">Ascending</option>
                </select>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading banned devices...</p>
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
                        <th>Device ID</th>
                        <th>Device Info</th>
                        <th>User</th>
                        <th>IP Address</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Banned At</th>
                        <th>Unban At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="bannedDevices.data.length === 0">
                        <td colspan="10" class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No banned devices found</p>
                        </td>
                    </tr>
                    <tr v-for="device in bannedDevices.data" :key="device.id">
                        <td><span class="id-badge">{{ device.id }}</span></td>
                        <td>
                            <small class="device-id">{{ device.device_id }}</small>
                        </td>
                        <td>
                            <div class="device-info">
                                <strong>{{ device.device_name }}</strong>
                                <span class="device-details">
                                    {{ device.device_brand }} {{ device.device_model }}
                                </span>
                                <span class="device-os">OS: {{ device.os_version }}</span>
                            </div>
                        </td>
                        <td>
                            <span v-if="device.user" class="user-info">
                                {{ device.user.user_name }}
                                <span class="user-id">(ID: {{ device.user.id }})</span>
                            </span>
                            <span v-else class="text-muted">-</span>
                        </td>
                        <td>{{ device.ip_address || '-' }}</td>
                        <td>
                            <span class="reason-text">{{ device.reason || '-' }}</span>
                        </td>
                        <td>
                            <span v-if="device.is_active" class="badge danger">
                                <i class="fas fa-ban"></i> Active Ban
                            </span>
                            <span v-else class="badge success">
                                <i class="fas fa-check"></i> Unbanned
                            </span>
                        </td>
                        <td>{{ formatDate(device.created_at) }}</td>
                        <td>{{ device.unban_at ? formatDate(device.unban_at) : '-' }}</td>
                        <td>
                            <button
                                v-if="device.is_active"
                                @click="openUnbanModal(device)"
                                class="action-btn success small"
                                title="Unban Device"
                            >
                                <i class="fas fa-mobile-alt"></i> Unban
                            </button>
                            <span v-else class="text-muted">-</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container" v-if="bannedDevices.last_page > 1">
            <div class="pagination-info">
                Showing {{ ((bannedDevices.current_page - 1) * bannedDevices.per_page) + 1 }}
                to {{ Math.min(bannedDevices.current_page * bannedDevices.per_page, bannedDevices.total) }}
                of {{ bannedDevices.total }} entries
            </div>
            <div class="pagination-controls">
                <button
                    class="pagination-btn"
                    :disabled="bannedDevices.current_page === 1"
                    @click="changePage(bannedDevices.current_page - 1)"
                >
                    <i class="fas fa-chevron-left"></i>
                    Previous
                </button>
                <button
                    v-for="page in displayPages"
                    :key="page"
                    class="pagination-btn"
                    :class="{ active: page === bannedDevices.current_page, dots: page === '...' }"
                    @click="page !== '...' && changePage(page)"
                    :disabled="page === '...'"
                >
                    {{ page }}
                </button>
                <button
                    class="pagination-btn"
                    :disabled="bannedDevices.current_page === bannedDevices.last_page"
                    @click="changePage(bannedDevices.current_page + 1)"
                >
                    Next
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Unban Modal -->
        <div class="modal-overlay" v-if="showUnbanModal" @click="closeUnbanModal">
            <div class="modern-modal" @click.stop>
                <div class="modal-header">
                    <h3><i class="fas fa-mobile-alt"></i> Unban Device</h3>
                    <button class="close-btn" @click="closeUnbanModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="modal-text">Are you sure you want to unban this device?</p>
                    <div v-if="selectedDevice" class="device-details-box">
                        <div class="detail-row">
                            <strong>Device:</strong>
                            <span>{{ selectedDevice.device_name }}</span>
                        </div>
                        <div class="detail-row">
                            <strong>Brand/Model:</strong>
                            <span>{{ selectedDevice.device_brand }} {{ selectedDevice.device_model }}</span>
                        </div>
                        <div class="detail-row">
                            <strong>Device ID:</strong>
                            <span class="device-id">{{ selectedDevice.device_id }}</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Reason (Optional)</label>
                        <textarea
                            class="form-textarea"
                            v-model="unbanReason"
                            rows="3"
                            placeholder="Enter reason for unbanning..."
                        ></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="action-btn secondary" @click="closeUnbanModal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button class="action-btn success" @click="handleUnban" :disabled="processing">
                        <i :class="processing ? 'fas fa-spinner fa-spin' : 'fas fa-mobile-alt'"></i>
                        {{ processing ? 'Processing...' : 'Unban Device' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useBans } from '../../composables/useBans'

const { bannedDevices, stats, loading, error, fetchBannedDevices, fetchStats, unbanDevice } = useBans()

const filters = ref({
    search: '',
    active_only: false,
    sort_by: 'created_at',
    sort_direction: 'desc',
    page: 1,
    per_page: 15
})

const selectedDevice = ref(null)
const unbanReason = ref('')
const processing = ref(false)
const showUnbanModal = ref(false)

let searchTimeout = null

const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        filters.value.page = 1
        loadBannedDevices()
    }, 500)
}

const loadBannedDevices = async () => {
    await fetchBannedDevices(filters.value)
}

const changePage = (page) => {
    if (page < 1 || page > bannedDevices.value.last_page) return
    filters.value.page = page
    loadBannedDevices()
}

const displayPages = computed(() => {
    const current = bannedDevices.value.current_page
    const last = bannedDevices.value.last_page
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
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const openUnbanModal = (device) => {
    selectedDevice.value = device
    unbanReason.value = ''
    showUnbanModal.value = true
}

const closeUnbanModal = () => {
    showUnbanModal.value = false
    selectedDevice.value = null
    unbanReason.value = ''
}

const handleUnban = async () => {
    if (!selectedDevice.value) return

    processing.value = true
    try {
        await unbanDevice(selectedDevice.value.id, unbanReason.value)

        closeUnbanModal()

        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'Device has been unbanned successfully'
        })

        await loadBannedDevices()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to unban device'
        })
    } finally {
        processing.value = false
    }
}

onMounted(async () => {
    console.log('📋 BannedDevicesList component mounted')
    await loadBannedDevices()
    await fetchStats()
})
</script>

<style scoped>
.banned-devices-modern {
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
    color: #e74c3c;
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
    border-color: #e74c3c;
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
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
    border-color: #e74c3c;
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
    border-top: 4px solid #e74c3c;
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

.device-id {
    font-family: monospace;
    background: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    color: #666;
    font-size: 0.8rem;
}

.device-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.device-info strong {
    color: #1a1a1a;
}

.device-details,
.device-os {
    font-size: 0.85rem;
    color: #666;
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.user-id {
    font-size: 0.8rem;
    color: #999;
}

.reason-text {
    font-size: 0.85rem;
    color: #666;
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

.badge.danger {
    background: #fee;
    color: #e74c3c;
}

.badge.success {
    background: #d4edda;
    color: #28a745;
}

/* Action Buttons */
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

.action-btn.success:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(39, 174, 96, 0.3);
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
    border-color: #e74c3c;
    color: #e74c3c;
}

.pagination-btn.active {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    border-color: #e74c3c;
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
    color: #666;
    margin-bottom: 1rem;
}

.device-details-box {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e8ecef;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row strong {
    color: #333;
    font-size: 0.9rem;
}

.detail-row span {
    color: #666;
    font-size: 0.9rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e8ecef;
    border-radius: 8px;
    font-size: 0.9rem;
    font-family: inherit;
    resize: vertical;
    transition: all 0.3s ease;
}

.form-textarea:focus {
    outline: none;
    border-color: #e74c3c;
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
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
        min-width: 1000px;
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
