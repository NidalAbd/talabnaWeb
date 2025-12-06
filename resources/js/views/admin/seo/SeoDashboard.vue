<template>
  <div class="seo-dashboard">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1">SEO Dashboard</h2>
        <p class="text-muted mb-0">Google Search Console Analytics & Performance</p>
      </div>
      <div class="d-flex gap-2">
        <select v-model="dateRange" class="form-select" style="width: auto;" @change="refreshData">
          <option value="7">Last 7 days</option>
          <option value="14">Last 14 days</option>
          <option value="28">Last 28 days</option>
          <option value="90">Last 90 days</option>
        </select>
        <button class="btn btn-outline-primary" @click="testGscConnection" :disabled="testing">
          <i class="fas fa-plug"></i> Test Connection
        </button>
        <button class="btn btn-primary" @click="syncNow" :disabled="syncing">
          <i class="fas fa-sync" :class="{ 'fa-spin': syncing }"></i>
          {{ syncing ? 'Syncing...' : 'Sync Now' }}
        </button>
      </div>
    </div>

    <!-- GSC Status Banner -->
    <div v-if="!gscEnabled" class="alert alert-warning mb-4">
      <i class="fas fa-exclamation-triangle me-2"></i>
      <strong>Google Search Console Not Configured</strong>
      <p class="mb-0 mt-1">
        To enable SEO data sync, set <code>GSC_ENABLED=true</code> in your .env file and configure the service account credentials.
      </p>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
      <div v-for="stat in stats" :key="stat.label" class="col-md-6 col-lg">
        <div class="card stat-card" :class="'border-' + stat.color">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="stat-icon me-3" :class="'bg-' + stat.color">
                <i :class="stat.icon"></i>
              </div>
              <div>
                <h4 class="mb-0">{{ stat.value }}</h4>
                <small class="text-muted">{{ stat.label }}</small>
                <div v-if="stat.change" class="mt-1">
                  <span :class="getTrendClass(stat.change)">
                    <i :class="getTrendIcon(stat.change)"></i>
                    {{ Math.abs(stat.change.value) }}%
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4">
      <li class="nav-item">
        <a class="nav-link" :class="{ active: activeTab === 'overview' }" href="#" @click.prevent="activeTab = 'overview'">
          <i class="fas fa-chart-line me-1"></i> Overview
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" :class="{ active: activeTab === 'keywords' }" href="#" @click.prevent="activeTab = 'keywords'; loadKeywords()">
          <i class="fas fa-key me-1"></i> Keywords
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" :class="{ active: activeTab === 'pages' }" href="#" @click.prevent="activeTab = 'pages'; loadPages()">
          <i class="fas fa-file-alt me-1"></i> Pages
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" :class="{ active: activeTab === 'issues' }" href="#" @click.prevent="activeTab = 'issues'; loadIssues()">
          <i class="fas fa-exclamation-circle me-1"></i> Issues
          <span v-if="issueCount > 0" class="badge bg-danger ms-1">{{ issueCount }}</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" :class="{ active: activeTab === 'logs' }" href="#" @click.prevent="activeTab = 'logs'; loadLogs()">
          <i class="fas fa-history me-1"></i> Sync Logs
        </a>
      </li>
    </ul>

    <!-- Overview Tab -->
    <div v-show="activeTab === 'overview'">
      <div class="row">
        <!-- Performance by Page Type -->
        <div class="col-md-6 mb-4">
          <div class="card h-100">
            <div class="card-header">
              <h5 class="mb-0">Performance by Page Type</h5>
            </div>
            <div class="card-body">
              <div v-if="loading" class="text-center py-4">
                <div class="spinner-border" role="status"></div>
              </div>
              <div v-else-if="performanceByType.length === 0" class="text-center text-muted py-4">
                No data available
              </div>
              <table v-else class="table table-sm">
                <thead>
                  <tr>
                    <th>Page Type</th>
                    <th class="text-end">Pages</th>
                    <th class="text-end">Clicks</th>
                    <th class="text-end">Impressions</th>
                    <th class="text-end">Avg Position</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in performanceByType" :key="item.page_type">
                    <td>
                      <span class="badge bg-secondary">{{ formatPageType(item.page_type) }}</span>
                    </td>
                    <td class="text-end">{{ item.pages }}</td>
                    <td class="text-end">{{ formatNumber(item.total_clicks) }}</td>
                    <td class="text-end">{{ formatNumber(item.total_impressions) }}</td>
                    <td class="text-end">{{ parseFloat(item.avg_position).toFixed(1) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Traffic by Country -->
        <div class="col-md-6 mb-4">
          <div class="card h-100">
            <div class="card-header">
              <h5 class="mb-0">Traffic by Country</h5>
            </div>
            <div class="card-body">
              <div v-if="loading" class="text-center py-4">
                <div class="spinner-border" role="status"></div>
              </div>
              <div v-else-if="countries.length === 0" class="text-center text-muted py-4">
                No data available
              </div>
              <table v-else class="table table-sm">
                <thead>
                  <tr>
                    <th>Country</th>
                    <th class="text-end">Clicks</th>
                    <th class="text-end">Impressions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="country in countries.slice(0, 10)" :key="country.code">
                    <td>
                      <span class="me-2">{{ getCountryFlag(country.code) }}</span>
                      {{ country.name }}
                    </td>
                    <td class="text-end">{{ formatNumber(country.clicks) }}</td>
                    <td class="text-end">{{ formatNumber(country.impressions) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Sync Logs -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
          <h5 class="mb-0">Recent Activity</h5>
        </div>
        <div class="card-body">
          <div v-if="dashboard?.recent_logs?.length === 0" class="text-center text-muted py-3">
            No sync logs yet
          </div>
          <div v-else class="list-group list-group-flush">
            <div v-for="log in dashboard?.recent_logs?.slice(0, 5)" :key="log.id" class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <strong>{{ log.action }}</strong>
                <span class="text-muted ms-2">{{ log.source }}</span>
              </div>
              <div>
                <span class="badge" :class="getLogStatusClass(log.status)">{{ log.status }}</span>
                <small class="text-muted ms-2">{{ formatDate(log.created_at) }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Keywords Tab -->
    <div v-show="activeTab === 'keywords'">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Top Keywords</h5>
          <div class="d-flex gap-2">
            <input
              type="text"
              class="form-control form-control-sm"
              placeholder="Search keywords..."
              v-model="keywordSearch"
              @input="debounceKeywordSearch"
              style="width: 200px;"
            >
          </div>
        </div>
        <div class="card-body p-0">
          <div v-if="loading" class="text-center py-4">
            <div class="spinner-border" role="status"></div>
          </div>
          <div v-else-if="keywords.data.length === 0" class="text-center text-muted py-4">
            No keywords found
          </div>
          <table v-else class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Keyword</th>
                <th class="text-end">Clicks</th>
                <th class="text-end">Impressions</th>
                <th class="text-end">CTR</th>
                <th class="text-end">Avg Position</th>
                <th class="text-center">Trend</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="kw in keywords.data" :key="kw.keyword">
                <td>{{ kw.keyword }}</td>
                <td class="text-end">{{ formatNumber(kw.total_clicks) }}</td>
                <td class="text-end">{{ formatNumber(kw.total_impressions) }}</td>
                <td class="text-end">{{ parseFloat(kw.avg_ctr).toFixed(2) }}%</td>
                <td class="text-end">{{ parseFloat(kw.avg_position).toFixed(1) }}</td>
                <td class="text-center">
                  <span v-if="kw.trend" :class="getTrendBadgeClass(kw.trend)">
                    <i :class="getTrendArrow(kw.trend)"></i>
                    {{ kw.trend }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="keywords.last_page > 1" class="card-footer d-flex justify-content-between align-items-center">
          <span class="text-muted">
            Showing {{ (keywords.current_page - 1) * keywords.per_page + 1 }} -
            {{ Math.min(keywords.current_page * keywords.per_page, keywords.total) }}
            of {{ keywords.total }}
          </span>
          <nav>
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item" :class="{ disabled: keywords.current_page === 1 }">
                <a class="page-link" href="#" @click.prevent="loadKeywords(keywords.current_page - 1)">Previous</a>
              </li>
              <li class="page-item" :class="{ disabled: keywords.current_page === keywords.last_page }">
                <a class="page-link" href="#" @click.prevent="loadKeywords(keywords.current_page + 1)">Next</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>

    <!-- Pages Tab -->
    <div v-show="activeTab === 'pages'">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Page Performance</h5>
          <div class="d-flex gap-2">
            <select v-model="pageTypeFilter" class="form-select form-select-sm" style="width: auto;" @change="loadPages()">
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
        <div class="card-body p-0">
          <div v-if="loading" class="text-center py-4">
            <div class="spinner-border" role="status"></div>
          </div>
          <div v-else-if="pages.data.length === 0" class="text-center text-muted py-4">
            No pages found
          </div>
          <table v-else class="table table-hover mb-0">
            <thead>
              <tr>
                <th>URL</th>
                <th>Type</th>
                <th class="text-end">Clicks</th>
                <th class="text-end">Impressions</th>
                <th class="text-end">CTR</th>
                <th class="text-end">Avg Position</th>
                <th class="text-center">Trend</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="page in pages.data" :key="page.url">
                <td class="text-truncate" style="max-width: 300px;" :title="page.url">
                  <a :href="page.url" target="_blank">{{ page.url }}</a>
                </td>
                <td>
                  <span class="badge bg-secondary">{{ formatPageType(page.page_type) }}</span>
                </td>
                <td class="text-end">{{ formatNumber(page.total_clicks) }}</td>
                <td class="text-end">{{ formatNumber(page.total_impressions) }}</td>
                <td class="text-end">{{ parseFloat(page.avg_ctr).toFixed(2) }}%</td>
                <td class="text-end">{{ parseFloat(page.avg_position).toFixed(1) }}</td>
                <td class="text-center">
                  <span v-if="page.trend" :class="getTrendBadgeClass(page.trend)">
                    <i :class="getTrendArrow(page.trend)"></i>
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="pages.last_page > 1" class="card-footer d-flex justify-content-between align-items-center">
          <span class="text-muted">
            Showing {{ (pages.current_page - 1) * pages.per_page + 1 }} -
            {{ Math.min(pages.current_page * pages.per_page, pages.total) }}
            of {{ pages.total }}
          </span>
          <nav>
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item" :class="{ disabled: pages.current_page === 1 }">
                <a class="page-link" href="#" @click.prevent="loadPages(pages.current_page - 1)">Previous</a>
              </li>
              <li class="page-item" :class="{ disabled: pages.current_page === pages.last_page }">
                <a class="page-link" href="#" @click.prevent="loadPages(pages.current_page + 1)">Next</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>

    <!-- Issues Tab -->
    <div v-show="activeTab === 'issues'">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">SEO Issues</h5>
        </div>
        <div class="card-body p-0">
          <div v-if="loading" class="text-center py-4">
            <div class="spinner-border" role="status"></div>
          </div>
          <div v-else-if="issues.data.length === 0" class="text-center text-muted py-4">
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <p>No open issues found!</p>
          </div>
          <table v-else class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Issue</th>
                <th>URL</th>
                <th>Type</th>
                <th>Severity</th>
                <th>Detected</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="issue in issues.data" :key="issue.id">
                <td>{{ issue.title }}</td>
                <td class="text-truncate" style="max-width: 200px;">{{ issue.url }}</td>
                <td>{{ issue.issue_type }}</td>
                <td>
                  <span class="badge" :class="getSeverityClass(issue.severity)">
                    {{ issue.severity }}
                  </span>
                </td>
                <td>{{ formatDate(issue.detected_at) }}</td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-success" @click="resolveIssue(issue.id)" title="Mark Resolved">
                      <i class="fas fa-check"></i>
                    </button>
                    <button class="btn btn-secondary" @click="ignoreIssue(issue.id)" title="Ignore">
                      <i class="fas fa-eye-slash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Logs Tab -->
    <div v-show="activeTab === 'logs'">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Sync Logs</h5>
        </div>
        <div class="card-body p-0">
          <div v-if="loading" class="text-center py-4">
            <div class="spinner-border" role="status"></div>
          </div>
          <div v-else-if="logs.data.length === 0" class="text-center text-muted py-4">
            No logs found
          </div>
          <table v-else class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Action</th>
                <th>Source</th>
                <th>Status</th>
                <th class="text-end">Processed</th>
                <th class="text-end">Created</th>
                <th class="text-end">Updated</th>
                <th class="text-end">Duration</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="log in logs.data" :key="log.id">
                <td>{{ log.action }}</td>
                <td>{{ log.source }}</td>
                <td>
                  <span class="badge" :class="getLogStatusClass(log.status)">{{ log.status }}</span>
                </td>
                <td class="text-end">{{ log.records_processed }}</td>
                <td class="text-end">{{ log.records_created }}</td>
                <td class="text-end">{{ log.records_updated }}</td>
                <td class="text-end">{{ log.duration_seconds ? log.duration_seconds + 's' : '-' }}</td>
                <td>{{ formatDate(log.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useSeoAdmin } from '@/composables/useSeoAdmin.js'
import { ref, onMounted, computed } from 'vue'

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

    const issueCount = computed(() => {
      return dashboard.value?.issue_stats?.open || 0
    })

    const loadData = async () => {
      await Promise.all([
        fetchDashboard(),
        fetchStats(),
        fetchPerformanceByType(parseInt(dateRange.value)),
        fetchCountries(parseInt(dateRange.value))
      ])
    }

    const refreshData = () => {
      loadData()
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
          alert(`Sync completed! Processed: ${result.processed}, Created: ${result.created}, Updated: ${result.updated}`)
          loadData()
        } else {
          alert('Sync failed: ' + (result.message || 'Unknown error'))
        }
      } catch (err) {
        alert('Sync failed: ' + err.message)
      } finally {
        syncing.value = false
      }
    }

    const testGscConnection = async () => {
      testing.value = true
      try {
        const result = await testConnection()
        if (result.success) {
          alert('Connection successful! Site found: ' + (result.site_found ? 'Yes' : 'No'))
        } else {
          alert('Connection failed: ' + result.message)
        }
      } catch (err) {
        alert('Connection test failed: ' + err.message)
      } finally {
        testing.value = false
      }
    }

    const resolveIssue = async (id) => {
      try {
        await updateIssue(id, 'resolve')
        loadIssues()
      } catch (err) {
        alert('Failed to resolve issue')
      }
    }

    const ignoreIssue = async (id) => {
      try {
        await updateIssue(id, 'ignore')
        loadIssues()
      } catch (err) {
        alert('Failed to ignore issue')
      }
    }

    // Formatting helpers
    const formatNumber = (num) => {
      return new Intl.NumberFormat().format(num || 0)
    }

    const formatDate = (date) => {
      if (!date) return '-'
      return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
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
      return types[type] || type
    }

    const getTrendClass = (change) => {
      if (!change) return ''
      return change.direction === 'up' ? 'text-success' : change.direction === 'down' ? 'text-danger' : 'text-muted'
    }

    const getTrendIcon = (change) => {
      if (!change) return ''
      return change.direction === 'up' ? 'fas fa-arrow-up' : change.direction === 'down' ? 'fas fa-arrow-down' : 'fas fa-minus'
    }

    const getTrendBadgeClass = (trend) => {
      if (trend === 'up') return 'badge bg-success'
      if (trend === 'down') return 'badge bg-danger'
      return 'badge bg-secondary'
    }

    const getTrendArrow = (trend) => {
      if (trend === 'up') return 'fas fa-arrow-up'
      if (trend === 'down') return 'fas fa-arrow-down'
      return 'fas fa-minus'
    }

    const getSeverityClass = (severity) => {
      if (severity === 'critical') return 'bg-danger'
      if (severity === 'warning') return 'bg-warning text-dark'
      return 'bg-info'
    }

    const getLogStatusClass = (status) => {
      if (status === 'completed') return 'bg-success'
      if (status === 'failed') return 'bg-danger'
      if (status === 'running') return 'bg-primary'
      return 'bg-secondary'
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

    onMounted(() => {
      loadData()
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
      performanceByType,
      countries,
      gscEnabled,
      activeTab,
      dateRange,
      syncing,
      testing,
      keywordSearch,
      pageTypeFilter,
      issueCount,
      refreshData,
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
      getTrendClass,
      getTrendIcon,
      getTrendBadgeClass,
      getTrendArrow,
      getSeverityClass,
      getLogStatusClass,
      getCountryFlag
    }
  }
}
</script>

<style scoped>
.seo-dashboard {
  padding: 1rem;
}

.stat-card {
  border-left-width: 4px;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.stat-icon i {
  font-size: 1.25rem;
}

.bg-primary { background-color: #007bff !important; }
.bg-info { background-color: #17a2b8 !important; }
.bg-success { background-color: #28a745 !important; }
.bg-warning { background-color: #ffc107 !important; }
.bg-danger { background-color: #dc3545 !important; }
.bg-secondary { background-color: #6c757d !important; }

.border-primary { border-color: #007bff !important; }
.border-info { border-color: #17a2b8 !important; }
.border-success { border-color: #28a745 !important; }
.border-warning { border-color: #ffc107 !important; }
.border-danger { border-color: #dc3545 !important; }
.border-secondary { border-color: #6c757d !important; }

.nav-tabs .nav-link {
  color: #495057;
}

.nav-tabs .nav-link.active {
  font-weight: 600;
}

.table th {
  font-weight: 600;
  font-size: 0.875rem;
  text-transform: uppercase;
  color: #6c757d;
}

.text-truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
