<template>
  <div class="messages-page min-h-screen bg-gray-50">
    <div class="container-app py-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">Messages</h1>

      <!-- Loading -->
      <div v-if="loading" class="space-y-4">
        <div v-for="i in 5" :key="i" class="card p-4 flex gap-4">
          <div class="skeleton w-12 h-12 rounded-full"></div>
          <div class="flex-1">
            <div class="skeleton h-4 w-1/3 mb-2"></div>
            <div class="skeleton h-3 w-2/3"></div>
          </div>
        </div>
      </div>

      <!-- Conversations List -->
      <div v-else-if="conversations.length" class="space-y-2">
        <router-link
          v-for="conversation in conversations"
          :key="conversation.id"
          :to="`/messages/${conversation.uuid}`"
          class="card p-4 flex gap-4 hover:shadow-md transition"
        >
          <!-- User Avatar -->
          <img
            :src="getOtherUser(conversation).avatar_url"
            :alt="getOtherUser(conversation).name"
            class="w-12 h-12 rounded-full object-cover"
          />

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-1">
              <h3 class="font-semibold text-gray-900 truncate">
                {{ getOtherUser(conversation).name }}
              </h3>
              <span class="text-xs text-gray-500">
                {{ formatDate(conversation.latest_message?.created_at) }}
              </span>
            </div>

            <!-- Listing info -->
            <p class="text-sm text-gray-500 truncate mb-1">
              Re: {{ conversation.listing?.title }}
            </p>

            <!-- Last message -->
            <p class="text-sm text-gray-600 truncate">
              <span v-if="conversation.latest_message?.sender_id === currentUserId">You: </span>
              {{ conversation.latest_message?.body || 'No messages yet' }}
            </p>
          </div>

          <!-- Unread badge -->
          <div v-if="conversation.unread_count > 0" class="flex items-center">
            <span class="w-6 h-6 bg-primary-600 text-white text-xs rounded-full flex items-center justify-center">
              {{ conversation.unread_count }}
            </span>
          </div>

          <!-- Listing thumbnail -->
          <img
            v-if="conversation.listing?.primary_image"
            :src="conversation.listing.primary_image.thumbnail_url"
            class="w-16 h-16 rounded-lg object-cover hidden sm:block"
          />
        </router-link>
      </div>

      <!-- Empty State -->
      <div v-else class="card p-12 text-center">
        <ChatBubbleLeftRightIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No messages yet</h3>
        <p class="text-gray-500 mb-4">Start a conversation by contacting a seller</p>
        <router-link to="/search" class="btn-primary">
          Browse Listings
        </router-link>
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
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import { ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline'

dayjs.extend(relativeTime)

const authStore = useAuthStore()

const loading = ref(true)
const loadingMore = ref(false)
const conversations = ref([])
const currentPage = ref(1)
const lastPage = ref(1)

const currentUserId = computed(() => authStore.user?.id)
const hasMore = computed(() => currentPage.value < lastPage.value)

const getOtherUser = (conversation) => {
  return conversation.buyer_id === currentUserId.value
    ? conversation.seller
    : conversation.buyer
}

const formatDate = (date) => {
  if (!date) return ''
  return dayjs(date).fromNow()
}

const fetchConversations = async (append = false) => {
  if (append) {
    loadingMore.value = true
  } else {
    loading.value = true
  }

  try {
    const response = await api.get('/conversations', {
      params: { page: append ? currentPage.value + 1 : 1 }
    })

    if (append) {
      conversations.value = [...conversations.value, ...response.data.data]
    } else {
      conversations.value = response.data.data
    }

    currentPage.value = response.data.meta.current_page
    lastPage.value = response.data.meta.last_page
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const loadMore = () => {
  fetchConversations(true)
}

onMounted(() => {
  fetchConversations()
})
</script>
