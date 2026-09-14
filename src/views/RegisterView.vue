<template>
  <div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <router-link to="/" class="text-2xl font-bold text-primary-600">CLOTH STORE</router-link>
        <h1 class="text-2xl font-bold mt-4 mb-1">Buat Akun Baru</h1>
        <p class="text-gray-500">Daftar untuk mulai berbelanja</p>
      </div>

      <form @submit.prevent="handleRegister" class="bg-white p-6 sm:p-8 rounded-xl shadow-sm">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
          <input
            v-model="form.name"
            type="text"
            required
            autocomplete="name"
            class="input-field w-full"
            placeholder="Nama lengkap Anda"
          >
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            autocomplete="email"
            class="input-field w-full"
            placeholder="nama@email.com"
          >
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
          <input
            v-model="form.phone"
            type="tel"
            required
            autocomplete="tel"
            class="input-field w-full"
            placeholder="08xxxxxxxxxx"
          >
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
          <div class="relative">
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              minlength="8"
              autocomplete="new-password"
              class="input-field w-full pr-10"
              placeholder="Minimal 8 karakter"
            >
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
              </svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Kata Sandi</label>
          <input
            v-model="form.passwordConfirmation"
            :type="showPassword ? 'text' : 'password'"
            required
            autocomplete="new-password"
            class="input-field w-full"
            placeholder="Ulangi kata sandi"
          >
          <p v-if="passwordMismatch" class="text-xs text-red-600 mt-1">Konfirmasi kata sandi tidak cocok.</p>
        </div>

        <label class="flex items-start gap-2 mb-6">
          <input v-model="form.agreeToTerms" type="checkbox" required class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
          <span class="text-sm text-gray-600">
            Saya setuju dengan <a href="#" class="text-primary-600 hover:underline">Syarat & Ketentuan</a> dan <a href="#" class="text-primary-600 hover:underline">Kebijakan Privasi</a>
          </span>
        </label>

        <p v-if="errorMessage" class="text-sm text-red-600 mb-4 text-center">{{ errorMessage }}</p>

        <button
          type="submit"
          :disabled="isSubmitting || passwordMismatch"
          class="btn-primary w-full disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ isSubmitting ? 'Memproses...' : 'Daftar' }}
        </button>

        <p class="text-center text-sm text-gray-500 mt-6">
          Sudah punya akun?
          <router-link to="/login" class="text-primary-600 font-medium hover:underline">Masuk di sini</router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { register } = useAuth()

const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  passwordConfirmation: '',
  agreeToTerms: false,
})

const showPassword = ref(false)
const isSubmitting = ref(false)
const errorMessage = ref('')

const passwordMismatch = computed(() => {
  return form.passwordConfirmation.length > 0 && form.password !== form.passwordConfirmation
})

const handleRegister = async () => {
  errorMessage.value = ''

  if (passwordMismatch.value) {
    errorMessage.value = 'Konfirmasi kata sandi tidak cocok.'
    return
  }

  isSubmitting.value = true

  try {
    await register({
      name: form.name,
      email: form.email,
      phone: form.phone,
      password: form.password,
      password_confirmation: form.passwordConfirmation,
    })
    router.push('/')
  } catch (error) {
    errorMessage.value = error?.response?.data?.message || 'Gagal mendaftar. Coba lagi.'
  } finally {
    isSubmitting.value = false
  }
}
</script>