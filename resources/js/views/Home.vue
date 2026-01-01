<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Hero Section - OLX Style -->
    <section class="bg-primary-950 py-8">
      <div class="container-app">
        <!-- Search Bar -->
        <div class="flex gap-2 max-w-4xl mx-auto">
          <button
            @click="showLocationModal = true"
            class="flex items-center px-4 py-3 bg-white rounded-md border-2 border-gray-200 text-gray-700 hover:border-primary-500 transition min-w-[140px]"
          >
            <MapPinIcon class="w-5 h-5 mr-2 text-primary-600" />
            <span class="truncate">{{ currentLocation || 'All India' }}</span>
            <ChevronDownIcon class="w-4 h-4 ml-auto" />
          </button>
          <div class="flex-1 flex bg-white rounded-md border-2 border-gray-200 overflow-hidden focus-within:border-primary-500 transition">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Find Cars, Mobile Phones and more..."
              class="flex-1 px-4 py-3 focus:outline-none text-gray-900"
              @keyup.enter="handleSearch"
            />
            <button
              @click="handleSearch"
              class="px-6 bg-primary-950 hover:bg-primary-900 transition"
            >
              <MagnifyingGlassIcon class="w-6 h-6 text-white" />
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section class="py-6 bg-white border-b">
      <div class="container-app">
        <div class="flex items-center justify-between overflow-x-auto hide-scrollbar gap-6 py-2">
          <router-link
            v-for="category in categories"
            :key="category.id"
            :to="`/category/${category.slug}`"
            class="flex flex-col items-center min-w-[80px] group"
          >
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-2 group-hover:bg-primary-50 transition border border-gray-100">
              <component :is="getCategoryIcon(category.slug)" class="w-7 h-7 text-primary-950" />
            </div>
            <span class="text-xs text-gray-700 font-medium text-center">{{ category.name }}</span>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Fresh Recommendations -->
    <section class="py-8">
      <div class="container-app">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Fresh recommendations</h2>

        <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
          <div v-for="i in 12" :key="i" class="bg-white rounded-md overflow-hidden border border-gray-200">
            <div class="skeleton aspect-square"></div>
            <div class="p-3">
              <div class="skeleton h-5 w-20 mb-2"></div>
              <div class="skeleton h-4 w-full mb-2"></div>
              <div class="skeleton h-3 w-16"></div>
            </div>
          </div>
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
          <ListingCard
            v-for="listing in [...featuredListings, ...recentListings]"
            :key="listing.id"
            :listing="listing"
          />
        </div>

        <div class="text-center mt-8">
          <router-link
            to="/search"
            class="inline-flex items-center px-6 py-3 border-2 border-primary-950 text-primary-950 font-semibold rounded-md hover:bg-primary-50 transition"
          >
            Load more
          </router-link>
        </div>
      </div>
    </section>

    <!-- Sell Banner -->
    <section class="py-12 bg-gradient-to-r from-primary-100 to-primary-50">
      <div class="container-app">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
          <div>
            <h2 class="text-2xl font-bold text-primary-950 mb-2">Want to see your stuff here?</h2>
            <p class="text-gray-600">Make some extra cash by selling things in your community. Go for it, it's quick and easy.</p>
          </div>
          <router-link
            to="/sell"
            class="px-8 py-3 bg-primary-950 text-white font-semibold rounded-full hover:bg-primary-900 transition whitespace-nowrap"
          >
            + Sell Now
          </router-link>
        </div>
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
import {
  MapPinIcon,
  ChevronDownIcon,
  MagnifyingGlassIcon,
  DevicePhoneMobileIcon,
  TruckIcon,
  HomeModernIcon,
  BriefcaseIcon,
  ComputerDesktopIcon,
  ShoppingBagIcon,
  WrenchScrewdriverIcon,
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
    'furniture': HomeModernIcon,
    'services': WrenchScrewdriverIcon,
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
