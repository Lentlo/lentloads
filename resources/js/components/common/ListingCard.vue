<template>
  <router-link
    :to="`/listing/${listing.slug}`"
    class="group block bg-white rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100"
  >
    <!-- Image -->
    <div class="relative aspect-[4/3] bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden">
      <img
        :src="listing.primary_image_url"
        :alt="listing.title"
        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
        loading="lazy"
        @error="handleImageError"
      />

      <!-- Gradient overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

      <!-- Featured badge -->
      <div
        v-if="listing.is_featured"
        class="absolute top-3 left-3 px-3 py-1 bg-gradient-to-r from-yellow-400 to-orange-400 text-white text-xs font-bold rounded-full shadow-lg"
      >
        ⭐ Featured
      </div>

      <!-- Urgent badge -->
      <div
        v-if="listing.is_urgent"
        class="absolute top-3 right-3 px-3 py-1 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold rounded-full shadow-lg animate-pulse"
      >
        🔥 Urgent
      </div>

      <!-- Favorite button -->
      <button
        v-if="showFavorite"
        @click.prevent="toggleFavorite"
        class="absolute bottom-3 right-3 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full shadow-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110"
      >
        <HeartIcon
          class="w-5 h-5"
          :class="isFavorited ? 'text-red-500 fill-current' : 'text-gray-600'"
        />
      </button>
    </div>

    <!-- Content -->
    <div class="p-4">
      <!-- Price -->
      <p class="text-xl font-bold bg-gradient-to-r from-primary-600 to-primary-500 bg-clip-text text-transparent">
        {{ listing.formatted_price }}
      </p>

      <!-- Title -->
      <h3 class="text-gray-800 font-semibold line-clamp-2 mt-2 group-hover:text-primary-600 transition-colors">
        {{ listing.title }}
      </h3>

      <!-- Location & Date -->
      <div class="flex items-center justify-between mt-3 text-sm text-gray-500">
        <span class="flex items-center bg-gray-50 px-2 py-1 rounded-full">
          <MapPinIcon class="w-4 h-4 mr-1 text-primary-500" />
          {{ listing.location }}
        </span>
        <span class="text-xs">{{ formatDate(listing.published_at || listing.created_at) }}</span>
      </div>
    </div>
  </router-link>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useListingsStore } from '@/stores/listings'
import { HeartIcon, MapPinIcon } from '@heroicons/vue/24/outline'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'

dayjs.extend(relativeTime)

const props = defineProps({
  listing: {
    type: Object,
    required: true
  },
  showFavorite: {
    type: Boolean,
    default: true
  }
})

const authStore = useAuthStore()
const listingsStore = useListingsStore()

const isFavorited = ref(props.listing.is_favorited || false)

const toggleFavorite = async () => {
  if (!authStore.isAuthenticated) {
    // Redirect to login
    return
  }

  const result = await listingsStore.toggleFavorite(props.listing.id)
  isFavorited.value = result.is_favorited
}

const formatDate = (date) => {
  return dayjs(date).fromNow()
}

const handleImageError = (e) => {
  e.target.src = 'https://placehold.co/400x300/E5E7EB/9CA3AF?text=No+Image'
}
</script>
