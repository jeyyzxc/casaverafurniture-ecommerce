<template>
  <div class="stat-card" :class="type" @click="$emit('click')">
    <div class="stat-icon">
      <slot name="icon"></slot>
    </div>
    <div class="stat-content">
      <div class="stat-label">{{ label }}</div>
      <div class="stat-value">{{ value }}</div>
      <div v-if="change" class="stat-change" :class="changeType">
        <svg v-if="changeType === 'positive'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
          <polyline points="17 6 23 6 23 12"/>
        </svg>
        <svg v-else-if="changeType === 'negative'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/>
          <polyline points="17 18 23 18 23 12"/>
        </svg>
        {{ change }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  label: string
  value: string | number
  type: string
  change?: string
  changeType?: 'positive' | 'negative' | 'neutral'
}>()

defineEmits(['click'])
</script>

<style scoped>
.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  gap: 1.25rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  border: 1px solid rgba(0, 0, 0, 0.03);
  position: relative;
  overflow: hidden;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 54px;
  height: 54px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all 0.3s ease;
}

.stat-icon svg {
  width: 28px;
  height: 28px;
}

/* Specific Card Colors */
.stat-card.revenue .stat-icon { background: #ecfdf5; color: #10b981; }
.stat-card.orders .stat-icon { background: #eff6ff; color: #3b82f6; }
.stat-card.products .stat-icon { background: #fffbeb; color: #f59e0b; }
.stat-card.customers .stat-icon { background: #faf5ff; color: #8b5cf6; }

.stat-card.revenue:hover .stat-icon { background: #10b981; color: white; }
.stat-card.orders:hover .stat-icon { background: #3b82f6; color: white; }
.stat-card.products:hover .stat-icon { background: #f59e0b; color: white; }
.stat-card.customers:hover .stat-icon { background: #8b5cf6; color: white; }

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 800;
  color: #1e293b;
  line-height: 1.2;
}

.stat-change {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  font-weight: 600;
  margin-top: 0.5rem;
}

.stat-change svg {
  width: 14px;
  height: 14px;
}

.stat-change.positive { color: #10b981; }
.stat-change.negative { color: #ef4444; }
.stat-change.neutral { color: #94a3b8; }
</style>
