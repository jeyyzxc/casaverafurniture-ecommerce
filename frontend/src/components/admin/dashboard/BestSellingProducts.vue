<template>
  <article class="dashboard-card">
    <header class="card-header">
      <h3 class="card-title">Best Selling Products</h3>
      <router-link to="/admin/reports" class="view-all">Full Report</router-link>
    </header>
    <div class="card-content">
      <div v-if="products.length > 0" class="products-list">
        <div
          v-for="(product, index) in products"
          :key="product.id"
          class="product-item clickable-product"
          @click="$emit('view-product', product.id)"
        >
          <div class="product-rank">{{ index + 1 }}</div>
          <img :src="product.image || '/images/products/placeholder.png'" :alt="product.name" class="product-thumb" />
          <div class="product-info">
            <h4 class="product-name">{{ product.name }}</h4>
            <p class="product-sales">{{ product.sales }} sales</p>
          </div>
          <div class="product-revenue">₱{{ formatPrice(product.revenue) }}</div>
        </div>
      </div>
      <div v-else class="empty-state">
        <p>No sales data available</p>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
defineProps<{
  products: Array<{
    id: number
    name: string
    image: string
    sales: number
    revenue: number
  }>
}>()

defineEmits(['view-product'])

const formatPrice = (price: number) => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
</script>

<style scoped>
.dashboard-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  border: 1px solid rgba(0,0,0,0.03);
  overflow: hidden;
}

.card-header {
  padding: 1.25rem 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #f1f5f9;
}

.card-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1e293b;
}

.view-all {
  font-size: 0.875rem;
  font-weight: 600;
  color: white;
  background-color: var(--gold);
  padding: 0.5rem 1rem;
  border-radius: 8px;
  text-decoration: none;
  transition: all 0.2s;
}

.view-all:hover {
  background-color: #b08d44;
  color: white;
}

.card-content {
  padding: 1.25rem;
}

.products-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.product-item {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 1rem;
  border-radius: 12px;
  transition: all 0.2s ease;
  background: white;
  border: 1px solid #f1f5f9;
}

.product-item.clickable-product {
  cursor: pointer;
}

.product-item.clickable-product:hover {
  background: #f8fafc;
  border-color: #b8860b;
  transform: translateX(4px);
}

.product-rank {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #b8860b;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.75rem;
  flex-shrink: 0;
  box-shadow: 0 2px 4px rgba(184, 134, 11, 0.2);
}

.product-thumb {
  width: 48px;
  height: 48px;
  object-fit: cover;
  border-radius: 8px;
  flex-shrink: 0;
  border: 1px solid #e2e8f0;
}

.product-info {
  flex: 1;
  min-width: 0;
}

.product-name {
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.25rem;
  font-size: 0.9375rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.product-sales {
  font-size: 0.8125rem;
  color: #475569;
  font-weight: 500;
}

.product-revenue {
  font-weight: 800;
  color: #b8860b;
  font-size: 1rem;
  flex-shrink: 0;
}

.empty-state {
  padding: 2rem;
  text-align: center;
  color: #94a3b8;
  font-style: italic;
}
</style>
