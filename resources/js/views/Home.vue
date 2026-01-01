<template>
  <div class="min-h-screen bg-slate-50">
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-primary-600 via-primary-700 to-accent-600 text-white">
      <!-- Animated Background -->
      <div class="absolute inset-0 bg-mesh-gradient opacity-50"></div>
      <div class="absolute top-0 left-0 w-72 h-72 bg-white/10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
      <div class="absolute bottom-0 right-0 w-96 h-96 bg-accent-500/20 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>

      <div class="relative container-app py-12 md:py-20">
        <div class="text-center max-w-3xl mx-auto">
          <h1 class="text-4xl md:text-6xl font-bold mb-6 animate-fade-in-up">
            Buy & Sell
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 to-orange-400">
              Anything
            </span>
          </h1>
          <p class="text-lg md:text-xl text-white/80 mb-10 animate-fade-in-up" style="animation-delay: 0.1s">
            Discover amazing deals from thousands of sellers in your area
          </p>

          <!-- Search Box -->
          <div class="max-w-2xl mx-auto animate-fade-in-up" style="animation-delay: 0.2s">
            <div class="flex flex-col sm:flex-row gap-3 p-2 bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20">
              <!-- Location Button -->
              <button
                @click="showLocationModal = true"
                class="flex items-center justify-center sm:justify-start px-4 py-3 bg-white/10 hover:bg-white/20 rounded-xl text-white transition-colors"
              >
                <MapPinIcon class="w-5 h-5 mr-2 text-amber-400" />
                <span class="truncate">{{ currentLocation || 'All India' }}</span>
                <ChevronDownIcon class="w-4 h-4 ml-2 opacity-60" />
              </button>

              <!-- Search Input -->
              <div class="flex-1 relative">
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="What are you looking for?"
                  class="w-full px-5 py-3 bg-white rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-400 shadow-soft"
                  @keyup.enter="handleSearch"
                />
                <button
                  @click="handleSearch"
                  class="absolute right-2 top-1/2 -translate-y-1/2 p-2 bg-gradient-primary text-white rounded-lg hover:shadow-glow transition-shadow"
                >
                  <MagnifyingGlassIcon class="w-5 h-5" />
                </button>
              </div>
            </div>
          </div>

          <!-- Quick Stats -->
          <div class="flex items-center justify-center gap-8 mt-10 animate-fade-in-up" style="animation-delay: 0.3s">
            <div class="text-center">
              <p class="text-3xl font-bold text-white">10K+</p>
              <p class="text-white/60 text-sm">Active Listings</p>
            </div>
            <div class="w-px h-10 bg-white/20"></div>
            <div class="text-center">
              <p class="text-3xl font-bold text-white">5K+</p>
              <p class="text-white/60 text-sm">Happy Users</p>
            </div>
            <div class="w-px h-10 bg-white/20"></div>
            <div class="text-center">
              <p class="text-3xl font-bold text-white">50+</p>
              <p class="text-white/60 text-sm">Cities</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Wave Decoration -->
      <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
          <path d="M0 50L48 45.7C96 41 192 33 288 35.2C384 37 480 50 576 54.8C672 60 768 56 864 48.5C960 41 1056 30 1152 30C1248 30 1344 41 1392 46.5L1440 52V100H1392C1344 100 1248 100 1152 100C1056 100 960 100 864 100C768 100 672 100 576 100C480 100 384 100 288 100C192 100 96 100 48 100H0V50Z" fill="#f8fafc"/>
        </svg>
      </div>
    </section>

    <!-- Categories Section -->
    <section class="py-12 bg-slate-50">
      <div class="container-app">
        <div class="flex items-center justify-between mb-8">
          <h2 class="text-2xl font-bold text-slate-800">Browse Categories</h2>
          <router-link to="/categories" class="text-primary-600 font-medium hover:text-primary-700 flex items-center">
            View All
            <ChevronRightIcon class="w-4 h-4 ml-1" />
          </router-link>
        </div>

        <!-- Categories Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <router-link
            v-for="(category, index) in categories"
            :key="category.id"
            :to="`/category/${category.slug}`"
            class="group p-6 bg-white rounded-2xl shadow-soft hover:shadow-soft-lg transition-all duration-300 transform hover:-translate-y-1 text-center animate-fade-in-up"
            :style="{ animationDelay: `${index * 0.05}s` }"
          >
            <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
              <component :is="getCategoryIcon(category.slug)" class="w-7 h-7 text-primary-600" />
            </div>
            <h3 class="font-semibold text-slate-700 mb-1 group-hover:text-primary-600 transition-colors">{{ category.name }}</h3>
            <p class="text-sm text-slate-400">{{ category.active_listings_count || 0 }} ads</p>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Featured Listings -->
    <section v-if="featuredListings.length > 0" class="py-12 bg-white">
      <div class="container-app">
        <div class="flex items-center justify-between mb-8">
          <div class="flex items-center">
            <SparklesIcon class="w-6 h-6 text-amber-500 mr-2" />
            <h2 class="text-2xl font-bold text-slate-800">Featured Ads</h2>
          </div>
          <router-link to="/search?featured=1" class="text-primary-600 font-medium hover:text-primary-700 flex items-center">
            View All
            <ChevronRightIcon class="w-4 h-4 ml-1" />
          </router-link>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <ListingCard
            v-for="(listing, index) in featuredListings"
            :key="listing.id"
            :listing="listing"
            class="animate-fade-in-up"
            :style="{ animationDelay: `${index * 0.1}s` }"
          />
        </div>
      </div>
    </section>

    <!-- Recent Listings -->
    <section class="py-12 bg-slate-50">
      <div class="container-app">
        <div class="flex items-center justify-between mb-8">
          <h2 class="text-2xl font-bold text-slate-800">Fresh Recommendations</h2>
          <router-link to="/search" class="text-primary-600 font-medium hover:text-primary-700 flex items-center">
            View All
            <ChevronRightIcon class="w-4 h-4 ml-1" />
          </router-link>
        </div>

        <!-- Loading Skeleton -->
        <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <div v-for="i in 8" :key="i" class="bg-white rounded-2xl overflow-hidden shadow-soft">
            <div class="aspect-[4/3] bg-slate-200 animate-pulse"></div>
            <div class="p-4 space-y-3">
              <div class="h-6 bg-slate-200 rounded-lg w-24 animate-pulse"></div>
              <div class="h-4 bg-slate-200 rounded-lg w-full animate-pulse"></div>
              <div class="h-4 bg-slate-200 rounded-lg w-20 animate-pulse"></div>
            </div>
          </div>
        </div>

        <!-- Listings Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <ListingCard
            v-for="(listing, index) in recentListings"
            :key="listing.id"
            :listing="listing"
            class="animate-fade-in-up"
            :style="{ animationDelay: `${index * 0.05}s` }"
          />
        </div>

        <!-- Load More Button -->
        <div class="text-center mt-10">
          <router-link
            to="/search"
            class="inline-flex items-center px-8 py-3 bg-white border-2 border-primary-500 text-primary-600 font-semibold rounded-full hover:bg-primary-50 transition-colors shadow-soft"
          >
            Explore All Listings
            <ArrowRightIcon class="w-5 h-5 ml-2" />
          </router-link>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-r from-primary-600 to-accent-600 text-white relative overflow-hidden">
      <div class="absolute inset-0 bg-mesh-gradient opacity-30"></div>
      <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>

      <div class="relative container-app text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4 animate-fade-in-up">Ready to Sell?</h2>
        <p class="text-lg text-white/80 mb-8 max-w-xl mx-auto animate-fade-in-up" style="animation-delay: 0.1s">
          Post your ad in minutes and reach thousands of buyers in your area. It's free and easy!
        </p>
        <router-link
          to="/sell"
          class="inline-flex items-center px-8 py-4 bg-white text-primary-600 font-bold rounded-full hover:shadow-xl transform hover:scale-105 transition-all duration-300 shadow-soft-lg animate-fade-in-up"
          style="animation-delay: 0.2s"
        >
          <PlusCircleIcon class="w-6 h-6 mr-2" />
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
import {
  MapPinIcon,
  ChevronDownIcon,
  ChevronRightIcon,
  MagnifyingGlassIcon,
  ArrowRightIcon,
  PlusCircleIcon,
  SparklesIcon,
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
const currentLocation = computed(() => appStore.currentLocation?.city)

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: '/search', query: { q: searchQuery.value } })
  }
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
    featuredListings.value = response.data.data.featured_listings || []
    recentListings.value = response.data.data.recent_listings || []
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
