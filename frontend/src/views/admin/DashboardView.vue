<template>
  <div class="dashboard-page">
    <header class="page-header">
      <div class="header-content">
        <div>
          <h1 class="page-title">Dashboard</h1>
          <p class="page-subtitle">Welcome back, {{ currentAdmin.name }}. Here's what's happening today.</p>
        </div>
        <div class="header-actions">
          <select v-model="selectedPeriod" class="period-select" @change="handlePeriodChange">
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="year">This Year</option>
          </select>
        </div>
      </div>
    </header>

    <section class="stats-section">
      <div class="stats-grid">
        <StatCard
          label="Total Revenue"
          :value="'₱' + formatPrice(totalRevenue)"
          type="revenue"
          change="+12.5% from last period"
          changeType="positive"
          @click="navigateToReports"
        >
          <template #icon>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" y1="1" x2="12" y2="23"/>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
          </template>
        </StatCard>

        <StatCard
          label="Total Orders"
          :value="totalOrders"
          type="orders"
          change="+8.2% from last period"
          changeType="positive"
          @click="navigateToOrders()"
        >
          <template #icon>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
              <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
            </svg>
          </template>
        </StatCard>

        <StatCard
          label="Total Products"
          :value="totalProducts"
          type="products"
          change="No change"
          changeType="neutral"
          @click="router.push('/admin/products')"
        >
          <template #icon>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2"/>
              <path d="M3 9h18M9 3v18"/>
            </svg>
          </template>
        </StatCard>

        <StatCard
          label="Total Customers"
          :value="totalCustomers"
          type="customers"
          :change="'+' + newCustomersToday + ' new today'"
          changeType="positive"
          @click="router.push('/admin/users')"
        >
          <template #icon>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </template>
        </StatCard>
      </div>
    </section>

    <div class="dashboard-grid">
      <DashboardChart
        title="Sales Overview"
        :data="revenueByDay"
        showExport
        @export="exportSalesData"
        class="span-3"
      />

      <OrderStatusDistribution
        :distribution="orderStatus"
        @filter-status="navigateToOrders"
        class="span-2"
      />

      <RecentOrdersTable
        :orders="recentOrders"
        @view-order="viewOrder"
        class="span-3"
      />

      <BestSellingProducts
        :products="bestSellingProducts"
        @view-product="navigateToProduct"
        class="span-2"
      />

      <LowStockAlerts
        :items="lowStockItems"
        class="span-5"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminAuthStore } from '@/stores/adminAuth'
import { getAdminAccessToken } from '@/utils/tokenManager'
import { dashboard } from '@/services/adminApi'
import { useRealtimeAdmin } from '@/composables/useRealtimeAdmin'

import StatCard from '@/components/admin/dashboard/StatCard.vue'
import DashboardChart from '@/components/admin/dashboard/DashboardChart.vue'
import RecentOrdersTable from '@/components/admin/dashboard/RecentOrdersTable.vue'
import BestSellingProducts from '@/components/admin/dashboard/BestSellingProducts.vue'
import OrderStatusDistribution from '@/components/admin/dashboard/OrderStatusDistribution.vue'
import LowStockAlerts from '@/components/admin/dashboard/LowStockAlerts.vue'

const router = useRouter()
const adminStore = useAdminAuthStore()
const { startListening, stopListening } = useRealtimeAdmin()

const selectedPeriod = ref<string>('month')
const currentAdmin = computed(() => {
  if (adminStore.admin) {
    return { name: adminStore.admin.full_name || `${adminStore.admin.first_name} ${adminStore.admin.last_name}` }
  }
  return { name: 'Admin User' }
})
const isLoading = ref(false)
const error = ref<string | null>(null)

interface DashboardStats {
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

interface OrderStatusDist {
  pending: number
  processing: number
  shipped: number
  delivered: number
  cancelled: number
}

interface RecentOrder {
  id: string
  customer: string
  amount: number
  status: string
  date: Date
}

interface BestSellingProduct {
  id: number
  name: string
  image: string
  sales: number
  revenue: number
}

interface LowStockItem {
  id: number
  name: string
  stock: number
  threshold: number
}

interface RevenueByDay {
  date: string
  revenue: string | number
}

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

const orderStatus = ref<OrderStatusDist>({
  pending: 0,
  processing: 0,
  shipped: 0,
  delivered: 0,
  cancelled: 0,
})

const recentOrders = ref<RecentOrder[]>([])
const bestSellingProducts = ref<BestSellingProduct[]>([])
const lowStockItems = ref<LowStockItem[]>([])
const revenueByDay = ref<RevenueByDay[]>([])

const totalRevenue = computed(() => stats.value.total_revenue)
const totalOrders = computed(() => stats.value.total_orders)
const totalProducts = computed(() => stats.value.total_products)
const totalCustomers = computed(() => stats.value.total_customers)
const newCustomersToday = computed(() => stats.value.new_customers)

const formatPrice = (price: number): string => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const getDateRange = () => {
  const now = new Date()
  let startDate: Date
  const endDate = now

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

const loadDashboardData = async () => {
  isLoading.value = true
  error.value = null

  try {
    const dateRange = getDateRange()
    const response = await dashboard.getStats(dateRange.start_date, dateRange.end_date)

    if (response.data.success) {
      const data = response.data.data

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

      if (data.orders_by_status) {
        orderStatus.value = {
          pending: data.orders_by_status.pending || 0,
          processing: data.orders_by_status.processing || 0,
          shipped: data.orders_by_status.shipped || 0,
          delivered: data.orders_by_status.delivered || 0,
          cancelled: data.orders_by_status.cancelled || 0,
        }
      }

      if (data.recent_orders) {
        recentOrders.value = data.recent_orders.map((order: { order_number: string; customer_name?: string; user?: { first_name?: string; last_name?: string }; total: string | number; status: string; created_at: string }) => ({
          id: order.order_number,
          customer: order.customer_name || `${order.user?.first_name || ''} ${order.user?.last_name || ''}`.trim() || 'Guest',
          amount: parseFloat(order.total.toString() || '0'),
          status: order.status,
          date: new Date(order.created_at),
        }))
      }

      if (data.top_products) {
        bestSellingProducts.value = data.top_products.map((product: { id: number; name: string; primary_image?: string; order_count?: number; price: string | number }) => {
          return {
            id: product.id,
            name: product.name,
            image: product.primary_image || '/images/products/placeholder.png',
            sales: product.order_count || 0,
            revenue: parseFloat(product.price.toString() || '0') * (product.order_count || 0),
          }
        })
      }

      if (data.stock_alerts) {
        lowStockItems.value = data.stock_alerts.map((alert: {
          product?: { id: number; name: string; stock_quantity: number; low_stock_threshold: number }
          product_id?: number
          product_sku?: string
          product_stock?: number
        }) => ({
          id: alert.product?.id || alert.product_id || 0,
          name: alert.product?.name || alert.product_sku || 'Unknown Product',
          stock: alert.product?.stock_quantity ?? alert.product_stock ?? 0,
          threshold: alert.product?.low_stock_threshold || 5
        }))
      }

      if (data.revenue_by_day) {
        revenueByDay.value = data.revenue_by_day
      }
    }
  } catch (err: unknown) {
    console.error('Failed to load dashboard data:', err)
  } finally {
    isLoading.value = false
  }
}

const handlePeriodChange = () => {
  loadDashboardData()
}

const viewOrder = (orderId: string): void => {
  router.push(`/admin/orders/${orderId}`)
}

const exportSalesData = (): void => {
  console.log('Exporting sales data for period:', selectedPeriod.value)
  alert('Sales data export functionality will be implemented soon!')
}

const navigateToReports = (): void => {
  router.push('/admin/reports')
}

const navigateToOrders = (status?: string): void => {
  if (status) {
    router.push({ path: '/admin/orders', query: { status } })
  } else {
    router.push('/admin/orders')
  }
}

const navigateToProduct = (productId: number): void => {
  router.push(`/admin/products/${productId}`)
}

const handleDashboardReload = () => {
  loadDashboardData()
}

onMounted(async () => {
  if (!adminStore.admin || !getAdminAccessToken()) {
    await adminStore.fetchAdmin()
  }

  await loadDashboardData()

  startListening()

  window.addEventListener('realtime:admin:order:created', handleDashboardReload)
  window.addEventListener('realtime:admin:order:status:updated', handleDashboardReload)
  window.addEventListener('realtime:admin:stock:changed', handleDashboardReload)
  window.addEventListener('realtime:admin:notification:created', handleDashboardReload)
  window.addEventListener('realtime:admin:user:login', handleDashboardReload)
  window.addEventListener('realtime:admin:user:registered', handleDashboardReload)
  window.addEventListener('realtime:admin:payment:received', handleDashboardReload)
  window.addEventListener('realtime:admin:promotion:created', handleDashboardReload)
  window.addEventListener('realtime:admin:product:created', handleDashboardReload)
  window.addEventListener('realtime:admin:product:updated', handleDashboardReload)
})

onUnmounted(() => {
  stopListening()

  window.removeEventListener('realtime:admin:order:created', handleDashboardReload)
  window.removeEventListener('realtime:admin:order:status:updated', handleDashboardReload)
  window.removeEventListener('realtime:admin:stock:changed', handleDashboardReload)
  window.removeEventListener('realtime:admin:notification:created', handleDashboardReload)
  window.removeEventListener('realtime:admin:user:login', handleDashboardReload)
  window.removeEventListener('realtime:admin:user:registered', handleDashboardReload)
  window.removeEventListener('realtime:admin:payment:received', handleDashboardReload)
  window.removeEventListener('realtime:admin:promotion:created', handleDashboardReload)
  window.removeEventListener('realtime:admin:product:created', handleDashboardReload)
  window.removeEventListener('realtime:admin:product:updated', handleDashboardReload)
})
</script>

<style scoped>
.dashboard-page {
  padding: 2rem;
  max-width: 1600px;
  margin: 0 auto;
  background: #f8fafc;
  min-height: 100vh;
}

.page-header {
  margin-bottom: 2rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.page-title {
  font-size: 2rem;
  font-weight: 800;
  color: #1e293b;
  margin: 0;
}

.page-subtitle {
  color: #64748b;
  margin: 0.25rem 0 0;
}

.period-select {
  padding: 0.625rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  background: white;
  font-weight: 600;
  color: #1e293b;
  cursor: pointer;
  outline: none;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.stats-section {
  margin-bottom: 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 1.5rem;
}

.span-5 {
  grid-column: span 5;
}

.span-4 {
  grid-column: span 4;
}

.span-3 {
  grid-column: span 3;
}

.span-2 {
  grid-column: span 2;
}

.span-1 {
  grid-column: span 1;
}

@media (max-width: 1400px) {
  .dashboard-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .span-5, .span-4, .span-3, .span-2, .span-1 {
    grid-column: span 2;
  }
}

@media (max-width: 768px) {
  .dashboard-grid {
    grid-template-columns: 1fr;
  }

  .span-5, .span-4, .span-3, .span-2, .span-1 {
    grid-column: span 1;
  }

  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
}
</style>
