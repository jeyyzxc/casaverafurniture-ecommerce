<template>
  <div class="home-page">
    <!-- ═══════════════════════════════════════════════════
         HERO SECTION
         ═══════════════════════════════════════════════════ -->
    <HeroSection
      title="Timeless Elegance"
      subtitle="Discover furniture that blends modern sophistication with classic comfort. Elevate your living space with CASA VÉRA."
      size="full"
    >
      <router-link to="/products" class="btn-hero">
        <span>Explore Collection</span>
        <svg class="icon-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
      </router-link>
    </HeroSection>

    <!-- ═══════════════════════════════════════════════════
         THE SPOTLIGHT SECTION
         ═══════════════════════════════════════════════════ -->
    <section class="section spotlight-section">
      <div class="section-header rise-up">
        <span class="section-label">Exclusive Designs</span>
        <h2 class="section-title">The Spotlight</h2>
        <div class="section-divider"></div>
      </div>

      <div class="film-roll-container rise-up-delay-1">
        <div 
          class="film-track" 
          :class="{ 'no-transition': isResetting }"
          ref="filmTrack"
          :style="{ transform: `translateX(${filmPosition}px)` }"
          @transitionend="onFilmTransitionEnd"
        >
          <div 
            v-for="(item, index) in filmCards" 
            :key="`film-${index}`"
            class="film-card"
            :class="{ active: index === currentFilmIndex }"
          >
            <router-link :to="`/products/${item.slug}`" class="film-link" :aria-label="'View ' + item.name">
              <div class="film-img-box">
                <img :src="item.image || '/images/products/placeholder.png'" :alt="item.name" loading="lazy">
              </div>
            </router-link>
            <div class="film-details">
              <h4>{{ item.name }}</h4>
              <p class="film-price">₱{{ formatPrice(item.sale_price || item.price) }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════
         SIGNATURE COLLECTION SECTION
         ═══════════════════════════════════════════════════ -->
    <section class="signature-section">
      <div class="signature-container">
        <header class="signature-header rise-up">
          <h2 class="signature-title">Signature Collection</h2>
          <p class="signature-subtitle">Handpicked favorites for your home.</p>
        </header>

        <div class="signature-grid">
          <article 
            v-for="(product, index) in signatureProducts" 
            :key="product.id" 
            class="signature-card"
            :class="`rise-up-delay-${Math.min(index + 1, 5)}`"
          >
            <div class="signature-card-image">
              <span v-if="product.is_featured" class="signature-badge">Featured</span>
              <router-link :to="`/products/${product.slug}`" class="signature-link">
                <img :src="product.image || '/images/products/placeholder.png'" :alt="product.name" loading="lazy">
              </router-link>
              <div class="signature-actions">
                <button 
                  class="action-btn"
                  :disabled="isAddingToCart && addingProductId === product.id"
                  @click="addToCart(product)"
                  aria-label="Add to Cart"
                >
                  <svg v-if="addingProductId === product.id" class="action-spinner" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="32" stroke-linecap="round"/>
                  </svg>
                  <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 6h15l-1.5 9h-12z"/>
                    <circle cx="9" cy="20" r="1"/>
                    <circle cx="18" cy="20" r="1"/>
                    <path d="M6 6L4 2H1"/>
                  </svg>
                </button>
                <button class="action-btn" @click.stop="openQuickView(product)" aria-label="Quick View">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                </button>
                <button 
                  class="action-btn" 
                  :class="{ 'in-wishlist': isInWishlist(product.id) }"
                  :disabled="isToggling === product.id"
                  @click.stop="toggleWishlist(product.id)"
                  aria-label="Add to Wishlist"
                >
                  <svg v-if="isToggling === product.id" class="action-spinner" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="32" stroke-linecap="round"/>
                  </svg>
                  <svg v-else viewBox="0 0 24 24" :fill="isInWishlist(product.id) ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                  </svg>
                </button>
              </div>
            </div>
            <div class="signature-card-info">
              <h3 class="signature-card-name">{{ product.name }}</h3>
              <p class="signature-card-price" :class="{ 'sale': product.sale_price }">₱{{ formatPrice(product.sale_price || product.price) }}</p>
            </div>
          </article>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════
         CURATED SPACES SECTION
         ═══════════════════════════════════════════════════ -->
    <section class="section spaces-section">
      <div class="section-inner">
        <div class="section-header section-header--split rise-up">
          <div>
            <h2 class="section-title">Curated Spaces</h2>
            <p class="section-desc">Design your perfect sanctuary, room by room.</p>
          </div>
          <router-link to="/products" class="view-all-link">
            <span>View All</span>
            <svg class="icon-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
          </router-link>
        </div>

        <div class="spaces-grid">
          <router-link 
            v-for="(space, key) in spaces" 
            :key="key"
            :to="space.link" 
            class="space-card"
            :class="[space.size, `rise-up-delay-${Object.keys(spaces).indexOf(key) + 1}`]"
          >
            <div class="space-image" :style="{ backgroundImage: `url(${space.image})` }"></div>
            <div class="space-overlay">
              <div class="space-content">
                <span class="space-label">{{ space.label }}</span>
                <h3 class="space-title">{{ space.title }}</h3>
                <span class="space-cta">
                  Explore
                  <svg class="icon-arrow-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                  </svg>
                </span>
              </div>
            </div>
          </router-link>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════
         QUICK VIEW MODAL
         ═══════════════════════════════════════════════════ -->
    <Teleport to="body">
      <div class="quick-view-overlay" :class="{ active: quickViewOpen }" @click.self="closeQuickView">
        <div class="quick-view-card">
          <button class="close-btn" @click="closeQuickView">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>

          <div class="qv-image-col">
            <span v-if="quickViewProduct?.is_new" class="qv-badge">New</span>
            <img :src="quickViewProduct?.image || '/images/products/placeholder.png'" :alt="quickViewProduct?.name" class="qv-img">
          </div>

          <div class="qv-content-col">
            <div class="qv-details">
              <span class="qv-category">{{ quickViewProduct?.category_name || quickViewProduct?.category || 'Uncategorized' }}</span>
              <h2 class="qv-name">{{ quickViewProduct?.name }}</h2>
              <p class="qv-desc">{{ quickViewProduct?.description || 'Experience luxury with this handcrafted piece, designed to bring elegance and comfort to your home.' }}</p>
            </div>

            <div class="qv-price-area">
              <div class="qv-price-wrapper">
                <h3 v-if="quickViewProduct?.sale_price" class="qv-price sale">₱{{ formatPrice(quickViewProduct.sale_price) }}</h3>
                <h3 v-else class="qv-price">₱{{ formatPrice(quickViewProduct?.price || 0) }}</h3>
                <p v-if="quickViewProduct?.sale_price" class="qv-price-original">₱{{ formatPrice(quickViewProduct.price) }}</p>
              </div>
              <div class="qv-stock-info">
                <span class="qv-stock-badge" :class="quickViewProduct ? getStockBadgeClass(quickViewProduct) : ''">
                  {{ quickViewProduct ? getStockStatusText(quickViewProduct) : 'In Stock' }}
                </span>
                <span v-if="quickViewProduct && !isOutOfStock(quickViewProduct)" class="qv-stock-count">
                  {{ getStockQuantity(quickViewProduct) }} left
                </span>
              </div>
            </div>

            <div class="qv-buttons" v-if="quickViewProduct && !isOutOfStock(quickViewProduct)">
              <button class="qv-btn qv-btn-cart" @click="addToCartFromModal">
                <svg class="qv-btn-icon" viewBox="0 0 576 512" fill="currentColor">
                  <path d="M0 24C0 10.7 10.7 0 24 0H69.5c22 0 41.5 12.8 50.6 32h411c26.3 0 45.5 25 38.6 50.4l-41 152.3c-8.5 31.4-37 53.3-69.5 53.3H170.7l5.4 28.5c2.2 11.3 12.1 19.5 23.6 19.5H488c13.3 0 24 10.7 24 24s-10.7 24-24 24H199.7c-11.5 0-21.4-8.2-23.6-19.5L154.7 384H128.1c-35.3 0-64-28.7-64-64L0 24zM176 512a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm336-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0z"/>
                </svg>
                <span>Add to Cart</span>
              </button>
              <button class="qv-btn qv-btn-buy" @click="buyNowFromModal">
                <svg class="qv-btn-icon" viewBox="0 0 448 512" fill="currentColor">
                  <path d="M160 112c0-35.3 28.7-64 64-64s64 28.7 64 64v48H160V112zm-48 48H48c-26.5 0-48 21.5-48 48V416c0 53 43 96 96 96H352c53 0 96-43 96-96V208c0-26.5-21.5-48-48-48H336V112C336 50.1 285.9 0 224 0S112 50.1 112 112v48zm24 96a24 24 0 1 1 0 48 24 24 0 1 1 0-48zm152 24a24 24 0 1 1 48 0 24 24 0 1 1 -48 0z"/>
                </svg>
                <span>Buy Now</span>
              </button>
            </div>
            <div v-else class="qv-out-of-stock-message">
              <p>This item is currently out of stock. Please check back later.</p>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { Teleport } from 'vue'
import HeroSection from '@/components/HeroSection.vue'
import { useRealtimeHomepage } from '@/composables/useRealtimeHomepage'
import { useRealtimeProducts } from '@/composables/useRealtimeProducts'
import { useCartStore } from '@/stores/cart'
import { useWishlist } from '@/composables/useWishlist'
import { products as productsApi } from '@/services/clientApi'

const cartStore = useCartStore()
const { isInWishlist, toggleWishlist, isToggling, loadWishlist } = useWishlist()
const { startListening: startProductListening, stopListening: stopProductListening } = useRealtimeProducts()

// ═══════════════════════════════════════════════════
// IMAGE IMPORTS
// ═══════════════════════════════════════════════════
import livingRoomImg from '@/assets/images/furni5.png'
import diningImg from '@/assets/images/furni4.png'
import bedroomImg from '@/assets/images/f4.png'
import officeImg from '@/assets/images/f5.png'

// ═══════════════════════════════════════════════════
// PRODUCT INTERFACE
// ═══════════════════════════════════════════════════
interface Product {
  id: number
  name: string
  slug: string
  category?: string
  category_name?: string
  price: number
  sale_price?: number | null
  image?: string
  primary_image?: string
  is_new?: boolean
  is_featured?: boolean
  is_bestseller?: boolean
  description?: string
  stock?: number
  stock_quantity?: number
  stock_status?: string
  low_stock_threshold?: number
  track_inventory?: boolean
  description?: string
  stock?: number
}

// ═══════════════════════════════════════════════════
// FILM ROLL CAROUSEL (EXCLUSIVE DESIGNS)
// ═══════════════════════════════════════════════════
const spotlightItems = ref<Product[]>([])

const cardWidth = 300
const gap = 40
const slideStep = cardWidth + gap

const filmCards = computed(() => {
  const items = spotlightItems.value
  if (items.length === 0) {
    // Return empty array - carousel will be hidden until items load
    return []
  }
  // Create enough copies for seamless infinite loop (at least 5 copies for smooth looping)
  // This ensures we have enough items on both sides for seamless transitions
  return [...items, ...items, ...items, ...items, ...items, ...items]
})

const totalReal = computed(() => spotlightItems.value.length)
const startIndex = computed(() => totalReal.value * 2) // Start in the third copy for better buffer
const currentFilmIndex = ref(0)
const filmPosition = ref(0)
const isFilmAnimating = ref(false)
const isResetting = ref(false)
const filmTrack = ref<HTMLElement | null>(null)
let filmInterval: ReturnType<typeof setInterval> | null = null

const centerOffset = -(cardWidth / 2)

const moveToFilmIndex = (index: number, animate = true) => {
  // Check if filmTrack ref is available and we have real items (not placeholders)
  if (!filmTrack.value || totalReal.value === 0 || spotlightItems.value.length === 0) {
    return
  }
  
  if (!animate) {
    isResetting.value = true
  }
  
  filmPosition.value = centerOffset - (index * slideStep)
  currentFilmIndex.value = index
  isFilmAnimating.value = animate
  
  if (!animate) {
    // Force reflow to ensure the transition is disabled
    void filmTrack.value.offsetWidth
    // Re-enable transition after a brief moment using double RAF for better browser compatibility
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        if (filmTrack.value) {
          isResetting.value = false
        }
      })
    })
  }
}

const onFilmTransitionEnd = () => {
  if (totalReal.value === 0 || spotlightItems.value.length === 0) return
  
  isFilmAnimating.value = false
  const total = totalReal.value
  
  // When we reach near the end of the fourth copy, seamlessly reset to the start of the third copy
  // This ensures we're always in the middle copies, giving us buffer on both sides
  if (currentFilmIndex.value >= total * 4) {
    // Reset to equivalent position in the third copy (invisible jump)
    moveToFilmIndex(currentFilmIndex.value - total * 2, false)
  } else if (currentFilmIndex.value < total * 2) {
    // If we somehow go backwards too far, reset to third copy
    moveToFilmIndex(currentFilmIndex.value + total * 2, false)
  }
}

const startFilmCarousel = () => {
  // Ensure filmTrack ref is available and we have real items (not placeholders) before starting carousel
  if (!filmTrack.value || totalReal.value === 0 || spotlightItems.value.length === 0) {
    // Retry immediately using requestAnimationFrame for faster initialization
    if (filmTrack.value && spotlightItems.value.length === 0) {
      // Items are still loading, retry soon
      requestAnimationFrame(() => {
        if (filmTrack.value && spotlightItems.value.length > 0 && totalReal.value > 0) {
          startFilmCarousel()
        }
      })
    }
    return
  }
  
  // Clear any existing interval
  if (filmInterval) {
    clearInterval(filmInterval)
  }
  
  // Initialize to start position (third copy) immediately without delay
  moveToFilmIndex(startIndex.value, false)
  
  // Start the carousel loop immediately using requestAnimationFrame
  requestAnimationFrame(() => {
    filmInterval = setInterval(() => {
      if (!isFilmAnimating.value && !document.hidden && filmTrack.value && totalReal.value > 0 && spotlightItems.value.length > 0) {
        isFilmAnimating.value = true
        currentFilmIndex.value++
        moveToFilmIndex(currentFilmIndex.value, true)
      }
    }, 4000)
  })
}

// ═══════════════════════════════════════════════════
// SIGNATURE COLLECTION
// ═══════════════════════════════════════════════════
const signatureProducts = ref<Product[]>([])

// ═══════════════════════════════════════════════════
// CURATED SPACES
// ═══════════════════════════════════════════════════
const spaces = ref({
  living: { title: 'Living Room', label: 'Gather & Relax', image: livingRoomImg, link: '/products?cat=living', size: 'large' },
  dining: { title: 'Dining', label: 'Feast in Style', image: diningImg, link: '/products?cat=dining', size: '' },
  bedroom: { title: 'Bedroom', label: 'Dream & Rest', image: bedroomImg, link: '/products?cat=bedroom', size: '' },
  office: { title: 'Office & Decor', label: 'Work & Inspire', image: officeImg, link: '/products?cat=office', size: 'large' },
})

// ═══════════════════════════════════════════════════
// QUICK VIEW
// ═══════════════════════════════════════════════════
const quickViewOpen = ref(false)
const quickViewProduct = ref<Product | null>(null)
const isLoadingQuickView = ref(false)

const openQuickView = async (product: Product) => {
  // If product doesn't have description, fetch full details
  if (!product.description) {
    isLoadingQuickView.value = true
    try {
      const response = await productsApi.get(product.slug)
      if (response.data.success && response.data.data?.product) {
        const fullProduct = response.data.data.product
        quickViewProduct.value = {
          ...product,
          description: fullProduct.description || fullProduct.short_description || product.description,
          stock_quantity: fullProduct.stock_quantity ?? product.stock_quantity,
          stock_status: fullProduct.stock_status || product.stock_status,
          low_stock_threshold: fullProduct.low_stock_threshold ?? product.low_stock_threshold,
          track_inventory: fullProduct.track_inventory ?? product.track_inventory,
        } as Product
      } else {
        quickViewProduct.value = product
      }
    } catch (error) {
      console.error('Failed to load product details:', error)
      quickViewProduct.value = product
    } finally {
      isLoadingQuickView.value = false
    }
  } else {
    quickViewProduct.value = product
  }
  
  quickViewOpen.value = true
  document.body.style.overflow = 'hidden'
}

const closeQuickView = () => {
  quickViewOpen.value = false
  document.body.style.overflow = ''
  // Clear product after animation
  setTimeout(() => {
    quickViewProduct.value = null
  }, 300)
}

// ═══════════════════════════════════════════════════
// CART FUNCTIONALITY
// ═══════════════════════════════════════════════════
const isAddingToCart = ref(false)
const addingProductId = ref<number | null>(null)

const addToCart = async (product: Product) => {
  if (isAddingToCart.value) return
  
  isAddingToCart.value = true
  addingProductId.value = product.id
  
  try {
    const result = await cartStore.addItem(product.id, 1)
    if (result.success) {
      console.log('Added to cart:', product.name)
    } else {
      alert(result.message || 'Failed to add item to cart')
    }
  } catch (error) {
    console.error('Failed to add to cart:', error)
    alert('Failed to add item to cart. Please try again.')
  } finally {
    isAddingToCart.value = false
    addingProductId.value = null
  }
}

const addToCartFromModal = () => {
  if (quickViewProduct.value) {
    addToCart(quickViewProduct.value)
    closeQuickView()
  }
}

const buyNowFromModal = async () => {
  if (!quickViewProduct.value) return
  
  isAddingToCart.value = true
  addingProductId.value = quickViewProduct.value.id
  
  try {
    const result = await cartStore.addItem(quickViewProduct.value.id, 1)
    if (result.success) {
      const { useRouter } = await import('vue-router')
      const router = useRouter()
      router.push('/checkout')
      closeQuickView()
    } else {
      alert(result.message || 'Failed to add item to cart')
    }
  } catch (error) {
    console.error('Failed to add to cart:', error)
    alert('Failed to add item to cart. Please try again.')
  } finally {
    isAddingToCart.value = false
    addingProductId.value = null
  }
}

// ═══════════════════════════════════════════════════
// STOCK STATUS HELPERS
// ═══════════════════════════════════════════════════
const getStockQuantity = (product: Product): number => {
  return product.stock_quantity ?? product.stock ?? 0
}

const getLowStockThreshold = (product: Product): number => {
  return product.low_stock_threshold ?? 5
}

const isOutOfStock = (product: Product): boolean => {
  if (product.track_inventory === false) {
    return false // If not tracking inventory, always available
  }
  if (!product.track_inventory) {
    return false // Default to in stock if tracking is not specified
  }
  const stock = getStockQuantity(product)
  return stock <= 0 || product.stock_status === 'out_of_stock'
}

const isLowStock = (product: Product): boolean => {
  if (!product.track_inventory || isOutOfStock(product)) {
    return false
  }
  const stock = getStockQuantity(product)
  const threshold = getLowStockThreshold(product)
  return stock > 0 && stock <= threshold
}

const getStockStatusText = (product: Product): string => {
  if (isOutOfStock(product)) {
    return 'Out of Stock'
  }
  if (isLowStock(product)) {
    return 'Low Stock'
  }
  return 'In Stock'
}

const getStockBadgeClass = (product: Product): string => {
  if (isOutOfStock(product)) {
    return 'stock-badge-out'
  }
  if (isLowStock(product)) {
    return 'stock-badge-low'
  }
  return 'stock-badge-in'
}

// ═══════════════════════════════════════════════════
// UTILITIES
// ═══════════════════════════════════════════════════
const formatPrice = (price: number) => {
  return price.toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

// ═══════════════════════════════════════════════════
// LOAD PRODUCTS
// ═══════════════════════════════════════════════════
const loadFeaturedProducts = async () => {
  try {
    const response = await productsApi.list({
      featured: true,
      per_page: 10,
      sort_by: 'newest',
      sort_order: 'desc'
    })
    
    if (response.data.success) {
      const responseData = response.data.data
      const products: Array<Record<string, unknown>> = Array.isArray(responseData) 
        ? responseData 
        : (responseData?.data || [])
      
      spotlightItems.value = products.map((p) => {
        const product = p as {
          id: number
          name: string
          slug: string
          price: number | string
          sale_price?: number | string | null
          image?: string | null
          category?: { id: number; name: string; slug: string } | null
          is_new?: boolean
          is_featured?: boolean
          description?: string
          stock_quantity?: number
          stock?: number
          stock_status?: string
          low_stock_threshold?: number
          track_inventory?: boolean
        }
        
        return {
          id: product.id,
          name: product.name,
          slug: product.slug,
          category: product.category?.slug,
          category_name: product.category?.name,
          price: typeof product.price === 'string' ? parseFloat(product.price) : (typeof product.price === 'number' ? product.price : 0),
          sale_price: product.sale_price ? (typeof product.sale_price === 'string' ? parseFloat(product.sale_price) : (typeof product.sale_price === 'number' ? product.sale_price : null)) : null,
          image: product.image || '/images/products/placeholder.png',
          is_new: product.is_new || false,
          is_featured: product.is_featured || false,
          description: product.description,
          stock_quantity: product.stock_quantity,
          stock: product.stock,
          stock_status: product.stock_status,
          low_stock_threshold: product.low_stock_threshold,
          track_inventory: product.track_inventory,
        } as Product
      })
    }
  } catch (error) {
    console.error('Failed to load featured products:', error)
  }
}

const loadSignatureProducts = async () => {
  try {
    const response = await productsApi.list({
      featured: true,
      per_page: 5,
      sort_by: 'newest',
      sort_order: 'desc'
    })
    
    if (response.data.success) {
      const responseData = response.data.data
      const products: Array<Record<string, unknown>> = Array.isArray(responseData) 
        ? responseData 
        : (responseData?.data || [])
      
      signatureProducts.value = products.map((p) => {
        const product = p as {
          id: number
          name: string
          slug: string
          price: number | string
          sale_price?: number | string | null
          image?: string | null
          category?: { id: number; name: string; slug: string } | null
          is_new?: boolean
          is_featured?: boolean
          description?: string
          stock_quantity?: number
          stock?: number
          stock_status?: string
          low_stock_threshold?: number
          track_inventory?: boolean
        }
        
        return {
          id: product.id,
          name: product.name,
          slug: product.slug,
          category: product.category?.slug,
          category_name: product.category?.name,
          price: typeof product.price === 'string' ? parseFloat(product.price) : (typeof product.price === 'number' ? product.price : 0),
          sale_price: product.sale_price ? (typeof product.sale_price === 'string' ? parseFloat(product.sale_price) : (typeof product.sale_price === 'number' ? product.sale_price : null)) : null,
          image: product.image || '/images/products/placeholder.png',
          is_new: product.is_new || false,
          is_featured: product.is_featured || false,
          description: product.description,
          stock_quantity: product.stock_quantity,
          stock: product.stock,
          stock_status: product.stock_status,
          low_stock_threshold: product.low_stock_threshold,
          track_inventory: product.track_inventory,
        } as Product
      })
    }
  } catch (error) {
    console.error('Failed to load signature products:', error)
  }
}

// ═══════════════════════════════════════════════════
// WATCH FOR ITEMS CHANGES
// ═══════════════════════════════════════════════════
watch(spotlightItems, (newItems) => {
  if (newItems.length > 0) {
    // Clear existing interval
    if (filmInterval) {
      clearInterval(filmInterval)
    }
    // Start carousel immediately - no delays
    nextTick().then(() => {
      startFilmCarousel()
    })
  }
}, { immediate: false })

// ═══════════════════════════════════════════════════
// SCROLL ANIMATIONS
// ═══════════════════════════════════════════════════
let scrollObserver: IntersectionObserver | null = null

const initScrollAnimations = () => {
  scrollObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('in-view')
        }
      })
    },
    { threshold: 0.1, rootMargin: '0px 0px -50px 0px' }
  )

  document.querySelectorAll('.section').forEach(el => scrollObserver?.observe(el))
}

// ═══════════════════════════════════════════════════
// LIFECYCLE
// ═══════════════════════════════════════════════════
// Real-time homepage updates
const { startListening, stopListening } = useRealtimeHomepage()

const handleHomepageUpdate = (event: Event) => {
  const customEvent = event as CustomEvent
  const updateData = customEvent.detail
  console.log('Homepage updated:', updateData.type)
  
  // Reload homepage data when updates occur
  if (updateData.type === 'featured_products' || updateData.type === 'product') {
    // Reload signature collection when featured products change
    loadSignatureProducts()
    // Also reload spotlight if it's a featured product update
    if (updateData.type === 'featured_products') {
      loadFeaturedProducts()
    }
  } else if (updateData.type === 'banner' || updateData.type === 'section') {
    // Handle other updates as needed
  }
}

const handleProductUpdated = (event: Event) => {
  const customEvent = event as CustomEvent<{
    id: number
    is_featured?: boolean
    [key: string]: any
  }>
  const productData = customEvent.detail
  if (!productData) return
  
  // If featured status changed, reload signature products
  const existingProduct = signatureProducts.value.find(p => p.id === productData.id)
  if (existingProduct && existingProduct.is_featured !== productData.is_featured) {
    // Featured status changed, reload signature products
    loadSignatureProducts()
  }
}

onMounted(async () => {
  // Set up real-time listeners
  startListening()
  startProductListening()
  window.addEventListener('realtime:homepage:updated', handleHomepageUpdate)
  window.addEventListener('realtime:product:updated', handleProductUpdated)
  
  // Load wishlist if user is authenticated
  loadWishlist()
  
  // Start scroll animations immediately (don't wait for products)
  initScrollAnimations()
  
  // Load products in parallel - don't await, let them load in background
  Promise.all([
    loadFeaturedProducts(),
    loadSignatureProducts()
  ]).then(() => {
    // When products load, start carousel immediately
    if (spotlightItems.value.length > 0) {
      nextTick().then(() => {
        requestAnimationFrame(() => {
          startFilmCarousel()
        })
      })
    }
  })
  
  // Start carousel immediately if products are already loaded (from cache or previous load)
  if (spotlightItems.value.length > 0) {
    await nextTick()
    requestAnimationFrame(() => {
      startFilmCarousel()
    })
  }
})

onUnmounted(() => {
  stopListening()
  stopProductListening()
  window.removeEventListener('realtime:homepage:updated', handleHomepageUpdate)
  window.removeEventListener('realtime:product:updated', handleProductUpdated)
  if (filmInterval) clearInterval(filmInterval)
  scrollObserver?.disconnect()
})
</script>

<style scoped>
/* ═══════════════════════════════════════════════════
   CSS CUSTOM PROPERTIES
   ═══════════════════════════════════════════════════ */
.home-page {
  --gold: #c9a050;
  --gold-light: #FFD700;
  --gold-dark: #a6833e;
  --dark: #1a1a1a;
  --dark-soft: #2d2d2d;
  --light: #f8f6f3;
  --white: #ffffff;
  --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.08);
  --shadow-md: 0 10px 30px rgba(0, 0, 0, 0.12);
  --shadow-lg: 0 20px 50px rgba(0, 0, 0, 0.15);
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 20px;
  --transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  overflow-x: hidden;
  width: 100%;
}

/* Hide scrollbar but allow scrolling */
.home-page::-webkit-scrollbar {
  display: none;
}

.home-page {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

/* ═══════════════════════════════════════════════════
   SVG ICONS
   ═══════════════════════════════════════════════════ */
.icon,
.icon-arrow,
.icon-arrow-sm {
  width: 1em;
  height: 1em;
  flex-shrink: 0;
}

.icon-arrow {
  width: 1.25rem;
  height: 1.25rem;
}

.icon-arrow-sm {
  width: 1rem;
  height: 1rem;
}

.icon-spinner {
  width: 1.25rem;
  height: 1.25rem;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* ═══════════════════════════════════════════════════
   LAYOUT UTILITIES
   ═══════════════════════════════════════════════════ */
.section {
  padding: 80px 0;
  opacity: 0;
  transform: translateY(30px);
  transition: opacity 0.8s ease, transform 0.8s ease;
}

.section.in-view {
  opacity: 1;
  transform: translateY(0);
}

.section-inner {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 3rem;
}

.section-header {
  text-align: center;
  margin-bottom: 3rem;
}

.section-header--split {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  text-align: left;
  flex-wrap: wrap;
  gap: 1rem;
}

.section-label {
  display: inline-block;
  color: var(--gold);
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 3px;
  margin-bottom: 0.75rem;
}

.section-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(2rem, 5vw, 2.75rem);
  font-weight: 600;
  color: var(--dark);
  margin: 0;
  line-height: 1.2;
}

.section-desc {
  color: #666;
  font-size: 1rem;
  margin-top: 0.5rem;
}

.section-divider {
  width: 60px;
  height: 3px;
  background: linear-gradient(90deg, var(--gold), var(--gold-light));
  margin: 1.25rem auto 0;
  border-radius: 2px;
}

/* ═══════════════════════════════════════════════════
   1. HERO BUTTON (for slot content)
   ═══════════════════════════════════════════════════ */
.btn-hero {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  margin-top: 1.5rem;
  padding: 1rem 2.25rem;
  background: transparent;
  border: 2px solid var(--gold);
  color: var(--white);
  font-size: 0.85rem;
  font-weight: 600;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  text-decoration: none;
  border-radius: 50px;
  transition: var(--transition);
  overflow: hidden;
  position: relative;
}

.btn-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background: var(--gold);
  transform: scaleX(0);
  transform-origin: right;
  transition: transform 0.4s ease;
  z-index: -1;
}

.btn-hero:hover {
  color: var(--dark);
  border-color: var(--gold);
  transform: translateY(-3px);
  box-shadow: 0 15px 35px rgba(201, 160, 80, 0.35);
}

.btn-hero:hover::before {
  transform: scaleX(1);
  transform-origin: left;
}

.btn-hero svg {
  transition: transform 0.3s ease;
}

.btn-hero:hover svg {
  transform: translateX(4px);
}

/* ═══════════════════════════════════════════════════
   2. SPOTLIGHT SECTION
   ═══════════════════════════════════════════════════ */
.spotlight-section {
  background: var(--light);
}

.film-roll-container {
  position: relative;
  width: 100%;
  height: 500px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  -webkit-mask-image: linear-gradient(to right, transparent 0%, black 10%, black 90%, transparent 100%);
  mask-image: linear-gradient(to right, transparent 0%, black 10%, black 90%, transparent 100%);
  opacity: 1;
  animation: none;
}


.film-track {
  display: flex;
  position: absolute;
  left: 50%;
  will-change: transform;
  transition: transform 0.9s cubic-bezier(0.25, 1, 0.5, 1);
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
  transform: translateZ(0);
  -webkit-transform: translateZ(0);
  perspective: 1000px;
  -webkit-perspective: 1000px;
}

.film-track.no-transition {
  transition: none !important;
}

.film-card {
  flex: 0 0 300px;
  width: 300px;
  height: 380px;
  margin-right: 40px;
  position: relative;
  transition: all 0.7s cubic-bezier(0.4, 0, 0.2, 1);
  opacity: 0.35;
  transform: scale(0.85) translateY(20px);
  filter: grayscale(100%) brightness(0.8);
}

.film-card-placeholder {
  opacity: 0.1;
  pointer-events: none;
}

.film-card.active {
  opacity: 1;
  transform: scale(1.08) translateY(0);
  z-index: 10;
  filter: grayscale(0%) brightness(1);
}

.film-link {
  display: block;
  height: 100%;
  border-radius: var(--radius-md);
  overflow: hidden;
  box-shadow: var(--shadow-md);
  transition: box-shadow 0.4s ease;
}

.film-card.active .film-link {
  box-shadow: 0 30px 60px rgba(0, 0, 0, 0.25);
}

.film-img-box {
  width: 100%;
  height: 100%;
}

.film-img-box img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.film-details {
  position: absolute;
  bottom: 20px;
  left: 0;
  right: 0;
  text-align: center;
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.5s ease;
  z-index: 5;
}

.film-card.active .film-details {
  opacity: 1;
  transform: translateY(0);
}

.film-details h4 {
  font-family: 'Playfair Display', serif;
  font-size: 1.1rem;
  color: var(--white);
  margin-bottom: 0.25rem;
  background: rgba(0, 0, 0, 0.7);
  padding: 8px 16px;
  border-radius: 20px;
  display: inline-block;
  backdrop-filter: blur(10px);
}

.film-price {
  color: var(--white);
  font-weight: 700;
  font-size: 1rem;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
}

/* ═══════════════════════════════════════════════════
   3. SIGNATURE COLLECTION
   ═══════════════════════════════════════════════════ */
.signature-section {
  background: var(--white);
  padding: 70px 0;
}

.signature-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
}

.signature-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.signature-title {
  font-family: 'Playfair Display', serif;
  font-size: 2.25rem;
  font-weight: 700;
  color: var(--dark);
  margin: 0 0 0.5rem;
}

.signature-subtitle {
  color: #666;
  font-size: 1rem;
  margin: 0;
}

.signature-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 1.25rem;
}

.signature-card {
  background: var(--white);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.signature-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
}

.signature-card-image {
  position: relative;
  aspect-ratio: 1;
  overflow: hidden;
  background: #f8f8f8;
}

.signature-card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.signature-card:hover .signature-card-image img {
  transform: scale(1.06);
}

.signature-link {
  display: block;
  width: 100%;
  height: 100%;
}

.signature-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  z-index: 5;
  padding: 5px 12px;
  background: linear-gradient(135deg, var(--gold-light), #FF8C00);
  color: white;
  font-size: 0.6rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-radius: 20px;
}

.signature-actions {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%) translateX(15px);
  display: flex;
  flex-direction: column;
  gap: 6px;
  opacity: 0;
  transition: all 0.35s ease;
}

.signature-card:hover .signature-actions {
  opacity: 1;
  transform: translateY(-50%) translateX(0);
}

.action-btn {
  width: 34px;
  height: 34px;
  border: none;
  border-radius: 50%;
  background: var(--white);
  color: var(--dark);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.25s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}

.action-btn svg {
  width: 15px;
  height: 15px;
}

.action-btn:hover {
  background: var(--gold);
  color: var(--white);
  transform: scale(1.08);
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.action-btn.in-wishlist {
  background: #ff6b6b;
  color: white;
}

.action-btn.in-wishlist:hover {
  background: #ff5252;
  transform: scale(1.1);
}

.action-spinner {
  animation: spin 1s linear infinite;
}

.signature-card-info {
  padding: 0.875rem 1rem;
  text-align: center;
}

.signature-card-name {
  font-family: 'Playfair Display', serif;
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--dark);
  margin: 0 0 0.25rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.signature-card-price {
  color: #000000;
  font-weight: 600;
  font-size: 0.85rem;
  margin: 0;
}

.signature-card-price.sale {
  color: #000000;
}

/* ═══════════════════════════════════════════════════
   4. CURATED SPACES
   ═══════════════════════════════════════════════════ */
.spaces-section {
  background: var(--light);
}

.view-all-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--gold);
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 2px;
  text-decoration: none;
  transition: var(--transition);
}

.view-all-link:hover {
  color: var(--gold-dark);
}

.view-all-link svg {
  transition: transform 0.3s ease;
}

.view-all-link:hover svg {
  transform: translateX(5px);
}

.spaces-grid {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  gap: 1.5rem;
}

.space-card {
  position: relative;
  border-radius: var(--radius-md);
  overflow: hidden;
  height: 350px;
  text-decoration: none;
  grid-column: span 6;
}

.space-card.large {
  grid-column: span 8;
}

.space-card:nth-child(2),
.space-card:nth-child(3) {
  grid-column: span 4;
}

.space-image {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  transition: transform 0.7s cubic-bezier(0.4, 0, 0.2, 1);
}

.space-card:hover .space-image {
  transform: scale(1.1);
}

.space-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.85) 0%, rgba(0, 0, 0, 0.2) 50%, transparent 100%);
  display: flex;
  align-items: flex-end;
  padding: 2rem;
  border: 2px solid transparent;
  transition: var(--transition);
}

.space-card:hover .space-overlay {
  border-color: var(--gold);
}

.space-content {
  transform: translateY(10px);
  transition: transform 0.4s ease;
}

.space-card:hover .space-content {
  transform: translateY(0);
}

.space-label {
  color: var(--gold);
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  display: block;
  margin-bottom: 0.5rem;
  opacity: 0;
  transform: translateY(10px);
  transition: all 0.4s ease 0.1s;
}

.space-card:hover .space-label {
  opacity: 1;
  transform: translateY(0);
}

.space-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.75rem;
  color: var(--white);
  margin: 0 0 0.75rem;
}

.space-cta {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--white);
  font-size: 0.8rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 1px;
  opacity: 0;
  transform: translateY(10px);
  transition: all 0.4s ease 0.15s;
}

.space-card:hover .space-cta {
  opacity: 1;
  transform: translateY(0);
}

.space-cta svg {
  transition: transform 0.3s ease;
}

.space-card:hover .space-cta svg {
  transform: translateX(5px);
}

/* ═══════════════════════════════════════════════════
   5. RESPONSIVE DESIGN
   ═══════════════════════════════════════════════════ */
@media (max-width: 1400px) {
  .signature-container {
    padding: 0 1.5rem;
  }

  .signature-grid {
    gap: 1rem;
  }
}

@media (max-width: 1200px) {
  .signature-grid {
    grid-template-columns: repeat(5, 1fr);
    gap: 0.875rem;
  }

  .section-inner {
    padding: 0 2rem;
  }
}

@media (max-width: 991px) {
  .section {
    padding: 60px 0;
  }

  .signature-section {
    padding: 50px 0;
  }

  .signature-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
  }

  .signature-title {
    font-size: 1.875rem;
  }

  .spaces-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .space-card,
  .space-card.large,
  .space-card:nth-child(2),
  .space-card:nth-child(3) {
    grid-column: span 1;
    height: 280px;
  }
}

@media (max-width: 768px) {
  .section {
    padding: 50px 0;
  }

  .section-header--split {
    flex-direction: column;
    align-items: flex-start;
  }

  .section-inner {
    padding: 0 1.5rem;
  }

  .film-roll-container {
    height: 400px;
  }

  .film-card {
    flex: 0 0 240px;
    width: 240px;
    height: 320px;
    margin-right: 30px;
  }

  .signature-section {
    padding: 40px 0;
  }

  .signature-container {
    padding: 0 1rem;
  }

  .signature-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
  }

  .signature-title {
    font-size: 1.625rem;
  }

  .spaces-grid {
    grid-template-columns: 1fr;
  }

  .space-card,
  .space-card.large,
  .space-card:nth-child(2),
  .space-card:nth-child(3) {
    grid-column: span 1;
    height: 260px;
  }

  .scroll-indicator {
    display: none;
  }
}

@media (max-width: 576px) {
  .section {
    padding: 40px 0;
  }

  .film-card {
    flex: 0 0 200px;
    width: 200px;
    height: 280px;
    margin-right: 20px;
  }

  .film-details h4 {
    font-size: 0.9rem;
    padding: 6px 12px;
  }

  .film-price {
    font-size: 0.85rem;
  }

  .signature-header {
    margin-bottom: 1.75rem;
  }

  .signature-title {
    font-size: 1.5rem;
  }

  .signature-card-info {
    padding: 0.625rem 0.75rem;
  }

  .signature-card-name {
    font-size: 0.8rem;
  }

  .signature-card-price {
    font-size: 0.75rem;
  }

  .action-btn {
    width: 30px;
    height: 30px;
  }

  .action-btn svg {
    width: 13px;
    height: 13px;
  }
}

/* ═══════════════════════════════════════════════════
   6. QUICK VIEW MODAL
   ═══════════════════════════════════════════════════ */
.quick-view-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(5px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
}

.quick-view-overlay.active {
  opacity: 1;
  visibility: visible;
}

.quick-view-card {
  background: #ffffff;
  width: 900px;
  max-width: 95%;
  height: 520px;
  border-radius: 16px;
  display: flex;
  overflow: hidden;
  position: relative;
  transform: scale(0.95);
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.quick-view-overlay.active .quick-view-card {
  transform: scale(1);
}

.close-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  width: 40px;
  height: 40px;
  background: var(--white);
  border: none;
  border-radius: 50%;
  cursor: pointer;
  z-index: 20;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.close-btn svg {
  width: 20px;
  height: 20px;
  transition: transform 0.3s ease;
}

.close-btn:hover {
  background: #dc3545;
  color: var(--white);
  transform: scale(1.1) rotate(90deg);
  box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
}

.close-btn:active {
  transform: scale(0.95) rotate(90deg);
}

.close-btn::before {
  content: '';
  position: absolute;
  inset: -4px;
  border-radius: 50%;
  background: rgba(220, 53, 69, 0.1);
  opacity: 0;
  transition: opacity 0.3s ease, transform 0.3s ease;
  transform: scale(0.8);
}

.close-btn:hover::before {
  opacity: 1;
  transform: scale(1);
}

.qv-image-col {
  width: 50%;
  background: #ffffff;
  position: relative;
  overflow: hidden;
}

.qv-badge {
  position: absolute;
  top: 16px;
  left: 16px;
  z-index: 10;
  padding: 5px 12px;
  background: linear-gradient(135deg, #f0d890, #e6c866);
  color: #000000;
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1px;
  border-radius: 5px;
  box-shadow: 0 3px 10px rgba(230, 200, 102, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.5);
}

.qv-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.qv-content-col {
  width: 50%;
  padding: 2.5rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
  background: #ffffff;
}

.qv-details {
  margin-bottom: 1.5rem;
}

.qv-category {
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: #000000;
  display: block;
  margin-bottom: 0.75rem;
}

.qv-name {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0 0 1.25rem;
  line-height: 1.3;
}

.qv-desc {
  color: #555;
  line-height: 1.7;
  font-size: 1rem;
  margin: 0;
}

.qv-price-area {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin: 2rem 0;
  padding: 1.25rem 0;
  border-top: 1px solid #eee;
  border-bottom: 1px solid #eee;
}

.qv-price-wrapper {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.qv-price {
  font-size: 1.75rem;
  font-weight: 700;
  color: #000000;
  margin: 0;
  font-family: 'Playfair Display', serif;
}

.qv-price.sale {
  color: #000000;
}

.qv-price-original {
  font-size: 1rem;
  color: #dc3545;
  text-decoration: line-through;
  margin: 0;
}

.qv-stock-info {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.5rem;
}

.qv-stock-badge {
  background: #e3f2fd;
  color: #000000;
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.qv-stock-badge.stock-badge-out {
  background: #f8d7da;
  color: #000000;
}

.qv-stock-badge.stock-badge-low {
  background: #fff3e0;
  color: #000000;
}

.qv-stock-badge.stock-badge-in {
  background: #e3f2fd;
  color: #000000;
}

.qv-stock-count {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.95rem;
  font-weight: 600;
  color: #000000;
  font-family: 'Playfair Display', serif;
}

.qv-out-of-stock-message {
  padding: 1.5rem;
  text-align: center;
  background: #f8f9fa;
  border-radius: 12px;
  margin-top: 1rem;
}

.qv-out-of-stock-message p {
  color: #dc3545;
  font-weight: 600;
  margin: 0;
  font-size: 0.95rem;
}

.qv-buttons {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-top: auto;
}

.qv-btn {
  padding: 1rem 0;
  border-radius: 50px;
  border: none;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  font-weight: 700;
  cursor: pointer;
  transition: var(--transition);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.qv-btn-icon {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.qv-btn-cart {
  background: #1a1a1a;
  color: #ffffff;
}

.qv-btn-cart:hover {
  background: #000000;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.qv-btn-buy {
  background: linear-gradient(135deg, #c9a050, #b8860b);
  color: #1a1a1a;
  font-weight: 700;
  box-shadow: 0 4px 15px rgba(201, 160, 80, 0.3);
}

.qv-btn-buy:hover {
  background: linear-gradient(135deg, #b8860b, #9a7209);
  color: #ffffff;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(201, 160, 80, 0.5);
}

@media (max-width: 768px) {
  .quick-view-card {
    flex-direction: column;
    height: auto;
    max-height: 90vh;
    overflow-y: auto;
  }

  .qv-image-col,
  .qv-content-col {
    width: 100%;
  }

  .qv-image-col {
    height: 250px;
  }

  .qv-badge {
    top: 12px;
    left: 12px;
    padding: 4px 10px;
    font-size: 0.6rem;
  }

  .qv-content-col {
    padding: 1.75rem;
  }

  .qv-category {
    font-size: 0.75rem;
    margin-bottom: 0.5rem;
  }

  .qv-name {
    font-size: 1.5rem;
    margin-bottom: 1rem;
  }

  .qv-desc {
    font-size: 0.9rem;
    line-height: 1.6;
  }

  .qv-price {
    font-size: 1.5rem;
  }

  .qv-price-area {
    margin: 1.5rem 0;
    padding: 1rem 0;
  }

  .qv-buttons {
    gap: 0.75rem;
  }

  .qv-btn {
    padding: 0.75rem 0;
    font-size: 0.75rem;
  }
}
</style>
