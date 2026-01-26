import { ref } from 'vue'

export interface Notification {
  id: string
  type: 'success' | 'error' | 'warning' | 'info'
  title: string
  message: string
  duration?: number
}

// Singleton notifications array shared across all components
export const notifications = ref<Notification[]>([])

const showNotification = (notification: Omit<Notification, 'id'>) => {
  const id = `notification-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
  const newNotification: Notification = {
    id,
    duration: 5000,
    ...notification,
  }

  notifications.value.push(newNotification)

  // Auto remove after duration
  if (newNotification.duration && newNotification.duration > 0) {
    setTimeout(() => {
      removeNotification(id)
    }, newNotification.duration)
  }

  return id
}

export const removeNotification = (id: string) => {
  const index = notifications.value.findIndex(n => n.id === id)
  if (index > -1) {
    notifications.value.splice(index, 1)
  }
}

export function useNotification() {
  const success = (title: string, message?: string) => {
    return showNotification({ type: 'success', title, message: message || '' })
  }

  const error = (title: string, message?: string) => {
    return showNotification({ type: 'error', title, message: message || '', duration: 7000 })
  }

  const warning = (title: string, message?: string) => {
    return showNotification({ type: 'warning', title, message: message || '' })
  }

  const info = (title: string, message?: string) => {
    return showNotification({ type: 'info', title, message: message || '' })
  }

  return {
    notifications,
    showNotification,
    removeNotification,
    success,
    error,
    warning,
    info,
  }
}
