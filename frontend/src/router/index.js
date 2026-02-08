import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/',
    redirect: '/dashboard',
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/Auth/Login.vue'),
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/views/Auth/Register.vue'),
    meta: { guest: true },
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@/views/Dashboard/Overview.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/assessment/start',
    name: 'AssessmentStart',
    component: () => import('@/views/Assessment/Start.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/assessment/:id',
    name: 'Questionnaire',
    component: () => import('@/views/Assessment/Questionnaire.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/assessment/:id/results',
    name: 'Results',
    component: () => import('@/views/Assessment/Results.vue'),
    meta: { requiresAuth: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'Login' })
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next({ name: 'Dashboard' })
  } else {
    next()
  }
})

export default router
