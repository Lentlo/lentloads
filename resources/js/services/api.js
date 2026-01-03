import axios from 'axios'
import { toast } from 'vue3-toastify'
import { Capacitor } from '@capacitor/core'

// Use full URL for native apps, relative for web
const getBaseURL = () => {
  if (Capacitor.isNativePlatform()) {
    // TODO: Change to https://lentloads.com/api/v1 after migration
    return 'https://phplaravel-1016958-6108537.cloudwaysapps.com/api/v1'
  }
  return '/api/v1'
}

const api = axios.create({
  baseURL: getBaseURL(),
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor
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

// Response interceptor
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    const { response } = error

    if (response) {
      switch (response.status) {
        case 401:
          // Unauthorized - clear auth and redirect to login
          localStorage.removeItem('token')
          if (window.location.pathname !== '/login') {
            window.location.href = '/login'
          }
          break

        case 403:
          toast.error('You do not have permission to perform this action')
          break

        case 404:
          toast.error('Resource not found')
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
          toast.error(response.data.message || 'Something went wrong')
      }
    } else {
      toast.error('Network error. Please check your connection.')
    }

    return Promise.reject(error)
  }
)

export default api
