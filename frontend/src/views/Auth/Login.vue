<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100 py-12 px-4">
    <div class="card max-w-md w-full space-y-8">
      <div class="text-center">
        <h1 class="text-3xl font-bold text-gradient">Marketing AI</h1>
        <p class="mt-2 text-gray-600">نظام التقييم التسويقي الذكي</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-6">
        <div v-if="errorMsg" class="bg-red-50 text-red-700 p-3 rounded-lg text-sm">
          {{ errorMsg }}
        </div>

        <div>
          <label class="label">البريد الإلكتروني</label>
          <input v-model="form.email" type="email" class="input-field" placeholder="example@company.com" required />
        </div>

        <div>
          <label class="label">كلمة المرور</label>
          <input v-model="form.password" type="password" class="input-field" placeholder="••••••••" required />
        </div>

        <button type="submit" :disabled="loading" class="btn-primary w-full flex items-center justify-center gap-2">
          <span v-if="loading" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
          <span>{{ loading ? 'جاري الدخول...' : 'تسجيل الدخول' }}</span>
        </button>

        <p class="text-center text-sm text-gray-600">
          ليس لديك حساب؟
          <router-link to="/register" class="text-primary-600 hover:text-primary-700 font-medium">إنشاء حساب جديد</router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useAuth } from '@/composables/useAuth'

const { login } = useAuth()
const loading = ref(false)
const errorMsg = ref('')

const form = reactive({
  email: '',
  password: '',
})

async function handleLogin() {
  loading.value = true
  errorMsg.value = ''
  try {
    await login(form.email, form.password)
  } catch (err) {
    errorMsg.value = err.message || 'فشل تسجيل الدخول. تأكد من بياناتك.'
  } finally {
    loading.value = false
  }
}
</script>
