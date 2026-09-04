import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  // User routes
  {
    path: '/',
    name: 'Home',
    component: () => import('../views/HomeView.vue'),
    meta: { title: 'Cloth Store - Home' }
  },
  {
    path: '/shop',
    name: 'Shop',
    component: () => import('../views/ShopView.vue'),
    meta: { title: 'Cloth Store - Shop' }
  },
  {
    path: '/product/:slug',
    name: 'ProductDetail',
    component: () => import('../views/ProductDetailView.vue'),
    meta: { title: 'Cloth Store - Product' }
  },
  {
    path: '/cart',
    name: 'Cart',
    component: () => import('../views/CartView.vue'),
    meta: { title: 'Cloth Store - Cart' }
  },
  {
    path: '/checkout',
    name: 'Checkout',
    component: () => import('../views/CheckoutView.vue'),
    meta: { title: 'Cloth Store - Checkout', requiresAuth: true }
  },
  {
    path: '/order-success',
    name: 'OrderSuccess',
    component: () => import('../views/OrderSuccessView.vue'),
    meta: { title: 'Cloth Store - Order Success' }
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/LoginView.vue'),
    meta: { title: 'Cloth Store - Login' }
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../views/RegisterView.vue'),
    meta: { title: 'Cloth Store - Register' }
  },
  {
    path: '/wishlist',
    name: 'Wishlist',
    component: () => import('../views/WishlistView.vue'),
    meta: { title: 'Cloth Store - Wishlist', requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'Profile',
    component: () => import('../views/ProfileView.vue'),
    meta: { title: 'Cloth Store - Profile', requiresAuth: true }
  },
  
  // Admin routes
  {
    path: '/admin',
    component: () => import('../views/admin/AdminLayout.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      {
        path: '',
        name: 'AdminDashboard',
        component: () => import('../views/admin/AdminDashboard.vue'),
        meta: { title: 'Admin - Dashboard' }
      },
      {
        path: 'categories',
        name: 'AdminCategories',
        component: () => import('../views/admin/AdminCategories.vue'),
        meta: { title: 'Admin - Kategori' }
      },
      {
        path: 'products',
        name: 'AdminProducts',
        component: () => import('../views/admin/AdminProducts.vue'),
        meta: { title: 'Admin - Produk' }
      },
      {
        path: 'products/create',
        name: 'AdminProductCreate',
        component: () => import('../views/admin/AdminProductForm.vue'),
        meta: { title: 'Admin - Tambah Produk' }
      },
      {
        path: 'products/:id/edit',
        name: 'AdminProductEdit',
        component: () => import('../views/admin/AdminProductForm.vue'),
        meta: { title: 'Admin - Edit Produk' }
      },
      {
        path: 'orders',
        name: 'AdminOrders',
        component: () => import('../views/admin/AdminOrders.vue'),
        meta: { title: 'Admin - Pesanan' }
      },
      {
        path: 'reports',
        name: 'AdminReports',
        component: () => import('../views/admin/AdminReports.vue'),
        meta: { title: 'Admin - Laporan' }
      }
    ]
  },
  
  // 404
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../views/NotFoundView.vue'),
    meta: { title: 'Page Not Found' }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  }
})

router.beforeEach((to, from, next) => {
  document.title = to.meta.title || 'Cloth Store'
  
  const token = localStorage.getItem('token')
  const user = JSON.parse(localStorage.getItem('user') || 'null')
  
  if (to.meta.requiresAuth && !token) {
    next({ name: 'Login', query: { redirect: to.fullPath } })
  } else if (to.meta.requiresAdmin && user?.role !== 'admin') {
    next({ name: 'Home' })
  } else {
    next()
  }
})

export default router