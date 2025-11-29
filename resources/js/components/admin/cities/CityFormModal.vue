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
                City Name (English) <span class="required">*</span>
              </label>
              <input
                v-model="formData.name.en"
                type="text"
                class="form-input-modern"
                placeholder="Enter city name in English"
                required
              >
            </div>
          </div>

          <!-- Arabic Tab -->
          <div v-show="activeTab === 'ar'" class="tab-content">
            <div class="form-group-modern">
              <label class="form-label-modern">
                City Name (Arabic) <span class="required">*</span>
              </label>
              <input
                v-model="formData.name.ar"
                type="text"
                class="form-input-modern"
                placeholder="أدخل اسم المدينة بالعربية"
                required
                dir="rtl"
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

const formData = ref({
  name: {
    en: '',
    ar: ''
  },
  country_id: ''
})

onMounted(async () => {
  await loadCountries()
})

const loadCountries = async () => {
  try {
    const response = await fetch('/api/admin/countries?per_page=1000')
    const data = await response.json()
    countries.value = data.data || []
  } catch (error) {
    console.error('Error loading countries:', error)
  }
}

// Initialize form data when editing
watch(() => props.city, (newCity) => {
  if (newCity && props.mode === 'edit') {
    formData.value = {
      name: {
        en: newCity.name?.en || '',
        ar: newCity.name?.ar || ''
      },
      country_id: newCity.country_id || ''
    }

    if (newCity.image_url) {
      imagePreview.value = `/storage/${newCity.image_url}`
    }
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
    submitData.append('name[en]', formData.value.name.en)
    submitData.append('name[ar]', formData.value.name.ar)
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
