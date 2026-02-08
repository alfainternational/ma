<template>
  <div :class="['bg-white rounded-xl shadow-sm border border-gray-200', padding ? 'p-6' : '', className]">
    <div v-if="title" class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-bold text-gray-900">{{ title }}</h3>
      <slot name="header-action" />
    </div>
    <slot />
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: '' },
  padding: { type: Boolean, default: true },
  className: { type: String, default: '' },
})
</script>
