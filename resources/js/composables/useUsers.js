import { ref } from 'vue'

export function useUsers() {
    const users = ref({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0
    })
    const loading = ref(false)
    const error = ref(null)

    const fetchUsers = async (filters = {}) => {
        loading.value = true
        error.value = null

        try {
            const params = new URLSearchParams()

            if (filters.status) params.append('status', filters.status)
            if (filters.role) params.append('role', filters.role)
            if (filters.search) params.append('search', filters.search)
            if (filters.page) params.append('page', filters.page)
            if (filters.per_page) params.append('per_page', filters.per_page)

            const response = await fetch(`/api/admin/users?${params.toString()}`)

            if (!response.ok) {
                throw new Error('Failed to fetch users')
            }

            const data = await response.json()
            users.value = data.users
        } catch (err) {
            error.value = err.message
            console.error('Error fetching users:', err)
        } finally {
            loading.value = false
        }
    }

    const toggleBan = async (userId) => {
        try {
            const response = await fetch(`/api/admin/users/${userId}/toggle-ban`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                throw new Error('Failed to toggle ban status')
            }

            const data = await response.json()
            return data
        } catch (err) {
            console.error('Error toggling ban:', err)
            throw err
        }
    }

    const deleteUser = async (userId) => {
        try {
            const response = await fetch(`/api/admin/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to delete user')
            }

            const data = await response.json()
            return data
        } catch (err) {
            console.error('Error deleting user:', err)
            throw err
        }
    }

    const fetchUser = async (userId) => {
        try {
            const response = await fetch(`/api/admin/users/${userId}`)

            if (!response.ok) {
                throw new Error('Failed to fetch user')
            }

            return await response.json()
        } catch (err) {
            console.error('Error fetching user:', err)
            throw err
        }
    }

    const updateUser = async (userId, userData) => {
        try {
            const response = await fetch(`/api/admin/users/${userId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(userData)
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || errorData.message || 'Failed to update user')
            }

            return await response.json()
        } catch (err) {
            console.error('Error updating user:', err)
            throw err
        }
    }

    const getRoles = async () => {
        try {
            const response = await fetch('/api/admin/users/roles')

            if (!response.ok) {
                throw new Error('Failed to fetch roles')
            }

            return await response.json()
        } catch (err) {
            console.error('Error fetching roles:', err)
            throw err
        }
    }

    return {
        users,
        loading,
        error,
        fetchUsers,
        fetchUser,
        updateUser,
        toggleBan,
        deleteUser,
        getRoles
    }
}
