<template>
  <article class="dashboard-card chart-card">
    <header class="card-header">
      <h3 class="card-title">{{ title }}</h3>
      <div class="card-actions">
        <button v-if="showExport" class="action-btn btn-export" @click="$emit('export')" title="Export Data">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
          </svg>
          Export
        </button>
      </div>
    </header>
    <div class="chart-container">
      <div v-if="data.length > 0" class="chart-wrapper">
        <svg viewBox="0 0 400 150" class="sales-chart">
          <!-- Grid Lines -->
          <line x1="20" y1="20" x2="380" y2="20" stroke="#f3f4f6" stroke-width="1" />
          <line x1="20" y1="75" x2="380" y2="75" stroke="#f3f4f6" stroke-width="1" />
          <line x1="20" y1="130" x2="380" y2="130" stroke="#e5e7eb" stroke-width="1" />

          <!-- Area Fill -->
          <polyline :points="chartAreaPoints" fill="rgba(201, 160, 80, 0.1)" />

          <!-- Line -->
          <polyline
            :points="chartPoints"
            fill="none"
            stroke="var(--gold)"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round"
          />

          <!-- Data Points -->
          <circle
            v-for="(point, index) in dataPoints"
            :key="index"
            :cx="point.x"
            :cy="point.y"
            r="4"
            fill="white"
            stroke="var(--gold)"
            stroke-width="2"
            class="chart-dot"
          >
            <title>{{ point.label }}: {{ formatValue(point.value) }}</title>
          </circle>

          <!-- Labels (Simplified) -->
          <text
            v-if="data.length > 0"
            x="20"
            y="145"
            class="chart-label"
          >{{ formatDate(data[0].date) }}</text>
          <text
            v-if="data.length > 1"
            x="380"
            y="145"
            class="chart-label"
            text-anchor="end"
          >{{ formatDate(data[data.length - 1].date) }}</text>
        </svg>
      </div>
      <div v-else class="empty-chart">
        <p>No data available for the selected period</p>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  title: string
  data: Array<{ date: string; revenue: string | number }>
  showExport?: boolean
}>()

defineEmits(['export'])

const formatValue = (val: number) => {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
  }).format(val)
}

const formatDate = (dateStr: string) => {
  const d = new Date(dateStr)
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const dataPoints = computed(() => {
  if (!props.data || props.data.length === 0) return []

  const values = props.data.map(d => typeof d.revenue === 'string' ? parseFloat(d.revenue) : (d.revenue as number))
  const maxVal = Math.max(...values, 1000)
  const width = 360
  const height = 110
  const xOffset = 20
  const yOffset = 20

  return props.data.map((d, i) => {
    const val = typeof d.revenue === 'string' ? parseFloat(d.revenue) : (d.revenue as number)
    const x = xOffset + (i * (width / Math.max(props.data.length - 1, 1)))
    const y = (yOffset + height) - (val / maxVal * height)
    return { x, y, value: val, label: d.date }
  })
})

const chartPoints = computed(() => {
  return dataPoints.value.map(p => `${p.x},${p.y}`).join(' ')
})

const chartAreaPoints = computed(() => {
  if (dataPoints.value.length === 0) return ''
  const first = dataPoints.value[0]
  const last = dataPoints.value[dataPoints.value.length - 1]
  return `${first.x},130 ${chartPoints.value} ${last.x},130`
})
</script>

<style scoped>
.dashboard-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  border: 1px solid rgba(0,0,0,0.03);
  overflow: hidden;
  display: flex;
  flex-direction: column;
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

.chart-container {
  padding: 1.5rem;
  flex: 1;
  min-height: 250px;
}

.chart-wrapper {
  height: 100%;
  width: 100%;
}

.sales-chart {
  width: 100%;
  height: auto;
  overflow: visible;
}

.chart-dot {
  transition: r 0.2s ease, stroke-width 0.2s ease;
  cursor: pointer;
}

.chart-dot:hover {
  r: 6px;
  stroke-width: 3;
}

.chart-label {
  font-size: 8px;
  fill: #94a3b8;
  font-weight: 500;
}

.empty-chart {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  font-style: italic;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  transition: all 0.2s ease;
  background: var(--gold);
  border: 1px solid var(--gold);
  color: white;
  cursor: pointer;
}

.action-btn:hover {
  background: #b08d44;
  border-color: #b08d44;
  color: white;
}

.action-btn svg {
  width: 16px;
  height: 16px;
}
</style>
