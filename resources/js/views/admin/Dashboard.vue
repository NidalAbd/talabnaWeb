<template>
  <div class="dashboard-container">
    <!-- Page Header with Date -->
    <v-row class="mb-4 align-center">
      <v-col cols="12" md="8">
        <h1 class="text-h4 font-weight-bold">Dashboard</h1>
        <p class="text-subtitle-1 text-medium-emphasis">
          Welcome to your admin dashboard
          <v-chip size="small" color="primary" variant="tonal" class="ml-2">
            <v-icon start size="small">mdi-calendar</v-icon>
            {{ currentDate }}
          </v-chip>
        </p>
      </v-col>
      <v-col cols="12" md="4" class="text-md-right">
        <v-btn color="primary" variant="tonal" @click="loadDashboardData" :loading="loading">
          <v-icon start>mdi-refresh</v-icon>
          Refresh Data
        </v-btn>
      </v-col>
    </v-row>

    <!-- Loading State -->
    <v-row v-if="loading">
      <v-col v-for="i in 8" :key="i" cols="12" sm="6" md="3">
        <v-skeleton-loader type="card" />
      </v-col>
    </v-row>

    <!-- Stats Cards -->
    <template v-else>
      <!-- Growth Summary Cards -->
      <v-row class="mb-4">
        <v-col cols="12" md="6">
          <v-card class="growth-card">
            <v-card-text>
              <v-row align="center">
                <v-col cols="auto">
                  <v-avatar color="success" size="64">
                    <v-icon size="32" color="white">mdi-account-group</v-icon>
                  </v-avatar>
                </v-col>
                <v-col>
                  <div class="text-h5 font-weight-bold">{{ formatNumber(growthStats.users?.current || 0) }} New Users</div>
                  <div class="text-subtitle-2 text-medium-emphasis">This month</div>
                </v-col>
                <v-col cols="auto">
                  <v-chip
                    :color="(growthStats.users?.growth || 0) >= 0 ? 'success' : 'error'"
                    variant="flat"
                    size="large"
                  >
                    <v-icon start size="small">
                      {{ (growthStats.users?.growth || 0) >= 0 ? 'mdi-trending-up' : 'mdi-trending-down' }}
                    </v-icon>
                    {{ Math.abs(growthStats.users?.growth || 0) }}%
                  </v-chip>
                </v-col>
              </v-row>
              <v-progress-linear
                :model-value="calculateProgressValue(growthStats.users)"
                color="success"
                class="mt-3"
                height="6"
                rounded
              />
              <div class="text-caption text-medium-emphasis mt-1">
                vs {{ growthStats.users?.previous || 0 }} last month
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card class="growth-card">
            <v-card-text>
              <v-row align="center">
                <v-col cols="auto">
                  <v-avatar color="primary" size="64">
                    <v-icon size="32" color="white">mdi-clipboard-text</v-icon>
                  </v-avatar>
                </v-col>
                <v-col>
                  <div class="text-h5 font-weight-bold">{{ formatNumber(growthStats.posts?.current || 0) }} New Posts</div>
                  <div class="text-subtitle-2 text-medium-emphasis">This month</div>
                </v-col>
                <v-col cols="auto">
                  <v-chip
                    :color="(growthStats.posts?.growth || 0) >= 0 ? 'success' : 'error'"
                    variant="flat"
                    size="large"
                  >
                    <v-icon start size="small">
                      {{ (growthStats.posts?.growth || 0) >= 0 ? 'mdi-trending-up' : 'mdi-trending-down' }}
                    </v-icon>
                    {{ Math.abs(growthStats.posts?.growth || 0) }}%
                  </v-chip>
                </v-col>
              </v-row>
              <v-progress-linear
                :model-value="calculateProgressValue(growthStats.posts)"
                color="primary"
                class="mt-3"
                height="6"
                rounded
              />
              <div class="text-caption text-medium-emphasis mt-1">
                vs {{ growthStats.posts?.previous || 0 }} last month
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- User Stats -->
      <v-row class="mb-4">
        <v-col cols="12" sm="6" md="3">
          <stats-card
            title="Total Users"
            :value="stats.totalUsers"
            icon="mdi-account-group"
            color="info"
            :to="{ name: 'admin-users' }"
          />
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <stats-card
            title="Active Users"
            :value="stats.activeUsers"
            icon="mdi-account-check"
            color="success"
            :to="{ name: 'admin-users', query: { status: 'active' } }"
          />
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <stats-card
            title="Banned Users"
            :value="stats.bannedUsers"
            icon="mdi-account-cancel"
            color="warning"
            :to="{ name: 'admin-users', query: { status: 'banned' } }"
          />
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <stats-card
            title="Total Reports"
            :value="stats.totalReports"
            icon="mdi-flag"
            color="error"
            :to="{ name: 'admin-reports' }"
          />
        </v-col>
      </v-row>

      <!-- Service Posts Stats -->
      <v-row class="mb-4">
        <v-col cols="12" sm="6" md="3">
          <stats-card
            title="Total Posts"
            :value="stats.totalPosts"
            icon="mdi-clipboard-text"
            color="primary"
            :to="{ name: 'admin-service-posts' }"
          />
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <stats-card
            title="Published"
            :value="stats.publishedPosts"
            icon="mdi-check-circle"
            color="success"
            :to="{ name: 'admin-service-posts', query: { status: 'published' } }"
          />
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <stats-card
            title="Pending"
            :value="stats.notPublishedPosts"
            icon="mdi-clock-outline"
            color="warning"
            :to="{ name: 'admin-service-posts', query: { status: 'not published' } }"
          />
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <stats-card
            title="Rejected"
            :value="stats.rejectedPosts"
            icon="mdi-close-circle"
            color="error"
            :to="{ name: 'admin-service-posts', query: { status: 'rejected' } }"
          />
        </v-col>
      </v-row>

      <!-- Points System Stats -->
      <v-row class="mb-4">
        <v-col cols="12" md="4">
          <stats-card
            title="Total Points"
            :value="formatNumber(stats.totalPoints)"
            icon="mdi-diamond-stone"
            color="info"
            :to="{ name: 'admin-points' }"
          />
        </v-col>

        <v-col cols="12" md="4">
          <stats-card
            title="Points Used"
            :value="formatNumber(stats.pointsUsed)"
            icon="mdi-cash-multiple"
            color="warning"
            :to="{ name: 'admin-points', query: { type: 'used' } }"
          />
        </v-col>

        <v-col cols="12" md="4">
          <stats-card
            title="Pending Requests"
            :value="stats.pendingPurchaseRequests"
            icon="mdi-cart-outline"
            color="error"
            :to="{ name: 'admin-points' }"
          />
        </v-col>
      </v-row>

      <!-- Activity Timeline & System Health -->
      <v-row class="mb-4">
        <v-col cols="12" md="8">
          <v-card class="h-100">
            <v-card-title class="d-flex align-center">
              <v-icon start color="primary">mdi-timeline-clock</v-icon>
              Recent Activity
            </v-card-title>
            <v-card-text class="pa-0">
              <v-timeline side="end" density="compact" class="activity-timeline">
                <v-timeline-item
                  v-for="activity in recentActivity"
                  :key="activity.id"
                  :dot-color="activity.color"
                  size="small"
                >
                  <template #icon>
                    <v-icon size="small" color="white">{{ activity.icon }}</v-icon>
                  </template>
                  <div class="d-flex justify-space-between align-center">
                    <div>
                      <div class="font-weight-medium">{{ activity.title }}</div>
                      <div class="text-caption text-medium-emphasis">{{ activity.description }}</div>
                    </div>
                    <v-chip size="x-small" variant="tonal" color="grey">
                      {{ activity.time }}
                    </v-chip>
                  </div>
                </v-timeline-item>
                <v-timeline-item v-if="recentActivity.length === 0" dot-color="grey" size="small">
                  <div class="text-medium-emphasis">No recent activity</div>
                </v-timeline-item>
              </v-timeline>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card class="h-100">
            <v-card-title class="d-flex align-center">
              <v-icon start color="success">mdi-pulse</v-icon>
              System Overview
            </v-card-title>
            <v-card-text>
              <div class="system-stat mb-4">
                <div class="d-flex justify-space-between align-center mb-2">
                  <span class="text-body-2">User Engagement</span>
                  <span class="font-weight-bold">{{ userEngagementPercent }}%</span>
                </div>
                <v-progress-linear
                  :model-value="userEngagementPercent"
                  color="success"
                  height="8"
                  rounded
                />
              </div>

              <div class="system-stat mb-4">
                <div class="d-flex justify-space-between align-center mb-2">
                  <span class="text-body-2">Post Approval Rate</span>
                  <span class="font-weight-bold">{{ postApprovalRate }}%</span>
                </div>
                <v-progress-linear
                  :model-value="postApprovalRate"
                  color="primary"
                  height="8"
                  rounded
                />
              </div>

              <div class="system-stat mb-4">
                <div class="d-flex justify-space-between align-center mb-2">
                  <span class="text-body-2">Points Utilization</span>
                  <span class="font-weight-bold">{{ pointsUtilization }}%</span>
                </div>
                <v-progress-linear
                  :model-value="pointsUtilization"
                  color="warning"
                  height="8"
                  rounded
                />
              </div>

              <v-divider class="my-4" />

              <div class="d-flex flex-wrap ga-2">
                <v-chip color="success" variant="tonal" size="small">
                  <v-icon start size="small">mdi-check-circle</v-icon>
                  System Active
                </v-chip>
                <v-chip color="info" variant="tonal" size="small">
                  <v-icon start size="small">mdi-database</v-icon>
                  {{ formatNumber(stats.totalPosts) }} Records
                </v-chip>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Charts Row 1: Distribution Charts -->
      <v-row class="mb-4">
        <v-col cols="12" md="4">
          <v-card class="h-100">
            <v-card-title class="d-flex align-center">
              <v-icon start color="amber">mdi-certificate</v-icon>
              Badge Distribution
            </v-card-title>
            <v-card-text>
              <pie-chart
                v-if="badgeChartData"
                :data="badgeChartData"
                :height="220"
              />
              <div v-else class="text-center py-8 text-medium-emphasis">
                No data available
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card class="h-100">
            <v-card-title class="d-flex align-center">
              <v-icon start color="green">mdi-swap-horizontal</v-icon>
              Post Type Distribution
            </v-card-title>
            <v-card-text>
              <pie-chart
                v-if="postTypeChartData"
                :data="postTypeChartData"
                :height="220"
              />
              <div v-else class="text-center py-8 text-medium-emphasis">
                No data available
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card class="h-100">
            <v-card-title class="d-flex align-center">
              <v-icon start color="blue">mdi-check-decagram</v-icon>
              Post Status Distribution
            </v-card-title>
            <v-card-text>
              <pie-chart
                v-if="postStatusChartData"
                :data="postStatusChartData"
                :height="220"
              />
              <div v-else class="text-center py-8 text-medium-emphasis">
                No data available
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Charts Row 2: Time Series -->
      <v-row class="mb-4">
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon start color="indigo">mdi-chart-timeline-variant</v-icon>
              Posts by Month
            </v-card-title>
            <v-card-text>
              <line-chart
                v-if="postsByMonthData"
                :data="postsByMonthData"
                :height="250"
              />
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon start color="teal">mdi-account-clock</v-icon>
              Users by Month
            </v-card-title>
            <v-card-text>
              <line-chart
                v-if="usersByMonthData"
                :data="usersByMonthData"
                :height="250"
              />
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Charts Row 3 -->
      <v-row class="mb-4">
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon start color="purple">mdi-shape</v-icon>
              Posts by Category
            </v-card-title>
            <v-card-text>
              <bar-chart
                v-if="postsByCategoryData"
                :data="postsByCategoryData"
                :height="250"
              />
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon start color="orange">mdi-chart-areaspline</v-icon>
              Point Transactions
            </v-card-title>
            <v-card-text>
              <mixed-chart
                v-if="pointTransactionsData"
                :data="pointTransactionsData"
                :height="250"
              />
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Top Users & Recent Reports -->
      <v-row class="mb-4">
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon start color="amber">mdi-trophy</v-icon>
              Top Users by Posts
              <v-spacer />
              <v-chip size="small" color="amber" variant="tonal">Top 5</v-chip>
            </v-card-title>
            <v-card-text class="pa-0">
              <v-list>
                <v-list-item
                  v-for="(user, index) in topUsers"
                  :key="user.id"
                  :to="{ name: 'admin-users', query: { search: user.user_name } }"
                >
                  <template #prepend>
                    <v-avatar
                      :color="getRankColor(index)"
                      size="36"
                      class="font-weight-bold"
                    >
                      {{ index + 1 }}
                    </v-avatar>
                  </template>
                  <v-list-item-title class="font-weight-medium">
                    {{ user.user_name }}
                  </v-list-item-title>
                  <v-list-item-subtitle>
                    {{ user.email }}
                  </v-list-item-subtitle>
                  <template #append>
                    <v-chip size="small" color="primary" variant="tonal">
                      {{ user.posts_count }} posts
                    </v-chip>
                  </template>
                </v-list-item>
                <v-list-item v-if="topUsers.length === 0">
                  <v-list-item-title class="text-medium-emphasis text-center">
                    No data available
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon start color="error">mdi-flag-variant</v-icon>
              Recent Reports
              <v-spacer />
              <v-btn
                size="small"
                variant="text"
                color="primary"
                :to="{ name: 'admin-reports' }"
              >
                View All
              </v-btn>
            </v-card-title>
            <v-card-text class="pa-0">
              <v-list>
                <v-list-item
                  v-for="report in recentReports"
                  :key="report.id"
                >
                  <template #prepend>
                    <v-avatar color="error" size="36">
                      <v-icon color="white" size="small">mdi-flag</v-icon>
                    </v-avatar>
                  </template>
                  <v-list-item-title class="font-weight-medium">
                    {{ report.reported_item }}
                  </v-list-item-title>
                  <v-list-item-subtitle>
                    Reported by: {{ report.reporter_name }}
                  </v-list-item-subtitle>
                  <template #append>
                    <div class="text-right">
                      <div class="text-caption text-medium-emphasis">
                        {{ formatDate(report.created_at) }}
                      </div>
                    </div>
                  </template>
                </v-list-item>
                <v-list-item v-if="recentReports.length === 0">
                  <v-list-item-title class="text-medium-emphasis text-center">
                    No recent reports
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Tables Row -->
      <v-row class="mb-4">
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon start color="primary">mdi-clipboard-text-clock</v-icon>
              Latest Service Posts
            </v-card-title>
            <v-card-text class="pa-0">
              <v-table density="comfortable">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="post in recentPosts" :key="post.id">
                    <td>
                      <v-chip size="x-small" color="grey" variant="tonal">
                        #{{ post.id }}
                      </v-chip>
                    </td>
                    <td class="text-truncate" style="max-width: 150px;">{{ post.title }}</td>
                    <td>{{ post.user_name }}</td>
                    <td>
                      <v-chip
                        :color="getStatusColor(post.state)"
                        size="small"
                        variant="flat"
                      >
                        {{ post.state }}
                      </v-chip>
                    </td>
                    <td class="text-caption">{{ formatDate(post.created_at) }}</td>
                  </tr>
                  <tr v-if="recentPosts.length === 0">
                    <td colspan="5" class="text-center text-medium-emphasis py-4">
                      No posts found
                    </td>
                  </tr>
                </tbody>
              </v-table>
            </v-card-text>
            <v-card-actions>
              <v-spacer />
              <v-btn
                color="primary"
                variant="text"
                :to="{ name: 'admin-service-posts' }"
              >
                View All
                <v-icon end>mdi-arrow-right</v-icon>
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon start color="success">mdi-account-clock</v-icon>
              Recent Users
            </v-card-title>
            <v-card-text class="pa-0">
              <v-table density="comfortable">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="user in recentUsers" :key="user.id">
                    <td>
                      <v-chip size="x-small" color="grey" variant="tonal">
                        #{{ user.id }}
                      </v-chip>
                    </td>
                    <td>{{ user.user_name }}</td>
                    <td class="text-truncate" style="max-width: 150px;">{{ user.email }}</td>
                    <td>
                      <v-chip
                        :color="getUserStatusColor(user.is_active)"
                        size="small"
                        variant="flat"
                      >
                        {{ user.is_active }}
                      </v-chip>
                    </td>
                    <td class="text-caption">{{ formatDate(user.created_at) }}</td>
                  </tr>
                  <tr v-if="recentUsers.length === 0">
                    <td colspan="5" class="text-center text-medium-emphasis py-4">
                      No users found
                    </td>
                  </tr>
                </tbody>
              </v-table>
            </v-card-text>
            <v-card-actions>
              <v-spacer />
              <v-btn
                color="primary"
                variant="text"
                :to="{ name: 'admin-users' }"
              >
                View All
                <v-icon end>mdi-arrow-right</v-icon>
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import StatsCard from '@/components/admin/StatsCard.vue'
import PieChart from '@/components/admin/charts/PieChart.vue'
import LineChart from '@/components/admin/charts/LineChart.vue'
import BarChart from '@/components/admin/charts/BarChart.vue'
import MixedChart from '@/components/admin/charts/MixedChart.vue'

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

// Chart data
const badgeChartData = ref(null)
const postTypeChartData = ref(null)
const postStatusChartData = ref(null)
const postsByMonthData = ref(null)
const usersByMonthData = ref(null)
const postsByCategoryData = ref(null)
const pointTransactionsData = ref(null)

const currentDate = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

// Computed metrics for System Overview
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

// Recent Activity - combines posts and users into timeline
const recentActivity = computed(() => {
  const activities = []

  // Add recent posts to activity
  recentPosts.value.slice(0, 3).forEach(post => {
    activities.push({
      id: `post-${post.id}`,
      title: 'New Post Created',
      description: post.title,
      icon: 'mdi-clipboard-text',
      color: getStatusColor(post.state),
      time: getRelativeTime(post.created_at),
      timestamp: new Date(post.created_at)
    })
  })

  // Add recent users to activity
  recentUsers.value.slice(0, 3).forEach(user => {
    activities.push({
      id: `user-${user.id}`,
      title: 'New User Registered',
      description: user.user_name,
      icon: 'mdi-account-plus',
      color: 'success',
      time: getRelativeTime(user.created_at),
      timestamp: new Date(user.created_at)
    })
  })

  // Add recent reports to activity
  recentReports.value.slice(0, 2).forEach(report => {
    activities.push({
      id: `report-${report.id}`,
      title: 'New Report Filed',
      description: report.reported_item,
      icon: 'mdi-flag',
      color: 'error',
      time: getRelativeTime(report.created_at),
      timestamp: new Date(report.created_at)
    })
  })

  // Sort by timestamp and take latest 6
  return activities
    .sort((a, b) => b.timestamp - a.timestamp)
    .slice(0, 6)
})

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
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

onMounted(async () => {
  await loadDashboardData()
})

const loadDashboardData = async () => {
  try {
    loading.value = true
    const response = await fetch('/api/admin/dashboard')
    const data = await response.json()

    // Update stats
    Object.assign(stats.value, data.stats)

    // Update tables
    recentPosts.value = data.recentPosts || []
    recentUsers.value = data.recentUsers || []
    topUsers.value = data.topUsers || []
    recentReports.value = data.recentReports || []
    growthStats.value = data.growthStats || {
      users: { current: 0, previous: 0, growth: 0 },
      posts: { current: 0, previous: 0, growth: 0 }
    }

    // Update charts
    badgeChartData.value = {
      labels: ['Normal', 'Golden', 'Diamond'],
      datasets: [{
        data: [data.normalPosts || 0, data.goldenPosts || 0, data.diamondPosts || 0],
        backgroundColor: ['#6c757d', '#ffc107', '#17a2b8']
      }]
    };

    postTypeChartData.value = {
      labels: ['Offers', 'Requests'],
      datasets: [{
        data: [data.offerPosts || 0, data.requestPosts || 0],
        backgroundColor: ['#10B981', '#EF4444']
      }]
    };

    // Post status distribution chart
    if (data.postsByState) {
      postStatusChartData.value = {
        labels: data.postsByState.labels || [],
        datasets: [{
          data: data.postsByState.data || [],
          backgroundColor: data.postsByState.backgroundColor || ['#10B981', '#F59E0B', '#EF4444', '#6B7280']
        }]
      };
    }

    postsByMonthData.value = {
      labels: data.postsByMonth?.labels || [],
      datasets: [{
        label: 'Posts',
        data: data.postsByMonth?.data || [],
        borderColor: '#6366F1',
        backgroundColor: 'rgba(99, 102, 241, 0.1)',
        tension: 0.4,
        fill: true
      }]
    };

    usersByMonthData.value = {
      labels: data.usersByMonth?.labels || [],
      datasets: [{
        label: 'New Users',
        data: data.usersByMonth?.data || [],
        borderColor: '#10B981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        tension: 0.4,
        fill: true
      }]
    };

    postsByCategoryData.value = {
      labels: data.postsByCategory?.labels || [],
      datasets: [{
        label: 'Posts Count',
        data: data.postsByCategory?.data || [],
        backgroundColor: 'rgba(99, 102, 241, 0.7)',
        borderColor: 'rgba(99, 102, 241, 1)',
        borderWidth: 1
      }]
    };

    pointTransactionsData.value = {
      labels: data.pointTransactions?.labels || [],
      datasets: [
        {
          label: 'Transactions Count',
          data: data.pointTransactions?.counts || [],
          backgroundColor: 'rgba(99, 102, 241, 0.7)',
          borderColor: 'rgba(99, 102, 241, 1)',
          borderWidth: 1,
          type: 'bar',
          yAxisID: 'y'
        },
        {
          label: 'Points Volume',
          data: data.pointTransactions?.points || [],
          type: 'line',
          borderColor: '#F59E0B',
          backgroundColor: 'transparent',
          borderWidth: 2,
          pointBackgroundColor: '#F59E0B',
          yAxisID: 'y1'
        }
      ]
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

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const getStatusColor = (status) => {
  const colors = {
    published: 'success',
    'not published': 'warning',
    archive: 'grey',
    rejected: 'error'
  }
  return colors[status] || 'grey'
}

const getUserStatusColor = (status) => {
  const colors = {
    active: 'success',
    inactive: 'warning',
    banned: 'error'
  }
  return colors[status] || 'grey'
}

const getRankColor = (index) => {
  const colors = ['amber', 'grey-lighten-1', 'brown-lighten-1', 'blue-grey', 'blue-grey']
  return colors[index] || 'grey'
}

const calculateProgressValue = (data) => {
  if (!data || !data.previous) return 50
  const ratio = data.current / Math.max(data.previous, 1)
  return Math.min(Math.max(ratio * 50, 5), 100)
}
</script>

<style scoped>
.dashboard-container {
  padding: 0;
}

.growth-card {
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.growth-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 25px 0 rgba(0, 0, 0, 0.1);
}

.h-100 {
  height: 100%;
}

/* Activity Timeline */
.activity-timeline {
  padding: 16px;
}

:deep(.v-timeline-item__body) {
  padding-top: 2px;
  padding-bottom: 12px;
}

:deep(.v-timeline--density-compact .v-timeline-item__opposite) {
  display: none;
}

/* System Stats */
.system-stat {
  padding: 8px 0;
}

:deep(.v-table) {
  background: transparent;
}

:deep(.v-table th) {
  font-weight: 600 !important;
  text-transform: uppercase;
  font-size: 0.75rem !important;
  letter-spacing: 0.5px;
}

/* Card hover effects */
:deep(.v-card) {
  transition: box-shadow 0.2s ease-in-out;
}

:deep(.v-card:hover) {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}
</style>
