import { ref, computed, onMounted, onUnmounted } from 'vue'
import { defineStore } from 'pinia'
import { useRealtimeAdmin } from '@/composables/useRealtimeAdmin'

export interface Notification {
  id: number
  type: 'order' | 'inventory' | 'review' | 'system' | 'promotion' | 'user' | 'payment' | 'product'
  title: string
  message: string
  read: boolean
  timestamp: string
  link?: string
  icon?: string
}

export const useNotificationStore = defineStore('notifications', () => {
  // State - Empty initially as requested to replace static contexts with real-time
  const notifications = ref<Notification[]>([])

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
        icon: 'alert-triangle'
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

  // Real-time order handler
  const handleOrderCreated = (event: CustomEvent) => {
    const orderData = event.detail
    addNotification({
      type: 'order',
      title: 'New Order Received',
      message: `Order #${orderData.order_number || orderData.id} has been placed by ${orderData.customer_name || 'a customer'}`,
      link: `/admin/orders`,
      icon: 'shopping-cart'
    })
  }

  const handleOrderStatusUpdated = (event: CustomEvent) => {
    const orderData = event.detail
    addNotification({
      type: 'order',
      title: 'Order Status Updated',
      message: `Order #${orderData.order_number || orderData.id} status changed to ${orderData.status}`,
      link: `/admin/orders`,
      icon: 'truck'
    })
  }

  const handleNotificationCreated = (event: CustomEvent) => {
    const data = event.detail
    addNotification({
      type: data.type === 'stock' ? 'inventory' : (data.type || 'info'),
      title: data.title,
      message: data.message,
      link: data.action_url || '#',
      icon: getIconForType(data.type)
    })
  }

  const handleUserLogin = (event: CustomEvent) => {
    const data = event.detail
    addNotification({
      type: 'user',
      title: 'User Login',
      message: `${data.user_name} has logged in.`,
      link: '/admin/users',
      icon: 'user'
    })
  }

  const handleUserRegistered = (event: CustomEvent) => {
    const data = event.detail
    addNotification({
      type: 'user',
      title: 'New User Registered',
      message: `${data.user_name} has registered.`,
      link: '/admin/users',
      icon: 'user-plus'
    })
  }

  const handlePaymentReceived = (event: CustomEvent) => {
    const data = event.detail
    addNotification({
      type: 'payment',
      title: 'Payment Received',
      message: `Payment of ₱${data.amount} received for Order #${data.order_number}.`,
      link: '/admin/payments',
      icon: 'credit-card'
    })
  }

  const handlePromotionCreated = (event: CustomEvent) => {
    const data = event.detail
    addNotification({
      type: 'promotion',
      title: 'New Promotion Added',
      message: `Promotion "${data.name}" has been created.`,
      link: '/admin/promotions',
      icon: 'tag'
    })
  }

  const handleProductCreated = (event: CustomEvent) => {
    const data = event.detail
    addNotification({
      type: 'product',
      title: 'New Product Added',
      message: `Product "${data.name}" has been added.`,
      link: `/admin/products/${data.id}`,
      icon: 'plus-circle'
    })
  }

  const handleProductUpdated = (event: CustomEvent) => {
    const data = event.detail
    addNotification({
      type: 'product',
      title: 'Product Updated',
      message: `Product "${data.name}" has been updated.`,
      link: `/admin/products/${data.id}`,
      icon: 'edit'
    })
  }

  const getIconForType = (type: string): string => {
    const icons: Record<string, string> = {
      order: 'shopping-cart',
      inventory: 'box',
      stock: 'alert-triangle',
      payment: 'credit-card',
      user: 'user',
      system: 'info',
      promotion: 'tag',
      product: 'box'
    }
    return icons[type] || 'bell'
  }

  // Set up real-time listeners for admin notifications
  if (typeof window !== 'undefined') {
    onMounted(() => {
      window.addEventListener('realtime:admin:stock:changed', handleStockAlert as EventListener)
      window.addEventListener('realtime:admin:order:created', handleOrderCreated as EventListener)
      window.addEventListener('realtime:admin:order:status:updated', handleOrderStatusUpdated as EventListener)
      window.addEventListener('realtime:admin:notification:created', handleNotificationCreated as EventListener)
      window.addEventListener('realtime:admin:user:login', handleUserLogin as EventListener)
      window.addEventListener('realtime:admin:user:registered', handleUserRegistered as EventListener)
      window.addEventListener('realtime:admin:payment:received', handlePaymentReceived as EventListener)
      window.addEventListener('realtime:admin:promotion:created', handlePromotionCreated as EventListener)
      window.addEventListener('realtime:admin:product:created', handleProductCreated as EventListener)
      window.addEventListener('realtime:admin:product:updated', handleProductUpdated as EventListener)
    })

    onUnmounted(() => {
      window.removeEventListener('realtime:admin:stock:changed', handleStockAlert as EventListener)
      window.removeEventListener('realtime:admin:order:created', handleOrderCreated as EventListener)
      window.removeEventListener('realtime:admin:order:status:updated', handleOrderStatusUpdated as EventListener)
      window.removeEventListener('realtime:admin:notification:created', handleNotificationCreated as EventListener)
      window.removeEventListener('realtime:admin:user:login', handleUserLogin as EventListener)
      window.removeEventListener('realtime:admin:user:registered', handleUserRegistered as EventListener)
      window.removeEventListener('realtime:admin:payment:received', handlePaymentReceived as EventListener)
      window.removeEventListener('realtime:admin:promotion:created', handlePromotionCreated as EventListener)
      window.removeEventListener('realtime:admin:product:created', handleProductCreated as EventListener)
      window.removeEventListener('realtime:admin:product:updated', handleProductUpdated as EventListener)
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
