import apiClient from './client';

export const paymentsAPI = {
  checkout: (data) => apiClient.post('/payments/checkout', data),
  status: (orderId, sync = false) =>
    apiClient.get(`/payments/${orderId}`, { params: sync ? { sync: 1 } : {} }),
};