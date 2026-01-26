<template>
  <div class="admin-users-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle">Manage customer accounts, orders, and permissions.</p>
      </div>
      <div class="header-actions">
        <button class="btn-secondary" @click="exportUsers">Export CSV</button>
      </div>
    </div>

    <div class="filters-bar">
      <div class="search-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/>
          <path d="m21 21-4.35-4.35"/>
        </svg>
        <input v-model="searchQuery" placeholder="Search by name or email..." class="search-input">
      </div>
      <select v-model="selectedStatus" class="filter-select">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
        <option value="banned">Banned</option>
      </select>
    </div>

    <div class="table-card">
      <table class="data-table">
        <thead>
          <tr>
            <th>User</th>
            <th>Email</th>
            <th>Orders</th>
            <th>Total Spent</th>
            <th>Status</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading && users.length === 0">
            <td colspan="7" style="text-align: center; padding: 3rem;">
              <div class="spinner"></div>
              <p>Loading users...</p>
            </td>
          </tr>
          <tr v-else-if="!isLoading && filteredUsers.length === 0">
            <td colspan="7" style="text-align: center; padding: 3rem; color: #6b7280;">
              No users found
            </td>
          </tr>
          <tr v-else v-for="user in filteredUsers" :key="user.id">
            <td>
              <div class="user-cell">
                <div class="user-avatar">{{ user.name.charAt(0) }}</div>
                <div>
                  <div class="user-name">{{ user.name }}</div>
                  <div class="user-id">ID: {{ user.id }}</div>
                </div>
              </div>
            </td>
            <td>{{ user.email }}</td>
            <td>{{ user.orderCount }}</td>
            <td class="amount">₱{{ formatPrice(user.totalSpent) }}</td>
            <td>
              <span class="status-badge" :class="user.status.toLowerCase()">
                {{ user.status === 'active' ? 'Active' : user.status === 'inactive' ? 'Inactive' : 'Banned' }}
              </span>
            </td>
            <td class="date">{{ formatDate(user.joinedDate) }}</td>
            <td>
              <div class="action-buttons">
                <button class="action-btn" @click="viewUser(user.id)" title="View Orders">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                </button>
                <button class="action-btn" @click="toggleUserStatus(user)" :title="user.status === 'active' ? 'Deactivate' : 'Activate'">
                  <svg v-if="user.status === 'active'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                  </svg>
                  <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                </button>
                <button v-if="user.status === 'banned'" class="action-btn success" @click="unbanUser(user)" title="Unban User">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                </button>
                <button v-else class="action-btn danger" @click="openBanModal(user)" title="Ban User">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                  </svg>
                </button>
                <button class="action-btn danger" @click="openDeleteModal(user)" title="Delete User">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    <line x1="10" y1="11" x2="10" y2="17"/>
                    <line x1="14" y1="11" x2="14" y2="17"/>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Ban User Modal -->
    <Teleport to="body">
      <div v-if="showBanModal" class="modal-overlay" @click="closeBanModal">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h2>Ban User</h2>
            <button class="close-btn" @click="closeBanModal">×</button>
          </div>
          <form @submit.prevent="confirmBan" class="modal-body">
            <div class="form-group">
              <label>User</label>
              <input :value="banningUser?.name" type="text" disabled>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input :value="banningUser?.email" type="text" disabled>
            </div>
            <div class="form-group">
              <label>Reason for Ban *</label>
              <textarea v-model="banReason" rows="4" placeholder="Enter reason for banning this user..." required></textarea>
            </div>
            <div class="form-actions">
              <button type="button" class="btn-secondary" @click="closeBanModal" :disabled="isBanning">Cancel</button>
              <button type="submit" class="btn-danger" :disabled="isBanning || !banReason.trim()">
                {{ isBanning ? 'Banning...' : 'Ban User' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Delete User Confirmation Modal -->
    <Teleport to="body">
      <div v-if="showDeleteModal" class="modal-overlay delete-modal-overlay" @click.self="closeDeleteModal">
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
            <h2 class="delete-title">Delete User</h2>
            <p class="delete-message">
              Are you sure you want to delete
              <strong class="delete-item-name">{{ deletingUser?.name || 'this user' }}</strong>?
            </p>
            <p class="delete-warning">
              This action cannot be undone. All user data, orders, and related information will be permanently removed.
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
                {{ isDeleting ? 'Deleting...' : 'Delete User' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { Teleport } from 'vue'
import { useRouter } from 'vue-router'
import { users as usersApi } from '@/services/adminApi'
import { useNotification } from '@/composables/useNotification'

const router = useRouter()
const { success, error: showError, info } = useNotification()

interface User {
  id: number
  first_name: string
  last_name: string
  name: string
  email: string
  status: 'active' | 'inactive' | 'banned'
  order_count?: number
  orderCount: number
  total_spent?: number
  totalSpent: number
  created_at: string | Date
  joinedDate: Date
}

const searchQuery = ref('')
const selectedStatus = ref('')
const users = ref<User[]>([])
const isLoading = ref(false)
const currentPage = ref(1)
const totalUsers = ref(0)
const error = ref<string | null>(null)

// Ban Modal State
const showBanModal = ref(false)
const banningUser = ref<User | null>(null)
const banReason = ref('')
const isBanning = ref(false)

// Delete Modal State
const showDeleteModal = ref(false)
const deletingUser = ref<User | null>(null)
const isDeleting = ref(false)

// Load users from API
const loadUsers = async () => {
  isLoading.value = true
  error.value = null
  try {
    const params: any = {
      page: currentPage.value,
      per_page: 20,
    }

    if (searchQuery.value) {
      params.search = searchQuery.value
    }

    if (selectedStatus.value) {
      params.status = selectedStatus.value
    }

    const response = await usersApi.list(params)
    
    if (response.data.success) {
      const data = response.data.data
      // Handle both paginated and direct array responses
      const usersData = data.data || data || []
      
      users.value = usersData.map((u: any) => ({
        id: u.id,
        first_name: u.first_name || '',
        last_name: u.last_name || '',
        name: `${u.first_name || ''} ${u.last_name || ''}`.trim() || 'N/A',
        email: u.email || '',
        status: (u.status || 'active').toLowerCase(),
        order_count: u.order_count || 0,
        orderCount: u.order_count || 0,
        total_spent: parseFloat(u.total_spent || 0),
        totalSpent: parseFloat(u.total_spent || 0),
        created_at: u.created_at,
        joinedDate: new Date(u.created_at),
      }))
      totalUsers.value = data.total || usersData.length || 0
    } else {
      throw new Error(response.data.message || 'Failed to load users')
    }
  } catch (err: any) {
    console.error('Failed to load users:', err)
    error.value = err.response?.data?.message || err.message || 'Failed to load users. Please try again.'
    showError('Failed to Load Users', error.value)
    users.value = []
  } finally {
    isLoading.value = false
  }
}

// Users are already filtered by API, so we use them directly
const filteredUsers = computed(() => users.value)

const formatPrice = (price: number) => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatDate = (date: Date | string) => {
  const d = typeof date === 'string' ? new Date(date) : date
  return new Intl.DateTimeFormat('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric'
  }).format(d)
}

const viewUser = (id: number) => {
  // Navigate to orders page filtered by user, or show user details
  // For now, navigate to orders page - can be enhanced later with user detail page
  router.push({ path: '/admin/orders', query: { user_id: id } })
}

const toggleUserStatus = async (user: User) => {
  const originalStatus = user.status
  
  // Optimistically update UI
  user.status = user.status === 'active' ? 'inactive' : 'active'
  
  try {
    const newStatus = user.status
    const response = await usersApi.update(user.id, { status: newStatus })
    
    if (response.data.success) {
      success(
        'Status Updated',
        `User status has been changed to ${newStatus}.`
      )
      await loadUsers()
    } else {
      throw new Error(response.data.message || 'Failed to update user status')
    }
  } catch (error: any) {
    console.error('Failed to update user status:', error)
    // Revert on error
    user.status = originalStatus
    showError(
      'Failed to Update Status',
      error.response?.data?.message || error.message || 'Failed to update user status. Please try again.'
    )
  }
}

const openBanModal = (user: User) => {
  banningUser.value = user
  banReason.value = ''
  showBanModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeBanModal = () => {
  if (isBanning.value) return
  showBanModal.value = false
  banningUser.value = null
  banReason.value = ''
  document.body.style.overflow = ''
}

const confirmBan = async () => {
  if (!banningUser.value || !banReason.value.trim()) return

  isBanning.value = true

  try {
    const response = await usersApi.ban(banningUser.value.id, banReason.value.trim())
    
    if (response.data.success) {
      success(
        'User Banned',
        `User "${banningUser.value.name}" has been banned successfully.`
      )
      closeBanModal()
      await loadUsers()
    } else {
      throw new Error(response.data.message || 'Failed to ban user')
    }
  } catch (error: any) {
    console.error('Failed to ban user:', error)
    showError(
      'Failed to Ban User',
      error.response?.data?.message || error.message || 'Failed to ban user. Please try again.'
    )
  } finally {
    isBanning.value = false
  }
}

const unbanUser = async (user: User) => {
  try {
    const response = await usersApi.unban(user.id)
    
    if (response.data.success) {
      success(
        'User Unbanned',
        `User "${user.name}" has been unbanned successfully.`
      )
      await loadUsers()
    } else {
      throw new Error(response.data.message || 'Failed to unban user')
    }
  } catch (error: any) {
    console.error('Failed to unban user:', error)
    showError(
      'Failed to Unban User',
      error.response?.data?.message || error.message || 'Failed to unban user. Please try again.'
    )
  }
}

const openDeleteModal = (user: User) => {
  deletingUser.value = user
  showDeleteModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeDeleteModal = () => {
  if (isDeleting.value) return
  showDeleteModal.value = false
  deletingUser.value = null
  document.body.style.overflow = ''
}

const confirmDelete = async () => {
  if (!deletingUser.value) {
    closeDeleteModal()
    return
  }

  isDeleting.value = true

  try {
    const response = await usersApi.delete(deletingUser.value.id)
    
    if (response.data.success) {
      success(
        'User Deleted',
        `User "${deletingUser.value.name}" has been deleted successfully.`
      )
      closeDeleteModal()
      await loadUsers()
    } else {
      throw new Error(response.data.message || 'Failed to delete user')
    }
  } catch (error: any) {
    console.error('Failed to delete user:', error)
    showError(
      'Failed to Delete User',
      error.response?.data?.message || error.message || 'Failed to delete user. Please try again.'
    )
  } finally {
    isDeleting.value = false
  }
}

const exportUsers = () => {
  try {
    if (filteredUsers.value.length === 0) {
      showError('Export Failed', 'No users available to export.')
      return
    }

    // Generate CSV content
    const headers = ['ID', 'Name', 'Email', 'Status', 'Orders', 'Total Spent', 'Joined Date']
    const rows = filteredUsers.value.map(u => [
      u.id,
      u.name,
      u.email,
      u.status,
      u.orderCount,
      u.totalSpent,
      formatDate(u.joinedDate)
    ])
    
    const csvContent = [
      headers.join(','),
      ...rows.map(row => row.map(cell => {
        const value = String(cell).replace(/"/g, '""')
        return `"${value}"`
      }).join(','))
    ].join('\n')
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    link.setAttribute('href', url)
    link.setAttribute('download', `users_export_${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)

    success('Export Successful', `Users exported successfully (${filteredUsers.value.length} users).`)
  } catch (error: any) {
    console.error('Export failed:', error)
    showError('Export Failed', error.message || 'Failed to export users. Please try again.')
  }
}

// Watch for filter changes and reload users
watch([searchQuery, selectedStatus], () => {
  currentPage.value = 1
  loadUsers()
})

watch(currentPage, () => {
  loadUsers()
})

onMounted(() => {
  loadUsers()
})
</script>

<style scoped>
.admin-users-page {
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

.btn-secondary:hover {
  background: #e5e7eb;
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

.data-table tbody tr:hover {
  background: #f9fafb;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--gold);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1rem;
}

.user-name {
  font-weight: 600;
  color: var(--dark);
}

.user-id {
  font-size: 0.75rem;
  color: #6b7280;
}

.amount {
  font-weight: 600;
  color: var(--dark);
}

.date {
  color: #6b7280;
  font-size: 0.9rem;
}

.status-badge {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-badge.active {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.inactive {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.banned {
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

.action-btn.danger:hover {
  background: #fee2e2;
  color: #ef4444;
}

.action-btn svg {
  width: 18px;
  height: 18px;
}

.action-btn.success {
  background: #d1fae5;
  color: #059669;
}

.action-btn.success:hover {
  background: #a7f3d0;
  color: #047857;
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
  transition: all 0.3s ease;
}

.btn-small:hover {
  background: #b8860b;
  transform: translateY(-1px);
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
  max-width: 500px;
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
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #000000;
  background: #ffffff;
}

.form-group input:disabled {
  background: #f3f4f6;
  color: #000000;
}

.form-group textarea::placeholder {
  color: #9ca3af;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
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
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-danger:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Delete Modal Styles (same as other pages) */
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
  background: #fee2e2;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
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
}

.delete-btn-confirm:hover:not(:disabled) {
  background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
}

.delete-btn-confirm:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.delete-btn-confirm svg {
  width: 18px;
  height: 18px;
}
</style>
