import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { authAPI } from '../api/auth';

const TOKEN_KEY = 'access_token';

export const useAuthStore = create(
  persist(
    (set) => {
      const startSession = (data) => {
        localStorage.setItem(TOKEN_KEY, data.access_token);
        set({ user: data.user, isAuthenticated: true });
      };

      const endSession = () => {
        localStorage.removeItem(TOKEN_KEY);
        set({ user: null, isAuthenticated: false });
      };

      return {
        user: null,
        isAuthenticated: false,
        isLoading: false,

        login: async (credentials) => {
          set({ isLoading: true });
          try {
            const res = await authAPI.login(credentials);
            startSession(res.data);
            return res;
          } finally {
            set({ isLoading: false });
          }
        },

        register: async (payload) => {
          set({ isLoading: true });
          try {
            const res = await authAPI.register(payload);
            startSession(res.data);
            return res;
          } finally {
            set({ isLoading: false });
          }
        },

        logout: async () => {
          try {
            await authAPI.logout();
          } catch {
            // token sudah tidak berlaku, tetap bersihkan sesi lokal
          } finally {
            endSession();
          }
        },

        // Dipanggil saat aplikasi dibuka untuk memastikan token masih berlaku
        fetchUser: async () => {
          if (!localStorage.getItem(TOKEN_KEY)) {
            endSession();
            return;
          }
          try {
            const res = await authAPI.me();
            set({ user: res.data.user, isAuthenticated: true });
          } catch {
            endSession();
          }
        },
      };
    },
    {
      name: 'auth-storage',
      partialize: (state) => ({ user: state.user, isAuthenticated: state.isAuthenticated }),
    }
  )
);