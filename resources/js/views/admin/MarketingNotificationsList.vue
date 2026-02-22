<template>
    <div class="marketing-notifications">

        <!-- Toast Notification -->
        <transition name="toast-fade">
            <div v-if="toast.show" class="toast-notification" :class="toast.type">
                <i :class="toast.icon"></i>
                <div class="toast-content">
                    <div class="toast-title">{{ toast.title }}</div>
                    <div class="toast-message">{{ toast.message }}</div>
                </div>
                <button class="toast-close" @click="toast.show = false"><i class="fas fa-times"></i></button>
            </div>
        </transition>

        <!-- Stats Cards -->
        <div class="stats-dashboard">
            <div class="stats-grid">
                <div
                    v-for="stat in stats"
                    :key="stat.label"
                    class="stat-card-compact"
                    :class="stat.color"
                >
                    <div class="stat-icon"><i :class="stat.icon"></i></div>
                    <div class="stat-info">
                        <div class="stat-value-compact">{{ formatNumber(stat.value) }}</div>
                        <div class="stat-label-compact">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Rate + Campaign Chart Row -->
        <div class="charts-row" v-if="deliveryRate !== null || campaignChart">
            <div class="chart-card">
                <h4 class="chart-title">Delivery Performance</h4>
                <div class="chart-body delivery-metrics">
                    <div class="delivery-rate-ring">
                        <svg viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="52" fill="none" stroke="#f0f0f0" stroke-width="10" />
                            <circle
                                cx="60" cy="60" r="52" fill="none"
                                :stroke="deliveryRate >= 90 ? '#22c55e' : deliveryRate >= 70 ? '#f97316' : '#ef4444'"
                                stroke-width="10"
                                stroke-linecap="round"
                                :stroke-dasharray="`${deliveryRate * 3.267} 326.7`"
                                stroke-dashoffset="0"
                                transform="rotate(-90 60 60)"
                            />
                        </svg>
                        <div class="rate-center">
                            <span class="rate-number">{{ deliveryRate }}%</span>
                            <span class="rate-text">Delivery</span>
                        </div>
                    </div>
                    <div class="delivery-details">
                        <div class="detail-item">
                            <span class="dot green"></span>
                            <span>Delivered: {{ formatNumber(getStatValue('Total Delivered')) }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="dot red"></span>
                            <span>Failed: {{ formatNumber(getStatValue('Failed Deliveries')) }}</span>
                        </div>
                        <div class="detail-item" v-if="weekTrend">
                            <span class="dot blue"></span>
                            <span>This week: {{ weekTrend.this_week }} campaigns</span>
                        </div>
                        <div class="detail-item" v-if="weekTrend">
                            <span class="dot gray"></span>
                            <span>Last week: {{ weekTrend.last_week }} campaigns</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="chart-card" v-if="campaignChart">
                <h4 class="chart-title">Delivery Trend (30 days)</h4>
                <div class="chart-body" style="height: 260px;">
                    <LineChart :data="campaignChart" :height="260" />
                </div>
            </div>
        </div>

        <!-- Send Notification + Preview -->
        <div class="compose-section">
            <div class="compose-card">
                <div class="compose-header">
                    <i class="fas fa-paper-plane"></i>
                    <h3>Compose Notification</h3>
                </div>

                <form @submit.prevent="handleSend" class="compose-form">
                    <div class="form-group">
                        <label>Title <span class="required">*</span></label>
                        <input
                            type="text"
                            v-model="formData.title"
                            maxlength="100"
                            required
                            placeholder="Enter notification title"
                            class="form-input"
                        >
                        <div class="char-count">{{ formData.title.length }}/100</div>
                    </div>

                    <div class="form-group">
                        <label>Message <span class="required">*</span></label>
                        <textarea
                            v-model="formData.body"
                            rows="4"
                            maxlength="500"
                            required
                            placeholder="Enter notification message"
                            class="form-input"
                        ></textarea>
                        <div class="char-count">{{ formData.body.length }}/500</div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Image URL</label>
                            <input
                                type="url"
                                v-model="formData.image_url"
                                placeholder="https://example.com/image.jpg"
                                class="form-input"
                            >
                        </div>
                        <div class="form-group">
                            <label>Deep Link</label>
                            <input
                                type="text"
                                v-model="formData.deep_link"
                                placeholder="talabna://page/details"
                                class="form-input"
                            >
                        </div>
                    </div>

                    <!-- Test user selector -->
                    <div v-if="showTestUser" class="form-group test-user-group">
                        <label>Test User</label>
                        <div class="user-search-container">
                            <input
                                type="text"
                                v-model="userSearch"
                                @input="debouncedUserSearch"
                                placeholder="Search users by name or email..."
                                class="form-input"
                            >
                            <select v-model="formData.user_id" class="form-input">
                                <option value="">Select a user</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.user_name }} ({{ user.email }})
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="send-actions">
                        <button
                            type="submit"
                            class="btn-action btn-send"
                            :disabled="processing || !formData.title || !formData.body"
                            @click="sendType = 'all'"
                        >
                            <i v-if="processing && sendType === 'all'" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-paper-plane"></i>
                            {{ processing && sendType === 'all' ? 'Sending...' : 'Send to All' }}
                        </button>

                        <button
                            type="button"
                            class="btn-action btn-test-toggle"
                            @click="toggleTestMode"
                        >
                            <i class="fas fa-vial"></i>
                            {{ showTestUser ? 'Cancel Test' : 'Test Mode' }}
                        </button>

                        <button
                            v-if="showTestUser"
                            type="button"
                            class="btn-action btn-test-send"
                            :disabled="processing || !formData.user_id || !formData.title || !formData.body"
                            @click="handleSendTest"
                        >
                            <i v-if="processing && sendType === 'test'" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-flask"></i>
                            {{ processing && sendType === 'test' ? 'Sending...' : 'Send Test' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Live Preview -->
            <div class="preview-card">
                <div class="preview-header">
                    <i class="fas fa-mobile-alt"></i>
                    <h3>Preview</h3>
                </div>
                <div class="phone-preview">
                    <div class="phone-frame">
                        <div class="phone-notch"></div>
                        <div class="notification-preview" :class="{ 'has-content': formData.title || formData.body }">
                            <div class="preview-app-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="preview-content">
                                <div class="preview-app-name">Talabna</div>
                                <div class="preview-title">{{ formData.title || 'Notification Title' }}</div>
                                <div class="preview-body">{{ formData.body || 'Your notification message will appear here...' }}</div>
                            </div>
                            <div v-if="formData.image_url" class="preview-image">
                                <img :src="formData.image_url" alt="Preview" @error="$event.target.style.display='none'">
                            </div>
                        </div>
                        <div class="preview-time">now</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification History -->
        <div class="history-card">
            <div class="history-header">
                <div class="history-title-row">
                    <i class="fas fa-history"></i>
                    <h3>Campaign History</h3>
                    <span class="history-count">{{ logs.total }} campaigns</span>
                </div>

                <!-- Filters -->
                <div class="history-filters">
                    <div class="search-input-wrap">
                        <i class="fas fa-search"></i>
                        <input
                            type="text"
                            v-model="filters.search"
                            @input="debouncedSearch"
                            placeholder="Search campaigns..."
                            class="form-input"
                        >
                    </div>
                    <select class="form-input filter-select" v-model="filters.sort_by" @change="loadLogs">
                        <option value="created_at">Date</option>
                        <option value="total_recipients">Recipients</option>
                        <option value="successful_count">Delivered</option>
                        <option value="failed_count">Failed</option>
                    </select>
                    <select class="form-input filter-select" v-model="filters.sort_direction" @change="loadLogs">
                        <option value="desc">Newest first</option>
                        <option value="asc">Oldest first</option>
                    </select>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="loading-state">
                <div class="spinner"></div>
                <p>Loading campaigns...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="error-banner">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ error }}</span>
                <button @click="loadLogs" class="retry-btn">Retry</button>
            </div>

            <!-- Campaign Cards / Table -->
            <div v-else-if="logs.data.length > 0" class="campaigns-list">
                <div
                    v-for="log in logs.data"
                    :key="log.id"
                    class="campaign-item"
                >
                    <div class="campaign-main">
                        <div class="campaign-id">#{{ log.id }}</div>
                        <div class="campaign-info">
                            <div class="campaign-title-text">{{ log.title }}</div>
                            <div class="campaign-body-text">{{ truncate(log.body, 80) }}</div>
                            <div class="campaign-meta">
                                <span class="meta-item">
                                    <i class="fas fa-user-shield"></i> {{ log.admin_name || 'Unknown' }}
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-calendar"></i> {{ formatDate(log.created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="campaign-stats">
                        <div class="campaign-stat">
                            <span class="stat-num">{{ log.total_recipients }}</span>
                            <span class="stat-desc">Recipients</span>
                        </div>
                        <div class="campaign-stat success">
                            <span class="stat-num">{{ log.successful_count }}</span>
                            <span class="stat-desc">Delivered</span>
                        </div>
                        <div class="campaign-stat" :class="log.failed_count > 0 ? 'danger' : ''">
                            <span class="stat-num">{{ log.failed_count }}</span>
                            <span class="stat-desc">Failed</span>
                        </div>
                        <div class="campaign-stat">
                            <span class="stat-num">{{ getDeliveryPct(log) }}%</span>
                            <span class="stat-desc">Rate</span>
                        </div>
                    </div>

                    <div class="campaign-actions">
                        <button @click="openDetailsModal(log)" class="icon-btn view" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button @click="openDeleteModal(log)" class="icon-btn delete" title="Delete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="empty-state">
                <i class="fas fa-bullhorn"></i>
                <h4>No campaigns yet</h4>
                <p>Create your first push notification campaign above</p>
            </div>

            <!-- Pagination -->
            <div class="pagination-bar" v-if="logs.last_page > 1">
                <div class="pagination-info">
                    {{ ((logs.current_page - 1) * logs.per_page) + 1 }}-{{ Math.min(logs.current_page * logs.per_page, logs.total) }}
                    of {{ logs.total }}
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
            <div class="modal-dialog" @click.stop>
                <div class="modal-top">
                    <h3><i class="fas fa-info-circle"></i> Campaign Details</h3>
                    <button class="modal-close" @click="closeDetailsModal"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-inner" v-if="selectedLog">
                    <!-- Campaign Summary -->
                    <div class="detail-section">
                        <div class="detail-grid">
                            <div class="detail-cell">
                                <span class="detail-key">Campaign ID</span>
                                <span class="detail-val">#{{ selectedLog.id }}</span>
                            </div>
                            <div class="detail-cell">
                                <span class="detail-key">Sent By</span>
                                <span class="detail-val">{{ selectedLog.admin_name || 'Unknown' }}</span>
                            </div>
                            <div class="detail-cell">
                                <span class="detail-key">Date</span>
                                <span class="detail-val">{{ formatDate(selectedLog.created_at) }}</span>
                            </div>
                            <div class="detail-cell">
                                <span class="detail-key">Delivery Rate</span>
                                <span class="detail-val" :class="getDeliveryPct(selectedLog) >= 90 ? 'text-success' : 'text-warning'">
                                    {{ getDeliveryPct(selectedLog) }}%
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Content -->
                    <div class="detail-section">
                        <h4>Notification Content</h4>
                        <div class="content-preview-box">
                            <div class="content-title">{{ selectedLog.title }}</div>
                            <div class="content-body">{{ selectedLog.body }}</div>
                            <div v-if="selectedLog.image_url" class="content-link">
                                <i class="fas fa-image"></i>
                                <a :href="selectedLog.image_url" target="_blank">{{ selectedLog.image_url }}</a>
                            </div>
                            <div v-if="selectedLog.deep_link" class="content-link">
                                <i class="fas fa-link"></i>
                                <code>{{ selectedLog.deep_link }}</code>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Stats -->
                    <div class="detail-section">
                        <h4>Delivery Statistics</h4>
                        <div class="delivery-stats-row">
                            <div class="delivery-stat-card blue">
                                <div class="ds-value">{{ selectedLog.total_recipients }}</div>
                                <div class="ds-label">Recipients</div>
                            </div>
                            <div class="delivery-stat-card green">
                                <div class="ds-value">{{ selectedLog.successful_count }}</div>
                                <div class="ds-label">Delivered</div>
                            </div>
                            <div class="delivery-stat-card red">
                                <div class="ds-value">{{ selectedLog.failed_count }}</div>
                                <div class="ds-label">Failed</div>
                            </div>
                        </div>
                        <!-- Delivery progress bar -->
                        <div class="delivery-progress">
                            <div
                                class="delivery-progress-fill"
                                :style="{ width: getDeliveryPct(selectedLog) + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>
                <div class="modal-bottom">
                    <button @click="closeDetailsModal" class="btn-action btn-cancel">Close</button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal-overlay" v-if="showDeleteModal" @click="closeDeleteModal">
            <div class="modal-dialog small" @click.stop>
                <div class="modal-top danger">
                    <h3><i class="fas fa-exclamation-triangle"></i> Delete Campaign</h3>
                    <button class="modal-close" @click="closeDeleteModal"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-inner">
                    <p>Are you sure you want to delete this campaign log?</p>
                    <div v-if="selectedLog" class="delete-target">
                        <strong>{{ selectedLog.title }}</strong>
                        <span>{{ selectedLog.total_recipients }} recipients</span>
                    </div>
                    <p class="destructive-warning">
                        <i class="fas fa-exclamation-circle"></i> This action cannot be undone.
                    </p>
                </div>
                <div class="modal-bottom">
                    <button @click="closeDeleteModal" class="btn-action btn-cancel">Cancel</button>
                    <button @click="handleDelete" :disabled="processing" class="btn-action btn-delete">
                        <i v-if="processing" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-trash-alt"></i>
                        {{ processing ? 'Deleting...' : 'Delete' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import { useMarketingNotifications } from '../../composables/useMarketingNotifications'
import LineChart from '../../components/admin/charts/LineChart.vue'

const {
    logs,
    stats,
    deliveryRate,
    campaignChart,
    weekTrend,
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

const filters = reactive({
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
const userSearch = ref('')

// Toast system
const toast = reactive({
    show: false,
    type: 'success',
    title: '',
    message: '',
    icon: 'fas fa-check-circle',
    timeout: null
})

function showToast(type, title, message) {
    if (toast.timeout) clearTimeout(toast.timeout)
    toast.type = type
    toast.title = title
    toast.message = message
    toast.icon = type === 'success' ? 'fas fa-check-circle'
        : type === 'error' ? 'fas fa-exclamation-circle'
        : 'fas fa-info-circle'
    toast.show = true
    toast.timeout = setTimeout(() => { toast.show = false }, 5000)
}

let searchTimeout = null
const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        filters.page = 1
        loadLogs()
    }, 400)
}

let userSearchTimeout = null
const debouncedUserSearch = () => {
    clearTimeout(userSearchTimeout)
    userSearchTimeout = setTimeout(() => {
        fetchActiveUsers(userSearch.value)
    }, 400)
}

const loadLogs = async () => {
    await fetchLogs(filters)
}

const changePage = (page) => {
    if (page < 1 || page > logs.value.last_page) return
    filters.page = page
    loadLogs()
}

const displayPages = computed(() => {
    const current = logs.value.current_page
    const last = logs.value.last_page
    const pages = []

    for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
        pages.push(i)
    }

    return pages
})

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const truncate = (text, length) => {
    if (!text) return ''
    return text.length > length ? text.substring(0, length) + '...' : text
}

const getDeliveryPct = (log) => {
    const total = (log.successful_count || 0) + (log.failed_count || 0)
    if (total === 0) return 0
    return Math.round((log.successful_count / total) * 100)
}

const getStatValue = (label) => {
    const stat = stats.value.find(s => s.label === label)
    return stat ? stat.value : 0
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
    const fcmCount = getStatValue('Active Users with FCM')
    if (!confirm(`Send this notification to all ${fcmCount} active users?`)) return

    processing.value = true
    sendType.value = 'all'

    try {
        const result = await sendToAll(formData.value)
        showToast('success', 'Campaign Sent',
            `Delivered: ${result.result.successful}, Failed: ${result.result.failed}`)

        if (result.result.failed > 0) {
            console.warn('Campaign delivery failures:', result.result.errors)
        }

        resetForm()
        await loadLogs()
        await fetchStats()
    } catch (err) {
        showToast('error', 'Send Failed', err.message || 'Failed to send notification')
        console.error('Send to all error:', err)
    } finally {
        processing.value = false
    }
}

const handleSendTest = async () => {
    if (!formData.value.user_id) {
        showToast('error', 'No User', 'Please select a user for testing')
        return
    }

    processing.value = true
    sendType.value = 'test'

    try {
        const result = await sendTest(formData.value)
        showToast('success', 'Test Sent', `Notification sent to ${result.user.name}`)
        showTestUser.value = false
    } catch (err) {
        showToast('error', 'Test Failed', err.message || 'Failed to send test notification')
        console.error('Send test error:', err)
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
        showToast('success', 'Deleted', 'Campaign log deleted successfully')
        await loadLogs()
        await fetchStats()
    } catch (err) {
        showToast('error', 'Delete Failed', err.message || 'Failed to delete log')
        console.error('Delete log error:', err)
    } finally {
        processing.value = false
    }
}

onMounted(async () => {
    await Promise.all([loadLogs(), fetchStats()])
})
</script>

<style scoped>
.marketing-notifications {
    padding: 0;
}

/* ── Toast ─────────────────────────── */
.toast-notification {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 10000;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    border-radius: 12px;
    color: white;
    font-size: 0.9rem;
    box-shadow: 0 8px 30px rgba(0,0,0,0.18);
    max-width: 420px;
}

.toast-notification.success { background: linear-gradient(135deg, #22c55e, #16a34a); }
.toast-notification.error { background: linear-gradient(135deg, #ef4444, #dc2626); }
.toast-notification.info { background: linear-gradient(135deg, #3b82f6, #2563eb); }

.toast-notification > i { font-size: 1.25rem; flex-shrink: 0; }
.toast-content { flex: 1; }
.toast-title { font-weight: 700; font-size: 0.85rem; }
.toast-message { opacity: 0.9; font-size: 0.8rem; margin-top: 2px; }
.toast-close {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.toast-fade-enter-active { animation: slideIn 0.35s ease; }
.toast-fade-leave-active { animation: slideOut 0.25s ease; }
@keyframes slideIn { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
@keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(120%); opacity: 0; } }

/* ── Compose Section ──────────────── */
.compose-section {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.compose-card, .preview-card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    overflow: hidden;
}

.compose-header, .preview-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #f0f0f0;
    font-size: 1rem;
    font-weight: 700;
    color: #1a1a2e;
}

.compose-header i, .preview-header i {
    color: #3b82f6;
}

.compose-form {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1.25rem;
    position: relative;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
    font-size: 0.85rem;
}

.required { color: #ef4444; }

.form-input {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    background: white;
    color: #1a1a2e;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
}

.char-count {
    position: absolute;
    right: 12px;
    bottom: -18px;
    font-size: 0.7rem;
    color: #9ca3af;
    font-weight: 500;
}

.form-row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.test-user-group {
    background: #f0f9ff;
    padding: 1rem;
    border-radius: 10px;
    border: 1px solid #bae6fd;
}

.user-search-container {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.send-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-top: 0.5rem;
}

.btn-action {
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.btn-action:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none !important;
}

.btn-send {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
}

.btn-test-toggle {
    background: #e5e7eb;
    color: #374151;
}

.btn-test-send {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
}

.btn-cancel {
    background: #e5e7eb;
    color: #374151;
}

.btn-delete {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}

.btn-action:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* ── Phone Preview ────────────────── */
.phone-preview {
    padding: 1.5rem;
    display: flex;
    justify-content: center;
}

.phone-frame {
    width: 260px;
    min-height: 200px;
    background: linear-gradient(180deg, #1a1a2e, #16213e);
    border-radius: 28px;
    padding: 16px;
    position: relative;
}

.phone-notch {
    width: 80px;
    height: 6px;
    background: #2a2a4a;
    border-radius: 3px;
    margin: 0 auto 16px;
}

.notification-preview {
    background: rgba(255,255,255,0.95);
    border-radius: 14px;
    padding: 12px;
    display: flex;
    gap: 10px;
    align-items: flex-start;
    transition: all 0.3s ease;
}

.notification-preview.has-content {
    box-shadow: 0 2px 12px rgba(0,0,0,0.1);
}

.preview-app-icon {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.preview-content {
    flex: 1;
    min-width: 0;
}

.preview-app-name {
    font-size: 0.65rem;
    color: #9ca3af;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.preview-title {
    font-size: 0.8rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 2px 0;
    word-break: break-word;
}

.preview-body {
    font-size: 0.72rem;
    color: #6b7280;
    line-height: 1.4;
    word-break: break-word;
}

.preview-image {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
}

.preview-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-time {
    text-align: right;
    color: rgba(255,255,255,0.4);
    font-size: 0.7rem;
    margin-top: 8px;
}

/* ── Delivery Metrics ─────────────── */
.delivery-metrics {
    display: flex;
    align-items: center;
    gap: 2rem;
    padding: 1.5rem;
}

.delivery-rate-ring {
    position: relative;
    width: 120px;
    height: 120px;
    flex-shrink: 0;
}

.delivery-rate-ring svg {
    width: 100%;
    height: 100%;
}

.rate-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.rate-number {
    display: block;
    font-size: 1.5rem;
    font-weight: 800;
    color: #1a1a2e;
}

.rate-text {
    display: block;
    font-size: 0.7rem;
    color: #9ca3af;
    font-weight: 600;
    text-transform: uppercase;
}

.delivery-details {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    color: #374151;
}

.dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.dot.green { background: #22c55e; }
.dot.red { background: #ef4444; }
.dot.blue { background: #3b82f6; }
.dot.gray { background: #9ca3af; }

/* ── History Card ─────────────────── */
.history-card {
    background: white;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    overflow: hidden;
}

.history-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
}

.history-title-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 1rem;
}

.history-title-row i {
    color: #3b82f6;
    font-size: 1.1rem;
}

.history-title-row h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a1a2e;
}

.history-count {
    margin-left: auto;
    font-size: 0.8rem;
    color: #9ca3af;
    font-weight: 500;
}

.history-filters {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.search-input-wrap {
    flex: 1;
    min-width: 200px;
    position: relative;
}

.search-input-wrap i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 0.85rem;
}

.search-input-wrap .form-input {
    padding-left: 40px;
}

.filter-select {
    width: auto;
    min-width: 140px;
}

/* ── Campaign Items ───────────────── */
.campaigns-list {
    padding: 0.5rem 0;
}

.campaign-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f5f5f5;
    transition: background 0.15s ease;
}

.campaign-item:last-child {
    border-bottom: none;
}

.campaign-item:hover {
    background: #fafbfc;
}

.campaign-id {
    width: 52px;
    text-align: center;
    font-weight: 700;
    font-size: 0.8rem;
    color: #3b82f6;
    background: rgba(59,130,246,0.08);
    padding: 6px 0;
    border-radius: 8px;
    flex-shrink: 0;
}

.campaign-main {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    min-width: 0;
}

.campaign-info {
    flex: 1;
    min-width: 0;
}

.campaign-title-text {
    font-weight: 600;
    font-size: 0.9rem;
    color: #1a1a2e;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.campaign-body-text {
    font-size: 0.78rem;
    color: #9ca3af;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.campaign-meta {
    display: flex;
    gap: 1rem;
    margin-top: 4px;
}

.meta-item {
    font-size: 0.72rem;
    color: #b0b8c4;
    display: flex;
    align-items: center;
    gap: 4px;
}

.campaign-stats {
    display: flex;
    gap: 1rem;
    flex-shrink: 0;
}

.campaign-stat {
    text-align: center;
    min-width: 55px;
}

.stat-num {
    display: block;
    font-weight: 800;
    font-size: 1rem;
    color: #1a1a2e;
}

.campaign-stat.success .stat-num { color: #22c55e; }
.campaign-stat.danger .stat-num { color: #ef4444; }

.stat-desc {
    display: block;
    font-size: 0.65rem;
    color: #9ca3af;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.3px;
}

.campaign-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}

.icon-btn {
    width: 34px;
    height: 34px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    font-size: 0.85rem;
}

.icon-btn.view {
    background: rgba(59,130,246,0.1);
    color: #3b82f6;
}

.icon-btn.delete {
    background: rgba(239,68,68,0.1);
    color: #ef4444;
}

.icon-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* ── Loading / Error / Empty ──────── */
.loading-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #9ca3af;
}

.spinner {
    width: 40px;
    height: 40px;
    margin: 0 auto 1rem;
    border: 3px solid #f3f4f6;
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.error-banner {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 1rem 1.5rem;
    background: #fef2f2;
    color: #dc2626;
    font-size: 0.9rem;
}

.retry-btn {
    margin-left: auto;
    padding: 6px 14px;
    border: 1px solid #fca5a5;
    background: white;
    border-radius: 6px;
    color: #dc2626;
    font-weight: 600;
    cursor: pointer;
    font-size: 0.8rem;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #9ca3af;
}

.empty-state i {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    opacity: 0.4;
}

.empty-state h4 {
    margin: 0 0 0.5rem;
    color: #6b7280;
}

.empty-state p {
    margin: 0;
    font-size: 0.85rem;
}

/* ── Pagination ───────────────────── */
.pagination-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-top: 1px solid #f0f0f0;
}

.pagination-info {
    font-size: 0.8rem;
    color: #9ca3af;
}

.pagination-controls {
    display: flex;
    gap: 4px;
}

.page-btn {
    padding: 6px 12px;
    border: 1px solid #e5e7eb;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    color: #374151;
    font-weight: 500;
    font-size: 0.85rem;
    transition: all 0.15s ease;
}

.page-btn:hover:not(:disabled) {
    background: #f3f4f6;
    border-color: #3b82f6;
}

.page-btn.active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.page-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* ── Modal ────────────────────────── */
.modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}

.modal-dialog {
    background: white;
    border-radius: 16px;
    width: 100%;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 16px 48px rgba(0,0,0,0.2);
}

.modal-dialog.small {
    max-width: 460px;
}

.modal-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #f0f0f0;
    border-radius: 16px 16px 0 0;
}

.modal-top.danger {
    background: #fef2f2;
    border-bottom-color: #fecaca;
}

.modal-top h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #1a1a2e;
}

.modal-top.danger h3 { color: #dc2626; }

.modal-close {
    width: 32px;
    height: 32px;
    border: none;
    background: rgba(0,0,0,0.06);
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: rgba(0,0,0,0.1);
    transform: rotate(90deg);
}

.modal-inner {
    padding: 1.5rem;
}

.modal-bottom {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-radius: 0 0 16px 16px;
}

/* Detail sections in modal */
.detail-section {
    margin-bottom: 1.5rem;
}

.detail-section:last-child {
    margin-bottom: 0;
}

.detail-section h4 {
    margin: 0 0 0.75rem;
    font-size: 0.9rem;
    font-weight: 700;
    color: #374151;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.detail-cell {
    background: #f8f9fa;
    padding: 0.75rem 1rem;
    border-radius: 10px;
}

.detail-key {
    display: block;
    font-size: 0.72rem;
    color: #9ca3af;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.detail-val {
    font-weight: 700;
    color: #1a1a2e;
    font-size: 0.95rem;
}

.text-success { color: #22c55e !important; }
.text-warning { color: #f97316 !important; }

.content-preview-box {
    background: #f8f9fa;
    padding: 1rem 1.25rem;
    border-radius: 10px;
    border-left: 4px solid #3b82f6;
}

.content-title {
    font-weight: 700;
    font-size: 1rem;
    color: #1a1a2e;
    margin-bottom: 6px;
}

.content-body {
    color: #6b7280;
    font-size: 0.9rem;
    line-height: 1.5;
}

.content-link {
    margin-top: 8px;
    font-size: 0.8rem;
    color: #9ca3af;
    display: flex;
    align-items: center;
    gap: 6px;
}

.content-link a {
    color: #3b82f6;
    text-decoration: none;
    word-break: break-all;
}

.content-link code {
    background: #e5e7eb;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.78rem;
}

.delivery-stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.delivery-stat-card {
    text-align: center;
    padding: 1rem;
    border-radius: 10px;
    background: #f8f9fa;
}

.delivery-stat-card.blue { background: rgba(59,130,246,0.08); }
.delivery-stat-card.green { background: rgba(34,197,94,0.08); }
.delivery-stat-card.red { background: rgba(239,68,68,0.08); }

.ds-value {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1a1a2e;
}

.delivery-stat-card.blue .ds-value { color: #3b82f6; }
.delivery-stat-card.green .ds-value { color: #22c55e; }
.delivery-stat-card.red .ds-value { color: #ef4444; }

.ds-label {
    font-size: 0.72rem;
    color: #9ca3af;
    font-weight: 600;
    text-transform: uppercase;
}

.delivery-progress {
    width: 100%;
    height: 8px;
    background: #fee2e2;
    border-radius: 4px;
    overflow: hidden;
}

.delivery-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #22c55e, #4ade80);
    border-radius: 4px;
    transition: width 0.8s ease;
}

.delete-target {
    background: #fef3cd;
    border: 1px solid #fde68a;
    padding: 1rem;
    border-radius: 10px;
    margin: 1rem 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.delete-target strong {
    color: #1a1a2e;
}

.delete-target span {
    font-size: 0.8rem;
    color: #9ca3af;
}

.destructive-warning {
    color: #dc2626;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0;
}

/* ── Responsive ───────────────────── */
@media (max-width: 900px) {
    .compose-section {
        grid-template-columns: 1fr;
    }

    .form-row-2 {
        grid-template-columns: 1fr;
    }

    .delivery-metrics {
        flex-direction: column;
        align-items: center;
    }

    .campaign-item {
        flex-wrap: wrap;
    }

    .campaign-stats {
        width: 100%;
        justify-content: space-around;
        padding-top: 0.75rem;
        border-top: 1px solid #f5f5f5;
    }

    .detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>
