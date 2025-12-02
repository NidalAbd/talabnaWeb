<template>
  <div class="modal-overlay-advanced" @click.self="$emit('close')">
    <div class="modal-container-advanced">
      <div class="modal-header-advanced">
        <h2>
          <i :class="mode === 'create' ? 'fas fa-plus-circle' : 'fas fa-edit'"></i>
          {{ mode === 'create' ? 'Create Country' : 'Edit Country' }}
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
                Country Name (English) <span class="required">*</span>
              </label>
              <input
                v-model="formData.name.en"
                type="text"
                class="form-input-modern"
                placeholder="Enter country name in English"
                required
              >
            </div>

            <div class="form-group-modern">
              <label class="form-label-modern">Currency Name (English)</label>
              <input
                v-model="formData.currency_name.en"
                type="text"
                class="form-input-modern"
                placeholder="Enter currency name in English"
              >
            </div>
          </div>

          <!-- Arabic Tab -->
          <div v-show="activeTab === 'ar'" class="tab-content">
            <div class="form-group-modern">
              <label class="form-label-modern">
                Country Name (Arabic) <span class="required">*</span>
              </label>
              <input
                v-model="formData.name.ar"
                type="text"
                class="form-input-modern"
                placeholder="أدخل اسم الدولة بالعربية"
                required
                dir="rtl"
              >
            </div>

            <div class="form-group-modern">
              <label class="form-label-modern">Currency Name (Arabic)</label>
              <input
                v-model="formData.currency_name.ar"
                type="text"
                class="form-input-modern"
                placeholder="أدخل اسم العملة بالعربية"
                dir="rtl"
              >
            </div>
          </div>

          <!-- Country Code & Currency Code -->
          <div class="form-row">
            <div class="form-group-modern">
              <label class="form-label-modern">
                Country Code <span class="required">*</span>
              </label>
              <input
                v-model="formData.country_code"
                type="text"
                class="form-input-modern"
                placeholder="e.g., US, GB, SA"
                maxlength="3"
                required
              >
            </div>

            <div class="form-group-modern">
              <label class="form-label-modern">Currency Code</label>
              <input
                v-model="formData.currency_code"
                type="text"
                class="form-input-modern"
                placeholder="e.g., USD, GBP, SAR"
                maxlength="3"
              >
            </div>
          </div>

          <!-- Flag Upload -->
          <div class="form-group-modern">
            <label class="form-label-modern">Country Flag</label>
            <div class="image-upload-container">
              <div v-if="imagePreview" class="image-preview">
                <img :src="imagePreview" alt="Flag preview">
                <button type="button" class="remove-image-btn" @click="removeImage">
                  <i class="fas fa-times"></i>
                </button>
              </div>
              <label v-else class="upload-label">
                <i class="fas fa-cloud-upload-alt"></i>
                <span>Click to upload flag image</span>
                <input
                  type="file"
                  accept="image/*"
                  @change="handleImageUpload"
                  hidden
                >
              </label>
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
            {{ isSubmitting ? 'Saving...' : (mode === 'create' ? 'Create Country' : 'Update Country') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useCountries } from '../../../composables/useCountries'

const props = defineProps({
  country: Object,
  mode: {
    type: String,
    default: 'create',
    validator: (value) => ['create', 'edit'].includes(value)
  }
})

const emit = defineEmits(['close', 'saved'])

const { createCountry, updateCountry } = useCountries()

const activeTab = ref('en')
const isSubmitting = ref(false)
const errorMessage = ref('')
const imagePreview = ref(null)
const imageFile = ref(null)
const originalCountryCode = ref('')

const formData = ref({
  name: {
    en: '',
    ar: ''
  },
  currency_name: {
    en: '',
    ar: ''
  },
  country_code: '',
  currency_code: ''
})

// Initialize form data when editing
watch(() => props.country, (newCountry) => {
  if (newCountry && props.mode === 'edit') {
    originalCountryCode.value = newCountry.country_code || ''
    formData.value = {
      name: {
        en: newCountry.name?.en || '',
        ar: newCountry.name?.ar || ''
      },
      currency_name: {
        en: newCountry.currency_name?.en || '',
        ar: newCountry.currency_name?.ar || ''
      },
      country_code: newCountry.country_code || '',
      currency_code: newCountry.currency_code || ''
    }

    if (newCountry.flag_url) {
      imagePreview.value = `/storage/${newCountry.flag_url}`
    }
  } else {
    originalCountryCode.value = ''
  }
}, { immediate: true })

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    imageFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const removeImage = () => {
  imagePreview.value = null
  imageFile.value = null
}

const handleSubmit = async () => {
  isSubmitting.value = true
  errorMessage.value = ''

  try {
    const submitData = new FormData()

    // Always include name fields
    submitData.append('name[en]', formData.value.name.en || '')
    submitData.append('name[ar]', formData.value.name.ar || '')

    // Only include country_code if it has changed from original (for edit) or has value (for create)
    const newCountryCode = formData.value.country_code?.trim().toUpperCase().substring(0, 3) || ''
    if (props.mode === 'create') {
      if (newCountryCode) {
        submitData.append('country_code', newCountryCode)
      }
    } else {
      // Only send if changed
      if (newCountryCode !== originalCountryCode.value.toUpperCase()) {
        submitData.append('country_code', newCountryCode)
      }
    }

    // Only include currency_code if it has a value
    if (formData.value.currency_code && formData.value.currency_code.trim()) {
      submitData.append('currency_code', formData.value.currency_code.trim().toUpperCase().substring(0, 3))
    }

    // Include currency name fields
    submitData.append('currency_name[en]', formData.value.currency_name.en || '')
    submitData.append('currency_name[ar]', formData.value.currency_name.ar || '')

    // Include flag if new one was uploaded
    if (imageFile.value) {
      submitData.append('flag', imageFile.value)
    }

    if (props.mode === 'create') {
      await createCountry(submitData)
    } else {
      await updateCountry(props.country.id, submitData)
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
.modal-overlay-advanced {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
}

.modal-container-advanced {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow: hidden;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
  animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.modal-header-advanced {
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header-advanced h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.modal-close-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  color: white;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.modal-body-advanced {
  padding: 2rem;
  max-height: 60vh;
  overflow-y: auto;
}

/* Tabs */
.tabs-container {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  background: #f8f9fa;
  padding: 0.375rem;
  border-radius: 12px;
}

.tab-btn {
  flex: 1;
  padding: 0.75rem 1rem;
  border: none;
  background: transparent;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #6c757d;
}

.tab-btn.active {
  background: white;
  color: #667eea;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.tab-content {
  margin-bottom: 1rem;
}

/* Form Styles */
.form-group-modern {
  margin-bottom: 1.5rem;
}

.form-label-modern {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #2c3e50;
  font-size: 0.95rem;
}

.required {
  color: #dc3545;
}

.form-input-modern {
  width: 100%;
  padding: 0.875rem 1.25rem;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.2s ease;
  box-sizing: border-box;
}

.form-input-modern:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

/* Image Upload */
.image-upload-container {
  border: 2px dashed #e9ecef;
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.2s ease;
}

.image-upload-container:hover {
  border-color: #667eea;
}

.upload-label {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  cursor: pointer;
  color: #6c757d;
  transition: all 0.2s ease;
}

.upload-label:hover {
  color: #667eea;
  background: rgba(102, 126, 234, 0.05);
}

.upload-label i {
  font-size: 2.5rem;
  margin-bottom: 0.75rem;
}

.image-preview {
  position: relative;
  padding: 1rem;
  display: flex;
  justify-content: center;
  background: #f8f9fa;
}

.image-preview img {
  max-width: 200px;
  max-height: 150px;
  border-radius: 8px;
  object-fit: contain;
}

.remove-image-btn {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #dc3545;
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.remove-image-btn:hover {
  background: #c82333;
  transform: scale(1.1);
}

/* Error Message */
.error-message-modern {
  background: #fee;
  color: #dc3545;
  padding: 1rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-top: 1rem;
  font-weight: 500;
}

/* Footer */
.modal-footer-advanced {
  padding: 1.5rem 2rem;
  background: #f8f9fa;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  border-top: 1px solid #e9ecef;
}

.btn-cancel-advanced {
  padding: 0.875rem 2rem;
  border: 2px solid #e9ecef;
  background: white;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #6c757d;
}

.btn-cancel-advanced:hover {
  background: #f8f9fa;
  border-color: #dee2e6;
}

.btn-submit-advanced {
  padding: 0.875rem 2rem;
  border: none;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-submit-advanced:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-submit-advanced:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Responsive */
@media (max-width: 576px) {
  .modal-container-advanced {
    max-height: 100vh;
    border-radius: 0;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .modal-footer-advanced {
    flex-direction: column;
  }

  .btn-cancel-advanced,
  .btn-submit-advanced {
    width: 100%;
    justify-content: center;
  }
}
</style>
