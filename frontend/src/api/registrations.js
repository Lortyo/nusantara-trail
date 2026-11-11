import apiClient from './client';

export const registrationsAPI = {
  create: (data) => apiClient.post('/registrations', data),
  my: () => apiClient.get('/registrations/my'),
  get: (id) => apiClient.get(`/registrations/${id}`),
  cancel: (id) => apiClient.delete(`/registrations/${id}`),
  credential: (id) => apiClient.get(`/registrations/${id}/credential`),
  changeCategory: (id, data) => apiClient.post(`/registrations/${id}/change-category`, data),
  transfer: (id, recipientEmail) =>
    apiClient.post(`/registrations/${id}/transfer`, { recipient_email: recipientEmail }),
};