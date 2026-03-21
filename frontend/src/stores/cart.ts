import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { cart as cartApi } from '@/services/clientApi'

interface CartItem {
  id: number
  product_id: number
  product_name: string
  product_sku: string
  product_image: string | null
  product_slug: string | null
  quantity: number
  unit_price: number
  sale_price: number | null
  subtotal: number
  stock_status: string
  max_quantity: number
}

interface Promotion {
  id: number
  name: string
  code: string
  discount_type: string
  discount_value: number
}

interface Cart {
  id: number
  item_count: number
  subtotal: number
  discount_amount: number
  total: number
  coupon_code: string | null
  promotion: Promotion | null
  items: CartItem[]
}

export const useCartStore = defineStore('cart', () => {
  const cart = ref<Cart | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const itemCount = computed(() => {
    const value = cart.value?.item_count
    return typeof value === 'number' ? value : parseInt(String(value || 0), 10)
  })
  const subtotal = computed(() => {
    const value = cart.value?.subtotal
    if (typeof value === 'number') return value
    if (typeof value === 'string') {
      return parseFloat(value.replace(/[^0-9.-]/g, '')) || 0
    }
    return 0
  })
  const discount = computed(() => {
    const value = cart.value?.discount_amount
    if (typeof value === 'number') return value
    if (typeof value === 'string') {
      return parseFloat(value.replace(/[^0-9.-]/g, '')) || 0
    }
    return 0
  })
  const total = computed(() => {
    const value = cart.value?.total
    if (typeof value === 'number') return value
    if (typeof value === 'string') {
      return parseFloat(value.replace(/[^0-9.-]/g, '')) || 0
    }
    return 0
  })
  const items = computed(() => cart.value?.items || [])
  const appliedCoupon = computed(() => cart.value?.coupon_code || null)
  const isEmpty = computed(() => !cart.value || cart.value.items.length === 0)

  async function fetchCart() {
    isLoading.value = true
    error.value = null

    try {
      const response = await cartApi.get()
      if (response.data.success) {
        cart.value = response.data.data
      } else {
        error.value = response.data.message || 'Failed to load cart'
        if (!cart.value) {
          cart.value = {
            id: 0,
            item_count: 0,
            subtotal: 0,
            discount_amount: 0,
            total: 0,
            coupon_code: null,
            promotion: null,
            items: [],
          }
        }
      }
    } catch (err: unknown) {
      const apiError = err as { response?: { status?: number; data?: { message?: string } }; message?: string }
      error.value = apiError.response?.data?.message || apiError.message || 'Failed to load cart'

      if (apiError.response?.status !== 401) {
        cart.value = {
          id: 0,
          item_count: 0,
          subtotal: 0,
          discount_amount: 0,
          total: 0,
          coupon_code: null,
          promotion: null,
          items: [],
        }
      }
    } finally {
      isLoading.value = false
    }
  }

  async function addItem(productId: number, quantity = 1) {
    isLoading.value = true
    error.value = null

    try {
      const response = await cartApi.addItem(productId, quantity)
      if (response.data.success) {
        cart.value = response.data.data

        if (typeof window !== 'undefined') {
          window.dispatchEvent(new CustomEvent('realtime:cart:updated', {
            detail: {
              item_count: cart.value.item_count,
              action: 'add',
              product_id: productId
            }
          }))
        }

        return { success: true, message: response.data.message }
      }
      return { success: false, message: response.data.message || 'Failed to add item' }
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : 'Failed to add item'
      error.value = message
      return { success: false, message }
    } finally {
      isLoading.value = false
    }
  }

  async function updateItem(itemId: number, quantity: number) {
    isLoading.value = true
    error.value = null

    try {
      const response = await cartApi.updateItem(itemId, quantity)
      if (response.data.success) {
        cart.value = response.data.data

        if (typeof window !== 'undefined') {
          window.dispatchEvent(new CustomEvent('realtime:cart:updated', {
            detail: {
              item_count: cart.value.item_count,
              action: 'update',
              item_id: itemId
            }
          }))
        }

        return { success: true, message: response.data.message }
      }
      return { success: false, message: response.data.message || 'Failed to update item' }
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : 'Failed to update item'
      error.value = message
      return { success: false, message }
    } finally {
      isLoading.value = false
    }
  }

  async function removeItem(itemId: number) {
    isLoading.value = true
    error.value = null

    try {
      const response = await cartApi.removeItem(itemId)
      if (response.data.success) {
        cart.value = response.data.data

        if (typeof window !== 'undefined') {
          window.dispatchEvent(new CustomEvent('realtime:cart:updated', {
            detail: {
              item_count: cart.value.item_count,
              action: 'remove',
              item_id: itemId
            }
          }))
        }

        return { success: true, message: response.data.message }
      }
      return { success: false, message: response.data.message || 'Failed to remove item' }
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : 'Failed to remove item'
      error.value = message
      return { success: false, message }
    } finally {
      isLoading.value = false
    }
  }

  async function clearCart() {
    isLoading.value = true
    error.value = null

    try {
      const response = await cartApi.clear()
      if (response.data.success) {
        cart.value = response.data.data

        if (typeof window !== 'undefined') {
          window.dispatchEvent(new CustomEvent('realtime:cart:updated', {
            detail: {
              item_count: 0,
              action: 'clear'
            }
          }))
        }

        return { success: true, message: response.data.message }
      }
      return { success: false, message: response.data.message || 'Failed to clear cart' }
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : 'Failed to clear cart'
      error.value = message
      return { success: false, message }
    } finally {
      isLoading.value = false
    }
  }

  async function applyCoupon(code: string) {
    isLoading.value = true
    error.value = null

    try {
      const response = await cartApi.applyCoupon(code)
      if (response.data.success) {
        cart.value = response.data.data
        return { success: true, message: response.data.message }
      }
      return { success: false, message: response.data.message || 'Invalid coupon code' }
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : 'Invalid coupon code'
      error.value = message
      return { success: false, message }
    } finally {
      isLoading.value = false
    }
  }

  async function removeCoupon() {
    isLoading.value = true
    error.value = null

    try {
      const response = await cartApi.removeCoupon()
      if (response.data.success) {
        cart.value = response.data.data
        return { success: true, message: response.data.message }
      }
      return { success: false, message: response.data.message || 'Failed to remove coupon' }
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : 'Failed to remove coupon'
      error.value = message
      return { success: false, message }
    } finally {
      isLoading.value = false
    }
  }

  const handleCartUpdate = () => {
    fetchCart()
  }

  if (typeof window !== 'undefined') {
    fetchCart()
  }

  return {
    cart,
    isLoading,
    error,
    itemCount,
    subtotal,
    discount,
    total,
    items,
    appliedCoupon,
    isEmpty,
    fetchCart,
    addItem,
    updateItem,
    removeItem,
    clearCart,
    applyCoupon,
    removeCoupon,
  }
})
