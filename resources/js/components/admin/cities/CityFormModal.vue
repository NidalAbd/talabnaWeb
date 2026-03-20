<template>
  <div class="modal-overlay-advanced" @click.self="$emit('close')">
    <div class="modal-container-advanced">
      <div class="modal-header-advanced">
        <h2>
          <i :class="mode === 'create' ? 'fas fa-plus-circle' : 'fas fa-edit'"></i>
          {{ mode === 'create' ? 'Create City' : 'Edit City' }}
        </h2>
        <button class="modal-close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit">
        <div class="modal-body-advanced">
          <!-- Language Tabs (all active languages) -->
          <div class="tabs-container" style="flex-wrap: wrap;">
            <button
              v-for="lang in languages"
              :key="lang.code"
              type="button"
              class="tab-btn"
              :class="{ active: activeTab === lang.code }"
              @click="activeTab = lang.code"
            >
              {{ lang.native_name || lang.name }}
              <span v-if="formData.name[lang.code]" style="color: #10b981; margin-left: 4px;">&#10003;</span>
            </button>
          </div>

          <!-- Language Input (dynamic for each language) -->
          <div v-for="lang in languages" :key="'input-'+lang.code" v-show="activeTab === lang.code" class="tab-content">
            <div class="form-group-modern">
              <label class="form-label-modern">
                City Name ({{ lang.name }}) <span v-if="lang.code === 'en'" class="required">*</span>
              </label>
              <input
                v-model="formData.name[lang.code]"
                type="text"
                class="form-input-modern"
                :placeholder="`Enter city name in ${lang.name}`"
                :required="lang.code === 'en'"
                :dir="['ar','he','fa','ur','ps','ku','yi','sd'].includes(lang.code) ? 'rtl' : 'ltr'"
              >
            </div>
          </div>

          <!-- Country Selection -->
          <div class="form-group-modern">
            <label class="form-label-modern">
              Country <span class="required">*</span>
            </label>
            <select
              v-model="formData.country_id"
              class="form-input-modern"
              required
            >
              <option value="">Select a country</option>
              <option v-for="country in countries" :key="country.id" :value="country.id">
                {{ country.name.en }}
              </option>
            </select>
          </div>

          <!-- Image Upload -->
          <div class="form-group-modern">
            <label class="form-label-modern">City Image</label>
            <div class="image-upload-container">
              <div v-if="imagePreview" class="image-preview">
                <img :src="imagePreview" alt="Image preview">
                <button type="button" class="remove-image-btn" @click="removeImage">
                  <i class="fas fa-times"></i>
                </button>
              </div>
              <label v-else class="upload-label">
                <i class="fas fa-cloud-upload-alt"></i>
                <span>Click to upload city image</span>
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
            {{ isSubmitting ? 'Saving...' : (mode === 'create' ? 'Create City' : 'Update City') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useCities } from '../../../composables/useCities'

const props = defineProps({
  city: Object,
  mode: {
    type: String,
    default: 'create',
    validator: (value) => ['create', 'edit'].includes(value)
  }
})

const emit = defineEmits(['close', 'saved'])

const { createCity, updateCity } = useCities()

const activeTab = ref('en')
const isSubmitting = ref(false)
const errorMessage = ref('')
const imagePreview = ref(null)
const imageFile = ref(null)
const countries = ref([])
const languages = ref([])

const formData = ref({
  name: {},
  country_id: ''
})

onMounted(async () => {
  await loadLanguages()
  await loadCountries()
})

const loadLanguages = async () => {
  try {
    const response = await fetch('/api/languages')
    if (response.ok) {
      const data = await response.json()
      languages.value = data.languages || []
      // Initialize name fields, preserving existing data
      const nameObj = {}
      for (const lang of languages.value) {
        nameObj[lang.code] = formData.value.name[lang.code] || ''
      }
      formData.value.name = nameObj
      // Re-apply city data after languages loaded (edit mode)
      if (props.city && props.mode === 'edit') {
        applyCityData(props.city)
      }
    }
  } catch (_) {
    // Fallback to ar+en
    languages.value = [
      { code: 'ar', name: 'Arabic', native_name: 'العربية' },
      { code: 'en', name: 'English', native_name: 'English' },
    ]
    formData.value.name = { ar: '', en: '' }
  }
}

const loadCountries = async () => {
  try {
    const response = await fetch('/api/admin/countries?per_page=1000')
    const data = await response.json()
    countries.value = data.data || []
  } catch (error) {
    console.error('Error loading countries:', error)
  }
}

const applyCityData = (city) => {
  if (!city) return
  const nameObj = {}
  if (city.name && typeof city.name === 'object') {
    for (const [key, value] of Object.entries(city.name)) {
      nameObj[key] = value || ''
    }
  }
  for (const lang of languages.value) {
    if (!nameObj[lang.code]) {
      nameObj[lang.code] = ''
    }
  }
  formData.value = {
    name: nameObj,
    country_id: city.country_id || ''
  }
  if (city.image_url) {
    imagePreview.value = '/storage/' + city.image_url
  }
}

// Initialize form data when editing
watch(() => props.city, (newCity) => {
  if (newCity && props.mode === 'edit') {
    applyCityData(newCity)
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
    // Send all language translations
    for (const [locale, value] of Object.entries(formData.value.name)) {
      if (value) {
        submitData.append(`name[${locale}]`, value)
      }
    }
    submitData.append('country_id', formData.value.country_id)

    if (imageFile.value) {
      submitData.append('image', imageFile.value)
    }

    if (props.mode === 'create') {
      await createCity(submitData)
    } else {
      await updateCity(props.city.id, submitData)
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
