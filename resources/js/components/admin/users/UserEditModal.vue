<template>
  <div class="modal-overlay-advanced" @click.self="$emit('close')">
    <div class="modal-container-advanced modal-lg">
      <div class="modal-header-advanced">
        <h2>
          <i class="fas fa-user-edit"></i>
          Edit User
        </h2>
        <button class="modal-close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="modal-body-advanced">
        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p>Loading user details...</p>
        </div>

        <template v-else-if="userData">
          <!-- User Profile Header -->
          <div class="user-profile-header">
            <div class="user-avatar">
              <img :src="userData.avatar" :alt="userData.name">
            </div>
            <div class="user-info">
              <h3>{{ userData.name }}</h3>
              <p class="username">@{{ userData.user_name }}</p>
              <div class="user-badges">
                <span
                  v-for="role in userData.roles"
                  :key="role.id"
                  class="role-badge"
                  :class="getRoleBadgeClass(role.name)"
                >
                  {{ role.display_name || role.name }}
                </span>
              </div>
            </div>
          </div>

          <form @submit.prevent="handleSubmit">
            <!-- Basic Info Section -->
            <div class="form-section">
              <h4 class="section-title">
                <i class="fas fa-info-circle"></i> Basic Information
              </h4>

              <div class="form-row">
                <div class="form-group-modern">
                  <label class="form-label-modern">Full Name</label>
                  <input
                    v-model="formData.name"
                    type="text"
                    class="form-input-modern"
                    placeholder="Enter full name"
                  >
                </div>

                <div class="form-group-modern">
                  <label class="form-label-modern">Email</label>
                  <input
                    v-model="formData.email"
                    type="email"
                    class="form-input-modern"
                    placeholder="Enter email address"
                  >
                </div>
              </div>

              <div class="form-row">
                <div class="form-group-modern">
                  <label class="form-label-modern">Phone</label>
                  <input
                    v-model="formData.phones"
                    type="text"
                    class="form-input-modern"
                    placeholder="Enter phone number"
                  >
                </div>

                <div class="form-group-modern">
                  <label class="form-label-modern">Status</label>
                  <select v-model="formData.is_active" class="form-input-modern">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="banned">Banned</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Roles Section -->
            <div class="form-section">
              <h4 class="section-title">
                <i class="fas fa-user-tag"></i> Roles
              </h4>

              <div v-if="loadingRoles" class="loading-mini">
                <i class="fas fa-spinner fa-spin"></i> Loading roles...
              </div>

              <div v-else class="roles-grid">
                <label
                  v-for="role in availableRoles"
                  :key="role.id"
                  class="role-checkbox"
                  :class="{ protected: role.name === 'superadmin' }"
                >
                  <input
                    type="checkbox"
                    :value="role.id"
                    v-model="formData.roles"
                    :disabled="role.name === 'superadmin'"
                    class="form-checkbox-modern"
                  >
                  <span class="role-icon" :class="getRoleBadgeClass(role.name)">
                    <i :class="getRoleIcon(role.name)"></i>
                  </span>
                  <span class="role-name">{{ role.display_name || role.name }}</span>
                </label>
              </div>
            </div>

            <!-- Additional Info -->
            <div class="form-section info-section">
              <h4 class="section-title">
                <i class="fas fa-chart-bar"></i> User Statistics
              </h4>

              <div class="stats-row">
                <div class="stat-item">
                  <i class="fas fa-file-alt"></i>
                  <span class="stat-value">{{ userData.service_posts_count || 0 }}</span>
                  <span class="stat-label">Posts</span>
                </div>
                <div class="stat-item">
                  <i class="fas fa-flag"></i>
                  <span class="stat-value">{{ userData.reports_count || 0 }}</span>
                  <span class="stat-label">Reports</span>
                </div>
                <div class="stat-item">
                  <i class="fas fa-coins"></i>
                  <span class="stat-value">{{ userData.points_balance || 0 }}</span>
                  <span class="stat-label">Points</span>
                </div>
                <div class="stat-item">
                  <i class="fas fa-calendar-alt"></i>
                  <span class="stat-value">{{ formatDate(userData.created_at) }}</span>
                  <span class="stat-label">Joined</span>
                </div>
              </div>
            </div>

            <!-- Points Section -->
            <div class="form-section">
              <h4 class="section-title">
                <i class="fas fa-coins"></i> Adjust Points
              </h4>

              <p class="points-current">
                Current balance: <strong>{{ userData.points_balance || 0 }}</strong> points
              </p>

              <div class="form-row">
                <div class="form-group-modern">
                  <label class="form-label-modern">Amount</label>
                  <input
                    v-model.number="pointsForm.amount"
                    type="number"
                    min="1"
                    step="1"
                    class="form-input-modern"
                    placeholder="e.g. 100"
                  >
                </div>

                <div class="form-group-modern">
                  <label class="form-label-modern">Reason (optional)</label>
                  <input
                    v-model="pointsForm.reason"
                    type="text"
                    class="form-input-modern"
                    placeholder="e.g. Support compensation"
                  >
                </div>
              </div>

              <div class="points-actions">
                <button
                  type="button"
                  class="btn-points btn-points-add"
                  :disabled="isAdjustingPoints || !pointsForm.amount"
                  @click="handleAdjustPoints('add')"
                >
                  <i class="fas fa-plus"></i> Add Points
                </button>
                <button
                  type="button"
                  class="btn-points btn-points-deduct"
                  :disabled="isAdjustingPoints || !pointsForm.amount"
                  @click="handleAdjustPoints('deduct')"
                >
                  <i class="fas fa-minus"></i> Deduct Points
                </button>
              </div>

              <div v-if="pointsMessage" class="points-message" :class="pointsMessageType">
                <i :class="pointsMessageType === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
                {{ pointsMessage }}
              </div>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="error-message-modern">
              <i class="fas fa-exclamation-circle"></i>
              {{ errorMessage }}
            </div>
          </form>
        </template>
      </div>

      <div class="modal-footer-advanced">
        <button type="button" class="btn-cancel-advanced" @click="$emit('close')">
          Cancel
        </button>
        <button type="button" class="btn-submit-advanced" @click="handleSubmit" :disabled="isSubmitting || loading">
          <i :class="isSubmitting ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
          {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useUsers } from '../../../composables/useUsers'

const props = defineProps({
  userId: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['close', 'saved'])

const { fetchUser, updateUser, getRoles, adjustPoints } = useUsers()

const loading = ref(true)
const loadingRoles = ref(true)
const isSubmitting = ref(false)
const errorMessage = ref('')
const userData = ref(null)
const availableRoles = ref([])

const pointsForm = ref({
  amount: null,
  reason: ''
})
const isAdjustingPoints = ref(false)
const pointsMessage = ref('')
const pointsMessageType = ref('success')

const formData = ref({
  name: '',
  email: '',
  phones: '',
  is_active: 'active',
  roles: []
})

onMounted(async () => {
  await Promise.all([loadUserData(), loadRoles()])
})

watch(() => props.userId, async () => {
  await loadUserData()
}, { immediate: false })

const loadUserData = async () => {
  loading.value = true
  try {
    const data = await fetchUser(props.userId)
    userData.value = data.user

    formData.value = {
      name: data.user.name || '',
      email: data.user.email || '',
      phones: data.user.phones || '',
      is_active: data.user.is_active || 'active',
      roles: data.user.roles?.map(r => r.id) || []
    }
  } catch (error) {
    console.error('Error loading user:', error)
    errorMessage.value = 'Failed to load user details'
  } finally {
    loading.value = false
  }
}

const loadRoles = async () => {
  loadingRoles.value = true
  try {
    const data = await getRoles()
    availableRoles.value = data.roles || []
  } catch (error) {
    console.error('Error loading roles:', error)
  } finally {
    loadingRoles.value = false
  }
}

const handleSubmit = async () => {
  isSubmitting.value = true
  errorMessage.value = ''

  try {
    await updateUser(props.userId, formData.value)
    emit('saved')
  } catch (error) {
    errorMessage.value = error.message || 'Failed to update user'
  } finally {
    isSubmitting.value = false
  }
}

const handleAdjustPoints = async (direction) => {
  const rawAmount = Number(pointsForm.value.amount)

  if (!Number.isInteger(rawAmount) || rawAmount <= 0) {
    pointsMessageType.value = 'error'
    pointsMessage.value = 'Enter a whole number greater than 0'
    return
  }

  isAdjustingPoints.value = true
  pointsMessage.value = ''

  try {
    const signedAmount = direction === 'deduct' ? -rawAmount : rawAmount
    const data = await adjustPoints(props.userId, signedAmount, pointsForm.value.reason || undefined)

    userData.value.points_balance = data.balance
    pointsMessageType.value = 'success'
    pointsMessage.value = data.message || 'Points updated successfully'
    pointsForm.value.amount = null
    pointsForm.value.reason = ''
  } catch (error) {
    pointsMessageType.value = 'error'
    pointsMessage.value = error.message || 'Failed to adjust points'
  } finally {
    isAdjustingPoints.value = false
  }
}

const getRoleBadgeClass = (roleName) => {
  const classes = {
    superadmin: 'role-superadmin',
    admin: 'role-admin',
    moderator: 'role-moderator',
    manager: 'role-manager',
    investor: 'role-investor',
    user: 'role-user'
  }
  return classes[roleName] || 'role-default'
}

const getRoleIcon = (roleName) => {
  const icons = {
    superadmin: 'fas fa-crown',
    admin: 'fas fa-user-shield',
    moderator: 'fas fa-user-cog',
    manager: 'fas fa-user-tie',
    investor: 'fas fa-hand-holding-usd',
    user: 'fas fa-user'
  }
  return icons[roleName] || 'fas fa-user-tag'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
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
  max-height: 90vh;
  overflow: hidden;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
  animation: modalSlideIn 0.3s ease;
  display: flex;
  flex-direction: column;
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
  flex-shrink: 0;
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
  overflow-y: auto;
}

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

.modal-footer-advanced {
  padding: 1.5rem 2rem;
  background: #f8f9fa;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  border-top: 1px solid #e9ecef;
  flex-shrink: 0;
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

.modal-lg {
  max-width: 800px;
}

.loading-state,
.loading-mini {
  text-align: center;
  padding: 2rem;
  color: #666;
}

.spinner {
  width: 50px;
  height: 50px;
  margin: 0 auto 1rem;
  border: 4px solid #f3f4f6;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.user-profile-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 12px;
  margin-bottom: 1.5rem;
}

.user-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  overflow: hidden;
  border: 4px solid white;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-info h3 {
  margin: 0;
  font-size: 1.25rem;
  color: #333;
}

.user-info .username {
  margin: 0.25rem 0 0.5rem;
  color: #888;
  font-size: 0.9rem;
}

.user-badges {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.role-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}

.role-superadmin { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); color: white; }
.role-admin { background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); color: white; }
.role-moderator { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
.role-manager { background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%); color: white; }
.role-investor { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
.role-user { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; }
.role-default { background: #e9ecef; color: #333; }

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
  font-size: 1rem;
  font-weight: 600;
  color: #333;
  margin: 0 0 1rem 0;
}

.section-title i {
  color: #667eea;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.roles-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 0.75rem;
}

.role-checkbox {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  background: white;
  border: 1px solid #eee;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s;
}

.role-checkbox:hover {
  border-color: #667eea;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
}

.role-checkbox.protected {
  opacity: 0.6;
  cursor: not-allowed;
}

.role-checkbox input[type="checkbox"] {
  width: 16px;
  height: 16px;
  accent-color: #667eea;
}

.role-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.9rem;
}

.role-name {
  font-size: 0.9rem;
  color: #333;
}

.info-section {
  background: linear-gradient(135deg, #e8f4f8 0%, #e3f2fd 100%);
}

.points-current {
  margin: 0 0 1rem;
  color: #555;
  font-size: 0.9rem;
}

.points-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1rem;
}

.btn-points {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.65rem 1rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: opacity 0.2s;
  color: white;
}

.btn-points:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-points-add {
  background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.btn-points-deduct {
  background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
}

.points-message {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1rem;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.9rem;
}

.points-message.success {
  background: #e6f9f0;
  color: #0f9d58;
}

.points-message.error {
  background: #fdecea;
  color: #c0392b;
}

.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

.stat-item {
  text-align: center;
  padding: 1rem;
  background: white;
  border-radius: 10px;
}

.stat-item i {
  font-size: 1.25rem;
  color: #667eea;
  margin-bottom: 0.5rem;
  display: block;
}

.stat-value {
  display: block;
  font-size: 1.25rem;
  font-weight: 700;
  color: #333;
}

.stat-label {
  display: block;
  font-size: 0.8rem;
  color: #888;
  margin-top: 0.25rem;
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }

  .roles-grid {
    grid-template-columns: 1fr;
  }

  .stats-row {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
