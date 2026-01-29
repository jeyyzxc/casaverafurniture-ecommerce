<template>
  <article class="dashboard-card alert-card">
    <header class="card-header">
      <h3 class="card-title">Inventory Alerts</h3>
      <router-link to="/admin/inventory" class="view-all">View All</router-link>
    </header>
    <div class="card-content">
      <div v-if="items.length > 0" class="alerts-list">
        <div v-for="item in items" :key="item.id" class="alert-item">
          <div class="alert-info">
            <span class="product-name">{{ item.name }}</span>
            <span class="stock-status" :class="item.stock === 0 ? 'out-of-stock' : 'low-stock'">
              {{ item.stock === 0 ? 'Out of Stock' : `${item.stock} left in stock` }}
            </span>
          </div>
          <router-link :to="`/admin/inventory?search=${item.name}`" class="restock-btn">
            Manage
          </router-link>
        </div>
      </div>
      <div v-else class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="success-icon">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
          <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        <p>All products are sufficiently stocked</p>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
defineProps<{
  items: Array<{
    id: number
    name: string
    stock: number
    threshold?: number
  }>
}>()
</script>

<style scoped>
.dashboard-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  border: 1px solid rgba(0,0,0,0.03);
  overflow: hidden;
}

.alert-card {
  border-left: 4px solid #f59e0b;
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
  padding: 1rem;
  max-height: 400px;
  overflow-y: auto;
}

.alerts-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.alert-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem;
  background: #fffbeb;
  border-radius: 10px;
  border: 1px solid #fef3c7;
}

.alert-info {
  display: flex;
  flex-direction: column;
}

.product-name {
  font-weight: 600;
  color: #92400e;
  font-size: 0.9375rem;
}

.stock-status {
  font-size: 0.8125rem;
  font-weight: 500;
  margin-top: 0.125rem;
}

.stock-status.low-stock { color: #d97706; }
.stock-status.out-of-stock { color: #dc2626; font-weight: 700; }

.restock-btn {
  padding: 0.5rem 1rem;
  background: white;
  color: #92400e;
  text-decoration: none;
  font-size: 0.8125rem;
  font-weight: 700;
  border-radius: 8px;
  border: 1px solid #fcd34d;
  transition: all 0.2s ease;
}

.restock-btn:hover {
  background: #fcd34d;
  color: #92400e;
}

.empty-state {
  padding: 2rem;
  text-align: center;
  color: #10b981;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.success-icon {
  width: 48px;
  height: 48px;
  stroke: #10b981;
}

.empty-state p {
  font-weight: 600;
  font-size: 0.9375rem;
}
</style>
