import { ref, computed } from 'vue'
import api from '../services/api'

const user = ref(null)
const token = ref(localStorage.getItem('token') || null)

export function useAuth() {
  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')

  const checkAuth = async () => {
    if (token.value) {
      try {
        const response = await api.get('/user')
        user.value = response.data
        localStorage.setItem('user', JSON.stringify(response.data))
      } catch (error) {
        logout()
      }
    }
  }

  const login = async (email, password) => {
    try {
      const response = await api.post('/login', { email, password })
      token.value = response.data.token
      user.value = response.data.user
      localStorage.setItem('token', token.value)
      localStorage.setItem('user', JSON.stringify(response.data))
      return response.data
    } catch (error) {
      throw error
    }
  }

  const googleLogin = () => {
    window.location.href = import.meta.env.VITE_API_URL + '/auth/google/redirect'
  }

  const logout = async () => {
    try {
      if (token.value) {
        await api.post('/logout')
      }
    } catch (error) {
      console.error('Logout error:', error)
    }
    token.value = null
    user.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  return {
    user,
    token,
    isAuthenticated,
    isAdmin,
    checkAuth,
    login,
    googleLogin,
    logout
  }
}