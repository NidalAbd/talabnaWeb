<template>
  <div class="seo-dashboard-modern">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-title">
          <h1><i class="fas fa-chart-line"></i> SEO Analytics</h1>
          <p class="subtitle">Google Search Console Performance Dashboard</p>
        </div>
        <div class="header-actions">
          <div class="date-selector">
            <i class="fas fa-calendar-alt"></i>
            <select v-model="dateRange" @change="refreshData">
              <option value="7">Last 7 days</option>
              <option value="14">Last 14 days</option>
              <option value="28">Last 28 days</option>
              <option value="90">Last 90 days</option>
            </select>
          </div>
          <button class="btn-action btn-secondary" @click="testGscConnection" :disabled="testing">
            <i class="fas fa-plug"></i>
            <span>{{ testing ? 'Testing...' : 'Test Connection' }}</span>
          </button>
          <button class="btn-action btn-primary" @click="syncNow" :disabled="syncing">
            <i class="fas fa-sync-alt" :class="{ 'fa-spin': syncing }"></i>
            <span>{{ syncing ? 'Syncing...' : 'Sync Now' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- GSC Status Alert -->
    <div v-if="!gscEnabled" class="alert-banner warning">
      <div class="alert-icon">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <div class="alert-content">
        <h4>Google Search Console Not Configured</h4>
        <p>Configure GSC credentials in your .env file to enable real-time SEO data synchronization.</p>
      </div>
    </div>

    <!-- Stats Overview Cards -->
    <div class="stats-grid">
      <div class="stat-card clicks">
        <div class="stat-icon">
          <i class="fas fa-mouse-pointer"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ formatNumber(totalClicks) }}</span>
          <span class="stat-label">Total Clicks</span>
          <div class="stat-trend up" v-if="clicksTrend > 0">
            <i class="fas fa-arrow-up"></i> {{ clicksTrend }}%
          </div>
          <div class="stat-trend down" v-else-if="clicksTrend < 0">
            <i class="fas fa-arrow-down"></i> {{ Math.abs(clicksTrend) }}%
          </div>
        </div>
      </div>

      <div class="stat-card impressions">
        <div class="stat-icon">
          <i class="fas fa-eye"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ formatNumber(totalImpressions) }}</span>
          <span class="stat-label">Impressions</span>
          <div class="stat-trend up" v-if="impressionsTrend > 0">
            <i class="fas fa-arrow-up"></i> {{ impressionsTrend }}%
          </div>
        </div>
      </div>

      <div class="stat-card ctr">
        <div class="stat-icon">
          <i class="fas fa-percentage"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ avgCtr }}%</span>
          <span class="stat-label">Avg. CTR</span>
        </div>
      </div>

      <div class="stat-card position">
        <div class="stat-icon">
          <i class="fas fa-sort-amount-up"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ avgPosition }}</span>
          <span class="stat-label">Avg. Position</span>
        </div>
      </div>

      <div class="stat-card keywords">
        <div class="stat-icon">
          <i class="fas fa-key"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ formatNumber(totalKeywords) }}</span>
          <span class="stat-label">Keywords</span>
        </div>
      </div>

      <div class="stat-card pages">
        <div class="stat-icon">
          <i class="fas fa-file-alt"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ formatNumber(totalPages) }}</span>
          <span class="stat-label">Pages Indexed</span>
        </div>
      </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="content-tabs">
      <div class="tabs-nav">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          class="tab-btn"
          :class="{ active: activeTab === tab.id }"
          @click="switchTab(tab.id)"
        >
          <i :class="tab.icon"></i>
          <span>{{ tab.label }}</span>
          <span v-if="tab.badge" class="tab-badge">{{ tab.badge }}</span>
        </button>
      </div>

      <div class="tabs-content">
        <!-- Overview Tab -->
        <div v-show="activeTab === 'overview'" class="tab-pane">
          <div class="overview-grid">
            <!-- Performance Chart -->
            <div class="card chart-card">
              <div class="card-header">
                <h3><i class="fas fa-chart-area"></i> Performance Trend</h3>
              </div>
              <div class="card-body">
                <div v-if="loading" class="loading-state">
                  <div class="spinner"></div>
                </div>
                <div v-else-if="dailyStats.length === 0" class="empty-state">
                  <i class="fas fa-chart-line"></i>
                  <p>No performance data available</p>
                </div>
                <div v-else class="chart-container">
                  <canvas ref="performanceChart"></canvas>
                </div>
              </div>
            </div>

            <!-- Page Type Distribution -->
            <div class="card">
              <div class="card-header">
                <h3><i class="fas fa-sitemap"></i> Performance by Page Type</h3>
              </div>
              <div class="card-body">
                <div v-if="loading" class="loading-state">
                  <div class="spinner"></div>
                </div>
                <div v-else-if="performanceByType.length === 0" class="empty-state">
                  <i class="fas fa-folder-open"></i>
                  <p>No page data available</p>
                </div>
                <div v-else class="page-type-list">
                  <div v-for="item in performanceByType" :key="item.page_type" class="page-type-item">
                    <div class="page-type-info">
                      <span class="page-type-badge" :class="getPageTypeClass(item.page_type)">
                        <i :class="getPageTypeIcon(item.page_type)"></i>
                        {{ formatPageType(item.page_type) }}
                      </span>
                      <span class="page-count">{{ item.pages }} pages</span>
                    </div>
                    <div class="page-type-stats">
                      <div class="mini-stat">
                        <span class="label">Clicks</span>
                        <span class="value">{{ formatNumber(item.total_clicks) }}</span>
                      </div>
                      <div class="mini-stat">
                        <span class="label">Impressions</span>
                        <span class="value">{{ formatNumber(item.total_impressions) }}</span>
                      </div>
                      <div class="mini-stat">
                        <span class="label">Avg Pos</span>
                        <span class="value">{{ parseFloat(item.avg_position).toFixed(1) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Top Countries -->
            <div class="card">
              <div class="card-header">
                <h3><i class="fas fa-globe"></i> Traffic by Country</h3>
              </div>
              <div class="card-body">
                <div v-if="loading" class="loading-state">
                  <div class="spinner"></div>
                </div>
                <div v-else-if="countries.length === 0" class="empty-state">
                  <i class="fas fa-globe-americas"></i>
                  <p>No country data available</p>
                </div>
                <div v-else class="country-list">
                  <div v-for="(country, index) in countries.slice(0, 8)" :key="country.code" class="country-item">
                    <div class="country-rank">{{ index + 1 }}</div>
                    <div class="country-flag">{{ getCountryFlag(country.code) }}</div>
                    <div class="country-name">{{ country.name }}</div>
                    <div class="country-stats">
                      <span class="clicks">{{ formatNumber(country.clicks) }} clicks</span>
                    </div>
                    <div class="country-bar">
                      <div class="bar-fill" :style="{ width: getCountryBarWidth(country.clicks) }"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
              <div class="card-header">
                <h3><i class="fas fa-history"></i> Recent Sync Activity</h3>
              </div>
              <div class="card-body">
                <div v-if="dashboard?.recent_logs?.length === 0" class="empty-state">
                  <i class="fas fa-clock"></i>
                  <p>No sync activity yet</p>
                </div>
                <div v-else class="activity-list">
                  <div v-for="log in dashboard?.recent_logs?.slice(0, 5)" :key="log.id" class="activity-item">
                    <div class="activity-icon" :class="log.status">
                      <i :class="getLogIcon(log.status)"></i>
                    </div>
                    <div class="activity-info">
                      <span class="activity-action">{{ log.action }}</span>
                      <span class="activity-meta">
                        <span class="source">{{ log.source }}</span>
                        <span class="time">{{ formatDate(log.created_at) }}</span>
                      </span>
                    </div>
                    <div class="activity-status">
                      <span class="status-badge" :class="log.status">{{ log.status }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Keywords Tab -->
        <div v-show="activeTab === 'keywords'" class="tab-pane">
          <div class="card">
            <div class="card-header with-search">
              <h3><i class="fas fa-key"></i> Top Keywords</h3>
              <div class="search-box">
                <i class="fas fa-search"></i>
                <input
                  type="text"
                  v-model="keywordSearch"
                  @input="debounceKeywordSearch"
                  placeholder="Search keywords..."
                >
              </div>
            </div>
            <div class="card-body no-padding">
              <div v-if="loading" class="loading-state">
                <div class="spinner"></div>
              </div>
              <div v-else-if="keywords.data.length === 0" class="empty-state">
                <i class="fas fa-search"></i>
                <p>No keywords found</p>
              </div>
              <div v-else class="data-table-wrapper">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th class="keyword-col">Keyword</th>
                      <th class="text-right">Clicks</th>
                      <th class="text-right">Impressions</th>
                      <th class="text-right">CTR</th>
                      <th class="text-right">Position</th>
                      <th class="text-center">Trend</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="kw in keywords.data" :key="kw.keyword">
                      <td class="keyword-col">
                        <span class="keyword-text">{{ kw.keyword }}</span>
                      </td>
                      <td class="text-right">
                        <span class="metric clicks">{{ formatNumber(kw.total_clicks) }}</span>
                      </td>
                      <td class="text-right">
                        <span class="metric">{{ formatNumber(kw.total_impressions) }}</span>
                      </td>
                      <td class="text-right">
                        <span class="metric">{{ parseFloat(kw.avg_ctr).toFixed(2) }}%</span>
                      </td>
                      <td class="text-right">
                        <span class="position-badge" :class="getPositionClass(kw.avg_position)">
                          {{ parseFloat(kw.avg_position).toFixed(1) }}
                        </span>
                      </td>
                      <td class="text-center">
                        <span v-if="kw.trend" class="trend-indicator" :class="kw.trend">
                          <i :class="getTrendArrow(kw.trend)"></i>
                        </span>
                        <span v-else class="trend-indicator neutral">
                          <i class="fas fa-minus"></i>
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-if="keywords.last_page > 1" class="pagination-bar">
                <span class="pagination-info">
                  Showing {{ (keywords.current_page - 1) * 50 + 1 }} - {{ Math.min(keywords.current_page * 50, keywords.total) }} of {{ keywords.total }}
                </span>
                <div class="pagination-buttons">
                  <button
                    class="btn-page"
                    :disabled="keywords.current_page === 1"
                    @click="loadKeywords(keywords.current_page - 1)"
                  >
                    <i class="fas fa-chevron-left"></i>
                  </button>
                  <span class="page-indicator">{{ keywords.current_page }} / {{ keywords.last_page }}</span>
                  <button
                    class="btn-page"
                    :disabled="keywords.current_page === keywords.last_page"
                    @click="loadKeywords(keywords.current_page + 1)"
                  >
                    <i class="fas fa-chevron-right"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pages Tab -->
        <div v-show="activeTab === 'pages'" class="tab-pane">
          <div class="card">
            <div class="card-header with-filters">
              <h3><i class="fas fa-file-alt"></i> Page Performance</h3>
              <div class="filter-group">
                <select v-model="pageTypeFilter" @change="loadPages()" class="filter-select">
                  <option value="">All Page Types</option>
                  <option value="home">Home</option>
                  <option value="category">Category</option>
                  <option value="subcategory">Subcategory</option>
                  <option value="post">Post</option>
                  <option value="city">City</option>
                  <option value="country">Country</option>
                </select>
              </div>
            </div>
            <div class="card-body no-padding">
              <div v-if="loading" class="loading-state">
                <div class="spinner"></div>
              </div>
              <div v-else-if="pages.data.length === 0" class="empty-state">
                <i class="fas fa-file"></i>
                <p>No pages found</p>
              </div>
              <div v-else class="data-table-wrapper">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th class="url-col">URL</th>
                      <th>Type</th>
                      <th class="text-right">Clicks</th>
                      <th class="text-right">Impressions</th>
                      <th class="text-right">CTR</th>
                      <th class="text-right">Position</th>
                      <th class="text-center">Trend</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="page in pages.data" :key="page.url">
                      <td class="url-col">
                        <a :href="page.url" target="_blank" class="url-link">
                          {{ truncateUrl(page.url) }}
                          <i class="fas fa-external-link-alt"></i>
                        </a>
                      </td>
                      <td>
                        <span class="type-badge" :class="getPageTypeClass(page.page_type)">
                          {{ formatPageType(page.page_type) }}
                        </span>
                      </td>
                      <td class="text-right">
                        <span class="metric clicks">{{ formatNumber(page.total_clicks) }}</span>
                      </td>
                      <td class="text-right">
                        <span class="metric">{{ formatNumber(page.total_impressions) }}</span>
                      </td>
                      <td class="text-right">
                        <span class="metric">{{ parseFloat(page.avg_ctr).toFixed(2) }}%</span>
                      </td>
                      <td class="text-right">
                        <span class="position-badge" :class="getPositionClass(page.avg_position)">
                          {{ parseFloat(page.avg_position).toFixed(1) }}
                        </span>
                      </td>
                      <td class="text-center">
                        <span v-if="page.trend" class="trend-indicator" :class="page.trend">
                          <i :class="getTrendArrow(page.trend)"></i>
                        </span>
                        <span v-else class="trend-indicator neutral">
                          <i class="fas fa-minus"></i>
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-if="pages.last_page > 1" class="pagination-bar">
                <span class="pagination-info">
                  Showing {{ (pages.current_page - 1) * 50 + 1 }} - {{ Math.min(pages.current_page * 50, pages.total) }} of {{ pages.total }}
                </span>
                <div class="pagination-buttons">
                  <button
                    class="btn-page"
                    :disabled="pages.current_page === 1"
                    @click="loadPages(pages.current_page - 1)"
                  >
                    <i class="fas fa-chevron-left"></i>
                  </button>
                  <span class="page-indicator">{{ pages.current_page }} / {{ pages.last_page }}</span>
                  <button
                    class="btn-page"
                    :disabled="pages.current_page === pages.last_page"
                    @click="loadPages(pages.current_page + 1)"
                  >
                    <i class="fas fa-chevron-right"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Issues Tab -->
        <div v-show="activeTab === 'issues'" class="tab-pane">
          <div class="card">
            <div class="card-header">
              <h3><i class="fas fa-exclamation-triangle"></i> SEO Issues</h3>
            </div>
            <div class="card-body">
              <div v-if="loading" class="loading-state">
                <div class="spinner"></div>
              </div>
              <div v-else-if="issues.data.length === 0" class="empty-state success">
                <i class="fas fa-check-circle"></i>
                <h4>No Issues Found!</h4>
                <p>Your site is performing well with no detected SEO issues.</p>
              </div>
              <div v-else class="issues-list">
                <div v-for="issue in issues.data" :key="issue.id" class="issue-card" :class="issue.severity">
                  <div class="issue-severity">
                    <i :class="getSeverityIcon(issue.severity)"></i>
                  </div>
                  <div class="issue-content">
                    <h4>{{ issue.title }}</h4>
                    <p class="issue-url">{{ issue.url }}</p>
                    <p class="issue-desc" v-if="issue.description">{{ issue.description }}</p>
                    <div class="issue-meta">
                      <span class="issue-type">{{ issue.issue_type }}</span>
                      <span class="issue-date">Detected: {{ formatDate(issue.detected_at) }}</span>
                    </div>
                  </div>
                  <div class="issue-actions">
                    <button class="btn-icon success" @click="resolveIssue(issue.id)" title="Mark Resolved">
                      <i class="fas fa-check"></i>
                    </button>
                    <button class="btn-icon secondary" @click="ignoreIssue(issue.id)" title="Ignore">
                      <i class="fas fa-eye-slash"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Logs Tab -->
        <div v-show="activeTab === 'logs'" class="tab-pane">
          <div class="card">
            <div class="card-header">
              <h3><i class="fas fa-list-alt"></i> Sync History</h3>
            </div>
            <div class="card-body no-padding">
              <div v-if="loading" class="loading-state">
                <div class="spinner"></div>
              </div>
              <div v-else-if="logs.data.length === 0" class="empty-state">
                <i class="fas fa-history"></i>
                <p>No sync logs found</p>
              </div>
              <div v-else class="data-table-wrapper">
                <table class="data-table logs-table">
                  <thead>
                    <tr>
                      <th>Action</th>
                      <th>Source</th>
                      <th>Status</th>
                      <th class="text-right">Processed</th>
                      <th class="text-right">Created</th>
                      <th class="text-right">Updated</th>
                      <th class="text-right">Duration</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="log in logs.data" :key="log.id">
                      <td><strong>{{ log.action }}</strong></td>
                      <td>{{ log.source }}</td>
                      <td>
                        <span class="status-badge" :class="log.status">
                          <i :class="getLogIcon(log.status)"></i>
                          {{ log.status }}
                        </span>
                      </td>
                      <td class="text-right">{{ log.records_processed }}</td>
                      <td class="text-right text-success">+{{ log.records_created }}</td>
                      <td class="text-right text-info">{{ log.records_updated }}</td>
                      <td class="text-right">{{ log.duration_seconds ? log.duration_seconds + 's' : '-' }}</td>
                      <td>{{ formatDate(log.created_at) }}</td>
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

<script>
import { useSeoAdmin } from '@/composables/useSeoAdmin.js'
import { ref, onMounted, computed, watch, nextTick } from 'vue'
import Chart from 'chart.js/auto'

export default {
  name: 'SeoDashboard',
  setup() {
    const {
      loading,
      error,
      dashboard,
      stats,
      keywords,
      pages,
      issues,
      logs,
      dailyStats,
      performanceByType,
      countries,
      gscEnabled,
      fetchDashboard,
      fetchStats,
      fetchKeywords,
      fetchPages,
      fetchIssues,
      updateIssue,
      fetchLogs,
      fetchDailyStats,
      fetchPerformanceByType,
      fetchCountries,
      syncData,
      testConnection
    } = useSeoAdmin()

    const activeTab = ref('overview')
    const dateRange = ref('28')
    const syncing = ref(false)
    const testing = ref(false)
    const keywordSearch = ref('')
    const pageTypeFilter = ref('')
    const performanceChart = ref(null)
    let chartInstance = null

    const tabs = computed(() => [
      { id: 'overview', label: 'Overview', icon: 'fas fa-chart-pie' },
      { id: 'keywords', label: 'Keywords', icon: 'fas fa-key' },
      { id: 'pages', label: 'Pages', icon: 'fas fa-file-alt' },
      { id: 'issues', label: 'Issues', icon: 'fas fa-exclamation-circle', badge: issueCount.value > 0 ? issueCount.value : null },
      { id: 'logs', label: 'Logs', icon: 'fas fa-history' }
    ])

    const issueCount = computed(() => dashboard.value?.issue_stats?.open || 0)

    const totalClicks = computed(() => dashboard.value?.totals?.clicks || 0)
    const totalImpressions = computed(() => dashboard.value?.totals?.impressions || 0)
    const avgCtr = computed(() => (dashboard.value?.totals?.ctr || 0).toFixed(2))
    const avgPosition = computed(() => (dashboard.value?.totals?.position || 0).toFixed(1))
    const totalKeywords = computed(() => dashboard.value?.totals?.keywords || 0)
    const totalPages = computed(() => dashboard.value?.totals?.pages || 0)
    const clicksTrend = computed(() => dashboard.value?.trends?.clicks || 0)
    const impressionsTrend = computed(() => dashboard.value?.trends?.impressions || 0)

    const maxCountryClicks = computed(() => {
      if (!countries.value.length) return 1
      return Math.max(...countries.value.map(c => c.clicks))
    })

    const loadData = async () => {
      await Promise.all([
        fetchDashboard(),
        fetchStats(),
        fetchDailyStats(parseInt(dateRange.value)),
        fetchPerformanceByType(parseInt(dateRange.value)),
        fetchCountries(parseInt(dateRange.value))
      ])
      await nextTick()
      renderChart()
    }

    const refreshData = () => {
      loadData()
    }

    const switchTab = (tabId) => {
      activeTab.value = tabId
      if (tabId === 'keywords') loadKeywords()
      if (tabId === 'pages') loadPages()
      if (tabId === 'issues') loadIssues()
      if (tabId === 'logs') loadLogs()
    }

    const loadKeywords = async (page = 1) => {
      await fetchKeywords({
        days: dateRange.value,
        search: keywordSearch.value,
        page,
        per_page: 50
      })
    }

    const loadPages = async (page = 1) => {
      await fetchPages({
        days: dateRange.value,
        page_type: pageTypeFilter.value,
        page,
        per_page: 50
      })
    }

    const loadIssues = async () => {
      await fetchIssues({ status: 'open' })
    }

    const loadLogs = async () => {
      await fetchLogs({ limit: 50 })
    }

    let searchTimeout
    const debounceKeywordSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        loadKeywords()
      }, 300)
    }

    const syncNow = async () => {
      syncing.value = true
      try {
        const result = await syncData(parseInt(dateRange.value))
        if (result.success) {
          showNotification('success', `Sync completed! Processed: ${result.processed}, Created: ${result.created}, Updated: ${result.updated}`)
          loadData()
        } else {
          showNotification('error', 'Sync failed: ' + (result.message || 'Unknown error'))
        }
      } catch (err) {
        showNotification('error', 'Sync failed: ' + err.message)
      } finally {
        syncing.value = false
      }
    }

    const testGscConnection = async () => {
      testing.value = true
      try {
        const result = await testConnection()
        if (result.success) {
          showNotification('success', 'Connection successful!')
        } else {
          showNotification('error', 'Connection failed: ' + result.message)
        }
      } catch (err) {
        showNotification('error', 'Connection test failed: ' + err.message)
      } finally {
        testing.value = false
      }
    }

    const resolveIssue = async (id) => {
      try {
        await updateIssue(id, 'resolve')
        showNotification('success', 'Issue marked as resolved')
        loadIssues()
      } catch (err) {
        showNotification('error', 'Failed to resolve issue')
      }
    }

    const ignoreIssue = async (id) => {
      try {
        await updateIssue(id, 'ignore')
        showNotification('success', 'Issue ignored')
        loadIssues()
      } catch (err) {
        showNotification('error', 'Failed to ignore issue')
      }
    }

    const showNotification = (type, message) => {
      alert(message) // Replace with better notification system
    }

    const renderChart = () => {
      if (!performanceChart.value || !dailyStats.value.length) return

      if (chartInstance) {
        chartInstance.destroy()
      }

      const ctx = performanceChart.value.getContext('2d')
      chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: dailyStats.value.map(d => formatChartDate(d.date)),
          datasets: [
            {
              label: 'Clicks',
              data: dailyStats.value.map(d => d.clicks),
              borderColor: '#3b82f6',
              backgroundColor: 'rgba(59, 130, 246, 0.1)',
              fill: true,
              tension: 0.4,
              yAxisID: 'y'
            },
            {
              label: 'Impressions',
              data: dailyStats.value.map(d => d.impressions),
              borderColor: '#10b981',
              backgroundColor: 'rgba(16, 185, 129, 0.1)',
              fill: true,
              tension: 0.4,
              yAxisID: 'y1'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: 'index',
            intersect: false
          },
          plugins: {
            legend: {
              position: 'top',
              labels: {
                usePointStyle: true,
                padding: 20
              }
            }
          },
          scales: {
            y: {
              type: 'linear',
              display: true,
              position: 'left',
              title: {
                display: true,
                text: 'Clicks'
              }
            },
            y1: {
              type: 'linear',
              display: true,
              position: 'right',
              title: {
                display: true,
                text: 'Impressions'
              },
              grid: {
                drawOnChartArea: false
              }
            }
          }
        }
      })
    }

    // Formatting helpers
    const formatNumber = (num) => new Intl.NumberFormat().format(num || 0)

    const formatDate = (date) => {
      if (!date) return '-'
      return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const formatChartDate = (date) => {
      if (!date) return ''
      return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
    }

    const formatPageType = (type) => {
      const types = {
        home: 'Home',
        category: 'Category',
        subcategory: 'Subcategory',
        post: 'Post',
        city: 'City',
        country: 'Country',
        user: 'User',
        other: 'Other'
      }
      return types[type] || type || 'Unknown'
    }

    const truncateUrl = (url) => {
      if (!url) return ''
      try {
        const urlObj = new URL(url)
        return urlObj.pathname.length > 50 ? urlObj.pathname.substring(0, 50) + '...' : urlObj.pathname
      } catch {
        return url.length > 50 ? url.substring(0, 50) + '...' : url
      }
    }

    const getPageTypeClass = (type) => type || 'other'
    const getPageTypeIcon = (type) => {
      const icons = {
        home: 'fas fa-home',
        category: 'fas fa-folder',
        subcategory: 'fas fa-folder-open',
        post: 'fas fa-file-alt',
        city: 'fas fa-city',
        country: 'fas fa-flag',
        user: 'fas fa-user'
      }
      return icons[type] || 'fas fa-file'
    }

    const getPositionClass = (pos) => {
      const p = parseFloat(pos)
      if (p <= 3) return 'excellent'
      if (p <= 10) return 'good'
      if (p <= 20) return 'average'
      return 'poor'
    }

    const getTrendArrow = (trend) => {
      if (trend === 'up') return 'fas fa-arrow-up'
      if (trend === 'down') return 'fas fa-arrow-down'
      return 'fas fa-minus'
    }

    const getSeverityIcon = (severity) => {
      if (severity === 'critical') return 'fas fa-times-circle'
      if (severity === 'warning') return 'fas fa-exclamation-triangle'
      return 'fas fa-info-circle'
    }

    const getLogIcon = (status) => {
      if (status === 'completed') return 'fas fa-check-circle'
      if (status === 'failed') return 'fas fa-times-circle'
      if (status === 'running') return 'fas fa-spinner fa-spin'
      return 'fas fa-clock'
    }

    const getCountryFlag = (code) => {
      const flags = {
        SA: '🇸🇦', AE: '🇦🇪', EG: '🇪🇬', JO: '🇯🇴', KW: '🇰🇼',
        QA: '🇶🇦', BH: '🇧🇭', OM: '🇴🇲', IQ: '🇮🇶', LB: '🇱🇧',
        SY: '🇸🇾', PS: '🇵🇸', YE: '🇾🇪', LY: '🇱🇾', TN: '🇹🇳',
        MA: '🇲🇦', DZ: '🇩🇿', SD: '🇸🇩', US: '🇺🇸', GB: '🇬🇧',
        DE: '🇩🇪', FR: '🇫🇷', IN: '🇮🇳', PK: '🇵🇰', TR: '🇹🇷'
      }
      return flags[code] || '🌍'
    }

    const getCountryBarWidth = (clicks) => {
      return ((clicks / maxCountryClicks.value) * 100) + '%'
    }

    onMounted(() => {
      loadData()
    })

    watch(dailyStats, () => {
      nextTick(() => renderChart())
    })

    return {
      loading,
      error,
      dashboard,
      stats,
      keywords,
      pages,
      issues,
      logs,
      dailyStats,
      performanceByType,
      countries,
      gscEnabled,
      activeTab,
      tabs,
      dateRange,
      syncing,
      testing,
      keywordSearch,
      pageTypeFilter,
      performanceChart,
      issueCount,
      totalClicks,
      totalImpressions,
      avgCtr,
      avgPosition,
      totalKeywords,
      totalPages,
      clicksTrend,
      impressionsTrend,
      refreshData,
      switchTab,
      loadKeywords,
      loadPages,
      loadIssues,
      loadLogs,
      debounceKeywordSearch,
      syncNow,
      testGscConnection,
      resolveIssue,
      ignoreIssue,
      formatNumber,
      formatDate,
      formatPageType,
      truncateUrl,
      getPageTypeClass,
      getPageTypeIcon,
      getPositionClass,
      getTrendArrow,
      getSeverityIcon,
      getLogIcon,
      getCountryFlag,
      getCountryBarWidth
    }
  }
}
</script>

<style scoped>
.seo-dashboard-modern {
  padding: 0;
  background: #f8fafc;
  min-height: 100vh;
}

/* Page Header */
.page-header {
  background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
  padding: 24px 28px;
  margin: -8px -8px 24px -8px;
  border-radius: 0 0 16px 16px;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.header-title h1 {
  color: white;
  font-size: 1.75rem;
  font-weight: 700;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-title h1 i {
  font-size: 1.5rem;
}

.header-title .subtitle {
  color: rgba(255,255,255,0.7);
  font-size: 0.9rem;
  margin: 4px 0 0 0;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.date-selector {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255,255,255,0.15);
  padding: 8px 14px;
  border-radius: 8px;
  color: white;
}

.date-selector select {
  background: transparent;
  border: none;
  color: white;
  font-size: 0.9rem;
  cursor: pointer;
  outline: none;
}

.date-selector select option {
  color: #1e3a5f;
}

.btn-action {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn-action.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-action.btn-primary:hover {
  background: #2563eb;
  transform: translateY(-1px);
}

.btn-action.btn-secondary {
  background: rgba(255,255,255,0.2);
  color: white;
}

.btn-action.btn-secondary:hover {
  background: rgba(255,255,255,0.3);
}

.btn-action:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Alert Banner */
.alert-banner {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 16px 20px;
  border-radius: 12px;
  margin-bottom: 24px;
}

.alert-banner.warning {
  background: #fef3c7;
  border: 1px solid #f59e0b;
}

.alert-icon {
  font-size: 1.5rem;
}

.alert-banner.warning .alert-icon {
  color: #f59e0b;
}

.alert-content h4 {
  margin: 0 0 4px 0;
  font-size: 1rem;
  color: #92400e;
}

.alert-content p {
  margin: 0;
  font-size: 0.875rem;
  color: #a16207;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.stat-icon {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  color: white;
}

.stat-card.clicks .stat-icon { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.stat-card.impressions .stat-icon { background: linear-gradient(135deg, #10b981, #059669); }
.stat-card.ctr .stat-icon { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
.stat-card.position .stat-icon { background: linear-gradient(135deg, #f59e0b, #d97706); }
.stat-card.keywords .stat-icon { background: linear-gradient(135deg, #ec4899, #be185d); }
.stat-card.pages .stat-icon { background: linear-gradient(135deg, #06b6d4, #0891b2); }

.stat-info {
  flex: 1;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
}

.stat-label {
  display: block;
  font-size: 0.8rem;
  color: #64748b;
  margin-top: 2px;
}

.stat-trend {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 12px;
  margin-top: 6px;
}

.stat-trend.up {
  background: #dcfce7;
  color: #16a34a;
}

.stat-trend.down {
  background: #fee2e2;
  color: #dc2626;
}

/* Content Tabs */
.content-tabs {
  background: white;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  overflow: hidden;
}

.tabs-nav {
  display: flex;
  border-bottom: 1px solid #e2e8f0;
  padding: 0 8px;
  overflow-x: auto;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 16px 20px;
  background: none;
  border: none;
  font-size: 0.9rem;
  font-weight: 500;
  color: #64748b;
  cursor: pointer;
  border-bottom: 3px solid transparent;
  transition: all 0.2s;
  white-space: nowrap;
}

.tab-btn:hover {
  color: #3b82f6;
}

.tab-btn.active {
  color: #3b82f6;
  border-bottom-color: #3b82f6;
}

.tab-badge {
  background: #ef4444;
  color: white;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 10px;
}

.tabs-content {
  padding: 24px;
}

/* Cards */
.card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
}

.card-header h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 10px;
}

.card-header h3 i {
  color: #3b82f6;
}

.card-body {
  padding: 20px;
}

.card-body.no-padding {
  padding: 0;
}

/* Overview Grid */
.overview-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.chart-card {
  grid-column: span 2;
}

.chart-container {
  height: 300px;
  position: relative;
}

@media (max-width: 992px) {
  .overview-grid {
    grid-template-columns: 1fr;
  }
  .chart-card {
    grid-column: span 1;
  }
}

/* Page Type List */
.page-type-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.page-type-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: #f8fafc;
  border-radius: 8px;
}

.page-type-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.page-type-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
}

.page-type-badge.home { background: #dbeafe; color: #1d4ed8; }
.page-type-badge.category { background: #dcfce7; color: #16a34a; }
.page-type-badge.subcategory { background: #fef3c7; color: #d97706; }
.page-type-badge.post { background: #fce7f3; color: #be185d; }
.page-type-badge.city { background: #e0e7ff; color: #4338ca; }
.page-type-badge.country { background: #ccfbf1; color: #0d9488; }
.page-type-badge.other { background: #f1f5f9; color: #475569; }

.page-count {
  font-size: 0.8rem;
  color: #64748b;
}

.page-type-stats {
  display: flex;
  gap: 20px;
}

.mini-stat {
  text-align: center;
}

.mini-stat .label {
  display: block;
  font-size: 0.7rem;
  color: #94a3b8;
  text-transform: uppercase;
}

.mini-stat .value {
  display: block;
  font-size: 0.95rem;
  font-weight: 600;
  color: #1e293b;
}

/* Country List */
.country-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.country-item {
  display: grid;
  grid-template-columns: 24px 30px 1fr auto 100px;
  align-items: center;
  gap: 10px;
  padding: 8px 0;
}

.country-rank {
  font-size: 0.8rem;
  font-weight: 700;
  color: #94a3b8;
}

.country-flag {
  font-size: 1.25rem;
}

.country-name {
  font-size: 0.9rem;
  font-weight: 500;
  color: #1e293b;
}

.country-stats {
  font-size: 0.8rem;
  color: #64748b;
}

.country-bar {
  height: 6px;
  background: #e2e8f0;
  border-radius: 3px;
  overflow: hidden;
}

.bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6);
  border-radius: 3px;
  transition: width 0.3s;
}

/* Activity List */
.activity-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 0;
  border-bottom: 1px solid #f1f5f9;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
}

.activity-icon.completed { background: #dcfce7; color: #16a34a; }
.activity-icon.failed { background: #fee2e2; color: #dc2626; }
.activity-icon.running { background: #dbeafe; color: #2563eb; }

.activity-info {
  flex: 1;
}

.activity-action {
  display: block;
  font-weight: 500;
  color: #1e293b;
}

.activity-meta {
  display: flex;
  gap: 12px;
  font-size: 0.8rem;
  color: #64748b;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.status-badge.completed { background: #dcfce7; color: #16a34a; }
.status-badge.failed { background: #fee2e2; color: #dc2626; }
.status-badge.running { background: #dbeafe; color: #2563eb; }
.status-badge.pending { background: #fef3c7; color: #d97706; }

/* Data Tables */
.data-table-wrapper {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 14px 16px;
  text-align: left;
  border-bottom: 1px solid #f1f5f9;
}

.data-table th {
  background: #f8fafc;
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.data-table tr:hover {
  background: #f8fafc;
}

.keyword-col {
  max-width: 300px;
}

.keyword-text {
  display: block;
  font-weight: 500;
  color: #1e293b;
}

.url-col {
  max-width: 300px;
}

.url-link {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
}

.url-link:hover {
  text-decoration: underline;
}

.url-link i {
  font-size: 0.7rem;
  opacity: 0.6;
}

.metric {
  font-weight: 500;
}

.metric.clicks {
  color: #3b82f6;
}

.position-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
}

.position-badge.excellent { background: #dcfce7; color: #16a34a; }
.position-badge.good { background: #dbeafe; color: #2563eb; }
.position-badge.average { background: #fef3c7; color: #d97706; }
.position-badge.poor { background: #fee2e2; color: #dc2626; }

.type-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}

.type-badge.home { background: #dbeafe; color: #1d4ed8; }
.type-badge.category { background: #dcfce7; color: #16a34a; }
.type-badge.subcategory { background: #fef3c7; color: #d97706; }
.type-badge.post { background: #fce7f3; color: #be185d; }
.type-badge.city { background: #e0e7ff; color: #4338ca; }
.type-badge.country { background: #ccfbf1; color: #0d9488; }
.type-badge.other { background: #f1f5f9; color: #475569; }

.trend-indicator {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  font-size: 0.75rem;
}

.trend-indicator.up { background: #dcfce7; color: #16a34a; }
.trend-indicator.down { background: #fee2e2; color: #dc2626; }
.trend-indicator.neutral { background: #f1f5f9; color: #94a3b8; }

/* Pagination */
.pagination-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-top: 1px solid #f1f5f9;
  background: #f8fafc;
}

.pagination-info {
  font-size: 0.85rem;
  color: #64748b;
}

.pagination-buttons {
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-page {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-page:hover:not(:disabled) {
  border-color: #3b82f6;
  color: #3b82f6;
}

.btn-page:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-indicator {
  font-size: 0.85rem;
  font-weight: 500;
  color: #1e293b;
  padding: 0 12px;
}

/* Search & Filters */
.search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 8px 14px;
}

.search-box i {
  color: #94a3b8;
}

.search-box input {
  border: none;
  outline: none;
  font-size: 0.9rem;
  width: 200px;
}

.filter-select {
  padding: 8px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.9rem;
  background: white;
  cursor: pointer;
}

/* Issues */
.issues-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.issue-card {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 16px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
}

.issue-card.critical {
  background: #fef2f2;
  border-color: #fecaca;
}

.issue-card.warning {
  background: #fffbeb;
  border-color: #fde68a;
}

.issue-card.info {
  background: #eff6ff;
  border-color: #bfdbfe;
}

.issue-severity {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.issue-card.critical .issue-severity {
  background: #fee2e2;
  color: #dc2626;
}

.issue-card.warning .issue-severity {
  background: #fef3c7;
  color: #d97706;
}

.issue-card.info .issue-severity {
  background: #dbeafe;
  color: #2563eb;
}

.issue-content {
  flex: 1;
}

.issue-content h4 {
  margin: 0 0 4px 0;
  font-size: 1rem;
  color: #1e293b;
}

.issue-url {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0 0 8px 0;
  word-break: break-all;
}

.issue-desc {
  font-size: 0.85rem;
  color: #475569;
  margin: 0 0 12px 0;
}

.issue-meta {
  display: flex;
  gap: 16px;
  font-size: 0.8rem;
  color: #94a3b8;
}

.issue-actions {
  display: flex;
  gap: 8px;
}

.btn-icon {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-icon.success {
  background: #dcfce7;
  color: #16a34a;
}

.btn-icon.success:hover {
  background: #16a34a;
  color: white;
}

.btn-icon.secondary {
  background: #f1f5f9;
  color: #64748b;
}

.btn-icon.secondary:hover {
  background: #64748b;
  color: white;
}

/* Loading & Empty States */
.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 48px 20px;
  text-align: center;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e2e8f0;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-state i {
  font-size: 3rem;
  color: #cbd5e1;
  margin-bottom: 16px;
}

.empty-state h4 {
  margin: 0 0 8px 0;
  color: #1e293b;
}

.empty-state p {
  margin: 0;
  color: #64748b;
  font-size: 0.9rem;
}

.empty-state.success i {
  color: #22c55e;
}

/* Responsive */
@media (max-width: 768px) {
  .page-header {
    padding: 16px;
  }

  .header-content {
    flex-direction: column;
    align-items: flex-start;
  }

  .header-actions {
    width: 100%;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .tabs-nav {
    overflow-x: auto;
    padding-bottom: 0;
  }

  .tab-btn span {
    display: none;
  }

  .tab-btn i {
    font-size: 1.1rem;
  }

  .tabs-content {
    padding: 16px;
  }

  .page-type-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .page-type-stats {
    width: 100%;
    justify-content: space-between;
  }

  .country-item {
    grid-template-columns: 24px 30px 1fr;
  }

  .country-stats,
  .country-bar {
    display: none;
  }
}

.text-right { text-align: right; }
.text-center { text-align: center; }
.text-success { color: #16a34a; }
.text-info { color: #0891b2; }
</style>
