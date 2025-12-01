<template>
    <div class="premium-features-modern">
        <!-- Action Bar -->
        <div class="action-bar mb-4">
            <button class="action-btn primary" @click="openCreateModal">
                <i class="fas fa-plus"></i> Create Feature
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid mb-4">
            <div class="stat-card" v-for="stat in stats" :key="stat.label" :class="stat.color">
                <div class="stat-icon">
                    <i :class="stat.icon"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">{{ stat.value }}</h3>
                    <p class="stat-label">{{ stat.label }}</p>
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
                    placeholder="Search features..."
                >
            </div>
            <div class="filter-group">
                <select class="filter-select" v-model="filters.type" @change="loadFeatures">
                    <option value="">All Types</option>
                    <option v-for="type in types" :key="type.value" :value="type.value">
                        {{ type.label }}
                    </option>
                </select>
                <select class="filter-select" v-model="filters.status" @change="loadFeatures">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <select class="filter-select" v-model="filters.sort_by" @change="loadFeatures">
                    <option value="created_at">Sort by Date</option>
                    <option value="name">Sort by Name</option>
                    <option value="point_cost">Sort by Cost</option>
                    <option value="feature_type">Sort by Type</option>
                </select>
                <select class="filter-select" v-model="filters.sort_direction" @change="loadFeatures">
                    <option value="desc">Descending</option>
                    <option value="asc">Ascending</option>
                </select>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading premium features...</p>
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
                        <th>Name</th>
                        <th>Description</th>
                        <th>Cost</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="features.data.length === 0">
                        <td colspan="7" class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No features found</p>
                        </td>
                    </tr>
                    <tr v-for="feature in features.data" :key="feature.id">
                        <td><span class="id-badge">{{ feature.id }}</span></td>
                        <td>
                            <div class="feature-name">
                                <i v-if="feature.icon" :class="feature.icon" :style="{ color: feature.color }"></i>
                                <strong>{{ feature.name }}</strong>
                            </div>
                        </td>
                        <td>
                            <span class="description-text">{{ feature.description || '-' }}</span>
                        </td>
                        <td>
                            <span class="points-badge">{{ feature.point_cost }} pts</span>
                        </td>
                        <td>
                            <span class="badge secondary">{{ feature.type_label }}</span>
                        </td>
                        <td>
                            <button
                                @click="handleToggleStatus(feature.id)"
                                :class="feature.is_active ? 'status-btn active' : 'status-btn inactive'"
                                title="Toggle Status"
                            >
                                <i :class="feature.is_active ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
                                {{ feature.is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button
                                    @click="openEditModal(feature)"
                                    class="action-btn info small"
                                    title="Edit"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button
                                    @click="openDeleteModal(feature)"
                                    class="action-btn danger small"
                                    title="Delete"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container" v-if="features.last_page > 1">
            <div class="pagination-info">
                Showing {{ ((features.current_page - 1) * features.per_page) + 1 }}
                to {{ Math.min(features.current_page * features.per_page, features.total) }}
                of {{ features.total }} entries
            </div>
            <div class="pagination-controls">
                <button
                    class="pagination-btn"
                    :disabled="features.current_page === 1"
                    @click="changePage(features.current_page - 1)"
                >
                    <i class="fas fa-chevron-left"></i>
                    Previous
                </button>
                <button
                    v-for="page in displayPages"
                    :key="page"
                    class="pagination-btn"
                    :class="{ active: page === features.current_page, dots: page === '...' }"
                    @click="page !== '...' && changePage(page)"
                    :disabled="page === '...'"
                >
                    {{ page }}
                </button>
                <button
                    class="pagination-btn"
                    :disabled="features.current_page === features.last_page"
                    @click="changePage(features.current_page + 1)"
                >
                    Next
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div class="modal-overlay" v-if="showFormModal" @click="closeFormModal">
            <div class="modern-modal large" @click.stop>
                <div class="modal-header">
                    <h3>
                        <i class="fas fa-crown"></i>
                        {{ isEditMode ? 'Edit Feature' : 'Create Feature' }}
                    </h3>
                    <button class="close-btn" @click="closeFormModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="handleSubmit">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" class="form-input" v-model="formData.name" required>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-textarea" v-model="formData.description" rows="3"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Point Cost *</label>
                                <input type="number" class="form-input" v-model="formData.point_cost" required min="1">
                            </div>
                            <div class="form-group">
                                <label>Feature Type *</label>
                                <select class="form-select" v-model="formData.feature_type" required>
                                    <option value="">Select Type</option>
                                    <option v-for="type in types" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Icon (Font Awesome class)</label>
                                <input type="text" class="form-input" v-model="formData.icon" placeholder="e.g., fas fa-star">
                            </div>
                            <div class="form-group">
                                <label>Color (Hex)</label>
                                <input type="text" class="form-input" v-model="formData.color" placeholder="#000000">
                            </div>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" class="form-checkbox" id="is_active" v-model="formData.is_active">
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="action-btn secondary" @click="closeFormModal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button class="action-btn primary" @click="handleSubmit" :disabled="processing">
                        <i :class="processing ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
                        {{ processing ? 'Processing...' : (isEditMode ? 'Update' : 'Create') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal-overlay" v-if="showDeleteModal" @click="closeDeleteModal">
            <div class="modern-modal" @click.stop>
                <div class="modal-header danger">
                    <h3><i class="fas fa-exclamation-triangle"></i> Delete Feature</h3>
                    <button class="close-btn" @click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="modal-text">Are you sure you want to delete <strong>{{ selectedFeature?.name }}</strong>?</p>
                    <p class="danger-text">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button class="action-btn secondary" @click="closeDeleteModal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button class="action-btn danger" @click="handleDelete" :disabled="processing">
                        <i :class="processing ? 'fas fa-spinner fa-spin' : 'fas fa-trash'"></i>
                        {{ processing ? 'Deleting...' : 'Delete' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePremiumFeatures } from '../../composables/usePremiumFeatures'

const {
    features,
    stats,
    types,
    loading,
    error,
    fetchFeatures,
    fetchStats,
    fetchTypes,
    createFeature,
    updateFeature,
    deleteFeature,
    toggleStatus
} = usePremiumFeatures()

const filters = ref({
    search: '',
    type: '',
    status: '',
    sort_by: 'created_at',
    sort_direction: 'desc',
    page: 1,
    per_page: 15
})

const formData = ref({
    name: '',
    description: '',
    point_cost: '',
    feature_type: '',
    icon: '',
    color: '',
    is_active: true
})

const isEditMode = ref(false)
const selectedFeature = ref(null)
const processing = ref(false)
const showFormModal = ref(false)
const showDeleteModal = ref(false)

let searchTimeout = null

const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        filters.value.page = 1
        loadFeatures()
    }, 500)
}

const loadFeatures = async () => {
    await fetchFeatures(filters.value)
}

const changePage = (page) => {
    if (page < 1 || page > features.value.last_page) return
    filters.value.page = page
    loadFeatures()
}

const displayPages = computed(() => {
    const current = features.value.current_page
    const last = features.value.last_page
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
        name: '',
        description: '',
        point_cost: '',
        feature_type: '',
        icon: '',
        color: '',
        is_active: true
    }
}

const openCreateModal = () => {
    resetForm()
    isEditMode.value = false
    showFormModal.value = true
}

const openEditModal = (feature) => {
    formData.value = {
        name: feature.name,
        description: feature.description || '',
        point_cost: feature.point_cost,
        feature_type: feature.feature_type,
        icon: feature.icon || '',
        color: feature.color || '',
        is_active: feature.is_active
    }
    selectedFeature.value = feature
    isEditMode.value = true
    showFormModal.value = true
}

const closeFormModal = () => {
    showFormModal.value = false
    resetForm()
    selectedFeature.value = null
}

const openDeleteModal = (feature) => {
    selectedFeature.value = feature
    showDeleteModal.value = true
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
    selectedFeature.value = null
}

const handleSubmit = async () => {
    processing.value = true
    try {
        if (isEditMode.value) {
            await updateFeature(selectedFeature.value.id, formData.value)
            window.$(document).Toasts('create', {
                class: 'bg-success',
                title: 'Success',
                body: 'Feature updated successfully'
            })
        } else {
            await createFeature(formData.value)
            window.$(document).Toasts('create', {
                class: 'bg-success',
                title: 'Success',
                body: 'Feature created successfully'
            })
        }

        closeFormModal()
        await loadFeatures()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to save feature'
        })
    } finally {
        processing.value = false
    }
}

const handleDelete = async () => {
    if (!selectedFeature.value) return

    processing.value = true
    try {
        await deleteFeature(selectedFeature.value.id)
        closeDeleteModal()

        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'Feature deleted successfully'
        })

        await loadFeatures()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to delete feature'
        })
    } finally {
        processing.value = false
    }
}

const handleToggleStatus = async (featureId) => {
    try {
        await toggleStatus(featureId)
        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'Feature status updated'
        })
        await loadFeatures()
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
    console.log('📋 PremiumFeaturesList component mounted')
    await fetchTypes()
    await loadFeatures()
    await fetchStats()
})
</script>

<style scoped>
.premium-features-modern {
    padding: 0;
}

/* Header Section */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.header-content {
    flex: 1;
}

.section-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0 0 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-title i {
    color: #f39c12;
}

.section-subtitle {
    color: #666;
    font-size: 0.95rem;
    margin: 0;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.stat-card {
    border-radius: 12px;
    padding: 1.5rem;
    color: white;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
}

.stat-card.blue {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.stat-card.green {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
}

.stat-card.orange {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
}

.stat-card.purple {
    background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 0.25rem 0;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.95;
    margin: 0;
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
    border-color: #f39c12;
    box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.1);
}

.filter-group {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
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
    border-color: #f39c12;
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
    border-top: 4px solid #f39c12;
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
    padding: 3rem !important;
    color: #999;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
    opacity: 0.5;
}

.id-badge {
    background: #e8ecef;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.85rem;
}

.feature-name {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.feature-name i {
    font-size: 1.2rem;
}

.description-text {
    font-size: 0.85rem;
    color: #666;
}

.points-badge {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-block;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.75rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge.secondary {
    background: #e2e3e5;
    color: #6c757d;
}

.status-btn {
    padding: 0.4rem 0.75rem;
    border: none;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.status-btn.active {
    background: #d4edda;
    color: #28a745;
}

.status-btn.inactive {
    background: #f8d7da;
    color: #dc3545;
}

.status-btn:hover {
    transform: scale(1.05);
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

.action-btn.primary {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.action-btn.primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(243, 156, 18, 0.3);
}

.action-btn.info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

.action-btn.info:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
}

.action-btn.danger {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
}

.action-btn.danger:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
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

.pagination-btn:hover:not(:disabled):not(.dots) {
    border-color: #f39c12;
    color: #f39c12;
}

.pagination-btn.active {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    border-color: #f39c12;
    color: white;
}

.pagination-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.pagination-btn.dots {
    border: none;
    cursor: default;
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
    max-width: 500px;
    width: 100%;
    max-height: 90vh;
    overflow: auto;
}

.modern-modal.large {
    max-width: 700px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e8ecef;
}

.modal-header.danger {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
}

.modal-header.danger h3 {
    color: white;
}

.modal-header.danger .close-btn {
    color: white;
}

.modal-header.danger .close-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #999;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.close-btn:hover {
    background: #f8f9fa;
    color: #333;
}

.modal-body {
    padding: 1.5rem;
}

.modal-text {
    font-size: 1rem;
    color: #333;
    margin-bottom: 1rem;
}

.danger-text {
    color: #e74c3c;
    font-weight: 600;
    margin-bottom: 0;
}

/* Form Elements */
.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e8ecef;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #f39c12;
    box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.1);
}

.form-textarea {
    resize: vertical;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.form-check-label {
    cursor: pointer;
    font-size: 0.9rem;
    color: #333;
    user-select: none;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.5rem;
    border-top: 1px solid #e8ecef;
}

.mb-4 {
    margin-bottom: 1.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .search-filter-bar {
        flex-direction: column;
    }

    .search-box {
        min-width: 100%;
    }

    .filter-group {
        width: 100%;
    }

    .filter-select {
        flex: 1;
    }

    .data-table-container {
        overflow-x: auto;
    }

    .modern-table {
        min-width: 900px;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .pagination-container {
        flex-direction: column;
        align-items: flex-start;
    }

    .pagination-controls {
        width: 100%;
        justify-content: center;
    }
}
</style>
