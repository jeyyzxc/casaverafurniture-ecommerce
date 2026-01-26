<template>
  <div class="order-detail-page">
    <HeroSection
      title="Order Details"
      subtitle="View complete order information"
      size="large"
    />

    <div class="order-detail-container">
      <div v-if="isLoading" class="loading">
        <div class="spinner"></div>
        <p>Loading order details...</p>
      </div>

      <div v-else-if="error" class="error-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <h3>{{ error }}</h3>
        <router-link to="/orders" class="btn-back">Back to Orders</router-link>
      </div>

      <div v-else-if="orderDetails" class="order-detail-content">
        <!-- Order Header -->
        <div class="order-header-card rise-up">
          <div class="order-header-info">
            <h2 class="order-number">Order #{{ orderDetails.order_number }}</h2>
            <p class="order-date">Placed on {{ formatDate(orderDetails.created_at) }}</p>
          </div>
          <div class="order-status-badge" :class="orderDetails.status">
            <span>{{ formatStatus(orderDetails.status) }}</span>
          </div>
        </div>

        <!-- Transaction Summary -->
        <div class="transaction-summary rise-up-delay-1">
          <h3 class="summary-title">Transaction Summary</h3>
          
          <!-- Customer Information -->
          <div class="summary-section rise-up-delay-2">
            <h4 class="section-heading">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
              Customer Information
            </h4>
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ orderDetails.customer_name || authStore.user?.full_name }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ orderDetails.customer_email || authStore.user?.email }}</span>
              </div>
              <div class="info-item" v-if="orderDetails.customer_phone">
                <span class="info-label">Phone:</span>
                <span class="info-value">{{ orderDetails.customer_phone }}</span>
              </div>
            </div>
          </div>

          <!-- Shipping Address -->
          <div class="summary-section rise-up-delay-3">
            <h4 class="section-heading">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                <circle cx="12" cy="10" r="3"/>
              </svg>
              Shipping Address
            </h4>
            <div class="address-display">
              <p><strong>{{ orderDetails.shipping_name }}</strong></p>
              <p>{{ orderDetails.shipping_address_line_1 }}</p>
              <p v-if="orderDetails.shipping_address_line_2">{{ orderDetails.shipping_address_line_2 }}</p>
              <p>{{ orderDetails.shipping_city }}, {{ orderDetails.shipping_province }} {{ orderDetails.shipping_postal_code }}</p>
              <p>{{ orderDetails.shipping_country || 'Philippines' }}</p>
              <p v-if="orderDetails.shipping_phone">Phone: {{ orderDetails.shipping_phone }}</p>
            </div>
          </div>

          <!-- Billing Address (if different) -->
          <div v-if="orderDetails.billing_name && orderDetails.billing_name !== orderDetails.shipping_name" class="summary-section">
            <h4 class="section-heading">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
              </svg>
              Billing Address
            </h4>
            <div class="address-display">
              <p><strong>{{ orderDetails.billing_name }}</strong></p>
              <p>{{ orderDetails.billing_address_line_1 }}</p>
              <p v-if="orderDetails.billing_address_line_2">{{ orderDetails.billing_address_line_2 }}</p>
              <p>{{ orderDetails.billing_city }}, {{ orderDetails.billing_province }} {{ orderDetails.billing_postal_code }}</p>
              <p>{{ orderDetails.billing_country || 'Philippines' }}</p>
            </div>
          </div>

          <!-- Payment Information -->
          <div class="summary-section rise-up-delay-4" v-if="orderDetails.latest_payment">
            <h4 class="section-heading">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="1" y1="4" x2="23" y2="4"/>
                <path d="M1 10h22v10H1z"/>
                <line x1="1" y1="14" x2="23" y2="14"/>
              </svg>
              Payment Information
            </h4>
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Payment Method:</span>
                <span class="info-value">{{ orderDetails.latest_payment.payment_method_name }}</span>
              </div>
              <div class="info-item" v-if="orderDetails.latest_payment.transaction_id">
                <span class="info-label">Transaction ID:</span>
                <span class="info-value">{{ orderDetails.latest_payment.transaction_id }}</span>
              </div>
              <div class="info-item" v-if="orderDetails.latest_payment.payment_details">
                <template v-if="orderDetails.latest_payment.payment_details.sender_name">
                  <div class="info-item">
                    <span class="info-label">Sender Name:</span>
                    <span class="info-value">{{ orderDetails.latest_payment.payment_details.sender_name }}</span>
                  </div>
                </template>
                <template v-if="orderDetails.latest_payment.payment_details.sender_account">
                  <div class="info-item">
                    <span class="info-label">Sender Account:</span>
                    <span class="info-value">{{ orderDetails.latest_payment.payment_details.sender_account }}</span>
                  </div>
                </template>
                <template v-if="orderDetails.latest_payment.payment_details.reference_number">
                  <div class="info-item">
                    <span class="info-label">Reference Number:</span>
                    <span class="info-value">{{ orderDetails.latest_payment.payment_details.reference_number }}</span>
                  </div>
                </template>
                <template v-if="orderDetails.latest_payment.payment_details.card_number">
                  <div class="info-item">
                    <span class="info-label">Card Number (Last 4):</span>
                    <span class="info-value">****{{ orderDetails.latest_payment.payment_details.card_number }}</span>
                  </div>
                </template>
                <template v-if="orderDetails.latest_payment.payment_details.card_holder_name">
                  <div class="info-item">
                    <span class="info-label">Card Holder:</span>
                    <span class="info-value">{{ orderDetails.latest_payment.payment_details.card_holder_name }}</span>
                  </div>
                </template>
                <template v-if="orderDetails.latest_payment.payment_details.card_expiry">
                  <div class="info-item">
                    <span class="info-label">Expiry:</span>
                    <span class="info-value">{{ orderDetails.latest_payment.payment_details.card_expiry }}</span>
                  </div>
                </template>
              </div>
              <div class="info-item">
                <span class="info-label">Amount Paid:</span>
                <span class="info-value gold">₱{{ formatPrice(orderDetails.latest_payment.amount) }}</span>
              </div>
              <div class="info-item" v-if="orderDetails.latest_payment.fee_amount > 0">
                <span class="info-label">Payment Fee:</span>
                <span class="info-value">₱{{ formatPrice(orderDetails.latest_payment.fee_amount) }}</span>
              </div>
              <div class="info-item" v-if="orderDetails.latest_payment.status">
                <span class="info-label">Payment Status:</span>
                <span class="info-value" :class="`status-${orderDetails.latest_payment.status}`">
                  {{ formatPaymentStatus(orderDetails.latest_payment.status) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Order Items -->
          <div class="summary-section rise-up-delay-5" v-if="orderDetails.items && orderDetails.items.length > 0">
            <h4 class="section-heading">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 0 1-8 0"/>
              </svg>
              Order Items
            </h4>
            <div class="order-items-list">
              <div v-for="item in orderDetails.items" :key="item.id" class="order-item-detail">
                <img
                  :src="item.product_image || '/images/products/placeholder.png'"
                  :alt="item.product_name"
                  class="item-image"
                />
                <div class="item-info">
                  <h4 class="item-name">{{ item.product_name }}</h4>
                  <p class="item-sku">SKU: {{ item.product_sku }}</p>
                  <p class="item-qty">Quantity: {{ item.quantity }}</p>
                  <p class="item-price">₱{{ formatPrice(item.total) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Order Totals -->
          <div class="summary-section rise-up-delay-5">
            <h4 class="section-heading">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
              </svg>
              Order Totals
            </h4>
            <div class="totals-grid">
              <div class="total-row">
                <span>Subtotal:</span>
                <span>₱{{ formatPrice(orderDetails.subtotal || 0) }}</span>
              </div>
              <div class="total-row" v-if="orderDetails.discount_amount > 0">
                <span>Discount:</span>
                <span class="discount">-₱{{ formatPrice(orderDetails.discount_amount) }}</span>
              </div>
              <div class="total-row">
                <span>Shipping:</span>
                <span>₱{{ formatPrice(orderDetails.shipping_amount || 0) }}</span>
              </div>
              <div class="total-row" v-if="orderDetails.latest_payment?.fee_amount > 0">
                <span>Payment Fee:</span>
                <span>₱{{ formatPrice(orderDetails.latest_payment.fee_amount) }}</span>
              </div>
              <div class="total-row final-total">
                <span>Total:</span>
                <span class="gold">₱{{ formatPrice(orderDetails.total || 0) }}</span>
              </div>
            </div>
          </div>

          <!-- Order Notes -->
          <div v-if="orderDetails.notes" class="summary-section">
            <h4 class="section-heading">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
              </svg>
              Order Notes
            </h4>
            <p class="order-notes">{{ orderDetails.notes }}</p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="order-actions">
          <router-link to="/orders" class="btn-back">Back to Orders</router-link>
          <button
            v-if="canCancel(orderDetails)"
            @click="showCancelModal = true"
            class="btn-cancel"
          >
            Cancel Order
          </button>
        </div>
      </div>
    </div>

    <!-- Cancel Order Confirmation Modal -->
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
              Are you sure you want to cancel order <strong>#{{ orderDetails?.order_number }}</strong>?
            </p>
            <p class="modal-submessage">
              This action cannot be undone. Your order will be cancelled immediately.
            </p>
          </div>

          <div class="modal-footer">
            <button class="btn-modal-secondary" @click="showCancelModal = false">
              Keep Order
            </button>
            <button class="btn-modal-danger" @click="handleCancelOrder" :disabled="isCancelling">
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
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Teleport } from 'vue'
import HeroSection from '@/components/HeroSection.vue'
import { orders as ordersApi } from '@/services/clientApi'
import { useAuthStore } from '@/stores/auth'
import { useNotification } from '@/composables/useNotification'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const { success, error: showError } = useNotification()

const orderDetails = ref<any>(null)
const isLoading = ref(true)
const error = ref<string | null>(null)
const showCancelModal = ref(false)
const isCancelling = ref(false)

const formatPrice = (price: number) => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 2 })
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
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

const formatPaymentStatus = (status: string) => {
  const statusMap: Record<string, string> = {
    pending: 'Pending',
    processing: 'Processing',
    completed: 'Completed',
    failed: 'Failed',
    refunded: 'Refunded',
  }
  return statusMap[status] || status
}

const canCancel = (order: any) => {
  return ['pending', 'processing'].includes(order.status)
}

const loadOrderDetails = async () => {
  const orderNumber = route.params.orderNumber as string
  
  if (!orderNumber) {
    error.value = 'Order number is required'
    isLoading.value = false
    return
  }

  isLoading.value = true
  error.value = null

  try {
    const response = await ordersApi.get(orderNumber)
    
    if (response.data.success) {
      orderDetails.value = response.data.data
    } else {
      error.value = response.data.message || 'Failed to load order details'
    }
  } catch (err: any) {
    console.error('Failed to load order details:', err)
    error.value = err.response?.data?.message || 'Failed to load order details. Please try again.'
    showError('Error', error.value)
  } finally {
    isLoading.value = false
  }
}

const handleCancelOrder = async () => {
  if (!orderDetails.value) return

  isCancelling.value = true

  try {
    const response = await ordersApi.cancel(orderDetails.value.order_number)
    
    if (response.data.success) {
      showCancelModal.value = false
      success('Order Cancelled', `Order #${orderDetails.value.order_number} has been cancelled successfully.`)
      await loadOrderDetails() // Refresh order details
    } else {
      showError('Cancellation Failed', response.data.message || 'Failed to cancel order. Please try again.')
    }
  } catch (err: any) {
    console.error('Failed to cancel order:', err)
    showError('Error', err.response?.data?.message || 'Failed to cancel order. Please try again.')
  } finally {
    isCancelling.value = false
  }
}

onMounted(() => {
  loadOrderDetails()
})
</script>

<style scoped>
.order-detail-page {
  min-height: 100vh;
  background: #f5f7fa;
}

.order-detail-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 3rem 2rem;
}

.loading,
.error-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.error-state svg {
  width: 80px;
  height: 80px;
  color: #ef4444;
  margin-bottom: 1.5rem;
}

.error-state h3 {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  margin-bottom: 2rem;
  color: #1a1a1a;
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

.order-detail-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.order-header-card {
  background: white;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.order-header-info {
  flex: 1;
}

.order-number {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  margin-bottom: 0.5rem;
  color: #1a1a1a;
}

.order-date {
  color: #666;
  font-size: 0.95rem;
}

.order-status-badge {
  padding: 0.75rem 1.5rem;
  border-radius: 25px;
  font-weight: 600;
  font-size: 0.9rem;
  text-transform: uppercase;
}

.order-status-badge.pending {
  background: #fef3c7;
  color: #92400e;
}

.order-status-badge.processing {
  background: #dbeafe;
  color: #1e40af;
}

.order-status-badge.shipped {
  background: #e0e7ff;
  color: #3730a3;
}

.order-status-badge.delivered {
  background: #d1fae5;
  color: #065f46;
}

.order-status-badge.cancelled {
  background: #fee2e2;
  color: #dc2626;
}

.transaction-summary {
  background: white;
  border-radius: 20px;
  padding: 2.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.summary-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.75rem;
  margin-bottom: 2rem;
  color: #1a1a1a;
  border-bottom: 2px solid #f0f0f0;
  padding-bottom: 1rem;
}

.summary-section {
  margin-bottom: 2.5rem;
}

.summary-section:last-child {
  margin-bottom: 0;
}

.section-heading {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-family: 'Playfair Display', serif;
  font-size: 1.25rem;
  margin-bottom: 1.5rem;
  color: #1a1a1a;
}

.section-heading svg {
  width: 24px;
  height: 24px;
  color: #c9a050;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.info-label {
  font-weight: 600;
  font-size: 0.875rem;
  color: #666;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-value {
  font-size: 1rem;
  color: #1a1a1a;
}

.info-value.gold {
  color: #c9a050;
  font-weight: 600;
  font-size: 1.1rem;
}

.status-pending {
  color: #92400e;
}

.status-completed {
  color: #065f46;
}

.status-failed {
  color: #dc2626;
}

.address-display {
  line-height: 1.8;
  color: #333;
}

.address-display p {
  margin: 0.5rem 0;
}

.order-items-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.order-item-detail {
  display: flex;
  gap: 1.5rem;
  padding: 1.5rem;
  background: #f9fafb;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

.item-image {
  width: 120px;
  height: 120px;
  object-fit: cover;
  border-radius: 10px;
  flex-shrink: 0;
}

.item-info {
  flex: 1;
}

.item-name {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #1a1a1a;
}

.item-sku,
.item-qty {
  font-size: 0.9rem;
  color: #666;
  margin-bottom: 0.25rem;
}

.item-price {
  font-size: 1.25rem;
  font-weight: 700;
  color: #c9a050;
  margin-top: 0.5rem;
}

.totals-grid {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.total-row {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f0f0f0;
  color: #000000;
}

.total-row span {
  color: #000000;
}

.total-row:last-child {
  border-bottom: none;
}

.total-row.final-total {
  border-top: 2px solid #e5e7eb;
  padding-top: 1rem;
  margin-top: 0.5rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: #000000;
}

.total-row.final-total span {
  color: #000000;
}

.total-row .discount {
  color: #000000;
}

.total-row .gold {
  color: #000000;
  font-size: 1.5rem;
  font-weight: 700;
}

.order-notes {
  padding: 1rem;
  background: #f9fafb;
  border-radius: 8px;
  border-left: 4px solid #c9a050;
  color: #333;
  line-height: 1.6;
}

.order-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  padding-top: 2rem;
}

.btn-back,
.btn-cancel {
  padding: 0.875rem 2rem;
  border-radius: 10px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s;
  border: none;
  cursor: pointer;
  font-size: 1rem;
}

.btn-back {
  background: linear-gradient(135deg, #c9a050, #b8860b);
  color: white;
}

.btn-back:hover {
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

@media (max-width: 768px) {
  .order-detail-container {
    padding: 2rem 1rem;
  }

  .order-header-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .transaction-summary {
    padding: 1.5rem;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }

  .order-item-detail {
    flex-direction: column;
  }

  .item-image {
    width: 100%;
    height: 200px;
  }

  .order-actions {
    flex-direction: column;
  }

  .btn-back,
  .btn-cancel {
    width: 100%;
  }
}

/* Cancel Order Modal */
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
