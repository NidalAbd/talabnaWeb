<template>
    <div class="modern-levels-container">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card" v-for="stat in stats" :key="stat.label">
                <div class="stat-icon">
                    <i :class="stat.icon"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ stat.value }}</div>
                    <div class="stat-label">{{ stat.label }}</div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="modern-card">
            <!-- Header -->
            <div class="card-header">
                <div class="header-left">
                    <i class="fas fa-layer-group"></i>
                    <h2>User Levels Management</h2>
                </div>
                <button class="create-btn" @click="openCreateModal">
                    <i class="fas fa-plus"></i>
                    Create Level
                </button>
            </div>

            <!-- Filters -->
            <div class="filters-section">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input
                        type="text"
                        v-model="filters.search"
                        @input="debouncedSearch"
                        placeholder="Search levels by name..."
                    >
                </div>
                <select class="filter-select" v-model="filters.premium" @change="loadLevels">
                    <option value="">All Types</option>
                    <option value="premium">Premium</option>
                    <option value="regular">Regular</option>
                </select>
                <select class="filter-select" v-model="filters.status" @change="loadLevels">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <select class="filter-select" v-model="filters.sort_by" @change="loadLevels">
                    <option value="display_order">Sort by Order</option>
                    <option value="points_per_day">Sort by Points/Day</option>
                    <option value="view_boost_percentage">Sort by Boost</option>
                    <option value="created_at">Sort by Date</option>
                </select>
                <select class="filter-select" v-model="filters.sort_direction" @change="loadLevels">
                    <option value="asc">Ascending</option>
                    <option value="desc">Descending</option>
                </select>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="loading-state">
                <div class="spinner"></div>
                <p>Loading levels...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="error-state">
                <i class="fas fa-exclamation-circle"></i>
                <p>{{ error }}</p>
            </div>

            <!-- Table -->
            <div v-else class="table-container">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Level Details</th>
                            <th>Points/Day</th>
                            <th>View Boost</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="levels.data.length === 0">
                            <td colspan="7" class="empty-state">
                                <i class="fas fa-layer-group"></i>
                                <p>No levels found</p>
                            </td>
                        </tr>
                        <tr v-for="level in levels.data" :key="level.id">
                            <td>
                                <span class="order-badge">{{ level.display_order }}</span>
                            </td>
                            <td>
                                <div class="level-info">
                                    <i v-if="level.icon" :class="level.icon" :style="{ color: level.color }" class="level-icon"></i>
                                    <div>
                                        <div class="level-name">{{ level.name?.en || level.name }}</div>
                                        <div class="level-name-ar">{{ level.name?.ar }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="points-badge">{{ level.points_per_day }} pts</span>
                            </td>
                            <td>
                                <span class="boost-badge">+{{ level.view_boost_percentage }}%</span>
                            </td>
                            <td>
                                <span v-if="level.is_premium" class="type-badge premium">
                                    <i class="fas fa-crown"></i> Premium
                                </span>
                                <span v-else class="type-badge regular">Regular</span>
                            </td>
                            <td>
                                <button
                                    @click="handleToggleStatus(level.id)"
                                    :class="['status-toggle', level.is_active ? 'active' : 'inactive']"
                                >
                                    <i :class="level.is_active ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
                                    {{ level.is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button @click="openEditModal(level)" class="action-btn edit" title="Edit Level">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button @click="openDeleteModal(level)" class="action-btn delete" title="Delete Level">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-container" v-if="levels.last_page > 1">
                <div class="pagination-info">
                    Showing {{ ((levels.current_page - 1) * levels.per_page) + 1 }}
                    to {{ Math.min(levels.current_page * levels.per_page, levels.total) }}
                    of {{ levels.total }} entries
                </div>
                <div class="pagination-controls">
                    <button
                        @click="changePage(levels.current_page - 1)"
                        :disabled="levels.current_page === 1"
                        class="page-btn"
                    >
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button
                        v-for="page in displayPages"
                        :key="page"
                        @click="changePage(page)"
                        :class="['page-btn', { active: page === levels.current_page }]"
                    >
                        {{ page }}
                    </button>
                    <button
                        @click="changePage(levels.current_page + 1)"
                        :disabled="levels.current_page === levels.last_page"
                        class="page-btn"
                    >
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div class="modal-overlay" v-if="showFormModal" @click="closeFormModal">
            <div class="modern-modal large" @click.stop>
                <div class="modal-header">
                    <h3>
                        <i class="fas fa-layer-group"></i>
                        {{ isEditMode ? 'Edit Level' : 'Create Level' }}
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
                                    <label>Name (English) *</label>
                                    <input type="text" v-model="formData.name.en" required placeholder="Enter English name">
                                </div>
                                <div class="form-group">
                                    <label>Name (Arabic) *</label>
                                    <input type="text" v-model="formData.name.ar" required placeholder="أدخل الاسم بالعربية">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4><i class="fas fa-align-left"></i> Descriptions</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Description (English)</label>
                                    <textarea v-model="formData.description.en" rows="2" placeholder="Enter description in English"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Description (Arabic)</label>
                                    <textarea v-model="formData.description.ar" rows="2" placeholder="أدخل الوصف بالعربية"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4><i class="fas fa-cog"></i> Configuration</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Points Per Day *</label>
                                    <input type="number" v-model="formData.points_per_day" required min="0" placeholder="100">
                                </div>
                                <div class="form-group">
                                    <label>View Boost (%) *</label>
                                    <input type="number" v-model="formData.view_boost_percentage" required min="0" placeholder="50">
                                </div>
                                <div class="form-group">
                                    <label>Display Order *</label>
                                    <input type="number" v-model="formData.display_order" required min="1" placeholder="1">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4><i class="fas fa-palette"></i> Appearance</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Icon (Font Awesome class)</label>
                                    <input type="text" v-model="formData.icon" placeholder="fas fa-star">
                                    <small>Example: fas fa-star, fas fa-trophy, fas fa-crown</small>
                                </div>
                                <div class="form-group">
                                    <label>Color *</label>
                                    <input type="color" v-model="formData.color" required>
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
                                    <input type="checkbox" v-model="formData.is_premium">
                                    <span><i class="fas fa-crown"></i> Premium Level</span>
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
                        {{ processing ? 'Processing...' : (isEditMode ? 'Update Level' : 'Create Level') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal-overlay" v-if="showDeleteModal" @click="closeDeleteModal">
            <div class="modern-modal small" @click.stop>
                <div class="modal-header danger">
                    <h3><i class="fas fa-exclamation-triangle"></i> Delete Level</h3>
                    <button class="close-btn" @click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>{{ getLevelName(selectedLevel) }}</strong>?</p>
                    <p class="warning-text"><i class="fas fa-exclamation-circle"></i> This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button @click="closeDeleteModal" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button @click="handleDelete" :disabled="processing" class="btn-danger">
                        <i v-if="processing" class="fas fa-spinner fa-spin"></i>
                        <i v-else class="fas fa-trash"></i>
                        {{ processing ? 'Deleting...' : 'Delete Level' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useLevels } from '../../composables/useLevels'

const {
    levels,
    stats,
    loading,
    error,
    fetchLevels,
    fetchStats,
    createLevel,
    updateLevel,
    deleteLevel,
    toggleStatus
} = useLevels()

const filters = ref({
    search: '',
    premium: '',
    status: '',
    sort_by: 'display_order',
    sort_direction: 'asc',
    page: 1,
    per_page: 15
})

const formData = ref({
    name: { ar: '', en: '' },
    description: { ar: '', en: '' },
    points_per_day: '',
    view_boost_percentage: '',
    display_order: '',
    icon: '',
    color: '#000000',
    is_active: true,
    is_premium: false
})

const showFormModal = ref(false)
const showDeleteModal = ref(false)
const isEditMode = ref(false)
const selectedLevel = ref(null)
const processing = ref(false)

let searchTimeout = null

const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        filters.value.page = 1
        loadLevels()
    }, 500)
}

const loadLevels = async () => {
    await fetchLevels(filters.value)
}

const changePage = (page) => {
    if (page < 1 || page > levels.value.last_page) return
    filters.value.page = page
    loadLevels()
}

const displayPages = computed(() => {
    const current = levels.value.current_page
    const last = levels.value.last_page
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
        points_per_day: '',
        view_boost_percentage: '',
        display_order: '',
        icon: '',
        color: '#000000',
        is_active: true,
        is_premium: false
    }
}

const openCreateModal = () => {
    resetForm()
    isEditMode.value = false
    showFormModal.value = true
}

const openEditModal = (level) => {
    formData.value = {
        name: {
            ar: level.name?.ar || '',
            en: level.name?.en || ''
        },
        description: {
            ar: level.description?.ar || '',
            en: level.description?.en || ''
        },
        points_per_day: level.points_per_day,
        view_boost_percentage: level.view_boost_percentage,
        display_order: level.display_order,
        icon: level.icon || '',
        color: level.color || '#000000',
        is_active: level.is_active,
        is_premium: level.is_premium
    }
    selectedLevel.value = level
    isEditMode.value = true
    showFormModal.value = true
}

const openDeleteModal = (level) => {
    selectedLevel.value = level
    showDeleteModal.value = true
}

const closeFormModal = () => {
    showFormModal.value = false
    resetForm()
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
    selectedLevel.value = null
}

const getLevelName = (level) => {
    return level?.name?.en || level?.name || 'Unknown Level'
}

const handleSubmit = async () => {
    processing.value = true
    try {
        if (isEditMode.value) {
            await updateLevel(selectedLevel.value.id, formData.value)
            window.$(document).Toasts('create', {
                class: 'bg-success',
                title: 'Success',
                body: 'Level updated successfully'
            })
        } else {
            await createLevel(formData.value)
            window.$(document).Toasts('create', {
                class: 'bg-success',
                title: 'Success',
                body: 'Level created successfully'
            })
        }

        closeFormModal()
        await loadLevels()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to save level'
        })
    } finally {
        processing.value = false
    }
}

const handleDelete = async () => {
    if (!selectedLevel.value) return

    processing.value = true
    try {
        await deleteLevel(selectedLevel.value.id)
        closeDeleteModal()

        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'Level deleted successfully'
        })

        await loadLevels()
        await fetchStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to delete level'
        })
    } finally {
        processing.value = false
    }
}

const handleToggleStatus = async (levelId) => {
    try {
        await toggleStatus(levelId)
        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'Level status updated'
        })
        await loadLevels()
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
    console.log('📋 LevelsList component mounted')
    await loadLevels()
    await fetchStats()
})
</script>

<style scoped>
.modern-levels-container {
    padding: 20px;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
}

.stat-card:nth-child(2) {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    box-shadow: 0 4px 15px rgba(240, 147, 251, 0.4);
}

.stat-card:nth-child(2):hover {
    box-shadow: 0 8px 25px rgba(240, 147, 251, 0.5);
}

.stat-card:nth-child(3) {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);
}

.stat-card:nth-child(3):hover {
    box-shadow: 0 8px 25px rgba(79, 172, 254, 0.5);
}

.stat-card:nth-child(4) {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    box-shadow: 0 4px 15px rgba(67, 233, 123, 0.4);
}

.stat-card:nth-child(4):hover {
    box-shadow: 0 8px 25px rgba(67, 233, 123, 0.5);
}

.stat-icon {
    font-size: 3rem;
    opacity: 0.9;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.95rem;
    opacity: 0.95;
}

/* Modern Card */
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

.create-btn {
    background: white;
    color: #667eea;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.create-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Filters Section */
.filters-section {
    padding: 25px 30px;
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.search-box {
    flex: 1;
    min-width: 250px;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

.search-box input {
    width: 100%;
    padding: 10px 15px 10px 45px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filter-select {
    padding: 10px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Loading & Error States */
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

/* Table Container */
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

.empty-state i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

.order-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
}

.level-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.level-icon {
    font-size: 1.5rem;
}

.level-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 3px;
}

.level-name-ar {
    font-size: 13px;
    color: #6c757d;
}

.points-badge {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    display: inline-block;
}

.boost-badge {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    display: inline-block;
}

.type-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.type-badge.premium {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.type-badge.regular {
    background: #6c757d;
    color: white;
}

.status-toggle {
    padding: 6px 14px;
    border: none;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.status-toggle.active {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
}

.status-toggle.inactive {
    background: #6c757d;
    color: white;
}

.status-toggle:hover {
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
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.action-btn.delete {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 30px;
    border-top: 1px solid #e9ecef;
    flex-wrap: wrap;
    gap: 15px;
}

.pagination-info {
    color: #6c757d;
    font-size: 14px;
}

.pagination-controls {
    display: flex;
    gap: 5px;
}

.page-btn {
    padding: 8px 12px;
    border: 1px solid #e9ecef;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #495057;
    font-weight: 500;
}

.page-btn:hover:not(:disabled) {
    background: #f8f9fa;
    border-color: #667eea;
}

.page-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}

.page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Modal Overlay */
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
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.modern-modal.small {
    max-width: 500px;
}

.modern-modal.large {
    max-width: 800px;
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

.modal-header.danger {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.modal-header h3 {
    margin: 0;
    font-size: 1.3rem;
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
    transform: rotate(90deg);
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
    color: #667eea;
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
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-danger {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
