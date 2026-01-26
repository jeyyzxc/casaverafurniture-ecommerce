import { createRouter, createWebHistory } from 'vue-router'
import ClientLayout from '../layouts/ClientLayout.vue'
import AdminLayout from '../layouts/AdminLayout.vue'
import HomeView from '../views/client/HomeView.vue'
import ProductsView from '../views/client/ProductsView.vue'
import AboutView from '../views/client/AboutView.vue'
import ContactView from '../views/client/ContactView.vue'
import TermsView from '../views/client/TermsView.vue'
import PrivacyView from '../views/client/PrivacyView.vue'
import CartView from '../views/client/CartView.vue'
import CheckoutView from '../views/client/CheckoutView.vue'
import OrdersView from '../views/client/OrdersView.vue'
import OrderDetailView from '../views/client/OrderDetailView.vue'
import LoginView from '../views/admin/LoginView.vue'
import DashboardView from '../views/admin/DashboardView.vue'
import AdminProductsView from '../views/admin/ProductsView.vue'
import AdminOrdersView from '../views/admin/OrdersView.vue'

// Lazy load admin views for better performance
const AdminCategoriesView = () => import('../views/admin/CategoriesView.vue')
const AdminUsersView = () => import('../views/admin/UsersView.vue')
const AdminInventoryView = () => import('../views/admin/InventoryView.vue')
const AdminPromotionsView = () => import('../views/admin/PromotionsView.vue')
const AdminReviewsView = () => import('../views/admin/ReviewsView.vue')
const AdminCMSView = () => import('../views/admin/CMSView.vue')
const AdminReportsView = () => import('../views/admin/ReportsView.vue')
const AdminSettingsView = () => import('../views/admin/SettingsView.vue')
const AdminLogsView = () => import('../views/admin/LogsView.vue')
const AdminAdminsView = () => import('../views/admin/AdminsView.vue')
const AdminShippingView = () => import('../views/admin/ShippingView.vue')
const AdminPaymentsView = () => import('../views/admin/PaymentsView.vue')
const AdminProfileView = () => import('../views/admin/ProfileView.vue')

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // CLIENT ROUTES
    {
      path: '/',
      component: ClientLayout,
      children: [
        {
          path: '',
          name: 'home',
          component: HomeView
        },
        {
          path: 'products',
          name: 'products',
          component: ProductsView
        },
        {
          path: 'products/:id',
          name: 'product-detail',
          component: ProductsView // TODO: Create ProductDetailView
        },
        {
          path: 'collection',
          redirect: '/products'
        },
        {
          path: 'about',
          name: 'about',
          component: AboutView
        },
        {
          path: 'contact',
          name: 'contact',
          component: ContactView
        },
        {
          path: 'terms',
          name: 'terms',
          component: TermsView
        },
        {
          path: 'privacy',
          name: 'privacy',
          component: PrivacyView
        },
        {
          path: 'cart',
          name: 'cart',
          component: CartView
        },
        {
          path: 'checkout',
          name: 'checkout',
          component: CheckoutView,
          meta: { requiresAuth: true }
        },
        {
          path: 'orders',
          name: 'orders',
          component: OrdersView,
          meta: { requiresAuth: true }
        },
        {
          path: 'orders/:orderNumber',
          name: 'order-detail',
          component: OrderDetailView,
          meta: { requiresAuth: true }
        },
        {
          path: 'profile',
          name: 'profile',
          component: () => import('../views/client/ProfileView.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: 'auth/google/callback',
          name: 'google-auth-callback',
          component: () => import('../views/client/GoogleAuthCallback.vue')
        }
      ]
    },
    // ADMIN ROUTES
    {
      path: '/admin/login',
      name: 'admin-login',
      component: LoginView
    },
    {
      path: '/admin',
      component: AdminLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          redirect: '/admin/dashboard'
        },
        {
          path: 'dashboard',
          name: 'admin-dashboard',
          component: DashboardView
        },
        {
          path: 'products',
          name: 'admin-products',
          component: AdminProductsView
        },
        {
          path: 'products/add',
          name: 'admin-products-add',
          component: AdminProductsView
        },
        {
          path: 'products/:id',
          name: 'admin-product-detail',
          component: AdminProductsView
        },
        {
          path: 'products/:id/edit',
          name: 'admin-product-edit',
          component: AdminProductsView
        },
        {
          path: 'categories',
          name: 'admin-categories',
          component: AdminCategoriesView
        },
        {
          path: 'orders',
          name: 'admin-orders',
          component: AdminOrdersView
        },
        {
          path: 'orders/:id',
          name: 'admin-order-detail',
          component: AdminOrdersView
        },
        {
          path: 'users',
          name: 'admin-users',
          component: AdminUsersView
        },
        {
          path: 'inventory',
          name: 'admin-inventory',
          component: AdminInventoryView
        },
        {
          path: 'promotions',
          name: 'admin-promotions',
          component: AdminPromotionsView
        },
        {
          path: 'reviews',
          name: 'admin-reviews',
          component: AdminReviewsView
        },
        {
          path: 'content',
          name: 'admin-content',
          component: AdminCMSView
        },
        {
          path: 'reports',
          name: 'admin-reports',
          component: AdminReportsView
        },
        {
          path: 'settings',
          name: 'admin-settings',
          component: AdminSettingsView
        },
        {
          path: 'logs',
          name: 'admin-logs',
          component: AdminLogsView
        },
        {
          path: 'admins',
          name: 'admin-admins',
          component: AdminAdminsView
        },
        {
          path: 'shipping',
          name: 'admin-shipping',
          component: AdminShippingView
        },
        {
          path: 'payments',
          name: 'admin-payments',
          component: AdminPaymentsView
        },
        {
          path: 'profile',
          name: 'admin-profile',
          component: AdminProfileView
        }
      ]
    }
  ],
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

// Navigation guard for admin and client routes
router.beforeEach(async (to, from, next) => {
  // Check if route requires admin auth
  if (to.meta.requiresAuth && to.path.startsWith('/admin')) {
    const { useAdminAuthStore } = await import('@/stores/adminAuth')
    const adminStore = useAdminAuthStore()
    
    // Check if admin data exists (token is in memory, not localStorage)
    if (!adminStore.isAuthenticated) {
      // Try to fetch admin data (will trigger token refresh if refresh token exists)
      try {
        await adminStore.fetchAdmin()
        if (adminStore.isAuthenticated) {
          next()
          return
        }
      } catch {
        // Fetch failed, redirect to login
      }
      // Not authenticated, redirect to login
      next('/admin/login')
    } else {
      next()
    }
  } 
  // Check if route requires client auth
  else if (to.meta.requiresAuth && !to.path.startsWith('/admin')) {
    // #region agent log
    fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'router/index.ts:264',message:'Router guard ENTRY - client auth required',data:{toPath:to.path,toName:to.name,fromPath:from.path},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A,B,C,D'})}).catch(()=>{});
    // #endregion
    const { useAuthStore } = await import('@/stores/auth')
    const authStore = useAuthStore()
    
    // #region agent log
    fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'router/index.ts:269',message:'Router guard - BEFORE auth check',data:{isAuthenticated:authStore.isAuthenticated,hasUser:!!authStore.user,userId:authStore.user?.id},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A,B'})}).catch(()=>{});
    // #endregion
    
    // Check if user data exists (token is in memory, not localStorage)
    if (!authStore.isAuthenticated) {
      // #region agent log
      fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'router/index.ts:272',message:'Router guard - NOT authenticated, calling fetchUser',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
      // #endregion
      // Try to fetch user data (will trigger token refresh if refresh token exists)
      try {
        const result = await authStore.fetchUser()
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'router/index.ts:275',message:'Router guard - AFTER fetchUser',data:{resultSuccess:result.success,resultExpired:result.expired,resultNoToken:result.noToken,isAuthenticated:authStore.isAuthenticated,hasUser:!!authStore.user},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
        // #endregion
        if (result.success && authStore.isAuthenticated) {
          // #region agent log
          fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'router/index.ts:277',message:'Router guard - Auth SUCCESS, calling next()',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
          // #endregion
          next()
          return
        }
      } catch (err) {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'router/index.ts:280',message:'Router guard - fetchUser EXCEPTION',data:{error:err?.message||String(err)},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
        // #endregion
        // Fetch failed, redirect to login
      }
      // #region agent log
      fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'router/index.ts:281',message:'Router guard - REDIRECTING to home (not authenticated)',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A,B,C'})}).catch(()=>{});
      // #endregion
      // Not authenticated, redirect to home with login prompt
      next({ name: 'home', query: { login: 'true' } })
    } else {
      // #region agent log
      fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'router/index.ts:283',message:'Router guard - Auth OK, calling next()',data:{isAuthenticated:authStore.isAuthenticated,hasUser:!!authStore.user},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
      // #endregion
      next()
    }
  } 
  else if (to.name === 'admin-login') {
    // If already logged in, redirect to dashboard
    const { useAdminAuthStore } = await import('@/stores/adminAuth')
    const adminStore = useAdminAuthStore()
    if (adminStore.isAuthenticated) {
      next('/admin/dashboard')
    } else {
      next()
    }
  } else {
    next()
  }
})

export default router
