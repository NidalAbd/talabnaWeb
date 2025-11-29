import { ref } from 'vue'

export function useRoles() {
    const roles = ref({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0
    })
    const loading = ref(false)
    const error = ref(null)

    const fetchRoles = async (filters = {}) => {
        loading.value = true
        error.value = null

        try {
            const params = new URLSearchParams()

            if (filters.search) params.append('search', filters.search)
            if (filters.sort_by) params.append('sort_by', filters.sort_by)
            if (filters.sort_direction) params.append('sort_direction', filters.sort_direction)
            if (filters.page) params.append('page', filters.page)
            if (filters.per_page) params.append('per_page', filters.per_page)

            const response = await fetch(`/api/admin/roles?${params.toString()}`)

            if (!response.ok) {
                throw new Error('Failed to fetch roles')
            }

            const data = await response.json()
            roles.value = data.roles
        } catch (err) {
            error.value = err.message
            console.error('Error fetching roles:', err)
        } finally {
            loading.value = false
        }
    }

    const fetchRole = async (roleId) => {
        try {
            const response = await fetch(`/api/admin/roles/${roleId}`)

            if (!response.ok) {
                throw new Error('Failed to fetch role')
            }

            const data = await response.json()
            return data
        } catch (err) {
            console.error('Error fetching role:', err)
            throw err
        }
    }

    const deleteRole = async (roleId) => {
        try {
            const response = await fetch(`/api/admin/roles/${roleId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to delete role')
            }

            const data = await response.json()
            return data
        } catch (err) {
            console.error('Error deleting role:', err)
            throw err
        }
    }

    const getUsersWithRole = async (roleId, page = 1) => {
        try {
            const response = await fetch(`/api/admin/roles/${roleId}/users?page=${page}`)

            if (!response.ok) {
                throw new Error('Failed to fetch users')
            }

            const data = await response.json()
            return data
        } catch (err) {
            console.error('Error fetching users with role:', err)
            throw err
        }
    }

    return {
        roles,
        loading,
        error,
        fetchRoles,
        fetchRole,
        deleteRole,
        getUsersWithRole
    }
}
