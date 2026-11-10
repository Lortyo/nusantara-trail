import apiClient from './client';

export const eventsAPI = {
  list: (params) => apiClient.get('/events', { params }),
  get: (id) => apiClient.get(`/events/${id}`),
  create: (data) => apiClient.post('/events', data),
  update: (id, data) => apiClient.put(`/events/${id}`, data),
  remove: (id) => apiClient.delete(`/events/${id}`),
  setStatus: (id, action) => apiClient.patch(`/events/${id}/status`, { action }),
  entryList: (id, params) => apiClient.get(`/events/${id}/entry-list`, { params }),
  results: (id, params) => apiClient.get(`/events/${id}/results`, { params }),
  categories: (eventId) => apiClient.get(`/events/${eventId}/categories`),
  createCategory: (eventId, data) => apiClient.post(`/events/${eventId}/categories`, data),
  updateCategory: (id, data) => apiClient.put(`/categories/${id}`, data),
  removeCategory: (id) => apiClient.delete(`/categories/${id}`),
};