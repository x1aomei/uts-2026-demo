import { ref, computed } from 'vue'
import api from '../services/api'

const wishlistItems = ref([])

export function useWishlist() {
  const fetchWishlist = async () => {
    try {
      const response = await api.get('/wishlist')
      wishlistItems.value = response.data || []
    } catch (error) {
      console.error('Failed to fetch wishlist:', error)
    }
  }

  const isInWishlist = (productId) => {
    return wishlistItems.value.some(item => item.id === productId)
  }

  const addToWishlist = async (product) => {
    try {
      await api.post('/wishlist', { product_id: product.id })
      wishlistItems.value.push(product)
    } catch (error) {
      throw error
    }
  }

  const removeFromWishlist = async (productId) => {
    try {
      await api.delete(`/wishlist/${productId}`)
      wishlistItems.value = wishlistItems.value.filter(item => item.id !== productId)
    } catch (error) {
      throw error
    }
  }

  return {
    wishlistItems,
    fetchWishlist,
    isInWishlist,
    addToWishlist,
    removeFromWishlist
  }
}