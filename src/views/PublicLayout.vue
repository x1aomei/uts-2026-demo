<script setup>
import { RouterLink, useRouter } from 'vue-router'
import { ref, onMounted } from 'vue'

const router = useRouter()
const isLoggedIn = ref(false)

onMounted(() => {
  if (localStorage.getItem('token')) {
    isLoggedIn.value = true
  }
})

const logout = () => {
  localStorage.removeItem('token')
  isLoggedIn.value = false
  router.push('/login')
}
</script>

<template>
  <div class="public-layout">
    <!-- Navbar Glassmorphism -->
    <header class="navbar glass">
      <div class="container d-flex justify-between align-center">
        <RouterLink to="/" class="logo">
          <span class="logo-icon">🛍️</span> Toko Sederhana
        </RouterLink>

        <nav class="nav-links d-flex gap-4">
          <RouterLink to="/">Beranda</RouterLink>
          <RouterLink to="/produk">Produk</RouterLink>
        </nav>

        <div class="nav-actions">
          <RouterLink v-if="!isLoggedIn" to="/login" class="btn btn-primary">Login Admin</RouterLink>
          <div v-else class="d-flex gap-2">
            <RouterLink to="/admin" class="btn btn-secondary">Dashboard</RouterLink>
            <button @click="logout" class="btn btn-danger">Logout</button>
          </div>
        </div>
      </div>
    </header>

    <!-- Konten halaman akan masuk di sini (slot) -->
    <main class="main-content">
      <slot />
    </main>

    <footer class="footer glass">
      <div class="container text-center text-muted">
        <p>&copy; 2026 Toko Sederhana. Dibuat untuk Kelas XII RPL.</p>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.navbar {
  position: sticky;
  top: 0;
  z-index: 100;
  padding: 1rem 0;
  margin-bottom: 2rem;
}

.logo {
  font-size: 1.25rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.logo-icon {
  font-size: 1.5rem;
}

.nav-links a {
  font-weight: 500;
  color: var(--text-muted);
}

.nav-links a:hover, .nav-links a.router-link-active {
  color: var(--primary-color);
}

.main-content {
  min-height: calc(100vh - 200px);
}

.footer {
  margin-top: 4rem;
  padding: 2rem 0;
}
</style>