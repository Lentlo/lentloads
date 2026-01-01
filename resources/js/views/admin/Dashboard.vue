<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="card p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total Users</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.users || 0 }}</p>
            <p class="text-sm text-green-600">+{{ stats.newUsersToday || 0 }} today</p>
          </div>
          <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
            <UsersIcon class="w-6 h-6 text-blue-600" />
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Active Listings</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.listings || 0 }}</p>
            <p class="text-sm text-green-600">+{{ stats.newListingsToday || 0 }} today</p>
          </div>
          <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
            <TagIcon class="w-6 h-6 text-green-600" />
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Pending Approval</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.pendingListings || 0 }}</p>
            <p class="text-sm text-yellow-600">Needs attention</p>
          </div>
          <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
            <ClockIcon class="w-6 h-6 text-yellow-600" />
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Open Reports</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.openReports || 0 }}</p>
            <p class="text-sm text-red-600">Requires review</p>
          </div>
          <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
            <FlagIcon class="w-6 h-6 text-red-600" />
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Listings -->
      <div class="card">
        <div class="p-4 border-b flex items-center justify-between">
          <h2 class="font-semibold text-gray-900">Recent Listings</h2>
          <router-link to="/admin/listings" class="text-sm text-primary-600 hover:underline">
            View all
          </router-link>
        </div>
        <div class="divide-y">
          <div
            v-for="listing in recentListings"
            :key="listing.id"
            class="p-4 flex items-center gap-4"
          >
            <img
              :src="listing.primary_image_url"
              :alt="listing.title"
              class="w-12 h-12 rounded object-cover"
            />
            <div class="flex-1 min-w-0">
              <p class="font-medium text-gray-900 truncate">{{ listing.title }}</p>
              <p class="text-sm text-gray-500">by {{ listing.user?.name }}</p>
            </div>
            <span
              class="badge"
              :class="getStatusClass(listing.status)"
            >
              {{ listing.status }}
            </span>
          </div>
          <div v-if="!recentListings.length" class="p-8 text-center text-gray-500">
            No recent listings
          </div>
        </div>
      </div>

      <!-- Recent Reports -->
      <div class="card">
        <div class="p-4 border-b flex items-center justify-between">
          <h2 class="font-semibold text-gray-900">Recent Reports</h2>
          <router-link to="/admin/reports" class="text-sm text-primary-600 hover:underline">
            View all
          </router-link>
        </div>
        <div class="divide-y">
          <div
            v-for="report in recentReports"
            :key="report.id"
            class="p-4"
          >
            <div class="flex items-center justify-between mb-2">
              <span class="badge bg-red-100 text-red-700">{{ report.reason }}</span>
              <span class="text-sm text-gray-500">{{ formatDate(report.created_at) }}</span>
            </div>
            <p class="text-sm text-gray-600 line-clamp-2">{{ report.description || 'No description provided' }}</p>
            <p class="text-xs text-gray-400 mt-1">
              Reported by {{ report.reporter?.name }}
            </p>
          </div>
          <div v-if="!recentReports.length" class="p-8 text-center text-gray-500">
            No recent reports
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8">
      <h2 class="font-semibold text-gray-900 mb-4">Quick Actions</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <router-link
          to="/admin/listings?status=pending"
          class="card p-4 text-center hover:shadow-md transition"
        >
          <ClipboardDocumentCheckIcon class="w-8 h-8 text-primary-600 mx-auto mb-2" />
          <p class="font-medium">Review Listings</p>
        </router-link>
        <router-link
          to="/admin/users"
          class="card p-4 text-center hover:shadow-md transition"
        >
          <UserGroupIcon class="w-8 h-8 text-primary-600 mx-auto mb-2" />
          <p class="font-medium">Manage Users</p>
        </router-link>
        <router-link
          to="/admin/categories"
          class="card p-4 text-center hover:shadow-md transition"
        >
          <FolderIcon class="w-8 h-8 text-primary-600 mx-auto mb-2" />
          <p class="font-medium">Categories</p>
        </router-link>
        <router-link
          to="/admin/settings"
          class="card p-4 text-center hover:shadow-md transition"
        >
          <Cog6ToothIcon class="w-8 h-8 text-primary-600 mx-auto mb-2" />
          <p class="font-medium">Settings</p>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import dayjs from 'dayjs'
import {
  UsersIcon,
  TagIcon,
  ClockIcon,
  FlagIcon,
  ClipboardDocumentCheckIcon,
  UserGroupIcon,
  FolderIcon,
  Cog6ToothIcon,
} from '@heroicons/vue/24/outline'

const stats = ref({})
const recentListings = ref([])
const recentReports = ref([])

const formatDate = (date) => dayjs(date).fromNow()

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    pending: 'bg-yellow-100 text-yellow-800',
    rejected: 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const fetchDashboard = async () => {
  try {
    const response = await api.get('/admin/dashboard')
    stats.value = response.data.stats || {}
    recentListings.value = response.data.recentListings || []
    recentReports.value = response.data.recentReports || []
  } catch (error) {
    console.error('Failed to fetch dashboard data')
  }
}

onMounted(() => {
  fetchDashboard()
})
</script>
