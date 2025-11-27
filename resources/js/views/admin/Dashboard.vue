<template>
  <div>
    <!-- Page Header -->
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4 font-weight-bold">Dashboard</h1>
        <p class="text-subtitle-1 text-medium-emphasis">Welcome to your admin dashboard</p>
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

      <!-- Charts Row 1 -->
      <v-row class="mb-4">
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title>Badge Distribution</v-card-title>
            <v-card-text>
              <pie-chart
                :data="badgeChartData"
                :height="250"
              />
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card>
            <v-card-title>Post Type Distribution</v-card-title>
            <v-card-text>
              <pie-chart
                :data="postTypeChartData"
                :height="250"
              />
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Charts Row 2 -->
      <v-row class="mb-4">
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title>Posts by Month</v-card-title>
            <v-card-text>
              <line-chart
                :data="postsByMonthData"
                :height="250"
              />
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card>
            <v-card-title>Users by Month</v-card-title>
            <v-card-text>
              <line-chart
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
            <v-card-title>Posts by Category</v-card-title>
            <v-card-text>
              <bar-chart
                :data="postsByCategoryData"
                :height="250"
              />
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card>
            <v-card-title>Point Transactions</v-card-title>
            <v-card-text>
              <mixed-chart
                :data="pointTransactionsData"
                :height="250"
              />
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Tables Row -->
      <v-row class="mb-4">
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title>Latest Service Posts</v-card-title>
            <v-card-text class="pa-0">
              <v-table>
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
                    <td>{{ post.id }}</td>
                    <td>{{ post.title }}</td>
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
                    <td>{{ formatDate(post.created_at) }}</td>
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
            <v-card-title>Recent Users</v-card-title>
            <v-card-text class="pa-0">
              <v-table>
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
                    <td>{{ user.id }}</td>
                    <td>{{ user.user_name }}</td>
                    <td>{{ user.email }}</td>
                    <td>
                      <v-chip
                        :color="getUserStatusColor(user.is_active)"
                        size="small"
                        variant="flat"
                      >
                        {{ user.is_active }}
                      </v-chip>
                    </td>
                    <td>{{ formatDate(user.created_at) }}</td>
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

// Chart data
const badgeChartData = ref(null)
const postTypeChartData = ref(null)
const postsByMonthData = ref(null)
const usersByMonthData = ref(null)
const postsByCategoryData = ref(null)
const pointTransactionsData = ref(null)

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

    pointTransactionsData.value = {
      labels: data.pointTransactions.labels,
      datasets: [
        {
          label: 'Transactions Count',
          data: data.pointTransactions.counts,
          backgroundColor: 'rgba(99, 102, 241, 0.7)',
          borderColor: 'rgba(99, 102, 241, 1)',
          borderWidth: 1,
          type: 'bar',
          yAxisID: 'y'
        },
        {
          label: 'Points Volume',
          data: data.pointTransactions.points,
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
  return new Date(date).toLocaleDateString()
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
</script>
