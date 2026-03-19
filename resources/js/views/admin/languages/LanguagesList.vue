<template>
  <div class="languages-page">
    <!-- Page Header -->
    <div class="page-header">
      <div class="page-header-left">
        <h1 class="page-title">
          <i class="fas fa-globe"></i>
          Languages
        </h1>
        <p class="page-subtitle">
          Manage translations and localization across {{ stats?.total || 0 }} languages
        </p>
      </div>
      <div class="page-header-right">
        <button class="btn-refresh" @click="loadStats" :disabled="loading">
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
          Refresh
        </button>
        <button class="btn-add" @click="showCreateModal = true">
          <i class="fas fa-plus"></i>
          Add Language
        </button>
      </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-container" v-if="stats">
      <div class="stat-card">
        <div class="stat-icon-circle stat-blue">
          <i class="fas fa-globe"></i>
        </div>
        <div class="stat-info">
          <div class="stat-label">TOTAL</div>
          <div class="stat-value">{{ stats.total }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-circle stat-green">
          <i class="fas fa-check"></i>
        </div>
        <div class="stat-info">
          <div class="stat-label">ACTIVE</div>
          <div class="stat-value">{{ stats.active }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-circle stat-purple">
          <i class="fas fa-percentage"></i>
        </div>
        <div class="stat-info">
          <div class="stat-label">AVG. COMPLETION</div>
          <div class="stat-value">{{ stats.avg_completion }}%</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-circle stat-orange">
          <i class="fas fa-exchange-alt"></i>
        </div>
        <div class="stat-info">
          <div class="stat-label">RTL</div>
          <div class="stat-value">{{ stats.rtl_count }}</div>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
      <div class="search-box">
        <i class="fas fa-search search-icon"></i>
        <input
          type="text"
          v-model="searchQuery"
          class="search-input"
          placeholder="Search languages..."
        >
        <span v-if="searchQuery" class="clear-search" @click="searchQuery = ''">
          <i class="fas fa-times"></i>
        </span>
      </div>
      <div class="filter-tabs">
        <button
          class="filter-tab"
          :class="{ active: filter === 'all' }"
          @click="filter = 'all'"
        >
          All <span class="tab-count">{{ allCount }}</span>
        </button>
        <button
          class="filter-tab"
          :class="{ active: filter === 'active' }"
          @click="filter = 'active'"
        >
          Active <span class="tab-count">{{ activeCount }}</span>
        </button>
        <button
          class="filter-tab"
          :class="{ active: filter === 'incomplete' }"
          @click="filter = 'incomplete'"
        >
          Incomplete <span class="tab-count">{{ incompleteCount }}</span>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !stats" class="loading-state">
      <div class="spinner"></div>
      <p>Loading languages...</p>
    </div>

    <!-- Languages Grid -->
    <div v-else class="languages-grid">
      <div
        v-for="lang in filteredLanguages"
        :key="lang.id"
        class="language-card"
        :class="{
          'card-source': lang.is_source,
          'card-inactive': !lang.is_active
        }"
      >
        <!-- Card Top -->
        <div class="card-top">
          <div class="card-top-left">
            <div class="lang-code">{{ lang.code.toUpperCase() }}</div>
            <div class="lang-names">
              <div class="lang-name">{{ lang.name }}</div>
              <div class="lang-native">{{ lang.native_name }} &middot; {{ lang.code }}</div>
            </div>
          </div>
          <div class="card-top-right">
            <span v-if="lang.direction === 'rtl'" class="rtl-badge">RTL</span>
            <div class="active-toggle" @click.stop="handleToggleActive(lang)">
              <div class="toggle-track" :class="{ on: lang.is_active }">
                <div class="toggle-thumb"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Source Language Info -->
        <div v-if="lang.is_source" class="source-info">
          <span class="source-badge">
            <i class="fas fa-star"></i> Source Language
          </span>
          <p class="source-description">
            All content is authored in {{ lang.name }} and translated to other languages.
          </p>
        </div>

        <!-- Translation Progress (non-source only) -->
        <div v-else class="card-progress">
          <!-- Overall Progress -->
          <div class="overall-progress">
            <div class="progress-header">
              <span class="progress-label">Translation Progress</span>
              <span class="progress-pct" :class="progressColor(lang.overall?.percentage)">
                {{ lang.overall?.percentage || 0 }}%
              </span>
            </div>
            <div class="progress-bar-track">
              <div
                class="progress-bar-fill"
                :class="progressColor(lang.overall?.percentage)"
                :style="{ width: (lang.overall?.percentage || 0) + '%' }"
              ></div>
            </div>
            <div class="progress-counts">
              <span>{{ lang.overall?.translated || 0 }} translated</span>
              <span>{{ lang.overall?.remaining || 0 }} remaining</span>
            </div>
          </div>

          <!-- Per-Model Breakdown -->
          <div class="model-breakdown" v-if="lang.models">
            <div
              v-for="(model, key) in lang.models"
              :key="key"
              class="model-row"
            >
              <div class="model-label">
                <i :class="modelIcon(key)"></i>
                <span>{{ model.label || formatModelKey(key) }}</span>
              </div>
              <div class="model-progress">
                <div class="model-bar-track">
                  <div
                    class="model-bar-fill"
                    :class="progressColor(model.percentage)"
                    :style="{ width: (model.percentage || 0) + '%' }"
                  ></div>
                </div>
                <span class="model-count">{{ model.translated }}/{{ model.total }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Card Footer -->
        <div class="card-footer">
          <button class="btn-edit" @click="handleEdit(lang)">
            <i class="fas fa-edit"></i> Edit
          </button>
          <button
            v-if="!lang.is_source && translatingLocale !== lang.code"
            class="btn-ai-translate"
            @click="handleAiTranslate(lang)"
          >
            <i class="fas fa-magic"></i> AI Translate
          </button>
          <template v-if="!lang.is_source && translatingLocale === lang.code">
            <button class="btn-ai-translate translating" disabled>
              <i class="fas fa-spinner fa-spin"></i>
              {{ translatingProgress !== null ? translatingProgress : '...' }}
            </button>
            <button class="btn-stop" @click="handleStopTranslate(lang)" title="Stop translation">
              <i class="fas fa-stop"></i>
            </button>
          </template>
          <button
            v-if="!lang.is_source && !lang.is_default"
            class="btn-delete"
            @click="handleDelete(lang)"
            title="Delete language"
          >
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredLanguages.length === 0" class="empty-state">
        <i class="fas fa-globe"></i>
        <h3>No Languages Found</h3>
        <p>Try adjusting your search or filters</p>
        <button class="btn-add" @click="searchQuery = ''; filter = 'all'">
          <i class="fas fa-redo"></i> Reset Filters
        </button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <LanguageFormModal
      v-if="showCreateModal || showEditModal"
      :language="selectedLanguage"
      :mode="showCreateModal ? 'create' : 'edit'"
      @close="closeModal"
      @saved="handleLanguageSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useLanguages } from '../../../composables/useLanguages'
import { useAutoTranslate } from '../../../composables/useAutoTranslate'
import LanguageFormModal from '../../../components/admin/languages/LanguageFormModal.vue'

const router = useRouter()
const { deleteLanguage, toggleActive } = useLanguages()
const { startTranslation, checkProgress, stopTranslation } = useAutoTranslate()

// State
const stats = ref(null)
const loading = ref(false)
const filter = ref('all')
const searchQuery = ref('')
const translatingLocale = ref(null)
const translatingProgress = ref(null)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedLanguage = ref(null)

let pollInterval = null

// Computed
const languages = computed(() => stats.value?.languages || [])

const allCount = computed(() => languages.value.length)

const activeCount = computed(() => languages.value.filter(l => l.is_active).length)

const incompleteCount = computed(() =>
  languages.value.filter(l => !l.is_source && l.overall && l.overall.percentage < 100).length
)

const filteredLanguages = computed(() => {
  let result = languages.value

  // Search filter
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(l =>
      l.name.toLowerCase().includes(q) ||
      l.native_name.toLowerCase().includes(q) ||
      l.code.toLowerCase().includes(q)
    )
  }

  // Tab filter
  if (filter.value === 'active') {
    result = result.filter(l => l.is_active)
  } else if (filter.value === 'incomplete') {
    result = result.filter(l => !l.is_source && l.overall && l.overall.percentage < 100)
  }

  return result
})

// Functions
const loadStats = async () => {
  loading.value = true
  try {
    const response = await fetch('/api/admin/languages/stats', {
      credentials: 'same-origin'
    })
    if (!response.ok) throw new Error('Failed to load stats')
    const data = await response.json()
    stats.value = data
  } catch (err) {
    console.error('Error loading language stats:', err)
  } finally {
    loading.value = false
  }
}

const progressColor = (pct) => {
  if (pct === undefined || pct === null) return 'progress-red'
  if (pct >= 100) return 'progress-green'
  if (pct >= 50) return 'progress-orange'
  return 'progress-red'
}

const modelIcon = (key) => {
  const icons = {
    ui_strings: 'fas fa-font',
    categories: 'fas fa-folder',
    subcategories: 'fas fa-folder-open',
    badge_types: 'fas fa-certificate',
    service_posts: 'fas fa-file-alt'
  }
  return icons[key] || 'fas fa-cube'
}

const formatModelKey = (key) => {
  return key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

const handleAiTranslate = async (lang) => {
  if (!confirm(`Start AI translation for "${lang.name}"? This will translate all untranslated content.`)) {
    return
  }

  translatingLocale.value = lang.code
  translatingProgress.value = 0

  try {
    await startTranslation(lang.code, 'all')

    // Start polling progress
    pollInterval = setInterval(async () => {
      const prog = await checkProgress(lang.code)
      if (prog !== null && typeof prog === 'object') {
        // Calculate real percentage from completed/total
        const completed = prog.completed || 0
        const total = prog.total || 0
        const pct = total > 0 && total < 999999 ? Math.round((completed / total) * 100) : (prog.percentage || 0)
        translatingProgress.value = pct > 0 ? pct : (completed > 0 ? `${completed} done` : 0)

        // Check if complete
        if (prog.status === 'completed' || prog.status === 'failed') {
          clearInterval(pollInterval)
          pollInterval = null
          translatingLocale.value = null
          translatingProgress.value = null
          if (prog.status === 'completed') {
            alert(`Translation completed! ${completed} items translated.`)
          }
          await loadStats()
        }
      }
    }, 3000)
  } catch (err) {
    console.error('Error starting AI translation:', err)
    alert('Failed to start AI translation: ' + err.message)
    translatingLocale.value = null
    translatingProgress.value = null
  }
}

const handleStopTranslate = async (lang) => {
  try {
    await stopTranslation(lang.code)
    if (pollInterval) {
      clearInterval(pollInterval)
      pollInterval = null
    }
    translatingLocale.value = null
    translatingProgress.value = null
    await loadStats()
  } catch (err) {
    alert('Failed to stop: ' + err.message)
  }
}

const handleToggleActive = async (lang) => {
  if (lang.is_default && lang.is_active) {
    alert('Cannot deactivate the default language')
    return
  }

  try {
    await toggleActive(lang.id)
    await loadStats()
  } catch (err) {
    alert(err.message || 'Failed to toggle status')
  }
}

const handleEdit = (lang) => {
  router.push({ name: 'languages.translations', params: { code: lang.code } })
}

const handleDelete = async (lang) => {
  if (lang.is_default || lang.is_source) {
    alert('Cannot delete the source/default language')
    return
  }
  if (!confirm(`Are you sure you want to delete "${lang.name}"? This will also delete all translations for this language. This action cannot be undone.`)) {
    return
  }
  try {
    await deleteLanguage(lang.id)
    await loadStats()
  } catch (err) {
    alert('Error deleting language: ' + (err.message || 'Unknown error'))
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  selectedLanguage.value = null
}

const handleLanguageSaved = async (eventData) => {
  closeModal()
  await loadStats()

  // If new language, offer immediate AI translate
  if (eventData && eventData.isNew && eventData.language) {
    const langCode = eventData.language.code
    const langName = eventData.language.name || langCode

    const doTranslate = confirm(
      `Language "${langName}" created!\n\nDo you want to auto-translate all content using AI?`
    )

    if (doTranslate) {
      // Find the lang in the refreshed stats
      const newLang = languages.value.find(l => l.code === langCode)
      if (newLang) {
        await handleAiTranslate(newLang)
      } else {
        try {
          await startTranslation(langCode, 'all')
          alert(`Auto-translation started for ${langName}.`)
          await loadStats()
        } catch (e) {
          alert('Failed to start auto-translation: ' + e.message)
        }
      }
    }
  }
}

// Check if any language has a running translation and resume polling
const checkRunningTranslations = async () => {
  if (!stats.value?.languages) return
  for (const lang of stats.value.languages) {
    if (lang.is_source) continue
    try {
      const prog = await checkProgress(lang.code)
      if (prog && typeof prog === 'object' && prog.status === 'running') {
        translatingLocale.value = lang.code
        const completed = prog.completed || 0
        const total = prog.total || 0
        translatingProgress.value = total > 0 && total < 999999
          ? Math.round((completed / total) * 100)
          : (completed > 0 ? `${completed} done` : 0)
        // Resume polling
        pollInterval = setInterval(async () => {
          const p = await checkProgress(lang.code)
          if (p && typeof p === 'object') {
            const c = p.completed || 0
            const t = p.total || 0
            translatingProgress.value = t > 0 && t < 999999 ? Math.round((c / t) * 100) : (c > 0 ? `${c} done` : 0)
            if (p.status === 'completed' || p.status === 'failed') {
              clearInterval(pollInterval)
              pollInterval = null
              translatingLocale.value = null
              translatingProgress.value = null
              if (p.status === 'completed') alert(`Translation completed! ${c} items translated.`)
              await loadStats()
            }
          }
        }, 3000)
        break // Only one translation at a time
      }
    } catch (_) {}
  }
}

// Lifecycle
onMounted(async () => {
  await loadStats()
  await checkRunningTranslations()
})

onBeforeUnmount(() => {
  if (pollInterval) {
    clearInterval(pollInterval)
    pollInterval = null
  }
})
</script>

<style scoped>
.languages-page {
  padding: 0;
}

/* ========== Page Header ========== */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.page-header-left {
  flex: 1;
  min-width: 250px;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 800;
  color: #1a1a2e;
  margin: 0 0 0.25rem 0;
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.page-title i {
  color: #667eea;
  font-size: 1.5rem;
}

.page-subtitle {
  color: #6b7280;
  font-size: 0.95rem;
  margin: 0;
}

.page-header-right {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.btn-refresh {
  padding: 0.65rem 1.25rem;
  border: 2px solid #e5e7eb;
  background: white;
  border-radius: 10px;
  font-size: 0.9rem;
  font-weight: 600;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-refresh:hover:not(:disabled) {
  border-color: #667eea;
  color: #667eea;
  background: #f0f4ff;
}

.btn-refresh:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-add {
  padding: 0.65rem 1.25rem;
  border: none;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 10px;
  font-size: 0.9rem;
  font-weight: 600;
  color: white;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-add:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.35);
}

/* ========== Stats Container ========== */
.stats-container {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0;
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
  margin-bottom: 1.5rem;
  overflow: hidden;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  position: relative;
}

.stat-card:not(:last-child)::after {
  content: '';
  position: absolute;
  right: 0;
  top: 20%;
  height: 60%;
  width: 1px;
  background: #eef2f7;
}

.stat-icon-circle {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  flex-shrink: 0;
}

.stat-icon-circle.stat-blue {
  background: #eff3ff;
  color: #667eea;
}

.stat-icon-circle.stat-green {
  background: #e8f5e9;
  color: #2e7d32;
}

.stat-icon-circle.stat-purple {
  background: #f3e8ff;
  color: #7c3aed;
}

.stat-icon-circle.stat-orange {
  background: #fff4e6;
  color: #e67e22;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-label {
  font-size: 0.7rem;
  font-weight: 700;
  color: #9ca3af;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1a1a2e;
  line-height: 1.2;
}

/* ========== Filter Bar ========== */
.filter-bar {
  background: white;
  padding: 1rem 1.25rem;
  border-radius: 16px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
  margin-bottom: 1.5rem;
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 240px;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  font-size: 0.9rem;
}

.search-input {
  width: 100%;
  padding: 0.7rem 2.5rem 0.7rem 2.75rem;
  border: 2px solid #eef2f7;
  border-radius: 10px;
  font-size: 0.9rem;
  transition: all 0.25s ease;
  background: #f9fafb;
}

.search-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
  background: white;
}

.clear-search {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  cursor: pointer;
  padding: 0.25rem;
}

.clear-search:hover {
  color: #ef4444;
}

.filter-tabs {
  display: flex;
  gap: 0.35rem;
  background: #f3f4f6;
  padding: 0.25rem;
  border-radius: 10px;
}

.filter-tab {
  padding: 0.55rem 1rem;
  border: none;
  background: transparent;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

.filter-tab:hover {
  color: #374151;
  background: rgba(255, 255, 255, 0.5);
}

.filter-tab.active {
  background: #10b981;
  color: white;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.tab-count {
  background: rgba(0, 0, 0, 0.1);
  padding: 0.1rem 0.45rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  min-width: 20px;
  text-align: center;
}

.filter-tab.active .tab-count {
  background: rgba(255, 255, 255, 0.25);
}

/* ========== Loading State ========== */
.loading-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
  color: #6b7280;
}

.spinner {
  width: 48px;
  height: 48px;
  margin: 0 auto 1rem;
  border: 4px solid #f3f4f6;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* ========== Languages Grid ========== */
.languages-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
  gap: 1.25rem;
}

/* ========== Language Card ========== */
.language-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.language-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
}

.language-card.card-source {
  background: linear-gradient(135deg, #f0f7ff 0%, #e8f0fe 100%);
  border: 1px solid #c3d9f7;
}

.language-card.card-inactive {
  opacity: 0.65;
}

/* Card Top */
.card-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.25rem 1.25rem 0.75rem;
}

.card-top-left {
  display: flex;
  align-items: center;
  gap: 0.85rem;
}

.lang-code {
  font-size: 1.5rem;
  font-weight: 900;
  color: #1a1a2e;
  letter-spacing: 1px;
  background: #f3f4f6;
  padding: 0.5rem 0.75rem;
  border-radius: 10px;
  line-height: 1;
  min-width: 52px;
  text-align: center;
}

.card-source .lang-code {
  background: #dbeafe;
  color: #1d4ed8;
}

.lang-names {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.lang-name {
  font-size: 1.05rem;
  font-weight: 700;
  color: #1a1a2e;
}

.lang-native {
  font-size: 0.82rem;
  color: #6b7280;
}

.card-top-right {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

/* RTL Badge */
.rtl-badge {
  background: #fef3c7;
  color: #d97706;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 20px;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

/* Active Toggle */
.active-toggle {
  cursor: pointer;
}

.toggle-track {
  width: 40px;
  height: 22px;
  border-radius: 11px;
  background: #d1d5db;
  position: relative;
  transition: background 0.25s ease;
}

.toggle-track.on {
  background: #10b981;
}

.toggle-thumb {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: white;
  position: absolute;
  top: 2px;
  left: 2px;
  transition: transform 0.25s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
}

.toggle-track.on .toggle-thumb {
  transform: translateX(18px);
}

/* Source Language Info */
.source-info {
  padding: 0 1.25rem 1rem;
}

.source-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: #dbeafe;
  color: #1d4ed8;
  font-size: 0.78rem;
  font-weight: 700;
  padding: 0.3rem 0.75rem;
  border-radius: 20px;
  margin-bottom: 0.5rem;
}

.source-badge i {
  color: #f59e0b;
  font-size: 0.7rem;
}

.source-description {
  font-size: 0.85rem;
  color: #6b7280;
  margin: 0;
  line-height: 1.5;
}

/* Card Progress */
.card-progress {
  padding: 0 1.25rem 0.75rem;
  flex: 1;
}

/* Overall Progress */
.overall-progress {
  margin-bottom: 0.75rem;
}

.progress-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.35rem;
}

.progress-label {
  font-size: 0.82rem;
  font-weight: 600;
  color: #374151;
}

.progress-pct {
  font-size: 0.85rem;
  font-weight: 800;
}

.progress-pct.progress-green { color: #10b981; }
.progress-pct.progress-orange { color: #f59e0b; }
.progress-pct.progress-red { color: #ef4444; }

.progress-bar-track {
  width: 100%;
  height: 8px;
  background: #f3f4f6;
  border-radius: 4px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.5s ease;
}

.progress-bar-fill.progress-green { background: #10b981; }
.progress-bar-fill.progress-orange { background: #f59e0b; }
.progress-bar-fill.progress-red { background: #ef4444; }

.progress-counts {
  display: flex;
  justify-content: space-between;
  margin-top: 0.3rem;
  font-size: 0.75rem;
  color: #9ca3af;
}

/* Model Breakdown */
.model-breakdown {
  border-top: 1px solid #f3f4f6;
  padding-top: 0.6rem;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.model-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  justify-content: space-between;
}

.model-label {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.78rem;
  color: #6b7280;
  min-width: 130px;
  flex-shrink: 0;
}

.model-label i {
  width: 16px;
  text-align: center;
  color: #9ca3af;
  font-size: 0.72rem;
}

.model-progress {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex: 1;
}

.model-bar-track {
  flex: 1;
  height: 5px;
  background: #f3f4f6;
  border-radius: 3px;
  overflow: hidden;
  min-width: 60px;
}

.model-bar-fill {
  height: 100%;
  border-radius: 3px;
  transition: width 0.4s ease;
}

.model-bar-fill.progress-green { background: #10b981; }
.model-bar-fill.progress-orange { background: #f59e0b; }
.model-bar-fill.progress-red { background: #ef4444; }

.model-count {
  font-size: 0.72rem;
  color: #9ca3af;
  font-weight: 600;
  min-width: 50px;
  text-align: right;
  white-space: nowrap;
}

/* Card Footer */
.card-footer {
  padding: 0.75rem 1.25rem;
  border-top: 1px solid #f3f4f6;
  display: flex;
  gap: 0.6rem;
  margin-top: auto;
  background: #fafbfc;
}

.btn-edit {
  flex: 1;
  padding: 0.55rem 0.75rem;
  border: 2px solid #e5e7eb;
  background: white;
  border-radius: 8px;
  font-size: 0.82rem;
  font-weight: 600;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
}

.btn-edit:hover {
  border-color: #667eea;
  color: #667eea;
  background: #f0f4ff;
}

.btn-ai-translate {
  flex: 1;
  padding: 0.55rem 0.75rem;
  border: none;
  background: linear-gradient(135deg, #7c3aed 0%, #10b981 100%);
  border-radius: 8px;
  font-size: 0.82rem;
  font-weight: 600;
  color: white;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
}

.btn-ai-translate:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(124, 58, 237, 0.3);
}

.btn-ai-translate.translating {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  opacity: 0.9;
}

.btn-stop {
  padding: 0.55rem 0.65rem;
  border: 2px solid #fecaca;
  background: white;
  border-radius: 8px;
  font-size: 0.82rem;
  color: #ef4444;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-stop:hover {
  background: #fef2f2;
  border-color: #ef4444;
}

.btn-ai-translate:disabled {
  opacity: 0.8;
  cursor: not-allowed;
}

.btn-delete {
  padding: 0.55rem 0.65rem;
  border: 2px solid #fecaca;
  background: white;
  border-radius: 8px;
  font-size: 0.82rem;
  color: #ef4444;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-delete:hover {
  background: #fef2f2;
  border-color: #ef4444;
}

/* ========== Empty State ========== */
.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
}

.empty-state i {
  font-size: 3.5rem;
  color: #d1d5db;
  margin-bottom: 1rem;
}

.empty-state h3 {
  color: #374151;
  margin: 0 0 0.5rem 0;
}

.empty-state p {
  color: #9ca3af;
  margin: 0 0 1.25rem 0;
}

/* ========== Responsive ========== */
@media (max-width: 1024px) {
  .stats-container {
    grid-template-columns: repeat(2, 1fr);
  }

  .stat-card:nth-child(2)::after {
    display: none;
  }

  .stat-card:nth-child(2) {
    border-right: none;
  }

  .stat-card:nth-child(1),
  .stat-card:nth-child(2) {
    border-bottom: 1px solid #eef2f7;
  }
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
  }

  .page-header-right {
    width: 100%;
  }

  .btn-refresh, .btn-add {
    flex: 1;
    justify-content: center;
  }

  .stats-container {
    grid-template-columns: repeat(2, 1fr);
  }

  .filter-bar {
    flex-direction: column;
    align-items: stretch;
  }

  .search-box {
    min-width: unset;
  }

  .filter-tabs {
    justify-content: center;
  }

  .languages-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .stats-container {
    grid-template-columns: 1fr;
  }

  .stat-card::after {
    display: none !important;
  }

  .stat-card {
    border-bottom: 1px solid #eef2f7;
  }

  .stat-card:last-child {
    border-bottom: none;
  }

  .card-top {
    flex-direction: column;
    gap: 0.75rem;
  }

  .card-top-right {
    align-self: flex-end;
  }
}
</style>
