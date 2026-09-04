<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-2xl font-bold">Manajemen Produk</h2>
        <p class="text-gray-500 mt-1">Kelola produk toko Anda</p>
      </div>
      <router-link to="/admin/products/create" class="btn-primary">
        + Tambah Produk
      </router-link>
    </div>

    <!-- Filters -->
    <div class="flex gap-4 mb-6">
      <input v-model="searchQuery" type="text" placeholder="Cari produk..." class="input-field max-w-xs">
      <select v-model="filterCategory" class="input-field max-w-xs">
        <option value="">Semua Kategori</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
      </select>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
      <table class="w-full text-left">
        <thead class="bg-gray-50">
          <tr>
            <th class="py-3 px-6 font-semibold">Produk</th>
            <th class="py-3 px-6 font-semibold">Kategori</th>
            <th class="py-3 px-6 font-semibold">Harga</th>
            <th class="py-3 px-6 font-semibold">Stok</th>
            <th class="py-3 px-6 font-semibold">Status</th>
            <th class="py-3 px-6 font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filteredProducts.length === 0" class="text-center text-gray-500 py-8">
            <td colspan="6">Tidak ada produk ditemukan</td>
          </tr>
          <tr v-for="product in filteredProducts" :key="product.id" class="border-t hover:bg-gray-50">
            <td class="py-3 px-6">
              <div class="flex items-center space-x-3">
                <img :src="product.images?.[0]?.image_url || 'https://via.placeholder.com/50'" class="w-12 h-12 rounded-lg object-cover" alt="Product">
                <div>
                  <p class="font-medium">{{ product.name }}</p>
                  <p class="text-sm text-gray-500">{{ product.slug }}</p>
                </div>
              </div>
            </td>
            <td class="py-3 px-6">{{ product.category?.name }}</td>
            <td class="py-3 px-6">Rp{{ formatPrice(product.base_price) }}</td>
            <td class="py-3 px-6">
              <span :class="product.total_stock > 0 ? 'text-green-600' : 'text-red-600'">
                {{ product.total_stock || 0 }}
              </span>
            </td>
            <td class="py-3 px-6">
              <span class="px-2 py-1 rounded-full text-xs font-semibold" 
                :class="product.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                {{ product.is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td class="py-3 px-6">
              <div class="flex space-x-2">
                <router-link :to="`/admin/products/${product.id}/edit`" class="text-blue-600 hover:text-blue-700">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </router-link>
                <button @click="deleteProduct(product.id)" class="text-red-600 hover:text-red-700">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </div>
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

const products = ref([])
const categories = ref([])
const searchQuery = ref('')
const filterCategory = ref('')

const filteredProducts = computed(() => {
  return products.value.filter(product => {
    const matchesSearch = product.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesCategory = !filterCategory.value || product.category_id === parseInt(filterCategory.value)
    return matchesSearch && matchesCategory
  })
})

const fetchProducts = async () => {
  try {
    const response = await api.get('/admin/products')
    products.value = response.data
  } catch (error) {
    console.error('Failed to fetch products:', error)
  }
}

const fetchCategories = async () => {
  try {
    const response = await api.get('/admin/categories')
    categories.value = response.data
  } catch (error) {
    console.error('Failed to fetch categories:', error)
  }
}

const deleteProduct = async (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
    try {
      await api.delete(`/admin/products/${id}`)
      fetchProducts()
    } catch (error) {
      console.error('Failed to delete product:', error)
      alert('Gagal menghapus produk')
    }
  }
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

onMounted(() => {
  fetchProducts()
  fetchCategories()
})
</script>