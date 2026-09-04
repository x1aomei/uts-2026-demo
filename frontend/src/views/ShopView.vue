<template>
  <div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold mb-4">Shop</h1>
      <p class="text-gray-600">Temukan koleksi terbaik untuk gaya Anda</p>
    </div>
    
    <!-- Filters -->
    <div class="flex flex-wrap gap-4 mb-8">
      <select v-model="filterCategory" class="input-field w-48">
        <option value="">Semua Kategori</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
      </select>
      
      <select v-model="sortBy" class="input-field w-48">
        <option value="newest">Terbaru</option>
        <option value="price_low">Harga Terendah</option>
        <option value="price_high">Harga Tertinggi</option>
        <option value="popular">Terlaris</option>
      </select>
      
      <div class="flex-1"></div>
      <span class="text-gray-600 self-center">{{ filteredProducts.length }} produk</span>
    </div>
    
    <!-- Product Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <div v-if="filteredProducts.length === 0" class="col-span-full text-center py-16">
        <p class="text-gray-500 text-lg">Tidak ada produk ditemukan</p>
      </div>
      <ProductCard 
        v-for="product in filteredProducts" 
        :key="product.id" 
        :product="product"
      />
    </div>
    
    <!-- Load More -->
    <div v-if="hasMore" class="text-center mt-8">
      <button @click="loadMore" class="btn-outline" :disabled="loading">
        {{ loading ? 'Loading...' : 'Load More' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../services/api'
import ProductCard from '../components/product/ProductCard.vue'

const route = useRoute()
const products = ref([])
const categories = ref([])
const filterCategory = ref(route.query.category || '')
const sortBy = ref('newest')
const loading = ref(false)
const hasMore = ref(true)
const page = ref(1)

const filteredProducts = computed(() => {
  let result = [...products.value]
  
  // Filter by category
  if (filterCategory.value) {
    result = result.filter(p => p.category_id === parseInt(filterCategory.value))
  }
  
  // Sort
  switch (sortBy.value) {
    case 'price_low':
      result.sort((a, b) => a.base_price - b.base_price)
      break
    case 'price_high':
      result.sort((a, b) => b.base_price - a.base_price)
      break
    case 'popular':
      result.sort((a, b) => (b.sold_count || 0) - (a.sold_count || 0))
      break
    default:
      // newest - sort by created_at desc
      result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
  }
  
  return result
})

const fetchProducts = async () => {
  loading.value = true
  try {
    const response = await api.get('/products', {
      params: {
        page: page.value,
        category: filterCategory.value,
        sort: sortBy.value
      }
    })
    
    products.value = [...products.value, ...response.data.data]
    hasMore.value = response.data.current_page < response.data.last_page
  } catch (error) {
    console.error('Failed to fetch products:', error)
  } finally {
    loading.value = false
  }
}

const fetchCategories = async () => {
  try {
    const response = await api.get('/categories')
    categories.value = response.data
  } catch (error) {
    console.error('Failed to fetch categories:', error)
  }
}

const loadMore = () => {
  page.value++
  fetchProducts()
}

onMounted(() => {
  fetchCategories()
  fetchProducts()
})
</script>