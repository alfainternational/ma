<template>
  <div class="p-6 max-w-5xl mx-auto space-y-8">
    <header class="text-center">
      <h1 class="text-3xl font-bold text-gray-900">نتائج التقييم</h1>
      <p class="text-gray-500 mt-2">تحليل شامل لمستوى النضج التسويقي لمنشأتك</p>
    </header>

    <div v-if="loading" class="text-center py-20">
      <div class="animate-spin h-12 w-12 border-4 border-primary-500 border-t-transparent rounded-full mx-auto"></div>
      <p class="mt-4 text-gray-500">جاري تحليل النتائج...</p>
    </div>

    <template v-else>
      <!-- Overall Score Gauge -->
      <div class="card text-center">
        <p class="text-sm text-gray-500 mb-2">مستوى النضج العام</p>
        <div class="relative inline-flex items-center justify-center">
          <svg class="w-40 h-40" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="45" fill="none" stroke="#e5e7eb" stroke-width="8" />
            <circle
              cx="50" cy="50" r="45" fill="none"
              :stroke="overallColor"
              stroke-width="8"
              stroke-linecap="round"
              :stroke-dasharray="`${overallScore * 2.83} 283`"
              transform="rotate(-90 50 50)"
            />
          </svg>
          <div class="absolute text-center">
            <span class="text-4xl font-bold" :class="maturityColorClass">{{ overallScore }}</span>
            <span class="block text-xs text-gray-500">من 100</span>
          </div>
        </div>
        <p class="mt-4 text-lg font-bold" :class="maturityColorClass">{{ maturityLabel }}</p>
      </div>

      <!-- Dimension Scores -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div v-for="dim in dimensions" :key="dim.id" class="card text-center">
          <p class="text-xs text-gray-500 mb-2">{{ dim.label }}</p>
          <p class="text-2xl font-bold" :style="{ color: dim.color }">{{ dim.score }}%</p>
          <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
            <div
              class="h-2 rounded-full transition-all"
              :style="{ width: dim.score + '%', backgroundColor: dim.color }"
            ></div>
          </div>
        </div>
      </div>

      <!-- Strengths & Weaknesses -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card">
          <h3 class="text-lg font-bold text-green-700 mb-4">نقاط القوة</h3>
          <ul class="space-y-2">
            <li v-for="(s, i) in strengths" :key="i" class="flex items-start gap-2 text-sm">
              <span class="text-green-500 mt-0.5">&#10003;</span>
              <span>{{ s }}</span>
            </li>
          </ul>
        </div>
        <div class="card">
          <h3 class="text-lg font-bold text-red-700 mb-4">نقاط الضعف</h3>
          <ul class="space-y-2">
            <li v-for="(w, i) in weaknesses" :key="i" class="flex items-start gap-2 text-sm">
              <span class="text-red-500 mt-0.5">&#10007;</span>
              <span>{{ w }}</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Recommendations -->
      <div class="card">
        <h3 class="text-lg font-bold mb-4">التوصيات الرئيسية</h3>
        <div class="space-y-4">
          <div v-for="(rec, i) in recommendations" :key="i" class="flex gap-4 p-4 bg-gray-50 rounded-lg">
            <div class="flex-shrink-0 w-8 h-8 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center font-bold text-sm">
              {{ i + 1 }}
            </div>
            <div>
              <p class="font-medium">{{ rec.title }}</p>
              <p class="text-sm text-gray-500 mt-1">{{ rec.description }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="text-center space-x-4 space-x-reverse">
        <button @click="generateReport" class="btn-primary px-8">تحميل التقرير الكامل</button>
        <router-link to="/dashboard" class="btn-secondary px-8">العودة للوحة التحكم</router-link>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { getMaturityLabel, getMaturityColor } from '@/utils/formatters'
import { SCORING_DIMENSIONS } from '@/utils/constants'
import { reportsApi } from '@/api/reports'
import { useToast } from '@/composables/useToast'

const route = useRoute()
const toast = useToast()
const sessionId = route.params.id

const loading = ref(true)
const overallScore = ref(0)
const dimensions = ref([])
const strengths = ref([])
const weaknesses = ref([])
const recommendations = ref([])

const maturityLabel = computed(() => getMaturityLabel(overallScore.value))
const maturityColorClass = computed(() => getMaturityColor(overallScore.value))
const overallColor = computed(() => {
  if (overallScore.value >= 75) return '#22c55e'
  if (overallScore.value >= 50) return '#f59e0b'
  return '#ef4444'
})

async function generateReport() {
  try {
    await reportsApi.generate({ session_id: sessionId, report_type: 'executive_summary' })
    toast.success('تم إنشاء التقرير بنجاح')
  } catch {
    toast.error('فشل إنشاء التقرير')
  }
}

onMounted(async () => {
  try {
    overallScore.value = 62
    dimensions.value = SCORING_DIMENSIONS.map(d => ({
      ...d,
      score: Math.floor(Math.random() * 40) + 40,
    }))
    strengths.value = [
      'وجود حسابات نشطة على منصات التواصل الاجتماعي',
      'فهم جيد للسوق المستهدف',
      'جودة المنتجات والخدمات المقدمة',
    ]
    weaknesses.value = [
      'غياب استراتيجية تسويق رقمي واضحة',
      'عدم استخدام أدوات تحليل البيانات',
      'ضعف في إدارة علاقات العملاء',
    ]
    recommendations.value = [
      { title: 'وضع استراتيجية تسويق رقمي', description: 'إعداد خطة تسويقية رقمية شاملة تتضمن أهداف واضحة ومؤشرات أداء' },
      { title: 'تبني أدوات تحليل البيانات', description: 'استخدام Google Analytics وأدوات تحليل وسائل التواصل الاجتماعي' },
      { title: 'تطوير نظام إدارة علاقات العملاء', description: 'تطبيق نظام CRM لتحسين تتبع العملاء وزيادة الولاء' },
    ]
  } finally {
    loading.value = false
  }
})
</script>
