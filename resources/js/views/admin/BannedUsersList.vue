<template>
    <div class="banned-users-modern">
        <!-- Header Section -->
        <div class="section-header mb-4">
            <div class="header-content">
                <h1 class="section-title">
                    <i class="fas fa-user-slash"></i>
                    Banned Users Management
                </h1>
                <p class="section-subtitle">Manage and monitor banned user accounts</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid" v-if="stats && stats.length > 0">
            <div v-for="stat in stats" :key="stat.label" class="stat-card" :class="stat.color">
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
                    placeholder="Search by name, email, or phone..."
                >
                <span v-if="filters.search" class="clear-search" @click="filters.search = ''; loadBannedUsers()">
                    <i class="fas fa-times"></i>
                </span>
            </div>

            <div class="filter-controls">
                <select class="filter-select" v-model="filters.sort_by" @change="loadBannedUsers">
                    <option value="created_at">Sort by Ban Date</option>
                    <option value="user_name">Sort by Name</option>
                    <option value="email">Sort by Email</option>
                </select>
                <select class="filter-select" v-model="filters.sort_direction" @change="loadBannedUsers">
                    <option value="desc">Descending</option>
                    <option value="asc">Ascending</option>
                </select>
            </div>
        </div>

        <!-- Results Info -->
        <div class="view-controls mb-4" v-if="!loading && bannedUsers.data">
            <div class="results-info">
                Showing {{ ((bannedUsers.current_page - 1) * bannedUsers.per_page) + 1 }}
                to {{ Math.min(bannedUsers.current_page * bannedUsers.per_page, bannedUsers.total) }}
                of {{ bannedUsers.total }} banned users
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
            <div class="loader-advanced"></div>
            <p>Loading banned users...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="error-state">
            <i class="fas fa-exclamation-triangle"></i>
            <p>{{ error }}</p>
        </div>

        <!-- Data Table -->
        <div v-else class="data-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Banned At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody v-if="bannedUsers.data && bannedUsers.data.length > 0">
                    <tr v-for="user in bannedUsers.data" :key="user.id">
                        <td>
                            <span class="user-id">#{{ user.id }}</span>
                        </td>
                        <td>
                            <span class="user-name">{{ user.user_name }}</span>
                        </td>
                        <td>
                            <span class="user-email">{{ user.email }}</span>
                        </td>
                        <td>
                            <span class="phone-text">{{ user.phones || 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="date-text">{{ formatDate(user.created_at) }}</span>
                        </td>
                        <td>
                            <button
                                @click="openUnbanModal(user)"
                                class="action-btn success"
                                title="Unban User"
                            >
                                <i class="fas fa-user-check"></i>
                                Unban
                            </button>
                        </td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No banned users found</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container" v-if="bannedUsers.last_page > 1">
            <button
                class="pagination-btn"
                :class="{ disabled: bannedUsers.current_page === 1 }"
                @click="changePage(bannedUsers.current_page - 1)"
                :disabled="bannedUsers.current_page === 1"
            >
                <i class="fas fa-chevron-left"></i>
                Previous
            </button>

            <div class="pagination-numbers">
                <button
                    v-for="page in displayPages"
                    :key="page"
                    class="page-number"
                    :class="{ active: page === bannedUsers.current_page }"
                    @click="changePage(page)"
                    :disabled="page === '...'"
                >
                    {{ page }}
                </button>
            </div>

            <button
                class="pagination-btn"
                :class="{ disabled: bannedUsers.current_page === bannedUsers.last_page }"
                @click="changePage(bannedUsers.current_page + 1)"
                :disabled="bannedUsers.current_page === bannedUsers.last_page"
            >
                Next
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <!-- Unban Modal -->
        <div class="modal fade" id="unbanModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Unban User</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to unban <strong>{{ selectedUser?.user_name }}</strong>?</p>
                        <div class="form-group">
                            <label>Reason (Optional)</label>
                            <textarea
                                class="form-control"
                                v-model="unbanReason"
                                rows="3"
                                placeholder="Enter reason for unbanning..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" @click="handleUnban" :disabled="processing">
                            <span v-if="processing">
                                <i class="fas fa-spinner fa-spin"></i> Processing...
                            </span>
                            <span v-else>
                                <i class="fas fa-user-check"></i> Unban User
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useBans } from '../../composables/useBans'

const { bannedUsers, stats, loading, error, fetchBannedUsers, fetchStats, toggleUserBan } = useBans()

const filters = ref({
    search: '',
    sort_by: 'created_at',
    sort_direction: 'desc',
    page: 1,
    per_page: 15
})

const selectedUser = ref(null)
const unbanReason = ref('')
const processing = ref(false)

let searchTimeout = null

const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        filters.value.page = 1
        loadBannedUsers()
    }, 500)
}

const loadBannedUsers = async () => {
    await fetchBannedUsers(filters.value)
}

const changePage = (page) => {
    if (page < 1 || page > bannedUsers.value.last_page || page === '...') return
    filters.value.page = page
    loadBannedUsers()
}

const displayPages = computed(() => {
    const current = bannedUsers.value.current_page
    const last = bannedUsers.value.last_page
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
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const openUnbanModal = (user) => {
    selectedUser.value = user
    unbanReason.value = ''
    window.$('#unbanModal').modal('show')
}

const handleUnban = async () => {
    if (!selectedUser.value) return

    processing.value = true
    try {
        await toggleUserBan(selectedUser.value.id, 'unban', unbanReason.value)

        window.$('#unbanModal').modal('hide')

        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'User has been unbanned successfully'
        })

        await loadBannedUsers()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to unban user'
        })
    } finally {
        processing.value = false
    }
}

onMounted(async () => {
    console.log('📋 BannedUsersList component mounted')
    await loadBannedUsers()
    await fetchStats()
})
</script>

<style scoped>
.banned-users-modern {
    padding: 20px;
}

/* Header Section */
.section-header {
    margin-bottom: 30px;
}

.header-content {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.section-title {
    font-size: 28px;
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0;
}

.section-title i {
    color: #e74c3c;
}

.section-subtitle {
    font-size: 14px;
    color: #7f8c8d;
    margin: 0;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-card.blue .stat-icon,
.stat-card.info .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-card.green .stat-icon,
.stat-card.success .stat-icon {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
}

.stat-card.red .stat-icon,
.stat-card.danger .stat-icon {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.stat-card.orange .stat-icon,
.stat-card.warning .stat-icon {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 4px 0;
}

.stat-label {
    font-size: 13px;
    color: #7f8c8d;
    margin: 0;
}

/* Search & Filter Bar */
.search-filter-bar {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    min-width: 300px;
    position: relative;
}

.search-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #95a5a6;
    font-size: 14px;
}

.search-input {
    width: 100%;
    padding: 12px 40px 12px 44px;
    border: 2px solid #ecf0f1;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
}

.search-input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.clear-search {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #95a5a6;
    transition: color 0.3s;
}

.clear-search:hover {
    color: #e74c3c;
}

.filter-controls {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-select {
    padding: 12px 16px;
    border: 2px solid #ecf0f1;
    border-radius: 8px;
    font-size: 14px;
    min-width: 180px;
    transition: all 0.3s;
    background: white;
}

.filter-select:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

/* View Controls */
.view-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.results-info {
    font-size: 14px;
    color: #7f8c8d;
}

/* Loading State */
.loading-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.loader-advanced {
    width: 50px;
    height: 50px;
    border: 4px solid #ecf0f1;
    border-top-color: #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 16px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Error State */
.error-state {
    text-align: center;
    padding: 60px 20px;
    background: #fee;
    border: 2px solid #e74c3c;
    border-radius: 12px;
    color: #c0392b;
}

.error-state i {
    font-size: 48px;
    margin-bottom: 16px;
    display: block;
}

/* Data Table */
.data-table-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-table thead {
    background: #f8f9fa;
}

.modern-table th {
    padding: 16px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    color: #2c3e50;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #ecf0f1;
}

.modern-table tbody tr {
    border-bottom: 1px solid #ecf0f1;
    transition: background-color 0.2s;
}

.modern-table tbody tr:hover {
    background-color: #f8f9fa;
}

.modern-table td {
    padding: 16px;
    font-size: 14px;
    color: #2c3e50;
}

.user-id {
    font-family: monospace;
    font-weight: 600;
    color: #7f8c8d;
}

.user-name {
    font-weight: 500;
}

.user-email {
    color: #7f8c8d;
    font-size: 13px;
}

.phone-text,
.date-text {
    color: #7f8c8d;
    font-size: 13px;
}

.action-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.action-btn.success {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    color: white;
}

.action-btn.success:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(46, 204, 113, 0.3);
}

.empty-state {
    text-align: center !important;
    padding: 60px 20px !important;
    color: #95a5a6;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    display: block;
}

.empty-state p {
    margin: 0;
    font-size: 16px;
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 24px;
}

.pagination-btn {
    padding: 10px 16px;
    background: white;
    border: 2px solid #ecf0f1;
    border-radius: 8px;
    color: #2c3e50;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.pagination-btn:hover:not(.disabled) {
    border-color: #3498db;
    color: #3498db;
}

.pagination-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-numbers {
    display: flex;
    gap: 4px;
}

.page-number {
    width: 40px;
    height: 40px;
    border: 2px solid #ecf0f1;
    background: white;
    border-radius: 8px;
    color: #2c3e50;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s;
}

.page-number:hover:not([disabled]) {
    border-color: #3498db;
    color: #3498db;
}

.page-number.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
    color: white;
}

.page-number:disabled {
    cursor: default;
    border-color: #ecf0f1;
}

@media (max-width: 768px) {
    .banned-users-modern {
        padding: 15px;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .search-filter-bar {
        flex-direction: column;
    }

    .search-box {
        min-width: 100%;
    }

    .filter-controls {
        flex-direction: column;
        width: 100%;
    }

    .filter-select {
        width: 100%;
    }

    .modern-table {
        font-size: 12px;
    }

    .modern-table th,
    .modern-table td {
        padding: 12px 8px;
    }
}
</style>
