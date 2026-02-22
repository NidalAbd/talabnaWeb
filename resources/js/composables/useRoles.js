import { ref } from 'vue'

// Updated: Force cache bust
export function useRoles() {
    console.log('🔧 useRoles composable initialized')

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

            const url = `/api/admin/roles?${params.toString()}`
            console.log('🔍 Fetching roles from:', url)

            const response = await fetch(url)
            console.log('📡 Response status:', response.status, response.statusText)

            if (!response.ok) {
                const errorText = await response.text()
                console.error('❌ Response error:', errorText)
                throw new Error('Failed to fetch roles')
            }

            const data = await response.json()
            console.log('✅ Roles data received:', data)
            console.log('📊 Roles nested object:', data.roles)
            console.log('📊 Actual roles array:', data.roles?.data)

            // Replace the entire ref value to ensure reactivity
            if (data.roles) {
                roles.value = {
                    data: [...(data.roles.data || [])],
                    current_page: data.roles.current_page || 1,
                    last_page: data.roles.last_page || 1,
                    per_page: data.roles.per_page || 10,
                    total: data.roles.total || 0
                }
            } else if (data.data) {
                roles.value = {
                    data: [...(data.data || [])],
                    current_page: data.current_page || 1,
                    last_page: data.last_page || 1,
                    per_page: data.per_page || 10,
                    total: data.total || 0
                }
            } else {
                console.warn('⚠️ Unexpected data format:', data)
                roles.value = {
                    data: [],
                    current_page: 1,
                    last_page: 1,
                    per_page: 10,
                    total: 0
                }
            }

            console.log('🔄 Roles updated:', roles.value.data.length, 'items')
        } catch (err) {
            error.value = err.message
            console.error('❌ Error fetching roles:', err)
        } finally {
            loading.value = false
            console.log('⏱️ Loading complete. Roles count:', roles.value.data?.length || 0)
            console.log('🔍 Loading state:', loading.value)
            console.log('🔍 Final roles:', roles.value)
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

    const createRole = async (roleData) => {
        try {
            const response = await fetch('/api/admin/roles', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(roleData)
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || errorData.message || 'Failed to create role')
            }

            return await response.json()
        } catch (err) {
            console.error('Error creating role:', err)
            throw err
        }
    }

    const updateRole = async (roleId, roleData) => {
        try {
            const response = await fetch(`/api/admin/roles/${roleId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(roleData)
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || errorData.message || 'Failed to update role')
            }

            return await response.json()
        } catch (err) {
            console.error('Error updating role:', err)
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

    const fetchAllPermissions = async () => {
        try {
            const response = await fetch('/api/admin/roles/permissions')

            if (!response.ok) {
                throw new Error('Failed to fetch permissions')
            }

            return await response.json()
        } catch (err) {
            console.error('Error fetching permissions:', err)
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
        createRole,
        updateRole,
        deleteRole,
        fetchAllPermissions,
        getUsersWithRole
    }
}
