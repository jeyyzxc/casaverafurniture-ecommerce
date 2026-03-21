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

  const user = ref<User | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!user.value)
  const userFullName = computed(() => user.value?.full_name || '')

  function init() {
    const storedUser = localStorage.getItem('user')

    if (storedUser) {
      try {
        user.value = JSON.parse(storedUser)
      } catch {
        user.value = null
        localStorage.removeItem('user')
      }
    }
  }

  function clearAuthState() {
    user.value = null
    clearAllTokens()
    localStorage.removeItem('user')
  }

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
        
        const accessToken = response.data.data.access_token
        if (accessToken) {
          setClientAccessToken(accessToken)
        }

        user.value = response.data.data.user
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
    isLoading.value = true
    error.value = null

    try {
      const response = await authApi.login(email, password, remember)

      if (response.data.success) {
        
        const accessToken = response.data.data.access_token
        

        
        if (accessToken) {
          setClientAccessToken(accessToken)
          

          
        }

        user.value = response.data.data.user
        localStorage.setItem('user', JSON.stringify(user.value))
        

        

        return { success: true, message: response.data.message || 'Login successful' }
      }

      return { success: false, message: response.data.message || 'Login failed' }
    } catch (err: unknown) {
      const apiError = err as { response?: { data?: { message?: string } }; message?: string }
      const errorMessage = apiError.response?.data?.message || apiError.message || 'Invalid email or password'
      error.value = errorMessage
      return { success: false, message: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  async function logout(): Promise<void> {
    clearAuthState()

    try {
      await authApi.logout()
    } catch {
    }

    router.push('/')
  }

  async function fetchUser(): Promise<AuthResult> {
    const defaultResult: AuthResult = { success: false, expired: false, noToken: false }

    try {
      let token = getClientAccessToken()

      if (!token && user.value) {
        token = await refreshClientToken()

        if (!token) {
          console.warn('[DEBUG_LOG] Client token refresh failed in fetchUser. Proceeding to trigger interceptor.')
        }
      }

      if (!user.value) {
         return { success: false, expired: false, noToken: true }
      }

      const response = await authApi.me()


      if (response.data.success) {
        user.value = response.data.data.user
        localStorage.setItem('user', JSON.stringify(user.value))
        

        
        return { success: true, expired: false }
      }

      return defaultResult
    } catch (err: unknown) {
      const apiError = err as { response?: { status?: number; data?: { message?: string } }; config?: { _retry?: boolean } }

      if (apiError.response?.status === 401) {
        if (apiError.config?._retry) {
          clearAuthState()
          return { success: false, expired: true }
        }
        return { success: false, expired: true }
      }

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
