import './assets/main.css'
import '@fortawesome/fontawesome-free/css/all.min.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/auth'
import { useAdminAuthStore } from './stores/adminAuth'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

const authStore = useAuthStore()
const adminAuthStore = useAdminAuthStore()

const isProtectedRoute = router.currentRoute.value.meta?.requiresAuth
const isAdminPath = window.location.pathname.includes('/admin')

if (!isProtectedRoute) {
  if (isAdminPath) {
    console.log('[DEBUG_LOG] Initializing Admin Auth in main.ts');
    adminAuthStore.init().then(() => {
      if (adminAuthStore.isAuthenticated) {
        adminAuthStore.fetchAdmin()
      }
    })
  } else {
    console.log('[DEBUG_LOG] Initializing Client Auth in main.ts');
    authStore.fetchUser()
  }
}

app.mount('#app')
