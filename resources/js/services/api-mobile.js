/**
 * Mobile API Service
 * Uses full URLs for API calls (required for Capacitor apps).
 */

import axios from 'axios'
import { toast } from 'vue3-toastify'

// API Base URL - always use full URL for mobile
// TODO: Change to https://lentloads.com/api/v1 when migrating to production
const API_BASE_URL = 'https://phplaravel-1016958-6108537.cloudwaysapps.com/api/v1'

const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 30000, // 30 second timeout
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor - add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor - handle errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const { response } = error

    if (response) {
      switch (response.status) {
        case 401:
          // Unauthorized - clear auth and redirect to login
          localStorage.removeItem('token')
          // Use hash-based navigation for mobile
          if (!window.location.hash.includes('/login')) {
            window.location.hash = '#/login'
          }
          break

        case 403:
          toast.error('You do not have permission to perform this action')
          break

        case 404:
          // Don't show toast for 404 - let components handle it
          break

        case 422:
          // Validation errors
          const errors = response.data.errors
          if (errors) {
            const firstError = Object.values(errors)[0]
            toast.error(Array.isArray(firstError) ? firstError[0] : firstError)
          } else if (response.data.message) {
            toast.error(response.data.message)
          }
          break

        case 429:
          toast.error('Too many requests. Please try again later.')
          break

        case 500:
          toast.error('Server error. Please try again later.')
          break

        default:
          if (response.data.message) {
            toast.error(response.data.message)
          }
      }
    } else if (error.code === 'ECONNABORTED') {
      toast.error('Request timed out. Please check your connection.')
    } else {
      toast.error('Network error. Please check your connection.')
    }

    return Promise.reject(error)
  }
)

export default api
