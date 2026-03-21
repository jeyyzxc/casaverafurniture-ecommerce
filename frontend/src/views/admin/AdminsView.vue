<template>
  <div class="admin-page-container">
    <div class="page-header">
      <div class="header-text">
        <h1 class="title">Admin Users</h1>
        <p class="subtitle">Manage system administrators and their permissions</p>
      </div>
      <button v-if="isSuperAdmin" class="btn-add" @click="openAddModal">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19"></line>
          <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <span>Add Admin</span>
      </button>
    </div>

    <div class="controls-panel">
      <div class="search-control">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search by name or email..."
          @input="debouncedSearch"
        />
      </div>

      <div class="filters-control">
        <select v-model="filterRole" @change="loadAdmins">
          <option value="">All Roles</option>
          <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
        </select>

        <select v-model="filterStatus" @change="loadAdmins">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>

        <button
          class="btn-reset"
          @click="resetFilters"
          title="Clear Filters"
          :disabled="!searchQuery && !filterRole && !filterStatus"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
            <path d="M3 3v5h5"/>
          </svg>
          <span>Reset</span>
        </button>
      </div>
    </div>

    <div v-if="error" class="alert-error">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
      </svg>
      {{ error }}
    </div>

    <div class="content-card">
      <div class="table-wrapper">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Admin User</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Last Login</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="isLoading && admins.length === 0">
              <td colspan="6" class="state-cell">
                <div class="spinner"></div>
                <p>Loading data...</p>
              </td>
            </tr>

            <tr v-else-if="admins.length === 0">
              <td colspan="6" class="state-cell">
                <p>No admins found.</p>
              </td>
            </tr>

            <tr v-else v-for="admin in admins" :key="admin.id">
              <td>
                <div class="user-info">
                  <div class="avatar">{{ admin.first_name.charAt(0).toUpperCase() }}</div>
                  <div class="details">
                    <span class="name">{{ admin.full_name }}</span>
                    <span class="id">ID: #{{ admin.id }}</span>
                  </div>
                </div>
              </td>
              <td class="email-text">{{ admin.email }}</td>
              <td>
                <span class="badge role" :class="getRoleClass(admin.role?.slug)">
                  {{ admin.role?.name || 'No Role' }}
                </span>
              </td>
              <td>
                <span class="badge status" :class="admin.status.toLowerCase()">
                  {{ admin.status }}
                </span>
              </td>
              <td class="meta-text">
                {{ admin.last_login_at ? formatDate(admin.last_login_at) : 'Never' }}
              </td>
              <td>
                <div class="actions">
                  <button
                    v-if="isSuperAdmin || currentAdminId === admin.id"
                    class="action-btn edit"
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
                    class="action-btn delete"
                    @click="openDeleteModal(admin)"
                    title="Delete Permanently"
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
      </div>

      <div class="pagination" v-if="pagination.last_page > 1">
        <span class="page-count">
          Page {{ pagination.current_page }} of {{ pagination.last_page }}
        </span>
        <div class="page-controls">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
          >
            Previous
          </button>
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showDeleteModal" class="modal-backdrop" @click.self="closeDeleteModal">
        <div class="modal-container delete-modal">
          <div class="delete-modal-content">
            <div class="delete-icon-wrapper">
              <div class="delete-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="3 6 5 6 21 6"/>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2-2v2"/>
                  <line x1="10" y1="11" x2="10" y2="17"/>
                  <line x1="14" y1="11" x2="14" y2="17"/>
                </svg>
              </div>
            </div>

            <h2 class="delete-title">Delete Admin</h2>
            <p class="delete-message">
              Are you sure you want to delete
              <strong class="delete-product-name">{{ deletingAdmin?.full_name }}</strong>?
            </p>
            <p class="delete-warning">
              This action cannot be undone. This admin will lose all access to the system.
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

      <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
        <div class="modal-card">
          <div class="modal-head">
            <h3>{{ editingAdmin ? 'Edit Admin' : 'Add New Admin' }}</h3>
            <button class="btn-close" @click="closeModal">×</button>
          </div>
          <form @submit.prevent="saveAdmin" class="modal-form">
            <div class="form-grid">
              <div class="field">
                <label>First Name</label>
                <input type="text" v-model="formData.first_name" required />
              </div>
              <div class="field">
                <label>Last Name</label>
                <input type="text" v-model="formData.last_name" required />
              </div>
            </div>
            <div class="field">
              <label>Email</label>
              <input type="email" v-model="formData.email" required />
            </div>
            <div class="form-grid" v-if="!editingAdmin">
              <div class="field">
                <label>Password</label>
                <div class="password-input-wrapper">
                  <input :type="showPassword ? 'text' : 'password'" v-model="formData.password" :required="!editingAdmin" minlength="8" />
                  <button type="button" class="toggle-password" @click="showPassword = !showPassword">
                    <svg v-if="showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                      <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                      <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                  </button>
                </div>
              </div>
              <div class="field">
                <label>Confirm Password</label>
                <div class="password-input-wrapper">
                  <input :type="showConfirmPassword ? 'text' : 'password'" v-model="formData.password_confirmation" :required="!editingAdmin" />
                  <button type="button" class="toggle-password" @click="showConfirmPassword = !showConfirmPassword">
                    <svg v-if="showConfirmPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                      <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                      <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
            <div class="field">
              <label>Phone</label>
              <input type="text" v-model="formData.phone" />
            </div>
            <div class="form-grid" v-if="isSuperAdmin">
              <div class="field">
                <label>Role</label>
                <select v-model="formData.role_id" required>
                  <option :value="null">Select Role</option>
                  <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                </select>
              </div>
              <div class="field">
                <label>Status</label>
                <select v-model="formData.status">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>

            <div v-if="formError" class="form-alert">{{ formError }}</div>

            <div class="modal-footer">
              <button type="button" class="btn-text" @click="closeModal">Cancel</button>
              <button type="submit" class="btn-primary" :disabled="isSaving">
                {{ isSaving ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <div v-if="showDeleteSuccess" class="toast-success">
        Admin permanently deleted.
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { admins as adminsApi } from '@/services/adminApi'
import { useAdminAuthStore } from '@/stores/adminAuth'
import { useNotification } from '@/composables/useNotification'

const { success, error: showError } = useNotification()

interface Admin {
  id: number
  first_name: string
  last_name: string
  full_name: string
  email: string
  phone: string | null
  avatar: string | null
  role_id: number
  role: { id: number; name: string; slug: string } | null
  status: 'active' | 'inactive'
  last_login_at?: string
  created_at: string
}

interface Role {
  id: number
  name: string
  slug: string
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

const showPassword = ref(false)
const showConfirmPassword = ref(false)

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

const isSuperAdmin = computed(() => adminAuthStore.admin?.role?.slug === 'super-admin')
const currentAdminId = computed(() => adminAuthStore.admin?.id)

const getErrorMessage = (error: unknown): string => {
  if (typeof error === 'object' && error !== null && 'response' in error) {
    const err = error as { response?: { data?: { message?: string } } };
    if (typeof err.response?.data?.message === 'string') {
      return err.response.data.message;
    }
  }
  if (error instanceof Error) {
    return error.message;
  }
  return 'An unexpected error occurred.';
};

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
    const params: any = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
    }
    if (searchQuery.value) params.search = searchQuery.value
    if (filterRole.value) params.role_id = filterRole.value
    if (filterStatus.value) params.status = filterStatus.value

    const response = await adminsApi.list(params)
    if (response.data.success) {
      admins.value = response.data.data.data || []
      pagination.value = {
        current_page: response.data.data.current_page || 1,
        last_page: response.data.data.last_page || 1,
        per_page: response.data.data.per_page || 15,
        total: response.data.data.total || 0,
      }
    }
  } catch (err: unknown) {
    error.value = 'Failed to load admins.'
    console.error(err)
  } finally {
    isLoading.value = false
  }
}

const loadRoles = async () => {
  try {
    const response = await adminsApi.getRoles()
    if (response.data.success) roles.value = response.data.data || []
  } catch (err) { console.error(err) }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const getRoleClass = (slug?: string) => slug || ''

const openAddModal = () => {
  editingAdmin.value = null
  formData.value = { first_name: '', last_name: '', email: '', password: '', password_confirmation: '', phone: '', role_id: null, status: 'active' }
  formError.value = null
  showPassword.value = false
  showConfirmPassword.value = false
  showModal.value = true
}

const editAdmin = (admin: Admin) => {
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
  showPassword.value = false
  showConfirmPassword.value = false
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingAdmin.value = null
}

const saveAdmin = async () => {
  isSaving.value = true
  formError.value = null
  try {
    if (editingAdmin.value) {
      const updateData: any = {
        first_name: formData.value.first_name,
        last_name: formData.value.last_name,
        email: formData.value.email,
        phone: formData.value.phone || null,
      }
      if (isSuperAdmin.value) {
        updateData.role_id = formData.value.role_id
        updateData.status = formData.value.status
      }
      const res = await adminsApi.update(editingAdmin.value.id, updateData)
      if (res.data.success) {
        await loadAdmins()
        closeModal()
      } else {
        formError.value = res.data.message
      }
    } else {
      const res = await adminsApi.create({
        ...formData.value,
        phone: formData.value.phone || undefined,
        role_id: formData.value.role_id!,
      })
      if (res.data.success) {
        await loadAdmins()
        closeModal()
      } else {
        formError.value = res.data.message
      }
    }
  } catch (error: unknown) {
    formError.value = getErrorMessage(error) || 'Failed to save.'
  } finally {
    isSaving.value = false
  }
}

const openDeleteModal = (admin: Admin) => {
  deletingAdmin.value = admin
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  deletingAdmin.value = null
}

const confirmDelete = async () => {
  if (!deletingAdmin.value) return
  isDeleting.value = true
  try {
    const res = await adminsApi.delete(deletingAdmin.value.id)
    if (res.data.success) {
      closeDeleteModal()
      showDeleteSuccess.value = true
      setTimeout(() => showDeleteSuccess.value = false, 3000)
      await loadAdmins()
    }
  } catch (error: unknown) {
    showError('Delete Failed', getErrorMessage(error) || 'Failed to delete admin.')
  } finally {
    isDeleting.value = false
  }
}

const resetFilters = () => {
  searchQuery.value = ''
  filterRole.value = ''
  filterStatus.value = ''
  loadAdmins()
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
.admin-page-container {
  padding: 3rem 2rem 2rem;
  max-width: 1400px;
  margin: 0 auto;
  color: #1a1d29;
}

/* Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.title {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
}

.subtitle {
  color: #64748b;
  margin: 0.5rem 0 0;
}

.btn-add {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #c9a050;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-add:hover {
  background: #b08d44;
}

.btn-add svg {
  width: 20px;
  height: 20px;
}

/* Controls */
.controls-panel {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 1rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.search-control {
  position: relative;
  flex: 1;
  min-width: 250px;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
  color: #94a3b8;
}

.search-control input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.95rem;
}

.filters-control {
  display: flex;
  gap: 0.75rem;
}

.filters-control select {
  padding: 0.75rem 2rem 0.75rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  cursor: pointer;
}

.btn-reset {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  color: #64748b;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.9rem;
}

.btn-reset:hover:not(:disabled) {
  background: #e2e8f0;
  color: #1a1d29;
}

.btn-reset:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  background: #f8fafc;
  color: #cbd5e1;
}

.btn-reset svg {
  width: 18px;
  height: 18px;
}

/* Table */
.content-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  overflow: hidden;
}

.table-wrapper {
  overflow-x: auto;
}

.admin-table {
  width: 100%;
  border-collapse: collapse;
}

.admin-table th {
  text-align: left;
  padding: 1rem 1.5rem;
  background: #f8fafc;
  color: #0f172a; /* Darker */
  font-weight: 700; /* Bolder */
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 1px solid #e2e8f0;
}

.admin-table td {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  vertical-align: middle;
}

.admin-table tr:last-child td {
  border-bottom: none;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.avatar {
  width: 40px;
  height: 40px;
  background: #c9a050;
  color: white;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
}

.details {
  display: flex;
  flex-direction: column;
}

.name {
  font-weight: 600;
  color: #1a1d29;
}

.id {
  font-size: 0.75rem;
  color: #334155; /* Darker */
}

.email-text {
  color: #0f172a; /* Darker */
  font-weight: 500;
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.badge.role { background: #f1f5f9; color: #475569; }
.badge.role.super-admin { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
.badge.role.admin { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }

.badge.status.active { background: #dcfce7; color: #15803d; }
.badge.status.inactive { background: #fee2e2; color: #b91c1c; }

.meta-text {
  color: #0f172a; /* Darker */
  font-size: 0.9rem;
  font-weight: 500;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}

.action-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: 2px solid transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.action-btn::before {
  content: '';
  position: absolute;
  inset: 0;
  background: currentColor;
  opacity: 0;
  transition: opacity 0.3s ease;
  border-radius: 8px;
}

.action-btn svg {
  width: 16px;
  height: 16px;
  position: relative;
  z-index: 1;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.action-btn:hover svg {
  transform: scale(1.15);
}

.action-btn:active {
  transform: scale(0.92);
}

/* Edit Button - Modern Gold/Amber */
.action-btn.edit {
  background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
  color: #d97706;
  border-color: rgba(217, 119, 6, 0.1);
  box-shadow: 0 2px 4px rgba(217, 119, 6, 0.1);
}

.action-btn.edit:hover {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  border-color: rgba(217, 119, 6, 0.3);
  box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25);
  transform: translateY(-2px);
}

.action-btn.edit:hover::before {
  opacity: 0.08;
}

.action-btn.edit:hover svg {
  animation: wiggle 0.4s ease;
}

@keyframes wiggle {
  0%, 100% { transform: scale(1.15) rotate(0deg); }
  25% { transform: scale(1.15) rotate(-8deg); }
  75% { transform: scale(1.15) rotate(8deg); }
}

/* Delete Button - Modern Red */
.action-btn.delete {
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
  color: #dc2626;
  border-color: rgba(220, 38, 38, 0.1);
  box-shadow: 0 2px 4px rgba(220, 38, 38, 0.1);
}

.action-btn.delete:hover {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  border-color: rgba(220, 38, 38, 0.3);
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
  transform: translateY(-2px);
}

.action-btn.delete:hover::before {
  opacity: 0.08;
}

.action-btn.delete:hover svg {
  animation: shake 0.4s ease;
}

@keyframes shake {
  0%, 100% { transform: scale(1.15) translateX(0); }
  25% { transform: scale(1.15) translateX(-2px); }
  75% { transform: scale(1.15) translateX(2px); }
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
}

.page-count {
  color: #64748b;
  font-size: 0.9rem;
}

.page-controls {
  display: flex;
  gap: 0.5rem;
}

.page-controls button {
  padding: 0.5rem 1rem;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  color: #1a1d29;
}

.page-controls button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-controls button:hover:not(:disabled) {
  border-color: #c9a050;
  color: #c9a050;
}

/* Modals */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-card {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  padding: 1.5rem;
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
}

.modal-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.modal-head h3 {
  margin: 0;
  font-size: 1.25rem;
  color: #000000; /* Darker */
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #94a3b8;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.field {
  margin-bottom: 1rem;
}

.field label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600; /* Bolder */
  font-size: 0.9rem;
  color: #000000; /* Darker */
}

.field input, .field select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #94a3b8; /* Darker border */
  border-radius: 6px;
  color: #000000; /* Darker text */
  font-weight: 500;
}

.password-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.password-input-wrapper input {
  padding-right: 2.5rem;
}

.toggle-password {
  position: absolute;
  right: 0.5rem;
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.25rem;
}

.toggle-password:hover {
  color: #1a1d29;
}

.toggle-password svg {
  width: 20px;
  height: 20px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
}

.btn-text {
  background: none;
  border: none;
  padding: 0.75rem 1.5rem;
  cursor: pointer;
  font-weight: 600;
  color: #334155; /* Darker */
}

.btn-primary {
  background: #c9a050;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
}

.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Delete Modal Styles */
.modal-container {
  background: linear-gradient(135deg, #ffffff 0%, #fafafa 100%);
  border-radius: 24px;
  width: 100%;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transform: scale(0.9) translateY(20px);
  transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  box-shadow:
    0 25px 50px -12px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(201, 160, 80, 0.1);
}

.modal-backdrop .modal-container {
  transform: scale(1) translateY(0);
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

.delete-product-name {
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

/* Toast */
.toast-success {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  background: #10b981;
  color: white;
  padding: 1rem 2rem;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  animation: slideUp 0.3s ease;
  z-index: 2000;
}

@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.state-cell {
  text-align: center;
  padding: 3rem !important;
  color: #94a3b8;
}

.spinner {
  border: 3px solid #f1f5f9;
  border-top: 3px solid #c9a050;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  animation: spin 1s linear infinite;
  margin: 0 auto 0.5rem;
}

@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

@media (max-width: 768px) {
  .controls-panel {
    flex-direction: column;
    align-items: stretch;
  }
  .filters-control {
    flex-direction: column;
  }
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
