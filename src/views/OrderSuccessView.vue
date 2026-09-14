<template>
  <div class="container mx-auto px-4 py-16">
    <div class="max-w-lg mx-auto text-center bg-white p-8 sm:p-10 rounded-xl shadow-sm">
      <!-- Success Icon -->
      <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-green-100 flex items-center justify-center">
        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>

      <h1 class="text-2xl sm:text-3xl font-bold mb-2">Pesanan Berhasil Dibuat!</h1>
      <p class="text-gray-500 mb-6">
        Terima kasih sudah berbelanja. Kami akan segera memproses pesananmu.
      </p>

      <div v-if="orderId" class="bg-gray-50 rounded-lg px-4 py-3 mb-8">
        <p class="text-sm text-gray-500">Nomor Pesanan</p>
        <p class="text-lg font-semibold text-primary-600">#{{ orderId }}</p>
      </div>

      <div class="space-y-3">
        <router-link
          v-if="orderId"
          :to="`/orders/${orderId}`"
          class="btn-primary w-full text-center block"
        >
          Lihat Detail Pesanan
        </router-link>
        <router-link
          to="/shop"
          class="w-full text-center block border border-gray-300 rounded-lg py-2.5 font-medium text-gray-700 hover:bg-gray-50 transition"
        >
          Lanjut Belanja
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const orderId = computed(() => route.query.orderId || null)
</script>