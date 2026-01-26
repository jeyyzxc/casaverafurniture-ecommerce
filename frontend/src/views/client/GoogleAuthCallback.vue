<template>
  <div class="google-auth-callback">
    <div class="callback-container">
      <div v-if="isProcessing" class="processing">
        <div class="spinner"></div>
        <p>Completing authentication...</p>
      </div>
      <div v-else-if="error" class="error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <h3>Authentication Failed</h3>
        <p>{{ error }}</p>
        <router-link to="/" class="btn-home">Go to Home</router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { setClientAccessToken } from '@/utils/tokenManager'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const isProcessing = ref(true)
const error = ref<string | null>(null)

onMounted(async () => {
  try {
    // Get token and action from query parameters
    const token = route.query.token as string
    const action = route.query.action as string || 'login'
    
    if (!token) {
      error.value = 'Authentication token not found. Please try again.'
      isProcessing.value = false
      return
    }
    
    // Store the access token in memory
    setClientAccessToken(token)
    
    // Fetch user data to complete authentication
    const result = await authStore.fetchUser()
    
    if (result.success && authStore.isAuthenticated) {
      // Authentication successful - redirect based on action
      const redirectPath = sessionStorage.getItem('redirectAfterLogin') || '/'
      sessionStorage.removeItem('redirectAfterLogin')
      
      // Close any open modals by navigating to home first, then to destination
      if (redirectPath === '/') {
        router.push('/').then(() => {
          // Emit login success event if needed
          window.dispatchEvent(new CustomEvent('google-auth-success', {
            detail: { user: authStore.user }
          }))
        })
      } else {
        router.push(redirectPath).then(() => {
          window.dispatchEvent(new CustomEvent('google-auth-success', {
            detail: { user: authStore.user }
          }))
        })
      }
    } else {
      error.value = 'Failed to authenticate. Please try again.'
      isProcessing.value = false
    }
  } catch (err) {
    console.error('Google auth callback error:', err)
    error.value = 'An error occurred during authentication. Please try again.'
    isProcessing.value = false
  }
})
</script>

<style scoped>
.google-auth-callback {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: #f8f8f8;
}

.callback-container {
  background: white;
  padding: 3rem;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
  text-align: center;
  max-width: 400px;
}

.processing {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #eee;
  border-top-color: #c9a050;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.processing p {
  color: #666;
  margin: 0;
}

.error {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.error svg {
  width: 48px;
  height: 48px;
  color: #dc3545;
}

.error h3 {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  color: #1a1a1a;
  margin: 0;
}

.error p {
  color: #666;
  margin: 0;
}

.btn-home {
  display: inline-block;
  margin-top: 1rem;
  padding: 0.75rem 2rem;
  background: #c9a050;
  color: #1a1a1a;
  text-decoration: none;
  border-radius: 50px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-home:hover {
  background: #b8860b;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(201, 160, 80, 0.3);
}
</style>
