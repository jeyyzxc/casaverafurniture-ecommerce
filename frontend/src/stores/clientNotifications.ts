import { ref, computed, onMounted, onUnmounted } from 'vue'
import { defineStore } from 'pinia'
import { notifications as notificationsApi } from '@/services/clientApi'
import { useRealtimeOrders } from '@/composables/useRealtimeOrders'

export interface ClientNotification {
  id: string
  type: 'order' | 'product' | 'payment' | 'promotion' | 'system'
  title: string
  message: string
  read: boolean
  is_read?: boolean
  timestamp: string
  created_at?: string
  link?: string
  action_url?: string
  icon?: string
  priority?: 'low' | 'normal' | 'high' | 'urgent'
  related_type?: string
  related_id?: number
}

export const useClientNotificationStore = defineStore('clientNotifications', () => {
  
  const notifications = ref<ClientNotification[]>([])
  const isLoading = ref(false)
  const lastFetched = ref<Date | null>(null)
  let pollingInterval: number | null = null

  
  const unreadCount = computed(() => 
    notifications.value.filter(n => !n.read && !n.is_read).length
  )

  const unreadNotifications = computed(() =>
    notifications.value.filter(n => !n.read && !n.is_read)
  )

  const recentNotifications = computed(() =>
    [...notifications.value]
      .sort((a, b) => {
        const timeA = new Date(a.timestamp || a.created_at || 0).getTime()
        const timeB = new Date(b.timestamp || b.created_at || 0).getTime()
        return timeB - timeA
      })
      .slice(0, 10)
  )

  
  const loadNotifications = async (force = false) => {
    if (isLoading.value && !force) return

    isLoading.value = true
    try {
      const response = await notificationsApi.recent()
      if (response.data.success) {
        notifications.value = response.data.data.map((n: any) => ({
          id: n.id,
          type: n.type,
          title: n.title,
          message: n.message,
          read: n.is_read || n.read || false,
          is_read: n.is_read || n.read || false,
          timestamp: n.created_at || n.timestamp,
          created_at: n.created_at,
          link: n.action_url || n.link,
          action_url: n.action_url || n.link,
          icon: n.icon,
          priority: n.priority,
          related_type: n.related_type,
          related_id: n.related_id,
        }))
        lastFetched.value = new Date()
      }
    } catch (error) {
      console.error('Failed to load notifications:', error)
    } finally {
      isLoading.value = false
    }
  }

  const fetchUnreadCount = async (): Promise<number> => {
    try {
      const response = await notificationsApi.unreadCount()
      if (response.data.success) {
        return response.data.data.count || 0
      }
    } catch (error) {
      console.error('Failed to fetch unread count:', error)
    }
    return 0
  }

  const markAsRead = async (id: string) => {
    const notification = notifications.value.find(n => n.id === id)
    if (notification) {
      notification.read = true
      notification.is_read = true
    }
    
    try {
      await notificationsApi.markAsRead(id)
    } catch (error) {
      console.error('Failed to mark notification as read:', error)
      
      if (notification) {
        notification.read = false
        notification.is_read = false
      }
    }
  }

  const markAllAsRead = async () => {
    notifications.value.forEach(n => {
      n.read = true
      n.is_read = true
    })
    
    try {
      await notificationsApi.markAllAsRead()
    } catch (error) {
      console.error('Failed to mark all as read:', error)
    }
  }

  const removeNotification = async (id: string) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
    
    try {
      await notificationsApi.delete(id)
    } catch (error) {
      console.error('Failed to delete notification:', error)
    }
  }

  const clearAll = () => {
    notifications.value = []
  }

  const addNotification = (notification: Omit<ClientNotification, 'id' | 'read' | 'timestamp'>) => {
    const newNotification: ClientNotification = {
      ...notification,
      id: Date.now().toString(),
      read: false,
      is_read: false,
      timestamp: new Date().toISOString()
    }
    notifications.value.unshift(newNotification)
    return newNotification
  }

  const startPolling = () => {
    if (pollingInterval) return
    
    
    loadNotifications(true)
    
    
    pollingInterval = window.setInterval(() => {
      loadNotifications(true)
    }, 5000)
  }

  const stopPolling = () => {
    if (pollingInterval) {
      clearInterval(pollingInterval)
      pollingInterval = null
    }
  }

  
  const handleOrderStatusUpdate = (event: CustomEvent) => {
    
    loadNotifications(true)
  }

  const handleProductUpdate = (event: CustomEvent) => {
    
    loadNotifications(true)
  }

  const handlePaymentUpdate = (event: CustomEvent) => {
    
    loadNotifications(true)
  }

  
  if (typeof window !== 'undefined') {
    onMounted(() => {
      startPolling()
      window.addEventListener('realtime:order:status:updated', handleOrderStatusUpdate as EventListener)
      window.addEventListener('realtime:product:updated', handleProductUpdate as EventListener)
      window.addEventListener('realtime:payment:updated', handlePaymentUpdate as EventListener)
      
      window.addEventListener('realtime:order:created', handleOrderStatusUpdate as EventListener)
    })

    onUnmounted(() => {
      stopPolling()
      window.removeEventListener('realtime:order:status:updated', handleOrderStatusUpdate as EventListener)
      window.removeEventListener('realtime:product:updated', handleProductUpdate as EventListener)
      window.removeEventListener('realtime:payment:updated', handlePaymentUpdate as EventListener)
      window.removeEventListener('realtime:order:created', handleOrderStatusUpdate as EventListener)
    })
  }

  const formatTimeAgo = (timestamp: string): string => {
    const now = new Date()
    const time = new Date(timestamp)
    const diffInSeconds = Math.floor((now.getTime() - time.getTime()) / 1000)

    if (diffInSeconds < 60) {
      return 'Just now'
    } else if (diffInSeconds < 3600) {
      const minutes = Math.floor(diffInSeconds / 60)
      return `${minutes} minute${minutes > 1 ? 's' : ''} ago`
    } else if (diffInSeconds < 86400) {
      const hours = Math.floor(diffInSeconds / 3600)
      return `${hours} hour${hours > 1 ? 's' : ''} ago`
    } else {
      const days = Math.floor(diffInSeconds / 86400)
      return `${days} day${days > 1 ? 's' : ''} ago`
    }
  }

  return {
    notifications,
    isLoading,
    unreadCount,
    unreadNotifications,
    recentNotifications,
    loadNotifications,
    fetchUnreadCount,
    markAsRead,
    markAllAsRead,
    removeNotification,
    clearAll,
    addNotification,
    formatTimeAgo,
    startPolling,
    stopPolling,
  }
})
