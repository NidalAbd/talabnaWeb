<template>
  <div class="modern-dashboard-content">
    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading dashboard...</p>
    </div>

    <!-- Dashboard Content -->
    <div v-else>
      <!-- Stats Cards -->
      <div class="stats-dashboard">
        <div class="stats-grid">
          <a href="/users" class="stat-card-compact stat-blue" style="text-decoration:none;">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-info">
              <div class="stat-value-compact">{{ formatNumber(stats.totalUsers) }}</div>
              <div class="stat-label-compact">Total Users</div>
            </div>
          </a>
          <a href="/users?status=active" class="stat-card-compact stat-green" style="text-decoration:none;">
            <div class="stat-icon"><i class="fas fa-user-check"></i></div>
            <div class="stat-info">
              <div class="stat-value-compact">{{ formatNumber(stats.activeUsers) }}</div>
              <div class="stat-label-compact">Active Users</div>
            </div>
          </a>
          <a href="/service_posts" class="stat-card-compact stat-orange" style="text-decoration:none;">
            <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-info">
              <div class="stat-value-compact">{{ formatNumber(stats.totalPosts) }}</div>
              <div class="stat-label-compact">Total Posts</div>
            </div>
          </a>
          <a href="/service_posts?state=published" class="stat-card-compact stat-purple" style="text-decoration:none;">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
              <div class="stat-value-compact">{{ formatNumber(stats.publishedPosts) }}</div>
              <div class="stat-label-compact">Published Posts</div>
            </div>
          </a>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="charts-grid">
        <!-- Badge Distribution -->
        <div class="modern-card">
          <div class="card-header">
            <h3><i class="fas fa-certificate"></i> Badge Distribution</h3>
          </div>
          <div class="card-body">
            <pie-chart :data="badgeChartData" :height="250" />
          </div>
        </div>

        <!-- Post Types -->
        <div class="modern-card">
          <div class="card-header">
            <h3><i class="fas fa-chart-pie"></i> Post Types</h3>
          </div>
          <div class="card-body">
            <pie-chart :data="postTypeChartData" :height="250" />
          </div>
        </div>
      </div>

      <!-- Trends Row -->
      <div class="charts-grid">
        <!-- Posts by Month -->
        <div class="modern-card">
          <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> Posts by Month</h3>
          </div>
          <div class="card-body">
            <line-chart :data="postsByMonthData" :height="250" />
          </div>
        </div>

        <!-- Users by Month -->
        <div class="modern-card">
          <div class="card-header">
            <h3><i class="fas fa-users"></i> New Users by Month</h3>
          </div>
          <div class="card-body">
            <line-chart :data="usersByMonthData" :height="250" />
          </div>
        </div>
      </div>

      <!-- Category Stats -->
      <div class="modern-card">
        <div class="card-header">
          <h3><i class="fas fa-chart-bar"></i> Posts by Category (Top 10)</h3>
        </div>
        <div class="card-body">
          <bar-chart :data="postsByCategoryData" :height="300" />
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="charts-grid">
        <!-- Recent Posts -->
        <div class="modern-card">
          <div class="card-header">
            <h3><i class="fas fa-clock"></i> Recent Posts</h3>
          </div>
          <div class="table-container">
            <table class="modern-table">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>User</th>
                  <th>State</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="recentPosts.length === 0">
                  <td colspan="4" class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No recent posts</p>
                  </td>
                </tr>
                <tr v-for="post in recentPosts" :key="post.id">
                  <td class="post-title">{{ post.title }}</td>
                  <td>{{ post.user_name }}</td>
                  <td>
                    <span :class="getStateBadgeClass(post.state)">
                      {{ post.state }}
                    </span>
                  </td>
                  <td class="date-cell">{{ formatDate(post.created_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Users -->
        <div class="modern-card">
          <div class="card-header">
            <h3><i class="fas fa-user-plus"></i> Recent Users</h3>
          </div>
          <div class="table-container">
            <table class="modern-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="recentUsers.length === 0">
                  <td colspan="4" class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No recent users</p>
                  </td>
                </tr>
                <tr v-for="user in recentUsers" :key="user.id">
                  <td class="user-name">{{ user.user_name }}</td>
                  <td class="user-email">{{ user.email }}</td>
                  <td>
                    <span :class="getUserStatusBadgeClass(user.is_active)">
                      {{ user.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="date-cell">{{ formatDate(user.created_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import PieChart from '@/components/admin/charts/PieChart.vue'
import LineChart from '@/components/admin/charts/LineChart.vue'
import BarChart from '@/components/admin/charts/BarChart.vue'

const loading = ref(true)
const stats = ref({
  totalUsers: 0,
  activeUsers: 0,
  bannedUsers: 0,
  totalPosts: 0,
  publishedPosts: 0,
  notPublishedPosts: 0,
  rejectedPosts: 0,
  totalReports: 0,
  totalPoints: 0,
  pointsUsed: 0,
  pendingPurchaseRequests: 0
})

const recentPosts = ref([])
const recentUsers = ref([])

const badgeChartData = ref({})
const postTypeChartData = ref({})
const postsByMonthData = ref({})
const usersByMonthData = ref({})
const postsByCategoryData = ref({})

onMounted(async () => {
  await loadDashboardData()
})

const loadDashboardData = async () => {
  try {
    loading.value = true
    const response = await fetch('/api/dashboard')
    const data = await response.json()

    // Update stats
    stats.value = data.stats

    // Update tables
    recentPosts.value = data.recentPosts || []
    recentUsers.value = data.recentUsers || []

    // Update charts
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

const formatNumber = (value) => {
  if (value === null || value === undefined) return '0'
  return new Intl.NumberFormat().format(value)
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString()
}

const getStateBadgeClass = (state) => {
  const classes = {
    'published': 'status-badge success',
    'not published': 'status-badge warning',
    'rejected': 'status-badge danger',
    'archive': 'status-badge secondary'
  }
  return classes[state] || 'status-badge info'
}

const getUserStatusBadgeClass = (isActive) => {
  return isActive ? 'status-badge success' : 'status-badge secondary'
}
</script>

<style scoped>
.modern-dashboard-content {
  padding: 20px;
}

/* Loading State */
.loading-state {
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

/* Stats Grid */
/* Charts Grid */
.charts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

/* Modern Card */
.modern-card {
  background: white;
  border-radius: 15px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 20px 25px;
}

.card-header h3 {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 10px;
}

.card-body {
  padding: 25px;
}

/* Table Container */
.table-container {
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
  padding: 12px 15px;
  text-align: left;
  font-weight: 600;
  font-size: 13px;
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
  padding: 12px 15px;
  vertical-align: middle;
  font-size: 14px;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #6c757d;
}

.empty-state i {
  font-size: 2rem;
  margin-bottom: 10px;
  opacity: 0.5;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}

.post-title,
.user-name {
  font-weight: 600;
  color: #2c3e50;
}

.user-email {
  color: #6c757d;
  font-size: 13px;
}

.date-cell {
  color: #6c757d;
  font-size: 13px;
}

.status-badge {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  display: inline-block;
  text-transform: capitalize;
}

.status-badge.success {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  color: white;
}

.status-badge.warning {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
}

.status-badge.danger {
  background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
  color: white;
}

.status-badge.secondary {
  background: #6c757d;
  color: white;
}

.status-badge.info {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
  .charts-grid {
    grid-template-columns: 1fr;
  }

  .modern-table {
    font-size: 12px;
  }

  .modern-table thead th,
  .modern-table tbody td {
    padding: 8px 10px;
  }

}
</style>
