<template>
  <div class="text-center">
    <div class="relative inline-flex items-center justify-center">
      <svg :width="size" :height="size" :viewBox="`0 0 ${size} ${size}`">
        <circle :cx="center" :cy="center" :r="radius" fill="none" stroke="#e5e7eb" :stroke-width="strokeWidth" />
        <circle
          :cx="center" :cy="center" :r="radius" fill="none"
          :stroke="color"
          :stroke-width="strokeWidth"
          stroke-linecap="round"
          :stroke-dasharray="`${dashArray} ${circumference}`"
          :transform="`rotate(-90 ${center} ${center})`"
          class="transition-all duration-1000 ease-out"
        />
      </svg>
      <div class="absolute text-center">
        <span :class="['font-bold', textSizeClass]" :style="{ color }">{{ Math.round(score) }}</span>
        <span class="block text-xs text-gray-500">من 100</span>
      </div>
    </div>
    <p v-if="label" class="mt-2 text-sm font-medium text-gray-700">{{ label }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  score: { type: Number, default: 0 },
  label: { type: String, default: '' },
  size: { type: Number, default: 160 },
  strokeWidth: { type: Number, default: 8 },
})

const center = computed(() => props.size / 2)
const radius = computed(() => (props.size - props.strokeWidth) / 2 - 4)
const circumference = computed(() => 2 * Math.PI * radius.value)
const dashArray = computed(() => (props.score / 100) * circumference.value)

const textSizeClass = computed(() => props.size >= 160 ? 'text-4xl' : 'text-2xl')

const color = computed(() => {
  if (props.score >= 75) return '#22c55e'
  if (props.score >= 50) return '#f59e0b'
  if (props.score >= 25) return '#f97316'
  return '#ef4444'
})
</script>
