import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api/auth'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('access_token') || null)
  const refreshToken = ref(localStorage.getItem('refresh_token') || null)
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const userName = computed(() => user.value?.full_name || '')

  async function login(email, password) {
    loading.value = true
    try {
      const response = await authApi.login({ email, password })
      token.value = response.data.access_token
      refreshToken.value = response.data.refresh_token
      user.value = response.data.user
      localStorage.setItem('access_token', token.value)
      localStorage.setItem('refresh_token', refreshToken.value)
      return response
    } finally {
      loading.value = false
    }
  }

  async function register(data) {
    loading.value = true
    try {
      const response = await authApi.register(data)
      return response
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await authApi.logout()
    } finally {
      user.value = null
      token.value = null
      refreshToken.value = null
      localStorage.removeItem('access_token')
      localStorage.removeItem('refresh_token')
    }
  }

  async function fetchUser() {
    if (!token.value) return
    try {
      const response = await authApi.me()
      user.value = response.data
    } catch {
      await logout()
    }
  }

  return {
    user, token, refreshToken, loading,
    isAuthenticated, isAdmin, userName,
    login, register, logout, fetchUser,
  }
})
