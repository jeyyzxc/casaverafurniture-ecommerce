import { ref, computed } from 'vue'
import { orders as ordersApi } from '@/services/clientApi'
import { useAuthStore } from '@/stores/auth'

const orderCount = ref<number>(0)
const isLoading = ref(false)

export function useOrderCount() {
  const authStore = useAuthStore()

  const fetchOrderCount = async () => {
    if (!authStore.isAuthenticated) {
      orderCount.value = 0
      return
    }

    isLoading.value = true
    try {
      const response = await ordersApi.list({ per_page: 1 })
      if (response.data.success) {
        
        if (response.data.data.meta?.total !== undefined) {
          orderCount.value = response.data.data.meta.total
        } else if (response.data.data.total !== undefined) {
          orderCount.value = response.data.data.total
        } else if (Array.isArray(response.data.data)) {
          orderCount.value = response.data.data.length
        } else if (response.data.data.data) {
          
          orderCount.value = response.data.data.meta?.total || response.data.data.data.length
        } else {
          orderCount.value = 0
        }
      }
    } catch (error) {
      console.error('Failed to fetch order count:', error)
      orderCount.value = 0
    } finally {
      isLoading.value = false
    }
  }

  const updateOrderCount = (count: number) => {
    orderCount.value = count
  }

  const hasOrders = computed(() => orderCount.value > 0)

  return {
    orderCount: computed(() => orderCount.value),
    isLoading: computed(() => isLoading.value),
    hasOrders,
    fetchOrderCount,
    updateOrderCount,
  }
}
