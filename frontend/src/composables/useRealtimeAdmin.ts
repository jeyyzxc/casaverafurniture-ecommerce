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
