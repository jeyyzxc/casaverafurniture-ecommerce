/**
 * Composable for product-related operations
 */
import { ref, computed } from 'vue'
import { Product } from '@/types'
import { formatPrice } from '@/utils/formatters'

export function useProduct() {
  const isAddingToCart = ref(false)
  const addingProductId = ref<number | null>(null)

  /**
   * Check if product is out of stock
   */
  const isOutOfStock = (product: Product): boolean => {
    if (!product.track_inventory) return false
    const stock = product.stock_quantity ?? product.stock ?? 0
    return stock <= 0
  }

  /**
   * Check if product is low stock
   */
  const isLowStock = (product: Product): boolean => {
    if (!product.track_inventory || isOutOfStock(product)) return false
    const stock = product.stock_quantity ?? product.stock ?? 0
    const threshold = product.low_stock_threshold ?? 10
    return stock > 0 && stock <= threshold
  }

  /**
   * Get display price for product
   */
  const getDisplayPrice = (product: Product): number => {
    return product.sale_price ?? product.price
  }

  /**
   * Get formatted price for product
   */
  const getFormattedPrice = (product: Product): string => {
    return formatPrice(getDisplayPrice(product))
  }

  /**
   * Get original price if on sale
   */
  const getOriginalPrice = (product: Product): string | null => {
    if (product.sale_price) {
      return formatPrice(product.price)
    }
    return null
  }

  return {
    isAddingToCart,
    addingProductId,
    isOutOfStock,
    isLowStock,
    getDisplayPrice,
    getFormattedPrice,
    getOriginalPrice,
  }
}
