<template>
  <router-link
    :to="`/listing/${listing.slug}`"
    class="card-hover group"
  >
    <!-- Image -->
    <div class="relative aspect-[4/3] bg-gray-100">
      <img
        :src="listing.primary_image_url"
        :alt="listing.title"
        class="w-full h-full object-cover"
        loading="lazy"
      />

      <!-- Featured badge -->
      <div
        v-if="listing.is_featured"
        class="absolute top-2 left-2 badge bg-yellow-400 text-yellow-900"
      >
        Featured
      </div>

      <!-- Urgent badge -->
      <div
        v-if="listing.is_urgent"
        class="absolute top-2 right-2 badge bg-red-500 text-white"
      >
        Urgent
      </div>

      <!-- Favorite button -->
      <button
        v-if="showFavorite"
        @click.prevent="toggleFavorite"
        class="absolute bottom-2 right-2 w-8 h-8 bg-white rounded-full shadow flex items-center justify-center opacity-0 group-hover:opacity-100 transition"
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
      <p class="text-lg font-bold text-gray-900">
        {{ listing.formatted_price }}
      </p>

      <!-- Title -->
      <h3 class="text-gray-700 font-medium line-clamp-2 mt-1">
        {{ listing.title }}
      </h3>

      <!-- Location & Date -->
      <div class="flex items-center justify-between mt-2 text-sm text-gray-500">
        <span class="flex items-center">
          <MapPinIcon class="w-4 h-4 mr-1" />
          {{ listing.location }}
        </span>
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
</script>
