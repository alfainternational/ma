<template>
  <div class="p-6 space-y-6">
    <header class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">لوحة التحكم</h1>
        <p class="text-gray-500 mt-1">مرحباً بك في نظام التقييم التسويقي الذكي</p>
      </div>
      <router-link to="/assessment/start" class="btn-primary">
        بدء تقييم جديد
      </router-link>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="card">
        <p class="text-sm text-gray-500">التقييمات المكتملة</p>
        <p class="text-3xl font-bold text-primary-600 mt-2">{{ stats.completedSessions }}</p>
      </div>
      <div class="card">
        <p class="text-sm text-gray-500">التقييمات الجارية</p>
        <p class="text-3xl font-bold text-yellow-600 mt-2">{{ stats.activeSessions }}</p>
      </div>
      <div class="card">
        <p class="text-sm text-gray-500">متوسط النضج</p>
        <p class="text-3xl font-bold text-green-600 mt-2">{{ stats.avgMaturity }}%</p>
      </div>
      <div class="card">
        <p class="text-sm text-gray-500">التقارير المتاحة</p>
        <p class="text-3xl font-bold text-purple-600 mt-2">{{ stats.reportsCount }}</p>
      </div>
    </div>

    <div class="card">
      <h2 class="text-lg font-bold mb-4">آخر التقييمات</h2>
      <div v-if="sessions.length === 0" class="text-center py-8 text-gray-500">
        لا توجد تقييمات بعد. ابدأ تقييمك الأول الآن!
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-right font-medium text-gray-500">اسم التقييم</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">النوع</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">الحالة</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">التقدم</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">التاريخ</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">إجراء</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="session in sessions" :key="session.id">
              <td class="px-4 py-3">{{ session.session_name || 'تقييم بدون اسم' }}</td>
              <td class="px-4 py-3">{{ sessionTypes[session.session_type] || session.session_type }}</td>
              <td class="px-4 py-3">
                <span :class="statusClass(session.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                  {{ statusLabels[session.status] }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <div class="w-24 bg-gray-200 rounded-full h-2">
                    <div class="bg-primary-600 h-2 rounded-full" :style="{ width: session.progress_percentage + '%' }"></div>
                  </div>
                  <span class="text-xs text-gray-500">{{ Math.round(session.progress_percentage) }}%</span>
                </div>
              </td>
              <td class="px-4 py-3 text-gray-500">{{ formatDate(session.created_at) }}</td>
              <td class="px-4 py-3">
                <router-link
                  v-if="session.status === 'in_progress'"
                  :to="`/assessment/${session.id}`"
                  class="text-primary-600 hover:text-primary-700 font-medium text-sm"
                >متابعة</router-link>
                <router-link
                  v-else-if="session.status === 'completed'"
                  :to="`/assessment/${session.id}/results`"
                  class="text-green-600 hover:text-green-700 font-medium text-sm"
                >النتائج</router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAssessmentStore } from '@/stores/assessment'
import { formatDate } from '@/utils/formatters'
import { SESSION_TYPES, SESSION_STATUS } from '@/utils/constants'

const assessmentStore = useAssessmentStore()
const sessions = ref([])
const sessionTypes = SESSION_TYPES
const statusLabels = SESSION_STATUS

const stats = reactive({
  completedSessions: 0,
  activeSessions: 0,
  avgMaturity: 0,
  reportsCount: 0,
})

function statusClass(status) {
  const classes = {
    draft: 'bg-gray-100 text-gray-700',
    in_progress: 'bg-yellow-100 text-yellow-700',
    completed: 'bg-green-100 text-green-700',
    abandoned: 'bg-red-100 text-red-700',
  }
  return classes[status] || 'bg-gray-100 text-gray-700'
}

onMounted(async () => {
  try {
    await assessmentStore.fetchSessions('')
    sessions.value = assessmentStore.sessions
    stats.completedSessions = sessions.value.filter(s => s.status === 'completed').length
    stats.activeSessions = sessions.value.filter(s => s.status === 'in_progress').length
  } catch {
    // handle error silently
  }
})
</script>
