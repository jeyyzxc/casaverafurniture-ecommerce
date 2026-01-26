import axios, { type AxiosInstance, type AxiosResponse, type InternalAxiosRequestConfig } from 'axios'
import { getClientAccessToken, getAdminAccessToken, refreshClientToken, refreshAdminToken, clearAllTokens } from '@/utils/tokenManager'

// API Base URL
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

// Create axios instance
const api: AxiosInstance = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  withCredentials: true, // For Sanctum cookie-based auth and HTTP-only cookies
})

// Track if we're currently handling a 401 to prevent loops
let isHandling401 = false

// Request interceptor to add auth token
api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    // Determine if this is an admin or client route
    const isAdminRoute = config.url?.startsWith('/admin')
    
    // Get appropriate token from memory (not localStorage)
    const token = isAdminRoute ? getAdminAccessToken() : getClientAccessToken()


    if (token) {
      // Ensure headers object exists
      if (!config.headers) {
        config.headers = {} as any
      }
      // Set Authorization header
      config.headers.Authorization = `Bearer ${token}`
    }

    // Add session ID for guest cart
    const sessionId = localStorage.getItem('session_id')
    if (sessionId) {
      config.headers['X-Session-ID'] = sessionId
    }

    return config
  },
  (error) => {
    return Promise.reject(error)
  },
)

// Response interceptor for error handling and automatic token refresh
api.interceptors.response.use(
  (response: AxiosResponse) => response,
  async (error) => {
    const originalRequest = error.config

    if (error.response) {
      const { status, data } = error.response

      // Handle 401 Unauthorized - try to refresh token
      if (status === 401 && !isHandling401 && !originalRequest._retry) {
        // Mark request as retried to prevent infinite loops
        originalRequest._retry = true
        isHandling401 = true

        try {
          // Determine if this is an admin or client route
          const isAdminRoute = originalRequest.url?.startsWith('/admin')
          
          // Attempt to refresh token
          const newToken = isAdminRoute 
            ? await refreshAdminToken() 
            : await refreshClientToken()

          if (newToken) {
            // Update the original request with new token
            originalRequest.headers.Authorization = `Bearer ${newToken}`
            
            // Mark that we're retrying after successful refresh
            // This prevents the original 401 error from being logged
            originalRequest._retryAfterRefresh = true
            
            // Retry the original request
            isHandling401 = false
            
            // Retry the request - if successful, the response will be returned
            try {
              const retryResponse = await api(originalRequest)
              return retryResponse
            } catch (retryError) {
              // Retry failed - propagate the error
              return Promise.reject(retryError)
            }
          } else {
            // Refresh failed - clear all auth state
            clearAllTokens()
            localStorage.removeItem('admin')
            localStorage.removeItem('user')

            // Set error message
            error.message = data?.message || 'Your session has expired. Please log in again.'

            // Redirect to appropriate login page
            const currentPath = window.location.pathname
            if (currentPath.startsWith('/admin') && !currentPath.includes('/login')) {
              setTimeout(() => {
                window.location.href = '/admin/login'
              }, 100)
            } else if (!currentPath.includes('/login') && !currentPath.includes('/register')) {
              // For client routes, redirect to home with login prompt
              setTimeout(() => {
                window.location.href = '/?login=true'
              }, 100)
            }
          }
        } catch (refreshError) {
          // Refresh failed completely
          clearAllTokens()
          localStorage.removeItem('admin')
          localStorage.removeItem('user')
          
          error.message = 'Authentication failed. Please log in again.'
        } finally {
          // Reset flag after a delay
          setTimeout(() => {
            isHandling401 = false
          }, 1000)
        }
      } else if (status === 401 && isHandling401) {
        // Already handling 401, just return the error
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'services/api.ts:123',message:'401 already being handled - rejecting',data:{url:originalRequest.url},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
        // #endregion
        return Promise.reject(error)
      }

      // Handle validation errors (422)
      if (status === 422) {
        if (data.errors) {
          const errors = Object.values(data.errors).flat() as string[]
          error.message = errors.join(', ')
        } else if (data.message) {
          error.message = data.message
        } else {
          error.message = 'Validation failed. Please check your input.'
        }
      }

      // Handle other errors
      if (data?.message && !error.message) {
        error.message = data.message
      }
    }

    return Promise.reject(error)
  },
)

export default api

// Helper function to generate session ID for guests
export function getOrCreateSessionId(): string {
  let sessionId = localStorage.getItem('session_id')
  if (!sessionId) {
    sessionId = 'sess_' + Math.random().toString(36).substring(2) + Date.now().toString(36)
    localStorage.setItem('session_id', sessionId)
  }
  return sessionId
}
