<template>
  <div class="container mx-auto px-4 py-8">
    <!-- Loading -->
    <div v-if="isLoading" class="text-center py-24 text-gray-500">
      Memuat produk...
    </div>

    <!-- Not Found -->
    <div v-else-if="!product" class="text-center py-24">
      <p class="text-gray-500 text-lg mb-4">Produk tidak ditemukan</p>
      <router-link to="/shop" class="btn-primary inline-block">
        Kembali ke Toko
      </router-link>
    </div>

    <!-- Product Detail -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-10">
      <!-- Image Gallery -->
      <div>
        <div class="aspect-square bg-gray-50 rounded-xl overflow-hidden mb-4">
          <img :src="activeImage" :alt="product.name" class="w-full h-full object-cover" />
        </div>

        <div v-if="product.images?.length > 1" class="grid grid-cols-4 gap-3">
          <button
            v-for="(img, idx) in product.images"
            :key="idx"
            @click="activeImage = img"
            class="aspect-square rounded-lg overflow-hidden border-2 transition"
            :class="activeImage === img ? 'border-primary-600' : 'border-transparent'"
          >
            <img :src="img" :alt="`${product.name} ${idx + 1}`" class="w-full h-full object-cover" />
          </button>
        </div>
      </div>

      <!-- Info -->
      <div>
        <p v-if="product.category" class="text-sm text-gray-500 mb-1">{{ product.category }}</p>
        <h1 class="text-2xl sm:text-3xl font-bold mb-3">{{ product.name }}</h1>
        <p class="text-2xl font-bold text-primary-600 mb-6">Rp{{ formatPrice(product.price) }}</p>

        <p v-if="product.description" class="text-gray-600 mb-6 leading-relaxed">
          {{ product.description }}
        </p>

        <!-- Size -->
        <div v-if="product.sizes?.length" class="mb-6">
          <p class="text-sm font-medium text-gray-700 mb-2">Ukuran</p>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="size in product.sizes"
              :key="size"
              @click="selectedSize = size"
              class="px-4 py-2 rounded-lg border text-sm font-medium transition"
              :class="selectedSize === size
                ? 'border-primary-600 bg-primary-50 text-primary-600'
                : 'border-gray-300 text-gray-700 hover:border-gray-400'"
            >
              {{ size }}
            </button>
          </div>
        </div>

        <!-- Color -->
        <div v-if="product.colors?.length" class="mb-6">
          <p class="text-sm font-medium text-gray-700 mb-2">Warna</p>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="color in product.colors"
              :key="color"
              @click="selectedColor = color"
              class="px-4 py-2 rounded-lg border text-sm font-medium transition"
              :class="selectedColor === color
                ? 'border-primary-600 bg-primary-50 text-primary-600'
                : 'border-gray-300 text-gray-700 hover:border-gray-400'"
            >
              {{ color }}
            </button>
          </div>
        </div>

        <!-- Quantity -->
        <div class="mb-8">
          <p class="text-sm font-medium text-gray-700 mb-2">Jumlah</p>
          <div class="flex items-center border rounded-lg w-fit">
            <button @click="quantity = Math.max(1, quantity - 1)" class="px-4 py-2 text-gray-600 hover:text-primary-600">-</button>
            <span class="px-4 py-2 text-sm font-medium">{{ quantity }}</span>
            <button @click="quantity++" class="px-4 py-2 text-gray-600 hover:text-primary-600">+</button>
          </div>
        </div>

        <p v-if="feedbackMessage" class="text-sm text-green-600 mb-3">{{ feedbackMessage }}</p>
        <p v-if="errorMessage" class="text-sm text-red-600 mb-3">{{ errorMessage }}</p>

        <!-- Actions -->
        <div class="flex gap-3">
          <button
            @click="handleAddToCart"
            :disabled="isAdding"
            class="btn-primary flex-1 text-center disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ isAdding ? 'Menambahkan...' : 'Tambah ke Keranjang' }}
          </button>
          <button
            @click="handleToggleWishlist"
            class="w-12 h-12 flex items-center justify-center border rounded-lg transition"
            :class="isWishlisted ? 'border-red-500 text-red-500' : 'border-gray-300 text-gray-500 hover:border-gray-400'"
          >
            <svg class="w-5 h-5" :fill="isWishlisted ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useCart } from '../composables/useCart'
import { useWishlist } from '../composables/useWishlist'
// TODO: sesuaikan import ini dengan service produk kamu yang sebenarnya
// import { getProductById } from '../router/services/productService'
import api from '../services/api'

const route = useRoute()
const { addToCart } = useCart()
const { wishlistItems, toggleWishlist } = useWishlist()

const product = ref(null)
const isLoading = ref(true)
const activeImage = ref('')
const selectedSize = ref(null)
const selectedColor = ref(null)
const quantity = ref(1)
const isAdding = ref(false)
const feedbackMessage = ref('')
const errorMessage = ref('')

const isWishlisted = computed(() =>
  wishlistItems?.value?.some((w) => w.product?.id === product.value?.id || w.productId === product.value?.id)
)

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price || 0)
}

const fetchProduct = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    // TODO: ganti dengan endpoint produk asli kamu kalau beda
    const response = await api.get(`/products/${route.params.id}`)
    product.value = response.data.data || response.data
  } catch (err) {
    console.error('Failed to fetch product:', err)
    product.value = null
  } finally {
    isLoading.value = false
  }
}

watch(product, (val) => {
  if (val) {
    activeImage.value = val.image || val.images?.[0] || ''
    selectedSize.value = val.sizes?.[0] || null
    selectedColor.value = val.colors?.[0] || null
  }
})

const handleAddToCart = async () => {
  errorMessage.value = ''
  feedbackMessage.value = ''
  isAdding.value = true

  try {
    await addToCart(product.value, quantity.value, selectedSize.value, selectedColor.value)
    feedbackMessage.value = 'Berhasil ditambahkan ke keranjang!'
  } catch (err) {
    errorMessage.value = err?.response?.data?.message || 'Gagal menambahkan ke keranjang.'
  } finally {
    isAdding.value = false
  }
}

const handleToggleWishlist = () => {
  toggleWishlist(product.value.id)
}

onMounted(() => {
  fetchProduct()
})
</script>