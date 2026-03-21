import axios, { type AxiosInstance, type AxiosResponse, type InternalAxiosRequestConfig } from 'axios'
import { getClientAccessToken, getAdminAccessToken, refreshClientToken, refreshAdminToken, clearAllTokens } from '@/utils/tokenManager'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const api: AxiosInstance = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  withCredentials: true,
})

let isHandling401 = false
let failedQueue: Array<{
  resolve: (token: string | null) => void
  reject: (error: Error) => void
}> = []

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

api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const isAdminRoute = config.url?.includes('/admin/')

    const token = isAdminRoute ? getAdminAccessToken() : getClientAccessToken()


    if (token && token !== 'undefined' && token !== 'null') {
      config.headers.Authorization = `Bearer ${token}`
    } else {
      if (isAdminRoute && !config.url?.includes('/auth/login')) {
         console.log(`[DEBUG_LOG] Admin request to ${config.url} has no in-memory token.`)
      }
    }

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

api.interceptors.response.use(
  (response: AxiosResponse) => response,
  async (error) => {
    const originalRequest = error.config

    if (error.response) {
      const { status, data } = error.response

      if (status === 401 && !originalRequest._retry) {
        if (originalRequest.url?.includes('/auth/login') || originalRequest.url?.includes('/auth/refresh')) {
          return Promise.reject(error)
        }

        originalRequest._retry = true

        const isAdminRoute = originalRequest.url?.includes('/admin/')

        const isPublicRoute = (originalRequest.url?.includes('/home') ||
                             originalRequest.url?.includes('/categories') ||
                             originalRequest.url?.includes('/products') ||
                             originalRequest.url?.includes('/settings')) &&
                             !isAdminRoute

        if (isPublicRoute) {
          return Promise.reject(error)
        }

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
          const newToken = isAdminRoute
            ? await refreshAdminToken()
            : await refreshClientToken()

          if (newToken) {
            console.log(`[DEBUG_LOG] Token refreshed successfully for ${isAdminRoute ? 'admin' : 'client'}`)

            isHandling401 = false
            processQueue(null, newToken)

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

          clearAllTokens()
          localStorage.removeItem('admin')
          localStorage.removeItem('user')

          error.message = data?.message || 'Your session has expired. Please log in again.'

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

      if (data?.message && !error.message) {
        error.message = data.message
      }
    }

    return Promise.reject(error)
  },
)

export default api

export function getOrCreateSessionId(): string {
  let sessionId = localStorage.getItem('session_id')
  if (!sessionId) {
    sessionId = 'sess_' + Math.random().toString(36).substring(2) + Date.now().toString(36)
    localStorage.setItem('session_id', sessionId)
  }
  return sessionId
}
