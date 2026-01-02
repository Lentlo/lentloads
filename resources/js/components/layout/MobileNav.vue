<template>
  <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 md:hidden z-50">
    <div class="flex items-center justify-around h-16 safe-area-bottom">
      <!-- Home / My Ads -->
      <router-link
        :to="isAuthenticated ? '/my-listings' : '/'"
        class="flex flex-col items-center justify-center flex-1 h-full"
        :class="(isAuthenticated ? isActive('/my-listings') : isActive('/')) ? 'text-primary-600' : 'text-gray-500'"
      >
        <component
          :is="isAuthenticated ? ClipboardDocumentListIcon : HomeIcon"
          class="w-6 h-6"
        />
        <span class="text-[10px] mt-0.5 font-medium">{{ isAuthenticated ? 'My Ads' : 'Home' }}</span>
      </router-link>

      <!-- Search -->
      <router-link
        to="/search"
        class="flex flex-col items-center justify-center flex-1 h-full"
        :class="isActive('/search') ? 'text-primary-600' : 'text-gray-500'"
      >
        <MagnifyingGlassIcon class="w-6 h-6" />
        <span class="text-[10px] mt-0.5 font-medium">Search</span>
      </router-link>

      <!-- Sell Button (Center) -->
      <div class="flex flex-col items-center justify-center flex-1 h-full relative">
        <router-link
          to="/sell"
          class="absolute -top-5 w-14 h-14 bg-primary-600 rounded-full flex items-center justify-center shadow-lg"
        >
          <PlusIcon class="w-7 h-7 text-white" />
        </router-link>
        <span class="text-[10px] mt-8 font-medium text-primary-600">Sell</span>
      </div>

      <!-- Messages -->
      <router-link
        to="/messages"
        class="flex flex-col items-center justify-center flex-1 h-full relative"
        :class="isActive('/messages') ? 'text-primary-600' : 'text-gray-500'"
      >
        <ChatBubbleLeftRightIcon class="w-6 h-6" />
        <span
          v-if="unreadMessages > 0"
          class="absolute top-2 right-1/4 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center"
        >
          {{ unreadMessages > 9 ? '9+' : unreadMessages }}
        </span>
        <span class="text-[10px] mt-0.5 font-medium">Chat</span>
      </router-link>

      <!-- Account -->
      <router-link
        :to="isAuthenticated ? '/dashboard' : '/login'"
        class="flex flex-col items-center justify-center flex-1 h-full"
        :class="isActive('/dashboard') || isActive('/settings') || isActive('/login') ? 'text-primary-600' : 'text-gray-500'"
      >
        <UserCircleIcon class="w-6 h-6" />
        <span class="text-[10px] mt-0.5 font-medium">{{ isAuthenticated ? 'Account' : 'Login' }}</span>
      </router-link>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import {
  HomeIcon,
  MagnifyingGlassIcon,
  PlusIcon,
  ChatBubbleLeftRightIcon,
  UserCircleIcon,
  ClipboardDocumentListIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const authStore = useAuthStore()

const isAuthenticated = computed(() => authStore.isAuthenticated)
const unreadMessages = ref(0)

const isActive = (path) => {
  if (path === '/') return route.path === '/'
  return route.path.startsWith(path)
}

const fetchUnreadMessages = async () => {
  if (!isAuthenticated.value) return
  try {
    const response = await api.get('/conversations/unread-count')
    unreadMessages.value = response.data.data?.count || 0
  } catch (e) {}
}

let interval = null

onMounted(() => {
  if (isAuthenticated.value) {
    fetchUnreadMessages()
    interval = setInterval(fetchUnreadMessages, 30000)
  }
})

onUnmounted(() => {
  if (interval) clearInterval(interval)
})

watch(isAuthenticated, (val) => {
  if (val) {
    fetchUnreadMessages()
    interval = setInterval(fetchUnreadMessages, 30000)
  } else {
    unreadMessages.value = 0
    if (interval) clearInterval(interval)
  }
})
</script>

<style scoped>
.safe-area-bottom {
  padding-bottom: env(safe-area-inset-bottom, 0);
}
</style>
