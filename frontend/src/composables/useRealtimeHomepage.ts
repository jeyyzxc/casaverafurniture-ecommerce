import { ref, onMounted, onUnmounted } from 'vue'
import { realtimeService } from '@/services/realtime'

export function useRealtimeHomepage() {
  const lastUpdateTime = ref<number>(Date.now())
  const isConnected = ref(false)

  const handleHomepageEvent = (event: string, data: any) => {
    lastUpdateTime.value = Date.now()

    if (event === 'homepage.updated') {
      window.dispatchEvent(new CustomEvent('realtime:homepage:updated', { detail: data }))
    }
  }

  const startListening = () => {
    realtimeService.listenToHomepage(handleHomepageEvent)
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
