<template>
  <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
    <div class="flex items-center justify-between h-16 px-6">
      <div class="flex items-center gap-4">
        <button @click="$emit('toggleSidebar')" class="text-gray-500 hover:text-gray-700 lg:hidden">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <h1 class="text-xl font-bold text-gradient">Marketing AI</h1>
      </div>

      <div class="flex items-center gap-4">
        <span class="text-sm text-gray-600">{{ userName }}</span>
        <button @click="handleLogout" class="text-sm text-red-600 hover:text-red-700 font-medium">
          خروج
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { useAuth } from '@/composables/useAuth'
import { useAuthStore } from '@/stores/auth'
import { computed } from 'vue'

defineEmits(['toggleSidebar'])

const authStore = useAuthStore()
const { logout } = useAuth()
const userName = computed(() => authStore.userName)

async function handleLogout() {
  await logout()
}
</script>
