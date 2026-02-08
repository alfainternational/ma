import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUiStore = defineStore('ui', () => {
  const sidebarOpen = ref(true)
  const loading = ref(false)
  const toast = ref(null)
  const modal = ref(null)

  function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value
  }

  function showToast(message, type = 'info', duration = 3000) {
    toast.value = { message, type, duration }
    setTimeout(() => { toast.value = null }, duration)
  }

  function showModal(config) {
    modal.value = config
  }

  function closeModal() {
    modal.value = null
  }

  return {
    sidebarOpen, loading, toast, modal,
    toggleSidebar, showToast, showModal, closeModal,
  }
})
