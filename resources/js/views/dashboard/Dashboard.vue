<template>
  <div class="min-h-screen bg-gray-50 py-6">
    <div class="container-app">
      <!-- Welcome -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Welcome, {{ user?.name }}!</h1>
        <p class="text-gray-500">Manage your listings and messages</p>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card p-4">
          <p class="text-3xl font-bold text-primary-600">{{ stats.active_listings }}</p>
          <p class="text-sm text-gray-500">Active Listings</p>
        </div>
        <div class="card p-4">
          <p class="text-3xl font-bold text-green-600">{{ stats.total_views }}</p>
          <p class="text-sm text-gray-500">Total Views</p>
        </div>
        <div class="card p-4">
          <p class="text-3xl font-bold text-blue-600">{{ stats.favorites }}</p>
          <p class="text-sm text-gray-500">Favorites</p>
        </div>
        <div class="card p-4">
          <p class="text-3xl font-bold text-purple-600">{{ stats.messages }}</p>
          <p class="text-sm text-gray-500">Messages</p>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <router-link to="/sell" class="card p-6 hover:shadow-md transition flex items-center gap-4">
          <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
            <PlusIcon class="w-6 h-6 text-primary-600" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">Post New Ad</h3>
            <p class="text-sm text-gray-500">Create a new listing</p>
          </div>
        </router-link>

        <router-link to="/my-listings" class="card p-6 hover:shadow-md transition flex items-center gap-4">
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
            <ClipboardDocumentListIcon class="w-6 h-6 text-green-600" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">My Listings</h3>
            <p class="text-sm text-gray-500">View all your ads</p>
          </div>
        </router-link>

        <router-link to="/messages" class="card p-6 hover:shadow-md transition flex items-center gap-4">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <ChatBubbleLeftRightIcon class="w-6 h-6 text-blue-600" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">Messages</h3>
            <p class="text-sm text-gray-500">Chat with buyers</p>
          </div>
        </router-link>

        <router-link to="/favorites" class="card p-6 hover:shadow-md transition flex items-center gap-4">
          <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
            <HeartIcon class="w-6 h-6 text-red-600" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">Favorites</h3>
            <p class="text-sm text-gray-500">Your saved items</p>
          </div>
        </router-link>

        <router-link to="/saved-searches" class="card p-6 hover:shadow-md transition flex items-center gap-4">
          <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
            <BellIcon class="w-6 h-6 text-yellow-600" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">Saved Searches</h3>
            <p class="text-sm text-gray-500">Get notified</p>
          </div>
        </router-link>

        <router-link to="/settings" class="card p-6 hover:shadow-md transition flex items-center gap-4">
          <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
            <Cog6ToothIcon class="w-6 h-6 text-gray-600" />
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">Settings</h3>
            <p class="text-sm text-gray-500">Account settings</p>
          </div>
        </router-link>
      </div>

      <!-- Recent Listings -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Your Recent Listings</h2>
          <router-link to="/my-listings" class="text-primary-600 text-sm hover:underline">
            View all
          </router-link>
        </div>

        <div v-if="recentListings.length" class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <ListingCard
            v-for="listing in recentListings"
            :key="listing.id"
            :listing="listing"
            :showFavorite="false"
          />
        </div>

        <div v-else class="text-center py-8">
          <p class="text-gray-500 mb-4">You haven't posted any ads yet</p>
          <router-link to="/sell" class="btn-primary">
            Post Your First Ad
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import ListingCard from '@/components/common/ListingCard.vue'
import {
  PlusIcon,
  ClipboardDocumentListIcon,
  ChatBubbleLeftRightIcon,
  HeartIcon,
  BellIcon,
  Cog6ToothIcon,
} from '@heroicons/vue/24/outline'

const authStore = useAuthStore()

const user = computed(() => authStore.user)
const stats = ref({
  active_listings: 0,
  total_views: 0,
  favorites: 0,
  messages: 0,
})
const recentListings = ref([])

const fetchDashboard = async () => {
  try {
    const response = await api.get('/auth/user')
    stats.value = response.data.data.stats
    recentListings.value = response.data.data.user.listings || []
  } catch (error) {
    console.error('Failed to fetch dashboard data')
  }
}

onMounted(() => {
  fetchDashboard()
})
</script>
