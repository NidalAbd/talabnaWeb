<template>
    <div class="palservice-points-advanced">

        <!-- Stats Cards -->
        <div class="stats-grid" v-if="stats">
            <div class="stat-card blue">
                <div class="stat-icon">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">{{ stats.total_points.toLocaleString() }}</h3>
                    <p class="stat-label">Total Points in System</p>
                </div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">{{ stats.users_with_points }}</h3>
                    <p class="stat-label">Users with Points</p>
                </div>
            </div>
            <div class="stat-card orange">
                <div class="stat-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">{{ stats.average_points }}</h3>
                    <p class="stat-label">Average Points</p>
                </div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">{{ stats.max_points.toLocaleString() }}</h3>
                    <p class="stat-label">Maximum Points</p>
                </div>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="search-filter-bar">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input
                    type="text"
                    v-model="searchQuery"
                    @input="onSearch"
                    class="search-input"
                    placeholder="Search users by name or email..."
                >
                <span v-if="searchQuery" class="clear-search" @click="searchQuery = ''">
                    <i class="fas fa-times"></i>
                </span>
            </div>

            <div class="filter-controls">
                <div class="filter-group">
                    <input
                        type="number"
                        v-model="minPoints"
                        @input="onFilterChange"
                        class="filter-input"
                        placeholder="Min points"
                    >
                    <input
                        type="number"
                        v-model="maxPoints"
                        @input="onFilterChange"
                        class="filter-input"
                        placeholder="Max points"
                    >
                </div>
            </div>
        </div>

        <!-- Results Info -->
        <div class="view-controls mb-4">
            <div class="results-info">
                Showing {{ pointsData.length }} of {{ pagination.total }} users
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
            <div class="loader-advanced"></div>
            <p>Loading points data...</p>
        </div>

        <!-- Data Table -->
        <div v-else class="data-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th @click="sortBy('point')" class="sortable">
                            Points <i class="fas fa-sort"></i>
                        </th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody v-if="pointsData.length > 0">
                    <tr v-for="item in pointsData" :key="item.id">
                        <td>
                            <span class="user-id">#{{ item.user?.id || 'N/A' }}</span>
                        </td>
                        <td>
                            <div class="user-info">
                                <span class="user-name">{{ item.user?.user_name || 'N/A' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="user-email">{{ item.user?.email || 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="points-badge">{{ item.point }} pts</span>
                        </td>
                        <td>
                            <span class="date-text">{{ formatDate(item.updated_at) }}</span>
                        </td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr>
                        <td colspan="5" class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No data found</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container" v-if="pagination.last_page > 1">
            <button
                class="pagination-btn"
                :class="{ disabled: pagination.current_page === 1 }"
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
            >
                <i class="fas fa-chevron-left"></i>
                Previous
            </button>

            <div class="pagination-numbers">
                <button
                    v-for="page in displayedPages"
                    :key="page"
                    class="page-number"
                    :class="{ active: page === pagination.current_page }"
                    @click="changePage(page)"
                >
                    {{ page }}
                </button>
            </div>

            <button
                class="pagination-btn"
                :class="{ disabled: pagination.current_page === pagination.last_page }"
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
            >
                Next
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePalServicePoints } from '../../composables/usePalServicePoints'

const { pointsData, stats, loading, pagination, fetchPointsData, fetchStats } = usePalServicePoints()

const searchQuery = ref('')
const minPoints = ref(null)
const maxPoints = ref(null)
const sortField = ref('point')
const sortDirection = ref('desc')

let searchTimeout = null

const displayedPages = computed(() => {
    const pages = []
    const currentPage = pagination.value.current_page
    const lastPage = pagination.value.last_page
    const delta = 2

    for (let i = Math.max(2, currentPage - delta); i <= Math.min(lastPage - 1, currentPage + delta); i++) {
        pages.push(i)
    }
    if (currentPage - delta > 2) pages.unshift('...')
    if (currentPage + delta < lastPage - 1) pages.push('...')
    pages.unshift(1)
    if (lastPage > 1) pages.push(lastPage)
    return pages.filter((page, index, self) => self.indexOf(page) === index)
})

const onSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => loadData(), 500)
}

const onFilterChange = () => {
    loadData()
}

const sortBy = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortField.value = field
        sortDirection.value = 'desc'
    }
    loadData()
}

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) loadData(page)
}

const loadData = (page = 1) => {
    const filters = {
        sort_field: sortField.value,
        sort_direction: sortDirection.value
    }
    if (searchQuery.value) filters.search = searchQuery.value
    if (minPoints.value) filters.min_points = minPoints.value
    if (maxPoints.value) filters.max_points = maxPoints.value
    fetchPointsData(page, filters)
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

onMounted(() => {
    loadData()
    fetchStats()
})
</script>

<style scoped>
.palservice-points-advanced {
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
    color: #3498db;
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

.stat-card.blue .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-card.green .stat-icon {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
}

.stat-card.orange .stat-icon {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
}

.stat-card.red .stat-icon {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
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

.filter-group {
    display: flex;
    gap: 12px;
}

.filter-input {
    padding: 12px 16px;
    border: 2px solid #ecf0f1;
    border-radius: 8px;
    font-size: 14px;
    width: 150px;
    transition: all 0.3s;
}

.filter-input:focus {
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

.modern-table th.sortable {
    cursor: pointer;
    user-select: none;
    transition: color 0.3s;
}

.modern-table th.sortable:hover {
    color: #3498db;
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

.points-badge {
    display: inline-block;
    padding: 6px 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 6px;
    font-weight: 600;
    font-size: 13px;
}

.date-text {
    color: #7f8c8d;
    font-size: 13px;
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

.page-number:hover {
    border-color: #3498db;
    color: #3498db;
}

.page-number.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
    color: white;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .search-filter-bar {
        flex-direction: column;
    }

    .search-box {
        min-width: 100%;
    }

    .filter-group {
        flex-direction: column;
    }

    .filter-input {
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
