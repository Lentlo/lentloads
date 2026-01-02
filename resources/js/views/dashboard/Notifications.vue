<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container-app py-4 max-w-2xl">
      <!-- Header -->
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold text-gray-900">Notifications</h1>
        <button
          v-if="notifications.length && hasUnread"
          @click="markAllAsRead"
          class="text-sm text-primary-600 hover:underline"
        >
          Mark all as read
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading && !notifications.length" class="space-y-3">
        <div v-for="i in 5" :key="i" class="card p-4">
          <div class="flex gap-3">
            <div class="skeleton w-10 h-10 rounded-full"></div>
            <div class="flex-1">
              <div class="skeleton h-4 w-3/4 mb-2"></div>
              <div class="skeleton h-3 w-1/2"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Notifications List -->
      <div v-else-if="notifications.length" class="space-y-2">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          @click="handleNotificationClick(notification)"
          class="card p-4 cursor-pointer hover:bg-gray-50 transition"
          :class="!notification.read_at ? 'border-l-4 border-l-primary-500 bg-primary-50/30' : ''"
        >
          <div class="flex gap-3">
            <!-- Icon -->
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
              :class="getIconClass(notification.data.type)"
            >
              <component :is="getIcon(notification.data.type)" class="w-5 h-5" />
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <p class="text-sm text-gray-900" :class="!notification.read_at ? 'font-medium' : ''">
                {{ notification.data.message }}
              </p>
              <p class="text-xs text-gray-500 mt-1">{{ formatTime(notification.created_at) }}</p>
            </div>

            <!-- Actions -->
            <button
              @click.stop="deleteNotification(notification.id)"
              class="p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded"
            >
              <XMarkIcon class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Load More -->
        <div v-if="hasMore" class="text-center pt-4">
          <button @click="loadMore" :disabled="loading" class="btn-secondary">
            {{ loading ? 'Loading...' : 'Load More' }}
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="card p-12 text-center">
        <BellIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications yet</h3>
        <p class="text-gray-500">We'll notify you about your listings and messages</p>
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
  XMarkIcon,
  CheckCircleIcon,
  XCircleIcon,
  ClockIcon,
  ChatBubbleLeftIcon,
  HeartIcon,
  TagIcon,
} from '@heroicons/vue/24/outline'

dayjs.extend(relativeTime)

const router = useRouter()

const loading = ref(true)
const notifications = ref([])
const currentPage = ref(1)
const lastPage = ref(1)

const hasMore = computed(() => currentPage.value < lastPage.value)
const hasUnread = computed(() => notifications.value.some(n => !n.read_at))

const formatTime = (date) => dayjs(date).fromNow()

const getIcon = (type) => {
  const icons = {
    'listing_approved': CheckCircleIcon,
    'listing_rejected': XCircleIcon,
    'listing_pending': ClockIcon,
    'listing_expired': ClockIcon,
    'new_message': ChatBubbleLeftIcon,
    'new_offer': TagIcon,
    'offer_accepted': CheckCircleIcon,
    'offer_rejected': XCircleIcon,
    'new_favorite': HeartIcon,
  }
  return icons[type] || BellIcon
}

const getIconClass = (type) => {
  const classes = {
    'listing_approved': 'bg-green-100 text-green-600',
    'listing_rejected': 'bg-red-100 text-red-600',
    'listing_pending': 'bg-yellow-100 text-yellow-600',
    'listing_expired': 'bg-gray-100 text-gray-600',
    'new_message': 'bg-blue-100 text-blue-600',
    'new_offer': 'bg-purple-100 text-purple-600',
    'offer_accepted': 'bg-green-100 text-green-600',
    'offer_rejected': 'bg-red-100 text-red-600',
    'new_favorite': 'bg-pink-100 text-pink-600',
  }
  return classes[type] || 'bg-gray-100 text-gray-600'
}

const fetchNotifications = async (append = false) => {
  loading.value = true
  try {
    const response = await api.get('/notifications', {
      params: { page: append ? currentPage.value + 1 : 1 }
    })
    const data = response.data.data
    if (append) {
      notifications.value = [...notifications.value, ...data]
    } else {
      notifications.value = data
    }
    currentPage.value = response.data.meta.current_page
    lastPage.value = response.data.meta.last_page
  } finally {
    loading.value = false
  }
}

const loadMore = () => fetchNotifications(true)

const handleNotificationClick = async (notification) => {
  // Mark as read
  if (!notification.read_at) {
    try {
      await api.post(`/notifications/${notification.id}/read`)
      notification.read_at = new Date().toISOString()
    } catch (e) {}
  }

  // Navigate based on type
  const data = notification.data
  if (data.listing_slug) {
    router.push(`/listing/${data.listing_slug}`)
  } else if (data.conversation_uuid) {
    router.push(`/messages/${data.conversation_uuid}`)
  }
}

const markAllAsRead = async () => {
  try {
    await api.post('/notifications/read-all')
    notifications.value.forEach(n => n.read_at = new Date().toISOString())
  } catch (e) {}
}

const deleteNotification = async (id) => {
  try {
    await api.delete(`/notifications/${id}`)
    notifications.value = notifications.value.filter(n => n.id !== id)
  } catch (e) {}
}

onMounted(() => fetchNotifications())
</script>
