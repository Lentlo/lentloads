<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Contact Views</h1>
      <span class="text-sm text-gray-500">Track who viewed whose contact details</span>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="card p-4">
        <p class="text-sm text-gray-500">Total Views</p>
        <p class="text-2xl font-bold">{{ stats.total_views || 0 }}</p>
      </div>
      <div class="card p-4">
        <p class="text-sm text-gray-500">Today</p>
        <p class="text-2xl font-bold text-green-600">{{ stats.today || 0 }}</p>
      </div>
      <div class="card p-4">
        <p class="text-sm text-gray-500">This Week</p>
        <p class="text-2xl font-bold text-blue-600">{{ stats.this_week || 0 }}</p>
      </div>
      <div class="card p-4">
        <p class="text-sm text-gray-500">This Month</p>
        <p class="text-2xl font-bold text-purple-600">{{ stats.this_month || 0 }}</p>
      </div>
    </div>

    <!-- Top Users -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <div class="card p-4">
        <h3 class="font-semibold mb-3">Most Viewed Sellers</h3>
        <div class="space-y-2">
          <div v-for="(item, index) in topOwners" :key="item.owner_id" class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-medium">
                {{ index + 1 }}
              </span>
              <div>
                <p class="text-sm font-medium">{{ item.owner?.name || 'Unknown' }}</p>
                <p class="text-xs text-gray-500">{{ item.owner?.email }}</p>
              </div>
            </div>
            <span class="badge bg-green-100 text-green-800">{{ item.view_count }} views</span>
          </div>
          <p v-if="!topOwners.length" class="text-sm text-gray-500 text-center py-2">No data</p>
        </div>
      </div>

      <div class="card p-4">
        <h3 class="font-semibold mb-3">Most Active Viewers</h3>
        <div class="space-y-2">
          <div v-for="(item, index) in topViewers" :key="item.viewer_id" class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center text-xs font-medium">
                {{ index + 1 }}
              </span>
              <div>
                <p class="text-sm font-medium">{{ item.viewer?.name || 'Unknown' }}</p>
                <p class="text-xs text-gray-500">{{ item.viewer?.email }}</p>
              </div>
            </div>
            <span class="badge bg-blue-100 text-blue-800">{{ item.view_count }} views</span>
          </div>
          <p v-if="!topViewers.length" class="text-sm text-gray-500 text-center py-2">No data</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-6">
      <div class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search by viewer or owner name/email..."
            class="input"
            @input="debouncedFetch"
          />
        </div>
        <input
          v-model="filters.from_date"
          type="date"
          class="input w-40"
          @change="fetchViews"
        />
        <input
          v-model="filters.to_date"
          type="date"
          class="input w-40"
          @change="fetchViews"
        />
      </div>
    </div>

    <!-- Views Table -->
    <div class="card overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin h-8 w-8 border-4 border-primary-500 border-t-transparent rounded-full mx-auto"></div>
      </div>

      <table v-else class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Viewer</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Viewed Contact Of</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Listing</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Type</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="view in views" :key="view.id" class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-sm font-medium">
                  {{ view.viewer?.name?.charAt(0)?.toUpperCase() || '?' }}
                </div>
                <div>
                  <p class="font-medium text-sm">{{ view.viewer?.name }}</p>
                  <p class="text-xs text-gray-500">{{ view.viewer?.email }}</p>
                </div>
              </div>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-600 text-sm font-medium">
                  {{ view.owner?.name?.charAt(0)?.toUpperCase() || '?' }}
                </div>
                <div>
                  <p class="font-medium text-sm">{{ view.owner?.name }}</p>
                  <p class="text-xs text-gray-500">{{ view.owner?.phone || view.owner?.email }}</p>
                </div>
              </div>
            </td>
            <td class="px-4 py-3">
              <p class="text-sm font-medium truncate max-w-[200px]">{{ view.listing?.title || 'Deleted' }}</p>
            </td>
            <td class="px-4 py-3">
              <span
                class="badge"
                :class="{
                  'bg-blue-100 text-blue-800': view.contact_type === 'phone',
                  'bg-green-100 text-green-800': view.contact_type === 'whatsapp',
                  'bg-purple-100 text-purple-800': view.contact_type === 'email',
                }"
              >
                {{ view.contact_type }}
              </span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-600">
              {{ formatDate(view.created_at) }}
            </td>
          </tr>
          <tr v-if="!views.length">
            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
              No contact views found
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="p-4 border-t flex items-center justify-between">
        <p class="text-sm text-gray-600">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }}
        </p>
        <div class="flex gap-2">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="btn-sm"
          >
            Previous
          </button>
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="btn-sm"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
// Debounce utility
const debounce = (fn, delay) => {
  let timeout
  return (...args) => {
    clearTimeout(timeout)
    timeout = setTimeout(() => fn(...args), delay)
  }
}

dayjs.extend(relativeTime)

const views = ref([])
const loading = ref(true)
const stats = ref({})
const topOwners = ref([])
const topViewers = ref([])

const filters = reactive({
  search: '',
  from_date: '',
  to_date: '',
})

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  total: 0,
})

const formatDate = (date) => dayjs(date).format('MMM D, YYYY h:mm A')

const fetchStats = async () => {
  try {
    const response = await api.get('/admin/contact-views/stats')
    const data = response.data.data || response.data
    stats.value = data.stats || {}
    topOwners.value = data.top_owners || []
    topViewers.value = data.top_viewers || []
  } catch (error) {
    console.error('Failed to fetch stats', error)
  }
}

const fetchViews = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.current_page,
      ...filters,
    }
    const response = await api.get('/admin/contact-views', { params })
    const data = response.data

    views.value = data.data || []
    Object.assign(pagination, {
      current_page: data.meta?.current_page || data.current_page || 1,
      last_page: data.meta?.last_page || data.last_page || 1,
      from: data.meta?.from || data.from || 0,
      to: data.meta?.to || data.to || 0,
      total: data.meta?.total || data.total || 0,
    })
  } catch (error) {
    console.error('Failed to fetch contact views', error)
  } finally {
    loading.value = false
  }
}

const debouncedFetch = debounce(() => {
  pagination.current_page = 1
  fetchViews()
}, 300)

const changePage = (page) => {
  pagination.current_page = page
  fetchViews()
}

onMounted(() => {
  fetchStats()
  fetchViews()
})
</script>
