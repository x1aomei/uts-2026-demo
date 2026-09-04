<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-2xl font-bold">Laporan Penjualan</h2>
        <p class="text-gray-500 mt-1">Analisis dan export laporan penjualan</p>
      </div>
      
      <div class="flex space-x-3">
        <button @click="exportSalesReport" class="btn-primary">
          <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Export PDF
        </button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white p-6 rounded-xl shadow-sm">
        <p class="text-gray-500 text-sm mb-2">Total Pendapatan</p>
        <p class="text-3xl font-bold text-green-600">Rp{{ formatPrice(totalRevenue) }}</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow-sm">
        <p class="text-gray-500 text-sm mb-2">Total Pesanan</p>
        <p class="text-3xl font-bold">{{ orders.length }}</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow-sm">
        <p class="text-gray-500 text-sm mb-2">Pesanan Pending</p>
        <p class="text-3xl font-bold text-yellow-600">{{ pendingOrders }}</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow-sm">
        <p class="text-gray-500 text-sm mb-2">Pesanan Sukses</p>
        <p class="text-3xl font-bold text-blue-600">{{ completedOrders }}</p>
      </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
      <h3 class="text-lg font-semibold mb-4">Filter Laporan</h3>
      <div class="flex gap-4">
        <div>
          <label class="block text-sm text-gray-600 mb-1">Dari Tanggal</label>
          <input v-model="dateFrom" type="date" class="input-field">
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Sampai Tanggal</label>
          <input v-model="dateTo" type="date" class="input-field">
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Status</label>
          <select v-model="filterStatus" class="input-field">
            <option value="">Semua</option>
            <option value="pending">Pending</option>
            <option value="completed">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
          </select>
        </div>
        <button @click="applyFilter" class="btn-primary self-end">Terapkan</button>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
      <div class="p-6 border-b">
        <h3 class="text-lg font-semibold">Daftar Pesanan</h3>
      </div>
      
      <table class="w-full text-left">
        <thead class="bg-gray-50">
          <tr>
            <th class="py-3 px-6 font-semibold">No. Pesanan</th>
            <th class="py-3 px-6 font-semibold">Tanggal</th>
            <th class="py-3 px-6 font-semibold">Pelanggan</th>
            <th class="py-3 px-6 font-semibold">Jumlah Item</th>
            <th class="py-3 px-6 font-semibold">Total</th>
            <th class="py-3 px-6 font-semibold">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filteredOrders.length === 0" class="text-center text-gray-500 py-8">
            <td colspan="6">Tidak ada data untuk ditampilkan</td>
          </tr>
          <tr v-for="order in filteredOrders" :key="order.id" class="border-t hover:bg-gray-50">
            <td class="py-3 px-6 font-medium">{{ order.order_number }}</td>
            <td class="py-3 px-6">{{ formatDate(order.created_at) }}</td>
            <td class="py-3 px-6">{{ order.user?.name }}</td>
            <td class="py-3 px-6">{{ order.order_items?.length || 0 }}</td>
            <td class="py-3 px-6">Rp{{ formatPrice(order.total_amount) }}</td>
            <td class="py-3 px-6">
              <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="getStatusClass(order.status)">
                {{ getStatusLabel(order.status) }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'
import jsPDF from 'jspdf'
import 'jspdf-autotable'

const orders = ref([])
const dateFrom = ref('')
const dateTo = ref('')
const filterStatus = ref('')

const filteredOrders = computed(() => {
  return orders.value.filter(order => {
    // Filter by status
    if (filterStatus.value && order.status !== filterStatus.value) return false
    
    // Filter by date
    if (dateFrom.value && new Date(order.created_at) < new Date(dateFrom.value)) return false
    if (dateTo.value && new Date(order.created_at) > new Date(dateTo.value)) return false
    
    return true
  })
})

const totalRevenue = computed(() => {
  return filteredOrders.value
    .filter(order => ['paid', 'completed'].includes(order.status))
    .reduce((sum, order) => sum + order.total_amount, 0)
})

const pendingOrders = computed(() => {
  return filteredOrders.value.filter(order => order.status === 'pending').length
})

const completedOrders = computed(() => {
  return filteredOrders.value.filter(order => ['paid', 'completed'].includes(order.status)).length
})

const fetchOrders = async () => {
  try {
    const response = await api.get('/admin/orders')
    orders.value = response.data
  } catch (error) {
    console.error('Failed to fetch orders:', error)
  }
}

const applyFilter = () => {
  // Filtering happens automatically via computed
  console.log('Filter applied')
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric'
  })
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Pending',
    paid: 'Dibayar',
    shipped: 'Dikirim',
    completed: 'Selesai',
    cancelled: 'Dibatalkan'
  }
  return labels[status] || status
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-700',
    paid: 'bg-blue-100 text-blue-700',
    shipped: 'bg-purple-100 text-purple-700',
    completed: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700'
  }
  return classes[status] || 'bg-gray-100 text-gray-700'
}

const exportSalesReport = () => {
  const doc = new jsPDF()
  
  // Title
  doc.setFontSize(20)
  doc.setTextColor(0, 0, 0)
  doc.text('Laporan Penjualan Cloth Store', 14, 20)
  
  doc.setFontSize(12)
  doc.setTextColor(100)
  doc.text(`Periode: ${dateFrom.value || 'Awal'} - ${dateTo.value || 'Sekarang'}`, 14, 30)
  
  // Summary
  doc.setFontSize(14)
  doc.setTextColor(0, 0, 0)
  doc.text(`Total Pendapatan: Rp${formatPrice(totalRevenue.value)}`, 14, 45)
  doc.text(`Total Pesanan: ${filteredOrders.value.length}`, 14, 55)
  doc.text(`Pesanan Pending: ${pendingOrders.value}`, 14, 65)
  doc.text(`Pesanan Sukses: ${completedOrders.value}`, 14, 75)
  
  // Table
  const tableBody = filteredOrders.value.map(order => [
    order.order_number,
    formatDate(order.created_at),
    order.user?.name || '-',
    order.order_items?.length || 0,
    `Rp${formatPrice(order.total_amount)}`,
    getStatusLabel(order.status)
  ])
  
  doc.autoTable({
    head: [['No. Pesanan', 'Tanggal', 'Pelanggan', 'Items', 'Total', 'Status']],
    body: tableBody,
    startY: 90,
    theme: 'grid',
    styles: { fontSize: 10 },
    headStyles: { fillColor: [2, 132, 199], textColor: 255 }
  })
  
  // Save PDF
  doc.save(`laporan-penjualan-${new Date().toISOString().split('T')[0]}.pdf`)
}

onMounted(() => {
  fetchOrders()
})
</script>