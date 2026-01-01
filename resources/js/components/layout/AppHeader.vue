<template>
  <header class="bg-primary-950 sticky top-0 z-50">
    <div class="container-app">
      <!-- Top bar -->
      <div class="flex items-center justify-between h-14">
        <!-- Logo -->
        <router-link to="/" class="flex items-center space-x-2">
          <span class="text-2xl font-bold text-white">Lentloads</span>
        </router-link>

        <!-- Actions -->
        <div class="flex items-center space-x-2 sm:space-x-4">
          <!-- Notifications -->
          <button
            v-if="isAuthenticated"
            @click="$router.push('/notifications')"
            class="relative p-2 text-white/80 hover:text-white"
          >
            <BellIcon class="w-6 h-6" />
            <span
              v-if="unreadCount > 0"
              class="absolute -top-1 -right-1 w-5 h-5 bg-accent text-primary-950 text-xs font-bold rounded-full flex items-center justify-center"
            >
              {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
          </button>

          <!-- Messages -->
          <router-link
            v-if="isAuthenticated"
            to="/messages"
            class="relative p-2 text-white/80 hover:text-white"
          >
            <ChatBubbleLeftRightIcon class="w-6 h-6" />
            <span
              v-if="unreadMessages > 0"
              class="absolute -top-1 -right-1 w-5 h-5 bg-accent text-primary-950 text-xs font-bold rounded-full flex items-center justify-center"
            >
              {{ unreadMessages > 9 ? '9+' : unreadMessages }}
            </span>
          </router-link>

          <!-- Login/Register or User Menu -->
          <div v-if="isAuthenticated" class="relative">
            <button
              @click="showUserMenu = !showUserMenu"
              class="flex items-center space-x-2 text-white/80 hover:text-white"
            >
              <UserCircleIcon class="w-7 h-7" />
              <ChevronDownIcon class="w-4 h-4 hidden sm:block" />
            </button>

            <!-- Dropdown -->
            <transition name="fade">
              <div
                v-if="showUserMenu"
                class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg border border-gray-100 py-1 z-50"
              >
                <div class="px-4 py-3 border-b border-gray-100">
                  <p class="font-semibold text-gray-900">{{ user?.name }}</p>
                  <p class="text-sm text-gray-500 truncate">{{ user?.email }}</p>
                </div>

                <router-link
                  to="/dashboard"
                  class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50"
                  @click="showUserMenu = false"
                >
                  <Squares2X2Icon class="w-5 h-5 mr-3 text-gray-400" />
                  Dashboard
                </router-link>
                <router-link
                  to="/my-listings"
                  class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50"
                  @click="showUserMenu = false"
                >
                  <ClipboardDocumentListIcon class="w-5 h-5 mr-3 text-gray-400" />
                  My Ads
                </router-link>
                <router-link
                  to="/favorites"
                  class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50"
                  @click="showUserMenu = false"
                >
                  <HeartIcon class="w-5 h-5 mr-3 text-gray-400" />
                  Favorites
                </router-link>
                <router-link
                  to="/settings"
                  class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50"
                  @click="showUserMenu = false"
                >
                  <Cog6ToothIcon class="w-5 h-5 mr-3 text-gray-400" />
                  Settings
                </router-link>

                <div class="border-t border-gray-100 mt-1 pt-1">
                  <button
                    @click="handleLogout"
                    class="w-full flex items-center px-4 py-2 text-red-600 hover:bg-red-50"
                  >
                    <ArrowRightOnRectangleIcon class="w-5 h-5 mr-3" />
                    Logout
                  </button>
                </div>
              </div>
            </transition>
          </div>

          <div v-else class="flex items-center space-x-3">
            <router-link to="/login" class="text-white/80 hover:text-white font-medium text-sm">
              Login
            </router-link>
          </div>

          <!-- Sell Button -->
          <router-link
            to="/sell"
            class="flex items-center px-4 py-2 bg-white text-primary-950 font-bold rounded-full hover:bg-gray-100 transition text-sm"
          >
            <PlusCircleIcon class="w-5 h-5 mr-1" />
            <span class="hidden sm:inline">SELL</span>
          </router-link>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'
import {
  PlusCircleIcon,
  BellIcon,
  ChatBubbleLeftRightIcon,
  ChevronDownIcon,
  UserCircleIcon,
  Squares2X2Icon,
  ClipboardDocumentListIcon,
  HeartIcon,
  Cog6ToothIcon,
  ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()
const appStore = useAppStore()

const showUserMenu = ref(false)
const unreadCount = ref(0)
const unreadMessages = ref(0)

const isAuthenticated = computed(() => authStore.isAuthenticated)
const user = computed(() => authStore.user)

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
