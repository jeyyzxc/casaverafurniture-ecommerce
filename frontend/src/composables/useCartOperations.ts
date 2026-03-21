/**
 * Composable for cart operations
 */
import { ref } from 'vue'
import { useCartStore } from '@/stores/cart'
import { Product } from '@/types'
import { useNotification } from './useNotification'

export function useCartOperations() {
  const cartStore = useCartStore()
  const { success, error: showError } = useNotification()
  const isAddingToCart = ref(false)
  const addingProductId = ref<number | null>(null)

  /**
   * Add product to cart
   */
  const addToCart = async (product: Product, quantity: number = 1) => {
    if (isAddingToCart.value) return

    isAddingToCart.value = true
    addingProductId.value = product.id

    try {
      const result = await cartStore.addItem(product.id, quantity)
      if (result.success) {
        success('Added to Cart', `${product.name} has been added to your cart.`)
        return { success: true }
      } else {
        showError('Failed to Add', result.message || 'Failed to add item to cart')
        return { success: false, message: result.message }
      }
    } catch (error: any) {
      console.error('Failed to add to cart:', error)
      showError('Error', error.response?.data?.message || 'Failed to add item to cart. Please try again.')
      return { success: false, message: error.response?.data?.message || 'Failed to add item to cart' }
    } finally {
      isAddingToCart.value = false
      addingProductId.value = null
    }
  }

  /**
   * Buy now - add to cart and redirect to checkout
   */
  const buyNow = async (product: Product, quantity: number = 1) => {
    const result = await addToCart(product, quantity)
    if (result.success) {
      
      return { success: true }
    }
    return result
  }

  return {
    isAddingToCart,
    addingProductId,
    addToCart,
    buyNow,
  }
}
