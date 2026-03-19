<template>
  <div class="languages-modern">
    <!-- Stats Cards -->
    <div class="stats-dashboard">
      <div class="stats-grid">
        <div class="stat-card-compact stat-blue">
          <div class="stat-icon"><i class="fas fa-globe"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(languages.total) }}</div>
            <div class="stat-label-compact">Total Languages</div>
          </div>
        </div>
        <div class="stat-card-compact stat-green">
          <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(activeCount) }}</div>
            <div class="stat-label-compact">Active</div>
          </div>
        </div>
        <div class="stat-card-compact stat-orange">
          <div class="stat-icon"><i class="fas fa-arrow-right"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(ltrCount) }}</div>
            <div class="stat-label-compact">LTR Languages</div>
          </div>
        </div>
        <div class="stat-card-compact stat-purple">
          <div class="stat-icon"><i class="fas fa-arrow-left"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(rtlCount) }}</div>
            <div class="stat-label-compact">RTL Languages</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="search-filter-bar">
      <div class="search-box">
        <i class="fas fa-search search-icon"></i>
        <input
          type="text"
          v-model="filters.search"
          class="search-input"
          placeholder="Search languages by name or code..."
        >
        <span v-if="filters.search" class="clear-search" @click="filters.search = ''">
          <i class="fas fa-times"></i>
        </span>
      </div>

      <div class="filter-group">
        <select v-model="filters.status" class="filter-select">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>

        <select v-model="filters.direction" class="filter-select">
          <option value="">All Directions</option>
          <option value="ltr">LTR Only</option>
          <option value="rtl">RTL Only</option>
        </select>
      </div>

      <div class="action-buttons">
        <button class="action-btn secondary" @click="resetFilters">
          <i class="fas fa-redo"></i>
        </button>
        <button class="action-btn primary" @click="showCreateModal = true">
          <i class="fas fa-plus"></i> Add Language
        </button>
      </div>
    </div>

    <!-- View Toggle -->
    <div class="view-controls mb-4">
      <div class="view-toggle">
        <button
          class="toggle-btn"
          :class="{ active: viewMode === 'grid' }"
          @click="viewMode = 'grid'"
        >
          <i class="fas fa-th"></i> Grid
        </button>
        <button
          class="toggle-btn"
          :class="{ active: viewMode === 'list' }"
          @click="viewMode = 'list'"
        >
          <i class="fas fa-list"></i> List
        </button>
      </div>

      <div class="results-info">
        Showing {{ languages.data.length }} of {{ languages.total }} languages
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading languages...</p>
    </div>

    <!-- Grid View -->
    <div v-else-if="viewMode === 'grid'" class="languages-grid">
      <div
        v-for="lang in languages.data"
        :key="lang.id"
        class="language-card"
        :class="{ 'card-inactive': !lang.is_active, 'card-default': lang.is_default }"
      >
        <div class="card-header-custom">
          <div class="language-code-badge" :class="lang.direction">
            {{ lang.code.toUpperCase() }}
          </div>
          <div class="card-menu">
            <button class="menu-btn" @click.stop="toggleMenu(lang.id)">
              <i class="fas fa-ellipsis-v"></i>
            </button>
            <div v-if="activeMenu === lang.id" class="dropdown-menu" @click.stop>
              <button @click="editLanguage(lang)" class="menu-item">
                <i class="fas fa-edit"></i> Edit Language
              </button>
              <button @click="handleToggleActive(lang)" class="menu-item">
                <i :class="lang.is_active ? 'fas fa-ban' : 'fas fa-check'"></i>
                {{ lang.is_active ? 'Deactivate' : 'Activate' }}
              </button>
              <button v-if="!lang.is_default" @click="handleSetDefault(lang)" class="menu-item">
                <i class="fas fa-star"></i> Set as Default
              </button>
              <button @click="viewTranslations(lang)" class="menu-item">
                <i class="fas fa-language"></i> View Translations
              </button>
              <button v-if="!lang.is_default" @click="handleDelete(lang)" class="menu-item danger">
                <i class="fas fa-trash"></i> Delete
              </button>
            </div>
          </div>
        </div>

        <div class="card-body-custom">
          <h3 class="language-name">{{ lang.name }}</h3>
          <p class="language-native">{{ lang.native_name }}</p>

          <div class="language-badges">
            <span class="badge" :class="lang.is_active ? 'success' : 'danger'">
              <i :class="lang.is_active ? 'fas fa-check-circle' : 'fas fa-ban'"></i>
              {{ lang.is_active ? 'Active' : 'Inactive' }}
            </span>
            <span v-if="lang.is_default" class="badge warning">
              <i class="fas fa-star"></i> Default
            </span>
            <span class="badge" :class="lang.direction === 'rtl' ? 'fire' : 'info'">
              <i :class="lang.direction === 'rtl' ? 'fas fa-arrow-left' : 'fas fa-arrow-right'"></i>
              {{ lang.direction.toUpperCase() }}
            </span>
          </div>

          <div class="spacer"></div>

          <div class="language-stats">
            <div class="stat-item">
              <i class="fas fa-language"></i>
              <span>{{ lang.translations_count || 0 }} Translations</span>
            </div>
            <div class="stat-item">
              <i class="fas fa-sort"></i>
              <span>Order: {{ lang.sort_order }}</span>
            </div>
          </div>
        </div>

        <div class="card-footer-custom">
          <button @click="editLanguage(lang)" class="action-btn primary">
            <i class="fas fa-edit"></i> Edit
          </button>
          <button @click="viewTranslations(lang)" class="action-btn info">
            <i class="fas fa-language"></i> Translations
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="languages.data.length === 0" class="empty-state-grid">
        <i class="fas fa-globe"></i>
        <h3>No Languages Found</h3>
        <p>Try adjusting your filters or create a new language</p>
        <button @click="resetFilters" class="action-btn primary">
          <i class="fas fa-redo"></i> Reset Filters
        </button>
      </div>
    </div>

    <!-- Table View -->
    <div v-else class="data-table-container">
      <table class="modern-table">
        <thead>
          <tr>
            <th style="width: 80px;">Code</th>
            <th>Language Name</th>
            <th>Direction</th>
            <th>Status</th>
            <th>Translations</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody v-if="languages.data.length > 0">
          <tr v-for="lang in languages.data" :key="lang.id" :class="{ 'row-inactive': !lang.is_active, 'row-default': lang.is_default }">
            <td>
              <div class="code-cell">
                <span class="language-code-badge small" :class="lang.direction">
                  {{ lang.code.toUpperCase() }}
                </span>
              </div>
            </td>
            <td>
              <div class="name-cell">
                <strong>{{ lang.name }}</strong>
                <span class="name-native">{{ lang.native_name }}</span>
              </div>
            </td>
            <td>
              <span class="badge" :class="lang.direction === 'rtl' ? 'fire' : 'info'">
                <i :class="lang.direction === 'rtl' ? 'fas fa-arrow-left' : 'fas fa-arrow-right'"></i>
                {{ lang.direction.toUpperCase() }}
              </span>
            </td>
            <td>
              <div class="status-badges">
                <span class="badge" :class="lang.is_active ? 'success' : 'danger'">
                  <i :class="lang.is_active ? 'fas fa-check-circle' : 'fas fa-ban'"></i>
                  {{ lang.is_active ? 'Active' : 'Inactive' }}
                </span>
                <span v-if="lang.is_default" class="badge warning">
                  <i class="fas fa-star"></i>
                </span>
              </div>
            </td>
            <td>
              <span class="badge secondary">
                <i class="fas fa-language"></i> {{ lang.translations_count || 0 }}
              </span>
            </td>
            <td>
              <div class="table-actions">
                <button @click="editLanguage(lang)" class="action-btn-small edit" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button
                  @click="handleToggleActive(lang)"
                  class="action-btn-small"
                  :class="lang.is_active ? 'ban' : 'unban'"
                  :title="lang.is_active ? 'Deactivate' : 'Activate'"
                >
                  <i :class="lang.is_active ? 'fas fa-ban' : 'fas fa-check'"></i>
                </button>
                <button
                  v-if="!lang.is_default"
                  @click="handleSetDefault(lang)"
                  class="action-btn-small featured"
                  title="Set as Default"
                >
                  <i class="fas fa-star"></i>
                </button>
                <button
                  @click="viewTranslations(lang)"
                  class="action-btn-small info-btn"
                  title="View Translations"
                >
                  <i class="fas fa-language"></i>
                </button>
                <button
                  v-if="!lang.is_default"
                  @click="handleDelete(lang)"
                  class="action-btn-small delete"
                  title="Delete"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="6" class="empty-state">
              <i class="fas fa-globe"></i>
              <p>No languages found</p>
              <button @click="resetFilters" class="action-btn primary">
                <i class="fas fa-redo"></i> Reset Filters
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-container" v-if="languages.last_page > 1">
      <div class="pagination-info">
        Showing {{ languages.data.length }} of {{ languages.total }} languages
      </div>
      <div class="pagination-controls">
        <button
          class="pagination-btn"
          :disabled="languages.current_page === 1"
          @click="loadLanguages(languages.current_page - 1)"
        >
          <i class="fas fa-chevron-left"></i> Previous
        </button>
        <button
          v-for="page in visiblePages"
          :key="page"
          class="pagination-btn"
          :class="{ active: page === languages.current_page }"
          @click="loadLanguages(page)"
        >
          {{ page }}
        </button>
        <button
          class="pagination-btn"
          :disabled="languages.current_page === languages.last_page"
          @click="loadLanguages(languages.current_page + 1)"
        >
          Next <i class="fas fa-chevron-right"></i>
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

    <!-- Missing Translations Warning Modal -->
    <div v-if="showMissingModal" class="modal-overlay" @click.self="closeMissingModal">
      <div class="modal-dialog-advanced missing-modal">
        <div class="modal-header warning-header">
          <h3 class="modal-title">
            <i class="fas fa-exclamation-triangle"></i>
            Missing Translations
          </h3>
          <button class="close-btn" @click="closeMissingModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <div class="warning-message">
            <p>
              <strong>{{ missingLanguage?.name }}</strong> is missing
              <span class="missing-count">{{ missingTranslations.length }}</span> translation keys
              that exist in the default language.
            </p>
            <p class="warning-hint">
              You can still activate this language, but some text may not display correctly for users.
            </p>
          </div>

          <div class="missing-list-container">
            <div class="missing-list-header">
              <span>Missing Keys ({{ missingTranslations.length }})</span>
              <button class="action-btn-small info-btn" @click="goToAddTranslations" title="Add Translations">
                <i class="fas fa-plus"></i> Add All
              </button>
            </div>
            <div class="missing-list">
              <div
                v-for="(item, index) in missingTranslations.slice(0, showAllMissing ? missingTranslations.length : 10)"
                :key="index"
                class="missing-item"
              >
                <div class="missing-key">
                  <code>{{ item.full_key }}</code>
                </div>
                <div class="missing-default-value">
                  {{ truncateValue(item.default_value) }}
                </div>
              </div>
              <div v-if="missingTranslations.length > 10 && !showAllMissing" class="show-more">
                <button @click="showAllMissing = true" class="show-more-btn">
                  <i class="fas fa-chevron-down"></i>
                  Show {{ missingTranslations.length - 10 }} more
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="action-btn secondary" @click="closeMissingModal">
            <i class="fas fa-times"></i>
            Cancel
          </button>
          <button type="button" class="action-btn info" @click="goToAddTranslations">
            <i class="fas fa-language"></i>
            Add Translations
          </button>
          <button type="button" class="action-btn warning" @click="confirmActivateAnyway">
            <i class="fas fa-check"></i>
            Activate Anyway
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, reactive, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useLanguages } from '../../../composables/useLanguages'
import { useAutoTranslate } from '../../../composables/useAutoTranslate'
import LanguageFormModal from '../../../components/admin/languages/LanguageFormModal.vue'

const router = useRouter()
const { languages, loading, fetchLanguages, deleteLanguage, toggleActive, setDefault } = useLanguages()

const formatNumber = (value) => {
  if (value === null || value === undefined) return '0'
  return new Intl.NumberFormat().format(value)
}

const viewMode = ref('list')
const activeMenu = ref(null)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedLanguage = ref(null)

// Missing translations modal
const showMissingModal = ref(false)
const missingTranslations = ref([])
const missingLanguage = ref(null)
const showAllMissing = ref(false)
const pendingActivation = ref(null)

const filters = reactive({
  search: '',
  status: '',
  direction: '',
  sort_by: 'sort_order',
  sort_direction: 'asc',
  per_page: 15
})

const activeCount = computed(() => {
  return languages.value.data.filter(l => l.is_active).length
})

const ltrCount = computed(() => {
  return languages.value.data.filter(l => l.direction === 'ltr').length
})

const rtlCount = computed(() => {
  return languages.value.data.filter(l => l.direction === 'rtl').length
})

const visiblePages = computed(() => {
  const pages = []
  const current = languages.value.current_page
  const last = languages.value.last_page

  let start = Math.max(1, current - 2)
  let end = Math.min(last, current + 2)

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

onMounted(async () => {
  await loadLanguages()
  document.addEventListener('click', closeMenus)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', closeMenus)
})

let searchTimeout = null
watch(() => filters.search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadLanguages(1), 300)
})

watch(() => [filters.status, filters.direction], () => {
  loadLanguages(1)
})

const loadLanguages = async (page = 1) => {
  await fetchLanguages({ ...filters, page })
}

const resetFilters = () => {
  filters.search = ''
  filters.status = ''
  filters.direction = ''
  loadLanguages(1)
}

const toggleMenu = (langId) => {
  activeMenu.value = activeMenu.value === langId ? null : langId
}

const closeMenus = () => {
  activeMenu.value = null
}

const editLanguage = (lang) => {
  closeMenus()
  selectedLanguage.value = lang
  showEditModal.value = true
}

const viewTranslations = (lang) => {
  closeMenus()
  router.push({ path: '/admin/translations', query: { locale: lang.code } })
}

const handleToggleActive = async (lang) => {
  closeMenus()
  if (lang.is_default && lang.is_active) {
    alert('Cannot deactivate the default language')
    return
  }

  // If activating, check for missing translations first
  if (!lang.is_active) {
    try {
      const missing = await fetchMissingTranslations(lang.code)
      if (missing.length > 0) {
        // Show warning modal
        missingLanguage.value = lang
        missingTranslations.value = missing
        pendingActivation.value = lang
        showAllMissing.value = false
        showMissingModal.value = true
        return
      }
    } catch (error) {
      console.error('Error checking missing translations:', error)
      // Continue with activation even if check fails
    }
  }

  // Proceed with toggle
  await performToggle(lang)
}

const performToggle = async (lang) => {
  if (!confirm(`Are you sure you want to ${lang.is_active ? 'deactivate' : 'activate'} "${lang.name}"?`)) {
    return
  }
  try {
    await toggleActive(lang.id)
    await loadLanguages(languages.value.current_page)
  } catch (error) {
    alert(error.message || 'Failed to toggle status')
  }
}

const fetchMissingTranslations = async (localeCode) => {
  try {
    const response = await fetch(`/api/admin/translations/missing/${localeCode}`, {
      credentials: 'same-origin'
    })
    if (!response.ok) {
      throw new Error('Failed to fetch missing translations')
    }
    const data = await response.json()
    return data.missing || []
  } catch (error) {
    console.error('Error fetching missing translations:', error)
    return []
  }
}

const closeMissingModal = () => {
  showMissingModal.value = false
  missingTranslations.value = []
  missingLanguage.value = null
  pendingActivation.value = null
  showAllMissing.value = false
}

const goToAddTranslations = () => {
  const lang = missingLanguage.value
  closeMissingModal()
  if (lang) {
    router.push({
      path: '/admin/translations',
      query: { locale: lang.code, show_missing: '1' }
    })
  }
}

const confirmActivateAnyway = async () => {
  const lang = pendingActivation.value
  closeMissingModal()
  if (lang) {
    try {
      await toggleActive(lang.id)
      await loadLanguages(languages.value.current_page)
    } catch (error) {
      alert(error.message || 'Failed to activate language')
    }
  }
}

const truncateValue = (value) => {
  if (!value) return ''
  return value.length > 50 ? value.substring(0, 50) + '...' : value
}

const handleSetDefault = async (lang) => {
  closeMenus()
  if (!confirm(`Are you sure you want to set "${lang.name}" as the default language?`)) {
    return
  }
  try {
    await setDefault(lang.id)
    await loadLanguages(languages.value.current_page)
  } catch (error) {
    alert(error.message || 'Failed to set default language')
  }
}

const handleDelete = async (lang) => {
  closeMenus()
  if (lang.is_default) {
    alert('Cannot delete the default language')
    return
  }
  if (!confirm(`Are you sure you want to delete "${lang.name}"? This will also delete all translations for this language. This action cannot be undone.`)) {
    return
  }
  try {
    await deleteLanguage(lang.id)
    await loadLanguages(languages.value.current_page)
  } catch (error) {
    alert('Error deleting language: ' + (error.message || 'Unknown error'))
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  selectedLanguage.value = null
}

const { startTranslation: aiStart, checkProgress: aiCheck } = useAutoTranslate()

const handleLanguageSaved = async (eventData) => {
  closeModal()
  await loadLanguages(languages.value.current_page)

  // If it's a new language, offer to auto-translate UI strings via OpenAI
  if (eventData && eventData.isNew && eventData.language) {
    const langCode = eventData.language.code
    const langName = eventData.language.name || langCode

    const doTranslate = confirm(
      `Language "${langName}" created!\n\nDo you want to auto-translate all UI strings using OpenAI?\n(This will translate Tier 1 - UI labels, buttons, messages)`
    )

    if (doTranslate) {
      try {
        await aiStart(langCode, '1') // Tier 1 = UI strings
        alert(`Auto-translation started for ${langName}.\nGo to Auto-Translate dashboard to monitor progress.`)
      } catch (e) {
        alert('Failed to start auto-translation: ' + e.message)
      }
    }

    // Redirect to translations page
    setTimeout(() => {
      router.push({
        path: '/admin/translations',
        query: {
          locale: langCode,
          new_language: '1'
        }
      })
    }, 500)
  }
}
</script>

<style scoped>
.languages-modern {
  padding: 0;
}

/* Search & Filters */
.search-filter-bar {
  background: white;
  padding: 1.25rem;
  border-radius: 16px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
  margin-bottom: 1.5rem;
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  align-items: center;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 280px;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #999;
}

.search-input {
  width: 100%;
  padding: 0.875rem 2.5rem 0.875rem 2.75rem;
  border: 2px solid #eef2f7;
  border-radius: 12px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.search-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.clear-search {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #999;
  cursor: pointer;
}

.filter-group {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.filter-select {
  padding: 0.875rem 1rem;
  border: 2px solid #eef2f7;
  border-radius: 12px;
  font-size: 0.9rem;
  background: white;
  cursor: pointer;
  transition: all 0.3s ease;
}

.filter-select:focus {
  outline: none;
  border-color: #667eea;
}

.action-buttons {
  display: flex;
  gap: 0.75rem;
}

.action-btn {
  padding: 0.75rem 1.25rem;
  border: none;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
}

.action-btn.primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.action-btn.info {
  background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
  color: white;
}

.action-btn.secondary {
  background: #6c757d;
  color: white;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

/* View Controls */
.view-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.view-toggle {
  display: flex;
  gap: 0.25rem;
  background: white;
  padding: 0.25rem;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.toggle-btn {
  padding: 0.6rem 1rem;
  border: none;
  background: transparent;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  color: #6c757d;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.85rem;
}

.toggle-btn.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.results-info {
  color: #666;
  font-size: 0.9rem;
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
}

.spinner {
  width: 50px;
  height: 50px;
  margin: 0 auto 1rem;
  border: 4px solid #f3f4f6;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Languages Grid */
.languages-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.language-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  min-height: 320px;
}

.language-card.card-inactive {
  opacity: 0.7;
  border-left: 4px solid #dc3545;
}

.language-card.card-default {
  border-left: 4px solid #ffc107;
}

.language-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
}

.card-header-custom {
  padding: 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  background: linear-gradient(135deg, #f8f9ff 0%, #eef2f7 100%);
}

.language-code-badge {
  padding: 0.75rem 1.25rem;
  border-radius: 12px;
  font-weight: 700;
  font-size: 1.25rem;
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.language-code-badge.ltr {
  background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}

.language-code-badge.rtl {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.language-code-badge.small {
  padding: 0.4rem 0.75rem;
  font-size: 0.9rem;
}

.card-menu {
  position: relative;
}

.menu-btn {
  background: white;
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
}

.menu-btn:hover {
  background: #f8f9fa;
  transform: scale(1.1);
}

.dropdown-menu {
  position: absolute;
  right: 0;
  top: 42px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  min-width: 180px;
  z-index: 100;
  overflow: hidden;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border: none;
  background: transparent;
  width: 100%;
  text-align: left;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #495057;
  font-size: 0.9rem;
}

.menu-item:hover {
  background: #f8f9fa;
}

.menu-item.danger {
  color: #dc3545;
}

.menu-item.danger:hover {
  background: #fee;
}

.card-body-custom {
  padding: 1.25rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.language-name {
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0 0 0.25rem 0;
  color: #2c3e50;
}

.language-native {
  color: #888;
  font-size: 1rem;
  margin: 0 0 0.75rem 0;
}

.language-badges {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
  margin-bottom: 0.75rem;
}

.spacer {
  flex: 1;
}

.language-stats {
  display: flex;
  gap: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #eef2f7;
  flex-wrap: wrap;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  color: #666;
  font-size: 0.8rem;
}

.stat-item i {
  color: #667eea;
}

.card-footer-custom {
  padding: 1rem 1.25rem;
  background: #f8f9ff;
  display: flex;
  gap: 0.75rem;
  border-top: 1px solid #eef2f7;
}

.card-footer-custom .action-btn {
  flex: 1;
  justify-content: center;
  padding: 0.65rem 1rem;
  font-size: 0.85rem;
}

/* Badges */
.badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.35rem 0.7rem;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge.success { background: #e8f5e9; color: #2e7d32; }
.badge.danger { background: #ffebee; color: #c62828; }
.badge.warning { background: #fff8e1; color: #ff8f00; }
.badge.fire { background: #fce4ec; color: #c2185b; }
.badge.info { background: #e0f7fa; color: #00838f; }
.badge.secondary { background: #f5f5f5; color: #616161; }

/* Empty State Grid */
.empty-state-grid {
  grid-column: 1 / -1;
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 16px;
}

.empty-state-grid i {
  font-size: 4rem;
  color: #ddd;
  margin-bottom: 1rem;
}

/* Data Table */
.data-table-container {
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.modern-table {
  width: 100%;
  border-collapse: collapse;
}

.modern-table thead {
  background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
}

.modern-table th {
  padding: 1.125rem 1rem;
  text-align: left;
  font-weight: 600;
  color: white;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.modern-table tbody tr {
  border-bottom: 1px solid #f0f4f8;
  transition: all 0.2s ease;
}

.modern-table tbody tr:hover {
  background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
}

.modern-table tbody tr.row-inactive {
  background: #fff5f5;
}

.modern-table tbody tr.row-default {
  background: #fffbf0;
}

.modern-table td {
  padding: 1rem;
  font-size: 0.9rem;
  color: #333;
  vertical-align: middle;
}

.empty-state {
  text-align: center;
  padding: 4rem !important;
  color: #999;
}

.empty-state i {
  font-size: 3.5rem;
  margin-bottom: 1rem;
  display: block;
  opacity: 0.3;
}

.empty-state p {
  margin-bottom: 1.5rem;
}

/* Table Cells */
.code-cell {
  display: inline-block;
}

.name-cell {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.name-native {
  font-size: 0.85rem;
  color: #888;
}

.status-badges {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
}

/* Table Actions */
.table-actions {
  display: flex;
  gap: 0.4rem;
}

.action-btn-small {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
}

.action-btn-small.edit {
  background: #e3f2fd;
  color: #1976d2;
}

.action-btn-small.ban {
  background: #ffebee;
  color: #c62828;
}

.action-btn-small.unban {
  background: #e8f5e9;
  color: #2e7d32;
}

.action-btn-small.featured {
  background: #fff8e1;
  color: #ff8f00;
}

.action-btn-small.info-btn {
  background: #e0f7fa;
  color: #00838f;
}

.action-btn-small.delete {
  background: #ffebee;
  color: #c62828;
}

.action-btn-small:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Pagination */
.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1.5rem;
  padding: 1.25rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
  flex-wrap: wrap;
  gap: 1rem;
}

.pagination-info {
  color: #666;
  font-size: 0.9rem;
}

.pagination-controls {
  display: flex;
  gap: 0.5rem;
}

.pagination-btn {
  padding: 0.6rem 1rem;
  border: 2px solid #eef2f7;
  background: white;
  border-radius: 10px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.pagination-btn:hover:not(:disabled) {
  border-color: #667eea;
  color: #667eea;
}

.pagination-btn.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-color: transparent;
  color: white;
}

.pagination-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
  .search-filter-bar {
    flex-direction: column;
  }

  .search-box, .filter-group, .action-buttons {
    width: 100%;
  }

  .filter-group {
    flex-direction: column;
  }

  .filter-select {
    width: 100%;
  }

  .action-buttons {
    justify-content: stretch;
  }

  .action-btn {
    flex: 1;
    justify-content: center;
  }

  .languages-grid {
    grid-template-columns: 1fr;
  }

  .data-table-container {
    overflow-x: auto;
  }

  .modern-table {
    min-width: 700px;
  }

  .pagination-container {
    flex-direction: column;
  }
}

/* Missing Translations Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
}

.modal-dialog-advanced {
  background: white;
  border-radius: 16px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: modalSlideIn 0.3s ease;
}

.missing-modal {
  max-width: 700px;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header.warning-header {
  background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
  border-bottom: 2px solid #f39c12;
}

.modal-header.warning-header .modal-title {
  color: #856404;
}

.modal-header.warning-header .modal-title i {
  color: #f39c12;
}

.modal-title {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 700;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  color: #6c757d;
  cursor: pointer;
  padding: 0.25rem;
  transition: all 0.2s ease;
  border-radius: 6px;
}

.close-btn:hover {
  color: #dc3545;
  background: rgba(220, 53, 69, 0.1);
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #e9ecef;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.warning-message {
  background: #fff8e1;
  border: 1px solid #ffe082;
  border-radius: 10px;
  padding: 1rem 1.25rem;
  margin-bottom: 1.25rem;
}

.warning-message p {
  margin: 0 0 0.5rem 0;
  color: #5d4e37;
}

.warning-message p:last-child {
  margin-bottom: 0;
}

.warning-hint {
  font-size: 0.9rem;
  color: #7c6a54 !important;
}

.missing-count {
  display: inline-block;
  background: #f39c12;
  color: white;
  padding: 0.15rem 0.5rem;
  border-radius: 20px;
  font-weight: 700;
  font-size: 0.9rem;
}

.missing-list-container {
  border: 1px solid #e9ecef;
  border-radius: 10px;
  overflow: hidden;
}

.missing-list-header {
  background: #f8f9fa;
  padding: 0.75rem 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 600;
  font-size: 0.9rem;
  color: #495057;
  border-bottom: 1px solid #e9ecef;
}

.missing-list {
  max-height: 300px;
  overflow-y: auto;
}

.missing-item {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #f0f0f0;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.missing-item:last-child {
  border-bottom: none;
}

.missing-item:hover {
  background: #f8f9ff;
}

.missing-key code {
  background: #f5f5f5;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.85rem;
  color: #c7254e;
}

.missing-default-value {
  font-size: 0.85rem;
  color: #666;
  padding-left: 0.25rem;
}

.show-more {
  padding: 0.75rem 1rem;
  text-align: center;
  background: #f8f9fa;
}

.show-more-btn {
  background: none;
  border: none;
  color: #667eea;
  cursor: pointer;
  font-size: 0.9rem;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.show-more-btn:hover {
  background: rgba(102, 126, 234, 0.1);
}

.action-btn.warning {
  background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
  color: white;
}
</style>
