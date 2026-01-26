<template>
  <div class="admin-reports-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Reports & Analytics</h1>
        <p class="page-subtitle">Comprehensive insights into sales, orders, products, and customer activity.</p>
      </div>
      <div class="header-actions">
        <div class="filter-group">
          <select v-model="reportType" class="filter-select" @change="onReportTypeChange">
            <option value="summary">Summary</option>
            <option value="sales">Sales Report</option>
            <option value="orders">Order Report</option>
            <option value="products">Product Performance</option>
            <option value="users">User Activity</option>
          </select>
          <select v-if="reportType === 'sales'" v-model="groupBy" class="filter-select">
            <option value="day">By Day</option>
            <option value="week">By Week</option>
            <option value="month">By Month</option>
          </select>
        </div>
        <div class="date-group">
          <input type="date" v-model="startDate" class="date-input" />
          <span class="date-separator">to</span>
          <input type="date" v-model="endDate" class="date-input" />
        </div>
        <div class="action-buttons">
          <button 
            class="btn-primary" 
            @click="generateReport" 
            :disabled="isLoading || !startDate || !endDate"
            title="Generate report for selected date range"
          >
            <span v-if="!isLoading">Generate Report</span>
            <span v-else class="loading-spinner">Loading...</span>
          </button>
          <button 
            class="btn-secondary" 
            @click="resetFilters"
            title="Reset all filters to default"
          >
            Reset
          </button>
          <button 
            class="btn-export" 
            @click="exportReport" 
            :disabled="!hasData || isLoading"
            title="Export current report to CSV"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export CSV
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading report data...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <div class="error-icon">⚠️</div>
      <h3>Failed to Load Report</h3>
      <p>{{ error }}</p>
      <button class="btn-primary" @click="generateReport">Try Again</button>
    </div>

    <!-- Summary Report -->
    <div v-else-if="reportType === 'summary' && summaryData" class="reports-content">
      <div class="report-summary">
        <div class="summary-card revenue">
          <div class="card-icon">💰</div>
          <div class="card-content">
            <div class="summary-label">Total Revenue</div>
            <div class="summary-value">₱{{ formatPrice(summaryData.stats.total_revenue) }}</div>
            <div class="summary-period">{{ formatPeriod() }}</div>
          </div>
        </div>
        <div class="summary-card orders">
          <div class="card-icon">📦</div>
          <div class="card-content">
            <div class="summary-label">Total Orders</div>
            <div class="summary-value">{{ summaryData.stats.total_orders }}</div>
            <div class="summary-subtext">{{ summaryData.stats.paid_orders }} paid</div>
          </div>
        </div>
        <div class="summary-card avg-order">
          <div class="card-icon">📊</div>
          <div class="card-content">
            <div class="summary-label">Average Order Value</div>
            <div class="summary-value">₱{{ formatPrice(summaryData.stats.average_order_value) }}</div>
          </div>
        </div>
        <div class="summary-card customers">
          <div class="card-icon">👥</div>
          <div class="card-content">
            <div class="summary-label">Total Customers</div>
            <div class="summary-value">{{ summaryData.stats.total_customers }}</div>
            <div class="summary-subtext">{{ summaryData.stats.new_customers }} new</div>
          </div>
        </div>
        <div class="summary-card products">
          <div class="card-icon">🛍️</div>
          <div class="card-content">
            <div class="summary-label">Active Products</div>
            <div class="summary-value">{{ summaryData.stats.total_products }}</div>
          </div>
        </div>
        <div class="summary-card top-product">
          <div class="card-icon">⭐</div>
          <div class="card-content">
            <div class="summary-label">Top Product</div>
            <div class="summary-value">{{ summaryData.top_product }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sales Report -->
    <div v-else-if="reportType === 'sales' && salesData" class="reports-content">
      <div class="report-summary">
        <div class="summary-card">
          <div class="summary-label">Total Revenue</div>
          <div class="summary-value">₱{{ formatPrice(salesData.totals.total_revenue) }}</div>
        </div>
        <div class="summary-card">
          <div class="summary-label">Total Orders</div>
          <div class="summary-value">{{ salesData.totals.total_orders }}</div>
        </div>
        <div class="summary-card">
          <div class="summary-label">Average Order Value</div>
          <div class="summary-value">₱{{ formatPrice(salesData.totals.average_order_value) }}</div>
        </div>
      </div>

      <div class="chart-section">
        <div class="chart-card">
          <h3>Sales Trend</h3>
          <div class="chart-container">
            <div class="chart-placeholder">
              <div class="chart-bars">
                <div
                  v-for="(item, index) in salesData.sales_data"
                  :key="index"
                  class="chart-bar"
                  :style="{ height: `${getBarHeight(item.revenue, salesData.totals.total_revenue)}%` }"
                  :title="`${item.period}: ₱${formatPrice(item.revenue)}`"
                >
                  <div class="bar-value">₱{{ formatPrice(item.revenue) }}</div>
                </div>
              </div>
            </div>
            <div class="chart-legend">
              <div v-for="(item, index) in salesData.sales_data" :key="index" class="legend-item">
                <span class="legend-period">{{ formatPeriodLabel(item.period) }}</span>
                <span class="legend-value">₱{{ formatPrice(item.revenue) }}</span>
                <span class="legend-orders">{{ item.orders }} orders</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Orders Report -->
    <div v-else-if="reportType === 'orders' && ordersData" class="reports-content">
      <div class="report-summary">
        <div class="summary-card">
          <div class="summary-label">Total Orders</div>
          <div class="summary-value">{{ ordersData.total_orders }}</div>
        </div>
        <div class="summary-card">
          <div class="summary-label">By Status</div>
          <div class="status-breakdown">
            <div v-for="(count, status) in ordersData.orders_by_status" :key="status" class="status-item">
              <span class="status-label">{{ formatStatus(status) }}</span>
              <span class="status-count">{{ count }}</span>
            </div>
          </div>
        </div>
        <div class="summary-card">
          <div class="summary-label">By Payment Status</div>
          <div class="status-breakdown">
            <div v-for="(count, status) in ordersData.orders_by_payment_status" :key="status" class="status-item">
              <span class="status-label">{{ formatPaymentStatus(status) }}</span>
              <span class="status-count">{{ count }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="table-section">
        <div class="table-card">
          <h3>Recent Orders</h3>
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>Order #</th>
                  <th>Customer</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Payment</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in ordersData.recent_orders" :key="order.id">
                  <td>{{ order.order_number }}</td>
                  <td>{{ order.customer_name || order.user?.first_name + ' ' + order.user?.last_name || 'Guest' }}</td>
                  <td>₱{{ formatPrice(order.total) }}</td>
                  <td><span class="status-badge" :class="`status-${order.status}`">{{ formatStatus(order.status) }}</span></td>
                  <td><span class="payment-badge" :class="`payment-${order.payment_status}`">{{ formatPaymentStatus(order.payment_status) }}</span></td>
                  <td>{{ formatDate(order.created_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Products Report -->
    <div v-else-if="reportType === 'products' && productsData" class="reports-content">
      <div class="table-section">
        <div class="table-card">
          <h3>Top Selling Products</h3>
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>Product</th>
                  <th>SKU</th>
                  <th>Quantity Sold</th>
                  <th>Revenue</th>
                  <th>Orders</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="product in productsData.top_products" :key="product.id">
                  <td><strong>{{ product.name }}</strong></td>
                  <td>{{ product.sku }}</td>
                  <td>{{ product.total_quantity_sold }}</td>
                  <td>₱{{ formatPrice(product.total_revenue) }}</td>
                  <td>{{ product.order_count }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Users Report -->
    <div v-else-if="reportType === 'users' && usersData" class="reports-content">
      <div class="report-summary">
        <div class="summary-card">
          <div class="summary-label">New Users</div>
          <div class="summary-value">{{ usersData.total_new_users }}</div>
          <div class="summary-period">{{ formatPeriod() }}</div>
        </div>
      </div>

      <div class="table-section">
        <div class="table-card">
          <h3>Top Customers by Revenue</h3>
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>Customer</th>
                  <th>Email</th>
                  <th>Orders</th>
                  <th>Total Spent</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="customer in usersData.top_customers" :key="customer.id">
                  <td><strong>{{ customer.first_name }} {{ customer.last_name }}</strong></td>
                  <td>{{ customer.email }}</td>
                  <td>{{ customer.order_count }}</td>
                  <td>₱{{ formatPrice(customer.total_spent) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!isLoading && !error" class="empty-state">
      <div class="empty-icon">📊</div>
      <h3>No Data Available</h3>
      <p>Select a report type and date range, then click "Generate Report" to view analytics.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { reports as reportsApi } from '@/services/adminApi'
import { useNotification } from '@/composables/useNotification'

const { success: originalSuccess, error: originalError, info: originalInfo } = useNotification()

// Notification deduplication: Track shown notifications to prevent duplicates
interface NotificationSignature {
  title: string
  message: string
  timestamp: number
}

const shownNotifications = ref<Map<string, NotificationSignature>>(new Map())
const NOTIFICATION_COOLDOWN = 5000 // 5 seconds - don't show same notification again within this time

// Create a unique key for a notification
const getNotificationKey = (title: string, message: string): string => {
  return `${title}:${message}`
}

// Check if notification was shown recently
const wasNotificationShownRecently = (title: string, message: string): boolean => {
  const key = getNotificationKey(title, message)
  const existing = shownNotifications.value.get(key)
  
  if (!existing) return false
  
  const timeSinceShown = Date.now() - existing.timestamp
  return timeSinceShown < NOTIFICATION_COOLDOWN
}

// Track a notification as shown
const trackNotification = (title: string, message: string): void => {
  const key = getNotificationKey(title, message)
  shownNotifications.value.set(key, {
    title,
    message,
    timestamp: Date.now()
  })
  
  // Clean up old entries (older than cooldown period)
  setTimeout(() => {
    shownNotifications.value.delete(key)
  }, NOTIFICATION_COOLDOWN + 1000) // Add 1 second buffer
}

// Wrapper functions that check for duplicates before showing
const success = (title: string, message?: string) => {
  const msg = message || ''
  if (wasNotificationShownRecently(title, msg)) {
    return // Don't show duplicate notification
  }
  trackNotification(title, msg)
  return originalSuccess(title, msg)
}

const showError = (title: string, message?: string) => {
  const msg = message || ''
  if (wasNotificationShownRecently(title, msg)) {
    return // Don't show duplicate notification
  }
  trackNotification(title, msg)
  return originalError(title, msg)
}

const info = (title: string, message?: string) => {
  const msg = message || ''
  if (wasNotificationShownRecently(title, msg)) {
    return // Don't show duplicate notification
  }
  trackNotification(title, msg)
  return originalInfo(title, msg)
}

const reportType = ref('summary')
const groupBy = ref<'day' | 'week' | 'month'>('day')
const startDate = ref('')
const endDate = ref('')
const isLoading = ref(false)
const error = ref<string | null>(null)

const summaryData = ref<any>(null)
const salesData = ref<any>(null)
const ordersData = ref<any>(null)
const productsData = ref<any>(null)
const usersData = ref<any>(null)

const hasData = computed(() => {
  return summaryData.value || salesData.value || ordersData.value || productsData.value || usersData.value
})

// Initialize date range (current month)
const initializeDates = () => {
  const now = new Date()
  const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
  startDate.value = firstDay.toISOString().split('T')[0]
  endDate.value = now.toISOString().split('T')[0]
}

const formatPrice = (price: number): string => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const formatPeriod = (): string => {
  if (!startDate.value || !endDate.value) return ''
  return `${formatDate(startDate.value)} - ${formatDate(endDate.value)}`
}

const formatPeriodLabel = (period: string): string => {
  if (groupBy.value === 'month') {
    return new Date(period + '-01').toLocaleDateString('en-PH', { month: 'short', year: 'numeric' })
  }
  return formatDate(period)
}

const formatStatus = (status: string): string => {
  return status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ')
}

const formatPaymentStatus = (status: string): string => {
  return status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ')
}

const getBarHeight = (value: number, max: number): number => {
  if (max === 0) return 0
  return Math.min((value / max) * 100, 100)
}

const onReportTypeChange = () => {
  // Clear previous data when changing report type
  summaryData.value = null
  salesData.value = null
  ordersData.value = null
  productsData.value = null
  usersData.value = null
  error.value = null
}

const generateReport = async () => {
  if (!startDate.value || !endDate.value) {
    error.value = 'Please select both start and end dates.'
    showError('Invalid Date Range', 'Please select both start and end dates to generate the report.')
    return
  }

  isLoading.value = true
  error.value = null

  try {
    const params: any = {
      start_date: startDate.value,
      end_date: endDate.value,
    }

    let reportName = ''

    if (reportType.value === 'sales') {
      params.group_by = groupBy.value
      reportName = 'Sales Report'
      const response = await reportsApi.sales(params)
      if (response.data.success) {
        salesData.value = response.data.data
        summaryData.value = null
        ordersData.value = null
        productsData.value = null
        usersData.value = null
        success('Report Generated', `${reportName} has been generated successfully for the selected period.`)
      } else {
        throw new Error(response.data.message || 'Failed to load sales report')
      }
    } else if (reportType.value === 'orders') {
      reportName = 'Order Report'
      const response = await reportsApi.orders(params)
      if (response.data.success) {
        ordersData.value = response.data.data
        summaryData.value = null
        salesData.value = null
        productsData.value = null
        usersData.value = null
        success('Report Generated', `${reportName} has been generated successfully for the selected period.`)
      } else {
        throw new Error(response.data.message || 'Failed to load orders report')
      }
    } else if (reportType.value === 'products') {
      reportName = 'Product Performance Report'
      const response = await reportsApi.products(params)
      if (response.data.success) {
        productsData.value = response.data.data
        summaryData.value = null
        salesData.value = null
        ordersData.value = null
        usersData.value = null
        success('Report Generated', `${reportName} has been generated successfully for the selected period.`)
      } else {
        throw new Error(response.data.message || 'Failed to load products report')
      }
    } else if (reportType.value === 'users') {
      reportName = 'User Activity Report'
      const response = await reportsApi.users(params)
      if (response.data.success) {
        usersData.value = response.data.data
        summaryData.value = null
        salesData.value = null
        ordersData.value = null
        productsData.value = null
        success('Report Generated', `${reportName} has been generated successfully for the selected period.`)
      } else {
        throw new Error(response.data.message || 'Failed to load users report')
      }
    } else {
      // Summary
      reportName = 'Summary Report'
      const response = await reportsApi.summary(params)
      if (response.data.success) {
        summaryData.value = response.data.data
        salesData.value = null
        ordersData.value = null
        productsData.value = null
        usersData.value = null
        success('Report Generated', `${reportName} has been generated successfully for the selected period.`)
      } else {
        throw new Error(response.data.message || 'Failed to load summary report')
      }
    }
  } catch (err: any) {
    console.error('Failed to generate report:', err)
    const errorMessage = err.response?.data?.message || err.message || 'Failed to generate report. Please try again.'
    error.value = errorMessage
    showError('Failed to Generate Report', errorMessage)
  } finally {
    isLoading.value = false
  }
}

const resetFilters = () => {
  initializeDates()
  reportType.value = 'summary'
  groupBy.value = 'day'
  onReportTypeChange()
  info('Filters Reset', 'All filters have been reset to default values.')
}

// Export report to CSV
const exportToCSV = (data: any[], filename: string) => {
  try {
    if (!data || data.length === 0) {
      showError('Export Failed', 'No data available to export.')
      return
    }

    // Get headers from first object
    const headers = Object.keys(data[0])
    
    // Create CSV content
    const csvContent = [
      headers.join(','),
      ...data.map(row => 
        headers.map(header => {
          const value = row[header]
          // Handle values with commas, quotes, or newlines
          if (value === null || value === undefined) return ''
          const stringValue = String(value).replace(/"/g, '""')
          return `"${stringValue}"`
        }).join(',')
      )
    ].join('\n')

    // Create blob and download
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    link.setAttribute('href', url)
    link.setAttribute('download', `${filename}_${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)

    return true
  } catch (err: any) {
    console.error('Export failed:', err)
    showError('Export Failed', err.message || 'Failed to export report. Please try again.')
    return false
  }
}

const exportReport = () => {
  if (!hasData.value) {
    showError('Export Failed', 'No report data available. Please generate a report first.')
    return
  }

  try {
    let exportData: any[] = []
    let filename = ''

    if (reportType.value === 'summary' && summaryData.value) {
      // Export summary as key-value pairs
      exportData = [
        { Metric: 'Total Revenue', Value: `₱${formatPrice(summaryData.value.stats.total_revenue)}` },
        { Metric: 'Total Orders', Value: summaryData.value.stats.total_orders },
        { Metric: 'Paid Orders', Value: summaryData.value.stats.paid_orders },
        { Metric: 'Average Order Value', Value: `₱${formatPrice(summaryData.value.stats.average_order_value)}` },
        { Metric: 'Total Customers', Value: summaryData.value.stats.total_customers },
        { Metric: 'New Customers', Value: summaryData.value.stats.new_customers },
        { Metric: 'Total Products', Value: summaryData.value.stats.total_products },
        { Metric: 'Top Product', Value: summaryData.value.top_product },
      ]
      filename = 'summary_report'
    } else if (reportType.value === 'sales' && salesData.value) {
      exportData = salesData.value.sales_data.map((item: any) => ({
        Period: formatPeriodLabel(item.period),
        Revenue: `₱${formatPrice(item.revenue)}`,
        Orders: item.orders,
        'Average Order Value': `₱${formatPrice(item.average_order_value)}`,
      }))
      filename = 'sales_report'
    } else if (reportType.value === 'orders' && ordersData.value) {
      exportData = ordersData.value.recent_orders.map((order: any) => ({
        'Order Number': order.order_number,
        Customer: order.customer_name || `${order.user?.first_name || ''} ${order.user?.last_name || ''}`.trim() || 'Guest',
        Total: `₱${formatPrice(order.total)}`,
        Status: formatStatus(order.status),
        'Payment Status': formatPaymentStatus(order.payment_status),
        Date: formatDate(order.created_at),
      }))
      filename = 'orders_report'
    } else if (reportType.value === 'products' && productsData.value) {
      exportData = productsData.value.top_products.map((product: any) => ({
        Product: product.name,
        SKU: product.sku,
        'Quantity Sold': product.total_quantity_sold,
        Revenue: `₱${formatPrice(product.total_revenue)}`,
        Orders: product.order_count,
      }))
      filename = 'products_report'
    } else if (reportType.value === 'users' && usersData.value) {
      exportData = usersData.value.top_customers.map((customer: any) => ({
        'First Name': customer.first_name,
        'Last Name': customer.last_name,
        Email: customer.email,
        Orders: customer.order_count,
        'Total Spent': `₱${formatPrice(customer.total_spent)}`,
      }))
      filename = 'users_report'
    }

    if (exportData.length > 0) {
      const exportSuccess = exportToCSV(exportData, filename)
      if (exportSuccess) {
        success('Export Successful', `Report has been exported as ${filename}.csv`)
      }
    } else {
      showError('Export Failed', 'No data available to export for this report type.')
    }
  } catch (err: any) {
    console.error('Export failed:', err)
    showError('Export Failed', err.message || 'Failed to export report. Please try again.')
  }
}

onMounted(async () => {
  initializeDates()
  // Auto-generate report on mount, but don't show error if it fails
  try {
    await generateReport()
  } catch (err) {
    // Silently fail on initial load - user can manually generate
    console.log('Initial report generation skipped')
  }
})
</script>

<style scoped>
.admin-reports-page {
  --gold: #c9a050;
  --dark: #1a1d29;
  --light: #f5f7fa;
  --white: #ffffff;
  --gray: #6b7280;
  padding-top: 3.5rem;
  padding-left: 2rem;
  padding-right: 2rem;
  padding-bottom: 2rem;
  min-height: 100vh;
  background: var(--light);
}

.page-header {
  margin-bottom: 2rem;
}

.page-title {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  font-weight: 700;
  color: var(--dark);
  margin: 0 0 0.5rem;
}

.page-subtitle {
  color: #374151;
  font-size: 0.95rem;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
  margin-top: 1.5rem;
  padding: 1.5rem;
  background: var(--white);
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.filter-group {
  display: flex;
  gap: 0.75rem;
}

.filter-select,
.date-input {
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
  background: var(--white);
  color: var(--dark);
  transition: all 0.3s ease;
}

.filter-select:focus,
.date-input:focus {
  outline: none;
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(201, 160, 80, 0.1);
}

.date-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.date-separator {
  color: var(--gray);
  font-size: 0.9rem;
}

.action-buttons {
  display: flex;
  gap: 0.75rem;
  margin-left: auto;
}

.btn-primary,
.btn-secondary,
.btn-export {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
}

.btn-primary {
  background: var(--gold);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #b8860b;
  transform: translateY(-1px);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.btn-primary:disabled:hover {
  background: var(--gold);
  transform: none;
}

.btn-secondary {
  background: #f3f4f6;
  color: var(--dark);
}

.btn-secondary:hover {
  background: #e5e7eb;
}

.btn-export {
  background: var(--dark);
  color: white;
}

.btn-export:hover:not(:disabled) {
  background: #2d3142;
}

.btn-export:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.btn-export:disabled:hover {
  background: var(--dark);
  transform: none;
}

.btn-export svg {
  width: 18px;
  height: 18px;
}

.loading-spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-container,
.error-container,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e5e7eb;
  border-top-color: var(--gold);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 1rem;
}

.error-container .error-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.error-container h3 {
  color: var(--dark);
  margin: 0 0 0.5rem;
}

.error-container p {
  color: var(--gray);
  margin-bottom: 1.5rem;
}

.empty-state .empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  color: var(--dark);
  margin: 0 0 0.5rem;
}

.empty-state p {
  color: var(--gray);
}

.reports-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.report-summary {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
}

.summary-card {
  background: var(--white);
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.summary-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.summary-card.revenue {
  border-left: 4px solid #10b981;
}

.summary-card.orders {
  border-left: 4px solid #3b82f6;
}

.summary-card.avg-order {
  border-left: 4px solid #8b5cf6;
}

.summary-card.customers {
  border-left: 4px solid #f59e0b;
}

.summary-card.products {
  border-left: 4px solid #ef4444;
}

.summary-card.top-product {
  border-left: 4px solid var(--gold);
}

.card-icon {
  font-size: 2.5rem;
  flex-shrink: 0;
}

.card-content {
  flex: 1;
}

.summary-label {
  font-size: 0.85rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.summary-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--dark);
  margin-bottom: 0.25rem;
}

.summary-subtext,
.summary-period {
  font-size: 0.875rem;
  color: var(--gray);
}

.status-breakdown {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.status-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f3f4f6;
}

.status-item:last-child {
  border-bottom: none;
}

.status-label {
  color: var(--gray);
  font-size: 0.9rem;
}

.status-count {
  font-weight: 600;
  color: var(--dark);
}

.chart-section,
.table-section {
  background: var(--white);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.chart-card h3,
.table-card h3 {
  margin: 0 0 1.5rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--dark);
}

.chart-container {
  display: flex;
  gap: 2rem;
  align-items: flex-end;
}

.chart-placeholder {
  flex: 1;
  min-height: 300px;
  display: flex;
  align-items: flex-end;
  gap: 0.5rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 8px;
}

.chart-bars {
  display: flex;
  align-items: flex-end;
  gap: 0.5rem;
  width: 100%;
  height: 100%;
}

.chart-bar {
  flex: 1;
  background: linear-gradient(to top, var(--gold), #b8860b);
  border-radius: 4px 4px 0 0;
  min-height: 20px;
  position: relative;
  transition: all 0.3s ease;
  cursor: pointer;
}

.chart-bar:hover {
  opacity: 0.8;
  transform: scaleY(1.05);
}

.bar-value {
  position: absolute;
  top: -1.5rem;
  left: 50%;
  transform: translateX(-50%);
  font-size: 0.75rem;
  color: var(--dark);
  white-space: nowrap;
  font-weight: 600;
}

.chart-legend {
  flex: 0 0 200px;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  max-height: 300px;
  overflow-y: auto;
}

.legend-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  padding: 0.5rem;
  background: #f9fafb;
  border-radius: 6px;
  font-size: 0.875rem;
}

.legend-period {
  font-weight: 600;
  color: var(--dark);
}

.legend-value {
  color: var(--gold);
  font-weight: 600;
}

.legend-orders {
  color: var(--gray);
  font-size: 0.75rem;
}

.table-container {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead {
  background: #f9fafb;
}

th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: var(--dark);
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #e5e7eb;
}

td {
  padding: 1rem;
  border-bottom: 1px solid #f3f4f6;
  color: var(--dark);
}

tbody tr:hover {
  background: #f9fafb;
}

.status-badge,
.payment-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.status-badge.status-pending,
.payment-badge.payment-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.status-processing,
.payment-badge.payment-processing {
  background: #dbeafe;
  color: #1e40af;
}

.status-badge.status-shipped {
  background: #e0e7ff;
  color: #3730a3;
}

.status-badge.status-delivered,
.payment-badge.payment-paid {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.status-cancelled,
.payment-badge.payment-failed {
  background: #fee2e2;
  color: #991b1b;
}

@media (max-width: 768px) {
  .admin-reports-page {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .header-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-group,
  .date-group,
  .action-buttons {
    width: 100%;
  }

  .action-buttons {
    margin-left: 0;
  }

  .report-summary {
    grid-template-columns: 1fr;
  }

  .chart-container {
    flex-direction: column;
  }

  .chart-legend {
    flex: 1;
    max-height: 200px;
  }

  table {
    font-size: 0.875rem;
  }

  th,
  td {
    padding: 0.75rem 0.5rem;
  }
}
</style>
