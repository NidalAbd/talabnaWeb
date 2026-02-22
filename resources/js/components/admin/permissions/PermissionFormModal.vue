<template>
  <div class="modal-overlay-advanced" @click.self="$emit('close')">
    <div class="modal-container-advanced">
      <div class="modal-header-advanced">
        <h2>
          <i class="fas fa-plus-circle"></i>
          Create Permission
        </h2>
        <button class="modal-close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit">
        <div class="modal-body-advanced">
          <!-- Permission Name -->
          <div class="form-group-modern">
            <label class="form-label-modern">
              Permission Name <span class="required">*</span>
            </label>
            <input
              v-model="formData.name"
              type="text"
              class="form-input-modern"
              placeholder="e.g., view_dashboard, manage_users"
              required
              @input="validateName"
            >
            <small class="form-help-text">
              Use lowercase letters and underscores only (e.g., view_posts, edit_comments)
            </small>
            <small v-if="nameError" class="form-error-text">{{ nameError }}</small>
          </div>

          <!-- Display Name -->
          <div class="form-group-modern">
            <label class="form-label-modern">Display Name</label>
            <input
              v-model="formData.display_name"
              type="text"
              class="form-input-modern"
              placeholder="e.g., View Dashboard"
            >
            <small class="form-help-text">Human-readable name (auto-generated if empty)</small>
          </div>

          <!-- Description -->
          <div class="form-group-modern">
            <label class="form-label-modern">Description</label>
            <textarea
              v-model="formData.description"
              class="form-input-modern"
              rows="2"
              placeholder="Describe what this permission allows..."
            ></textarea>
          </div>

          <!-- Quick Create Suggestions -->
          <div class="form-group-modern">
            <label class="form-label-modern">Quick Create</label>
            <div class="quick-suggestions">
              <button
                v-for="suggestion in suggestions"
                :key="suggestion"
                type="button"
                class="suggestion-btn"
                @click="applySuggestion(suggestion)"
              >
                {{ suggestion }}
              </button>
            </div>
            <small class="form-help-text">Click to auto-fill the permission name</small>
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
          <button type="submit" class="btn-submit-advanced" :disabled="isSubmitting || !!nameError">
            <i :class="isSubmitting ? 'fas fa-spinner fa-spin' : 'fas fa-plus-circle'"></i>
            {{ isSubmitting ? 'Creating...' : 'Create Permission' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePermissions } from '../../../composables/usePermissions'

const emit = defineEmits(['close', 'saved'])

const { createPermission } = usePermissions()

const isSubmitting = ref(false)
const errorMessage = ref('')
const nameError = ref('')

const formData = ref({
  name: '',
  display_name: '',
  description: ''
})

const suggestions = [
  'view_',
  'create_',
  'edit_',
  'delete_',
  'manage_',
  'approve_',
  'export_',
  'import_'
]

const validateName = () => {
  const name = formData.value.name
  if (name && !/^[a-z_]+$/.test(name)) {
    nameError.value = 'Only lowercase letters and underscores are allowed'
  } else {
    nameError.value = ''
  }
}

const applySuggestion = (suggestion) => {
  formData.value.name = suggestion
  // Focus on the input for user to continue typing
}

const handleSubmit = async () => {
  if (nameError.value) return

  isSubmitting.value = true
  errorMessage.value = ''

  try {
    const submitData = {
      name: formData.value.name.toLowerCase(),
      display_name: formData.value.display_name || null,
      description: formData.value.description || null
    }

    await createPermission(submitData)
    emit('saved')
  } catch (error) {
    errorMessage.value = error.message || 'An error occurred while creating permission'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.quick-suggestions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.suggestion-btn {
  padding: 0.4rem 0.75rem;
  border: 1px solid #667eea;
  border-radius: 20px;
  background: white;
  color: #667eea;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
}

.suggestion-btn:hover {
  background: #667eea;
  color: white;
}

.form-help-text {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.85rem;
  color: #888;
}

.form-error-text {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.85rem;
  color: #dc3545;
}
</style>
