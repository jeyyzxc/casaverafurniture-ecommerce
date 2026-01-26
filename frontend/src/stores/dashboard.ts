import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { dashboard } from '@/services/adminApi'

export interface DashboardStats {
  total_orders: number
  total_revenue: number
  pending_orders: number
  total_customers: number
  new_customers: number
  total_products: number
  low_stock_products: number
  out_of_stock_products: number
  pending_payments: number
  pending_reviews: number
}

export interface OrderStatusDistribution {
  pending: number
  processing: number
  shipped: number
  delivered: number
  cancelled: number
}

export interface OrderItem {
  id: number
  product_id: number | null
  product_name: string
  product_sku: string
  quantity: number
  unit_price: number
  subtotal: number
  total: number
}

export interface RecentOrder {
  id: string
  orderId?: number
  customer: string
  amount: number
  status: string
  paymentStatus?: string
  date: Date
  items?: OrderItem[]
  itemsCount?: number
}

export interface BestSellingProduct {
  id: number
  name: string
  image: string
  sales: number
  revenue: number
}

export interface LowStockItem {
  id: number
  name: string
  stock: number
}

export const useDashboardStore = defineStore('dashboard', () => {
  // ═══════════════════════════════════════════════════
  // STATE
  // ═══════════════════════════════════════════════════
  const stats = ref<DashboardStats>({
    total_orders: 0,
    total_revenue: 0,
    pending_orders: 0,
    total_customers: 0,
    new_customers: 0,
    total_products: 0,
    low_stock_products: 0,
    out_of_stock_products: 0,
    pending_payments: 0,
    pending_reviews: 0,
  })

  const orderStatus = ref<OrderStatusDistribution>({
    pending: 0,
    processing: 0,
    shipped: 0,
    delivered: 0,
    cancelled: 0,
  })

  const recentOrders = ref<RecentOrder[]>([])
  const bestSellingProducts = ref<BestSellingProduct[]>([])
  const lowStockItems = ref<LowStockItem[]>([])
  const revenueByDay = ref<any[]>([])

  const isLoading = ref(false)
  const error = ref<string | null>(null)
  const lastUpdated = ref<Date | null>(null)
  const selectedPeriod = ref<string>('month')
  const isConnected = ref(false)

  // ═══════════════════════════════════════════════════
  // COMPUTED
  // ═══════════════════════════════════════════════════
  const totalRevenue = computed(() => stats.value.total_revenue)
  const totalOrders = computed(() => stats.value.total_orders)
  const totalProducts = computed(() => stats.value.total_products)
  const totalCustomers = computed(() => stats.value.total_customers)
  const newCustomersToday = computed(() => stats.value.new_customers)
  const hasLowStockAlerts = computed(() => lowStockItems.value.length > 0)
  const hasPendingOrders = computed(() => stats.value.pending_orders > 0)

  // ═══════════════════════════════════════════════════
  // METHODS
  // ═══════════════════════════════════════════════════
  const getDateRange = () => {
    const now = new Date()
    let startDate: Date
    let endDate = now

    switch (selectedPeriod.value) {
      case 'today':
        startDate = new Date(now.setHours(0, 0, 0, 0))
        break
      case 'week':
        startDate = new Date(now.setDate(now.getDate() - 7))
        break
      case 'month':
        startDate = new Date(now.getFullYear(), now.getMonth(), 1)
        break
      case 'year':
        startDate = new Date(now.getFullYear(), 0, 1)
        break
      default:
        startDate = new Date(now.getFullYear(), now.getMonth(), 1)
    }

    return {
      start_date: startDate.toISOString().split('T')[0],
      end_date: endDate.toISOString().split('T')[0],
    }
  }

  const loadDashboardData = async (force = false) => {
    // Prevent concurrent requests unless forced
    if (isLoading.value && !force) return

    isLoading.value = true
    error.value = null

    try {
      const dateRange = getDateRange()
      const response = await dashboard.getStats(dateRange.start_date, dateRange.end_date)

      if (response.data.success) {
        const data = response.data.data

        // Update stats
        stats.value = {
          total_orders: data.stats.total_orders || 0,
          total_revenue: parseFloat(data.stats.total_revenue || 0),
          pending_orders: data.stats.pending_orders || 0,
          total_customers: data.stats.total_customers || 0,
          new_customers: data.stats.new_customers || 0,
          total_products: data.stats.total_products || 0,
          low_stock_products: data.stats.low_stock_products || 0,
          out_of_stock_products: data.stats.out_of_stock_products || 0,
          pending_payments: data.stats.pending_payments || 0,
          pending_reviews: data.stats.pending_reviews || 0,
        }

        // Update order status distribution
        if (data.orders_by_status) {
          orderStatus.value = {
            pending: data.orders_by_status.pending || 0,
            processing: data.orders_by_status.processing || 0,
            shipped: data.orders_by_status.shipped || 0,
            delivered: data.orders_by_status.delivered || 0,
            cancelled: data.orders_by_status.cancelled || 0,
          }
        }

        // Update recent orders with items
        if (data.recent_orders) {
          recentOrders.value = data.recent_orders.map((order: any) => ({
            id: order.order_number,
            orderId: order.id,
            customer: order.customer_name || `${order.user?.first_name || ''} ${order.user?.last_name || ''}`.trim() || 'Guest',
            amount: parseFloat(order.total || 0),
            status: order.status,
            paymentStatus: order.payment_status,
            date: new Date(order.created_at),
            items: order.items || [],
            itemsCount: order.items_count || order.items?.length || 0,
          }))
        }

        // Update best selling products
        if (data.top_products) {
          bestSellingProducts.value = data.top_products.map((product: any) => {
            let imagePath = product.primary_image || '/images/products/placeholder.png'
            if (!imagePath.startsWith('/images/products/')) {
              const filename = imagePath.split('/').pop()
              imagePath = `/images/products/${filename}`
            }
            return {
              id: product.id,
              name: product.name,
              image: imagePath,
              sales: product.order_count || 0,
              revenue: parseFloat(product.price || 0) * (product.order_count || 0),
            }
          })
        }

        // Update low stock items
        if (data.stock_alerts) {
          lowStockItems.value = data.stock_alerts.map((alert: any) => ({
            id: alert.product?.id,
            name: alert.product?.name,
            stock: alert.product?.stock_quantity || 0,
          }))
        }

        // Update revenue by day
        if (data.revenue_by_day) {
          revenueByDay.value = data.revenue_by_day
        }

        lastUpdated.value = new Date()
      }
    } catch (err: any) {
      console.error('Failed to load dashboard data:', err)
      error.value = err.response?.data?.message || 'Failed to load dashboard data'
    } finally {
      isLoading.value = false
    }
  }

  const updatePeriod = (period: string) => {
    selectedPeriod.value = period
    loadDashboardData(true)
  }

  // Update specific stats when changes occur
  const updateOrderCount = (increment: number) => {
    stats.value.total_orders += increment
  }

  const updateRevenue = (amount: number) => {
    stats.value.total_revenue += amount
  }

  const updateOrderStatusCount = (oldStatus: string, newStatus: string) => {
    if (oldStatus && orderStatus.value[oldStatus as keyof OrderStatusDistribution] !== undefined) {
      orderStatus.value[oldStatus as keyof OrderStatusDistribution]--
    }
    if (newStatus && orderStatus.value[newStatus as keyof OrderStatusDistribution] !== undefined) {
      orderStatus.value[newStatus as keyof OrderStatusDistribution]++
    }
  }

  const updateProductCount = (increment: number) => {
    stats.value.total_products += increment
  }

  const updateStockAlert = (productId: number, stock: number) => {
    const index = lowStockItems.value.findIndex(item => item.id === productId)
    if (index >= 0) {
      lowStockItems.value[index].stock = stock
      if (stock > 0) {
        // Keep in list if still low
      } else {
        // Remove if out of stock (handled by backend)
      }
    }
  }

  const setConnectionStatus = (connected: boolean) => {
    isConnected.value = connected
  }

  const refresh = () => {
    loadDashboardData(true)
  }

  return {
    // State
    stats,
    orderStatus,
    recentOrders,
    bestSellingProducts,
    lowStockItems,
    revenueByDay,
    isLoading,
    error,
    lastUpdated,
    selectedPeriod,
    isConnected,

    // Computed
    totalRevenue,
    totalOrders,
    totalProducts,
    totalCustomers,
    newCustomersToday,
    hasLowStockAlerts,
    hasPendingOrders,

    // Methods
    loadDashboardData,
    updatePeriod,
    updateOrderCount,
    updateRevenue,
    updateOrderStatusCount,
    updateProductCount,
    updateStockAlert,
    setConnectionStatus,
    refresh,
  }
})
