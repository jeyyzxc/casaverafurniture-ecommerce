import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { adminAuth } from '@/services/adminApi'
import { useRouter } from 'vue-router'
import { setAdminAccessToken, clearAllTokens } from '@/utils/tokenManager'

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

  // State
  const admin = ref<Admin | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Computed
  // Access token is stored in memory via tokenManager, not in this store
  const isAuthenticated = computed(() => !!admin.value)
  const adminFullName = computed(() => admin.value?.full_name || '')
  const roleName = computed(() => admin.value?.role?.name || '')
  const permissions = computed(() => admin.value?.permissions || [])

  // Initialize from localStorage (admin data only, not tokens)
  function init() {
    const storedAdmin = localStorage.getItem('admin')

    if (storedAdmin) {
      try {
        admin.value = JSON.parse(storedAdmin)
        // Token refresh will happen automatically on first API call if needed
      } catch {
        admin.value = null
        localStorage.removeItem('admin')
      }
    }
  }

  // Clear auth state (internal helper)
  function clearAuthState() {
    admin.value = null
    clearAllTokens() // Clear tokens from memory
    localStorage.removeItem('admin')
  }

  // Check if admin has a specific permission
  function hasPermission(permission: string): boolean {
    if (!admin.value) return false
    if (admin.value.role?.slug === 'super-admin') return true
    return permissions.value.includes(permission)
  }

  // Check if admin has any of the specified permissions
  function hasAnyPermission(perms: string[]): boolean {
    if (!admin.value) return false
    if (admin.value.role?.slug === 'super-admin') return true
    return perms.some((p) => permissions.value.includes(p))
  }

  // Actions
  async function login(email: string, password: string, remember = false) {
    isLoading.value = true
    error.value = null

    try {
      const response = await adminAuth.login(email, password, remember)

      if (response.data.success) {
        // Store access token in memory (not localStorage)
        const accessToken = response.data.data.access_token
        if (accessToken) {
          setAdminAccessToken(accessToken)
        }
        
        admin.value = response.data.data.admin
        // Store admin data in localStorage (but not tokens)
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
      // Ignore errors on logout
    }

    clearAuthState()
    router.push('/admin/login')
  }

  async function fetchAdmin() {
    try {
      // Make the request - the interceptor will handle token refresh automatically
      // Access token is managed by tokenManager in memory
      const response = await adminAuth.me()
      if (response.data.success) {
        admin.value = response.data.data.admin
        // Store admin data in localStorage (but not tokens)
        localStorage.setItem('admin', JSON.stringify(admin.value))
      }
    } catch {
      // Token might be invalid - clear auth state
      clearAuthState()
      router.push('/admin/login')
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

  // Initialize on store creation
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
