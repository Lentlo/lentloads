<template>
  <div class="min-h-screen bg-gray-50 py-6">
    <div class="container-app">
      <!-- Loading -->
      <div v-if="loading" class="max-w-4xl mx-auto">
        <div class="card p-6 mb-6">
          <div class="flex items-start gap-4">
            <div class="skeleton w-24 h-24 rounded-full"></div>
            <div class="flex-1">
              <div class="skeleton h-6 w-1/3 mb-2"></div>
              <div class="skeleton h-4 w-1/4 mb-4"></div>
              <div class="skeleton h-4 w-2/3"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- User Not Found -->
      <div v-else-if="!user" class="max-w-md mx-auto text-center py-12">
        <UserIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h2 class="text-xl font-bold text-gray-900 mb-2">User not found</h2>
        <p class="text-gray-500 mb-4">This user doesn't exist or has been removed.</p>
        <router-link to="/" class="btn-primary">Go Home</router-link>
      </div>

      <!-- Profile Content -->
      <div v-else class="max-w-4xl mx-auto">
        <!-- Profile Header -->
        <div class="card p-6 mb-6">
          <div class="flex flex-col sm:flex-row items-start gap-4">
            <img
              :src="user.avatar_url"
              :alt="user.name"
              class="w-24 h-24 rounded-full object-cover"
            />
            <div class="flex-1">
              <div class="flex items-start justify-between">
                <div>
                  <h1 class="text-2xl font-bold text-gray-900">{{ user.name }}</h1>
                  <div class="flex items-center gap-2 mt-1">
                    <div class="flex items-center text-yellow-500">
                      <StarIcon class="w-5 h-5 fill-current" />
                      <span class="ml-1 font-medium">{{ user.rating || '0.0' }}</span>
                    </div>
                    <span class="text-gray-400">•</span>
                    <span class="text-gray-500">{{ user.reviews_count || 0 }} reviews</span>
                  </div>
                </div>
                <div class="flex gap-2">
                  <button
                    v-if="!isOwnProfile"
                    @click="showReportModal = true"
                    class="p-2 text-gray-500 hover:bg-gray-100 rounded"
                    title="Report user"
                  >
                    <FlagIcon class="w-5 h-5" />
                  </button>
                </div>
              </div>

              <p v-if="user.bio" class="text-gray-600 mt-3">{{ user.bio }}</p>

              <div class="flex flex-wrap items-center gap-4 mt-4 text-sm text-gray-500">
                <div v-if="user.city" class="flex items-center">
                  <MapPinIcon class="w-4 h-4 mr-1" />
                  {{ user.city }}{{ user.state ? `, ${user.state}` : '' }}
                </div>
                <div class="flex items-center">
                  <CalendarIcon class="w-4 h-4 mr-1" />
                  Member since {{ formatJoinDate(user.created_at) }}
                </div>
                <div v-if="user.is_verified" class="flex items-center text-green-600">
                  <CheckBadgeIcon class="w-4 h-4 mr-1" />
                  Verified
                </div>
              </div>

              <!-- Stats -->
              <div class="flex gap-6 mt-4 pt-4 border-t">
                <div class="text-center">
                  <p class="text-2xl font-bold text-gray-900">{{ user.listings_count || 0 }}</p>
                  <p class="text-sm text-gray-500">Listings</p>
                </div>
                <div class="text-center">
                  <p class="text-2xl font-bold text-gray-900">{{ user.sold_count || 0 }}</p>
                  <p class="text-sm text-gray-500">Sold</p>
                </div>
                <div class="text-center">
                  <p class="text-2xl font-bold text-gray-900">{{ responseRate }}%</p>
                  <p class="text-sm text-gray-500">Response Rate</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="flex gap-4 mb-6">
          <button
            @click="activeTab = 'listings'"
            class="px-4 py-2 rounded-lg font-medium"
            :class="activeTab === 'listings' ? 'bg-primary-600 text-white' : 'bg-white text-gray-600'"
          >
            Listings
          </button>
          <button
            @click="activeTab = 'reviews'"
            class="px-4 py-2 rounded-lg font-medium"
            :class="activeTab === 'reviews' ? 'bg-primary-600 text-white' : 'bg-white text-gray-600'"
          >
            Reviews
          </button>
        </div>

        <!-- Listings Tab -->
        <div v-if="activeTab === 'listings'">
          <div v-if="loadingListings" class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div v-for="i in 6" :key="i" class="card p-4">
              <div class="skeleton aspect-square rounded-lg mb-2"></div>
              <div class="skeleton h-4 w-3/4 mb-1"></div>
              <div class="skeleton h-4 w-1/2"></div>
            </div>
          </div>

          <div v-else-if="listings.length" class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <ListingCard
              v-for="listing in listings"
              :key="listing.id"
              :listing="listing"
            />
          </div>

          <div v-else class="card p-12 text-center">
            <ShoppingBagIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <p class="text-gray-500">No listings yet</p>
          </div>
        </div>

        <!-- Reviews Tab -->
        <div v-if="activeTab === 'reviews'">
          <div v-if="loadingReviews" class="space-y-4">
            <div v-for="i in 3" :key="i" class="card p-4">
              <div class="flex gap-4">
                <div class="skeleton w-10 h-10 rounded-full"></div>
                <div class="flex-1">
                  <div class="skeleton h-4 w-1/4 mb-2"></div>
                  <div class="skeleton h-3 w-full"></div>
                </div>
              </div>
            </div>
          </div>

          <div v-else-if="reviews.length" class="space-y-4">
            <div
              v-for="review in reviews"
              :key="review.id"
              class="card p-4"
            >
              <div class="flex items-start gap-4">
                <img
                  :src="review.reviewer?.avatar_url"
                  :alt="review.reviewer?.name"
                  class="w-10 h-10 rounded-full object-cover"
                />
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="font-semibold text-gray-900">{{ review.reviewer?.name }}</p>
                      <div class="flex items-center gap-1 mt-1">
                        <StarIcon
                          v-for="i in 5"
                          :key="i"
                          class="w-4 h-4"
                          :class="i <= review.rating ? 'text-yellow-400 fill-current' : 'text-gray-300'"
                        />
                      </div>
                    </div>
                    <span class="text-sm text-gray-500">{{ formatDate(review.created_at) }}</span>
                  </div>
                  <p v-if="review.comment" class="text-gray-600 mt-2">{{ review.comment }}</p>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="card p-12 text-center">
            <StarIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <p class="text-gray-500">No reviews yet</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Report Modal -->
    <ReportModal
      v-if="showReportModal"
      type="user"
      :item-id="user?.id"
      @close="showReportModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import dayjs from 'dayjs'
import ListingCard from '@/components/common/ListingCard.vue'
import ReportModal from '@/components/modals/ReportModal.vue'
import {
  UserIcon,
  StarIcon,
  MapPinIcon,
  CalendarIcon,
  CheckBadgeIcon,
  FlagIcon,
  ShoppingBagIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const authStore = useAuthStore()

const loading = ref(true)
const loadingListings = ref(true)
const loadingReviews = ref(true)
const user = ref(null)
const listings = ref([])
const reviews = ref([])
const activeTab = ref('listings')
const showReportModal = ref(false)

const isOwnProfile = computed(() => {
  return authStore.user?.id === user.value?.id
})

const responseRate = computed(() => {
  if (!user.value?.response_rate) return 0
  return Math.round(user.value.response_rate)
})

const formatJoinDate = (date) => dayjs(date).format('MMMM YYYY')
const formatDate = (date) => dayjs(date).format('MMM D, YYYY')

const fetchUser = async () => {
  loading.value = true
  try {
    const response = await api.get(`/users/${route.params.id}`)
    user.value = response.data.data
  } catch (error) {
    user.value = null
  } finally {
    loading.value = false
  }
}

const fetchListings = async () => {
  loadingListings.value = true
  try {
    const response = await api.get(`/users/${route.params.id}/listings`)
    listings.value = response.data.data
  } finally {
    loadingListings.value = false
  }
}

const fetchReviews = async () => {
  loadingReviews.value = true
  try {
    const response = await api.get(`/users/${route.params.id}/reviews`)
    reviews.value = response.data.data
  } finally {
    loadingReviews.value = false
  }
}

watch(activeTab, (tab) => {
  if (tab === 'reviews' && !reviews.value.length) {
    fetchReviews()
  }
})

watch(() => route.params.id, () => {
  if (route.params.id) {
    fetchUser()
    fetchListings()
    reviews.value = []
    activeTab.value = 'listings'
  }
})

onMounted(() => {
  fetchUser()
  fetchListings()
})
</script>
