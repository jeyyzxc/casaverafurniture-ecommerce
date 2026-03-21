import { ref, onMounted, onUnmounted } from 'vue'
import { realtimeService } from '@/services/realtime'
import { useCartStore } from '@/stores/cart'
import { getOrCreateSessionId } from '@/services/api'

export function useRealtimeCart() {
  const lastUpdateTime = ref<number>(Date.now())
  const isConnected = ref(false)
  const cartStore = useCartStore()

  const handleCartEvent = (event: string, data: any) => {
    lastUpdateTime.value = Date.now()

    if (event === 'cart.updated') {
      
      const sessionId = getOrCreateSessionId()
      const userId = cartStore.cart?.user_id || null
      
      
      if (
        (data.user_id && userId && data.user_id === userId) ||
        (data.session_id && sessionId && data.session_id === sessionId) ||
        (!data.user_id && !data.session_id) 
      ) {
        
        cartStore.fetchCart()
        
        
        window.dispatchEvent(new CustomEvent('realtime:cart:updated', { detail: data }))
      }
    }
  }

  const startListening = () => {
    
    realtimeService.listenToCart(handleCartEvent)
    
    
    const sessionId = getOrCreateSessionId()
    if (sessionId) {
      
      
    }
    
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
