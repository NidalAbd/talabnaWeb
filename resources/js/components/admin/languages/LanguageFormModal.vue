<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-dialog-advanced">
      <div class="modal-header">
        <h3 class="modal-title">
          <i :class="mode === 'create' ? 'fas fa-plus-circle' : 'fas fa-edit'"></i>
          {{ mode === 'create' ? 'Add New Language' : 'Edit Language' }}
        </h3>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit">
        <div class="modal-body">
          <!-- Language Code -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-code"></i>
              Language Code *
            </label>
            <input
              v-model="formData.code"
              type="text"
              class="form-control-modern"
              placeholder="e.g., en, fr, de, es"
              :class="{ 'is-invalid': errors.code }"
              :disabled="mode === 'edit'"
              maxlength="10"
            >
            <small class="form-hint">2-10 character ISO language code (e.g., en, fr, zh-CN)</small>
            <div v-if="errors.code" class="error-message">{{ errors.code }}</div>
          </div>

          <!-- Language Name (English) -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-tag"></i>
              Language Name (English) *
            </label>
            <input
              v-model="formData.name"
              type="text"
              class="form-control-modern"
              placeholder="e.g., French, German, Spanish"
              :class="{ 'is-invalid': errors.name }"
            >
            <div v-if="errors.name" class="error-message">{{ errors.name }}</div>
          </div>

          <!-- Native Name -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-globe"></i>
              Native Name *
            </label>
            <input
              v-model="formData.native_name"
              type="text"
              class="form-control-modern"
              placeholder="e.g., Français, Deutsch, Español"
              :class="{ 'is-invalid': errors.native_name }"
            >
            <small class="form-hint">The name of the language in that language</small>
            <div v-if="errors.native_name" class="error-message">{{ errors.native_name }}</div>
          </div>

          <!-- Text Direction -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-align-left"></i>
              Text Direction *
            </label>
            <div class="direction-toggle">
              <button
                type="button"
                class="direction-btn"
                :class="{ active: formData.direction === 'ltr' }"
                @click="formData.direction = 'ltr'"
              >
                <i class="fas fa-arrow-right"></i>
                LTR (Left to Right)
              </button>
              <button
                type="button"
                class="direction-btn"
                :class="{ active: formData.direction === 'rtl' }"
                @click="formData.direction = 'rtl'"
              >
                <i class="fas fa-arrow-left"></i>
                RTL (Right to Left)
              </button>
            </div>
            <div v-if="errors.direction" class="error-message">{{ errors.direction }}</div>
          </div>

          <!-- Sort Order -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-sort"></i>
              Sort Order
            </label>
            <input
              v-model.number="formData.sort_order"
              type="number"
              class="form-control-modern"
              placeholder="0"
              min="0"
            >
            <small class="form-hint">Lower numbers appear first in language lists</small>
          </div>

          <!-- Options -->
          <div class="form-group">
            <label class="form-label">Options</label>
            <div class="checkbox-group">
              <label class="checkbox-label">
                <input
                  v-model="formData.is_active"
                  type="checkbox"
                  class="checkbox-input"
                >
                <span class="checkbox-text">
                  <i class="fas fa-check-circle"></i>
                  Active Language
                </span>
              </label>
              <label class="checkbox-label" v-if="mode === 'create'">
                <input
                  v-model="formData.is_default"
                  type="checkbox"
                  class="checkbox-input"
                >
                <span class="checkbox-text">
                  <i class="fas fa-star"></i>
                  Set as Default Language
                </span>
              </label>
            </div>
          </div>

          <!-- Quick Add Common Languages (Create mode only) -->
          <div v-if="mode === 'create'" class="form-group">
            <label class="form-label">
              <i class="fas fa-magic"></i>
              Quick Add Common Language
            </label>
            <select v-model="selectedQuickLanguage" class="form-control-modern" @change="applyQuickLanguage">
              <option value="">-- Select a common language --</option>
              <option v-for="(lang, code) in commonLanguages" :key="code" :value="code">
                {{ lang.name }} ({{ lang.native }})
              </option>
            </select>
          </div>

          <!-- General Error Message -->
          <div v-if="errorMessage" class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            {{ errorMessage }}
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="action-btn secondary" @click="$emit('close')">
            <i class="fas fa-times"></i>
            Cancel
          </button>
          <button type="submit" class="action-btn primary" :disabled="submitting">
            <i :class="submitting ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
            {{ submitting ? 'Saving...' : (mode === 'create' ? 'Add Language' : 'Update Language') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { useLanguages } from '../../../composables/useLanguages'

const props = defineProps({
  language: {
    type: Object,
    default: null
  },
  mode: {
    type: String,
    default: 'create',
    validator: (value) => ['create', 'edit'].includes(value)
  }
})

const emit = defineEmits(['close', 'saved'])

const { createLanguage, updateLanguage } = useLanguages()

const selectedQuickLanguage = ref('')
const submitting = ref(false)
const errorMessage = ref('')
const errors = reactive({})

const formData = reactive({
  code: '',
  name: '',
  native_name: '',
  direction: 'ltr',
  is_active: true,
  is_default: false,
  sort_order: 0
})

// Common languages for quick add
const commonLanguages = {
  'fr': { name: 'French', native: 'Français', direction: 'ltr' },
  'de': { name: 'German', native: 'Deutsch', direction: 'ltr' },
  'es': { name: 'Spanish', native: 'Español', direction: 'ltr' },
  'it': { name: 'Italian', native: 'Italiano', direction: 'ltr' },
  'pt': { name: 'Portuguese', native: 'Português', direction: 'ltr' },
  'ru': { name: 'Russian', native: 'Русский', direction: 'ltr' },
  'zh': { name: 'Chinese', native: '中文', direction: 'ltr' },
  'ja': { name: 'Japanese', native: '日本語', direction: 'ltr' },
  'ko': { name: 'Korean', native: '한국어', direction: 'ltr' },
  'hi': { name: 'Hindi', native: 'हिन्दी', direction: 'ltr' },
  'tr': { name: 'Turkish', native: 'Türkçe', direction: 'ltr' },
  'nl': { name: 'Dutch', native: 'Nederlands', direction: 'ltr' },
  'pl': { name: 'Polish', native: 'Polski', direction: 'ltr' },
  'sv': { name: 'Swedish', native: 'Svenska', direction: 'ltr' },
  'he': { name: 'Hebrew', native: 'עברית', direction: 'rtl' },
  'fa': { name: 'Persian', native: 'فارسی', direction: 'rtl' },
  'ur': { name: 'Urdu', native: 'اردو', direction: 'rtl' }
}

// Initialize form data if editing
watch(() => props.language, (newLanguage) => {
  if (newLanguage && props.mode === 'edit') {
    formData.code = newLanguage.code || ''
    formData.name = newLanguage.name || ''
    formData.native_name = newLanguage.native_name || ''
    formData.direction = newLanguage.direction || 'ltr'
    formData.is_active = newLanguage.is_active ?? true
    formData.is_default = newLanguage.is_default ?? false
    formData.sort_order = newLanguage.sort_order || 0
  }
}, { immediate: true })

const applyQuickLanguage = () => {
  if (selectedQuickLanguage.value && commonLanguages[selectedQuickLanguage.value]) {
    const lang = commonLanguages[selectedQuickLanguage.value]
    formData.code = selectedQuickLanguage.value
    formData.name = lang.name
    formData.native_name = lang.native
    formData.direction = lang.direction
    selectedQuickLanguage.value = ''
  }
}

const validateForm = () => {
  Object.keys(errors).forEach(key => delete errors[key])
  let isValid = true

  if (!formData.code.trim()) {
    errors.code = 'Language code is required'
    isValid = false
  } else if (formData.code.length < 2 || formData.code.length > 10) {
    errors.code = 'Language code must be 2-10 characters'
    isValid = false
  } else if (!/^[a-zA-Z-]+$/.test(formData.code)) {
    errors.code = 'Language code can only contain letters and hyphens'
    isValid = false
  }

  if (!formData.name.trim()) {
    errors.name = 'Language name is required'
    isValid = false
  }

  if (!formData.native_name.trim()) {
    errors.native_name = 'Native name is required'
    isValid = false
  }

  if (!['ltr', 'rtl'].includes(formData.direction)) {
    errors.direction = 'Please select a text direction'
    isValid = false
  }

  return isValid
}

const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }

  submitting.value = true
  errorMessage.value = ''

  try {
    const data = {
      code: formData.code.toLowerCase(),
      name: formData.name,
      native_name: formData.native_name,
      direction: formData.direction,
      is_active: formData.is_active,
      is_default: formData.is_default,
      sort_order: formData.sort_order || 0
    }

    let savedLanguage
    if (props.mode === 'create') {
      savedLanguage = await createLanguage(data)
      emit('saved', { language: savedLanguage || data, isNew: true })
    } else {
      savedLanguage = await updateLanguage(props.language.id, data)
      emit('saved', { language: savedLanguage || data, isNew: false })
    }
  } catch (error) {
    errorMessage.value = error.message || 'Failed to save language'
    console.error('Error saving language:', error)
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
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

.form-control-modern:disabled {
  background: #f8f9fa;
  cursor: not-allowed;
}

.form-control-modern.is-invalid {
  border-color: #dc3545;
}

.form-hint {
  display: block;
  margin-top: 0.35rem;
  font-size: 0.8rem;
  color: #6c757d;
}

.error-message {
  color: #dc3545;
  font-size: 0.85rem;
  margin-top: 0.35rem;
}

/* Direction Toggle */
.direction-toggle {
  display: flex;
  gap: 0.5rem;
}

.direction-btn {
  flex: 1;
  padding: 0.75rem 1rem;
  border: 2px solid #e9ecef;
  background: white;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  color: #6c757d;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.direction-btn:hover {
  background: #f8f9fa;
}

.direction-btn.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-color: #667eea;
}

/* Checkboxes */
.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  cursor: pointer;
  padding: 0.75rem 1rem;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.checkbox-label:hover {
  background: #f8f9fa;
  border-color: #667eea;
}

.checkbox-input {
  width: 20px;
  height: 20px;
  cursor: pointer;
  margin-right: 0.75rem;
}

.checkbox-text {
  font-weight: 500;
  color: #495057;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Alert */
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

/* Buttons */
.action-btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.action-btn.primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.action-btn.primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.action-btn.secondary {
  background: #6c757d;
  color: white;
}

.action-btn.secondary:hover {
  background: #5a6268;
}

.action-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
  .modal-dialog-advanced {
    max-width: 100%;
    margin: 0;
    border-radius: 0;
    max-height: 100vh;
  }

  .direction-toggle {
    flex-direction: column;
  }
}
</style>
