export function formatDate(dateStr) {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return new Intl.DateTimeFormat('ar-SA', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(date)
}

export function formatDateTime(dateStr) {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return new Intl.DateTimeFormat('ar-SA', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date)
}

export function formatPercentage(value) {
  return `${Math.round(value || 0)}%`
}

export function formatScore(score) {
  return Number(score || 0).toFixed(1)
}

export function getMaturityLabel(score) {
  if (score >= 90) return 'خبير'
  if (score >= 75) return 'متقدم'
  if (score >= 50) return 'متوسط'
  if (score >= 25) return 'نامي'
  return 'مبتدئ'
}

export function getMaturityColor(score) {
  if (score >= 90) return 'text-blue-600'
  if (score >= 75) return 'text-green-600'
  if (score >= 50) return 'text-yellow-600'
  if (score >= 25) return 'text-orange-600'
  return 'text-red-600'
}

export function truncate(str, maxLength = 50) {
  if (!str || str.length <= maxLength) return str || ''
  return str.substring(0, maxLength) + '...'
}
