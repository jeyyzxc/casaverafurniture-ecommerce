<template>
  <article
    class="product-card"
    :class="{
      'out-of-stock': isOutOfStock,
      [`rise-up-delay-${delayIndex}`]: delayIndex !== undefined
    }"
  >
    <div class="product-image-wrap" @click="$emit('quick-view', product)">
      <span v-if="product.is_new" class="product-badge">New</span>
      <span v-if="isOutOfStock" class="product-badge out-of-stock-badge">Out of Stock</span>
      <span v-else-if="isLowStock" class="product-badge low-stock-badge">Low Stock</span>
      <img
        :src="product.image || '/images/products/placeholder.png'"
        :alt="product.name"
        class="product-img"
        loading="lazy"
      >
      <div class="image-overlay"></div>
    </div>
    <div class="product-info">
      <h5 class="product-name">{{ product.name }}</h5>
      <p class="product-category">{{ product.category_name || product.category || 'Uncategorized' }}</p>
      <div class="product-price-wrapper">
        <p v-if="product.sale_price" class="product-price sale">₱{{ formattedPrice }}</p>
        <p v-else class="product-price">₱{{ formattedPrice }}</p>
        <p v-if="product.sale_price" class="product-price-original">₱{{ originalPrice }}</p>
      </div>
      <div class="product-actions" v-if="!isOutOfStock">
        <button
          class="btn-add-cart"
          @click="$emit('add-to-cart', product)"
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
        <button class="btn-buy-now" @click="$emit('buy-now', product)">
          <svg class="btn-icon" viewBox="0 0 448 512" fill="currentColor">
            <path d="M160 112c0-35.3 28.7-64 64-64s64 28.7 64 64v48H160V112zm-48 48H48c-26.5 0-48 21.5-48 48V416c0 53 43 96 96 96H352c53 0 96-43 96-96V208c0-26.5-21.5-48-48-48H336V112C336 50.1 285.9 0 224 0S112 50.1 112 112v48zm24 96a24 24 0 1 1 0 48 24 24 0 1 1 0-48zm152 24a24 24 0 1 1 48 0 24 24 0 1 1 -48 0z"/>
          </svg>
          <span>Buy Now</span>
        </button>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Product } from '@/types'
import { useProduct } from '@/composables/useProduct'
import { formatPrice } from '@/utils/formatters'

interface Props {
  product: Product
  delayIndex?: number
  isAddingToCart?: boolean
  addingProductId?: number | null
}

const props = withDefaults(defineProps<Props>(), {
  delayIndex: undefined,
  isAddingToCart: false,
  addingProductId: null,
})

const emit = defineEmits<{
  'add-to-cart': [product: Product]
  'buy-now': [product: Product]
  'quick-view': [product: Product]
}>()

const { isOutOfStock: checkOutOfStock, isLowStock: checkLowStock } = useProduct()

const isOutOfStock = computed(() => checkOutOfStock(props.product))
const isLowStock = computed(() => checkLowStock(props.product))
const formattedPrice = computed(() => formatPrice(props.product.sale_price ?? props.product.price))
const originalPrice = computed(() => formatPrice(props.product.price))
</script>

<style scoped>
.product-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  display: flex;
  flex-direction: column;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.product-card.out-of-stock {
  opacity: 0.7;
}

.product-image-wrap {
  position: relative;
  aspect-ratio: 1;
  overflow: hidden;
  cursor: pointer;
  background: #f8f8f8;
}

.product-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.product-card:hover .product-img {
  transform: scale(1.05);
}

.image-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.1) 100%);
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
  padding: 4px 12px;
  background: linear-gradient(135deg, #c9a050, #FFD700);
  color: white;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-radius: 20px;
}

.product-badge.out-of-stock-badge {
  background: linear-gradient(135deg, #d32f2f, #f44336);
}

.product-badge.low-stock-badge {
  background: linear-gradient(135deg, #ff9800, #ff6f00);
}

.product-info {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.product-name {
  font-family: 'Playfair Display', serif;
  font-size: 1rem;
  font-weight: 600;
  color: #1a1a1a;
  margin: 0 0 0.25rem;
  line-height: 1.3;
}

.product-category {
  font-size: 0.85rem;
  color: #666;
  margin: 0 0 0.5rem;
  text-transform: capitalize;
}

.product-price-wrapper {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.product-price {
  font-size: 1.1rem;
  font-weight: 700;
  color: #000;
  margin: 0;
}

.product-price.sale {
  color: #c9a050;
}

.product-price-original {
  font-size: 0.9rem;
  color: #999;
  text-decoration: line-through;
  margin: 0;
}

.product-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: auto;
}

.btn-add-cart,
.btn-buy-now {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
}

.btn-add-cart {
  background: #f8f6f3;
  color: #1a1a1a;
}

.btn-add-cart:hover:not(:disabled) {
  background: #c9a050;
  color: white;
}

.btn-add-cart:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-buy-now {
  background: #c9a050;
  color: white;
}

.btn-buy-now:hover {
  background: #a6833e;
}

.btn-icon {
  width: 16px;
  height: 16px;
}

.spinner {
  width: 20px;
  height: 20px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
