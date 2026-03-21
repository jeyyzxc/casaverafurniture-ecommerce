<template>
  <div class="orders-page">
    <HeroSection
      title="My Orders"
      subtitle="Track and manage your orders."
      size="large"
    />

    <div class="orders-container">
      <div v-if="!isLoading" class="orders-header rise-up">
        <h2 class="orders-title">My Orders</h2>
        <div class="order-count-badge">
          <span class="count-label">Total Orders:</span>
          <span class="count-value">{{ totalOrderCount }}</span>
        </div>
      </div>

      <div v-if="!isLoading && orders.length > 0" class="orders-list">
        <div
          v-for="(order, index) in orders"
          :key="order.id"
          class="order-card"
          :class="`rise-up-delay-${Math.min(index + 1, 5)}`"
        >
          <div class="order-header">
            <div class="order-info">
              <h3 class="order-number">Order #{{ order.order_number }}</h3>
              <p class="order-date">{{ formatDate(order.created_at) }}</p>
            </div>
            <div class="order-status" :class="order.status">
              <span class="status-badge">{{ formatStatus(order.status) }}</span>
            </div>
          </div>

          <div class="order-items">
            <div
              v-for="item in order.items"
              :key="item.id"
              class="order-item"
            >
              <img
                :src="item.product_image || '/images/products/placeholder.png'"
                :alt="item.product_name"
                class="item-image"
              />
              <div class="item-details">
                <h4>{{ item.product_name }}</h4>
                <p class="item-sku">SKU: {{ item.product_sku }}</p>
                <p class="item-qty">Quantity: {{ item.quantity }}</p>
              </div>
              <div class="item-price">₱{{ formatPrice(item.total) }}</div>
            </div>
          </div>

          <div class="order-footer">
            <div class="order-totals">
              <div class="total-row">
                <span>Total:</span>
                <span class="total-amount">₱{{ formatPrice(order.total) }}</span>
              </div>
            </div>
            <div class="order-actions">
              <router-link
                :to="`/orders/${order.order_number}`"
                class="btn-view"
              >
                View Details
              </router-link>
              <button
                v-if="canCancel(order)"
                @click="openCancelModal(order.order_number)"
                class="btn-cancel"
              >
                Cancel Order
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="!isLoading && orders.length === 0" class="empty-orders">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
          <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
        </svg>
        <h3>No orders yet</h3>
        <p>Start shopping to see your orders here.</p>
        <router-link to="/products" class="btn-shop">Browse Products</router-link>
      </div>

      <div v-else class="loading">
        <div class="spinner"></div>
        <p>Loading orders...</p>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showCancelModal" class="modal-overlay" @click.self="showCancelModal = false">
        <div class="modal-container">
          <div class="modal-header">
            <h3 class="modal-title">Cancel Order</h3>
            <button class="modal-close" @click="showCancelModal = false">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <div class="modal-body">
            <div class="warning-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
            </div>
            <p class="modal-message">
              Are you sure you want to cancel order <strong>#{{ selectedOrderNumber }}</strong>?
            </p>
            <p class="modal-submessage">
              This action cannot be undone. Your order will be cancelled immediately.
            </p>
          </div>

          <div class="modal-footer">
            <button class="btn-modal-secondary" @click="showCancelModal = false">
              Keep Order
            </button>
            <button class="btn-modal-danger" @click="cancelOrder" :disabled="isCancelling">
              <span v-if="isCancelling">Cancelling...</span>
              <span v-else>Yes, Cancel Order</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { Teleport } from 'vue'
import { useRouter } from 'vue-router'
import HeroSection from '@/components/HeroSection.vue'
import { orders as ordersApi } from '@/services/clientApi'
import { useAuthStore } from '@/stores/auth'
import { useRealtimeOrders } from '@/composables/useRealtimeOrders'
import { useNotification } from '@/composables/useNotification'
import { useOrderCount } from '@/composables/useOrderCount'

const router = useRouter()
const authStore = useAuthStore()
const { success, error: showError } = useNotification()
const { updateOrderCount } = useOrderCount()

const orders = ref<any[]>([])
const isLoading = ref(false)
const showCancelModal = ref(false)
const selectedOrderNumber = ref<string | null>(null)
const isCancelling = ref(false)
let pollInterval: number | null = null

const { startListening, stopListening } = useRealtimeOrders()

const totalOrderCount = computed(() => orders.value.length)

const formatPrice = (price: number) => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 2 })
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const formatStatus = (status: string) => {
  const statusMap: Record<string, string> = {
    pending: 'Pending',
    processing: 'Processing',
    shipped: 'Shipped',
    delivered: 'Delivered',
    cancelled: 'Cancelled',
  }
  return statusMap[status] || status
}

const canCancel = (order: any) => {
  return ['pending', 'processing'].includes(order.status)
}

const loadOrders = async () => {
  if (!authStore.isAuthenticated) {
    router.push('/?login=true')
    return
  }

  isLoading.value = true
  try {
    const response = await ordersApi.list({ per_page: 10000 })
    if (response.data.success) {
      if (response.data.data.data) {
        orders.value = response.data.data.data
        updateOrderCount(response.data.data.meta?.total || response.data.data.data.length)
      } else if (Array.isArray(response.data.data)) {
        orders.value = response.data.data
        updateOrderCount(response.data.data.length)
      } else {
        orders.value = []
        updateOrderCount(0)
      }
    }
  } catch (error) {
    console.error('Failed to load orders:', error)
    updateOrderCount(0)
  } finally {
    isLoading.value = false
  }
}

const openCancelModal = (orderNumber: string) => {
  selectedOrderNumber.value = orderNumber
  showCancelModal.value = true
}

const cancelOrder = async () => {
  if (!selectedOrderNumber.value) return

  isCancelling.value = true

  try {
    const response = await ordersApi.cancel(selectedOrderNumber.value)

    if (response.data.success) {
      showCancelModal.value = false
      success('Order Cancelled', `Order #${selectedOrderNumber.value} has been cancelled successfully.`)
      await loadOrders()
      selectedOrderNumber.value = null
    } else {
      showError('Cancellation Failed', response.data.message || 'Failed to cancel order. Please try again.')
    }
  } catch (error: any) {
    console.error('Failed to cancel order:', error)
    showError('Error', error.response?.data?.message || 'Failed to cancel order. Please try again.')
  } finally {
    isCancelling.value = false
  }
}

const handleOrderStatusUpdate = (event: CustomEvent) => {
  const orderData = event.detail
  const order = orders.value.find(o => o.order_number === orderData.order_number)
  if (order) {
    order.status = orderData.new_status
    console.log(`Order ${orderData.order_number} status updated to ${orderData.new_status}`)
  } else {
    loadOrders()
  }
}

const startPolling = () => {
  pollInterval = window.setInterval(() => {
    if (authStore.isAuthenticated && orders.value.length > 0 && !isLoading.value) {
      ordersApi.list({ per_page: 10000 })
        .then((response) => {
          if (response.data.success) {
            if (response.data.data.data) {
              orders.value = response.data.data.data
            } else if (Array.isArray(response.data.data)) {
              orders.value = response.data.data
            }
          }
        })
        .catch(() => {
        })
    }
  }, 30000)
}

const stopPolling = () => {
  if (pollInterval) {
    clearInterval(pollInterval)
    pollInterval = null
  }
}

watch(() => orders.value.length, (newCount) => {
  updateOrderCount(newCount)
}, { immediate: true })

onMounted(() => {
  loadOrders()
  startPolling()

  if (authStore.isAuthenticated) {
    startListening()
    window.addEventListener('realtime:order:status:updated', handleOrderStatusUpdate as EventListener)
  }
})

onUnmounted(() => {
  stopPolling()
  stopListening()
  window.removeEventListener('realtime:order:status:updated', handleOrderStatusUpdate as EventListener)
})
</script>

<style scoped>
.orders-page {
  min-height: 100vh;
  background: #f5f7fa;
}

.orders-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 3rem 2rem;
}

.orders-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #e5e7eb;
}

.orders-title {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  color: #1a1a1a;
  margin: 0;
}

.order-count-badge {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #c9a050, #b8860b);
  color: white;
  border-radius: 25px;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(201, 160, 80, 0.3);
}

.count-label {
  font-size: 0.9rem;
  opacity: 0.95;
}

.count-value {
  font-size: 1.25rem;
  font-weight: 700;
  background: rgba(255, 255, 255, 0.2);
  padding: 0.25rem 0.75rem;
  border-radius: 15px;
  min-width: 40px;
  text-align: center;
}

@media (max-width: 768px) {
  .orders-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .orders-title {
    font-size: 1.5rem;
  }

  .order-count-badge {
    width: 100%;
    justify-content: space-between;
  }
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.order-card {
  background: white;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f0f0f0;
}

.order-number {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
  color: #1a1a1a;
}

.order-date {
  color: #666;
  font-size: 0.9rem;
}

.status-badge {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
}

.order-status.pending .status-badge {
  background: #fef3c7;
  color: #92400e;
}

.order-status.processing .status-badge {
  background: #dbeafe;
  color: #1e40af;
}

.order-status.shipped .status-badge {
  background: #e0e7ff;
  color: #3730a3;
}

.order-status.delivered .status-badge {
  background: #d1fae5;
  color: #065f46;
}

.order-status.cancelled .status-badge {
  background: #fee2e2;
  color: #dc2626;
}

.order-items {
  margin-bottom: 1.5rem;
}

.order-item {
  display: flex;
  gap: 1rem;
  padding: 1rem 0;
  border-bottom: 1px solid #f0f0f0;
}

.order-item:last-child {
  border-bottom: none;
}

.item-image {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 10px;
}

.item-details {
  flex: 1;
}

.item-details h4 {
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.item-sku,
.item-qty {
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 0.25rem;
}

.item-price {
  font-weight: 600;
  color: #c9a050;
  font-size: 1.1rem;
}

.order-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1.5rem;
  border-top: 2px solid #f0f0f0;
}

.total-amount {
  font-size: 1.5rem;
  font-weight: 700;
  color: #c9a050;
}

.order-actions {
  display: flex;
  gap: 1rem;
}

.btn-view,
.btn-cancel {
  padding: 0.75rem 1.5rem;
  border-radius: 10px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s;
  border: none;
  cursor: pointer;
}

.btn-view {
  background: linear-gradient(135deg, #c9a050, #b8860b);
  color: white;
}

.btn-view:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(201, 160, 80, 0.4);
}

.btn-cancel {
  background: white;
  color: #dc2626;
  border: 2px solid #dc2626;
}

.btn-cancel:hover {
  background: #fee2e2;
}

.empty-orders,
.loading {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.empty-orders svg {
  width: 80px;
  height: 80px;
  color: #c9a050;
  margin-bottom: 1.5rem;
}

.empty-orders h3 {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
  color: #000000;
}

.empty-orders p {
  color: #666;
  margin-bottom: 2rem;
}

.btn-shop {
  display: inline-block;
  padding: 1rem 2rem;
  background: linear-gradient(135deg, #c9a050, #b8860b);
  color: white;
  border-radius: 10px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-shop:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(201, 160, 80, 0.4);
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #c9a050;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 1rem;
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.modal-container {
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  max-width: 500px;
  width: 100%;
  overflow: hidden;
  animation: slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
  padding: 1.5rem 2rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  font-weight: 600;
  color: #1a1a1a;
  margin: 0;
}

.modal-close {
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: all 0.2s;
  color: #666;
}

.modal-close:hover {
  background: #f3f4f6;
  color: #1a1a1a;
}

.modal-close svg {
  width: 20px;
  height: 20px;
}

.modal-body {
  padding: 2rem;
  text-align: center;
}

.warning-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 1.5rem;
  color: #f59e0b;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fef3c7;
  border-radius: 50%;
}

.warning-icon svg {
  width: 36px;
  height: 36px;
}

.modal-message {
  font-size: 1.1rem;
  color: #1a1a1a;
  margin-bottom: 0.75rem;
  line-height: 1.6;
}

.modal-message strong {
  color: #c9a050;
  font-weight: 600;
}

.modal-submessage {
  font-size: 0.9rem;
  color: #666;
  line-height: 1.5;
  margin: 0;
}

.modal-footer {
  display: flex;
  gap: 1rem;
  padding: 1.5rem 2rem;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
}

.btn-modal-secondary,
.btn-modal-danger {
  flex: 1;
  padding: 0.875rem 1.5rem;
  border-radius: 10px;
  font-weight: 600;
  font-size: 1rem;
  border: none;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-modal-secondary {
  background: white;
  color: #374151;
  border: 2px solid #e5e7eb;
}

.btn-modal-secondary:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

.btn-modal-danger {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
}

.btn-modal-danger:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.btn-modal-danger:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .modal-container {
    max-width: 100%;
    margin: 1rem;
  }

  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 1.25rem 1.5rem;
  }

  .modal-footer {
    flex-direction: column;
  }

  .btn-modal-secondary,
  .btn-modal-danger {
    width: 100%;
  }
}
</style>
