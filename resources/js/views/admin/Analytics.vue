<template>
    <div class="analytics-modern">

        <!-- Date Range Picker -->
        <div class="date-range-bar">
            <div class="range-buttons">
                <button
                    v-for="range in dateRanges"
                    :key="range.days"
                    class="range-btn"
                    :class="{ active: activeDays === range.days }"
                    @click="changeRange(range.days)"
                >
                    {{ range.label }}
                </button>
            </div>
        </div>

        <!-- Overview Stat Cards -->
        <div class="stats-dashboard">
            <div class="stats-grid">
                <div
                    v-for="stat in overview"
                    :key="stat.label"
                    class="stat-card-compact"
                    :class="stat.color"
                >
                    <div class="stat-icon"><i :class="stat.icon"></i></div>
                    <div class="stat-info">
                        <div class="stat-value-compact">{{ formatNumber(stat.value) }}</div>
                        <div class="stat-label-compact">{{ stat.label }}</div>
                        <div
                            v-if="stat.change !== undefined"
                            class="stat-trend"
                            :class="stat.change >= 0 ? 'trend-up' : 'trend-down'"
                        >
                            <i :class="stat.change >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                            {{ Math.abs(stat.change) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modern Tabs -->
        <div class="tabs-container">
            <div class="tabs-header">
                <button
                    class="tab-btn"
                    :class="{ active: activeTab === 'users' }"
                    @click="changeTab('users')"
                >
                    <i class="fas fa-users"></i> Users
                </button>
                <button
                    class="tab-btn"
                    :class="{ active: activeTab === 'points' }"
                    @click="changeTab('points')"
                >
                    <i class="fas fa-coins"></i> Points
                </button>
                <button
                    class="tab-btn"
                    :class="{ active: activeTab === 'posts' }"
                    @click="changeTab('posts')"
                >
                    <i class="fas fa-file-alt"></i> Posts
                </button>
            </div>

            <div class="tabs-body">
                <!-- Loading -->
                <div v-if="loading" class="loading-state">
                    <div class="spinner"></div>
                    <p>Loading analytics...</p>
                </div>

                <!-- Users Tab -->
                <div v-else-if="activeTab === 'users' && tabData" class="tab-content">
                    <div class="charts-row">
                        <div class="chart-card">
                            <h4 class="chart-title">Registrations Over Time</h4>
                            <div class="chart-body" style="height: 280px;">
                                <LineChart :data="tabData.registrations_chart" :height="280" />
                            </div>
                        </div>
                        <div class="chart-card">
                            <h4 class="chart-title">User Status</h4>
                            <div class="chart-body">
                                <PieChart :data="tabData.status_chart" :height="250" />
                            </div>
                        </div>
                    </div>
                    <div class="charts-row">
                        <div class="chart-card">
                            <h4 class="chart-title">Users by Country (Top 10)</h4>
                            <div class="chart-body" style="height: 280px;">
                                <BarChart :data="tabData.country_chart" :height="280" />
                            </div>
                        </div>
                        <div class="chart-card">
                            <h4 class="chart-title">Engagement Rates</h4>
                            <div class="chart-body metric-card-body" v-if="tabData.metrics">
                                <div class="metric-row">
                                    <span class="metric-label">Total Users</span>
                                    <span class="metric-value">{{ formatNumber(tabData.metrics.total_users) }}</span>
                                </div>
                                <div class="metric-row">
                                    <span class="metric-label">Active Users</span>
                                    <span class="metric-value">{{ formatNumber(tabData.metrics.active_users) }}</span>
                                </div>
                                <div class="metric-row">
                                    <span class="metric-label">Users with Posts</span>
                                    <span class="metric-value">{{ formatNumber(tabData.metrics.users_with_posts) }}</span>
                                </div>
                                <div class="metric-row highlight">
                                    <span class="metric-label">Engagement Rate</span>
                                    <span class="metric-value accent">{{ tabData.metrics.engagement_pct }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Points Tab -->
                <div v-else-if="activeTab === 'points' && tabData" class="tab-content">
                    <div class="charts-row">
                        <div class="chart-card">
                            <h4 class="chart-title">Inflow vs Outflow</h4>
                            <div class="chart-body" style="height: 280px;">
                                <LineChart :data="tabData.flow_chart" :height="280" />
                            </div>
                        </div>
                        <div class="chart-card">
                            <h4 class="chart-title">Transaction Types</h4>
                            <div class="chart-body">
                                <PieChart :data="tabData.type_chart" :height="250" />
                            </div>
                        </div>
                    </div>
                    <div class="charts-row">
                        <div class="chart-card">
                            <h4 class="chart-title">Revenue Trend</h4>
                            <div class="chart-body" style="height: 280px;">
                                <LineChart :data="tabData.revenue_chart" :height="280" />
                            </div>
                        </div>
                        <div class="chart-card">
                            <h4 class="chart-title">Points Health</h4>
                            <div class="chart-body metric-card-body" v-if="tabData.metrics">
                                <div class="metric-row">
                                    <span class="metric-label">Total in System</span>
                                    <span class="metric-value">{{ formatNumber(tabData.metrics.total_in_system) }}</span>
                                </div>
                                <div class="metric-row">
                                    <span class="metric-label">Period Inflow</span>
                                    <span class="metric-value text-green">+{{ formatNumber(tabData.metrics.period_inflow) }}</span>
                                </div>
                                <div class="metric-row">
                                    <span class="metric-label">Period Outflow</span>
                                    <span class="metric-value text-red">-{{ formatNumber(tabData.metrics.period_outflow) }}</span>
                                </div>
                                <div class="metric-row highlight">
                                    <span class="metric-label">Net Flow</span>
                                    <span class="metric-value" :class="tabData.metrics.net_flow >= 0 ? 'text-green' : 'text-red'">
                                        {{ tabData.metrics.net_flow >= 0 ? '+' : '' }}{{ formatNumber(tabData.metrics.net_flow) }}
                                    </span>
                                </div>
                                <div class="metric-row">
                                    <span class="metric-label">Pending Requests</span>
                                    <span class="metric-value text-orange">{{ tabData.metrics.pending_requests }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Posts Tab -->
                <div v-else-if="activeTab === 'posts' && tabData" class="tab-content">
                    <div class="charts-row">
                        <div class="chart-card">
                            <h4 class="chart-title">Post Creation Over Time</h4>
                            <div class="chart-body" style="height: 280px;">
                                <LineChart :data="tabData.creation_chart" :height="280" />
                            </div>
                        </div>
                        <div class="chart-card">
                            <h4 class="chart-title">Post Type</h4>
                            <div class="chart-body">
                                <PieChart :data="tabData.type_chart" :height="250" />
                            </div>
                        </div>
                    </div>
                    <div class="charts-row">
                        <div class="chart-card">
                            <h4 class="chart-title">Posts by Category</h4>
                            <div class="chart-body" style="height: 280px;">
                                <BarChart :data="tabData.category_chart" :height="280" />
                            </div>
                        </div>
                        <div class="chart-card">
                            <h4 class="chart-title">Post Metrics</h4>
                            <div class="chart-body metric-card-body" v-if="tabData.metrics">
                                <div class="metric-row">
                                    <span class="metric-label">Total Posts</span>
                                    <span class="metric-value">{{ formatNumber(tabData.metrics.total_posts) }}</span>
                                </div>
                                <div class="metric-row">
                                    <span class="metric-label">Published</span>
                                    <span class="metric-value text-green">{{ formatNumber(tabData.metrics.published_posts) }}</span>
                                </div>
                                <div class="metric-row">
                                    <span class="metric-label">Avg Views</span>
                                    <span class="metric-value">{{ tabData.metrics.avg_views }}</span>
                                </div>
                                <div class="metric-row">
                                    <span class="metric-label">Avg Favorites</span>
                                    <span class="metric-value">{{ tabData.metrics.avg_favorites }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAnalytics } from '../../composables/useAnalytics'
import LineChart from '../../components/admin/charts/LineChart.vue'
import PieChart from '../../components/admin/charts/PieChart.vue'
import BarChart from '../../components/admin/charts/BarChart.vue'

const {
    overview,
    tabData,
    activeTab,
    activeDays,
    loading,
    error,
    fetchAnalytics,
    changeRange,
    changeTab,
} = useAnalytics()

const dateRanges = [
    { label: '7 Days', days: 7 },
    { label: '30 Days', days: 30 },
    { label: '90 Days', days: 90 },
    { label: '1 Year', days: 365 },
]

const formatNumber = (value) => {
    if (value === null || value === undefined) return '0'
    const str = String(value)
    if (str.includes('%')) return str
    const num = Number(value)
    if (isNaN(num)) return str
    return new Intl.NumberFormat().format(num)
}

onMounted(async () => {
    await fetchAnalytics()
})
</script>

<style scoped>
.analytics-modern {
    padding: 0;
}

/* Date Range Bar */
.date-range-bar {
    background: white;
    border-radius: 12px;
    padding: 0.75rem 1.25rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    justify-content: flex-end;
}

.range-buttons {
    display: flex;
    gap: 0.5rem;
}

.range-btn {
    padding: 0.5rem 1rem;
    border: 1px solid #e5e7eb;
    background: white;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s ease;
}

.range-btn:hover {
    background: #f3f4f6;
    color: #374151;
}

.range-btn.active {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    border-color: #3b82f6;
}

/* Tabs Container */
.tabs-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.tabs-header {
    display: flex;
    border-bottom: 2px solid #e8ecef;
    background: #f8f9fa;
}

.tab-btn {
    flex: 1;
    padding: 1rem 1.5rem;
    border: none;
    background: transparent;
    font-size: 0.95rem;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    position: relative;
}

.tab-btn:hover {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.tab-btn.active {
    color: #3b82f6;
    background: white;
}

.tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.tabs-body {
    padding: 1.5rem;
}

.tab-content {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Loading State */
.loading-state {
    text-align: center;
    padding: 4rem 2rem;
}

.spinner {
    width: 50px;
    height: 50px;
    margin: 0 auto 1rem;
    border: 4px solid #f3f4f6;
    border-top: 4px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Metric Card Body */
.metric-card-body {
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0;
}

.metric-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.metric-row:last-child {
    border-bottom: none;
}

.metric-row.highlight {
    background: #f8f9fa;
    margin: 0.25rem -1.25rem;
    padding: 0.75rem 1.25rem;
    border-radius: 8px;
    border-bottom: none;
}

.metric-label {
    color: #6b7280;
    font-size: 0.9rem;
    font-weight: 500;
}

.metric-value {
    font-weight: 700;
    color: #1a1a2e;
    font-size: 1rem;
}

.metric-value.accent {
    color: #3b82f6;
    font-size: 1.15rem;
}

.text-green { color: #22c55e; }
.text-red { color: #ef4444; }
.text-orange { color: #f97316; }

/* Responsive */
@media (max-width: 768px) {
    .tabs-header {
        flex-direction: column;
    }

    .tab-btn {
        border-right: none;
        border-bottom: 1px solid #e8ecef;
    }

    .tab-btn:last-child {
        border-bottom: none;
    }

    .tabs-body {
        padding: 1rem;
    }

    .date-range-bar {
        justify-content: center;
    }

    .range-buttons {
        flex-wrap: wrap;
        justify-content: center;
    }
}
</style>
