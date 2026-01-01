<template>
  <div class="min-h-screen bg-gray-50 py-6">
    <div class="container-app max-w-2xl">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
        <button
          v-if="notifications.length"
          @click="markAllRead"
          class="text-sm text-primary-600 hover:underline"
        >
          Mark all as read
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-2">
        <div v-for="i in 5" :key="i" class="card p-4 flex gap-4">
          <div class="skeleton w-10 h-10 rounded-full"></div>
          <div class="flex-1">
            <div class="skeleton h-4 w-3/4 mb-2"></div>
            <div class="skeleton h-3 w-1/4"></div>
          </div>
        </div>
      </div>

      <!-- Notifications List -->
      <div v-else-if="notifications.length" class="space-y-2">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          class="card p-4 flex gap-4 cursor-pointer hover:shadow-md transition"
          :class="!notification.read_at ? 'bg-primary-50 border-primary-200' : ''"
          @click="handleNotificationClick(notification)"
        >
          <!-- Icon -->
          <div
            class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
            :class="getIconClass(notification.data.type)"
          >
            <component :is="getIcon(notification.data.type)" class="w-5 h-5" />
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <p class="text-gray-900">{{ notification.data.message }}</p>
            <p class="text-sm text-gray-500 mt-1">{{ formatDate(notification.created_at) }}</p>
          </div>

          <!-- Unread indicator -->
          <div v-if="!notification.read_at" class="w-2 h-2 bg-primary-600 rounded-full"></div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="card p-12 text-center">
        <BellIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications</h3>
        <p class="text-gray-500">You're all caught up!</p>
      </div>

      <!-- Load More -->
      <div v-if="hasMore" class="mt-6 text-center">
        <button @click="loadMore" :disabled="loadingMore" class="btn-secondary">
          {{ loadingMore ? 'Loading...' : 'Load More' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import {
  BellIcon,
  ChatBubbleLeftIcon,
  HeartIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  ShoppingBagIcon,
} from '@heroicons/vue/24/outline'

dayjs.extend(relativeTime)

const router = useRouter()

const loading = ref(true)
const loadingMore = ref(false)
const notifications = ref([])
const currentPage = ref(1)
const lastPage = ref(1)

const hasMore = computed(() => currentPage.value < lastPage.value)

const formatDate = (date) => dayjs(date).fromNow()

const getIcon = (type) => {
  const icons = {
    message: ChatBubbleLeftIcon,
    favorite: HeartIcon,
    listing_approved: CheckCircleIcon,
    listing_rejected: ExclamationCircleIcon,
    sold: ShoppingBagIcon,
  }
  return icons[type] || BellIcon
}

const getIconClass = (type) => {
  const classes = {
    message: 'bg-blue-100 text-blue-600',
    favorite: 'bg-red-100 text-red-600',
    listing_approved: 'bg-green-100 text-green-600',
    listing_rejected: 'bg-red-100 text-red-600',
    sold: 'bg-purple-100 text-purple-600',
  }
  return classes[type] || 'bg-gray-100 text-gray-600'
}

const fetchNotifications = async (append = false) => {
  if (append) {
    loadingMore.value = true
  } else {
    loading.value = true
  }

  try {
    const response = await api.get('/notifications', {
      params: { page: append ? currentPage.value + 1 : 1 }
    })

    if (append) {
      notifications.value = [...notifications.value, ...response.data.data]
    } else {
      notifications.value = response.data.data
    }

    currentPage.value = response.data.meta.current_page
    lastPage.value = response.data.meta.last_page
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const handleNotificationClick = async (notification) => {
  // Mark as read
  if (!notification.read_at) {
    await api.post(`/notifications/${notification.id}/read`)
    notification.read_at = new Date().toISOString()
  }

  // Navigate based on type
  if (notification.data.url) {
    router.push(notification.data.url)
  }
}

const markAllRead = async () => {
  try {
    await api.post('/notifications/read-all')
    notifications.value = notifications.value.map(n => ({
      ...n,
      read_at: n.read_at || new Date().toISOString()
    }))
  } catch (error) {
    console.error('Failed to mark all as read')
  }
}

const loadMore = () => {
  fetchNotifications(true)
}

onMounted(() => {
  fetchNotifications()
})
</script>
