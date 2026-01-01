import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAppStore = defineStore('app', {
  state: () => ({
    categories: [],
    popularCities: [],
    settings: {},
    isLoading: false,
    initialized: false,
    currentLocation: null,
  }),

  getters: {
    parentCategories: (state) => state.categories.filter(c => !c.parent_id),
    getCategoryBySlug: (state) => (slug) => {
      for (const cat of state.categories) {
        if (cat.slug === slug) return cat
        if (cat.children) {
          const child = cat.children.find(c => c.slug === slug)
          if (child) return child
        }
      }
      return null
    },
  },

  actions: {
    async initialize() {
      if (this.initialized) return

      this.isLoading = true
      try {
        const [categoriesRes, citiesRes] = await Promise.all([
          api.get('/categories'),
          api.get('/locations/popular-cities'),
        ])

        this.categories = categoriesRes.data.data
        this.popularCities = citiesRes.data.data
        this.initialized = true
      } catch (error) {
        console.error('Failed to initialize app:', error)
      } finally {
        this.isLoading = false
      }
    },

    async fetchCategories() {
      try {
        const response = await api.get('/categories')
        this.categories = response.data.data
        return this.categories
      } catch (error) {
        console.error('Failed to fetch categories:', error)
        return []
      }
    },

    async fetchSettings() {
      const response = await api.get('/settings')
      this.settings = response.data.data
    },

    async detectLocation() {
      return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
          reject(new Error('Geolocation not supported'))
          return
        }

        navigator.geolocation.getCurrentPosition(
          async (position) => {
            try {
              const response = await api.post('/locations/detect', {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
              })
              this.currentLocation = response.data.data
              resolve(this.currentLocation)
            } catch (error) {
              reject(error)
            }
          },
          (error) => {
            reject(error)
          }
        )
      })
    },

    setLoading(value) {
      this.isLoading = value
    },

    setLocation(location) {
      this.currentLocation = location
      localStorage.setItem('userLocation', JSON.stringify(location))
    },

    loadSavedLocation() {
      const saved = localStorage.getItem('userLocation')
      if (saved) {
        this.currentLocation = JSON.parse(saved)
      }
    }
  }
})
