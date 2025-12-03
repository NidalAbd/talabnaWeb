<template>
  <div class="modal-overlay-advanced" @click.self="$emit('close')">
    <div class="modal-container-advanced cities-modal">
      <div class="modal-header-advanced">
        <h2>
          <i class="fas fa-city"></i>
          Manage Cities - {{ country?.name?.en || 'Country' }}
        </h2>
        <button class="modal-close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="modal-body-advanced">
        <!-- Add New City Form -->
        <div class="add-city-form">
          <h3><i class="fas fa-plus-circle"></i> Add New City</h3>
          <div class="form-row">
            <div class="form-group-modern">
              <label class="form-label-modern">City Name (English) <span class="required">*</span></label>
              <input
                v-model="newCity.name_en"
                type="text"
                class="form-input-modern"
                placeholder="Enter city name in English"
              >
            </div>
            <div class="form-group-modern">
              <label class="form-label-modern">City Name (Arabic) <span class="required">*</span></label>
              <input
                v-model="newCity.name_ar"
                type="text"
                class="form-input-modern"
                placeholder="أدخل اسم المدينة بالعربية"
                dir="rtl"
              >
            </div>
            <div class="form-group-modern form-group-btn">
              <button
                type="button"
                class="btn-add-city"
                @click="addCity"
                :disabled="!newCity.name_en || !newCity.name_ar || isAdding"
              >
                <i :class="isAdding ? 'fas fa-spinner fa-spin' : 'fas fa-plus'"></i>
                Add
              </button>
            </div>
          </div>
          <div v-if="addError" class="error-message-modern">
            <i class="fas fa-exclamation-circle"></i>
            {{ addError }}
          </div>
        </div>

        <!-- Search Cities -->
        <div class="search-box-cities">
          <i class="fas fa-search search-icon"></i>
          <input
            type="text"
            v-model="searchQuery"
            class="search-input"
            placeholder="Search cities..."
          >
        </div>

        <!-- Cities List -->
        <div class="cities-list-container">
          <div v-if="loading" class="loading-state">
            <div class="loader-small"></div>
            <p>Loading cities...</p>
          </div>

          <div v-else-if="filteredCities.length === 0" class="empty-state">
            <i class="fas fa-city"></i>
            <p>{{ searchQuery ? 'No cities match your search' : 'No cities found for this country' }}</p>
          </div>

          <div v-else class="cities-table-wrapper">
            <table class="cities-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name (English)</th>
                  <th>Name (Arabic)</th>
                  <th>Listings</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="city in filteredCities" :key="city.id">
                  <td>{{ city.id }}</td>
                  <td>
                    <template v-if="editingCity === city.id">
                      <input
                        v-model="editForm.name_en"
                        type="text"
                        class="edit-input"
                      >
                    </template>
                    <template v-else>
                      {{ getCityName(city, 'en') }}
                    </template>
                  </td>
                  <td>
                    <template v-if="editingCity === city.id">
                      <input
                        v-model="editForm.name_ar"
                        type="text"
                        class="edit-input"
                        dir="rtl"
                      >
                    </template>
                    <template v-else>
                      {{ getCityName(city, 'ar') }}
                    </template>
                  </td>
                  <td>
                    <span class="badge">{{ city.service_posts_count || 0 }}</span>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <template v-if="editingCity === city.id">
                        <button
                          class="btn-save"
                          @click="saveCity(city)"
                          :disabled="isSaving"
                        >
                          <i :class="isSaving ? 'fas fa-spinner fa-spin' : 'fas fa-check'"></i>
                        </button>
                        <button class="btn-cancel" @click="cancelEdit">
                          <i class="fas fa-times"></i>
                        </button>
                      </template>
                      <template v-else>
                        <button class="btn-edit" @click="startEdit(city)">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button
                          class="btn-delete"
                          @click="deleteCity(city)"
                          :disabled="city.service_posts_count > 0"
                          :title="city.service_posts_count > 0 ? 'Cannot delete city with listings' : 'Delete city'"
                        >
                          <i class="fas fa-trash"></i>
                        </button>
                      </template>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Summary -->
        <div class="cities-summary">
          <span><strong>{{ cities.length }}</strong> cities total</span>
          <span v-if="searchQuery">| <strong>{{ filteredCities.length }}</strong> matching search</span>
        </div>
      </div>

      <div class="modal-footer-advanced">
        <button type="button" class="btn-cancel-advanced" @click="$emit('close')">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  country: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'saved'])

const loading = ref(false)
const cities = ref([])
const searchQuery = ref('')
const editingCity = ref(null)
const editForm = ref({ name_en: '', name_ar: '' })
const isSaving = ref(false)
const isAdding = ref(false)
const addError = ref('')

const newCity = ref({
  name_en: '',
  name_ar: ''
})

const filteredCities = computed(() => {
  if (!searchQuery.value) return cities.value
  const query = searchQuery.value.toLowerCase()
  return cities.value.filter(city => {
    const nameEn = getCityName(city, 'en').toLowerCase()
    const nameAr = getCityName(city, 'ar').toLowerCase()
    return nameEn.includes(query) || nameAr.includes(query)
  })
})

const getCityName = (city, locale) => {
  if (!city.name) return ''
  if (typeof city.name === 'object') {
    return city.name[locale] || city.name.en || city.name.ar || ''
  }
  // Handle JSON string
  try {
    const parsed = JSON.parse(city.name)
    if (typeof parsed === 'string') {
      // Double encoded
      const parsed2 = JSON.parse(parsed)
      return parsed2[locale] || parsed2.en || parsed2.ar || ''
    }
    return parsed[locale] || parsed.en || parsed.ar || ''
  } catch {
    return city.name
  }
}

const fetchCities = async () => {
  if (!props.country?.id) return
  loading.value = true
  try {
    const response = await axios.get(`/api/admin/countries/${props.country.id}/cities`)
    cities.value = response.data.cities || response.data || []
  } catch (error) {
    console.error('Error fetching cities:', error)
    cities.value = []
  } finally {
    loading.value = false
  }
}

const addCity = async () => {
  if (!newCity.value.name_en || !newCity.value.name_ar) return

  isAdding.value = true
  addError.value = ''

  try {
    const response = await axios.post(`/api/admin/countries/${props.country.id}/cities`, {
      name: {
        en: newCity.value.name_en,
        ar: newCity.value.name_ar
      }
    })

    // Add to list
    cities.value.push(response.data.city || response.data)

    // Clear form
    newCity.value.name_en = ''
    newCity.value.name_ar = ''

    emit('saved')
  } catch (error) {
    addError.value = error.response?.data?.message || 'Failed to add city'
  } finally {
    isAdding.value = false
  }
}

const startEdit = (city) => {
  editingCity.value = city.id
  editForm.value = {
    name_en: getCityName(city, 'en'),
    name_ar: getCityName(city, 'ar')
  }
}

const cancelEdit = () => {
  editingCity.value = null
  editForm.value = { name_en: '', name_ar: '' }
}

const saveCity = async (city) => {
  isSaving.value = true
  try {
    await axios.put(`/api/admin/cities/${city.id}`, {
      name: {
        en: editForm.value.name_en,
        ar: editForm.value.name_ar
      }
    })

    // Update local data
    const index = cities.value.findIndex(c => c.id === city.id)
    if (index !== -1) {
      cities.value[index].name = {
        en: editForm.value.name_en,
        ar: editForm.value.name_ar
      }
    }

    cancelEdit()
    emit('saved')
  } catch (error) {
    alert('Failed to update city: ' + (error.response?.data?.message || error.message))
  } finally {
    isSaving.value = false
  }
}

const deleteCity = async (city) => {
  if (city.service_posts_count > 0) {
    alert('Cannot delete city with existing listings')
    return
  }

  if (!confirm(`Are you sure you want to delete "${getCityName(city, 'en')}"?`)) {
    return
  }

  try {
    await axios.delete(`/api/admin/cities/${city.id}`)
    cities.value = cities.value.filter(c => c.id !== city.id)
    emit('saved')
  } catch (error) {
    alert('Failed to delete city: ' + (error.response?.data?.message || error.message))
  }
}

onMounted(() => {
  fetchCities()
})

watch(() => props.country, () => {
  fetchCities()
})
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

.modal-container-advanced.cities-modal {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 900px;
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
  background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
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
  padding: 1.5rem 2rem;
  overflow-y: auto;
  flex: 1;
}

/* Add City Form */
.add-city-form {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 1.25rem;
  margin-bottom: 1.5rem;
}

.add-city-form h3 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.form-row {
  display: flex;
  gap: 1rem;
  align-items: flex-end;
}

.form-group-modern {
  flex: 1;
}

.form-group-btn {
  flex: 0 0 auto;
}

.form-label-modern {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #2c3e50;
  font-size: 0.85rem;
}

.required {
  color: #dc3545;
}

.form-input-modern {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e9ecef;
  border-radius: 10px;
  font-size: 0.95rem;
  transition: all 0.2s ease;
  box-sizing: border-box;
}

.form-input-modern:focus {
  outline: none;
  border-color: #17a2b8;
  box-shadow: 0 0 0 4px rgba(23, 162, 184, 0.1);
}

.btn-add-city {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
}

.btn-add-city:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-add-city:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Search Box */
.search-box-cities {
  position: relative;
  margin-bottom: 1rem;
}

.search-box-cities .search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
}

.search-box-cities .search-input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.75rem;
  border: 2px solid #e9ecef;
  border-radius: 10px;
  font-size: 0.95rem;
  transition: all 0.2s ease;
}

.search-box-cities .search-input:focus {
  outline: none;
  border-color: #17a2b8;
}

/* Cities List */
.cities-list-container {
  border: 1px solid #e9ecef;
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 1rem;
}

.cities-table-wrapper {
  max-height: 350px;
  overflow-y: auto;
}

.cities-table {
  width: 100%;
  border-collapse: collapse;
}

.cities-table th,
.cities-table td {
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 1px solid #e9ecef;
}

.cities-table th {
  background: #f8f9fa;
  font-weight: 600;
  color: #2c3e50;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  position: sticky;
  top: 0;
  z-index: 1;
}

.cities-table tbody tr:hover {
  background: #f8f9fa;
}

.cities-table tbody tr:last-child td {
  border-bottom: none;
}

.badge {
  background: #e9ecef;
  color: #495057;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.edit-input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 2px solid #17a2b8;
  border-radius: 6px;
  font-size: 0.9rem;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.action-buttons button {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.btn-edit {
  background: #e3f2fd;
  color: #1976d2;
}

.btn-edit:hover {
  background: #1976d2;
  color: white;
}

.btn-delete {
  background: #ffebee;
  color: #dc3545;
}

.btn-delete:hover:not(:disabled) {
  background: #dc3545;
  color: white;
}

.btn-delete:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.btn-save {
  background: #e8f5e9;
  color: #28a745;
}

.btn-save:hover:not(:disabled) {
  background: #28a745;
  color: white;
}

.btn-cancel {
  background: #fff3e0;
  color: #ff9800;
}

.btn-cancel:hover {
  background: #ff9800;
  color: white;
}

/* Loading & Empty States */
.loading-state,
.empty-state {
  text-align: center;
  padding: 3rem 2rem;
  color: #6c757d;
}

.loader-small {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #17a2b8;
  border-radius: 50%;
  margin: 0 auto 1rem;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: #dee2e6;
}

/* Summary */
.cities-summary {
  color: #6c757d;
  font-size: 0.9rem;
}

/* Error Message */
.error-message-modern {
  background: #fee;
  color: #dc3545;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1rem;
  font-size: 0.9rem;
}

/* Footer */
.modal-footer-advanced {
  padding: 1rem 2rem;
  background: #f8f9fa;
  display: flex;
  justify-content: flex-end;
  border-top: 1px solid #e9ecef;
  flex-shrink: 0;
}

.btn-cancel-advanced {
  padding: 0.75rem 2rem;
  border: 2px solid #e9ecef;
  background: white;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #6c757d;
}

.btn-cancel-advanced:hover {
  background: #f8f9fa;
  border-color: #dee2e6;
}

/* Responsive */
@media (max-width: 768px) {
  .form-row {
    flex-direction: column;
  }

  .form-group-btn {
    width: 100%;
  }

  .btn-add-city {
    width: 100%;
    justify-content: center;
  }

  .cities-table th,
  .cities-table td {
    padding: 0.5rem;
    font-size: 0.85rem;
  }
}
</style>
