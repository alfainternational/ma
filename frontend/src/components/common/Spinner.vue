<template>
  <div :class="['flex items-center justify-center', fullScreen ? 'min-h-screen' : '']">
    <div :class="['animate-spin rounded-full border-t-transparent', sizeClasses[size], `border-${color}-500`]"
         :style="{ borderColor: `var(--spinner-color, #3b82f6) transparent transparent transparent` }">
    </div>
    <span v-if="text" class="mr-3 text-gray-500">{{ text }}</span>
  </div>
</template>

<script setup>
defineProps({
  size: { type: String, default: 'md' },
  color: { type: String, default: 'primary' },
  text: { type: String, default: '' },
  fullScreen: { type: Boolean, default: false },
})

const sizeClasses = {
  sm: 'h-5 w-5 border-2',
  md: 'h-8 w-8 border-3',
  lg: 'h-12 w-12 border-4',
  xl: 'h-16 w-16 border-4',
}
</script>
