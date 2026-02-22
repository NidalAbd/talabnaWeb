<template>
    <div class="role-assignments-modern">

        <!-- Stats Cards -->
        <div class="stats-dashboard" v-if="stats">
            <div class="stats-grid">
                <div class="stat-card-compact stat-blue">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <div class="stat-value-compact">{{ formatNumber(stats.total_users) }}</div>
                        <div class="stat-label-compact">Total Users</div>
                    </div>
                </div>
                <div class="stat-card-compact stat-green">
                    <div class="stat-icon"><i class="fas fa-user-tag"></i></div>
                    <div class="stat-info">
                        <div class="stat-value-compact">{{ formatNumber(stats.users_with_roles) }}</div>
                        <div class="stat-label-compact">Users with Roles</div>
                    </div>
                </div>
                <div class="stat-card-compact stat-orange" :class="{ clickable: stats.users_without_roles > 0 }" @click="stats.users_without_roles > 0 && confirmFixUsers()">
                    <div class="stat-icon"><i class="fas fa-user-minus"></i></div>
                    <div class="stat-info">
                        <div class="stat-value-compact">{{ formatNumber(stats.users_without_roles) }}</div>
                        <div class="stat-label-compact">Users without Roles</div>
                        <div class="stat-action" v-if="stats.users_without_roles > 0">
                            <i class="fas fa-wrench"></i> Click to fix
                        </div>
                    </div>
                </div>
                <div class="stat-card-compact stat-purple">
                    <div class="stat-icon"><i class="fas fa-key"></i></div>
                    <div class="stat-info">
                        <div class="stat-value-compact">{{ formatNumber(stats.users_with_permissions) }}</div>
                        <div class="stat-label-compact">Custom Permissions</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="search-filter-bar">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input
                    type="text"
                    class="search-input"
                    placeholder="Search by name or email..."
                    v-model="searchQuery"
                    @input="onSearch"
                >
            </div>
            <div class="filter-group">
                <select class="filter-select" v-model="roleFilter" @change="onFilterChange">
                    <option value="">All Roles</option>
                    <option v-for="role in roles" :key="role.id" :value="role.id">
                        {{ role.display_name || role.name }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading role assignments...</p>
        </div>

        <!-- Table -->
        <div v-else class="data-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Permissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody v-if="roleAssignments.length > 0">
                    <tr v-for="user in roleAssignments" :key="user.id">
                        <td><span class="id-badge">{{ user.id }}</span></td>
                        <td><strong>{{ user.user_name }}</strong></td>
                        <td>{{ user.email }}</td>
                        <td>
                            <span
                                v-for="role in user.roles"
                                :key="role.id"
                                class="badge primary"
                            >
                                {{ role.display_name || role.name }}
                            </span>
                            <span v-if="!user.roles || user.roles.length === 0" class="text-muted">
                                No roles assigned
                            </span>
                        </td>
                        <td>
                            <span class="badge info">
                                {{ user.permissions_count || 0 }} custom
                            </span>
                        </td>
                        <td>
                            <button
                                class="action-btn primary small"
                                @click="editUser(user)"
                                title="Edit Roles & Permissions"
                            >
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No users found</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container" v-if="pagination.last_page > 1">
            <div class="pagination-info">
                Showing {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }}
                to {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}
                of {{ pagination.total }} entries
            </div>
            <div class="pagination-controls">
                <button
                    class="pagination-btn"
                    :disabled="pagination.current_page === 1"
                    @click="changePage(pagination.current_page - 1)"
                >
                    <i class="fas fa-chevron-left"></i>
                    Previous
                </button>
                <button
                    v-for="page in displayedPages"
                    :key="page"
                    class="pagination-btn"
                    :class="{ active: page === pagination.current_page, dots: page === '...' }"
                    @click="page !== '...' && changePage(page)"
                    :disabled="page === '...'"
                >
                    {{ page }}
                </button>
                <button
                    class="pagination-btn"
                    :disabled="pagination.current_page === pagination.last_page"
                    @click="changePage(pagination.current_page + 1)"
                >
                    Next
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal-overlay" v-if="showEditModal" @click="closeEditModal">
            <div class="modern-modal large" @click.stop>
                <div class="modal-header">
                    <h3>
                        <i class="fas fa-user-shield"></i>
                        Edit Roles & Permissions: {{ editingUser?.user_name }}
                    </h3>
                    <button class="close-btn" @click="closeEditModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-section">
                        <label class="section-label">Roles</label>
                        <div class="checkbox-grid">
                            <div class="checkbox-item" v-for="role in roles" :key="role.id">
                                <input
                                    type="checkbox"
                                    class="checkbox-input"
                                    :id="'role-' + role.id"
                                    :value="role.id"
                                    v-model="selectedRoles"
                                >
                                <label class="checkbox-label" :for="'role-' + role.id">
                                    <span class="checkbox-title">{{ role.display_name || role.name }}</span>
                                    <span class="checkbox-desc" v-if="role.description">
                                        {{ role.description }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <label class="section-label">Custom Permissions</label>
                        <div class="checkbox-grid scrollable">
                            <div class="checkbox-item" v-for="permission in permissions" :key="permission.id">
                                <input
                                    type="checkbox"
                                    class="checkbox-input"
                                    :id="'perm-' + permission.id"
                                    :value="permission.id"
                                    v-model="selectedPermissions"
                                >
                                <label class="checkbox-label" :for="'perm-' + permission.id">
                                    <span class="checkbox-title">{{ permission.display_name || permission.name }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="action-btn secondary" @click="closeEditModal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button class="action-btn primary" @click="saveRoleAssignment">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoleAssignments } from '../../composables/useRoleAssignments'

const {
    roleAssignments,
    stats,
    roles,
    permissions,
    loading,
    pagination,
    fetchRoleAssignments,
    fetchStats,
    fetchRoles,
    fetchPermissions,
    updateRoleAssignment
} = useRoleAssignments()

const formatNumber = (value) => {
  if (value === null || value === undefined) return '0'
  return new Intl.NumberFormat().format(value)
}

const searchQuery = ref('')
const roleFilter = ref('')
const editingUser = ref(null)
const selectedRoles = ref([])
const selectedPermissions = ref([])
const showEditModal = ref(false)
const fixingUsers = ref(false)

let searchTimeout = null

const displayedPages = computed(() => {
    const pages = []
    const currentPage = pagination.value.current_page
    const lastPage = pagination.value.last_page
    const delta = 2

    for (let i = Math.max(2, currentPage - delta); i <= Math.min(lastPage - 1, currentPage + delta); i++) {
        pages.push(i)
    }

    if (currentPage - delta > 2) {
        pages.unshift('...')
    }
    if (currentPage + delta < lastPage - 1) {
        pages.push('...')
    }

    pages.unshift(1)
    if (lastPage > 1) {
        pages.push(lastPage)
    }

    return pages.filter((page, index, self) => self.indexOf(page) === index)
})

const onSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        loadData()
    }, 500)
}

const onFilterChange = () => {
    loadData()
}

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        loadData(page)
    }
}

const loadData = (page = 1) => {
    const filters = {}
    if (searchQuery.value) {
        filters.search = searchQuery.value
    }
    if (roleFilter.value) {
        filters.role_id = roleFilter.value
    }
    fetchRoleAssignments(page, filters)
}

const editUser = (user) => {
    editingUser.value = user
    selectedRoles.value = user.roles ? user.roles.map(r => r.id) : []
    selectedPermissions.value = user.permissions ? user.permissions.map(p => p.id) : []
    showEditModal.value = true
}

const closeEditModal = () => {
    showEditModal.value = false
    editingUser.value = null
    selectedRoles.value = []
    selectedPermissions.value = []
}

const saveRoleAssignment = async () => {
    try {
        await updateRoleAssignment(editingUser.value.id, {
            roles: selectedRoles.value,
            permissions: selectedPermissions.value
        })

        closeEditModal()
        window.toastr.success('Role assignment updated successfully')
        loadData(pagination.value.current_page)
        fetchStats()
    } catch (error) {
        window.toastr.error(error.message || 'Failed to update role assignment')
    }
}

const confirmFixUsers = async () => {
    if (fixingUsers.value) return
    if (!confirm(`Assign default "user" role to ${stats.value.users_without_roles} users without roles?`)) return

    fixingUsers.value = true
    try {
        const response = await fetch('/api/admin/role-assignments/fix-without-roles', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })

        if (!response.ok) {
            const data = await response.json()
            throw new Error(data.error || 'Failed to fix users')
        }

        const data = await response.json()
        window.toastr.success(data.message)
        loadData(pagination.value.current_page)
        fetchStats()
    } catch (error) {
        window.toastr.error(error.message || 'Failed to fix users without roles')
    } finally {
        fixingUsers.value = false
    }
}

onMounted(() => {
    loadData()
    fetchStats()
    fetchRoles()
    fetchPermissions()
})
</script>

<style scoped>
.role-assignments-modern {
    padding: 0;
}

/* Header Section */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
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
    color: #6f42c1;
}

.section-subtitle {
    color: #666;
    font-size: 0.95rem;
    margin: 0;
}

/* Clickable stat card */
.stat-card-compact.clickable {
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card-compact.clickable:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-action {
    font-size: 0.75rem;
    color: #e67e22;
    font-weight: 600;
    margin-top: 0.25rem;
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
    border-color: #6f42c1;
    box-shadow: 0 0 0 3px rgba(111, 66, 193, 0.1);
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
    border-color: #6f42c1;
}

/* Loading State */
.loading-state {
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
    border-top: 4px solid #6f42c1;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
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

.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.75rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-right: 0.25rem;
    margin-bottom: 0.25rem;
}

.badge.primary {
    background: #e3f2fd;
    color: #1976d2;
}

.badge.info {
    background: #e1f5fe;
    color: #0288d1;
}

.text-muted {
    color: #999;
}

/* Action Buttons */
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
    background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
    color: white;
}

.action-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(111, 66, 193, 0.3);
}

.action-btn.secondary {
    background: #6c757d;
    color: white;
}

.action-btn.secondary:hover {
    background: #5a6268;
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
    border-color: #6f42c1;
    color: #6f42c1;
}

.pagination-btn.active {
    background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
    border-color: #6f42c1;
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
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow: auto;
}

.modern-modal.large {
    max-width: 800px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e8ecef;
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

.form-section {
    margin-bottom: 2rem;
}

.form-section:last-child {
    margin-bottom: 0;
}

.section-label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 1rem;
    font-size: 1rem;
}

.checkbox-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 0.75rem;
}

.checkbox-grid.scrollable {
    max-height: 300px;
    overflow-y: auto;
    padding-right: 0.5rem;
}

.checkbox-item {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

.checkbox-input {
    margin-top: 0.25rem;
    cursor: pointer;
}

.checkbox-label {
    flex: 1;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.checkbox-title {
    font-weight: 500;
    color: #333;
    font-size: 0.9rem;
}

.checkbox-desc {
    font-size: 0.8rem;
    color: #999;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.5rem;
    border-top: 1px solid #e8ecef;
}

/* Responsive */
@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
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
        min-width: 800px;
    }

    .pagination-container {
        flex-direction: column;
        align-items: flex-start;
    }

    .pagination-controls {
        width: 100%;
        justify-content: center;
    }

    .checkbox-grid {
        grid-template-columns: 1fr;
    }
}
</style>
