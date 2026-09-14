<template>
  <div>
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
      <div>
        <h2 class="text-2xl font-bold">Laporan</h2>
        <p class="text-gray-500 mt-1">Ringkasan penjualan dan performa toko</p>
      </div>

      <!-- Date Range Filter -->
      <div class="flex items-center gap-2">
        <input v-model="startDate" type="date" class="input-field">
        <span class="text-gray-400">s/d</span>
        <input v-model="endDate" type="date" class="input-field">
        <button @click="fetchReport" class="btn-primary">Terapkan</button>
      </div>
    </div>

    <div v-if="isLoading" class="text-center py-16 text-gray-500">Memuat laporan...</div>
    <div v-else-if="errorMessage" class="text-center py-16 text-red-600">{{ errorMessage }}</div>

    <div v-else>
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-5">
          <p class="text-sm text-gray-500 mb-1">Total Pendapatan</p>
          <p class="text-2xl font-bold text-primary-600">Rp{{ formatPrice(summary.total_revenue) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
          <p class="text-sm text-gray-500 mb-1">Total Pesanan</p>
          <p class="text-2xl font-bold">{{ summary.total_orders ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
          <p class="text-sm text-gray-500 mb-1">Rata-rata / Pesanan</p>
          <p class="text-2xl font-bold">Rp{{ formatPrice(averageOrderValue) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
          <p class="text-sm text-gray-500 mb-1">Produk Terjual</p>
          <p class="text-2xl font-bold">{{ summary.total_items_sold ?? 0 }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales by Day -->
        <div class="bg-white rounded-xl shadow-sm p-6">
          <h3 class="text-lg font-semibold mb-4">Penjualan Harian</h3>

          <div v-if="salesByDay.length === 0" class="text-center py-8 text-gray-500">
            Tidak ada data pada rentang tanggal ini.
          </div>

          <div v-else class="space-y-3">
            <div v-for="day in salesByDay" :key="day.date" class="flex items-center gap-3">
              <span class="text-sm text-gray-500 w-20 shrink-0">{{ formatDateShort(day.date) }}</span>
              <div class="flex-1 bg-gray-100 rounded-full h-3 overflow-hidden">
                <div
                  class="bg-primary-600 h-full rounded-full"
                  :style="{ width: barWidth(day.revenue) + '%' }"
                ></div>
              </div>
              <span class="text-sm font-medium w-28 text-right shrink-0">Rp{{ formatPrice(day.revenue) }}</span>
            </div>
          </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white rounded-xl shadow-sm p-6">
          <h3 class="text-lg font-semibold mb-4">Produk Terlaris</h3>

          <div v-if="topProducts.length === 0" class="text-center py-8 text-gray-500">
            Belum ada data produk terjual.
          </div>

          <div v-else class="space-y-4">
            <div v-for="(product, index) in topProducts" :key="product.id" class="flex items-center gap-3">
              <span class="text-sm font-semibold text-gray-400 w-5">{{ index + 1 }}</span>
              <img
                v-if="product.image"
                :src="product.image"
                :alt="product.name"
                class="w-10 h-10 object-cover rounded-lg"
              >
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ product.name }}</p>
                <p class="text-xs text-gray-500">{{ product.sold_count }} terjual</p>
              </div>
              <span class="text-sm font-semibold">Rp{{ formatPrice(product.revenue) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Orders by Status -->
      <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
        <h3 class="text-lg font-semibold mb-4">Pesanan berdasarkan Status</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
          <div v-for="(count, status) in ordersByStatus" :key="status" class="text-center p-4 rounded-lg" :class="statusBg(status)">
            <p class="text-2xl font-bold">{{ count }}</p>
            <p class="text-sm">{{ statusLabel(status) }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'

const isLoading = ref(true)
const errorMessage = ref('')

const today = new Date().toISOString().slice(0, 10)
const thirtyDaysAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10)

const startDate = ref(thirtyDaysAgo)
const endDate = ref(today)

const summary = ref({
  total_revenue: 0,
  total_orders: 0,
  total_items_sold: 0,
})
const salesByDay = ref([])
const topProducts = ref([])
const ordersByStatus = ref({})

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price || 0)
}

const formatDateShort = (date) => {
  return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })
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

const statusBg = (status) => {
  const classes = {
    pending: 'bg-yellow-50 text-yellow-700',
    processing: 'bg-blue-50 text-blue-700',
    shipped: 'bg-indigo-50 text-indigo-700',
    completed: 'bg-green-50 text-green-700',
    cancelled: 'bg-red-50 text-red-700',
  }
  return classes[status] || 'bg-gray-50 text-gray-700'
}

const averageOrderValue = computed(() => {
  if (!summary.value.total_orders) return 0
  return summary.value.total_revenue / summary.value.total_orders
})

const maxDailyRevenue = computed(() => {
  return Math.max(...salesByDay.value.map(d => d.revenue), 1)
})

const barWidth = (revenue) => {
  return Math.max((revenue / maxDailyRevenue.value) * 100, 2)
}

const fetchReport = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const response = await api.get('/admin/reports', {
      params: {
        start_date: startDate.value,
        end_date: endDate.value,
      },
    })
    const data = response.data.data || response.data

    summary.value = {
      total_revenue: data.total_revenue ?? 0,
      total_orders: data.total_orders ?? 0,
      total_items_sold: data.total_items_sold ?? 0,
    }
    salesByDay.value = data.sales_by_day || []
    topProducts.value = data.top_products || []
    ordersByStatus.value = data.orders_by_status || {}
  } catch (error) {
    console.error('Failed to fetch report:', error)
    errorMessage.value = 'Gagal memuat laporan.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchReport()
})
</script>