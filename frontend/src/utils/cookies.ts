/**
 * Cookie utility functions
 * Handles secure cookie operations for authentication tokens
 */

/**
 * Get a cookie value by name
 */
export function getCookie(name: string): string | null {
  const value = `; ${document.cookie}`
  const parts = value.split(`; ${name}=`)
  if (parts.length === 2) {
    return parts.pop()?.split(';').shift() || null
  }
  return null
}

/**
 * Set a cookie
 */
export function setCookie(
  name: string,
  value: string,
  days: number = 30,
  options: {
    secure?: boolean
    sameSite?: 'Strict' | 'Lax' | 'None'
    path?: string
  } = {}
): void {
  const expires = new Date()
  expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000)

  let cookieString = `${name}=${value}; expires=${expires.toUTCString()}; path=${options.path || '/'}`

  if (options.secure) {
    cookieString += '; Secure'
  }

  if (options.sameSite) {
    cookieString += `; SameSite=${options.sameSite}`
  }

  document.cookie = cookieString
}

/**
 * Delete a cookie
 */
export function deleteCookie(name: string, path: string = '/'): void {
  document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=${path}`
}
