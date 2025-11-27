<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" class="text-center">
      <div class="spinner-border" role="status">
        <span class="sr-only">Loading...</span>
      </div>
    </div>

    <!-- Dashboard Content -->
    <div v-else>
      <!-- Stats Cards Row -->
      <div class="row">
        <!-- Total Users -->
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{ stats.totalUsers }}</h3>
              <p>Total Users</p>
            </div>
            <div class="icon">
              <i class="fas fa-users"></i>
            </div>
            <a href="/users" class="small-box-footer">
              More info <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <!-- Active Users -->
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3>{{ stats.activeUsers }}</h3>
              <p>Active Users</p>
            </div>
            <div class="icon">
              <i class="fas fa-user-check"></i>
            </div>
            <a href="/users?status=active" class="small-box-footer">
              More info <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <!-- Total Posts -->
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3>{{ stats.totalPosts }}</h3>
              <p>Total Posts</p>
            </div>
            <div class="icon">
              <i class="fas fa-clipboard-list"></i>
            </div>
            <a href="/service_posts" class="small-box-footer">
              More info <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>

        <!-- Published Posts -->
        <div class="col-lg-3 col-6">
          <div class="small-box bg-primary">
            <div class="inner">
              <h3>{{ stats.publishedPosts }}</h3>
              <p>Published Posts</p>
            </div>
            <div class="icon">
              <i class="fas fa-check-circle"></i>
            </div>
            <a href="/service_posts?state=published" class="small-box-footer">
              More info <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="row">
        <!-- Badge Distribution -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Badge Distribution</h3>
            </div>
            <div class="card-body">
              <pie-chart :data="badgeChartData" :height="250" />
            </div>
          </div>
        </div>

        <!-- Post Types -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Post Types</h3>
            </div>
            <div class="card-body">
              <pie-chart :data="postTypeChartData" :height="250" />
            </div>
          </div>
        </div>
      </div>

      <!-- Trends Row -->
      <div class="row">
        <!-- Posts by Month -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Posts by Month</h3>
            </div>
            <div class="card-body">
              <line-chart :data="postsByMonthData" :height="250" />
            </div>
          </div>
        </div>

        <!-- Users by Month -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">New Users by Month</h3>
            </div>
            <div class="card-body">
              <line-chart :data="usersByMonthData" :height="250" />
            </div>
          </div>
        </div>
      </div>

      <!-- Category Stats -->
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Posts by Category (Top 10)</h3>
            </div>
            <div class="card-body">
              <bar-chart :data="postsByCategoryData" :height="300" />
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="row">
        <!-- Recent Posts -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Recent Posts</h3>
            </div>
            <div class="card-body p-0">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Title</th>
                    <th>User</th>
                    <th>State</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="post in recentPosts" :key="post.id">
                    <td>{{ post.title }}</td>
                    <td>{{ post.user_name }}</td>
                    <td>
                      <span :class="getStateBadgeClass(post.state)">
                        {{ post.state }}
                      </span>
                    </td>
                    <td>{{ formatDate(post.created_at) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Recent Users -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Recent Users</h3>
            </div>
            <div class="card-body p-0">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="user in recentUsers" :key="user.id">
                    <td>{{ user.user_name }}</td>
                    <td>{{ user.email }}</td>
                    <td>
                      <span :class="getUserStatusBadgeClass(user.is_active)">
                        {{ user.is_active }}
                      </span>
                    </td>
                    <td>{{ formatDate(user.created_at) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
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

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString()
}

const getStateBadgeClass = (state) => {
  const classes = {
    'published': 'badge badge-success',
    'not published': 'badge badge-warning',
    'rejected': 'badge badge-danger',
    'archive': 'badge badge-secondary'
  }
  return classes[state] || 'badge badge-info'
}

const getUserStatusBadgeClass = (status) => {
  const classes = {
    'active': 'badge badge-success',
    'banned': 'badge badge-danger',
    'inactive': 'badge badge-secondary'
  }
  return classes[status] || 'badge badge-info'
}
</script>
