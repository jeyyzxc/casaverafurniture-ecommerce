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
      // Check if this cart update is for the current user/session
      const sessionId = getOrCreateSessionId()
      const userId = cartStore.cart?.user_id || null
      
      // Only update if it matches current user or session
      if (
        (data.user_id && userId && data.user_id === userId) ||
        (data.session_id && sessionId && data.session_id === sessionId) ||
        (!data.user_id && !data.session_id) // Public update
      ) {
        // Refresh cart from server to get latest data
        cartStore.fetchCart()
        
        // Emit custom event for components
        window.dispatchEvent(new CustomEvent('realtime:cart:updated', { detail: data }))
      }
    }
  }

  const startListening = () => {
    // Listen to public cart channel
    realtimeService.listenToCart(handleCartEvent)
    
    // Also listen to session-specific channel if available
    const sessionId = getOrCreateSessionId()
    if (sessionId) {
      // Note: This would require adding a method to realtimeService
      // For now, we'll use the public channel
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
