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
        
        window.dispatchEvent(new CustomEvent('realtime:order:created', { detail: data }))
        
        if (authStore.user && data.user_id === authStore.user.id) {
          window.dispatchEvent(new CustomEvent('realtime:order:created:user', { detail: data }))
        }
        break
      case 'order.status.updated':
        
        if (authStore.user && data.user_id === authStore.user.id) {
          window.dispatchEvent(new CustomEvent('realtime:order:status:updated', { detail: data }))
        }
        break
    }
  }

  const startListening = () => {
    realtimeService.listenToOrders(handleOrderEvent)
    
    
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
