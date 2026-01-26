<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import { RouterView, RouterLink, useRoute, useRouter } from 'vue-router'
import LoginModal from '@/components/LoginModal.vue'
import SignupModal from '@/components/SignupModal.vue'
import PromotionPopup from '@/components/PromotionPopup.vue'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { useOrderCount } from '@/composables/useOrderCount'
import { getClientAccessToken } from '@/utils/tokenManager'
import { promotions as promotionsApi } from '@/services/clientApi'

const route = useRoute()
const router = useRouter()

// ═══════════════════════════════════════════════════
// NAVBAR STATE
// ═══════════════════════════════════════════════════
const isScrolled = ref(false)
const isMobileMenuOpen = ref(false)
const scrollThreshold = 50

// ═══════════════════════════════════════════════════
// AUTH STATE
// ═══════════════════════════════════════════════════
const authStore = useAuthStore()
const isLoggedIn = computed(() => authStore.isAuthenticated)
const user = computed(() => ({
  firstname: authStore.user?.first_name || 'Guest',
  email: authStore.user?.email || ''
}))

// ═══════════════════════════════════════════════════
// CART STATE
// ═══════════════════════════════════════════════════
const cartStore = useCartStore()
const cartCount = computed(() => cartStore.itemCount)

// ═══════════════════════════════════════════════════
// ORDER COUNT STATE
// ═══════════════════════════════════════════════════
const { orderCount, fetchOrderCount, updateOrderCount } = useOrderCount()

// ═══════════════════════════════════════════════════
// MODAL STATE
// ═══════════════════════════════════════════════════
const showLoginModal = ref(false)
const showSignupModal = ref(false)

// ═══════════════════════════════════════════════════
// SCROLL HANDLER
// ═══════════════════════════════════════════════════
const handleScroll = () => {
  isScrolled.value = window.scrollY > scrollThreshold
}

// ═══════════════════════════════════════════════════
// MOBILE MENU
// ═══════════════════════════════════════════════════
const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
}

// ═══════════════════════════════════════════════════
// AUTH ACTIONS
// ═══════════════════════════════════════════════════
const openLoginModal = () => {
  showLoginModal.value = true
  closeMobileMenu()
}

const handleLoginSuccess = async () => {
  // #region agent log
  fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'ClientLayout.vue:67',message:'handleLoginSuccess ENTRY',data:{isAuthenticated:authStore.isAuthenticated,hasUser:!!authStore.user},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A,B'})}).catch(()=>{});
  // #endregion
  try {
    // Tokens are stored in memory via tokenManager, not localStorage
    // Just call fetchUser() - the API interceptor will use the token from memory
    // #region agent log
    fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'ClientLayout.vue:70',message:'handleLoginSuccess - calling fetchUser',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
    // #endregion
    await authStore.fetchUser()
    // #region agent log
    fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'ClientLayout.vue:72',message:'handleLoginSuccess - AFTER fetchUser',data:{isAuthenticated:authStore.isAuthenticated,hasUser:!!authStore.user,userId:authStore.user?.id},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
    // #endregion
    // Refresh cart to merge guest cart with user cart
    await cartStore.fetchCart()
    
    // Check for redirect destination after successful login
    const redirectPath = sessionStorage.getItem('redirectAfterLogin')
    if (redirectPath) {
      // #region agent log
      fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'ClientLayout.vue:78',message:'handleLoginSuccess - redirecting after login',data:{redirectPath},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
      // #endregion
      sessionStorage.removeItem('redirectAfterLogin')
      router.push(redirectPath).catch(() => {})
    }
  } catch (error) {
    // #region agent log
    fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'ClientLayout.vue:82',message:'handleLoginSuccess - EXCEPTION',data:{error:(error as Error)?.message||String(error)},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
    // #endregion
    console.error('Failed to fetch user after login:', error)
  }
  showLoginModal.value = false
  // Remove login query parameter if present - do this immediately to prevent watcher from re-triggering
  if (route.query.login) {
    router.replace({ query: {} }).catch(() => {
      // Ignore navigation errors
    })
  }
}

const handleSignupSuccess = async () => {
  try {
    await authStore.fetchUser()
    // Refresh cart to merge guest cart with user cart
    await cartStore.fetchCart()
  } catch (error) {
    console.error('Failed to fetch user after signup:', error)
  }
  showSignupModal.value = false
  
  // Check for redirect destination
  const redirectPath = sessionStorage.getItem('redirectAfterLogin')
  if (redirectPath) {
    sessionStorage.removeItem('redirectAfterLogin')
    router.push(redirectPath)
  } else {
    // Remove login query parameter if present
    if (route.query.login) {
      router.replace({ query: {} })
    }
  }
}

// Switch modal functions (used by modal components)
const switchToSignup = () => {
  showLoginModal.value = false
  showSignupModal.value = true
}

const switchToLogin = () => {
  showSignupModal.value = false
  showLoginModal.value = true
}

const logout = async () => {
  await authStore.logout()
  cartStore.clearCart()
  closeMobileMenu()
}

// ═══════════════════════════════════════════════════
// CURRENT YEAR
// ═══════════════════════════════════════════════════
const currentYear = computed(() => new Date().getFullYear())

// ═══════════════════════════════════════════════════
// PROMOTION POPUP
// ═══════════════════════════════════════════════════
interface Promotion {
  id: number
  name: string
  code: string
  description?: string
  discountType: 'percentage' | 'fixed' | 'free_shipping' | 'buy_x_get_y'
  value: number
  minOrderAmount?: number
  createdAt: string
}

const currentPromotion = ref<Promotion | null>(null)
const PROMOTION_STORAGE_KEY = 'casavera_shown_promotions'

// Get list of shown promotion IDs from localStorage
const getShownPromotionIds = (): number[] => {
  try {
    const stored = localStorage.getItem(PROMOTION_STORAGE_KEY)
    if (stored) {
      return JSON.parse(stored)
    }
  } catch (e) {
    console.error('Failed to read shown promotions from localStorage:', e)
  }
  return []
}

// Mark a promotion as shown
const markPromotionAsShown = (promotionId: number): void => {
  try {
    const shown = getShownPromotionIds()
    if (!shown.includes(promotionId)) {
      shown.push(promotionId)
      localStorage.setItem(PROMOTION_STORAGE_KEY, JSON.stringify(shown))
    }
  } catch (e) {
    console.error('Failed to save shown promotion to localStorage:', e)
  }
}

// Check for new visible promotions
const checkForNewPromotions = async (): Promise<void> => {
  try {
    const response = await promotionsApi.list()
    console.log('Promotions API response:', response.data)
    
    if (response.data.success && response.data.data && response.data.data.length > 0) {
      const promotions: Promotion[] = response.data.data
      const shownIds = getShownPromotionIds()
      
      console.log('Available promotions:', promotions.length)
      console.log('Shown promotion IDs:', shownIds)
      
      // Find the newest promotion that hasn't been shown yet
      // Sort by createdAt (newest first) and find first unshown
      const sortedPromotions = [...promotions].sort((a, b) => {
        const dateA = new Date(a.createdAt).getTime()
        const dateB = new Date(b.createdAt).getTime()
        return dateB - dateA // Newest first
      })
      
      const newPromotion = sortedPromotions.find(p => !shownIds.includes(p.id))
      
      console.log('New promotion found:', newPromotion)
      
      if (newPromotion) {
        // Show the popup after a short delay for better UX
        setTimeout(() => {
          currentPromotion.value = newPromotion
          // Mark as shown immediately so it won't show again
          markPromotionAsShown(newPromotion.id)
          console.log('Showing promotion popup:', newPromotion.name)
        }, 1500) // 1.5 second delay after page load
      } else {
        console.log('No new promotions to show')
      }
    } else {
      console.log('No promotions available or API error')
    }
  } catch (error) {
    console.error('Failed to check for promotions:', error)
    // Silently fail - don't show error to user
  }
}

const closePromotionPopup = (): void => {
  currentPromotion.value = null
}

// ═══════════════════════════════════════════════════
// WATCHERS
// ═══════════════════════════════════════════════════
// Watch for login query parameter to open login modal
watch(
  () => route.query.login,
  (loginParam) => {
    // Only open login modal if:
    // 1. login query param is 'true'
    // 2. User is NOT authenticated
    // 3. Login modal is not already open (prevent duplicate opens)
    if (loginParam === 'true' && !authStore.isAuthenticated && !showLoginModal.value) {
      showLoginModal.value = true
      showSignupModal.value = false
    } else if (loginParam === 'true' && authStore.isAuthenticated) {
      // If user is already authenticated, remove the login query parameter
      router.replace({ query: {} })
    }
  },
  { immediate: true }
)

// ═══════════════════════════════════════════════════
// LIFECYCLE
// ═══════════════════════════════════════════════════
onMounted(async () => {
  try {
    // Fetch initial cart
    await cartStore.fetchCart()
    
    // Fetch user if token exists in memory OR if user data exists in localStorage
    // fetchUser will handle token refresh automatically if needed (no token but user data exists)
    // Tokens are stored in memory (not localStorage), so they're lost on page refresh
    const token = getClientAccessToken()
    const hasStoredUser = !!authStore.user
    
    if (token || hasStoredUser) {
      // fetchUser will:
      // 1. Check if token exists
      // 2. If no token but user data exists, refresh token first (prevents 401 errors)
      // 3. Only make API call if token is available
      const result = await authStore.fetchUser()
      
      // fetchUser always returns an object, so we can safely check it
      if (result.expired) {
        // Session expired - user was already logged out by fetchUser
        console.log('Session expired, user logged out')
      } else if (result.noToken && !hasStoredUser) {
        // No token and no user data - user is not logged in
        // This is normal, no action needed
      } else if (result.success && authStore.isAuthenticated) {
        // Fetch order count if user is authenticated
        await fetchOrderCount()
      }
    }
    
    // Check for new visible promotions (available on all pages)
    checkForNewPromotions()
  } catch (error) {
    console.error('Failed to initialize layout:', error)
    // Don't clear auth state here - let fetchUser handle it
  }
  
  window.addEventListener('scroll', handleScroll, { passive: true })
  handleScroll() // Check initial scroll position
})

// Watch for authentication changes to update order count
watch(() => authStore.isAuthenticated, async (isAuthenticated) => {
  if (isAuthenticated) {
    await fetchOrderCount()
  } else {
    updateOrderCount(0)
  }
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <div class="client-layout">
    <!-- Announcements Banner (optional) -->
    <div id="clientAnnouncements" class="client-announcements-container"></div>

    <!-- Navigation -->
    <nav class="navbar" :class="{ scrolled: isScrolled }">
      <div class="nav-container">
        <!-- Mobile Toggle -->
        <button 
          class="navbar-toggler" 
          type="button" 
          @click="toggleMobileMenu"
          :class="{ active: isMobileMenuOpen }"
        >
          <span class="toggler-line"></span>
          <span class="toggler-line"></span>
          <span class="toggler-line"></span>
        </button>

        <!-- Centered Brand -->
        <div class="navbar-brand-centered">
          <RouterLink to="/" class="brand-logo">CASA VÉRA</RouterLink>
          <span class="brand-tagline">Est. 2022</span>
        </div>

        <!-- Navigation Content -->
        <div class="navbar-collapse" :class="{ show: isMobileMenuOpen }">
          <ul class="navbar-nav">
            <li class="nav-item">
              <RouterLink to="/" class="nav-link" @click="closeMobileMenu">Home</RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink to="/products" class="nav-link" @click="closeMobileMenu">Collection</RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink to="/contact" class="nav-link" @click="closeMobileMenu">Contact</RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink to="/about" class="nav-link" @click="closeMobileMenu">About</RouterLink>
            </li>
          </ul>

          <!-- Nav Actions -->
          <div class="nav-actions">
            <RouterLink to="/cart" class="nav-icon-link" title="View Cart" @click="closeMobileMenu">
              <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 0 1-8 0"/>
              </svg>
              <span class="badge-cart" v-if="cartCount > 0">{{ cartCount }}</span>
            </RouterLink>

            <!-- User Menu (Logged In) -->
            <div v-if="isLoggedIn" class="nav-item dropdown">
              <a class="nav-link dropdown-toggle text-gold" href="#" role="button">
                <svg class="nav-icon-sm me-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
                {{ user.firstname }}
              </a>
              <ul class="dropdown-menu">
                <li><RouterLink to="/profile" class="dropdown-item" @click="closeMobileMenu">My Profile</RouterLink></li>
                <li>
                  <RouterLink to="/orders" class="dropdown-item" @click="closeMobileMenu">
                    My Orders
                    <span v-if="orderCount > 0" class="nav-badge">{{ orderCount }}</span>
                  </RouterLink>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#" @click.prevent="logout">Logout</a></li>
              </ul>
            </div>

            <!-- Login Link (Not Logged In) -->
            <a v-else href="#" class="nav-link special-login-link" @click.prevent="openLoginModal">
              Login
            </a>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="client-main">
      <RouterView />
    </main>

    <!-- Footer -->
    <footer class="footer-luxury">
      <div class="footer-container">
        <div class="footer-grid">
          <!-- Brand Column -->
          <div class="footer-col footer-brand">
            <RouterLink to="/" class="text-decoration-none">
              <h3 class="brand-font text-gold">CASA VÉRA</h3>
            </RouterLink>
            <p class="footer-desc">
              Timeless elegance for the modern home.
            </p>
          </div>

          <!-- Explore Column -->
          <div class="footer-col">
            <h5 class="footer-heading">Explore</h5>
            <ul class="footer-links">
              <li><RouterLink to="/">Home</RouterLink></li>
              <li><RouterLink to="/products">Collection</RouterLink></li>
              <li><RouterLink to="/about">Our Story</RouterLink></li>
              <li><RouterLink to="/contact">Contact</RouterLink></li>
            </ul>
          </div>

          <!-- Social Column -->
          <div class="footer-col">
            <h5 class="footer-heading">Follow Us</h5>
            <p class="footer-social-text">Join our community.</p>
            <div class="social-icons">
              <a href="https://facebook.com" target="_blank" class="social-icon-box" aria-label="Facebook">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                </svg>
              </a>
              <a href="https://instagram.com" target="_blank" class="social-icon-box" aria-label="Instagram">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                  <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                  <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                </svg>
              </a>
            </div>
          </div>
        </div>

        <hr class="footer-divider">

        <div class="footer-bottom">
          <p class="copyright">
            &copy; {{ currentYear }} CASA VÉRA. All rights reserved.
          </p>
          <div class="footer-legal">
            <RouterLink to="/privacy" class="legal-link">Privacy Policy</RouterLink>
            <RouterLink to="/terms" class="legal-link">Terms of Service</RouterLink>
          </div>
        </div>
      </div>
    </footer>

    <!-- Auth Modals -->
    <LoginModal 
      :is-open="showLoginModal"
      @close="() => { showLoginModal = false; if (route.query.login) router.replace({ query: {} }) }"
      @switch-to-signup="switchToSignup"
      @login-success="handleLoginSuccess"
    />
    
    <SignupModal 
      :is-open="showSignupModal"
      @close="() => { showSignupModal = false; if (route.query.login) router.replace({ query: {} }) }"
      @switch-to-login="switchToLogin"
      @signup-success="handleSignupSuccess"
    />

    <!-- Promotion Popup (Available on all client pages) -->
    <PromotionPopup 
      v-if="currentPromotion" 
      :promotion="currentPromotion" 
      @close="closePromotionPopup"
    />
  </div>
</template>

<style scoped>
/* ==========================================================================
   CSS VARIABLES
   ========================================================================== */
.client-layout {
  --primary-color: #c9a050;
  --gold: #FFD700;
  --dark: #1a1a1a;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  width: 100%;
  overflow-x: hidden;
}

/* ==========================================================================
   NAVBAR: LUXURY GLASSMORPHISM
   ========================================================================== */
.navbar {
  height: 100px;
  width: 100%;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1030;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
  background: rgba(0, 0, 0, 0.02);
  -webkit-backdrop-filter: blur(8px);
  backdrop-filter: blur(8px);
}

.navbar.scrolled {
  height: 80px;
  background: rgba(10, 10, 10, 0.85);
  -webkit-backdrop-filter: blur(20px);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(255, 215, 0, 0.3);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.nav-container {
  max-width: 100%;
  height: 100%;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
}

/* Brand Centered */
.navbar-brand-centered {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  z-index: 10;
}

.brand-logo {
  font-family: 'Playfair Display', serif;
  font-size: 2.2rem;
  font-weight: 700;
  color: #ffffff;
  letter-spacing: 3px;
  text-transform: uppercase;
  text-decoration: none;
  transition: all 0.3s ease;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
  display: block;
}

.brand-logo:hover {
  color: var(--gold);
  text-shadow: 0 0 20px rgba(255, 215, 0, 0.6);
}

.brand-tagline {
  display: block;
  font-family: 'Inter', sans-serif;
  font-size: 0.65rem;
  letter-spacing: 5px;
  color: rgba(255, 255, 255, 0.7);
  margin-top: -2px;
  text-transform: uppercase;
  opacity: 1;
  transition: opacity 0.3s ease;
}

.navbar.scrolled .brand-tagline {
  opacity: 0;
}

/* Navigation Links */
.navbar-collapse {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
}

.navbar-nav {
  display: flex;
  list-style: none;
  margin: 0;
  padding: 0;
  gap: 2.5rem;
}

.nav-link {
  font-family: 'Inter', sans-serif;
  font-size: 0.8rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 2px;
  color: #ffffff !important;
  text-decoration: none;
  position: relative;
  padding: 0;
  opacity: 0.8;
  transition: all 0.3s ease;
}

.nav-link:hover,
.nav-link.router-link-exact-active {
  opacity: 1;
  color: var(--gold) !important;
}

.nav-link::after {
  content: '';
  position: absolute;
  bottom: -5px;
  left: 0;
  width: 100%;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--gold), transparent);
  transform: scaleX(0);
  transition: transform 0.4s ease;
}

.nav-link:hover::after,
.nav-link.router-link-exact-active::after {
  transform: scaleX(1);
}

/* Nav Actions */
.nav-actions {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-left: auto;
}

.nav-icon-link {
  color: #fff;
  font-size: 1.1rem;
  transition: transform 0.3s ease, color 0.3s ease;
  position: relative;
  text-decoration: none;
}

.nav-icon-link:hover {
  color: var(--gold);
  transform: translateY(-2px);
}

.nav-icon {
  width: 22px;
  height: 22px;
  display: block;
}

.nav-icon-sm {
  width: 18px;
  height: 18px;
  display: inline-block;
  vertical-align: middle;
}

.me-1 {
  margin-right: 0.25rem;
}

.badge-cart {
  position: absolute;
  top: -8px;
  right: -10px;
  background-color: var(--gold);
  color: #000;
  font-size: 0.6rem;
  font-weight: 800;
  height: 16px;
  width: 16px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.nav-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 20px;
  height: 20px;
  padding: 0 6px;
  background-color: var(--gold);
  color: #000;
  font-size: 0.7rem;
  font-weight: 700;
  border-radius: 10px;
  margin-left: 0.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.special-login-link {
  font-family: 'Inter', sans-serif;
  font-size: 0.8rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 2px;
  color: #ffffff !important;
  text-decoration: none;
  opacity: 0.8;
  transition: all 0.3s ease;
}

.special-login-link:hover {
  opacity: 1;
  color: var(--gold) !important;
}

/* User Dropdown */
.dropdown {
  position: relative;
}

.dropdown-toggle {
  cursor: pointer;
}

.text-gold {
  color: var(--gold) !important;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  background: rgba(10, 10, 10, 0.95);
  border: none;
  border-radius: 8px;
  padding: 0.5rem 0;
  min-width: 160px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  display: none;
  list-style: none;
}

.dropdown:hover .dropdown-menu {
  display: block;
}

.dropdown-item {
  display: block;
  padding: 0.5rem 1rem;
  color: #fff;
  text-decoration: none;
  font-size: 0.85rem;
  transition: all 0.3s ease;
}

.dropdown-item:hover {
  color: var(--gold);
  background: rgba(255, 255, 255, 0.05);
}

.dropdown-item.text-danger {
  color: #dc3545 !important;
}

.dropdown-divider {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  margin: 0.25rem 0;
}

/* Mobile Toggle */
.navbar-toggler {
  display: none;
  border: none;
  background: none;
  padding: 0;
  cursor: pointer;
  z-index: 20;
}

.toggler-line {
  display: block;
  width: 30px;
  height: 2px;
  background-color: #fff;
  margin: 6px 0;
  transition: all 0.3s ease;
}

.navbar-toggler:hover .toggler-line {
  background-color: var(--gold);
  width: 35px;
}

.navbar-toggler.active .toggler-line:nth-child(1) {
  transform: rotate(45deg) translate(5px, 5px);
}

.navbar-toggler.active .toggler-line:nth-child(2) {
  opacity: 0;
}

.navbar-toggler.active .toggler-line:nth-child(3) {
  transform: rotate(-45deg) translate(7px, -6px);
}

/* Mobile Menu */
@media (max-width: 991px) {
  .navbar-toggler {
    display: block;
  }

  .navbar-collapse {
    position: fixed;
    top: 100px;
    left: 0;
    width: 100%;
    height: calc(100vh - 100px);
    background: rgba(0, 0, 0, 0.95);
    -webkit-backdrop-filter: blur(20px);
    backdrop-filter: blur(20px);
    flex-direction: column;
    justify-content: flex-start;
    padding: 2rem;
    border-top: 1px solid rgba(255, 215, 0, 0.2);
    transform: translateX(-100%);
    transition: transform 0.4s ease;
  }

  .navbar-collapse.show {
    transform: translateX(0);
  }

  .navbar.scrolled .navbar-collapse {
    top: 80px;
    height: calc(100vh - 80px);
  }

  .navbar-nav {
    flex-direction: column;
    gap: 1.5rem;
    width: 100%;
    margin-bottom: 2rem;
  }

  .nav-link {
    font-size: 1rem;
  }

  .nav-actions {
    flex-direction: column;
    gap: 1.5rem;
    width: 100%;
  }

  .brand-logo {
    font-size: 1.6rem;
  }

  .brand-tagline {
    font-size: 0.55rem;
    letter-spacing: 3px;
  }
}

/* ==========================================================================
   MAIN CONTENT
   ========================================================================== */
.client-main {
  flex: 1;
  width: 100%;
  padding: 0;
  margin: 0;
}

/* ==========================================================================
   FOOTER
   ========================================================================== */
.footer-luxury {
  background-color: #ffffff;
  color: var(--dark);
  padding: 1.5rem 0 1rem;
  position: relative;
  z-index: 100;
  border-top: 4px solid var(--primary-color);
  margin-top: auto;
}

.footer-container {
  max-width: 1400px;
  width: 100%;
  margin: 0 auto;
  padding: 0 2rem;
}

.footer-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 2rem;
}

.footer-col h3.brand-font {
  font-family: 'Playfair Display', serif;
  font-size: 1.25rem;
  font-weight: 700;
  background: linear-gradient(45deg, #B8860B, #FFD700, #F9F295, #B8860B);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 0.25rem;
}

.footer-desc {
  color: #666;
  line-height: 1.5;
  font-size: 0.8rem;
  max-width: 350px;
  margin: 0.5rem 0 0; 
}

.footer-heading {
  font-family: 'Inter', sans-serif;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: var(--dark);
  margin-bottom: 0.5rem;
}

.footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-links li {
  margin-bottom: 0.25rem;
}

.footer-links a {
  color: #666;
  text-decoration: none;
  transition: all 0.3s ease;
  display: inline-block;
  font-size: 0.8rem;
}

.footer-links a:hover {
  color: var(--primary-color);
  transform: translateX(3px);
}

.footer-social-text {
  font-size: 0.75rem;
  color: #888;
  margin-bottom: 0.5rem;
}

.social-icons {
  display: flex;
  gap: 0.4rem;
}

.social-icon-box {
  width: 32px;
  height: 32px;
  border: 1px solid #e0e0e0;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--dark);
  transition: all 0.3s ease;
  background: #fff;
  text-decoration: none;
}

.social-icon-box svg {
  width: 14px;
  height: 14px;
}

.social-icon-box:hover {
  border-color: var(--primary-color);
  background-color: var(--primary-color);
  color: #fff;
  transform: translateY(-2px);
}

.footer-divider {
  border: none;
  border-top: 1px solid #eee;
  margin: 1rem 0 0.75rem;
}

.footer-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.copyright {
  font-size: 0.75rem;
  color: #888;
  margin: 0;
}

.footer-legal {
  display: flex;
  gap: 1.5rem;
}

.legal-link {
  font-size: 0.8rem;
  color: #888;
  text-decoration: none;
  position: relative;
  transition: color 0.3s ease;
  padding-bottom: 2px;
}

.legal-link:hover {
  color: var(--primary-color);
}

.legal-link::after {
  content: '';
  position: absolute;
  width: 0;
  height: 1px;
  bottom: 0;
  left: 50%;
  background-color: var(--primary-color);
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  transform: translateX(-50%);
}

.legal-link:hover::after {
  width: 100%;
}

/* Footer Responsive */
@media (max-width: 768px) {
  .footer-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
    text-align: center;
  }

  .footer-desc {
    max-width: 100%;
    margin: 0 auto;
  }

  .social-icons {
    justify-content: center;
  }

  .footer-bottom {
    flex-direction: column;
    text-align: center;
  }

  .footer-legal {
    justify-content: center;
  }
}

/* Announcements Banner */
.client-announcements-container {
  display: none; /* Hidden until announcements are implemented */
}
</style>
