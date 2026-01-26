<template>
  <div class="admin-orders-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Order Management</h1>
        <p class="page-subtitle">Manage orders, track shipments, and process payments.</p>
      </div>
      <div class="header-actions">
        <button class="btn-secondary" @click="exportOrders">Export</button>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <div class="search-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/>
          <path d="m21 21-4.35-4.35"/>
        </svg>
        <input v-model="searchQuery" placeholder="Search by order ID or customer..." class="search-input">
      </div>
      <select v-model="selectedStatus" @change="loadOrders" class="filter-select">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="confirmed">Confirmed</option>
        <option value="processing">Processing</option>
        <option value="shipped">Shipped</option>
        <option value="out_for_delivery">Out for Delivery</option>
        <option value="delivered">Delivered</option>
        <option value="cancelled">Cancelled</option>
        <option value="returned">Returned</option>
        <option value="refunded">Refunded</option>
      </select>
      <select v-model="selectedPayment" @change="loadOrders" class="filter-select">
        <option value="">All Payment Methods</option>
        <option value="cod">Cash on Delivery</option>
        <option value="gcash">GCash</option>
        <option value="bank_transfer">Bank Transfer</option>
      </select>
      <input type="date" v-model="selectedDate" class="filter-select">
    </div>

    <!-- Orders Table -->
    <div class="table-card">
      <table class="data-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Items</th>
            <th>Amount</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading && orders.length === 0">
            <td colspan="8" style="text-align: center; padding: 3rem;">
              <div class="spinner"></div>
              <p>Loading orders...</p>
            </td>
          </tr>
          <tr v-else-if="error && orders.length === 0">
            <td colspan="8" style="text-align: center; padding: 3rem; color: #dc2626;">
              <div class="error-message">
                <p><strong>Error:</strong> {{ error }}</p>
                <button class="btn-small" @click="loadOrders" style="margin-top: 1rem; padding: 0.5rem 1rem; background: var(--gold); color: white; border: none; border-radius: 6px; cursor: pointer;">Retry</button>
              </div>
            </td>
          </tr>
          <tr v-else-if="!isLoading && filteredOrders.length === 0">
            <td colspan="8" style="text-align: center; padding: 3rem; color: #6b7280;">
              <p>No orders found</p>
              <p v-if="searchQuery || selectedStatus || selectedPayment || selectedDate" style="font-size: 0.9rem; margin-top: 0.5rem;">
                Try adjusting your filters
              </p>
            </td>
          </tr>
          <tr v-else v-for="order in filteredOrders" :key="order.id">
            <td class="order-id">#{{ order.order_number }}</td>
            <td>
              <div class="customer-cell">
                <div class="customer-name">{{ order.customer_name }}</div>
                <div class="customer-email">{{ order.customer_email }}</div>
              </div>
            </td>
            <td style="color: #000000;">{{ order.items?.length || 0 }} item(s)</td>
            <td class="amount" style="color: #000000;">₱{{ formatPrice(order.total) }}</td>
            <td>
              <span class="payment-badge" :class="getPaymentMethodClass(order)">
                {{ getPaymentMethodName(order) }}
              </span>
            </td>
            <td>
              <select 
                :value="order.status" 
                @change="updateOrderStatus(order.id, ($event.target as HTMLSelectElement).value)"
                class="status-select"
                :class="order.status.toLowerCase().replace('_', '-')"
                style="color: #000000;"
              >
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="out_for_delivery">Out for Delivery</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
                <option value="returned">Returned</option>
                <option value="refunded">Refunded</option>
              </select>
            </td>
            <td class="date" style="color: #000000;">{{ formatDate(order.created_at) }}</td>
            <td>
              <div class="action-buttons">
                <button class="action-btn view" @click="viewOrder(order.id)" title="View Details">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                </button>
                <button class="action-btn print" @click="printInvoice(order.id)" title="Print Invoice">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Order Details Modal -->
    <Teleport to="body">
      <div v-if="showOrderModal && selectedOrder" class="modal-overlay" @click.self="closeOrderModal">
        <div class="modal-content order-details-modal" @click.stop>
          <div class="modal-header">
            <h2>Order Details</h2>
            <button class="close-btn" @click.stop="closeOrderModal">×</button>
          </div>
          <div class="modal-body" v-if="isLoadingOrder">
            <div class="loading-state">
              <div class="spinner"></div>
              <p>Loading order details...</p>
            </div>
          </div>
          <div class="modal-body" v-else-if="orderDetails">
            <div class="detail-section">
              <h3>Order Information</h3>
              <div class="detail-row">
                <span class="detail-label">Order Number:</span>
                <span class="detail-value">#{{ orderDetails.order_number }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Customer:</span>
                <span class="detail-value">{{ orderDetails.customer_name }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value">{{ orderDetails.customer_email }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="status-badge" :class="orderDetails.status.toLowerCase().replace('_', '-')">
                  {{ orderDetails.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                </span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Date:</span>
                <span class="detail-value">{{ formatDate(orderDetails.created_at) }}</span>
              </div>
            </div>

            <div class="detail-section" v-if="orderDetails.items && orderDetails.items.length > 0">
              <h3>Order Items</h3>
              <div class="items-list">
                <div v-for="item in orderDetails.items" :key="item.id" class="item-row">
                  <div class="item-info">
                    <span class="item-name">{{ item.product_name || 'Product' }}</span>
                    <span class="item-sku">SKU: {{ item.product_sku || 'N/A' }}</span>
                  </div>
                  <div class="item-quantity">Qty: {{ item.quantity }}</div>
                  <div class="item-price">₱{{ formatPrice(item.total || item.unit_price * item.quantity) }}</div>
                </div>
              </div>
            </div>

            <div class="detail-section">
              <h3>Payment Summary</h3>
              <div class="detail-row">
                <span class="detail-label">Subtotal:</span>
                <span class="detail-value">₱{{ formatPrice(orderDetails.subtotal || 0) }}</span>
              </div>
              <div class="detail-row" v-if="orderDetails.discount_amount">
                <span class="detail-label">Discount:</span>
                <span class="detail-value">-₱{{ formatPrice(orderDetails.discount_amount) }}</span>
              </div>
              <div class="detail-row" v-if="orderDetails.shipping_amount">
                <span class="detail-label">Shipping:</span>
                <span class="detail-value">₱{{ formatPrice(orderDetails.shipping_amount) }}</span>
              </div>
              <div class="detail-row" v-if="orderDetails.tax_amount">
                <span class="detail-label">Tax:</span>
                <span class="detail-value">₱{{ formatPrice(orderDetails.tax_amount) }}</span>
              </div>
              <div class="detail-row total-row">
                <span class="detail-label">Total:</span>
                <span class="detail-value amount">₱{{ formatPrice(orderDetails.total || 0) }}</span>
              </div>
              <div class="detail-row" v-if="orderDetails.latest_payment || orderDetails.latestPayment">
                <span class="detail-label">Payment Method:</span>
                <span class="detail-value">{{ getPaymentMethodName(orderDetails) }}</span>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secondary" @click.stop="closeOrderModal" style="color: #000000;">Close</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { Teleport } from 'vue'
import { useRouter } from 'vue-router'
import { orders as ordersApi } from '@/services/adminApi'
import { useRealtimeAdmin } from '@/composables/useRealtimeAdmin'
import { useNotification } from '@/composables/useNotification'

const router = useRouter()
const { success, error: showError } = useNotification()

const searchQuery = ref('')
const selectedStatus = ref('')
const selectedPayment = ref('')
const selectedDate = ref('')
const orders = ref<any[]>([])
const isLoading = ref(false)
const error = ref<string | null>(null)
let pollInterval: number | null = null
let searchTimeout: number | null = null

// Order Details Modal
const showOrderModal = ref(false)
const selectedOrder = ref<any | null>(null)
const orderDetails = ref<any | null>(null)
const isLoadingOrder = ref(false)

// Real-time updates
const { startListening, stopListening } = useRealtimeAdmin()

const filteredOrders = computed(() => {
  let result = orders.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter((o: any) => 
      o.order_number?.toLowerCase().includes(query) ||
      o.customer_name?.toLowerCase().includes(query) ||
      o.customer_email?.toLowerCase().includes(query)
    )
  }

  if (selectedStatus.value) {
    result = result.filter((o: any) => o.status === selectedStatus.value)
  }

  if (selectedPayment.value) {
    result = result.filter((o: any) => {
      const payment = o.latest_payment || o.latestPayment
      const paymentMethod = payment?.payment_method?.code || ''
      return paymentMethod.toLowerCase() === selectedPayment.value.toLowerCase()
    })
  }

  if (selectedDate.value) {
    const filterDate = new Date(selectedDate.value).toDateString()
    result = result.filter((o: any) => {
      const orderDate = new Date(o.created_at).toDateString()
      return orderDate === filterDate
    })
  }

  return result
})

const formatPrice = (price: number) => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 2 })
}

const formatDate = (date: string | Date) => {
  const d = typeof date === 'string' ? new Date(date) : date
  return new Intl.DateTimeFormat('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric',
    hour: '2-digit', 
    minute: '2-digit' 
  }).format(d)
}

const getPaymentMethodName = (order: any) => {
  const payment = order.latest_payment || order.latestPayment
  if (!payment) return 'N/A'
  
  // Try both snake_case and camelCase
  const paymentMethod = payment.payment_method || payment.paymentMethod
  if (paymentMethod?.name) {
    return paymentMethod.name
  }
  if (paymentMethod?.code) {
    return paymentMethod.code.toUpperCase()
  }
  // Fallback to payment_method_name if available
  if (payment.payment_method_name) {
    return payment.payment_method_name
  }
  return 'N/A'
}

const getPaymentMethodClass = (order: any) => {
  const payment = order.latest_payment || order.latestPayment
  if (!payment) return 'cod'
  
  // Try both snake_case and camelCase
  const paymentMethod = payment.payment_method || payment.paymentMethod
  const methodCode = paymentMethod?.code || payment.payment_method_name?.toLowerCase() || 'cod'
  return methodCode.toLowerCase().replace('_', '-')
}

const loadOrders = async () => {
  isLoading.value = true
  error.value = null
  try {
    const params: any = {
      per_page: 100, // Reasonable limit
      sort_by: 'created_at',
      sort_order: 'desc',
    }

    if (selectedStatus.value) {
      params.status = selectedStatus.value
    }

    if (searchQuery.value) {
      params.search = searchQuery.value
    }

    if (selectedDate.value) {
      params.start_date = selectedDate.value
    }

    const response = await ordersApi.list(params)
    
    if (!response || !response.data) {
      throw new Error('Invalid response from server. Please check your connection.')
    }
    
    if (response.data.success) {
      const data = response.data.data
      
      // Handle paginated response
      let ordersData: any[] = []
      if (data) {
        if (data.data && Array.isArray(data.data)) {
          // Paginated response (Laravel paginator)
          ordersData = data.data
        } else if (Array.isArray(data)) {
          // Direct array response
          ordersData = data
        } else {
          // Empty or unexpected structure
          ordersData = []
        }
      } else {
        ordersData = []
      }
      
      // Map orders to ensure consistent structure
      orders.value = ordersData.map((order: any) => {
        // Handle payment data - support both snake_case and camelCase
        const payment = order.latest_payment || order.latestPayment || null
        const paymentMethod = payment?.payment_method || payment?.paymentMethod || null
        
        return {
          id: order.id,
          order_number: order.order_number || `#${order.id}`,
          customer_name: order.customer_name || 
            (order.user ? `${order.user.first_name || ''} ${order.user.last_name || ''}`.trim() : '') || 
            'Guest',
          customer_email: order.customer_email || order.user?.email || '',
          status: order.status || 'pending',
          total: parseFloat(order.total || 0),
          subtotal: parseFloat(order.subtotal || 0),
          shipping_amount: parseFloat(order.shipping_amount || 0),
          discount_amount: parseFloat(order.discount_amount || 0),
          tax_amount: parseFloat(order.tax_amount || 0),
          created_at: order.created_at,
          updated_at: order.updated_at,
          items: order.items || [],
          latest_payment: payment ? {
            id: payment.id,
            order_id: payment.order_id,
            status: payment.status,
            amount: payment.amount,
            payment_method_id: payment.payment_method_id,
            payment_method: paymentMethod || null,
            payment_method_name: payment.payment_method_name || null,
            created_at: payment.created_at,
          } : null,
          latestPayment: payment ? {
            id: payment.id,
            orderId: payment.order_id,
            status: payment.status,
            amount: payment.amount,
            paymentMethodId: payment.payment_method_id,
            paymentMethod: paymentMethod || null,
            paymentMethodName: payment.payment_method_name || null,
            createdAt: payment.created_at,
          } : null,
          payment_status: order.payment_status || 'pending',
          user: order.user || null,
        }
      })
    } else {
      throw new Error(response.data?.message || 'Failed to load orders')
    }
  } catch (err: any) {
    console.error('Failed to load orders:', err)
    error.value = err.response?.data?.message || err.message || 'Failed to load orders. Please try again.'
    showError('Failed to Load Orders', error.value)
    orders.value = []
  } finally {
    isLoading.value = false
  }
}

const updateOrderStatus = async (orderId: number, status: string) => {
  const order = orders.value.find((o: any) => o.id === orderId)
  if (!order) return

  const previousStatus = order.status
  if (previousStatus === status) return

  // Optimistically update UI
  order.status = status

  try {
    const response = await ordersApi.updateStatus(orderId, status, undefined, false)
    
    if (response.data.success) {
      // Update order with latest data from response
      const updatedOrder = response.data.data
      if (updatedOrder) {
        Object.assign(order, {
          status: updatedOrder.status || status,
          updated_at: updatedOrder.updated_at || new Date().toISOString(),
        })
      }

      success(
        'Order Status Updated',
        `Order ${order.order_number} status has been updated from ${previousStatus} to ${status}.`
      )
      
      // Reload to get latest data and relationships
      await loadOrders()
    } else {
      // Revert on failure
      order.status = previousStatus
      throw new Error(response.data.message || 'Failed to update order status')
    }
  } catch (err: any) {
    console.error('Failed to update order status:', err)
    // Revert optimistic update
    order.status = previousStatus
    showError(
      'Failed to Update Status',
      err.response?.data?.message || err.message || 'Failed to update order status. Please try again.'
    )
  }
}

const viewOrder = async (orderId: number) => {
  const order = orders.value.find((o: any) => o.id === orderId)
  if (!order) {
    showError('Order Not Found', 'The selected order could not be found.')
    return
  }

  selectedOrder.value = order
  showOrderModal.value = true
  isLoadingOrder.value = true
  document.body.style.overflow = 'hidden'

  try {
    const response = await ordersApi.get(orderId)
    if (response.data && response.data.success) {
      const data = response.data.data
      
      // Map order details similar to how we map orders in the list
      const payment = data.latest_payment || data.latestPayment || null
      const paymentMethod = payment?.payment_method || payment?.paymentMethod || null
      
      orderDetails.value = {
        id: data.id,
        order_number: data.order_number || `#${data.id}`,
        customer_name: data.customer_name || 
          (data.user ? `${data.user.first_name || ''} ${data.user.last_name || ''}`.trim() : '') || 
          'Guest',
        customer_email: data.customer_email || data.user?.email || '',
        status: data.status || 'pending',
        total: parseFloat(data.total || 0),
        subtotal: parseFloat(data.subtotal || 0),
        shipping_amount: parseFloat(data.shipping_amount || 0),
        discount_amount: parseFloat(data.discount_amount || 0),
        tax_amount: parseFloat(data.tax_amount || 0),
        created_at: data.created_at,
        items: data.items || [],
        latest_payment: payment,
        latestPayment: payment,
      }
    } else {
      throw new Error(response.data?.message || 'Failed to load order details')
    }
  } catch (err: any) {
    console.error('Failed to load order details:', err)
    showError(
      'Failed to Load Details',
      err.response?.data?.message || err.message || 'Failed to load order details. Please try again.'
    )
    closeOrderModal()
  } finally {
    isLoadingOrder.value = false
  }
}

const closeOrderModal = () => {
  showOrderModal.value = false
  selectedOrder.value = null
  orderDetails.value = null
  document.body.style.overflow = ''
}

const printInvoice = (orderId: number) => {
  // TODO: Implement print invoice
  window.print()
}

const exportOrders = async () => {
  try {
    // Fetch all orders for export
    const response = await ordersApi.list({
      per_page: 10000,
      sort_by: 'created_at',
      sort_order: 'desc',
    })

    if (response.data.success) {
      const ordersData = response.data.data.data || response.data.data || []
      
      // Convert to CSV
      const headers = ['Order Number', 'Customer Name', 'Customer Email', 'Status', 'Total', 'Payment Method', 'Date']
      const rows = ordersData.map((order: any) => [
        order.order_number || `#${order.id}`,
        order.customer_name || 'Guest',
        order.customer_email || '',
        order.status || 'pending',
        `₱${parseFloat(order.total || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`,
        (order.latest_payment || order.latestPayment)?.payment_method?.name || 'N/A',
        new Date(order.created_at).toLocaleDateString('en-US'),
      ])

      const csvContent = [
        headers.join(','),
        ...rows.map((row: any[]) => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(','))
      ].join('\n')

      // Download CSV
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
      const link = document.createElement('a')
      const url = URL.createObjectURL(blob)
      link.setAttribute('href', url)
      link.setAttribute('download', `orders_${new Date().toISOString().split('T')[0]}.csv`)
      link.style.visibility = 'hidden'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)

      success('Export Successful', 'Orders have been exported to CSV successfully.')
    } else {
      throw new Error(response.data.message || 'Failed to export orders')
    }
  } catch (err: any) {
    console.error('Failed to export orders:', err)
    showError(
      'Export Failed',
      err.response?.data?.message || err.message || 'Failed to export orders. Please try again.'
    )
  }
}

// Real-time event handlers
const handleOrderCreated = (event: CustomEvent) => {
  const orderData = event.detail
  // Reload orders to show the new order
  loadOrders()
  console.log('New order created:', orderData.order_number)
}

const handleOrderStatusUpdate = (event: CustomEvent) => {
  const orderData = event.detail
  const order = orders.value.find((o: any) => o.id === orderData.order_id)
  if (order) {
    order.status = orderData.new_status
  } else {
    // Reload if order not found
    loadOrders()
  }
}

const handleStockChanged = (event: CustomEvent) => {
  const stockData = event.detail
  // Show notification for low stock or out of stock
  if (stockData.type === 'low_stock') {
    console.warn(`Low stock alert: ${stockData.product_name} (${stockData.new_quantity} remaining)`)
  } else if (stockData.type === 'out_of_stock') {
    console.warn(`Out of stock: ${stockData.product_name}`)
  }
  // Could trigger a notification system here
}

// Real-time polling for order updates (fallback) - disabled to reduce server load
// Use real-time events instead
const startPolling = () => {
  // Poll every 30 seconds for updates (reduced frequency)
  pollInterval = window.setInterval(() => {
    if (!isLoading.value) {
      loadOrders()
    }
  }, 30000)
}

const stopPolling = () => {
  if (pollInterval) {
    clearInterval(pollInterval)
    pollInterval = null
  }
}

// Watch for search query changes with debounce
watch(searchQuery, () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  searchTimeout = window.setTimeout(() => {
    loadOrders()
  }, 500) // Debounce search by 500ms
})

// Watch for filter changes
watch([selectedStatus, selectedPayment, selectedDate], () => {
  loadOrders()
})

onMounted(() => {
  loadOrders()
  startPolling()
  
  // Set up real-time listeners
  startListening()
  window.addEventListener('realtime:admin:order:created', handleOrderCreated as EventListener)
  window.addEventListener('realtime:admin:order:status:updated', handleOrderStatusUpdate as EventListener)
  window.addEventListener('realtime:admin:stock:changed', handleStockChanged as EventListener)
})

onUnmounted(() => {
  stopPolling()
  stopListening()
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  window.removeEventListener('realtime:admin:order:created', handleOrderCreated as EventListener)
  window.removeEventListener('realtime:admin:order:status:updated', handleOrderStatusUpdate as EventListener)
  window.removeEventListener('realtime:admin:stock:changed', handleStockChanged as EventListener)
  document.body.style.overflow = ''
})
</script>

<style scoped>
.admin-orders-page {
  --gold: #c9a050;
  --dark: #1a1d29;
  --light: #f5f7fa;
  --white: #ffffff;
  --gray: #6b7280;
  padding-top: 3.5rem;
  padding-left: 2rem;
  padding-right: 2rem;
  padding-bottom: 2rem;
}


.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
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
  color: #6b7280;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
}

.btn-secondary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: 2px solid #e5e7eb;
  background: var(--white);
  color: var(--dark);
}

.btn-secondary:hover {
  border-color: var(--gold);
  color: var(--gold);
}

.btn-secondary svg {
  width: 18px;
  height: 18px;
}

.filters-bar {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 250px;
  position: relative;
}

.search-box svg {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
  color: #9ca3af;
}

.search-input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 3rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s;
}

.search-input:focus {
  border-color: var(--gold);
}

.filter-select {
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  background: var(--white);
  font-size: 0.9rem;
  cursor: pointer;
  outline: none;
  min-width: 150px;
}

.filter-select:focus {
  border-color: var(--gold);
}

.table-card {
  background: var(--white);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table thead {
  background: #f9fafb;
}

.data-table th {
  padding: 1rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #6b7280;
  border-bottom: 2px solid #e5e7eb;
}

.data-table td {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  color: #000000;
}

.data-table tbody tr:hover {
  background: #f9fafb;
}

.order-id {
  font-weight: 600;
  color: #000000;
}

.customer-cell {
  display: flex;
  flex-direction: column;
}

.customer-name {
  font-weight: 600;
  color: #000000;
  margin-bottom: 0.25rem;
}

.customer-email {
  font-size: 0.85rem;
  color: #000000;
}

.amount {
  font-weight: 700;
  color: #000000;
}

.payment-badge {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.payment-badge.cod {
  background: #fef3c7;
  color: #92400e;
}

.payment-badge.gcash {
  background: #dbeafe;
  color: #1e40af;
}

.payment-badge.maya {
  background: #e0e7ff;
  color: #3730a3;
}

.payment-badge.bank {
  background: #d1fae5;
  color: #065f46;
}

.status-select {
  padding: 0.5rem 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  outline: none;
  text-transform: capitalize;
}

.status-select.pending {
  background: #fef3c7;
  color: #92400e;
  border-color: #fbbf24;
}

.status-select.processing {
  background: #dbeafe;
  color: #1e40af;
  border-color: #3b82f6;
}

.status-select.shipped {
  background: #e0e7ff;
  color: #3730a3;
  border-color: #8b5cf6;
}

.status-select.delivered {
  background: #d1fae5;
  color: #065f46;
  border-color: #10b981;
}

.status-select.cancelled,
.status-select.refunded,
.status-select.returned {
  background: #fee2e2;
  color: #991b1b;
  border-color: #ef4444;
}

.status-select.confirmed {
  background: #dbeafe;
  color: #1e40af;
  border-color: #3b82f6;
}

.status-select.out-for-delivery {
  background: #e0e7ff;
  color: #3730a3;
  border-color: #8b5cf6;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.action-btn svg {
  width: 16px;
  height: 16px;
}

.action-btn.view {
  background: #dbeafe;
  color: #1e40af;
}

.action-btn.view:hover {
  background: #bfdbfe;
}

.action-btn.print {
  background: #f3f4f6;
  color: #6b7280;
}

.action-btn.print:hover {
  background: #e5e7eb;
  color: var(--dark);
}

.date {
  color: #000000;
  font-size: 0.9rem;
}

/* Order Details Modal Styles */
.order-details-modal {
  max-width: 800px;
}

.detail-section {
  margin-bottom: 2rem;
}

.detail-section:last-child {
  margin-bottom: 0;
}

.detail-section h3 {
  font-size: 1.1rem;
  font-weight: 700;
  color: #000000;
  margin: 0 0 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e5e7eb;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f3f4f6;
  color: #000000;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-row.total-row {
  border-top: 2px solid #e5e7eb;
  margin-top: 0.5rem;
  padding-top: 1rem;
  font-weight: 700;
}

.detail-label {
  font-weight: 600;
  color: #000000;
}

.detail-value {
  color: #000000;
  text-align: right;
}

.detail-value.amount {
  font-weight: 700;
  font-size: 1.1rem;
  color: var(--gold);
}

.items-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.item-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 8px;
  color: #000000;
}

.item-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  flex: 1;
}

.item-name {
  font-weight: 600;
  color: #000000;
}

.item-sku {
  font-size: 0.85rem;
  color: #6b7280;
}

.item-quantity {
  font-weight: 600;
  color: #000000;
  margin: 0 1rem;
}

.item-price {
  font-weight: 700;
  color: #000000;
}

.loading-state {
  text-align: center;
  padding: 3rem;
  color: #000000;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid var(--gold);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: fadeIn 0.3s ease;
  padding: 1rem;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-content {
  background: #ffffff;
  border-radius: 16px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  margin: 0;
  color: #000000;
}

.close-btn {
  background: #4b5563;
  border: none;
  font-size: 2rem;
  color: #ffffff;
  cursor: pointer;
  line-height: 1;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: all 0.2s;
}

.close-btn:hover {
  background: #374151;
  color: #ffffff;
}

.modal-body {
  padding: 1.5rem;
  color: #000000;
}

.modal-footer {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.status-badge {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.pending {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.confirmed {
  background: #dbeafe;
  color: #1e40af;
}

.status-badge.processing {
  background: #dbeafe;
  color: #1e40af;
}

.status-badge.shipped {
  background: #e0e7ff;
  color: #3730a3;
}

.status-badge.out-for-delivery {
  background: #e0e7ff;
  color: #3730a3;
}

.status-badge.delivered {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.cancelled,
.status-badge.returned,
.status-badge.refunded {
  background: #fee2e2;
  color: #991b1b;
}
</style>
