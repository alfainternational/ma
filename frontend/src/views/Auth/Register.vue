<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100 py-12 px-4">
    <div class="card max-w-md w-full space-y-8">
      <div class="text-center">
        <h1 class="text-3xl font-bold text-gradient">Marketing AI</h1>
        <p class="mt-2 text-gray-600">إنشاء حساب جديد</p>
      </div>

      <form @submit.prevent="handleRegister" class="space-y-5">
        <div v-if="errorMsg" class="bg-red-50 text-red-700 p-3 rounded-lg text-sm">{{ errorMsg }}</div>
        <div v-if="successMsg" class="bg-green-50 text-green-700 p-3 rounded-lg text-sm">{{ successMsg }}</div>

        <div>
          <label class="label">الاسم الكامل</label>
          <input v-model="form.full_name" type="text" class="input-field" placeholder="محمد أحمد" required />
        </div>

        <div>
          <label class="label">البريد الإلكتروني</label>
          <input v-model="form.email" type="email" class="input-field" placeholder="example@company.com" required />
        </div>

        <div>
          <label class="label">رقم الجوال</label>
          <input v-model="form.phone" type="tel" class="input-field" placeholder="+966 5X XXX XXXX" />
        </div>

        <div>
          <label class="label">كلمة المرور</label>
          <input v-model="form.password" type="password" class="input-field" placeholder="8 أحرف على الأقل" required minlength="8" />
        </div>

        <div>
          <label class="label">تأكيد كلمة المرور</label>
          <input v-model="form.password_confirm" type="password" class="input-field" placeholder="أعد كتابة كلمة المرور" required />
        </div>

        <button type="submit" :disabled="loading" class="btn-primary w-full flex items-center justify-center gap-2">
          <span v-if="loading" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
          <span>{{ loading ? 'جاري الإنشاء...' : 'إنشاء الحساب' }}</span>
        </button>

        <p class="text-center text-sm text-gray-600">
          لديك حساب بالفعل؟
          <router-link to="/login" class="text-primary-600 hover:text-primary-700 font-medium">تسجيل الدخول</router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const loading = ref(false)
const errorMsg = ref('')
const successMsg = ref('')

const form = reactive({
  full_name: '',
  email: '',
  phone: '',
  password: '',
  password_confirm: '',
})

async function handleRegister() {
  errorMsg.value = ''
  successMsg.value = ''

  if (form.password !== form.password_confirm) {
    errorMsg.value = 'كلمتا المرور غير متطابقتين'
    return
  }

  loading.value = true
  try {
    await authStore.register(form)
    successMsg.value = 'تم إنشاء الحساب بنجاح! جاري التحويل...'
    setTimeout(() => router.push('/login'), 1500)
  } catch (err) {
    errorMsg.value = err.message || 'فشل إنشاء الحساب'
  } finally {
    loading.value = false
  }
}
</script>
