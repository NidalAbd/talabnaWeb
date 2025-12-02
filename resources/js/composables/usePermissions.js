import { ref } from 'vue'

export function usePermissions() {
    const permissions = ref({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
    })
    const loading = ref(false)

    const fetchPermissions = async (filters = {}) => {
        loading.value = true
        try {
            const params = new URLSearchParams()

            if (filters.search) params.append('search', filters.search)
            if (filters.category) params.append('category', filters.category)
            if (filters.per_page) params.append('per_page', filters.per_page)
            if (filters.sort_by) params.append('sort_by', filters.sort_by)
            if (filters.sort_direction) params.append('sort_direction', filters.sort_direction)
            if (filters.page) params.append('page', filters.page)

            const url = `/api/admin/permissions?${params.toString()}`
            console.log('🔍 Fetching permissions from:', url)

            const response = await fetch(url)
            console.log('📡 Response status:', response.status, response.statusText)

            if (!response.ok) {
                const errorText = await response.text()
                console.error('❌ Response error:', errorText)
                throw new Error('Failed to fetch permissions')
            }

            const data = await response.json()
            console.log('✅ Permissions data received:', data)
            console.log('📊 Permissions nested object:', data.permissions)
            console.log('📊 Actual permissions array:', data.permissions?.data)

            // Replace the entire ref value to ensure reactivity
            if (data.permissions) {
                permissions.value = {
                    data: [...(data.permissions.data || [])],
                    current_page: data.permissions.current_page || 1,
                    last_page: data.permissions.last_page || 1,
                    per_page: data.permissions.per_page || 15,
                    total: data.permissions.total || 0
                }
            } else if (data.data) {
                permissions.value = {
                    data: [...(data.data || [])],
                    current_page: data.current_page || 1,
                    last_page: data.last_page || 1,
                    per_page: data.per_page || 15,
                    total: data.total || 0
                }
            } else {
                console.warn('⚠️ Unexpected data format:', data)
                permissions.value = {
                    data: [],
                    current_page: 1,
                    last_page: 1,
                    per_page: 15,
                    total: 0
                }
            }

            console.log('🔄 Permissions updated:', permissions.value.data.length, 'items')
        } catch (error) {
            console.error('❌ Error fetching permissions:', error)
            throw error
        } finally {
            loading.value = false
            console.log('⏱️ Loading complete. Permissions count:', permissions.value.data?.length || 0)
            console.log('🔍 Loading state:', loading.value)
            console.log('🔍 Final permissions:', permissions.value)
        }
    }

    const generatePermissions = async (moduleName) => {
        try {
            const response = await fetch('/api/admin/permissions/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ module_name: moduleName })
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.message || 'Failed to generate permissions')
            }

            return await response.json()
        } catch (error) {
            console.error('Error generating permissions:', error)
            throw error
        }
    }

    const deletePermission = async (permissionId) => {
        try {
            const response = await fetch(`/api/admin/permissions/${permissionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to delete permission')
            }

            return await response.json()
        } catch (error) {
            console.error('Error deleting permission:', error)
            throw error
        }
    }

    return {
        permissions,
        loading,
        fetchPermissions,
        generatePermissions,
        deletePermission
    }
}
