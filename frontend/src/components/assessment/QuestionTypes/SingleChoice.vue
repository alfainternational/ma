<template>
  <div class="space-y-3">
    <label
      v-for="(option, idx) in options"
      :key="idx"
      :class="[
        'flex items-center gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all',
        modelValue === option.value
          ? 'border-primary-500 bg-primary-50'
          : 'border-gray-200 hover:border-gray-300'
      ]"
    >
      <input type="radio" :value="option.value" :checked="modelValue === option.value"
             @change="$emit('update:modelValue', option.value)" class="hidden" />
      <div :class="[
        'w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0',
        modelValue === option.value ? 'border-primary-500' : 'border-gray-300'
      ]">
        <div v-if="modelValue === option.value" class="w-3 h-3 rounded-full bg-primary-500"></div>
      </div>
      <span class="text-sm">{{ option.label }}</span>
    </label>
  </div>
</template>

<script setup>
defineProps({
  options: { type: Array, required: true },
  modelValue: { type: [String, Number], default: '' },
})

defineEmits(['update:modelValue'])
</script>
