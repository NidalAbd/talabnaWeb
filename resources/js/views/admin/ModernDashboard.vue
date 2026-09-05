<template>
  <div class="dashboard-container">
    <!-- Row 1: Dashboard Header -->
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

    <template v-if="!loading">
      <!-- Row 2: Key Metrics (4 stat cards with growth trends) -->
      <div class="stats-grid">
        <div class="stat-card-compact stat-blue" style="cursor:pointer" @click="navigateTo('/users')">
          <div class="stat-icon"><i class="fas fa-users"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(stats.totalUsers) }}</div>
            <div class="stat-label-compact">Total Users</div>
            <div
              v-if="growthStats.users?.growth !== undefined"
              class="stat-trend"
              :class="growthStats.users.growth >= 0 ? 'trend-up' : 'trend-down'"
            >
              <i :class="growthStats.users.growth >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
              {{ Math.abs(growthStats.users.growth) }}%
            </div>
          </div>
        </div>

        <div class="stat-card-compact stat-purple" style="cursor:pointer" @click="navigateTo('/service_posts')">
          <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(stats.totalPosts) }}</div>
            <div class="stat-label-compact">Total Posts</div>
            <div
              v-if="growthStats.posts?.growth !== undefined"
              class="stat-trend"
              :class="growthStats.posts.growth >= 0 ? 'trend-up' : 'trend-down'"
            >
              <i :class="growthStats.posts.growth >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
              {{ Math.abs(growthStats.posts.growth) }}%
            </div>
          </div>
        </div>

        <div class="stat-card-compact stat-cyan">
          <div class="stat-icon"><i class="fas fa-coins"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(stats.totalPoints) }}</div>
            <div class="stat-label-compact">Points Volume</div>
          </div>
        </div>

        <div class="stat-card-compact stat-red" style="cursor:pointer" @click="navigateTo('/reports')">
          <div class="stat-icon"><i class="fas fa-flag"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(stats.totalReports) }}</div>
            <div class="stat-label-compact">Active Reports</div>
          </div>
        </div>
      </div>

      <!-- Row 3: Secondary Metrics (4 stat cards) -->
      <div class="stats-grid">
        <div class="stat-card-compact stat-green" style="cursor:pointer" @click="navigateTo('/service_posts?status=published')">
          <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(stats.publishedPosts) }}</div>
            <div class="stat-label-compact">Published Posts</div>
          </div>
        </div>

        <div class="stat-card-compact stat-yellow" style="cursor:pointer" @click="navigateTo('/service_posts?status=pending')">
          <div class="stat-icon"><i class="fas fa-clock"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(stats.notPublishedPosts) }}</div>
            <div class="stat-label-compact">Pending Posts</div>
          </div>
        </div>

        <div class="stat-card-compact stat-orange">
          <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(stats.pointsUsed) }}</div>
            <div class="stat-label-compact">Points Used</div>
          </div>
        </div>

        <div class="stat-card-compact stat-red">
          <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ stats.pendingPurchaseRequests }}</div>
            <div class="stat-label-compact">Pending Purchases</div>
          </div>
        </div>
      </div>

      <!-- Reported Items - Quick Actions (sorted by report count desc) -->
      <div class="column-card" style="margin-bottom: 1.5rem;" v-if="reportedItems.length > 0">
        <div class="card-header-row">
          <h3 class="card-title"><i class="fas fa-exclamation-triangle" style="color:#ef4444"></i> Reported Items — Quick Actions</h3>
          <router-link to="/reports" class="view-all-link">All Reports <i class="fas fa-arrow-right"></i></router-link>
        </div>
        <div class="modern-table-container">
          <table class="modern-table">
            <thead>
              <tr>
                <th>Type</th>
                <th>Item</th>
                <th>Status</th>
                <th>Reports</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in reportedItems" :key="`${item.reportable_type}-${item.reportable_id}`">
                <td>
                  <span class="type-badge" :class="item.reportable_type === 'post' ? 'type-post' : 'type-user'">
                    <i :class="item.reportable_type === 'post' ? 'fas fa-file-alt' : 'fas fa-user'"></i>
                    {{ item.reportable_type }}
                  </span>
                </td>
                <td class="cell-truncate">{{ item.reportable?.title || 'Deleted' }}</td>
                <td>
                  <span class="status-pill" :class="`status-${getReportableStatusClass(item)}`">
                    {{ item.reportable?.status || 'N/A' }}
                  </span>
                </td>
                <td>
                  <span class="report-count-badge">{{ item.total_reports }}</span>
                </td>
                <td class="actions-cell">
                  <div class="quick-actions" v-if="item.reportable">
                    <!-- View item -->
                    <button
                      v-if="item.reportable_type === 'post'"
                      class="action-btn-sm action-view"
                      @click="viewPostId = item.reportable_id"
                      title="View post"
                    >
                      <i class="fas fa-eye"></i>
                    </button>
                    <a
                      v-else
                      :href="`/users/${item.reportable_id}`"
                      class="action-btn-sm action-view"
                      title="View user"
                    >
                      <i class="fas fa-eye"></i>
                    </a>
                    <!-- Post actions -->
                    <template v-if="item.reportable_type === 'post'">
                      <button
                        class="action-btn-sm action-archive"
                        @click="handleReport(item, 'suspend')"
                        :disabled="actionLoading[`${item.reportable_type}-${item.reportable_id}`]"
                        title="Archive post"
                      >
                        <i class="fas fa-archive"></i> Archive
                      </button>
                      <button
                        class="action-btn-sm action-unpublish"
                        @click="handleReport(item, 'warning')"
                        :disabled="actionLoading[`${item.reportable_type}-${item.reportable_id}`]"
                        title="Unpublish post"
                      >
                        <i class="fas fa-eye-slash"></i> Unpublish
                      </button>
                      <button
                        class="action-btn-sm action-delete"
                        @click="handleReport(item, 'delete')"
                        :disabled="actionLoading[`${item.reportable_type}-${item.reportable_id}`]"
                        title="Delete post"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </template>
                    <!-- User actions -->
                    <template v-else>
                      <button
                        class="action-btn-sm action-ban"
                        @click="handleReport(item, 'suspend')"
                        :disabled="actionLoading[`${item.reportable_type}-${item.reportable_id}`]"
                        title="Ban user"
                        v-if="item.reportable.status !== 'banned'"
                      >
                        <i class="fas fa-ban"></i> Ban
                      </button>
                      <button
                        class="action-btn-sm action-unban"
                        @click="toggleUserBan(item)"
                        :disabled="actionLoading[`${item.reportable_type}-${item.reportable_id}`]"
                        title="Unban user"
                        v-else
                      >
                        <i class="fas fa-check-circle"></i> Unban
                      </button>
                      <button
                        class="action-btn-sm action-warn"
                        @click="handleReport(item, 'warning')"
                        :disabled="actionLoading[`${item.reportable_type}-${item.reportable_id}`]"
                        title="Issue warning"
                      >
                        <i class="fas fa-exclamation-circle"></i> Warn
                      </button>
                    </template>
                    <!-- Dismiss report for both types -->
                    <button
                      class="action-btn-sm action-dismiss"
                      @click="dismissReports(item)"
                      :disabled="actionLoading[`${item.reportable_type}-${item.reportable_id}`]"
                      title="Dismiss reports"
                    >
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                  <span v-else class="text-muted">Item deleted</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Row 4: Growth Charts (2-column) -->
      <div class="charts-row">
        <div class="chart-card">
          <h4 class="chart-title"><i class="fas fa-user-plus"></i> User Registrations (12 Months)</h4>
          <div class="chart-body" style="height: 280px;">
            <LineChart :data="usersChartData" :height="280" />
          </div>
        </div>
        <div class="chart-card">
          <h4 class="chart-title"><i class="fas fa-file-alt"></i> Posts Created (12 Months)</h4>
          <div class="chart-body" style="height: 280px;">
            <LineChart :data="postsChartData" :height="280" />
          </div>
        </div>
      </div>

      <!-- Row 5: Distribution Charts (2-column) -->
      <div class="charts-row">
        <div class="chart-card">
          <h4 class="chart-title"><i class="fas fa-chart-pie"></i> Posts by State</h4>
          <div class="chart-body">
            <PieChart :data="postStateChartData" :height="250" />
          </div>
        </div>
        <div class="chart-card">
          <h4 class="chart-title"><i class="fas fa-tags"></i> Posts by Category (Top 10)</h4>
          <div class="chart-body" style="height: 280px;">
            <BarChart :data="categoryChartData" :height="280" />
          </div>
        </div>
      </div>

      <!-- Row 6: Points & Content Charts (2-column) -->
      <div class="charts-row">
        <div class="chart-card">
          <h4 class="chart-title"><i class="fas fa-exchange-alt"></i> Point Transactions (12 Months)</h4>
          <div class="chart-body" style="height: 280px;">
            <MixedChart :data="pointsChartData" :height="280" />
          </div>
        </div>
        <div class="chart-card">
          <h4 class="chart-title"><i class="fas fa-chart-pie"></i> Content Distribution</h4>
          <div class="chart-body">
            <div class="dual-pie-container">
              <div class="mini-pie-section">
                <div class="mini-pie-label">Post Types</div>
                <PieChart :data="postTypeChartData" :height="180" />
              </div>
              <div class="mini-pie-section">
                <div class="mini-pie-label">Badge Distribution</div>
                <PieChart :data="badgeChartData" :height="180" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Row 7: System Health (full-width card) -->
      <div class="column-card" style="margin-bottom: 1.5rem;">
        <h3 class="card-title"><i class="fas fa-heartbeat"></i> System Health</h3>
        <div class="health-metrics-row">
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

      <!-- Row 8: Recent Tables with Quick Actions (2-column) -->
      <div class="two-column-section">
        <!-- Recent Posts Table with Quick Actions -->
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
                  <th>Actions</th>
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
                  <td class="actions-cell">
                    <div class="quick-actions">
                      <button
                        class="action-btn-sm action-view"
                        @click="viewPostId = post.id"
                        title="View post"
                      >
                        <i class="fas fa-eye"></i>
                      </button>
                      <button
                        v-if="post.state !== 'published'"
                        class="action-btn-sm action-publish"
                        @click="changePostState(post, 'published')"
                        :disabled="actionLoading[`post-${post.id}`]"
                        title="Publish"
                      >
                        <i class="fas fa-check"></i>
                      </button>
                      <button
                        v-if="post.state === 'published'"
                        class="action-btn-sm action-unpublish"
                        @click="changePostState(post, 'not published')"
                        :disabled="actionLoading[`post-${post.id}`]"
                        title="Unpublish"
                      >
                        <i class="fas fa-eye-slash"></i>
                      </button>
                      <button
                        v-if="post.state !== 'archive'"
                        class="action-btn-sm action-archive"
                        @click="changePostState(post, 'archive')"
                        :disabled="actionLoading[`post-${post.id}`]"
                        title="Archive"
                      >
                        <i class="fas fa-archive"></i>
                      </button>
                      <button
                        class="action-btn-sm action-delete"
                        @click="deletePost(post)"
                        :disabled="actionLoading[`post-${post.id}`]"
                        title="Delete"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="recentPosts.length === 0">
                  <td colspan="4" class="empty-cell">No posts found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Users Table with Quick Actions -->
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
                  <th>Actions</th>
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
                  <td class="actions-cell">
                    <div class="quick-actions">
                      <a
                        :href="`/users/${user.id}`"
                        class="action-btn-sm action-view"
                        title="View user"
                      >
                        <i class="fas fa-eye"></i>
                      </a>
                      <button
                        v-if="user.is_active !== 'banned'"
                        class="action-btn-sm action-ban"
                        @click="toggleBan(user)"
                        :disabled="actionLoading[`user-${user.id}`]"
                        title="Ban user"
                      >
                        <i class="fas fa-ban"></i>
                      </button>
                      <button
                        v-else
                        class="action-btn-sm action-unban"
                        @click="toggleBan(user)"
                        :disabled="actionLoading[`user-${user.id}`]"
                        title="Unban user"
                      >
                        <i class="fas fa-check-circle"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="recentUsers.length === 0">
                  <td colspan="4" class="empty-cell">No users found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Row 9: Activity & Top Users (2-column) -->
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
    </template>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="toast.show" class="toast-notification" :class="`toast-${toast.type}`">
        <i :class="toast.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
        {{ toast.message }}
      </div>
    </transition>

    <!-- Post View Modal -->
    <ServicePostViewModal
      v-if="viewPostId"
      :post-id="viewPostId"
      @close="viewPostId = null"
      @edit="viewPostId = null; navigateTo('/service_posts')"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import LineChart from '../../components/admin/charts/LineChart.vue'
import PieChart from '../../components/admin/charts/PieChart.vue'
import BarChart from '../../components/admin/charts/BarChart.vue'
import MixedChart from '../../components/admin/charts/MixedChart.vue'
import ServicePostViewModal from '../../components/admin/posts/ServicePostViewModal.vue'
import { useTopProgress } from '../../composables/useTopProgress'

const router = useRouter()
const loading = ref(true)
const actionLoading = reactive({})
const viewPostId = ref(null)
const topProgress = useTopProgress()

const toast = reactive({ show: false, message: '', type: 'success' })
const showToast = (message, type = 'success') => {
  toast.show = true
  toast.message = message
  toast.type = type
  setTimeout(() => { toast.show = false }, 3000)
}

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
const reportedItems = ref([])
const growthStats = ref({
  users: { current: 0, previous: 0, growth: 0 },
  posts: { current: 0, previous: 0, growth: 0 }
})

// Chart data refs
const postsByMonth = ref({ labels: [], data: [] })
const usersByMonth = ref({ labels: [], data: [] })
const postsByCategory = ref({ labels: [], data: [] })
const pointTransactions = ref({ labels: [], counts: [], points: [] })
const postsByState = ref({ labels: [], data: [], backgroundColor: [] })
const normalPosts = ref(0)
const goldenPosts = ref(0)
const diamondPosts = ref(0)
const offerPosts = ref(0)
const requestPosts = ref(0)

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

// Chart.js computed properties
const usersChartData = computed(() => ({
  labels: usersByMonth.value.labels,
  datasets: [{
    label: 'User Registrations',
    data: usersByMonth.value.data,
    borderColor: '#3b82f6',
    backgroundColor: 'rgba(59, 130, 246, 0.1)',
    fill: true,
    tension: 0.4
  }]
}))

const postsChartData = computed(() => ({
  labels: postsByMonth.value.labels,
  datasets: [{
    label: 'Posts Created',
    data: postsByMonth.value.data,
    borderColor: '#8b5cf6',
    backgroundColor: 'rgba(139, 92, 246, 0.1)',
    fill: true,
    tension: 0.4
  }]
}))

const postStateChartData = computed(() => ({
  labels: postsByState.value.labels,
  datasets: [{
    data: postsByState.value.data,
    backgroundColor: postsByState.value.backgroundColor
  }]
}))

const categoryChartData = computed(() => ({
  labels: postsByCategory.value.labels,
  datasets: [{
    label: 'Posts',
    data: postsByCategory.value.data,
    backgroundColor: [
      '#3b82f6', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b',
      '#ef4444', '#ec4899', '#6366f1', '#14b8a6', '#f97316'
    ]
  }]
}))

const pointsChartData = computed(() => ({
  labels: pointTransactions.value.labels,
  datasets: [
    {
      type: 'bar',
      label: 'Transaction Count',
      data: pointTransactions.value.counts,
      backgroundColor: 'rgba(59, 130, 246, 0.6)',
      yAxisID: 'y'
    },
    {
      type: 'line',
      label: 'Points Volume',
      data: pointTransactions.value.points,
      borderColor: '#f59e0b',
      backgroundColor: 'rgba(245, 158, 11, 0.1)',
      fill: true,
      tension: 0.4,
      yAxisID: 'y1'
    }
  ]
}))

const postTypeChartData = computed(() => ({
  labels: ['Offers', 'Requests'],
  datasets: [{
    data: [offerPosts.value, requestPosts.value],
    backgroundColor: ['#3b82f6', '#f59e0b']
  }]
}))

const badgeChartData = computed(() => ({
  labels: ['Normal', 'Golden', 'Diamond'],
  datasets: [{
    data: [normalPosts.value, goldenPosts.value, diamondPosts.value],
    backgroundColor: ['#6b7280', '#f59e0b', '#06b6d4']
  }]
}))

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
  topProgress.start()
  try {
    const [dashRes, reportsRes] = await Promise.all([
      fetch('/api/admin/dashboard'),
      fetch('/api/admin/reports?sort_by=total&sort_direction=desc&per_page=10')
    ])

    const data = await dashRes.json()

    Object.assign(stats.value, data.stats)
    recentPosts.value = data.recentPosts || []
    recentUsers.value = data.recentUsers || []
    topUsers.value = data.topUsers || []
    recentReports.value = data.recentReports || []
    growthStats.value = data.growthStats || {
      users: { current: 0, previous: 0, growth: 0 },
      posts: { current: 0, previous: 0, growth: 0 }
    }

    // Chart data
    postsByMonth.value = data.postsByMonth || { labels: [], data: [] }
    usersByMonth.value = data.usersByMonth || { labels: [], data: [] }
    postsByCategory.value = data.postsByCategory || { labels: [], data: [] }
    pointTransactions.value = data.pointTransactions || { labels: [], counts: [], points: [] }
    postsByState.value = data.postsByState || { labels: [], data: [], backgroundColor: [] }

    // Badge & type counts
    normalPosts.value = data.normalPosts || 0
    goldenPosts.value = data.goldenPosts || 0
    diamondPosts.value = data.diamondPosts || 0
    offerPosts.value = data.offerPosts || 0
    requestPosts.value = data.requestPosts || 0

    // Reported items
    if (reportsRes.ok) {
      const reportsData = await reportsRes.json()
      reportedItems.value = reportsData.reports?.data || []
    }
  } catch (error) {
    console.error('Error loading dashboard stats:', error)
  } finally {
    loading.value = false
    topProgress.finish()
  }
}

// === Quick Actions ===

// Handle report action (suspend/warning/delete) via reports API
const handleReport = async (item, action) => {
  const key = `${item.reportable_type}-${item.reportable_id}`
  actionLoading[key] = true
  try {
    const res = await fetch(`/api/admin/reports/handle/${item.reportable_type}/${item.reportable_id}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
      body: JSON.stringify({ action, reason: 'Action taken from dashboard' })
    })
    const result = await res.json()
    if (res.ok) {
      showToast(result.message || 'Action completed')
      // Remove from reported items list
      reportedItems.value = reportedItems.value.filter(
        r => !(r.reportable_type === item.reportable_type && r.reportable_id === item.reportable_id)
      )
      // Refresh stats in background
      refreshStats()
    } else {
      showToast(result.error || 'Action failed', 'error')
    }
  } catch (e) {
    showToast('Network error', 'error')
  } finally {
    actionLoading[key] = false
  }
}

// Dismiss all reports for an item
const dismissReports = async (item) => {
  const key = `${item.reportable_type}-${item.reportable_id}`
  actionLoading[key] = true
  try {
    // Fetch the individual reports for this item, then delete them
    const type = item.reportable_type
    const id = item.reportable_id
    const res = await fetch(`/api/admin/reports/${type}/${id}`)
    if (res.ok) {
      const data = await res.json()
      const reports = data.reports?.data || []
      // Delete each report
      await Promise.all(reports.map(r =>
        fetch(`/api/admin/reports/${r.id}`, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': getCsrfToken() }
        })
      ))
      showToast(`${reports.length} report(s) dismissed`)
      reportedItems.value = reportedItems.value.filter(
        r => !(r.reportable_type === type && r.reportable_id === id)
      )
      refreshStats()
    }
  } catch (e) {
    showToast('Failed to dismiss reports', 'error')
  } finally {
    actionLoading[key] = false
  }
}

// Toggle user ban from users table
const toggleBan = async (user) => {
  const key = `user-${user.id}`
  actionLoading[key] = true
  try {
    const res = await fetch(`/api/admin/users/${user.id}/toggle-ban`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() }
    })
    const result = await res.json()
    if (res.ok) {
      user.is_active = result.status
      showToast(result.message)
    } else {
      showToast(result.error || 'Action failed', 'error')
    }
  } catch (e) {
    showToast('Network error', 'error')
  } finally {
    actionLoading[key] = false
  }
}

// Toggle user ban from reported items (uses same endpoint)
const toggleUserBan = async (item) => {
  const key = `${item.reportable_type}-${item.reportable_id}`
  actionLoading[key] = true
  try {
    const res = await fetch(`/api/admin/users/${item.reportable_id}/toggle-ban`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() }
    })
    const result = await res.json()
    if (res.ok) {
      if (item.reportable) item.reportable.status = result.status
      showToast(result.message)
    } else {
      showToast(result.error || 'Action failed', 'error')
    }
  } catch (e) {
    showToast('Network error', 'error')
  } finally {
    actionLoading[key] = false
  }
}

// Change post state via reports handle endpoint (reuses suspend/warning actions)
const changePostState = async (post, newState) => {
  const key = `post-${post.id}`
  actionLoading[key] = true
  try {
    // Map desired state to report action
    const actionMap = { 'archive': 'suspend', 'not published': 'warning', 'published': 'publish' }
    const action = actionMap[newState]

    if (action === 'publish') {
      // No direct report action for publishing — use service posts update
      // We'll call the general update endpoint via api.php
      const res = await fetch(`/api/service_posts`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
        body: JSON.stringify({ id: post.id, state: 'published' })
      })
      if (res.ok) {
        post.state = 'published'
        showToast('Post published')
      } else {
        showToast('Failed to publish', 'error')
      }
    } else {
      const res = await fetch(`/api/admin/reports/handle/post/${post.id}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
        body: JSON.stringify({ action, reason: 'State changed from dashboard' })
      })
      const result = await res.json()
      if (res.ok) {
        post.state = newState
        showToast(result.message || `Post ${newState}`)
      } else {
        showToast(result.error || 'Action failed', 'error')
      }
    }
  } catch (e) {
    showToast('Network error', 'error')
  } finally {
    actionLoading[key] = false
  }
}

// Delete post
const deletePost = async (post) => {
  const key = `post-${post.id}`
  actionLoading[key] = true
  try {
    const res = await fetch(`/api/admin/service-posts/${post.id}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': getCsrfToken() }
    })
    if (res.ok) {
      recentPosts.value = recentPosts.value.filter(p => p.id !== post.id)
      showToast('Post deleted')
      refreshStats()
    } else {
      const result = await res.json()
      showToast(result.error || 'Delete failed', 'error')
    }
  } catch (e) {
    showToast('Network error', 'error')
  } finally {
    actionLoading[key] = false
  }
}

// Refresh just the stats (lightweight, no loading spinner)
const refreshStats = async () => {
  try {
    const res = await fetch('/api/admin/dashboard')
    const data = await res.json()
    Object.assign(stats.value, data.stats)
  } catch (e) {
    // silent
  }
}

const getCsrfToken = () => {
  return document.querySelector('meta[name="csrf-token"]')?.content || ''
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

const getReportableStatusClass = (item) => {
  if (!item.reportable) return 'gray'
  const status = item.reportable.status
  if (item.reportable_type === 'user') {
    return status === 'active' ? 'active' : status === 'banned' ? 'banned' : 'inactive'
  }
  return getStatusClass(status)
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

/* Stats grid spacing */
.stats-grid {
  margin-bottom: 1.25rem;
}

/* Dual Pie Container */
.dual-pie-container {
  display: flex;
  gap: 1rem;
  justify-content: center;
  align-items: flex-start;
}

.mini-pie-section {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.mini-pie-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.5rem;
}

/* Health Metrics Row - 3 column grid */
.health-metrics-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  margin-bottom: 1rem;
}

/* Chart title icon spacing */
.chart-title i {
  margin-right: 0.5rem;
  color: #667eea;
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

/* Health Metrics */
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

/* Type Badge */
.type-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
}

.type-badge.type-post {
  background: #ede9fe;
  color: #7c3aed;
}

.type-badge.type-user {
  background: #dbeafe;
  color: #2563eb;
}

/* Report Count Badge */
.report-count-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #fee2e2;
  color: #dc2626;
  font-size: 0.8rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 10px;
  min-width: 28px;
}

/* Quick Action Buttons */
.actions-cell {
  white-space: nowrap;
}

.quick-actions {
  display: flex;
  gap: 0.35rem;
  align-items: center;
}

.action-btn-sm {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.3rem 0.5rem;
  border: none;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;
}

.action-btn-sm:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.action-btn-sm:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.action-view {
  background: #e0f2fe;
  color: #0284c7;
  text-decoration: none;
}

.action-publish {
  background: #d1fae5;
  color: #059669;
}

.action-unpublish {
  background: #fef3c7;
  color: #d97706;
}

.action-archive {
  background: #f1f5f9;
  color: #64748b;
}

.action-delete {
  background: #fee2e2;
  color: #dc2626;
}

.action-ban {
  background: #fee2e2;
  color: #dc2626;
}

.action-unban {
  background: #d1fae5;
  color: #059669;
}

.action-warn {
  background: #fef3c7;
  color: #d97706;
}

.action-dismiss {
  background: #f1f5f9;
  color: #94a3b8;
}

.action-dismiss:hover:not(:disabled) {
  background: #e2e8f0;
  color: #64748b;
}

.text-muted {
  color: #94a3b8;
  font-size: 0.8rem;
}

/* Toast Notification */
.toast-notification {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  padding: 0.75rem 1.25rem;
  border-radius: 10px;
  font-size: 0.875rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  z-index: 9999;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}

.toast-success {
  background: #059669;
  color: white;
}

.toast-error {
  background: #dc2626;
  color: white;
}

.toast-enter-active {
  animation: toastIn 0.3s ease;
}

.toast-leave-active {
  animation: toastOut 0.3s ease;
}

@keyframes toastIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes toastOut {
  from { opacity: 1; transform: translateY(0); }
  to { opacity: 0; transform: translateY(20px); }
}

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
  .health-metrics-row {
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

  .two-column-section {
    grid-template-columns: 1fr;
  }

  .health-metrics-row {
    grid-template-columns: 1fr;
  }

  .dual-pie-container {
    flex-direction: column;
    align-items: center;
  }

  .mini-pie-section {
    width: 100%;
  }

  .quick-actions {
    flex-wrap: wrap;
  }
}
</style>
