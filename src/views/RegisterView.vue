<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="max-w-md w-full space-y-8 p-8 bg-white rounded-lg shadow">
      <h2 class="text-center text-3xl font-extrabold text-gray-900">Daftar Akun Baru</h2>
      <form class="mt-8 space-y-6" @submit.prevent="handleRegister">
        <div class="rounded-md shadow-sm space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
            <input v-model="name" type="text" required class="w-full px-3 py-2 border rounded-md" placeholder="Nama Kamu">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input v-model="email" type="email" required class="w-full px-3 py-2 border rounded-md" placeholder="email@domain.com">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <input v-model="password" type="password" required class="w-full px-3 py-2 border rounded-md" placeholder="••••••••">
          </div>
        </div>

        <button type="submit" class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md">
          Daftar Sekarang
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { register } = useAuth()

const name = ref('')
const email = ref('')
const password = ref('')

const handleRegister = async () => {
  try {
    await register({ name: name.value, email: email.value, password: password.value })
    router.push('/')
  } catch (error) {
    console.error('Register gagal:', error)
  }
}
</script>