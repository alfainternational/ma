<template>
  <div class="space-y-2">
    <div class="flex items-center justify-between text-sm">
      <span class="text-gray-500">التقدم في التقييم</span>
      <span class="font-medium text-primary-600">{{ percentage }}%</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-3">
      <div
        class="bg-gradient-to-l from-primary-500 to-primary-600 h-3 rounded-full transition-all duration-500 ease-out"
        :style="{ width: percentage + '%' }"
      ></div>
    </div>
    <div class="flex justify-between text-xs text-gray-400">
      <span>سؤال {{ current }}</span>
      <span>{{ total }} سؤال</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  current: { type: Number, default: 0 },
  total: { type: Number, default: 0 },
})

const percentage = computed(() =>
  props.total > 0 ? Math.round((props.current / props.total) * 100) : 0
)
</script>
