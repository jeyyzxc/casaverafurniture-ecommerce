/**
 * Validation utility functions
 */

export interface ValidationResult {
  isValid: boolean
  error: string
}

/**
 * Validate email address
 */
export const validateEmail = (email: string): ValidationResult => {
  if (!email || !email.trim()) {
    return { isValid: false, error: 'Email Address is required' }
  }

  if (/\s/.test(email)) {
    return { isValid: false, error: 'Email Address cannot contain spaces' }
  }

  if (!/^[a-zA-Z]/.test(email.trim())) {
    return { isValid: false, error: 'Email Address must start with a letter' }
  }

  const emailRegex = /^[^@\s]+@[^@\s]+\.[^@\s]+$/
  if (!emailRegex.test(email.trim())) {
    return { isValid: false, error: 'Please enter a valid email address' }
  }

  return { isValid: true, error: '' }
}

/**
 * Validate name field
 */
export const validateName = (name: string, fieldName: string = 'Name', minLength: number = 2): ValidationResult => {
  if (!name || !name.trim()) {
    return { isValid: false, error: `${fieldName} is required` }
  }

  if (name.trim().length < minLength) {
    return { isValid: false, error: `${fieldName} must be at least ${minLength} characters` }
  }

  if (name.trim().startsWith(' ')) {
    return { isValid: false, error: `${fieldName} cannot start with a space` }
  }

  if (!/^[a-zA-Z]/.test(name.trim())) {
    return { isValid: false, error: `${fieldName} must start with a letter` }
  }

  if (/\s{2,}/.test(name)) {
    return { isValid: false, error: `${fieldName} cannot contain consecutive spaces` }
  }

  return { isValid: true, error: '' }
}

/**
 * Validate password
 */
export const validatePassword = (password: string, minLength: number = 8): ValidationResult => {
  if (!password || password.trim() === '') {
    return { isValid: false, error: 'Password is required' }
  }

  if (password.length < minLength) {
    return { isValid: false, error: `Password must be at least ${minLength} characters` }
  }

  if (password.trim().startsWith(' ')) {
    return { isValid: false, error: 'Password cannot start with a space' }
  }

  return { isValid: true, error: '' }
}

/**
 * Validate phone number (Philippine format)
 */
export const validatePhone = (phone: string): ValidationResult => {
  if (!phone || !phone.trim()) {
    return { isValid: false, error: 'Phone number is required' }
  }

  // Philippine phone number format: +63XXXXXXXXXX or 09XXXXXXXXX
  const phoneRegex = /^(\+63|0)?[9]\d{9}$/
  const cleanedPhone = phone.replace(/\s|-/g, '')

  if (!phoneRegex.test(cleanedPhone)) {
    return { isValid: false, error: 'Please enter a valid Philippine phone number' }
  }

  return { isValid: true, error: '' }
}

/**
 * Validate required field
 */
export const validateRequired = (value: string, fieldName: string): ValidationResult => {
  if (!value || !value.trim()) {
    return { isValid: false, error: `${fieldName} is required` }
  }
  return { isValid: true, error: '' }
}

/**
 * Validate message/textarea
 */
export const validateMessage = (message: string, minLength: number = 8): ValidationResult => {
  if (!message || !message.trim()) {
    return { isValid: false, error: 'Message is required' }
  }

  if (message.trim().length < minLength) {
    return { isValid: false, error: `Message must be at least ${minLength} characters` }
  }

  if (!/^[a-zA-Z]/.test(message.trim())) {
    return { isValid: false, error: 'Message must start with a letter' }
  }

  return { isValid: true, error: '' }
}

/**
 * Validate password confirmation
 */
export const validatePasswordConfirmation = (password: string, confirmPassword: string): ValidationResult => {
  if (!confirmPassword || confirmPassword.trim() === '') {
    return { isValid: false, error: 'Please confirm your password' }
  }

  if (password !== confirmPassword) {
    return { isValid: false, error: 'Passwords do not match' }
  }

  if (confirmPassword.trim().startsWith(' ')) {
    return { isValid: false, error: 'Password cannot start with a space' }
  }

  return { isValid: true, error: '' }
}
