<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-2xl font-bold">Pesanan</h2>
        <p class="text-gray-500 mt-1">Kelola dan pantau semua pesanan pelanggan</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-wrap gap-4 items-center">
      <input
        v-model="searchQuery"
        type="text"
        class="input-field w-64"
        placeholder="Cari no. pesanan atau nama pelanggan..."
      >
      <select v-model="filterStatus" class="input-field w-48">
        <option value="">Semua Status</option>
        <option value="pending">Menunggu</option>
        <option value="processing">Diproses</option>
        <option value="shipped">Dikirim</option>
        <option value="completed">Selesai</option>
        <option value="cancelled">Dibatalkan</option>
      </select>
      <span class="text-gray-500 text-sm ml-auto">{{ filteredOrders.length }} pesanan</span>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
      <div v-if="isLoading" class="text-center py-16 text-gray-500">Memuat pesanan...</div>
      <div v-else-if="errorMessage" class="text-center py-16 text-red-600">{{ errorMessage }}</div>
      <div v-else-if="filteredOrders.length === 0" class="text-center py-16 text-gray-500">
        Tidak ada pesanan ditemukan.
      </div>

      <table v-else class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">No. Pesanan</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Pelanggan</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Item</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Total</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
            <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 font-medium">#{{ order.id }}</td>
            <td class="px-6 py-4">{{ order.customer_name || order.user?.name || '-' }}</td>
            <td class="px-6 py-4 text-gray-500">{{ formatDate(order.created_at) }}</td>
            <td class="px-6 py-4 text-gray-500">{{ order.items_count ?? order.items?.length ?? '-' }} item</td>
            <td class="px-6 py-4 font-semibold">Rp{{ formatPrice(order.total) }}</td>
            <td class="px-6 py-4">
              <span class="px-2.5 py-1 rounded-full text-xs font-semibold" :class="statusClass(order.status)">
                {{ statusLabel(order.status) }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <button @click="openDetail(order)" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                Detail
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Detail / Update Status Modal -->
    <div v-if="selectedOrder" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="closeDetail">
      <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 max-h-[85vh] overflow-y-auto">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 class="text-lg font-bold">Pesanan #{{ selectedOrder.id }}</h3>
            <p class="text-sm text-gray-500">{{ formatDate(selectedOrder.created_at) }}</p>
          </div>
          <button type="button" @click="closeDetail" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Customer Info -->
        <div class="mb-4 pb-4 border-b">
          <p class="text-sm font-medium text-gray-700 mb-1">Pelanggan</p>
          <p class="text-sm text-gray-600">{{ selectedOrder.customer_name || selectedOrder.user?.name || '-' }}</p>
          <p class="text-sm text-gray-600">{{ selectedOrder.phone || '-' }}</p>
          <p class="text-sm text-gray-600">{{ selectedOrder.address || '-' }}</p>
        </div>

        <!-- Items -->
        <div v-if="selectedOrder.items?.length" class="mb-4 pb-4 border-b space-y-3">
          <p class="text-sm font-medium text-gray-700 mb-2">Item Pesanan</p>
          <div v-for="item in selectedOrder.items" :key="item.id" class="flex justify-between text-sm">
            <span class="text-gray-600">{{ item.product?.name || item.name }} &times; {{ item.quantity }}</span>
            <span class="font-medium">Rp{{ formatPrice((item.price || 0) * (item.quantity || 1)) }}</span>
          </div>
        </div>

        <!-- Total -->
        <div class="flex justify-between mb-4 pb-4 border-b">
          <span class="font-bold">Total</span>
          <span class="font-bold text-primary-600">Rp{{ formatPrice(selectedOrder.total) }}</span>
        </div>

        <!-- Update Status -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Ubah Status</label>
          <select v-model="newStatus" class="input-field w-full mb-3">
            <option value="pending">Menunggu</option>
            <option value="processing">Diproses</option>
            <option value="shipped">Dikirim</option>
            <option value="completed">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
          </select>

          <p v-if="updateError" class="text-sm text-red-600 mb-3">{{ updateError }}</p>

          <button
            @click="updateStatus"
            :disabled="isUpdating || newStatus === selectedOrder.status"
            class="btn-primary w-full disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ isUpdating ? 'Menyimpan...' : 'Simpan Status' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'

const orders = ref([])
const isLoading = ref(true)
const errorMessage = ref('')

const searchQuery = ref('')
const filterStatus = ref('')

const selectedOrder = ref(null)
const newStatus = ref('')
const isUpdating = ref(false)
const updateError = ref('')

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

const statusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-700',
    processing: 'bg-blue-100 text-blue-700',
    shipped: 'bg-indigo-100 text-indigo-700',
    completed: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
  }
  return classes[status] || 'bg-gray-100 text-gray-700'
}

const filteredOrders = computed(() => {
  let result = [...orders.value]

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(o =>
      String(o.id).includes(q) ||
      (o.customer_name || o.user?.name || '').toLowerCase().includes(q)
    )
  }

  if (filterStatus.value) {
    result = result.filter(o => o.status === filterStatus.value)
  }

  return result
})

const fetchOrders = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const response = await api.get('/admin/orders')
    orders.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to fetch orders:', error)
    errorMessage.value = 'Gagal memuat pesanan.'
  } finally {
    isLoading.value = false
  }
}

const openDetail = (order) => {
  selectedOrder.value = order
  newStatus.value = order.status
  updateError.value = ''
}

const closeDetail = () => {
  selectedOrder.value = null
}

const updateStatus = async () => {
  if (!selectedOrder.value) return
  updateError.value = ''
  isUpdating.value = true

  try {
    await api.patch(`/admin/orders/${selectedOrder.value.id}`, { status: newStatus.value })

    // Update state lokal biar tabel langsung ke-refresh tanpa fetch ulang
    const idx = orders.value.findIndex(o => o.id === selectedOrder.value.id)
    if (idx !== -1) {
      orders.value[idx].status = newStatus.value
    }
    selectedOrder.value.status = newStatus.value
    closeDetail()
  } catch (error) {
    console.error('Failed to update status:', error)
    updateError.value = 'Gagal memperbarui status pesanan.'
  } finally {
    isUpdating.value = false
  }
}

onMounted(() => {
  fetchOrders()
})
</script>