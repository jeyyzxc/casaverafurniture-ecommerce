/**
 * Token Manager
 * Manages access tokens in memory (not localStorage for security)
 * Handles automatic token refresh using refresh tokens from HTTP-only cookies
 */

// In-memory storage for access tokens (not persisted)
let clientAccessToken: string | null = null
let adminAccessToken: string | null = null

// Track refresh attempts to prevent loops (separate for client and admin)
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
}

/**
 * Set admin access token
 */
export function setAdminAccessToken(token: string | null): void {
  adminAccessToken = token
}

/**
 * Clear all tokens
 */
export function clearAllTokens(): void {
  clientAccessToken = null
  adminAccessToken = null
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
      const response = await fetch(`${import.meta.env.VITE_API_URL || 'http://localhost:8000/api'}/auth/refresh`, {
        method: 'POST',
        credentials: 'include', // Include HTTP-only cookies
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
      })

      if (response.ok) {
        const data = await response.json()
        if (data.success && data.data?.access_token) {
          clientAccessToken = data.data.access_token
          return clientAccessToken
        }
      }

      // Refresh failed - clear client token only
      clientAccessToken = null
      return null
    } catch (error) {
      console.error('Client token refresh failed:', error)
      clientAccessToken = null
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
      const response = await fetch(`${import.meta.env.VITE_API_URL || 'http://localhost:8000/api'}/admin/auth/refresh`, {
        method: 'POST',
        credentials: 'include', // Include HTTP-only cookies
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
      })

      if (response.ok) {
        const data = await response.json()
        if (data.success && data.data?.access_token) {
          adminAccessToken = data.data.access_token
          return adminAccessToken
        }
      }

      // Refresh failed - clear admin token only
      adminAccessToken = null
      return null
    } catch (error) {
      console.error('Admin token refresh failed:', error)
      adminAccessToken = null
      return null
    } finally {
      isRefreshingAdmin = false
      adminRefreshPromise = null
    }
  })()

  return adminRefreshPromise
}
