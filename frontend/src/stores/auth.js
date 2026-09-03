import { defineStore } from 'pinia';
import api from '../services/api';

const getStoredUser = () => {
  const user = localStorage.getItem('user_data');
  if (!user || user === 'undefined') return null;
  try {
    return JSON.parse(user);
  } catch (e) {
    return null;
  }
};

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: getStoredUser(),
    token: localStorage.getItem('auth_token') || null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
  },
  actions: {
    async login(credentials) {
      const response = await api.post('/login', credentials);
      
      // Mengambil token & user secara fleksibel (mencakup response.data.token ATAU response.data.data.token)
      const resData = response.data.data || response.data;
      const token = resData.token || resData.access_token;
      const user = resData.user;

      if (token) {
        this.token = token;
        this.user = user;

        localStorage.setItem('auth_token', token);
        localStorage.setItem('user_data', JSON.stringify(user));
      }
      
      return response;
    },
    async register(userData) {
      const response = await api.post('/register', userData);
      
      const resData = response.data.data || response.data;
      const token = resData.token || resData.access_token;
      const user = resData.user;

      if (token) {
        this.token = token;
        this.user = user;

        localStorage.setItem('auth_token', token);
        localStorage.setItem('user_data', JSON.stringify(user));
      }

      return response;
    },
    async logout() {
      try {
        await api.post('/logout');
      } catch (e) {
        console.error(e);
      } finally {
        this.token = null;
        this.user = null;
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_data');
      }
    }
  }
});