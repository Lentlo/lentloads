<template>
  <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-slate-100">
    <div class="container-app">
      <!-- Top bar -->
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <router-link to="/" class="flex items-center space-x-2 group">
          <div class="w-10 h-10 bg-gradient-primary rounded-xl flex items-center justify-center shadow-glow transform group-hover:scale-105 transition-transform duration-300">
            <span class="text-white font-bold text-xl">L</span>
          </div>
          <span class="text-xl font-bold bg-gradient-to-r from-primary-600 to-accent-500 bg-clip-text text-transparent hidden sm:block">
            Lentloads
          </span>
        </router-link>

        <!-- Search Bar - Desktop -->
        <div class="hidden md:flex flex-1 max-w-xl mx-6">
          <div class="relative w-full group">
            <div class="absolute inset-0 bg-gradient-primary rounded-full opacity-0 group-focus-within:opacity-10 transition-opacity blur"></div>
            <div class="relative flex items-center bg-slate-50 rounded-full border-2 border-transparent focus-within:border-primary-400 focus-within:bg-white transition-all duration-300 shadow-inner-soft">
              <MagnifyingGlassIcon class="w-5 h-5 text-slate-400 ml-4" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search for anything..."
                class="flex-1 px-3 py-3 bg-transparent focus:outline-none text-slate-700 placeholder-slate-400"
                @keyup.enter="handleSearch"
              />
              <button
                v-if="searchQuery"
                @click="searchQuery = ''"
                class="p-2 mr-1 text-slate-400 hover:text-slate-600"
              >
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center space-x-2 sm:space-x-3">
          <!-- Notifications -->
          <button
            v-if="isAuthenticated"
            @click="$router.push('/notifications')"
            class="relative p-2.5 text-slate-500 hover:text-primary-600 hover:bg-primary-50 rounded-full transition-all duration-300"
          >
            <BellIcon class="w-6 h-6" />
            <span
              v-if="unreadCount > 0"
              class="absolute top-1 right-1 w-5 h-5 bg-gradient-accent text-white text-xs font-bold rounded-full flex items-center justify-center animate-bounce-in"
            >
              {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
          </button>

          <!-- Messages -->
          <router-link
            v-if="isAuthenticated"
            to="/messages"
            class="relative p-2.5 text-slate-500 hover:text-primary-600 hover:bg-primary-50 rounded-full transition-all duration-300"
          >
            <ChatBubbleLeftRightIcon class="w-6 h-6" />
            <span
              v-if="unreadMessages > 0"
              class="absolute top-1 right-1 w-5 h-5 bg-gradient-accent text-white text-xs font-bold rounded-full flex items-center justify-center animate-bounce-in"
            >
              {{ unreadMessages > 9 ? '9+' : unreadMessages }}
            </span>
          </router-link>

          <!-- User Menu -->
          <div v-if="isAuthenticated" class="relative">
            <button
              @click="showUserMenu = !showUserMenu"
              class="flex items-center space-x-2 p-1.5 rounded-full hover:bg-slate-100 transition-colors"
            >
              <div class="w-9 h-9 rounded-full bg-gradient-primary flex items-center justify-center text-white font-semibold shadow-soft">
                {{ user?.name?.charAt(0) || 'U' }}
              </div>
              <ChevronDownIcon class="w-4 h-4 text-slate-400 hidden sm:block" />
            </button>

            <!-- Dropdown -->
            <transition
              enter-active-class="transition ease-out duration-200"
              enter-from-class="opacity-0 scale-95"
              enter-to-class="opacity-100 scale-100"
              leave-active-class="transition ease-in duration-150"
              leave-from-class="opacity-100 scale-100"
              leave-to-class="opacity-0 scale-95"
            >
              <div
                v-if="showUserMenu"
                class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-soft-xl border border-slate-100 py-2 z-50 animate-fade-in-down"
              >
                <div class="px-4 py-3 border-b border-slate-100">
                  <p class="font-semibold text-slate-800">{{ user?.name }}</p>
                  <p class="text-sm text-slate-500 truncate">{{ user?.email }}</p>
                </div>

                <div class="py-2">
                  <router-link
                    to="/dashboard"
                    class="flex items-center px-4 py-2.5 text-slate-600 hover:bg-primary-50 hover:text-primary-600 transition-colors"
                    @click="showUserMenu = false"
                  >
                    <Squares2X2Icon class="w-5 h-5 mr-3" />
                    Dashboard
                  </router-link>
                  <router-link
                    to="/my-listings"
                    class="flex items-center px-4 py-2.5 text-slate-600 hover:bg-primary-50 hover:text-primary-600 transition-colors"
                    @click="showUserMenu = false"
                  >
                    <ClipboardDocumentListIcon class="w-5 h-5 mr-3" />
                    My Ads
                  </router-link>
                  <router-link
                    to="/favorites"
                    class="flex items-center px-4 py-2.5 text-slate-600 hover:bg-primary-50 hover:text-primary-600 transition-colors"
                    @click="showUserMenu = false"
                  >
                    <HeartIcon class="w-5 h-5 mr-3" />
                    Favorites
                  </router-link>
                  <router-link
                    to="/settings"
                    class="flex items-center px-4 py-2.5 text-slate-600 hover:bg-primary-50 hover:text-primary-600 transition-colors"
                    @click="showUserMenu = false"
                  >
                    <Cog6ToothIcon class="w-5 h-5 mr-3" />
                    Settings
                  </router-link>
                </div>

                <div class="border-t border-slate-100 pt-2">
                  <button
                    @click="handleLogout"
                    class="w-full flex items-center px-4 py-2.5 text-red-500 hover:bg-red-50 transition-colors"
                  >
                    <ArrowRightOnRectangleIcon class="w-5 h-5 mr-3" />
                    Logout
                  </button>
                </div>
              </div>
            </transition>
          </div>

          <!-- Login Button -->
          <router-link
            v-if="!isAuthenticated"
            to="/login"
            class="px-4 py-2 text-slate-600 hover:text-primary-600 font-medium transition-colors"
          >
            Login
          </router-link>

          <!-- Sell Button -->
          <router-link
            to="/sell"
            class="flex items-center px-5 py-2.5 bg-gradient-primary text-white font-semibold rounded-full shadow-glow hover:shadow-lg transform hover:scale-105 transition-all duration-300"
          >
            <PlusIcon class="w-5 h-5 mr-1" />
            <span class="hidden sm:inline">Sell</span>
          </router-link>
        </div>
      </div>

      <!-- Search Bar - Mobile -->
      <div class="md:hidden pb-3">
        <div class="relative">
          <div class="flex items-center bg-slate-50 rounded-full border-2 border-transparent focus-within:border-primary-400 focus-within:bg-white transition-all">
            <MagnifyingGlassIcon class="w-5 h-5 text-slate-400 ml-4" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search..."
              class="flex-1 px-3 py-2.5 bg-transparent focus:outline-none text-slate-700"
              @keyup.enter="handleSearch"
            />
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  PlusIcon,
  BellIcon,
  ChatBubbleLeftRightIcon,
  ChevronDownIcon,
  Squares2X2Icon,
  ClipboardDocumentListIcon,
  HeartIcon,
  Cog6ToothIcon,
  ArrowRightOnRectangleIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()

const showUserMenu = ref(false)
const searchQuery = ref('')
const unreadCount = ref(0)
const unreadMessages = ref(0)

const isAuthenticated = computed(() => authStore.isAuthenticated)
const user = computed(() => authStore.user)

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: '/search', query: { q: searchQuery.value } })
  }
}

const handleLogout = async () => {
  showUserMenu.value = false
  await authStore.logout()
  router.push('/')
}

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
