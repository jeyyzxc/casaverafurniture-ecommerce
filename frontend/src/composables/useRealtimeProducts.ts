import { ref, onMounted, onUnmounted } from 'vue'
import { realtimeService } from '@/services/realtime'
import { products as productsApi } from '@/services/clientApi'

export function useRealtimeProducts() {
  const lastUpdateTime = ref<number>(Date.now())
  const isConnected = ref(false)

  const handleProductEvent = (event: string, data: any) => {
    lastUpdateTime.value = Date.now()

    switch (event) {
      case 'product.created':
        // Emit custom event for components to handle
        window.dispatchEvent(new CustomEvent('realtime:product:created', { detail: data }))
        break
      case 'product.updated':
        window.dispatchEvent(new CustomEvent('realtime:product:updated', { detail: data }))
        break
      case 'product.deleted':
        window.dispatchEvent(new CustomEvent('realtime:product:deleted', { detail: data }))
        break
      case 'stock.changed':
        window.dispatchEvent(new CustomEvent('realtime:stock:changed', { detail: data }))
        break
    }
  }

  const startListening = () => {
    realtimeService.listenToProducts(handleProductEvent)
    isConnected.value = true
  }

  const stopListening = () => {
    // Real-time service handles cleanup
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
