<template>
  <div class="products-page">
    <HeroSection
      title="The Collection"
      subtitle="Curated pieces to elevate your modern living."
      size="large"
    />

    <section class="products-main">
      <div class="products-container">
        <div class="products-layout">

          <aside class="filter-sidebar rise-up">
            <div class="filter-header">
              <h5 class="filter-title">Filters</h5>
              <button class="reset-btn" @click="resetFilters" title="Reset All">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 4v6h6M23 20v-6h-6"/>
                  <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/>
                </svg>
              </button>
            </div>

            <div class="filter-group">
              <label class="filter-label">Search</label>
              <div class="search-input-wrap">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="11" cy="11" r="8"/>
                  <path d="m21 21-4.35-4.35"/>
                </svg>
                <input
                  type="text"
                  v-model="searchQuery"
                  class="search-input"
                  placeholder="Find your piece..."
                >
              </div>
            </div>

            <div class="filter-divider"></div>

            <div class="filter-group">
              <div class="filter-toggle" @click="toggleCategory">
                <label class="filter-label">Category</label>
                <svg :class="['toggle-arrow', { rotated: !categoryOpen }]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="m6 9 6 6 6-6"/>
                </svg>
              </div>
              <div class="category-list" v-show="categoryOpen">
                <div
                  v-for="cat in categoryOptions"
                  :key="cat.value"
                  :class="['category-item', { active: selectedCategory === cat.value }]"
                  @click="selectCategory(cat.value)"
                >
                  <span>{{ cat.label }}</span>
                  <svg v-if="selectedCategory === cat.value" class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                </div>
              </div>
            </div>

            <div class="filter-divider"></div>

            <div class="filter-group">
              <div class="filter-toggle" @click="togglePrice">
                <label class="filter-label">Price Range</label>
                <svg :class="['toggle-arrow', { rotated: !priceOpen }]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="m6 9 6 6 6-6"/>
                </svg>
              </div>
              <div class="price-list" v-show="priceOpen">
                <div class="price-range-display">
                  <span>₱0</span>
                  <span class="price-max">₱{{ maxPrice.toLocaleString() }}{{ maxPrice >= 300000 ? '+' : '' }}</span>
                </div>
                <input
                  type="range"
                  v-model.number="maxPrice"
                  class="price-slider"
                  min="0"
                  max="300000"
                  step="5000"
                >
                <div class="price-presets">
                  <label class="price-preset">
                    <input type="radio" v-model="pricePreset" value="all" @change="applyPricePreset">
                    <span>Any Price</span>
                  </label>
                  <label class="price-preset">
                    <input type="radio" v-model="pricePreset" value="0-15000" @change="applyPricePreset">
                    <span>Under ₱15k</span>
                  </label>
                  <label class="price-preset">
                    <input type="radio" v-model="pricePreset" value="15000-50000" @change="applyPricePreset">
                    <span>₱15k - ₱50k</span>
                  </label>
                  <label class="price-preset">
                    <input type="radio" v-model="pricePreset" value="50000-1000000" @change="applyPricePreset">
                    <span>Premium (₱50k+)</span>
                  </label>
                </div>
              </div>
            </div>

            <button class="apply-btn" @click="() => applyFilters()">APPLY FILTERS</button>
          </aside>

          <div class="products-content">
            <div class="products-header rise-up-delay-1">
              <div class="products-info">
                <h2 class="collection-title">{{ collectionTitle }}</h2>
                <small class="results-count">
                  <span v-if="!isLoading">
                    Showing <strong>{{ filteredProducts.length }}</strong> of <strong>{{ totalProducts }}</strong> results
                  </span>
                  <span v-else>Loading...</span>
                </small>
              </div>
              <div class="sort-wrap">
                <label class="sort-label">Sort By:</label>
                <select v-model="sortBy" class="sort-select">
                  <option value="newest">Newest Arrivals</option>
                  <option value="price_low">Price: Low to High</option>
                  <option value="price_high">Price: High to Low</option>
                  <option value="name_asc">Name: A-Z</option>
                </select>
              </div>
            </div>

            <div v-if="error" class="error-message" style="padding: 2rem; text-align: center; color: #d32f2f; background: #ffebee; border-radius: 8px; margin: 2rem 0;">
              <p>{{ error }}</p>
              <button @click="loadProducts" style="margin-top: 1rem; padding: 0.5rem 1rem; background: #1976d2; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Try Again
              </button>
            </div>

            <div v-else-if="isLoading" class="loading-state" style="padding: 4rem; text-align: center;">
              <div class="spinner-loader" style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #1976d2; border-radius: 50%;"></div>
              <p style="margin-top: 1rem; color: #666;">Loading products...</p>
            </div>

            <div v-else-if="!isLoading && filteredProducts.length === 0" class="no-results">
              <div class="no-results-content">
                <div class="no-results-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                    <path d="M320 576C461.4 576 576 461.4 576 320C576 178.6 461.4 64 320 64C178.6 64 64 178.6 64 320C64 461.4 178.6 576 320 576zM410.6 462.1C390.2 434.1 357.2 416 320 416C282.8 416 249.8 434.1 229.4 462.1C221.6 472.8 206.6 475.2 195.9 467.4C185.2 459.6 182.8 444.6 190.6 433.9C219.7 394 266.8 368 320 368C373.2 368 420.3 394 449.4 433.9C457.2 444.6 454.8 459.6 444.1 467.4C433.4 475.2 418.4 472.8 410.6 462.1zM208 272C208 254.3 222.3 240 240 240C257.7 240 272 254.3 272 272C272 289.7 257.7 304 240 304C222.3 304 208 289.7 208 272zM400 240C417.7 240 432 254.3 432 272C432 289.7 417.7 304 400 304C382.3 304 368 289.7 368 272C368 254.3 382.3 240 400 240z"/>
                  </svg>
                </div>
                <h3 class="no-results-title">No products found</h3>
                <p class="no-results-description">We couldn't find any products matching your search or filters.</p>
                <p class="no-results-message">Try adjusting your filters or search terms to discover more pieces.</p>
              </div>
            </div>

            <div class="products-grid" v-else-if="filteredProducts.length > 0">
              <article
                v-for="(product, index) in filteredProducts"
                :key="product.id"
                :class="['product-card', { 'out-of-stock': isOutOfStock(product) }, `rise-up-delay-${Math.min(Math.floor(index / 3) + 2, 5)}`]"
              >
                <div class="product-image-wrap" @click="openQuickView(product)">
                  <span v-if="product.is_new" class="product-badge">New</span>
                  <span v-if="isOutOfStock(product)" class="product-badge out-of-stock-badge">Out of Stock</span>
                  <span v-else-if="isLowStock(product)" class="product-badge low-stock-badge">Low Stock</span>
                  <button
                    :class="['wishlist-btn', { 'in-wishlist': isInWishlist(product.id) }]"
                    :disabled="isTogglingWishlist === product.id"
                    @click.stop="toggleWishlist(product.id)"
                    aria-label="Add to Wishlist"
                  >
                    <svg v-if="isTogglingWishlist === product.id" class="wishlist-spinner" viewBox="0 0 24 24">
                      <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="32" stroke-linecap="round"/>
                    </svg>
                    <svg v-else viewBox="0 0 24 24" :fill="isInWishlist(product.id) ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2">
                      <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                  </button>
                  <img :src="product.image || '/images/products/placeholder.png'" :alt="product.name" class="product-img" loading="lazy">
                  <div class="image-overlay"></div>
                </div>
                <div class="product-info">
                  <h5 class="product-name">{{ product.name }}</h5>
                  <p class="product-category">{{ product.category_name || product.category || 'Uncategorized' }}</p>
                  <div class="product-price-wrapper">
                    <p v-if="product.sale_price" class="product-price sale">₱{{ formatPrice(product.sale_price) }}</p>
                    <p v-else class="product-price">₱{{ formatPrice(product.price) }}</p>
                    <p v-if="product.sale_price" class="product-price-original">₱{{ formatPrice(product.price) }}</p>
                  </div>
                  <div class="product-actions" v-if="!isOutOfStock(product)">
                    <button
                      class="btn-add-cart"
                      @click="addToCart(product)"
                      :disabled="isAddingToCart && addingProductId === product.id"
                    >
                      <svg v-if="addingProductId === product.id" class="spinner" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="32" stroke-linecap="round"/>
                      </svg>
                      <template v-else>
                        <svg class="btn-icon" viewBox="0 0 576 512" fill="currentColor">
                          <path d="M0 24C0 10.7 10.7 0 24 0H69.5c22 0 41.5 12.8 50.6 32h411c26.3 0 45.5 25 38.6 50.4l-41 152.3c-8.5 31.4-37 53.3-69.5 53.3H170.7l5.4 28.5c2.2 11.3 12.1 19.5 23.6 19.5H488c13.3 0 24 10.7 24 24s-10.7 24-24 24H199.7c-11.5 0-21.4-8.2-23.6-19.5L154.7 384H128.1c-35.3 0-64-28.7-64-64L0 24zM176 512a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm336-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0z"/>
                        </svg>
                        <span>Add to Cart</span>
                      </template>
                    </button>
                    <button class="btn-buy-now" @click="buyNow(product)">
                      <svg class="btn-icon" viewBox="0 0 448 512" fill="currentColor">
                        <path d="M160 112c0-35.3 28.7-64 64-64s64 28.7 64 64v48H160V112zm-48 48H48c-26.5 0-48 21.5-48 48V416c0 53 43 96 96 96H352c53 0 96-43 96-96V208c0-26.5-21.5-48-48-48H336V112C336 50.1 285.9 0 224 0S112 50.1 112 112v48zm24 96a24 24 0 1 1 0 48 24 24 0 1 1 0-48zm152 24a24 24 0 1 1 48 0 24 24 0 1 1 -48 0z"/>
                      </svg>
                      <span>Buy Now</span>
                    </button>
                  </div>
                </div>
              </article>
            </div>

            <div v-if="!isLoading && !error && filteredProducts.length > 0 && totalPages > 1" class="pagination" style="margin-top: 3rem; display: flex; justify-content: center; align-items: center; gap: 0.5rem;">
              <button
                @click="changePage(currentPage - 1)"
                :disabled="currentPage === 1"
                style="padding: 0.5rem 1rem; border: 1px solid #ddd; background: white; cursor: pointer; border-radius: 4px;"
                :style="{ opacity: currentPage === 1 ? 0.5 : 1, cursor: currentPage === 1 ? 'not-allowed' : 'pointer' }"
              >
                Previous
              </button>

              <template v-for="page in Math.min(5, totalPages)" :key="page">
                <button
                  v-if="page <= totalPages"
                  @click="changePage(page)"
                  :style="{
                    padding: '0.5rem 1rem',
                    border: '1px solid #ddd',
                    background: currentPage === page ? '#1976d2' : 'white',
                    color: currentPage === page ? 'white' : '#333',
                    cursor: 'pointer',
                    borderRadius: '4px'
                  }"
                >
                  {{ page }}
                </button>
              </template>

              <span v-if="totalPages > 5" style="padding: 0.5rem;">...</span>

              <button
                v-if="totalPages > 5"
                @click="changePage(totalPages)"
                :style="{
                  padding: '0.5rem 1rem',
                  border: '1px solid #ddd',
                  background: currentPage === totalPages ? '#1976d2' : 'white',
                  color: currentPage === totalPages ? 'white' : '#333',
                  cursor: 'pointer',
                  borderRadius: '4px'
                }"
              >
                {{ totalPages }}
              </button>

              <button
                @click="changePage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                style="padding: 0.5rem 1rem; border: 1px solid #ddd; background: white; cursor: pointer; border-radius: 4px;"
                :style="{ opacity: currentPage === totalPages ? 0.5 : 1, cursor: currentPage === totalPages ? 'not-allowed' : 'pointer' }"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <Teleport to="body">
      <div :class="['quick-view-overlay', { active: quickViewOpen }]" @click.self="closeQuickView">
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
              <div class="qv-desc" v-html="quickViewProduct?.description || ''"></div>
            </div>

            <div class="qv-specs" v-if="quickViewProduct?.attributes">
              <div class="qv-spec-item" v-if="quickViewProduct.attributes.dimensions">
                <span class="qv-spec-label">Dimensions:</span>
                <span class="qv-spec-value">{{ quickViewProduct.attributes.dimensions }}</span>
              </div>
              <div class="qv-spec-item" v-if="quickViewProduct.attributes.material">
                <span class="qv-spec-label">Material:</span>
                <span class="qv-spec-value">{{ quickViewProduct.attributes.material }}</span>
              </div>
              <div class="qv-spec-item" v-if="quickViewProduct.attributes.color">
                <span class="qv-spec-label">Color:</span>
                <span class="qv-spec-value">{{ quickViewProduct.attributes.color }}</span>
              </div>
              <div class="qv-spec-item" v-if="quickViewProduct.attributes.weight">
                <span class="qv-spec-label">Weight:</span>
                <span class="qv-spec-value">{{ quickViewProduct.attributes.weight }}</span>
              </div>
            </div>

            <div class="qv-price-area">
              <div class="qv-price-wrapper">
                <h3 v-if="quickViewProduct?.sale_price" class="qv-price sale">₱{{ formatPrice(quickViewProduct.sale_price) }}</h3>
                <h3 v-else class="qv-price">₱{{ formatPrice(quickViewProduct?.price || 0) }}</h3>
                <p v-if="quickViewProduct?.sale_price" class="qv-price-original">₱{{ formatPrice(quickViewProduct.price) }}</p>
              </div>
              <div class="qv-stock-info">
                <span :class="['qv-stock-badge', quickViewProduct ? getStockBadgeClass(quickViewProduct) : '']">
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
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import HeroSection from '@/components/HeroSection.vue'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { useWishlist } from '@/composables/useWishlist'
import { products as productsApi, home } from '@/services/clientApi'
import { useRealtimeProducts } from '@/composables/useRealtimeProducts'

const router = useRouter()
const route = useRoute()
const cartStore = useCartStore()
const authStore = useAuthStore()


const { isInWishlist, toggleWishlist, isToggling, loadWishlist } = useWishlist()
const isTogglingWishlist = isToggling


const { startListening, stopListening } = useRealtimeProducts()




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
  description?: string
  attributes?: {
    dimensions?: string
    material?: string
    color?: string
    weight?: string
  }
  stock?: number
  stock_quantity?: number
  stock_status?: string
  low_stock_threshold?: number
  track_inventory?: boolean
}

const allProducts = ref<Product[]>([])
const filteredProducts = ref<Product[]>([])
const isLoading = ref(false)
const error = ref<string | null>(null)
const categories = ref<{ id: number; name: string; slug: string }[]>([])


const currentPage = ref(1)
const totalPages = ref(1)
const totalProducts = ref(0)
const perPage = ref(24)




const searchQuery = ref('')
const selectedCategory = ref('All')
const maxPrice = ref(300000)
const pricePreset = ref('all')
const sortBy = ref('newest')
const categoryOpen = ref(true)
const priceOpen = ref(true)

const categoryOptions = computed(() => {
  const all = { value: 'All', label: 'All Collections' }
  const mapped = categories.value.map(cat => ({
    value: cat.slug,
    label: cat.name
  }))
  return [all, ...mapped]
})

const collectionTitle = computed(() => {
  if (selectedCategory.value === 'All') return 'All Collections'
  const cat = categories.value.find(c => c.slug === selectedCategory.value)
  return cat?.name || 'All Collections'
})




const isAddingToCart = ref(false)
const addingProductId = ref<number | null>(null)




const quickViewOpen = ref(false)
const quickViewProduct = ref<Product | null>(null)

const openQuickView = (product: Product) => {
  quickViewProduct.value = product
  quickViewOpen.value = true
  document.body.style.overflow = 'hidden'
}

const closeQuickView = () => {
  quickViewOpen.value = false
  document.body.style.overflow = ''
}




const toggleCategory = () => {
  categoryOpen.value = !categoryOpen.value
}

const togglePrice = () => {
  priceOpen.value = !priceOpen.value
}

const selectCategory = (value: string) => {
  selectedCategory.value = value

}

const applyPricePreset = () => {
  if (pricePreset.value === 'all') {
    maxPrice.value = 300000
  } else if (pricePreset.value === '0-15000') {
    maxPrice.value = 15000
  } else if (pricePreset.value === '15000-50000') {
    maxPrice.value = 50000
  } else if (pricePreset.value === '50000-1000000') {
    maxPrice.value = 300000
  }

}

const resetFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = 'All'
  maxPrice.value = 300000
  pricePreset.value = 'all'
  sortBy.value = 'newest'
  currentPage.value = 1
  applyFilters()
}

const changePage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    loadProducts()

    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}


let searchTimeout: ReturnType<typeof setTimeout> | null = null

const applyFilters = async (resetPage = true) => {
  if (resetPage) {
    currentPage.value = 1
  }

  await loadProducts()
}


watch([searchQuery, selectedCategory, maxPrice, sortBy], () => {

  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }

  searchTimeout = setTimeout(() => {
    applyFilters()
  }, searchQuery.value ? 500 : 0)
})

const loadProducts = async () => {
  isLoading.value = true
  error.value = null

  try {

    const params: {
      page: number
      per_page: number
      search?: string
      category?: string
      min_price?: number
      max_price?: number
      sort_by?: string
      sort_order?: string
    } = {
      page: currentPage.value,
      per_page: perPage.value,
    }


    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim()
    }


    if (selectedCategory.value && selectedCategory.value !== 'All') {
      params.category = selectedCategory.value
    }


    if (maxPrice.value < 300000) {
      params.max_price = maxPrice.value
    }
    params.min_price = 0


    const sortMapping: Record<string, { sort_by: string; sort_order: string }> = {
      newest: { sort_by: 'newest', sort_order: 'desc' },
      price_low: { sort_by: 'price_low', sort_order: 'asc' },
      price_high: { sort_by: 'price_high', sort_order: 'desc' },
      name_asc: { sort_by: 'name', sort_order: 'asc' },
    }

    const sortConfig = sortMapping[sortBy.value]
    if (sortConfig) {
      params.sort_by = sortConfig.sort_by
      params.sort_order = sortConfig.sort_order
    }

    const response = await productsApi.list(params)

    if (response.data.success) {
      const responseData = response.data.data


      const isPaginated = responseData && typeof responseData === 'object' && 'data' in responseData && !Array.isArray(responseData)
      const paginatedData = isPaginated ? responseData as {
        data?: Array<Record<string, unknown>>
        current_page?: number
        last_page?: number
        total?: number
      } : null

      const products: Array<Record<string, unknown>> = isPaginated
        ? (paginatedData?.data || [])
        : (Array.isArray(responseData) ? responseData : [])


      allProducts.value = products.map((p) => {
        const product = p as Record<string, unknown>
        const cat = product.category as Record<string, unknown> | null
        const attrs = product.attributes as Record<string, unknown> | null

        return {
          id: product.id as number,
          name: product.name as string,
          slug: product.slug as string,
          category: cat?.slug as string,
          category_name: cat?.name as string,
          price: typeof product.price === 'string' ? parseFloat(product.price) : (typeof product.price === 'number' ? product.price : 0),
          sale_price: product.sale_price ? (typeof product.sale_price === 'string' ? parseFloat(product.sale_price as string) : (typeof product.sale_price === 'number' ? product.sale_price as number : null)) : null,
          image: (product.image as string | null) || '/images/products/placeholder.png',
          primary_image: product.image as string | undefined,
          is_new: (product.is_new || false) as boolean,
          is_featured: (product.is_featured || false) as boolean,
          is_bestseller: (product.is_bestseller || false) as boolean,
          description: (product.description || '') as string,
          attributes: {
            dimensions: attrs?.dimensions as string | undefined,
            material: attrs?.material as string | undefined,
            color: attrs?.color as string | undefined,
            weight: attrs?.weight as string | undefined,
          },
          stock_quantity: (product.stock_quantity || 0) as number,
          stock_status: (product.stock_status || 'in_stock') as string,
          low_stock_threshold: (product.low_stock_threshold || 5) as number,
          track_inventory: product.track_inventory !== false,
          average_rating: (product.average_rating || 0) as number,
          review_count: (product.review_count || 0) as number,
          created_at: product.created_at as string,
        }
      })


      if (isPaginated && paginatedData) {
        if (paginatedData.current_page !== undefined) {
          currentPage.value = paginatedData.current_page
          totalPages.value = paginatedData.last_page || 1
          totalProducts.value = paginatedData.total || 0
        } else {

          filteredProducts.value = allProducts.value
        }
      } else {

        totalPages.value = 1
        totalProducts.value = allProducts.value.length
      }


      filteredProducts.value = allProducts.value
    } else {
      error.value = 'Failed to load products. Please try again.'
      allProducts.value = []
      filteredProducts.value = []
    }
  } catch (err: unknown) {
    console.error('Failed to load products:', err)
    const errorMessage = (err as { response?: { data?: { message?: string } } })?.response?.data?.message
    error.value = errorMessage || 'Failed to load products. Please try again.'
    allProducts.value = []
    filteredProducts.value = []
  } finally {
    isLoading.value = false
  }
}

const loadCategories = async () => {
  try {
    const response = await home.getCategories()
    if (response.data.success) {
      categories.value = response.data.data || []
    }
  } catch (error) {
    console.error('Failed to load categories:', error)
  }
}


watch(() => route.params.category, async (newCategory) => {
  if (newCategory) {
    selectedCategory.value = newCategory as string
    currentPage.value = 1
    await loadProducts()
  }
}, { immediate: false })


const handleProductCreated = (event: Event) => {
  const customEvent = event as CustomEvent<{
    id: number
    status: string
    name: string
    slug: string
    price: number
    sale_price?: number | null
    primary_image?: string
    category?: { id: number; name: string; slug: string } | null
    is_new?: boolean
    is_featured?: boolean
    stock_quantity?: number
    stock_status?: string
    timestamp?: string
  }>
  const product = customEvent.detail
  if (product && product.status === 'active') {

    loadProducts()
  }
}

const handleProductUpdated = (event: Event) => {
  const customEvent = event as CustomEvent<{
    id: number
    name: string
    slug: string
    price: number | string
    sale_price?: number | string | null
    status?: string
    is_featured?: boolean
    is_new?: boolean
    stock_quantity?: number
    stock_status?: string
    low_stock_threshold?: number
    track_inventory?: boolean
    primary_image?: string
  }>
  const productData = customEvent.detail
  if (!productData) return

  const index = allProducts.value.findIndex(p => p.id === productData.id)
  if (index !== -1 && allProducts.value[index]) {
    const existingProduct = allProducts.value[index]

    const price = typeof productData.price === 'string'
      ? parseFloat(productData.price.replace(/[^0-9.-]/g, ''))
      : (typeof productData.price === 'number' ? productData.price : existingProduct.price)
    const salePrice = productData.sale_price
      ? (typeof productData.sale_price === 'string'
          ? parseFloat(productData.sale_price.replace(/[^0-9.-]/g, ''))
          : (typeof productData.sale_price === 'number' ? productData.sale_price : null))
      : null


    allProducts.value[index] = {
      ...existingProduct,
      name: productData.name,
      slug: productData.slug,
      price: price,
      sale_price: salePrice,
      is_featured: productData.is_featured ?? existingProduct.is_featured,
      is_new: productData.is_new ?? existingProduct.is_new,
      stock_quantity: productData.stock_quantity ?? existingProduct.stock_quantity,
      stock_status: productData.stock_status ?? existingProduct.stock_status,
      low_stock_threshold: productData.low_stock_threshold ?? existingProduct.low_stock_threshold,
      track_inventory: productData.track_inventory ?? existingProduct.track_inventory,
      image: productData.primary_image || existingProduct.image,
      primary_image: productData.primary_image,
    }

    filteredProducts.value = [...allProducts.value]
  } else {

    loadProducts()
  }
}

const handleProductDeleted = (event: Event) => {
  const customEvent = event as CustomEvent<{ id: number; slug: string }>
  const detail = customEvent.detail
  if (!detail) return

  const { id } = detail
  allProducts.value = allProducts.value.filter(p => p.id !== id)
  filteredProducts.value = filteredProducts.value.filter(p => p.id !== id)
  totalProducts.value = Math.max(0, totalProducts.value - 1)
}

const handleStockChanged = (event: Event) => {
  const customEvent = event as CustomEvent<{
    product_id: number
    product_name: string
    new_quantity: number
    stock_status: string
    type: string
  }>
  const stockData = customEvent.detail
  if (!stockData) return

  const product = allProducts.value.find(p => p.id === stockData.product_id)
  if (product) {
    product.stock_quantity = stockData.new_quantity
    product.stock_status = stockData.stock_status


    const filteredIndex = filteredProducts.value.findIndex(p => p.id === stockData.product_id)
    if (filteredIndex !== -1) {
      const filteredProduct = filteredProducts.value[filteredIndex]
      if (filteredProduct) {
        filteredProducts.value[filteredIndex] = { ...product }
      }
    }


    if (stockData.type === 'low_stock') {
      console.warn(`Low stock alert: ${stockData.product_name} (${stockData.new_quantity} remaining)`)
    } else if (stockData.type === 'out_of_stock') {
      console.warn(`Out of stock: ${stockData.product_name}`)
    }
  }
}

onMounted(async () => {

  loadWishlist()

  await loadCategories()
  await loadProducts()


  if (route.params.category) {
    selectedCategory.value = route.params.category as string
  }


  startListening()
  window.addEventListener('realtime:product:created', handleProductCreated)
  window.addEventListener('realtime:product:updated', handleProductUpdated)
  window.addEventListener('realtime:product:deleted', handleProductDeleted)
  window.addEventListener('realtime:stock:changed', handleStockChanged)
})

onUnmounted(() => {
  stopListening()
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  window.removeEventListener('realtime:product:created', handleProductCreated)
  window.removeEventListener('realtime:product:updated', handleProductUpdated)
  window.removeEventListener('realtime:product:deleted', handleProductDeleted)
  window.removeEventListener('realtime:stock:changed', handleStockChanged)
})




const addToCart = async (product: Product) => {
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

const buyNow = async (product: Product) => {

  if (!authStore.isAuthenticated) {

    sessionStorage.setItem('redirectAfterLogin', '/checkout')
    router.push({ name: 'home', query: { login: 'true' } })
    return
  }

  isAddingToCart.value = true
  addingProductId.value = product.id

  try {
    const result = await cartStore.addItem(product.id, 1)
    if (result.success) {
      router.push('/checkout')
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

const buyNowFromModal = () => {
  if (quickViewProduct.value) {
    buyNow(quickViewProduct.value)
    closeQuickView()
  }
}




const getStockQuantity = (product: Product): number => {
  return product.stock_quantity || product.stock || 0
}

const getLowStockThreshold = (product: Product): number => {
  return product.low_stock_threshold || 5
}

const isOutOfStock = (product: Product): boolean => {
  if (!product.track_inventory && product.track_inventory !== false) {
    return false
  }
  if (product.track_inventory === false) {
    return false
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




const formatPrice = (price: number) => {
  return price.toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}




onMounted(() => {
  applyFilters()
})
</script>

<style scoped>
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.spinner-loader {
  animation: spin 1s linear infinite;
}
.products-page {
  --gold: #c9a050;
  --gold-light: #d4af37;
  --gold-dark: #B8860B;
  --dark: #1a1a1a;
  --light: #f8f8f8;
  --white: #ffffff;
  --gray: #666;
  --transition: all 0.3s ease;

  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.products-main {
  background: var(--light);
  padding: 3rem 0 4rem;
  flex: 1;
}

.products-container {
  max-width: 1600px;
  margin: 0 auto;
  padding: 0 3rem;
}

.products-layout {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 3rem;
}

.filter-sidebar {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid #eee;
  border-radius: 12px;
  padding: 1.5rem;
  position: sticky;
  top: 120px;
  height: fit-content;
  box-shadow: 0 4px 20px rgba(0,0,0,0.04);
}

.filter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 1rem;
  margin-bottom: 1rem;
  border-bottom: 1px solid #eee;
}

.filter-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0;
  color: var(--dark);
}

.reset-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  color: var(--gray);
  cursor: pointer;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: var(--transition);
}

.reset-btn svg {
  width: 18px;
  height: 18px;
}

.reset-btn:hover {
  color: var(--gold);
  transform: rotate(-180deg);
}

.filter-group {
  margin-bottom: 1.25rem;
}

.filter-label {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: var(--dark);
  margin: 0;
  cursor: pointer;
}

.filter-toggle {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  margin-bottom: 0.75rem;
}

.toggle-arrow {
  width: 16px;
  height: 16px;
  color: var(--gray);
  transition: transform 0.3s ease;
}

.toggle-arrow.rotated {
  transform: rotate(-90deg);
}

.filter-divider {
  height: 1px;
  background: linear-gradient(to right, var(--gold), transparent);
  margin: 1rem 0;
}

.search-input-wrap {
  position: relative;
  margin-top: 0.5rem;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  color: #999;
}

.search-input {
  width: 100%;
  padding: 0.625rem 0.75rem 0.625rem 38px;
  border: 1px solid #eee;
  border-radius: 8px;
  font-size: 0.85rem;
  outline: none;
  transition: var(--transition);
}

.search-input:focus {
  border-color: var(--gold);
}

.category-list {
  margin-top: 0.5rem;
}

.category-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0.75rem;
  margin-bottom: 0.25rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
  color: var(--gray);
  transition: var(--transition);
}

.category-item:hover {
  background: #f8f9fa;
  color: var(--gold);
  padding-left: 1rem;
}

.category-item.active {
  background: rgba(201, 160, 80, 0.1);
  color: var(--gold-dark);
  font-weight: 600;
}

.check-icon {
  width: 14px;
  height: 14px;
}

.price-range-display {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  color: var(--gray);
  margin-bottom: 0.5rem;
}

.price-max {
  font-weight: 600;
  color: var(--dark);
}

.price-slider {
  width: 100%;
  height: 4px;
  -webkit-appearance: none;
  appearance: none;
  background: #e9ecef;
  border-radius: 2px;
  outline: none;
}

.price-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  width: 18px;
  height: 18px;
  background: var(--gold);
  border: 3px solid var(--white);
  border-radius: 50%;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  transition: transform 0.2s;
}

.price-slider::-webkit-slider-thumb:hover {
  transform: scale(1.2);
}

.price-presets {
  margin-top: 0.75rem;
}

.price-preset {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
  color: var(--gray);
  cursor: pointer;
  margin-bottom: 0.375rem;
}

.price-preset input {
  accent-color: var(--gold);
}

.apply-btn {
  width: 100%;
  padding: 0.75rem;
  background: var(--gold);
  color: var(--dark);
  border: none;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 1px;
  cursor: pointer;
  transition: var(--transition);
}

.apply-btn:hover {
  background: var(--gold-dark);
  color: var(--white);
}

.products-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 1rem;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid #ddd;
}

.collection-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0 0 0.25rem;
  color: var(--dark);
}

.results-count {
  color: var(--gray);
  font-size: 0.85rem;
}

.results-count strong {
  color: var(--dark);
}

.sort-wrap {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.sort-label {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  color: var(--gray);
}

.sort-select {
  padding: 0.5rem 1rem;
  border: none;
  background: var(--white);
  border-radius: 50px;
  font-size: 0.85rem;
  cursor: pointer;
  outline: none;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
}

.product-card {
  background: var(--white);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0,0,0,0.06);
  transition: var(--transition);
  display: flex;
  flex-direction: column;
  height: 100%;
}

.product-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 32px rgba(0,0,0,0.1);
}

.product-card.out-of-stock {
  opacity: 0.6;
  background: #f5f5f5;
  position: relative;
}

.product-card.out-of-stock::before {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 12px;
  z-index: 1;
  pointer-events: none;
}

.product-image-wrap {
  position: relative;
  aspect-ratio: 1;
  overflow: hidden;
  cursor: pointer;
  background: #f5f5f5;
}

.product-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.product-card:hover .product-img {
  transform: scale(1.06);
}

.image-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 60px;
  background: linear-gradient(to top, rgba(0,0,0,0.2), transparent);
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.product-card:hover .image-overlay {
  opacity: 1;
}

.product-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  z-index: 5;
  padding: 5px 12px;
  background: var(--gold);
  color: var(--white);
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-radius: 4px;
}

.product-badge.low-stock-badge {
  background: #ff9800;
  color: var(--white);
}

.product-badge.out-of-stock-badge {
  background: #dc3545;
  color: var(--white);
}

.wishlist-btn {
  position: absolute;
  top: 12px;
  right: 12px;
  z-index: 5;
  width: 38px;
  height: 38px;
  border: none;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  color: #666;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.wishlist-btn:hover {
  background: rgba(255, 107, 107, 0.95);
  color: white;
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
}

.wishlist-btn.in-wishlist {
  background: rgba(255, 107, 107, 0.95);
  color: white;
}

.wishlist-btn.in-wishlist:hover {
  background: rgba(255, 82, 82, 0.95);
  transform: scale(1.15);
}

.wishlist-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.wishlist-btn svg {
  width: 18px;
  height: 18px;
}

.wishlist-spinner {
  width: 18px;
  height: 18px;
  animation: spin 1s linear infinite;
}

.product-info {
  padding: 1.25rem 1.5rem 1.5rem;
  text-align: center;
  background: #ffffff;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.product-name {
  font-family: 'Playfair Display', serif;
  font-size: 1.15rem;
  font-weight: 600;
  color: var(--dark);
  margin: 0 0 0.5rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.product-category {
  font-size: 0.9rem;
  color: #444;
  margin: 0 0 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  opacity: 1;
}

.product-price {
  font-size: 1.4rem;
  font-weight: 700;
  color: #000000;
  margin: 0 0 0.5rem;
  font-family: 'Playfair Display', serif;
  line-height: 1.2;
}

.product-price.sale {
  color: #000000;
}

.product-price-wrapper {
  margin-bottom: 1rem;
}

.product-price-original {
  font-size: 1rem;
  color: #dc3545;
  text-decoration: line-through;
  margin: 0;
}

.product-stock-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.stock-badge {
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: #e3f2fd;
  color: #000000;
}

.stock-badge.stock-badge-in {
  background: #e3f2fd;
  color: #000000;
}

.stock-badge.stock-badge-low {
  background: #fff3e0;
  color: #000000;
}

.stock-badge.stock-badge-out {
  background: #f8d7da;
  color: #000000;
}

.stock-count {
  font-size: 0.85rem;
  font-weight: 600;
  color: #000000;
  font-family: 'Playfair Display', serif;
}

.product-actions {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  margin-top: auto;
}

.btn-add-cart,
.btn-buy-now {
  flex: 1;
  padding: 0.6rem 0.75rem;
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-icon {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}

.btn-add-cart {
  background: transparent;
  border: 1px solid var(--dark);
  color: var(--dark);
}

.btn-add-cart:hover {
  background: var(--dark);
  color: var(--white);
}

.btn-add-cart:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-buy-now {
  background: var(--gold);
  border: 1px solid var(--gold);
  color: var(--dark);
}

.btn-buy-now:hover {
  background: var(--gold-dark);
  border-color: var(--gold-dark);
  color: var(--white);
}

.spinner {
  width: 16px;
  height: 16px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.no-results {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 500px;
  padding: 4rem 2rem;
  background: linear-gradient(135deg, #fafafa 0%, #ffffff 100%);
  border-radius: 20px;
  margin: 2rem 0;
}

.no-results-content {
  text-align: center;
  max-width: 500px;
  animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.no-results-icon {
  margin-bottom: 2rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 120px;
  height: 120px;
  background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
  border-radius: 50%;
  margin: 0 auto;
  position: relative;
}

.no-results-icon::before {
  content: '';
  position: absolute;
  inset: -4px;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(153, 153, 153, 0.15), rgba(153, 153, 153, 0.05));
  z-index: -1;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.1);
    opacity: 0.7;
  }
}

.no-results-icon svg {
  width: 80px;
  height: 80px;
  color: #999;
  animation: float 3s ease-in-out infinite;
  filter: drop-shadow(0 2px 8px rgba(153, 153, 153, 0.2));
  opacity: 0.7;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.no-results-title {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0 0 1rem;
  line-height: 1.2;
}

.no-results-description {
  font-size: 1.05rem;
  color: #555;
  line-height: 1.6;
  margin: 0 0 0.75rem;
  font-weight: 500;
}

.no-results-message {
  font-size: 1rem;
  color: #666;
  line-height: 1.6;
  margin: 0;
}


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

.qv-stock-count {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.95rem;
  font-weight: 600;
  color: #000000;
  font-family: 'Playfair Display', serif;
  transition: all 0.3s ease;
  cursor: default;
  position: relative;
}

.qv-stock-count::before {
  content: '';
  display: inline-block;
  width: 6px;
  height: 6px;
  background: #2e7d32;
  border-radius: 50%;
  box-shadow: 0 0 0 2px rgba(46, 125, 50, 0.2);
  animation: pulse 2s ease-in-out infinite;
}

.qv-stock-count:hover {
  color: #1a1a1a;
  transform: translateX(2px);
}

.qv-stock-count:hover::before {
  box-shadow: 0 0 0 4px rgba(46, 125, 50, 0.3);
  transform: scale(1.2);
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

@media (max-width: 1200px) {
  .products-layout {
    grid-template-columns: 300px 1fr;
    gap: 2.5rem;
  }

  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1.75rem;
  }
}

@media (max-width: 991px) {
  .products-layout {
    grid-template-columns: 1fr;
  }

  .filter-sidebar {
    position: static;
    margin-bottom: 2rem;
  }

  .products-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
  }
}

@media (max-width: 768px) {
  .products-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
  }

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

@media (max-width: 576px) {
  .products-container {
    padding: 0 1rem;
  }

  .products-grid {
    grid-template-columns: 1fr 1fr;
    gap: 0.625rem;
  }

  .product-info {
    padding: 0.75rem;
  }

  .product-name {
    font-size: 0.85rem;
  }

  .product-category {
    font-size: 0.8rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #1c1c1c;
  }

  .product-price {
    font-size: 1.1rem;
    margin-bottom: 1rem;
    font-weight: 700;
  }

  .product-actions {
    flex-direction: column;
    gap: 0.375rem;
  }

  .btn-add-cart,
  .btn-buy-now {
    font-size: 0.7rem;
    padding: 0.5rem;
  }

  .btn-icon {
    width: 14px;
    height: 14px;
  }

  .no-results {
    min-height: 400px;
    padding: 3rem 1.5rem;
    margin: 1rem 0;
  }

  .no-results-icon {
    width: 100px;
    height: 100px;
  }

  .no-results-icon svg {
    width: 52px;
    height: 52px;
  }

  .no-results-title {
    font-size: 1.5rem;
  }

  .no-results-description {
    font-size: 0.95rem;
    margin-bottom: 0.5rem;
  }

  .no-results-message {
    font-size: 0.9rem;
  }
}
</style>
