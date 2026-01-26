<template>
  <Teleport to="body">
    <div class="modal-overlay" :class="{ active: isOpen }" @click.self="close">
      <div class="modal-box">
        <button class="modal-close" @click="close">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 6L6 18M6 6l12 12"/>
          </svg>
        </button>

        <div class="modal-header">
          <h2 class="modal-title">Welcome Back</h2>
          <p class="modal-subtitle">Sign in to access your curated collection.</p>
        </div>

        <form @submit.prevent="handleLogin" class="modal-form" novalidate>
          <div class="form-group">
            <div class="input-wrapper" :class="{ focused: emailFocused || form.email, error: errors.email }">
              <input 
                type="email" 
                v-model="form.email" 
                @input="handleEmailInput"
                @keydown="preventSpaceInEmail"
                @focus="emailFocused = true"
                @blur="emailFocused = false; validateEmail()"
                :class="{ 'input-error': errors.email }"
              >
              <label>Email Address</label>
            </div>
            <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
          </div>

          <div class="form-group">
            <div class="input-wrapper" :class="{ focused: passwordFocused || form.password, error: errors.password }">
              <input 
                :type="showPassword ? 'text' : 'password'" 
                v-model="form.password"
                @input="handlePasswordInput"
                @focus="passwordFocused = true"
                @blur="passwordFocused = false; validatePassword()"
              >
              <label>Password</label>
              <button type="button" class="password-toggle" @click="togglePassword">
                <svg v-if="!showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                  <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
              </button>
            </div>
            <span v-if="errors.password" class="error-text">{{ errors.password }}</span>
          </div>

          <div class="form-options">
            <label class="checkbox-wrapper">
              <input type="checkbox" v-model="rememberMe">
              <span class="checkmark"></span>
              <span class="checkbox-label">Remember me</span>
            </label>
            <a href="#" class="forgot-link">Forgot Password?</a>
          </div>

          <div v-if="errors.general" class="error-message">
            {{ errors.general }}
          </div>

          <button type="submit" class="btn-submit" :disabled="isLoading">
            <span v-if="isLoading" class="spinner"></span>
            <span v-else>Sign In</span>
          </button>
        </form>

        <div class="divider">
          <span>OR</span>
        </div>

        <button class="btn-google" @click="handleGoogleLogin" :disabled="isLoading">
          <svg class="google-icon" viewBox="0 0 48 48">
            <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
            <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
            <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
            <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
          </svg>
          Continue with Google
        </button>

        <div class="modal-footer">
          <span>Don't have an account?</span>
          <a href="#" @click.prevent="switchToSignup">Create Account</a>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, reactive, watch, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const props = defineProps<{
  isOpen: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'switch-to-signup'): void
  (e: 'login-success', user: { name: string; email: string }): void
}>()

const authStore = useAuthStore()

const form = reactive({
  email: '',
  password: ''
})

const errors = reactive({
  email: '',
  password: '',
  general: ''
})

const emailFocused = ref(false)
const passwordFocused = ref(false)
const showPassword = ref(false)
const rememberMe = ref(false)
const isLoading = ref(false)

const togglePassword = () => {
  showPassword.value = !showPassword.value
  // Auto-hide after 1 second
  if (showPassword.value) {
    setTimeout(() => {
      showPassword.value = false
    }, 1000)
  }
}

// Strictly prevent spaces in email field
const preventSpaceInEmail = (event: KeyboardEvent) => {
  // Prevent space key from being entered at all in email field
  if (event.key === ' ') {
    event.preventDefault()
  }
}

// Handle email input - remove any spaces that might have been pasted
const handleEmailInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  let value = target.value
  
  // Remove all spaces from email (in case user pastes text with spaces)
  value = value.replace(/\s/g, '')
  
  form.email = value
  errors.email = ''
}

// Password: allow spaces but prevent leading spaces
const handlePasswordInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  let value = target.value
  
  // Remove leading spaces only
  if (value.startsWith(' ')) {
    value = value.trimStart()
  }
  
  // Allow single spaces in password, but prevent consecutive spaces
  value = value.replace(/\s{2,}/g, ' ')
  
  form.password = value
}

const validateEmail = () => {
  errors.email = ''
  
  // Check if field is empty
  if (!form.email.trim()) {
    errors.email = 'Email Address is required'
    return false
  }
  
  // Check if email contains any spaces (strictly not allowed)
  if (/\s/.test(form.email)) {
    errors.email = 'Email Address cannot contain spaces'
    return false
  }
  
  // Check if starts with a letter (not a space or number)
  if (form.email.trim().length > 0 && !/^[a-zA-Z]/.test(form.email.trim())) {
    errors.email = 'Email Address must start with a letter'
    return false
  }
  
  // Email format validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(form.email.trim())) {
    errors.email = 'Please enter a valid email address'
    return false
  }
  
  return true
}

const validatePassword = () => {
  errors.password = ''
  if (!form.password) {
    errors.password = 'Password is required'
    return false
  }
  if (form.password.trim().startsWith(' ')) {
    errors.password = 'Password cannot start with a space'
    return false
  }
  return true
}

const validateForm = () => {
  let isValid = true
  errors.email = ''
  errors.password = ''
  errors.general = ''

  // Validate email
  if (!validateEmail()) {
    isValid = false
  }

  // Validate password
  if (!validatePassword()) {
    isValid = false
  }

  return isValid
}

const handleLogin = async () => {
  // Immediately validate all fields and show ALL errors at once
  errors.general = ''
  let isValid = true
  
  // Validate email - set error immediately if empty
  if (!form.email || !form.email.trim()) {
    errors.email = 'Email Address is required'
    isValid = false
  } else {
    // Field has content, validate it
    if (/\s/.test(form.email)) {
      errors.email = 'Email Address cannot contain spaces'
      isValid = false
    } else if (!/^[a-zA-Z]/.test(form.email.trim())) {
      errors.email = 'Email Address must start with a letter'
      isValid = false
    } else {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!emailRegex.test(form.email.trim())) {
        errors.email = 'Please enter a valid email address'
        isValid = false
      } else {
        errors.email = ''
      }
    }
  }
  
  // Validate password - set error immediately if empty
  if (!form.password || form.password.trim() === '') {
    errors.password = 'Password is required'
    isValid = false
  } else {
    // Field has content, validate it
    if (form.password.trim().startsWith(' ')) {
      errors.password = 'Password cannot start with a space'
      isValid = false
    } else {
      errors.password = ''
    }
  }
  
  // If validation fails, prevent submission (all errors are already set above)
  if (!isValid) {
    return
  }

  isLoading.value = true
  errors.general = ''

  try {
    const result = await authStore.login(form.email, form.password, rememberMe.value)

    if (result.success) {
      emit('login-success', {
        name: authStore.userFullName,
        email: authStore.user?.email || form.email
      })
      close()
    } else {
      // Handle specific error messages
      if (result.message?.toLowerCase().includes('password')) {
        errors.password = result.message || 'Incorrect password'
      } else if (result.message?.toLowerCase().includes('email') || result.message?.toLowerCase().includes('invalid')) {
        errors.email = result.message || 'Invalid email or password'
      } else {
        errors.general = result.message || 'Login failed. Please try again.'
      }
    }
  } catch (error: any) {
    const errorMessage = error?.response?.data?.message || 'An error occurred. Please try again.'
    if (errorMessage?.toLowerCase().includes('password')) {
      errors.password = errorMessage
    } else if (errorMessage?.toLowerCase().includes('email') || errorMessage?.toLowerCase().includes('invalid')) {
      errors.email = errorMessage
    } else {
      errors.general = errorMessage
    }
  } finally {
    isLoading.value = false
  }
}

const close = () => {
  form.email = ''
  form.password = ''
  errors.email = ''
  errors.password = ''
  errors.general = ''
  emit('close')
}

const switchToSignup = () => {
  close()
  emit('switch-to-signup')
}

// Handle Google OAuth login
const handleGoogleLogin = () => {
  const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
  // Redirect to backend Google OAuth endpoint
  window.location.href = `${apiUrl}/auth/google?action=login`
}

// Lock body scroll when modal is open
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    // Lock body scroll
    document.body.style.overflow = 'hidden'
    document.body.style.paddingRight = '0px' // Prevent layout shift
  } else {
    // Unlock body scroll
    document.body.style.overflow = ''
    document.body.style.paddingRight = ''
    // Reset form
    form.email = ''
    form.password = ''
    errors.email = ''
    errors.password = ''
    errors.general = ''
  }
})

// Cleanup on unmount
onUnmounted(() => {
  document.body.style.overflow = ''
  document.body.style.paddingRight = ''
})
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  pointer-events: auto;
}

.modal-overlay.active {
  opacity: 1;
  visibility: visible;
}

.modal-box {
  background: #fff;
  width: 90%;
  max-width: 420px;
  padding: 2.5rem;
  border-radius: 24px;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
  transform: translateY(20px) scale(0.95);
  transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-overlay.active .modal-box {
  transform: translateY(0) scale(1);
}

.modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #f5f5f5;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.modal-close svg {
  width: 18px;
  height: 18px;
  color: #666;
}

.modal-close:hover {
  background: #eee;
  transform: rotate(90deg);
}

.modal-close:hover svg {
  color: #dc3545;
}

.modal-header {
  text-align: center;
  margin-bottom: 2rem;
}

.modal-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.75rem;
  font-weight: 600;
  color: #1a1a1a;
  margin: 0 0 0.5rem;
}

.modal-subtitle {
  color: #888;
  font-size: 0.9rem;
  margin: 0;
}

.form-group {
  margin-bottom: 1.25rem;
}

.input-wrapper {
  position: relative;
}

.input-wrapper input {
  width: 100%;
  height: 56px;
  padding: 1.5rem 1rem 0.5rem;
  border: 2px solid #eee;
  border-radius: 14px;
  font-size: 1rem;
  background: #fafafa;
  transition: all 0.3s ease;
  outline: none;
}

.input-wrapper label {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #999;
  font-size: 1rem;
  pointer-events: none;
  transition: all 0.2s ease;
}

.input-wrapper.focused label,
.input-wrapper input:not(:placeholder-shown) + label {
  top: 0.75rem;
  transform: translateY(0);
  font-size: 0.75rem;
  color: #c9a050;
}

.input-wrapper.focused input {
  border-color: #c9a050;
  background: #fff;
  box-shadow: 0 0 0 4px rgba(201, 160, 80, 0.1);
}

.input-wrapper.error input,
.input-wrapper input.input-error {
  border-color: #dc3545;
  background: #fff5f5;
}

.password-toggle {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.25rem;
}

.password-toggle svg {
  width: 20px;
  height: 20px;
  color: #999;
  transition: color 0.2s;
}

.password-toggle:hover svg {
  color: #c9a050;
}

.error-text {
  display: block;
  color: #dc3545;
  font-size: 0.75rem;
  margin-top: 0.5rem;
  padding-left: 0.5rem;
}

.error-message {
  background: #fee2e2;
  border: 1px solid #fecaca;
  color: #dc2626;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.85rem;
  margin-bottom: 1rem;
  text-align: center;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.checkbox-wrapper {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-size: 0.85rem;
  color: #666;
}

.checkbox-wrapper input {
  display: none;
}

.checkmark {
  width: 18px;
  height: 18px;
  border: 2px solid #ddd;
  border-radius: 4px;
  transition: all 0.2s;
}

.checkbox-wrapper input:checked + .checkmark {
  background: #c9a050;
  border-color: #c9a050;
}

.forgot-link {
  color: #c9a050;
  font-size: 0.85rem;
  text-decoration: none;
  transition: color 0.2s;
}

.forgot-link:hover {
  color: #b8860b;
  text-decoration: underline;
}

.btn-submit {
  width: 100%;
  height: 52px;
  background: linear-gradient(135deg, #c9a050, #b8860b);
  color: #fff;
  border: none;
  border-radius: 26px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(201, 160, 80, 0.4);
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.divider {
  position: relative;
  text-align: center;
  margin: 1.5rem 0;
}

.divider::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  width: 100%;
  height: 1px;
  background: #eee;
}

.divider span {
  position: relative;
  background: #fff;
  padding: 0 1rem;
  color: #999;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.btn-google {
  width: 100%;
  height: 52px;
  background: #fff;
  border: 2px solid #eee;
  border-radius: 26px;
  font-size: 0.95rem;
  font-weight: 600;
  color: #333;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  transition: all 0.3s ease;
}

.btn-google:hover {
  background: #f8f8f8;
  border-color: #ddd;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.google-icon {
  width: 20px;
  height: 20px;
}

.modal-footer {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.9rem;
  color: #888;
}

.modal-footer a {
  color: #c9a050;
  font-weight: 600;
  text-decoration: none;
  margin-left: 0.25rem;
  transition: color 0.2s;
}

.modal-footer a:hover {
  color: #b8860b;
  text-decoration: underline;
}
</style>
