import apiClient from './client';

export const organizerAPI = {
  registrations: (params) => apiClient.get('/organizer/registrations', { params }),
  scan: (data) => apiClient.post('/organizer/checkin/scan', data),
  summary: (params) => apiClient.get('/organizer/dashboard/summary', { params }),
  inputResults: (eventId, results) =>
    apiClient.post(`/organizer/events/${eventId}/results`, { results }),
  updateResult: (id, data) => apiClient.put(`/organizer/results/${id}`, data),
};