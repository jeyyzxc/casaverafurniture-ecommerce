import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { auth as authApi } from '@/services/clientApi'
import { useRouter } from 'vue-router'
import { setClientAccessToken, clearAllTokens, getClientAccessToken, refreshClientToken } from '@/utils/tokenManager'

interface User {
  id: number
  first_name: string
  last_name: string
  full_name: string
  email: string
  phone: string | null
  avatar: string | null
  total_spent: number
  order_count: number
}

interface AuthResult {
  success: boolean
  expired?: boolean
  noToken?: boolean
  message?: string
}

export const useAuthStore = defineStore('auth', () => {
  const router = useRouter()

  // State
  const user = ref<User | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Computed
  // Access token is stored in memory via tokenManager, not in this store
  const isAuthenticated = computed(() => !!user.value)
  const userFullName = computed(() => user.value?.full_name || '')

  // Initialize from localStorage (user data only, not tokens)
  function init() {
    const storedUser = localStorage.getItem('user')

    if (storedUser) {
      try {
        user.value = JSON.parse(storedUser)
        // Try to refresh token on init if user data exists
        // Token refresh will happen automatically on first API call if needed
      } catch {
        user.value = null
        localStorage.removeItem('user')
      }
    }
  }

  // Clear auth state (internal helper)
  function clearAuthState() {
    user.value = null
    clearAllTokens() // Clear tokens from memory
    localStorage.removeItem('user')
  }

  // Actions
  async function register(data: {
    first_name: string
    last_name: string
    email: string
    password: string
    password_confirmation: string
    phone?: string
  }): Promise<{ success: boolean; message: string }> {
    isLoading.value = true
    error.value = null

    try {
      const response = await authApi.register(data)

      if (response.data.success) {
        // Store access token in memory (not localStorage)
        const accessToken = response.data.data.access_token
        if (accessToken) {
          setClientAccessToken(accessToken)
        }

        user.value = response.data.data.user
        // Store user data in localStorage (but not tokens)
        localStorage.setItem('user', JSON.stringify(user.value))

        return { success: true, message: response.data.message || 'Registration successful' }
      }

      return { success: false, message: response.data.message || 'Registration failed' }
    } catch (err: unknown) {
      const apiError = err as { response?: { data?: { message?: string } }; message?: string }
      const errorMessage = apiError.response?.data?.message || apiError.message || 'Registration failed'
      error.value = errorMessage
      return { success: false, message: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  async function login(email: string, password: string, remember = false): Promise<{ success: boolean; message: string }> {
    // #region agent log

    // #endregion
    isLoading.value = true
    error.value = null

    try {
      const response = await authApi.login(email, password, remember)
      // #region agent log

      // #endregion

      if (response.data.success) {
        // Store access token in memory (not localStorage)
        const accessToken = response.data.data.access_token
        // #region agent log

        // #endregion
        if (accessToken) {
          setClientAccessToken(accessToken)
          // #region agent log

          // #endregion
        }

        user.value = response.data.data.user
        // Store user data in localStorage (but not tokens)
        localStorage.setItem('user', JSON.stringify(user.value))
        // #region agent log

        // #endregion

        return { success: true, message: response.data.message || 'Login successful' }
      }

      return { success: false, message: response.data.message || 'Login failed' }
    } catch (err: unknown) {
      // #region agent log

      // #endregion
      const apiError = err as { response?: { data?: { message?: string } }; message?: string }
      const errorMessage = apiError.response?.data?.message || apiError.message || 'Invalid email or password'
      error.value = errorMessage
      return { success: false, message: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  async function logout(): Promise<void> {
    // Clear local state first (always succeeds)
    clearAuthState()

    // Try to call logout API, but don't fail if it errors (token might already be invalid)
    try {
      await authApi.logout()
    } catch {
      // Ignore errors on logout - token might already be invalid
    }

    router.push('/')
  }

  async function fetchUser(): Promise<AuthResult> {
    // #region agent log

    // #endregion
    // Always return a proper AuthResult object
    const defaultResult: AuthResult = { success: false, expired: false, noToken: false }

    try {
      // Step 1: Check if we have a token in memory
      let token = getClientAccessToken()

      // Step 2: If no token but user data exists, try to refresh the token first
      // This prevents 401 errors when the page is refreshed (tokens are in-memory only)
      if (!token && user.value) {
        token = await refreshClientToken()

        if (!token) {
          // Token refresh failed.
          // CHANGE: Don't clear auth state immediately.
          // Let the API call proceed so the interceptor can handle the 401 standard flow.
          // This prevents logging out due to network errors.
          console.warn('[DEBUG_LOG] Client token refresh failed in fetchUser. Proceeding to trigger interceptor.')
        }

        // #region agent log

        // #endregion
      }

      // Step 3: If still no token, we can't make the API call
      // Wait, if we want to trigger the interceptor, we SHOULD make the call even without a token?
      // The interceptor adds the token if it exists. If not, it sends without token.
      // Backend returns 401. Interceptor catches 401 and tries to refresh.
      // So we should proceed.

      // However, if we explicitly know we have no token and refresh failed, maybe we should stop?
      // But refresh might have failed due to network.
      // If we proceed, the API call will fail (network) or 401.

      // Let's proceed to Step 4 regardless of token presence if we have user data.
      // If we don't have user data, we are not logged in anyway.

      if (!user.value) {
         return { success: false, expired: false, noToken: true }
      }

      // Step 4: Make the API call with the token (interceptor will add it to headers)
      // #region agent log

      // #endregion
      const response = await authApi.me()

      // #region agent log

      // #endregion

      if (response.data.success) {
        user.value = response.data.data.user
        localStorage.setItem('user', JSON.stringify(user.value))
        // #region agent log

        // #endregion
        return { success: true, expired: false }
      }

      // #region agent log

      // #endregion
      return defaultResult
    } catch (err: unknown) {
      // #region agent log

      // #endregion
      // Token might be invalid - clear auth state without calling logout API
      const apiError = err as { response?: { status?: number; data?: { message?: string } }; config?: { _retry?: boolean } }

      // If this is a 401 that was retried (token refresh attempted), it means refresh failed
      // Otherwise, if it's a 401 without retry, the interceptor should have handled it
      // Only clear auth state if it's a 401 that couldn't be resolved
      if (apiError.response?.status === 401) {
        // Check if token refresh was attempted but failed
        // If _retry is true, it means we tried to refresh but it failed
        if (apiError.config?._retry) {
          // Token refresh was attempted but failed - session expired
          clearAuthState()
          return { success: false, expired: true }
        }
        // If _retry is not set, the interceptor should have handled it
        // This might be a race condition or the interceptor didn't catch it
        // Return expired to indicate authentication failed
        return { success: false, expired: true }
      }

      // For other errors (e.g. network), don't clear auth state but return failure
      return defaultResult
    }
  }

  async function updateProfile(data: Record<string, unknown>): Promise<{ success: boolean; message: string }> {
    isLoading.value = true
    error.value = null

    try {
      const response = await authApi.updateProfile(data)

      if (response.data.success) {
        user.value = response.data.data.user
        localStorage.setItem('user', JSON.stringify(user.value))
        return { success: true, message: response.data.message || 'Profile updated successfully' }
      }

      return { success: false, message: response.data.message || 'Update failed' }
    } catch (err: unknown) {
      const apiError = err as { response?: { data?: { message?: string } }; message?: string }
      const errorMessage = apiError.response?.data?.message || apiError.message || 'Update failed'
      error.value = errorMessage
      return { success: false, message: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  async function changePassword(currentPassword: string, newPassword: string, confirmPassword: string): Promise<{ success: boolean; message: string }> {
    isLoading.value = true
    error.value = null

    try {
      const response = await authApi.changePassword(currentPassword, newPassword, confirmPassword)

      if (response.data.success) {
        return { success: true, message: response.data.message || 'Password changed successfully' }
      }

      return { success: false, message: response.data.message || 'Password change failed' }
    } catch (err: unknown) {
      const apiError = err as { response?: { data?: { message?: string } }; message?: string }
      const errorMessage = apiError.response?.data?.message || apiError.message || 'Password change failed'
      error.value = errorMessage
      return { success: false, message: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  // Initialize on store creation
  init()

  return {
    user,
    isLoading,
    error,
    isAuthenticated,
    userFullName,
    register,
    login,
    logout,
    fetchUser,
    updateProfile,
    changePassword,
    init,
  }
})
