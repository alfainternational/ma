<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between gap-2">
      <button
        v-for="n in max"
        :key="n"
        @click="$emit('update:modelValue', String(n))"
        :class="[
          'w-12 h-12 rounded-lg font-bold transition-all',
          modelValue === String(n)
            ? 'bg-primary-600 text-white shadow-lg scale-110'
            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
        ]"
      >{{ n }}</button>
    </div>
    <div class="flex justify-between text-xs text-gray-400">
      <span>{{ minLabel }}</span>
      <span>{{ maxLabel }}</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue: { type: [String, Number], default: '' },
  max: { type: Number, default: 10 },
  minLabel: { type: String, default: 'ضعيف جداً' },
  maxLabel: { type: String, default: 'ممتاز' },
})

defineEmits(['update:modelValue'])
</script>
