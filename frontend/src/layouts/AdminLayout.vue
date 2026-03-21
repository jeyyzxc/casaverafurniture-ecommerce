<template>
  <div class="admin-layout">
    <aside class="admin-sidebar" :class="{ collapsed: sidebarCollapsed, 'mobile-open': mobileMenuOpen }">
      <div class="sidebar-header">
        <div class="logo-section">
          <h2 class="logo-text">CASA VÉRA</h2>
          <span class="logo-subtitle">{{ currentAdmin.role }}</span>
        </div>
        <button class="sidebar-toggle" @click="toggleSidebar" title="Toggle Sidebar">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 12h18M3 6h18M3 18h18"/>
          </svg>
        </button>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section">
          <div class="nav-section-title">Main</div>
          <router-link to="/admin/dashboard" class="nav-item" @click="closeMobileMenu" title="Dashboard">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="7" height="7"/>
              <rect x="14" y="3" width="7" height="7"/>
              <rect x="14" y="14" width="7" height="7"/>
              <rect x="3" y="14" width="7" height="7"/>
            </svg>
            <span>Dashboard</span>
          </router-link>
        </div>

        <div class="nav-section">
          <div class="nav-section-title">Products</div>
          <router-link to="/admin/products" class="nav-item" @click="closeMobileMenu" title="All Products">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2"/>
              <path d="M3 9h18M9 3v18"/>
            </svg>
            <span>All Products</span>
          </router-link>
          <router-link to="/admin/categories" class="nav-item" @click="closeMobileMenu" title="Categories">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 7h16M4 12h16M4 17h16"/>
            </svg>
            <span>Categories</span>
          </router-link>
        </div>

        <div class="nav-section">
          <div class="nav-section-title">Orders</div>
          <router-link to="/admin/orders" class="nav-item" @click="closeMobileMenu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
              <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
            </svg>
            <span>All Orders</span>
            <span v-if="pendingOrdersCount > 0" class="nav-badge">{{ pendingOrdersCount }}</span>
          </router-link>
        </div>

        <div class="nav-section">
          <div class="nav-section-title">Payments & Shipping</div>
          <router-link to="/admin/payments" class="nav-item" @click="closeMobileMenu" title="Payments">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
              <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
            <span>Payments</span>
          </router-link>
          <router-link to="/admin/shipping" class="nav-item" @click="closeMobileMenu" title="Shipping">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 3h15v13H1zM16 8h4l3 3v5h-7V8z"/>
              <circle cx="5.5" cy="18.5" r="2.5"/>
              <circle cx="18.5" cy="18.5" r="2.5"/>
            </svg>
            <span>Shipping</span>
          </router-link>
        </div>

        <div class="nav-section">
          <div class="nav-section-title">Users</div>
          <router-link to="/admin/users" class="nav-item" @click="closeMobileMenu" title="All Users">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span>All Users</span>
          </router-link>
        </div>

        <div class="nav-section">
          <div class="nav-section-title">Inventory</div>
          <router-link to="/admin/inventory" class="nav-item" @click="closeMobileMenu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
              <line x1="3" y1="6" x2="21" y2="6"/>
              <path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
            <span>Stock Control</span>
            <span v-if="lowStockCount > 0" class="nav-badge alert">{{ lowStockCount }}</span>
          </router-link>
        </div>

        <div class="nav-section">
          <div class="nav-section-title">Marketing</div>
          <router-link to="/admin/promotions" class="nav-item" @click="closeMobileMenu" title="Promotions">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2L2 7l10 5 10-5-10-5z"/>
              <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
            <span>Promotions</span>
          </router-link>
          <router-link to="/admin/reviews" class="nav-item" @click="closeMobileMenu" title="Reviews">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
            <span>Reviews</span>
          </router-link>
        </div>

        <div class="nav-section">
          <div class="nav-section-title">Content</div>
          <router-link to="/admin/content" class="nav-item" @click="closeMobileMenu" title="CMS">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
              <line x1="9" y1="9" x2="15" y2="9"/>
              <line x1="9" y1="15" x2="15" y2="15"/>
            </svg>
            <span>CMS</span>
          </router-link>
        </div>

        <div class="nav-section">
          <div class="nav-section-title">Reports</div>
          <router-link to="/admin/reports" class="nav-item" @click="closeMobileMenu" title="Analytics">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="20" x2="18" y2="10"/>
              <line x1="12" y1="20" x2="12" y2="4"/>
              <line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
            <span>Analytics</span>
          </router-link>
        </div>

        <div class="nav-section" v-if="isSuperAdmin">
          <div class="nav-section-title">Administration</div>
          <router-link to="/admin/admins" class="nav-item" @click="closeMobileMenu" title="Admin Users">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="8.5" cy="7" r="4"/>
              <path d="M20 8v6M23 11h-6"/>
            </svg>
            <span>Admin Users</span>
          </router-link>
        </div>

        <div class="nav-section">
          <div class="nav-section-title">System</div>
          <router-link to="/admin/settings" class="nav-item" @click="closeMobileMenu" title="Settings">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="3"/>
              <path d="M12 1v6m0 6v6M5.64 5.64l4.24 4.24m4.24 4.24l4.24 4.24M1 12h6m6 0h6M5.64 18.36l4.24-4.24m4.24-4.24l4.24-4.24"/>
            </svg>
            <span>Settings</span>
          </router-link>
          <router-link to="/admin/logs" class="nav-item" @click="closeMobileMenu" title="Activity Logs">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
              <line x1="16" y1="13" x2="8" y2="13"/>
              <line x1="16" y1="17" x2="8" y2="17"/>
              <polyline points="10 9 9 9 8 9"/>
            </svg>
            <span>Activity Logs</span>
          </router-link>
        </div>
      </nav>

      <div class="sidebar-footer">
        <div class="admin-info" @click="navigateToProfile" title="My Profile">
          <div class="admin-avatar">
            <svg viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
          </div>
          <div class="admin-details">
            <div class="admin-name">{{ currentAdmin.name }}</div>
            <div class="admin-role">{{ currentAdmin.role }}</div>
          </div>
        </div>
        <button class="logout-btn" @click="handleLogout" title="Logout">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <main class="admin-main" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
      <header class="admin-header" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
        <div class="header-left">
          <button class="mobile-menu-toggle" @click="toggleMobileMenu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 12h18M3 6h18M3 18h18"/>
            </svg>
          </button>
          <div class="breadcrumbs">
            <router-link to="/admin/dashboard" class="breadcrumb-link">Admin</router-link>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">{{ currentPageTitle }}</span>
          </div>
        </div>
        <div class="header-right">
          <div class="notification-wrapper">
            <button
              class="header-icon-btn"
              @click="toggleNotificationDropdown"
              title="Notifications"
              :class="{ active: notificationDropdownOpen }"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
              </svg>
              <span v-if="notificationStore.unreadCount > 0" class="notification-badge">
                {{ notificationStore.unreadCount }}
              </span>
            </button>

            <div v-if="notificationDropdownOpen" class="notification-dropdown">
              <div class="notification-header">
                <h3>Notifications</h3>
                <div class="notification-actions">
                  <button
                    v-if="notificationStore.unreadCount > 0"
                    @click="markAllAsRead"
                    class="mark-all-read-btn"
                  >
                    Mark all as read
                  </button>
                </div>
              </div>

              <div class="notification-list">
                <div
                  v-if="notificationStore.recentNotifications.length === 0"
                  class="notification-empty"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                  </svg>
                  <p>No notifications</p>
                </div>

                <div
                  v-for="notification in notificationStore.recentNotifications"
                  :key="notification.id"
                  class="notification-item"
                  :class="{ unread: !notification.read }"
                  @click="handleNotificationClick(notification)"
                >
                  <div class="notification-icon" :class="notification.type">
                    <svg v-if="notification.icon === 'shopping-cart'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="9" cy="21" r="1"/>
                      <circle cx="20" cy="21" r="1"/>
                      <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <svg v-else-if="notification.icon === 'box'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                      <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                      <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                    <svg v-else-if="notification.icon === 'star'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                    <svg v-else-if="notification.icon === 'truck'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 3h15v13H1zM16 8h4l3 3v5h-7V8z"/>
                      <circle cx="5.5" cy="18.5" r="2.5"/>
                      <circle cx="18.5" cy="18.5" r="2.5"/>
                    </svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="12" r="10"/>
                      <line x1="12" y1="16" x2="12" y2="12"/>
                      <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                  </div>
                  <div class="notification-content">
                    <div class="notification-title">{{ notification.title }}</div>
                    <div class="notification-message">{{ notification.message }}</div>
                    <div class="notification-time">{{ notificationStore.formatTimeAgo(notification.timestamp) }}</div>
                  </div>
                  <button
                    @click.stop="removeNotification(notification.id)"
                    class="notification-remove"
                    title="Remove"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <line x1="18" y1="6" x2="6" y2="18"/>
                      <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                  </button>
                  <div v-if="!notification.read" class="notification-unread-indicator"></div>
                </div>
              </div>

              <div v-if="notificationStore.recentNotifications.length > 0" class="notification-footer">
                <router-link to="/admin/notifications" @click="closeNotificationDropdown" class="view-all-link">
                  View all notifications
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </header>

      <div class="admin-content">
      <RouterView />
      </div>
    </main>

    <div v-if="mobileMenuOpen" class="mobile-overlay" @click="closeMobileMenu"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAdminAuthStore } from '@/stores/adminAuth'
import { getAdminAccessToken } from '@/utils/tokenManager'
import { useNotificationStore } from '@/stores/notifications'
import { dashboard } from '@/services/adminApi'

const router = useRouter()
const route = useRoute()
const adminAuthStore = useAdminAuthStore()
const notificationStore = useNotificationStore()

const sidebarCollapsed = ref(false)
const mobileMenuOpen = ref(false)
const notificationDropdownOpen = ref(false)

const currentAdmin = computed(() => {
  const roleName = adminAuthStore.roleName || 'Admin'
  const formattedRole = roleName.replace(/[-_]/g, ' ').toUpperCase()

  return {
    name: adminAuthStore.adminFullName || 'Admin User',
    role: formattedRole,
    email: adminAuthStore.admin?.email || 'admin@casavera.com'
  }
})

const isSuperAdmin = computed(() => adminAuthStore.admin?.role?.slug === 'super-admin')

const pendingOrdersCount = ref(0)
const lowStockCount = ref(0)

const currentPageTitle = computed(() => {
  const titles: Record<string, string> = {
    'admin-dashboard': 'Dashboard',
    'admin-products': 'Products',
    'admin-products-add': 'Add Product',
    'admin-categories': 'Categories',
    'admin-orders': 'Orders',
    'admin-users': 'Users',
    'admin-inventory': 'Inventory',
    'admin-promotions': 'Promotions',
    'admin-reviews': 'Reviews',
    'admin-content': 'Content Management',
    'admin-reports': 'Reports & Analytics',
    'admin-settings': 'Settings',
    'admin-logs': 'Activity Logs',
    'admin-profile': 'My Profile'
  }
  return titles[route.name as string] || 'Admin Panel'
})

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

const closeMobileMenu = () => {
  mobileMenuOpen.value = false
}

const toggleNotificationDropdown = () => {
  notificationDropdownOpen.value = !notificationDropdownOpen.value
}

const closeNotificationDropdown = () => {
  notificationDropdownOpen.value = false
}

const handleNotificationClick = (notification: any) => {
  notificationStore.markAsRead(notification.id)
  if (notification.link) {
    router.push(notification.link)
    closeNotificationDropdown()
  }
}

const markAllAsRead = () => {
  notificationStore.markAllAsRead()
}

const removeNotification = (id: number) => {
  notificationStore.removeNotification(id)
}

const navigateToProfile = () => {
  router.push('/admin/profile')
}

const handleLogout = () => {
  adminAuthStore.logout()
}

const fetchQuickStats = async () => {
  try {
    const response = await dashboard.getQuickStats()
    if (response.data.success) {
      pendingOrdersCount.value = response.data.data.pending_orders || 0
      lowStockCount.value = response.data.data.low_stock_alerts || 0
    }
  } catch (error) {
    console.error('Failed to fetch quick stats:', error)
  }
}

onMounted(async () => {
  if (!adminAuthStore.isAuthenticated) {
    router.push('/admin/login')
    return
  }

  if (!adminAuthStore.admin || !getAdminAccessToken()) {
    await adminAuthStore.fetchAdmin()
  }

  fetchQuickStats()

  document.documentElement.classList.remove('dark-mode')

  const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement
    if (!target.closest('.notification-wrapper')) {
      notificationDropdownOpen.value = false
    }
  }
  document.addEventListener('click', handleClickOutside)

  onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
  })
})

onUnmounted(() => {
})
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background: #f5f7fa;
}

.admin-sidebar {
  width: 280px;
  background: linear-gradient(180deg, #1a1d29 0%, #151821 100%);
  color: #ffffff !important;
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: 0;
  height: 100vh;
  z-index: 1000;
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow-y: auto;
  overflow-x: hidden;
  border-radius: 0 24px 24px 0;
  box-shadow: 4px 0 20px rgba(0, 0, 0, 0.3);
}

.admin-sidebar.collapsed {
  width: 70px;
}

.admin-sidebar.collapsed .logo-section {
  opacity: 0;
  visibility: hidden;
  width: 0;
  height: 0;
  overflow: hidden;
  transition: opacity 0.2s ease, visibility 0.2s ease;
}

.admin-sidebar.collapsed .sidebar-header {
  justify-content: center;
  padding: 1rem 0;
}

.admin-sidebar.collapsed .sidebar-toggle {
  margin: 0 auto;
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
}

.admin-sidebar.collapsed .sidebar-toggle svg {
  width: 22px;
  height: 22px;
}

.admin-sidebar.collapsed .nav-section-title {
  opacity: 0;
  visibility: hidden;
  height: 0;
  padding: 0;
  margin: 0;
  overflow: hidden;
  transition: opacity 0.2s ease, visibility 0.2s ease, height 0.2s ease, padding 0.2s ease, margin 0.2s ease;
}

.admin-sidebar.collapsed .nav-item {
  justify-content: center;
  padding: 0.75rem 0;
  margin: 0.25rem auto;
  width: 50px;
  border-radius: 12px;
}

.admin-sidebar.collapsed .nav-item span,
.admin-sidebar.collapsed .nav-badge {
  opacity: 0;
  visibility: hidden;
  width: 0;
  height: 0;
  overflow: hidden;
  position: absolute;
  transition: opacity 0.2s ease, visibility 0.2s ease;
}

.admin-sidebar.collapsed .nav-item svg {
  margin: 0 auto;
  width: 22px;
  height: 22px;
}

.admin-sidebar.collapsed .nav-section {
  margin-bottom: 0.25rem;
}

.admin-sidebar.collapsed .sidebar-footer {
  padding: 0.75rem 0.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.admin-sidebar.collapsed .admin-info {
  justify-content: center;
  padding: 0;
  margin: 0;
  width: 50px;
  height: 50px;
  border-radius: 12px;
  background: transparent;
}

.admin-sidebar.collapsed .admin-avatar {
  width: 40px;
  height: 40px;
}

.admin-sidebar.collapsed .admin-avatar svg {
  width: 22px;
  height: 22px;
}

.admin-sidebar.collapsed .admin-details {
  opacity: 0;
  visibility: hidden;
  width: 0;
  height: 0;
  overflow: hidden;
  position: absolute;
}

.admin-sidebar.collapsed .logout-btn {
  width: 50px;
  height: 50px;
  padding: 0;
  border-radius: 12px;
  justify-content: center;
}

.admin-sidebar.collapsed .logout-btn svg {
  width: 22px;
  height: 22px;
  margin: 0;
}

.admin-sidebar.collapsed .logout-btn span {
  opacity: 0;
  visibility: hidden;
  width: 0;
  height: 0;
  overflow: hidden;
  position: absolute;
}

.admin-sidebar.collapsed .nav-item::before {
  display: none;
}

.admin-sidebar.collapsed .nav-item:hover {
  transform: none;
  background: rgba(201, 160, 80, 0.15);
}

.admin-sidebar.collapsed .nav-item.router-link-active {
  background: rgba(201, 160, 80, 0.2);
}

.admin-sidebar.collapsed .admin-info:hover {
  transform: none;
  background: rgba(201, 160, 80, 0.15);
}

.admin-sidebar.collapsed .logout-btn:hover {
  transform: none;
}

.admin-sidebar,
.admin-sidebar *,
.admin-sidebar span,
.admin-sidebar div,
.admin-sidebar p,
.admin-sidebar h1,
.admin-sidebar h2,
.admin-sidebar h3,
.admin-sidebar h4,
.admin-sidebar h5,
.admin-sidebar h6 {
  color: #ffffff !important;
}

.admin-sidebar .logo-text {
  color: transparent !important;
  -webkit-text-fill-color: transparent !important;
}

.admin-sidebar .nav-item:hover svg,
.admin-sidebar .nav-item.router-link-active svg {
  color: #c9a050 !important;
}

.admin-sidebar .nav-badge {
  color: #ffffff !important;
}

.admin-sidebar::-webkit-scrollbar {
  width: 6px;
}

.admin-sidebar::-webkit-scrollbar-track {
  background: transparent;
}

.admin-sidebar::-webkit-scrollbar-thumb {
  background: rgba(201, 160, 80, 0.3);
  border-radius: 10px;
  transition: background 0.3s ease;
}

.admin-sidebar::-webkit-scrollbar-thumb:hover {
  background: rgba(201, 160, 80, 0.5);
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  backdrop-filter: blur(10px);
  position: sticky;
  top: 0;
  z-index: 10;
  background: rgba(26, 29, 41, 0.95);
}

.logo-section {
  display: flex;
  flex-direction: column;
}

.logo-text {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  font-weight: 700;
  background: linear-gradient(135deg, #c9a050 0%, #e6c866 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
  line-height: 1.2;
  text-shadow: 0 0 20px rgba(201, 160, 80, 0.3);
  transition: all 0.3s ease;
}

.logo-subtitle {
  font-size: 0.75rem;
  color: #ffffff !important;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-top: 0.25rem;
  transition: color 0.3s ease;
}

.sidebar-toggle {
  background: rgba(201, 160, 80, 0.1);
  border: 1px solid rgba(201, 160, 80, 0.2);
  color: #c9a050;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 10px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
}

.sidebar-toggle:hover {
  background: rgba(201, 160, 80, 0.2);
  border-color: rgba(201, 160, 80, 0.4);
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(201, 160, 80, 0.2);
}

.sidebar-toggle:active {
  transform: scale(0.95);
}

.sidebar-toggle svg {
  width: 20px;
  height: 20px;
  transition: transform 0.3s ease;
}

.sidebar-toggle:hover svg {
  transform: rotate(90deg);
}

.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
  overflow-y: auto;
  overflow-x: hidden;
}

.sidebar-nav {
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.sidebar-nav::-webkit-scrollbar {
  width: 0;
  background: transparent;
}

.nav-section {
  margin-bottom: 1.5rem;
}

.nav-section-title {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: #ffffff !important;
  padding: 0.5rem 1.5rem;
  margin-bottom: 0.5rem;
  font-weight: 700;
  transition: color 0.3s ease;
  position: relative;
}

.nav-section-title::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 1.5rem;
  right: 1.5rem;
  height: 1px;
  background: linear-gradient(90deg, transparent 0%, rgba(201, 160, 80, 0.3) 50%, transparent 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.nav-section:hover .nav-section-title {
  color: #ffffff !important;
  opacity: 0.9;
}

.nav-section:hover .nav-section-title::after {
  opacity: 1;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.5rem;
  color: #ffffff !important;
  transition: padding 0.3s ease, justify-content 0.3s ease;
  text-decoration: none;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  border-radius: 0 16px 16px 0;
  margin: 0.25rem 0.75rem 0.25rem 0;
  font-weight: 500;
  cursor: pointer;
  outline: none;
}

.nav-item span {
  color: #ffffff !important;
}

.nav-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 0;
  background: linear-gradient(180deg, #c9a050 0%, #b8860b 100%);
  border-radius: 0 3px 3px 0;
  transition: height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.nav-item svg {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  transition: all 0.3s ease;
  display: block;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #ffffff !important;
  transform: translateX(4px);
  box-shadow: -4px 0 12px rgba(0, 0, 0, 0.1);
}

.nav-item:hover span {
  color: #ffffff !important;
}

.nav-item:hover::before {
  height: 60%;
}

.nav-item:hover svg {
  transform: scale(1.1);
  color: #c9a050;
}

.nav-item:focus-visible {
  outline: 2px solid rgba(201, 160, 80, 0.5);
  outline-offset: -2px;
}

.nav-item.router-link-active {
  background: linear-gradient(90deg, rgba(201, 160, 80, 0.2) 0%, rgba(201, 160, 80, 0.1) 100%);
  color: #ffffff !important;
  font-weight: 600;
  box-shadow: -4px 0 16px rgba(201, 160, 80, 0.2);
}

.nav-item.router-link-active span {
  color: #ffffff !important;
}

.nav-item.router-link-active::before {
  height: 70%;
  box-shadow: 0 0 8px rgba(201, 160, 80, 0.6);
}

.nav-item.router-link-active svg {
  color: #c9a050;
  transform: scale(1.1);
  filter: drop-shadow(0 0 4px rgba(201, 160, 80, 0.4));
}

.nav-badge {
  margin-left: auto;
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  color: white;
  font-size: 0.7rem;
  padding: 0.25rem 0.6rem;
  border-radius: 12px;
  font-weight: 700;
  min-width: 22px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
  animation: pulse 2s infinite;
}

.nav-badge.alert {
  background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
  box-shadow: 0 2px 8px rgba(255, 152, 0, 0.3);
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(1.05);
  }
}

.sidebar-footer {
  padding: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  position: sticky;
  bottom: 0;
  background: rgba(26, 29, 41, 0.95);
  border-radius: 0 0 24px 0;
}

.admin-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
  padding: 0.75rem;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.03);
  transition: all 0.3s ease;
  cursor: pointer;
}

.admin-info:hover {
  background: rgba(255, 255, 255, 0.05);
  transform: translateX(2px);
}

.admin-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(201, 160, 80, 0.3) 0%, rgba(201, 160, 80, 0.2) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #c9a050;
  border: 2px solid rgba(201, 160, 80, 0.3);
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(201, 160, 80, 0.2);
}

.admin-info:hover .admin-avatar {
  border-color: rgba(201, 160, 80, 0.5);
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(201, 160, 80, 0.3);
}

.admin-avatar svg {
  width: 24px;
  height: 24px;
  transition: transform 0.3s ease;
}

.admin-info:hover .admin-avatar svg {
  transform: rotate(5deg);
}

.admin-details {
  flex: 1;
}

.admin-name {
  font-weight: 600;
  color: #ffffff !important;
  font-size: 0.9rem;
  transition: color 0.3s ease;
}

.admin-info:hover .admin-name {
  color: #ffffff !important;
}

.admin-role {
  font-size: 0.75rem;
  color: #ffffff !important;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: color 0.3s ease;
  opacity: 0.9;
}

.admin-info:hover .admin-role {
  color: #ffffff !important;
  opacity: 0.95;
}

.logout-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 0.875rem;
  background: linear-gradient(135deg, rgba(220, 53, 69, 0.15) 0%, rgba(220, 53, 69, 0.1) 100%);
  border: 1px solid rgba(220, 53, 69, 0.3);
  border-radius: 12px;
  color: #ffffff !important;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  font-weight: 600;
  font-size: 0.9rem;
  outline: none;
  position: relative;
  overflow: hidden;
}

.logout-btn span {
  color: #ffffff !important;
}

.logout-btn::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  border-radius: 50%;
  background: rgba(220, 53, 69, 0.2);
  transform: translate(-50%, -50%);
  transition: width 0.6s ease, height 0.6s ease;
}

.logout-btn:hover {
  background: linear-gradient(135deg, rgba(220, 53, 69, 0.25) 0%, rgba(220, 53, 69, 0.15) 100%);
  border-color: rgba(220, 53, 69, 0.5);
  color: #ffffff !important;
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(220, 53, 69, 0.3);
}

.logout-btn:hover span {
  color: #ffffff !important;
}

.logout-btn:hover::before {
  width: 300px;
  height: 300px;
}

.logout-btn:active {
  transform: translateY(0);
}

.logout-btn:focus-visible {
  outline: 2px solid rgba(220, 53, 69, 0.5);
  outline-offset: 2px;
}

.logout-btn svg {
  width: 18px;
  height: 18px;
  transition: transform 0.3s ease;
  position: relative;
  z-index: 1;
}

.logout-btn:hover svg {
  transform: translateX(2px);
}

.logout-btn span {
  position: relative;
  z-index: 1;
}

.admin-main {
  flex: 1;
  margin-left: 280px;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.admin-layout:has(.admin-sidebar.collapsed) .admin-main,
.admin-main.sidebar-collapsed {
  margin-left: 70px;
}

.admin-header {
  background: #ffffff;
  border-bottom: 1px solid #e5e7eb;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: fixed;
  top: 0;
  right: 0;
  left: 280px;
  z-index: 500;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.admin-layout:has(.admin-sidebar.collapsed) .admin-header,
.admin-header.sidebar-collapsed {
  left: 70px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.mobile-menu-toggle {
  display: none;
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  cursor: pointer;
  padding: 0.625rem;
  color: #374151;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  outline: none;
  width: 40px;
  height: 40px;
}


.mobile-menu-toggle svg {
  color: inherit;
}

.mobile-menu-toggle:hover {
  background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
  color: #c9a050;
  transform: scale(1.05);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.mobile-menu-toggle:active {
  transform: scale(0.95);
}

.mobile-menu-toggle:focus-visible {
  outline: 2px solid rgba(201, 160, 80, 0.5);
  outline-offset: 2px;
}

.mobile-menu-toggle svg {
  width: 20px;
  height: 20px;
  transition: transform 0.3s ease;
}

.mobile-menu-toggle:hover svg {
  transform: rotate(90deg);
}

.breadcrumbs {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
}

.breadcrumb-link {
  color: #6b7280;
  text-decoration: none;
  transition: color 0.2s;
}

.breadcrumb-link:hover {
  color: #c9a050;
}

.breadcrumb-separator {
  color: #d1d5db;
  transition: color 0.3s ease;
}

.breadcrumb-current {
  color: #1f2937;
  font-weight: 600;
  transition: color 0.3s ease;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.header-icon-btn {
  position: relative;
  width: 44px;
  height: 44px;
  border-radius: 12px;
  border: none;
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  color: #374151;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  outline: none;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.header-icon-btn svg {
  color: inherit;
}

.header-icon-btn::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 12px;
  background: linear-gradient(135deg, #c9a050 0%, #b8860b 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.header-icon-btn:hover {
  background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
  color: #c9a050;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(201, 160, 80, 0.2);
}

.header-icon-btn:hover::before {
  opacity: 0.1;
}

.header-icon-btn:active {
  transform: translateY(0);
}

.header-icon-btn:focus-visible {
  outline: 2px solid rgba(201, 160, 80, 0.5);
  outline-offset: 2px;
}

.header-icon-btn svg {
  width: 20px;
  height: 20px;
  position: relative;
  z-index: 1;
  transition: transform 0.3s ease;
}

.header-icon-btn:hover svg {
  transform: scale(1.1);
}

.notification-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  color: #ffffff !important;
  font-size: 0.7rem;
  padding: 0.2rem 0.4rem;
  border-radius: 10px;
  font-weight: 700;
  min-width: 20px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
  animation: pulse 2s infinite;
  z-index: 1;
}


.notification-wrapper {
  position: relative;
}

.header-icon-btn.active {
  background: linear-gradient(135deg, #c9a050 0%, #b8860b 100%);
  color: #ffffff;
}

.header-icon-btn.active svg {
  color: #ffffff;
}

.notification-dropdown {
  position: absolute;
  top: calc(100% + 0.75rem);
  right: 0;
  width: 380px;
  max-height: 500px;
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15),
              0 0 0 1px rgba(0, 0, 0, 0.05);
  z-index: 1000;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.notification-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f9fafb;
}

.notification-header h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a1d29;
}

.notification-actions {
  display: flex;
  gap: 0.5rem;
}

.mark-all-read-btn {
  background: none;
  border: none;
  color: #c9a050;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  transition: all 0.2s;
}

.mark-all-read-btn:hover {
  background: rgba(201, 160, 80, 0.1);
}

.notification-list {
  flex: 1;
  overflow-y: auto;
  max-height: 400px;
}

.notification-empty {
  padding: 3rem 2rem;
  text-align: center;
  color: #6b7280;
}

.notification-empty svg {
  width: 48px;
  height: 48px;
  margin: 0 auto 1rem;
  opacity: 0.5;
}

.notification-empty p {
  margin: 0;
  font-size: 0.9rem;
}

.notification-item {
  display: flex;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #f3f4f6;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
  background: #ffffff;
}

.notification-item:hover {
  background: #f9fafb;
}

.notification-item.unread {
  background: #f0f9ff;
  border-left: 3px solid #c9a050;
}

.notification-item.unread:hover {
  background: #e0f2fe;
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  background: #f3f4f6;
  color: #6b7280;
}

.notification-icon svg {
  width: 20px;
  height: 20px;
}

.notification-icon.order {
  background: #dbeafe;
  color: #1e40af;
}

.notification-icon.inventory {
  background: #fef3c7;
  color: #92400e;
}

.notification-icon.review {
  background: #fce7f3;
  color: #9f1239;
}

.notification-icon.system {
  background: #e0e7ff;
  color: #3730a3;
}

.notification-icon.promotion {
  background: #d1fae5;
  color: #065f46;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-title {
  font-weight: 600;
  font-size: 0.9rem;
  color: #1a1d29;
  margin-bottom: 0.25rem;
}

.notification-message {
  font-size: 0.85rem;
  color: #6b7280;
  margin-bottom: 0.5rem;
  line-height: 1.4;
}

.notification-time {
  font-size: 0.75rem;
  color: #9ca3af;
}

.notification-remove {
  width: 24px;
  height: 24px;
  border: none;
  background: none;
  color: #9ca3af;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: all 0.2s;
  flex-shrink: 0;
  opacity: 0;
}

.notification-item:hover .notification-remove {
  opacity: 1;
}

.notification-remove:hover {
  background: #fee2e2;
  color: #dc2626;
}

.notification-remove svg {
  width: 14px;
  height: 14px;
}

.notification-unread-indicator {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #c9a050;
}

.notification-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
  text-align: center;
}

.view-all-link {
  color: #c9a050;
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 600;
  transition: color 0.2s;
}

.view-all-link:hover {
  color: #b8860b;
  text-decoration: underline;
}

.admin-content {
  flex: 1;
  padding: 2rem;
  overflow-y: auto;
  overflow-x: hidden;
  scrollbar-width: thin;
  scrollbar-color: rgba(201, 160, 80, 0.2) transparent;
}

.admin-content::-webkit-scrollbar {
  width: 8px;
}

.admin-content::-webkit-scrollbar-track {
  background: transparent;
}

.admin-content::-webkit-scrollbar-thumb {
  background: rgba(201, 160, 80, 0.2);
  border-radius: 10px;
  transition: background 0.3s ease;
}

.admin-content::-webkit-scrollbar-thumb:hover {
  background: rgba(201, 160, 80, 0.4);
}


.mobile-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
}


@media (max-width: 1024px) {
  .admin-sidebar {
    transform: translateX(-100%);
    border-radius: 0 0 24px 0;
  }

  .admin-sidebar.mobile-open {
    transform: translateX(0);
    box-shadow: 4px 0 30px rgba(0, 0, 0, 0.5);
  }

  .admin-sidebar.collapsed {
    width: 70px;
  }

  .admin-main {
    margin-left: 0;
  }

  .admin-main.sidebar-collapsed {
    margin-left: 70px;
  }

  .admin-header {
    left: 0;
  }

  .admin-header.sidebar-collapsed {
    left: 70px;
  }

  .mobile-menu-toggle {
    display: flex;
  }

  .mobile-overlay {
    display: block;
    backdrop-filter: blur(4px);
    transition: opacity 0.3s ease;
  }
}

@media (max-width: 768px) {
  .admin-content {
    padding: 1rem;
    padding-top: calc(1rem + 65px);
  }

  .admin-header {
    padding: 1rem;
    left: 0;
  }
}

</style>
