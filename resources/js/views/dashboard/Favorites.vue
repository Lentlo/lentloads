<template>
  <div class="favorites-page min-h-screen bg-gray-50">
    <div class="container-app py-6">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">My Favorites</h1>
          <p class="text-gray-500">{{ total }} saved items</p>
        </div>

        <button
          v-if="favorites.length"
          @click="clearAll"
          class="text-sm text-red-600 hover:underline"
        >
          Clear all
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div v-for="i in 8" :key="i" class="card p-4">
          <div class="skeleton h-40 mb-3"></div>
          <div class="skeleton h-4 w-20 mb-2"></div>
          <div class="skeleton h-4 w-full"></div>
        </div>
      </div>

      <!-- Favorites Grid -->
      <div v-else-if="favorites.length" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div
          v-for="listing in favorites"
          :key="listing.id"
          class="card-hover group relative"
        >
          <!-- Remove button -->
          <button
            @click="removeFavorite(listing.id)"
            class="absolute top-2 right-2 z-10 w-8 h-8 bg-white rounded-full shadow flex items-center justify-center opacity-0 group-hover:opacity-100 transition"
          >
            <XMarkIcon class="w-5 h-5 text-gray-600" />
          </button>

          <router-link :to="`/listing/${listing.slug}`">
            <!-- Image -->
            <div class="aspect-[4/3] bg-gray-100">
              <img
                :src="listing.primary_image_url"
                :alt="listing.title"
                class="w-full h-full object-cover"
              />

              <!-- Sold badge -->
              <div
                v-if="listing.status === 'sold'"
                class="absolute inset-0 bg-black/50 flex items-center justify-center"
              >
                <span class="bg-white text-gray-900 px-4 py-2 rounded-lg font-semibold">
                  SOLD
                </span>
              </div>
            </div>

            <!-- Content -->
            <div class="p-4">
              <p class="text-lg font-bold text-gray-900">{{ listing.formatted_price }}</p>
              <h3 class="text-gray-700 font-medium line-clamp-2 mt-1">{{ listing.title }}</h3>
              <div class="flex items-center mt-2 text-sm text-gray-500">
                <MapPinIcon class="w-4 h-4 mr-1" />
                {{ listing.location }}
              </div>
              <p class="text-xs text-gray-400 mt-1">
                Saved {{ formatDate(listing.favorited_at) }}
              </p>
            </div>
          </router-link>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="card p-12 text-center">
        <HeartIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No favorites yet</h3>
        <p class="text-gray-500 mb-4">Save items you like by clicking the heart icon</p>
        <router-link to="/search" class="btn-primary">
          Browse Listings
        </router-link>
      </div>

      <!-- Load More -->
      <div v-if="hasMore" class="mt-6 text-center">
        <button @click="loadMore" :disabled="loadingMore" class="btn-secondary">
          {{ loadingMore ? 'Loading...' : 'Load More' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useListingsStore } from '@/stores/listings'
import { toast } from 'vue3-toastify'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import { HeartIcon, MapPinIcon, XMarkIcon } from '@heroicons/vue/24/outline'

dayjs.extend(relativeTime)

const listingsStore = useListingsStore()

const loading = ref(true)
const loadingMore = ref(false)
const favorites = ref([])
const total = ref(0)
const currentPage = ref(1)
const lastPage = ref(1)

const hasMore = computed(() => currentPage.value < lastPage.value)

const formatDate = (date) => dayjs(date).fromNow()

const fetchFavorites = async (append = false) => {
  if (append) {
    loadingMore.value = true
  } else {
    loading.value = true
  }

  try {
    const response = await listingsStore.fetchFavorites()

    if (append) {
      favorites.value = [...favorites.value, ...response.data]
    } else {
      favorites.value = response.data
    }

    total.value = response.meta.total
    currentPage.value = response.meta.current_page
    lastPage.value = response.meta.last_page
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const removeFavorite = async (listingId) => {
  try {
    await listingsStore.toggleFavorite(listingId)
    favorites.value = favorites.value.filter(f => f.id !== listingId)
    total.value--
    toast.success('Removed from favorites')
  } catch (error) {
    toast.error('Failed to remove from favorites')
  }
}

const clearAll = async () => {
  if (!confirm('Are you sure you want to clear all favorites?')) return

  try {
    await api.delete('/favorites/clear')
    favorites.value = []
    total.value = 0
    toast.success('All favorites cleared')
  } catch (error) {
    toast.error('Failed to clear favorites')
  }
}

const loadMore = () => {
  fetchFavorites(true)
}

onMounted(() => {
  fetchFavorites()
})
</script>
