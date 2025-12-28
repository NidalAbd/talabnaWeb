<template>
    <div class="modern-pricing-container">
        <!-- Header Card -->
        <div class="modern-card">
            <div class="card-header">
                <div class="header-left">
                    <i class="fas fa-globe-americas"></i>
                    <h2>Country Pricing Settings</h2>
                </div>
                <div class="header-info">
                    <span class="info-badge">
                        <i class="fas fa-info-circle"></i>
                        Set price per point and currency for each country
                    </span>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="loading-state">
                <div class="spinner"></div>
                <p>Loading countries...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="error-state">
                <i class="fas fa-exclamation-circle"></i>
                <p>{{ error }}</p>
                <button @click="loadCountries" class="retry-btn">
                    <i class="fas fa-redo"></i> Retry
                </button>
            </div>

            <!-- Table -->
            <div v-else class="table-container">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Country</th>
                            <th>Currency Code</th>
                            <th>Currency Symbol</th>
                            <th>Price per Point</th>
                            <th>Allow Transfers</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="countries.length === 0">
                            <td colspan="7" class="empty-state">
                                <i class="fas fa-globe"></i>
                                <p>No countries found</p>
                            </td>
                        </tr>
                        <tr v-for="country in countries" :key="country.id">
                            <td>
                                <span class="id-badge">{{ country.id }}</span>
                            </td>
                            <td>
                                <div class="country-name">
                                    <div class="name-ar">{{ country.name?.ar || '-' }}</div>
                                    <div class="name-en">{{ country.name?.en || '-' }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="currency-code">{{ country.currency_code || 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="currency-symbol">{{ country.currency_symbol || '₪' }}</span>
                            </td>
                            <td>
                                <span class="price-badge">{{ country.price_per_point || 7.5 }}</span>
                            </td>
                            <td>
                                <button
                                    @click="handleToggleTransfers(country)"
                                    :class="['transfer-toggle', country.allow_point_transfers !== false ? 'allowed' : 'blocked']"
                                >
                                    <i :class="country.allow_point_transfers !== false ? 'fas fa-check-circle' : 'fas fa-ban'"></i>
                                    {{ country.allow_point_transfers !== false ? 'Allowed' : 'Blocked' }}
                                </button>
                            </td>
                            <td>
                                <button @click="openEditModal(country)" class="action-btn edit" title="Edit Pricing">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal-overlay" v-if="showEditModal" @click="closeEditModal">
            <div class="modern-modal" @click.stop>
                <div class="modal-header">
                    <h3>
                        <i class="fas fa-edit"></i>
                        Edit Country Pricing
                    </h3>
                    <button class="close-btn" @click="closeEditModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="country-info">
                        <i class="fas fa-flag"></i>
                        <div>
                            <div class="country-title">{{ selectedCountry?.name?.en }}</div>
                            <div class="country-subtitle">{{ selectedCountry?.name?.ar }}</div>
                        </div>
                    </div>

                    <form @submit.prevent="handleSubmit">
                        <div class="form-group">
                            <label>Currency Code</label>
                            <input
                                type="text"
                                v-model="formData.currency_code"
                                placeholder="e.g., ILS, USD, EGP"
                                maxlength="10"
                            >
                            <small>ISO 4217 currency code</small>
                        </div>

                        <div class="form-group">
                            <label>Currency Symbol</label>
                            <input
                                type="text"
                                v-model="formData.currency_symbol"
                                placeholder="e.g., ₪, $, £"
                                maxlength="10"
                            >
                            <small>Symbol displayed in the app</small>
                        </div>

                        <div class="form-group">
                            <label>Price per Point</label>
                            <input
                                type="number"
                                v-model="formData.price_per_point"
                                step="0.01"
                                min="0"
                                placeholder="7.50"
                            >
                            <small>Base price for 1 point in this country's currency</small>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" v-model="formData.allow_point_transfers">
                                <span>Allow Point Transfers</span>
                            </label>
                            <small>Enable/disable point transfers for users in this country</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="closeEditModal" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" @click="handleSubmit" :disabled="processing" class="btn-primary">
                        <i v-if="processing" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-save"></i>
                        {{ processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const countries = ref([])
const loading = ref(true)
const error = ref(null)
const showEditModal = ref(false)
const selectedCountry = ref(null)
const processing = ref(false)

const formData = ref({
    currency_code: '',
    currency_symbol: '',
    price_per_point: 7.5,
    allow_point_transfers: true
})

const loadCountries = async () => {
    loading.value = true
    error.value = null

    try {
        const response = await fetch('/api/admin/country-pricing', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin'
        })

        if (!response.ok) {
            throw new Error('Failed to load countries')
        }

        const data = await response.json()
        countries.value = data.countries || []
    } catch (err) {
        error.value = err.message
        console.error('Error loading countries:', err)
    } finally {
        loading.value = false
    }
}

const openEditModal = (country) => {
    selectedCountry.value = country
    formData.value = {
        currency_code: country.currency_code || '',
        currency_symbol: country.currency_symbol || '₪',
        price_per_point: country.price_per_point || 7.5,
        allow_point_transfers: country.allow_point_transfers !== false
    }
    showEditModal.value = true
}

const closeEditModal = () => {
    showEditModal.value = false
    selectedCountry.value = null
}

const handleSubmit = async () => {
    if (!selectedCountry.value) return

    processing.value = true

    try {
        const response = await fetch(`/api/admin/country-pricing/${selectedCountry.value.id}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin',
            body: JSON.stringify(formData.value)
        })

        if (!response.ok) {
            const errorData = await response.json()
            throw new Error(errorData.message || 'Failed to update pricing')
        }

        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'Country pricing updated successfully'
        })

        closeEditModal()
        await loadCountries()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to update pricing'
        })
    } finally {
        processing.value = false
    }
}

const handleToggleTransfers = async (country) => {
    try {
        const response = await fetch(`/api/admin/country-pricing/${country.id}/toggle-transfers`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin'
        })

        if (!response.ok) {
            throw new Error('Failed to toggle transfers')
        }

        // Update locally
        country.allow_point_transfers = !country.allow_point_transfers

        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: `Transfers ${country.allow_point_transfers ? 'enabled' : 'disabled'} for ${country.name?.en}`
        })
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message
        })
    }
}

onMounted(() => {
    loadCountries()
})
</script>

<style scoped>
.modern-pricing-container {
    padding: 20px;
}

.modern-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 25px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    flex-wrap: wrap;
    gap: 15px;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.header-left i {
    font-size: 1.8rem;
}

.header-left h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.info-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.loading-state,
.error-state {
    padding: 60px;
    text-align: center;
}

.spinner {
    width: 50px;
    height: 50px;
    margin: 0 auto 20px;
    border: 4px solid #f3f4f6;
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.error-state {
    color: #dc3545;
}

.error-state i {
    font-size: 3rem;
    margin-bottom: 15px;
}

.retry-btn {
    margin-top: 15px;
    padding: 10px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

.table-container {
    padding: 30px;
    overflow-x: auto;
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.modern-table thead th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.modern-table tbody tr {
    border-bottom: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.modern-table tbody tr:hover {
    background: #f8f9fa;
}

.modern-table tbody td {
    padding: 15px;
    vertical-align: middle;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.id-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
}

.country-name {
    max-width: 200px;
}

.name-ar {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 3px;
}

.name-en {
    font-size: 13px;
    color: #6c757d;
}

.currency-code {
    background: #e9ecef;
    padding: 5px 12px;
    border-radius: 6px;
    font-weight: 600;
    font-family: monospace;
}

.currency-symbol {
    font-size: 1.2rem;
    font-weight: 600;
    color: #667eea;
}

.price-badge {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
}

.transfer-toggle {
    padding: 8px 16px;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.transfer-toggle.allowed {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
}

.transfer-toggle.blocked {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.transfer-toggle:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.action-btn {
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: white;
}

.action-btn.edit {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Modal */
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
    padding: 20px;
}

.modern-modal {
    background: white;
    border-radius: 15px;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 15px 15px 0 0;
}

.modal-header h3 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.close-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.modal-body {
    padding: 30px;
}

.country-info {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 25px;
}

.country-info i {
    font-size: 2rem;
    color: #667eea;
}

.country-title {
    font-weight: 600;
    font-size: 1.1rem;
    color: #2c3e50;
}

.country-subtitle {
    color: #6c757d;
    font-size: 14px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
}

.form-group input[type="text"],
.form-group input[type="number"] {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group small {
    display: block;
    color: #6c757d;
    font-size: 12px;
    margin-top: 5px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
}

.checkbox-label span {
    font-weight: 500;
}

.modal-footer {
    padding: 20px 30px;
    background: #f8f9fa;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    border-radius: 0 0 15px 15px;
}

.btn-cancel,
.btn-primary {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-cancel {
    background: #6c757d;
    color: white;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-cancel:hover,
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
