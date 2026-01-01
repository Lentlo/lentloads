import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    loading: false,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    isAdmin: (state) => state.user?.role === 'admin' || state.user?.role === 'moderator',
    userName: (state) => state.user?.name || 'User',
    userAvatar: (state) => state.user?.avatar_url,
  },

  actions: {
    async register(data) {
      this.loading = true
      try {
        const response = await api.post('/auth/register', data)
        this.setAuth(response.data.data)
        return response.data
      } finally {
        this.loading = false
      }
    },

    async login(credentials) {
      this.loading = true
      try {
        const response = await api.post('/auth/login', credentials)
        this.setAuth(response.data.data)
        return response.data
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        await api.post('/auth/logout')
      } catch (e) {
        // Ignore errors
      } finally {
        this.clearAuth()
      }
    },

    async fetchUser() {
      if (!this.token) return

      this.loading = true
      try {
        const response = await api.get('/auth/user')
        this.user = response.data.data.user
        return response.data.data
      } catch (error) {
        if (error.response?.status === 401) {
          this.clearAuth()
        }
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateProfile(data) {
      const response = await api.put('/auth/profile', data)
      this.user = response.data.data
      return response.data
    },

    async updateAvatar(file) {
      const formData = new FormData()
      formData.append('avatar', file)
      const response = await api.post('/auth/avatar', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      this.user.avatar_url = response.data.data.avatar_url
      return response.data
    },

    async changePassword(data) {
      return await api.put('/auth/password', data)
    },

    async forgotPassword(email) {
      return await api.post('/auth/forgot-password', { email })
    },

    async resetPassword(data) {
      return await api.post('/auth/reset-password', data)
    },

    setAuth(data) {
      this.user = data.user
      this.token = data.token
      localStorage.setItem('token', data.token)
      api.defaults.headers.common['Authorization'] = `Bearer ${data.token}`
    },

    clearAuth() {
      this.user = null
      this.token = null
      localStorage.removeItem('token')
      delete api.defaults.headers.common['Authorization']
    },

    initAuth() {
      if (this.token) {
        api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
      }
    }
  }
})
