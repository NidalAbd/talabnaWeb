<template>
    <div class="modern-plans-container">
        <!-- Stats Cards -->
        <div class="stats-dashboard">
            <div class="stats-grid">
                <div class="stat-card-compact stat-blue">
                    <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="stat-info">
                        <div class="stat-value-compact">{{ formatNumber(statsData.total_plans) }}</div>
                        <div class="stat-label-compact">Total Plans</div>
                    </div>
                </div>
                <div class="stat-card-compact stat-green">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info">
                        <div class="stat-value-compact">{{ formatNumber(statsData.active_plans) }}</div>
                        <div class="stat-label-compact">Active Plans</div>
                    </div>
                </div>
                <div class="stat-card-compact stat-orange">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <div class="stat-value-compact">{{ formatNumber(statsData.active_subscribers) }}</div>
                        <div class="stat-label-compact">Active Subscribers</div>
                    </div>
                </div>
                <div class="stat-card-compact stat-purple">
                    <div class="stat-icon"><i class="fas fa-coins"></i></div>
                    <div class="stat-info">
                        <div class="stat-value-compact">{{ formatNumber(statsData.total_revenue) }}</div>
                        <div class="stat-label-compact">Total Revenue (pts)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="search-filter-bar">
            <div class="search-input-group">
                <i class="fas fa-search search-icon"></i>
                <input
                    type="text"
                    v-model="filters.search"
                    @input="debouncedSearch"
                    class="search-input"
                    placeholder="Search plans..."
                >
            </div>
            <div class="filter-group">
                <select class="filter-select" v-model="filters.status" @change="loadPlans">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button class="action-btn primary" @click="openCreateModal">
                    <i class="fas fa-plus"></i> Add Plan
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading subscription plans...</p>
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
                        <th>Plan Name</th>
                        <th>Slug</th>
                        <th>Price (pts)</th>
                        <th>Duration</th>
                        <th>Features</th>
                        <th>Subscribers</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="plans.data.length === 0">
                        <td colspan="8" class="empty-state">
                            <i class="fas fa-layer-group"></i>
                            <p>No subscription plans found</p>
                        </td>
                    </tr>
                    <tr v-for="plan in plans.data" :key="plan.id">
                        <td>
                            <div class="plan-name">
                                <div class="name-ar">{{ plan.name?.ar || '-' }}</div>
                                <div class="name-en">{{ plan.name?.en || '-' }}</div>
                                <span v-if="plan.is_popular" class="popular-badge">
                                    <i class="fas fa-star"></i> Popular
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="slug-badge">{{ plan.slug }}</span>
                        </td>
                        <td>
                            <span class="points-badge">{{ formatNumber(plan.price_points) }} pts</span>
                        </td>
                        <td>
                            <span class="duration-badge">{{ plan.duration_days }}d</span>
                        </td>
                        <td>
                            <span class="features-count-badge">{{ countFeatures(plan.features) }}</span>
                        </td>
                        <td>
                            <span class="subscribers-badge">{{ formatNumber(plan.subscribers_count || 0) }}</span>
                        </td>
                        <td>
                            <span :class="['status-badge', plan.is_active ? 'active' : 'inactive']">
                                {{ plan.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button @click="openEditModal(plan)" class="action-btn-small edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click="handleToggleActive(plan.id)" class="action-btn-small toggle" title="Toggle Active">
                                    <i :class="plan.is_active ? 'fas fa-toggle-on' : 'fas fa-toggle-off'"></i>
                                </button>
                                <button @click="handleDelete(plan)" class="action-btn-small delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container" v-if="plans.last_page > 1">
            <div class="pagination-info">
                Showing {{ ((plans.current_page - 1) * plans.per_page) + 1 }}
                to {{ Math.min(plans.current_page * plans.per_page, plans.total) }}
                of {{ plans.total }} entries
            </div>
            <div class="pagination-controls">
                <button
                    @click="changePage(plans.current_page - 1)"
                    :disabled="plans.current_page === 1"
                    class="page-btn"
                >
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button
                    v-for="page in displayPages"
                    :key="page"
                    @click="changePage(page)"
                    :class="['page-btn', { active: page === plans.current_page }]"
                >
                    {{ page }}
                </button>
                <button
                    @click="changePage(plans.current_page + 1)"
                    :disabled="plans.current_page === plans.last_page"
                    class="page-btn"
                >
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div class="modal-overlay" v-if="showFormModal" @click="closeFormModal">
            <div class="modal-dialog-advanced" @click.stop>
                <div class="modal-header">
                    <h3>
                        <i class="fas fa-layer-group"></i>
                        {{ isEditMode ? 'Edit Plan' : 'Create Plan' }}
                    </h3>
                    <button class="close-btn" @click="closeFormModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="handleSubmit">
                        <!-- Names -->
                        <div class="form-section">
                            <h4><i class="fas fa-language"></i> Names</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Name (Arabic) *</label>
                                    <input type="text" v-model="formData.name_ar" required class="form-control-modern" placeholder="أدخل اسم الخطة">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Name (English) *</label>
                                    <input type="text" v-model="formData.name_en" required class="form-control-modern" placeholder="Enter plan name" @input="generateSlug">
                                </div>
                            </div>
                        </div>

                        <!-- Descriptions -->
                        <div class="form-section">
                            <h4><i class="fas fa-align-left"></i> Descriptions</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Description (Arabic)</label>
                                    <textarea v-model="formData.description_ar" rows="3" class="form-control-modern" placeholder="أدخل الوصف"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Description (English)</label>
                                    <textarea v-model="formData.description_en" rows="3" class="form-control-modern" placeholder="Enter description"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Slug & Pricing -->
                        <div class="form-section">
                            <h4><i class="fas fa-tag"></i> Slug & Pricing</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Slug *</label>
                                    <input type="text" v-model="formData.slug" required class="form-control-modern" placeholder="auto-generated-slug">
                                    <span class="form-hint">Auto-generated from English name</span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Price Points *</label>
                                    <input type="number" v-model="formData.price_points" required min="0" class="form-control-modern" placeholder="1000">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Duration Days *</label>
                                    <input type="number" v-model="formData.duration_days" required min="1" class="form-control-modern" placeholder="30">
                                </div>
                            </div>
                        </div>

                        <!-- Features (Number Inputs) -->
                        <div class="form-section">
                            <h4><i class="fas fa-list-check"></i> Features (Limits)</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">AI Images / Month</label>
                                    <input type="number" v-model="formData.features.ai_images_per_month" min="0" class="form-control-modern" placeholder="0">
                                    <span class="form-hint">Number of AI-generated images per month</span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Bonus Points %</label>
                                    <input type="number" v-model="formData.features.bonus_points_percent" min="0" max="100" class="form-control-modern" placeholder="0">
                                    <span class="form-hint">Percentage bonus on point purchases</span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Featured Posts</label>
                                    <input type="number" v-model="formData.features.featured_posts" min="0" class="form-control-modern" placeholder="0">
                                    <span class="form-hint">Number of featured post slots</span>
                                </div>
                            </div>
                            <div class="form-row" style="margin-top: 15px;">
                                <div class="form-group">
                                    <label class="form-label">Max Photos / Post</label>
                                    <input type="number" v-model="formData.features.max_photos_per_post" min="1" class="form-control-modern" placeholder="5">
                                    <span class="form-hint">Maximum photos allowed per post</span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Badge Discount %</label>
                                    <input type="number" v-model="formData.features.badge_discount_percent" min="0" max="100" class="form-control-modern" placeholder="0">
                                    <span class="form-hint">Discount on badge purchases</span>
                                </div>
                            </div>
                        </div>

                        <!-- Feature Toggles -->
                        <div class="form-section">
                            <h4><i class="fas fa-toggle-on"></i> Feature Toggles</h4>
                            <div class="checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" v-model="formData.features.auto_translate_posts">
                                    <span>Auto Translate Posts</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" v-model="formData.features.priority_support">
                                    <span>Priority Support</span>
                                </label>
                            </div>
                        </div>

                        <!-- Appearance -->
                        <div class="form-section">
                            <h4><i class="fas fa-palette"></i> Appearance</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Color</label>
                                    <input type="text" v-model="formData.color" class="form-control-modern" placeholder="#3498db">
                                    <span class="form-hint">Hex color code (e.g. #3498db)</span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Icon Name</label>
                                    <input type="text" v-model="formData.icon" class="form-control-modern" placeholder="fas fa-crown">
                                    <span class="form-hint">Font Awesome class name</span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" v-model="formData.sort_order" min="0" class="form-control-modern" placeholder="0">
                                </div>
                            </div>
                        </div>

                        <!-- Settings -->
                        <div class="form-section">
                            <h4><i class="fas fa-cog"></i> Settings</h4>
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
                        {{ processing ? 'Processing...' : (isEditMode ? 'Update Plan' : 'Create Plan') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useSubscriptionPlans } from '../../composables/useSubscriptionPlans'

const {
    plans,
    loading,
    error,
    fetchPlans,
    createPlan,
    updatePlan,
    deletePlan,
    toggleActive,
    fetchStats
} = useSubscriptionPlans()

const statsData = ref({
    total_plans: 0,
    active_plans: 0,
    active_subscribers: 0,
    total_revenue: 0
})

const filters = reactive({
    search: '',
    status: '',
    page: 1,
    per_page: 15
})

const showFormModal = ref(false)
const isEditMode = ref(false)
const selectedPlan = ref(null)
const processing = ref(false)

const defaultFormData = () => ({
    name_ar: '',
    name_en: '',
    description_ar: '',
    description_en: '',
    slug: '',
    price_points: '',
    duration_days: 30,
    features: {
        ai_images_per_month: 0,
        bonus_points_percent: 0,
        featured_posts: 0,
        max_photos_per_post: 5,
        badge_discount_percent: 0,
        auto_translate_posts: false,
        priority_support: false
    },
    color: '',
    icon: '',
    sort_order: 0,
    is_active: true,
    is_popular: false
})

const formData = ref(defaultFormData())

const formatNumber = (value) => {
    if (value === null || value === undefined) return '0'
    return new Intl.NumberFormat().format(value)
}

const countFeatures = (features) => {
    if (!features || typeof features !== 'object') return 0
    return Object.keys(features).length
}

const generateSlug = () => {
    if (formData.value.name_en) {
        formData.value.slug = formData.value.name_en
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim()
    }
}

let searchTimeout = null

const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        filters.page = 1
        loadPlans()
    }, 500)
}

const loadPlans = async () => {
    await fetchPlans({
        search: filters.search,
        status: filters.status,
        page: filters.page,
        per_page: filters.per_page
    })
}

const loadStats = async () => {
    try {
        const data = await fetchStats()
        if (data) {
            statsData.value = {
                total_plans: data.total_plans || 0,
                active_plans: data.active_plans || 0,
                active_subscribers: data.active_subscribers || 0,
                total_revenue: data.total_revenue || 0
            }
        }
    } catch (err) {
        console.error('Failed to load stats:', err)
    }
}

const changePage = (page) => {
    if (page < 1 || page > plans.value.last_page) return
    filters.page = page
    loadPlans()
}

const displayPages = computed(() => {
    const current = plans.value.current_page
    const last = plans.value.last_page
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
    formData.value = defaultFormData()
}

const openCreateModal = () => {
    resetForm()
    isEditMode.value = false
    showFormModal.value = true
}

const openEditModal = (plan) => {
    const features = plan.features || {}
    formData.value = {
        name_ar: plan.name?.ar || '',
        name_en: plan.name?.en || '',
        description_ar: plan.description?.ar || '',
        description_en: plan.description?.en || '',
        slug: plan.slug || '',
        price_points: plan.price_points,
        duration_days: plan.duration_days,
        features: {
            ai_images_per_month: features.ai_images_per_month || 0,
            bonus_points_percent: features.bonus_points_percent || 0,
            featured_posts: features.featured_posts || 0,
            max_photos_per_post: features.max_photos_per_post || 5,
            badge_discount_percent: features.badge_discount_percent || 0,
            auto_translate_posts: !!features.auto_translate_posts,
            priority_support: !!features.priority_support
        },
        color: plan.color || '',
        icon: plan.icon || '',
        sort_order: plan.sort_order || 0,
        is_active: plan.is_active,
        is_popular: plan.is_popular
    }
    selectedPlan.value = plan
    isEditMode.value = true
    showFormModal.value = true
}

const closeFormModal = () => {
    showFormModal.value = false
    resetForm()
}

const handleSubmit = async () => {
    processing.value = true
    try {
        const payload = {
            name: { ar: formData.value.name_ar, en: formData.value.name_en },
            description: { ar: formData.value.description_ar, en: formData.value.description_en },
            slug: formData.value.slug,
            price_points: formData.value.price_points,
            duration_days: formData.value.duration_days,
            features: formData.value.features,
            color: formData.value.color,
            icon: formData.value.icon,
            sort_order: formData.value.sort_order,
            is_active: formData.value.is_active,
            is_popular: formData.value.is_popular
        }

        if (isEditMode.value) {
            await updatePlan(selectedPlan.value.id, payload)
            window.$(document).Toasts('create', {
                class: 'bg-success',
                title: 'Success',
                body: 'Plan updated successfully'
            })
        } else {
            await createPlan(payload)
            window.$(document).Toasts('create', {
                class: 'bg-success',
                title: 'Success',
                body: 'Plan created successfully'
            })
        }

        closeFormModal()
        await loadPlans()
        await loadStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to save plan'
        })
    } finally {
        processing.value = false
    }
}

const handleToggleActive = async (planId) => {
    try {
        await toggleActive(planId)
        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'Plan status updated'
        })
        await loadPlans()
        await loadStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message
        })
    }
}

const handleDelete = async (plan) => {
    if (!confirm(`Are you sure you want to delete "${plan.name?.en || plan.slug}"? This action cannot be undone.`)) return

    try {
        await deletePlan(plan.id)
        window.$(document).Toasts('create', {
            class: 'bg-success',
            title: 'Success',
            body: 'Plan deleted successfully'
        })
        await loadPlans()
        await loadStats()
    } catch (err) {
        window.$(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Error',
            body: err.message || 'Failed to delete plan'
        })
    }
}

watch(() => filters.status, () => {
    filters.page = 1
})

onMounted(async () => {
    await loadPlans()
    await loadStats()
})
</script>

<style scoped>
.modern-plans-container {
    padding: 0;
}

/* Stats Dashboard */
.stats-dashboard {
    margin-bottom: 1.5rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

.stat-card-compact {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border-left: 4px solid transparent;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card-compact:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.stat-card-compact.stat-blue {
    border-left-color: #3498db;
}

.stat-card-compact.stat-blue .stat-icon {
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
}

.stat-card-compact.stat-green {
    border-left-color: #27ae60;
}

.stat-card-compact.stat-green .stat-icon {
    background: rgba(39, 174, 96, 0.1);
    color: #27ae60;
}

.stat-card-compact.stat-orange {
    border-left-color: #f39c12;
}

.stat-card-compact.stat-orange .stat-icon {
    background: rgba(243, 156, 18, 0.1);
    color: #f39c12;
}

.stat-card-compact.stat-purple {
    border-left-color: #9b59b6;
}

.stat-card-compact.stat-purple .stat-icon {
    background: rgba(155, 89, 182, 0.1);
    color: #9b59b6;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stat-info {
    flex: 1;
}

.stat-value-compact {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1.2;
}

.stat-label-compact {
    font-size: 0.8rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 2px;
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
    align-items: center;
}

.search-input-group {
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

.action-btn.primary {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
}

.action-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
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

/* Plan Name Cell */
.plan-name {
    max-width: 220px;
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

.popular-badge {
    display: inline-block;
    margin-top: 4px;
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
}

.slug-badge {
    background: #e8ecef;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    display: inline-block;
    font-family: monospace;
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

.duration-badge {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    display: inline-block;
}

.features-count-badge {
    background: #e8ecef;
    color: #495057;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    display: inline-block;
}

.subscribers-badge {
    background: rgba(155, 89, 182, 0.1);
    color: #9b59b6;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    display: inline-block;
}

/* Status Badge */
.status-badge {
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.active {
    background: #d4edda;
    color: #28a745;
}

.status-badge.inactive {
    background: #f8d7da;
    color: #dc3545;
}

/* Action Buttons (Small) */
.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.action-btn-small {
    padding: 6px 10px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.85rem;
}

.action-btn-small.edit {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

.action-btn-small.toggle {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
}

.action-btn-small.delete {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
}

.action-btn-small:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
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

.page-btn {
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

.page-btn:hover:not(:disabled) {
    border-color: #3498db;
    color: #3498db;
}

.page-btn.active {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    border-color: #3498db;
    color: white;
}

.page-btn:disabled {
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

.modal-dialog-advanced {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    width: 100%;
    max-width: 900px;
    max-height: 90vh;
    overflow: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #e8ecef;
    border-radius: 12px 12px 0 0;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.3rem;
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

.modal-footer {
    padding: 20px 30px;
    background: #f8f9fa;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    border-radius: 0 0 12px 12px;
}

/* Form Styles */
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

.form-label {
    font-weight: 600;
    color: #495057;
    font-size: 14px;
}

.form-control-modern {
    padding: 10px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    width: 100%;
    box-sizing: border-box;
}

.form-control-modern:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-hint {
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

/* Modal Footer Buttons */
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

/* Responsive Design */
@media (max-width: 992px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .search-filter-bar {
        flex-direction: column;
    }

    .search-input-group {
        width: 100%;
        min-width: unset;
    }

    .filter-group {
        width: 100%;
    }

    .filter-select {
        width: 100%;
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

    .modal-dialog-advanced {
        max-width: 100%;
        margin: 0.5rem;
    }

    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>
