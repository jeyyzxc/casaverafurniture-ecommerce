import { ref, onMounted, onUnmounted } from 'vue'
import { realtimeService } from '@/services/realtime'

export function useRealtimeAdmin() {
  const lastUpdateTime = ref<number>(Date.now())
  const isConnected = ref(false)

  const handleAdminEvent = (event: string, data: any) => {
    lastUpdateTime.value = Date.now()

    switch (event) {
      case 'stock.changed':
        window.dispatchEvent(new CustomEvent('realtime:admin:stock:changed', { detail: data }))
        break
      case 'order.created':
        window.dispatchEvent(new CustomEvent('realtime:admin:order:created', { detail: data }))
        break
      case 'order.status.updated':
        window.dispatchEvent(new CustomEvent('realtime:admin:order:status:updated', { detail: data }))
        break
      case 'notification.created':
        window.dispatchEvent(new CustomEvent('realtime:admin:notification:created', { detail: data }))
        break
      case 'user.login':
        window.dispatchEvent(new CustomEvent('realtime:admin:user:login', { detail: data }))
        break
      case 'user.registered':
        window.dispatchEvent(new CustomEvent('realtime:admin:user:registered', { detail: data }))
        break
      case 'payment.received':
        window.dispatchEvent(new CustomEvent('realtime:admin:payment:received', { detail: data }))
        break
      case 'promotion.created':
        window.dispatchEvent(new CustomEvent('realtime:admin:promotion:created', { detail: data }))
        break
      case 'product.created':
        window.dispatchEvent(new CustomEvent('realtime:admin:product:created', { detail: data }))
        break
      case 'product.updated':
        window.dispatchEvent(new CustomEvent('realtime:admin:product:updated', { detail: data }))
        break
    }
  }

  const startListening = () => {
    realtimeService.listenToAdmin(handleAdminEvent)
    isConnected.value = true
  }

  const stopListening = () => {
    isConnected.value = false
  }

  onMounted(() => {
    startListening()
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
