<template>
  <div class="modal-overlay-advanced" @click.self="$emit('close')">
    <div class="modal-container-advanced modal-lg">
      <div class="modal-header-advanced">
        <h2>
          <i :class="mode === 'create' ? 'fas fa-plus-circle' : 'fas fa-edit'"></i>
          {{ mode === 'create' ? 'Create Role' : 'Edit Role' }}
        </h2>
        <button class="modal-close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit">
        <div class="modal-body-advanced">
          <!-- Role Details Section -->
          <div class="form-section">
            <h3 class="section-title">
              <i class="fas fa-user-tag"></i> Role Details
            </h3>

            <div class="form-row">
              <div class="form-group-modern">
                <label class="form-label-modern">
                  Role Name <span class="required">*</span>
                </label>
                <input
                  v-model="formData.name"
                  type="text"
                  class="form-input-modern"
                  placeholder="e.g., editor, moderator"
                  :disabled="mode === 'edit'"
                  required
                >
                <small v-if="mode === 'edit'" class="form-help-text">Role name cannot be changed after creation</small>
              </div>

              <div class="form-group-modern">
                <label class="form-label-modern">Display Name</label>
                <input
                  v-model="formData.display_name"
                  type="text"
                  class="form-input-modern"
                  placeholder="e.g., Content Editor"
                >
              </div>
            </div>

            <div class="form-group-modern">
              <label class="form-label-modern">Description</label>
              <textarea
                v-model="formData.description"
                class="form-input-modern"
                rows="2"
                placeholder="Describe what this role can do..."
              ></textarea>
            </div>
          </div>

          <!-- Permissions Section -->
          <div class="form-section">
            <h3 class="section-title">
              <i class="fas fa-key"></i> Permissions
              <span class="badge-count">{{ selectedPermissionsCount }} selected</span>
            </h3>

            <!-- Quick Actions -->
            <div class="quick-actions">
              <button type="button" class="quick-btn" @click="selectAll">
                <i class="fas fa-check-double"></i> Select All
              </button>
              <button type="button" class="quick-btn" @click="deselectAll">
                <i class="fas fa-times"></i> Deselect All
              </button>
              <input
                v-model="permissionSearch"
                type="text"
                class="permission-search"
                placeholder="Search permissions..."
              >
            </div>

            <!-- Loading Permissions -->
            <div v-if="loadingPermissions" class="permissions-loading">
              <i class="fas fa-spinner fa-spin"></i> Loading permissions...
            </div>

            <!-- Permissions Grid by Category -->
            <div v-else class="permissions-grid">
              <div
                v-for="(perms, category) in filteredGroupedPermissions"
                :key="category"
                class="permission-category"
              >
                <div class="category-header" @click="toggleCategory(category)">
                  <div class="category-title">
                    <i :class="getCategoryIcon(category)"></i>
                    <span>{{ formatCategoryName(category) }}</span>
                    <span class="category-count">({{ perms.length }})</span>
                  </div>
                  <div class="category-actions">
                    <button
                      type="button"
                      class="category-toggle-btn"
                      @click.stop="toggleCategoryPermissions(category, perms)"
                    >
                      {{ isCategoryFullySelected(perms) ? 'Deselect All' : 'Select All' }}
                    </button>
                    <i :class="expandedCategories.includes(category) ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
                  </div>
                </div>
                <div v-show="expandedCategories.includes(category)" class="category-permissions">
                  <label
                    v-for="permission in perms"
                    :key="permission.id"
                    class="permission-checkbox"
                  >
                    <input
                      type="checkbox"
                      :value="permission.id"
                      v-model="formData.permissions"
                      class="form-checkbox-modern"
                    >
                    <span class="permission-name">{{ permission.display_name || permission.name }}</span>
                    <span class="permission-slug">({{ permission.name }})</span>
                  </label>
                </div>
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
            {{ isSubmitting ? 'Saving...' : (mode === 'create' ? 'Create Role' : 'Update Role') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoles } from '../../../composables/useRoles'

const props = defineProps({
  role: Object,
  mode: {
    type: String,
    default: 'create',
    validator: (value) => ['create', 'edit'].includes(value)
  }
})

const emit = defineEmits(['close', 'saved'])

const { createRole, updateRole, fetchAllPermissions } = useRoles()

const isSubmitting = ref(false)
const errorMessage = ref('')
const loadingPermissions = ref(true)
const allPermissions = ref([])
const groupedPermissions = ref({})
const expandedCategories = ref([])
const permissionSearch = ref('')

const formData = ref({
  name: '',
  display_name: '',
  description: '',
  permissions: []
})

const selectedPermissionsCount = computed(() => formData.value.permissions.length)

const filteredGroupedPermissions = computed(() => {
  if (!permissionSearch.value) {
    return groupedPermissions.value
  }

  const search = permissionSearch.value.toLowerCase()
  const filtered = {}

  for (const [category, perms] of Object.entries(groupedPermissions.value)) {
    const matchingPerms = perms.filter(p =>
      p.name.toLowerCase().includes(search) ||
      (p.display_name && p.display_name.toLowerCase().includes(search))
    )
    if (matchingPerms.length > 0) {
      filtered[category] = matchingPerms
    }
  }

  return filtered
})

onMounted(async () => {
  await loadPermissions()
})

// Initialize form data when editing
watch(() => props.role, (newRole) => {
  if (newRole && props.mode === 'edit') {
    formData.value = {
      name: newRole.name || '',
      display_name: newRole.display_name || '',
      description: newRole.description || '',
      permissions: newRole.permissions?.map(p => p.id) || []
    }
  }
}, { immediate: true })

const loadPermissions = async () => {
  loadingPermissions.value = true
  try {
    const data = await fetchAllPermissions()
    allPermissions.value = data.permissions || []
    groupedPermissions.value = data.grouped_permissions || {}

    // Expand first 3 categories by default
    const categories = Object.keys(groupedPermissions.value)
    expandedCategories.value = categories.slice(0, 3)
  } catch (error) {
    console.error('Error loading permissions:', error)
    errorMessage.value = 'Failed to load permissions'
  } finally {
    loadingPermissions.value = false
  }
}

const toggleCategory = (category) => {
  const index = expandedCategories.value.indexOf(category)
  if (index > -1) {
    expandedCategories.value.splice(index, 1)
  } else {
    expandedCategories.value.push(category)
  }
}

const isCategoryFullySelected = (perms) => {
  return perms.every(p => formData.value.permissions.includes(p.id))
}

const toggleCategoryPermissions = (category, perms) => {
  if (isCategoryFullySelected(perms)) {
    // Deselect all in category
    formData.value.permissions = formData.value.permissions.filter(
      id => !perms.some(p => p.id === id)
    )
  } else {
    // Select all in category
    perms.forEach(p => {
      if (!formData.value.permissions.includes(p.id)) {
        formData.value.permissions.push(p.id)
      }
    })
  }
}

const selectAll = () => {
  formData.value.permissions = allPermissions.value.map(p => p.id)
}

const deselectAll = () => {
  formData.value.permissions = []
}

const getCategoryIcon = (category) => {
  const icons = {
    view: 'fas fa-eye',
    create: 'fas fa-plus',
    edit: 'fas fa-edit',
    delete: 'fas fa-trash',
    users: 'fas fa-users',
    roles: 'fas fa-user-tag',
    permissions: 'fas fa-key',
    posts: 'fas fa-file-alt',
    categories: 'fas fa-folder',
    settings: 'fas fa-cog',
    reports: 'fas fa-flag'
  }
  return icons[category] || 'fas fa-shield-alt'
}

const formatCategoryName = (category) => {
  return category.charAt(0).toUpperCase() + category.slice(1).replace(/_/g, ' ')
}

const handleSubmit = async () => {
  isSubmitting.value = true
  errorMessage.value = ''

  try {
    const submitData = {
      name: formData.value.name,
      display_name: formData.value.display_name,
      description: formData.value.description,
      permissions: formData.value.permissions
    }

    if (props.mode === 'create') {
      await createRole(submitData)
    } else {
      await updateRole(props.role.id, submitData)
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
.modal-lg {
  max-width: 900px;
}

.form-section {
  margin-bottom: 1.5rem;
  padding: 1.25rem;
  background: #f8f9fa;
  border-radius: 12px;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
  margin: 0 0 1rem 0;
}

.section-title i {
  color: #667eea;
}

.badge-count {
  font-size: 0.8rem;
  font-weight: normal;
  background: #667eea;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  margin-left: auto;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.quick-actions {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
  align-items: center;
}

.quick-btn {
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: white;
  color: #555;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

.quick-btn:hover {
  background: #667eea;
  color: white;
  border-color: #667eea;
}

.permission-search {
  flex: 1;
  min-width: 200px;
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 0.9rem;
}

.permission-search:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.permissions-loading {
  text-align: center;
  padding: 2rem;
  color: #666;
}

.permissions-grid {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.permission-category {
  background: white;
  border-radius: 10px;
  border: 1px solid #eee;
  overflow: hidden;
}

.category-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.875rem 1rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  cursor: pointer;
  transition: background 0.2s;
}

.category-header:hover {
  background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
}

.category-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  color: #333;
}

.category-title i {
  color: #667eea;
  width: 20px;
  text-align: center;
}

.category-count {
  font-weight: normal;
  color: #888;
  font-size: 0.85rem;
}

.category-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.category-toggle-btn {
  padding: 0.35rem 0.75rem;
  border: 1px solid #667eea;
  border-radius: 6px;
  background: white;
  color: #667eea;
  font-size: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
}

.category-toggle-btn:hover {
  background: #667eea;
  color: white;
}

.category-permissions {
  padding: 1rem;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 0.5rem;
  border-top: 1px solid #eee;
}

.permission-checkbox {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.2s;
}

.permission-checkbox:hover {
  background: #f8f9fa;
}

.permission-checkbox input[type="checkbox"] {
  width: 16px;
  height: 16px;
  accent-color: #667eea;
}

.permission-name {
  font-size: 0.9rem;
  color: #333;
}

.permission-slug {
  font-size: 0.75rem;
  color: #999;
}

.form-help-text {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.85rem;
  color: #888;
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }

  .category-permissions {
    grid-template-columns: 1fr;
  }
}
</style>
