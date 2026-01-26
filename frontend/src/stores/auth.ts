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
    fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'stores/auth.ts:102',message:'login ENTRY',data:{email},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
    // #endregion
    isLoading.value = true
    error.value = null

    try {
      const response = await authApi.login(email, password, remember)
      // #region agent log
      fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'stores/auth.ts:108',message:'login - AFTER API call',data:{success:response.data.success,hasAccessToken:!!response.data.data?.access_token,hasUser:!!response.data.data?.user},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
      // #endregion

      if (response.data.success) {
        // Store access token in memory (not localStorage)
        const accessToken = response.data.data.access_token
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'stores/auth.ts:113',message:'login - BEFORE setClientAccessToken',data:{hasAccessToken:!!accessToken,accessTokenLength:accessToken?.length||0},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
        // #endregion
        if (accessToken) {
          setClientAccessToken(accessToken)
          // #region agent log
          fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'stores/auth.ts:116',message:'login - AFTER setClientAccessToken',data:{tokenInMemory:!!getClientAccessToken()},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
          // #endregion
        }
        
        user.value = response.data.data.user
        // Store user data in localStorage (but not tokens)
        localStorage.setItem('user', JSON.stringify(user.value))
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'stores/auth.ts:121',message:'login - SUCCESS',data:{userId:user.value?.id,isAuthenticated:!!user.value},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
        // #endregion

        return { success: true, message: response.data.message || 'Login successful' }
      }

      return { success: false, message: response.data.message || 'Login failed' }
    } catch (err: unknown) {
      // #region agent log
      fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'stores/auth.ts:128',message:'login - EXCEPTION',data:{error:(err as Error)?.message||String(err)},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
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
    fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'stores/auth.ts:148',message:'fetchUser ENTRY',data:{hasUser:!!user.value,userId:user.value?.id},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C,D'})}).catch(()=>{});
    // #endregion
    // Always return a proper AuthResult object
    const defaultResult: AuthResult = { success: false, expired: false, noToken: false }

    try {
      // Step 1: Check if we have a token in memory
      let token = getClientAccessToken()
      
      // Step 2: If no token but user data exists, try to refresh the token first
      // This prevents 401 errors when the page is refreshed (tokens are in-memory only)
      if (!token && user.value) {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'stores/auth.ts:157',message:'fetchUser - No token, attempting refresh',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
        // #endregion
        token = await refreshClientToken()
        
        if (!token) {
          // Token refresh failed - session expired
          // #region agent log
          fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'stores/auth.ts:162',message:'fetchUser - Token refresh failed',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
          // #endregion
          clearAuthState()
          return { success: false, expired: true, noToken: true }
        }
        
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'stores/auth.ts:168',message:'fetchUser - Token refresh succeeded',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
        // #endregion
      }
      
      // Step 3: If still no token, we can't make the API call
      if (!token) {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'stores/auth.ts:173',message:'fetchUser - No token available',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
        // #endregion
        return { success: false, expired: false, noToken: true }
      }
      
      // Step 4: Make the API call with the token (interceptor will add it to headers)
      // #region agent log
      fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'stores/auth.ts:179',message:'fetchUser - BEFORE API call',data:{hasToken:!!token},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
      // #endregion
      const response = await authApi.me()
      
      // #region agent log
      fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'stores/auth.ts:160',message:'fetchUser - AFTER API call',data:{responseSuccess:response.data.success,hasUserData:!!response.data.data?.user},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
      // #endregion
      
      if (response.data.success) {
        user.value = response.data.data.user
        localStorage.setItem('user', JSON.stringify(user.value))
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'stores/auth.ts:165',message:'fetchUser - SUCCESS, user updated',data:{userId:user.value?.id,isAuthenticated:!!user.value},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
        // #endregion
        return { success: true, expired: false }
      }
      
      // #region agent log
      fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'stores/auth.ts:169',message:'fetchUser - API returned failure',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
      // #endregion
      return defaultResult
    } catch (err: unknown) {
      // #region agent log
      fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'stores/auth.ts:172',message:'fetchUser - EXCEPTION',data:{error:(err as Error)?.message||String(err),status:(err as any)?.response?.status},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
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
      
      // For other errors, don't clear auth state but return failure
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
