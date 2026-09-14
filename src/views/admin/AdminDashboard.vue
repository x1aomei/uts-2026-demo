<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../services/api'

const isLoading = ref(true)
const errorMessage = ref('')

const stats = ref({
  total_products: 0,
  total_orders: 0,
  total_customers: 0,
  total_revenue: 0,
})

const recentOrders = ref([])

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price || 0)
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

const statusLabel = (status) => {
  const labels = {
    pending: 'Menunggu',
    processing: 'Diproses',
    shipped: 'Dikirim',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
  }
  return labels[status] || status
}

const fetchDashboard = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const response = await api.get('/admin/dashboard')
    const data = response.data.data || response.data

    stats.value = {
      total_products: data.total_products ?? 0,
      total_orders: data.total_orders ?? 0,
      total_customers: data.total_customers ?? 0,
      total_revenue: data.total_revenue ?? 0,
    }
    recentOrders.value = data.recent_orders || []
  } catch (error) {
    console.error('Failed to fetch dashboard:', error)
    errorMessage.value = 'Gagal memuat data dashboard.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchDashboard()
})
</script>

<template>
  <div class="admin-dashboard">
    <div class="page-header">
      <div>
        <h1>Dashboard</h1>
        <p class="text-muted">Ringkasan performa toko kamu</p>
      </div>
    </div>

    <p v-if="errorMessage" class="error-text">{{ errorMessage }}</p>

    <!-- Stat Cards -->
    <div class="stats-grid">
      <div class="glass-card stat-card">
        <span class="stat-icon">📦</span>
        <div>
          <p class="stat-label">Total Produk</p>
          <p class="stat-value">{{ isLoading ? '...' : stats.total_products }}</p>
        </div>
      </div>

      <div class="glass-card stat-card">
        <span class="stat-icon">🛒</span>
        <div>
          <p class="stat-label">Total Pesanan</p>
          <p class="stat-value">{{ isLoading ? '...' : stats.total_orders }}</p>
        </div>
      </div>

      <div class="glass-card stat-card">
        <span class="stat-icon">👥</span>
        <div>
          <p class="stat-label">Total Pelanggan</p>
          <p class="stat-value">{{ isLoading ? '...' : stats.total_customers }}</p>
        </div>
      </div>

      <div class="glass-card stat-card">
        <span class="stat-icon">💰</span>
        <div>
          <p class="stat-label">Total Pendapatan</p>
          <p class="stat-value">Rp{{ isLoading ? '...' : formatPrice(stats.total_revenue) }}</p>
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="glass-card table-card">
      <div class="table-header">
        <h3>Pesanan Terbaru</h3>
        <RouterLink to="/admin/pesanan" class="link">Lihat Semua &rarr;</RouterLink>
      </div>

      <div v-if="isLoading" class="state-message">Memuat pesanan...</div>
      <div v-else-if="recentOrders.length === 0" class="state-message">Belum ada pesanan.</div>

      <table v-else class="data-table">
        <thead>
          <tr>
            <th>No. Pesanan</th>
            <th>Pelanggan</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th class="text-right">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in recentOrders" :key="order.id">
            <td>#{{ order.id }}</td>
            <td>{{ order.customer_name || order.user?.name || '-' }}</td>
            <td>{{ formatDate(order.created_at) }}</td>
            <td>
              <span class="badge" :class="`badge-${order.status}`">
                {{ statusLabel(order.status) }}
              </span>
            </td>
            <td class="text-right">Rp{{ formatPrice(order.total) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.page-header {
  margin-bottom: 1.5rem;
}

.page-header h1 {
  margin: 0 0 0.25rem 0;
  font-size: 1.5rem;
}

.text-muted {
  color: var(--text-muted);
  margin: 0;
}

.error-text {
  color: #dc2626;
  margin-bottom: 1rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.25rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
}

.stat-icon {
  font-size: 1.75rem;
}

.stat-label {
  margin: 0 0 0.25rem 0;
  color: var(--text-muted);
  font-size: 0.85rem;
}

.stat-value {
  margin: 0;
  font-size: 1.4rem;
  font-weight: 700;
}

.table-card {
  padding: 0;
  overflow: hidden;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.table-header h3 {
  margin: 0;
  font-size: 1.05rem;
}

.link {
  color: var(--primary-color);
  font-size: 0.9rem;
  font-weight: 500;
}

.state-message {
  padding: 3rem 1.5rem;
  text-align: center;
  color: var(--text-muted);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 0.9rem 1.5rem;
  text-align: left;
  border-bottom: 1px solid var(--border-color);
}

.data-table th {
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: var(--text-muted);
  font-weight: 600;
}

.text-right {
  text-align: right;
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.7rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 600;
}

.badge-pending { background: #fef3c7; color: #92400e; }
.badge-processing { background: #dbeafe; color: #1e40af; }
.badge-shipped { background: #e0e7ff; color: #3730a3; }
.badge-completed { background: #d1fae5; color: #065f46; }
.badge-cancelled { background: #fee2e2; color: #991b1b; }
</style>
