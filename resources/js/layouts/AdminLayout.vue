<template>
  <div class="min-h-screen bg-gray-100 flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white fixed h-full hidden lg:block">
      <div class="p-4 border-b border-gray-800">
        <router-link to="/admin" class="block">
          <h1 class="text-xl font-bold">Lentloads Admin</h1>
        </router-link>
      </div>

      <nav class="p-4 space-y-1">
        <router-link
          v-for="item in menuItems"
          :key="item.path"
          :to="item.path"
          class="flex items-center gap-3 px-4 py-2 rounded-lg transition"
          :class="isActive(item.path)
            ? 'bg-primary-600 text-white'
            : 'text-gray-300 hover:bg-gray-800'"
        >
          <component :is="item.icon" class="w-5 h-5" />
          {{ item.label }}
        </router-link>
      </nav>

      <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-800">
        <router-link
          to="/"
          class="flex items-center gap-3 px-4 py-2 text-gray-300 hover:bg-gray-800 rounded-lg"
        >
          <ArrowLeftIcon class="w-5 h-5" />
          Back to Site
        </router-link>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 lg:ml-64">
      <!-- Top Bar -->
      <header class="bg-white shadow-sm h-16 flex items-center px-6 justify-between sticky top-0 z-10">
        <button @click="mobileMenuOpen = true" class="lg:hidden p-2 -ml-2">
          <Bars3Icon class="w-6 h-6" />
        </button>

        <div class="flex items-center gap-4 ml-auto">
          <router-link to="/admin/reports" class="relative p-2 hover:bg-gray-100 rounded-lg">
            <BellIcon class="w-6 h-6 text-gray-600" />
            <span v-if="pendingReports" class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
          </router-link>

          <div class="flex items-center gap-2">
            <img
              :src="user?.avatar_url"
              :alt="user?.name"
              class="w-8 h-8 rounded-full"
            />
            <span class="text-sm font-medium text-gray-700">{{ user?.name }}</span>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main>
        <router-view />
      </main>
    </div>

    <!-- Mobile Menu -->
    <div
      v-if="mobileMenuOpen"
      class="fixed inset-0 z-50 lg:hidden"
    >
      <div class="absolute inset-0 bg-black/50" @click="mobileMenuOpen = false"></div>
      <aside class="absolute left-0 top-0 bottom-0 w-64 bg-gray-900 text-white">
        <div class="p-4 border-b border-gray-800 flex items-center justify-between">
          <h1 class="text-xl font-bold">Admin</h1>
          <button @click="mobileMenuOpen = false" class="p-1">
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>

        <nav class="p-4 space-y-1">
          <router-link
            v-for="item in menuItems"
            :key="item.path"
            :to="item.path"
            @click="mobileMenuOpen = false"
            class="flex items-center gap-3 px-4 py-2 rounded-lg transition"
            :class="isActive(item.path)
              ? 'bg-primary-600 text-white'
              : 'text-gray-300 hover:bg-gray-800'"
          >
            <component :is="item.icon" class="w-5 h-5" />
            {{ item.label }}
          </router-link>
        </nav>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  HomeIcon,
  UsersIcon,
  TagIcon,
  FolderIcon,
  FlagIcon,
  Cog6ToothIcon,
  Bars3Icon,
  BellIcon,
  XMarkIcon,
  ArrowLeftIcon,
  ChatBubbleLeftRightIcon,
  PhoneIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const authStore = useAuthStore()

const mobileMenuOpen = ref(false)
const pendingReports = ref(0)

const user = computed(() => authStore.user)

const menuItems = [
  { path: '/admin', label: 'Dashboard', icon: HomeIcon },
  { path: '/admin/users', label: 'Users', icon: UsersIcon },
  { path: '/admin/listings', label: 'Listings', icon: TagIcon },
  { path: '/admin/categories', label: 'Categories', icon: FolderIcon },
  { path: '/admin/conversations', label: 'Conversations', icon: ChatBubbleLeftRightIcon },
  { path: '/admin/contact-views', label: 'Contact Views', icon: PhoneIcon },
  { path: '/admin/reports', label: 'Reports', icon: FlagIcon },
  { path: '/admin/settings', label: 'Settings', icon: Cog6ToothIcon },
]

const isActive = (path) => {
  if (path === '/admin') {
    return route.path === '/admin'
  }
  return route.path.startsWith(path)
}
</script>
