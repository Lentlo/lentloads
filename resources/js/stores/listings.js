import { defineStore } from 'pinia'
import api from '@/services/api'

export const useListingsStore = defineStore('listings', {
  state: () => ({
    listings: [],
    currentListing: null,
    myListings: [],
    favorites: [],
    filters: {
      q: '',
      category: null,
      city: null,
      min_price: null,
      max_price: null,
      condition: null,
      sort: 'nearest', // Default to nearest when location available
      latitude: null,
      longitude: null,
    },
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 20,
      total: 0,
    },
    loading: false,
  }),

  getters: {
    hasMore: (state) => state.pagination.current_page < state.pagination.last_page,
  },

  actions: {
    async fetchListings(params = {}, append = false) {
      this.loading = true
      try {
        const response = await api.get('/listings', {
          params: { ...this.filters, ...params }
        })

        if (append) {
          this.listings = [...this.listings, ...response.data.data]
        } else {
          this.listings = response.data.data
        }

        this.pagination = response.data.meta
        return response.data
      } finally {
        this.loading = false
      }
    },

    async fetchListing(slug) {
      this.loading = true
      try {
        const response = await api.get(`/listings/${slug}`)
        this.currentListing = response.data.data.listing
        return response.data.data
      } finally {
        this.loading = false
      }
    },

    async createListing(data) {
      const formData = new FormData()

      // Append all fields
      Object.keys(data).forEach(key => {
        if (key === 'images') {
          data.images.forEach(image => {
            formData.append('images[]', image)
          })
        } else if (key === 'attributes' && data.attributes) {
          formData.append('attributes', JSON.stringify(data.attributes))
        } else if (data[key] !== null && data[key] !== undefined) {
          formData.append(key, data[key])
        }
      })

      const response = await api.post('/listings', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })

      return response.data
    },

    async updateListing(id, data) {
      const response = await api.put(`/listings/${id}`, data)
      return response.data
    },

    async deleteListing(id) {
      await api.delete(`/listings/${id}`)
      this.myListings = this.myListings.filter(l => l.id !== id)
    },

    async markAsSold(id) {
      const response = await api.post(`/listings/${id}/sold`)
      const index = this.myListings.findIndex(l => l.id === id)
      if (index !== -1) {
        this.myListings[index] = response.data.data
      }
      return response.data
    },

    async renewListing(id) {
      const response = await api.post(`/listings/${id}/renew`)
      return response.data
    },

    async fetchMyListings(params = {}) {
      this.loading = true
      try {
        const response = await api.get('/my-listings', { params })
        this.myListings = response.data.data
        this.pagination = response.data.meta
        return response.data
      } finally {
        this.loading = false
      }
    },

    async fetchFavorites() {
      this.loading = true
      try {
        const response = await api.get('/favorites')
        this.favorites = response.data.data
        this.pagination = response.data.meta
        return response.data
      } finally {
        this.loading = false
      }
    },

    async toggleFavorite(listingId) {
      const response = await api.post(`/favorites/toggle/${listingId}`)
      return response.data.data
    },

    async addImages(listingId, images) {
      const formData = new FormData()
      images.forEach(image => {
        formData.append('images[]', image)
      })

      return await api.post(`/listings/${listingId}/images`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    },

    async deleteImage(listingId, imageId) {
      return await api.delete(`/listings/${listingId}/images/${imageId}`)
    },

    setFilters(filters) {
      this.filters = { ...this.filters, ...filters }
    },

    resetFilters() {
      // Preserve location when resetting filters
      const { latitude, longitude } = this.filters
      this.filters = {
        q: '',
        category: null,
        city: null,
        min_price: null,
        max_price: null,
        condition: null,
        sort: latitude && longitude ? 'nearest' : 'newest',
        latitude,
        longitude,
      }
    },

    clearListing() {
      this.currentListing = null
    },

    setUserLocation(latitude, longitude) {
      this.filters.latitude = latitude
      this.filters.longitude = longitude
      // Auto-switch to nearest sort when location is set
      if (latitude && longitude && this.filters.sort === 'newest') {
        this.filters.sort = 'nearest'
      }
    }
  }
})
