<template>
  <div class="min-h-[80vh] flex items-center justify-center">
    <div class="max-w-md w-full bg-white p-8 rounded-2xl shadow-lg">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold mb-2">Welcome Back</h1>
        <p class="text-gray-600">Login to continue shopping</p>
      </div>
      
      <!-- Google Login -->
      <button 
        @click="handleGoogleLogin"
        class="w-full flex items-center justify-center gap-3 bg-white border border-gray-300 rounded-lg py-3 px-4 hover:bg-gray-50 transition-colors mb-4"
      >
        <svg class="w-5 h-5" viewBox="0 0 24 24">
          <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
          <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
          <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
          <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        <span class="font-medium text-gray-700">Continue with Google</span>
      </button>
      
      <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-gray-300"></div>
        </div>
        <div class="relative flex justify-center text-sm">
          <span class="px-2 bg-white text-gray-500">or</span>
        </div>
      </div>
      
      <!-- Email Login -->
      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input 
            v-model="email"
            type="email" 
            required
            class="input-field"
            placeholder="Enter your email"
          >
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input 
            v-model="password"
            type="password" 
            required
            class="input-field"
            placeholder="Enter your password"
          >
        </div>
        
        <div class="flex items-center justify-between">
          <label class="flex items-center">
            <input type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
            <span class="ml-2 text-sm text-gray-600">Remember me</span>
          </label>
          <a href="#" class="text-sm text-primary-600 hover:text-primary-700">Forgot password?</a>
        </div>
        
        <button type="submit" class="btn-primary w-full">
          Login
        </button>
      </form>
      
      <p class="text-center text-sm text-gray-600 mt-6">
        Don't have an account? 
        <router-link to="/register" class="text-primary-600 hover:text-primary-700 font-medium">
          Sign up
        </router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { login, googleLogin } = useAuth()

const email = ref('')
const password = ref('')

const handleLogin = async () => {
  try {
    await login(email.value, password.value)
    router.push('/')
  } catch (error) {
    alert('Login failed. Please check your credentials.')
  }
}

const handleGoogleLogin = () => {
  googleLogin()
}
</script>