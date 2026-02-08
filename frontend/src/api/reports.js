import apiClient from './client'

export const reportsApi = {
  generate: (data) => apiClient.post('/reports', data),
  get: (id) => apiClient.get(`/reports/${id}`),
  list: (companyId) => apiClient.get('/reports', { params: { company_id: companyId } }),
  export: (id, format) => apiClient.get(`/reports/${id}/export`, { params: { format } }),
}
