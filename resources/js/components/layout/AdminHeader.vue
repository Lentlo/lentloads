<template>
  <header class="bg-gray-900 text-white">
    <div class="container-app">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <div class="flex items-center gap-6">
          <router-link to="/admin" class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-primary-500 rounded flex items-center justify-center">
              <span class="text-white font-bold">L</span>
            </div>
            <span class="font-semibold">Admin Panel</span>
          </router-link>

          <!-- Nav -->
          <nav class="hidden md:flex items-center gap-4">
            <router-link
              to="/admin"
              class="px-3 py-2 rounded hover:bg-gray-800"
              :class="{ 'bg-gray-800': $route.path === '/admin' }"
            >
              Dashboard
            </router-link>
            <router-link
              to="/admin/users"
              class="px-3 py-2 rounded hover:bg-gray-800"
              :class="{ 'bg-gray-800': $route.path.includes('/admin/users') }"
            >
              Users
            </router-link>
            <router-link
              to="/admin/listings"
              class="px-3 py-2 rounded hover:bg-gray-800"
              :class="{ 'bg-gray-800': $route.path.includes('/admin/listings') }"
            >
              Listings
            </router-link>
            <router-link
              to="/admin/categories"
              class="px-3 py-2 rounded hover:bg-gray-800"
              :class="{ 'bg-gray-800': $route.path.includes('/admin/categories') }"
            >
              Categories
            </router-link>
            <router-link
              to="/admin/reports"
              class="px-3 py-2 rounded hover:bg-gray-800"
              :class="{ 'bg-gray-800': $route.path.includes('/admin/reports') }"
            >
              Reports
            </router-link>
            <router-link
              to="/admin/settings"
              class="px-3 py-2 rounded hover:bg-gray-800"
              :class="{ 'bg-gray-800': $route.path.includes('/admin/settings') }"
            >
              Settings
            </router-link>
          </nav>
        </div>

        <!-- Right -->
        <div class="flex items-center gap-4">
          <router-link to="/" class="text-sm text-gray-400 hover:text-white">
            View Site
          </router-link>
          <button @click="logout" class="text-sm text-gray-400 hover:text-white">
            Logout
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const logout = async () => {
  await authStore.logout()
  router.push('/')
}
</script>
