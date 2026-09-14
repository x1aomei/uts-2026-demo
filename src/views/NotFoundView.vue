<template>
  <div class="container mx-auto px-4 py-16">
    <div class="max-w-md mx-auto text-center">
      <p class="text-7xl sm:text-8xl font-bold text-primary-600 mb-4">404</p>

      <h1 class="text-2xl sm:text-3xl font-bold mb-2">Halaman Tidak Ditemukan</h1>
      <p class="text-gray-500 mb-8">
        Maaf, halaman yang kamu cari tidak ada atau sudah dipindahkan.
      </p>

      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <router-link to="/" class="btn-primary text-center">
          Kembali ke Beranda
        </router-link>
        <router-link
          to="/shop"
          class="text-center border border-gray-300 rounded-lg py-2.5 px-6 font-medium text-gray-700 hover:bg-gray-50 transition"
        >
          Lihat Produk
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
</script>