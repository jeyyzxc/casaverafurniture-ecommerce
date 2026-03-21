<template>
  <div class="admin-payments-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Payment Management</h1>
        <p class="page-subtitle">Manage payment methods, verify transactions, and process refunds.</p>
      </div>
    </div>

    <div class="filters-bar">
      <select v-model="filterStatus" class="filter-select">
        <option value="">All Payments</option>
        <option value="pending">Pending</option>
        <option value="confirmed">Confirmed</option>
        <option value="failed">Failed</option>
      </select>
      <select v-model="filterMethod" class="filter-select">
        <option value="">All Methods</option>
        <option value="online_payment">Online Payment (GCash, Maya, PayPal)</option>
        <option value="bank_transfer">Bank Transfers (BDO, BPI, Metrobank, Card)</option>
        <option value="cod">Cash on Delivery</option>
      </select>
      <button class="btn-reset" @click="resetFilters" title="Reset Filters">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
          <path d="M3 3v5h5"/>
        </svg>
        Reset
      </button>
    </div>

    <div class="table-card">
      <table class="data-table">
        <thead>
          <tr>
            <th>Transaction ID</th>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading && payments.length === 0">
            <td colspan="8" style="text-align: center; padding: 3rem;">
              <div class="spinner"></div>
              <p>Loading payments...</p>
            </td>
          </tr>
          <tr v-else-if="error">
            <td colspan="8" style="text-align: center; padding: 3rem; color: #dc2626;">
              <div class="error-message">
                <p><strong>Error:</strong> {{ error }}</p>
                <button class="btn-small" @click="loadPayments" style="margin-top: 1rem;">Retry</button>
              </div>
            </td>
          </tr>
          <tr v-else-if="!isLoading && filteredPayments.length === 0">
            <td colspan="8" style="text-align: center; padding: 3rem; color: #6b7280;">
              No payments found
            </td>
          </tr>
          <tr v-else v-for="payment in filteredPayments" :key="payment.id">
            <td class="transaction-id">{{ payment.transactionId }}</td>
            <td class="order-id">{{ payment.order_number || `#${payment.orderId}` }}</td>
            <td>{{ payment.customerName }}</td>
            <td class="amount">₱{{ formatPrice(payment.amount) }}</td>
            <td>
              <span class="method-badge">{{ payment.method.toUpperCase() }}</span>
            </td>
            <td>
              <span class="status-badge" :class="getStatusClass(payment.status)">
                {{ getStatusLabel(payment.status) }}
              </span>
            </td>
            <td class="date">{{ formatDate(payment.date) }}</td>
            <td>
              <div class="action-buttons">
                <button v-if="payment.status.toLowerCase() === 'pending' || payment.status.toLowerCase() === 'awaiting_verification'" class="btn-small success" @click="openVerifyModal(payment)">Verify</button>
                <button v-if="payment.status.toLowerCase() === 'pending' || payment.status.toLowerCase() === 'awaiting_verification'" class="btn-small danger" @click="openRejectModal(payment)">Reject</button>
                <button class="btn-small" @click="viewDetails(payment.id)">View</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Teleport to="body">
      <div v-if="showDetailsModal && selectedPayment" class="modal-overlay" @click.self="closeDetailsModal">
        <div class="modal-content details-modal" @click.stop>
          <div class="modal-header">
            <h2>Payment Details</h2>
            <button class="close-btn" @click.stop="closeDetailsModal">×</button>
          </div>
          <div class="modal-body">
            <div class="detail-section">
              <h3>Transaction Information</h3>
              <div class="detail-row">
                <span class="detail-label">Transaction ID:</span>
                <span class="detail-value">{{ selectedPayment.transactionId }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Order Number:</span>
                <span class="detail-value">{{ selectedPayment.order_number || `#${selectedPayment.orderId}` }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Customer:</span>
                <span class="detail-value">{{ selectedPayment.customerName }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Amount:</span>
                <span class="detail-value amount">₱{{ formatPrice(selectedPayment.amount) }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span class="detail-value">{{ selectedPayment.payment_method?.name || selectedPayment.method.toUpperCase() }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="status-badge" :class="getStatusClass(selectedPayment.status)">
                  {{ getStatusLabel(selectedPayment.status) }}
                </span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Date:</span>
                <span class="detail-value">{{ formatDate(selectedPayment.date) }}</span>
              </div>
            </div>

            <div class="payment-info-grid">
              <div class="info-column">
                <h3>From ({{ selectedPayment.sender_name || selectedPayment.customerName || 'N/A' }})</h3>
                <div class="detail-row">
                  <span class="detail-label">Sender Name:</span>
                  <span class="detail-value">{{ selectedPayment.sender_name || selectedPayment.customerName || 'N/A' }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Account Number:</span>
                  <span class="detail-value">{{ selectedPayment.sender_account || 'N/A' }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Reference Number:</span>
                  <span class="detail-value">{{ selectedPayment.reference_number || 'N/A' }}</span>
                </div>
              </div>

              <div class="info-column">
                <h3>To(Casa Vera Furniture)</h3>
                <div class="detail-row">
                  <span class="detail-label">Method:</span>
                  <span class="detail-value">{{ selectedPayment.payment_method?.name || selectedPayment.method.toUpperCase() }}</span>
                </div>
                <div v-if="selectedPayment.payment_method?.account_details?.account_number" class="detail-row">
                  <span class="detail-label">Account Number:</span>
                  <span class="detail-value">{{ selectedPayment.payment_method.account_details.account_number }}</span>
                </div>
                <div v-if="selectedPayment.payment_method?.account_details?.account_name" class="detail-row">
                  <span class="detail-label">Account Name:</span>
                  <span class="detail-value">{{ selectedPayment.payment_method.account_details.account_name }}</span>
                </div>
              </div>
            </div>

            <div v-if="selectedPayment.proof_image" class="detail-section proof-section">
              <h3>Proof of Payment</h3>
              <div class="proof-image-container">
                <img :src="selectedPayment.proof_image" alt="Proof of Payment" class="proof-image">
              </div>
            </div>

            <div v-if="selectedPayment.verification_notes" class="detail-section">
              <h3>Verification Notes</h3>
              <p class="verification-notes">{{ selectedPayment.verification_notes }}</p>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secondary" @click.stop="closeDetailsModal">Close</button>
            <button v-if="selectedPayment.status.toLowerCase() === 'pending' || selectedPayment.status.toLowerCase() === 'awaiting_verification'" class="btn-primary" @click.stop="() => { closeDetailsModal(); openVerifyModal(selectedPayment); }">Verify</button>
          </div>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div v-if="showVerifyModal && selectedPayment" class="modal-overlay" @click.self="closeVerifyModal">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h2>Verify Payment</h2>
            <button class="close-btn" @click.stop="closeVerifyModal">×</button>
          </div>
          <form @submit.prevent="confirmVerify" class="modal-body">
            <div class="form-group">
              <label>Transaction ID</label>
              <input :value="selectedPayment.transactionId" type="text" disabled>
            </div>
            <div class="form-group">
              <label>Amount</label>
              <input :value="`₱${formatPrice(selectedPayment.amount)}`" type="text" disabled>
            </div>
            <div class="form-group">
              <label>Verification Notes (Optional)</label>
              <textarea v-model="verifyNotes" rows="4" placeholder="Add any notes about this verification..." :disabled="isProcessing"></textarea>
            </div>
            <div class="form-actions">
              <button type="button" class="btn-secondary" @click.stop="closeVerifyModal" :disabled="isProcessing">Cancel</button>
              <button type="submit" class="btn-primary" :disabled="isProcessing">
                {{ isProcessing ? 'Verifying...' : 'Verify Payment' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div v-if="showRejectModal && selectedPayment" class="modal-overlay" @click.self="closeRejectModal">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h2>Reject Payment</h2>
            <button class="close-btn" @click.stop="closeRejectModal">×</button>
          </div>
          <form @submit.prevent="confirmReject" class="modal-body">
            <div class="form-group">
              <label>Transaction ID</label>
              <input :value="selectedPayment.transactionId" type="text" disabled>
            </div>
            <div class="form-group">
              <label>Amount</label>
              <input :value="`₱${formatPrice(selectedPayment.amount)}`" type="text" disabled>
            </div>
            <div class="form-group">
              <label>Rejection Reason *</label>
              <textarea v-model="rejectReason" rows="4" placeholder="Enter reason for rejecting this payment..." required :disabled="isProcessing"></textarea>
            </div>
            <div class="form-actions">
              <button type="button" class="btn-secondary" @click.stop="closeRejectModal" :disabled="isProcessing">Cancel</button>
              <button type="submit" class="btn-danger" :disabled="isProcessing || !rejectReason.trim()">
                {{ isProcessing ? 'Rejecting...' : 'Reject Payment' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, onUnmounted } from 'vue'
import { Teleport } from 'vue'
import { payments as paymentsApi, settings as settingsApi } from '@/services/adminApi'
import { useNotification } from '@/composables/useNotification'

const { success, error: showError, info } = useNotification()

interface Payment {
  id: number
  transaction_id: string
  transactionId: string
  order_id: number
  orderId: number
  order_number?: string
  customer_name?: string
  customerName: string
  amount: number
  payment_method?: any
  method: string
  status: string
  created_at: string | Date
  date: Date
  reference_number?: string
  sender_name?: string
  sender_account?: string
  proof_image?: string
  verification_notes?: string
  verified_at?: string
  verified_by?: any
  failure_reason?: string
  failure_code?: string
}

interface PaymentMethod {
  id: number
  name: string
  code: string
}

const filterStatus = ref('')
const filterMethod = ref('')
const payments = ref<Payment[]>([])
const paymentMethods = ref<PaymentMethod[]>([])
const isLoading = ref(false)
const error = ref<string | null>(null)
const currentPage = ref(1)
const totalPayments = ref(0)

const showDetailsModal = ref(false)
const showVerifyModal = ref(false)
const showRejectModal = ref(false)
const selectedPayment = ref<Payment | null>(null)
const verifyNotes = ref('')
const rejectReason = ref('')
const isProcessing = ref(false)

const loadPaymentMethods = async () => {
  try {
    const response = await settingsApi.getPaymentMethods()
    if (response.data.success) {
      paymentMethods.value = response.data.data || []
    }
  } catch (err) {
    console.error('Failed to load payment methods:', err)
  }
}

const loadPayments = async () => {
  isLoading.value = true
  error.value = null
  try {
    const params: any = {
      page: currentPage.value,
      per_page: 20,
    }

    if (filterStatus.value) {
      params.status = filterStatus.value
    }

    if (filterMethod.value) {
      if (['online_payment', 'bank_transfer', 'cod'].includes(filterMethod.value)) {
        params.payment_category = filterMethod.value
      } else {
        params.payment_method_id = parseInt(filterMethod.value)
      }
    }

    const response = await paymentsApi.list(params)

    if (response.data.success) {
      const data = response.data.data
      const paymentsData = data.data || data || []

      payments.value = paymentsData.map((p: any) => ({
        id: p.id,
        transaction_id: p.transaction_id,
        transactionId: p.transaction_id,
        order_id: p.order_id,
        orderId: p.order_id,
        order_number: p.order?.order_number || `#${p.order_id}`,
        customer_name: p.order?.customer_name || p.user?.first_name + ' ' + p.user?.last_name || 'N/A',
        customerName: p.order?.customer_name || p.user?.first_name + ' ' + p.user?.last_name || 'N/A',
        amount: parseFloat(p.amount || 0),
        payment_method: p.payment_method,
        method: p.payment_method?.code || p.payment_method_name?.toLowerCase() || 'cod',
        status: p.status || 'pending',
        created_at: p.created_at,
        date: new Date(p.created_at),
        reference_number: p.reference_number,
        sender_name: p.sender_name,
        sender_account: p.sender_account,
        proof_image: p.proof_image,
        verification_notes: p.verification_notes,
        verified_at: p.verified_at,
        verified_by: p.verified_by,
      }))
      totalPayments.value = data.total || paymentsData.length || 0
    } else {
      throw new Error(response.data.message || 'Failed to load payments')
    }
  } catch (err: any) {
    console.error('Failed to load payments:', err)
    error.value = err.response?.data?.message || err.message || 'Failed to load payments. Please try again.'
    showError('Failed to Load Payments', error.value)
    payments.value = []
  } finally {
    isLoading.value = false
  }
}

const filteredPayments = computed(() => payments.value)

const resetFilters = () => {
  filterStatus.value = ''
  filterMethod.value = ''
  currentPage.value = 1
  loadPayments()
}

const formatPrice = (price: number) => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatDate = (date: Date | string) => {
  const d = typeof date === 'string' ? new Date(date) : date
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(d)
}

const getStatusClass = (status: string) => {
  const s = status.toLowerCase().replace('_', '-')
  if (s === 'confirmed') return 'confirmed'
  if (s === 'verified') return 'confirmed'
  if (s === 'rejected') return 'failed'
  return s
}

const getStatusLabel = (status: string) => {
  const s = status.toLowerCase()
  if (s === 'verified') return 'Confirmed'
  if (s === 'rejected') return 'Failed'
  return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const viewDetails = async (id: number) => {
  try {
    const response = await paymentsApi.get(id)
    if (response.data.success) {
      const p = response.data.data
      selectedPayment.value = {
        id: p.id,
        transaction_id: p.transaction_id,
        transactionId: p.transaction_id,
        order_id: p.order_id,
        orderId: p.order_id,
        order_number: p.order?.order_number || `#${p.order_id}`,
        customer_name: p.order?.customer_name || p.user?.first_name + ' ' + p.user?.last_name || 'N/A',
        customerName: p.order?.customer_name || p.user?.first_name + ' ' + p.user?.last_name || 'N/A',
        amount: parseFloat(p.amount || 0),
        payment_method: p.payment_method,
        method: p.payment_method?.code || p.payment_method_name?.toLowerCase() || 'cod',
        status: p.status || 'pending',
        created_at: p.created_at,
        date: new Date(p.created_at),
        reference_number: p.reference_number,
        sender_name: p.sender_name,
        sender_account: p.sender_account,
        proof_image: p.proof_image,
        verification_notes: p.verification_notes,
        verified_at: p.verified_at,
        verified_by: p.verified_by,
      }
      showDetailsModal.value = true
      document.body.style.overflow = 'hidden'
    }
  } catch (err: any) {
    console.error('Failed to load payment details:', err)
    showError('Failed to Load Details', err.response?.data?.message || err.message || 'Failed to load payment details.')
  }
}

const closeDetailsModal = () => {
  showDetailsModal.value = false
  selectedPayment.value = null
  document.body.style.overflow = ''
}

const openVerifyModal = (payment: Payment) => {
  selectedPayment.value = payment
  verifyNotes.value = ''
  showVerifyModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeVerifyModal = () => {
  if (isProcessing.value) return
  showVerifyModal.value = false
  selectedPayment.value = null
  verifyNotes.value = ''
  document.body.style.overflow = ''
}

const confirmVerify = async () => {
  if (!selectedPayment.value) return

  const status = selectedPayment.value.status.toLowerCase()
  if (status !== 'pending' && status !== 'awaiting_verification') {
    showError(
      'Cannot Verify Payment',
      'Only pending or awaiting verification payments can be verified.'
    )
    return
  }

  isProcessing.value = true

  try {
    const response = await paymentsApi.verify(selectedPayment.value.id, verifyNotes.value || undefined)

    if (response.data.success) {
      const transactionId = selectedPayment.value.transactionId

      const payment = payments.value.find(p => p.id === selectedPayment.value.id)
      if (payment) {
        payment.status = 'confirmed'
        if (response.data.data) {
          const updatedPayment = response.data.data
          payment.verification_notes = updatedPayment.verification_notes
          payment.verified_at = updatedPayment.verified_at
          payment.verified_by = updatedPayment.verified_by
        }
      }

      closeVerifyModal()

      success(
        'Payment Verified',
        `Payment ${transactionId} has been verified and confirmed successfully.`
      )

      await loadPayments()
    } else {
      throw new Error(response.data.message || 'Failed to verify payment')
    }
  } catch (err: any) {
    console.error('Failed to verify payment:', err)
    showError(
      'Failed to Verify Payment',
      err.response?.data?.message || err.message || 'Failed to verify payment. Please try again.'
    )
  } finally {
    isProcessing.value = false
  }
}

const openRejectModal = (payment: Payment) => {
  selectedPayment.value = payment
  rejectReason.value = ''
  showRejectModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeRejectModal = () => {
  if (isProcessing.value) return
  showRejectModal.value = false
  selectedPayment.value = null
  rejectReason.value = ''
  document.body.style.overflow = ''
}

const confirmReject = async () => {
  if (!selectedPayment.value || !rejectReason.value.trim()) {
    showError('Validation Error', 'Rejection reason is required.')
    return
  }

  const status = selectedPayment.value.status.toLowerCase()
  if (status !== 'pending' && status !== 'awaiting_verification') {
    showError(
      'Cannot Reject Payment',
      'Only pending or awaiting verification payments can be rejected.'
    )
    return
  }

  isProcessing.value = true

  try {
    const response = await paymentsApi.reject(selectedPayment.value.id, rejectReason.value.trim())

    if (response.data.success) {
      const transactionId = selectedPayment.value.transactionId

      const payment = payments.value.find(p => p.id === selectedPayment.value.id)
      if (payment) {
        payment.status = 'failed'
        if (response.data.data) {
          const updatedPayment = response.data.data
          payment.failure_reason = updatedPayment.failure_reason
          payment.failure_code = updatedPayment.failure_code
        }
      }

      closeRejectModal()

      success(
        'Payment Rejected',
        `Payment ${transactionId} has been rejected.`
      )

      await loadPayments()
    } else {
      throw new Error(response.data.message || 'Failed to reject payment')
    }
  } catch (err: any) {
    console.error('Failed to reject payment:', err)
    showError(
      'Failed to Reject Payment',
      err.response?.data?.message || err.message || 'Failed to reject payment. Please try again.'
    )
  } finally {
    isProcessing.value = false
  }
}

watch([filterStatus, filterMethod], () => {
  currentPage.value = 1
  loadPayments()
})

watch(currentPage, () => {
  loadPayments()
})

onMounted(async () => {
  await loadPaymentMethods()
  await loadPayments()
})

onUnmounted(() => {
  document.body.style.overflow = ''
})
</script>

<style scoped>
.admin-payments-page {
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
  margin-bottom: 2rem;
}

.page-title {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  font-weight: 700;
  color: var(--dark);
  margin: 0 0 0.5rem;
  transition: color 0.3s ease;
}

.page-subtitle {
  color: #374151;
  font-size: 0.95rem;
  margin: 0;
  transition: color 0.3s ease;
}

.filters-bar {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.filter-select {
  padding: 0.875rem 1.25rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  background: var(--white);
  font-size: 0.9rem;
  color: var(--dark);
  transition: all 0.3s ease;
}

.filter-select:focus {
  outline: none;
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(201, 160, 80, 0.1);
}

.btn-reset {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  background: var(--white);
  color: var(--gray);
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-reset:hover {
  background: #f9fafb;
  color: var(--dark);
  border-color: #d1d5db;
}

.btn-reset svg {
  width: 18px;
  height: 18px;
}

.table-card {
  background: var(--white);
  border-radius: 16px;
  padding: 1.5rem;
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
  padding: 0.75rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #6b7280;
  border-bottom: 2px solid #e5e7eb;
}

.data-table td {
  padding: 1rem 0.75rem;
  border-bottom: 1px solid #e5e7eb;
  color: var(--dark);
}

.transaction-id {
  font-family: monospace;
  color: var(--gold);
  font-weight: 600;
}

.order-id {
  color: var(--gold);
  font-weight: 600;
}

.amount {
  font-weight: 600;
}

.method-badge {
  padding: 0.35rem 0.75rem;
  background: #f3f4f6;
  color: var(--dark);
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
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
  background: #d1fae5;
  color: #065f46;
}

.status-badge.failed {
  background: #fee2e2;
  color: #991b1b;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-small {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-small.success {
  background: #d1fae5;
  color: #065f46;
}

.btn-small:not(.success) {
  background: #f3f4f6;
  color: var(--dark);
}

.btn-small.danger {
  background: #fee2e2;
  color: #991b1b;
}

.btn-small.danger:hover {
  background: #fecaca;
}

.error-message {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
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
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  animation: slideUp 0.3s ease;
}

.details-modal {
  max-width: 700px;
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

.detail-section {
  margin-bottom: 2rem;
}

.detail-section:last-child {
  margin-bottom: 0;
}

.payment-info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: #f9fafb;
  border-radius: 12px;
}

.info-column h3 {
  font-size: 1rem;
  font-weight: 700;
  color: #000000;
  margin: 0 0 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e5e7eb;
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.proof-section {
  text-align: center;
}

.proof-image-container {
  display: inline-block;
  padding: 0.5rem;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.proof-image {
  display: block;
  width: 100%;
  max-width: 450px;
  height: auto;
  border-radius: 8px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  border-bottom: 1px solid #e5e7eb;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  font-weight: 600;
  color: #6b7280;
}

.detail-value {
  color: #000000;
  text-align: right;
  word-break: break-all;
  padding-left: 1rem;
}

.detail-value.amount {
  font-weight: 700;
  font-size: 1.1rem;
  color: #b8860b;
}

.verification-notes {
  padding: 1rem;
  background: #f9fafb;
  border-radius: 8px;
  color: #000000;
  margin: 0;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: #000000;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #000000;
  background: #ffffff;
}

.form-group input:disabled,
.form-group textarea:disabled {
  background: #f3f4f6;
  color: #000000;
  cursor: not-allowed;
}

.form-group textarea::placeholder {
  color: #9ca3af;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.modal-footer {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: #f3f4f6;
  color: #000000;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover:not(:disabled) {
  background: #e5e7eb;
  color: #000000;
}

.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  padding: 0.75rem 1.5rem;
  background: #000000;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary:hover:not(:disabled) {
  background: #1a1a1a;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.btn-danger {
  padding: 0.75rem 1.5rem;
  background: #dc2626;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-danger:hover:not(:disabled) {
  background: #b91c1c;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-danger:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.status-badge.awaiting-verification {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.rejected {
  background: #fee2e2;
  color: #991b1b;
}

.status-badge.cancelled {
  background: #f3f4f6;
  color: #6b7280;
}

</style>
