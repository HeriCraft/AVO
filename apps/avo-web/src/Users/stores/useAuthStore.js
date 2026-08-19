import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiClient from '../../Shared/http/client'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(null)

  const isSuperAdmin = computed(() => user.value?.role === 'SUPER_ADMIN')
  const isAuthenticated = computed(() => !!token.value)

  function setSession(sessionToken, sessionUser) {
    token.value = sessionToken
    user.value = sessionUser
    localStorage.setItem('access_token', sessionToken)
    localStorage.setItem('user', JSON.stringify(sessionUser))
    // apiClient already handles token injection via interceptor and localStorage,
    // but we can set it explicitly on defaults if needed.
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${sessionToken}`
  }

  function clearSession() {
    token.value = null
    user.value = null
    localStorage.removeItem('access_token')
    localStorage.removeItem('user')
    delete apiClient.defaults.headers.common['Authorization']
  }

  function initSession() {
    const storedToken = localStorage.getItem('access_token')
    const storedUser = localStorage.getItem('user')
    if (storedToken && storedUser) {
      setSession(storedToken, JSON.parse(storedUser))
    }
  }

  async function login(email, password) {
    try {
      const response = await apiClient.post('/auth/login', { email, password })
      setSession(response.data.token, response.data.user)
      if (isSuperAdmin.value) {
        return { success: true, redirect: '/admin/dashboard' }
      }
      return { success: true, redirect: '/dashboard' }
    } catch (error) {
      return { success: false, error: error.response?.data?.error || 'Login failed' }
    }
  }

  function logout() {
    clearSession()
    window.location.href = '/login'
  }

  return {
    user,
    token,
    isSuperAdmin,
    isAuthenticated,
    setSession,
    clearSession,
    initSession,
    login,
    logout
  }
})
