<template>
  <div class="p-6 max-w-3xl mx-auto space-y-8">
    <header class="text-center">
      <h1 class="text-3xl font-bold text-gray-900">بدء تقييم جديد</h1>
      <p class="text-gray-500 mt-2">اختر نوع التقييم المناسب لمنشأتك</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        v-for="type in assessmentTypes"
        :key="type.id"
        @click="selectedType = type.id"
        :class="[
          'card cursor-pointer transition-all duration-200 border-2',
          selectedType === type.id ? 'border-primary-500 ring-2 ring-primary-200' : 'border-transparent hover:border-gray-300'
        ]"
      >
        <div class="text-center space-y-3">
          <div class="text-4xl">{{ type.icon }}</div>
          <h3 class="text-lg font-bold">{{ type.label }}</h3>
          <p class="text-sm text-gray-500">{{ type.description }}</p>
          <p class="text-xs text-gray-400">{{ type.duration }}</p>
        </div>
      </div>
    </div>

    <div class="card">
      <label class="label">اسم التقييم (اختياري)</label>
      <input v-model="sessionName" type="text" class="input-field" placeholder="مثال: تقييم الربع الأول 2026" />
    </div>

    <div class="text-center">
      <button @click="startAssessment" :disabled="loading || !selectedType" class="btn-primary px-12 py-3 text-lg">
        <span v-if="loading" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full inline-block ml-2"></span>
        {{ loading ? 'جاري البدء...' : 'بدء التقييم' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAssessmentStore } from '@/stores/assessment'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const assessmentStore = useAssessmentStore()
const toast = useToast()

const selectedType = ref('full')
const sessionName = ref('')
const loading = ref(false)

const assessmentTypes = [
  {
    id: 'full',
    label: 'تقييم شامل',
    icon: '\u{1F4CA}',
    description: 'تقييم كامل يغطي جميع أبعاد التسويق والنضج الرقمي',
    duration: '250 سؤال - 45-60 دقيقة',
  },
  {
    id: 'quick',
    label: 'تقييم سريع',
    icon: '\u{26A1}',
    description: 'تقييم مختصر للحصول على نظرة عامة سريعة',
    duration: '50 سؤال - 10-15 دقيقة',
  },
  {
    id: 'focused',
    label: 'تقييم مركز',
    icon: '\u{1F3AF}',
    description: 'تقييم معمق في مجال محدد من اختيارك',
    duration: '40 سؤال - 15-20 دقيقة',
  },
]

async function startAssessment() {
  loading.value = true
  try {
    const session = await assessmentStore.createSession('', selectedType.value)
    toast.success('تم بدء التقييم بنجاح')
    router.push(`/assessment/${session.id}`)
  } catch (err) {
    toast.error(err.message || 'فشل بدء التقييم')
  } finally {
    loading.value = false
  }
}
</script>
