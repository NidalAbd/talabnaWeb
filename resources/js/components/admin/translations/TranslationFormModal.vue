<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-dialog-advanced">
      <div class="modal-header">
        <h3 class="modal-title">
          <i :class="mode === 'create' ? 'fas fa-plus-circle' : 'fas fa-edit'"></i>
          {{ mode === 'create' ? 'Add New Translation' : 'Edit Translation' }}
        </h3>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit">
        <div class="modal-body">
          <!-- Language -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-globe"></i>
              Language *
            </label>
            <select
              v-model="formData.locale"
              class="form-control-modern"
              :class="{ 'is-invalid': errors.locale }"
              :disabled="mode === 'edit'"
            >
              <option value="">Select language...</option>
              <option v-for="lang in languages" :key="lang.code" :value="lang.code">
                {{ lang.name }} ({{ lang.code }})
              </option>
            </select>
            <div v-if="errors.locale" class="error-message">{{ errors.locale }}</div>
          </div>

          <!-- Group -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-folder"></i>
              Group *
            </label>
            <div class="input-with-dropdown">
              <input
                v-model="formData.group"
                type="text"
                class="form-control-modern"
                placeholder="e.g., app, auth, home, errors"
                :class="{ 'is-invalid': errors.group }"
                list="group-suggestions"
                maxlength="30"
              >
              <datalist id="group-suggestions">
                <option v-for="group in groups" :key="group" :value="group">{{ group }}</option>
              </datalist>
            </div>
            <small class="form-hint">Group name (max 30 characters). Common groups: app, auth, home, errors, common</small>
            <div v-if="errors.group" class="error-message">{{ errors.group }}</div>
          </div>

          <!-- Key -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-key"></i>
              Key *
            </label>
            <input
              v-model="formData.key"
              type="text"
              class="form-control-modern"
              placeholder="e.g., welcome_message, button_submit"
              :class="{ 'is-invalid': errors.key }"
              :disabled="mode === 'edit'"
              maxlength="150"
            >
            <small class="form-hint">Translation key (max 150 characters). Use snake_case or dot.notation</small>
            <div v-if="errors.key" class="error-message">{{ errors.key }}</div>
          </div>

          <!-- Full Key Preview -->
          <div v-if="formData.group && formData.key" class="key-preview">
            <label class="form-label">Full Key</label>
            <code class="full-key-display">{{ formData.group }}.{{ formData.key }}</code>
            <small class="form-hint">Use this key in your app: tr('{{ formData.group }}.{{ formData.key }}')</small>
          </div>

          <!-- Value -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-align-left"></i>
              Value *
            </label>
            <textarea
              v-model="formData.value"
              class="form-control-modern textarea"
              placeholder="Enter the translation value..."
              :class="{ 'is-invalid': errors.value }"
              :dir="getDirection()"
              rows="4"
            ></textarea>
            <small class="form-hint">
              The translated text. Use {placeholder} for dynamic values (e.g., "Hello, {name}!")
            </small>
            <div v-if="errors.value" class="error-message">{{ errors.value }}</div>
          </div>

          <!-- Placeholder Detection -->
          <div v-if="detectedPlaceholders.length > 0" class="placeholders-info">
            <label class="form-label">
              <i class="fas fa-code"></i>
              Detected Placeholders
            </label>
            <div class="placeholders-list">
              <span v-for="ph in detectedPlaceholders" :key="ph" class="placeholder-badge">
                {{ ph }}
              </span>
            </div>
            <small class="form-hint">These placeholders will be replaced with dynamic values at runtime</small>
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
            {{ submitting ? 'Saving...' : (mode === 'create' ? 'Add Translation' : 'Update Translation') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, computed } from 'vue'
import { useTranslations } from '../../../composables/useTranslations'

const props = defineProps({
  translation: {
    type: Object,
    default: null
  },
  mode: {
    type: String,
    default: 'create',
    validator: (value) => ['create', 'edit'].includes(value)
  },
  languages: {
    type: Array,
    default: () => []
  },
  groups: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'saved'])

const { createTranslation, updateTranslation } = useTranslations()

const submitting = ref(false)
const errorMessage = ref('')
const errors = reactive({})

const formData = reactive({
  locale: '',
  group: '',
  key: '',
  value: ''
})

// Detect placeholders in value
const detectedPlaceholders = computed(() => {
  if (!formData.value) return []
  const matches = formData.value.match(/\{[a-zA-Z_][a-zA-Z0-9_]*\}/g)
  return matches ? [...new Set(matches)] : []
})

// Initialize form data if editing
watch(() => props.translation, (newTranslation) => {
  if (newTranslation && props.mode === 'edit') {
    formData.locale = newTranslation.locale || ''
    formData.group = newTranslation.group || ''
    formData.key = newTranslation.key || ''
    formData.value = newTranslation.value || ''
  }
}, { immediate: true })

const getDirection = () => {
  if (!formData.locale) return 'ltr'
  const lang = props.languages.find(l => l.code === formData.locale)
  return lang?.direction || 'ltr'
}

const validateForm = () => {
  Object.keys(errors).forEach(key => delete errors[key])
  let isValid = true

  if (!formData.locale) {
    errors.locale = 'Please select a language'
    isValid = false
  }

  if (!formData.group.trim()) {
    errors.group = 'Group is required'
    isValid = false
  } else if (formData.group.length > 30) {
    errors.group = 'Group must be 30 characters or less'
    isValid = false
  } else if (!/^[a-zA-Z][a-zA-Z0-9_-]*$/.test(formData.group)) {
    errors.group = 'Group must start with a letter and contain only letters, numbers, underscores, and hyphens'
    isValid = false
  }

  if (!formData.key.trim()) {
    errors.key = 'Key is required'
    isValid = false
  } else if (formData.key.length > 150) {
    errors.key = 'Key must be 150 characters or less'
    isValid = false
  } else if (!/^[a-zA-Z][a-zA-Z0-9_.-]*$/.test(formData.key)) {
    errors.key = 'Key must start with a letter and contain only letters, numbers, underscores, dots, and hyphens'
    isValid = false
  }

  if (!formData.value.trim()) {
    errors.value = 'Value is required'
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
      locale: formData.locale,
      group: formData.group.toLowerCase(),
      key: formData.key,
      value: formData.value
    }

    if (props.mode === 'create') {
      await createTranslation(data)
    } else {
      await updateTranslation(props.translation.id, data)
    }

    emit('saved')
  } catch (error) {
    errorMessage.value = error.message || 'Failed to save translation'
    console.error('Error saving translation:', error)
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
  max-width: 650px;
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

.form-control-modern.textarea {
  resize: vertical;
  min-height: 100px;
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

/* Key Preview */
.key-preview {
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.full-key-display {
  display: block;
  background: white;
  padding: 0.75rem 1rem;
  border-radius: 6px;
  font-size: 0.95rem;
  color: #c7254e;
  border: 1px solid #e9ecef;
  word-break: break-all;
}

/* Placeholders Info */
.placeholders-info {
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: #e0f7fa;
  border-radius: 8px;
  border: 1px solid #b2ebf2;
}

.placeholders-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.placeholder-badge {
  background: white;
  color: #00838f;
  padding: 0.35rem 0.75rem;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  font-family: monospace;
  border: 1px solid #b2ebf2;
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
}
</style>
