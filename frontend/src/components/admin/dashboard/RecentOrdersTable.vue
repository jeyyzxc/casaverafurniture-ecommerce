<template>
  <article class="dashboard-card">
    <header class="card-header">
      <h3 class="card-title">Recent Orders</h3>
      <router-link to="/admin/orders" class="view-all">View All</router-link>
    </header>
    <div class="table-responsive">
      <table v-if="orders.length > 0" class="dashboard-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id" @click="$emit('view-order', order.id)" class="clickable-row">
            <td class="order-id">#{{ order.id }}</td>
            <td class="customer-name">{{ order.customer }}</td>
            <td class="amount">₱{{ formatPrice(order.amount) }}</td>
            <td>
              <span class="status-badge" :class="order.status.toLowerCase()">
                {{ order.status }}
              </span>
            </td>
            <td class="date">{{ formatDate(order.date) }}</td>
          </tr>
        </tbody>
      </table>
      <div v-else class="empty-state">
        <p>No recent orders found</p>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
defineProps<{
  orders: Array<{
    id: string
    customer: string
    amount: number
    status: string
    date: Date
  }>
}>()

defineEmits(['view-order'])

const formatPrice = (price: number) => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatDate = (date: Date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
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

.table-responsive {
  width: 100%;
  overflow: auto;
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;  /* Firefox */
}

.table-responsive::-webkit-scrollbar {
  display: none; /* Chrome, Safari and Opera */
}

.dashboard-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

.dashboard-table th {
  padding: 1.25rem 1.5rem;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
  color: #64748b;
  background: #f8fafc;
}

.dashboard-table td {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.875rem;
  color: #1e293b;
}

.clickable-row {
  cursor: pointer;
  transition: background 0.2s;
}

.clickable-row:hover {
  background: #f8fafc;
}

.order-id {
  font-weight: 700;
  color: var(--gold);
}

.customer-name {
  font-weight: 500;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.status-badge.pending { background: #fffbeb; color: #d97706; }
.status-badge.processing { background: #eff6ff; color: #2563eb; }
.status-badge.shipped { background: #faf5ff; color: #7c3aed; }
.status-badge.delivered { background: #ecfdf5; color: #059669; }
.status-badge.cancelled { background: #fef2f2; color: #dc2626; }

.empty-state {
  padding: 3rem;
  text-align: center;
  color: #94a3b8;
  font-style: italic;
}
</style>
