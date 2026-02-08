import { useUiStore } from '@/stores/ui'

export function useToast() {
  const uiStore = useUiStore()

  function success(message) {
    uiStore.showToast(message, 'success')
  }

  function error(message) {
    uiStore.showToast(message, 'error', 5000)
  }

  function info(message) {
    uiStore.showToast(message, 'info')
  }

  function warning(message) {
    uiStore.showToast(message, 'warning', 4000)
  }

  return { success, error, info, warning }
}
