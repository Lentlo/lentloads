<template>
  <div class="search-page bg-gray-50">
    <div class="container-app py-4">
      <!-- Mobile Search Header -->
      <div class="lg:hidden mb-4">
        <!-- Main controls row -->
        <div class="flex items-center gap-2 mb-2">
          <!-- Filters Button -->
          <button
            @click="showFilters = true"
            class="flex items-center gap-2 px-4 py-2.5 bg-white rounded-full border shadow-sm flex-shrink-0"
          >
            <AdjustmentsHorizontalIcon class="w-5 h-5 text-gray-600" />
            <span class="text-sm font-medium">Filters</span>
            <span
              v-if="activeFilterCount > 0"
              class="w-5 h-5 bg-primary-600 text-white text-xs rounded-full flex items-center justify-center"
            >
              {{ activeFilterCount }}
            </span>
          </button>

          <!-- Near Me Button -->
          <button
            @click="handleNearMeClick"
            class="flex items-center gap-1.5 px-4 py-2.5 rounded-full border shadow-sm flex-shrink-0 text-sm font-medium transition"
            :class="locationActive ? 'bg-primary-600 text-white border-primary-600' : 'bg-white text-gray-700 hover:bg-gray-50'"
          >
            <MapPinIcon class="w-4 h-4" />
            <span>{{ locationActive && appStore.currentLocation?.city ? appStore.currentLocation.city : 'Near Me' }}</span>
            <span v-if="locationLoading" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <XMarkIcon v-if="locationActive && !locationLoading" class="w-4 h-4" />
          </button>

          <!-- Sort -->
          <div class="relative flex-shrink-0 ml-auto">
            <select
              v-model="filters.sort"
              @change="fetchListings"
              class="appearance-none px-4 py-2.5 pr-8 bg-white rounded-full border shadow-sm text-sm font-medium"
            >
              <option value="newest">Newest</option>
              <option value="price_low">Price: Low</option>
              <option value="price_high">Price: High</option>
            </select>
            <ChevronDownIcon class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
          </div>
        </div>

        <!-- Active Filter Tags - wrap to show all -->
        <div v-if="filters.q || filters.category || filters.city || filters.min_price || filters.max_price" class="flex flex-wrap gap-2">
          <span
            v-if="filters.q"
            class="px-3 py-1.5 bg-primary-100 text-primary-700 rounded-full text-sm flex items-center gap-1"
          >
            "{{ filters.q }}"
            <button @click="clearSearchQuery"><XMarkIcon class="w-4 h-4" /></button>
          </span>
          <span
            v-if="filters.category"
            class="px-3 py-1.5 bg-primary-100 text-primary-700 rounded-full text-sm flex items-center gap-1"
          >
            {{ getCategoryName(filters.category) }}
            <button @click="clearFilter('category')"><XMarkIcon class="w-4 h-4" /></button>
          </span>
          <span
            v-if="filters.city"
            class="px-3 py-1.5 bg-primary-100 text-primary-700 rounded-full text-sm flex items-center gap-1"
          >
            {{ filters.city }}
            <button @click="clearFilter('city')"><XMarkIcon class="w-4 h-4" /></button>
          </span>
          <span
            v-if="filters.min_price || filters.max_price"
            class="px-3 py-1.5 bg-primary-100 text-primary-700 rounded-full text-sm flex items-center gap-1"
          >
            ₹{{ filters.min_price || 0 }} - {{ filters.max_price || '∞' }}
            <button @click="clearPriceFilter"><XMarkIcon class="w-4 h-4" /></button>
          </span>
        </div>
      </div>

      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Filters Sidebar - Desktop -->
        <aside class="hidden lg:block lg:w-64 flex-shrink-0">
          <div class="card p-4 sticky top-20">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-gray-900">Filters</h3>
              <button @click="resetFilters" class="text-sm text-primary-600 hover:underline">
                Clear all
              </button>
            </div>

            <!-- Category -->
            <div class="mb-5">
              <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
              <select v-model="filters.category" class="input text-sm">
                <option value="">All Categories</option>
                <option v-for="category in categories" :key="category.id" :value="category.slug">
                  {{ category.name }}
                </option>
              </select>
            </div>

            <!-- Location -->
            <div class="mb-5">
              <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
              <input
                v-model="filters.city"
                type="text"
                placeholder="Enter city"
                class="input text-sm"
              />
            </div>

            <!-- Price Range -->
            <div class="mb-5">
              <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
              <div class="flex items-center gap-2">
                <input v-model="filters.min_price" type="number" placeholder="Min" class="input text-sm" />
                <span class="text-gray-400">-</span>
                <input v-model="filters.max_price" type="number" placeholder="Max" class="input text-sm" />
              </div>
            </div>

            <!-- Condition -->
            <div class="mb-5">
              <label class="block text-sm font-medium text-gray-700 mb-2">Condition</label>
              <div class="space-y-2">
                <label v-for="condition in conditions" :key="condition.value" class="flex items-center">
                  <input type="radio" v-model="filters.condition" :value="condition.value" class="text-primary-600" />
                  <span class="ml-2 text-sm text-gray-600">{{ condition.label }}</span>
                </label>
              </div>
            </div>

            <button @click="fetchListings" class="btn-primary w-full mb-3">
              Apply Filters
            </button>

            <!-- Save Search -->
            <button
              v-if="isAuthenticated && hasActiveFilters"
              @click="showSaveSearchModal = true"
              class="btn-secondary w-full text-sm"
            >
              <BookmarkIcon class="w-4 h-4 mr-2" />
              Save This Search
            </button>
          </div>
        </aside>

        <!-- Results -->
        <main class="flex-1">
          <!-- Header -->
          <div class="flex items-center justify-between mb-4">
            <div>
              <h1 class="text-lg lg:text-xl font-bold text-gray-900">
                {{ searchQuery ? `"${searchQuery}"` : 'All Listings' }}
              </h1>
              <p class="text-sm text-gray-500">{{ total }} results</p>
            </div>

            <div class="hidden lg:flex items-center gap-3">
              <!-- Near Me Button (Desktop) -->
              <button
                @click="handleNearMeClick"
                class="flex items-center gap-2 px-4 py-2 rounded-lg border text-sm font-medium transition"
                :class="locationActive ? 'bg-primary-600 text-white border-primary-600' : 'bg-white text-gray-700 hover:bg-gray-50'"
              >
                <MapPinIcon class="w-4 h-4" />
                <span>{{ locationActive && appStore.currentLocation?.city ? appStore.currentLocation.city : 'Near Me' }}</span>
                <span v-if="locationLoading" class="w-3 h-3 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
                <XMarkIcon v-if="locationActive && !locationLoading" class="w-4 h-4" />
              </button>

              <select v-model="filters.sort" @change="fetchListings" class="input w-auto text-sm">
                <option value="newest">Newest First</option>
                <option value="price_low">Price: Low to High</option>
                <option value="price_high">Price: High to Low</option>
              </select>
            </div>
          </div>

          <!-- Loading -->
          <div v-if="loading && !listings.length" class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <div v-for="i in 6" :key="i" class="card p-3">
              <div class="skeleton aspect-square mb-2 rounded-lg"></div>
              <div class="skeleton h-4 w-16 mb-1"></div>
              <div class="skeleton h-4 w-full mb-1"></div>
              <div class="skeleton h-3 w-20"></div>
            </div>
          </div>

          <!-- Results Grid -->
          <div v-else-if="listings.length" class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <ListingCard v-for="listing in listings" :key="listing.id" :listing="listing" />
          </div>

          <!-- Empty State -->
          <div v-else class="card p-8 text-center">
            <MagnifyingGlassIcon class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <h3 class="font-medium text-gray-900 mb-1">No results found</h3>
            <p class="text-sm text-gray-500 mb-4">Try adjusting your filters</p>
            <button @click="resetFilters" class="btn-primary">Clear Filters</button>
          </div>

          <!-- Load More -->
          <div v-if="hasMore" class="mt-6 text-center">
            <button @click="loadMore" :disabled="loading" class="btn-secondary">
              {{ loading ? 'Loading...' : 'Load More' }}
            </button>
          </div>
        </main>
      </div>
    </div>

    <!-- Mobile Filters Modal -->
    <div v-if="showFilters" class="filter-modal lg:hidden">
      <div class="filter-backdrop" @click="applyFilters"></div>

      <div class="filter-sheet">
        <!-- Header -->
        <div class="filter-header">
          <h3 class="text-lg font-semibold">Filters</h3>
          <div class="flex items-center gap-2">
            <button @click="resetFilters" class="text-sm text-primary-600 font-medium">Clear all</button>
            <button @click="applyFilters" class="p-2 hover:bg-gray-100 rounded-full">
              <XMarkIcon class="w-5 h-5" />
            </button>
          </div>
        </div>

        <!-- Filter Options - Scrollable -->
        <div class="filter-content">
          <!-- Category -->
          <div class="mb-6">
            <h4 class="font-medium text-gray-900 mb-3">Category</h4>
            <div class="flex flex-wrap gap-2">
              <button
                @click="filters.category = ''"
                class="px-4 py-2 rounded-full border text-sm transition"
                :class="!filters.category ? 'border-primary-500 bg-primary-50 text-primary-700' : 'hover:bg-gray-50'"
              >
                All
              </button>
              <button
                v-for="category in categories"
                :key="category.id"
                @click="filters.category = category.slug"
                class="px-4 py-2 rounded-full border text-sm transition"
                :class="filters.category === category.slug ? 'border-primary-500 bg-primary-50 text-primary-700' : 'hover:bg-gray-50'"
              >
                {{ category.name }}
              </button>
            </div>
          </div>

          <!-- Location -->
          <div class="mb-6">
            <h4 class="font-medium text-gray-900 mb-3">City</h4>
            <input v-model="filters.city" type="text" placeholder="Enter city name" class="input" />
          </div>

          <!-- Price Range -->
          <div class="mb-6">
            <h4 class="font-medium text-gray-900 mb-3">Price Range</h4>
            <div class="flex flex-wrap gap-2 mb-3">
              <button
                v-for="range in priceRanges"
                :key="range.label"
                @click="setPrice(range)"
                class="px-3 py-2 text-sm border rounded-full transition"
                :class="String(filters.min_price) === String(range.min) && String(filters.max_price) === String(range.max) ? 'border-primary-500 bg-primary-50 text-primary-700' : 'hover:bg-gray-50'"
              >
                {{ range.label }}
              </button>
            </div>
            <div class="flex items-center gap-3">
              <input v-model="filters.min_price" type="number" placeholder="Min" class="input text-center" />
              <span class="text-gray-400">to</span>
              <input v-model="filters.max_price" type="number" placeholder="Max" class="input text-center" />
            </div>
          </div>

          <!-- Condition -->
          <div class="mb-6">
            <h4 class="font-medium text-gray-900 mb-3">Condition</h4>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="condition in conditions"
                :key="condition.value"
                @click="filters.condition = condition.value"
                class="px-4 py-2 rounded-full border text-sm transition"
                :class="filters.condition === condition.value ? 'border-primary-500 bg-primary-50 text-primary-700' : 'hover:bg-gray-50'"
              >
                {{ condition.label }}
              </button>
            </div>
          </div>
        </div>

        <!-- Actions - Always visible at bottom -->
        <div class="filter-actions">
          <button
            v-if="isAuthenticated && hasActiveFilters"
            @click="showSaveSearchModal = true; showFilters = false"
            class="btn-secondary"
          >
            <BookmarkIcon class="w-4 h-4" />
          </button>
          <button @click="applyFilters" class="btn-primary flex-1">
            Show Results
          </button>
        </div>
      </div>
    </div>

    <!-- Save Search Modal -->
    <div v-if="showSaveSearchModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
      <div class="absolute inset-0 bg-black/50" @click="showSaveSearchModal = false"></div>
      <div class="relative bg-white w-full sm:max-w-sm sm:rounded-xl rounded-t-2xl p-6 safe-area-bottom animate-slide-up">
        <h3 class="text-lg font-semibold mb-4">Save This Search</h3>
        <p class="text-sm text-gray-500 mb-4">Get notified when new listings match your search criteria.</p>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Search Name</label>
          <input
            v-model="saveSearchName"
            type="text"
            class="input"
            placeholder="e.g., iPhone in Mumbai"
          />
        </div>
        <div class="flex gap-3">
          <button @click="showSaveSearchModal = false" class="btn-secondary flex-1">Cancel</button>
          <button @click="saveSearch" :disabled="!saveSearchName || savingSearch" class="btn-primary flex-1">
            {{ savingSearch ? 'Saving...' : 'Save Search' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Location Permission Modal -->
    <div v-if="showLocationModal" class="location-modal">
      <div class="location-backdrop" @click="showLocationModal = false"></div>
      <div class="location-sheet">
        <div class="text-center mb-4">
          <div class="w-16 h-16 mx-auto mb-4 bg-primary-100 rounded-full flex items-center justify-center">
            <MapPinIcon class="w-8 h-8 text-primary-600" />
          </div>
          <h3 class="text-lg font-semibold mb-2">Enable Location</h3>
          <p class="text-sm text-gray-500">
            To show listings near you, we need access to your location. This helps you find the best deals nearby.
          </p>
        </div>
        <div class="bg-gray-50 rounded-lg p-3 mb-4">
          <ul class="text-sm text-gray-600 space-y-2">
            <li class="flex items-start gap-2">
              <CheckCircleIcon class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
              <span>See distance to each listing</span>
            </li>
            <li class="flex items-start gap-2">
              <CheckCircleIcon class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
              <span>Sort by nearest first</span>
            </li>
            <li class="flex items-start gap-2">
              <CheckCircleIcon class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
              <span>Find deals in your area</span>
            </li>
          </ul>
        </div>
        <div class="location-actions">
          <button @click="showLocationModal = false" class="btn-secondary flex-1">Not Now</button>
          <button @click="requestLocationPermission" class="btn-primary flex-1">
            Allow Location
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { useAuthStore } from '@/stores/auth'
import { useListingsStore } from '@/stores/listings'
import { toast } from 'vue3-toastify'
import api from '@/services/api'
import ListingCard from '@/components/common/ListingCard.vue'
import {
  MagnifyingGlassIcon,
  AdjustmentsHorizontalIcon,
  XMarkIcon,
  ChevronDownIcon,
  BookmarkIcon,
  MapPinIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const appStore = useAppStore()
const authStore = useAuthStore()
const listingsStore = useListingsStore()

const loading = ref(false)
const listings = ref([])
const total = ref(0)
const currentPage = ref(1)
const lastPage = ref(1)
const showFilters = ref(false)
const showSaveSearchModal = ref(false)
const saveSearchName = ref('')
const savingSearch = ref(false)
const showLocationModal = ref(false)
const locationLoading = ref(false)
const locationActive = ref(false)

const filters = reactive({
  q: '',
  category: '',
  city: '',
  min_price: '',
  max_price: '',
  condition: '',
  sort: 'newest', // Default to newest, switch to nearest when location enabled
  latitude: null,
  longitude: null,
})

const conditions = [
  { value: '', label: 'All' },
  { value: 'new', label: 'New' },
  { value: 'like_new', label: 'Like New' },
  { value: 'good', label: 'Good' },
  { value: 'fair', label: 'Fair' },
]

const priceRanges = [
  { label: 'Under ₹1K', min: '', max: 1000 },
  { label: '₹1K - ₹5K', min: 1000, max: 5000 },
  { label: '₹5K - ₹10K', min: 5000, max: 10000 },
  { label: '₹10K+', min: 10000, max: '' },
]

const categories = computed(() => appStore.categories)
const isAuthenticated = computed(() => authStore.isAuthenticated)
const searchQuery = computed(() => filters.q)
const hasMore = computed(() => currentPage.value < lastPage.value)

const activeFilterCount = computed(() => {
  let count = 0
  if (filters.category) count++
  if (filters.city) count++
  if (filters.min_price || filters.max_price) count++
  if (filters.condition) count++
  return count
})

const hasActiveFilters = computed(() => {
  return filters.q || filters.category || filters.city || filters.min_price || filters.max_price || filters.condition
})

const getCategoryName = (slug) => {
  const cat = categories.value.find(c => c.slug === slug)
  return cat?.name || slug
}

const setPrice = (range) => {
  filters.min_price = range.min
  filters.max_price = range.max
}

const clearFilter = (key) => {
  filters[key] = ''
  fetchListings()
}

const clearPriceFilter = () => {
  filters.min_price = ''
  filters.max_price = ''
  fetchListings()
}

const clearSearchQuery = () => {
  filters.q = ''
  fetchListings()
}

const fetchListings = async (append = false) => {
  loading.value = true

  try {
    const params = {
      page: append ? currentPage.value + 1 : 1,
      sort: filters.sort,
    }

    // Only add non-empty filter values
    if (filters.q) params.q = filters.q
    if (filters.category) params.category = filters.category
    if (filters.city) params.city = filters.city
    if (filters.min_price) params.min_price = filters.min_price
    if (filters.max_price) params.max_price = filters.max_price
    if (filters.condition) params.condition = filters.condition

    // Add location for nearest sorting
    if (filters.latitude && filters.longitude) {
      params.latitude = filters.latitude
      params.longitude = filters.longitude
    }

    const response = await api.get('/listings', { params })

    if (append) {
      listings.value = [...listings.value, ...response.data.data]
    } else {
      listings.value = response.data.data
    }

    total.value = response.data.meta.total
    currentPage.value = response.data.meta.current_page
    lastPage.value = response.data.meta.last_page

    // Update URL with current filters
    const queryParams = { ...params }
    delete queryParams.page
    if (params.page > 1) queryParams.page = params.page
    router.replace({ query: queryParams })
  } catch (error) {
    toast.error('Failed to fetch listings')
  } finally {
    loading.value = false
  }
}

const loadMore = () => fetchListings(true)

const applyFilters = () => {
  showFilters.value = false
  fetchListings()
}

const resetFilters = () => {
  filters.category = ''
  filters.city = ''
  filters.min_price = ''
  filters.max_price = ''
  filters.condition = ''
  // Keep sort and location as they are
  showFilters.value = false
  fetchListings()
}

const saveSearch = async () => {
  if (!saveSearchName.value) return

  savingSearch.value = true
  try {
    await api.post('/saved-searches', {
      name: saveSearchName.value,
      query: filters.q || null,
      category_id: categories.value.find(c => c.slug === filters.category)?.id || null,
      city: filters.city || null,
      min_price: filters.min_price || null,
      max_price: filters.max_price || null,
      condition: filters.condition || null,
    })
    toast.success('Search saved! You\'ll be notified of new listings.')
    showSaveSearchModal.value = false
    saveSearchName.value = ''
  } catch (error) {
    toast.error('Failed to save search')
  } finally {
    savingSearch.value = false
  }
}

// Handle Near Me button click
const handleNearMeClick = () => {
  if (locationActive.value) {
    // Toggle off - clear location and switch to newest
    locationActive.value = false
    filters.latitude = null
    filters.longitude = null
    filters.sort = 'newest'
    localStorage.removeItem('nearMeActive')
    fetchListings()
    return
  }

  // Check if we already have permission (stored location)
  if (appStore.currentLocation?.latitude && appStore.currentLocation?.longitude) {
    // Already have location, just activate
    filters.latitude = appStore.currentLocation.latitude
    filters.longitude = appStore.currentLocation.longitude
    filters.sort = 'nearest'
    locationActive.value = true
    localStorage.setItem('nearMeActive', 'true')
    fetchListings()
    return
  }

  // Show educational popup before requesting permission
  showLocationModal.value = true
}

// Request location permission after user agrees
const requestLocationPermission = () => {
  showLocationModal.value = false
  locationLoading.value = true

  if (!navigator.geolocation) {
    toast.error('Geolocation is not supported by your browser')
    locationLoading.value = false
    return
  }

  navigator.geolocation.getCurrentPosition(
    async (position) => {
      filters.latitude = position.coords.latitude
      filters.longitude = position.coords.longitude
      // Backend automatically sorts by nearest when lat/lng provided
      locationActive.value = true

      // Reverse geocode to get city name
      try {
        const response = await api.post('/locations/detect', {
          latitude: position.coords.latitude,
          longitude: position.coords.longitude,
        })
        const locationData = response.data.data
        // Save to app store with city name
        appStore.setLocation({
          latitude: position.coords.latitude,
          longitude: position.coords.longitude,
          city: locationData.city,
          state: locationData.state,
        })
        if (locationData.city) {
          toast.success(`Location set to ${locationData.city}`)
        } else {
          toast.success('Location enabled! Showing nearest listings.')
        }
      } catch (e) {
        // Save without city name if reverse geocode fails
        appStore.setLocation({
          latitude: position.coords.latitude,
          longitude: position.coords.longitude,
        })
        toast.success('Location enabled! Showing nearest listings.')
      }

      locationLoading.value = false
      localStorage.setItem('nearMeActive', 'true')
      fetchListings()
    },
    (error) => {
      locationLoading.value = false
      if (error.code === error.PERMISSION_DENIED) {
        toast.error('Location access denied. Please enable in browser settings.')
      } else {
        toast.error('Could not get your location. Please try again.')
      }
    },
    { timeout: 10000, maximumAge: 300000 } // 10s timeout, cache for 5 min
  )
}

// Get approximate location from IP (used as fallback on page load)
const getLocationFromIP = async () => {
  try {
    const response = await api.get('/location/detect-by-ip')
    if (response.data.data?.latitude && response.data.data?.longitude) {
      // Store IP-based location but don't activate location filter automatically
      appStore.setLocation({
        latitude: response.data.data.latitude,
        longitude: response.data.data.longitude,
        city: response.data.data.city,
        source: 'ip'
      })
    }
  } catch (error) {
    console.log('IP location detection failed:', error)
  }
}

onMounted(() => {
  const query = route.query
  if (query.q) filters.q = query.q
  if (query.category) filters.category = query.category
  if (query.city) filters.city = query.city
  if (query.min_price) filters.min_price = query.min_price
  if (query.max_price) filters.max_price = query.max_price
  if (query.condition) filters.condition = query.condition
  if (query.sort) filters.sort = query.sort

  // Load saved location from store
  appStore.loadSavedLocation()

  // Restore Near Me state if it was active
  const nearMeWasActive = localStorage.getItem('nearMeActive') === 'true'
  if (nearMeWasActive && appStore.currentLocation?.latitude && appStore.currentLocation?.longitude) {
    filters.latitude = appStore.currentLocation.latitude
    filters.longitude = appStore.currentLocation.longitude
    // Backend automatically sorts by nearest when lat/lng provided
    locationActive.value = true
  } else {
    // Get IP-based location in background (doesn't require permission)
    getLocationFromIP()
  }

  fetchListings()
})

// Watch for route query changes
watch(() => route.query, (newQuery) => {
  let changed = false
  if (newQuery.q !== filters.q) { filters.q = newQuery.q || ''; changed = true }
  if (newQuery.category !== filters.category) { filters.category = newQuery.category || ''; changed = true }
  if (newQuery.city !== filters.city) { filters.city = newQuery.city || ''; changed = true }
  if (newQuery.min_price !== filters.min_price) { filters.min_price = newQuery.min_price || ''; changed = true }
  if (newQuery.max_price !== filters.max_price) { filters.max_price = newQuery.max_price || ''; changed = true }
  if (newQuery.condition !== filters.condition) { filters.condition = newQuery.condition || ''; changed = true }
  if (newQuery.sort !== filters.sort && newQuery.sort) { filters.sort = newQuery.sort; changed = true }
  if (changed) fetchListings()
}, { deep: true })
</script>

<style scoped>
/* Page container - fix for iOS viewport issues */
.search-page {
  min-height: 100vh;
  min-height: 100dvh;
  min-height: -webkit-fill-available;
}

/* Filter Modal Styles */
.filter-modal {
  position: fixed;
  inset: 0;
  z-index: 60;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

.filter-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
}

.filter-sheet {
  position: relative;
  background: white;
  width: 100%;
  border-radius: 1rem 1rem 0 0;
  display: flex;
  flex-direction: column;
  max-height: 70vh;
  max-height: 70dvh;
  /* Position above mobile nav */
  margin-bottom: calc(60px + env(safe-area-inset-bottom, 8px));
  animation: slide-up 0.3s ease-out;
}

/* Desktop - no mobile nav */
@media (min-width: 768px) {
  .filter-sheet {
    margin-bottom: 0;
  }
}

.filter-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  flex-shrink: 0;
}

.filter-content {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  -webkit-overflow-scrolling: touch;
  min-height: 0;
}

.filter-actions {
  display: flex;
  gap: 0.75rem;
  padding: 1rem;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
  border-radius: 0 0 1rem 1rem;
  flex-shrink: 0;
}

@keyframes slide-up {
  from { transform: translateY(100%); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.animate-slide-up { animation: slide-up 0.3s ease-out; }
.safe-area-bottom { padding-bottom: max(env(safe-area-inset-bottom, 0), 16px); }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
.scrollbar-hide::-webkit-scrollbar { display: none; }

/* Location Permission Modal */
.location-modal {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

@media (min-width: 640px) {
  .location-modal {
    align-items: center;
  }
}

.location-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
}

.location-sheet {
  position: relative;
  background: white;
  width: 100%;
  border-radius: 1rem 1rem 0 0;
  padding: 1.5rem;
  /* Position above mobile nav */
  margin-bottom: calc(70px + env(safe-area-inset-bottom, 8px));
  animation: slide-up 0.3s ease-out;
}

@media (min-width: 640px) {
  .location-sheet {
    max-width: 24rem;
    border-radius: 0.75rem;
    margin-bottom: 0;
  }
}

.location-actions {
  display: flex;
  gap: 0.75rem;
}
</style>
