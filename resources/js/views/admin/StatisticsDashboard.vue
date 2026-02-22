<template>
    <div class="statistics-dashboard-modern">
        <!-- Action Bar -->
        <div class="action-bar mb-4">
            <button class="action-btn primary" @click="loadData">
                <i class="fas fa-sync-alt"></i>
                Refresh
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
            <div class="loader-advanced"></div>
            <p>Loading statistics...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="error-state">
            <i class="fas fa-exclamation-triangle"></i>
            <p>{{ error }}</p>
        </div>

        <!-- Statistics Content -->
        <div v-else-if="statistics" class="statistics-content">

            <!-- Today's Pulse -->
            <div class="stats-section">
                <h3 class="stats-section-title">
                    <i class="fas fa-heartbeat"></i>
                    Today's Pulse
                </h3>
                <div class="stats-dashboard">
                    <div class="stats-grid">
                        <div
                            v-for="stat in todaysPulse"
                            :key="stat.label"
                            class="stat-card-compact"
                            :class="stat.color"
                        >
                            <div class="stat-icon"><i :class="stat.icon"></i></div>
                            <div class="stat-info">
                                <div class="stat-value-compact">{{ stat.value }}</div>
                                <div class="stat-label-compact">{{ stat.label }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Required -->
            <div class="stats-section">
                <h3 class="stats-section-title">
                    <i class="fas fa-exclamation-circle"></i>
                    Action Required
                </h3>
                <div class="action-required-grid">
                    <a
                        v-for="item in actionRequired"
                        :key="item.label"
                        :href="item.link"
                        class="action-required-card"
                        :class="item.color"
                    >
                        <div class="action-required-icon">
                            <i :class="item.icon"></i>
                        </div>
                        <div class="action-required-info">
                            <div class="action-required-value">{{ item.value }}</div>
                            <div class="action-required-label">{{ item.label }}</div>
                        </div>
                        <div class="action-required-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Distribution Charts -->
            <div class="stats-section">
                <h3 class="stats-section-title">
                    <i class="fas fa-chart-pie"></i>
                    Distributions
                </h3>
                <div class="charts-grid-2x2">
                    <div v-if="charts.user_status" class="chart-card">
                        <h4 class="chart-title">User Status</h4>
                        <div class="chart-body">
                            <PieChart :data="charts.user_status" :height="220" />
                        </div>
                    </div>
                    <div v-if="charts.post_state" class="chart-card">
                        <h4 class="chart-title">Post State</h4>
                        <div class="chart-body">
                            <PieChart :data="charts.post_state" :height="220" />
                        </div>
                    </div>
                    <div v-if="charts.by_category" class="chart-card">
                        <h4 class="chart-title">Posts by Category</h4>
                        <div class="chart-body" style="height: 250px;">
                            <BarChart :data="charts.by_category" :height="250" />
                        </div>
                    </div>
                    <div v-if="charts.badge_dist" class="chart-card">
                        <h4 class="chart-title">Badge Distribution</h4>
                        <div class="chart-body">
                            <PieChart :data="charts.badge_dist" :height="220" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Health Rates -->
            <div class="stats-section">
                <h3 class="stats-section-title">
                    <i class="fas fa-tachometer-alt"></i>
                    System Health Rates
                </h3>
                <div class="rates-container">
                    <div
                        v-for="rate in systemRates"
                        :key="rate.label"
                        class="rate-item"
                    >
                        <div class="rate-header">
                            <span class="rate-label">{{ rate.label }}</span>
                            <span class="rate-value" :class="`text-${rate.color}`">{{ rate.value }}%</span>
                        </div>
                        <div class="rate-bar">
                            <div
                                class="rate-bar-fill"
                                :class="`bg-${rate.color}`"
                                :style="{ width: rate.value + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useStatistics } from '../../composables/useStatistics'
import PieChart from '../../components/admin/charts/PieChart.vue'
import BarChart from '../../components/admin/charts/BarChart.vue'

const {
    statistics,
    loading,
    error,
    fetchStatistics,
    todaysPulse,
    actionRequired,
    charts,
    systemRates,
} = useStatistics()

const loadData = () => {
    fetchStatistics()
}

onMounted(() => {
    loadData()
})
</script>

<style scoped>
.statistics-dashboard-modern {
    padding: 20px;
}

/* Action Bar */
.action-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.action-btn.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.action-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

/* Loading State */
.loading-state {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.loader-advanced {
    width: 60px;
    height: 60px;
    border: 5px solid #ecf0f1;
    border-top-color: #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
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

/* Stats Section */
.stats-section {
    margin-bottom: 2rem;
}

.stats-section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 1rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stats-section-title i {
    color: #667eea;
}

/* Action Required */
.action-required-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
}

.action-required-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.action-required-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.action-required-card.warning {
    border-left-color: #f97316;
}

.action-required-card.danger {
    border-left-color: #ef4444;
}

.action-required-card.info {
    border-left-color: #06b6d4;
}

.action-required-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.action-required-card.warning .action-required-icon {
    background: rgba(249, 115, 22, 0.15);
    color: #f97316;
}

.action-required-card.danger .action-required-icon {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
}

.action-required-card.info .action-required-icon {
    background: rgba(6, 182, 212, 0.15);
    color: #06b6d4;
}

.action-required-info {
    flex: 1;
}

.action-required-value {
    font-size: 1.75rem;
    font-weight: 800;
    color: #1a1a2e;
    line-height: 1.2;
}

.action-required-label {
    font-size: 0.8rem;
    color: #6b7280;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.action-required-arrow {
    color: #d1d5db;
    font-size: 1rem;
}

.action-required-card:hover .action-required-arrow {
    color: #6b7280;
}

/* Charts 2x2 Grid */
.charts-grid-2x2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

/* System Health Rates */
.rates-container {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.rate-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.rate-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.rate-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #374151;
}

.rate-value {
    font-size: 0.9rem;
    font-weight: 700;
}

.text-primary { color: #3b82f6; }
.text-success { color: #22c55e; }
.text-warning { color: #f97316; }

.rate-bar {
    width: 100%;
    height: 10px;
    background: #f3f4f6;
    border-radius: 5px;
    overflow: hidden;
}

.rate-bar-fill {
    height: 100%;
    border-radius: 5px;
    transition: width 1s ease-out;
}

.bg-primary { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
.bg-success { background: linear-gradient(90deg, #22c55e, #4ade80); }
.bg-warning { background: linear-gradient(90deg, #f97316, #fb923c); }

/* Responsive */
@media (max-width: 768px) {
    .statistics-dashboard-modern {
        padding: 15px;
    }

    .charts-grid-2x2 {
        grid-template-columns: 1fr;
    }

    .action-required-grid {
        grid-template-columns: 1fr;
    }
}

.mb-4 {
    margin-bottom: 1rem;
}
</style>
