/**
 * Composable for wishlist operations
 */
import { ref, computed } from 'vue'
import { wishlist as wishlistApi } from '@/services/clientApi'
import { useAuthStore } from '@/stores/auth'
import { useNotification } from './useNotification'

export function useWishlist() {
  const authStore = useAuthStore()
  const { success, error: showError } = useNotification()
  const wishlistItems = ref<Set<number>>(new Set())
  const isLoading = ref(false)
  const isToggling = ref<number | null>(null)

  /**
   * Check if product is in wishlist
   */
  const isInWishlist = (productId: number): boolean => {
    return wishlistItems.value.has(productId)
  }

  /**
   * Load user's wishlist
   */
  const loadWishlist = async () => {
    if (!authStore.isAuthenticated) {
      wishlistItems.value.clear()
      return
    }

    isLoading.value = true
    try {
      const response = await wishlistApi.get()
      if (response.data.success) {
        const items = response.data.data || []
        wishlistItems.value = new Set(items.map((item: any) => item.product_id || item.product?.id))
      }
    } catch (error) {
      console.error('Failed to load wishlist:', error)
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Check wishlist status for a product
   */
  const checkWishlistStatus = async (productId: number) => {
    if (!authStore.isAuthenticated) return false

    try {
      const response = await wishlistApi.check(productId)
      if (response.data.success) {
        const inWishlist = response.data.data?.in_wishlist || false
        if (inWishlist) {
          wishlistItems.value.add(productId)
        } else {
          wishlistItems.value.delete(productId)
        }
        return inWishlist
      }
    } catch (error) {
      console.error('Failed to check wishlist status:', error)
    }
    return false
  }

  /**
   * Toggle product in wishlist
   */
  const toggleWishlist = async (productId: number) => {
    if (!authStore.isAuthenticated) {
      showError('Login Required', 'Please login to add items to your wishlist.')
      return { success: false, message: 'Login required' }
    }

    if (isToggling.value === productId) return { success: false, message: 'Already processing' }

    isToggling.value = productId
    const isCurrentlyInWishlist = wishlistItems.value.has(productId)

    try {
      if (isCurrentlyInWishlist) {
        
        const response = await wishlistApi.remove(productId)
        if (response.data.success) {
          wishlistItems.value.delete(productId)
          success('Removed', 'Product removed from wishlist.')
          return { success: true, inWishlist: false }
        } else {
          showError('Error', response.data.message || 'Failed to remove from wishlist.')
          return { success: false, message: response.data.message }
        }
      } else {
        
        const response = await wishlistApi.add(productId)
        if (response.data.success) {
          wishlistItems.value.add(productId)
          success('Added', 'Product added to wishlist.')
          return { success: true, inWishlist: true }
        } else {
          showError('Error', response.data.message || 'Failed to add to wishlist.')
          return { success: false, message: response.data.message }
        }
      }
    } catch (error: any) {
      console.error('Failed to toggle wishlist:', error)
      showError('Error', error.response?.data?.message || 'Failed to update wishlist. Please try again.')
      return { success: false, message: error.response?.data?.message || 'Failed to update wishlist' }
    } finally {
      isToggling.value = null
    }
  }

  /**
   * Add product to wishlist
   */
  const addToWishlist = async (productId: number) => {
    if (wishlistItems.value.has(productId)) {
      return { success: true, inWishlist: true }
    }
    return toggleWishlist(productId)
  }

  /**
   * Remove product from wishlist
   */
  const removeFromWishlist = async (productId: number) => {
    if (!wishlistItems.value.has(productId)) {
      return { success: true, inWishlist: false }
    }
    return toggleWishlist(productId)
  }

  return {
    wishlistItems: computed(() => wishlistItems.value),
    isLoading,
    isToggling,
    isInWishlist,
    loadWishlist,
    checkWishlistStatus,
    toggleWishlist,
    addToWishlist,
    removeFromWishlist,
  }
}
