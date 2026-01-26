/**
 * Composable for form validation
 */
import { ref, Ref } from 'vue'
import {
  validateEmail,
  validateName,
  validatePassword,
  validatePasswordConfirmation,
  validatePhone,
  validateRequired,
  validateMessage,
  ValidationResult,
} from '@/utils/validators'

export interface FormErrors {
  [key: string]: string
}

export function useFormValidation() {
  const errors = ref<FormErrors>({})

  /**
   * Validate a field and set error
   */
  const validateField = (fieldName: string, value: string, validator: (value: string) => ValidationResult) => {
    const result = validator(value)
    if (result.isValid) {
      errors.value[fieldName] = ''
    } else {
      errors.value[fieldName] = result.error
    }
    return result.isValid
  }

  /**
   * Validate email field
   */
  const validateEmailField = (fieldName: string, value: string): boolean => {
    return validateField(fieldName, value, validateEmail)
  }

  /**
   * Validate name field
   */
  const validateNameField = (fieldName: string, value: string, fieldLabel: string = 'Name', minLength: number = 2): boolean => {
    return validateField(fieldName, value, (val) => validateName(val, fieldLabel, minLength))
  }

  /**
   * Validate password field
   */
  const validatePasswordField = (fieldName: string, value: string, minLength: number = 8): boolean => {
    return validateField(fieldName, value, (val) => validatePassword(val, minLength))
  }

  /**
   * Validate password confirmation
   */
  const validatePasswordConfirmationField = (fieldName: string, password: string, confirmPassword: string): boolean => {
    const result = validatePasswordConfirmation(password, confirmPassword)
    if (result.isValid) {
      errors.value[fieldName] = ''
    } else {
      errors.value[fieldName] = result.error
    }
    return result.isValid
  }

  /**
   * Validate phone field
   */
  const validatePhoneField = (fieldName: string, value: string): boolean => {
    return validateField(fieldName, value, validatePhone)
  }

  /**
   * Validate required field
   */
  const validateRequiredField = (fieldName: string, value: string, fieldLabel: string): boolean => {
    return validateField(fieldName, value, (val) => validateRequired(val, fieldLabel))
  }

  /**
   * Validate message field
   */
  const validateMessageField = (fieldName: string, value: string, minLength: number = 8): boolean => {
    return validateField(fieldName, value, (val) => validateMessage(val, minLength))
  }

  /**
   * Clear all errors
   */
  const clearErrors = () => {
    errors.value = {}
  }

  /**
   * Clear specific error
   */
  const clearError = (fieldName: string) => {
    errors.value[fieldName] = ''
  }

  /**
   * Check if form is valid
   */
  const isValid = (): boolean => {
    return Object.values(errors.value).every(error => !error)
  }

  /**
   * Get all errors
   */
  const getErrors = (): FormErrors => {
    return { ...errors.value }
  }

  return {
    errors: errors as Ref<FormErrors>,
    validateField,
    validateEmailField,
    validateNameField,
    validatePasswordField,
    validatePasswordConfirmationField,
    validatePhoneField,
    validateRequiredField,
    validateMessageField,
    clearErrors,
    clearError,
    isValid,
    getErrors,
  }
}
