/**
 * Token Manager
 * Manages access tokens with localStorage persistence for better UX on reload
 * Handles automatic token refresh using refresh tokens from HTTP-only cookies
 */
import api from '@/services/api'


let clientAccessToken: string | null = localStorage.getItem('client_access_token')
let adminAccessToken: string | null = localStorage.getItem('admin_access_token')


let isRefreshingClient = false
let isRefreshingAdmin = false
let clientRefreshPromise: Promise<string | null> | null = null
let adminRefreshPromise: Promise<string | null> | null = null

/**
 * Get client access token
 */
export function getClientAccessToken(): string | null {
  return clientAccessToken
}

/**
 * Get admin access token
 */
export function getAdminAccessToken(): string | null {
  return adminAccessToken
}

/**
 * Set client access token
 */
export function setClientAccessToken(token: string | null): void {
  clientAccessToken = token
  if (token) {
    localStorage.setItem('client_access_token', token)
  } else {
    localStorage.removeItem('client_access_token')
  }
}

/**
 * Set admin access token
 */
export function setAdminAccessToken(token: string | null): void {
  adminAccessToken = token
  if (token) {
    localStorage.setItem('admin_access_token', token)
  } else {
    localStorage.removeItem('admin_access_token')
  }
}

/**
 * Clear all tokens
 */
export function clearAllTokens(): void {
  clientAccessToken = null
  adminAccessToken = null
  localStorage.removeItem('client_access_token')
  localStorage.removeItem('admin_access_token')
}

/**
 * Refresh client access token
 */
export async function refreshClientToken(): Promise<string | null> {
  if (isRefreshingClient && clientRefreshPromise) {
    return clientRefreshPromise
  }

  isRefreshingClient = true
  clientRefreshPromise = (async () => {
    try {
      const response = await api.post('/auth/refresh')

      if (response.data.success && response.data.data?.access_token) {
        const newToken = response.data.data.access_token
        setClientAccessToken(newToken)
        console.log('[DEBUG_LOG] Client token successfully refreshed.')
        return newToken
      }

      console.warn('[DEBUG_LOG] Client token refresh failed.')
      setClientAccessToken(null)
      return null
    } catch (error) {
      console.error('Client token refresh failed:', error)
      setClientAccessToken(null)
      return null
    } finally {
      isRefreshingClient = false
      clientRefreshPromise = null
    }
  })()

  return clientRefreshPromise
}

/**
 * Refresh admin access token
 */
export async function refreshAdminToken(): Promise<string | null> {
  if (isRefreshingAdmin && adminRefreshPromise) {
    return adminRefreshPromise
  }

  isRefreshingAdmin = true
  adminRefreshPromise = (async () => {
    try {
      const response = await api.post('/admin/auth/refresh')

      if (response.data.success && response.data.data?.access_token) {
        const newToken = response.data.data.access_token
        setAdminAccessToken(newToken)
        console.log('[DEBUG_LOG] Admin token successfully refreshed.')
        return newToken
      }

      console.warn('[DEBUG_LOG] Admin token refresh failed.')
      setAdminAccessToken(null)
      return null
    } catch (error) {
      console.error('Admin token refresh failed:', error)
      setAdminAccessToken(null)
      return null
    } finally {
      isRefreshingAdmin = false
      adminRefreshPromise = null
    }
  })()

  return adminRefreshPromise
}
