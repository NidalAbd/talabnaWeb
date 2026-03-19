<template>
  <div class="auto-translate-dashboard">
    <!-- Header -->
    <div class="page-header-section">
      <h2 class="page-title"><i class="fas fa-language mr-2"></i>Auto-Translation (OpenAI)</h2>
      <p class="page-subtitle">
        3-tier translation system powered by GPT-4o-mini. Tier 1 translates UI strings, Tier 2 handles core content (categories, subcategories, badges), and Tier 3 progressively translates service posts.
      </p>
    </div>

    <!-- Language Selector -->
    <div class="search-filter-bar">
      <div class="filter-group" style="flex: 1;">
        <label class="filter-label"><i class="fas fa-globe mr-1"></i> Target Language</label>
        <select v-model="selectedLocale" class="filter-select" style="min-width: 280px;">
          <option value="">-- Select Language --</option>
          <option
            v-for="lang in activeLanguages"
            :key="lang.code"
            :value="lang.code"
          >
            {{ lang.name }} ({{ lang.native_name }}) — {{ lang.code }}
          </option>
        </select>
      </div>
    </div>

    <!-- 3-Tier Explanation Cards -->
    <div class="stats-dashboard">
      <div class="stats-grid">
        <div class="stat-card-compact stat-blue">
          <div class="stat-icon"><i class="fas fa-font"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">Tier 1</div>
            <div class="stat-label-compact">UI Strings</div>
            <div class="stat-desc">Fast — translates interface labels, buttons, messages. Activates the language.</div>
          </div>
        </div>
        <div class="stat-card-compact stat-orange">
          <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">Tier 2</div>
            <div class="stat-label-compact">Core Content</div>
            <div class="stat-desc">Categories, subcategories, badge types. Medium speed.</div>
          </div>
        </div>
        <div class="stat-card-compact stat-purple">
          <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">Tier 3</div>
            <div class="stat-label-compact">Service Posts</div>
            <div class="stat-desc">Large dataset — progressive translation of all service posts with configurable limits.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons Row -->
    <div class="action-buttons-row">
      <button
        class="action-btn primary"
        :disabled="!selectedLocale || loading"
        @click="handleTranslate('ui')"
      >
        <i class="fas fa-font mr-1"></i>
        {{ loading ? 'Working...' : 'Translate Tier 1 (UI)' }}
      </button>
      <button
        class="action-btn warning"
        :disabled="!selectedLocale || loading"
        @click="handleTranslate('core')"
      >
        <i class="fas fa-layer-group mr-1"></i>
        {{ loading ? 'Working...' : 'Translate Tier 2 (Core)' }}
      </button>
      <div class="tier3-group">
        <button
          class="action-btn info"
          :disabled="!selectedLocale || loading"
          @click="handleTranslate('posts')"
        >
          <i class="fas fa-file-alt mr-1"></i>
          {{ loading ? 'Working...' : 'Translate Tier 3 (Posts)' }}
        </button>
        <div class="limit-input-wrap">
          <label class="limit-label">Limit:</label>
          <input
            v-model.number="tier3Limit"
            type="number"
            class="limit-input"
            min="1"
            max="5000"
            placeholder="50"
          >
        </div>
      </div>
      <button
        class="action-btn success"
        :disabled="!selectedLocale || loading"
        @click="handleTranslate('all')"
      >
        <i class="fas fa-globe mr-1"></i>
        {{ loading ? 'Working...' : 'Translate All Tiers' }}
      </button>
    </div>

    <!-- Error Display -->
    <div v-if="error" class="error-banner">
      <i class="fas fa-exclamation-triangle mr-1"></i>
      {{ error }}
      <button class="error-dismiss" @click="error = null"><i class="fas fa-times"></i></button>
    </div>

    <!-- Progress Section -->
    <div v-if="progress" class="section-card progress-section">
      <h3 class="section-title">
        <i class="fas fa-tasks mr-2"></i>
        Translation Progress
        <span class="status-badge-inline" :class="'status-' + progress.status">
          {{ progress.status }}
        </span>
      </h3>
      <div class="progress-body">
        <div class="progress-bar-wrapper">
          <div class="progress-bar">
            <div
              class="progress-fill"
              :class="progressBarClass"
              :style="{ width: progressPercent + '%' }"
            ></div>
          </div>
          <span class="progress-pct">{{ progressPercent }}%</span>
        </div>

        <div class="progress-stats-row">
          <div class="progress-stat">
            <span class="progress-stat-value">{{ progress.completed_posts || progress.completed || 0 }}</span>
            <span class="progress-stat-label">Completed</span>
          </div>
          <div class="progress-stat">
            <span class="progress-stat-value">{{ progress.total_posts || progress.total || 0 }}</span>
            <span class="progress-stat-label">Total</span>
          </div>
          <div class="progress-stat" v-if="progress.errors_count || progress.errors">
            <span class="progress-stat-value text-danger">{{ progress.errors_count || (Array.isArray(progress.errors) ? progress.errors.length : 0) }}</span>
            <span class="progress-stat-label">Errors</span>
          </div>
        </div>

        <div v-if="progress.current_item" class="progress-current-task">
          <i class="fas fa-cog fa-spin mr-1"></i>
          {{ progress.current_item }}
        </div>
      </div>
    </div>

    <!-- Separator -->
    <hr class="section-divider">

    <!-- On-Demand Translation Section -->
    <div class="section-card on-demand-section">
      <h3 class="section-title">
        <i class="fas fa-bolt mr-2"></i>
        On-Demand Translation
      </h3>
      <p class="section-desc">Translate a single service post by ID into a specific language.</p>
      <div class="on-demand-form">
        <div class="on-demand-field">
          <label class="on-demand-label">Post ID</label>
          <input
            v-model.number="onDemandPostId"
            type="number"
            class="form-control"
            placeholder="Enter Post ID"
            min="1"
          >
        </div>
        <div class="on-demand-field">
          <label class="on-demand-label">Language</label>
          <select v-model="onDemandLocale" class="filter-select">
            <option value="">-- Select --</option>
            <option
              v-for="lang in activeLanguages"
              :key="'od-' + lang.code"
              :value="lang.code"
            >
              {{ lang.name }} ({{ lang.code }})
            </option>
          </select>
        </div>
        <div class="on-demand-field on-demand-action">
          <button
            class="action-btn primary"
            :disabled="!onDemandPostId || !onDemandLocale || loading"
            @click="handleOnDemand"
          >
            <i class="fas fa-bolt mr-1"></i>
            {{ loading ? 'Translating...' : 'Translate' }}
          </button>
        </div>
      </div>
      <div v-if="onDemandResult" class="on-demand-result">
        <i class="fas fa-check-circle text-success mr-1"></i>
        {{ onDemandResult }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useAutoTranslate } from '../../composables/useAutoTranslate'
import { useLanguages } from '../../composables/useLanguages'

const {
  progress,
  loading,
  error,
  startTranslation,
  checkProgress,
  translateOnDemand
} = useAutoTranslate()

const { languages, fetchLanguages } = useLanguages()

const selectedLocale = ref('')
const tier3Limit = ref(50)
const onDemandPostId = ref(null)
const onDemandLocale = ref('')
const onDemandResult = ref(null)

let pollInterval = null

const activeLanguages = computed(() => {
  if (!languages.value?.data) return []
  return languages.value.data.filter(l => l.is_active)
})

const progressPercent = computed(() => {
  if (!progress.value) return 0
  const completed = progress.value.completed_posts || progress.value.completed || 0
  const total = progress.value.total_posts || progress.value.total || 0
  if (total === 0) return 0
  return Math.round((completed / total) * 100)
})

const progressBarClass = computed(() => {
  if (!progress.value) return ''
  if (progress.value.status === 'running') return 'fill-running'
  if (progress.value.status === 'completed' || progress.value.status === 'finished') return 'fill-completed'
  if (progress.value.status === 'failed') return 'fill-failed'
  return ''
})

onMounted(async () => {
  await fetchLanguages({ per_page: 100, status: 'active' })
})

onBeforeUnmount(() => {
  if (pollInterval) clearInterval(pollInterval)
})

watch(() => progress.value?.status, (newStatus) => {
  if (newStatus === 'completed' || newStatus === 'finished' || newStatus === 'failed') {
    stopPolling()
    if (newStatus === 'completed' || newStatus === 'finished') {
      alert('Translation completed successfully!')
    } else {
      alert('Translation failed. Check errors above.')
    }
  }
})

function startPolling() {
  if (pollInterval) clearInterval(pollInterval)
  pollInterval = setInterval(async () => {
    if (selectedLocale.value) {
      await checkProgress(selectedLocale.value)
    }
  }, 3000)
}

function stopPolling() {
  if (pollInterval) {
    clearInterval(pollInterval)
    pollInterval = null
  }
}

async function handleTranslate(tier) {
  if (!selectedLocale.value) return
  onDemandResult.value = null
  try {
    const limit = tier === 'posts' || tier === 'all' ? tier3Limit.value : null
    await startTranslation(selectedLocale.value, tier, limit)
    startPolling()
    await checkProgress(selectedLocale.value)
  } catch (e) {
    alert('Error starting translation: ' + e.message)
  }
}

async function handleOnDemand() {
  if (!onDemandPostId.value || !onDemandLocale.value) return
  onDemandResult.value = null
  try {
    const result = await translateOnDemand(onDemandPostId.value, onDemandLocale.value)
    onDemandResult.value = result.message || `Post #${onDemandPostId.value} translated to ${onDemandLocale.value} successfully.`
  } catch (e) {
    alert('Error: ' + e.message)
  }
}

function formatNumber(n) {
  if (n === null || n === undefined) return '0'
  return Number(n).toLocaleString()
}
</script>

<style scoped>
.auto-translate-dashboard {
  padding: 0;
}

/* Page Header */
.page-header-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.25rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.page-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1a1a2e;
  margin: 0 0 0.5rem 0;
}

.page-subtitle {
  color: #6b7280;
  font-size: 0.9rem;
  margin: 0;
  line-height: 1.5;
}

/* Search Filter Bar */
.search-filter-bar {
  background: white;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  margin-bottom: 1.25rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.filter-label {
  font-weight: 600;
  color: #374151;
  white-space: nowrap;
  margin: 0;
}

.filter-select {
  padding: 0.5rem 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.875rem;
  color: #374151;
  background: white;
  outline: none;
  transition: border-color 0.2s;
}

.filter-select:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Stats Dashboard */
.stats-dashboard {
  margin-bottom: 1.25rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.stat-card-compact {
  background: white;
  border-radius: 12px;
  padding: 1.25rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  border-left: 4px solid #e5e7eb;
  transition: all 0.2s ease;
}

.stat-card-compact:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.stat-card-compact.stat-blue {
  border-left-color: #3b82f6;
}

.stat-card-compact.stat-orange {
  border-left-color: #f59e0b;
}

.stat-card-compact.stat-purple {
  border-left-color: #7c3aed;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.stat-blue .stat-icon {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}

.stat-orange .stat-icon {
  background: rgba(245, 158, 11, 0.1);
  color: #f59e0b;
}

.stat-purple .stat-icon {
  background: rgba(124, 58, 237, 0.1);
  color: #7c3aed;
}

.stat-info {
  flex: 1;
}

.stat-value-compact {
  font-size: 1.1rem;
  font-weight: 800;
  color: #1a1a2e;
}

.stat-label-compact {
  font-size: 0.85rem;
  font-weight: 600;
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.stat-desc {
  font-size: 0.78rem;
  color: #9ca3af;
  line-height: 1.4;
}

/* Action Buttons Row */
.action-buttons-row {
  background: white;
  border-radius: 12px;
  padding: 1.25rem;
  margin-bottom: 1.25rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.6rem 1.25rem;
  border: none;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.action-btn.primary {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
}

.action-btn.primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  transform: translateY(-1px);
}

.action-btn.success {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
}

.action-btn.success:hover:not(:disabled) {
  background: linear-gradient(135deg, #16a34a, #15803d);
  transform: translateY(-1px);
}

.action-btn.warning {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
}

.action-btn.warning:hover:not(:disabled) {
  background: linear-gradient(135deg, #d97706, #b45309);
  transform: translateY(-1px);
}

.action-btn.info {
  background: linear-gradient(135deg, #7c3aed, #6d28d9);
  color: white;
}

.action-btn.info:hover:not(:disabled) {
  background: linear-gradient(135deg, #6d28d9, #5b21b6);
  transform: translateY(-1px);
}

.tier3-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.limit-input-wrap {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.limit-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #6b7280;
  margin: 0;
}

.limit-input {
  width: 70px;
  padding: 0.45rem 0.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.85rem;
  color: #374151;
  text-align: center;
  outline: none;
}

.limit-input:focus {
  border-color: #7c3aed;
  box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
}

/* Error Banner */
.error-banner {
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 10px;
  padding: 0.75rem 1.25rem;
  margin-bottom: 1.25rem;
  color: #991b1b;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.error-dismiss {
  margin-left: auto;
  background: none;
  border: none;
  color: #991b1b;
  cursor: pointer;
  padding: 0.25rem;
}

/* Section Card */
.section-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 1.25rem;
  overflow: hidden;
}

.section-title {
  display: flex;
  align-items: center;
  padding: 1rem 1.25rem;
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: #1a1a2e;
  border-bottom: 1px solid #f0f0f0;
}

.section-desc {
  padding: 0 1.25rem;
  margin: 0.5rem 0 0 0;
  font-size: 0.85rem;
  color: #6b7280;
}

/* Progress Section */
.progress-section {
  border-left: 4px solid #3b82f6;
}

.progress-body {
  padding: 1.25rem;
}

.progress-bar-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.progress-bar {
  flex: 1;
  height: 12px;
  background: #e5e7eb;
  border-radius: 6px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: 6px;
  transition: width 0.5s ease;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.progress-fill.fill-running {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  animation: pulse-fill 2s infinite;
}

.progress-fill.fill-completed {
  background: linear-gradient(135deg, #22c55e, #16a34a);
}

.progress-fill.fill-failed {
  background: linear-gradient(135deg, #ef4444, #dc2626);
}

@keyframes pulse-fill {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

.progress-pct {
  font-weight: 700;
  font-size: 0.95rem;
  color: #374151;
  min-width: 3.5rem;
  text-align: right;
}

.progress-stats-row {
  display: flex;
  gap: 2rem;
  margin-bottom: 0.75rem;
}

.progress-stat {
  display: flex;
  flex-direction: column;
}

.progress-stat-value {
  font-size: 1.25rem;
  font-weight: 800;
  color: #1a1a2e;
}

.progress-stat-value.text-danger {
  color: #ef4444;
}

.progress-stat-label {
  font-size: 0.78rem;
  color: #6b7280;
}

.progress-current-task {
  font-size: 0.85rem;
  color: #6b7280;
  font-style: italic;
  padding: 0.5rem 0.75rem;
  background: #f8f9fa;
  border-radius: 8px;
}

/* Status Badges */
.status-badge-inline {
  display: inline-flex;
  align-items: center;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  margin-left: auto;
  text-transform: capitalize;
}

.status-running {
  background: #dbeafe;
  color: #1d4ed8;
  animation: badge-pulse 2s infinite;
}

.status-completed,
.status-finished {
  background: #dcfce7;
  color: #166534;
}

.status-failed {
  background: #fef2f2;
  color: #991b1b;
}

@keyframes badge-pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.6; }
}

/* Separator */
.section-divider {
  border: none;
  border-top: 2px dashed #e5e7eb;
  margin: 1.5rem 0;
}

/* On-Demand Section */
.on-demand-section {
  border-left: 4px solid #f59e0b;
}

.on-demand-form {
  display: flex;
  align-items: flex-end;
  gap: 1rem;
  padding: 1rem 1.25rem 1.25rem;
  flex-wrap: wrap;
}

.on-demand-field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.on-demand-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #374151;
  margin: 0;
}

.on-demand-field .form-control {
  padding: 0.5rem 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.875rem;
  color: #374151;
  outline: none;
  width: 160px;
}

.on-demand-field .form-control:focus {
  border-color: #f59e0b;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

.on-demand-field .filter-select {
  min-width: 200px;
}

.on-demand-action {
  justify-content: flex-end;
}

.on-demand-result {
  padding: 0.75rem 1.25rem;
  font-size: 0.875rem;
  color: #166534;
  background: #f0fdf4;
  border-top: 1px solid #dcfce7;
}

/* Responsive */
@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }

  .action-buttons-row {
    flex-direction: column;
    align-items: stretch;
  }

  .action-btn {
    justify-content: center;
  }

  .tier3-group {
    flex-direction: column;
    align-items: stretch;
  }
}

@media (max-width: 768px) {
  .on-demand-form {
    flex-direction: column;
    align-items: stretch;
  }

  .on-demand-field .form-control,
  .on-demand-field .filter-select {
    width: 100%;
    min-width: unset;
  }

  .filter-group {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-select {
    min-width: unset !important;
  }

  .progress-stats-row {
    gap: 1rem;
  }
}
</style>
