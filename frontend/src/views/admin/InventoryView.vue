<template>
  <div class="admin-inventory-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Inventory Management</h1>
        <p class="page-subtitle">Track stock levels, manage inventory, and set up alerts.</p>
      </div>
    </div>

    <div class="alerts-section" v-if="lowStockItems.length > 0">
      <div class="alert-card">
        <div class="alert-header">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
          </svg>
          <h3>Inventory Alerts ({{ lowStockItems.length }})</h3>
        </div>
        <div class="alert-items">
          <div v-for="item in lowStockItems" :key="item.id" class="alert-item">
            <span class="alert-product">{{ item.name }}</span>
            <span class="alert-stock" :class="{ 'out-of-stock': item.stock === 0 }">
              {{ item.stock === 0 ? 'Out of Stock' : `Only ${item.stock} left` }}
            </span>
            <button class="btn-small" @click="quickRestock(item)">Restock</button>
          </div>
        </div>
      </div>
    </div>

    <div class="filters-bar">
      <div class="search-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/>
          <path d="m21 21-4.35-4.35"/>
        </svg>
        <input v-model="searchQuery" placeholder="Search products..." class="search-input">
      </div>
      <select v-model="stockFilter" class="filter-select">
        <option value="">All Stock Levels</option>
        <option value="low">Low Stock</option>
        <option value="out">Out of Stock</option>
        <option value="in_stock">In Stock</option>
      </select>
    </div>

    <div class="table-card">
      <table class="data-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Current Stock</th>
            <th>Low Stock Threshold</th>
            <th>Status</th>
            <th>Last Updated</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading && inventory.length === 0">
            <td colspan="7" style="text-align: center; padding: 3rem;">
              <div class="spinner"></div>
              <p>Loading inventory...</p>
            </td>
          </tr>
          <tr v-else-if="error">
            <td colspan="7" style="text-align: center; padding: 3rem; color: #dc2626;">
              <div class="error-message">
                <p><strong>Error:</strong> {{ error }}</p>
                <button class="btn-small" @click="loadInventory" style="margin-top: 1rem;">Retry</button>
              </div>
            </td>
          </tr>
          <tr v-else-if="!isLoading && filteredInventory.length === 0">
            <td colspan="7" style="text-align: center; padding: 3rem; color: #6b7280;">
              No inventory items found
            </td>
          </tr>
          <tr v-else v-for="item in filteredInventory" :key="item.id">
            <td>
              <div class="product-cell">
                <div class="product-thumb-container">
                  <img
                    :src="item.image || '/images/products/placeholder.png'"
                    :alt="item.name"
                    class="product-thumb"
                    @error="(e) => { (e.target as HTMLImageElement).src = '/images/products/placeholder.png' }"
                  >
                </div>
                <div>
                  <div class="product-name">{{ item.name }}</div>
                  <div class="product-category">{{ item.category }}</div>
                </div>
              </div>
            </td>
            <td class="sku">{{ item.sku }}</td>
            <td>
              <span :class="{ 'low-stock': item.stock <= item.lowStockThreshold, 'out-of-stock': item.stock === 0 }">
                {{ item.stock }}
              </span>
            </td>
            <td>{{ item.lowStockThreshold }}</td>
            <td>
              <span class="status-badge" :class="getStockStatus(item)">
                {{ getStockStatusLabel(item) }}
              </span>
            </td>
            <td class="date">{{ formatDate(item.lastUpdated) }}</td>
            <td>
              <div class="action-buttons">
                <button class="action-btn" @click="adjustStock(item)" title="Adjust Stock">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3v18m-9-9h18"/>
                  </svg>
                </button>
                <button class="action-btn" @click="viewHistory(item.id)" title="View History">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Adjust Stock Modal -->
    <Teleport to="body">
      <div v-if="showAdjustModal" class="modal-overlay" @click.self="closeAdjustModal(true)">
        <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Adjust Stock</h2>
          <button class="close-btn" @click.stop="closeAdjustModal(true)">×</button>
        </div>
        <form @submit.prevent="saveAdjustment" class="modal-body">
          <div class="form-group">
            <label>Product</label>
            <input :value="adjustingItem?.name" type="text" disabled>
          </div>
          <div class="form-group">
            <label>Current Stock</label>
            <input :value="adjustingItem?.stock" type="number" disabled>
          </div>
          <div class="form-group">
            <label>Adjustment Type</label>
            <select v-model="adjustmentForm.type" required>
              <option value="add">Add Stock</option>
              <option value="subtract">Subtract Stock</option>
              <option value="set">Set Stock</option>
            </select>
          </div>
          <div class="form-group">
            <label>Quantity</label>
            <input v-model.number="adjustmentForm.quantity" type="number" min="1" required>
          </div>
          <div class="form-group">
            <label>Reason</label>
            <textarea v-model="adjustmentForm.reason" rows="3" placeholder="Reason for adjustment..."></textarea>
          </div>
          <div class="form-actions">
            <button type="button" class="btn-secondary" @click.stop="closeAdjustModal(true)" :disabled="isSaving">Cancel</button>
            <button type="submit" class="btn-primary" :disabled="isSaving">
              {{ isSaving ? 'Saving...' : 'Save Adjustment' }}
            </button>
          </div>
        </form>
        </div>
      </div>
    </Teleport>

    <!-- Stock History Modal -->
    <Teleport to="body">
      <div v-if="showHistoryModal" class="modal-overlay" @click="closeHistoryModal">
        <div class="modal-content history-modal" @click.stop>
          <div class="modal-header">
            <h2>Stock History</h2>
            <button class="close-btn" @click="closeHistoryModal">×</button>
          </div>
          <div class="modal-body">
            <div v-if="isLoadingHistory" class="loading-state">
              <div class="spinner"></div>
              <p>Loading history...</p>
            </div>
            <div v-else-if="stockHistory.length === 0" class="empty-state">
              <p>No stock history available for this product.</p>
            </div>
            <div v-else class="history-table-container">
              <table class="history-table">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Quantity Before</th>
                    <th>Change</th>
                    <th>Quantity After</th>
                    <th>Reason</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="log in stockHistory" :key="log.id">
                    <td class="date-cell">{{ formatDate(new Date(log.created_at)) }}</td>
                    <td>
                      <span class="type-badge" :class="getTypeClass(log.type)">
                        {{ log.type }}
                      </span>
                    </td>
                    <td>{{ log.quantity_before }}</td>
                    <td :class="getChangeClass(log.quantity_change)">
                      {{ log.quantity_change > 0 ? '+' : '' }}{{ log.quantity_change }}
                    </td>
                    <td>{{ log.quantity_after }}</td>
                    <td class="reason-cell">{{ log.notes || log.reason || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { Teleport } from 'vue'
import { products as productsApi } from '@/services/adminApi'
import { useRealtimeProducts } from '@/composables/useRealtimeProducts'
import { useNotification } from '@/composables/useNotification'

const { success, error: showError } = useNotification()

interface InventoryItem {
  id: number
  name: string
  sku: string
  category: string
  stock: number
  lowStockThreshold: number
  lastUpdated: Date
  image: string
}

const searchQuery = ref('')
const stockFilter = ref('')
const showAdjustModal = ref(false)
const adjustingItem = ref<InventoryItem | null>(null)
const adjustmentForm = ref({
  type: 'add' as 'add' | 'subtract' | 'set',
  quantity: 0,
  reason: ''
})

const inventory = ref<InventoryItem[]>([])
const isLoading = ref(false)
const isSaving = ref(false)
const error = ref<string | null>(null)
const { startListening, stopListening } = useRealtimeProducts()


const showHistoryModal = ref(false)
const historyProductId = ref<number | null>(null)
const stockHistory = ref<any[]>([])
const isLoadingHistory = ref(false)


const loadInventory = async () => {
  isLoading.value = true
  error.value = null
  try {
    const params: any = {
      per_page: 100,
      low_stock: stockFilter.value === 'low' ? true : undefined,
      stock_status: stockFilter.value === 'out' ? 'out_of_stock' : stockFilter.value === 'in_stock' ? 'in_stock' : undefined,
    }

    if (searchQuery.value) {
      params.search = searchQuery.value
    }

    const response = await productsApi.list(params)

    if (response.data.success) {
      const data = response.data.data
      
      const productsData = data.data || data || []

      inventory.value = productsData.map((p: any) => {
        
        let imagePath = '/images/products/placeholder.png'

        
        
        
        
        

        if (p.primary_image?.image_url) {
          imagePath = p.primary_image.image_url
        } else if (p.image) {
          imagePath = p.image
        } else if (p.primary_image?.image_path) {
          
          imagePath = p.primary_image.image_path
        } else if (p.images && Array.isArray(p.images) && p.images.length > 0) {
          imagePath = p.images[0].image_url || p.images[0].image_path || imagePath
        } else if (p.image_path) {
          imagePath = p.image_path
        }

        
        if (imagePath && !imagePath.startsWith('http') && !imagePath.startsWith('/images') && !imagePath.startsWith('blob:')) {
          
          
          
        }

        return {
          id: p.id,
          name: p.name || 'Unnamed Product',
          sku: p.sku || `PROD-${p.id}`,
          category: p.category?.name || p.category_name || 'Uncategorized',
          stock: parseInt(p.stock_quantity || p.stock || 0),
          lowStockThreshold: parseInt(p.low_stock_threshold || p.low_stock_threshold || 5),
          lastUpdated: new Date(p.updated_at || p.created_at || Date.now()),
          image: imagePath,
        }
      })
    } else {
      throw new Error(response.data.message || 'Failed to load inventory')
    }
  } catch (err: any) {
    console.error('Failed to load inventory:', err)
    error.value = err.response?.data?.message || err.message || 'Failed to load inventory. Please try again.'
    showError('Failed to Load Inventory', error.value)
    inventory.value = []
  } finally {
    isLoading.value = false
  }
}

const lowStockItems = computed(() =>
  inventory.value.filter(item => item.stock <= item.lowStockThreshold)
)


const filteredInventory = computed(() => inventory.value)

const getStockStatus = (item: InventoryItem) => {
  if (item.stock === 0) return 'out-of-stock'
  if (item.stock <= item.lowStockThreshold) return 'low-stock'
  return 'in-stock'
}

const getStockStatusLabel = (item: InventoryItem) => {
  if (item.stock === 0) return 'Out of Stock'
  if (item.stock <= item.lowStockThreshold) return 'Low Stock'
  return 'In Stock'
}

const formatDate = (date: Date) => {
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}

const getTypeClass = (type: string) => {
  if (type === 'adjustment') return 'type-adjustment'
  if (type === 'sale') return 'type-sale'
  if (type === 'restock') return 'type-restock'
  return ''
}

const getChangeClass = (change: number) => {
  if (change > 0) return 'change-positive'
  if (change < 0) return 'change-negative'
  return 'change-neutral'
}

const adjustStock = (item: InventoryItem) => {
  adjustingItem.value = item
  adjustmentForm.value = {
    type: 'add',
    quantity: 0,
    reason: ''
  }
  showAdjustModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeAdjustModal = (force = false) => {
  
  if (!force && isSaving.value) {
    
    return
  }
  showAdjustModal.value = false
  adjustingItem.value = null
  adjustmentForm.value = { type: 'add', quantity: 0, reason: '' }
  document.body.style.overflow = ''
}

const saveAdjustment = async () => {
  if (!adjustingItem.value) return

  
  if (adjustmentForm.value.quantity <= 0) {
    showError('Invalid Quantity', 'Quantity must be greater than 0.')
    return
  }

  if (adjustmentForm.value.type === 'subtract' && adjustmentForm.value.quantity > adjustingItem.value.stock) {
    showError('Invalid Quantity', `Cannot subtract more than current stock (${adjustingItem.value.stock}).`)
    return
  }

  isSaving.value = true

  try {
    const response = await productsApi.updateStock(
      adjustingItem.value.id,
      adjustmentForm.value.quantity,
      adjustmentForm.value.type,
      adjustmentForm.value.reason || undefined
    )

    if (response.data.success) {
      const oldQty = response.data.data.old_quantity
      const newQty = response.data.data.new_quantity
      const productName = adjustingItem.value.name

      
      const item = inventory.value.find(i => i.id === adjustingItem.value.id)
      if (item) {
        item.stock = newQty
        item.lastUpdated = new Date()
      }

      
      closeAdjustModal(true)

      
      success(
        'Stock Adjusted Successfully',
        `Stock for "${productName}" has been updated from ${oldQty} to ${newQty}.`
      )

      
      await loadInventory()
    } else {
      throw new Error(response.data.message || 'Failed to adjust stock')
    }
  } catch (error: any) {
    console.error('Failed to adjust stock:', error)
    showError(
      'Failed to Adjust Stock',
      error.response?.data?.message || error.message || 'Failed to adjust stock. Please try again.'
    )
  } finally {
    isSaving.value = false
  }
}

const quickRestock = (item: InventoryItem) => {
  adjustStock(item)
  adjustmentForm.value.type = 'add'
  adjustmentForm.value.quantity = item.lowStockThreshold * 2
}

const viewHistory = async (id: number) => {
  historyProductId.value = id
  showHistoryModal.value = true
  document.body.style.overflow = 'hidden'
  await loadStockHistory(id)
}

const loadStockHistory = async (productId: number) => {
  isLoadingHistory.value = true
  try {
    const response = await productsApi.getStockHistory(productId, { per_page: 50 })
    if (response.data.success) {
      
      const data = response.data.data
      if (data.data && Array.isArray(data.data)) {
        
        stockHistory.value = data.data
      } else if (Array.isArray(data)) {
        
        stockHistory.value = data
      } else {
        stockHistory.value = []
      }
    } else {
      throw new Error(response.data.message || 'Failed to load stock history')
    }
  } catch (error: any) {
    console.error('Failed to load stock history:', error)
    showError(
      'Failed to Load History',
      error.response?.data?.message || error.message || 'Failed to load stock history. Please try again.'
    )
    stockHistory.value = []
  } finally {
    isLoadingHistory.value = false
  }
}

const closeHistoryModal = () => {
  showHistoryModal.value = false
  historyProductId.value = null
  stockHistory.value = []
  document.body.style.overflow = ''
}


const handleStockChanged = (event: Event) => {
  const customEvent = event as CustomEvent
  const data = customEvent.detail
  const item = inventory.value.find((i: InventoryItem) => i.id === data.product_id)
  if (item) {
    item.stock = data.new_quantity
  }
  loadInventory()
}


watch([searchQuery, stockFilter], () => {
  loadInventory()
})

onMounted(async () => {
  
  const urlParams = new URLSearchParams(window.location.search)
  const searchParam = urlParams.get('search')
  if (searchParam) {
    searchQuery.value = searchParam
  }

  await loadInventory()

  
  startListening()
  window.addEventListener('realtime:stock:changed', handleStockChanged)
})

onUnmounted(() => {
  window.removeEventListener('realtime:stock:changed', handleStockChanged)
  stopListening()
  document.body.style.overflow = ''
})
</script>

<style scoped>
.admin-inventory-page {
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

.btn-primary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #c9a050;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary:hover:not(:disabled) {
  background: #b8860b;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(201, 160, 80, 0.3);
}

.btn-primary svg {
  width: 18px;
  height: 18px;
}

.alerts-section {
  margin-bottom: 2rem;
}

.alert-card {
  background: #fffbeb;
  border: 1px solid #fef3c7;
  border-left: 4px solid #f59e0b;
  border-radius: 12px;
  padding: 1.5rem;
}

.alert-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.alert-header svg {
  width: 24px;
  height: 24px;
  color: #f59e0b;
}

.alert-header h3 {
  margin: 0;
  color: var(--dark);
}

.alert-items {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.alert-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: var(--white);
  border-radius: 8px;
}

.alert-product {
  flex: 1;
  font-weight: 600;
  color: #000000;
}

.alert-stock {
  color: #d97706; /* Darker amber for better visibility */
  font-weight: 700;
}

.alert-stock.out-of-stock {
  color: #dc2626; /* Red */
}

.btn-small {
  padding: 0.5rem 1rem;
  background: var(--gold);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
}

.filters-bar {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.search-box {
  flex: 1;
  position: relative;
  display: flex;
  align-items: center;
}

.search-box svg {
  position: absolute;
  left: 1rem;
  width: 20px;
  height: 20px;
  color: #6b7280;
}

.search-input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 3rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
}

.filter-select {
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: var(--white);
  font-size: 0.9rem;
  cursor: pointer;
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

.product-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.product-thumb-container {
  width: 50px;
  height: 50px;
  border-radius: 8px;
  overflow: hidden;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #e5e7eb;
}

.product-thumb {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-name {
  font-weight: 600;
}

.product-category {
  font-size: 0.75rem;
  color: #6b7280;
}

.sku {
  font-family: monospace;
  color: #6b7280;
}

.low-stock {
  color: #f59e0b;
  font-weight: 600;
}

.out-of-stock {
  color: #ef4444;
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

.status-badge.in-stock {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.low-stock {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.out-of-stock {
  background: #fee2e2;
  color: #991b1b;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: none;
  background: #f3f4f6;
  color: #6b7280;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.action-btn:hover {
  background: #e5e7eb;
  color: var(--gold);
}

.action-btn svg {
  width: 18px;
  height: 18px;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  animation: fadeIn 0.3s ease;
  padding: 1rem;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
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

.form-group input::placeholder,
.form-group textarea::placeholder {
  color: #9ca3af;
}

.form-group input:disabled {
  background: #f3f4f6;
  color: #000000;
}

.form-group select option {
  color: #000000;
  background: #ffffff;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: #f3f4f6;
  color: var(--dark);
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.btn-primary:disabled,
.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* History Modal Styles */
.history-modal {
  max-width: 900px;
  background: #ffffff;
}

.loading-state,
.empty-state {
  text-align: center;
  padding: 3rem;
  color: #000000;
}

.history-table-container {
  max-height: 500px;
  overflow-y: auto;
}

.history-table {
  width: 100%;
  border-collapse: collapse;
}

.history-table thead {
  background: #f9fafb;
  position: sticky;
  top: 0;
  z-index: 10;
}

.history-table th {
  padding: 0.75rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #000000;
  border-bottom: 2px solid #e5e7eb;
}

.history-table td {
  padding: 0.75rem;
  border-bottom: 1px solid #e5e7eb;
  color: #000000;
  font-size: 0.875rem;
}

.date-cell {
  white-space: nowrap;
}

.type-badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.type-badge.type-adjustment {
  background: #dbeafe;
  color: #1e40af;
}

.type-badge.type-sale {
  background: #fee2e2;
  color: #991b1b;
}

.type-badge.type-restock {
  background: #d1fae5;
  color: #065f46;
}

.change-positive {
  color: #059669;
  font-weight: 600;
}

.change-negative {
  color: #dc2626;
  font-weight: 600;
}

.change-neutral {
  color: #6b7280;
}

.reason-cell {
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
