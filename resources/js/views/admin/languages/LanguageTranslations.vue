<template>
  <div class="translations-page">
    <!-- Page Header -->
    <div class="trans-header">
      <button class="btn-back" @click="router.back()" title="Go back">
        <i class="fas fa-arrow-left"></i>
      </button>
      <div class="header-lang-badge">{{ langCode.toUpperCase() }}</div>
      <div class="header-info">
        <h1 class="header-title">{{ langInfo?.name || '...' }} Translations</h1>
        <p class="header-subtitle">
          {{ langInfo?.native_name || '' }} &middot; {{ langCode }}
        </p>
      </div>
      <div class="header-badges">
        <span v-if="langInfo?.direction === 'rtl'" class="rtl-badge">RTL</span>
      </div>
      <div class="header-actions">
        <button
          class="btn-ai-all"
          :disabled="translatingAll"
          @click="handleAiTranslateAll"
        >
          <i :class="translatingAll ? 'fas fa-spinner fa-spin' : 'fas fa-magic'"></i>
          {{ translatingAll ? 'Translating...' : 'AI Translate All' }}
        </button>
        <button
          class="btn-save"
          :disabled="saving || pendingChangesCount === 0"
          @click="handleSaveAll"
        >
          <i :class="saving ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
          Save {{ pendingChangesCount > 0 ? `(${pendingChangesCount})` : '' }}
        </button>
      </div>
    </div>

    <!-- Category Tabs -->
    <div class="category-tabs">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        class="category-tab"
        :class="{ active: activeTab === tab.key }"
        @click="switchTab(tab.key)"
      >
        <i :class="tab.icon"></i>
        <span class="tab-label">{{ tab.label }}</span>
        <span
          class="tab-pct-badge"
          :class="pctBadgeClass(tab)"
        >{{ tabPercent(tab) }}%</span>
      </button>
    </div>

    <!-- Content Area -->
    <div class="content-area">
      <!-- Sub-header -->
      <div class="content-subheader">
        <div class="subheader-left">
          <h2 class="subheader-title">
            <i :class="currentTab?.icon"></i>
            {{ currentTab?.label || '' }}
          </h2>
          <span class="subheader-count">{{ tabTranslated }}/{{ tabTotal }} translated</span>
        </div>
        <div class="subheader-right">
          <button
            class="btn-ai-tab"
            :disabled="translatingTab"
            @click="handleAiTranslateTab"
          >
            <i :class="translatingTab ? 'fas fa-spinner fa-spin' : 'fas fa-magic'"></i>
            AI Translate
          </button>
          <div class="filter-pills">
            <button
              class="filter-pill"
              :class="{ active: tabFilter === 'all' }"
              @click="setFilter('all')"
            >All</button>
            <button
              class="filter-pill"
              :class="{ active: tabFilter === 'missing' }"
              @click="setFilter('missing')"
            >Missing</button>
            <button
              class="filter-pill"
              :class="{ active: tabFilter === 'done' }"
              @click="setFilter('done')"
            >Done</button>
          </div>
          <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input
              type="text"
              v-model="searchInput"
              class="search-input"
              placeholder="Search..."
              @input="debouncedSearch"
            >
            <span v-if="searchInput" class="clear-search" @click="clearSearch">
              <i class="fas fa-times"></i>
            </span>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="tabLoading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading translations...</p>
      </div>

      <!-- Items List -->
      <div v-else-if="items.length > 0" class="items-list">
        <div
          v-for="(item, idx) in items"
          :key="`${activeTab}-${item.id}`"
          class="item-card"
        >
          <!-- Item Header -->
          <div class="item-header">
            <span class="item-number">#{{ (tabPage - 1) * perPage + idx + 1 }}</span>
            <span class="item-label">{{ item.label }}</span>
            <span
              class="item-status"
              :class="itemFullyTranslated(item) ? 'status-done' : 'status-missing'"
            >
              <i :class="itemFullyTranslated(item) ? 'fas fa-check-circle' : 'fas fa-circle'"></i>
            </span>
          </div>

          <!-- Fields -->
          <div
            v-for="field in item.fields"
            :key="field.field"
            class="field-pair"
          >
            <div class="field-col field-source">
              <div class="field-label-badge source-badge-label">
                <span class="lang-indicator">{{ sourceLangCode }}</span> SOURCE
                <span v-if="field.field !== 'key'" class="field-name-tag">{{ field.field }}</span>
              </div>
              <textarea
                class="field-textarea"
                :value="field.source"
                readonly
                :dir="sourceDir"
                rows="2"
              ></textarea>
            </div>
            <div class="field-col field-target">
              <div class="field-label-badge target-badge-label">
                <span class="lang-indicator">{{ langCode.toUpperCase() }}</span> TRANSLATION
                <span v-if="field.field !== 'key'" class="field-name-tag">{{ field.field }}</span>
              </div>
              <textarea
                class="field-textarea editable"
                :value="getFieldValue(item, field)"
                @input="handleFieldInput(item, field, $event)"
                :dir="targetDir"
                rows="2"
                :placeholder="'Enter ' + langCode.toUpperCase() + ' translation...'"
              ></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <i class="fas fa-language"></i>
        <h3>No Items Found</h3>
        <p>Try adjusting your search or filter</p>
      </div>

      <!-- Pagination -->
      <div v-if="tabLastPage > 1" class="pagination-bar">
        <button
          class="page-btn"
          :disabled="tabPage <= 1"
          @click="goToPage(tabPage - 1)"
        >
          <i class="fas fa-chevron-left"></i>
        </button>
        <div class="page-info">
          Page {{ tabPage }} of {{ tabLastPage }}
          <span class="page-total">({{ tabTotalItems }} items)</span>
        </div>
        <button
          class="page-btn"
          :disabled="tabPage >= tabLastPage"
          @click="goToPage(tabPage + 1)"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Toast Notification -->
    <transition name="toast-fade">
      <div v-if="toast.show" class="toast-notification" :class="'toast-' + toast.type">
        <i :class="toast.icon"></i>
        <span>{{ toast.message }}</span>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const langCode = computed(() => route.params.code || '')

// State
const langInfo = ref(null)
const activeTab = ref('ui_strings')
const tabFilter = ref('all')
const searchQuery = ref('')
const searchInput = ref('')
const items = ref([])
const tabLoading = ref(false)
const tabPage = ref(1)
const tabLastPage = ref(1)
const tabTotalItems = ref(0)
const perPage = 20
const pendingChanges = ref(new Map())
const saving = ref(false)
const translatingAll = ref(false)
const translatingTab = ref(false)
const sourceLangCode = ref('AR')
const sourceDir = ref('rtl')

// Toast
const toast = ref({ show: false, message: '', type: 'success', icon: 'fas fa-check-circle' })

let searchTimeout = null

// Tab definitions
const tabs = ref([
  { key: 'ui_strings', label: 'UI Strings', icon: 'fas fa-font', translated: 0, total: 0 },
  { key: 'categories', label: 'Categories', icon: 'fas fa-th-large', translated: 0, total: 0 },
  { key: 'sub_categories', label: 'Subcategories', icon: 'fas fa-sitemap', translated: 0, total: 0 },
  { key: 'badge_types', label: 'Badge Types', icon: 'fas fa-certificate', translated: 0, total: 0 },
  { key: 'service_posts', label: 'Service Posts', icon: 'fas fa-briefcase', translated: 0, total: 0 },
])

// Computed
const currentTab = computed(() => tabs.value.find(t => t.key === activeTab.value))

const tabTranslated = computed(() => currentTab.value?.translated || 0)
const tabTotal = computed(() => currentTab.value?.total || 0)

const targetDir = computed(() => langInfo.value?.direction === 'rtl' ? 'rtl' : 'ltr')

const pendingChangesCount = computed(() => pendingChanges.value.size)

// CSRF headers helper
const csrfHeaders = () => {
  const token = document.querySelector('meta[name="csrf-token"]')
  return {
    'X-CSRF-TOKEN': token ? token.content : '',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
}

// Functions
const showToast = (message, type = 'success') => {
  const icons = {
    success: 'fas fa-check-circle',
    error: 'fas fa-exclamation-circle',
    info: 'fas fa-info-circle'
  }
  toast.value = { show: true, message, type, icon: icons[type] || icons.info }
  setTimeout(() => { toast.value.show = false }, 3500)
}

const tabPercent = (tab) => {
  if (!tab.total) return 0
  return Math.round((tab.translated / tab.total) * 100)
}

const pctBadgeClass = (tab) => {
  const pct = tabPercent(tab)
  if (pct >= 100) return 'pct-green'
  if (pct >= 50) return 'pct-orange'
  return 'pct-red'
}

const itemFullyTranslated = (item) => {
  return item.fields.every(f => {
    const changeKey = `${activeTab.value}:${item.id}:${f.field}`
    if (pendingChanges.value.has(changeKey)) {
      return pendingChanges.value.get(changeKey).trim() !== ''
    }
    return f.is_translated
  })
}

const getFieldValue = (item, field) => {
  const changeKey = `${activeTab.value}:${item.id}:${field.field}`
  if (pendingChanges.value.has(changeKey)) {
    return pendingChanges.value.get(changeKey)
  }
  return field.translation || ''
}

const handleFieldInput = (item, field, event) => {
  const changeKey = `${activeTab.value}:${item.id}:${field.field}`
  const newVal = event.target.value
  // Only track if different from original
  if (newVal === (field.translation || '')) {
    pendingChanges.value.delete(changeKey)
  } else {
    pendingChanges.value.set(changeKey, newVal)
  }
  // Force reactivity
  pendingChanges.value = new Map(pendingChanges.value)
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    searchQuery.value = searchInput.value
    tabPage.value = 1
    loadTabContent()
  }, 400)
}

const clearSearch = () => {
  searchInput.value = ''
  searchQuery.value = ''
  tabPage.value = 1
  loadTabContent()
}

const setFilter = (f) => {
  tabFilter.value = f
  tabPage.value = 1
  loadTabContent()
}

const switchTab = (key) => {
  activeTab.value = key
  tabFilter.value = 'all'
  searchQuery.value = ''
  searchInput.value = ''
  tabPage.value = 1
  loadTabContent()
}

const goToPage = (page) => {
  tabPage.value = page
  loadTabContent()
}

// Load language info from stats
const loadLangInfo = async () => {
  try {
    const response = await fetch('/api/admin/languages/stats', {
      credentials: 'same-origin'
    })
    if (!response.ok) throw new Error('Failed to load language stats')
    const data = await response.json()

    // Find our language
    const lang = data.languages?.find(l => l.code === langCode.value)
    if (lang) {
      langInfo.value = lang

      // Find source language
      const source = data.languages?.find(l => l.is_source)
      if (source) {
        sourceLangCode.value = source.code.toUpperCase()
        sourceDir.value = source.direction === 'rtl' ? 'rtl' : 'ltr'
      }

      // Populate tab stats from models
      if (lang.models) {
        for (const tab of tabs.value) {
          const modelData = lang.models[tab.key]
          if (modelData) {
            tab.translated = modelData.translated || 0
            tab.total = modelData.total || 0
          }
        }
      }
    }
  } catch (err) {
    console.error('Error loading language info:', err)
    showToast('Failed to load language info', 'error')
  }
}

// Load content for active tab
const loadTabContent = async () => {
  tabLoading.value = true
  try {
    const params = new URLSearchParams({
      model: activeTab.value,
      page: tabPage.value,
      per_page: perPage,
      filter: tabFilter.value,
      search: searchQuery.value
    })

    const response = await fetch(
      `/api/admin/languages/${langCode.value}/content-translations?${params.toString()}`,
      { credentials: 'same-origin' }
    )
    if (!response.ok) throw new Error('Failed to load translations')
    const data = await response.json()

    items.value = data.data || []
    tabPage.value = data.current_page || 1
    tabLastPage.value = data.last_page || 1
    tabTotalItems.value = data.total || 0
  } catch (err) {
    console.error('Error loading tab content:', err)
    items.value = []
    showToast('Failed to load translations', 'error')
  } finally {
    tabLoading.value = false
  }
}

// Save all pending changes
const handleSaveAll = async () => {
  if (pendingChanges.value.size === 0) return

  saving.value = true
  try {
    // Group changes by model
    const grouped = {}
    for (const [key, value] of pendingChanges.value.entries()) {
      const [model, id, field] = key.split(':')
      if (!grouped[model]) grouped[model] = []
      grouped[model].push({ id: parseInt(id), field, value })
    }

    // Send one request per model
    const promises = Object.entries(grouped).map(([model, translations]) =>
      fetch(`/api/admin/languages/${langCode.value}/content-translations`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: csrfHeaders(),
        body: JSON.stringify({ model, translations })
      })
    )

    const results = await Promise.all(promises)
    const allOk = results.every(r => r.ok)

    if (allOk) {
      pendingChanges.value = new Map()
      showToast('All changes saved successfully!', 'success')
      // Refresh stats and content
      await loadLangInfo()
      await loadTabContent()
    } else {
      showToast('Some changes failed to save', 'error')
    }
  } catch (err) {
    console.error('Error saving translations:', err)
    showToast('Failed to save changes: ' + err.message, 'error')
  } finally {
    saving.value = false
  }
}

// AI Translate All
const handleAiTranslateAll = async () => {
  if (!confirm(`Start AI translation for all untranslated content in "${langInfo.value?.name || langCode.value}"?`)) {
    return
  }

  translatingAll.value = true
  try {
    const response = await fetch(`/api/admin/auto-translate/${langCode.value}?tier=all`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: csrfHeaders()
    })

    if (!response.ok) {
      const data = await response.json()
      throw new Error(data.message || 'Failed to start translation')
    }

    showToast('AI translation started! This may take a few minutes.', 'info')

    // Poll for completion
    const pollId = setInterval(async () => {
      try {
        const progRes = await fetch(`/api/admin/auto-translate/${langCode.value}/progress`, {
          credentials: 'same-origin'
        })
        if (progRes.ok) {
          const progData = await progRes.json()
          const prog = progData.progress
          if (prog && (prog.status === 'completed' || prog.status === 'failed')) {
            clearInterval(pollId)
            translatingAll.value = false
            if (prog.status === 'completed') {
              showToast(`AI translation completed! ${prog.completed || 0} items translated.`, 'success')
            } else {
              showToast('AI translation failed.', 'error')
            }
            await loadLangInfo()
            await loadTabContent()
          }
        }
      } catch (_) {}
    }, 3000)
  } catch (err) {
    console.error('Error starting AI translation:', err)
    showToast('Failed to start AI translation: ' + err.message, 'error')
    translatingAll.value = false
  }
}

// AI Translate current tab
const handleAiTranslateTab = async () => {
  const tierMap = {
    ui_strings: 'ui',
    categories: 'core',
    sub_categories: 'core',
    badge_types: 'core',
    service_posts: 'posts'
  }
  const tier = tierMap[activeTab.value] || 'all'

  if (!confirm(`Start AI translation for ${currentTab.value?.label || activeTab.value}?`)) {
    return
  }

  translatingTab.value = true
  try {
    const response = await fetch(`/api/admin/auto-translate/${langCode.value}?tier=${tier}`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: csrfHeaders()
    })

    if (!response.ok) {
      const data = await response.json()
      throw new Error(data.message || 'Failed to start translation')
    }

    showToast(`AI translation started for ${currentTab.value?.label}!`, 'info')

    const pollId = setInterval(async () => {
      try {
        const progRes = await fetch(`/api/admin/auto-translate/${langCode.value}/progress`, {
          credentials: 'same-origin'
        })
        if (progRes.ok) {
          const progData = await progRes.json()
          const prog = progData.progress
          if (prog && (prog.status === 'completed' || prog.status === 'failed')) {
            clearInterval(pollId)
            translatingTab.value = false
            if (prog.status === 'completed') {
              showToast('Translation completed!', 'success')
            } else {
              showToast('Translation failed.', 'error')
            }
            await loadLangInfo()
            await loadTabContent()
          }
        }
      } catch (_) {}
    }, 3000)
  } catch (err) {
    console.error('Error starting tab AI translation:', err)
    showToast('Failed to start AI translation: ' + err.message, 'error')
    translatingTab.value = false
  }
}

// Lifecycle
onMounted(async () => {
  await loadLangInfo()
  await loadTabContent()
})
</script>

<style scoped>
.translations-page {
  padding: 0;
  max-width: 100%;
}

/* ========== Header ========== */
.trans-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  background: white;
  padding: 1.25rem 1.5rem;
  border-radius: 16px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
}

.btn-back {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  border: 2px solid #e5e7eb;
  background: white;
  color: #4b5563;
  font-size: 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.btn-back:hover {
  border-color: #667eea;
  color: #667eea;
  background: #f0f4ff;
}

.header-lang-badge {
  font-size: 1.6rem;
  font-weight: 900;
  color: #1a1a2e;
  letter-spacing: 1.5px;
  background: #f3f4f6;
  padding: 0.6rem 0.9rem;
  border-radius: 12px;
  line-height: 1;
  min-width: 58px;
  text-align: center;
  flex-shrink: 0;
}

.header-info {
  flex: 1;
  min-width: 200px;
}

.header-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1a1a2e;
  margin: 0 0 0.15rem 0;
  line-height: 1.2;
}

.header-subtitle {
  color: #6b7280;
  font-size: 0.9rem;
  margin: 0;
}

.header-badges {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.rtl-badge {
  background: #fef3c7;
  color: #d97706;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 0.25rem 0.65rem;
  border-radius: 20px;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.header-actions {
  display: flex;
  gap: 0.6rem;
  flex-shrink: 0;
}

.btn-ai-all {
  padding: 0.65rem 1.25rem;
  border: none;
  background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
  border-radius: 10px;
  font-size: 0.88rem;
  font-weight: 600;
  color: white;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  white-space: nowrap;
}

.btn-ai-all:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(124, 58, 237, 0.35);
}

.btn-ai-all:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.btn-save {
  padding: 0.65rem 1.25rem;
  border: none;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  border-radius: 10px;
  font-size: 0.88rem;
  font-weight: 600;
  color: white;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  white-space: nowrap;
}

.btn-save:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35);
}

.btn-save:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

/* ========== Category Tabs ========== */
.category-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.25rem;
  overflow-x: auto;
  padding-bottom: 0.25rem;
  -webkit-overflow-scrolling: touch;
}

.category-tabs::-webkit-scrollbar {
  height: 4px;
}

.category-tabs::-webkit-scrollbar-track {
  background: transparent;
}

.category-tabs::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 2px;
}

.category-tab {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.7rem 1.15rem;
  border: 2px solid #e5e7eb;
  background: white;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
  flex-shrink: 0;
}

.category-tab:hover {
  border-color: #667eea;
  color: #667eea;
  background: #f0f4ff;
}

.category-tab.active {
  background: linear-gradient(135deg, #667eea 0%, #5b6fd6 100%);
  border-color: #667eea;
  color: white;
  box-shadow: 0 4px 14px rgba(102, 126, 234, 0.3);
}

.category-tab.active i {
  color: white;
}

.category-tab i {
  font-size: 0.85rem;
  color: #9ca3af;
  width: 18px;
  text-align: center;
}

.tab-label {
  font-size: 0.85rem;
}

.tab-pct-badge {
  font-size: 0.72rem;
  font-weight: 700;
  padding: 0.15rem 0.45rem;
  border-radius: 20px;
  min-width: 32px;
  text-align: center;
}

.tab-pct-badge.pct-green {
  background: #d1fae5;
  color: #059669;
}

.tab-pct-badge.pct-orange {
  background: #fef3c7;
  color: #d97706;
}

.tab-pct-badge.pct-red {
  background: #fee2e2;
  color: #dc2626;
}

.category-tab.active .tab-pct-badge {
  background: rgba(255, 255, 255, 0.25);
  color: white;
}

/* ========== Content Area ========== */
.content-area {
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

/* Sub-header */
.content-subheader {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #f3f4f6;
  flex-wrap: wrap;
}

.subheader-left {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.subheader-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.subheader-title i {
  color: #667eea;
  font-size: 0.95rem;
}

.subheader-count {
  font-size: 0.82rem;
  color: #6b7280;
  font-weight: 500;
  background: #f3f4f6;
  padding: 0.2rem 0.6rem;
  border-radius: 20px;
}

.subheader-right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.btn-ai-tab {
  padding: 0.45rem 0.85rem;
  border: none;
  background: linear-gradient(135deg, #7c3aed 0%, #10b981 100%);
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 600;
  color: white;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  white-space: nowrap;
}

.btn-ai-tab:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
}

.btn-ai-tab:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Filter Pills */
.filter-pills {
  display: flex;
  gap: 0.25rem;
  background: #f3f4f6;
  padding: 0.2rem;
  border-radius: 8px;
}

.filter-pill {
  padding: 0.4rem 0.75rem;
  border: none;
  background: transparent;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-pill:hover {
  color: #374151;
  background: rgba(255, 255, 255, 0.5);
}

.filter-pill.active {
  background: #10b981;
  color: white;
  box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
}

/* Search Box */
.search-box {
  position: relative;
  min-width: 180px;
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  font-size: 0.82rem;
}

.search-input {
  width: 100%;
  padding: 0.5rem 2rem 0.5rem 2.25rem;
  border: 2px solid #eef2f7;
  border-radius: 8px;
  font-size: 0.85rem;
  transition: all 0.2s ease;
  background: #f9fafb;
}

.search-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  background: white;
}

.clear-search {
  position: absolute;
  right: 0.6rem;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  cursor: pointer;
  padding: 0.15rem;
}

.clear-search:hover {
  color: #ef4444;
}

/* ========== Loading ========== */
.loading-state {
  text-align: center;
  padding: 4rem 2rem;
  color: #6b7280;
}

.spinner {
  width: 42px;
  height: 42px;
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

/* ========== Items List ========== */
.items-list {
  padding: 1rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.item-card {
  border: 1px solid #eef2f7;
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.2s ease;
}

.item-card:hover {
  border-color: #d1d5db;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
}

/* Item Header */
.item-header {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.7rem 1rem;
  background: #fafbfc;
  border-bottom: 1px solid #eef2f7;
}

.item-number {
  font-size: 0.78rem;
  font-weight: 700;
  color: #9ca3af;
  min-width: 32px;
}

.item-label {
  flex: 1;
  font-size: 0.9rem;
  font-weight: 600;
  color: #374151;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.item-status {
  font-size: 1rem;
  flex-shrink: 0;
}

.item-status.status-done {
  color: #10b981;
}

.item-status.status-missing {
  color: #ef4444;
  font-size: 0.6rem;
}

/* Field Pair */
.field-pair {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
}

.field-col {
  padding: 0.75rem 1rem;
}

.field-source {
  background: #f9fafb;
  border-right: 1px solid #eef2f7;
}

.field-target {
  background: white;
}

.field-pair:not(:last-child) .field-col {
  border-bottom: 1px solid #eef2f7;
}

/* Field Labels */
.field-label-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  margin-bottom: 0.45rem;
}

.source-badge-label {
  color: #3b82f6;
}

.target-badge-label {
  color: #10b981;
}

.lang-indicator {
  font-weight: 800;
  padding: 0.1rem 0.35rem;
  border-radius: 4px;
  font-size: 0.65rem;
}

.source-badge-label .lang-indicator {
  background: #dbeafe;
  color: #1d4ed8;
}

.target-badge-label .lang-indicator {
  background: #d1fae5;
  color: #059669;
}

.field-name-tag {
  background: #f3f4f6;
  color: #6b7280;
  padding: 0.05rem 0.35rem;
  border-radius: 4px;
  font-size: 0.65rem;
  font-weight: 600;
  text-transform: lowercase;
}

/* Textareas */
.field-textarea {
  width: 100%;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  padding: 0.6rem 0.8rem;
  font-size: 0.88rem;
  font-family: inherit;
  line-height: 1.5;
  resize: vertical;
  transition: all 0.2s ease;
  min-height: 48px;
  color: #374151;
}

.field-source .field-textarea {
  background: #f3f4f6;
  color: #6b7280;
  cursor: default;
  border-color: #e5e7eb;
}

.field-target .field-textarea.editable {
  background: white;
  border-color: #d1d5db;
}

.field-target .field-textarea.editable:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
}

.field-target .field-textarea.editable::placeholder {
  color: #c4c9d2;
  font-style: italic;
}

/* ========== Empty State ========== */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
}

.empty-state i {
  font-size: 3.5rem;
  color: #d1d5db;
  margin-bottom: 1rem;
}

.empty-state h3 {
  color: #374151;
  margin: 0 0 0.5rem 0;
  font-weight: 700;
}

.empty-state p {
  color: #9ca3af;
  margin: 0;
}

/* ========== Pagination ========== */
.pagination-bar {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  border-top: 1px solid #f3f4f6;
}

.page-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 2px solid #e5e7eb;
  background: white;
  color: #4b5563;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  font-size: 0.85rem;
}

.page-btn:hover:not(:disabled) {
  border-color: #667eea;
  color: #667eea;
  background: #f0f4ff;
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.88rem;
  font-weight: 600;
  color: #374151;
}

.page-total {
  color: #9ca3af;
  font-weight: 400;
}

/* ========== Toast ========== */
.toast-notification {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  padding: 0.85rem 1.5rem;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 600;
  color: white;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
  z-index: 9999;
}

.toast-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.toast-error {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.toast-info {
  background: linear-gradient(135deg, #667eea 0%, #5b6fd6 100%);
}

.toast-fade-enter-active,
.toast-fade-leave-active {
  transition: all 0.35s ease;
}

.toast-fade-enter-from,
.toast-fade-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

/* ========== Responsive ========== */
@media (max-width: 1024px) {
  .trans-header {
    gap: 0.75rem;
  }

  .header-actions {
    width: 100%;
    justify-content: flex-end;
  }

  .content-subheader {
    flex-direction: column;
    align-items: stretch;
  }

  .subheader-right {
    justify-content: flex-start;
  }
}

@media (max-width: 768px) {
  .trans-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .header-lang-badge {
    align-self: flex-start;
  }

  .header-actions {
    width: 100%;
    flex-direction: column;
  }

  .btn-ai-all,
  .btn-save {
    width: 100%;
    justify-content: center;
  }

  .category-tabs {
    gap: 0.35rem;
  }

  .category-tab {
    padding: 0.6rem 0.85rem;
    font-size: 0.8rem;
  }

  .field-pair {
    grid-template-columns: 1fr;
  }

  .field-source {
    border-right: none;
    border-bottom: 1px solid #eef2f7;
  }

  .subheader-right {
    flex-direction: column;
    align-items: stretch;
  }

  .search-box {
    min-width: unset;
    width: 100%;
  }

  .filter-pills {
    justify-content: center;
  }

  .pagination-bar {
    gap: 0.75rem;
  }

  .toast-notification {
    left: 1rem;
    right: 1rem;
    bottom: 1rem;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .category-tab .tab-label {
    display: none;
  }

  .category-tab {
    padding: 0.6rem 0.7rem;
  }

  .item-label {
    font-size: 0.82rem;
  }

  .field-textarea {
    font-size: 0.82rem;
    padding: 0.5rem 0.65rem;
  }
}
</style>
