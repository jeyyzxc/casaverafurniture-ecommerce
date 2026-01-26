<template>
  <div class="admin-promotions-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Promotions & Discounts</h1>
        <p class="page-subtitle">Manage discount codes, flash sales, and special offers.</p>
      </div>
      <div class="header-actions">
        <button class="btn-primary" @click="openAddModal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 8v8M8 12h8"/>
          </svg>
          Create Promotion
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading promotions...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <div class="error-icon">⚠️</div>
      <h3>Failed to Load Promotions</h3>
      <p>{{ error }}</p>
      <button class="btn-primary" @click="loadPromotions">Try Again</button>
    </div>

    <!-- Promotions Grid -->
    <div v-else-if="!isLoading && !error" class="promotions-grid">
      <div v-if="promotions.length === 0" class="empty-state">
        <div class="empty-icon">🎁</div>
        <h3>No Promotions Yet</h3>
        <p>Create your first promotion to start offering discounts to customers.</p>
      </div>

      <template v-else>
        <div v-for="promo in promotions" :key="promo.id" class="promo-card" :class="{ active: promo.isActive, expired: promo.isExpired }">
        <div class="promo-header">
          <div class="promo-code">{{ promo.code }}</div>
          <div class="promo-discount">{{ promo.discountType === 'percentage' ? `${promo.value}%` : `₱${formatPrice(promo.value)}` }} OFF</div>
        </div>
        <div class="promo-body">
          <h3 class="promo-name">{{ promo.name }}</h3>
          <p v-if="promo.description" class="promo-description">{{ promo.description }}</p>
          <div class="promo-details">
            <div class="detail-item">
              <span class="detail-label">Valid From:</span>
              <span class="detail-value">{{ formatDate(promo.startDate) }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Valid Until:</span>
              <span class="detail-value">{{ promo.endDate ? formatDate(promo.endDate) : 'No expiry' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Usage Limit:</span>
              <span class="detail-value">{{ promo.usageLimit || 'Unlimited' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Used:</span>
              <span class="detail-value">{{ promo.usedCount }} / {{ promo.usageLimit || '∞' }}</span>
            </div>
            <div v-if="promo.minOrderAmount" class="detail-item">
              <span class="detail-label">Min Order:</span>
              <span class="detail-value">₱{{ formatPrice(promo.minOrderAmount) }}</span>
            </div>
          </div>
          <div class="promo-actions">
            <button class="btn-small" @click="editPromo(promo)">Edit</button>
            <button class="btn-small danger" @click="deletePromo(promo.id)">Delete</button>
            <label class="toggle-switch">
              <input type="checkbox" :checked="promo.isActive" @change="togglePromo(promo)">
              <span class="toggle-slider"></span>
            </label>
          </div>
        </div>
      </div>
      </template>
    </div>

    <!-- Add/Edit Promotion Modal -->
    <Teleport to="body">
      <div v-if="showAddModal || editingPromo" class="modal-overlay" @click="closeModal">
        <div class="modal-container" @click.stop>
          <div class="modal-header">
            <h2>{{ editingPromo ? 'Edit Promotion' : 'Create New Promotion' }}</h2>
            <button class="btn-close" @click="closeModal">×</button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Name *</label>
              <input v-model="promoForm.name" type="text" placeholder="Promotion name" required />
            </div>
            <div class="form-group">
              <label>Code *</label>
              <input v-model="promoForm.code" type="text" placeholder="PROMOCODE" required style="text-transform: uppercase;" />
            </div>
            <div class="form-group">
              <label>Description</label>
              <textarea v-model="promoForm.description" rows="3" placeholder="Promotion description"></textarea>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Discount Type *</label>
                <select v-model="promoForm.discountType" required>
                  <option value="percentage">Percentage</option>
                  <option value="fixed">Fixed Amount</option>
                  <option value="free_shipping">Free Shipping</option>
                </select>
              </div>
              <div class="form-group">
                <label>Discount Value *</label>
                <input v-model.number="promoForm.value" type="number" min="0" step="0.01" placeholder="0" required />
              </div>
            </div>
            <div v-if="promoForm.discountType === 'percentage'" class="form-group">
              <label>Max Discount Amount (Optional)</label>
              <input v-model.number="promoForm.maxDiscountAmount" type="number" min="0" step="0.01" placeholder="No limit" />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Start Date *</label>
                <input v-model="promoForm.startDate" type="datetime-local" required />
              </div>
              <div class="form-group">
                <label>End Date</label>
                <input v-model="promoForm.endDate" type="datetime-local" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Usage Limit (Total)</label>
                <input v-model.number="promoForm.usageLimit" type="number" min="1" placeholder="Unlimited" />
              </div>
              <div class="form-group">
                <label>Usage Limit Per User</label>
                <input v-model.number="promoForm.usageLimitPerUser" type="number" min="1" placeholder="Unlimited" />
              </div>
            </div>
            <div class="form-group">
              <label>Minimum Order Amount</label>
              <input v-model.number="promoForm.minOrderAmount" type="number" min="0" step="0.01" placeholder="No minimum" />
            </div>
            <div class="form-group">
              <label>Applies To</label>
              <select v-model="promoForm.appliesTo">
                <option value="all">All Products</option>
                <option value="specific_products">Specific Products</option>
                <option value="specific_categories">Specific Categories</option>
                <option value="specific_collections">Specific Collections</option>
              </select>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>
                  <input type="checkbox" v-model="promoForm.isActive" />
                  Active
                </label>
              </div>
              <div class="form-group">
                <label>
                  <input type="checkbox" v-model="promoForm.isVisible" />
                  Visible to Customers
                </label>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>
                  <input type="checkbox" v-model="promoForm.firstOrderOnly" />
                  First Order Only
                </label>
              </div>
              <div class="form-group">
                <label>
                  <input type="checkbox" v-model="promoForm.autoApply" />
                  Auto Apply
                </label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secondary" @click="closeModal">Cancel</button>
            <button class="btn-primary" @click="savePromotion" :disabled="isSaving">
              {{ isSaving ? 'Saving...' : (editingPromo ? 'Update' : 'Create') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <div 
        v-if="showDeleteModal" 
        class="modal-overlay delete-modal-overlay" 
        @click.self="closeDeleteModal"
        @keydown.esc="closeDeleteModal"
      >
        <div class="modal-container delete-modal" @click.stop>
          <div class="delete-modal-content">
            <div class="delete-icon-wrapper">
              <div class="delete-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="3 6 5 6 21 6"/>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                  <line x1="10" y1="11" x2="10" y2="17"/>
                  <line x1="14" y1="11" x2="14" y2="17"/>
                </svg>
              </div>
            </div>
            <h2 class="delete-title">Delete Promotion</h2>
            <p class="delete-message">
              Are you sure you want to delete
              <strong class="delete-item-name">{{ deletingPromo?.name || 'this promotion' }}</strong>?
            </p>
            <p class="delete-warning">
              This action cannot be undone. All promotion data will be permanently removed.
            </p>
            <div class="delete-actions">
              <button 
                type="button" 
                class="delete-btn-cancel" 
                @click.stop="closeDeleteModal" 
                :disabled="isDeleting"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/>
                  <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Cancel
              </button>
              <button 
                type="button" 
                class="delete-btn-confirm" 
                @click.stop="confirmDelete" 
                :disabled="isDeleting"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                {{ isDeleting ? 'Deleting...' : 'Delete Promotion' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { promotions as promotionsApi } from '@/services/adminApi'
import { useNotification } from '@/composables/useNotification'

const { success, error: showError } = useNotification()

interface Promotion {
  id: number
  name: string
  code: string
  description?: string
  discountType: 'percentage' | 'fixed' | 'free_shipping' | 'buy_x_get_y'
  value: number
  maxDiscountAmount?: number
  startDate: string
  endDate?: string
  usageLimit?: number
  usageLimitPerUser?: number
  usedCount: number
  isActive: boolean
  isExpired: boolean
  minOrderAmount?: number
  appliesTo: string
  firstOrderOnly?: boolean
  autoApply?: boolean
  isVisible?: boolean
}

const isLoading = ref(false)
const isSaving = ref(false)
const isDeleting = ref(false)
const error = ref<string | null>(null)

const promotions = ref<Promotion[]>([])
const showAddModal = ref(false)
const editingPromo = ref<Promotion | null>(null)
const showDeleteModal = ref(false)
const deletingPromo = ref<Promotion | null>(null)

const promoForm = ref({
  name: '',
  code: '',
  description: '',
  discountType: 'percentage' as 'percentage' | 'fixed' | 'free_shipping',
  value: 0,
  maxDiscountAmount: undefined as number | undefined,
  startDate: '',
  endDate: '',
  usageLimit: undefined as number | undefined,
  usageLimitPerUser: undefined as number | undefined,
  minOrderAmount: undefined as number | undefined,
  appliesTo: 'all',
  isActive: true,
  isVisible: true,
  firstOrderOnly: false,
  autoApply: false,
})

const formatPrice = (price: number): string => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatDate = (dateString: string): string => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date)
}

const loadPromotions = async () => {
  isLoading.value = true
  error.value = null
  // Close any open modals
  showAddModal.value = false
  showDeleteModal.value = false
  editingPromo.value = null
  deletingPromo.value = null
  document.body.style.overflow = ''

  try {
    const response = await promotionsApi.list({ per_page: 100 })
    if (response.data.success) {
      // Handle both paginated and non-paginated responses
      const data = response.data.data
      if (data.data && Array.isArray(data.data)) {
        // Paginated response
        promotions.value = data.data
      } else if (Array.isArray(data)) {
        // Direct array response
        promotions.value = data
      } else {
        promotions.value = []
      }
    } else {
      throw new Error(response.data.message || 'Failed to load promotions')
    }
  } catch (err: any) {
    console.error('Failed to load promotions:', err)
    error.value = err.response?.data?.message || err.message || 'Failed to load promotions. Please try again.'
    // Don't show error notification on initial load to avoid spam
    if (promotions.value.length > 0) {
      showError('Failed to Load', error.value)
    }
  } finally {
    isLoading.value = false
  }
}

const openAddModal = () => {
  editingPromo.value = null
  // Set default start date to current date/time
  const now = new Date()
  const year = now.getFullYear()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const day = String(now.getDate()).padStart(2, '0')
  const hours = String(now.getHours()).padStart(2, '0')
  const minutes = String(now.getMinutes()).padStart(2, '0')
  const defaultStartDate = `${year}-${month}-${day}T${hours}:${minutes}`
  
  promoForm.value = {
    name: '',
    code: '',
    description: '',
    discountType: 'percentage',
    value: 0,
    maxDiscountAmount: undefined,
    startDate: defaultStartDate,
    endDate: '',
    usageLimit: undefined,
    usageLimitPerUser: undefined,
    minOrderAmount: undefined,
    appliesTo: 'all',
    isActive: true,
    isVisible: true,
    firstOrderOnly: false,
    autoApply: false,
  }
  showAddModal.value = true
  // Prevent body scroll when modal is open
  document.body.style.overflow = 'hidden'
}

const editPromo = (promo: Promotion) => {
  editingPromo.value = promo
  // Format dates for datetime-local input
  const formatDateForInput = (dateString: string | undefined): string => {
    if (!dateString) return ''
    const date = new Date(dateString)
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    const hours = String(date.getHours()).padStart(2, '0')
    const minutes = String(date.getMinutes()).padStart(2, '0')
    return `${year}-${month}-${day}T${hours}:${minutes}`
  }
  
  promoForm.value = {
    name: promo.name,
    code: promo.code,
    description: promo.description || '',
    discountType: promo.discountType,
    value: promo.value,
    maxDiscountAmount: promo.maxDiscountAmount,
    startDate: formatDateForInput(promo.startDate),
    endDate: formatDateForInput(promo.endDate),
    usageLimit: promo.usageLimit,
    usageLimitPerUser: promo.usageLimitPerUser,
    minOrderAmount: promo.minOrderAmount,
    appliesTo: promo.appliesTo,
    isActive: promo.isActive,
    isVisible: promo.isVisible !== undefined ? promo.isVisible : true,
    firstOrderOnly: promo.firstOrderOnly || false,
    autoApply: promo.autoApply || false,
  }
  showAddModal.value = true
  // Prevent body scroll when modal is open
  document.body.style.overflow = 'hidden'
}

const closeModal = () => {
  showAddModal.value = false
  editingPromo.value = null
  // Restore body scroll
  document.body.style.overflow = ''
}

const savePromotion = async () => {
  // Validate required fields
  if (!promoForm.value.name || !promoForm.value.code || !promoForm.value.discountType || !promoForm.value.startDate) {
    showError('Validation Failed', 'Please fill in all required fields (Name, Code, Discount Type, and Start Date).')
    return
  }

  // Validate discount value
  if (!promoForm.value.value || promoForm.value.value <= 0) {
    showError('Validation Failed', 'Discount value must be greater than 0.')
    return
  }

  // Validate code format (alphanumeric and uppercase)
  const codeRegex = /^[A-Z0-9]+$/
  if (!codeRegex.test(promoForm.value.code.toUpperCase())) {
    showError('Validation Failed', 'Promotion code must contain only letters and numbers.')
    return
  }

  isSaving.value = true

  try {
    // Convert datetime-local to ISO format for backend
    const startDate = promoForm.value.startDate ? new Date(promoForm.value.startDate).toISOString() : null
    const endDate = promoForm.value.endDate ? new Date(promoForm.value.endDate).toISOString() : null

    const formData: any = {
      name: promoForm.value.name,
      code: promoForm.value.code.toUpperCase(),
      description: promoForm.value.description,
      discount_type: promoForm.value.discountType,
      discount_value: promoForm.value.value,
      starts_at: startDate,
      applies_to: promoForm.value.appliesTo,
      is_active: promoForm.value.isActive,
      is_visible: promoForm.value.isVisible,
      first_order_only: promoForm.value.firstOrderOnly,
      auto_apply: promoForm.value.autoApply,
    }

    if (promoForm.value.maxDiscountAmount) {
      formData.max_discount_amount = promoForm.value.maxDiscountAmount
    }
    if (endDate) {
      formData.ends_at = endDate
    }
    if (promoForm.value.usageLimit) {
      formData.usage_limit = promoForm.value.usageLimit
    }
    if (promoForm.value.usageLimitPerUser) {
      formData.usage_limit_per_user = promoForm.value.usageLimitPerUser
    }
    if (promoForm.value.minOrderAmount) {
      formData.min_order_amount = promoForm.value.minOrderAmount
    }

    let response
    if (editingPromo.value) {
      response = await promotionsApi.update(editingPromo.value.id, formData)
    } else {
      response = await promotionsApi.create(formData)
    }

    if (response.data.success) {
      success(
        editingPromo.value ? 'Promotion Updated' : 'Promotion Created',
        `Promotion "${promoForm.value.name}" has been ${editingPromo.value ? 'updated' : 'created'} successfully.`
      )
      closeModal()
      await loadPromotions()
    } else {
      throw new Error(response.data.message || 'Failed to save promotion')
    }
  } catch (err: any) {
    console.error('Failed to save promotion:', err)
    const errorMessage = err.response?.data?.message || err.message || 'Failed to save promotion. Please try again.'
    if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      showError('Validation Failed', errors.join(', '))
    } else {
      showError('Failed to Save', errorMessage)
    }
  } finally {
    isSaving.value = false
  }
}

const deletePromo = (id: number) => {
  // Prevent opening delete modal if already deleting
  if (isDeleting.value) return
  
  const promo = promotions.value.find(p => p.id === id)
  if (promo) {
    deletingPromo.value = promo
    showDeleteModal.value = true
    document.body.style.overflow = 'hidden'
  } else {
    showError('Error', 'Promotion not found.')
  }
}

const confirmDelete = async () => {
  if (!deletingPromo.value) {
    closeDeleteModal()
    return
  }

  isDeleting.value = true

  try {
    const response = await promotionsApi.delete(deletingPromo.value.id)
    if (response.data.success) {
      success('Promotion Deleted', `Promotion "${deletingPromo.value.name}" has been deleted successfully.`)
      closeDeleteModal()
      await loadPromotions()
    } else {
      throw new Error(response.data.message || 'Failed to delete promotion')
    }
  } catch (err: any) {
    console.error('Failed to delete promotion:', err)
    showError('Failed to Delete', err.response?.data?.message || err.message || 'Failed to delete promotion. Please try again.')
  } finally {
    isDeleting.value = false
  }
}

const closeDeleteModal = () => {
  if (isDeleting.value) {
    // Prevent closing while deleting
    return
  }
  showDeleteModal.value = false
  deletingPromo.value = null
  document.body.style.overflow = ''
}

const togglePromo = async (promo: Promotion) => {
  // Store original state
  const originalState = promo.isActive
  
  // Optimistically update UI
  promo.isActive = !promo.isActive
  
  try {
    const response = await promotionsApi.toggle(promo.id)
    if (response.data.success) {
      // Update local state with server response
      const index = promotions.value.findIndex(p => p.id === promo.id)
      if (index !== -1) {
        promotions.value[index] = response.data.data
      }
      success(
        response.data.data.isActive ? 'Promotion Activated' : 'Promotion Deactivated',
        `Promotion "${promo.name}" has been ${response.data.data.isActive ? 'activated' : 'deactivated'}.`
      )
    } else {
      // Revert on failure
      promo.isActive = originalState
      throw new Error(response.data.message || 'Failed to toggle promotion')
    }
  } catch (err: any) {
    console.error('Failed to toggle promotion:', err)
    // Revert toggle on error
    promo.isActive = originalState
    showError('Failed to Update', err.response?.data?.message || err.message || 'Failed to update promotion status. Please try again.')
  }
}

// Cleanup function
const cleanup = () => {
  showAddModal.value = false
  showDeleteModal.value = false
  editingPromo.value = null
  deletingPromo.value = null
  document.body.style.overflow = ''
}

onMounted(() => {
  // Ensure all modals are closed on mount
  cleanup()
  
  // Load promotions
  loadPromotions()
})

onBeforeUnmount(() => {
  // Cleanup on component unmount
  cleanup()
})
</script>

<style scoped>
.admin-promotions-page {
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
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
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

.btn-primary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: var(--gold);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background: #b8860b;
  transform: translateY(-1px);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.modal-footer .btn-primary {
  background: #000000;
  color: white;
}

.modal-footer .btn-primary:hover:not(:disabled) {
  background: #333333;
  transform: translateY(-1px);
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

@keyframes spin {
  to { transform: rotate(360deg); }
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
  margin-bottom: 1.5rem;
}

.promotions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
}

.promo-card {
  background: var(--white);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  border: 2px solid transparent;
  transition: all 0.3s ease;
}

.promo-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.promo-card.active {
  border-color: var(--gold);
}

.promo-card.expired {
  opacity: 0.6;
}

.promo-header {
  background: linear-gradient(135deg, var(--gold), #b8860b);
  color: white;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.promo-code {
  font-family: monospace;
  font-size: 1.25rem;
  font-weight: 700;
  letter-spacing: 2px;
}

.promo-discount {
  font-size: 1.5rem;
  font-weight: 700;
}

.promo-body {
  padding: 1.5rem;
}

.promo-name {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0 0 0.5rem;
  color: var(--dark);
}

.promo-description {
  color: #6b7280;
  margin: 0 0 1rem;
}

.promo-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.detail-item {
  display: flex;
  justify-content: space-between;
}

.detail-label {
  color: #6b7280;
  font-size: 0.9rem;
}

.detail-value {
  font-weight: 600;
  color: var(--dark);
}

.promo-actions {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.btn-small {
  padding: 0.5rem 1rem;
  background: #f3f4f6;
  color: var(--dark);
  border: none;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-small:hover {
  background: #e5e7eb;
}

.btn-small.danger {
  background: #fee2e2;
  color: #991b1b;
}

.btn-small.danger:hover {
  background: #fecaca;
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 48px;
  height: 24px;
  margin-left: auto;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: 0.3s;
  border-radius: 24px;
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.3s;
  border-radius: 50%;
}

input:checked + .toggle-slider {
  background-color: var(--gold);
}

input:checked + .toggle-slider:before {
  transform: translateX(24px);
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 2rem;
  opacity: 1;
  visibility: visible;
  animation: fadeIn 0.3s ease;
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
  border-radius: 16px;
  width: 100%;
  max-width: 700px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  color: #000000;
  animation: slideUp 0.3s ease;
  position: relative;
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
  border-bottom: 2px solid #e5e7eb;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #000000;
}

.btn-close {
  background: none;
  border: none;
  font-size: 2rem;
  color: #6b7280;
  cursor: pointer;
  line-height: 1;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s;
}

.btn-close:hover {
  background: #f3f4f6;
  color: var(--dark);
}

.modal-body {
  padding: 1.5rem;
  color: #000000;
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
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #000000;
  transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(201, 160, 80, 0.1);
}

.form-group input[type="checkbox"] {
  width: auto;
  margin-right: 0.5rem;
}

.form-group select option {
  color: #000000;
  background: white;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
  color: #6b7280;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 2px solid #e5e7eb;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: #f3f4f6;
  color: var(--dark);
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

/* Delete Modal Styles */
.delete-modal-overlay {
  animation: fadeIn 0.3s ease;
}

.delete-modal {
  max-width: 480px;
  width: 100%;
  background: #ffffff;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(220, 53, 69, 0.1);
  animation: slideUpScale 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes slideUpScale {
  from {
    transform: scale(0.9) translateY(20px);
    opacity: 0;
  }
  to {
    transform: scale(1) translateY(0);
    opacity: 1;
  }
}

.delete-modal-content {
  padding: 2.5rem 2rem;
  text-align: center;
}

.delete-icon-wrapper {
  margin-bottom: 1.5rem;
  display: flex;
  justify-content: center;
}

.delete-icon-circle {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  border: 3px solid #fca5a5;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: pulse-delete 2s ease-in-out infinite;
  position: relative;
}

.delete-icon-circle::before {
  content: '';
  position: absolute;
  inset: -4px;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(220, 53, 69, 0.2) 0%, rgba(239, 68, 68, 0.1) 100%);
  z-index: -1;
  animation: ripple 2s ease-out infinite;
}

@keyframes pulse-delete {
  0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
  50% { transform: scale(1.05); box-shadow: 0 0 0 8px rgba(220, 53, 69, 0); }
}

@keyframes ripple {
  0% { transform: scale(1); opacity: 1; }
  100% { transform: scale(1.3); opacity: 0; }
}

.delete-icon-circle svg {
  width: 36px;
  height: 36px;
  color: #dc2626;
}

.delete-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.75rem;
  font-weight: 700;
  color: #1a1d29;
  margin: 0 0 1rem;
}

.delete-message {
  font-size: 1rem;
  color: #4b5563;
  margin: 0 0 0.5rem;
  line-height: 1.6;
}

.delete-item-name {
  color: #dc2626;
  font-weight: 700;
  font-size: 1.1rem;
}

.delete-warning {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0 0 2rem;
  padding: 1rem;
  background: #fef2f2;
  border-left: 3px solid #dc2626;
  border-radius: 8px;
  text-align: left;
}

.delete-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.delete-btn-cancel {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.75rem;
  border: 2px solid #d1d5db;
  border-radius: 12px;
  background: #ffffff;
  color: #374151;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.delete-btn-cancel:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.delete-btn-cancel:hover:not(:disabled) {
  border-color: #9ca3af;
  background: #f9fafb;
  color: #1f2937;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.delete-btn-cancel svg {
  width: 18px;
  height: 18px;
}

.delete-btn-cancel:hover {
  border-color: #9ca3af;
  background: #f9fafb;
  color: #1f2937;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.delete-btn-confirm {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.75rem;
  border: none;
  border-radius: 12px;
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  color: #ffffff;
  font-weight: 700;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.delete-btn-confirm:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.delete-btn-confirm:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

@media (max-width: 768px) {
  .admin-promotions-page {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .page-header {
    flex-direction: column;
  }

  .promotions-grid {
    grid-template-columns: 1fr;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .delete-modal-content {
    padding: 2rem 1.5rem;
  }

  .delete-actions {
    flex-direction: column;
  }

  .delete-btn-cancel,
  .delete-btn-confirm {
    width: 100%;
    justify-content: center;
  }
}
</style>
