<template>
  <div class="products-grid">
    <ProductCard
      v-for="(product, index) in products"
      :key="product.id"
      :product="product"
      :delay-index="Math.min(Math.floor(index / 3) + 2, 5)"
      :is-adding-to-cart="isAddingToCart"
      :adding-product-id="addingProductId"
      @add-to-cart="$emit('add-to-cart', product)"
      @buy-now="$emit('buy-now', product)"
      @quick-view="$emit('quick-view', product)"
    />
  </div>
</template>

<script setup lang="ts">
import { Product } from '@/types'
import ProductCard from './ProductCard.vue'

interface Props {
  products: Product[]
  isAddingToCart?: boolean
  addingProductId?: number | null
}

withDefaults(defineProps<Props>(), {
  isAddingToCart: false,
  addingProductId: null,
})

defineEmits<{
  'add-to-cart': [product: Product]
  'buy-now': [product: Product]
  'quick-view': [product: Product]
}>()
</script>

<style scoped>
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 2rem;
  padding: 2rem 0;
}

@media (max-width: 768px) {
  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 1.5rem;
  }
}
</style>
