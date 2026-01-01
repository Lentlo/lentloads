<template>
  <router-link
    :to="`/listing/${listing.slug}`"
    class="group block bg-white rounded-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow duration-200"
  >
    <!-- Image -->
    <div class="relative aspect-square bg-gray-100 overflow-hidden">
      <img
        :src="listing.primary_image_url"
        :alt="listing.title"
        class="w-full h-full object-cover"
        loading="lazy"
        @error="handleImageError"
      />

      <!-- Featured badge -->
      <div
        v-if="listing.is_featured"
        class="absolute top-0 left-0 bg-amber-400 text-amber-900 text-xs font-semibold px-2 py-1"
      >
        FEATURED
      </div>

      <!-- Favorite button -->
      <button
        v-if="showFavorite"
        @click.prevent="toggleFavorite"
        class="absolute top-2 right-2 w-8 h-8 bg-white rounded-full shadow flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
      >
        <HeartIcon
          class="w-5 h-5"
          :class="isFavorited ? 'text-red-500 fill-current' : 'text-gray-400'"
        />
      </button>
    </div>

    <!-- Content -->
    <div class="p-3">
      <!-- Price -->
      <p class="text-lg font-bold text-gray-900">
        {{ listing.formatted_price }}
      </p>

      <!-- Title -->
      <h3 class="text-sm text-gray-600 line-clamp-1 mt-1">
        {{ listing.title }}
      </h3>

      <!-- Location & Date -->
      <div class="flex items-center justify-between mt-2 text-xs text-gray-400">
        <span class="uppercase truncate max-w-[60%]">{{ listing.location }}</span>
        <span>{{ formatDate(listing.published_at || listing.created_at) }}</span>
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
