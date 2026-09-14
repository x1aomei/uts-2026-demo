<template>
  <div>
    <!-- Hero Section -->
    <section class="relative h-[80vh] bg-gray-900 overflow-hidden">
      <img
        src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=2070&auto=format&fit=crop"
        alt="Hero"
        class="absolute inset-0 w-full h-full object-cover opacity-60"
      >
      <div class="relative z-10 flex flex-col items-center justify-center h-full text-center px-4">
        <h1 class="text-5xl md:text-7xl font-bold text-white mb-4">
          STREET WEAR <span class="text-primary-400">2026</span>
        </h1>
        <p class="text-xl md:text-2xl text-gray-300 mb-8 max-w-2xl">
          Koleksi terbaru untuk gaya urban Anda
        </p>
        <router-link to="/shop" class="btn-primary text-lg px-8 py-4">
          Belanja Sekarang
        </router-link>
      </div>
    </section>

    <!-- Categories -->
    <section class="container mx-auto px-4 py-16">
      <h2 class="text-3xl font-bold text-center mb-8">Kategori Populer</h2>

      <div v-if="isLoadingCategories" class="text-center text-gray-500 py-8">Memuat kategori...</div>
      <div v-else-if="categories.length === 0" class="text-center text-gray-500 py-8">
        Belum ada kategori.
      </div>

      <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <router-link
          v-for="category in categories"
          :key="category.id"
          :to="`/shop?category=${category.id}`"
          class="group relative overflow-hidden rounded-lg aspect-square"
        >
          <img
            v-if="category.image"
            :src="category.image"
            :alt="category.name"
            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
          >
          <div v-else class="w-full h-full bg-gray-200"></div>
          <div class="absolute inset-0 bg-black/40 group-hover:bg-black/50 transition-colors"></div>
          <span class="absolute bottom-4 left-4 text-white text-xl font-bold">{{ category.name }}</span>
        </router-link>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="bg-gray-50 py-16">
      <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
          <h2 class="text-3xl font-bold">Produk Unggulan</h2>
          <router-link to="/shop" class="text-primary-600 hover:text-primary-700 font-medium">
            Lihat Semua &rarr;
          </router-link>
        </div>

        <div v-if="isLoadingProducts" class="text-center text-gray-500 py-8">Memuat produk...</div>
        <div v-else-if="featuredProducts.length === 0" class="text-center text-gray-500 py-8">
          Belum ada produk unggulan.
        </div>

        <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-6">
          <ProductCard v-for="product in featuredProducts" :key="product.id" :product="product" />
        </div>
      </div>
    </section>

    <!-- Promo Banner -->
    <section class="container mx-auto px-4 py-16">
      <div class="bg-gradient-to-r from-primary-600 to-primary-800 rounded-2xl p-8 md:p-12 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Gunakan Kode: <span class="text-yellow-400">CLOTH20</span></h2>
        <p class="text-white/90 text-lg mb-6">Dapatkan diskon 20% untuk pembelian pertama Anda</p>
        <router-link to="/shop" class="btn-white text-lg">
          Belanja Sekarang
        </router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import ProductCard from '../components/product/ProductCard.vue'
// FIX: sebelumnya '../utils/api' -- disamain ke '../services/api'
// biar konsisten sama file lain (ShopView, CartView, dll)
import api from '../services/api'

const categories = ref([])
const featuredProducts = ref([])
const isLoadingCategories = ref(true)
const isLoadingProducts = ref(true)

const fetchCategories = async () => {
  isLoadingCategories.value = true
  try {
    // FIX: fetch kategori beneran dari API, bukan data hardcode
    const response = await api.get('/categories')
    categories.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to fetch categories:', error)
  } finally {
    isLoadingCategories.value = false
  }
}

const fetchProducts = async () => {
  isLoadingProducts.value = true
  try {
    const response = await api.get('/products', { params: { featured: true, limit: 4 } })
    featuredProducts.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to fetch featured products:', error)
  } finally {
    isLoadingProducts.value = false
  }
}

onMounted(() => {
  fetchCategories()
  fetchProducts()
})
</script>