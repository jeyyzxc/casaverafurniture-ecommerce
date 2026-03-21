import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { adminAuth } from '@/services/adminApi'
import { useRouter } from 'vue-router'
import { setAdminAccessToken, clearAllTokens, getAdminAccessToken, refreshAdminToken } from '@/utils/tokenManager'

interface Admin {
  id: number
  first_name: string
  last_name: string
  full_name: string
  email: string
  phone: string | null
  avatar: string | null
  role: {
    id: number
    name: string
    slug: string
  } | null
  permissions: string[]
}

export const useAdminAuthStore = defineStore('adminAuth', () => {
  const router = useRouter()

  const admin = ref<Admin | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!admin.value)
  const adminFullName = computed(() => admin.value?.full_name || '')
  const roleName = computed(() => admin.value?.role?.name || '')
  const permissions = computed(() => admin.value?.permissions || [])

  function clearAuthState() {
    admin.value = null
    clearAllTokens()
    localStorage.removeItem('admin')
  }

  async function init() {
    const storedAdmin = localStorage.getItem('admin')

    if (storedAdmin) {
      try {
        const parsedAdmin = JSON.parse(storedAdmin)
        admin.value = parsedAdmin

        if (!getAdminAccessToken()) {
          console.log('[DEBUG_LOG] Admin data found but no token, attempting proactive refresh')
          const token = await refreshAdminToken()

          if (!token) {
            console.warn('[DEBUG_LOG] Proactive refresh failed. Token missing, but keeping local session state for resilience.')
          }
        }
      } catch (e) {
        console.error('Error initializing admin auth:', e)
        clearAuthState()
      }
    }
  }

  function hasPermission(permission: string): boolean {
    if (!admin.value) return false
    if (admin.value.role?.slug === 'super-admin') return true
    return permissions.value.includes(permission)
  }

  function hasAnyPermission(perms: string[]): boolean {
    if (!admin.value) return false
    if (admin.value.role?.slug === 'super-admin') return true
    return perms.some((p) => permissions.value.includes(p))
  }

  async function login(email: string, password: string, remember = false) {
    isLoading.value = true
    error.value = null

    try {
      const response = await adminAuth.login(email, password, remember)

      if (response.data.success) {
        const accessToken = response.data.data.access_token
        if (accessToken) {
          setAdminAccessToken(accessToken)
        }

        admin.value = response.data.data.admin
        localStorage.setItem('admin', JSON.stringify(admin.value))

        return { success: true, message: response.data.message }
      }

      return { success: false, message: response.data.message || 'Login failed' }
    } catch (err: unknown) {
      const errorMessage = err instanceof Error ? err.message : 'Invalid email or password'
      error.value = errorMessage
      return { success: false, message: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  async function logout() {
    try {
      await adminAuth.logout()
    } catch {
    }

    clearAuthState()
    router.push('/admin/login')
  }

  async function fetchAdmin() {
    try {
      const token = getAdminAccessToken()
      if (!token) {
        console.log('[DEBUG_LOG] No admin token in memory, attempting refresh before fetchAdmin')
        const refreshedToken = await refreshAdminToken()
        if (!refreshedToken) {
          console.warn('[DEBUG_LOG] Refresh before fetchAdmin failed, proceeding to trigger interceptor')
        }
      }

      const response = await adminAuth.me()
      if (response.data.success) {
        admin.value = response.data.data.admin
        localStorage.setItem('admin', JSON.stringify(admin.value))
      }
    } catch (err) {
      console.error('[DEBUG_LOG] fetchAdmin failed:', err)
      
      
    }
  }

  async function updateProfile(data: { first_name?: string; last_name?: string; phone?: string }) {
    isLoading.value = true
    error.value = null

    try {
      const response = await adminAuth.updateProfile(data)

      if (response.data.success) {
        if (admin.value) {
          admin.value = { ...admin.value, ...response.data.data.admin }
          localStorage.setItem('admin', JSON.stringify(admin.value))
        }
        return { success: true, message: response.data.message }
      }

      return { success: false, message: response.data.message || 'Update failed' }
    } catch (err: unknown) {
      const errorMessage = err instanceof Error ? err.message : 'Update failed'
      error.value = errorMessage
      return { success: false, message: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  async function changePassword(currentPassword: string, newPassword: string, confirmPassword: string) {
    isLoading.value = true
    error.value = null

    try {
      const response = await adminAuth.changePassword(currentPassword, newPassword, confirmPassword)

      if (response.data.success) {
        return { success: true, message: response.data.message }
      }

      return { success: false, message: response.data.message || 'Password change failed' }
    } catch (err: unknown) {
      const errorMessage = err instanceof Error ? err.message : 'Password change failed'
      error.value = errorMessage
      return { success: false, message: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  init()

  return {
    admin,
    isLoading,
    error,
    isAuthenticated,
    adminFullName,
    roleName,
    permissions,
    hasPermission,
    hasAnyPermission,
    login,
    logout,
    fetchAdmin,
    updateProfile,
    changePassword,
    init,
  }
})
