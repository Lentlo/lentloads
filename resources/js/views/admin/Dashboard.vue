<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
      <div class="flex items-center gap-2">
        <select
          v-model="chartPeriod"
          @change="fetchStats"
          class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        >
          <option value="7">Last 7 days</option>
          <option value="14">Last 14 days</option>
          <option value="30">Last 30 days</option>
          <option value="90">Last 90 days</option>
        </select>
        <button
          @click="refreshAll"
          class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg"
          :class="{ 'animate-spin': refreshing }"
        >
          <ArrowPathIcon class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="card p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total Users</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.users?.total || 0 }}</p>
            <div class="flex items-center gap-2 mt-1">
              <span class="text-sm text-green-600">+{{ stats.users?.new_today || 0 }} today</span>
              <span class="text-xs text-gray-400">{{ stats.users?.new_this_week || 0 }} this week</span>
            </div>
          </div>
          <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
            <UsersIcon class="w-6 h-6 text-blue-600" />
          </div>
        </div>
        <div class="mt-3 pt-3 border-t">
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Active</span>
            <span class="font-medium">{{ stats.users?.active || 0 }}</span>
          </div>
        </div>
      </div>

      <div class="card p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Listings</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.listings?.total || 0 }}</p>
            <div class="flex items-center gap-2 mt-1">
              <span class="text-sm text-green-600">+{{ stats.listings?.new_today || 0 }} today</span>
            </div>
          </div>
          <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
            <TagIcon class="w-6 h-6 text-green-600" />
          </div>
        </div>
        <div class="mt-3 pt-3 border-t">
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Active</span>
            <span class="font-medium text-green-600">{{ stats.listings?.active || 0 }}</span>
          </div>
        </div>
      </div>

      <div class="card p-6 hover:shadow-md transition-shadow cursor-pointer" @click="$router.push('/admin/listings?status=pending')">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Pending Approval</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.listings?.pending || 0 }}</p>
            <p class="text-sm text-yellow-600 mt-1">Needs attention</p>
          </div>
          <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
            <ClockIcon class="w-6 h-6 text-yellow-600" />
          </div>
        </div>
        <div class="mt-3 pt-3 border-t">
          <span class="text-sm text-primary-600 hover:underline">Review pending →</span>
        </div>
      </div>

      <div class="card p-6 hover:shadow-md transition-shadow cursor-pointer" @click="$router.push('/admin/reports')">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Open Reports</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.reports?.pending || 0 }}</p>
            <p class="text-sm text-red-600 mt-1">Requires review</p>
          </div>
          <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
            <FlagIcon class="w-6 h-6 text-red-600" />
          </div>
        </div>
        <div class="mt-3 pt-3 border-t">
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Total</span>
            <span class="font-medium">{{ stats.reports?.total || 0 }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Listings Chart -->
      <div class="card p-6">
        <h3 class="font-semibold text-gray-900 mb-4">New Listings</h3>
        <div class="h-48">
          <div v-if="chartData.listings.length" class="flex items-end justify-between h-full gap-1">
            <div
              v-for="(item, index) in chartData.listings"
              :key="index"
              class="flex-1 bg-primary-100 hover:bg-primary-200 rounded-t transition-colors relative group"
              :style="{ height: getBarHeight(item.count, chartData.listingsMax) + '%' }"
            >
              <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                {{ item.count }} listings
              </div>
            </div>
          </div>
          <div v-else class="h-full flex items-center justify-center text-gray-400">
            No data for selected period
          </div>
        </div>
        <div class="flex justify-between mt-2 text-xs text-gray-500">
          <span>{{ chartPeriod }} days ago</span>
          <span>Today</span>
        </div>
      </div>

      <!-- Users Chart -->
      <div class="card p-6">
        <h3 class="font-semibold text-gray-900 mb-4">New Users</h3>
        <div class="h-48">
          <div v-if="chartData.users.length" class="flex items-end justify-between h-full gap-1">
            <div
              v-for="(item, index) in chartData.users"
              :key="index"
              class="flex-1 bg-blue-100 hover:bg-blue-200 rounded-t transition-colors relative group"
              :style="{ height: getBarHeight(item.count, chartData.usersMax) + '%' }"
            >
              <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                {{ item.count }} users
              </div>
            </div>
          </div>
          <div v-else class="h-full flex items-center justify-center text-gray-400">
            No data for selected period
          </div>
        </div>
        <div class="flex justify-between mt-2 text-xs text-gray-500">
          <span>{{ chartPeriod }} days ago</span>
          <span>Today</span>
        </div>
      </div>
    </div>

    <!-- Category Distribution & Top Cities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Category Distribution -->
      <div class="card p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Listings by Category</h3>
        <div class="space-y-3">
          <div
            v-for="cat in chartData.categories"
            :key="cat.category_id"
            class="flex items-center gap-3"
          >
            <div class="flex-1">
              <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-700">{{ cat.category?.name || 'Unknown' }}</span>
                <span class="font-medium">{{ cat.count }}</span>
              </div>
              <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div
                  class="h-full bg-primary-500 rounded-full"
                  :style="{ width: getCategoryWidth(cat.count) + '%' }"
                ></div>
              </div>
            </div>
          </div>
          <div v-if="!chartData.categories.length" class="text-center text-gray-400 py-4">
            No category data
          </div>
        </div>
      </div>

      <!-- Top Cities -->
      <div class="card p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Top Cities</h3>
        <div class="space-y-3">
          <div
            v-for="(city, index) in chartData.cities"
            :key="city.city"
            class="flex items-center gap-3"
          >
            <span class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-medium text-gray-600">
              {{ index + 1 }}
            </span>
            <div class="flex-1">
              <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-700">{{ city.city || 'Unknown' }}</span>
                <span class="font-medium">{{ city.count }} listings</span>
              </div>
              <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div
                  class="h-full bg-green-500 rounded-full"
                  :style="{ width: getCityWidth(city.count) + '%' }"
                ></div>
              </div>
            </div>
          </div>
          <div v-if="!chartData.cities.length" class="text-center text-gray-400 py-4">
            No city data
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Recent Listings -->
      <div class="card">
        <div class="p-4 border-b flex items-center justify-between">
          <h2 class="font-semibold text-gray-900">Recent Listings</h2>
          <router-link to="/admin/listings" class="text-sm text-primary-600 hover:underline">
            View all
          </router-link>
        </div>
        <div class="divide-y max-h-80 overflow-y-auto">
          <div
            v-for="listing in recentListings"
            :key="listing.id"
            class="p-4 flex items-center gap-3 hover:bg-gray-50 cursor-pointer"
            @click="$router.push(`/admin/listings?search=${listing.title}`)"
          >
            <div class="flex-1 min-w-0">
              <p class="font-medium text-gray-900 truncate text-sm">{{ listing.title }}</p>
              <p class="text-xs text-gray-500">by {{ listing.user?.name }}</p>
            </div>
            <span
              class="badge text-xs"
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

      <!-- Recent Users -->
      <div class="card">
        <div class="p-4 border-b flex items-center justify-between">
          <h2 class="font-semibold text-gray-900">Recent Users</h2>
          <router-link to="/admin/users" class="text-sm text-primary-600 hover:underline">
            View all
          </router-link>
        </div>
        <div class="divide-y max-h-80 overflow-y-auto">
          <div
            v-for="user in recentUsers"
            :key="user.id"
            class="p-4 flex items-center gap-3 hover:bg-gray-50"
          >
            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 font-medium">
              {{ user.name?.charAt(0)?.toUpperCase() || '?' }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-medium text-gray-900 truncate text-sm">{{ user.name }}</p>
              <p class="text-xs text-gray-500">{{ user.email }}</p>
            </div>
            <span class="text-xs text-gray-400">{{ formatDate(user.created_at) }}</span>
          </div>
          <div v-if="!recentUsers.length" class="p-8 text-center text-gray-500">
            No recent users
          </div>
        </div>
      </div>

      <!-- Recent Reports -->
      <div class="card">
        <div class="p-4 border-b flex items-center justify-between">
          <h2 class="font-semibold text-gray-900">Pending Reports</h2>
          <router-link to="/admin/reports" class="text-sm text-primary-600 hover:underline">
            View all
          </router-link>
        </div>
        <div class="divide-y max-h-80 overflow-y-auto">
          <div
            v-for="report in recentReports"
            :key="report.id"
            class="p-4 hover:bg-gray-50 cursor-pointer"
            @click="$router.push('/admin/reports')"
          >
            <div class="flex items-center justify-between mb-2">
              <span class="badge bg-red-100 text-red-700 text-xs">{{ report.reason }}</span>
              <span class="text-xs text-gray-500">{{ formatDate(report.created_at) }}</span>
            </div>
            <p class="text-xs text-gray-600 line-clamp-2">{{ report.description || 'No description provided' }}</p>
            <p class="text-xs text-gray-400 mt-1">
              By {{ report.reporter?.name }}
            </p>
          </div>
          <div v-if="!recentReports.length" class="p-8 text-center text-gray-500">
            No pending reports
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
          class="card p-4 text-center hover:shadow-md transition group"
        >
          <ClipboardDocumentCheckIcon class="w-8 h-8 text-yellow-600 mx-auto mb-2 group-hover:scale-110 transition-transform" />
          <p class="font-medium">Review Listings</p>
          <p class="text-xs text-gray-500 mt-1">{{ stats.listings?.pending || 0 }} pending</p>
        </router-link>
        <router-link
          to="/admin/users"
          class="card p-4 text-center hover:shadow-md transition group"
        >
          <UserGroupIcon class="w-8 h-8 text-blue-600 mx-auto mb-2 group-hover:scale-110 transition-transform" />
          <p class="font-medium">Manage Users</p>
          <p class="text-xs text-gray-500 mt-1">{{ stats.users?.total || 0 }} users</p>
        </router-link>
        <router-link
          to="/admin/categories"
          class="card p-4 text-center hover:shadow-md transition group"
        >
          <FolderIcon class="w-8 h-8 text-green-600 mx-auto mb-2 group-hover:scale-110 transition-transform" />
          <p class="font-medium">Categories</p>
          <p class="text-xs text-gray-500 mt-1">Organize listings</p>
        </router-link>
        <router-link
          to="/admin/settings"
          class="card p-4 text-center hover:shadow-md transition group"
        >
          <Cog6ToothIcon class="w-8 h-8 text-gray-600 mx-auto mb-2 group-hover:scale-110 transition-transform" />
          <p class="font-medium">Settings</p>
          <p class="text-xs text-gray-500 mt-1">Site configuration</p>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import {
  UsersIcon,
  TagIcon,
  ClockIcon,
  FlagIcon,
  ClipboardDocumentCheckIcon,
  UserGroupIcon,
  FolderIcon,
  Cog6ToothIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline'

dayjs.extend(relativeTime)

const stats = ref({})
const recentListings = ref([])
const recentUsers = ref([])
const recentReports = ref([])
const chartPeriod = ref('7')
const refreshing = ref(false)

const chartData = reactive({
  listings: [],
  listingsMax: 0,
  users: [],
  usersMax: 0,
  categories: [],
  cities: [],
})

const formatDate = (date) => dayjs(date).fromNow()

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    pending: 'bg-yellow-100 text-yellow-800',
    rejected: 'bg-red-100 text-red-800',
    sold: 'bg-blue-100 text-blue-800',
    expired: 'bg-gray-100 text-gray-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getBarHeight = (value, max) => {
  if (!max) return 5
  return Math.max((value / max) * 100, 5)
}

const getCategoryWidth = (count) => {
  if (!chartData.categories.length) return 0
  const max = Math.max(...chartData.categories.map(c => c.count))
  return (count / max) * 100
}

const getCityWidth = (count) => {
  if (!chartData.cities.length) return 0
  const max = Math.max(...chartData.cities.map(c => c.count))
  return (count / max) * 100
}

const fetchDashboard = async () => {
  try {
    const response = await api.get('/admin/dashboard')
    stats.value = response.data.data?.stats || response.data.stats || {}
    recentListings.value = response.data.data?.recent_listings || response.data.recent_listings || []
    recentUsers.value = response.data.data?.recent_users || response.data.recent_users || []
    recentReports.value = response.data.data?.pending_reports || response.data.pending_reports || []
  } catch (error) {
    console.error('Failed to fetch dashboard data', error)
  }
}

const fetchStats = async () => {
  try {
    const response = await api.get('/admin/stats', {
      params: { period: chartPeriod.value }
    })
    const data = response.data.data || response.data

    chartData.listings = data.listings_chart || []
    chartData.listingsMax = Math.max(...chartData.listings.map(l => l.count), 1)

    chartData.users = data.users_chart || []
    chartData.usersMax = Math.max(...chartData.users.map(u => u.count), 1)

    chartData.categories = data.category_distribution || []
    chartData.cities = data.top_cities || []
  } catch (error) {
    console.error('Failed to fetch stats', error)
  }
}

const refreshAll = async () => {
  refreshing.value = true
  await Promise.all([fetchDashboard(), fetchStats()])
  refreshing.value = false
}

onMounted(() => {
  fetchDashboard()
  fetchStats()
})
</script>
