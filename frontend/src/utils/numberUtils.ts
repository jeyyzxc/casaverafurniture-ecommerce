/**
 * Number Utilities
 * Provides utilities for number parsing and formatting
 */

/**
 * Parse a value to number, handling strings with currency symbols
 */
export function parseNumber(value: unknown, defaultValue: number = 0): number {
  if (value === null || value === undefined) {
    return defaultValue
  }

  if (typeof value === 'number') {
    return value
  }

  if (typeof value === 'string') {
    
    const cleaned = value.replace(/[^0-9.-]/g, '')
    const parsed = parseFloat(cleaned)
    
    return isNaN(parsed) ? defaultValue : parsed
  }

  return defaultValue
}

/**
 * Format price with currency symbol
 */
export function formatPrice(price: number, currency: string = '₱'): string {
  return `${currency}${price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

/**
 * Calculate percentage discount
 */
export function calculateDiscountPercentage(originalPrice: number, salePrice: number): number {
  if (originalPrice <= 0) {
    return 0
  }

  return Math.round(((originalPrice - salePrice) / originalPrice) * 100 * 100) / 100
}

/**
 * Safely parse string to number with fallback
 */
export function safeParseFloat(value: string | number | null | undefined, fallback: number = 0): number {
  if (typeof value === 'number') {
    return value
  }

  if (typeof value === 'string') {
    const cleaned = value.replace(/[^0-9.-]/g, '')
    const parsed = parseFloat(cleaned)
    return isNaN(parsed) ? fallback : parsed
  }

  return fallback
}
