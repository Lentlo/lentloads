<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-primary-600 to-primary-700 text-white py-12 md:py-20">
      <div class="container-app text-center">
        <h1 class="text-3xl md:text-5xl font-bold mb-4">
          Buy & Sell Anything Near You
        </h1>
        <p class="text-lg md:text-xl text-primary-100 mb-8 max-w-2xl mx-auto">
          Discover great deals on new and used items from sellers in your area
        </p>

        <!-- Location bar -->
        <div class="max-w-2xl mx-auto bg-white rounded-lg p-2 flex items-center">
          <button
            @click="showLocationModal = true"
            class="flex items-center px-4 py-2 text-gray-600 hover:text-gray-900 border-r border-gray-200"
          >
            <MapPinIcon class="w-5 h-5 mr-2" />
            <span>{{ currentLocation || 'All India' }}</span>
          </button>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="What are you looking for?"
            class="flex-1 px-4 py-2 focus:outline-none text-gray-900"
            @keyup.enter="handleSearch"
          />
          <button
            @click="handleSearch"
            class="btn-primary"
          >
            Search
          </button>
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section class="py-12 bg-white">
      <div class="container-app">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Browse Categories</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <router-link
            v-for="category in categories"
            :key="category.id"
            :to="`/category/${category.slug}`"
            class="card-hover p-4 text-center group"
          >
            <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:bg-primary-200 transition">
              <component :is="getCategoryIcon(category.slug)" class="w-6 h-6 text-primary-600" />
            </div>
            <h3 class="font-medium text-gray-900">{{ category.name }}</h3>
            <p class="text-sm text-gray-500">{{ category.active_listings_count || 0 }} ads</p>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Featured Listings -->
    <section class="py-12 bg-gray-50">
      <div class="container-app">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-gray-900">Featured Ads</h2>
          <router-link to="/search?featured=1" class="text-primary-600 font-medium hover:underline">
            View All
          </router-link>
        </div>

        <div v-if="loading" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <div v-for="i in 8" :key="i" class="card p-4">
            <div class="skeleton h-40 mb-3"></div>
            <div class="skeleton h-4 w-20 mb-2"></div>
            <div class="skeleton h-4 w-full mb-2"></div>
            <div class="skeleton h-3 w-24"></div>
          </div>
        </div>

        <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <ListingCard
            v-for="listing in featuredListings"
            :key="listing.id"
            :listing="listing"
          />
        </div>
      </div>
    </section>

    <!-- Recent Listings -->
    <section class="py-12 bg-white">
      <div class="container-app">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-gray-900">Fresh Recommendations</h2>
          <router-link to="/search" class="text-primary-600 font-medium hover:underline">
            View All
          </router-link>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <ListingCard
            v-for="listing in recentListings"
            :key="listing.id"
            :listing="listing"
          />
        </div>
      </div>
    </section>

    <!-- Popular Cities -->
    <section class="py-12 bg-gray-50">
      <div class="container-app">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Popular Cities</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <button
            v-for="city in popularCities"
            :key="city.id"
            @click="selectCity(city)"
            class="card-hover p-4 text-center"
          >
            <h3 class="font-medium text-gray-900">{{ city.name }}</h3>
            <p class="text-sm text-gray-500">{{ city.listings_count }} ads</p>
          </button>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-primary-600 text-white">
      <div class="container-app text-center">
        <h2 class="text-3xl font-bold mb-4">Have something to sell?</h2>
        <p class="text-lg text-primary-100 mb-8 max-w-xl mx-auto">
          Post your ad for free and reach thousands of potential buyers in your area
        </p>
        <router-link to="/sell" class="btn bg-white text-primary-600 hover:bg-gray-100 btn-lg">
          Post Free Ad
        </router-link>
      </div>
    </section>

    <!-- Location Modal -->
    <LocationModal v-if="showLocationModal" @close="showLocationModal = false" @select="onLocationSelect" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAppStore } from '@/stores/app'
import api from '@/services/api'
import ListingCard from '@/components/common/ListingCard.vue'
import LocationModal from '@/components/modals/LocationModal.vue'
import { MapPinIcon } from '@heroicons/vue/24/outline'
import {
  DevicePhoneMobileIcon,
  TruckIcon,
  HomeModernIcon,
  BriefcaseIcon,
  ComputerDesktopIcon,
  ShoppingBagIcon,
} from '@heroicons/vue/24/outline'

const router = useRouter()
const appStore = useAppStore()

const loading = ref(true)
const searchQuery = ref('')
const showLocationModal = ref(false)
const featuredListings = ref([])
const recentListings = ref([])

const categories = computed(() => appStore.categories)
const popularCities = computed(() => appStore.popularCities)
const currentLocation = computed(() => appStore.currentLocation?.city)

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: '/search', query: { q: searchQuery.value } })
  }
}

const selectCity = (city) => {
  appStore.setLocation(city)
  router.push({ path: '/search', query: { city: city.name } })
}

const onLocationSelect = (location) => {
  showLocationModal.value = false
  appStore.setLocation(location)
}

const getCategoryIcon = (slug) => {
  const icons = {
    'mobiles': DevicePhoneMobileIcon,
    'vehicles': TruckIcon,
    'property': HomeModernIcon,
    'jobs': BriefcaseIcon,
    'electronics': ComputerDesktopIcon,
    'fashion': ShoppingBagIcon,
  }
  return icons[slug] || ShoppingBagIcon
}

const fetchHomeData = async () => {
  try {
    const response = await api.get('/home')
    featuredListings.value = response.data.data.featured_listings
    recentListings.value = response.data.data.recent_listings
  } catch (error) {
    console.error('Failed to fetch home data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchHomeData()
})
</script>
