import { ref, computed, onMounted, onUnmounted } from 'vue'
import { defineStore } from 'pinia'
import { useRealtimeAdmin } from '@/composables/useRealtimeAdmin'

export interface Notification {
  id: number
  type: 'order' | 'inventory' | 'review' | 'system' | 'promotion'
  title: string
  message: string
  read: boolean
  timestamp: string
  link?: string
  icon?: string
}

export const useNotificationStore = defineStore('notifications', () => {
  // State
  const notifications = ref<Notification[]>([
    {
      id: 1,
      type: 'order',
      title: 'New Order Received',
      message: 'Order #12345 has been placed by John Doe',
      read: false,
      timestamp: new Date(Date.now() - 5 * 60 * 1000).toISOString(),
      link: '/admin/orders',
      icon: 'shopping-cart'
    },
    {
      id: 2,
      type: 'inventory',
      title: 'Low Stock Alert',
      message: 'Product "Luxury Sofa" is running low (5 items remaining)',
      read: false,
      timestamp: new Date(Date.now() - 15 * 60 * 1000).toISOString(),
      link: '/admin/inventory',
      icon: 'box'
    },
    {
      id: 3,
      type: 'review',
      title: 'New Product Review',
      message: 'A new 5-star review has been submitted for "Modern Chair"',
      read: true,
      timestamp: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(),
      link: '/admin/reviews',
      icon: 'star'
    },
    {
      id: 4,
      type: 'order',
      title: 'Order Status Updated',
      message: 'Order #12340 has been shipped',
      read: true,
      timestamp: new Date(Date.now() - 3 * 60 * 60 * 1000).toISOString(),
      link: '/admin/orders',
      icon: 'truck'
    },
    {
      id: 5,
      type: 'system',
      title: 'System Update',
      message: 'New features have been added to the admin panel',
      read: false,
      timestamp: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString(),
      link: '/admin/settings',
      icon: 'info-circle'
    }
  ])

  // Computed
  const unreadCount = computed(() => 
    notifications.value.filter(n => !n.read).length
  )

  const unreadNotifications = computed(() =>
    notifications.value.filter(n => !n.read)
  )

  const recentNotifications = computed(() =>
    [...notifications.value]
      .sort((a, b) => new Date(b.timestamp).getTime() - new Date(a.timestamp).getTime())
      .slice(0, 10)
  )

  // Methods
  const markAsRead = (id: number) => {
    const notification = notifications.value.find(n => n.id === id)
    if (notification) {
      notification.read = true
    }
  }

  const markAllAsRead = () => {
    notifications.value.forEach(n => {
      n.read = true
    })
  }

  const removeNotification = (id: number) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }

  const clearAll = () => {
    notifications.value = []
  }

  const addNotification = (notification: Omit<Notification, 'id' | 'read' | 'timestamp'>) => {
    const newNotification: Notification = {
      ...notification,
      id: Date.now(),
      read: false,
      timestamp: new Date().toISOString()
    }
    notifications.value.unshift(newNotification)
    return newNotification
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

  // Real-time stock alert handler
  const handleStockAlert = (event: CustomEvent) => {
    const stockData = event.detail
    if (stockData.type === 'low_stock') {
      addNotification({
        type: 'inventory',
        title: 'Low Stock Alert',
        message: `${stockData.product_name} is running low (${stockData.new_quantity} items remaining)`,
        link: '/admin/inventory',
        icon: 'box'
      })
    } else if (stockData.type === 'out_of_stock') {
      addNotification({
        type: 'inventory',
        title: 'Out of Stock',
        message: `${stockData.product_name} is now out of stock`,
        link: '/admin/inventory',
        icon: 'box'
      })
    }
  }

  // Set up real-time listeners for admin notifications
  if (typeof window !== 'undefined') {
    onMounted(() => {
      window.addEventListener('realtime:admin:stock:changed', handleStockAlert as EventListener)
    })

    onUnmounted(() => {
      window.removeEventListener('realtime:admin:stock:changed', handleStockAlert as EventListener)
    })
  }

  return {
    notifications,
    unreadCount,
    unreadNotifications,
    recentNotifications,
    markAsRead,
    markAllAsRead,
    removeNotification,
    clearAll,
    addNotification,
    formatTimeAgo
  }
})
