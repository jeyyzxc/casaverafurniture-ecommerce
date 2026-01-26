<template>
  <div class="admin-categories-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Category Management</h1>
        <p class="page-subtitle">Organize your products with categories and collections.</p>
      </div>
      <div class="header-actions">
        <button class="btn-primary" @click="openAddModal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 8v8M8 12h8"/>
          </svg>
          Add Category
        </button>
      </div>
    </div>

    <div v-if="isLoading && categories.length === 0" class="loading-state">
      <div class="spinner"></div>
      <p>Loading categories...</p>
    </div>

    <div v-else-if="error && categories.length === 0" class="error-state">
      <p><strong>Error:</strong> {{ error }}</p>
      <button class="btn-secondary" @click="loadCategories">Retry</button>
    </div>

    <div v-else-if="categories.length === 0" class="empty-state">
      <p>No categories found. Create your first category to get started.</p>
    </div>

    <div v-else class="categories-grid">
      <div v-for="category in categories" :key="category.id" class="category-card">
        <div class="category-header">
          <div class="category-icon" :style="{ background: category.color }">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 7h16M4 12h16M4 17h16"/>
            </svg>
          </div>
          <div class="category-actions">
            <button class="icon-btn" @click="editCategory(category)" title="Edit">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
              </svg>
            </button>
            <button class="icon-btn danger" @click="deleteCategory(category.id)" title="Delete">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
              </svg>
            </button>
          </div>
        </div>
        <div class="category-body">
          <h3 class="category-name">{{ category.name }}</h3>
          <p class="category-description">{{ category.description || 'No description' }}</p>
          <div class="category-stats">
            <div class="stat">
              <span class="stat-value">{{ category.productCount }}</span>
              <span class="stat-label">Products</span>
            </div>
            <div class="stat">
              <span class="stat-value">{{ category.subCategories?.length || 0 }}</span>
              <span class="stat-label">Sub-categories</span>
            </div>
          </div>
          <div class="category-visibility">
            <label class="toggle-switch">
              <input type="checkbox" v-model="category.isVisible" @change="updateVisibility(category)">
              <span class="toggle-slider"></span>
            </label>
            <span class="visibility-label">{{ category.isVisible ? 'Visible' : 'Hidden' }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h2>{{ editingCategory ? 'Edit Category' : 'Add Category' }}</h2>
            <button class="close-btn" @click.stop="closeModal(true)">×</button>
          </div>
          <form @submit.prevent="saveCategory" class="modal-body">
            <div class="form-group">
              <label>Category Name *</label>
              <input v-model="form.name" type="text" required placeholder="e.g., Living Room" :disabled="isSaving" style="color: #000000;">
            </div>
            <div class="form-group">
              <label>Description</label>
              <textarea v-model="form.description" rows="3" placeholder="Category description..." :disabled="isSaving" style="color: #000000;"></textarea>
            </div>
            <div class="form-group">
              <label>Parent Category</label>
              <select v-model="form.parentId" :disabled="isSaving" style="color: #000000;">
                <option value="">None (Main Category)</option>
                <option v-for="cat in mainCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>
            <div class="form-group">
              <label>Color</label>
              <input v-model="form.color" type="color" :disabled="isSaving">
            </div>
            <div class="form-group">
              <label>Display Order</label>
              <input v-model.number="form.displayOrder" type="number" min="0" :disabled="isSaving" style="color: #000000;">
            </div>
            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="form.isVisible" :disabled="isSaving">
                <span>Visible on website</span>
              </label>
            </div>
            <div class="form-actions">
              <button type="button" class="btn-secondary" @click.stop="closeModal(true)" :disabled="isSaving" style="color: #000000;">Cancel</button>
              <button type="submit" class="btn-primary" :disabled="isSaving">
                {{ isSaving ? 'Saving...' : (editingCategory ? 'Update Category' : 'Create Category') }}
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
            <h2 class="delete-title">Delete Category</h2>
            <p class="delete-message">
              Are you sure you want to delete 
              <strong class="delete-item-name">{{ deletingCategory?.name }}</strong>?
            </p>
            <p class="delete-warning">
              This action cannot be undone. All category data will be permanently removed.
            </p>
            <div class="delete-actions">
              <button type="button" class="delete-btn-cancel" @click.stop="closeDeleteModal(true)" :disabled="isDeleting">
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
                {{ isDeleting ? 'Deleting...' : 'Delete Category' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Teleport } from 'vue'
import { categories as categoriesApi } from '@/services/adminApi'
import { useRealtimeProducts } from '@/composables/useRealtimeProducts'
import { useNotification } from '@/composables/useNotification'

const { success, error: showError } = useNotification()

interface Category {
  id: number
  name: string
  description?: string
  parent_id?: number
  parentId?: number
  color: string
  display_order: number
  displayOrder: number
  is_visible: boolean
  isVisible: boolean
  products_count?: number
  productCount: number
  subCategories?: Category[]
}

const categories = ref<Category[]>([])
const isLoading = ref(false)
const error = ref<string | null>(null)
const { startListening, stopListening } = useRealtimeProducts()

// Load categories from API
const loadCategories = async () => {
  isLoading.value = true
  error.value = null
  try {
    const response = await categoriesApi.list({ hierarchical: true })
    if (response.data.success) {
      categories.value = (response.data.data || []).map((cat: any) => ({
        id: cat.id,
        name: cat.name,
        description: cat.description || '',
        parent_id: cat.parent_id,
        parentId: cat.parent_id,
        color: cat.color || '#c9a050',
        display_order: cat.display_order || 0,
        displayOrder: cat.display_order || 0,
        is_visible: cat.is_visible !== false,
        isVisible: cat.is_visible !== false,
        products_count: cat.products_count || 0,
        productCount: cat.products_count || 0,
        subCategories: cat.children?.map((child: any) => ({
          id: child.id,
          name: child.name,
          description: child.description || '',
          parent_id: child.parent_id,
          parentId: child.parent_id,
          color: child.color || '#c9a050',
          display_order: child.display_order || 0,
          displayOrder: child.display_order || 0,
          is_visible: child.is_visible !== false,
          isVisible: child.is_visible !== false,
          products_count: child.products_count || 0,
          productCount: child.products_count || 0,
        })) || [],
      }))
    } else {
      throw new Error(response.data.message || 'Failed to load categories')
    }
  } catch (err: any) {
    console.error('Failed to load categories:', err)
    error.value = err.response?.data?.message || err.message || 'Failed to load categories. Please try again.'
    showError('Failed to Load Categories', error.value)
    categories.value = []
  } finally {
    isLoading.value = false
  }
}

const showModal = ref(false)
const editingCategory = ref<Category | null>(null)
const showDeleteModal = ref(false)
const deletingCategory = ref<Category | null>(null)
const isSaving = ref(false)
const isDeleting = ref(false)
const form = ref({
  name: '',
  description: '',
  parentId: '',
  color: '#c9a050',
  displayOrder: 0,
  isVisible: true
})

const mainCategories = computed(() => categories.value.filter(c => !c.parentId))

const openAddModal = () => {
  editingCategory.value = null
  form.value = {
    name: '',
    description: '',
    parentId: '',
    color: '#c9a050',
    displayOrder: categories.value.length + 1,
    isVisible: true
  }
  showModal.value = true
  document.body.style.overflow = 'hidden'
}

const editCategory = (category: Category) => {
  editingCategory.value = category
  form.value = {
    name: category.name,
    description: category.description || '',
    parentId: category.parentId?.toString() || '',
    color: category.color,
    displayOrder: category.displayOrder,
    isVisible: category.isVisible
  }
  showModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeModal = (force = false) => {
  if (!force && isSaving.value) return
  showModal.value = false
  editingCategory.value = null
  document.body.style.overflow = ''
}

const saveCategory = async () => {
  if (!form.value.name.trim()) {
    showError('Validation Error', 'Category name is required.')
    return
  }

  isSaving.value = true

  try {
    const categoryData = {
      name: form.value.name.trim(),
      description: form.value.description || null,
      parent_id: form.value.parentId ? parseInt(form.value.parentId) : null,
      color: form.value.color,
      display_order: form.value.displayOrder || 0,
      is_visible: form.value.isVisible,
    }

    let response
    if (editingCategory.value) {
      response = await categoriesApi.update(editingCategory.value.id, categoryData)
    } else {
      response = await categoriesApi.create(categoryData)
    }

    if (response.data.success) {
      const isEdit = !!editingCategory.value
      isSaving.value = false
      closeModal(true)
      
      success(
        isEdit ? 'Category Updated' : 'Category Created',
        `Category "${categoryData.name}" has been ${isEdit ? 'updated' : 'created'} successfully.`
      )
      
      await loadCategories()
    } else {
      throw new Error(response.data.message || `Failed to ${editingCategory.value ? 'update' : 'create'} category`)
    }
  } catch (err: any) {
    console.error('Failed to save category:', err)
    showError(
      `Failed to ${editingCategory.value ? 'Update' : 'Create'} Category`,
      err.response?.data?.message || err.message || 'Failed to save category. Please try again.'
    )
  } finally {
    isSaving.value = false
  }
}

const deleteCategory = (id: number) => {
  const category = categories.value.find(c => c.id === id)
  if (category) {
    deletingCategory.value = category
    showDeleteModal.value = true
    document.body.style.overflow = 'hidden'
  }
}

const confirmDelete = async () => {
  if (!deletingCategory.value) return

  isDeleting.value = true

  try {
    const response = await categoriesApi.delete(deletingCategory.value.id)
    
    if (response.data.success) {
      success(
        'Category Deleted',
        `Category "${deletingCategory.value.name}" has been deleted successfully.`
      )
      closeDeleteModal()
      await loadCategories()
    } else {
      throw new Error(response.data.message || 'Failed to delete category')
    }
  } catch (err: any) {
    console.error('Failed to delete category:', err)
    showError(
      'Failed to Delete Category',
      err.response?.data?.message || err.message || 'Failed to delete category. Please try again.'
    )
  } finally {
    isDeleting.value = false
  }
}

const closeDeleteModal = (force = false) => {
  // Allow closing if force is true or if not currently deleting
  if (!force && isDeleting.value) {
    // Don't close if currently deleting (unless forced)
    return
  }
  showDeleteModal.value = false
  deletingCategory.value = null
  document.body.style.overflow = ''
}

const updateVisibility = async (category: Category) => {
  const originalValue = category.isVisible
  
  try {
    const response = await categoriesApi.update(category.id, { is_visible: category.isVisible })
    
    if (response.data.success) {
      success(
        'Visibility Updated',
        `Category "${category.name}" is now ${category.isVisible ? 'visible' : 'hidden'}.`
      )
    } else {
      throw new Error(response.data.message || 'Failed to update visibility')
    }
  } catch (err: any) {
    console.error('Failed to update visibility:', err)
    // Revert the change on error
    category.isVisible = !category.isVisible
    showError(
      'Failed to Update Visibility',
      err.response?.data?.message || err.message || 'Failed to update visibility. Please try again.'
    )
  }
}

// Real-time event handlers
const handleCategoryCreated = () => loadCategories()
const handleCategoryUpdated = () => loadCategories()
const handleCategoryDeleted = () => loadCategories()

onMounted(async () => {
  await loadCategories()

  // Set up real-time listeners using window events
  startListening()
  
  window.addEventListener('realtime:product:created', handleCategoryCreated)
  window.addEventListener('realtime:product:updated', handleCategoryUpdated)
  window.addEventListener('realtime:product:deleted', handleCategoryDeleted)
})

onUnmounted(() => {
  window.removeEventListener('realtime:product:created', handleCategoryCreated)
  window.removeEventListener('realtime:product:updated', handleCategoryUpdated)
  window.removeEventListener('realtime:product:deleted', handleCategoryDeleted)
  stopListening()
})
</script>

<style scoped>
.admin-categories-page {
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
  background: var(--gold);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary:hover {
  background: #b8860b;
  transform: translateY(-1px);
}

.btn-primary svg {
  width: 18px;
  height: 18px;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.category-card {
  background: var(--white);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s, box-shadow 0.2s;
}

.category-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.category-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  background: linear-gradient(135deg, #f9fafb, #f3f4f6);
}

.category-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.category-icon svg {
  width: 28px;
  height: 28px;
}

.category-actions {
  display: flex;
  gap: 0.5rem;
}

.icon-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: none;
  background: var(--white);
  color: #6b7280;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.icon-btn:hover {
  background: #f3f4f6;
  color: var(--gold);
}

.icon-btn.danger:hover {
  background: #fee2e2;
  color: #ef4444;
}

.icon-btn svg {
  width: 18px;
  height: 18px;
}

.category-body {
  padding: 1.5rem;
}

.category-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--dark);
  margin: 0 0 0.5rem;
}

.category-description {
  color: #6b7280;
  font-size: 0.9rem;
  margin: 0 0 1rem;
}

.category-stats {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.stat {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--gold);
}

.stat-label {
  font-size: 0.75rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.category-visibility {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 48px;
  height: 24px;
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

.visibility-label {
  font-size: 0.9rem;
  color: #6b7280;
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

/* Modal */
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
  transition: border-color 0.2s;
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

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: var(--gold);
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  color: #000000;
}

.checkbox-label input[type="checkbox"] {
  width: auto;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
  padding-top: 1.5rem;
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
</style>
