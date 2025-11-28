<template>
  <div class="modern-dashboard">
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
      </div>
      <p class="mt-3 text-muted">Loading dashboard data...</p>
    </div>

    <!-- Dashboard Content -->
    <div v-else>
      <!-- Header with Growth Stats -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h2 class="mb-1">Dashboard Overview</h2>
              <p class="text-muted mb-0">Welcome back! Here's what's happening with your platform.</p>
            </div>
            <div class="text-end">
              <small class="text-muted d-block">Last updated</small>
              <strong>{{ formatDate(new Date()) }}</strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Growth Stats Cards with Animations -->
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="card border-0 shadow-sm growth-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <p class="text-muted mb-1 small">New Users This Month</p>
                  <h3 class="mb-0">{{ growthStats.users?.current || 0 }}</h3>
                </div>
                <div class="growth-badge" :class="growthStats.users?.growth >= 0 ? 'bg-success-subtle' : 'bg-danger-subtle'">
                  <i :class="growthStats.users?.growth >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                  {{ Math.abs(growthStats.users?.growth || 0) }}%
                </div>
              </div>
              <div class="progress mt-3" style="height: 4px;">
                <div class="progress-bar bg-success" :style="{width: calculateProgress(growthStats.users?.current, growthStats.users?.previous) + '%'}"></div>
              </div>
              <small class="text-muted mt-2 d-block">{{ growthStats.users?.previous || 0 }} last month</small>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card border-0 shadow-sm growth-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <p class="text-muted mb-1 small">New Posts This Month</p>
                  <h3 class="mb-0">{{ growthStats.posts?.current || 0 }}</h3>
                </div>
                <div class="growth-badge" :class="growthStats.posts?.growth >= 0 ? 'bg-success-subtle' : 'bg-danger-subtle'">
                  <i :class="growthStats.posts?.growth >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                  {{ Math.abs(growthStats.posts?.growth || 0) }}%
                </div>
              </div>
              <div class="progress mt-3" style="height: 4px;">
                <div class="progress-bar bg-primary" :style="{width: calculateProgress(growthStats.posts?.current, growthStats.posts?.previous) + '%'}"></div>
              </div>
              <small class="text-muted mt-2 d-block">{{ growthStats.posts?.previous || 0 }} last month</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Stats Cards -->
      <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6">
          <div class="stat-card bg-gradient-primary">
            <div class="stat-icon">
              <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-value">{{ stats.totalUsers }}</h3>
              <p class="stat-label">Total Users</p>
              <small class="stat-detail">{{ stats.activeUsers }} active</small>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-sm-6">
          <div class="stat-card bg-gradient-success">
            <div class="stat-icon">
              <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-value">{{ stats.totalPosts }}</h3>
              <p class="stat-label">Total Posts</p>
              <small class="stat-detail">{{ stats.publishedPosts }} published</small>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-sm-6">
          <div class="stat-card bg-gradient-warning">
            <div class="stat-icon">
              <i class="fas fa-coins"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-value">{{ formatNumber(stats.totalPoints) }}</h3>
              <p class="stat-label">Total Points</p>
              <small class="stat-detail">{{ formatNumber(stats.pointsUsed) }} used</small>
            </div>
          </div>
        </div>

        <div class="col-lg-3 col-sm-6">
          <div class="stat-card bg-gradient-danger">
            <div class="stat-icon">
              <i class="fas fa-flag"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-value">{{ stats.totalReports }}</h3>
              <p class="stat-label">Reports</p>
              <small class="stat-detail">{{ stats.pendingPurchaseRequests }} pending requests</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="row g-3 mb-4">
        <!-- Posts by State -->
        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
              <h5 class="mb-0"><i class="fas fa-chart-pie text-primary me-2"></i>Posts by State</h5>
            </div>
            <div class="card-body" style="height: 300px; max-height: 300px; overflow: hidden;">
              <pie-chart :data="postsByStateData" :height="250" :key="'posts-by-state'" />
            </div>
          </div>
        </div>

        <!-- Badge Distribution -->
        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
              <h5 class="mb-0"><i class="fas fa-award text-warning me-2"></i>Badge Distribution</h5>
            </div>
            <div class="card-body" style="height: 300px; max-height: 300px; overflow: hidden;">
              <pie-chart :data="badgeChartData" :height="250" :key="'badge-distribution'" />
            </div>
          </div>
        </div>

        <!-- Post Types -->
        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
              <h5 class="mb-0"><i class="fas fa-tags text-success me-2"></i>Post Types</h5>
            </div>
            <div class="card-body" style="height: 300px; max-height: 300px; overflow: hidden;">
              <pie-chart :data="postTypeChartData" :height="250" :key="'post-types'" />
            </div>
          </div>
        </div>
      </div>

      <!-- Trend Charts -->
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
              <h5 class="mb-0"><i class="fas fa-chart-line text-primary me-2"></i>Posts Trend</h5>
            </div>
            <div class="card-body" style="height: 350px; max-height: 350px; overflow: hidden;">
              <line-chart :data="postsByMonthData" :height="300" />
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
              <h5 class="mb-0"><i class="fas fa-user-plus text-success me-2"></i>User Growth</h5>
            </div>
            <div class="card-body" style="height: 350px; max-height: 350px; overflow: hidden;">
              <line-chart :data="usersByMonthData" :height="300" />
            </div>
          </div>
        </div>
      </div>

      <!-- Category Stats & Top Users -->
      <div class="row g-3 mb-4">
        <div class="col-md-8">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
              <h5 class="mb-0"><i class="fas fa-chart-bar text-info me-2"></i>Top Categories</h5>
            </div>
            <div class="card-body" style="height: 400px; max-height: 400px; overflow: hidden;">
              <bar-chart :data="postsByCategoryData" :height="350" />
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
              <h5 class="mb-0"><i class="fas fa-crown text-warning me-2"></i>Top Users</h5>
            </div>
            <div class="card-body p-0">
              <div class="list-group list-group-flush">
                <div v-for="(user, index) in topUsers" :key="user.id" class="list-group-item">
                  <div class="d-flex align-items-center">
                    <div class="rank-badge" :class="'rank-' + (index + 1)">{{ index + 1 }}</div>
                    <div class="flex-grow-1 ms-3">
                      <div class="d-flex justify-content-between">
                        <strong>{{ user.user_name }}</strong>
                        <span class="badge bg-primary">{{ user.posts_count }} posts</span>
                      </div>
                      <small class="text-muted">{{ user.email }}</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="row g-3">
        <div class="col-md-6">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
              <h5 class="mb-0"><i class="fas fa-newspaper text-primary me-2"></i>Recent Posts</h5>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <tbody>
                    <tr v-for="post in recentPosts" :key="post.id">
                      <td>
                        <div>
                          <strong class="d-block">{{ post.title }}</strong>
                          <small class="text-muted">by {{ post.user_name }}</small>
                        </div>
                      </td>
                      <td class="text-end">
                        <span :class="getStateBadgeClass(post.state)">{{ post.state }}</span>
                        <small class="text-muted d-block mt-1">{{ formatDateShort(post.created_at) }}</small>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
              <h5 class="mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Recent Reports</h5>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <tbody>
                    <tr v-for="report in recentReports" :key="report.id">
                      <td>
                        <div>
                          <strong class="d-block">{{ report.reported_item }}</strong>
                          <small class="text-muted">reported by {{ report.reporter_name }}</small>
                        </div>
                      </td>
                      <td class="text-end">
                        <small class="text-muted">{{ formatDateShort(report.created_at) }}</small>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import PieChart from '@/components/admin/charts/PieChart.vue'
import LineChart from '@/components/admin/charts/LineChart.vue'
import BarChart from '@/components/admin/charts/BarChart.vue'

console.log('ModernDashboard: Script setup running')

const loading = ref(true)
const stats = ref({})
const growthStats = ref({})
const recentPosts = ref([])
const recentUsers = ref([])
const topUsers = ref([])
const recentReports = ref([])

const badgeChartData = ref({})
const postTypeChartData = ref({})
const postsByStateData = ref({})
const postsByMonthData = ref({})
const usersByMonthData = ref({})
const postsByCategoryData = ref({})
const renderCount = ref(0)

let loadCount = 0

onMounted(async () => {
  console.log('ModernDashboard: onMounted')
  renderCount.value++
  console.log('ModernDashboard: renderCount =', renderCount.value)
  await loadDashboardData()
})

onBeforeUnmount(() => {
  console.log('ModernDashboard: onBeforeUnmount')
})

const loadDashboardData = async () => {
  loadCount++
  console.log('ModernDashboard: loadDashboardData called (count:', loadCount, ')')

  if (loadCount > 5) {
    console.error('ModernDashboard: Too many load attempts! Stopping to prevent infinite loop.')
    loading.value = false
    return
  }

  try {
    loading.value = true
    const response = await fetch('/api/dashboard')
    const data = await response.json()

    stats.value = data.stats
    growthStats.value = data.growthStats || {}
    recentPosts.value = data.recentPosts || []
    recentUsers.value = data.recentUsers || []
    topUsers.value = data.topUsers || []
    recentReports.value = data.recentReports || []

    badgeChartData.value = {
      labels: ['Normal', 'Golden', 'Diamond'],
      datasets: [{
        data: [data.normalPosts, data.goldenPosts, data.diamondPosts],
        backgroundColor: ['#6c757d', '#ffc107', '#17a2b8']
      }]
    };

    postTypeChartData.value = {
      labels: ['Offers', 'Requests'],
      datasets: [{
        data: [data.offerPosts, data.requestPosts],
        backgroundColor: ['#10B981', '#EF4444']
      }]
    };

    postsByStateData.value = {
      labels: data.postsByState.labels,
      datasets: [{
        data: data.postsByState.data,
        backgroundColor: data.postsByState.backgroundColor
      }]
    };

    postsByMonthData.value = {
      labels: data.postsByMonth.labels,
      datasets: [{
        label: 'Posts',
        data: data.postsByMonth.data,
        borderColor: '#6366F1',
        backgroundColor: 'rgba(99, 102, 241, 0.1)',
        tension: 0.4,
        fill: true
      }]
    };

    usersByMonthData.value = {
      labels: data.usersByMonth.labels,
      datasets: [{
        label: 'New Users',
        data: data.usersByMonth.data,
        borderColor: '#10B981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        tension: 0.4,
        fill: true
      }]
    };

    postsByCategoryData.value = {
      labels: data.postsByCategory.labels,
      datasets: [{
        label: 'Posts Count',
        data: data.postsByCategory.data,
        backgroundColor: 'rgba(99, 102, 241, 0.7)',
        borderColor: 'rgba(99, 102, 241, 1)',
        borderWidth: 1
      }]
    };
  } catch (error) {
    console.error('Error loading dashboard data:', error)
  } finally {
    loading.value = false
  }
}

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num || 0)
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleString()
}

const formatDateShort = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diff = now - date
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))

  if (days === 0) return 'Today'
  if (days === 1) return 'Yesterday'
  if (days < 7) return `${days} days ago`
  return date.toLocaleDateString()
}

const getStateBadgeClass = (state) => {
  const classes = {
    'published': 'badge bg-success',
    'not published': 'badge bg-warning',
    'rejected': 'badge bg-danger',
    'archive': 'badge bg-secondary'
  }
  return classes[state] || 'badge bg-info'
}

const calculateProgress = (current, previous) => {
  if (!previous || previous === 0) return 100
  return Math.min(100, (current / previous) * 100)
}
</script>

<style scoped>
.modern-dashboard {
  animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.growth-card {
  transition: transform 0.2s, box-shadow 0.2s;
}

.growth-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.growth-badge {
  padding: 8px 12px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
}

.stat-card {
  border-radius: 12px;
  padding: 20px;
  color: white;
  position: relative;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-success {
  background: linear-gradient(135deg, #10B981 0%, #059669 100%);
}

.bg-gradient-warning {
  background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
}

.bg-gradient-danger {
  background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
}

.stat-icon {
  position: absolute;
  right: 20px;
  top: 20px;
  font-size: 48px;
  opacity: 0.2;
}

.stat-content {
  position: relative;
  z-index: 1;
}

.stat-value {
  font-size: 32px;
  font-weight: 700;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 14px;
  margin-bottom: 4px;
  opacity: 0.9;
}

.stat-detail {
  font-size: 12px;
  opacity: 0.8;
}

.rank-badge {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 14px;
}

.rank-1 {
  background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
  color: white;
}

.rank-2 {
  background: linear-gradient(135deg, #C0C0C0 0%, #808080 100%);
  color: white;
}

.rank-3 {
  background: linear-gradient(135deg, #CD7F32 0%, #8B4513 100%);
  color: white;
}

.rank-4, .rank-5 {
  background: #e9ecef;
  color: #6c757d;
}

.card {
  transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
}

.bg-success-subtle {
  background-color: #d1f4e0;
  color: #0d6832;
}

.bg-danger-subtle {
  background-color: #f8d7da;
  color: #721c24;
}
</style>
