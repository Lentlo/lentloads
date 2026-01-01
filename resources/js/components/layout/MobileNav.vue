<template>
  <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-lg border-t border-slate-100 md:hidden z-50 safe-area-bottom">
    <div class="flex items-center justify-around py-2">
      <!-- Home -->
      <router-link
        to="/"
        class="flex flex-col items-center py-2 px-4 transition-all duration-300"
        :class="isActive('/') ? 'text-primary-600' : 'text-slate-400'"
      >
        <HomeIcon class="w-6 h-6" :class="isActive('/') ? 'scale-110' : ''" />
        <span class="text-xs mt-1 font-medium">Home</span>
      </router-link>

      <!-- Search -->
      <router-link
        to="/search"
        class="flex flex-col items-center py-2 px-4 transition-all duration-300"
        :class="isActive('/search') ? 'text-primary-600' : 'text-slate-400'"
      >
        <MagnifyingGlassIcon class="w-6 h-6" :class="isActive('/search') ? 'scale-110' : ''" />
        <span class="text-xs mt-1 font-medium">Search</span>
      </router-link>

      <!-- Sell Button (Center) -->
      <router-link
        to="/sell"
        class="flex flex-col items-center -mt-6"
      >
        <div class="w-14 h-14 bg-gradient-primary rounded-full flex items-center justify-center shadow-glow transform hover:scale-105 active:scale-95 transition-transform">
          <PlusIcon class="w-7 h-7 text-white" />
        </div>
        <span class="text-xs mt-1 font-medium text-primary-600">Sell</span>
      </router-link>

      <!-- Favorites -->
      <router-link
        to="/favorites"
        class="flex flex-col items-center py-2 px-4 transition-all duration-300"
        :class="isActive('/favorites') ? 'text-primary-600' : 'text-slate-400'"
      >
        <HeartIcon class="w-6 h-6" :class="isActive('/favorites') ? 'scale-110' : ''" />
        <span class="text-xs mt-1 font-medium">Saved</span>
      </router-link>

      <!-- Account -->
      <router-link
        :to="isAuthenticated ? '/dashboard' : '/login'"
        class="flex flex-col items-center py-2 px-4 transition-all duration-300"
        :class="isActive('/dashboard') || isActive('/login') ? 'text-primary-600' : 'text-slate-400'"
      >
        <UserCircleIcon class="w-6 h-6" />
        <span class="text-xs mt-1 font-medium">{{ isAuthenticated ? 'Account' : 'Login' }}</span>
      </router-link>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  HomeIcon,
  MagnifyingGlassIcon,
  PlusIcon,
  HeartIcon,
  UserCircleIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const authStore = useAuthStore()

const isAuthenticated = computed(() => authStore.isAuthenticated)

const isActive = (path) => {
  if (path === '/') {
    return route.path === '/'
  }
  return route.path.startsWith(path)
}
</script>

<style scoped>
.safe-area-bottom {
  padding-bottom: env(safe-area-inset-bottom, 0);
}
</style>
