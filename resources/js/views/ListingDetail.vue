<template>
  <div class="min-h-screen bg-gray-50 py-6">
    <div class="container-app">
      <!-- Loading -->
      <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
          <div class="card p-4">
            <div class="skeleton h-96 mb-4"></div>
            <div class="skeleton h-8 w-1/3 mb-2"></div>
            <div class="skeleton h-6 w-full mb-4"></div>
            <div class="skeleton h-24 w-full"></div>
          </div>
        </div>
        <div class="card p-4">
          <div class="skeleton h-16 w-full mb-4"></div>
          <div class="skeleton h-12 w-full mb-2"></div>
          <div class="skeleton h-12 w-full"></div>
        </div>
      </div>

      <!-- Content -->
      <div v-else-if="listing" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Image Gallery -->
          <div class="card overflow-hidden">
            <div class="relative aspect-[4/3] bg-gray-100">
              <img
                :src="currentImage"
                :alt="listing.title"
                class="w-full h-full object-contain"
              />

              <!-- Navigation arrows -->
              <button
                v-if="listing.images?.length > 1"
                @click="prevImage"
                class="absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/80 rounded-full flex items-center justify-center hover:bg-white"
              >
                <ChevronLeftIcon class="w-6 h-6" />
              </button>
              <button
                v-if="listing.images?.length > 1"
                @click="nextImage"
                class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/80 rounded-full flex items-center justify-center hover:bg-white"
              >
                <ChevronRightIcon class="w-6 h-6" />
              </button>
            </div>

            <!-- Thumbnails -->
            <div v-if="listing.images?.length > 1" class="flex gap-2 p-4 overflow-x-auto">
              <button
                v-for="(image, index) in listing.images"
                :key="image.id"
                @click="currentImageIndex = index"
                class="w-20 h-20 flex-shrink-0 rounded-lg overflow-hidden border-2"
                :class="currentImageIndex === index ? 'border-primary-500' : 'border-transparent'"
              >
                <img :src="image.thumbnail_url" class="w-full h-full object-cover" />
              </button>
            </div>
          </div>

          <!-- Details -->
          <div class="card p-6">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-2xl font-bold text-primary-600">{{ listing.formatted_price }}</p>
                <h1 class="text-xl font-semibold text-gray-900 mt-1">{{ listing.title }}</h1>
              </div>

              <div class="flex items-center gap-2">
                <button
                  @click="toggleFavorite"
                  class="p-2 rounded-full hover:bg-gray-100"
                >
                  <HeartIcon
                    class="w-6 h-6"
                    :class="isFavorited ? 'text-red-500 fill-current' : 'text-gray-400'"
                  />
                </button>
                <button
                  @click="shareList"
                  class="p-2 rounded-full hover:bg-gray-100"
                >
                  <ShareIcon class="w-6 h-6 text-gray-400" />
                </button>
              </div>
            </div>

            <!-- Meta info -->
            <div class="flex flex-wrap gap-4 text-sm text-gray-500 mb-6">
              <span class="flex items-center">
                <MapPinIcon class="w-4 h-4 mr-1" />
                {{ listing.location }}
              </span>
              <span class="flex items-center">
                <ClockIcon class="w-4 h-4 mr-1" />
                {{ formatDate(listing.published_at) }}
              </span>
              <span class="flex items-center">
                <EyeIcon class="w-4 h-4 mr-1" />
                {{ listing.views_count }} views
              </span>
            </div>

            <!-- Description -->
            <div class="border-t pt-6">
              <h3 class="font-semibold text-gray-900 mb-3">Description</h3>
              <p class="text-gray-600 whitespace-pre-line">{{ listing.description }}</p>
            </div>

            <!-- Attributes -->
            <div v-if="listing.attributes" class="border-t pt-6 mt-6">
              <h3 class="font-semibold text-gray-900 mb-3">Details</h3>
              <div class="grid grid-cols-2 gap-4">
                <div v-if="listing.condition">
                  <span class="text-gray-500">Condition:</span>
                  <span class="ml-2 font-medium capitalize">{{ listing.condition.replace('_', ' ') }}</span>
                </div>
                <div v-if="listing.brand">
                  <span class="text-gray-500">Brand:</span>
                  <span class="ml-2 font-medium">{{ listing.brand }}</span>
                </div>
                <div v-if="listing.model">
                  <span class="text-gray-500">Model:</span>
                  <span class="ml-2 font-medium">{{ listing.model }}</span>
                </div>
                <div v-if="listing.year">
                  <span class="text-gray-500">Year:</span>
                  <span class="ml-2 font-medium">{{ listing.year }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Seller Card -->
          <div class="card p-6">
            <div class="flex items-center gap-4 mb-4">
              <img
                :src="listing.user.avatar_url"
                :alt="listing.user.name"
                class="w-14 h-14 rounded-full object-cover"
              />
              <div>
                <router-link
                  :to="`/user/${listing.user.id}`"
                  class="font-semibold text-gray-900 hover:text-primary-600"
                >
                  {{ listing.user.name }}
                </router-link>
                <div class="flex items-center text-sm text-gray-500">
                  <StarIcon class="w-4 h-4 text-yellow-400 mr-1" />
                  {{ listing.user.rating || '0.0' }} ({{ listing.user.total_reviews }} reviews)
                </div>
                <p class="text-sm text-gray-500">{{ listing.user.city }}</p>
              </div>
            </div>

            <div v-if="listing.user.is_verified_seller" class="flex items-center text-green-600 text-sm mb-4">
              <CheckBadgeIcon class="w-5 h-5 mr-1" />
              Verified Seller
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
              <button
                v-if="!isOwner"
                @click="startChat"
                class="btn-primary w-full"
              >
                <ChatBubbleLeftRightIcon class="w-5 h-5 mr-2" />
                Chat with Seller
              </button>

              <a
                v-if="listing.user.phone && !isOwner"
                :href="`tel:${listing.user.phone}`"
                class="btn-outline w-full"
              >
                <PhoneIcon class="w-5 h-5 mr-2" />
                Call Seller
              </a>

              <router-link
                v-if="isOwner"
                :to="`/listing/${listing.id}/edit`"
                class="btn-primary w-full"
              >
                Edit Listing
              </router-link>
            </div>
          </div>

          <!-- Safety Tips -->
          <div class="card p-6 bg-yellow-50 border-yellow-200">
            <h3 class="font-semibold text-yellow-800 mb-2">Safety Tips</h3>
            <ul class="text-sm text-yellow-700 space-y-1">
              <li>Meet in a safe public place</li>
              <li>Check the item before you pay</li>
              <li>Pay only after collecting the item</li>
              <li>Never share financial information</li>
            </ul>
          </div>

          <!-- Report -->
          <button
            @click="showReportModal = true"
            class="text-sm text-gray-500 hover:text-red-600 flex items-center"
          >
            <FlagIcon class="w-4 h-4 mr-1" />
            Report this ad
          </button>
        </div>
      </div>

      <!-- Similar Listings -->
      <div v-if="similar?.length" class="mt-12">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Similar Ads</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <ListingCard
            v-for="item in similar"
            :key="item.id"
            :listing="item"
          />
        </div>
      </div>
    </div>

    <!-- Chat Modal -->
    <ChatModal
      v-if="showChatModal"
      :listing="listing"
      @close="showChatModal = false"
    />

    <!-- Report Modal -->
    <ReportModal
      v-if="showReportModal"
      :type="'listing'"
      :id="listing?.id"
      @close="showReportModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useListingsStore } from '@/stores/listings'
import ListingCard from '@/components/common/ListingCard.vue'
import ChatModal from '@/components/modals/ChatModal.vue'
import ReportModal from '@/components/modals/ReportModal.vue'
import dayjs from 'dayjs'
import {
  ChevronLeftIcon,
  ChevronRightIcon,
  HeartIcon,
  ShareIcon,
  MapPinIcon,
  ClockIcon,
  EyeIcon,
  StarIcon,
  CheckBadgeIcon,
  ChatBubbleLeftRightIcon,
  PhoneIcon,
  FlagIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const listingsStore = useListingsStore()

const loading = ref(true)
const listing = ref(null)
const similar = ref([])
const currentImageIndex = ref(0)
const isFavorited = ref(false)
const showChatModal = ref(false)
const showReportModal = ref(false)

const currentImage = computed(() => {
  if (!listing.value?.images?.length) return listing.value?.primary_image_url
  return listing.value.images[currentImageIndex.value]?.url
})

const isOwner = computed(() => {
  return authStore.user?.id === listing.value?.user?.id
})

const formatDate = (date) => dayjs(date).format('MMM D, YYYY')

const prevImage = () => {
  if (currentImageIndex.value > 0) {
    currentImageIndex.value--
  } else {
    currentImageIndex.value = listing.value.images.length - 1
  }
}

const nextImage = () => {
  if (currentImageIndex.value < listing.value.images.length - 1) {
    currentImageIndex.value++
  } else {
    currentImageIndex.value = 0
  }
}

const toggleFavorite = async () => {
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }
  const result = await listingsStore.toggleFavorite(listing.value.id)
  isFavorited.value = result.is_favorited
}

const startChat = () => {
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }
  showChatModal.value = true
}

const shareList = () => {
  if (navigator.share) {
    navigator.share({
      title: listing.value.title,
      text: `Check out this listing: ${listing.value.title}`,
      url: window.location.href,
    })
  }
}

const fetchListing = async () => {
  try {
    const data = await listingsStore.fetchListing(route.params.slug)
    listing.value = data.listing
    similar.value = data.similar
    isFavorited.value = data.listing.is_favorited
  } catch (error) {
    if (error.response?.status === 404) {
      router.push('/404')
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchListing()
})

// Watch for route changes to handle clicking on similar listings
watch(() => route.params.slug, (newSlug, oldSlug) => {
  if (newSlug !== oldSlug) {
    loading.value = true
    currentImageIndex.value = 0
    fetchListing()
  }
})
</script>
