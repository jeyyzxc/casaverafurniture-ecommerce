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
let failedQueue: Array<{
  resolve: (token: string | null) => void
  reject: (error: Error) => void
}> = []

/**
 * Process the queue of failed requests
 */
const processQueue = (error: Error | null, token: string | null = null) => {
  failedQueue.forEach((prom) => {
    if (error) {
      prom.reject(error)
    } else {
      prom.resolve(token)
    }
  })

  failedQueue = []
}

// Request interceptor to add auth token
api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    // Determine if this is an admin or client route
    const isAdminRoute = config.url?.includes('/admin/')

    // Get appropriate token from memory (not localStorage)
    const token = isAdminRoute ? getAdminAccessToken() : getClientAccessToken()


    if (token && token !== 'undefined' && token !== 'null') {
      config.headers.Authorization = `Bearer ${token}`
    } else {
      // Missing token - if this is a protected route, it will likely fail with 401
      // and trigger our robust refresh logic in the response interceptor.
      // We log this for debugging.
      if (isAdminRoute && !config.url?.includes('/auth/login')) {
         console.log(`[DEBUG_LOG] Admin request to ${config.url} has no in-memory token.`)
      }
    }

    // Add session ID for guest cart
    const sessionId = localStorage.getItem('session_id')
    if (sessionId) {
      config.headers['X-Session-ID'] = sessionId
    }

    if (isAdminRoute) {
      console.log(`[DEBUG_LOG] Admin Request to ${config.url}`, {
        hasToken: !!token,
        tokenValue: token ? (token.substring(0, 10) + '...') : 'none'
      });
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
      if (status === 401 && !originalRequest._retry) {
        // Don't attempt to refresh if the failed request was a login or refresh attempt
        if (originalRequest.url?.includes('/auth/login') || originalRequest.url?.includes('/auth/refresh')) {
          return Promise.reject(error)
        }

        // Mark request as retried to prevent infinite loops
        originalRequest._retry = true

        const isAdminRoute = originalRequest.url?.includes('/admin/')

        // If it's a public route that we expect to work without auth, don't try to refresh
        const isPublicRoute = (originalRequest.url?.includes('/home') ||
                             originalRequest.url?.includes('/categories') ||
                             originalRequest.url?.includes('/products') ||
                             originalRequest.url?.includes('/settings')) &&
                             !isAdminRoute

        if (isPublicRoute) {
          return Promise.reject(error)
        }

        // If already handling 401, WAIT for the existing refresh process to complete
        if (isHandling401) {
          console.log(`[DEBUG_LOG] 401 already being handled, queuing request for ${originalRequest.url}`)

          return new Promise((resolve, reject) => {
            failedQueue.push({ resolve, reject })
          })
            .then((token) => {
              originalRequest.headers.Authorization = `Bearer ${token}`
              return api(originalRequest)
            })
            .catch((err) => {
              return Promise.reject(err)
            })
        }

        isHandling401 = true
        console.log(`[DEBUG_LOG] Handling 401 for ${originalRequest.url}`)

        try {
          // Attempt to refresh token
          const newToken = isAdminRoute
            ? await refreshAdminToken()
            : await refreshClientToken()

          if (newToken) {
            console.log(`[DEBUG_LOG] Token refreshed successfully for ${isAdminRoute ? 'admin' : 'client'}`)

            isHandling401 = false
            processQueue(null, newToken)

            // Update the original request with new token
            originalRequest.headers.Authorization = `Bearer ${newToken}`
            originalRequest._retryAfterRefresh = true

            console.log(`[DEBUG_LOG] Retrying original request to ${originalRequest.url}`)
            return api(originalRequest)
          } else {
            console.log(`[DEBUG_LOG] Token refresh FAILED for ${isAdminRoute ? 'admin' : 'client'}`)
            throw new Error('Refresh failed')
          }
        } catch (err: unknown) {
          console.error(`[DEBUG_LOG] Exception during 401 handling:`, err)
          isHandling401 = false
          processQueue(err as Error, null)

          // Refresh failed - clear all auth state
          clearAllTokens()
          localStorage.removeItem('admin')
          localStorage.removeItem('user')

          error.message = data?.message || 'Your session has expired. Please log in again.'

          // Redirect to appropriate login page
          const currentPath = window.location.pathname
          if (currentPath.startsWith('/admin') && !currentPath.includes('/login')) {
            setTimeout(() => {
              window.location.href = '/admin/login'
            }, 100)
          } else if (!currentPath.includes('/login') && !currentPath.includes('/register')) {
            setTimeout(() => {
              window.location.href = '/?login=true'
            }, 100)
          }

          return Promise.reject(error)
        }
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
