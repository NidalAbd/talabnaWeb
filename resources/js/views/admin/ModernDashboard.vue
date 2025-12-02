<template>
  <div class="dashboard-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
      <div class="header-left">
        <h1 class="dashboard-title">Dashboard</h1>
        <p class="dashboard-subtitle">Welcome back! Here's what's happening today.</p>
      </div>
      <div class="header-right">
        <div class="date-badge">
          <i class="fas fa-calendar-alt"></i>
          {{ currentDate }}
        </div>
        <button class="refresh-btn" @click="loadAllStats" :disabled="loading">
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
          Refresh
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="loader-modern"></div>
      <p>Loading dashboard data...</p>
    </div>

    <template v-else>
      <!-- Growth Summary Section -->
      <div class="section-row">
        <div class="growth-card growth-users">
          <div class="growth-content">
            <div class="growth-icon">
              <i class="fas fa-user-plus"></i>
            </div>
            <div class="growth-info">
              <span class="growth-value">{{ formatNumber(growthStats.users?.current || 0) }}</span>
              <span class="growth-label">New Users This Month</span>
            </div>
            <div class="growth-trend" :class="(growthStats.users?.growth || 0) >= 0 ? 'trend-up' : 'trend-down'">
              <i :class="(growthStats.users?.growth || 0) >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
              {{ Math.abs(growthStats.users?.growth || 0) }}%
            </div>
          </div>
          <div class="growth-comparison">
            vs {{ formatNumber(growthStats.users?.previous || 0) }} last month
          </div>
          <div class="growth-bar">
            <div class="growth-bar-fill" :style="{ width: calculateGrowthWidth(growthStats.users) + '%' }"></div>
          </div>
        </div>

        <div class="growth-card growth-posts">
          <div class="growth-content">
            <div class="growth-icon">
              <i class="fas fa-file-alt"></i>
            </div>
            <div class="growth-info">
              <span class="growth-value">{{ formatNumber(growthStats.posts?.current || 0) }}</span>
              <span class="growth-label">New Posts This Month</span>
            </div>
            <div class="growth-trend" :class="(growthStats.posts?.growth || 0) >= 0 ? 'trend-up' : 'trend-down'">
              <i :class="(growthStats.posts?.growth || 0) >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
              {{ Math.abs(growthStats.posts?.growth || 0) }}%
            </div>
          </div>
          <div class="growth-comparison">
            vs {{ formatNumber(growthStats.posts?.previous || 0) }} last month
          </div>
          <div class="growth-bar">
            <div class="growth-bar-fill" :style="{ width: calculateGrowthWidth(growthStats.posts) + '%' }"></div>
          </div>
        </div>
      </div>

      <!-- Main Stats Grid -->
      <div class="stats-section">
        <h3 class="section-title"><i class="fas fa-chart-bar"></i> Overview Statistics</h3>
        <div class="stats-grid-modern">
          <div class="stat-card-modern stat-blue" @click="navigateTo('/users')">
            <div class="stat-icon-modern"><i class="fas fa-users"></i></div>
            <div class="stat-info-modern">
              <span class="stat-value-modern">{{ formatNumber(stats.totalUsers) }}</span>
              <span class="stat-label-modern">Total Users</span>
            </div>
            <div class="stat-arrow"><i class="fas fa-chevron-right"></i></div>
          </div>

          <div class="stat-card-modern stat-green" @click="navigateTo('/users?status=active')">
            <div class="stat-icon-modern"><i class="fas fa-user-check"></i></div>
            <div class="stat-info-modern">
              <span class="stat-value-modern">{{ formatNumber(stats.activeUsers) }}</span>
              <span class="stat-label-modern">Active Users</span>
            </div>
            <div class="stat-arrow"><i class="fas fa-chevron-right"></i></div>
          </div>

          <div class="stat-card-modern stat-orange" @click="navigateTo('/users?status=banned')">
            <div class="stat-icon-modern"><i class="fas fa-user-slash"></i></div>
            <div class="stat-info-modern">
              <span class="stat-value-modern">{{ formatNumber(stats.bannedUsers) }}</span>
              <span class="stat-label-modern">Banned Users</span>
            </div>
            <div class="stat-arrow"><i class="fas fa-chevron-right"></i></div>
          </div>

          <div class="stat-card-modern stat-red" @click="navigateTo('/reports')">
            <div class="stat-icon-modern"><i class="fas fa-flag"></i></div>
            <div class="stat-info-modern">
              <span class="stat-value-modern">{{ formatNumber(stats.totalReports) }}</span>
              <span class="stat-label-modern">Total Reports</span>
            </div>
            <div class="stat-arrow"><i class="fas fa-chevron-right"></i></div>
          </div>
        </div>
      </div>

      <!-- Posts Stats -->
      <div class="stats-section">
        <h3 class="section-title"><i class="fas fa-clipboard-list"></i> Service Posts</h3>
        <div class="stats-grid-modern">
          <div class="stat-card-modern stat-purple" @click="navigateTo('/service_posts')">
            <div class="stat-icon-modern"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-info-modern">
              <span class="stat-value-modern">{{ formatNumber(stats.totalPosts) }}</span>
              <span class="stat-label-modern">Total Posts</span>
            </div>
            <div class="stat-arrow"><i class="fas fa-chevron-right"></i></div>
          </div>

          <div class="stat-card-modern stat-green" @click="navigateTo('/service_posts?status=published')">
            <div class="stat-icon-modern"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info-modern">
              <span class="stat-value-modern">{{ formatNumber(stats.publishedPosts) }}</span>
              <span class="stat-label-modern">Published</span>
            </div>
            <div class="stat-arrow"><i class="fas fa-chevron-right"></i></div>
          </div>

          <div class="stat-card-modern stat-yellow" @click="navigateTo('/service_posts?status=pending')">
            <div class="stat-icon-modern"><i class="fas fa-clock"></i></div>
            <div class="stat-info-modern">
              <span class="stat-value-modern">{{ formatNumber(stats.notPublishedPosts) }}</span>
              <span class="stat-label-modern">Pending</span>
            </div>
            <div class="stat-arrow"><i class="fas fa-chevron-right"></i></div>
          </div>

          <div class="stat-card-modern stat-red" @click="navigateTo('/service_posts?status=rejected')">
            <div class="stat-icon-modern"><i class="fas fa-times-circle"></i></div>
            <div class="stat-info-modern">
              <span class="stat-value-modern">{{ formatNumber(stats.rejectedPosts) }}</span>
              <span class="stat-label-modern">Rejected</span>
            </div>
            <div class="stat-arrow"><i class="fas fa-chevron-right"></i></div>
          </div>
        </div>
      </div>

      <!-- Points & System Health Row -->
      <div class="two-column-section">
        <!-- Points Stats -->
        <div class="column-card">
          <h3 class="card-title"><i class="fas fa-coins"></i> Points System</h3>
          <div class="points-stats">
            <div class="point-item">
              <div class="point-icon bg-blue"><i class="fas fa-gem"></i></div>
              <div class="point-info">
                <span class="point-value">{{ formatNumber(stats.totalPoints) }}</span>
                <span class="point-label">Total Points</span>
              </div>
            </div>
            <div class="point-item">
              <div class="point-icon bg-orange"><i class="fas fa-shopping-cart"></i></div>
              <div class="point-info">
                <span class="point-value">{{ formatNumber(stats.pointsUsed) }}</span>
                <span class="point-label">Points Used</span>
              </div>
            </div>
            <div class="point-item">
              <div class="point-icon bg-red"><i class="fas fa-hourglass-half"></i></div>
              <div class="point-info">
                <span class="point-value">{{ stats.pendingPurchaseRequests }}</span>
                <span class="point-label">Pending Requests</span>
              </div>
            </div>
          </div>
        </div>

        <!-- System Health -->
        <div class="column-card">
          <h3 class="card-title"><i class="fas fa-heartbeat"></i> System Health</h3>
          <div class="health-metrics">
            <div class="health-item">
              <div class="health-header">
                <span>User Engagement</span>
                <span class="health-value">{{ userEngagementPercent }}%</span>
              </div>
              <div class="health-bar">
                <div class="health-bar-fill bg-green" :style="{ width: userEngagementPercent + '%' }"></div>
              </div>
            </div>
            <div class="health-item">
              <div class="health-header">
                <span>Post Approval Rate</span>
                <span class="health-value">{{ postApprovalRate }}%</span>
              </div>
              <div class="health-bar">
                <div class="health-bar-fill bg-blue" :style="{ width: postApprovalRate + '%' }"></div>
              </div>
            </div>
            <div class="health-item">
              <div class="health-header">
                <span>Points Utilization</span>
                <span class="health-value">{{ pointsUtilization }}%</span>
              </div>
              <div class="health-bar">
                <div class="health-bar-fill bg-orange" :style="{ width: pointsUtilization + '%' }"></div>
              </div>
            </div>
          </div>
          <div class="system-status">
            <span class="status-badge status-online"><i class="fas fa-circle"></i> System Online</span>
            <span class="status-info">{{ formatNumber(stats.totalPosts + stats.totalUsers) }} Total Records</span>
          </div>
        </div>
      </div>

      <!-- Activity & Top Users Row -->
      <div class="two-column-section">
        <!-- Recent Activity Timeline -->
        <div class="column-card">
          <h3 class="card-title"><i class="fas fa-stream"></i> Recent Activity</h3>
          <div class="activity-list">
            <div v-for="activity in recentActivity" :key="activity.id" class="activity-item">
              <div class="activity-dot" :class="`dot-${activity.color}`"></div>
              <div class="activity-content">
                <div class="activity-title">{{ activity.title }}</div>
                <div class="activity-desc">{{ activity.description }}</div>
              </div>
              <div class="activity-time">{{ activity.time }}</div>
            </div>
            <div v-if="recentActivity.length === 0" class="empty-state">
              <i class="fas fa-inbox"></i>
              <p>No recent activity</p>
            </div>
          </div>
        </div>

        <!-- Top Users -->
        <div class="column-card">
          <h3 class="card-title"><i class="fas fa-trophy"></i> Top Users by Posts</h3>
          <div class="top-users-list">
            <div v-for="(user, index) in topUsers" :key="user.id" class="top-user-item">
              <div class="user-rank" :class="`rank-${index + 1}`">{{ index + 1 }}</div>
              <div class="user-info">
                <div class="user-name">{{ user.user_name }}</div>
                <div class="user-email">{{ user.email }}</div>
              </div>
              <div class="user-posts">{{ user.posts_count }} posts</div>
            </div>
            <div v-if="topUsers.length === 0" class="empty-state">
              <i class="fas fa-users"></i>
              <p>No users data</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Tables Row -->
      <div class="two-column-section">
        <!-- Recent Posts Table -->
        <div class="column-card">
          <div class="card-header-row">
            <h3 class="card-title"><i class="fas fa-file-alt"></i> Latest Posts</h3>
            <router-link to="/service_posts" class="view-all-link">View All <i class="fas fa-arrow-right"></i></router-link>
          </div>
          <div class="modern-table-container">
            <table class="modern-table">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>User</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="post in recentPosts" :key="post.id">
                  <td class="cell-truncate">{{ post.title }}</td>
                  <td>{{ post.user_name }}</td>
                  <td>
                    <span class="status-pill" :class="`status-${getStatusClass(post.state)}`">
                      {{ post.state }}
                    </span>
                  </td>
                  <td class="cell-date">{{ formatShortDate(post.created_at) }}</td>
                </tr>
                <tr v-if="recentPosts.length === 0">
                  <td colspan="4" class="empty-cell">No posts found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Users Table -->
        <div class="column-card">
          <div class="card-header-row">
            <h3 class="card-title"><i class="fas fa-user-friends"></i> Recent Users</h3>
            <router-link to="/users" class="view-all-link">View All <i class="fas fa-arrow-right"></i></router-link>
          </div>
          <div class="modern-table-container">
            <table class="modern-table">
              <thead>
                <tr>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in recentUsers" :key="user.id">
                  <td>{{ user.user_name }}</td>
                  <td class="cell-truncate">{{ user.email }}</td>
                  <td>
                    <span class="status-pill" :class="`status-${user.is_active}`">
                      {{ user.is_active }}
                    </span>
                  </td>
                  <td class="cell-date">{{ formatShortDate(user.created_at) }}</td>
                </tr>
                <tr v-if="recentUsers.length === 0">
                  <td colspan="4" class="empty-cell">No users found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const loading = ref(true)

const stats = ref({
  totalUsers: 0,
  activeUsers: 0,
  bannedUsers: 0,
  totalReports: 0,
  totalPosts: 0,
  publishedPosts: 0,
  notPublishedPosts: 0,
  rejectedPosts: 0,
  totalPoints: 0,
  pointsUsed: 0,
  pendingPurchaseRequests: 0
})

const recentPosts = ref([])
const recentUsers = ref([])
const topUsers = ref([])
const recentReports = ref([])
const growthStats = ref({
  users: { current: 0, previous: 0, growth: 0 },
  posts: { current: 0, previous: 0, growth: 0 }
})

const currentDate = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

// Computed metrics
const userEngagementPercent = computed(() => {
  if (!stats.value.totalUsers) return 0
  return Math.round((stats.value.activeUsers / stats.value.totalUsers) * 100)
})

const postApprovalRate = computed(() => {
  if (!stats.value.totalPosts) return 0
  return Math.round((stats.value.publishedPosts / stats.value.totalPosts) * 100)
})

const pointsUtilization = computed(() => {
  if (!stats.value.totalPoints) return 0
  return Math.min(Math.round((stats.value.pointsUsed / stats.value.totalPoints) * 100), 100)
})

// Recent Activity
const recentActivity = computed(() => {
  const activities = []

  recentPosts.value.slice(0, 3).forEach(post => {
    activities.push({
      id: `post-${post.id}`,
      title: 'New Post Created',
      description: post.title,
      color: getStatusClass(post.state),
      time: getRelativeTime(post.created_at),
      timestamp: new Date(post.created_at)
    })
  })

  recentUsers.value.slice(0, 3).forEach(user => {
    activities.push({
      id: `user-${user.id}`,
      title: 'New User Registered',
      description: user.user_name,
      color: 'green',
      time: getRelativeTime(user.created_at),
      timestamp: new Date(user.created_at)
    })
  })

  recentReports.value.slice(0, 2).forEach(report => {
    activities.push({
      id: `report-${report.id}`,
      title: 'New Report Filed',
      description: report.reported_item,
      color: 'red',
      time: getRelativeTime(report.created_at),
      timestamp: new Date(report.created_at)
    })
  })

  return activities.sort((a, b) => b.timestamp - a.timestamp).slice(0, 6)
})

onMounted(async () => {
  await loadAllStats()
})

const loadAllStats = async () => {
  loading.value = true
  try {
    const response = await fetch('/api/admin/dashboard')
    const data = await response.json()

    Object.assign(stats.value, data.stats)
    recentPosts.value = data.recentPosts || []
    recentUsers.value = data.recentUsers || []
    topUsers.value = data.topUsers || []
    recentReports.value = data.recentReports || []
    growthStats.value = data.growthStats || {
      users: { current: 0, previous: 0, growth: 0 },
      posts: { current: 0, previous: 0, growth: 0 }
    }
  } catch (error) {
    console.error('Error loading dashboard stats:', error)
  } finally {
    loading.value = false
  }
}

const navigateTo = (path) => {
  router.push(path)
}

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num || 0)
}

const formatShortDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  })
}

const getRelativeTime = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  return formatShortDate(dateString)
}

const getStatusClass = (status) => {
  const map = {
    'published': 'green',
    'not published': 'yellow',
    'rejected': 'red',
    'archive': 'gray'
  }
  return map[status] || 'gray'
}

const calculateGrowthWidth = (data) => {
  if (!data || !data.previous) return 50
  const ratio = data.current / Math.max(data.previous, 1)
  return Math.min(Math.max(ratio * 50, 10), 100)
}
</script>

<style scoped>
.dashboard-container {
  padding: 0;
  background: #f8fafc;
  min-height: 100vh;
}

/* Header */
.dashboard-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
}

.dashboard-title {
  font-size: 2rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.dashboard-subtitle {
  margin: 0;
  opacity: 0.9;
  font-size: 1rem;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.date-badge {
  background: rgba(255,255,255,0.2);
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.refresh-btn {
  background: white;
  color: #667eea;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.refresh-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.refresh-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Loading */
.loading-state {
  text-align: center;
  padding: 4rem;
  background: white;
  border-radius: 16px;
}

.loader-modern {
  width: 50px;
  height: 50px;
  border: 4px solid #e2e8f0;
  border-top-color: #667eea;
  border-radius: 50%;
  margin: 0 auto 1rem;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Section Row */
.section-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

/* Growth Cards */
.growth-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.growth-card.growth-users {
  border-left: 4px solid #10b981;
}

.growth-card.growth-posts {
  border-left: 4px solid #6366f1;
}

.growth-content {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.75rem;
}

.growth-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.growth-users .growth-icon {
  background: #d1fae5;
  color: #10b981;
}

.growth-posts .growth-icon {
  background: #e0e7ff;
  color: #6366f1;
}

.growth-info {
  flex: 1;
}

.growth-value {
  display: block;
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
}

.growth-label {
  display: block;
  font-size: 0.875rem;
  color: #64748b;
}

.growth-trend {
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.growth-trend.trend-up {
  background: #d1fae5;
  color: #059669;
}

.growth-trend.trend-down {
  background: #fee2e2;
  color: #dc2626;
}

.growth-comparison {
  font-size: 0.8rem;
  color: #94a3b8;
  margin-bottom: 0.75rem;
}

.growth-bar {
  height: 6px;
  background: #e2e8f0;
  border-radius: 3px;
  overflow: hidden;
}

.growth-bar-fill {
  height: 100%;
  border-radius: 3px;
  transition: width 0.5s ease;
}

.growth-users .growth-bar-fill {
  background: linear-gradient(90deg, #10b981, #34d399);
}

.growth-posts .growth-bar-fill {
  background: linear-gradient(90deg, #6366f1, #818cf8);
}

/* Stats Section */
.stats-section {
  margin-bottom: 1.5rem;
}

.section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #475569;
  margin: 0 0 1rem 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.stats-grid-modern {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

.stat-card-modern {
  background: white;
  border-radius: 12px;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.stat-card-modern:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.stat-icon-modern {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.stat-blue .stat-icon-modern { background: #dbeafe; color: #2563eb; }
.stat-green .stat-icon-modern { background: #d1fae5; color: #059669; }
.stat-orange .stat-icon-modern { background: #ffedd5; color: #ea580c; }
.stat-red .stat-icon-modern { background: #fee2e2; color: #dc2626; }
.stat-purple .stat-icon-modern { background: #ede9fe; color: #7c3aed; }
.stat-yellow .stat-icon-modern { background: #fef3c7; color: #d97706; }

.stat-info-modern {
  flex: 1;
}

.stat-value-modern {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
}

.stat-label-modern {
  display: block;
  font-size: 0.8rem;
  color: #64748b;
}

.stat-arrow {
  color: #cbd5e1;
  transition: all 0.2s;
}

.stat-card-modern:hover .stat-arrow {
  color: #667eea;
  transform: translateX(3px);
}

/* Two Column Section */
.two-column-section {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.column-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.card-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 1.25rem 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.card-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.card-header-row .card-title {
  margin: 0;
}

.view-all-link {
  color: #667eea;
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.view-all-link:hover {
  text-decoration: underline;
}

/* Points Stats */
.points-stats {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.point-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 10px;
}

.point-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.point-icon.bg-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.point-icon.bg-orange { background: linear-gradient(135deg, #f97316, #ea580c); }
.point-icon.bg-red { background: linear-gradient(135deg, #ef4444, #dc2626); }

.point-info {
  flex: 1;
}

.point-value {
  display: block;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
}

.point-label {
  display: block;
  font-size: 0.8rem;
  color: #64748b;
}

/* Health Metrics */
.health-metrics {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 1rem;
}

.health-item {
  padding: 0.5rem 0;
}

.health-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.health-value {
  font-weight: 600;
  color: #1e293b;
}

.health-bar {
  height: 8px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
}

.health-bar-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.5s ease;
}

.health-bar-fill.bg-green { background: linear-gradient(90deg, #10b981, #34d399); }
.health-bar-fill.bg-blue { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
.health-bar-fill.bg-orange { background: linear-gradient(90deg, #f97316, #fb923c); }

.system-status {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}

.status-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
}

.status-badge.status-online {
  color: #059669;
}

.status-badge i {
  font-size: 0.5rem;
}

.status-info {
  font-size: 0.8rem;
  color: #64748b;
}

/* Activity List */
.activity-list {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  margin-top: 0.35rem;
  flex-shrink: 0;
}

.activity-dot.dot-green { background: #10b981; }
.activity-dot.dot-yellow { background: #f59e0b; }
.activity-dot.dot-red { background: #ef4444; }
.activity-dot.dot-gray { background: #94a3b8; }

.activity-content {
  flex: 1;
  min-width: 0;
}

.activity-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: #1e293b;
}

.activity-desc {
  font-size: 0.8rem;
  color: #64748b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.activity-time {
  font-size: 0.75rem;
  color: #94a3b8;
  white-space: nowrap;
}

/* Top Users */
.top-users-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.top-user-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 10px;
}

.user-rank {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 700;
  color: white;
}

.user-rank.rank-1 { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
.user-rank.rank-2 { background: linear-gradient(135deg, #9ca3af, #6b7280); }
.user-rank.rank-3 { background: linear-gradient(135deg, #d97706, #b45309); }
.user-rank.rank-4, .user-rank.rank-5 { background: #94a3b8; }

.user-info {
  flex: 1;
  min-width: 0;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 500;
  color: #1e293b;
}

.user-email {
  font-size: 0.75rem;
  color: #64748b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-posts {
  font-size: 0.75rem;
  font-weight: 600;
  color: #667eea;
  background: #eef2ff;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
}

/* Modern Table */
.modern-table-container {
  overflow-x: auto;
}

.modern-table {
  width: 100%;
  border-collapse: collapse;
}

.modern-table th {
  text-align: left;
  padding: 0.75rem 0.5rem;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #64748b;
  border-bottom: 2px solid #e2e8f0;
}

.modern-table td {
  padding: 0.75rem 0.5rem;
  font-size: 0.875rem;
  color: #334155;
  border-bottom: 1px solid #f1f5f9;
}

.modern-table tr:hover td {
  background: #f8fafc;
}

.cell-truncate {
  max-width: 150px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.cell-date {
  color: #94a3b8;
  font-size: 0.8rem;
}

.empty-cell {
  text-align: center;
  color: #94a3b8;
  padding: 2rem !important;
}

/* Status Pills */
.status-pill {
  display: inline-block;
  padding: 0.2rem 0.5rem;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 500;
  text-transform: capitalize;
}

.status-pill.status-green, .status-pill.status-active { background: #d1fae5; color: #059669; }
.status-pill.status-yellow { background: #fef3c7; color: #d97706; }
.status-pill.status-red, .status-pill.status-banned { background: #fee2e2; color: #dc2626; }
.status-pill.status-gray, .status-pill.status-inactive { background: #f1f5f9; color: #64748b; }

/* Empty State */
.empty-state {
  text-align: center;
  padding: 2rem;
  color: #94a3b8;
}

.empty-state i {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.empty-state p {
  margin: 0;
  font-size: 0.875rem;
}

/* Responsive */
@media (max-width: 1200px) {
  .stats-grid-modern {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .header-right {
    width: 100%;
    justify-content: space-between;
  }

  .section-row,
  .two-column-section {
    grid-template-columns: 1fr;
  }

  .stats-grid-modern {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .stats-grid-modern {
    grid-template-columns: 1fr;
  }
}
</style>
