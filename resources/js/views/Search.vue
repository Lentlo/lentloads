<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container-app py-4">
      <!-- Mobile Search Header -->
      <div class="lg:hidden mb-4">
        <!-- Active Filters Summary -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide">
          <button
            @click="showFilters = true"
            class="flex items-center gap-2 px-4 py-2 bg-white rounded-full border shadow-sm flex-shrink-0"
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

          <!-- Sort Dropdown -->
          <div class="relative flex-shrink-0">
            <select
              v-model="filters.sort"
              @change="fetchListings"
              class="appearance-none px-4 py-2 pr-8 bg-white rounded-full border shadow-sm text-sm font-medium"
            >
              <option value="newest">Newest</option>
              <option value="price_low">Price: Low</option>
              <option value="price_high">Price: High</option>
              <option value="popular">Popular</option>
            </select>
            <ChevronDownIcon class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
          </div>

          <!-- Active Filter Tags -->
          <span
            v-if="filters.category"
            class="px-3 py-1.5 bg-primary-100 text-primary-700 rounded-full text-sm flex items-center gap-1 flex-shrink-0"
          >
            {{ getCategoryName(filters.category) }}
            <button @click="clearFilter('category')" class="hover:text-primary-900">
              <XMarkIcon class="w-4 h-4" />
            </button>
          </span>
          <span
            v-if="filters.city"
            class="px-3 py-1.5 bg-primary-100 text-primary-700 rounded-full text-sm flex items-center gap-1 flex-shrink-0"
          >
            {{ filters.city }}
            <button @click="clearFilter('city')" class="hover:text-primary-900">
              <XMarkIcon class="w-4 h-4" />
            </button>
          </span>
          <span
            v-if="filters.min_price || filters.max_price"
            class="px-3 py-1.5 bg-primary-100 text-primary-700 rounded-full text-sm flex items-center gap-1 flex-shrink-0"
          >
            ₹{{ filters.min_price || 0 }} - ₹{{ filters.max_price || '∞' }}
            <button @click="clearPriceFilter" class="hover:text-primary-900">
              <XMarkIcon class="w-4 h-4" />
            </button>
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

            <!-- Category Filter -->
            <div class="mb-6">
              <h4 class="font-medium text-gray-700 mb-2 text-sm">Category</h4>
              <select v-model="filters.category" @change="fetchListings" class="input text-sm">
                <option value="">All Categories</option>
                <option v-for="category in categories" :key="category.id" :value="category.slug">
                  {{ category.name }}
                </option>
              </select>
            </div>

            <!-- Location Filter -->
            <div class="mb-6">
              <h4 class="font-medium text-gray-700 mb-2 text-sm">Location</h4>
              <input
                v-model="filters.city"
                type="text"
                placeholder="Enter city"
                class="input text-sm"
                @change="fetchListings"
              />
            </div>

            <!-- Price Range -->
            <div class="mb-6">
              <h4 class="font-medium text-gray-700 mb-2 text-sm">Price Range</h4>
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
              <h4 class="font-medium text-gray-700 mb-2 text-sm">Condition</h4>
              <div class="space-y-2">
                <label v-for="condition in conditions" :key="condition.value" class="flex items-center">
                  <input
                    type="radio"
                    v-model="filters.condition"
                    :value="condition.value"
                    @change="fetchListings"
                    class="text-primary-600"
                  />
                  <span class="ml-2 text-sm text-gray-600">{{ condition.label }}</span>
                </label>
              </div>
            </div>

            <button @click="fetchListings" class="btn-primary w-full">
              Apply Filters
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

            <!-- Desktop Sort -->
            <select v-model="filters.sort" @change="fetchListings" class="hidden lg:block input w-auto text-sm">
              <option value="newest">Newest First</option>
              <option value="price_low">Price: Low to High</option>
              <option value="price_high">Price: High to Low</option>
              <option value="popular">Most Popular</option>
            </select>
          </div>

          <!-- Loading -->
          <div v-if="loading && !listings.length" class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <div v-for="i in 9" :key="i" class="card p-3">
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
            <button @click="resetFilters" class="btn-primary">
              Clear Filters
            </button>
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
    <div v-if="showFilters" class="fixed inset-0 z-50 flex items-end justify-center lg:hidden">
      <div class="absolute inset-0 bg-black/50" @click="showFilters = false"></div>

      <div class="relative bg-white w-full rounded-t-2xl max-h-[85vh] flex flex-col animate-slide-up">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b flex-shrink-0">
          <h3 class="text-lg font-semibold">Filters</h3>
          <button @click="showFilters = false" class="p-2 hover:bg-gray-100 rounded-full">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <!-- Filter Options -->
        <div class="flex-1 overflow-y-auto p-4 space-y-6">
          <!-- Category -->
          <div>
            <h4 class="font-medium text-gray-900 mb-3">Category</h4>
            <div class="grid grid-cols-2 gap-2">
              <button
                @click="filters.category = ''"
                class="p-3 rounded-xl border text-sm text-left transition"
                :class="!filters.category ? 'border-primary-500 bg-primary-50 text-primary-700' : 'hover:bg-gray-50'"
              >
                All Categories
              </button>
              <button
                v-for="category in categories.slice(0, 7)"
                :key="category.id"
                @click="filters.category = category.slug"
                class="p-3 rounded-xl border text-sm text-left transition"
                :class="filters.category === category.slug ? 'border-primary-500 bg-primary-50 text-primary-700' : 'hover:bg-gray-50'"
              >
                {{ category.name }}
              </button>
            </div>
          </div>

          <!-- Location -->
          <div>
            <h4 class="font-medium text-gray-900 mb-3">Location</h4>
            <input
              v-model="filters.city"
              type="text"
              placeholder="Enter city name"
              class="input"
            />
          </div>

          <!-- Price Range -->
          <div>
            <h4 class="font-medium text-gray-900 mb-3">Price Range</h4>
            <div class="flex items-center gap-3">
              <div class="flex-1">
                <input
                  v-model="filters.min_price"
                  type="number"
                  placeholder="Min"
                  class="input text-center"
                />
              </div>
              <span class="text-gray-400">to</span>
              <div class="flex-1">
                <input
                  v-model="filters.max_price"
                  type="number"
                  placeholder="Max"
                  class="input text-center"
                />
              </div>
            </div>
            <!-- Quick Price Options -->
            <div class="flex flex-wrap gap-2 mt-3">
              <button
                v-for="range in priceRanges"
                :key="range.label"
                @click="setPrice(range)"
                class="px-3 py-1.5 text-sm border rounded-full hover:bg-gray-50"
              >
                {{ range.label }}
              </button>
            </div>
          </div>

          <!-- Condition -->
          <div>
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

        <!-- Actions -->
        <div class="flex gap-3 p-4 border-t bg-gray-50 flex-shrink-0 safe-area-bottom">
          <button @click="resetFilters" class="btn-secondary flex-1">
            Clear All
          </button>
          <button @click="applyFilters" class="btn-primary flex-1">
            Show {{ total }} Results
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
import { useListingsStore } from '@/stores/listings'
import ListingCard from '@/components/common/ListingCard.vue'
import {
  MagnifyingGlassIcon,
  AdjustmentsHorizontalIcon,
  XMarkIcon,
  ChevronDownIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const appStore = useAppStore()
const listingsStore = useListingsStore()

const loading = ref(false)
const listings = ref([])
const total = ref(0)
const currentPage = ref(1)
const lastPage = ref(1)
const showFilters = ref(false)

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
  { label: '₹10K - ₹50K', min: 10000, max: 50000 },
  { label: 'Over ₹50K', min: 50000, max: '' },
]

const categories = computed(() => appStore.categories)
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

const fetchListings = async (append = false) => {
  loading.value = true

  try {
    const params = { ...filters, page: append ? currentPage.value + 1 : 1 }
    Object.keys(params).forEach(key => { if (!params[key]) delete params[key] })

    const response = await listingsStore.fetchListings(params, append)

    if (append) {
      listings.value = [...listings.value, ...response.data]
    } else {
      listings.value = response.data
    }

    total.value = response.meta.total
    currentPage.value = response.meta.current_page
    lastPage.value = response.meta.last_page

    router.replace({ query: params })
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
  Object.keys(filters).forEach(key => {
    filters[key] = key === 'sort' ? 'newest' : ''
  })
  showFilters.value = false
  fetchListings()
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
  fetchListings()
})

watch(() => route.query, () => {
  if (route.query.q !== filters.q) {
    filters.q = route.query.q || ''
    fetchListings()
  }
})
</script>

<style scoped>
@keyframes slide-up {
  from { transform: translateY(100%); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
.animate-slide-up { animation: slide-up 0.3s ease-out; }
.safe-area-bottom { padding-bottom: max(env(safe-area-inset-bottom, 0), 16px); }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
.scrollbar-hide::-webkit-scrollbar { display: none; }
</style>
