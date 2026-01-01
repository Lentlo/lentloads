<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container-app py-6">
      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Filters Sidebar -->
        <aside class="lg:w-64 flex-shrink-0">
          <div class="card p-4 sticky top-20">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-gray-900">Filters</h3>
              <button @click="resetFilters" class="text-sm text-primary-600 hover:underline">
                Clear all
              </button>
            </div>

            <!-- Category Filter -->
            <div class="mb-6">
              <h4 class="font-medium text-gray-700 mb-2">Category</h4>
              <select v-model="filters.category" class="input text-sm">
                <option value="">All Categories</option>
                <option
                  v-for="category in categories"
                  :key="category.id"
                  :value="category.slug"
                >
                  {{ category.name }}
                </option>
              </select>
            </div>

            <!-- Location Filter -->
            <div class="mb-6">
              <h4 class="font-medium text-gray-700 mb-2">Location</h4>
              <input
                v-model="filters.city"
                type="text"
                placeholder="Enter city"
                class="input text-sm"
              />
            </div>

            <!-- Price Range -->
            <div class="mb-6">
              <h4 class="font-medium text-gray-700 mb-2">Price Range</h4>
              <div class="flex items-center gap-2">
                <input
                  v-model="filters.min_price"
                  type="number"
                  placeholder="Min"
                  class="input text-sm"
                />
                <span class="text-gray-400">-</span>
                <input
                  v-model="filters.max_price"
                  type="number"
                  placeholder="Max"
                  class="input text-sm"
                />
              </div>
            </div>

            <!-- Condition Filter -->
            <div class="mb-6">
              <h4 class="font-medium text-gray-700 mb-2">Condition</h4>
              <div class="space-y-2">
                <label v-for="condition in conditions" :key="condition.value" class="flex items-center">
                  <input
                    type="radio"
                    v-model="filters.condition"
                    :value="condition.value"
                    class="text-primary-600"
                  />
                  <span class="ml-2 text-sm text-gray-600">{{ condition.label }}</span>
                </label>
              </div>
            </div>

            <button @click="applyFilters" class="btn-primary w-full">
              Apply Filters
            </button>
          </div>
        </aside>

        <!-- Results -->
        <main class="flex-1">
          <!-- Header -->
          <div class="flex items-center justify-between mb-4">
            <div>
              <h1 class="text-xl font-bold text-gray-900">
                {{ searchQuery ? `Results for "${searchQuery}"` : 'All Listings' }}
              </h1>
              <p class="text-sm text-gray-500">{{ total }} results found</p>
            </div>

            <select v-model="filters.sort" @change="fetchListings" class="input w-auto text-sm">
              <option value="newest">Newest First</option>
              <option value="price_low">Price: Low to High</option>
              <option value="price_high">Price: High to Low</option>
              <option value="popular">Most Popular</option>
            </select>
          </div>

          <!-- Loading -->
          <div v-if="loading && !listings.length" class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div v-for="i in 9" :key="i" class="card p-4">
              <div class="skeleton h-40 mb-3"></div>
              <div class="skeleton h-4 w-20 mb-2"></div>
              <div class="skeleton h-4 w-full mb-2"></div>
              <div class="skeleton h-3 w-24"></div>
            </div>
          </div>

          <!-- Results Grid -->
          <div v-else-if="listings.length" class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <ListingCard
              v-for="listing in listings"
              :key="listing.id"
              :listing="listing"
            />
          </div>

          <!-- Empty State -->
          <div v-else class="card p-12 text-center">
            <MagnifyingGlassIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">No results found</h3>
            <p class="text-gray-500 mb-4">Try adjusting your search or filters</p>
            <button @click="resetFilters" class="btn-primary">
              Clear Filters
            </button>
          </div>

          <!-- Load More -->
          <div v-if="hasMore" class="mt-6 text-center">
            <button
              @click="loadMore"
              :disabled="loading"
              class="btn-secondary"
            >
              {{ loading ? 'Loading...' : 'Load More' }}
            </button>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { useListingsStore } from '@/stores/listings'
import ListingCard from '@/components/common/ListingCard.vue'
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const appStore = useAppStore()
const listingsStore = useListingsStore()

const loading = ref(false)
const listings = ref([])
const total = ref(0)
const currentPage = ref(1)
const lastPage = ref(1)

const filters = reactive({
  q: '',
  category: '',
  city: '',
  min_price: '',
  max_price: '',
  condition: '',
  sort: 'newest',
})

const conditions = [
  { value: '', label: 'All Conditions' },
  { value: 'new', label: 'New' },
  { value: 'like_new', label: 'Like New' },
  { value: 'good', label: 'Good' },
  { value: 'fair', label: 'Fair' },
]

const categories = computed(() => appStore.categories)
const searchQuery = computed(() => filters.q)
const hasMore = computed(() => currentPage.value < lastPage.value)

const fetchListings = async (append = false) => {
  loading.value = true

  try {
    const params = {
      ...filters,
      page: append ? currentPage.value + 1 : 1,
    }

    // Remove empty params
    Object.keys(params).forEach(key => {
      if (!params[key]) delete params[key]
    })

    const response = await listingsStore.fetchListings(params, append)

    if (append) {
      listings.value = [...listings.value, ...response.data]
    } else {
      listings.value = response.data
    }

    total.value = response.meta.total
    currentPage.value = response.meta.current_page
    lastPage.value = response.meta.last_page

    // Update URL
    router.replace({ query: params })
  } finally {
    loading.value = false
  }
}

const loadMore = () => {
  fetchListings(true)
}

const applyFilters = () => {
  fetchListings()
}

const resetFilters = () => {
  Object.keys(filters).forEach(key => {
    filters[key] = key === 'sort' ? 'newest' : ''
  })
  fetchListings()
}

// Initialize from URL params
onMounted(() => {
  const query = route.query
  if (query.q) filters.q = query.q
  if (query.category) filters.category = query.category
  if (query.city) filters.city = query.city
  if (query.min_price) filters.min_price = query.min_price
  if (query.max_price) filters.max_price = query.max_price
  if (query.condition) filters.condition = query.condition
  if (query.sort) filters.sort = query.sort

  fetchListings()
})

// Watch for route changes
watch(() => route.query, () => {
  if (route.query.q !== filters.q) {
    filters.q = route.query.q || ''
    fetchListings()
  }
})
</script>
