<template>
  <transition name="slide">
    <div v-if="isOpen" class="fixed inset-0 z-50">
      <!-- Backdrop -->
      <div @click="closeCart" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
      
      <!-- Drawer -->
      <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-xl overflow-y-auto">
        <div class="p-6 flex flex-col h-full">
          <!-- Header -->
          <div class="flex items-center justify-between pb-4 border-b">
            <h2 class="text-xl font-bold">Shopping Cart</h2>
            <button @click="closeCart" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
              <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          
          <!-- Cart Items -->
          <div class="flex-1 py-4 space-y-4">
            <div v-if="cartItems.length === 0" class="text-center py-12">
              <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
              <p class="text-gray-500 mb-4">Your cart is empty</p>
              <router-link to="/shop" @click="closeCart" class="btn-primary inline-block">
                Continue Shopping
              </router-link>
            </div>
            
            <div v-for="item in cartItems" :key="item.id" class="flex items-center space-x-4">
              <img :src="item.product?.image" :alt="item.product?.name" class="w-20 h-20 object-cover rounded-lg">
              
              <div class="flex-1">
                <h3 class="text-sm font-medium text-gray-900">{{ item.product?.name }}</h3>
                <p class="text-xs text-gray-500">{{ item.size }} / {{ item.color }}</p>
                <p class="text-sm font-semibold text-primary-600 mt-1">Rp{{ formatPrice(item.price) }}</p>
              </div>
              
              <div class="flex flex-col items-end space-y-2">
                <div class="flex items-center border rounded-lg">
                  <button @click="updateQuantity(item.id, item.quantity - 1)" class="px-2 py-1 text-gray-600 hover:text-primary-600">-</button>
                  <span class="px-2 py-1 text-sm">{{ item.quantity }}</span>
                  <button @click="updateQuantity(item.id, item.quantity + 1)" class="px-2 py-1 text-gray-600 hover:text-primary-600">+</button>
                </div>
                <button @click="removeFromCart(item.id)" class="text-xs text-red-600 hover:text-red-700">
                  Remove
                </button>
              </div>
            </div>
          </div>
          
          <!-- Footer -->
          <div v-if="cartItems.length > 0" class="border-t pt-4">
            <div class="flex justify-between mb-4">
              <span class="font-semibold">Total</span>
              <span class="font-bold text-primary-600">Rp{{ formatPrice(cartTotal) }}</span>
            </div>
            <router-link to="/checkout" @click="closeCart" class="btn-primary w-full text-center">
              Checkout
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useCart } from '../../composables/useCart'

const { cartItems, cartTotal, removeFromCart, updateQuantity } = useCart()

const isOpen = ref(false)

const openCart = () => {
  isOpen.value = true
}

const closeCart = () => {
  isOpen.value = false
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

// Expose methods
defineExpose({
  openCart,
  closeCart
})
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
  transform: translateX(100%);
}
</style>