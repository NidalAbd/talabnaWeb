<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-dialog-advanced">
      <div class="modal-header">
        <h3 class="modal-title">
          <i :class="mode === 'create' ? 'fas fa-plus-circle' : 'fas fa-edit'"></i>
          {{ mode === 'create' ? 'Create New Sub-Category' : 'Edit Sub-Category' }}
        </h3>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit">
        <div class="modal-body">
          <!-- Parent Category Selection -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-folder"></i>
              Parent Category *
            </label>
            <select
              v-model="formData.categories_id"
              class="form-control-modern"
              :class="{ 'is-invalid': errors.categories_id }"
            >
              <option value="">Select Parent Category</option>
              <option v-for="category in parentCategories" :key="category.id" :value="category.id">
                {{ category.name.en }}
              </option>
            </select>
            <div v-if="errors.categories_id" class="error-message">{{ errors.categories_id }}</div>
          </div>

          <!-- Image Upload -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-image"></i>
              Sub-Category Image *
            </label>
            <div class="image-upload-container">
              <div class="image-preview" v-if="imagePreview || (subcategory && subcategory.image_url)">
                <img :src="imagePreview || getImageUrl(subcategory.image_url)" alt="Preview">
                <button type="button" class="remove-image-btn" @click="removeImage">
                  <i class="fas fa-times"></i>
                </button>
              </div>
              <div v-else class="image-upload-placeholder" @click="$refs.imageInput.click()">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Click to upload image</p>
                <small>JPG, PNG, GIF, WEBP (Max 2MB)</small>
              </div>
              <input
                ref="imageInput"
                type="file"
                accept="image/*"
                @change="handleImageChange"
                style="display: none"
              >
            </div>
            <div v-if="errors.photo" class="error-message">{{ errors.photo }}</div>
          </div>

          <!-- Multilingual Names -->
          <div class="form-group">
            <label class="form-label">
              <i class="fas fa-tag"></i>
              Sub-Category Name *
            </label>

            <!-- Tabs for Languages -->
            <div class="language-tabs">
              <button
                type="button"
                class="lang-tab"
                :class="{ active: activeTab === 'en' }"
                @click="activeTab = 'en'"
              >
                <i class="fas fa-globe"></i>
                English
              </button>
              <button
                type="button"
                class="lang-tab"
                :class="{ active: activeTab === 'ar' }"
                @click="activeTab = 'ar'"
              >
                <i class="fas fa-globe"></i>
                العربية
              </button>
            </div>

            <!-- English Name -->
            <div v-show="activeTab === 'en'" class="tab-content">
              <input
                v-model="formData.name.en"
                type="text"
                class="form-control-modern"
                placeholder="Enter sub-category name in English"
                :class="{ 'is-invalid': errors['name.en'] }"
              >
              <div v-if="errors['name.en']" class="error-message">{{ errors['name.en'] }}</div>
            </div>

            <!-- Arabic Name -->
            <div v-show="activeTab === 'ar'" class="tab-content">
              <input
                v-model="formData.name.ar"
                type="text"
                class="form-control-modern"
                placeholder="أدخل اسم الفئة الفرعية بالعربية"
                dir="rtl"
                :class="{ 'is-invalid': errors['name.ar'] }"
              >
              <div v-if="errors['name.ar']" class="error-message">{{ errors['name.ar'] }}</div>
            </div>
          </div>

          <!-- Featured & Popular Checkboxes -->
          <div class="form-group">
            <label class="form-label">Options</label>
            <div class="checkbox-group">
              <label class="checkbox-label">
                <input
                  v-model="formData.is_featured"
                  type="checkbox"
                  class="checkbox-input"
                >
                <span class="checkbox-text">
                  <i class="fas fa-star"></i>
                  Featured Sub-Category
                </span>
              </label>
              <label class="checkbox-label">
                <input
                  v-model="formData.is_popular"
                  type="checkbox"
                  class="checkbox-input"
                >
                <span class="checkbox-text">
                  <i class="fas fa-fire"></i>
                  Popular Sub-Category
                </span>
              </label>
            </div>
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
            {{ submitting ? 'Saving...' : (mode === 'create' ? 'Create Sub-Category' : 'Update Sub-Category') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { useSubCategories } from '../../../composables/useSubCategories'

const props = defineProps({
  subcategory: {
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

const { createSubCategory, updateSubCategory } = useSubCategories()

const activeTab = ref('en')
const imagePreview = ref(null)
const imageFile = ref(null)
const submitting = ref(false)
const errorMessage = ref('')
const errors = reactive({})
const parentCategories = ref([])

const formData = reactive({
  categories_id: '',
  name: {
    ar: '',
    en: ''
  },
  is_featured: false,
  is_popular: false
})

onMounted(async () => {
  await loadParentCategories()
})

// Initialize form data if editing
watch(() => props.subcategory, (newSubcategory) => {
  if (newSubcategory && props.mode === 'edit') {
    formData.categories_id = newSubcategory.categories_id || ''
    formData.name.ar = newSubcategory.name.ar || ''
    formData.name.en = newSubcategory.name.en || ''
    formData.is_featured = newSubcategory.is_featured || false
    formData.is_popular = newSubcategory.is_popular || false
  }
}, { immediate: true })

const loadParentCategories = async () => {
  try {
    const response = await fetch('/api/admin/categories?per_page=1000')
    const data = await response.json()
    parentCategories.value = data.data || []
  } catch (error) {
    console.error('Error loading parent categories:', error)
  }
}

const handleImageChange = (event) => {
  const file = event.target.files[0]
  if (!file) return

  // Validate file size (2MB)
  if (file.size > 2 * 1024 * 1024) {
    errors.photo = 'Image size must be less than 2MB'
    return
  }

  // Validate file type
  if (!file.type.startsWith('image/')) {
    errors.photo = 'Please select a valid image file'
    return
  }

  imageFile.value = file
  errors.photo = null

  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

const removeImage = () => {
  imageFile.value = null
  imagePreview.value = null
  if (props.refs && props.refs.imageInput) {
    props.refs.imageInput.value = ''
  }
}

const validateForm = () => {
  Object.keys(errors).forEach(key => delete errors[key])
  let isValid = true

  if (!formData.categories_id) {
    errors.categories_id = 'Parent category is required'
    isValid = false
  }

  if (!formData.name.en.trim()) {
    errors['name.en'] = 'English name is required'
    isValid = false
  }

  if (!formData.name.ar.trim()) {
    errors['name.ar'] = 'Arabic name is required'
    isValid = false
  }

  if (props.mode === 'create' && !imageFile.value) {
    errors.photo = 'Sub-category image is required'
    isValid = false
  }

  return isValid
}

const getImageUrl = (url) => {
  if (!url) return null
  if (url.startsWith('http')) return url
  if (url.startsWith('/storage/')) return url
  if (url.startsWith('storage/')) return `/${url}`
  return `/storage/${url}`
}

const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }

  submitting.value = true
  errorMessage.value = ''

  try {
    // Create FormData for multipart/form-data
    const data = new FormData()
    data.append('categories_id', formData.categories_id)
    data.append('name[ar]', formData.name.ar)
    data.append('name[en]', formData.name.en)
    data.append('is_featured', formData.is_featured ? '1' : '0')
    data.append('is_popular', formData.is_popular ? '1' : '0')

    if (imageFile.value) {
      data.append('photo', imageFile.value)
    }

    if (props.mode === 'create') {
      await createSubCategory(data)
    } else {
      await updateSubCategory(props.subcategory.id, data)
    }

    emit('saved')
  } catch (error) {
    errorMessage.value = error.message || 'Failed to save sub-category'
    console.error('Error saving sub-category:', error)
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

.form-control-modern.is-invalid {
  border-color: #dc3545;
}

.error-message {
  color: #dc3545;
  font-size: 0.85rem;
  margin-top: 0.35rem;
}

/* Image Upload */
.image-upload-container {
  margin-top: 0.5rem;
}

.image-preview {
  position: relative;
  width: 100%;
  max-width: 300px;
  margin: 0 auto;
}

.image-preview img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 8px;
  border: 2px solid #e9ecef;
}

.remove-image-btn {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: #dc3545;
  color: white;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s ease;
}

.remove-image-btn:hover {
  background: #c82333;
  transform: scale(1.1);
}

.image-upload-placeholder {
  border: 2px dashed #e9ecef;
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.image-upload-placeholder:hover {
  border-color: #667eea;
  background: #f8f9fa;
}

.image-upload-placeholder i {
  font-size: 3rem;
  color: #adb5bd;
  margin-bottom: 1rem;
}

.image-upload-placeholder p {
  margin: 0;
  font-weight: 600;
  color: #495057;
}

.image-upload-placeholder small {
  color: #6c757d;
}

/* Language Tabs */
.language-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.lang-tab {
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

.lang-tab:hover {
  background: #f8f9fa;
}

.lang-tab.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-color: #667eea;
}

.tab-content {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
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
}
</style>
