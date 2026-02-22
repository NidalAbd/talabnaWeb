<template>
    <div class="analytics-modern">

        <!-- Overview Stats -->
        <div class="stats-dashboard">
          <div class="stats-grid">
            <div class="stat-card-compact" :class="stat.color" v-for="stat in overview" :key="stat.label">
              <div class="stat-icon"><i :class="stat.icon"></i></div>
              <div class="stat-info">
                <div class="stat-value-compact">{{ formatNumber(stat.value) }}</div>
                <div class="stat-label-compact">{{ stat.label }}</div>
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
                    @click="activeTab = 'users'; loadTabData('users')"
                >
                    <i class="fas fa-users"></i> User Analytics
                </button>
                <button
                    class="tab-btn"
                    :class="{ active: activeTab === 'points' }"
                    @click="activeTab = 'points'; loadTabData('points')"
                >
                    <i class="fas fa-coins"></i> Point Analytics
                </button>
                <button
                    class="tab-btn"
                    :class="{ active: activeTab === 'posts' }"
                    @click="activeTab = 'posts'; loadTabData('posts')"
                >
                    <i class="fas fa-file-alt"></i> Post Analytics
                </button>
            </div>

            <div class="tabs-body">
                <!-- User Analytics Tab -->
                <div v-show="activeTab === 'users'" class="tab-content">
                    <div v-if="loading" class="loading-state">
                        <div class="spinner"></div>
                        <p>Loading user analytics...</p>
                    </div>
                    <div v-else-if="userAnalytics">
                        <!-- User Stats Cards -->
                        <div class="info-boxes-grid mb-4">
                            <div class="info-box blue">
                                <div class="info-box-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="info-box-content">
                                    <span class="info-box-label">Total Users</span>
                                    <span class="info-box-value">{{ userAnalytics.stats.total_users }}</span>
                                </div>
                            </div>
                            <div class="info-box green">
                                <div class="info-box-icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div class="info-box-content">
                                    <span class="info-box-label">Active Users</span>
                                    <span class="info-box-value">{{ userAnalytics.stats.active_users }}</span>
                                </div>
                            </div>
                            <div class="info-box orange">
                                <div class="info-box-icon">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="info-box-content">
                                    <span class="info-box-label">New This Month</span>
                                    <span class="info-box-value">{{ userAnalytics.stats.new_this_month }}</span>
                                </div>
                            </div>
                            <div class="info-box purple">
                                <div class="info-box-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="info-box-content">
                                    <span class="info-box-label">Growth Rate</span>
                                    <span class="info-box-value">{{ userAnalytics.stats.growth_rate }}%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Metrics -->
                        <div class="cards-grid">
                            <div class="metric-card">
                                <h3 class="card-title">User Engagement</h3>
                                <div class="metric-table">
                                    <div class="metric-row">
                                        <span class="metric-label">Users with Posts:</span>
                                        <span class="metric-value">{{ userAnalytics.stats.users_with_posts }}</span>
                                    </div>
                                    <div class="metric-row">
                                        <span class="metric-label">Users with Points:</span>
                                        <span class="metric-value">{{ userAnalytics.stats.users_with_points }}</span>
                                    </div>
                                    <div class="metric-row">
                                        <span class="metric-label">Premium Users:</span>
                                        <span class="metric-value">{{ userAnalytics.stats.premium_users }}</span>
                                    </div>
                                    <div class="metric-row">
                                        <span class="metric-label">Monthly Active:</span>
                                        <span class="metric-value">{{ userAnalytics.stats.monthly_active }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="metric-card">
                                <h3 class="card-title">Top Users by Posts</h3>
                                <div class="top-list">
                                    <div class="top-item" v-for="user in userAnalytics.top_users" :key="user.id">
                                        <span class="top-name">{{ user.name }}</span>
                                        <span class="badge primary">{{ user.posts_count }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Point Analytics Tab -->
                <div v-show="activeTab === 'points'" class="tab-content">
                    <div v-if="loading" class="loading-state">
                        <div class="spinner"></div>
                        <p>Loading point analytics...</p>
                    </div>
                    <div v-else-if="pointAnalytics">
                        <!-- Point Stats Cards -->
                        <div class="info-boxes-grid mb-4">
                            <div class="info-box orange">
                                <div class="info-box-icon">
                                    <i class="fas fa-coins"></i>
                                </div>
                                <div class="info-box-content">
                                    <span class="info-box-label">Total Points</span>
                                    <span class="info-box-value">{{ pointAnalytics.stats.total_points }}</span>
                                </div>
                            </div>
                            <div class="info-box green">
                                <div class="info-box-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="info-box-content">
                                    <span class="info-box-label">Monthly Sold</span>
                                    <span class="info-box-value">{{ pointAnalytics.stats.monthly_sold }}</span>
                                </div>
                            </div>
                            <div class="info-box red">
                                <div class="info-box-icon">
                                    <i class="fas fa-minus-circle"></i>
                                </div>
                                <div class="info-box-content">
                                    <span class="info-box-label">Monthly Used</span>
                                    <span class="info-box-value">{{ pointAnalytics.stats.monthly_used }}</span>
                                </div>
                            </div>
                            <div class="info-box blue">
                                <div class="info-box-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="info-box-content">
                                    <span class="info-box-label">Growth Rate</span>
                                    <span class="info-box-value">{{ pointAnalytics.stats.growth_rate }}%</span>
                                </div>
                            </div>
                        </div>

                        <div class="cards-grid">
                            <div class="metric-card">
                                <h3 class="card-title">Point System Overview</h3>
                                <div class="metric-table">
                                    <div class="metric-row">
                                        <span class="metric-label">Total Transactions:</span>
                                        <span class="metric-value">{{ pointAnalytics.stats.total_transactions }}</span>
                                    </div>
                                    <div class="metric-row">
                                        <span class="metric-label">Total Requests:</span>
                                        <span class="metric-value">{{ pointAnalytics.stats.total_requests }}</span>
                                    </div>
                                    <div class="metric-row">
                                        <span class="metric-label">Pending Requests:</span>
                                        <span class="badge warning">{{ pointAnalytics.stats.pending_requests }}</span>
                                    </div>
                                    <div class="metric-row">
                                        <span class="metric-label">Approved Requests:</span>
                                        <span class="badge success">{{ pointAnalytics.stats.approved_requests }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="metric-card">
                                <h3 class="card-title">Top Users by Points</h3>
                                <div class="top-list">
                                    <div class="top-item" v-for="user in pointAnalytics.top_users" :key="user.id">
                                        <span class="top-name">{{ user.name }}</span>
                                        <span class="badge warning">{{ user.total_points }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Post Analytics Tab -->
                <div v-show="activeTab === 'posts'" class="tab-content">
                    <div v-if="loading" class="loading-state">
                        <div class="spinner"></div>
                        <p>Loading post analytics...</p>
                    </div>
                    <div v-else-if="postAnalytics">
                        <!-- Post Stats Cards -->
                        <div class="info-boxes-grid mb-4">
                            <div class="info-box blue">
                                <div class="info-box-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="info-box-content">
                                    <span class="info-box-label">Total Posts</span>
                                    <span class="info-box-value">{{ postAnalytics.stats.total }}</span>
                                </div>
                            </div>
                            <div class="info-box green">
                                <div class="info-box-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="info-box-content">
                                    <span class="info-box-label">Published</span>
                                    <span class="info-box-value">{{ postAnalytics.stats.published }}</span>
                                </div>
                            </div>
                            <div class="info-box orange">
                                <div class="info-box-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="info-box-content">
                                    <span class="info-box-label">Pending</span>
                                    <span class="info-box-value">{{ postAnalytics.stats.pending }}</span>
                                </div>
                            </div>
                            <div class="info-box purple">
                                <div class="info-box-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="info-box-content">
                                    <span class="info-box-label">Growth Rate</span>
                                    <span class="info-box-value">{{ postAnalytics.stats.growth_rate }}%</span>
                                </div>
                            </div>
                        </div>

                        <div class="cards-grid">
                            <div class="metric-card">
                                <h3 class="card-title">Badge Distribution</h3>
                                <div class="metric-table">
                                    <div class="metric-row">
                                        <span class="metric-label">
                                            <i class="fas fa-gem" style="color: #17a2b8;"></i> Diamond Posts:
                                        </span>
                                        <span class="metric-value">{{ postAnalytics.stats.diamond }}</span>
                                    </div>
                                    <div class="metric-row">
                                        <span class="metric-label">
                                            <i class="fas fa-medal" style="color: #f39c12;"></i> Golden Posts:
                                        </span>
                                        <span class="metric-value">{{ postAnalytics.stats.golden }}</span>
                                    </div>
                                    <div class="metric-row">
                                        <span class="metric-label">
                                            <i class="fas fa-circle" style="color: #6c757d;"></i> Normal Posts:
                                        </span>
                                        <span class="metric-value">{{ postAnalytics.stats.normal }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="metric-card">
                                <h3 class="card-title">Top Posts by Views</h3>
                                <div class="top-list">
                                    <div class="top-item" v-for="post in postAnalytics.top_posts" :key="post.id">
                                        <span class="top-name">{{ truncate(post.title, 30) }}</span>
                                        <span class="badge info">{{ post.view_count }}</span>
                                    </div>
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
import { ref, onMounted } from 'vue'
import { useAnalytics } from '../../composables/useAnalytics'

const {
    userAnalytics,
    pointAnalytics,
    postAnalytics,
    overview,
    loading,
    error,
    fetchUserAnalytics,
    fetchPointAnalytics,
    fetchPostAnalytics,
    fetchOverview
} = useAnalytics()

const activeTab = ref('users')

const formatNumber = (value) => {
  if (value === null || value === undefined) return '0'
  return new Intl.NumberFormat().format(value)
}

const truncate = (text, length) => {
    if (!text) return ''
    return text.length > length ? text.substring(0, length) + '...' : text
}

const loadTabData = async (tab) => {
    if (tab === 'users' && !userAnalytics.value) {
        await fetchUserAnalytics()
    } else if (tab === 'points' && !pointAnalytics.value) {
        await fetchPointAnalytics()
    } else if (tab === 'posts' && !postAnalytics.value) {
        await fetchPostAnalytics()
    }
}

onMounted(async () => {
    console.log('📊 Analytics component mounted')

    // Load overview and user analytics by default
    await fetchOverview()
    await fetchUserAnalytics()
})
</script>

<style scoped>
.analytics-modern {
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
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
}

.tab-btn.active {
    color: #3498db;
    background: white;
}

.tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.tabs-body {
    padding: 2rem;
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
    border-top: 4px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Info Boxes Grid */
.info-boxes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.25rem;
}

.info-box {
    border-radius: 12px;
    padding: 1.25rem;
    color: white;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.info-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.info-box.blue {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.info-box.green {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
}

.info-box.orange {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: #212529;
}

.info-box.purple {
    background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
}

.info-box.red {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.info-box-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.info-box-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-box-label {
    font-size: 0.85rem;
    opacity: 0.95;
}

.info-box-value {
    font-size: 1.75rem;
    font-weight: 700;
}

/* Cards Grid */
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.metric-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.card-title {
    padding: 1.25rem 1.5rem;
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #1a1a1a;
    border-bottom: 1px solid #e8ecef;
    background: #f8f9fa;
}

.metric-table {
    padding: 1rem 1.5rem;
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

.metric-label {
    color: #666;
    font-size: 0.9rem;
}

.metric-value {
    font-weight: 600;
    color: #1a1a1a;
    font-size: 1rem;
}

.top-list {
    padding: 1rem 1.5rem;
}

.top-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.top-item:last-child {
    border-bottom: none;
}

.top-name {
    color: #333;
    font-size: 0.9rem;
    flex: 1;
}

.badge {
    padding: 0.35rem 0.75rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge.primary {
    background: #cfe2ff;
    color: #0d6efd;
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

.mb-4 {
    margin-bottom: 1.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

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

    .cards-grid {
        grid-template-columns: 1fr;
    }
}
</style>
