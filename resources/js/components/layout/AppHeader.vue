<template>
  <header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container-app">
      <!-- Top bar -->
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <router-link to="/" class="flex items-center space-x-2">
          <div class="w-10 h-10 bg-primary-600 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-xl">L</span>
          </div>
          <span class="text-xl font-bold text-gray-900 hidden sm:block">Lentloads</span>
        </router-link>

        <!-- Search Bar - Desktop -->
        <div class="hidden md:flex flex-1 max-w-2xl mx-8">
          <SearchBar />
        </div>

        <!-- Actions -->
        <div class="flex items-center space-x-4">
          <!-- Location -->
          <button
            @click="showLocationModal = true"
            class="hidden md:flex items-center text-sm text-gray-600 hover:text-gray-900"
          >
            <MapPinIcon class="w-5 h-5 mr-1" />
            <span class="truncate max-w-[120px]">{{ currentLocation || 'Select Location' }}</span>
          </button>

          <!-- Sell Button -->
          <router-link
            to="/sell"
            class="btn-primary hidden sm:inline-flex"
          >
            <PlusIcon class="w-5 h-5 mr-1" />
            Sell
          </router-link>

          <!-- Notifications -->
          <button
            v-if="isAuthenticated"
            @click="$router.push('/notifications')"
            class="relative p-2 text-gray-600 hover:text-gray-900"
          >
            <BellIcon class="w-6 h-6" />
            <span
              v-if="unreadCount > 0"
              class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center"
            >
              {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
          </button>

          <!-- Messages -->
          <router-link
            v-if="isAuthenticated"
            to="/messages"
            class="relative p-2 text-gray-600 hover:text-gray-900"
          >
            <ChatBubbleLeftRightIcon class="w-6 h-6" />
            <span
              v-if="unreadMessages > 0"
              class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center"
            >
              {{ unreadMessages > 9 ? '9+' : unreadMessages }}
            </span>
          </router-link>

          <!-- User Menu -->
          <div v-if="isAuthenticated" class="relative">
            <button
              @click="showUserMenu = !showUserMenu"
              class="flex items-center space-x-2"
            >
              <img
                :src="user?.avatar_url"
                :alt="user?.name"
                class="w-8 h-8 rounded-full object-cover"
              />
              <ChevronDownIcon class="w-4 h-4 text-gray-500 hidden sm:block" />
            </button>

            <!-- Dropdown -->
            <transition name="fade">
              <div
                v-if="showUserMenu"
                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50"
              >
                <div class="px-4 py-2 border-b border-gray-100">
                  <p class="font-medium text-gray-900">{{ user?.name }}</p>
                  <p class="text-sm text-gray-500 truncate">{{ user?.email }}</p>
                </div>

                <router-link
                  to="/dashboard"
                  class="block px-4 py-2 text-gray-700 hover:bg-gray-50"
                  @click="showUserMenu = false"
                >
                  Dashboard
                </router-link>
                <router-link
                  to="/my-listings"
                  class="block px-4 py-2 text-gray-700 hover:bg-gray-50"
                  @click="showUserMenu = false"
                >
                  My Listings
                </router-link>
                <router-link
                  to="/favorites"
                  class="block px-4 py-2 text-gray-700 hover:bg-gray-50"
                  @click="showUserMenu = false"
                >
                  Favorites
                </router-link>
                <router-link
                  to="/settings"
                  class="block px-4 py-2 text-gray-700 hover:bg-gray-50"
                  @click="showUserMenu = false"
                >
                  Settings
                </router-link>

                <div class="border-t border-gray-100 mt-1 pt-1">
                  <button
                    @click="handleLogout"
                    class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50"
                  >
                    Logout
                  </button>
                </div>
              </div>
            </transition>
          </div>

          <!-- Login/Register -->
          <div v-else class="flex items-center space-x-3">
            <router-link to="/login" class="text-gray-600 hover:text-gray-900 font-medium">
              Login
            </router-link>
            <router-link to="/register" class="btn-primary">
              Register
            </router-link>
          </div>
        </div>
      </div>

      <!-- Search Bar - Mobile -->
      <div class="md:hidden pb-3">
        <SearchBar />
      </div>
    </div>

    <!-- Location Modal -->
    <LocationModal v-if="showLocationModal" @close="showLocationModal = false" />
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'
import {
  MapPinIcon,
  PlusIcon,
  BellIcon,
  ChatBubbleLeftRightIcon,
  ChevronDownIcon,
} from '@heroicons/vue/24/outline'
import SearchBar from '@/components/common/SearchBar.vue'
import LocationModal from '@/components/modals/LocationModal.vue'

const router = useRouter()
const authStore = useAuthStore()
const appStore = useAppStore()

const showUserMenu = ref(false)
const showLocationModal = ref(false)
const unreadCount = ref(0)
const unreadMessages = ref(0)

const isAuthenticated = computed(() => authStore.isAuthenticated)
const user = computed(() => authStore.user)
const currentLocation = computed(() => appStore.currentLocation?.city)

const handleLogout = async () => {
  showUserMenu.value = false
  await authStore.logout()
  router.push('/')
}

// Close menu on outside click
const closeMenu = (e) => {
  if (showUserMenu.value && !e.target.closest('.relative')) {
    showUserMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', closeMenu)
})

onUnmounted(() => {
  document.removeEventListener('click', closeMenu)
})
</script>
