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
            <!-- User Statistics -->
            <div class="stats-section">
                <h3 class="stats-section-title">
                    <i class="fas fa-users"></i>
                    User Statistics
                </h3>
                <div class="stats-grid">
                    <div class="stat-card blue">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">{{ statistics.users.total }}</h3>
                            <p class="stat-label">Total Users</p>
                        </div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">{{ statistics.users.active }}</h3>
                            <p class="stat-label">Active Users</p>
                        </div>
                    </div>
                    <div class="stat-card red">
                        <div class="stat-icon">
                            <i class="fas fa-user-slash"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">{{ statistics.users.banned }}</h3>
                            <p class="stat-label">Banned Users</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Post Statistics -->
            <div class="stats-section">
                <h3 class="stats-section-title">
                    <i class="fas fa-clipboard-list"></i>
                    Service Post Statistics
                </h3>
                <div class="stats-grid">
                    <div class="stat-card purple">
                        <div class="stat-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">{{ statistics.posts.total }}</h3>
                            <p class="stat-label">Total Posts</p>
                        </div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">{{ statistics.posts.by_status.published }}</h3>
                            <p class="stat-label">Published Posts</p>
                        </div>
                    </div>
                    <div class="stat-card orange">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">{{ statistics.posts.by_status.pending }}</h3>
                            <p class="stat-label">Pending Posts</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Posts by Category -->
            <div class="stats-section">
                <h3 class="stats-section-title">
                    <i class="fas fa-folder"></i>
                    Posts by Category
                </h3>
                <div class="category-grid">
                    <div class="category-card">
                        <div class="category-icon blue">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="category-content">
                            <span class="category-label">Devices</span>
                            <span class="category-value">{{ statistics.posts.by_category.devices }}</span>
                        </div>
                    </div>
                    <div class="category-card">
                        <div class="category-icon green">
                            <i class="fas fa-car"></i>
                        </div>
                        <div class="category-content">
                            <span class="category-label">Cars</span>
                            <span class="category-value">{{ statistics.posts.by_category.cars }}</span>
                        </div>
                    </div>
                    <div class="category-card">
                        <div class="category-icon orange">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="category-content">
                            <span class="category-label">Jobs</span>
                            <span class="category-value">{{ statistics.posts.by_category.jobs }}</span>
                        </div>
                    </div>
                    <div class="category-card">
                        <div class="category-icon red">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="category-content">
                            <span class="category-label">Real Estate</span>
                            <span class="category-value">{{ statistics.posts.by_category.real_estate }}</span>
                        </div>
                    </div>
                    <div class="category-card">
                        <div class="category-icon purple">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="category-content">
                            <span class="category-label">Services</span>
                            <span class="category-value">{{ statistics.posts.by_category.services }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Badge Statistics -->
            <div class="stats-section">
                <h3 class="stats-section-title">
                    <i class="fas fa-award"></i>
                    Badge Statistics
                </h3>
                <div class="stats-grid">
                    <div class="stat-card gold">
                        <div class="stat-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">{{ statistics.posts.by_badge.golden }}</h3>
                            <p class="stat-label">Golden Badges</p>
                        </div>
                    </div>
                    <div class="stat-card diamond">
                        <div class="stat-icon">
                            <i class="fas fa-gem"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">{{ statistics.posts.by_badge.diamond }}</h3>
                            <p class="stat-label">Diamond Badges</p>
                        </div>
                    </div>
                    <div class="stat-card gray">
                        <div class="stat-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">{{ statistics.posts.by_badge.normal }}</h3>
                            <p class="stat-label">Normal Posts</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase Requests Statistics -->
            <div class="stats-section">
                <h3 class="stats-section-title">
                    <i class="fas fa-shopping-cart"></i>
                    Point Purchase Requests
                </h3>
                <div class="stats-grid-4">
                    <div class="stat-card blue">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">{{ statistics.purchase_requests.total }}</h3>
                            <p class="stat-label">Total Requests</p>
                        </div>
                    </div>
                    <div class="stat-card orange">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">{{ statistics.purchase_requests.pending }}</h3>
                            <p class="stat-label">Pending</p>
                        </div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">{{ statistics.purchase_requests.approved }}</h3>
                            <p class="stat-label">Approved</p>
                        </div>
                    </div>
                    <div class="stat-card red">
                        <div class="stat-icon">
                            <i class="fas fa-times"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">{{ statistics.purchase_requests.cancelled }}</h3>
                            <p class="stat-label">Cancelled</p>
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

const { statistics, loading, error, fetchStatistics } = useStatistics()

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

/* Header Section */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
}

.header-content {
    flex: 1;
}

.section-title {
    font-size: 28px;
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0 0 8px 0;
}

.section-title i {
    color: #3498db;
}

.section-subtitle {
    font-size: 14px;
    color: #7f8c8d;
    margin: 0;
}

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

/* Statistics Sections */
.stats-section {
    margin-bottom: 40px;
}

.stats-section-title {
    font-size: 20px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.stats-section-title i {
    color: #3498db;
}

/* Stats Grid (3 columns) */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

/* Stats Grid (4 columns) */
.stats-grid-4 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

/* Stat Card */
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
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
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
    flex-shrink: 0;
}

.stat-card.blue .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-card.green .stat-icon {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
}

.stat-card.red .stat-icon {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.stat-card.orange .stat-icon {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
}

.stat-card.purple .stat-icon {
    background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
}

.stat-card.gold .stat-icon {
    background: linear-gradient(135deg, #f39c12 0%, #f1c40f 100%);
}

.stat-card.diamond .stat-icon {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.stat-card.gray .stat-icon {
    background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 4px 0;
    line-height: 1;
}

.stat-label {
    font-size: 14px;
    color: #7f8c8d;
    margin: 0;
}

/* Category Grid */
.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.category-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    transition: all 0.3s;
}

.category-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.category-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
    flex-shrink: 0;
}

.category-icon.blue {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.category-icon.green {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
}

.category-icon.orange {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
}

.category-icon.red {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.category-icon.purple {
    background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
}

.category-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.category-label {
    font-size: 13px;
    color: #7f8c8d;
    font-weight: 500;
}

.category-value {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
}

/* Responsive Design */
@media (max-width: 768px) {
    .statistics-dashboard-modern {
        padding: 15px;
    }

    .section-header {
        flex-direction: column;
        gap: 16px;
    }

    .stats-grid,
    .stats-grid-4 {
        grid-template-columns: 1fr;
    }

    .category-grid {
        grid-template-columns: 1fr;
    }

    .stat-value {
        font-size: 24px;
    }

    .section-title {
        font-size: 22px;
    }
}
</style>
