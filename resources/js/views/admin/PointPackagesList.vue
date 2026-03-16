<template>
    <div class="modern-packages-container">
        <!-- Stats Cards -->
        <div class="stats-dashboard">
            <div class="stats-grid">
                <div class="stat-card-compact" :class="stat.color" v-for="stat in stats" :key="stat.label">
                    <div class="stat-icon"><i :class="stat.icon"></i></div>
                    <div class="stat-info">
                        <div class="stat-value-compact">{{ formatNumber(stat.value) }}</div>
                        <div class="stat-label-compact">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="search-filter-bar">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input
                    type="text"
                    v-model="filters.search"
                    @input="debouncedSearch"
                    class="search-input"
                    placeholder="Search packages..."
                >
            </div>
            <div class="filter-group">
                <select class="filter-select" v-model="filters.status" @change="loadPackages">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <select class="filter-select" v-model="filters.popular" @change="loadPackages">
                    <option value="">All Packages</option>
                    <option value="true">Popular Only</option>
                    <option value="false">Not Popular</option>
                </select>
                <select class="filter-select" v-model="filters.sort_by" @change="loadPackages">
                    <option value="created_at">Sort by Date</option>
                    <option value="points_amount">Sort by Points</option>
                    <option value="price">Sort by Price</option>
                    <option value="display_order">Sort by Order</option>
                </select>
                <select class="filter-select" v-model="filters.sort_direction" @change="loadPackages">
                    <option value="desc">Descending</option>
                    <option value="asc">Ascending</option>
                </select>
                <button class="action-btn info small" @click="openCreateModal" title="Create Package">
                    <i class="fas fa-plus"></i> Create
                </button>
                <button class="action-btn small" style="background: #28a745; color: white;" @click="handleBulkActivate" title="Activate All">
                    <i class="fas fa-check"></i>
                </button>
                <button class="action-btn danger small" @click="handleBulkDeactivate" title="Deactivate All">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading packages...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="error-state">
            <i class="fas fa-exclamation-circle"></i>
            <p>{{ error }}</p>
        </div>

        <!-- Table -->
        <div v-else class="data-table-container">
            <table class="modern-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name (AR/EN)</th>
                            <th>Points</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Discount</th>
                            <th>Status</th>
                            <th>Popular</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="packages.data.length === 0">
                            <td colspan="10" class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <p>No packages found</p>
                            </td>
                        </tr>
                        <tr v-for="pkg in packages.data" :key="pkg.id">
                            <td>
                                <span class="id-badge">{{ pkg.id }}</span>
                            </td>
                            <td>
                                <div class="package-name">
                                    <div class="name-ar">{{ pkg.name?.ar || '-' }}</div>
                                    <div class="name-en">{{ pkg.name?.en || '-' }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="points-badge">{{ pkg.points_amount }} pts</span>
                            </td>
                            <td>
                                <div class="price-info">{{ pkg.price }} {{ pkg.currency_code }}</div>
                            </td>
                            <td>
                                <span class="duration-badge">{{ pkg.validity_days }}d</span>
                            </td>
                            <td>
                                <span v-if="pkg.discount_percentage > 0" class="discount-badge">
                                    {{ pkg.discount_percentage }}%
                                </span>
                                <span v-else class="no-discount">-</span>
                            </td>
                            <td>
                                <button
                                    @click="handleToggleStatus(pkg.id)"
                                    :class="['status-toggle', pkg.is_active ? 'active' : 'inactive']"
                                >
                                    <i :class="pkg.is_active ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
                                </button>
                            </td>
                            <td>
                                <button
                                    @click="handleTogglePopular(pkg.id)"
                                    :class="['popular-toggle', pkg.is_popular ? 'popular' : 'not-popular']"
                                >
                                    <i class="fas fa-star"></i>
                                </button>
                            </td>
                            <td>
                                <span class="order-badge">{{ pkg.display_order }}</span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button @click="openEditModal(pkg)" class="action-btn edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button @click="handleDuplicate(pkg.id)" class="action-btn duplicate" title="Duplicate">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button @click="openDeleteModal(pkg)" class="action-btn delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-container" v-if="packages.last_page > 1">
                <div class="pagination-info">
                    Showing {{ ((packages.current_page - 1) * packages.per_page) + 1 }}
                    to {{ Math.min(packages.current_page * packages.per_page, packages.total) }}
                    of {{ packages.total }} entries
                </div>
                <div class="pagination-controls">
                    <button
                        @click="changePage(packages.current_page - 1)"
                        :disabled="packages.current_page === 1"
                        class="pagination-btn"
                    >
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button
                        v-for="page in displayPages"
                        :key="page"
                        @click="changePage(page)"
                        :class="['pagination-btn', { active: page === packages.current_page }]"
                    >
                        {{ page }}
                    </button>
                    <button
                        @click="changePage(packages.current_page + 1)"
                        :disabled="packages.current_page === packages.last_page"
                        class="pagination-btn"
                    >
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

        <!-- Create/Edit Modal -->
        <div class="modal-overlay" v-if="showFormModal" @click="closeFormModal">
            <div class="modern-modal large" @click.stop>
                <div class="modal-header">
                    <h3>
                        <i class="fas fa-box"></i>
                        {{ isEditMode ? 'Edit Package' : 'Create Package' }}
                    </h3>
                    <button class="close-btn" @click="closeFormModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="handleSubmit">
                        <div class="form-section">
                            <h4><i class="fas fa-language"></i> Names</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Name (Arabic) *</label>
                                    <input type="text" v-model="formData.name.ar" required placeholder="أدخل الاسم">
                                </div>
                                <div class="form-group">
                                    <label>Name (English) *</label>
                                    <input type="text" v-model="formData.name.en" required placeholder="Enter name">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4><i class="fas fa-dollar-sign"></i> Pricing</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Points Amount *</label>
                                    <input type="number" v-model="formData.points" required min="1" placeholder="1000">
                                </div>
                                <div class="form-group">
                                    <label>Price *</label>
                                    <input type="number" step="0.01" v-model="formData.price" required min="0" placeholder="9.99">
                                </div>
                                <div class="form-group">
                                    <label>Currency *</label>
                                    <input type="text" v-model="formData.currency" required maxlength="10" placeholder="USD">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4><i class="fas fa-cog"></i> Configuration</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Duration (Days) *</label>
                                    <input type="number" v-model="formData.duration_days" required min="1" placeholder="30">
                                </div>
                                <div class="form-group">
                                    <label>Discount %</label>
                                    <input type="number" v-model="formData.discount_percentage" min="0" max="100" placeholder="10">
                                </div>
                                <div class="form-group">
                                    <label>Display Order</label>
                                    <input type="number" v-model="formData.display_order" min="0" placeholder="0">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4><i class="fas fa-align-left"></i> Descriptions</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Description (Arabic)</label>
                                    <textarea v-model="formData.description.ar" rows="3" placeholder="أدخل الوصف"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Description (English)</label>
                                    <textarea v-model="formData.description.en" rows="3" placeholder="Enter description"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4><i class="fas fa-list"></i> Features</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Features (Arabic)</label>
                                    <textarea v-model="formData.features.ar" rows="3" placeholder="أدخل المميزات"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Features (English)</label>
                                    <textarea v-model="formData.features.en" rows="3" placeholder="Enter features"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4><i class="fas fa-palette"></i> Appearance</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Icon (Font Awesome class)</label>
                                    <input type="text" v-model="formData.icon" placeholder="fas fa-star">
                                    <small>Example: fas fa-star, fas fa-gift, fas fa-crown</small>
                                </div>
                                <div class="form-group">
                                    <label>Color (Hex)</label>
                                    <input type="text" v-model="formData.color" placeholder="#FFD700">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4><i class="fas fa-toggle-on"></i> Settings</h4>
                            <div class="checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" v-model="formData.is_active">
                                    <span>Active</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" v-model="formData.is_popular">
                                    <span><i class="fas fa-star"></i> Popular</span>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="closeFormModal" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" @click="handleSubmit" :disabled="processing" class="btn-primary">
                        <i v-if="processing" class="fas fa-spinner fa-spin"></i>
                        <i v-else :class="isEditMode ? 'fas fa-save' : 'fas fa-plus'"></i>
                        {{ processing ? 'Processing...' : (isEditMode ? 'Update Package' : 'Create Package') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal-overlay" v-if="showDeleteModal" @click="closeDeleteModal">
            <div class="modern-modal small" @click.stop>
                <div class="modal-header danger">
                    <h3><i class="fas fa-exclamation-triangle"></i> Delete Package</h3>
                    <button class="close-btn" @click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>{{ selectedPackage?.name?.en }}</strong>?</p>
                    <p class="warning-text"><i class="fas fa-exclamation-circle"></i> This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button @click="closeDeleteModal" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button @click="handleDelete" :disabled="processing" class="btn-danger">
                        <i v-if="processing" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-trash"></i>
                        {{ processing ? 'Deleting...' : 'Delete Package' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePointPackages } from '../../composables/usePointPackages'

const {
    packages,
    stats,
    loading,
    error,
    fetchPackages,
    fetchStats,
    createPackage,
    updatePackage,
    deletePackage,
    toggleStatus,
    togglePopular,
    duplicatePackage,
    bulkActivate,
    bulkDeactivate
} = usePointPackages()

const formatNumber = (value) => {
    if (value === null || value === undefined) return '0'
    return new Intl.NumberFormat().format(value)
}

const filters = ref({
    search: '',
    status: '',
    popular: '',
    sort_by: 'created_at',
    sort_direction: 'desc',
    page: 1,
    per_page: 15
})

const formData = ref({
    name: { ar: '', en: '' },
    description: { ar: '', en: '' },
    features: { ar: '', en: '' },
    points: '',
    price: '',
    currency: 'USD',
    duration_days: 30,
    discount_percentage: 0,
    display_order: 0,
    icon: '',
    color: '',
    is_active: true,
    is_popular: false
})

const showFormModal = ref(false)
const showDeleteModal = ref(false)
const isEditMode = ref(false)
const selectedPackage = ref(null)
const processing = ref(false)

let searchTimeout = null

const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        filters.value.page = 1
        loadPackages()
    }, 500)
}

const loadPackages = async () => {
    await fetchPackages(filters.value)
}

const changePage = (page) => {
    if (page < 1 || page > packages.value.last_page) return
    filters.value.page = page
    loadPackages()
}

const displayPages = computed(() => {
    const current = packages.value.current_page
    const last = packages.value.last_page
    const delta = 2
    const range = []
    const rangeWithDots = []

    for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
        range.push(i)
    }

    if (current - delta > 2) {
        rangeWithDots.push(1, '...')
    } else {
        rangeWithDots.push(1)
    }

    rangeWithDots.push(...range)

    if (current + delta < last - 1) {
        rangeWithDots.push('...', last)
    } else if (last > 1) {
        rangeWithDots.push(last)
    }

    return rangeWithDots.filter(p => p !== '...' || rangeWithDots.indexOf(p) === rangeWithDots.lastIndexOf(p))
})

const resetForm = () => {
    formData.value = {
        name: { ar: '', en: '' },
        description: { ar: '', en: '' },
        features: { ar: '', en: '' },
        points: '',
        price: '',
        currency: 'USD',
        duration_days: 30,
        discount_percentage: 0,
        display_order: 0,
        icon: '',
        color: '',
        is_active: true,
        is_popular: false
    }
}

const openCreateModal = () => {
    resetForm()
    isEditMode.value = false
    showFormModal.value = true
}

// Helper to convert array to string (for features that may come as arrays from DB)
const arrayToString = (value) => {
    if (Array.isArray(value)) {
        return value.join('\n')
    }
    return value || ''
}

const openEditModal = (pkg) => {
    // Handle features - convert arrays to strings for textarea
    const features = pkg.features || { ar: '', en: '' }
    const processedFeatures = {
        ar: arrayToString(features.ar),
        en: arrayToString(features.en)
    }

    formData.value = {
        name: pkg.name || { ar: '', en: '' },
        description: pkg.description || { ar: '', en: '' },
        features: processedFeatures,
        points: pkg.points_amount,
        price: pkg.price,
        currency: pkg.currency_code,
        duration_days: pkg.validity_days,
        discount_percentage: pkg.discount_percentage || 0,
        display_order: pkg.display_order || 0,
        icon: pkg.icon || '',
        color: pkg.color || '',
        is_active: pkg.is_active,
        is_popular: pkg.is_popular
    }
    selectedPackage.value = pkg
    isEditMode.value = true
    showFormModal.value = true
}

const openDeleteModal = (pkg) => {
    selectedPackage.value = pkg
    showDeleteModal.value = true
}

const closeFormModal = () => {
    showFormModal.value = false
    resetForm()
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
    selectedPackage.value = null
}

const handleSubmit = async () => {
    processing.value = true
    try {
        if (isEditMode.value) {
            await updatePackage(selectedPackage.value.id, formData.value)
            window.$(document).Toasts('create', {
                class: 'bg-success',
                title: 'Success',
                body: 'Package updated successfully'
            })
        } else {
            await createPackage(formData.value)
            window.$(document).Toasts('create', {
                class: 'bg-success',
                title: 'Success',
                body: 'Package created successfully'
            })
        }

        closeFormModal()
        await loadPackages()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to save package'
        })
    } finally {
        processing.value = false
    }
}

const handleDelete = async () => {
    if (!selectedPackage.value) return

    processing.value = true
    try {
        await deletePackage(selectedPackage.value.id)
        closeDeleteModal()

        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'Package deleted successfully'
        })

        await loadPackages()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to delete package'
        })
    } finally {
        processing.value = false
    }
}

const handleToggleStatus = async (packageId) => {
    try {
        await toggleStatus(packageId)
        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'Package status updated'
        })
        await loadPackages()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message
        })
    }
}

const handleTogglePopular = async (packageId) => {
    try {
        await togglePopular(packageId)
        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'Popular status updated'
        })
        await loadPackages()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message
        })
    }
}

const handleDuplicate = async (packageId) => {
    try {
        await duplicatePackage(packageId)
        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'Package duplicated successfully'
        })
        await loadPackages()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message
        })
    }
}

const handleBulkActivate = async () => {
    try {
        await bulkActivate()
        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'All packages activated'
        })
        await loadPackages()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message
        })
    }
}

const handleBulkDeactivate = async () => {
    try {
        await bulkDeactivate()
        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'All packages deactivated'
        })
        await loadPackages()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message
        })
    }
}

onMounted(async () => {
    console.log('📋 PointPackagesList component mounted')
    await loadPackages()
    await fetchStats()
})
</script>

<style scoped>
.modern-packages-container {
    padding: 0;
}

/* Search & Filters */
.search-filter-bar {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    margin-bottom: 1.5rem;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.search-box {
    position: relative;
    flex: 1;
    min-width: 280px;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 1rem;
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.75rem;
    border: 2px solid #e8ecef;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.filter-group {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: center;
}

.filter-select {
    padding: 0.75rem 1rem;
    border: 2px solid #e8ecef;
    border-radius: 8px;
    font-size: 0.9rem;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #3498db;
}

/* Loading & Error States */
.loading-state,
.error-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.spinner {
    width: 50px;
    height: 50px;
    margin: 0 auto 1rem;
    border: 4px solid #f3f4f6;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.error-state i {
    font-size: 3rem;
    color: #e74c3c;
    margin-bottom: 1rem;
}

/* Data Table */
.data-table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-table thead {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
}

.modern-table th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: white;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.modern-table tbody tr {
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s ease;
}

.modern-table tbody tr:hover {
    background-color: #f8f9fa;
}

.modern-table td {
    padding: 1rem;
    font-size: 0.9rem;
    color: #333;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

.id-badge {
    background: #e8ecef;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    display: inline-block;
}

.package-name {
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

.points-badge {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    display: inline-block;
}

.price-info {
    font-weight: 600;
    color: #2c3e50;
}

.duration-badge {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    display: inline-block;
}

.discount-badge {
    background: #fff3cd;
    color: #f39c12;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    display: inline-block;
}

.no-discount {
    color: #6c757d;
}

.status-toggle,
.popular-toggle {
    padding: 6px 10px;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.status-toggle.active {
    background: #d4edda;
    color: #28a745;
}

.status-toggle.inactive {
    background: #6c757d;
    color: white;
}

.popular-toggle.popular {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.popular-toggle.not-popular {
    background: #e9ecef;
    color: #6c757d;
}

.status-toggle:hover,
.popular-toggle:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.order-badge {
    background: #e9ecef;
    color: #495057;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    display: inline-block;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.action-btn.small {
    padding: 0.4rem 0.75rem;
    font-size: 0.85rem;
}

.action-btn.info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

.action-btn.info:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
}

.action-btn.edit {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

.action-btn.duplicate {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
}

.action-btn.delete,
.action-btn.danger {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.action-btn.secondary {
    background: #6c757d;
    color: white;
}

.action-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.5rem;
    padding: 1rem 1.5rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    flex-wrap: wrap;
    gap: 1rem;
}

.pagination-info {
    color: #666;
    font-size: 0.9rem;
}

.pagination-controls {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.pagination-btn {
    padding: 0.5rem 0.75rem;
    border: 2px solid #e8ecef;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.pagination-btn:hover:not(:disabled) {
    border-color: #3498db;
    color: #3498db;
}

.pagination-btn.active {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    border-color: #3498db;
    color: white;
}

.pagination-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
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
    z-index: 1050;
    padding: 1rem;
}

.modern-modal {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    width: 100%;
    max-height: 90vh;
    overflow: auto;
}

.modern-modal.small {
    max-width: 500px;
}

.modern-modal.large {
    max-width: 900px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #e8ecef;
    border-radius: 15px 15px 0 0;
}

.modal-header.danger {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
    border-bottom: none;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.3rem;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-header.danger h3 {
    color: white;
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

.modal-header.danger .close-btn {
    color: white;
}

.modal-header.danger .close-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.modal-body {
    padding: 30px;
}

.modal-body p {
    margin: 10px 0;
    color: #495057;
}

.warning-text {
    color: #dc3545;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 15px;
    padding: 10px;
    background: #fff5f5;
    border-radius: 8px;
}

.form-section {
    margin-bottom: 30px;
}

.form-section h4 {
    font-size: 1.1rem;
    color: #3498db;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f8f9fa;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 600;
    color: #495057;
    font-size: 14px;
}

.form-group input,
.form-group textarea,
.form-group select {
    padding: 10px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-group small {
    color: #6c757d;
    font-size: 12px;
}

.checkbox-group {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 10px 15px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.checkbox-label:hover {
    background: #e9ecef;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.checkbox-label span {
    font-weight: 500;
    color: #495057;
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
.btn-primary,
.btn-danger {
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

.btn-danger {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
}

.btn-cancel:hover,
.btn-primary:hover,
.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.btn-primary:disabled,
.btn-danger:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Responsive Design */
@media (max-width: 768px) {
    .filters-section {
        flex-direction: column;
    }

    .search-box {
        width: 100%;
    }

    .filter-select {
        width: 100%;
    }

    .card-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }

    .modern-table {
        font-size: 13px;
    }

    .modern-table thead th,
    .modern-table tbody td {
        padding: 10px;
    }

    .pagination-container {
        flex-direction: column;
        text-align: center;
    }
}
</style>
