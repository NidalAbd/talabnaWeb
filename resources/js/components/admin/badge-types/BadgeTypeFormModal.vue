<template>
  <div class="modal-overlay-advanced" @click.self="$emit('close')">
    <div class="modal-container-advanced">
      <div class="modal-header-advanced">
        <h2>
          <i :class="mode === 'create' ? 'fas fa-plus-circle' : 'fas fa-edit'"></i>
          {{ mode === 'create' ? 'Create Badge Type' : 'Edit Badge Type' }}
        </h2>
        <button class="modal-close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit">
        <div class="modal-body-advanced">
          <!-- Language Tabs -->
          <div class="tabs-container">
            <button
              type="button"
              class="tab-btn"
              :class="{ active: activeTab === 'en' }"
              @click="activeTab = 'en'"
            >
              English
            </button>
            <button
              type="button"
              class="tab-btn"
              :class="{ active: activeTab === 'ar' }"
              @click="activeTab = 'ar'"
            >
              Arabic
            </button>
          </div>

          <!-- English Tab -->
          <div v-show="activeTab === 'en'" class="tab-content">
            <div class="form-group-modern">
              <label class="form-label-modern">
                Badge Name (English) <span class="required">*</span>
              </label>
              <input
                v-model="formData.name.en"
                type="text"
                class="form-input-modern"
                placeholder="Enter badge name in English"
                required
              >
            </div>
          </div>

          <!-- Arabic Tab -->
          <div v-show="activeTab === 'ar'" class="tab-content">
            <div class="form-group-modern">
              <label class="form-label-modern">
                Badge Name (Arabic) <span class="required">*</span>
              </label>
              <input
                v-model="formData.name.ar"
                type="text"
                class="form-input-modern"
                placeholder="أدخل اسم الشارة بالعربية"
                required
                dir="rtl"
              >
            </div>
          </div>

          <!-- Slug -->
          <div class="form-group-modern">
            <label class="form-label-modern">Slug</label>
            <input
              v-model="formData.slug"
              type="text"
              class="form-input-modern"
              placeholder="badge-slug (auto-generated if empty)"
            >
          </div>

          <!-- Colors Row -->
          <div class="form-row">
            <div class="form-group-modern">
              <label class="form-label-modern">
                Primary Color <span class="required">*</span>
              </label>
              <input
                v-model="formData.color"
                type="color"
                class="form-input-modern"
                required
              >
            </div>

            <div class="form-group-modern">
              <label class="form-label-modern">Secondary Color</label>
              <input
                v-model="formData.secondary_color"
                type="color"
                class="form-input-modern"
              >
            </div>
          </div>

          <!-- Icon -->
          <div class="form-group-modern">
            <label class="form-label-modern">Icon Class</label>
            <input
              v-model="formData.icon"
              type="text"
              class="form-input-modern"
              placeholder="fas fa-certificate"
            >
            <small class="form-help-text">FontAwesome icon class (e.g., fas fa-star, fas fa-gem)</small>
          </div>

          <!-- Numeric Fields Row -->
          <div class="form-row">
            <div class="form-group-modern">
              <label class="form-label-modern">
                Points Per Day <span class="required">*</span>
              </label>
              <input
                v-model.number="formData.points_per_day"
                type="number"
                class="form-input-modern"
                min="0"
                required
              >
            </div>

            <div class="form-group-modern">
              <label class="form-label-modern">
                Priority <span class="required">*</span>
              </label>
              <input
                v-model.number="formData.priority"
                type="number"
                class="form-input-modern"
                min="0"
                required
              >
              <small class="form-help-text">Lower number = higher priority</small>
            </div>

            <div class="form-group-modern">
              <label class="form-label-modern">
                View Boost % <span class="required">*</span>
              </label>
              <input
                v-model.number="formData.view_boost_percent"
                type="number"
                class="form-input-modern"
                min="0"
                max="100"
                required
              >
            </div>
          </div>

          <!-- Checkboxes -->
          <div class="form-group-modern">
            <label class="checkbox-label-modern">
              <input
                v-model="formData.is_active"
                type="checkbox"
                class="form-checkbox-modern"
              >
              <span>Active</span>
            </label>
          </div>

          <div class="form-group-modern">
            <label class="checkbox-label-modern">
              <input
                v-model="formData.is_default"
                type="checkbox"
                class="form-checkbox-modern"
              >
              <span>Set as Default Badge</span>
            </label>
          </div>

          <!-- Preview -->
          <div class="form-group-modern">
            <label class="form-label-modern">Preview</label>
            <div
              class="badge-preview"
              :style="{
                background: `linear-gradient(135deg, ${formData.color}, ${formData.secondary_color || formData.color})`,
                borderLeft: `4px solid ${formData.color}`
              }"
            >
              <i :class="formData.icon || 'fas fa-certificate'" :style="{ fontSize: '2rem', color: getContrastColor(formData.color) }"></i>
              <div :style="{ color: getContrastColor(formData.color), marginTop: '0.5rem' }">
                <strong>{{ formData.name.en || 'Badge Name' }}</strong>
              </div>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="error-message-modern">
            <i class="fas fa-exclamation-circle"></i>
            {{ errorMessage }}
          </div>
        </div>

        <div class="modal-footer-advanced">
          <button type="button" class="btn-cancel-advanced" @click="$emit('close')">
            Cancel
          </button>
          <button type="submit" class="btn-submit-advanced" :disabled="isSubmitting">
            <i :class="isSubmitting ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
            {{ isSubmitting ? 'Saving...' : (mode === 'create' ? 'Create Badge Type' : 'Update Badge Type') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useBadgeTypes } from '../../../composables/useBadgeTypes'

const props = defineProps({
  badgeType: Object,
  mode: {
    type: String,
    default: 'create',
    validator: (value) => ['create', 'edit'].includes(value)
  }
})

const emit = defineEmits(['close', 'saved'])

const { createBadgeType, updateBadgeType } = useBadgeTypes()

const activeTab = ref('en')
const isSubmitting = ref(false)
const errorMessage = ref('')

const formData = ref({
  name: {
    en: '',
    ar: ''
  },
  slug: '',
  color: '#3498db',
  secondary_color: '#2980b9',
  icon: 'fas fa-certificate',
  points_per_day: 0,
  priority: 0,
  view_boost_percent: 0,
  is_active: true,
  is_default: false
})

// Function to determine contrasting text color based on background
const getContrastColor = (hexColor) => {
  if (!hexColor) return '#ffffff'
  // Remove # if present
  const hex = hexColor.replace('#', '')
  // Convert to RGB
  const r = parseInt(hex.substr(0, 2), 16)
  const g = parseInt(hex.substr(2, 2), 16)
  const b = parseInt(hex.substr(4, 2), 16)
  // Calculate luminance
  const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255
  // Return dark text for light backgrounds, white for dark backgrounds
  return luminance > 0.5 ? '#212529' : '#ffffff'
}

// Initialize form data when editing
watch(() => props.badgeType, (newBadgeType) => {
  if (newBadgeType && props.mode === 'edit') {
    formData.value = {
      name: {
        en: newBadgeType.name?.en || '',
        ar: newBadgeType.name?.ar || ''
      },
      slug: newBadgeType.slug || '',
      color: newBadgeType.color || '#3498db',
      secondary_color: newBadgeType.secondary_color || '#2980b9',
      icon: newBadgeType.icon || 'fas fa-certificate',
      points_per_day: newBadgeType.points_per_day || 0,
      priority: newBadgeType.priority || 0,
      view_boost_percent: newBadgeType.view_boost_percent || 0,
      is_active: newBadgeType.is_active ?? true,
      is_default: newBadgeType.is_default ?? false
    }
  }
}, { immediate: true })

const handleSubmit = async () => {
  isSubmitting.value = true
  errorMessage.value = ''

  try {
    const submitData = {
      name: {
        en: formData.value.name.en,
        ar: formData.value.name.ar
      },
      slug: formData.value.slug,
      color: formData.value.color,
      secondary_color: formData.value.secondary_color,
      icon: formData.value.icon,
      points_per_day: formData.value.points_per_day,
      priority: formData.value.priority,
      view_boost_percent: formData.value.view_boost_percent,
      is_active: formData.value.is_active,
      is_default: formData.value.is_default
    }

    if (props.mode === 'create') {
      await createBadgeType(submitData)
    } else {
      await updateBadgeType(props.badgeType.id, submitData)
    }

    emit('saved')
  } catch (error) {
    errorMessage.value = error.message || 'An error occurred while saving'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.badge-preview {
  padding: 2rem;
  border-radius: 8px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 120px;
}

.form-help-text {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.875rem;
  color: #6c757d;
}
</style>
