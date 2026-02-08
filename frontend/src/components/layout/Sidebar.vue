<template>
  <aside :class="['fixed top-16 right-0 h-[calc(100vh-4rem)] bg-white border-l border-gray-200 transition-all duration-300 z-30',
                   open ? 'w-64' : 'w-0 overflow-hidden lg:w-20']">
    <nav class="p-4 space-y-2">
      <router-link
        v-for="item in menuItems"
        :key="item.to"
        :to="item.to"
        :class="['flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-sm font-medium',
                  isActive(item.to) ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50']"
      >
        <span class="text-lg">{{ item.icon }}</span>
        <span :class="{ 'lg:hidden': !open }">{{ item.label }}</span>
      </router-link>
    </nav>
  </aside>
</template>

<script setup>
import { useRoute } from 'vue-router'

defineProps({
  open: { type: Boolean, default: true },
})

const route = useRoute()

const menuItems = [
  { to: '/dashboard', icon: '\uD83D\uDCCA', label: '\u0644\u0648\u062D\u0629 \u0627\u0644\u062A\u062D\u0643\u0645' },
  { to: '/assessment/start', icon: '\uD83D\uDCDD', label: '\u062A\u0642\u064A\u064A\u0645 \u062C\u062F\u064A\u062F' },
  { to: '/reports', icon: '\uD83D\uDCC4', label: '\u0627\u0644\u062A\u0642\u0627\u0631\u064A\u0631' },
  { to: '/profile', icon: '\uD83D\uDC64', label: '\u0627\u0644\u0645\u0644\u0641 \u0627\u0644\u0634\u062E\u0635\u064A' },
]

function isActive(path) {
  return route.path.startsWith(path)
}
</script>
