<template>
  <div class="card space-y-6">
    <div class="space-y-2">
      <span class="inline-block px-3 py-1 bg-primary-50 text-primary-700 rounded-full text-xs font-medium">
        {{ question.category }}
      </span>
      <h2 class="text-xl font-bold text-gray-900 leading-relaxed">{{ question.text }}</h2>
    </div>
    <slot />
  </div>
</template>

<script setup>
defineProps({
  question: { type: Object, required: true },
})
</script>
