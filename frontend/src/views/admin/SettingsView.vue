<template>
  <div class="admin-settings-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">System Settings</h1>
        <p class="page-subtitle">Configure store settings, payment methods, and system preferences.</p>
      </div>
    </div>

    <div class="settings-tabs">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        :class="['tab-btn', { active: activeTab === tab.id }]"
        @click="activeTab = tab.id"
      >
        <span class="tab-icon" v-html="tab.icon"></span>
        {{ tab.label }}
      </button>
    </div>

    <div class="settings-content">
      <!-- STORE SETTINGS -->
      <div v-if="activeTab === 'store'" class="settings-section fade-in">
        <div class="section-header">
          <h3>Store Information</h3>
          <p class="section-description">Manage your store's public profile and contact details.</p>
        </div>

        <div class="store-grid">
          <div class="store-logo-section">
            <label>Store Logo</label>
            <div class="logo-uploader" @click="triggerLogoUpload">
              <img v-if="storeSettings.logo" :src="storeSettings.logo" alt="Store Logo" class="logo-preview">
              <div v-else class="logo-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                  <circle cx="8.5" cy="8.5" r="1.5"></circle>
                  <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
                <span>Upload Logo</span>
              </div>
              <div v-if="isUploadingLogo" class="upload-overlay">
                <div class="spinner"></div>
              </div>
            </div>
            <input type="file" ref="logoInput" accept="image/*" style="display: none" @change="handleLogoUpload">
            <p class="help-text">Recommended size: 200x200px. Max 2MB.</p>
          </div>

          <div class="store-form">
            <div class="form-group">
              <label>Store Name</label>
              <input v-model="storeSettings.name" type="text" placeholder="Enter store name">
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Store Email</label>
                <input v-model="storeSettings.email" type="email" placeholder="Enter store email">
              </div>
              <div class="form-group">
                <label>Store Phone</label>
                <input v-model="storeSettings.phone" type="tel" placeholder="Enter store phone">
              </div>
            </div>
            <div class="form-group">
              <label>Store Address</label>
              <textarea v-model="storeSettings.address" rows="3" placeholder="Enter store address"></textarea>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button class="btn-primary" @click="saveSettings" :disabled="isSaving">
            <span v-if="isSaving" class="spinner-sm"></span>
            {{ isSaving ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>

      <!-- PAYMENT SETTINGS -->
      <div v-if="activeTab === 'payment'" class="settings-section fade-in">
        <div class="section-header">
          <h3>Payment Methods</h3>
          <p class="section-description">Manage available payment methods and their settings.</p>
        </div>

        <div v-if="isLoadingPaymentMethods" class="loading-state">
          <div class="spinner"></div>
          <span>Loading payment methods...</span>
        </div>

        <div v-else class="payment-methods-list">
          <div
            v-for="(method, index) in paymentMethods"
            :key="method.id"
            class="payment-method-item"
            :class="{ 'is-active': method.is_active, 'is-inactive': !method.is_active }"
          >
            <div class="drag-handle">
              <div class="order-controls">
                <button
                  class="btn-order"
                  :disabled="index === 0"
                  @click="reorderPaymentMethod(index, -1)"
                  title="Move Up"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"></polyline></svg>
                </button>
                <button
                  class="btn-order"
                  :disabled="index === paymentMethods.length - 1"
                  @click="reorderPaymentMethod(index, 1)"
                  title="Move Down"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
              </div>
            </div>
            <div class="method-main">
              <div class="method-info">
                <div class="method-header-row">
                  <h4 class="method-name">{{ method.name }}</h4>
                  <span class="method-type-badge" :class="`type-${method.type}`">
                    {{ formatPaymentType(method.type) }}
                  </span>
                  <span v-if="method.is_active" class="status-badge active">Active</span>
                  <span v-else class="status-badge inactive">Inactive</span>
                </div>
                <p class="method-description">{{ method.description }}</p>
                <div class="method-details">
                  <span v-if="method.fee_percentage > 0" class="fee-info">
                    Fee: {{ method.fee_percentage }}%
                  </span>
                  <span v-else-if="method.fee_fixed > 0" class="fee-info">
                    Fee: ₱{{ formatPrice(method.fee_fixed) }}
                  </span>
                  <span v-else class="fee-info no-fee">No fee</span>
                </div>
              </div>
              <div class="method-actions">
                <label class="switch">
                  <input type="checkbox" :checked="method.is_active" @change="togglePaymentMethod(method)">
                  <span class="slider round"></span>
                </label>
                <button class="btn-icon" @click="editPaymentMethod(method)" title="Edit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- SHIPPING SETTINGS -->
      <div v-if="activeTab === 'shipping'" class="settings-section fade-in">
        <div class="section-header">
          <h3>Shipping Configuration</h3>
          <p class="section-description">Set up global shipping rules and specific shipping methods.</p>
        </div>

        <div class="settings-card">
          <h4>Global Rules</h4>
          <div class="form-row">
            <div class="form-group">
              <label>Default Shipping Fee (₱)</label>
              <input v-model.number="shippingSettings.defaultFee" type="number" min="0">
            </div>
            <div class="form-group">
              <label>Free Shipping Threshold (₱)</label>
              <input v-model.number="shippingSettings.freeThreshold" type="number" min="0">
              <p class="help-text">Orders above this amount get free shipping.</p>
            </div>
          </div>
          <div class="form-actions">
            <button class="btn-primary" @click="saveSettings" :disabled="isSaving">
              <span v-if="isSaving" class="spinner-sm"></span>
              Save Global Rules
            </button>
          </div>
        </div>

        <div class="settings-card mt-4">
          <div class="card-header-row">
            <h4>Shipping Methods</h4>
            <button class="btn-secondary btn-sm" @click="openAddShippingModal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
              Add Method
            </button>
          </div>

          <div v-if="isLoadingShipping" class="loading-state">
            <div class="spinner"></div>
          </div>

          <div v-else-if="shippingMethods.length === 0" class="empty-state">
            <p>No shipping methods configured.</p>
          </div>

          <div v-else class="shipping-list">
            <div v-for="method in shippingMethods" :key="method.id" class="shipping-item">
              <div class="shipping-info">
                <div class="shipping-name">
                  {{ method.name }}
                  <span v-if="!method.is_active" class="badge-inactive">Inactive</span>
                </div>
                <div class="shipping-meta">
                  <span>Fee: ₱{{ formatPrice(method.amount) }}</span>
                  <span v-if="method.min_order || method.max_order">
                    Orders: ₱{{ method.min_order || 0 }} - {{ method.max_order ? '₱' + method.max_order : '∞' }}
                  </span>
                </div>
              </div>
              <div class="shipping-actions">
                <button class="btn-icon" @click="editShippingMethod(method)">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                </button>
                <button class="btn-icon delete" @click="deleteShippingMethod(method)">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- SYSTEM SETTINGS -->
      <div v-if="activeTab === 'system'" class="settings-section fade-in">
        <div class="section-header">
          <h3>System Preferences</h3>
          <p class="section-description">Configure system-wide behaviors and notifications.</p>
        </div>

        <div class="system-options">
          <div class="option-card">
            <div class="option-info">
              <h4>Maintenance Mode</h4>
              <p>Enable to show a maintenance page to customers. Admins can still access the site.</p>
            </div>
            <label class="switch">
              <input type="checkbox" v-model="systemSettings.maintenanceMode">
              <span class="slider round"></span>
            </label>
          </div>

          <div class="option-card">
            <div class="option-info">
              <h4>Email Notifications</h4>
              <p>Enable system-wide email notifications for orders and updates.</p>
            </div>
            <label class="switch">
              <input type="checkbox" v-model="systemSettings.emailNotifications">
              <span class="slider round"></span>
            </label>
          </div>
        </div>

        <div class="form-actions">
          <button class="btn-primary" @click="saveSettings" :disabled="isSaving">
            <span v-if="isSaving" class="spinner-sm"></span>
            Save Preferences
          </button>
        </div>
      </div>
    </div>

    <!-- Payment Method Modal -->
    <Teleport to="body">
      <div v-if="editingMethod" class="modal-overlay" @click="closeEditModal">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>{{ editingMethod.id ? 'Edit' : 'Add' }} Payment Method</h3>
            <button class="btn-close" @click="closeEditModal">×</button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Name *</label>
              <input v-model="editForm.name" type="text" required />
            </div>
            <div class="form-group">
              <label>Description</label>
              <textarea v-model="editForm.description" rows="2"></textarea>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Fixed Fee (₱)</label>
                <input v-model.number="editForm.fee_fixed" type="number" min="0" step="0.01" />
              </div>
              <div class="form-group">
                <label>Percentage Fee (%)</label>
                <input v-model.number="editForm.fee_percentage" type="number" min="0" max="100" step="0.01" />
              </div>
            </div>
            <div class="form-group">
              <label>Payment Instructions</label>
              <textarea v-model="editForm.payment_instructions" rows="4" placeholder="Instructions shown to customer..."></textarea>
            </div>
            <div class="checkbox-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="editForm.requires_proof_of_payment" />
                <span>Requires Proof of Payment (Screenshot)</span>
              </label>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secondary" @click="closeEditModal">Cancel</button>
            <button class="btn-primary" @click="savePaymentMethod" :disabled="isSaving">
              {{ isSaving ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Shipping Method Modal -->
    <Teleport to="body">
      <div v-if="showShippingModal" class="modal-overlay" @click="closeShippingModal">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>{{ editingShippingId ? 'Edit' : 'Add' }} Shipping Method</h3>
            <button class="btn-close" @click="closeShippingModal">×</button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Method Name *</label>
              <input v-model="shippingForm.name" type="text" required placeholder="e.g., Express Delivery" />
            </div>
            <div class="form-group">
              <label>Description</label>
              <textarea v-model="shippingForm.description" rows="2"></textarea>
            </div>
            <div class="form-group">
              <label>Shipping Fee (₱) *</label>
              <input v-model.number="shippingForm.amount" type="number" min="0" required />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Min Order Amount</label>
                <input v-model.number="shippingForm.min_order" type="number" min="0" />
              </div>
              <div class="form-group">
                <label>Max Order Amount</label>
                <input v-model.number="shippingForm.max_order" type="number" min="0" />
              </div>
            </div>
            <div class="checkbox-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="shippingForm.is_active" />
                <span>Active</span>
              </label>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secondary" @click="closeShippingModal">Cancel</button>
            <button class="btn-primary" @click="saveShippingMethod" :disabled="isSaving">
              {{ isSaving ? 'Saving...' : 'Save Method' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { settings as settingsApi, shipping as shippingApi, upload as uploadApi } from '@/services/adminApi'
import { useNotification } from '@/composables/useNotification'

const { success, error: showError } = useNotification()

const activeTab = ref('store')

const tabs = [
  {
    id: 'store',
    label: 'Store Info',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>'
  },
  {
    id: 'payment',
    label: 'Payments',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>'
  },
  {
    id: 'shipping',
    label: 'Shipping',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>'
  },
  {
    id: 'system',
    label: 'System',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>'
  }
]

// ==========================================
// STORE SETTINGS
// ==========================================
const storeSettings = ref({
  name: 'CASA VÉRA',
  email: 'info@casavera.com',
  phone: '+63 123 456 7890',
  address: '123 Furniture St, Manila, Philippines',
  logo: ''
})
const isUploadingLogo = ref(false)
const logoInput = ref<HTMLInputElement | null>(null)

const triggerLogoUpload = () => logoInput.value?.click()

const handleLogoUpload = async (event: Event) => {
  const target = event.target as HTMLInputElement
  if (!target.files?.length) return

  isUploadingLogo.value = true
  try {
    const file = target.files[0]
    const response = await uploadApi.image(file, 'settings')
    if (response.data.success) {
      storeSettings.value.logo = response.data.data.url
      success('Logo Uploaded', 'Store logo has been updated.')
    }
  } catch (err: any) {
    showError('Upload Failed', err.response?.data?.message || 'Failed to upload logo.')
  } finally {
    isUploadingLogo.value = false
  }
}

// ==========================================
// PAYMENT SETTINGS
// ==========================================
const paymentMethods = ref<any[]>([])
const isLoadingPaymentMethods = ref(false)
const editingMethod = ref<any>(null)
const editForm = ref<any>({})

const loadPaymentMethods = async () => {
  isLoadingPaymentMethods.value = true
  try {
    const response = await settingsApi.getPaymentMethods()
    if (response.data.success) {
      paymentMethods.value = (response.data.data || []).sort((a: any, b: any) => a.display_order - b.display_order)
    }
  } catch (err) {
    console.error(err)
  } finally {
    isLoadingPaymentMethods.value = false
  }
}

const togglePaymentMethod = async (method: any) => {
  try {
    const newState = !method.is_active
    const response = await settingsApi.updatePaymentMethod(method.id, { is_active: newState })
    if (response.data.success) {
      method.is_active = newState
      success('Updated', `${method.name} is now ${newState ? 'active' : 'inactive'}.`)
    }
  } catch (err) {
    method.is_active = !method.is_active // Revert
    showError('Error', 'Failed to update payment method.')
  }
}

const reorderPaymentMethod = async (index: number, direction: number) => {
  const newIndex = index + direction
  if (newIndex < 0 || newIndex >= paymentMethods.value.length) return

  // Swap in local array
  const temp = paymentMethods.value[index]
  paymentMethods.value[index] = paymentMethods.value[newIndex]
  paymentMethods.value[newIndex] = temp

  // Update display_order for swapped items
  const item1 = paymentMethods.value[index]
  const item2 = paymentMethods.value[newIndex]

  try {
    await Promise.all([
      settingsApi.updatePaymentMethod(item1.id, { display_order: index }),
      settingsApi.updatePaymentMethod(item2.id, { display_order: newIndex })
    ])
  } catch (err) {
    showError('Error', 'Failed to save order.')
    loadPaymentMethods() // Reload to reset
  }
}

const editPaymentMethod = (method: any) => {
  editingMethod.value = method
  editForm.value = { ...method }
}

const closeEditModal = () => {
  editingMethod.value = null
  editForm.value = {}
}

const savePaymentMethod = async () => {
  if (!editingMethod.value) return
  isSaving.value = true
  try {
    const response = await settingsApi.updatePaymentMethod(editingMethod.value.id, editForm.value)
    if (response.data.success) {
      Object.assign(editingMethod.value, editForm.value)
      success('Saved', 'Payment method updated successfully.')
      closeEditModal()
    }
  } catch (err: any) {
    showError('Error', err.response?.data?.message || 'Failed to save.')
  } finally {
    isSaving.value = false
  }
}

// ==========================================
// SHIPPING SETTINGS
// ==========================================
const shippingSettings = ref({
  defaultFee: 150,
  freeThreshold: 5000
})
const shippingMethods = ref<any[]>([])
const isLoadingShipping = ref(false)
const showShippingModal = ref(false)
const editingShippingId = ref<number | null>(null)
const shippingForm = ref({
  name: '',
  description: '',
  amount: 0,
  min_order: null,
  max_order: null,
  is_active: true
})

const loadShippingMethods = async () => {
  isLoadingShipping.value = true
  try {
    const response = await shippingApi.list()
    if (response.data.success) {
      shippingMethods.value = response.data.data || []
    }
  } catch (err) {
    console.error(err)
  } finally {
    isLoadingShipping.value = false
  }
}

const openAddShippingModal = () => {
  editingShippingId.value = null
  shippingForm.value = { name: '', description: '', amount: 0, min_order: null, max_order: null, is_active: true }
  showShippingModal.value = true
}

const editShippingMethod = (method: any) => {
  editingShippingId.value = method.id
  shippingForm.value = { ...method }
  showShippingModal.value = true
}

const closeShippingModal = () => {
  showShippingModal.value = false
}

const saveShippingMethod = async () => {
  isSaving.value = true
  try {
    let response
    if (editingShippingId.value) {
      response = await shippingApi.update(editingShippingId.value, shippingForm.value)
    } else {
      response = await shippingApi.create(shippingForm.value)
    }

    if (response.data.success) {
      success('Success', `Shipping method ${editingShippingId.value ? 'updated' : 'created'}.`)
      closeShippingModal()
      loadShippingMethods()
    }
  } catch (err: any) {
    showError('Error', err.response?.data?.message || 'Failed to save shipping method.')
  } finally {
    isSaving.value = false
  }
}

const deleteShippingMethod = async (method: any) => {
  if (!confirm(`Are you sure you want to delete "${method.name}"?`)) return
  try {
    const response = await shippingApi.delete(method.id)
    if (response.data.success) {
      success('Deleted', 'Shipping method removed.')
      loadShippingMethods()
    }
  } catch (err: any) {
    showError('Error', 'Failed to delete shipping method.')
  }
}

// ==========================================
// SYSTEM SETTINGS
// ==========================================
const systemSettings = ref({
  maintenanceMode: false,
  emailNotifications: true
})

// ==========================================
// GLOBAL SAVE & LOAD
// ==========================================
const isSaving = ref(false)

const saveSettings = async () => {
  isSaving.value = true
  try {
    const settingsToSave: { key: string; value: unknown; group: string }[] = []

    if (activeTab.value === 'store') {
      settingsToSave.push(
        { key: 'store_name', value: storeSettings.value.name, group: 'store' },
        { key: 'store_email', value: storeSettings.value.email, group: 'store' },
        { key: 'store_phone', value: storeSettings.value.phone, group: 'store' },
        { key: 'store_address', value: storeSettings.value.address, group: 'store' },
        { key: 'store_logo', value: storeSettings.value.logo, group: 'store' }
      )
    } else if (activeTab.value === 'shipping') {
      settingsToSave.push(
        { key: 'default_shipping_fee', value: shippingSettings.value.defaultFee, group: 'shipping' },
        { key: 'free_shipping_threshold', value: shippingSettings.value.freeThreshold, group: 'shipping' }
      )
    } else if (activeTab.value === 'system') {
      settingsToSave.push(
        { key: 'maintenance_mode', value: systemSettings.value.maintenanceMode, group: 'system' },
        { key: 'email_notifications', value: systemSettings.value.emailNotifications, group: 'system' }
      )
    }

    if (settingsToSave.length > 0) {
      const response = await settingsApi.update(settingsToSave)
      if (response.data.success) {
        success('Settings Saved', 'Your changes have been saved successfully.')
      }
    }
  } catch (err: any) {
    showError('Save Failed', 'Could not save settings.')
  } finally {
    isSaving.value = false
  }
}

const loadAllSettings = async () => {
  try {
    const response = await settingsApi.getAll()
    if (response.data.success && response.data.data) {
      const data = response.data.data

      // Store
      if (data.store) {
        storeSettings.value = {
          name: data.store.store_name || '',
          email: data.store.store_email || '',
          phone: data.store.store_phone || '',
          address: data.store.store_address || '',
          logo: data.store.store_logo || ''
        }
      }

      // Shipping
      if (data.shipping) {
        shippingSettings.value = {
          defaultFee: Number(data.shipping.default_shipping_fee) || 0,
          freeThreshold: Number(data.shipping.free_shipping_threshold) || 0
        }
      }

      // System
      if (data.system) {
        systemSettings.value = {
          maintenanceMode: data.system.maintenance_mode === 'true' || data.system.maintenance_mode === true,
          emailNotifications: data.system.email_notifications !== 'false' && data.system.email_notifications !== false
        }
      }
    }
  } catch (err) {
    console.error('Failed to load settings', err)
  }
}

// Watchers & Lifecycle
watch(activeTab, (newTab) => {
  if (newTab === 'payment' && paymentMethods.value.length === 0) loadPaymentMethods()
  if (newTab === 'shipping' && shippingMethods.value.length === 0) loadShippingMethods()
})

onMounted(() => {
  loadAllSettings()
  if (activeTab.value === 'payment') loadPaymentMethods()
})

// Helpers
const formatPrice = (price: number | null | undefined) => {
  return (Number(price) || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
const formatPaymentType = (type: string) => {
  const types: Record<string, string> = {
    ewallet: 'E-Wallet', bank_transfer: 'Bank Transfer', cod: 'Cash on Delivery',
    credit_card: 'Credit Card', debit_card: 'Debit Card', other: 'Other'
  }
  return types[type] || type
}
const loadStoreSettings = loadAllSettings // Alias for template
</script>

<style scoped>
.admin-settings-page {
  --gold: #c9a050;
  --dark: #1a1d29;
  --light: #f5f7fa;
  --white: #ffffff;
  --gray: #6b7280;
  padding: 3.5rem 2rem 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header { margin-bottom: 2rem; }
.page-title { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: var(--dark); margin: 0 0 0.5rem; }
.page-subtitle { color: #4b5563; margin: 0; }

/* Tabs */
.settings-tabs {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  border-bottom: 1px solid #e5e7eb;
  overflow-x: auto;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 1.5rem;
  background: none;
  border: none;
  border-bottom: 3px solid transparent;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.tab-btn:hover { color: var(--dark); }
.tab-btn.active { color: var(--gold); border-bottom-color: var(--gold); }
.tab-icon { display: flex; align-items: center; }
.tab-icon :deep(svg) { width: 18px; height: 18px; }

/* Content */
.settings-content {
  background: var(--white);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  min-height: 400px;
}

.fade-in { animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.section-header { margin-bottom: 2rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 1rem; }
.section-header h3 { font-size: 1.5rem; font-weight: 700; color: var(--dark); margin: 0 0 0.5rem; }
.section-description { color: #6b7280; margin: 0; }

/* Forms */
.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; font-weight: 600; color: var(--dark); margin-bottom: 0.5rem; font-size: 0.9rem; }
.form-group input, .form-group textarea {
  width: 100%; padding: 0.75rem 1rem; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 0.95rem; transition: border-color 0.2s;
}
.form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--gold); }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }

/* Store Logo */
.store-grid { display: grid; grid-template-columns: 250px 1fr; gap: 2rem; }
.store-logo-section { text-align: center; }
.logo-uploader {
  width: 100%; aspect-ratio: 1; border: 2px dashed #e5e7eb; border-radius: 12px; display: flex; align-items: center; justify-content: center;
  cursor: pointer; position: relative; overflow: hidden; transition: all 0.2s; background: #f9fafb;
}
.logo-uploader:hover { border-color: var(--gold); background: #fefce8; }
.logo-preview { width: 100%; height: 100%; object-fit: contain; padding: 1rem; }
.logo-placeholder { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; color: #9ca3af; }
.logo-placeholder svg { width: 32px; height: 32px; }
.upload-overlay { position: absolute; inset: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; }
.help-text { font-size: 0.8rem; color: #9ca3af; margin-top: 0.5rem; }

/* Buttons */
.btn-primary {
  display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: var(--gold); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;
}
.btn-primary:hover:not(:disabled) { background: #b08d44; transform: translateY(-1px); }
.btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }

.btn-secondary {
  padding: 0.75rem 1.5rem; background: white; color: #4b5563; border: 1px solid #d1d5db; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;
}
.btn-secondary:hover { background: #f3f4f6; color: var(--dark); }

.btn-icon {
  width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: none; background: transparent; color: #6b7280; border-radius: 6px; cursor: pointer; transition: all 0.2s;
}
.btn-icon:hover { background: #f3f4f6; color: var(--dark); }
.btn-icon.delete:hover { background: #fee2e2; color: #ef4444; }
.btn-icon svg { width: 18px; height: 18px; }

/* Payment Methods */
.payment-methods-list { display: flex; flex-direction: column; gap: 1rem; }
.payment-method-item {
  display: flex; gap: 1rem; background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.25rem; transition: all 0.2s;
}
.payment-method-item:hover { border-color: #d1d5db; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
.payment-method-item.is-active { border-left: 4px solid var(--gold); }
.payment-method-item.is-inactive { opacity: 0.7; background: #f9fafb; }

.drag-handle { display: flex; align-items: center; }
.order-controls { display: flex; flex-direction: column; gap: 2px; }
.btn-order {
  width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border: 1px solid #e5e7eb; background: white; border-radius: 4px; cursor: pointer; color: #6b7280;
}
.btn-order:hover:not(:disabled) { background: #f3f4f6; color: var(--dark); }
.btn-order:disabled { opacity: 0.3; cursor: not-allowed; }
.btn-order svg { width: 14px; height: 14px; }

.method-main { flex: 1; display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; }
.method-info { flex: 1; }
.method-header-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; }
.method-name { font-size: 1.1rem; font-weight: 700; color: var(--dark); margin: 0; }
.method-type-badge { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 4px; background: #f3f4f6; color: #4b5563; }
.status-badge { font-size: 0.75rem; font-weight: 600; padding: 0.2rem 0.6rem; border-radius: 4px; }
.status-badge.active { background: #dcfce7; color: #166534; }
.status-badge.inactive { background: #fee2e2; color: #991b1b; }
.method-description { color: #6b7280; font-size: 0.9rem; margin: 0 0 0.5rem; }
.method-details { display: flex; gap: 1rem; font-size: 0.85rem; color: #4b5563; }
.fee-info { background: #fffbeb; color: #92400e; padding: 0.1rem 0.5rem; border-radius: 4px; }

.method-actions { display: flex; align-items: center; gap: 1rem; }

/* Switch */
.switch { position: relative; display: inline-block; width: 44px; height: 24px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px; }
.slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
input:checked + .slider { background-color: var(--gold); }
input:checked + .slider:before { transform: translateX(20px); }

/* Shipping */
.settings-card { background: #f9fafb; border-radius: 12px; padding: 1.5rem; border: 1px solid #e5e7eb; }
.settings-card h4 { margin: 0 0 1rem; color: var(--dark); font-size: 1.1rem; }
.mt-4 { margin-top: 1.5rem; }
.card-header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.btn-sm { padding: 0.5rem 1rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.4rem; }
.btn-sm svg { width: 16px; height: 16px; }

.shipping-list { display: flex; flex-direction: column; gap: 0.75rem; }
.shipping-item { display: flex; justify-content: space-between; align-items: center; background: white; padding: 1rem; border-radius: 8px; border: 1px solid #e5e7eb; }
.shipping-name { font-weight: 600; color: var(--dark); }
.badge-inactive { font-size: 0.7rem; background: #fee2e2; color: #991b1b; padding: 0.1rem 0.4rem; border-radius: 4px; margin-left: 0.5rem; }
.shipping-meta { font-size: 0.85rem; color: #6b7280; margin-top: 0.2rem; display: flex; gap: 1rem; }
.shipping-actions { display: flex; gap: 0.5rem; }

/* System */
.system-options { display: flex; flex-direction: column; gap: 1rem; }
.option-card { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border: 1px solid #e5e7eb; border-radius: 12px; }
.option-info h4 { margin: 0 0 0.25rem; color: var(--dark); }
.option-info p { margin: 0; color: #6b7280; font-size: 0.9rem; }

/* Modals */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; backdrop-filter: blur(2px); }
.modal-content { background: white; width: 90%; max-width: 550px; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.1); overflow: hidden; animation: slideUp 0.3s ease; }
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: #f9fafb; }
.modal-header h3 { margin: 0; font-size: 1.25rem; color: var(--dark); }
.modal-body { padding: 1.5rem; max-height: 70vh; overflow-y: auto; }
.modal-footer { padding: 1.25rem 1.5rem; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 1rem; background: #f9fafb; }

.checkbox-group { margin-top: 1rem; }
.checkbox-label { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem; color: var(--dark); }
.checkbox-label input { width: 18px; height: 18px; accent-color: var(--gold); }

/* Loading */
.loading-state { padding: 3rem; display: flex; flex-direction: column; align-items: center; gap: 1rem; color: #6b7280; }
.spinner { width: 32px; height: 32px; border: 3px solid #f3f4f6; border-top-color: var(--gold); border-radius: 50%; animation: spin 1s linear infinite; }
.spinner-sm { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 768px) {
  .store-grid { grid-template-columns: 1fr; }
  .form-row { grid-template-columns: 1fr; }
  .method-main { flex-direction: column; gap: 1rem; }
  .method-actions { width: 100%; justify-content: space-between; }
}
</style>
