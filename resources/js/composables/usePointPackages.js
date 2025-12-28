import { ref } from 'vue'

export function usePointPackages() {
    const packages = ref({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
    })
    const stats = ref([])
    const loading = ref(false)
    const error = ref(null)

    const fetchPackages = async (filters = {}) => {
        loading.value = true
        error.value = null

        try {
            const params = new URLSearchParams()

            if (filters.search) params.append('search', filters.search)
            if (filters.status) params.append('status', filters.status)
            if (filters.popular) params.append('popular', filters.popular)
            if (filters.sort_by) params.append('sort_by', filters.sort_by)
            if (filters.sort_direction) params.append('sort_direction', filters.sort_direction)
            if (filters.page) params.append('page', filters.page)
            if (filters.per_page) params.append('per_page', filters.per_page)

            const url = `/api/admin/point-packages?${params.toString()}`
            console.log('🔍 Fetching point packages from:', url)

            const response = await fetch(url)
            console.log('📡 Response status:', response.status, response.statusText)

            if (!response.ok) {
                const errorText = await response.text()
                console.error('❌ Response error:', errorText)
                throw new Error('Failed to fetch point packages')
            }

            const data = await response.json()
            console.log('✅ Point packages data received:', data)

            if (data.packages) {
                packages.value = data.packages
            } else {
                packages.value = data
            }
        } catch (err) {
            error.value = err.message
            console.error('❌ Error fetching point packages:', err)
        } finally {
            loading.value = false
            console.log('⏱️ Loading complete. Packages count:', packages.value.data?.length || 0)
        }
    }

    const fetchStats = async () => {
        try {
            const response = await fetch('/api/admin/point-packages/stats')

            if (!response.ok) {
                throw new Error('Failed to fetch stats')
            }

            const data = await response.json()
            stats.value = data.stats
        } catch (err) {
            console.error('Error fetching package stats:', err)
        }
    }

    const createPackage = async (packageData) => {
        try {
            const response = await fetch('/api/admin/point-packages', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(packageData)
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to create package')
            }

            return await response.json()
        } catch (err) {
            console.error('Error creating package:', err)
            throw err
        }
    }

    const updatePackage = async (packageId, packageData) => {
        console.log('🔵 updatePackage called with:', { packageId, packageDataKeys: Object.keys(packageData || {}) })

        if (!packageId) {
            console.error('❌ updatePackage called without packageId!')
            throw new Error('Package ID is required for update')
        }

        const url = `/api/admin/point-packages/${packageId}`
        console.log('📝 Full URL being called:', url)
        console.log('📝 Window location:', window.location.href)

        try {
            console.log('🚀 Making fetch request to:', url, 'with method: POST')
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(packageData)
            })
            console.log('📡 Fetch completed. Response URL:', response.url)

            console.log('📡 Update response:', response.status, response.statusText)

            if (!response.ok) {
                const errorText = await response.text()
                console.error('❌ Update failed:', errorText)
                try {
                    const errorData = JSON.parse(errorText)
                    throw new Error(errorData.error || 'Failed to update package')
                } catch (e) {
                    throw new Error(`Failed to update package: ${response.status}`)
                }
            }

            return await response.json()
        } catch (err) {
            console.error('Error updating package:', err)
            throw err
        }
    }

    const deletePackage = async (packageId) => {
        try {
            const response = await fetch(`/api/admin/point-packages/${packageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to delete package')
            }

            return await response.json()
        } catch (err) {
            console.error('Error deleting package:', err)
            throw err
        }
    }

    const toggleStatus = async (packageId) => {
        try {
            const response = await fetch(`/api/admin/point-packages/${packageId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to toggle status')
            }

            return await response.json()
        } catch (err) {
            console.error('Error toggling status:', err)
            throw err
        }
    }

    const togglePopular = async (packageId) => {
        try {
            const response = await fetch(`/api/admin/point-packages/${packageId}/toggle-popular`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to toggle popular status')
            }

            return await response.json()
        } catch (err) {
            console.error('Error toggling popular status:', err)
            throw err
        }
    }

    const duplicatePackage = async (packageId) => {
        try {
            const response = await fetch(`/api/admin/point-packages/${packageId}/duplicate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to duplicate package')
            }

            return await response.json()
        } catch (err) {
            console.error('Error duplicating package:', err)
            throw err
        }
    }

    const bulkActivate = async () => {
        try {
            const response = await fetch('/api/admin/point-packages/bulk-activate', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to activate packages')
            }

            return await response.json()
        } catch (err) {
            console.error('Error bulk activating:', err)
            throw err
        }
    }

    const bulkDeactivate = async () => {
        try {
            const response = await fetch('/api/admin/point-packages/bulk-deactivate', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })

            if (!response.ok) {
                const errorData = await response.json()
                throw new Error(errorData.error || 'Failed to deactivate packages')
            }

            return await response.json()
        } catch (err) {
            console.error('Error bulk deactivating:', err)
            throw err
        }
    }

    return {
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
    }
}
