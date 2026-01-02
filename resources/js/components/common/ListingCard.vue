<template>
  <router-link
    :to="`/listing/${listing.slug}`"
    class="listing-card group"
  >
    <!-- Image Container -->
    <div class="card-image">
      <img
        :src="listing.primary_image_url"
        :alt="listing.title"
        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
        loading="lazy"
        @error="handleImageError"
      />

      <!-- Featured Badge -->
      <div v-if="listing.is_featured" class="badge-featured">
        <SparklesIcon class="w-3 h-3" />
        <span>Featured</span>
      </div>

      <!-- Urgent Badge -->
      <div v-if="listing.is_urgent" class="badge-urgent">
        <BoltIcon class="w-3 h-3" />
        <span>Urgent</span>
      </div>

      <!-- Favorite Button -->
      <button
        v-if="showFavorite"
        @click.prevent="toggleFavorite"
        class="btn-favorite"
        :class="{ 'is-favorited': isFavorited }"
      >
        <HeartIcon class="w-5 h-5" :class="isFavorited ? 'fill-current' : ''" />
      </button>
    </div>

    <!-- Content -->
    <div class="card-content">
      <!-- Price Row -->
      <div class="price-row">
        <span class="price">{{ displayPrice }}</span>
        <span v-if="formattedCondition" class="condition-badge">{{ formattedCondition }}</span>
      </div>

      <!-- Title -->
      <h3 class="card-title">{{ listing.title }}</h3>

      <!-- Meta Row -->
      <div class="meta-row">
        <span class="location">
          <MapPinIcon class="w-3.5 h-3.5" />
          <span>{{ shortLocation }}</span>
        </span>
        <span class="time">{{ formatDate(listing.published_at || listing.created_at) }}</span>
      </div>
    </div>
  </router-link>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useListingsStore } from '@/stores/listings'
import {
  HeartIcon,
  MapPinIcon,
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

// Clean price display - remove "(Negotiable)" text
const displayPrice = computed(() => {
  const formatted = props.listing.formatted_price || `₹${props.listing.price?.toLocaleString()}`
  // Remove "(Negotiable)" if present
  return formatted.replace(/\s*\(Negotiable\)/gi, '').trim()
})

// Format condition nicely
const formattedCondition = computed(() => {
  const condition = props.listing.condition
  if (!condition) return ''

  const conditionMap = {
    'new': 'New',
    'like_new': 'Like New',
    'good': 'Good',
    'fair': 'Fair',
    'poor': 'Poor'
  }
  return conditionMap[condition.toLowerCase()] || condition.replace(/_/g, ' ')
})

// Shorter location for cards
const shortLocation = computed(() => {
  const loc = props.listing.location || props.listing.city || ''
  // Get just the city name (first part before comma)
  const city = loc.split(',')[0].trim()
  return city.length > 15 ? city.substring(0, 15) + '...' : city
})

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

<style scoped>
.listing-card {
  display: block;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  border: 1px solid #f1f5f9;
}

.listing-card:hover {
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.card-image {
  position: relative;
  aspect-ratio: 1 / 1;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  overflow: hidden;
}

.badge-featured {
  position: absolute;
  top: 8px;
  left: 8px;
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
  color: white;
  font-size: 10px;
  font-weight: 600;
  border-radius: 6px;
  box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4);
}

.badge-urgent {
  position: absolute;
  top: 8px;
  right: 8px;
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  font-size: 10px;
  font-weight: 600;
  border-radius: 6px;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
}

.btn-favorite {
  position: absolute;
  bottom: 8px;
  right: 8px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  border-radius: 50%;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  color: #94a3b8;
  opacity: 0;
  transform: scale(0.9);
  transition: all 0.2s ease;
}

.listing-card:hover .btn-favorite {
  opacity: 1;
  transform: scale(1);
}

.btn-favorite:hover {
  transform: scale(1.1);
}

.btn-favorite.is-favorited {
  color: #ef4444;
  opacity: 1;
}

.card-content {
  padding: 12px;
}

.price-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 6px;
}

.price {
  font-size: 16px;
  font-weight: 700;
  color: #7c3aed;
}

.condition-badge {
  font-size: 10px;
  font-weight: 500;
  padding: 2px 6px;
  background: #f1f5f9;
  color: #64748b;
  border-radius: 4px;
  white-space: nowrap;
}

.card-title {
  font-size: 13px;
  font-weight: 500;
  color: #334155;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  margin-bottom: 8px;
  min-height: 36px;
}

.listing-card:hover .card-title {
  color: #7c3aed;
}

.meta-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  font-size: 11px;
  color: #94a3b8;
}

.location {
  display: flex;
  align-items: center;
  gap: 2px;
  min-width: 0;
}

.location span {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.time {
  white-space: nowrap;
  flex-shrink: 0;
}

/* Mobile optimizations */
@media (max-width: 640px) {
  .card-content {
    padding: 10px;
  }

  .price {
    font-size: 15px;
  }

  .card-title {
    font-size: 12px;
    min-height: 34px;
  }

  .meta-row {
    font-size: 10px;
  }

  .condition-badge {
    font-size: 9px;
    padding: 2px 5px;
  }
}
</style>
