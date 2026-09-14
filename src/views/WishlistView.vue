<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Wishlist</h1>

    <!-- Empty State -->
    <div v-if="wishlistItems.length === 0" class="text-center py-16">
      <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
      </svg>
      <p class="text-gray-500 text-lg mb-4">Wishlist Anda masih kosong</p>
      <router-link to="/shop" class="btn-primary inline-block">
        Mulai Belanja
      </router-link>
    </div>

    <!-- Wishlist Grid -->
    <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
      <div
        v-for="item in wishlistItems"
        :key="item.id"
        class="bg-white rounded-xl shadow-sm overflow-hidden group"
      >
        <router-link :to="`/product/${item.product?.id || item.productId}`" class="block relative">
          <img
            :src="item.product?.image"
            :alt="item.product?.name"
            class="w-full aspect-square object-cover group-hover:scale-105 transition-transform duration-300"
          />
          <button
            @click.prevent="handleRemove(item.id)"
            class="absolute top-2 right-2 w-8 h-8 flex items-center justify-center bg-white/90 rounded-full text-red-500 hover:bg-white transition"
          >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
              <path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
          </button>
        </router-link>

        <div class="p-4">
          <router-link :to="`/product/${item.product?.id || item.productId}`">
            <h3 class="font-semibold text-gray-900 mb-1 line-clamp-1 hover:text-primary-600 transition">
              {{ item.product?.name }}
            </h3>
          </router-link>
          <p class="text-sm font-semibold text-primary-600 mb-3">Rp{{ formatPrice(item.product?.price) }}</p>

          <button
            @click="handleAddToCart(item)"
            :disabled="addingId === item.id"
            class="btn-primary w-full text-center text-sm py-2 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ addingId === item.id ? 'Menambahkan...' : 'Tambah ke Keranjang' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useWishlist } from '../composables/useWishlist'
import { useCart } from '../composables/useCart'

const { wishlistItems, fetchWishlist, removeFromWishlist } = useWishlist()
const { addToCart } = useCart()

const addingId = ref(null)

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price || 0)
}

const handleRemove = (itemId) => {
  removeFromWishlist(itemId)
}

const handleAddToCart = async (item) => {
  addingId.value = item.id
  try {
    await addToCart(item.product, 1, item.size, item.color)
  } catch (err) {
    console.error('Gagal menambahkan ke keranjang', err)
  } finally {
    addingId.value = null
  }
}

onMounted(() => {
  fetchWishlist()
})
</script>