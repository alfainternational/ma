<template>
  <div v-if="show" :class="['p-4 rounded-lg flex items-start gap-3', typeClasses[type]]" role="alert">
    <span class="text-lg leading-none">{{ icons[type] }}</span>
    <div class="flex-1">
      <p v-if="title" class="font-medium mb-1">{{ title }}</p>
      <p class="text-sm"><slot /></p>
    </div>
    <button v-if="dismissible" @click="$emit('dismiss')" class="text-current opacity-50 hover:opacity-100">
      &times;
    </button>
  </div>
</template>

<script setup>
defineProps({
  type: { type: String, default: 'info' },
  title: { type: String, default: '' },
  show: { type: Boolean, default: true },
  dismissible: { type: Boolean, default: false },
})

defineEmits(['dismiss'])

const typeClasses = {
  info: 'bg-blue-50 text-blue-800 border border-blue-200',
  success: 'bg-green-50 text-green-800 border border-green-200',
  warning: 'bg-yellow-50 text-yellow-800 border border-yellow-200',
  error: 'bg-red-50 text-red-800 border border-red-200',
}

const icons = {
  info: '\u2139',
  success: '\u2713',
  warning: '\u26A0',
  error: '\u2715',
}
</script>
