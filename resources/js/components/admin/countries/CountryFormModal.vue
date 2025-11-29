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
    submitData.append('country_code', formData.value.country_code.toUpperCase())

    if (formData.value.currency_code) {
      submitData.append('currency_code', formData.value.currency_code.toUpperCase())
    }

    if (formData.value.currency_name.en) {
      submitData.append('currency_name[en]', formData.value.currency_name.en)
    }

    if (formData.value.currency_name.ar) {
      submitData.append('currency_name[ar]', formData.value.currency_name.ar)
    }

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
