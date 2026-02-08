import apiClient from './client'

export const assessmentApi = {
  createSession: (data) => apiClient.post('/assessments/sessions', data),
  getSession: (id) => apiClient.get(`/assessments/sessions/${id}`),
  submitAnswer: (sessionId, data) => apiClient.post(`/assessments/sessions/${sessionId}/answers`, data),
  getNextQuestion: (sessionId) => apiClient.get(`/assessments/sessions/${sessionId}/next-question`),
  completeSession: (sessionId) => apiClient.post(`/assessments/sessions/${sessionId}/complete`),
  listSessions: (companyId) => apiClient.get(`/assessments/sessions`, { params: { company_id: companyId } }),
  getProgress: (sessionId) => apiClient.get(`/assessments/sessions/${sessionId}/progress`),
}
