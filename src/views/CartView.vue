<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Shopping Cart</h1>
    
    <div v-if="cartItems.length === 0" class="text-center py-16">
      <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
      </svg>
      <p class="text-gray-500 text-lg mb-4">Keranjang Anda masih kosong</p>
      <router-link to="/shop" class="btn-primary inline-block">
        Mulai Belanja
      </router-link>
    </div>
    
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Cart Items -->
      <div class="lg:col-span-2 space-y-4">
        <div v-for="item in cartItems" :key="item.id" class="flex items-center space-x-4 bg-white p-4 rounded-xl shadow-sm">
          <img :src="item.product?.image" :alt="item.product?.name" class="w-24 h-24 object-cover rounded-lg">
          
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900">{{ item.product?.name }}</h3>
            <p class="text-sm text-gray-500">{{ item.size }} / {{ item.color }}</p>
            <p class="text-sm font-semibold text-primary-600 mt-1">Rp{{ formatPrice(item.price) }}</p>
          </div>
          
          <div class="flex flex-col items-end space-y-2">
            <div class="flex items-center border rounded-lg">
              <button @click="updateQuantity(item.id, item.quantity - 1)" class="px-3 py-1 text-gray-600 hover:text-primary-600">-</button>
              <span class="px-3 py-1 text-sm font-medium">{{ item.quantity }}</span>
              <button @click="updateQuantity(item.id, item.quantity + 1)" class="px-3 py-1 text-gray-600 hover:text-primary-600">+</button>
            </div>
            <button @click="removeFromCart(item.id)" class="text-sm text-red-600 hover:text-red-700">
              Hapus
            </button>
          </div>
        </div>
      </div>
      
      <!-- Order Summary -->
      <div class="bg-white p-6 rounded-xl shadow-sm h-fit lg:sticky lg:top-24">
        <h2 class="text-xl font-bold mb-4">Ringkasan Pesanan</h2>
        
        <div class="space-y-3 mb-4">
          <div class="flex justify-between">
            <span class="text-gray-600">Subtotal</span>
            <span class="font-semibold">Rp{{ formatPrice(cartTotal) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Ongkos Kirim</span>
            <span class="font-semibold text-green-600">GRATIS</span>
          </div>
        </div>
        
        <div class="border-t pt-4 mb-6">
          <div class="flex justify-between text-lg">
            <span class="font-bold">Total</span>
            <span class="font-bold text-primary-600">Rp{{ formatPrice(cartTotal) }}</span>
          </div>
        </div>
        
        <router-link to="/checkout" class="btn-primary w-full text-center">
          Lanjut ke Checkout
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useCart } from '../composables/useCart'

const { cartItems, cartTotal, fetchCart, removeFromCart, updateQuantity } = useCart()

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

onMounted(() => {
  fetchCart()
})
</script>