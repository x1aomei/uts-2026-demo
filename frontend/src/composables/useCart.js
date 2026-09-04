import { ref, computed } from 'vue'
import api from '../services/api'

const cartItems = ref([])

export function useCart() {
  const cartCount = computed(() => {
    return cartItems.value.reduce((total, item) => total + item.quantity, 0)
  })

  const cartTotal = computed(() => {
    return cartItems.value.reduce((total, item) => {
      return total + (item.price * item.quantity)
    }, 0)
  })

  const fetchCart = async () => {
    try {
      const token = localStorage.getItem('token')
      if (token) {
        const response = await api.get('/cart')
        cartItems.value = response.data.items || []
      }
    } catch (error) {
      console.error('Failed to fetch cart:', error)
    }
  }

  const addToCart = async (product, quantity = 1, size, color) => {
    try {
      const response = await api.post('/cart/items', {
        product_variant_id: product.id,
        quantity,
        size,
        color
      })
      cartItems.value = response.data.items || []
      return response.data
    } catch (error) {
      throw error
    }
  }

  const removeFromCart = async (itemId) => {
    try {
      await api.delete(`/cart/items/${itemId}`)
      cartItems.value = cartItems.value.filter(item => item.id !== itemId)
    } catch (error) {
      throw error
    }
  }

  const updateQuantity = async (itemId, quantity) => {
    try {
      const response = await api.patch(`/cart/items/${itemId}`, { quantity })
      cartItems.value = response.data.items || []
    } catch (error) {
      throw error
    }
  }

  const clearCart = async () => {
    try {
      await api.delete('/cart')
      cartItems.value = []
    } catch (error) {
      throw error
    }
  }

  return {
    cartItems,
    cartCount,
    cartTotal,
    fetchCart,
    addToCart,
    removeFromCart,
    updateQuantity,
    clearCart
  }
}