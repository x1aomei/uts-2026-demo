<template>
  <div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">Profil Saya</h1>

      <div class="space-y-4">
        <div>
          <label class="text-sm font-medium text-gray-500">Nama</label>
          <p class="text-lg font-semibold text-gray-800">{{ user?.name || 'User Dummy' }}</p>
        </div>

        <div>
          <label class="text-sm font-medium text-gray-500">Email</label>
          <p class="text-lg font-semibold text-gray-800">{{ user?.email || 'cerli7220@gmail.com' }}</p>
        </div>

        <div>
          <label class="text-sm font-medium text-gray-500">Role</label>
          <p class="text-lg font-semibold text-gray-800 capitalize">{{ user?.role || 'customer' }}</p>
        </div>
      </div>

      <div class="mt-8 pt-6 border-t border-gray-200">
        <button
          @click="handleLogout"
          class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md transition"
        >
          Logout
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { user, logout } = useAuth()

const handleLogout = async () => {
  await logout()
  router.push('/login')
}
</script>