<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Checkout</h1>

    <div v-if="cartItems.length === 0" class="text-center py-16">
      <p class="text-gray-500 text-lg mb-4">Keranjang Anda kosong, tidak ada yang bisa di-checkout</p>
      <router-link to="/shop" class="btn-primary inline-block">
        Mulai Belanja
      </router-link>
    </div>

    <form v-else @submit.prevent="submitOrder" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Shipping & Payment Form -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Shipping Info -->
        <div class="bg-white p-6 rounded-xl shadow-sm">
          <h2 class="text-xl font-bold mb-4">Alamat Pengiriman</h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="Nama lengkap"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
              <input
                v-model="form.phone"
                type="tel"
                required
                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="08xxxxxxxxxx"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos</label>
              <input
                v-model="form.postalCode"
                type="text"
                required
                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="45123"
              />
            </div>

            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Kota</label>
              <input
                v-model="form.city"
                type="text"
                required
                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="Cirebon"
              />
            </div>

            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
              <textarea
                v-model="form.address"
                required
                rows="3"
                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="Nama jalan, no rumah, RT/RW, kecamatan"
              ></textarea>
            </div>

            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
              <input
                v-model="form.notes"
                type="text"
                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="Contoh: titip di satpam"
              />
            </div>
          </div>
        </div>

        <!-- Payment Method -->
        <div class="bg-white p-6 rounded-xl shadow-sm">
          <h2 class="text-xl font-bold mb-4">Metode Pembayaran</h2>

          <div class="space-y-3">
            <label
              v-for="method in paymentMethods"
              :key="method.value"
              class="flex items-center space-x-3 border rounded-lg px-4 py-3 cursor-pointer transition"
              :class="form.paymentMethod === method.value ? 'border-primary-600 bg-primary-50' : 'border-gray-200'"
            >
              <input
                type="radio"
                :value="method.value"
                v-model="form.paymentMethod"
                class="text-primary-600 focus:ring-primary-500"
              />
              <span class="font-medium">{{ method.label }}</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="bg-white p-6 rounded-xl shadow-sm h-fit lg:sticky lg:top-24">
        <h2 class="text-xl font-bold mb-4">Ringkasan Pesanan</h2>

        <div class="space-y-4 mb-4 max-h-64 overflow-y-auto pr-1">
          <div v-for="item in cartItems" :key="item.id" class="flex items-center space-x-3">
            <img :src="item.product?.image" :alt="item.product?.name" class="w-14 h-14 object-cover rounded-lg" />
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900">{{ item.product?.name }}</p>
              <p class="text-xs text-gray-500">{{ item.size }} / {{ item.color }} &times; {{ item.quantity }}</p>
            </div>
            <p class="text-sm font-semibold">Rp{{ formatPrice(item.price * item.quantity) }}</p>
          </div>
        </div>

        <div class="border-t pt-4 space-y-3 mb-4">
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

        <p v-if="errorMessage" class="text-sm text-red-600 mb-3">{{ errorMessage }}</p>

        <button
          type="submit"
          :disabled="isSubmitting"
          class="btn-primary w-full text-center disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ isSubmitting ? 'Memproses...' : 'Buat Pesanan' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCart } from '../composables/useCart'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { cartItems, cartTotal, fetchCart, clearCart } = useCart()
const { user } = useAuth()

const isSubmitting = ref(false)
const errorMessage = ref('')

const paymentMethods = [
  { value: 'bank_transfer', label: 'Transfer Bank' },
  { value: 'ewallet', label: 'E-Wallet (OVO / GoPay / Dana)' },
  { value: 'cod', label: 'Bayar di Tempat (COD)' },
]

const form = reactive({
  name: user?.value?.name || '',
  phone: user?.value?.phone || '',
  city: '',
  postalCode: '',
  address: '',
  notes: '',
  paymentMethod: 'bank_transfer',
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const submitOrder = async () => {
  errorMessage.value = ''
  isSubmitting.value = true

  try {
    // TODO: ganti ini dengan pemanggilan service/API order kamu, contoh:
    // const order = await orderService.create({
    //   ...form,
    //   items: cartItems.value,
    //   total: cartTotal.value,
    // })

    // Simulasi delay request
    await new Promise((resolve) => setTimeout(resolve, 800))

    await clearCart()
    router.push('/order-success')
  } catch (err) {
    errorMessage.value = err?.response?.data?.message || 'Gagal membuat pesanan, coba lagi.'
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  fetchCart()
})
</script>