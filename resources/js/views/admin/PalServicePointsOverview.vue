<template>
    <div class="palservice-points-advanced">

        <!-- Stats Cards -->
        <div class="stats-dashboard" v-if="stats">
            <div class="stats-grid">
                <div class="stat-card-compact primary">
                    <div class="stat-icon"><i class="fas fa-coins"></i></div>
                    <div class="stat-info">
                        <div class="stat-value-compact">{{ stats.total_points.toLocaleString() }}</div>
                        <div class="stat-label-compact">Total Points</div>
                    </div>
                </div>
                <div class="stat-card-compact success">
                    <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                    <div class="stat-info">
                        <div class="stat-value-compact">{{ stats.users_with_points }}</div>
                        <div class="stat-label-compact">Users with Points</div>
                    </div>
                </div>
                <div class="stat-card-compact warning">
                    <div class="stat-icon"><i class="fas fa-chart-bar"></i></div>
                    <div class="stat-info">
                        <div class="stat-value-compact">{{ stats.average_points }}</div>
                        <div class="stat-label-compact">Average Points</div>
                    </div>
                </div>
                <div class="stat-card-compact info">
                    <div class="stat-icon"><i class="fas fa-trophy"></i></div>
                    <div class="stat-info">
                        <div class="stat-value-compact">{{ stats.max_points.toLocaleString() }}</div>
                        <div class="stat-label-compact">Maximum Points</div>
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
    padding: 0;
}

/* Search & Filter Bar */
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
    padding: 0.75rem 2.5rem 0.75rem 2.75rem;
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

.clear-search {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #999;
    transition: color 0.3s;
}

.clear-search:hover {
    color: #e74c3c;
}

.filter-controls {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    gap: 0.75rem;
}

.filter-input {
    padding: 0.75rem 1rem;
    border: 2px solid #e8ecef;
    border-radius: 8px;
    font-size: 0.9rem;
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
}

.results-info {
    font-size: 0.9rem;
    color: #666;
}

/* Loading & Error States */
.loading-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.loader-advanced {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f4f6;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
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

.modern-table th.sortable {
    cursor: pointer;
    user-select: none;
}

.modern-table th.sortable:hover {
    color: rgba(255, 255, 255, 0.7);
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

.user-id {
    background: #e8ecef;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.85rem;
}

.user-name {
    font-weight: 600;
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

.date-text {
    font-size: 0.85rem;
    color: #666;
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

.pagination-btn:hover:not(.disabled) {
    border-color: #3498db;
    color: #3498db;
}

.pagination-btn.disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.pagination-numbers {
    display: flex;
    gap: 0.5rem;
}

.page-number {
    padding: 0.5rem 0.75rem;
    border: 2px solid #e8ecef;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.page-number:hover {
    border-color: #3498db;
    color: #3498db;
}

.page-number.active {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    border-color: #3498db;
    color: white;
}

.text-muted {
    color: #999;
}

/* Responsive */
@media (max-width: 768px) {
    .search-filter-bar {
        flex-direction: column;
    }

    .search-box {
        min-width: 100%;
    }

    .filter-group {
        width: 100%;
    }

    .filter-input {
        flex: 1;
    }

    .data-table-container {
        overflow-x: auto;
    }

    .modern-table {
        min-width: 800px;
    }

    .pagination-container {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
