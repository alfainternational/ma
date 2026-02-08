import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { assessmentApi } from '@/api/assessment'

export const useAssessmentStore = defineStore('assessment', () => {
  const currentSession = ref(null)
  const currentQuestion = ref(null)
  const answers = ref([])
  const progress = ref(0)
  const loading = ref(false)
  const sessions = ref([])

  const isActive = computed(() => currentSession.value?.status === 'in_progress')
  const questionsAnswered = computed(() => currentSession.value?.questions_answered || 0)
  const questionsTotal = computed(() => currentSession.value?.questions_total || 0)

  async function createSession(companyId, type = 'full') {
    loading.value = true
    try {
      const response = await assessmentApi.createSession({ company_id: companyId, session_type: type })
      currentSession.value = response.data
      return response.data
    } finally {
      loading.value = false
    }
  }

  async function loadSession(sessionId) {
    loading.value = true
    try {
      const response = await assessmentApi.getSession(sessionId)
      currentSession.value = response.data
    } finally {
      loading.value = false
    }
  }

  async function fetchNextQuestion(sessionId) {
    loading.value = true
    try {
      const response = await assessmentApi.getNextQuestion(sessionId)
      currentQuestion.value = response.data
      return response.data
    } finally {
      loading.value = false
    }
  }

  async function submitAnswer(sessionId, questionId, answerData) {
    loading.value = true
    try {
      const response = await assessmentApi.submitAnswer(sessionId, {
        question_id: questionId,
        ...answerData,
      })
      answers.value.push(response.data)
      if (currentSession.value) {
        currentSession.value.questions_answered = (currentSession.value.questions_answered || 0) + 1
        progress.value = currentSession.value.questions_total > 0
          ? Math.round((currentSession.value.questions_answered / currentSession.value.questions_total) * 100)
          : 0
      }
      return response.data
    } finally {
      loading.value = false
    }
  }

  async function completeSession(sessionId) {
    loading.value = true
    try {
      const response = await assessmentApi.completeSession(sessionId)
      currentSession.value = response.data
      return response.data
    } finally {
      loading.value = false
    }
  }

  async function fetchSessions(companyId) {
    loading.value = true
    try {
      const response = await assessmentApi.listSessions(companyId)
      sessions.value = response.data || []
    } finally {
      loading.value = false
    }
  }

  function reset() {
    currentSession.value = null
    currentQuestion.value = null
    answers.value = []
    progress.value = 0
  }

  return {
    currentSession, currentQuestion, answers, progress, loading, sessions,
    isActive, questionsAnswered, questionsTotal,
    createSession, loadSession, fetchNextQuestion, submitAnswer, completeSession, fetchSessions, reset,
  }
})
