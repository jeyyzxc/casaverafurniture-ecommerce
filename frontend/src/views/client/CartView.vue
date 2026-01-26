<template>
  <div class="cart-page">
    <!-- Hero Section -->
    <HeroSection
      title="Shopping Cart"
      subtitle="Review your curated masterpieces before securing your order."
      size="large"
    />

    <!-- Cart Content -->
    <section class="cart-section">
      <div class="cart-container">
        <!-- Main Cart Content -->
        <div v-if="!orderSuccess" class="cart-layout">
          <!-- Cart Items -->
          <div class="cart-items-wrapper rise-up">
            <div class="cart-header">
              <h3 class="cart-title">Selected Items ({{ cartItems.length }})</h3>
              <button 
                v-if="cartItems.length > 0" 
                class="clear-btn"
                @click="clearCart"
              >
                Clear All
              </button>
            </div>

            <!-- Cart Items List -->
            <div v-if="!isEmpty && !isLoading && cartItems.length > 0" class="cart-items">
              <div 
                v-for="(item, index) in cartItems" 
                :key="item.id" 
                class="cart-item"
                :class="`rise-up-delay-${Math.min(index + 1, 5)}`"
              >
                <img :src="item.product_image || '/images/products/placeholder.png'" :alt="item.product_name" class="item-image">
                <div class="item-details">
                  <div class="item-header">
                    <h4 class="item-name">{{ item.product_name }}</h4>
                    <button class="remove-btn" @click="removeItem(item.id)" :disabled="isLoading">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                      </svg>
                    </button>
                  </div>
                  <p class="item-category">SKU: {{ item.product_sku }}</p>
                  <div class="item-footer">
                    <div class="quantity-control">
                      <button @click="updateQuantity(item.id, -1)" :disabled="item.quantity <= 1 || isLoading">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M5 12h14"/>
                        </svg>
                      </button>
                      <span>{{ item.quantity }}</span>
                      <button @click="updateQuantity(item.id, 1)" :disabled="item.quantity >= item.max_quantity || isLoading">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M12 5v14M5 12h14"/>
                        </svg>
                      </button>
                    </div>
                    <span class="item-total">₱{{ formatPrice(item.subtotal) }}</span>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Loading State -->
            <div v-if="isLoading" class="cart-loading">
              <div class="spinner"></div>
              <p>Loading cart...</p>
            </div>

            <!-- Empty Cart - Only show when cart is actually empty and not loading -->
            <div v-else-if="isEmpty && !isLoading && cartItems.length === 0" class="empty-cart">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 0 1-8 0"/>
              </svg>
              <h4>Your cart is empty</h4>
              <p>Looks like you haven't found your perfect piece yet.</p>
              <router-link to="/products" class="btn-continue">Continue Shopping</router-link>
            </div>
          </div>

          <!-- Order Summary -->
          <div class="order-summary rise-up-delay-2" v-if="cartItems.length > 0">
            <div class="summary-card">
              <div class="summary-header">
                <h4>Order Summary</h4>
              </div>
              <div class="summary-body">
                <div class="summary-row">
                  <span>Subtotal</span>
                  <span class="value">₱{{ formatPrice(subtotal) }}</span>
                </div>
                <div v-if="discount > 0" class="summary-row">
                  <span>Discount</span>
                  <span class="value discount">-₱{{ formatPrice(discount) }}</span>
                </div>
                <div class="summary-row">
                  <span>Shipping</span>
                  <span class="value free">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                      <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                    </svg>
                    Calculated at checkout
                  </span>
                </div>
                <div class="summary-divider"></div>
                <div class="summary-row total">
                  <span>Total</span>
                  <span class="value gold">₱{{ formatPrice(total) }}</span>
                </div>

                <button 
                  class="btn-checkout"
                  @click="proceedToCheckout"
                  :disabled="isEmpty || isLoading"
                >
                  <span>Proceed to Checkout</span>
                  <div class="btn-shimmer"></div>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Success -->
        <div v-else class="order-success">
          <div class="success-icon">
            <svg viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
          </div>
          <h2>Payment Successful!</h2>
          <p>Thank you for your purchase. Your order has been confirmed.</p>
          
          <div class="order-details">
            <div class="detail-row">
              <span>Order Reference:</span>
              <span class="value">{{ orderRef }}</span>
            </div>
            <div class="detail-row">
              <span>Date:</span>
              <span class="value">{{ orderDate }}</span>
            </div>
            <div class="detail-row">
              <span>Payment Method:</span>
              <span class="value">{{ paymentMethod }}</span>
            </div>
            <div class="delivery-note">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
              </svg>
              Estimated Delivery: 3-5 Business Days
            </div>
          </div>

          <router-link to="/products" class="btn-continue">Continue Shopping</router-link>
        </div>
      </div>
    </section>

    <!-- Payment Modal -->
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import HeroSection from '@/components/HeroSection.vue'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const cartStore = useCartStore()
const authStore = useAuthStore()

// Payment state
const orderSuccess = ref(false)
const orderRef = ref('')
const orderDate = ref('')
const paymentMethod = ref('')

// Computed
const cartItems = computed(() => cartStore.items)
const subtotal = computed(() => cartStore.subtotal)
const discount = computed(() => cartStore.discount)
const total = computed(() => cartStore.total)
const isEmpty = computed(() => cartStore.isEmpty)
const isLoading = computed(() => cartStore.isLoading)

// Methods
const formatPrice = (price: number) => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 2 })
}

const updateQuantity = async (itemId: number, change: number) => {
  const item = cartItems.value.find(i => i.id === itemId)
  if (item) {
    const newQty = item.quantity + change
    if (newQty >= 1 && newQty <= item.max_quantity) {
      await cartStore.updateItem(itemId, newQty)
    }
  }
}

const removeItem = async (itemId: number) => {
  await cartStore.removeItem(itemId)
}

const clearCart = async () => {
  if (confirm('Are you sure you want to clear your cart?')) {
    await cartStore.clearCart()
  }
}

const proceedToCheckout = async () => {
  // #region agent log
  fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'CartView.vue:218',message:'proceedToCheckout ENTRY',data:{cartItemsCount:cartItems.value.length,isAuthenticated:authStore.isAuthenticated,hasUser:!!authStore.user},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A,B'})}).catch(()=>{});
  // #endregion
  
  // Step 1: Validate cart has items
  if (cartItems.value.length === 0) {
    // #region agent log
    fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'CartView.vue:224',message:'Cart empty - early return',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
    // #endregion
    console.warn('Cannot proceed to checkout: cart is empty')
    return
  }
  
  // Step 2: Check authentication - if not authenticated, redirect to login
  // The router guard will handle deeper authentication verification
  if (!authStore.isAuthenticated || !authStore.user) {
    // #region agent log
    fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'CartView.vue:232',message:'Not authenticated - redirecting to login',data:{isAuthenticated:authStore.isAuthenticated,hasUser:!!authStore.user},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'B'})}).catch(()=>{});
    // #endregion
    sessionStorage.setItem('redirectAfterLogin', '/checkout')
    router.push({ name: 'home', query: { login: 'true' } }).catch(() => {})
    return
  }
  
  // Step 3: Ensure cart is fresh before checkout
  try {
    await cartStore.fetchCart()
    
    // Double-check cart still has items after refresh
    if (cartStore.items.length === 0) {
      console.warn('Cart is empty after refresh, cannot proceed')
      return
    }
  } catch (error) {
    console.error('Failed to refresh cart:', error)
    // Continue anyway - cart might still be valid
  }
  
  // Step 4: Navigate directly to checkout
  // Let the router guard and CheckoutView handle authentication verification
  // #region agent log
  fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'CartView.vue:250',message:'BEFORE router.push to /checkout',data:{isAuthenticated:authStore.isAuthenticated,hasUser:!!authStore.user,userId:authStore.user?.id,cartItemsCount:cartStore.items.length},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A,B'})}).catch(()=>{});
  // #endregion
  
  try {
    await router.push('/checkout')
    // #region agent log
    fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'CartView.vue:255',message:'router.push SUCCESS',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
    // #endregion
  } catch (error) {
    // #region agent log
    fetch('http://127.0.0.1:7242/ingest/519d2bb1-4823-4c4b-a812-0b4fe5394aa0',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'CartView.vue:258',message:'router.push ERROR',data:{error:(error as Error)?.message||String(error)},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
    // #endregion
    console.error('Navigation to checkout failed:', error)
    // If navigation fails, redirect to login as fallback
    sessionStorage.setItem('redirectAfterLogin', '/checkout')
    router.push({ name: 'home', query: { login: 'true' } }).catch(() => {})
  }
}

// Load cart on mount
onMounted(() => {
  cartStore.fetchCart()
})
</script>

<style scoped>
.cart-page {
  --gold: #c9a050;
  --gold-light: #d4af37;
  --dark: #1a1a1a;
  --white: #ffffff;
  --light: #f8f8f8;
  --gray: #666;
  
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.cart-section {
  background: var(--light);
  padding: 4rem 0;
  flex: 1;
}

.cart-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
}

.cart-layout {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 3rem;
  align-items: start;
}

/* Cart Items */
.cart-items-wrapper {
  background: var(--white);
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.cart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #eee;
  margin-bottom: 1.5rem;
}

.cart-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--dark);
  margin: 0;
}

.clear-btn {
  background: none;
  border: none;
  color: var(--gray);
  font-size: 0.85rem;
  cursor: pointer;
  transition: color 0.2s;
}

.clear-btn:hover {
  color: #dc3545;
}

.cart-items {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.cart-item {
  display: flex;
  gap: 1.5rem;
  padding: 1.5rem;
  background: #fafafa;
  border-radius: 16px;
  transition: all 0.3s ease;
}

.cart-item:hover {
  background: #f5f5f5;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
}

.item-image {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 12px;
}

.item-details {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.item-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.item-name {
  font-family: 'Playfair Display', serif;
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--dark);
  margin: 0;
}

.remove-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.25rem;
  color: var(--gray);
  transition: color 0.2s;
}

.remove-btn svg {
  width: 18px;
  height: 18px;
}

.remove-btn:hover {
  color: #dc3545;
}

.item-category {
  color: var(--gray);
  font-size: 0.85rem;
  margin: 0.25rem 0 auto;
}

.item-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1rem;
}

.quantity-control {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--white);
  border-radius: 25px;
  padding: 0.25rem;
  border: 1px solid #eee;
}

.quantity-control button {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.quantity-control button svg {
  width: 14px;
  height: 14px;
  color: var(--gray);
}

.quantity-control button:hover:not(:disabled) {
  background: var(--gold);
}

.quantity-control button:hover:not(:disabled) svg {
  color: var(--white);
}

.quantity-control button:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.quantity-control span {
  min-width: 30px;
  text-align: center;
  font-weight: 600;
  color: var(--dark);
}

.item-total {
  font-weight: 700;
  font-size: 1.1rem;
  color: var(--dark);
}

/* Empty Cart */
.empty-cart {
  text-align: center;
  padding: 4rem 2rem;
}

.empty-cart svg {
  width: 80px;
  height: 80px;
  color: #ddd;
  margin-bottom: 1.5rem;
}

.empty-cart h4 {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  color: var(--dark);
  margin: 0 0 0.5rem;
}

.empty-cart p {
  color: var(--gray);
  margin: 0 0 2rem;
}

.btn-continue {
  display: inline-block;
  padding: 1rem 2.5rem;
  background: var(--dark);
  color: var(--white);
  text-decoration: none;
  border-radius: 50px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-continue:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

/* Order Summary */
.order-summary {
  position: sticky;
  top: 120px;
}

.summary-card {
  background: var(--white);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
}

.summary-header {
  background: var(--dark);
  padding: 1.5rem 2rem;
  border-bottom: 4px solid var(--gold);
}

.summary-header h4 {
  font-family: 'Playfair Display', serif;
  color: var(--white);
  font-size: 1.25rem;
  margin: 0;
  text-align: center;
}

.summary-body {
  padding: 2rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  font-size: 0.9rem;
  color: var(--gray);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.summary-row .value {
  font-weight: 700;
  color: var(--dark);
}

.summary-row .free {
  color: var(--gold);
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.summary-row .free svg {
  width: 16px;
  height: 16px;
}

.summary-divider {
  border-bottom: 1px dashed #eee;
  margin: 1.5rem 0;
}

.summary-row.total {
  font-size: 1rem;
  margin-bottom: 1.5rem;
}

.summary-row.total .value.gold {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  background: linear-gradient(135deg, #B8860B 0%, #FFD700 50%, #B8860B 100%);
  background-size: 200% auto;
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  animation: goldShine 3s linear infinite;
}

@keyframes goldShine {
  to { background-position: 200% center; }
}

.btn-checkout {
  width: 100%;
  padding: 1rem;
  background: var(--dark);
  color: var(--white);
  border: none;
  border-radius: 50px;
  font-size: 0.9rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
}

.btn-checkout:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(201, 160, 80, 0.4);
}

.btn-checkout:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-shimmer {
  position: absolute;
  top: 0;
  left: 0;
  width: 50%;
  height: 100%;
  background: linear-gradient(to right, transparent, rgba(255, 215, 0, 0.3), transparent);
  transform: skewX(-20deg) translateX(-150%);
  animation: shimmer 3s infinite;
}

@keyframes shimmer {
  100% { transform: skewX(-20deg) translateX(300%); }
}

/* Order Success */
.order-success {
  text-align: center;
  padding: 4rem 2rem;
  background: var(--white);
  border-radius: 20px;
  max-width: 600px;
  margin: 0 auto;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
}

.success-icon {
  margin-bottom: 1.5rem;
}

.success-icon svg {
  width: 80px;
  height: 80px;
  color: var(--gold);
}

.order-success h2 {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  color: var(--dark);
  margin: 0 0 0.75rem;
}

.order-success > p {
  color: var(--gray);
  margin: 0 0 2rem;
}

.order-details {
  background: #fafafa;
  border-radius: 16px;
  padding: 1.5rem 2rem;
  text-align: left;
  margin-bottom: 2rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  border-bottom: 1px solid #eee;
  font-size: 0.95rem;
}

.detail-row:last-of-type {
  border-bottom: none;
}

.detail-row span:first-child {
  color: var(--gray);
}

.detail-row .value {
  font-weight: 600;
  color: var(--dark);
}

.delivery-note {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #eee;
  font-size: 0.85rem;
  color: var(--gray);
}

.delivery-note svg {
  width: 20px;
  height: 20px;
  color: var(--gold);
}

/* Payment Modal */
.payment-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
}

.payment-overlay.active {
  opacity: 1;
  visibility: visible;
}

.payment-modal {
  background: var(--white);
  width: 90%;
  max-width: 420px;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
  transform: translateY(20px) scale(0.95);
  transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.payment-overlay.active .payment-modal {
  transform: translateY(0) scale(1);
}

.payment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid #eee;
}

.payment-header h4 {
  font-family: 'Playfair Display', serif;
  font-size: 1.25rem;
  color: var(--dark);
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.25rem;
}

.close-btn svg {
  width: 20px;
  height: 20px;
  color: var(--gray);
}

.close-btn:hover svg {
  color: #dc3545;
}

.payment-body {
  padding: 2rem;
}

.payment-body > p {
  color: var(--gray);
  font-size: 0.9rem;
  margin: 0 0 1.5rem;
}

.payment-options {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.payment-option {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 1rem;
  background: var(--white);
  border: 2px solid #eee;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.payment-option svg {
  width: 24px;
  height: 24px;
}

.payment-option:hover {
  border-color: var(--gold);
  background: rgba(201, 160, 80, 0.05);
}

.processing {
  text-align: center;
  padding: 2rem;
}

.processing .spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #eee;
  border-top-color: var(--gold);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.processing p {
  color: var(--gray);
  margin: 0;
}

.payment-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 1.5rem;
  background: #fff5f5;
  border: 1px solid #ffcdd2;
  border-radius: 12px;
  color: #dc3545;
  text-align: center;
}

.payment-error svg {
  width: 32px;
  height: 32px;
}

.retry-btn {
  padding: 0.5rem 1.5rem;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 20px;
  cursor: pointer;
  font-weight: 600;
  margin-top: 0.5rem;
}

.retry-btn:hover {
  background: #c82333;
}

/* Responsive */
@media (max-width: 1024px) {
  .cart-layout {
    grid-template-columns: 1fr;
  }

  .order-summary {
    position: static;
  }
}

@media (max-width: 768px) {
  .cart-section {
    padding: 2rem 0;
  }

  .cart-container {
    padding: 0 1rem;
  }

  .cart-items-wrapper {
    padding: 1.5rem;
  }

  .cart-item {
    flex-direction: column;
    gap: 1rem;
  }

  .item-image {
    width: 100%;
    height: 150px;
  }

  .item-footer {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
}
</style>
