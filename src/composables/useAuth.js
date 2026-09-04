import { ref, computed } from 'vue'

// Langsung panggil file dari folder public/avatar.jpg.jpeg
const defaultAvatar = '/avatar.jpg.jpeg'

const user = ref(JSON.parse(localStorage.getItem('user')) || null)
const token = ref(localStorage.getItem('token') || null)

export function useAuth() {
  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')

  const checkAuth = async () => {
    if (token.value) {
      const dummyUser = {
        id: 1,
        name: 'User Dummy',
        email: 'cerli7220@gmail.com',
        role: 'customer',
        avatar: defaultAvatar
      }
      user.value = dummyUser
      localStorage.setItem('user', JSON.stringify(dummyUser))
    }
  }

  const login = async (email, password) => {
    await new Promise((resolve) => setTimeout(resolve, 300))

    const fakeToken = 'dummy-jwt-token-123456789'
    const fakeUser = {
      id: 1,
      name: email ? email.split('@')[0] : 'User',
      email: email || 'cerli7220@gmail.com',
      role: 'customer',
      avatar: defaultAvatar
    }

    token.value = fakeToken
    user.value = fakeUser

    localStorage.setItem('token', fakeToken)
    localStorage.setItem('user', JSON.stringify(fakeUser))

    return { success: true, user: fakeUser }
  }

  const register = async (userData) => {
    await new Promise((resolve) => setTimeout(resolve, 300))

    const fakeToken = 'dummy-jwt-token-123456789'
    const newUser = {
      id: Date.now(),
      name: userData.name || 'User Baru',
      email: userData.email,
      role: 'customer',
      avatar: defaultAvatar
    }

    token.value = fakeToken
    user.value = newUser

    localStorage.setItem('token', fakeToken)
    localStorage.setItem('user', JSON.stringify(newUser))

    return { success: true, user: newUser }
  }

  const logout = async () => {
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
    register,
    logout
  }
}