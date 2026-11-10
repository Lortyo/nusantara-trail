import apiClient from './client';

export const profileAPI = {
  get: () => apiClient.get('/profile'),
  update: (data) => apiClient.put('/profile', data),
};