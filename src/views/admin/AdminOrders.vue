<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-2xl font-bold">Manajemen Pesanan</h2>
        <p class="text-gray-500 mt-1">Kelola dan pantau pesanan pelanggan</p>
      </div>
      
      <!-- Filter Status -->
      <select v-model="filterStatus" class="input-field max-w-xs">
        <option value="">Semua Status</option>
        <option value="pending">Pending</option>
        <option value="paid">Dibayar</option>
        <option value="shipped">Dikirim</option>
        <option value="completed">Selesai</option>
        <option value="cancelled">Dibatalkan</option>
      </select>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
      <table class="w-full text-left">
        <thead class="bg-gray-50">
          <tr>
            <th class="py-3 px-6 font-semibold">No. Pesanan</th>
            <th class="py-3 px-6 font-semibold">Pelanggan</th>
            <th class="py-3 px-6 font-semibold">Total</th>
            <th class="py-3 px-6 font-semibold">Status</th>
            <th class="py-3 px-6 font-semibold">Tanggal</th>
            <th class="py-3 px-6 font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filteredOrders.length === 0" class="text-center text-gray-500 py-8">
            <td colspan="6">Tidak ada pesanan</td>
          </tr>
          <tr v-for="order in filteredOrders" :key="order.id" class="border-t hover:bg-gray-50">
            <td class="py-3 px-6 font-medium">{{ order.order_number }}</td>
            <td class="py-3 px-6">
              <div>
                <p class="font-medium">{{ order.user?.name }}</p>
                <p class="text-sm text-gray-500">{{ order.user?.email }}</p>
              </div>
            </td>
            <td class="py-3 px-6">Rp{{ formatPrice(order.total_amount) }}</td>
            <td class="py-3 px-6">
              <select 
                :value="order.status"
                @change="updateOrderStatus(order.id, $event.target.value)"
                class="px-2 py-1 rounded-full text-xs font-semibold border"
                :class="getStatusClass(order.status)"
              >
                <option value="pending">Pending</option>
                <option value="paid">Dibayar</option>
                <option value="shipped">Dikirim</option>
                <option value="completed">Selesai</option>
                <option value="cancelled">Dibatalkan</option>
              </select>
            </td>
            <td class="py-3 px-6">{{ formatDate(order.created_at) }}</td>
            <td class="py-3 px-6">
              <button @click="viewOrderDetails(order)" class="text-blue-600 hover:text-blue-700">
                Detail
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Order Detail Modal -->
    <div v-if="selectedOrder" class="fixed inset-0 z-50 flex items-center justify-center">
      <div @click="selectedOrder = null" class="absolute inset-0 bg-black/50"></div>
      <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full p-6">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold">Detail Pesanan</h3>
          <button @click="selectedOrder = null" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500">No. Pesanan</p>
              <p class="font-semibold">{{ selectedOrder.order_number }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Tanggal</p>
              <p class="font-semibold">{{ formatDate(selectedOrder.created_at) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Pelanggan</p>
              <p class="font-semibold">{{ selectedOrder.user?.name }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Status</p>
              <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="getStatusClass(selectedOrder.status)">
                {{ getStatusLabel(selectedOrder.status) }}
              </span>
            </div>
          </div>
          
          <div>
            <h4 class="font-semibold mb-2">Item Pesanan</h4>
            <div class="border rounded-lg p-3">
              <div v-for="item in selectedOrder.order_items" :key="item.id" class="flex justify-between py-2 border-b last:border-0">
                <div>
                  <p class="font-medium">{{ item.product?.name }}</p>
                  <p class="text-sm text-gray-500">{{ item.quantity }} x Rp{{ formatPrice(item.price_at_purchase) }}</p>
                </div>
                <span class="font-semibold">Rp{{ formatPrice(item.price_at_purchase * item.quantity) }}</span>
              </div>
            </div>
          </div>
          
          <div class="border-t pt-4">
            <div class="flex justify-between">
              <span class="font-bold">Total</span>
              <span class="font-bold text-lg">Rp{{ formatPrice(selectedOrder.total_amount) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'

const orders = ref([])
const filterStatus = ref('')
const selectedOrder = ref(null)

const filteredOrders = computed(() => {
  if (!filterStatus.value) return orders.value
  return orders.value.filter(order => order.status === filterStatus.value)
})

const fetchOrders = async () => {
  try {
    const response = await api.get('/admin/orders')
    orders.value = response.data
  } catch (error) {
    console.error('Failed to fetch orders:', error)
  }
}

const updateOrderStatus = async (orderId, status) => {
  try {
    await api.put(`/admin/orders/${orderId}/status`, { status })
    const order = orders.value.find(o => o.id === orderId)
    if (order) {
      order.status = status
    }
  } catch (error) {
    console.error('Failed to update order status:', error)
    alert('Gagal update status pesanan')
  }
}

const viewOrderDetails = (order) => {
  selectedOrder.value = order
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
    pending: 'bg-yellow-100 text-yellow-700 border-yellow-300',
    paid: 'bg-blue-100 text-blue-700 border-blue-300',
    shipped: 'bg-purple-100 text-purple-700 border-purple-300',
    completed: 'bg-green-100 text-green-700 border-green-300',
    cancelled: 'bg-red-100 text-red-700 border-red-300'
  }
  return classes[status] || 'bg-gray-100 text-gray-700 border-gray-300'
}

onMounted(() => {
  fetchOrders()
})
</script>