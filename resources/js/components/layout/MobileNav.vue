<template>
  <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 md:hidden z-50 pb-safe">
    <div class="flex items-center justify-around h-16">
      <router-link
        to="/"
        class="flex flex-col items-center justify-center w-full h-full"
        :class="isActive('/') ? 'text-primary-600' : 'text-gray-500'"
      >
        <HomeIcon class="w-6 h-6" />
        <span class="text-xs mt-1">Home</span>
      </router-link>

      <router-link
        to="/categories"
        class="flex flex-col items-center justify-center w-full h-full"
        :class="isActive('/categories') ? 'text-primary-600' : 'text-gray-500'"
      >
        <Squares2X2Icon class="w-6 h-6" />
        <span class="text-xs mt-1">Categories</span>
      </router-link>

      <router-link
        to="/sell"
        class="flex flex-col items-center justify-center w-full h-full relative -mt-4"
      >
        <div class="w-14 h-14 bg-primary-600 rounded-full flex items-center justify-center shadow-lg">
          <PlusIcon class="w-8 h-8 text-white" />
        </div>
        <span class="text-xs mt-1 text-gray-500">Sell</span>
      </router-link>

      <router-link
        to="/messages"
        class="flex flex-col items-center justify-center w-full h-full relative"
        :class="isActive('/messages') ? 'text-primary-600' : 'text-gray-500'"
      >
        <ChatBubbleLeftRightIcon class="w-6 h-6" />
        <span
          v-if="unreadMessages > 0"
          class="absolute top-1 right-1/4 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center"
        >
          {{ unreadMessages > 9 ? '9+' : unreadMessages }}
        </span>
        <span class="text-xs mt-1">Chat</span>
      </router-link>

      <router-link
        :to="isAuthenticated ? '/dashboard' : '/login'"
        class="flex flex-col items-center justify-center w-full h-full"
        :class="isActive('/dashboard') || isActive('/login') ? 'text-primary-600' : 'text-gray-500'"
      >
        <UserIcon class="w-6 h-6" />
        <span class="text-xs mt-1">{{ isAuthenticated ? 'Account' : 'Login' }}</span>
      </router-link>
    </div>
  </nav>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  HomeIcon,
  Squares2X2Icon,
  PlusIcon,
  ChatBubbleLeftRightIcon,
  UserIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const authStore = useAuthStore()

const unreadMessages = ref(0)

const isAuthenticated = computed(() => authStore.isAuthenticated)

const isActive = (path) => {
  if (path === '/') {
    return route.path === '/'
  }
  return route.path.startsWith(path)
}
</script>

<style scoped>
.pb-safe {
  padding-bottom: env(safe-area-inset-bottom);
}
</style>
