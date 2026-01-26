<template>
  <div class="admin-admins-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Admin Users</h1>
        <p class="page-subtitle">Manage admin accounts, roles, and permissions.</p>
      </div>
      <div class="header-actions">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search admins..."
          class="search-input"
          @input="debouncedSearch"
        />
        <select v-model="filterRole" class="filter-select" @change="loadAdmins">
          <option value="">All Roles</option>
          <option v-for="role in roles" :key="role.id" :value="role.id">
            {{ role.name }}
          </option>
        </select>
        <select v-model="filterStatus" class="filter-select" @change="loadAdmins">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
        <button
          v-if="isSuperAdmin"
          class="btn-primary"
          @click="openAddModal"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 8v8M8 12h8"/>
          </svg>
          Add Admin
        </button>
      </div>
    </div>

    <div v-if="error" class="error-message">
      {{ error }}
    </div>

    <div class="table-card" v-if="!isLoading">
      <table class="data-table">
        <thead>
          <tr>
            <th>Admin</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Last Login</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="admins.length === 0">
            <td colspan="6" class="no-data">No admins found</td>
          </tr>
          <tr v-else v-for="admin in admins" :key="admin.id">
            <td>
              <div class="admin-cell">
                <div class="admin-avatar">
                  {{ admin.first_name.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <div class="admin-name">{{ admin.full_name }}</div>
                  <div class="admin-id">ID: {{ admin.id }}</div>
                </div>
              </div>
            </td>
            <td>{{ admin.email }}</td>
            <td>
              <span class="role-badge" :class="getRoleClass(admin.role?.slug)">
                {{ admin.role?.name || 'No Role' }}
              </span>
            </td>
            <td>
              <span class="status-badge" :class="admin.status.toLowerCase()">
                {{ admin.status }}
              </span>
            </td>
            <td class="date">
              {{ admin.last_login_at ? formatDate(admin.last_login_at) : 'Never' }}
            </td>
            <td>
              <div class="action-buttons">
                <button
                  v-if="isSuperAdmin || currentAdminId === admin.id"
                  class="action-btn"
                  @click="editAdmin(admin)"
                  title="Edit"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                </button>
                <button
                  v-if="isSuperAdmin && currentAdminId !== admin.id"
                  class="action-btn danger"
                  @click="openDeleteModal(admin)"
                  title="Delete"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="pagination" v-if="pagination.last_page > 1">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="page-btn"
        >
          Previous
        </button>
        <span class="page-info">
          Page {{ pagination.current_page }} of {{ pagination.last_page }} ({{ pagination.total }} total)
        </span>
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="page-btn"
        >
          Next
        </button>
      </div>
    </div>

    <div v-if="isLoading" class="loading">
      Loading admins...
    </div>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <div class="modal-overlay" :class="{ active: showDeleteModal }" @click.self="closeDeleteModal">
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
            
            <h2 class="delete-title">Delete Admin</h2>
            <p class="delete-message">
              Are you sure you want to delete 
              <strong class="delete-admin-name">{{ deletingAdmin?.full_name }}</strong>?
            </p>
            <p class="delete-warning">
              This action cannot be undone. All admin data will be permanently removed.
            </p>

            <div class="delete-actions">
              <button type="button" class="delete-btn-cancel" @click="closeDeleteModal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/>
                  <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Cancel
              </button>
              <button type="button" class="delete-btn-confirm" @click="confirmDelete" :disabled="isDeleting">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                {{ isDeleting ? 'Deleting...' : 'Delete Admin' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Delete Success Notification -->
    <Teleport to="body">
      <div class="success-notification" :class="{ active: showDeleteSuccess }">
        <div class="success-content">
          <div class="success-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          </div>
          <div class="success-text">
            <div class="success-title">Admin Deleted</div>
            <div class="success-message">The admin account has been successfully removed.</div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Add/Edit Modal -->
    <Teleport to="body">
      <div class="modal-overlay" :class="{ active: showModal }" @click.self="closeModal">
        <div class="modal-container" @click.stop>
          <div class="modal-header">
            <h2>{{ editingAdmin ? 'Edit Admin' : 'Add New Admin' }}</h2>
            <button class="modal-close" @click="closeModal">×</button>
          </div>
          <form @submit.prevent="saveAdmin" class="modal-body">
          <div class="form-group">
            <label>First Name *</label>
            <input
              type="text"
              v-model="formData.first_name"
              required
              placeholder="First name"
            />
          </div>
          <div class="form-group">
            <label>Last Name *</label>
            <input
              type="text"
              v-model="formData.last_name"
              required
              placeholder="Last name"
            />
          </div>
          <div class="form-group">
            <label>Email *</label>
            <input
              type="email"
              v-model="formData.email"
              required
              placeholder="email@example.com"
            />
          </div>
          <div class="form-group" v-if="!editingAdmin">
            <label>Password *</label>
            <input
              type="password"
              v-model="formData.password"
              :required="!editingAdmin"
              placeholder="Minimum 8 characters"
              minlength="8"
            />
          </div>
          <div class="form-group" v-if="!editingAdmin">
            <label>Confirm Password *</label>
            <input
              type="password"
              v-model="formData.password_confirmation"
              :required="!editingAdmin"
              placeholder="Confirm password"
            />
          </div>
          <div class="form-group">
            <label>Phone</label>
            <input
              type="text"
              v-model="formData.phone"
              placeholder="Phone number"
            />
          </div>
          <div class="form-group" v-if="isSuperAdmin">
            <label>Role *</label>
            <select v-model="formData.role_id" required>
              <option value="">Select Role</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.name }}
              </option>
            </select>
          </div>
          <div class="form-group" v-if="isSuperAdmin">
            <label>Status</label>
            <select v-model="formData.status">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div v-if="formError" class="form-error">{{ formError }}</div>
          <div class="modal-actions">
            <button type="button" class="btn-secondary" @click="closeModal">Cancel</button>
            <button type="submit" class="btn-primary" :disabled="isSaving">
              {{ isSaving ? 'Saving...' : (editingAdmin ? 'Update' : 'Create') }}
            </button>
          </div>
        </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { admins as adminsApi } from '@/services/adminApi'
import { useAdminAuthStore } from '@/stores/adminAuth'

interface Admin {
  id: number
  first_name: string
  last_name: string
  full_name: string
  email: string
  phone: string | null
  avatar: string | null
  role_id: number
  role: {
    id: number
    name: string
    slug: string
  } | null
  status: 'active' | 'inactive'
  last_login_at?: string
  last_login_ip?: string
  created_at: string
  updated_at: string
}

interface Role {
  id: number
  name: string
  slug: string
  description: string
}

const adminAuthStore = useAdminAuthStore()

const admins = ref<Admin[]>([])
const roles = ref<Role[]>([])
const isLoading = ref(false)
const error = ref<string | null>(null)
const searchQuery = ref('')
const filterRole = ref<number | string>('')
const filterStatus = ref<string>('')
const showModal = ref(false)
const editingAdmin = ref<Admin | null>(null)
const isSaving = ref(false)
const formError = ref<string | null>(null)
const showDeleteModal = ref(false)
const deletingAdmin = ref<Admin | null>(null)
const isDeleting = ref(false)
const showDeleteSuccess = ref(false)

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

const formData = ref({
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  password_confirmation: '',
  phone: '',
  role_id: null as number | null,
  status: 'active' as 'active' | 'inactive',
})

const isSuperAdmin = computed(() => {
  return adminAuthStore.admin?.role?.slug === 'super-admin'
})

const currentAdminId = computed(() => adminAuthStore.admin?.id)

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout> | null = null
const debouncedSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    loadAdmins()
  }, 500)
}

const loadAdmins = async () => {
  isLoading.value = true
  error.value = null

  try {
    const params: Record<string, unknown> = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
    }

    if (searchQuery.value) {
      params.search = searchQuery.value
    }

    if (filterRole.value) {
      params.role_id = filterRole.value
    }

    if (filterStatus.value) {
      params.status = filterStatus.value
    }

    const response = await adminsApi.list(params)

    if (response.data.success) {
      admins.value = response.data.data.data || []
      pagination.value = {
        current_page: response.data.data.current_page || 1,
        last_page: response.data.data.last_page || 1,
        per_page: response.data.data.per_page || 15,
        total: response.data.data.total || 0,
      }
    } else {
      error.value = 'Failed to load admins'
    }
  } catch (err: unknown) {
    console.error('Failed to load admins:', err)
    error.value = 'Failed to load admins. Please try again.'
  } finally {
    isLoading.value = false
  }
}

const loadRoles = async () => {
  try {
    const response = await adminsApi.getRoles()
    if (response.data.success) {
      roles.value = response.data.data || []
    }
  } catch (err) {
    console.error('Failed to load roles:', err)
  }
}

const formatDate = (dateString: string) => {
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(dateString))
}

const getRoleClass = (slug: string | undefined): string => {
  if (!slug) return ''
  // Return slug as-is (CSS classes can have dashes)
  return slug
}

const openAddModal = () => {
  if (!isSuperAdmin.value) {
    alert('Only Super Admin can add admin accounts.')
    return
  }
  editingAdmin.value = null
  formData.value = {
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    role_id: null,
    status: 'active',
  }
  formError.value = null
  showModal.value = true
  document.body.style.overflow = 'hidden'
}

const editAdmin = (admin: Admin) => {
  if (!isSuperAdmin.value && currentAdminId.value !== admin.id) {
    alert('You can only edit your own profile.')
    return
  }
  editingAdmin.value = admin
  formData.value = {
    first_name: admin.first_name,
    last_name: admin.last_name,
    email: admin.email,
    password: '',
    password_confirmation: '',
    phone: admin.phone || '',
    role_id: admin.role_id,
    status: admin.status,
  }
  formError.value = null
  showModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeModal = () => {
  showModal.value = false
  editingAdmin.value = null
  formError.value = null
  document.body.style.overflow = ''
}

const saveAdmin = async () => {
  if (!isSuperAdmin.value && editingAdmin.value && currentAdminId.value !== editingAdmin.value.id) {
    alert('You can only edit your own profile.')
    return
  }

  isSaving.value = true
  formError.value = null

  try {
    if (editingAdmin.value) {
      // Update existing admin
      const updateData: Record<string, unknown> = {
        first_name: formData.value.first_name,
        last_name: formData.value.last_name,
        email: formData.value.email,
        phone: formData.value.phone || null,
      }

      // Only super-admin can update role and status
      if (isSuperAdmin.value) {
        updateData.role_id = formData.value.role_id
        updateData.status = formData.value.status
      }

      const response = await adminsApi.update(editingAdmin.value.id, updateData)

      if (response.data.success) {
        await loadAdmins()
        closeModal()
      } else {
        formError.value = response.data.message || 'Failed to update admin'
      }
    } else {
      // Create new admin
      if (!isSuperAdmin.value) {
        formError.value = 'Only Super Admin can create admin accounts.'
        return
      }

      const response = await adminsApi.create({
        first_name: formData.value.first_name,
        last_name: formData.value.last_name,
        email: formData.value.email,
        password: formData.value.password,
        password_confirmation: formData.value.password_confirmation,
        phone: formData.value.phone || undefined,
        role_id: formData.value.role_id!,
        status: formData.value.status,
      })

      if (response.data.success) {
        await loadAdmins()
        closeModal()
      } else {
        formError.value = response.data.message || 'Failed to create admin'
      }
    }
  } catch (err: any) {
    console.error('Failed to save admin:', err)
    formError.value = err.response?.data?.message || err.message || 'Failed to save admin'
  } finally {
    isSaving.value = false
  }
}

const openDeleteModal = (admin: Admin) => {
  if (!isSuperAdmin.value) {
    alert('Only Super Admin can delete admin accounts.')
    return
  }

  if (currentAdminId.value === admin.id) {
    alert('You cannot delete your own account.')
    return
  }

  deletingAdmin.value = admin
  showDeleteModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  deletingAdmin.value = null
  document.body.style.overflow = ''
}

const confirmDelete = async () => {
  if (!deletingAdmin.value) return

  isDeleting.value = true

  try {
    const response = await adminsApi.delete(deletingAdmin.value.id)
    
    if (response.data.success) {
      closeDeleteModal()
      
      // Show success notification
      showDeleteSuccess.value = true
      setTimeout(() => {
        showDeleteSuccess.value = false
      }, 4000)
      
      // Reload admins
      await loadAdmins()
    } else {
      alert(response.data.message || 'Failed to delete admin')
    }
  } catch (err: any) {
    console.error('Failed to delete admin:', err)
    alert(err.response?.data?.message || 'Failed to delete admin')
  } finally {
    isDeleting.value = false
  }
}

const changePage = (page: number) => {
  pagination.value.current_page = page
  loadAdmins()
}

onMounted(() => {
  loadAdmins()
  loadRoles()
})
</script>

<style scoped>
.admin-admins-page {
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

.header-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.search-input,
.filter-select {
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
  background: var(--white);
  color: var(--dark);
}

.search-input {
  min-width: 200px;
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
  transition: all 0.3s;
}

.btn-primary:hover {
  background: #b89040;
}

.modal-actions .btn-primary {
  color: #000000;
}

.btn-primary svg {
  width: 18px;
  height: 18px;
}

.error-message {
  background: #fee2e2;
  color: #991b1b;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
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

.no-data {
  text-align: center;
  padding: 2rem;
  color: var(--gray);
}

.admin-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.admin-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--gold);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
}

.admin-name {
  font-weight: 600;
}

.admin-id {
  font-size: 0.75rem;
  color: #6b7280;
}

.role-badge {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.role-badge.super-admin {
  background: #fef3c7;
  color: #92400e;
}

.role-badge.admin {
  background: #dbeafe;
  color: #1e40af;
}

.role-badge.manager {
  background: #d1fae5;
  color: #065f46;
}

.role-badge.support {
  background: #d1fae5;
  color: #065f46;
}

.role-badge.content-manager {
  background: #d1fae5;
  color: #065f46;
}

.role-badge.inventory {
  background: #d1fae5;
  color: #065f46;
}

.status-badge {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.active {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.inactive {
  background: #fee2e2;
  color: #991b1b;
}

.date {
  color: #6b7280;
  font-size: 0.9rem;
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

.action-btn.danger:hover {
  background: #fee2e2;
  color: #991b1b;
}

.action-btn svg {
  width: 18px;
  height: 18px;
}

.loading {
  text-align: center;
  padding: 2rem;
  color: var(--gray);
}

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.page-btn {
  padding: 0.5rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background: var(--white);
  color: var(--dark);
  cursor: pointer;
  transition: all 0.3s;
}

.page-btn:hover:not(:disabled) {
  background: #f9fafb;
  border-color: var(--gold);
  color: var(--gold);
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: var(--gray);
  font-size: 0.9rem;
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
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
}

.modal-overlay.active {
  opacity: 1;
  visibility: visible;
}

.modal-container {
  background: white;
  border-radius: 16px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  transform: scale(0.9);
  transition: transform 0.3s ease;
  color: #000000;
}

.modal-overlay.active .modal-container {
  transform: scale(1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #000000;
}

.modal-close {
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
}

.modal-close:hover {
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
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #000000;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #000000;
  background: #ffffff;
  transition: border-color 0.3s;
}

.form-group input::placeholder {
  color: #6b7280;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: var(--gold);
  color: #000000;
}

.form-group select option {
  color: #000000;
  background: #ffffff;
}

.form-error {
  background: #fee2e2;
  color: #000000;
  padding: 0.75rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  font-size: 0.9rem;
  font-weight: 600;
}

.modal-actions {
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
  transition: all 0.3s;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* ═══════════════════════════════════════════════════
   DELETE CONFIRMATION MODAL
   ═══════════════════════════════════════════════════ */
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
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
}

.modal-overlay.active {
  opacity: 1;
  visibility: visible;
}

.delete-modal {
  max-width: 480px;
  width: 100%;
  background: #ffffff;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(220, 53, 69, 0.1);
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
  0%, 100% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
  }
  50% {
    transform: scale(1.05);
    box-shadow: 0 0 0 8px rgba(220, 53, 69, 0);
  }
}

@keyframes ripple {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  100% {
    transform: scale(1.3);
    opacity: 0;
  }
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

.delete-admin-name {
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

.delete-btn-confirm:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* ═══════════════════════════════════════════════════
   DELETE SUCCESS NOTIFICATION
   ═══════════════════════════════════════════════════ */
.success-notification {
  position: fixed;
  top: 2rem;
  right: 2rem;
  z-index: 10000;
  opacity: 0;
  visibility: hidden;
  transform: translateX(400px);
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.success-notification.active {
  opacity: 1;
  visibility: visible;
  transform: translateX(0);
}

.success-content {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
  border-radius: 16px;
  box-shadow: 
    0 10px 25px -5px rgba(0, 0, 0, 0.15),
    0 0 0 1px rgba(16, 185, 129, 0.1);
  border-left: 4px solid #10b981;
  min-width: 320px;
  max-width: 420px;
  animation: slideInRight 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes slideInRight {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.success-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.success-icon svg {
  width: 24px;
  height: 24px;
  color: #10b981;
}

.success-text {
  flex: 1;
}

.success-title {
  font-weight: 700;
  font-size: 1rem;
  color: #1a1d29;
  margin-bottom: 0.25rem;
}

.success-message {
  font-size: 0.875rem;
  color: #6b7280;
}
</style>
