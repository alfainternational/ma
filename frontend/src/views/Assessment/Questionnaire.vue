<template>
  <div class="p-6 max-w-4xl mx-auto">
    <div class="mb-8">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm text-gray-500">التقدم في التقييم</span>
        <span class="text-sm font-medium text-primary-600">{{ progress }}%</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-3">
        <div
          class="bg-gradient-to-l from-primary-500 to-primary-600 h-3 rounded-full transition-all duration-500"
          :style="{ width: progress + '%' }"
        ></div>
      </div>
      <div class="flex justify-between mt-1 text-xs text-gray-400">
        <span>سؤال {{ currentIndex + 1 }}</span>
        <span>{{ assessmentStore.questionsTotal }} سؤال</span>
      </div>
    </div>

    <div v-if="loading" class="text-center py-20">
      <div class="animate-spin h-12 w-12 border-4 border-primary-500 border-t-transparent rounded-full mx-auto"></div>
      <p class="mt-4 text-gray-500">جاري تحميل السؤال...</p>
    </div>

    <div v-else-if="question" class="card space-y-6">
      <div class="space-y-2">
        <span class="inline-block px-3 py-1 bg-primary-50 text-primary-700 rounded-full text-xs font-medium">
          {{ question.category }}
        </span>
        <h2 class="text-xl font-bold text-gray-900 leading-relaxed">{{ question.text }}</h2>
      </div>

      <!-- Single Choice -->
      <div v-if="question.type === 'single_choice'" class="space-y-3">
        <label
          v-for="(option, idx) in question.options"
          :key="idx"
          :class="[
            'flex items-center gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all',
            selectedAnswer === option.value
              ? 'border-primary-500 bg-primary-50'
              : 'border-gray-200 hover:border-gray-300'
          ]"
        >
          <input type="radio" :value="option.value" v-model="selectedAnswer" class="hidden" />
          <div :class="[
            'w-5 h-5 rounded-full border-2 flex items-center justify-center',
            selectedAnswer === option.value ? 'border-primary-500' : 'border-gray-300'
          ]">
            <div v-if="selectedAnswer === option.value" class="w-3 h-3 rounded-full bg-primary-500"></div>
          </div>
          <span>{{ option.label }}</span>
        </label>
      </div>

      <!-- Scale Rating -->
      <div v-else-if="question.type === 'scale_rating'" class="space-y-4">
        <div class="flex items-center justify-between gap-2">
          <button
            v-for="n in 10"
            :key="n"
            @click="selectedAnswer = String(n)"
            :class="[
              'w-12 h-12 rounded-lg font-bold transition-all',
              selectedAnswer === String(n)
                ? 'bg-primary-600 text-white shadow-lg scale-110'
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
            ]"
          >{{ n }}</button>
        </div>
        <div class="flex justify-between text-xs text-gray-400">
          <span>ضعيف جداً</span>
          <span>ممتاز</span>
        </div>
      </div>

      <!-- Text Input -->
      <div v-else-if="question.type === 'text_input'">
        <textarea
          v-model="selectedAnswer"
          class="input-field min-h-[120px]"
          placeholder="اكتب إجابتك هنا..."
        ></textarea>
      </div>

      <!-- Numeric Input -->
      <div v-else-if="question.type === 'numeric_input'">
        <input
          v-model="selectedAnswer"
          type="number"
          class="input-field"
          placeholder="أدخل الرقم"
        />
      </div>

      <!-- Multiple Choice -->
      <div v-else-if="question.type === 'multiple_choice'" class="space-y-3">
        <label
          v-for="(option, idx) in question.options"
          :key="idx"
          :class="[
            'flex items-center gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all',
            selectedMultiple.includes(option.value)
              ? 'border-primary-500 bg-primary-50'
              : 'border-gray-200 hover:border-gray-300'
          ]"
        >
          <input type="checkbox" :value="option.value" v-model="selectedMultiple" class="hidden" />
          <div :class="[
            'w-5 h-5 rounded border-2 flex items-center justify-center',
            selectedMultiple.includes(option.value) ? 'border-primary-500 bg-primary-500' : 'border-gray-300'
          ]">
            <svg v-if="selectedMultiple.includes(option.value)" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </div>
          <span>{{ option.label }}</span>
        </label>
      </div>

      <div class="flex items-center justify-between pt-4 border-t">
        <button @click="skipQuestion" class="btn-secondary text-sm">
          تخطي السؤال
        </button>
        <button
          @click="submitCurrentAnswer"
          :disabled="!hasAnswer && question.required"
          class="btn-primary"
        >
          {{ isLastQuestion ? 'إنهاء التقييم' : 'السؤال التالي' }}
        </button>
      </div>
    </div>

    <div v-else class="text-center py-20 card">
      <p class="text-xl text-gray-600">تم الانتهاء من جميع الأسئلة!</p>
      <button @click="finishAssessment" class="btn-success mt-6 px-8">
        عرض النتائج
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAssessmentStore } from '@/stores/assessment'
import { useToast } from '@/composables/useToast'

const route = useRoute()
const router = useRouter()
const assessmentStore = useAssessmentStore()
const toast = useToast()

const sessionId = route.params.id
const question = ref(null)
const selectedAnswer = ref('')
const selectedMultiple = ref([])
const loading = ref(true)
const currentIndex = ref(0)

const progress = computed(() => assessmentStore.progress || 0)
const isLastQuestion = computed(() =>
  currentIndex.value >= (assessmentStore.questionsTotal - 1)
)
const hasAnswer = computed(() => {
  if (question.value?.type === 'multiple_choice') {
    return selectedMultiple.value.length > 0
  }
  return !!selectedAnswer.value
})

async function loadNextQuestion() {
  loading.value = true
  selectedAnswer.value = ''
  selectedMultiple.value = []
  try {
    const q = await assessmentStore.fetchNextQuestion(sessionId)
    question.value = q
  } catch {
    question.value = null
  } finally {
    loading.value = false
  }
}

async function submitCurrentAnswer() {
  if (!hasAnswer.value && question.value?.required) return

  const answerValue = question.value?.type === 'multiple_choice'
    ? JSON.stringify(selectedMultiple.value)
    : selectedAnswer.value

  try {
    await assessmentStore.submitAnswer(sessionId, question.value.id, {
      answer_value: answerValue,
    })
    currentIndex.value++

    if (isLastQuestion.value) {
      await finishAssessment()
    } else {
      await loadNextQuestion()
    }
  } catch (err) {
    toast.error(err.message || 'فشل حفظ الإجابة')
  }
}

async function skipQuestion() {
  try {
    await assessmentStore.submitAnswer(sessionId, question.value.id, {
      answer_value: null,
      is_skipped: true,
    })
    currentIndex.value++
    await loadNextQuestion()
  } catch {
    toast.error('فشل تخطي السؤال')
  }
}

async function finishAssessment() {
  try {
    await assessmentStore.completeSession(sessionId)
    router.push(`/assessment/${sessionId}/results`)
  } catch {
    toast.error('فشل إنهاء التقييم')
  }
}

onMounted(async () => {
  await assessmentStore.loadSession(sessionId)
  currentIndex.value = assessmentStore.questionsAnswered
  await loadNextQuestion()
})
</script>
