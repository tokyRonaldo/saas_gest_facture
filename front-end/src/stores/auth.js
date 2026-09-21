import { defineStore } from 'pinia'
import api from '@/api/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.user,

    can: (state) => (permission) =>
      state.user?.permissions?.includes(permission) ?? false,
  },

  actions: {
    async login(email, password) {
      await api.get('/sanctum/csrf-cookie')
      

      const { data } = await api.post('/api/login', {
        email,
        password,
      })

      this.user = data.user
    },

    async fetchUser() {
      try {
        const { data } = await api.get('/api/me')

        this.user = data.user
      } catch {
        this.user = null
      }
    },

    async logout() {
      await api.post('/api/logout')

      this.user = null
    },
  },
})