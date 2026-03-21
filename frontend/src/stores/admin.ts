import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useRouter } from 'vue-router'

export interface AdminUser {
  id: number
  name: string
  email: string
  role: 'super_admin' | 'admin' | 'staff'
  permissions?: string[]
}

export interface AdminPermission {
  module: string
  actions: string[]
}

export const useAdminStore = defineStore('admin', () => {
  const router = useRouter()

  const currentAdmin = ref<AdminUser | null>(null)
  const isAuthenticated = ref(false)
  const sessionTimeout = ref<number | null>(null)
  const activityLogs = ref<any[]>([])

  const isSuperAdmin = computed(() => currentAdmin.value?.role === 'super_admin')
  const isAdmin = computed(() => currentAdmin.value?.role === 'admin' || isSuperAdmin.value)
  const isStaff = computed(() => currentAdmin.value?.role === 'staff' || isAdmin.value)

  const hasPermission = (permission: string): boolean => {
    if (!currentAdmin.value) return false
    if (isSuperAdmin.value) return true
    return currentAdmin.value.permissions?.includes(permission) ?? false
  }

  const login = async (email: string, password: string): Promise<boolean> => {
    try {
      if (email && password) {
        currentAdmin.value = {
          id: 1,
          name: 'Admin User',
          email: email,
          role: 'super_admin',
          permissions: ['*'] 
        }
        isAuthenticated.value = true
        sessionTimeout.value = Date.now() + (8 * 60 * 60 * 1000) 
        localStorage.setItem('admin_authenticated', 'true')
        localStorage.setItem('admin_user', JSON.stringify(currentAdmin.value))
        logActivity('login', 'Admin logged in')
        return true
      }
      return false
    } catch (error) {
      console.error('Login error:', error)
      return false
    }
  }

  const logout = () => {
    logActivity('logout', 'Admin logged out')
    currentAdmin.value = null
    isAuthenticated.value = false
    sessionTimeout.value = null
    localStorage.removeItem('admin_authenticated')
    localStorage.removeItem('admin_user')
    router.push('/admin/login')
  }

  const checkSession = () => {
    const saved = localStorage.getItem('admin_user')
    if (saved) {
      try {
        currentAdmin.value = JSON.parse(saved)
        isAuthenticated.value = true
        return true
      } catch {
        logout()
        return false
      }
    }
    return false
  }

  const logActivity = (action: string, description: string, details?: any) => {
    activityLogs.value.unshift({
      id: Date.now(),
      adminId: currentAdmin.value?.id,
      adminName: currentAdmin.value?.name,
      action,
      description,
      details,
      timestamp: new Date().toISOString(),
      ip: '127.0.0.1'
    })
    if (activityLogs.value.length > 1000) {
      activityLogs.value = activityLogs.value.slice(0, 1000)
    }
  }

  const init = () => {
    checkSession()
  }

  return {
    currentAdmin,
    isAuthenticated,
    isSuperAdmin,
    isAdmin,
    isStaff,
    hasPermission,
    login,
    logout,
    checkSession,
    logActivity,
    init,
    activityLogs
  }
})
