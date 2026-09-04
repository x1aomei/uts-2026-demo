<template>
  <div class="product-card group cursor-pointer">
    <!-- Image Section -->
    <div class="relative overflow-hidden aspect-[3/4]">
      <img 
        :src="product.image" 
        :alt="product.name" 
        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
      >
      
      <!-- Badges -->
      <div class="absolute top-3 left-3 flex flex-col gap-2">
        <span v-if="product.isNew" class="bg-primary-600 text-white text-xs px-2 py-1 rounded">NEW</span>
        <span v-if="product.discount > 0" class="bg-red-500 text-white text-xs px-2 py-1 rounded">-{{ product.discount }}%</span>
      </div>
      
      <!-- Quick Actions -->
      <div class="absolute bottom-0 left-0 right-0 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
        <div class="bg-white/95 backdrop-blur-sm p-3 flex gap-2">
          <button 
            @click="handleAddToCart" 
            class="flex-1 bg-gray-900 text-white py-2 rounded text-sm font-medium hover:bg-primary-600 transition-colors"
          >
            Add to Cart
          </button>
          <button 
            @click="handleWishlist" 
            class="w-10 h-10 bg-gray-100 hover:bg-primary-100 rounded flex items-center justify-center transition-colors"
          >
            <svg class="w-5 h-5" :class="isWishlisted ? 'text-red-500 fill-red-500' : 'text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
    
    <!-- Product Info -->
    <div class="p-4">
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs text-gray-500 uppercase tracking-wider">{{ product.category?.name || 'General' }}</span>
        <div class="flex items-center space-x-1">
          <svg v-for="i in 5" :key="i" class="w-3 h-3" :class="i <= product.rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
          </svg>
          <span class="text-xs text-gray-500">({{ product.reviewCount || 0 }})</span>
        </div>
      </div>
      
      <router-link :to="`/product/${product.slug}`" class="block">
        <h3 class="font-semibold text-gray-900 hover:text-primary-600 transition-colors mb-1">
          {{ product.name }}
        </h3>
      </router-link>
      
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <span class="text-lg font-bold text-gray-900">Rp{{ formatPrice(product.base_price) }}</span>
          <span v-if="product.originalPrice" class="text-sm text-gray-400 line-through">
            Rp{{ formatPrice(product.originalPrice) }}
          </span>
        </div>
        <div class="flex items-center space-x-1">
          <span v-for="color in (product.colors || []).slice(0, 3)" :key="color" 
            class="w-3 h-3 rounded-full border border-gray-200" 
            :style="{ backgroundColor: color }"
          ></span>
          <span v-if="(product.colors || []).length > 3" class="text-xs text-gray-500">+{{ product.colors.length - 3 }}</span>
        </div>
      </div>
      
      <!-- Sizes -->
      <div v-if="product.sizes?.length" class="flex gap-1 mt-2">
        <span v-for="size in product.sizes.slice(0, 4)" :key="size" 
          class="text-xs bg-gray-100 px-2 py-1 rounded border border-gray-200"
        >
          {{ size }}
        </span>
        <span v-if="product.sizes.length > 4" class="text-xs text-gray-500">+{{ product.sizes.length - 4 }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCart } from '../../composables/useCart'
import { useWishlist } from '../../composables/useWishlist'

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

const router = useRouter()
const { addToCart } = useCart()
const { addToWishlist, isInWishlist } = useWishlist()

const isWishlisted = computed(() => isInWishlist(props.product.id))

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const handleAddToCart = () => {
  addToCart(props.product, 1, props.product.sizes?.[0], props.product.colors?.[0])
}

const handleWishlist = () => {
  addToWishlist(props.product)
}
</script>