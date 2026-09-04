<template>
  <header class="fixed top-0 left-0 right-0 z-50 bg-white shadow-sm">
    <nav class="container mx-auto px-4 py-3">
      <div class="flex items-center justify-between">
        <!-- Logo -->
        <router-link to="/" class="flex items-center space-x-2">
          <span class="text-2xl font-bold text-primary-600">CLOTH</span>
          <span class="text-2xl font-bold text-gray-900">STORE</span>
        </router-link>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-6">
          <router-link to="/" class="text-gray-600 hover:text-primary-600 transition-colors font-medium">Home</router-link>
          <router-link to="/shop" class="text-gray-600 hover:text-primary-600 transition-colors font-medium">Shop</router-link>
          <router-link to="/shop?category=men" class="text-gray-600 hover:text-primary-600 transition-colors font-medium">Men</router-link>
          <router-link to="/shop?category=women" class="text-gray-600 hover:text-primary-600 transition-colors font-medium">Women</router-link>
          <router-link to="/shop?category=streetwear" class="text-gray-600 hover:text-primary-600 transition-colors font-medium">Streetwear</router-link>
        </div>

        <!-- Right Actions -->
        <div class="flex items-center space-x-4">
          <!-- Search -->
          <button @click="searchOpen = true" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </button>

          <!-- Wishlist -->
          <router-link to="/wishlist" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
          </router-link>

          <!-- Cart -->
          <router-link to="/cart" class="relative p-2 hover:bg-gray-100 rounded-full transition-colors">
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span v-if="cartCount > 0" class="absolute -top-1 -right-1 bg-primary-600 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">
              {{ cartCount }}
            </span>
          </router-link>
          
          <!-- User Menu -->
          <div v-if="isAuthenticated" class="relative">
            <button @click="userMenuOpen = !userMenuOpen" class="flex items-center space-x-2 p-2 hover:bg-gray-100 rounded-full transition-colors">
              <img :src="user?.avatar || 'https://via.placeholder.com/32'" class="w-8 h-8 rounded-full object-cover" alt="User avatar">
            </button>
            
            <div v-if="userMenuOpen" @click.outside="userMenuOpen = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2">
              <router-link to="/profile" class="block px-4 py-2 hover:bg-gray-100 text-gray-700">Profile</router-link>
              <router-link to="/admin" v-if="isAdmin" class="block px-4 py-2 hover:bg-gray-100 text-gray-700">Admin Panel</router-link>
              <button @click="handleLogout" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">Logout</button>
            </div>
          </div>
          
          <router-link v-else to="/login" class="btn-primary px-4 py-2 text-sm">
            Login
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Search Overlay -->
    <transition name="fade">
      <div v-if="searchOpen" class="absolute top-0 left-0 right-0 bg-white shadow-lg p-4">
        <div class="container mx-auto flex items-center space-x-4">
          <input 
            v-model="searchQuery" 
            @keyup.enter="performSearch" 
            type="text" 
            placeholder="Search products..." 
            class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary-500"
          >
          <button @click="searchOpen = false" class="p-2 hover:bg-gray-100 rounded-full">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>
    </transition>
  </header>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../../composables/useAuth'
import { useCart } from '../../composables/useCart'

const router = useRouter()
const { isAuthenticated, isAdmin, user, logout } = useAuth()
const { cartCount } = useCart()

const searchOpen = ref(false)
const searchQuery = ref('')
const userMenuOpen = ref(false)

const performSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ name: 'Shop', query: { search: searchQuery.value } })
    searchOpen.value = false
    searchQuery.value = ''
  }
}

const handleLogout = async () => {
  await logout()
  router.push('/')
}
</script>