import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export function useAuth() {
  const router = useRouter()
  const authStore = useAuthStore()

  const isAuthenticated = computed(() => authStore.isAuthenticated)
  const isAdmin = computed(() => authStore.isAdmin)
  const user = computed(() => authStore.user)

  async function login(email, password) {
    await authStore.login(email, password)
    router.push({ name: 'Dashboard' })
  }

  async function logout() {
    await authStore.logout()
    router.push({ name: 'Login' })
  }

  return { isAuthenticated, isAdmin, user, login, logout }
}
