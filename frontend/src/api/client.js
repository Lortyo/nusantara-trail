import axios from 'axios';
import { jwtDecode } from 'jwt-decode';

export const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1';

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

function clearSession() {
  localStorage.removeItem('access_token');
  localStorage.removeItem('auth-storage');
}

function goToLogin() {
  if (window.location.pathname !== '/login') {
    window.location.href = '/login';
  }
}

// Satu proses refresh untuk semua request yang berjalan bersamaan
let refreshPromise = null;

function refreshToken(token) {
  if (!refreshPromise) {
    refreshPromise = axios
      .post(`${API_BASE_URL}/auth/refresh`, {}, { headers: { Authorization: `Bearer ${token}` } })
      .then((res) => {
        const fresh = res.data.data.access_token;
        localStorage.setItem('access_token', fresh);
        return fresh;
      })
      .finally(() => {
        refreshPromise = null;
      });
  }
  return refreshPromise;
}

apiClient.interceptors.request.use(async (config) => {
  let token = localStorage.getItem('access_token');
  if (!token) return config;

  try {
    const { exp } = jwtDecode(token);
    if (exp - Date.now() / 1000 < 300) {
      token = await refreshToken(token);
    }
  } catch (error) {
    clearSession();
    goToLogin();
    throw error;
  }

  config.headers.Authorization = `Bearer ${token}`;
  return config;
});

apiClient.interceptors.response.use(
  (response) => response.data,
  (error) => {
    const url = error.config?.url ?? '';
    const isAuthForm = url.includes('/auth/login') || url.includes('/auth/register');
    if (error.response?.status === 401 && !isAuthForm) {
      clearSession();
      goToLogin();
    }
    return Promise.reject(error);
  }
);

// Ambil pesan error yang paling berguna dari respons API
export function getErrorMessage(error, fallback = 'Something went wrong. Please try again.') {
  const data = error?.response?.data;
  if (data?.errors && typeof data.errors === 'object') {
    const first = Object.values(data.errors).flat()[0];
    if (typeof first === 'string') return first;
  }
  return data?.message || fallback;
}

export default apiClient;