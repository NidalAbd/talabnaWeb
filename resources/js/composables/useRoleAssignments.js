import { ref } from 'vue'

export function useRoleAssignments() {
    const roleAssignments = ref([])
    const stats = ref(null)
    const roles = ref([])
    const permissions = ref([])
    const loading = ref(false)
    const error = ref(null)
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
    })

    const fetchRoleAssignments = async (page = 1, filters = {}) => {
        loading.value = true
        error.value = null

        try {
            const params = new URLSearchParams({
                page,
                per_page: pagination.value.per_page,
                ...filters
            })

            const response = await fetch(`/api/admin/role-assignments?${params}`)

            if (!response.ok) {
                throw new Error('Failed to fetch role assignments')
            }

            const data = await response.json()
            roleAssignments.value = data.data
            pagination.value = {
                current_page: data.current_page,
                last_page: data.last_page,
                per_page: data.per_page,
                total: data.total
            }
        } catch (err) {
            error.value = err.message
            console.error('❌ Error fetching role assignments:', err)
        } finally {
            loading.value = false
        }
    }

    const fetchStats = async () => {
        try {
            const response = await fetch('/api/admin/role-assignments/stats')

            if (!response.ok) {
                throw new Error('Failed to fetch stats')
            }

            const data = await response.json()
            stats.value = data
        } catch (err) {
            console.error('❌ Error fetching stats:', err)
        }
    }

    const fetchRoles = async () => {
        try {
            const response = await fetch('/api/admin/role-assignments/roles')

            if (!response.ok) {
                throw new Error('Failed to fetch roles')
            }

            const data = await response.json()
            roles.value = data
        } catch (err) {
            console.error('❌ Error fetching roles:', err)
        }
    }

    const fetchPermissions = async () => {
        try {
            const response = await fetch('/api/admin/role-assignments/permissions')

            if (!response.ok) {
                throw new Error('Failed to fetch permissions')
            }

            const data = await response.json()
            permissions.value = data
        } catch (err) {
            console.error('❌ Error fetching permissions:', err)
        }
    }

    const updateRoleAssignment = async (userId, data) => {
        loading.value = true
        error.value = null

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

            const response = await fetch(`/api/admin/role-assignments/${userId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.message || 'Failed to update role assignment')
            }

            const result = await response.json()
            return result
        } catch (err) {
            error.value = err.message
            console.error('❌ Error updating role assignment:', err)
            throw err
        } finally {
            loading.value = false
        }
    }

    return {
        roleAssignments,
        stats,
        roles,
        permissions,
        loading,
        error,
        pagination,
        fetchRoleAssignments,
        fetchStats,
        fetchRoles,
        fetchPermissions,
        updateRoleAssignment
    }
}
