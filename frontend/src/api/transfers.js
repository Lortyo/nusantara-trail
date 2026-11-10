import apiClient from './client';

export const transfersAPI = {
  list: () => apiClient.get('/transfers'),
  respond: (id, action) => apiClient.patch(`/transfers/${id}`, { action }),
};