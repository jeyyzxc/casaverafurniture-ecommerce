<template>
  <div class="dashboard-page">
    <!-- ═══════════════════════════════════════════════════
         PAGE HEADER
         ═══════════════════════════════════════════════════ -->
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

    <!-- ═══════════════════════════════════════════════════
         STATS CARDS
         ═══════════════════════════════════════════════════ -->
    <section class="stats-section">
      <div class="stats-grid">
        <div class="stat-card revenue">
          <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" y1="1" x2="12" y2="23"/>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">₱{{ formatPrice(totalRevenue) }}</div>
            <div class="stat-change positive">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                <polyline points="17 6 23 6 23 12"/>
              </svg>
              +12.5% from last period
            </div>
          </div>
        </div>

        <div class="stat-card orders">
          <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
              <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value">{{ totalOrders }}</div>
            <div class="stat-change positive">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                <polyline points="17 6 23 6 23 12"/>
              </svg>
              +8.2% from last period
            </div>
          </div>
        </div>

        <div class="stat-card products">
          <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2"/>
              <path d="M3 9h18M9 3v18"/>
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-label">Total Products</div>
            <div class="stat-value">{{ totalProducts }}</div>
            <div class="stat-change neutral">No change</div>
          </div>
        </div>

        <div class="stat-card customers">
          <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-label">Total Customers</div>
            <div class="stat-value">{{ totalCustomers }}</div>
            <div class="stat-change positive">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                <polyline points="17 6 23 6 23 12"/>
              </svg>
              +{{ newCustomersToday }} new today
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════
         ROW 1: Sales Overview + Recent Orders
         ═══════════════════════════════════════════════════ -->
    <section class="dashboard-row row-two-cols">
      <!-- Sales Overview Card -->
      <article class="dashboard-card chart-card clickable-card" @click="navigateToReports">
        <header class="card-header">
          <h3 class="card-title">Sales Overview</h3>
          <div class="card-actions" @click.stop>
            <button class="action-btn btn-export" @click.stop="exportSalesData" title="Export Sales Data">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
              </svg>
              Export
            </button>
          </div>
        </header>
        <div class="chart-container">
          <div class="chart-placeholder">
            <svg viewBox="0 0 400 150" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20,130 60,110 100,90 140,70 180,55 220,65 260,50 300,40 340,35 380,25" 
                        fill="rgba(201, 160, 80, 0.1)" stroke="#c9a050"/>
              <line x1="20" y1="130" x2="380" y2="130" stroke="#e5e7eb"/>
              <line x1="20" y1="15" x2="20" y2="130" stroke="#e5e7eb"/>
            </svg>
            <p class="chart-note">Sales chart visualization</p>
          </div>
        </div>
      </article>

      <!-- Recent Orders Card -->
      <article class="dashboard-card orders-card">
        <header class="card-header">
          <h3 class="card-title">Recent Orders</h3>
          <router-link to="/admin/orders" class="view-all-link">View All</router-link>
        </header>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="order in recentOrders" 
                :key="order.id" 
                class="clickable-row" 
                @click="viewOrder(order.id)"
              >
                <td class="order-id">#{{ order.id }}</td>
                <td class="customer">{{ order.customer }}</td>
                <td class="amount">₱{{ formatPrice(order.amount) }}</td>
                <td>
                  <span class="status-badge" :class="order.status.toLowerCase()">
                    {{ order.status }}
                  </span>
                </td>
                <td class="date">{{ formatDate(order.date) }}</td>
                <td>
                  <button 
                    class="action-icon-btn" 
                    @click.stop="viewOrder(order.id)" 
                    title="View Order"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>
    </section>

    <!-- ═══════════════════════════════════════════════════
         ROW 2: Order Status + Best Selling Products
         ═══════════════════════════════════════════════════ -->
    <section class="dashboard-row row-status-products">
      <!-- Order Status Card -->
      <article class="dashboard-card status-card">
        <header class="card-header">
          <h3 class="card-title">Order Status</h3>
        </header>
        <div class="status-summary">
          <div 
            class="status-item clickable-status" 
            @click="navigateToOrders('pending')"
            title="View Pending Orders"
          >
            <div class="status-indicator pending"></div>
            <div class="status-info">
              <div class="status-count">{{ orderStatus.pending }}</div>
              <div class="status-label">Pending</div>
            </div>
          </div>
          <div 
            class="status-item clickable-status" 
            @click="navigateToOrders('processing')"
            title="View Processing Orders"
          >
            <div class="status-indicator processing"></div>
            <div class="status-info">
              <div class="status-count">{{ orderStatus.processing }}</div>
              <div class="status-label">Processing</div>
            </div>
          </div>
          <div 
            class="status-item clickable-status" 
            @click="navigateToOrders('shipped')"
            title="View Shipped Orders"
          >
            <div class="status-indicator shipped"></div>
            <div class="status-info">
              <div class="status-count">{{ orderStatus.shipped }}</div>
              <div class="status-label">Shipped</div>
            </div>
          </div>
          <div 
            class="status-item clickable-status" 
            @click="navigateToOrders('delivered')"
            title="View Delivered Orders"
          >
            <div class="status-indicator delivered"></div>
            <div class="status-info">
              <div class="status-count">{{ orderStatus.delivered }}</div>
              <div class="status-label">Delivered</div>
            </div>
          </div>
        </div>
      </article>

      <!-- Best Selling Products Card -->
      <article class="dashboard-card products-card">
        <header class="card-header">
          <h3 class="card-title">Best Selling Products</h3>
          <router-link to="/admin/products" class="view-all-link">View All</router-link>
        </header>
        <div class="products-list">
          <div 
            v-for="(product, index) in bestSellingProducts" 
            :key="product.id" 
            class="product-item clickable-product"
            @click="navigateToProduct(product.id)"
            :title="`View ${product.name}`"
          >
            <div class="product-rank">{{ index + 1 }}</div>
            <img :src="product.image" :alt="product.name" class="product-thumb">
            <div class="product-info">
              <div class="product-name">{{ product.name }}</div>
              <div class="product-sales">{{ product.sales }} sold</div>
            </div>
            <div class="product-revenue">₱{{ formatPrice(product.revenue) }}</div>
          </div>
        </div>
      </article>
    </section>

    <!-- ═══════════════════════════════════════════════════
         ROW 3: Low Stock Alerts (Full Width)
         ═══════════════════════════════════════════════════ -->
    <section class="dashboard-row row-full-width">
      <!-- Low Stock Alerts Card -->
      <article class="dashboard-card alert-card">
        <header class="card-header">
          <h3 class="card-title">Low Stock Alerts</h3>
          <span class="alert-count">{{ lowStockItems.length }}</span>
        </header>
        <div class="alerts-grid">
          <div 
            v-for="item in lowStockItems" 
            :key="item.id" 
            class="alert-item clickable-alert"
            @click="navigateToInventory"
            :title="`Restock ${item.name}`"
          >
            <div class="alert-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
            </div>
            <div class="alert-content">
              <div class="alert-product">{{ item.name }}</div>
              <div class="alert-stock">Only {{ item.stock }} left in stock</div>
            </div>
            <router-link 
              to="/admin/inventory" 
              class="alert-action" 
              @click.stop
              title="Go to Inventory"
            >
              Restock
            </router-link>
          </div>
        </div>
      </article>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminStore } from '../../stores/admin'
import { dashboard } from '@/services/adminApi'
import { useRealtimeAdmin } from '@/composables/useRealtimeAdmin'

// ═══════════════════════════════════════════════════
// COMPOSABLES
// ═══════════════════════════════════════════════════
const router = useRouter()
const adminStore = useAdminStore()
const { startListening, stopListening } = useRealtimeAdmin()

// ═══════════════════════════════════════════════════
// STATE
// ═══════════════════════════════════════════════════
const selectedPeriod = ref<string>('month')
const currentAdmin = computed(() => adminStore.currentAdmin || { name: 'Admin User' })
const isLoading = ref(false)
const error = ref<string | null>(null)

// Dashboard data from API
const stats = ref({
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

const orderStatus = ref({
  pending: 0,
  processing: 0,
  shipped: 0,
  delivered: 0,
  cancelled: 0,
})

const recentOrders = ref<any[]>([])
const bestSellingProducts = ref<any[]>([])
const lowStockItems = ref<any[]>([])
const revenueByDay = ref<any[]>([])

// Computed properties for display
const totalRevenue = computed(() => stats.value.total_revenue)
const totalOrders = computed(() => stats.value.total_orders)
const totalProducts = computed(() => stats.value.total_products)
const totalCustomers = computed(() => stats.value.total_customers)
const newCustomersToday = computed(() => stats.value.new_customers)

// ═══════════════════════════════════════════════════
// METHODS
// ═══════════════════════════════════════════════════
const formatPrice = (price: number): string => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatDate = (date: Date): string => {
  return new Intl.DateTimeFormat('en-US', { 
    month: 'short', 
    day: 'numeric', 
    hour: '2-digit', 
    minute: '2-digit' 
  }).format(date)
}

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

const loadDashboardData = async () => {
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

      // Update recent orders
      if (data.recent_orders) {
        recentOrders.value = data.recent_orders.map((order: any) => ({
          id: order.order_number,
          customer: order.customer_name || `${order.user?.first_name || ''} ${order.user?.last_name || ''}`.trim() || 'Guest',
          amount: parseFloat(order.total || 0),
          status: order.status,
          date: new Date(order.created_at),
        }))
      }

      // Update best selling products
      if (data.top_products) {
        bestSellingProducts.value = data.top_products.map((product: any) => {
          // Ensure image is from /images/products/ directory
          let imagePath = product.primary_image || '/images/products/placeholder.png'
          // If image doesn't start with /images/products/, use placeholder or try to construct path
          if (!imagePath.startsWith('/images/products/')) {
            // Extract filename if it's a full path
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
    }
  } catch (err: any) {
    console.error('Failed to load dashboard data:', err)
    error.value = err.response?.data?.message || 'Failed to load dashboard data'
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

const navigateToInventory = (): void => {
  router.push('/admin/inventory')
}

// ═══════════════════════════════════════════════════
// LIFECYCLE
// ═══════════════════════════════════════════════════
onMounted(async () => {
  adminStore.init()
  await loadDashboardData()

  // Set up real-time listeners
  startListening()
  
  // Listen for real-time events
  window.addEventListener('realtime:admin:order:created', () => {
    loadDashboardData()
  })
  
  window.addEventListener('realtime:admin:order:status:updated', () => {
    loadDashboardData()
  })
  
  window.addEventListener('realtime:admin:stock:changed', () => {
    loadDashboardData()
  })
})

onUnmounted(() => {
  stopListening()
  
  // Remove event listeners
  window.removeEventListener('realtime:admin:order:created', loadDashboardData)
  window.removeEventListener('realtime:admin:order:status:updated', loadDashboardData)
  window.removeEventListener('realtime:admin:stock:changed', loadDashboardData)
})
</script>

<style scoped>
/* ═══════════════════════════════════════════════════
   CSS VARIABLES
   ═══════════════════════════════════════════════════ */
.dashboard-page {
  --gold: #c9a050;
  --gold-light: #e6c866;
  --dark: #1a1d29;
  --light: #f5f7fa;
  --white: #ffffff;
  --gray: #6b7280;
  --gray-light: #e5e7eb;
  --gray-lighter: #f3f4f6;
  --gray-lightest: #f9fafb;
  padding-top: 3.5rem;
  padding-left: 2rem;
  padding-right: 2rem;
  padding-bottom: 2rem;
  
  max-width: 100%;
  overflow-x: hidden;
}

/* ═══════════════════════════════════════════════════
   PAGE HEADER
   ═══════════════════════════════════════════════════ */
.page-header {
  margin-bottom: 1.5rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  flex-wrap: wrap;
}

.page-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--dark);
  margin: 0 0 0.25rem;
}

.page-subtitle {
  color: var(--gray);
  margin: 0;
  font-size: 0.9rem;
}

.period-select {
  padding: 0.5rem 1rem;
  border: 2px solid var(--gray-light);
  border-radius: 8px;
  background: var(--white);
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--dark);
  cursor: pointer;
  outline: none;
  transition: all 0.2s ease;
}

.period-select:hover,
.period-select:focus {
  border-color: var(--gold);
}

/* ═══════════════════════════════════════════════════
   STATS SECTION
   ═══════════════════════════════════════════════════ */
.stats-section {
  margin-bottom: 1.5rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

.stat-card {
  background: var(--white);
  border-radius: 16px;
  padding: 1.25rem;
  display: flex;
  gap: 1rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  border: 1px solid transparent;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
  border-color: var(--gray-light);
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-card.revenue .stat-icon { background: linear-gradient(135deg, #c9a050, #b8860b); color: white; }
.stat-card.orders .stat-icon { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; }
.stat-card.products .stat-icon { background: linear-gradient(135deg, #10b981, #059669); color: white; }
.stat-card.customers .stat-icon { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; }

.stat-icon svg {
  width: 24px;
  height: 24px;
}

.stat-content {
  flex: 1;
  min-width: 0;
}

.stat-label {
  font-size: 0.75rem;
  color: var(--gray);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.25rem;
  font-weight: 600;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--dark);
  margin-bottom: 0.25rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.stat-change {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  font-weight: 500;
}

.stat-change.positive { color: #10b981; }
.stat-change.negative { color: #ef4444; }
.stat-change.neutral { color: var(--gray); }

.stat-change svg {
  width: 12px;
  height: 12px;
}

/* ═══════════════════════════════════════════════════
   DASHBOARD ROWS
   ═══════════════════════════════════════════════════ */
.dashboard-row {
  display: grid;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.row-two-cols {
  grid-template-columns: 1fr 1fr;
}

.row-status-products {
  grid-template-columns: 320px 1fr;
}

.row-full-width {
  grid-template-columns: 1fr;
}

/* ═══════════════════════════════════════════════════
   DASHBOARD CARDS
   ═══════════════════════════════════════════════════ */
.dashboard-card {
  background: var(--white);
  border-radius: 16px;
  padding: 1.25rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  border: 1px solid transparent;
  min-width: 0;
}

.dashboard-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  border-color: var(--gray-light);
}

.clickable-card {
  cursor: pointer;
}

.clickable-card:hover {
  transform: translateY(-2px);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid var(--gray-lightest);
}

.card-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--dark);
  margin: 0;
}

.view-all-link {
  color: var(--gold);
  text-decoration: none;
  font-size: 0.8rem;
  font-weight: 600;
  transition: all 0.2s ease;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
}

.view-all-link:hover {
  color: #b8860b;
  background: rgba(201, 160, 80, 0.1);
}

.card-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 0.875rem;
  background: var(--white);
  border: 2px solid var(--gray-light);
  border-radius: 8px;
  color: var(--dark);
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-btn:hover {
  background: var(--gray-lightest);
  border-color: var(--gold);
  color: var(--gold);
}

.action-btn svg {
  width: 14px;
  height: 14px;
}

/* ═══════════════════════════════════════════════════
   CHART
   ═══════════════════════════════════════════════════ */
.chart-container {
  height: 120px;
}

.chart-placeholder {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--gray);
}

.chart-placeholder svg {
  width: 100%;
  max-height: 90px;
}

.chart-note {
  margin-top: 0.5rem;
  font-size: 0.8rem;
  color: var(--gray);
}

/* ═══════════════════════════════════════════════════
   TABLE
   ═══════════════════════════════════════════════════ */
.table-container {
  overflow-x: auto;
  overflow-y: hidden;
  margin: 0 -0.25rem;
  padding: 0 0.25rem;
}

.table-container::-webkit-scrollbar {
  display: none;
}

.table-container {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}

.data-table thead {
  background: var(--gray-lightest);
}

.data-table th {
  padding: 0.625rem 0.5rem;
  text-align: left;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  color: var(--gray);
  border-bottom: 2px solid var(--gray-light);
  white-space: nowrap;
}

.data-table td {
  padding: 0.625rem 0.5rem;
  border-bottom: 1px solid var(--gray-light);
  color: var(--dark);
  white-space: nowrap;
}

.data-table tbody tr {
  transition: all 0.2s ease;
}

.data-table tbody tr.clickable-row {
  cursor: pointer;
}

.data-table tbody tr.clickable-row:hover {
  background: var(--gray-lightest);
}

.order-id {
  font-weight: 600;
  color: var(--gold);
}

.customer {
  max-width: 100px;
  overflow: hidden;
  text-overflow: ellipsis;
}

.amount {
  font-weight: 600;
  color: var(--dark);
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.625rem;
  border-radius: 12px;
  font-size: 0.65rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.status-badge.pending { background: #fef3c7; color: #92400e; }
.status-badge.processing { background: #dbeafe; color: #1e40af; }
.status-badge.shipped { background: #e0e7ff; color: #3730a3; }
.status-badge.delivered { background: #d1fae5; color: #065f46; }

.action-icon-btn {
  background: none;
  border: none;
  color: var(--gray);
  cursor: pointer;
  padding: 0.375rem;
  border-radius: 6px;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-icon-btn:hover {
  background: var(--gray-lighter);
  color: var(--gold);
}

.action-icon-btn svg {
  width: 16px;
  height: 16px;
}

/* ═══════════════════════════════════════════════════
   STATUS SUMMARY
   ═══════════════════════════════════════════════════ */
.status-summary {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.status-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: var(--gray-lightest);
  border-radius: 12px;
  transition: all 0.2s ease;
}

.clickable-status {
  cursor: pointer;
}

.clickable-status:hover {
  background: var(--gray-lighter);
  transform: translateX(3px);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
}

.status-indicator {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-indicator.pending { background: #fbbf24; }
.status-indicator.processing { background: #3b82f6; }
.status-indicator.shipped { background: #8b5cf6; }
.status-indicator.delivered { background: #10b981; }

.status-count {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--dark);
}

.status-label {
  font-size: 0.8rem;
  color: var(--gray);
  font-weight: 500;
}

/* ═══════════════════════════════════════════════════
   PRODUCTS LIST
   ═══════════════════════════════════════════════════ */
.products-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.product-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  border-radius: 12px;
  transition: all 0.2s ease;
  background: var(--gray-lightest);
}

.product-item.clickable-product {
  cursor: pointer;
}

.product-item.clickable-product:hover {
  background: var(--gray-lighter);
  transform: translateX(3px);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
}

.product-rank {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--gold);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.85rem;
  flex-shrink: 0;
}

.product-thumb {
  width: 48px;
  height: 48px;
  object-fit: cover;
  border-radius: 8px;
  flex-shrink: 0;
  border: 2px solid var(--gray-light);
}

.product-info {
  flex: 1;
  min-width: 0;
}

.product-name {
  font-weight: 600;
  color: var(--dark);
  margin-bottom: 0.125rem;
  font-size: 0.9rem;
}

.product-sales {
  font-size: 0.8rem;
  color: var(--gray);
}

.product-revenue {
  font-weight: 700;
  color: var(--gold);
  font-size: 0.9rem;
  flex-shrink: 0;
  text-align: right;
}

/* ═══════════════════════════════════════════════════
   ALERTS
   ═══════════════════════════════════════════════════ */
.alert-card {
  border-left: 4px solid #f59e0b;
}

.alert-count {
  background: #f59e0b;
  color: white;
  padding: 0.25rem 0.625rem;
  border-radius: 10px;
  font-size: 0.8rem;
  font-weight: 700;
}

.alerts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 0.75rem;
}

.alert-item {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.875rem;
  background: #fffbeb;
  border-radius: 12px;
  border: 1px solid #fef3c7;
  transition: all 0.2s ease;
}

.alert-item.clickable-alert {
  cursor: pointer;
}

.alert-item.clickable-alert:hover {
  background: #fef3c7;
  border-color: #fbbf24;
  transform: translateX(3px);
  box-shadow: 0 2px 6px rgba(251, 191, 36, 0.2);
}

.alert-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #fef3c7;
  color: #f59e0b;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.alert-icon svg {
  width: 20px;
  height: 20px;
}

.alert-content {
  flex: 1;
  min-width: 0;
}

.alert-product {
  font-weight: 600;
  color: var(--dark);
  margin-bottom: 0.125rem;
  font-size: 0.9rem;
}

.alert-stock {
  font-size: 0.8rem;
  color: #92400e;
  font-weight: 500;
}

.alert-action {
  color: var(--gold);
  text-decoration: none;
  font-weight: 600;
  font-size: 0.85rem;
  transition: all 0.2s ease;
  padding: 0.375rem 0.625rem;
  border-radius: 6px;
  flex-shrink: 0;
}

.alert-action:hover {
  color: #b8860b;
  background: rgba(201, 160, 80, 0.15);
}

/* ═══════════════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════════════ */
@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .row-two-cols {
    grid-template-columns: 1fr;
  }
  
  .row-status-products {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }

  .header-content {
    flex-direction: column;
    gap: 0.75rem;
  }

  .page-title {
    font-size: 1.5rem;
  }

  .status-summary {
    grid-template-columns: 1fr;
  }
  
  .alerts-grid {
    grid-template-columns: 1fr;
  }
}
</style>
