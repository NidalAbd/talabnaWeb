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

            <!-- Base Price Section -->
            <div class="base-price-section">
                <div class="base-price-card">
                    <div class="base-price-header">
                        <i class="fas fa-dollar-sign"></i>
                        <h3>Base Price (USD)</h3>
                    </div>
                    <div class="base-price-content">
                        <div class="input-group">
                            <span class="input-prefix">$</span>
                            <input
                                type="number"
                                v-model="basePriceUsd"
                                step="0.01"
                                min="0.01"
                                placeholder="2.00"
                                class="base-price-input"
                            >
                            <span class="input-suffix">USD per point</span>
                        </div>
                        <div class="base-price-actions">
                            <label class="checkbox-inline">
                                <input type="checkbox" v-model="overrideCustom">
                                <span>Override custom prices</span>
                            </label>
                            <button
                                @click="applyBasePriceToAll"
                                :disabled="applyingPrice || !basePriceUsd"
                                class="apply-btn"
                            >
                                <i v-if="applyingPrice" class="fas fa-spinner fa-spin"></i>
                                <i v-else class="fas fa-sync"></i>
                                Apply to All Countries
                            </button>
                            <button
                                v-if="selectedCountries.length > 0"
                                @click="applyBasePriceToSelected"
                                :disabled="applyingPrice || !basePriceUsd"
                                class="apply-btn secondary"
                            >
                                <i v-if="applyingPrice" class="fas fa-spinner fa-spin"></i>
                                <i v-else class="fas fa-check-double"></i>
                                Apply to Selected ({{ selectedCountries.length }})
                            </button>
                        </div>
                        <p class="helper-text">
                            <i class="fas fa-info-circle"></i>
                            Automatically converts USD to local currency using exchange rates
                        </p>
                    </div>
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
                            <th class="checkbox-col">
                                <input
                                    type="checkbox"
                                    @change="toggleSelectAll"
                                    :checked="isAllSelected"
                                >
                            </th>
                            <th>ID</th>
                            <th>Country</th>
                            <th>Currency</th>
                            <th>Exchange Rate</th>
                            <th>Price per Point</th>
                            <th>Allow Transfers</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="countries.length === 0">
                            <td colspan="8" class="empty-state">
                                <i class="fas fa-globe"></i>
                                <p>No countries found</p>
                            </td>
                        </tr>
                        <tr v-for="country in countries" :key="country.id" :class="{ 'selected-row': selectedCountries.includes(country.id) }">
                            <td class="checkbox-col">
                                <input
                                    type="checkbox"
                                    :value="country.id"
                                    v-model="selectedCountries"
                                >
                            </td>
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
                                <div class="currency-info">
                                    <span class="currency-code">{{ country.currency_code || 'N/A' }}</span>
                                    <span class="currency-symbol">{{ country.currency_symbol || '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="exchange-rate">
                                    <span class="rate-value">{{ formatExchangeRate(country.exchange_rate_to_usd) }}</span>
                                    <span class="rate-label">per USD</span>
                                </div>
                            </td>
                            <td>
                                <div class="price-display">
                                    <span class="price-badge" :class="{ 'custom': country.use_custom_price }">
                                        {{ country.currency_symbol || '' }}{{ country.price_per_point || '0.00' }}
                                    </span>
                                    <span v-if="country.use_custom_price" class="custom-badge">
                                        <i class="fas fa-star"></i> Custom
                                    </span>
                                </div>
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
                                <div class="action-buttons">
                                    <button @click="openEditModal(country)" class="action-btn edit" title="Edit Pricing">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button @click="openExchangeRateModal(country)" class="action-btn rate" title="Edit Exchange Rate">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                </div>
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
                            <label>Price per Point ({{ formData.currency_code || 'Local Currency' }})</label>
                            <input
                                type="number"
                                v-model="formData.price_per_point"
                                step="0.01"
                                min="0"
                                placeholder="7.50"
                            >
                            <small>Price for 1 point in local currency</small>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" v-model="formData.use_custom_price">
                                <span>Use Custom Price</span>
                            </label>
                            <small>Custom prices won't be overwritten when applying base USD price</small>
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

        <!-- Exchange Rate Modal -->
        <div class="modal-overlay" v-if="showExchangeRateModal" @click="closeExchangeRateModal">
            <div class="modern-modal small" @click.stop>
                <div class="modal-header">
                    <h3>
                        <i class="fas fa-exchange-alt"></i>
                        Edit Exchange Rate
                    </h3>
                    <button class="close-btn" @click="closeExchangeRateModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="country-info">
                        <i class="fas fa-flag"></i>
                        <div>
                            <div class="country-title">{{ selectedCountry?.name?.en }}</div>
                            <div class="country-subtitle">{{ selectedCountry?.currency_code }} ({{ selectedCountry?.currency_symbol }})</div>
                        </div>
                    </div>

                    <form @submit.prevent="handleExchangeRateSubmit">
                        <div class="form-group">
                            <label>Exchange Rate ({{ selectedCountry?.currency_code }} per 1 USD)</label>
                            <input
                                type="number"
                                v-model="exchangeRateData.exchange_rate_to_usd"
                                step="0.000001"
                                min="0.000001"
                                placeholder="3.67"
                            >
                            <small>How many {{ selectedCountry?.currency_code || 'local currency' }} equals 1 USD</small>
                        </div>

                        <div class="preview-box">
                            <div class="preview-label">Preview Conversion</div>
                            <div class="preview-value">
                                $1.00 USD = {{ exchangeRateData.exchange_rate_to_usd || 1 }} {{ selectedCountry?.currency_symbol || '' }}
                            </div>
                            <div class="preview-value" v-if="basePriceUsd">
                                ${{ basePriceUsd }} USD = {{ (basePriceUsd * (exchangeRateData.exchange_rate_to_usd || 1)).toFixed(2) }} {{ selectedCountry?.currency_symbol || '' }}
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="closeExchangeRateModal" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" @click="handleExchangeRateSubmit" :disabled="processing" class="btn-primary">
                        <i v-if="processing" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-save"></i>
                        {{ processing ? 'Saving...' : 'Save Rate' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const countries = ref([])
const loading = ref(true)
const error = ref(null)
const showEditModal = ref(false)
const showExchangeRateModal = ref(false)
const selectedCountry = ref(null)
const processing = ref(false)
const applyingPrice = ref(false)
const selectedCountries = ref([])
const basePriceUsd = ref(2.00)
const overrideCustom = ref(false)

const formData = ref({
    currency_code: '',
    currency_symbol: '',
    price_per_point: 7.5,
    use_custom_price: false,
    allow_point_transfers: true
})

const exchangeRateData = ref({
    exchange_rate_to_usd: 1
})

const isAllSelected = computed(() => {
    return countries.value.length > 0 && selectedCountries.value.length === countries.value.length
})

const formatExchangeRate = (rate) => {
    if (!rate) return '1.00'
    const num = parseFloat(rate)
    if (num >= 100) return num.toFixed(2)
    if (num >= 1) return num.toFixed(4)
    return num.toFixed(6)
}

const toggleSelectAll = (event) => {
    if (event.target.checked) {
        selectedCountries.value = countries.value.map(c => c.id)
    } else {
        selectedCountries.value = []
    }
}

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

const applyBasePriceToAll = async () => {
    if (!basePriceUsd.value) return

    applyingPrice.value = true

    try {
        const response = await fetch('/api/admin/country-pricing/apply-base-price', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                base_price_usd: parseFloat(basePriceUsd.value),
                override_custom: overrideCustom.value
            })
        })

        if (!response.ok) {
            const errorData = await response.json()
            throw new Error(errorData.message || 'Failed to apply base price')
        }

        const result = await response.json()

        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: result.message || `Updated ${result.updated_count} countries`
        })

        await loadCountries()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to apply base price'
        })
    } finally {
        applyingPrice.value = false
    }
}

const applyBasePriceToSelected = async () => {
    if (!basePriceUsd.value || selectedCountries.value.length === 0) return

    applyingPrice.value = true

    try {
        const response = await fetch('/api/admin/country-pricing/apply-base-price', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                base_price_usd: parseFloat(basePriceUsd.value),
                country_ids: selectedCountries.value,
                override_custom: overrideCustom.value
            })
        })

        if (!response.ok) {
            const errorData = await response.json()
            throw new Error(errorData.message || 'Failed to apply base price')
        }

        const result = await response.json()

        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: result.message || `Updated ${result.updated_count} countries`
        })

        selectedCountries.value = []
        await loadCountries()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to apply base price'
        })
    } finally {
        applyingPrice.value = false
    }
}

const openEditModal = (country) => {
    selectedCountry.value = country
    formData.value = {
        currency_code: country.currency_code || '',
        currency_symbol: country.currency_symbol || '',
        price_per_point: country.price_per_point || 7.5,
        use_custom_price: country.use_custom_price || false,
        allow_point_transfers: country.allow_point_transfers !== false
    }
    showEditModal.value = true
}

const closeEditModal = () => {
    showEditModal.value = false
    selectedCountry.value = null
}

const openExchangeRateModal = (country) => {
    selectedCountry.value = country
    exchangeRateData.value = {
        exchange_rate_to_usd: country.exchange_rate_to_usd || 1
    }
    showExchangeRateModal.value = true
}

const closeExchangeRateModal = () => {
    showExchangeRateModal.value = false
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

const handleExchangeRateSubmit = async () => {
    if (!selectedCountry.value) return

    processing.value = true

    try {
        const response = await fetch(`/api/admin/country-pricing/${selectedCountry.value.id}/exchange-rate`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin',
            body: JSON.stringify(exchangeRateData.value)
        })

        if (!response.ok) {
            const errorData = await response.json()
            throw new Error(errorData.message || 'Failed to update exchange rate')
        }

        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'Exchange rate updated successfully'
        })

        closeExchangeRateModal()
        await loadCountries()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to update exchange rate'
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
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
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

/* Base Price Section */
.base-price-section {
    padding: 25px 30px;
    background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
    border-bottom: 1px solid #e0e0e0;
}

.base-price-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.base-price-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.base-price-header i {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.base-price-header h3 {
    margin: 0;
    font-size: 1.1rem;
    color: #2c3e50;
}

.base-price-content {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.input-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.input-prefix {
    font-size: 1.3rem;
    font-weight: 600;
    color: #3498db;
}

.base-price-input {
    width: 120px;
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
}

.base-price-input:focus {
    outline: none;
    border-color: #3498db;
}

.input-suffix {
    color: #6c757d;
    font-size: 14px;
}

.base-price-actions {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.checkbox-inline {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 14px;
    color: #495057;
}

.checkbox-inline input {
    width: 16px;
    height: 16px;
}

.apply-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
}

.apply-btn.secondary {
    background: linear-gradient(135deg, #28a745 0%, #218838 100%);
}

.apply-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.apply-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.helper-text {
    font-size: 13px;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0;
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
    border-top-color: #3498db;
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
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
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
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
}

.modern-table thead th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.checkbox-col {
    width: 40px;
    text-align: center;
}

.modern-table tbody tr {
    border-bottom: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.modern-table tbody tr:hover {
    background: #f8f9fa;
}

.modern-table tbody tr.selected-row {
    background: #e8f4fd;
}

.modern-table tbody td {
    padding: 15px;
    vertical-align: middle;
    font-size: 0.9rem;
    color: #333;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.id-badge {
    background: #e8ecef;
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

.currency-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.currency-code {
    background: #e9ecef;
    padding: 4px 10px;
    border-radius: 6px;
    font-weight: 600;
    font-family: monospace;
    font-size: 13px;
    display: inline-block;
}

.currency-symbol {
    font-size: 1rem;
    font-weight: 600;
    color: #3498db;
}

.exchange-rate {
    display: flex;
    flex-direction: column;
}

.rate-value {
    font-weight: 600;
    color: #2c3e50;
    font-family: monospace;
}

.rate-label {
    font-size: 11px;
    color: #6c757d;
}

.price-display {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.price-badge {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    display: inline-block;
}

.price-badge.custom {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
}

.custom-badge {
    font-size: 11px;
    color: #f39c12;
    display: flex;
    align-items: center;
    gap: 4px;
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
    background: #d4edda;
    color: #28a745;
}

.transfer-toggle.blocked {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
}

.transfer-toggle:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.action-buttons {
    display: flex;
    gap: 8px;
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
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}

.action-btn.rate {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
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

.modern-modal.small {
    max-width: 400px;
}

.modal-header {
    padding: 20px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e8ecef;
    border-radius: 15px 15px 0 0;
}

.modal-header h3 {
    margin: 0;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 10px;
}

.close-btn {
    background: none;
    border: none;
    color: #999;
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
    background: #f8f9fa;
    color: #333;
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
    color: #3498db;
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
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
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

.preview-box {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    margin-top: 20px;
}

.preview-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
}

.preview-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
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
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
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

    .base-price-actions {
        flex-direction: column;
        align-items: flex-start;
    }

    .input-group {
        flex-wrap: wrap;
    }
}
</style>
