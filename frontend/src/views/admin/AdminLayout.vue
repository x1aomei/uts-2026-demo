<script setup>
import { RouterLink, useRouter } from 'vue-router'

const router = useRouter()

const logout = () => {
  localStorage.removeItem('token')
  router.push('/login')
}
</script>

<template>
  <div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar glass">
      <div class="sidebar-header">
        <h2>Admin Panel</h2>
      </div>

      <nav class="sidebar-nav">
        <RouterLink to="/admin" class="nav-item">
          <span class="icon">📊</span> Dashboard
        </RouterLink>
        <RouterLink to="/admin/kategori" class="nav-item">
          <span class="icon">📁</span> Kategori
        </RouterLink>
        <RouterLink to="/admin/produk" class="nav-item">
          <span class="icon">📦</span> Produk
        </RouterLink>
        <RouterLink to="/admin/pelanggan" class="nav-item">
          <span class="icon">👥</span> Pelanggan
        </RouterLink>
        <RouterLink to="/admin/pesanan" class="nav-item">
          <span class="icon">🛒</span> Pesanan
        </RouterLink>
      </nav>

      <div class="sidebar-footer">
        <RouterLink to="/" class="btn btn-secondary w-100" style="margin-bottom: 0.5rem">Lihat Toko</RouterLink>
        <button @click="logout" class="btn btn-danger w-100">Logout</button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-content">
      <header class="admin-header glass-card">
        <div class="d-flex justify-between align-center w-100">
          <h3>Toko Sederhana</h3>
          <div class="user-profile">
            <span class="icon">👤</span> Admin User
          </div>
        </div>
      </header>

      <div class="admin-main">
        <slot />
      </div>
    </main>
  </div>
</template>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background-color: var(--bg-light);
}

.sidebar {
  width: 260px;
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  border-right: 1px solid var(--border-color);
  z-index: 50;
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.sidebar-header h2 {
  font-size: 1.25rem;
  margin: 0;
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 1.5rem;
  color: var(--text-dark);
  font-weight: 500;
  transition: var(--transition);
  gap: 0.75rem;
}

.nav-item:hover, .nav-item.router-link-exact-active {
  background-color: rgba(99, 102, 241, 0.1);
  color: var(--primary-color);
  border-right: 4px solid var(--primary-color);
}

.sidebar-footer {
  padding: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.w-100 {
  width: 100%;
}

.admin-content {
  flex: 1;
  margin-left: 260px;
  display: flex;
  flex-direction: column;
}

.admin-header {
  margin: 1.5rem 1.5rem 0 1.5rem;
  padding: 1rem 1.5rem;
  display: flex;
  align-items: center;
}

.admin-header h3 {
  margin: 0;
  font-size: 1.1rem;
}

.admin-main {
  padding: 1.5rem;
  flex: 1;
}

.user-profile {
  font-weight: 500;
  color: var(--text-muted);
}
</style>