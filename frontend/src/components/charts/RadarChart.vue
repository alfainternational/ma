<template>
  <div class="w-full">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'

const props = defineProps({
  labels: { type: Array, default: () => [] },
  datasets: { type: Array, default: () => [] },
})

const chartCanvas = ref(null)
let chartInstance = null

async function renderChart() {
  if (!chartCanvas.value) return

  const { Chart, registerables } = await import('chart.js')
  Chart.register(...registerables)

  if (chartInstance) {
    chartInstance.destroy()
  }

  chartInstance = new Chart(chartCanvas.value, {
    type: 'radar',
    data: {
      labels: props.labels,
      datasets: props.datasets.map((ds, i) => ({
        label: ds.label || `Dataset ${i + 1}`,
        data: ds.data,
        backgroundColor: ds.backgroundColor || 'rgba(59, 130, 246, 0.2)',
        borderColor: ds.borderColor || 'rgba(59, 130, 246, 1)',
        borderWidth: 2,
        pointBackgroundColor: ds.borderColor || 'rgba(59, 130, 246, 1)',
        pointRadius: 4,
      })),
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      scales: {
        r: {
          beginAtZero: true,
          max: 100,
          ticks: { stepSize: 20, font: { family: 'Tajawal' } },
          pointLabels: { font: { family: 'Tajawal', size: 12 } },
        },
      },
      plugins: {
        legend: {
          labels: { font: { family: 'Tajawal' } },
        },
      },
    },
  })
}

onMounted(renderChart)
watch(() => [props.labels, props.datasets], renderChart, { deep: true })
</script>
