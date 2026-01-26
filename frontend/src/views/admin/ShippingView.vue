<template>
  <div class="admin-shipping-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Shipping Management</h1>
        <p class="page-subtitle">Configure shipping rates, delivery zones, and courier settings.</p>
      </div>
      <div class="header-actions">
        <button class="btn-primary" @click="openAddModal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 8v8M8 12h8"/>
          </svg>
          Add Shipping Zone
        </button>
      </div>
    </div>

    <div v-if="isLoading && shippingZones.length === 0" class="loading-state">
      <div class="spinner"></div>
      <p>Loading shipping zones...</p>
    </div>

    <div v-else-if="error && shippingZones.length === 0" class="error-state">
      <p><strong>Error:</strong> {{ error }}</p>
      <button class="btn-secondary" @click="loadShippingZones">Retry</button>
    </div>

    <div v-else-if="shippingZones.length === 0" class="empty-state">
      <p>No shipping zones found. Create your first shipping zone to get started.</p>
    </div>

    <div v-else class="shipping-zones">
      <div v-for="zone in shippingZones" :key="zone.id" class="zone-card">
        <div class="zone-header">
          <div>
            <h3>{{ zone.name }}</h3>
            <span class="zone-type">{{ zone.type.charAt(0).toUpperCase() + zone.type.slice(1) }}</span>
          </div>
          <span v-if="!zone.is_active" class="inactive-badge">Inactive</span>
        </div>
        <div class="zone-details">
          <div class="detail-item">
            <span class="detail-label">Base Rate:</span>
            <span class="detail-value">₱{{ formatPrice(zone.baseRate) }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Free Shipping Threshold:</span>
            <span class="detail-value">{{ zone.freeThreshold ? `₱${formatPrice(zone.freeThreshold)}` : 'N/A' }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Estimated Delivery:</span>
            <span class="detail-value">{{ zone.min_delivery_days }}-{{ zone.max_delivery_days }} days</span>
          </div>
        </div>
        <div class="zone-actions">
          <button class="btn-small" @click="editZone(zone)">Edit</button>
          <button class="btn-small danger" @click="deleteZone(zone.id)">Delete</button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <Teleport to="body">
      <div v-if="showAddModal || showEditModal" class="modal-overlay" @click.self="editingZone ? closeEditModal() : closeAddModal()">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h2>{{ editingZone ? 'Edit Shipping Zone' : 'Add Shipping Zone' }}</h2>
            <button class="close-btn" @click.stop="editingZone ? closeEditModal() : closeAddModal()">×</button>
          </div>
          <form @submit.prevent="saveZone" class="modal-body">
            <div class="form-group">
              <label>Zone Name *</label>
              <input
                v-model="zoneForm.name"
                type="text"
                required
                placeholder="e.g., Metro Manila, National, International"
                :disabled="isSaving"
              />
            </div>
            <div class="form-group">
              <label>Zone Type *</label>
              <select v-model="zoneForm.type" required :disabled="isSaving">
                <option value="local">Local</option>
                <option value="national">National</option>
                <option value="international">International</option>
              </select>
            </div>
            <div class="form-group">
              <label>Description</label>
              <textarea
                v-model="zoneForm.description"
                rows="3"
                placeholder="Optional description for this shipping zone"
                :disabled="isSaving"
              ></textarea>
            </div>
            <div class="form-group">
              <label>Base Rate (₱) *</label>
              <input
                v-model.number="zoneForm.base_rate"
                type="number"
                step="0.01"
                min="0"
                required
                placeholder="0.00"
                :disabled="isSaving"
              />
            </div>
            <div class="form-group">
              <label>Free Shipping Threshold (₱)</label>
              <input
                v-model.number="zoneForm.free_shipping_threshold"
                type="number"
                step="0.01"
                min="0"
                placeholder="Leave empty for no free shipping"
                :disabled="isSaving"
              />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Min Delivery Days *</label>
                <input
                  v-model.number="zoneForm.min_delivery_days"
                  type="number"
                  min="1"
                  required
                  :disabled="isSaving"
                />
              </div>
              <div class="form-group">
                <label>Max Delivery Days *</label>
                <input
                  v-model.number="zoneForm.max_delivery_days"
                  type="number"
                  min="1"
                  required
                  :disabled="isSaving"
                />
              </div>
            </div>
            <div class="form-group">
              <label>
                <input type="checkbox" v-model="zoneForm.is_active" :disabled="isSaving" />
                Active (visible to customers)
              </label>
            </div>
            <div class="form-group">
              <label>Display Order</label>
              <input
                v-model.number="zoneForm.display_order"
                type="number"
                min="0"
                :disabled="isSaving"
              />
            </div>
            <div class="form-actions">
              <button type="button" class="btn-secondary" @click.stop="editingZone ? closeEditModal() : closeAddModal()" :disabled="isSaving">
                Cancel
              </button>
              <button type="submit" class="btn-primary" :disabled="isSaving">
                {{ isSaving ? 'Saving...' : (editingZone ? 'Update Zone' : 'Create Zone') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <div v-if="showDeleteModal" class="modal-overlay delete-modal-overlay" @click.self="closeDeleteModal">
        <div class="modal-container delete-modal">
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
            <h2 class="delete-title">Delete Shipping Zone</h2>
            <p class="delete-message">
              Are you sure you want to delete 
              <strong class="delete-item-name">{{ deletingZone?.name }}</strong>?
            </p>
            <p class="delete-warning">
              This action cannot be undone. All shipping zone data will be permanently removed.
            </p>
            <div class="delete-actions">
              <button type="button" class="delete-btn-cancel" @click.stop="closeDeleteModal" :disabled="isDeleting">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/>
                  <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Cancel
              </button>
              <button type="button" class="delete-btn-confirm" @click.stop="confirmDelete" :disabled="isDeleting">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                {{ isDeleting ? 'Deleting...' : 'Delete Zone' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Teleport } from 'vue'
import { shipping as shippingApi } from '@/services/adminApi'
import { useNotification } from '@/composables/useNotification'

const { success, error: showError } = useNotification()

interface ShippingZone {
  id: number
  name: string
  type: 'local' | 'national' | 'international'
  description?: string
  regions?: string[]
  postal_codes?: string[]
  base_rate: number
  baseRate: number
  free_shipping_threshold?: number
  freeThreshold?: number
  min_delivery_days: number
  max_delivery_days: number
  estimatedDays: number
  is_active?: boolean
  display_order?: number
}

const shippingZones = ref<ShippingZone[]>([])
const isLoading = ref(false)
const error = ref<string | null>(null)

// Modal States
const showAddModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const editingZone = ref<ShippingZone | null>(null)
const deletingZone = ref<ShippingZone | null>(null)
const isSaving = ref(false)
const isDeleting = ref(false)

// Form Data
const zoneForm = ref({
  name: '',
  type: 'national' as 'local' | 'national' | 'international',
  description: '',
  base_rate: 0,
  free_shipping_threshold: undefined as number | undefined,
  min_delivery_days: 1,
  max_delivery_days: 5,
  is_active: true,
  display_order: 0,
})

// Load shipping zones from API
const loadShippingZones = async () => {
  isLoading.value = true
  error.value = null
  try {
    const response = await shippingApi.list()
    
    if (response.data.success) {
      shippingZones.value = (response.data.data || []).map((z: any) => ({
        id: z.id,
        name: z.name,
        type: z.type || 'national',
        description: z.description || '',
        regions: z.regions || [],
        postal_codes: z.postal_codes || [],
        base_rate: parseFloat(z.base_rate || 0),
        baseRate: parseFloat(z.base_rate || 0),
        free_shipping_threshold: z.free_shipping_threshold ? parseFloat(z.free_shipping_threshold) : undefined,
        freeThreshold: z.free_shipping_threshold ? parseFloat(z.free_shipping_threshold) : undefined,
        min_delivery_days: z.min_delivery_days || 1,
        max_delivery_days: z.max_delivery_days || 5,
        estimatedDays: z.max_delivery_days || z.min_delivery_days || 3,
        is_active: z.is_active !== false,
        display_order: z.display_order || 0,
      }))
    } else {
      throw new Error(response.data.message || 'Failed to load shipping zones')
    }
  } catch (err: any) {
    console.error('Failed to load shipping zones:', err)
    error.value = err.response?.data?.message || err.message || 'Failed to load shipping zones. Please try again.'
    showError('Failed to Load Zones', error.value)
    shippingZones.value = []
  } finally {
    isLoading.value = false
  }
}

const openAddModal = () => {
  zoneForm.value = {
    name: '',
    type: 'national',
    description: '',
    base_rate: 0,
    free_shipping_threshold: undefined,
    min_delivery_days: 1,
    max_delivery_days: 5,
    is_active: true,
    display_order: 0,
  }
  editingZone.value = null
  showAddModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeAddModal = (force = false) => {
  // Allow closing if force is true (e.g., after successful save)
  if (!force && isSaving.value) return
  showAddModal.value = false
  document.body.style.overflow = ''
}

const editZone = (zone: ShippingZone) => {
  editingZone.value = zone
  zoneForm.value = {
    name: zone.name,
    type: zone.type,
    description: zone.description || '',
    base_rate: zone.base_rate,
    free_shipping_threshold: zone.free_shipping_threshold,
    min_delivery_days: zone.min_delivery_days,
    max_delivery_days: zone.max_delivery_days,
    is_active: zone.is_active !== false,
    display_order: zone.display_order || 0,
  }
  showEditModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeEditModal = (force = false) => {
  // Allow closing if force is true (e.g., after successful save)
  if (!force && isSaving.value) return
  showEditModal.value = false
  editingZone.value = null
  document.body.style.overflow = ''
}

const saveZone = async () => {
  // Validation
  if (!zoneForm.value.name.trim()) {
    showError('Validation Error', 'Zone name is required.')
    return
  }
  if (zoneForm.value.base_rate < 0) {
    showError('Validation Error', 'Base rate must be 0 or greater.')
    return
  }
  if (zoneForm.value.min_delivery_days < 1) {
    showError('Validation Error', 'Minimum delivery days must be at least 1.')
    return
  }
  if (zoneForm.value.max_delivery_days < zoneForm.value.min_delivery_days) {
    showError('Validation Error', 'Maximum delivery days must be greater than or equal to minimum delivery days.')
    return
  }

  isSaving.value = true

  try {
    const data: any = {
      name: zoneForm.value.name.trim(),
      type: zoneForm.value.type,
      description: zoneForm.value.description || null,
      base_rate: parseFloat(zoneForm.value.base_rate.toString()),
      free_shipping_threshold: zoneForm.value.free_shipping_threshold ? parseFloat(zoneForm.value.free_shipping_threshold.toString()) : null,
      min_delivery_days: parseInt(zoneForm.value.min_delivery_days.toString()),
      max_delivery_days: parseInt(zoneForm.value.max_delivery_days.toString()),
      is_active: zoneForm.value.is_active,
      display_order: parseInt(zoneForm.value.display_order.toString()) || 0,
    }

    let response
    if (editingZone.value) {
      response = await shippingApi.update(editingZone.value.id, data)
    } else {
      response = await shippingApi.create(data)
    }

    if (response.data.success) {
      const zoneName = data.name
      const isEdit = !!editingZone.value
      
      // Close modal first, then show notification and reload
      isSaving.value = false // Allow modal to close
      
      if (isEdit) {
        closeEditModal(true) // Force close after successful save
      } else {
        closeAddModal(true) // Force close after successful save
      }
      
      // Show success notification
      success(
        isEdit ? 'Zone Updated' : 'Zone Created',
        `Shipping zone "${zoneName}" has been ${isEdit ? 'updated' : 'created'} successfully.`
      )
      
      // Reload zones to reflect changes
      await loadShippingZones()
    } else {
      throw new Error(response.data.message || `Failed to ${editingZone.value ? 'update' : 'create'} shipping zone`)
    }
  } catch (err: any) {
    console.error(`Failed to ${editingZone.value ? 'update' : 'create'} shipping zone:`, err)
    showError(
      `Failed to ${editingZone.value ? 'Update' : 'Create'} Zone`,
      err.response?.data?.message || err.message || `Failed to ${editingZone.value ? 'update' : 'create'} shipping zone. Please try again.`
    )
  } finally {
    isSaving.value = false
  }
}

const deleteZone = (id: number) => {
  const zone = shippingZones.value.find(z => z.id === id)
  if (zone) {
    deletingZone.value = zone
    showDeleteModal.value = true
    document.body.style.overflow = 'hidden'
  }
}

const confirmDelete = async () => {
  if (!deletingZone.value) return

  isDeleting.value = true

  try {
    const response = await shippingApi.delete(deletingZone.value.id)
    
    if (response.data.success) {
      success(
        'Zone Deleted',
        `Shipping zone "${deletingZone.value.name}" has been deleted successfully.`
      )
      closeDeleteModal()
      await loadShippingZones()
    } else {
      throw new Error(response.data.message || 'Failed to delete shipping zone')
    }
  } catch (err: any) {
    console.error('Failed to delete shipping zone:', err)
    showError(
      'Failed to Delete Zone',
      err.response?.data?.message || err.message || 'Failed to delete shipping zone. Please try again.'
    )
  } finally {
    isDeleting.value = false
  }
}

const closeDeleteModal = () => {
  if (isDeleting.value) return
  showDeleteModal.value = false
  deletingZone.value = null
  document.body.style.overflow = ''
}

const formatPrice = (price: number) => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

onMounted(() => {
  loadShippingZones()
})

onUnmounted(() => {
  document.body.style.overflow = ''
})
</script>

<style scoped>
.admin-shipping-page {
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
  transition: color 0.3s ease;
}

.page-subtitle {
  color: #374151;
  font-size: 0.95rem;
  margin: 0;
  transition: color 0.3s ease;
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.75rem;
  background: linear-gradient(135deg, var(--gold), #b8860b);
  color: #ffffff;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(201, 160, 80, 0.2);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(201, 160, 80, 0.4);
}

.btn-primary svg {
  width: 20px;
  height: 20px;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: #f3f4f6;
  color: var(--dark);
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover:not(:disabled) {
  background: #e5e7eb;
}

.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.loading-state,
.error-state,
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: #6b7280;
}

.loading-state .spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #f3f4f6;
  border-top-color: var(--gold);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-state {
  color: #dc2626;
}

.error-state button {
  margin-top: 1rem;
}

.shipping-zones {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
}

.zone-card {
  background: var(--white);
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.zone-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.zone-header > div {
  flex: 1;
}

.zone-header h3 {
  margin: 0 0 0.5rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--dark);
}

.zone-type {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  background: #f3f4f6;
  color: var(--dark);
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}

.inactive-badge {
  padding: 0.35rem 0.75rem;
  background: #fee2e2;
  color: #991b1b;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}

.zone-details {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.detail-item {
  display: flex;
  justify-content: space-between;
}

.detail-label {
  color: #6b7280;
}

.detail-value {
  font-weight: 600;
  color: var(--dark);
}

.zone-actions {
  display: flex;
  gap: 0.75rem;
}

.btn-small {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-small:not(.danger) {
  background: var(--gold);
  color: white;
}

.btn-small.danger {
  background: #fee2e2;
  color: #991b1b;
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
  border-radius: 8px;
  transition: all 0.2s;
}

.close-btn:hover {
  background: #f3f4f6;
  color: #000000;
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
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #000000;
  background: #ffffff;
}

.form-group input:disabled,
.form-group textarea:disabled,
.form-group select:disabled {
  background: #f3f4f6;
  color: #000000;
  cursor: not-allowed;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
  color: #9ca3af;
}

.form-group select option {
  color: #000000;
  background: #ffffff;
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.form-group input[type="checkbox"] {
  width: auto;
  margin-right: 0.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.form-actions .btn-primary {
  background: #000000;
  color: #ffffff;
}

.form-actions .btn-primary:hover:not(:disabled) {
  background: #1a1a1a;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
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
  position: relative;
  overflow: hidden;
}

.delete-btn-confirm::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, transparent 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.delete-btn-confirm:hover::before {
  opacity: 1;
}

.delete-btn-confirm svg {
  width: 18px;
  height: 18px;
  position: relative;
  z-index: 1;
}

.delete-btn-confirm:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.delete-btn-confirm:active {
  transform: translateY(0);
}

.delete-btn-confirm:disabled,
.delete-btn-cancel:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

@media (max-width: 768px) {
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
  
  .success-notification {
    top: 1rem;
    right: 1rem;
    left: 1rem;
  }
  
  .success-content {
    min-width: auto;
    max-width: none;
  }
}
</style>
