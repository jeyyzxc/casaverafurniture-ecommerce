import { ref, onMounted, onUnmounted } from 'vue'
import { realtimeService } from '@/services/realtime'
import { useAuthStore } from '@/stores/auth'

export function useRealtimeOrders() {
  const lastUpdateTime = ref<number>(Date.now())
  const isConnected = ref(false)
  const authStore = useAuthStore()

  const handleOrderEvent = (event: string, data: any) => {
    lastUpdateTime.value = Date.now()

    switch (event) {
      case 'order.created':
        // Emit event for order creation
        window.dispatchEvent(new CustomEvent('realtime:order:created', { detail: data }))
        // If it's the current user's order, also emit user-specific event
        if (authStore.user && data.user_id === authStore.user.id) {
          window.dispatchEvent(new CustomEvent('realtime:order:created:user', { detail: data }))
        }
        break
      case 'order.status.updated':
        // Only update if it's the current user's order
        if (authStore.user && data.user_id === authStore.user.id) {
          window.dispatchEvent(new CustomEvent('realtime:order:status:updated', { detail: data }))
        }
        break
    }
  }

  const startListening = () => {
    realtimeService.listenToOrders(handleOrderEvent)
    
    // Also listen to private user channel if authenticated
    if (authStore.user) {
      realtimeService.listenToUser(authStore.user.id, handleOrderEvent)
    }
    
    isConnected.value = true
  }

  const stopListening = () => {
    isConnected.value = false
  }

  onMounted(() => {
    if (authStore.isAuthenticated) {
      startListening()
    }
  })

  onUnmounted(() => {
    stopListening()
  })

  return {
    lastUpdateTime,
    isConnected,
    startListening,
    stopListening,
  }
}
