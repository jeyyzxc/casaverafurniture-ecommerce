/**
 * Composable for loading products from API
 */
import { ref } from 'vue'
import { products as productsApi } from '@/services/clientApi'
import { Product, ProductFilters } from '@/types'

export function useProductLoader() {
  const isLoading = ref(false)
  const error = ref<string | null>(null)
  const products = ref<Product[]>([])

  /**
   * Transform API response to Product type
   */
  const transformProduct = (p: any): Product => {
    return {
      id: p.id,
      name: p.name,
      slug: p.slug,
      category: p.category?.slug,
      category_name: p.category?.name,
      price: typeof p.price === 'string' ? parseFloat(p.price) : (typeof p.price === 'number' ? p.price : 0),
      sale_price: p.sale_price ? (typeof p.sale_price === 'string' ? parseFloat(p.sale_price) : (typeof p.sale_price === 'number' ? p.sale_price : null)) : null,
      image: p.image || p.primary_image || '/images/products/placeholder.png',
      primary_image: p.primary_image || p.image,
      is_new: p.is_new || false,
      is_featured: p.is_featured || false,
      is_bestseller: p.is_bestseller || false,
      description: p.description,
      stock: p.stock,
      stock_quantity: p.stock_quantity,
      stock_status: p.stock_status,
      low_stock_threshold: p.low_stock_threshold,
      track_inventory: p.track_inventory,
      sku: p.sku,
    }
  }

  /**
   * Load products with filters
   */
  const loadProducts = async (filters?: ProductFilters) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await productsApi.list(filters)

      if (response.data.success) {
        const responseData = response.data.data
        const productArray: Array<Record<string, unknown>> = Array.isArray(responseData)
          ? responseData
          : (responseData?.data || [])

        products.value = productArray.map(transformProduct)
        return { success: true, products: products.value }
      } else {
        error.value = response.data.message || 'Failed to load products'
        return { success: false, message: error.value }
      }
    } catch (err: any) {
      console.error('Failed to load products:', err)
      error.value = err.response?.data?.message || 'Failed to load products. Please try again.'
      return { success: false, message: error.value }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Load featured products
   */
  const loadFeatured = async (limit: number = 10) => {
    return loadProducts({
      featured: true,
      per_page: limit,
      sort_by: 'newest',
      sort_order: 'desc',
    })
  }

  /**
   * Load bestseller products
   */
  const loadBestsellers = async (limit: number = 5) => {
    return loadProducts({
      bestseller: true,
      per_page: limit,
      sort_by: 'newest',
      sort_order: 'desc',
    })
  }

  return {
    isLoading,
    error,
    products,
    loadProducts,
    loadFeatured,
    loadBestsellers,
  }
}
