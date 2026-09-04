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
      <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <router-link 
          v-for="category in categories" 
          :key="category.id"
          :to="`/shop?category=${category.slug}`"
          class="group relative overflow-hidden rounded-lg aspect-square"
        >
          <img :src="category.image" :alt="category.name" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
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
            Lihat Semua →
          </router-link>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
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
import api from '../utils/api'

const categories = ref([
  { id: 1, name: 'Men', slug: 'men', image: 'https://images.unsplash.com/photo-1516257984-b1b4d707412e?q=80&w=1000&auto=format&fit=crop' },
  { id: 2, name: 'Women', slug: 'women', image: 'https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=1000&auto=format&fit=crop' },
  { id: 3, name: 'Streetwear', slug: 'streetwear', image: 'https://images.unsplash.com/photo-1523398002811-999ca8dec234?q=80&w=1000&auto=format&fit=crop' },
  { id: 4, name: 'Sport', slug: 'sport', image: 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?q=80&w=1000&auto=format&fit=crop' },
])

const featuredProducts = ref([])

const fetchProducts = async () => {
  try {
    const response = await api.get('/products', { params: { featured: true, limit: 4 } })
    featuredProducts.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to fetch featured products:', error)
  }
}

onMounted(() => {
  fetchProducts()
})
</script>