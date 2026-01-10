<template>
  <div class="all-ads-page min-h-screen bg-gray-50">
    <div class="container-app py-4">
      <!-- Page Header -->
      <div class="mb-4">
        <h1 class="text-xl font-bold text-gray-900">All Ads</h1>
        <p class="text-sm text-gray-500">Browse by category or explore all listings</p>
      </div>

      <!-- Categories Section -->
      <div class="mb-6">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Categories</h2>

        <!-- Loading skeleton for categories -->
        <div v-if="loadingCategories" class="grid grid-cols-3 sm:grid-cols-4 gap-3">
          <div v-for="i in 8" :key="i" class="category-skeleton">
            <div class="skeleton w-10 h-10 rounded-lg mb-2"></div>
            <div class="skeleton h-3 w-16"></div>
          </div>
        </div>

        <!-- Categories Grid -->
        <div v-else class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
          <router-link
            v-for="category in mainCategories"
            :key="category.id"
            :to="`/category/${category.slug}`"
            class="category-card"
          >
            <div class="category-icon">
              <img
                v-if="category.icon_url"
                :src="category.icon_url"
                :alt="category.name"
                class="w-6 h-6"
              />
              <Squares2X2Icon v-else class="w-6 h-6 text-primary-600" />
            </div>
            <span class="category-name">{{ category.name }}</span>
          </router-link>
        </div>

        <!-- No categories -->
        <div v-if="!loadingCategories && mainCategories.length === 0" class="text-center py-4 text-gray-500 text-sm">
          No categories found
        </div>
      </div>

      <!-- All Listings Section -->
      <div>
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">All Listings</h2>
          <select v-model="sort" @change="fetchListings" class="sort-select">
            <option value="newest">Newest First</option>
            <option value="price_low">Price: Low to High</option>
            <option value="price_high">Price: High to Low</option>
          </select>
        </div>

        <!-- Loading skeleton for listings -->
        <div v-if="loadingListings && listings.length === 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
          <div v-for="i in 8" :key="i" class="card p-3">
            <div class="skeleton h-32 mb-2 rounded-lg"></div>
            <div class="skeleton h-4 w-20 mb-1"></div>
            <div class="skeleton h-3 w-full"></div>
          </div>
        </div>

        <!-- Listings Grid -->
        <div v-else-if="listings.length" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
          <ListingCard
            v-for="listing in listings"
            :key="listing.id"
            :listing="listing"
          />
        </div>

        <!-- No listings -->
        <div v-else class="card p-8 text-center">
          <p class="text-gray-500">No listings found</p>
        </div>

        <!-- Load More Button -->
        <div v-if="hasMore" class="mt-6 text-center">
          <button
            @click="loadMore"
            :disabled="loadingMore"
            class="load-more-btn"
          >
            <span v-if="loadingMore" class="flex items-center gap-2">
              <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
                <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" class="opacity-75"></path>
              </svg>
              Loading...
            </span>
            <span v-else>Load More</span>
          </button>
        </div>

        <!-- Showing count -->
        <p v-if="listings.length > 0" class="text-center text-sm text-gray-500 mt-4">
          Showing {{ listings.length }} of {{ total }} ads
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAppStore } from '@/stores/app'
import api from '@/services/api'
import ListingCard from '@/components/common/ListingCard.vue'
import { Squares2X2Icon } from '@heroicons/vue/24/outline'

const appStore = useAppStore()

// Categories state
const loadingCategories = ref(true)
const categories = ref([])

// Listings state
const loadingListings = ref(true)
const loadingMore = ref(false)
const listings = ref([])
const sort = ref('newest')
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)

// Get main categories (parent_id is null or 0)
const mainCategories = computed(() => {
  return categories.value.filter(cat => !cat.parent_id)
})

const hasMore = computed(() => currentPage.value < lastPage.value)

// Fetch categories with listing counts
const fetchCategories = async () => {
  loadingCategories.value = true
  try {
    // Try to get from store first
    if (appStore.categories?.length) {
      categories.value = appStore.categories
    } else {
      const response = await api.get('/categories')
      categories.value = response.data.data || []
    }
  } catch (error) {
    console.error('Failed to fetch categories:', error)
  } finally {
    loadingCategories.value = false
  }
}

// Fetch listings
const fetchListings = async (append = false) => {
  if (append) {
    loadingMore.value = true
  } else {
    loadingListings.value = true
    currentPage.value = 1
  }

  try {
    const response = await api.get('/listings', {
      params: {
        sort: sort.value,
        page: append ? currentPage.value + 1 : 1,
        per_page: 12,
      }
    })

    if (append) {
      listings.value = [...listings.value, ...response.data.data]
    } else {
      listings.value = response.data.data || []
    }

    total.value = response.data.meta?.total || 0
    currentPage.value = response.data.meta?.current_page || 1
    lastPage.value = response.data.meta?.last_page || 1
  } catch (error) {
    console.error('Failed to fetch listings:', error)
  } finally {
    loadingListings.value = false
    loadingMore.value = false
  }
}

const loadMore = () => {
  if (!loadingMore.value && hasMore.value) {
    fetchListings(true)
  }
}

onMounted(() => {
  fetchCategories()
  fetchListings()
})
</script>

<style scoped>
.container-app {
  max-width: 1200px;
  margin: 0 auto;
  padding-left: 16px;
  padding-right: 16px;
}

/* Category Card */
.category-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 12px 8px;
  background: white;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  text-decoration: none;
  transition: all 0.2s ease;
}

.category-card:hover {
  border-color: #6366f1;
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.15);
}

.category-card:active {
  transform: scale(0.98);
}

.category-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #eef2ff;
  border-radius: 10px;
  margin-bottom: 6px;
}

.category-name {
  font-size: 12px;
  font-weight: 600;
  color: #1f2937;
  text-align: center;
  line-height: 1.2;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.category-skeleton {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 12px 8px;
  background: white;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

/* Sort Select */
.sort-select {
  appearance: none;
  padding: 6px 28px 6px 12px;
  background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E") no-repeat right 8px center;
  background-size: 14px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 13px;
  color: #374151;
  cursor: pointer;
}

.sort-select:focus {
  outline: none;
  border-color: #6366f1;
}

/* Load More Button */
.load-more-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 12px 32px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  font-weight: 600;
  font-size: 14px;
  border-radius: 10px;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
}

.load-more-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
}

.load-more-btn:active:not(:disabled) {
  transform: scale(0.98);
}

.load-more-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Skeleton */
.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s infinite;
  border-radius: 4px;
}

@keyframes skeleton-loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Card */
.card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}
</style>
