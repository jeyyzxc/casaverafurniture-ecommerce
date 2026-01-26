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
        {{ tab.label }}
      </button>
    </div>

    <div class="settings-content">
      <div v-if="activeTab === 'store'" class="settings-section">
        <h3>Store Information</h3>
        <p class="settings-note">
          Changes are automatically saved to your browser's local storage. Click "Save Changes" to sync with the server.
        </p>
        <div class="form-group">
          <label>Store Name</label>
          <input v-model="storeSettings.name" type="text" placeholder="Enter store name">
        </div>
        <div class="form-group">
          <label>Store Email</label>
          <input v-model="storeSettings.email" type="email" placeholder="Enter store email">
        </div>
        <div class="form-group">
          <label>Store Phone</label>
          <input v-model="storeSettings.phone" type="tel" placeholder="Enter store phone">
        </div>
        <div class="form-group">
          <label>Store Address</label>
          <textarea v-model="storeSettings.address" rows="3" placeholder="Enter store address"></textarea>
        </div>
        <div class="form-actions">
          <button class="btn-primary" @click="saveSettings">Save Changes</button>
          <button class="btn-secondary" @click="loadStoreSettings">Reload from Server</button>
        </div>
      </div>

      <div v-if="activeTab === 'payment'" class="settings-section">
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
            v-for="method in paymentMethods"
            :key="method.id"
            class="payment-method-item"
            :class="{ 'is-active': method.is_active, 'is-inactive': !method.is_active }"
          >
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
                  <span v-if="method.min_amount || method.max_amount" class="limits-info">
                    Limits: ₱{{ formatPrice(method.min_amount || 0) }} - ₱{{ formatPrice(method.max_amount || 0) }}
                  </span>
                </div>
              </div>
              <div class="method-actions">
                <button
                  class="btn-toggle"
                  :class="{ active: method.is_active }"
                  @click="togglePaymentMethod(method)"
                >
                  {{ method.is_active ? 'Disable' : 'Enable' }}
                </button>
                <button class="btn-edit" @click="editPaymentMethod(method)">Edit</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit Payment Method Modal -->
        <div v-if="editingMethod" class="modal-overlay" @click="closeEditModal">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h3>Edit Payment Method: {{ editingMethod.name }}</h3>
              <button class="btn-close" @click="closeEditModal">×</button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label>Name *</label>
                <input v-model="editForm.name" type="text" required />
              </div>
              <div class="form-group">
                <label>Description</label>
                <textarea v-model="editForm.description" rows="3"></textarea>
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
              <div class="form-row">
                <div class="form-group">
                  <label>Minimum Amount (₱)</label>
                  <input v-model.number="editForm.min_amount" type="number" min="0" step="0.01" />
                </div>
                <div class="form-group">
                  <label>Maximum Amount (₱)</label>
                  <input v-model.number="editForm.max_amount" type="number" min="0" step="0.01" />
                </div>
              </div>
              <div class="form-group">
                <label>Payment Instructions</label>
                <textarea v-model="editForm.payment_instructions" rows="5" placeholder="Step-by-step instructions for customers..."></textarea>
              </div>
              <div class="form-group">
                <label>
                  <input type="checkbox" v-model="editForm.requires_verification" />
                  Requires Manual Verification
                </label>
              </div>
              <div class="form-group">
                <label>
                  <input type="checkbox" v-model="editForm.requires_proof_of_payment" />
                  Requires Proof of Payment
                </label>
              </div>
              <div class="form-group">
                <label>
                  <input type="checkbox" v-model="editForm.is_active" />
                  Active (visible to customers)
                </label>
              </div>
              <div class="form-group">
                <label>Display Order</label>
                <input v-model.number="editForm.display_order" type="number" min="0" />
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
      </div>

      <div v-if="activeTab === 'shipping'" class="settings-section">
        <h3>Shipping Settings</h3>
        <div class="form-group">
          <label>Default Shipping Fee</label>
          <input v-model.number="shippingSettings.defaultFee" type="number">
        </div>
        <div class="form-group">
          <label>Free Shipping Threshold</label>
          <input v-model.number="shippingSettings.freeThreshold" type="number">
        </div>
        <button class="btn-primary" @click="saveSettings">Save Changes</button>
      </div>

      <div v-if="activeTab === 'system'" class="settings-section">
        <h3>System Preferences</h3>
        <label class="checkbox-label">
          <input type="checkbox" v-model="systemSettings.maintenanceMode">
          <span>Maintenance Mode</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" v-model="systemSettings.emailNotifications">
          <span>Email Notifications</span>
        </label>
        <button class="btn-primary" @click="saveSettings">Save Changes</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { settings as settingsApi } from '@/services/adminApi'

const activeTab = ref('store')

const tabs = [
  { id: 'store', label: 'Store Info' },
  { id: 'payment', label: 'Payments' },
  { id: 'shipping', label: 'Shipping' },
  { id: 'system', label: 'System' }
]

// Store Information with localStorage support
const STORE_SETTINGS_KEY = 'casavera_store_settings'

const storeSettings = ref({
  name: 'CASA VÉRA',
  email: 'info@casavera.com',
  phone: '+63 123 456 7890',
  address: '123 Furniture St, Manila, Philippines'
})

// Load store settings from localStorage
const loadStoreSettingsFromLocalStorage = () => {
  try {
    const stored = localStorage.getItem(STORE_SETTINGS_KEY)
    if (stored) {
      const parsed = JSON.parse(stored)
      if (parsed && typeof parsed === 'object') {
        storeSettings.value = {
          name: parsed.name || storeSettings.value.name,
          email: parsed.email || storeSettings.value.email,
          phone: parsed.phone || storeSettings.value.phone,
          address: parsed.address || storeSettings.value.address,
        }
      }
    }
  } catch (error) {
    console.error('Failed to load store settings from localStorage:', error)
    // Continue with default values
  }
}

// Save store settings to localStorage
const saveStoreSettingsToLocalStorage = () => {
  try {
    localStorage.setItem(STORE_SETTINGS_KEY, JSON.stringify(storeSettings.value))
  } catch (error) {
    console.error('Failed to save store settings to localStorage:', error)
  }
}

// Watch for changes and auto-save to localStorage
watch(storeSettings, () => {
  saveStoreSettingsToLocalStorage()
}, { deep: true })

const paymentMethods = ref<any[]>([])
const isLoadingPaymentMethods = ref(false)
const editingMethod = ref<any>(null)
const editForm = ref<any>({})
const isSaving = ref(false)

const shippingSettings = ref({
  defaultFee: 150,
  freeThreshold: 5000
})

const systemSettings = ref({
  maintenanceMode: false,
  emailNotifications: true
})

const formatPrice = (price: number) => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatPaymentType = (type: string) => {
  const types: Record<string, string> = {
    ewallet: 'E-Wallet',
    bank_transfer: 'Bank Transfer',
    cod: 'Cash on Delivery',
    credit_card: 'Credit Card',
    debit_card: 'Debit Card',
    other: 'Other'
  }
  return types[type] || type
}

const loadPaymentMethods = async () => {
  isLoadingPaymentMethods.value = true
  try {
    const response = await settingsApi.getPaymentMethods()
    if (response.data.success) {
      paymentMethods.value = response.data.data || []
    }
  } catch (error) {
    console.error('Failed to load payment methods:', error)
  } finally {
    isLoadingPaymentMethods.value = false
  }
}

const togglePaymentMethod = async (method: any) => {
  try {
    const response = await settingsApi.updatePaymentMethod(method.id, {
      is_active: !method.is_active
    })
    if (response.data.success) {
      method.is_active = !method.is_active
    }
  } catch (error) {
    console.error('Failed to toggle payment method:', error)
  }
}

const editPaymentMethod = (method: any) => {
  editingMethod.value = method
  editForm.value = {
    name: method.name,
    description: method.description || '',
    fee_fixed: method.fee_fixed || 0,
    fee_percentage: method.fee_percentage || 0,
    min_amount: method.min_amount || null,
    max_amount: method.max_amount || null,
    payment_instructions: method.payment_instructions || '',
    requires_verification: method.requires_verification || false,
    requires_proof_of_payment: method.requires_proof_of_payment || false,
    is_active: method.is_active !== undefined ? method.is_active : true,
    display_order: method.display_order || 0,
  }
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
      // Update local state
      Object.assign(editingMethod.value, editForm.value)
      closeEditModal()
    }
  } catch (error) {
    console.error('Failed to save payment method:', error)
  } finally {
    isSaving.value = false
  }
}

const saveSettings = async () => {
  try {
    // Save to backend via API
    const settingsToSave: { key: string; value: unknown; group: string }[] = []

    // Store settings
    if (activeTab.value === 'store') {
      settingsToSave.push(
        { key: 'store_name', value: storeSettings.value.name, group: 'store' },
        { key: 'store_email', value: storeSettings.value.email, group: 'store' },
        { key: 'store_phone', value: storeSettings.value.phone, group: 'store' },
        { key: 'store_address', value: storeSettings.value.address, group: 'store' }
      )
    }

    // Shipping settings
    if (activeTab.value === 'shipping') {
      settingsToSave.push(
        { key: 'default_shipping_fee', value: shippingSettings.value.defaultFee, group: 'shipping' },
        { key: 'free_shipping_threshold', value: shippingSettings.value.freeThreshold, group: 'shipping' }
      )
    }

    // System settings
    if (activeTab.value === 'system') {
      settingsToSave.push(
        { key: 'maintenance_mode', value: systemSettings.value.maintenanceMode, group: 'system' },
        { key: 'email_notifications', value: systemSettings.value.emailNotifications, group: 'system' }
      )
    }

    if (settingsToSave.length === 0) {
      alert('No settings to save.')
      return
    }

    const response = await settingsApi.update(settingsToSave)
    
    if (response.data.success) {
      // Settings saved to backend - localStorage is already updated via watcher for store settings
      alert('Settings saved successfully!')
    } else {
      alert('Failed to save settings. Please try again.')
    }
  } catch (error) {
    console.error('Failed to save settings:', error)
    alert('Failed to save settings. Please try again.')
  }
}

// Watch for tab changes
watch(activeTab, (newTab) => {
  if (newTab === 'payment' && paymentMethods.value.length === 0) {
    loadPaymentMethods()
  } else if (newTab === 'store') {
    // Reload store settings when switching to store tab
    loadStoreSettings()
  } else if (newTab === 'shipping') {
    loadShippingSettings()
  } else if (newTab === 'system') {
    loadSystemSettings()
  }
})

// Load store settings from backend on mount
const loadStoreSettings = async () => {
  try {
    const response = await settingsApi.getAll('store')
    if (response.data.success && response.data.data?.store) {
      const storeData = response.data.data.store
      storeSettings.value = {
        name: storeData.store_name || storeSettings.value.name,
        email: storeData.store_email || storeSettings.value.email,
        phone: storeData.store_phone || storeSettings.value.phone,
        address: storeData.store_address || storeSettings.value.address,
      }
      // Save loaded data to localStorage
      saveStoreSettingsToLocalStorage()
    } else {
      // If no backend data, load from localStorage
      loadStoreSettingsFromLocalStorage()
    }
  } catch (error) {
    console.error('Failed to load store settings from backend:', error)
    // Fallback to localStorage
    loadStoreSettingsFromLocalStorage()
  }
}

// Load shipping settings
const loadShippingSettings = async () => {
  try {
    const response = await settingsApi.getAll('shipping')
    if (response.data.success && response.data.data?.shipping) {
      const shippingData = response.data.data.shipping
      shippingSettings.value = {
        defaultFee: shippingData.default_shipping_fee || shippingSettings.value.defaultFee,
        freeThreshold: shippingData.free_shipping_threshold || shippingSettings.value.freeThreshold,
      }
    }
  } catch (error) {
    console.error('Failed to load shipping settings:', error)
  }
}

// Load system settings
const loadSystemSettings = async () => {
  try {
    const response = await settingsApi.getAll('system')
    if (response.data.success && response.data.data?.system) {
      const systemData = response.data.data.system
      systemSettings.value = {
        maintenanceMode: systemData.maintenance_mode || false,
        emailNotifications: systemData.email_notifications !== false,
      }
    }
  } catch (error) {
    console.error('Failed to load system settings:', error)
  }
}

onMounted(() => {
  // Load store settings (from backend or localStorage)
  loadStoreSettings()
  loadShippingSettings()
  loadSystemSettings()
  
  if (activeTab.value === 'payment') {
    loadPaymentMethods()
  }
})
</script>

<style scoped>
.admin-settings-page {
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

.settings-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 2rem;
  border-bottom: 2px solid #e5e7eb;
}

.tab-btn {
  padding: 1rem 1.5rem;
  background: none;
  border: none;
  border-bottom: 3px solid transparent;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s;
}

.tab-btn.active {
  color: var(--gold);
  border-bottom-color: var(--gold);
}

.settings-content {
  background: var(--white);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.settings-section h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--dark);
  margin: 0 0 0.5rem;
}

.settings-note {
  font-size: 0.875rem;
  color: #6b7280;
  background: #f3f4f6;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  border-left: 3px solid var(--gold);
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: var(--dark);
  margin-bottom: 0.5rem;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
}

.payment-methods {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  width: 20px;
  height: 20px;
}

.btn-primary {
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

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: white;
  color: var(--dark);
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  border-color: var(--gold);
  color: var(--gold);
}

/* Payment Methods Section */
.section-header {
  margin-bottom: 2rem;
}

.section-description {
  color: #6b7280;
  font-size: 0.9rem;
  margin-top: 0.5rem;
}

.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 3rem;
  color: #6b7280;
}

.spinner {
  width: 24px;
  height: 24px;
  border: 3px solid #e5e7eb;
  border-top-color: var(--gold);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.payment-methods-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.payment-method-item {
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.3s ease;
}

.payment-method-item.is-active {
  border-color: #c9a050;
  background: #fefbf5;
}

.payment-method-item.is-inactive {
  opacity: 0.7;
  background: #f9fafb;
}

.method-main {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1.5rem;
}

.method-info {
  flex: 1;
}

.method-header-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
  flex-wrap: wrap;
}

.method-name {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--dark);
  margin: 0;
}

.method-type-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.method-type-badge.type-ewallet {
  background: #dbeafe;
  color: #1e40af;
}

.method-type-badge.type-bank_transfer {
  background: #fef3c7;
  color: #92400e;
}

.method-type-badge.type-cod {
  background: #d1fae5;
  color: #065f46;
}

.method-type-badge.type-credit_card,
.method-type-badge.type-debit_card {
  background: #e0e7ff;
  color: #3730a3;
}

.method-type-badge.type-other {
  background: #f3f4f6;
  color: #374151;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-badge.active {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.inactive {
  background: #fee2e2;
  color: #991b1b;
}

.method-description {
  color: #6b7280;
  font-size: 0.9rem;
  margin: 0.5rem 0;
}

.method-details {
  display: flex;
  gap: 1rem;
  margin-top: 0.75rem;
  flex-wrap: wrap;
}

.fee-info,
.limits-info {
  font-size: 0.85rem;
  color: #6b7280;
  padding: 0.25rem 0.5rem;
  background: #f3f4f6;
  border-radius: 4px;
}

.fee-info.no-fee {
  color: #059669;
  background: #d1fae5;
}

.method-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-toggle,
.btn-edit {
  padding: 0.5rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-toggle {
  background: white;
  color: #6b7280;
}

.btn-toggle.active {
  background: #fee2e2;
  color: #991b1b;
  border-color: #fecaca;
}

.btn-toggle:hover {
  border-color: #c9a050;
}

.btn-edit {
  background: var(--gold);
  color: white;
  border-color: var(--gold);
}

.btn-edit:hover {
  background: #b8860b;
  border-color: #b8860b;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 2rem;
}

.modal-content {
  background: white;
  border-radius: 16px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 2px solid #e5e7eb;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.5rem;
  color: var(--dark);
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

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .method-main {
    flex-direction: column;
  }
  
  .method-actions {
    width: 100%;
  }
  
  .btn-toggle,
  .btn-edit {
    flex: 1;
  }
}

</style>
