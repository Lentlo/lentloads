<template>
  <router-link
    :to="`/listing/${listing.slug}`"
    class="group block bg-white rounded-2xl overflow-hidden shadow-soft hover:shadow-soft-xl transition-all duration-500 transform hover:-translate-y-2 border border-slate-100"
  >
    <!-- Image Container -->
    <div class="relative aspect-[4/3] bg-gradient-to-br from-slate-100 to-slate-50 overflow-hidden">
      <img
        :src="listing.primary_image_url"
        :alt="listing.title"
        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
        loading="lazy"
        @error="handleImageError"
      />

      <!-- Gradient Overlay on Hover -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

      <!-- Featured Badge -->
      <div
        v-if="listing.is_featured"
        class="absolute top-3 left-3 px-3 py-1.5 bg-gradient-to-r from-amber-400 to-orange-500 text-white text-xs font-bold rounded-full shadow-lg animate-pulse-soft"
      >
        <span class="flex items-center">
          <SparklesIcon class="w-3.5 h-3.5 mr-1" />
          Featured
        </span>
      </div>

      <!-- Urgent Badge -->
      <div
        v-if="listing.is_urgent"
        class="absolute top-3 right-3 px-3 py-1.5 bg-gradient-accent text-white text-xs font-bold rounded-full shadow-lg"
      >
        <span class="flex items-center">
          <BoltIcon class="w-3.5 h-3.5 mr-1" />
          Urgent
        </span>
      </div>

      <!-- Favorite Button -->
      <button
        v-if="showFavorite"
        @click.prevent="toggleFavorite"
        class="absolute bottom-3 right-3 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full shadow-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300 hover:scale-110"
      >
        <HeartIcon
          class="w-5 h-5 transition-colors"
          :class="isFavorited ? 'text-accent-500 fill-current' : 'text-slate-400'"
        />
      </button>

      <!-- Quick View Button -->
      <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <span class="px-4 py-2 bg-white/90 backdrop-blur-sm text-slate-700 text-sm font-medium rounded-full shadow-lg transform scale-90 group-hover:scale-100 transition-transform duration-300">
          View Details
        </span>
      </div>
    </div>

    <!-- Content -->
    <div class="p-4">
      <!-- Price -->
      <div class="flex items-center justify-between mb-2">
        <p class="text-xl font-bold bg-gradient-to-r from-primary-600 to-primary-500 bg-clip-text text-transparent">
          {{ listing.formatted_price }}
        </p>
        <span v-if="listing.condition" class="text-xs px-2 py-1 bg-slate-100 text-slate-600 rounded-full capitalize">
          {{ listing.condition }}
        </span>
      </div>

      <!-- Title -->
      <h3 class="text-slate-700 font-medium line-clamp-2 mb-3 group-hover:text-primary-600 transition-colors">
        {{ listing.title }}
      </h3>

      <!-- Location & Date -->
      <div class="flex items-center justify-between text-sm text-slate-400">
        <span class="flex items-center">
          <MapPinIcon class="w-4 h-4 mr-1" />
          <span class="truncate max-w-[120px]">{{ listing.location }}</span>
        </span>
        <span class="flex items-center">
          <ClockIcon class="w-4 h-4 mr-1" />
          {{ formatDate(listing.published_at || listing.created_at) }}
        </span>
      </div>
    </div>
  </router-link>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useListingsStore } from '@/stores/listings'
import {
  HeartIcon,
  MapPinIcon,
  ClockIcon,
  SparklesIcon,
  BoltIcon
} from '@heroicons/vue/24/outline'
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
    return
  }
  const result = await listingsStore.toggleFavorite(props.listing.id)
  isFavorited.value = result.is_favorited
}

const formatDate = (date) => {
  return dayjs(date).fromNow()
}

const handleImageError = (e) => {
  e.target.src = 'https://images.unsplash.com/photo-1560393464-5c69a73c5770?w=400&h=300&fit=crop&auto=format'
}
</script>
