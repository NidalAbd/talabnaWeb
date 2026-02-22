<template>
    <div class="modern-notifications-container">
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

        <!-- Send Notification Card -->
        <div class="modern-card send-card">
            <div class="card-header">
                <div class="header-left">
                    <i class="fas fa-paper-plane"></i>
                    <h2>Send Push Notification</h2>
                </div>
            </div>

            <div class="card-body">
                <form @submit.prevent="handleSend">
                    <div class="form-group">
                        <label>Title *</label>
                        <input
                            type="text"
                            v-model="formData.title"
                            maxlength="100"
                            required
                            placeholder="Enter notification title"
                        >
                        <small>{{ formData.title.length }}/100 characters</small>
                    </div>

                    <div class="form-group">
                        <label>Message *</label>
                        <textarea
                            v-model="formData.body"
                            rows="4"
                            maxlength="500"
                            required
                            placeholder="Enter notification message"
                        ></textarea>
                        <small>{{ formData.body.length }}/500 characters</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Image URL (Optional)</label>
                            <input
                                type="url"
                                v-model="formData.image_url"
                                placeholder="https://example.com/image.jpg"
                            >
                        </div>
                        <div class="form-group">
                            <label>Deep Link (Optional)</label>
                            <input
                                type="text"
                                v-model="formData.deep_link"
                                placeholder="talabna://page/details"
                            >
                        </div>
                    </div>

                    <div class="form-group" v-if="showTestUser">
                        <label>Test User</label>
                        <select v-model="formData.user_id">
                            <option value="">Select a user for testing</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.user_name }} ({{ user.email }})
                            </option>
                        </select>
                    </div>

                    <div class="button-group">
                        <button
                            type="submit"
                            class="btn-send-all"
                            :disabled="processing"
                            @click="sendType = 'all'"
                        >
                            <i v-if="processing && sendType === 'all'" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-paper-plane"></i>
                            {{ processing && sendType === 'all' ? 'Sending to All...' : 'Send to All Users' }}
                        </button>

                        <button
                            type="button"
                            class="btn-test"
                            :disabled="processing"
                            @click="toggleTestMode"
                        >
                            <i class="fas fa-vial"></i>
                            {{ showTestUser ? 'Cancel Test' : 'Send Test' }}
                        </button>

                        <button
                            v-if="showTestUser"
                            type="button"
                            class="btn-send-test"
                            :disabled="processing || !formData.user_id"
                            @click="handleSendTest"
                        >
                            <i v-if="processing && sendType === 'test'" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-flask"></i>
                            {{ processing && sendType === 'test' ? 'Sending Test...' : 'Send Test Notification' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Notification History -->
        <div class="modern-card">
            <div class="card-header">
                <div class="header-left">
                    <i class="fas fa-history"></i>
                    <h2>Notification History</h2>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-section">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input
                        type="text"
                        v-model="filters.search"
                        @input="debouncedSearch"
                        placeholder="Search by title, message, or admin..."
                    >
                </div>
                <select class="filter-select" v-model="filters.sort_by" @change="loadLogs">
                    <option value="created_at">Sort by Date</option>
                    <option value="total_recipients">Sort by Recipients</option>
                    <option value="successful_count">Sort by Successful</option>
                    <option value="failed_count">Sort by Failed</option>
                </select>
                <select class="filter-select" v-model="filters.sort_direction" @change="loadLogs">
                    <option value="desc">Descending</option>
                    <option value="asc">Ascending</option>
                </select>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="loading-state">
                <div class="spinner"></div>
                <p>Loading notification logs...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="error-state">
                <i class="fas fa-exclamation-circle"></i>
                <p>{{ error }}</p>
            </div>

            <!-- Table -->
            <div v-else class="table-container">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title & Message</th>
                            <th>Recipients</th>
                            <th>Successful</th>
                            <th>Failed</th>
                            <th>Sent By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="logs.data.length === 0">
                            <td colspan="8" class="empty-state">
                                <i class="fas fa-bell-slash"></i>
                                <p>No notification logs found</p>
                            </td>
                        </tr>
                        <tr v-for="log in logs.data" :key="log.id">
                            <td>
                                <span class="id-badge">{{ log.id }}</span>
                            </td>
                            <td>
                                <div class="notification-info">
                                    <div class="notification-title">{{ log.title }}</div>
                                    <div class="notification-body">{{ truncate(log.body, 50) }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="count-badge recipients">{{ log.total_recipients }}</span>
                            </td>
                            <td>
                                <span class="count-badge success">{{ log.successful_count }}</span>
                            </td>
                            <td>
                                <span class="count-badge failed">{{ log.failed_count }}</span>
                            </td>
                            <td>
                                <div class="admin-info">{{ log.admin_name || 'Unknown' }}</div>
                            </td>
                            <td>
                                <div class="date-info">{{ formatDate(log.created_at) }}</div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button @click="openDetailsModal(log)" class="action-btn view" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button @click="openDeleteModal(log)" class="action-btn delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-container" v-if="logs.last_page > 1">
                <div class="pagination-info">
                    Showing {{ ((logs.current_page - 1) * logs.per_page) + 1 }}
                    to {{ Math.min(logs.current_page * logs.per_page, logs.total) }}
                    of {{ logs.total }} entries
                </div>
                <div class="pagination-controls">
                    <button
                        @click="changePage(logs.current_page - 1)"
                        :disabled="logs.current_page === 1"
                        class="page-btn"
                    >
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button
                        v-for="page in displayPages"
                        :key="page"
                        @click="changePage(page)"
                        :class="['page-btn', { active: page === logs.current_page }]"
                    >
                        {{ page }}
                    </button>
                    <button
                        @click="changePage(logs.current_page + 1)"
                        :disabled="logs.current_page === logs.last_page"
                        class="page-btn"
                    >
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Details Modal -->
        <div class="modal-overlay" v-if="showDetailsModal" @click="closeDetailsModal">
            <div class="modern-modal large" @click.stop>
                <div class="modal-header">
                    <h3><i class="fas fa-info-circle"></i> Notification Details</h3>
                    <button class="close-btn" @click="closeDetailsModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body" v-if="selectedLog">
                    <div class="details-table">
                        <div class="detail-row">
                            <div class="detail-label">Campaign ID</div>
                            <div class="detail-value">{{ selectedLog.id }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Title</div>
                            <div class="detail-value"><strong>{{ selectedLog.title }}</strong></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Message</div>
                            <div class="detail-value">{{ selectedLog.body }}</div>
                        </div>
                        <div class="detail-row" v-if="selectedLog.image_url">
                            <div class="detail-label">Image URL</div>
                            <div class="detail-value">
                                <a :href="selectedLog.image_url" target="_blank" class="link">{{ selectedLog.image_url }}</a>
                            </div>
                        </div>
                        <div class="detail-row" v-if="selectedLog.deep_link">
                            <div class="detail-label">Deep Link</div>
                            <div class="detail-value"><code>{{ selectedLog.deep_link }}</code></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Total Recipients</div>
                            <div class="detail-value">
                                <span class="count-badge recipients large">{{ selectedLog.total_recipients }}</span>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Successful Deliveries</div>
                            <div class="detail-value">
                                <span class="count-badge success large">{{ selectedLog.successful_count }}</span>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Failed Deliveries</div>
                            <div class="detail-value">
                                <span class="count-badge failed large">{{ selectedLog.failed_count }}</span>
                            </div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Sent By</div>
                            <div class="detail-value">{{ selectedLog.admin_name || 'Unknown' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Date Sent</div>
                            <div class="detail-value">{{ formatDate(selectedLog.created_at) }}</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="closeDetailsModal" class="btn-cancel">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal-overlay" v-if="showDeleteModal" @click="closeDeleteModal">
            <div class="modern-modal small" @click.stop>
                <div class="modal-header danger">
                    <h3><i class="fas fa-exclamation-triangle"></i> Delete Notification Log</h3>
                    <button class="close-btn" @click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this notification log?</p>
                    <div v-if="selectedLog" class="warning-box">
                        <strong>Campaign:</strong> {{ selectedLog.title }}<br>
                        <strong>Recipients:</strong> {{ selectedLog.total_recipients }}
                    </div>
                    <p class="warning-text"><i class="fas fa-exclamation-circle"></i> This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button @click="closeDeleteModal" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button @click="handleDelete" :disabled="processing" class="btn-danger">
                        <i v-if="processing" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-trash"></i>
                        {{ processing ? 'Deleting...' : 'Delete Log' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useMarketingNotifications } from '../../composables/useMarketingNotifications'

const {
    logs,
    stats,
    users,
    loading,
    error,
    fetchLogs,
    fetchStats,
    fetchActiveUsers,
    sendToAll,
    sendTest,
    deleteLog
} = useMarketingNotifications()

const formatNumber = (value) => {
    if (value === null || value === undefined) return '0'
    return new Intl.NumberFormat().format(value)
}

const filters = ref({
    search: '',
    sort_by: 'created_at',
    sort_direction: 'desc',
    page: 1,
    per_page: 15
})

const formData = ref({
    title: '',
    body: '',
    image_url: '',
    deep_link: '',
    user_id: ''
})

const showDetailsModal = ref(false)
const showDeleteModal = ref(false)
const selectedLog = ref(null)
const processing = ref(false)
const showTestUser = ref(false)
const sendType = ref('all')

let searchTimeout = null

const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        filters.value.page = 1
        loadLogs()
    }, 500)
}

const loadLogs = async () => {
    await fetchLogs(filters.value)
}

const changePage = (page) => {
    if (page < 1 || page > logs.value.last_page) return
    filters.value.page = page
    loadLogs()
}

const displayPages = computed(() => {
    const current = logs.value.current_page
    const last = logs.value.last_page
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

const truncate = (text, length) => {
    if (!text) return ''
    return text.length > length ? text.substring(0, length) + '...' : text
}

const toggleTestMode = async () => {
    showTestUser.value = !showTestUser.value
    if (showTestUser.value && users.value.length === 0) {
        await fetchActiveUsers()
    }
}

const resetForm = () => {
    formData.value = {
        title: '',
        body: '',
        image_url: '',
        deep_link: '',
        user_id: ''
    }
}

const handleSend = async () => {
    if (!confirm(`Are you sure you want to send this notification to all users? This will send to approximately ${stats.value[1]?.value || 0} users.`)) {
        return
    }

    processing.value = true
    sendType.value = 'all'

    try {
        const result = await sendToAll(formData.value)

        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: `Notification sent! Success: ${result.result.successful}, Failed: ${result.result.failed}`,
            autohide: true,
            delay: 5000
        })

        resetForm()
        await loadLogs()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to send notification'
        })
    } finally {
        processing.value = false
    }
}

const handleSendTest = async () => {
    if (!formData.value.user_id) {
        window.$(document).Toasts('create', {
            class: 'bg-warning',
            title: 'Warning',
            body: 'Please select a user for testing'
        })
        return
    }

    processing.value = true
    sendType.value = 'test'

    try {
        const result = await sendTest(formData.value)

        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: `Test notification sent to ${result.user.name}`
        })

        showTestUser.value = false
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to send test notification'
        })
    } finally {
        processing.value = false
    }
}

const openDetailsModal = (log) => {
    selectedLog.value = log
    showDetailsModal.value = true
}

const closeDetailsModal = () => {
    showDetailsModal.value = false
    selectedLog.value = null
}

const openDeleteModal = (log) => {
    selectedLog.value = log
    showDeleteModal.value = true
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
    selectedLog.value = null
}

const handleDelete = async () => {
    if (!selectedLog.value) return

    processing.value = true
    try {
        await deleteLog(selectedLog.value.id)
        closeDeleteModal()

        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'Notification log deleted successfully'
        })

        await loadLogs()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to delete log'
        })
    } finally {
        processing.value = false
    }
}

onMounted(async () => {
    console.log('📋 MarketingNotificationsList component mounted')
    await loadLogs()
    await fetchStats()
})
</script>

<style scoped>
.modern-notifications-container {
    padding: 20px;
}

/* Modern Card */
.modern-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    margin-bottom: 30px;
}

.send-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 25px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.header-left i {
    font-size: 1.8rem;
}

.header-left h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.card-body {
    padding: 30px;
}

/* Form Styles */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group small {
    display: block;
    color: #6c757d;
    font-size: 12px;
    margin-top: 5px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.button-group {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-send-all,
.btn-test,
.btn-send-test {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    font-size: 15px;
}

.btn-send-all {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
}

.btn-test {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.btn-send-test {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.btn-send-all:hover,
.btn-test:hover,
.btn-send-test:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.btn-send-all:disabled,
.btn-test:disabled,
.btn-send-test:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Filters Section */
.filters-section {
    padding: 25px 30px;
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.search-box {
    flex: 1;
    min-width: 250px;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

.search-box input {
    width: 100%;
    padding: 10px 15px 10px 45px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filter-select {
    padding: 10px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Loading & Error States */
.loading-state,
.error-state {
    padding: 60px;
    text-align: center;
}

.spinner {
    width: 50px;
    height: 50px;
    margin: 0 auto 20px;
    border: 4px solid #f3f4f6;
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.error-state {
    color: #dc3545;
}

.error-state i {
    font-size: 3rem;
    margin-bottom: 15px;
}

/* Table Container */
.table-container {
    padding: 30px;
    overflow-x: auto;
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.modern-table thead th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.modern-table tbody tr {
    border-bottom: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.modern-table tbody tr:hover {
    background: #f8f9fa;
}

.modern-table tbody td {
    padding: 15px;
    vertical-align: middle;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

.id-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    display: inline-block;
}

.notification-info {
    max-width: 300px;
}

.notification-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
}

.notification-body {
    font-size: 13px;
    color: #6c757d;
}

.count-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    color: white;
    display: inline-block;
}

.count-badge.recipients {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.count-badge.success {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.count-badge.failed {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.count-badge.large {
    font-size: 1rem;
    padding: 8px 16px;
}

.admin-info,
.date-info {
    font-size: 13px;
    color: #6c757d;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.action-btn {
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: white;
}

.action-btn.view {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.action-btn.delete {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 30px;
    border-top: 1px solid #e9ecef;
    flex-wrap: wrap;
    gap: 15px;
}

.pagination-info {
    color: #6c757d;
    font-size: 14px;
}

.pagination-controls {
    display: flex;
    gap: 5px;
}

.page-btn {
    padding: 8px 12px;
    border: 1px solid #e9ecef;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #495057;
    font-weight: 500;
}

.page-btn:hover:not(:disabled) {
    background: #f8f9fa;
    border-color: #667eea;
}

.page-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}

.page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Modal Overlay */
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
    z-index: 9999;
    padding: 20px;
}

.modern-modal {
    background: white;
    border-radius: 15px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.modern-modal.small {
    max-width: 500px;
}

.modern-modal.large {
    max-width: 800px;
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 15px 15px 0 0;
}

.modal-header.danger {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.modal-header h3 {
    margin: 0;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.close-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.modal-body {
    padding: 30px;
}

.modal-body p {
    margin: 10px 0;
    color: #495057;
}

.details-table {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.detail-row {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 20px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
}

.detail-label {
    font-weight: 600;
    color: #495057;
}

.detail-value {
    color: #2c3e50;
}

.detail-value .link {
    color: #667eea;
    text-decoration: none;
}

.detail-value .link:hover {
    text-decoration: underline;
}

.detail-value code {
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 4px;
    font-family: monospace;
}

.warning-box {
    background: #fff3cd;
    border: 1px solid #ffc107;
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
}

.warning-text {
    color: #dc3545;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 15px;
    padding: 10px;
    background: #fff5f5;
    border-radius: 8px;
}

.modal-footer {
    padding: 20px 30px;
    background: #f8f9fa;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    border-radius: 0 0 15px 15px;
}

.btn-cancel,
.btn-danger {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-cancel {
    background: #6c757d;
    color: white;
}

.btn-danger {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.btn-cancel:hover,
.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.btn-danger:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Responsive Design */
@media (max-width: 768px) {
    .filters-section {
        flex-direction: column;
    }

    .search-box {
        width: 100%;
    }

    .filter-select {
        width: 100%;
    }

    .button-group {
        flex-direction: column;
    }

    .btn-send-all,
    .btn-test,
    .btn-send-test {
        width: 100%;
    }

    .detail-row {
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .modern-table {
        font-size: 13px;
    }

    .modern-table thead th,
    .modern-table tbody td {
        padding: 10px;
    }

    .pagination-container {
        flex-direction: column;
        text-align: center;
    }
}
</style>
