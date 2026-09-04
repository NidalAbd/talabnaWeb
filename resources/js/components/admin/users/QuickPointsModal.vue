<template>
  <div class="modal-overlay-advanced" @click.self="$emit('close')">
    <div class="quick-points-modal">
      <div class="modal-header-advanced">
        <h2>
          <i class="fas fa-coins"></i>
          Adjust Points
        </h2>
        <button class="modal-close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="modal-body-advanced">
        <div class="quick-points-user">
          <img :src="user.avatar_url" :alt="user.user_name" class="quick-points-avatar">
          <div>
            <strong>{{ user.user_name }}</strong>
            <span class="quick-points-id">#{{ user.id }}</span>
          </div>
        </div>

        <div class="form-group-modern">
          <label class="form-label-modern">Amount</label>
          <input
            v-model.number="amount"
            type="number"
            min="1"
            step="1"
            class="form-input-modern"
            placeholder="e.g. 100"
            autofocus
          >
        </div>

        <div class="form-group-modern">
          <label class="form-label-modern">Reason (optional)</label>
          <input
            v-model="reason"
            type="text"
            class="form-input-modern"
            placeholder="e.g. Support compensation"
          >
        </div>

        <label class="notify-checkbox">
          <input type="checkbox" v-model="notify">
          <span>Notify user (in-app + push)</span>
        </label>

        <div v-if="message" class="points-message" :class="messageType">
          <i :class="messageType === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
          {{ message }}
        </div>
      </div>

      <div class="modal-footer-advanced">
        <button type="button" class="btn-cancel-advanced" @click="$emit('close')">
          Cancel
        </button>
        <button
          type="button"
          class="btn-points btn-points-deduct"
          :disabled="isSubmitting || !amount"
          @click="submit('deduct')"
        >
          <i class="fas fa-minus"></i> Deduct
        </button>
        <button
          type="button"
          class="btn-points btn-points-add"
          :disabled="isSubmitting || !amount"
          @click="submit('add')"
        >
          <i class="fas fa-plus"></i> Add
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useUsers } from '../../../composables/useUsers'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'saved'])

const { adjustPoints } = useUsers()

const amount = ref(null)
const reason = ref('')
const notify = ref(true)
const isSubmitting = ref(false)
const message = ref('')
const messageType = ref('success')

const submit = async (direction) => {
  const rawAmount = Number(amount.value)

  if (!Number.isInteger(rawAmount) || rawAmount <= 0) {
    messageType.value = 'error'
    message.value = 'Enter a whole number greater than 0'
    return
  }

  isSubmitting.value = true
  message.value = ''

  try {
    const signedAmount = direction === 'deduct' ? -rawAmount : rawAmount
    const data = await adjustPoints(props.user.id, signedAmount, reason.value || undefined, notify.value)

    emit('saved', { userId: props.user.id, balance: data.balance })
  } catch (error) {
    messageType.value = 'error'
    message.value = error.message || 'Failed to adjust points'
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
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 1rem;
  backdrop-filter: blur(4px);
}

.quick-points-modal {
  background: white;
  border-radius: 20px;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
  max-width: 420px;
  width: 100%;
}

.modal-header-advanced {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #eef2f7;
}

.modal-header-advanced h2 {
  margin: 0;
  font-size: 1.15rem;
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.modal-close-btn {
  background: none;
  border: none;
  font-size: 1.1rem;
  color: #999;
  cursor: pointer;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  transition: all 0.2s ease;
}

.modal-close-btn:hover {
  background: #f8f9fa;
  color: #333;
}

.modal-body-advanced {
  padding: 1.5rem;
}

.quick-points-user {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
  padding: 0.75rem 1rem;
  background: #f8f9fa;
  border-radius: 12px;
}

.quick-points-avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  object-fit: cover;
}

.quick-points-id {
  display: block;
  font-size: 0.8rem;
  color: #888;
}

.form-group-modern {
  margin-bottom: 1rem;
}

.form-label-modern {
  display: block;
  margin-bottom: 0.4rem;
  font-size: 0.85rem;
  font-weight: 600;
  color: #555;
}

.form-input-modern {
  width: 100%;
  padding: 0.65rem 0.9rem;
  border: 2px solid #eef2f7;
  border-radius: 10px;
  font-size: 0.95rem;
  transition: border-color 0.2s;
}

.form-input-modern:focus {
  outline: none;
  border-color: #667eea;
}

.notify-checkbox {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: #555;
  cursor: pointer;
  margin-bottom: 0.5rem;
}

.notify-checkbox input {
  width: 16px;
  height: 16px;
  accent-color: #667eea;
}

.points-message {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1rem;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.85rem;
}

.points-message.success {
  background: #e6f9f0;
  color: #0f9d58;
}

.points-message.error {
  background: #fdecea;
  color: #c0392b;
}

.modal-footer-advanced {
  display: flex;
  justify-content: flex-end;
  gap: 0.6rem;
  padding: 1.25rem 1.5rem;
  border-top: 1px solid #eef2f7;
}

.btn-cancel-advanced {
  padding: 0.65rem 1.1rem;
  border: none;
  border-radius: 8px;
  background: #f1f3f5;
  color: #495057;
  font-weight: 600;
  cursor: pointer;
}

.btn-points {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.65rem 1.1rem;
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
</style>
