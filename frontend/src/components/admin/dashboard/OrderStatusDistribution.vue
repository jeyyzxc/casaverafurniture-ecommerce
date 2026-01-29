<template>
  <article class="dashboard-card">
    <header class="card-header">
      <h3 class="card-title">Order Status</h3>
      <router-link to="/admin/orders" class="view-all">View All</router-link>
    </header>
    <div class="card-content">
      <div class="status-grid">
        <div
          v-for="(count, status) in distribution"
          :key="status"
          class="status-item clickable-status"
          @click="$emit('filter-status', status)"
        >
          <div class="status-indicator" :class="status"></div>
          <div class="status-info">
            <span class="status-count">{{ count }}</span>
            <span class="status-label">{{ status }}</span>
          </div>
        </div>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
defineProps<{
  distribution: {
    pending: number
    processing: number
    shipped: number
    delivered: number
    cancelled: number
  }
}>()

defineEmits(['filter-status'])
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

.card-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1e293b;
}

.card-content {
  padding: 1.5rem;
}

.status-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 1rem;
}

.status-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 12px;
  transition: all 0.2s ease;
}

.status-item.clickable-status {
  cursor: pointer;
}

.status-item.clickable-status:hover {
  background: #f1f5f9;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.status-indicator {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-indicator.pending { background: #fbbf24; box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.1); }
.status-indicator.processing { background: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
.status-indicator.shipped { background: #8b5cf6; box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1); }
.status-indicator.delivered { background: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); }
.status-indicator.cancelled { background: #ef4444; box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1); }

.status-info {
  display: flex;
  flex-direction: column;
}

.status-count {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  line-height: 1;
}

.status-label {
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.025em;
  margin-top: 0.25rem;
}
</style>
