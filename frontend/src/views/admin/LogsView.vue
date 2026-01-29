<template>
  <div class="admin-logs-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Activity Logs</h1>
        <p class="page-subtitle">View admin actions, login history, and system events.</p>
      </div>
      <div class="header-actions">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search logs..."
          class="search-input"
        />
        <select v-model="filterAction" class="filter-select">
          <option value="">All Actions</option>
          <option value="login">Login</option>
          <option value="logout">Logout</option>
          <option value="create">Create</option>
          <option value="update">Update</option>
          <option value="delete">Delete</option>
          <option value="status_update">Status Update</option>
          <option value="verify">Verify</option>
          <option value="reject">Reject</option>
        </select>
        <select v-model="filterModule" class="filter-select">
          <option value="">All Modules</option>
          <option value="products">Products</option>
          <option value="orders">Orders</option>
          <option value="users">Users</option>
          <option value="categories">Categories</option>
          <option value="payments">Payments</option>
          <option value="settings">Settings</option>
          <option value="auth">Authentication</option>
        </select>
        <input type="date" v-model="filterDate" class="date-input">

        <button
          class="btn-reset"
          @click="resetFilters"
          title="Clear Filters"
          :disabled="!searchQuery && !filterAction && !filterModule && !filterDate"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
            <path d="M3 3v5h5"/>
          </svg>
          <span>Reset</span>
        </button>

        <button class="btn-export" @click="exportLogs">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
          </svg>
          Export CSV
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
            <th>Timestamp</th>
            <th>Admin</th>
            <th>Action</th>
            <th>Module</th>
            <th>Description</th>
            <th>IP Address</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="logs.length === 0">
            <td colspan="6" class="no-data">No activity logs found</td>
          </tr>
          <tr v-for="log in logs" :key="log.id" v-else>
            <td class="date">{{ formatDateTime(log.timestamp) }}</td>
            <td>{{ log.admin_name || 'System' }}</td>
            <td>
              <span class="action-badge" :class="log.action.toLowerCase().replace(' ', '-')">
                {{ log.action }}
              </span>
            </td>
            <td>
              <span class="module-badge">{{ log.module }}</span>
            </td>
            <td>{{ log.description }}</td>
            <td class="ip">{{ log.ip_address }}</td>
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
      Loading activity logs...
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { activityLogs } from '@/services/adminApi'

interface ActivityLog {
  id: number
  timestamp: string
  admin_id: number | null
  admin_name: string
  admin_email: string | null
  action: string
  module: string
  description: string
  subject_type: string | null
  subject_id: number | null
  subject_name: string | null
  old_values: Record<string, unknown> | null
  new_values: Record<string, unknown> | null
  ip_address: string
  user_agent: string | null
  url: string | null
  method: string | null
}

const filterAction = ref('')
const filterDate = ref('')
const filterModule = ref('')
const searchQuery = ref('')
const logs = ref<ActivityLog[]>([])
const isLoading = ref(false)
const error = ref<string | null>(null)
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 50,
  total: 0,
})

const loadLogs = async () => {
  isLoading.value = true
  error.value = null

  try {
    const params: Record<string, unknown> = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
    }

    if (filterAction.value) {
      params.action = filterAction.value.toLowerCase()
    }

    if (filterModule.value) {
      params.module = filterModule.value.toLowerCase()
    }

    if (filterDate.value) {
      params.date_from = filterDate.value
      params.date_to = filterDate.value
    }

    if (searchQuery.value) {
      params.search = searchQuery.value
    }

    const response = await activityLogs.list(params)

    if (response.data.success) {
      logs.value = response.data.data.data || []
      pagination.value = {
        current_page: response.data.data.current_page || 1,
        last_page: response.data.data.last_page || 1,
        per_page: response.data.data.per_page || 50,
        total: response.data.data.total || 0,
      }
    } else {
      error.value = 'Failed to load activity logs'
    }
  } catch (err: unknown) {
    console.error('Failed to load activity logs:', err)
    error.value = 'Failed to load activity logs. Please try again.'
  } finally {
    isLoading.value = false
  }
}

const resetFilters = () => {
  searchQuery.value = ''
  filterAction.value = ''
  filterModule.value = ''
  filterDate.value = ''
  pagination.value.current_page = 1
  loadLogs()
}

const formatDateTime = (timestamp: string) => {
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  }).format(new Date(timestamp))
}

const exportLogs = () => {
  // Create CSV content
  const headers = ['Timestamp', 'Admin', 'Action', 'Module', 'Description', 'IP Address']
  const rows = logs.value.map((log) => [
    formatDateTime(log.timestamp),
    log.admin_name,
    log.action,
    log.module,
    log.description,
    log.ip_address,
  ])

  const csvContent = [headers.join(','), ...rows.map((row) => row.map((cell) => `"${cell}"`).join(','))].join('\n')

  // Download CSV
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  const url = URL.createObjectURL(blob)
  link.setAttribute('href', url)
  link.setAttribute('download', `activity-logs-${new Date().toISOString().split('T')[0]}.csv`)
  link.style.visibility = 'hidden'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const changePage = (page: number) => {
  pagination.value.current_page = page
  loadLogs()
}

// Watch filters and reload
watch([filterAction, filterModule, filterDate, searchQuery], () => {
  pagination.value.current_page = 1
  loadLogs()
})

onMounted(() => {
  loadLogs()
})
</script>

<style scoped>
.admin-logs-page {
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
  transition: color 0.3s ease;
}

.page-subtitle {
  color: #374151;
  font-size: 0.95rem;
  margin: 0;
  transition: color 0.3s ease;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
}

.search-input,
.filter-select,
.date-input {
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
  background: var(--white);
  color: var(--dark);
  transition: all 0.3s ease;
}

.search-input {
  min-width: 200px;
}

.btn-reset {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: #f1f5f9;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  color: #64748b;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.2s ease;
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

.btn-export {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: var(--gold);
  color: var(--white);
  border: 2px solid var(--gold);
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-export:hover {
  background: #b08d44;
  border-color: #b08d44;
  color: var(--white);
}

.btn-export svg {
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

.data-table tbody tr:hover {
  background: #f9fafb;
}

.date {
  color: #6b7280;
  font-size: 0.9rem;
}

.ip {
  font-family: monospace;
  color: #6b7280;
  font-size: 0.85rem;
}

.action-badge {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.action-badge.login {
  background: #d1fae5;
  color: #065f46;
}

.action-badge.logout {
  background: #fee2e2;
  color: #991b1b;
}

.action-badge.create {
  background: #dbeafe;
  color: #1e40af;
}

.action-badge.update {
  background: #fef3c7;
  color: #92400e;
}

.action-badge.delete {
  background: #fee2e2;
  color: #991b1b;
}

.action-badge.status-update {
  background: #e0e7ff;
  color: #3730a3;
}

.action-badge.verify {
  background: #d1fae5;
  color: #065f46;
}

.action-badge.reject {
  background: #fee2e2;
  color: #991b1b;
}

.module-badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
  background: #f3f4f6;
  color: #6b7280;
  text-transform: capitalize;
}

.error-message {
  background: #fee2e2;
  color: #991b1b;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.loading {
  text-align: center;
  padding: 2rem;
  color: var(--gray);
}

.no-data {
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
  transition: all 0.3s ease;
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

</style>
