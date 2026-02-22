<template>
  <div class="translations-modern">
    <!-- Stats Cards -->
    <div class="stats-dashboard">
      <div class="stats-grid">
        <div class="stat-card-compact stat-blue">
          <div class="stat-icon"><i class="fas fa-language"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(translations.total) }}</div>
            <div class="stat-label-compact">Total Translations</div>
          </div>
        </div>
        <div class="stat-card-compact stat-green">
          <div class="stat-icon"><i class="fas fa-globe"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(availableLanguages.length) }}</div>
            <div class="stat-label-compact">Languages</div>
          </div>
        </div>
        <div class="stat-card-compact stat-orange">
          <div class="stat-icon"><i class="fas fa-folder"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(groups.length) }}</div>
            <div class="stat-label-compact">Groups</div>
          </div>
        </div>
        <div class="stat-card-compact stat-purple">
          <div class="stat-icon"><i class="fas fa-key"></i></div>
          <div class="stat-info">
            <div class="stat-value-compact">{{ formatNumber(uniqueKeysCount) }}</div>
            <div class="stat-label-compact">Unique Keys</div>
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
          placeholder="Search by key or value..."
        >
        <span v-if="filters.search" class="clear-search" @click="filters.search = ''">
          <i class="fas fa-times"></i>
        </span>
      </div>

      <div class="filter-group">
        <select v-model="filters.locale" class="filter-select">
          <option value="">All Languages</option>
          <option v-for="lang in availableLanguages" :key="lang.code" :value="lang.code">
            {{ lang.name }} ({{ lang.code }})
          </option>
        </select>

        <select v-model="filters.group" class="filter-select">
          <option value="">All Groups</option>
          <option v-for="group in groups" :key="group" :value="group">
            {{ group }}
          </option>
        </select>
      </div>

      <div class="action-buttons">
        <button class="action-btn secondary" @click="resetFilters">
          <i class="fas fa-redo"></i>
        </button>
        <button class="action-btn info" @click="showImportModal = true">
          <i class="fas fa-file-import"></i> Import
        </button>
        <button class="action-btn info" @click="handleExport">
          <i class="fas fa-file-export"></i> Export
        </button>
        <button class="action-btn primary" @click="showCreateModal = true">
          <i class="fas fa-plus"></i> Add Translation
        </button>
      </div>
    </div>

    <!-- New Language Welcome Banner -->
    <div v-if="showNewLanguageBanner" class="new-language-banner">
      <div class="banner-content">
        <div class="banner-icon">
          <i class="fas fa-flag-checkered"></i>
        </div>
        <div class="banner-text">
          <h4>New Language Created!</h4>
          <p>Start adding translations for <strong>{{ getLanguageName(filters.locale) }}</strong>. You can add translations one by one or import from a JSON file.</p>
        </div>
        <button class="banner-close" @click="dismissNewLanguageBanner">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="banner-actions">
        <button class="action-btn primary" @click="showCreateModal = true">
          <i class="fas fa-plus"></i> Add First Translation
        </button>
        <button class="action-btn info" @click="showImportModal = true">
          <i class="fas fa-file-import"></i> Import from JSON
        </button>
      </div>
    </div>

    <!-- Active Language Info -->
    <div v-if="filters.locale" class="active-locale-bar">
      <div class="locale-info">
        <span class="locale-badge">{{ filters.locale.toUpperCase() }}</span>
        <span class="locale-name">{{ getLanguageName(filters.locale) }}</span>
      </div>
      <div class="locale-stats">
        {{ translations.total }} translations in this language
        <button
          v-if="!showMissingSection"
          class="check-missing-btn"
          @click="showMissingSection = true; refreshMissingTranslations()"
          title="Check for missing translations"
        >
          <i class="fas fa-search"></i> Check Missing
        </button>
      </div>
    </div>

    <!-- Missing Translations Section -->
    <div v-if="showMissingSection && filters.locale" class="missing-section">
      <div class="missing-header">
        <div class="missing-title">
          <i class="fas fa-exclamation-triangle"></i>
          <span>Missing Translations</span>
          <span v-if="!loadingMissing" class="missing-badge">{{ missingTranslations.length }}</span>
        </div>
        <div class="missing-actions">
          <button class="action-btn-small" @click="refreshMissingTranslations" :disabled="loadingMissing">
            <i :class="loadingMissing ? 'fas fa-spinner fa-spin' : 'fas fa-sync'"></i>
          </button>
          <button class="action-btn-small" @click="closeMissingSection">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      <div v-if="loadingMissing" class="missing-loading">
        <i class="fas fa-spinner fa-spin"></i> Loading...
      </div>

      <div v-else-if="missingTranslations.length === 0" class="missing-empty">
        <i class="fas fa-check-circle"></i>
        <p>All translations are complete for this language!</p>
      </div>

      <div v-else class="missing-list-wrapper">
        <p class="missing-hint">
          These keys exist in the default language but are missing in <strong>{{ getLanguageName(filters.locale) }}</strong>.
          Click on a row to add the translation.
        </p>
        <div class="missing-items">
          <div
            v-for="item in missingTranslations.slice(0, 20)"
            :key="item.full_key"
            class="missing-item-card"
            :class="{ 'editing': addingMissingKey === item.full_key }"
          >
            <div class="missing-item-header" @click="startAddMissing(item)">
              <code class="missing-key-code">{{ item.full_key }}</code>
              <span class="missing-default-hint">Default: {{ truncateValue(item.default_value) }}</span>
            </div>

            <div v-if="addingMissingKey === item.full_key" class="missing-item-form">
              <textarea
                :id="`missing-input-${item.full_key}`"
                v-model="newMissingValue"
                class="missing-input"
                :placeholder="`Enter translation for ${filters.locale.toUpperCase()}...`"
                :dir="getLanguageDirection(filters.locale)"
                rows="2"
                @keydown.enter.ctrl="saveNewMissingTranslation(item)"
                @keydown.escape="cancelAddMissing"
              ></textarea>
              <div class="missing-form-actions">
                <button @click="cancelAddMissing" class="action-btn secondary small">
                  <i class="fas fa-times"></i> Cancel
                </button>
                <button @click="saveNewMissingTranslation(item)" class="action-btn primary small">
                  <i class="fas fa-check"></i> Save
                </button>
              </div>
            </div>
          </div>

          <div v-if="missingTranslations.length > 20" class="missing-more-info">
            <i class="fas fa-info-circle"></i>
            Showing 20 of {{ missingTranslations.length }} missing translations.
            Add translations to see more.
          </div>
        </div>
      </div>
    </div>

    <!-- Results Info -->
    <div class="view-controls mb-4">
      <div class="results-info">
        Showing {{ translations.data.length }} of {{ translations.total }} translations
      </div>
      <div class="per-page-select">
        <label>Per page:</label>
        <select v-model="filters.per_page" @change="loadTranslations(1)">
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading translations...</p>
    </div>

    <!-- Table View -->
    <div v-else class="data-table-container">
      <table class="modern-table">
        <thead>
          <tr>
            <th style="width: 70px; white-space: nowrap;">Locale</th>
            <th style="width: 120px; min-width: 120px; white-space: nowrap;">Group</th>
            <th style="width: 180px; min-width: 180px;">Key</th>
            <th style="min-width: 200px;">Value</th>
            <th style="width: 110px; white-space: nowrap;">Actions</th>
          </tr>
        </thead>
        <tbody v-if="translations.data.length > 0">
          <tr v-for="translation in translations.data" :key="translation.id">
            <td>
              <span class="locale-badge small">{{ translation.locale.toUpperCase() }}</span>
            </td>
            <td>
              <span class="group-badge">{{ translation.group }}</span>
            </td>
            <td>
              <div class="key-cell">
                <code class="translation-key">{{ translation.key }}</code>
              </div>
            </td>
            <td>
              <!-- Inline Editing Mode -->
              <div v-if="editingId === translation.id" class="inline-edit-cell">
                <textarea
                  ref="inlineEditInput"
                  v-model="editingValue"
                  class="inline-edit-input"
                  :dir="getLanguageDirection(translation.locale)"
                  @keydown.enter.ctrl="saveInlineEdit(translation)"
                  @keydown.escape="cancelInlineEdit"
                  rows="2"
                ></textarea>
                <div class="inline-edit-actions">
                  <button @click="saveInlineEdit(translation)" class="inline-btn save" title="Save (Ctrl+Enter)">
                    <i class="fas fa-check"></i>
                  </button>
                  <button @click="cancelInlineEdit" class="inline-btn cancel" title="Cancel (Esc)">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <!-- Normal Display Mode -->
              <div v-else class="value-cell" :dir="getLanguageDirection(translation.locale)">
                <span
                  class="translation-value editable"
                  @dblclick="startInlineEdit(translation)"
                  title="Double-click to edit"
                >{{ truncateValue(translation.value) }}</span>
                <button
                  v-if="translation.value && translation.value.length > 100"
                  class="expand-btn"
                  @click="showFullValue(translation)"
                  title="View full value"
                >
                  <i class="fas fa-expand"></i>
                </button>
                <button
                  class="inline-edit-btn"
                  @click="startInlineEdit(translation)"
                  title="Quick edit"
                >
                  <i class="fas fa-pencil-alt"></i>
                </button>
              </div>
            </td>
            <td>
              <div class="table-actions">
                <button @click="editTranslation(translation)" class="action-btn-small edit" title="Edit All Fields">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="copyKey(translation)" class="action-btn-small copy" title="Copy Key">
                  <i class="fas fa-copy"></i>
                </button>
                <button @click="handleDelete(translation)" class="action-btn-small delete" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="5" class="empty-state">
              <i class="fas fa-language"></i>
              <p>No translations found</p>
              <button @click="resetFilters" class="action-btn primary">
                <i class="fas fa-redo"></i> Reset Filters
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-container" v-if="translations.last_page > 1">
      <div class="pagination-info">
        Showing {{ translations.data.length }} of {{ translations.total }} translations
      </div>
      <div class="pagination-controls">
        <button
          class="pagination-btn"
          :disabled="translations.current_page === 1"
          @click="loadTranslations(translations.current_page - 1)"
        >
          <i class="fas fa-chevron-left"></i> Previous
        </button>
        <button
          v-for="page in visiblePages"
          :key="page"
          class="pagination-btn"
          :class="{ active: page === translations.current_page }"
          @click="loadTranslations(page)"
        >
          {{ page }}
        </button>
        <button
          class="pagination-btn"
          :disabled="translations.current_page === translations.last_page"
          @click="loadTranslations(translations.current_page + 1)"
        >
          Next <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <TranslationFormModal
      v-if="showCreateModal || showEditModal"
      :translation="selectedTranslation"
      :mode="showCreateModal ? 'create' : 'edit'"
      :languages="availableLanguages"
      :groups="groups"
      @close="closeModal"
      @saved="handleTranslationSaved"
    />

    <!-- Import Modal -->
    <div v-if="showImportModal" class="modal-overlay" @click.self="showImportModal = false">
      <div class="modal-dialog-advanced">
        <div class="modal-header">
          <h3 class="modal-title">
            <i class="fas fa-file-import"></i>
            Import Translations
          </h3>
          <button class="close-btn" @click="showImportModal = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-globe"></i>
              Target Language *
            </label>
            <select v-model="importLocale" class="form-control-modern">
              <option value="">Select language...</option>
              <option v-for="lang in availableLanguages" :key="lang.code" :value="lang.code">
                {{ lang.name }} ({{ lang.code }})
              </option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-file"></i>
              JSON File *
            </label>
            <input
              type="file"
              ref="importFileInput"
              accept=".json"
              class="form-control-modern"
              @change="handleFileSelect"
            >
            <small class="form-hint">Upload a JSON file with key-value translation pairs</small>
          </div>
          <div v-if="importError" class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            {{ importError }}
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="action-btn secondary" @click="showImportModal = false">
            <i class="fas fa-times"></i>
            Cancel
          </button>
          <button type="button" class="action-btn primary" @click="handleImport" :disabled="importing">
            <i :class="importing ? 'fas fa-spinner fa-spin' : 'fas fa-file-import'"></i>
            {{ importing ? 'Importing...' : 'Import' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Full Value Modal -->
    <div v-if="showValueModal" class="modal-overlay" @click.self="showValueModal = false">
      <div class="modal-dialog-advanced">
        <div class="modal-header">
          <h3 class="modal-title">
            <i class="fas fa-align-left"></i>
            Translation Value
          </h3>
          <button class="close-btn" @click="showValueModal = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Key</label>
            <code class="full-key">{{ fullValueTranslation?.group }}.{{ fullValueTranslation?.key }}</code>
          </div>
          <div class="form-group">
            <label class="form-label">Value</label>
            <div class="full-value" :dir="getLanguageDirection(fullValueTranslation?.locale)">
              {{ fullValueTranslation?.value }}
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="action-btn secondary" @click="showValueModal = false">
            <i class="fas fa-times"></i>
            Close
          </button>
          <button type="button" class="action-btn primary" @click="copyValue(fullValueTranslation)">
            <i class="fas fa-copy"></i>
            Copy Value
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, reactive, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useTranslations } from '../../../composables/useTranslations'
import { useLanguages } from '../../../composables/useLanguages'
import TranslationFormModal from '../../../components/admin/translations/TranslationFormModal.vue'

const route = useRoute()
const router = useRouter()
const { translations, groups, loading, fetchTranslations, fetchGroups, deleteTranslation, exportTranslations, importTranslations, updateTranslation, createTranslation } = useTranslations()
const { languages: languagesData, fetchLanguages } = useLanguages()

const formatNumber = (value) => {
  if (value === null || value === undefined) return '0'
  return new Intl.NumberFormat().format(value)
}

const showCreateModal = ref(false)
const showEditModal = ref(false)
const showImportModal = ref(false)
const showValueModal = ref(false)
const selectedTranslation = ref(null)
const fullValueTranslation = ref(null)
const availableLanguages = ref([])
const importLocale = ref('')
const importFile = ref(null)
const importError = ref('')
const importing = ref(false)
const uniqueKeysCount = ref(0)

// New language banner
const showNewLanguageBanner = ref(false)

// Missing translations
const showMissingSection = ref(false)
const missingTranslations = ref([])
const loadingMissing = ref(false)
const addingMissingKey = ref(null)
const newMissingValue = ref('')

// Inline editing
const editingId = ref(null)
const editingValue = ref('')
const inlineEditInput = ref(null)
const savingInline = ref(false)

const filters = reactive({
  search: '',
  locale: '',
  group: '',
  sort_by: 'key',
  sort_direction: 'asc',
  per_page: 25
})

const visiblePages = computed(() => {
  const pages = []
  const current = translations.value.current_page
  const last = translations.value.last_page

  let start = Math.max(1, current - 2)
  let end = Math.min(last, current + 2)

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

onMounted(async () => {
  // Load languages first
  await fetchLanguages({ per_page: 100 })
  availableLanguages.value = languagesData.value.data || []

  // Check for locale query param
  if (route.query.locale) {
    filters.locale = route.query.locale
  }

  // Check if this is a redirect from new language creation
  if (route.query.new_language === '1') {
    showNewLanguageBanner.value = true
    // Clean up the URL
    router.replace({
      path: route.path,
      query: { locale: route.query.locale }
    })
  }

  // Check if we should show missing translations
  if (route.query.show_missing === '1' && route.query.locale) {
    showMissingSection.value = true
    await fetchMissingTranslations(route.query.locale)
    // Clean up the URL
    router.replace({
      path: route.path,
      query: { locale: route.query.locale }
    })
  }

  await fetchGroups()
  await loadTranslations()
  await fetchUniqueKeysCount()
})

let searchTimeout = null
watch(() => filters.search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadTranslations(1), 300)
})

watch(() => [filters.locale, filters.group], () => {
  loadTranslations(1)
})

const loadTranslations = async (page = 1) => {
  await fetchTranslations({ ...filters, page })
}

const fetchUniqueKeysCount = async () => {
  try {
    const response = await fetch('/api/admin/translations/stats', {
      credentials: 'same-origin'
    })
    if (response.ok) {
      const data = await response.json()
      uniqueKeysCount.value = data.unique_keys || 0
    }
  } catch (e) {
    console.error('Error fetching stats:', e)
  }
}

const resetFilters = () => {
  filters.search = ''
  filters.locale = ''
  filters.group = ''
  loadTranslations(1)
}

const getLanguageName = (code) => {
  const lang = availableLanguages.value.find(l => l.code === code)
  return lang ? lang.name : code
}

const getLanguageDirection = (code) => {
  const lang = availableLanguages.value.find(l => l.code === code)
  return lang?.direction || 'ltr'
}

const truncateValue = (value) => {
  if (!value) return ''
  return value.length > 100 ? value.substring(0, 100) + '...' : value
}

const editTranslation = (translation) => {
  selectedTranslation.value = translation
  showEditModal.value = true
}

const showFullValue = (translation) => {
  fullValueTranslation.value = translation
  showValueModal.value = true
}

const copyKey = (translation) => {
  const fullKey = `${translation.group}.${translation.key}`
  navigator.clipboard.writeText(fullKey).then(() => {
    alert('Key copied to clipboard!')
  })
}

const copyValue = (translation) => {
  navigator.clipboard.writeText(translation.value).then(() => {
    alert('Value copied to clipboard!')
  })
}

const handleDelete = async (translation) => {
  if (!confirm(`Are you sure you want to delete the translation for "${translation.group}.${translation.key}" (${translation.locale})? This action cannot be undone.`)) {
    return
  }
  try {
    await deleteTranslation(translation.id)
    await loadTranslations(translations.value.current_page)
  } catch (error) {
    alert('Error deleting translation: ' + (error.message || 'Unknown error'))
  }
}

const handleExport = async () => {
  try {
    const data = await exportTranslations(filters.locale || null)

    // Create and download JSON file
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `translations${filters.locale ? '-' + filters.locale : ''}.json`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)
  } catch (error) {
    alert('Error exporting translations: ' + (error.message || 'Unknown error'))
  }
}

const handleFileSelect = (event) => {
  importFile.value = event.target.files[0]
  importError.value = ''
}

const handleImport = async () => {
  if (!importLocale.value) {
    importError.value = 'Please select a target language'
    return
  }
  if (!importFile.value) {
    importError.value = 'Please select a file to import'
    return
  }

  importing.value = true
  importError.value = ''

  try {
    await importTranslations(importFile.value, importLocale.value)
    showImportModal.value = false
    importLocale.value = ''
    importFile.value = null
    await loadTranslations(1)
    await fetchGroups()
    alert('Translations imported successfully!')
  } catch (error) {
    importError.value = error.message || 'Failed to import translations'
  } finally {
    importing.value = false
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  selectedTranslation.value = null
}

const handleTranslationSaved = () => {
  closeModal()
  loadTranslations(translations.value.current_page)
  fetchGroups()
}

// New language banner
const dismissNewLanguageBanner = () => {
  showNewLanguageBanner.value = false
}

// Inline editing methods
const startInlineEdit = (translation) => {
  editingId.value = translation.id
  editingValue.value = translation.value || ''
  nextTick(() => {
    if (inlineEditInput.value) {
      // Handle ref array from v-for
      const input = Array.isArray(inlineEditInput.value) ? inlineEditInput.value[0] : inlineEditInput.value
      if (input) {
        input.focus()
        input.select()
      }
    }
  })
}

const cancelInlineEdit = () => {
  editingId.value = null
  editingValue.value = ''
}

const saveInlineEdit = async (translation) => {
  if (savingInline.value) return
  if (editingValue.value === translation.value) {
    cancelInlineEdit()
    return
  }

  savingInline.value = true
  try {
    await updateTranslation(translation.id, {
      locale: translation.locale,
      group: translation.group,
      key: translation.key,
      value: editingValue.value
    })
    // Update the translation in the local list
    const index = translations.value.data.findIndex(t => t.id === translation.id)
    if (index !== -1) {
      translations.value.data[index].value = editingValue.value
    }
    cancelInlineEdit()
  } catch (error) {
    alert('Error saving translation: ' + (error.message || 'Unknown error'))
  } finally {
    savingInline.value = false
  }
}

// Missing translations functions
const fetchMissingTranslations = async (localeCode) => {
  loadingMissing.value = true
  try {
    const response = await fetch(`/api/admin/translations/missing/${localeCode}`, {
      credentials: 'same-origin'
    })
    if (!response.ok) {
      throw new Error('Failed to fetch missing translations')
    }
    const data = await response.json()
    missingTranslations.value = data.missing || []
  } catch (error) {
    console.error('Error fetching missing translations:', error)
    missingTranslations.value = []
  } finally {
    loadingMissing.value = false
  }
}

const closeMissingSection = () => {
  showMissingSection.value = false
  missingTranslations.value = []
  addingMissingKey.value = null
  newMissingValue.value = ''
}

const startAddMissing = (item) => {
  addingMissingKey.value = item.full_key
  newMissingValue.value = ''
  nextTick(() => {
    const input = document.getElementById(`missing-input-${item.full_key}`)
    if (input) {
      input.focus()
    }
  })
}

const cancelAddMissing = () => {
  addingMissingKey.value = null
  newMissingValue.value = ''
}

const saveNewMissingTranslation = async (item) => {
  if (!newMissingValue.value.trim()) {
    alert('Please enter a translation value')
    return
  }

  try {
    await createTranslation({
      locale: filters.locale,
      group: item.group,
      key: item.key,
      value: newMissingValue.value
    })

    // Remove from missing list
    missingTranslations.value = missingTranslations.value.filter(
      m => m.full_key !== item.full_key
    )
    addingMissingKey.value = null
    newMissingValue.value = ''

    // Reload translations list
    await loadTranslations()
  } catch (error) {
    alert('Error saving translation: ' + (error.message || 'Unknown error'))
  }
}

const refreshMissingTranslations = async () => {
  if (filters.locale) {
    await fetchMissingTranslations(filters.locale)
  }
}
</script>

<style scoped>
.translations-modern {
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
  min-width: 250px;
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
  gap: 0.5rem;
  flex-wrap: wrap;
}

.action-btn {
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 12px;
  font-size: 0.85rem;
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

/* Active Locale Bar */
.active-locale-bar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 1rem 1.5rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
}

.locale-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.locale-badge {
  background: rgba(255, 255, 255, 0.2);
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 700;
  font-size: 1rem;
}

.locale-badge.small {
  padding: 0.3rem 0.6rem;
  font-size: 0.8rem;
}

.locale-name {
  font-size: 1.1rem;
  font-weight: 600;
}

.locale-stats {
  font-size: 0.9rem;
  opacity: 0.9;
}

/* View Controls */
.view-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.results-info {
  color: #666;
  font-size: 0.9rem;
}

.per-page-select {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.per-page-select label {
  color: #666;
  font-size: 0.9rem;
}

.per-page-select select {
  padding: 0.5rem;
  border: 2px solid #eef2f7;
  border-radius: 8px;
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
  table-layout: fixed;
}

.modern-table thead {
  background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
}

.modern-table th {
  padding: 1rem;
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
.modern-table td {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.modern-table td:nth-child(4) {
  white-space: normal;
  word-break: break-word;
}

.group-badge {
  background: #e0f7fa;
  color: #00838f;
  padding: 0.35rem 0.7rem;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 600;
  white-space: nowrap;
  display: inline-block;
}

.key-cell {
  overflow: hidden;
  text-overflow: ellipsis;
}

.translation-key {
  background: #f5f5f5;
  padding: 0.3rem 0.6rem;
  border-radius: 4px;
  font-size: 0.85rem;
  color: #c7254e;
  word-break: break-all;
}

.value-cell {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  overflow: hidden;
}

.translation-value {
  flex: 1;
  word-break: break-word;
}

.expand-btn {
  background: #f5f5f5;
  border: none;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #666;
  transition: all 0.2s ease;
  flex-shrink: 0;
  margin-right: 4px;
}

.expand-btn:hover {
  background: #667eea;
  color: white;
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

.action-btn-small.copy {
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

/* Modal Styles */
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
  padding: 1.5rem;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #6c757d;
  cursor: pointer;
  padding: 0.25rem;
  transition: all 0.2s ease;
}

.close-btn:hover {
  color: #dc3545;
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
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #495057;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.form-control-modern {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.2s ease;
}

.form-control-modern:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-hint {
  display: block;
  margin-top: 0.35rem;
  font-size: 0.8rem;
  color: #6c757d;
}

.alert {
  padding: 0.75rem 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.alert-danger {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.full-key {
  display: block;
  background: #f5f5f5;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #c7254e;
  word-break: break-all;
}

.full-value {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  font-size: 0.95rem;
  white-space: pre-wrap;
  word-break: break-word;
  max-height: 300px;
  overflow-y: auto;
}

/* New Language Banner */
.new-language-banner {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  color: white;
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.banner-content {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1rem;
}

.banner-icon {
  width: 50px;
  height: 50px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.banner-text {
  flex: 1;
}

.banner-text h4 {
  margin: 0 0 0.5rem 0;
  font-size: 1.2rem;
  font-weight: 700;
}

.banner-text p {
  margin: 0;
  opacity: 0.9;
  font-size: 0.95rem;
  line-height: 1.5;
}

.banner-close {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.banner-close:hover {
  background: rgba(255, 255, 255, 0.3);
}

.banner-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.banner-actions .action-btn {
  background: rgba(255, 255, 255, 0.2);
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.banner-actions .action-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Inline Editing */
.inline-edit-cell {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.inline-edit-input {
  width: 100%;
  padding: 0.5rem;
  border: 2px solid #667eea;
  border-radius: 6px;
  font-size: 0.9rem;
  resize: vertical;
  min-height: 60px;
  font-family: inherit;
}

.inline-edit-input:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
}

.inline-edit-actions {
  display: flex;
  gap: 0.35rem;
  justify-content: flex-end;
}

.inline-btn {
  width: 28px;
  height: 28px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  transition: all 0.2s ease;
}

.inline-btn.save {
  background: #28a745;
  color: white;
}

.inline-btn.save:hover {
  background: #218838;
}

.inline-btn.cancel {
  background: #dc3545;
  color: white;
}

.inline-btn.cancel:hover {
  background: #c82333;
}

.translation-value.editable {
  cursor: text;
  padding: 0.25rem;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.translation-value.editable:hover {
  background: rgba(102, 126, 234, 0.1);
}

.value-cell {
  position: relative;
  padding-right: 28px;
}

.inline-edit-btn {
  position: absolute;
  right: 0;
  top: 50%;
  transform: translateY(-50%);
  background: transparent;
  border: none;
  width: 24px;
  height: 24px;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #999;
  opacity: 0;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.value-cell:hover .inline-edit-btn {
  opacity: 1;
}

.inline-edit-btn:hover {
  background: #667eea;
  color: white;
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

  .data-table-container {
    overflow-x: auto;
  }

  .modern-table {
    min-width: 700px;
  }

  .pagination-container {
    flex-direction: column;
  }

  .active-locale-bar {
    flex-direction: column;
    gap: 0.75rem;
    text-align: center;
  }

  .missing-section {
    max-height: none;
  }

  .missing-item-header {
    flex-direction: column;
    align-items: flex-start;
  }
}

/* Missing Translations Section */
.check-missing-btn {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 0.35rem 0.75rem;
  border-radius: 6px;
  font-size: 0.8rem;
  cursor: pointer;
  margin-left: 1rem;
  transition: all 0.2s ease;
}

.check-missing-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.missing-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
  margin-bottom: 1.5rem;
  overflow: hidden;
  border: 2px solid #ffc107;
}

.missing-header {
  background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
  padding: 1rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f0d78c;
}

.missing-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  color: #856404;
}

.missing-title i {
  color: #f39c12;
}

.missing-badge {
  background: #f39c12;
  color: white;
  padding: 0.15rem 0.5rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 700;
}

.missing-actions {
  display: flex;
  gap: 0.35rem;
}

.missing-actions .action-btn-small {
  background: rgba(0, 0, 0, 0.1);
  color: #856404;
  width: 30px;
  height: 30px;
}

.missing-actions .action-btn-small:hover {
  background: rgba(0, 0, 0, 0.15);
}

.missing-loading {
  padding: 2rem;
  text-align: center;
  color: #856404;
}

.missing-empty {
  padding: 2rem;
  text-align: center;
  color: #28a745;
}

.missing-empty i {
  font-size: 2.5rem;
  margin-bottom: 0.75rem;
  display: block;
}

.missing-empty p {
  margin: 0;
  font-weight: 500;
}

.missing-list-wrapper {
  padding: 1rem 1.25rem;
}

.missing-hint {
  font-size: 0.9rem;
  color: #666;
  margin: 0 0 1rem 0;
}

.missing-items {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.missing-item-card {
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.2s ease;
}

.missing-item-card:hover {
  border-color: #667eea;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
}

.missing-item-card.editing {
  border-color: #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.missing-item-header {
  padding: 0.75rem 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  gap: 1rem;
}

.missing-key-code {
  background: #fff;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.85rem;
  color: #c7254e;
  border: 1px solid #e9ecef;
}

.missing-default-hint {
  font-size: 0.8rem;
  color: #888;
  flex-shrink: 0;
}

.missing-item-form {
  padding: 0.75rem 1rem;
  background: white;
  border-top: 1px solid #e9ecef;
}

.missing-input {
  width: 100%;
  padding: 0.625rem;
  border: 2px solid #667eea;
  border-radius: 6px;
  font-size: 0.9rem;
  resize: vertical;
  min-height: 60px;
  font-family: inherit;
  margin-bottom: 0.75rem;
}

.missing-input:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
}

.missing-form-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

.action-btn.small {
  padding: 0.5rem 0.875rem;
  font-size: 0.85rem;
}

.missing-more-info {
  background: #e0f7fa;
  color: #00838f;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.85rem;
  text-align: center;
  margin-top: 0.5rem;
}

.missing-more-info i {
  margin-right: 0.35rem;
}
</style>
