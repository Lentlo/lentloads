<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Conversations</h1>
      <span class="text-sm text-gray-500">Monitor all user conversations</span>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-6">
      <div class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search by user name, email or listing..."
            class="input"
            @input="debouncedFetch"
          />
        </div>
        <select v-model="filters.blocked" @change="fetchConversations" class="input w-40">
          <option value="">All Status</option>
          <option value="false">Active</option>
          <option value="true">Blocked</option>
        </select>
        <input
          v-model="filters.from_date"
          type="date"
          class="input w-40"
          @change="fetchConversations"
        />
        <input
          v-model="filters.to_date"
          type="date"
          class="input w-40"
          @change="fetchConversations"
        />
      </div>
    </div>

    <!-- Conversations Table -->
    <div class="card overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin h-8 w-8 border-4 border-primary-500 border-t-transparent rounded-full mx-auto"></div>
      </div>

      <table v-else class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Buyer</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Seller</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Listing</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Messages</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Last Message</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="conv in conversations" :key="conv.id" class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-sm font-medium">
                  {{ conv.buyer?.name?.charAt(0)?.toUpperCase() || '?' }}
                </div>
                <div>
                  <p class="font-medium text-sm">{{ conv.buyer?.name }}</p>
                  <p class="text-xs text-gray-500">{{ conv.buyer?.email }}</p>
                </div>
              </div>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-600 text-sm font-medium">
                  {{ conv.seller?.name?.charAt(0)?.toUpperCase() || '?' }}
                </div>
                <div>
                  <p class="font-medium text-sm">{{ conv.seller?.name }}</p>
                  <p class="text-xs text-gray-500">{{ conv.seller?.email }}</p>
                </div>
              </div>
            </td>
            <td class="px-4 py-3">
              <p class="text-sm font-medium truncate max-w-[200px]">{{ conv.listing?.title || 'Deleted' }}</p>
            </td>
            <td class="px-4 py-3">
              <span class="badge bg-gray-100 text-gray-800">{{ conv.messages_count }} messages</span>
            </td>
            <td class="px-4 py-3">
              <p class="text-sm text-gray-600 truncate max-w-[150px]">{{ conv.latest_message?.body || '-' }}</p>
              <p class="text-xs text-gray-400">{{ formatDate(conv.updated_at) }}</p>
            </td>
            <td class="px-4 py-3">
              <span
                class="badge"
                :class="conv.is_blocked ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
              >
                {{ conv.is_blocked ? 'Blocked' : 'Active' }}
              </span>
            </td>
            <td class="px-4 py-3">
              <button
                @click="viewConversation(conv)"
                class="btn-sm bg-primary-100 text-primary-700"
              >
                View Chat
              </button>
            </td>
          </tr>
          <tr v-if="!conversations.length">
            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
              No conversations found
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

    <!-- View Conversation Modal -->
    <div v-if="selectedConversation" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="selectedConversation = null"></div>
      <div class="relative bg-white rounded-xl w-full max-w-2xl max-h-[80vh] flex flex-col">
        <div class="p-4 border-b flex items-center justify-between">
          <div>
            <h3 class="font-semibold">Conversation</h3>
            <p class="text-sm text-gray-500">
              {{ selectedConversation.buyer?.name }} &harr; {{ selectedConversation.seller?.name }}
            </p>
          </div>
          <button @click="selectedConversation = null" class="p-2 hover:bg-gray-100 rounded-lg">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3" ref="messagesContainer">
          <div v-if="loadingMessages" class="text-center py-4">
            <div class="animate-spin h-6 w-6 border-2 border-primary-500 border-t-transparent rounded-full mx-auto"></div>
          </div>
          <template v-else>
            <div
              v-for="msg in messages"
              :key="msg.id"
              class="flex"
              :class="msg.sender_id === selectedConversation.buyer_id ? 'justify-start' : 'justify-end'"
            >
              <div
                class="max-w-[70%] rounded-lg px-4 py-2"
                :class="msg.sender_id === selectedConversation.buyer_id
                  ? 'bg-gray-100'
                  : 'bg-primary-100'"
              >
                <p class="text-xs font-medium mb-1"
                  :class="msg.sender_id === selectedConversation.buyer_id ? 'text-blue-600' : 'text-green-600'"
                >
                  {{ msg.sender?.name }}
                </p>
                <p class="text-sm">{{ msg.body }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ formatDate(msg.created_at) }}</p>
              </div>
            </div>
          </template>
        </div>

        <div class="p-4 border-t bg-gray-50 rounded-b-xl">
          <p class="text-xs text-gray-500 text-center">
            This is a read-only view for monitoring purposes
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import api from '@/services/api'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import { XMarkIcon } from '@heroicons/vue/24/outline'
// Debounce utility
const debounce = (fn, delay) => {
  let timeout
  return (...args) => {
    clearTimeout(timeout)
    timeout = setTimeout(() => fn(...args), delay)
  }
}

dayjs.extend(relativeTime)

const conversations = ref([])
const loading = ref(true)
const loadingMessages = ref(false)
const selectedConversation = ref(null)
const messages = ref([])
const messagesContainer = ref(null)

const filters = reactive({
  search: '',
  blocked: '',
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

const formatDate = (date) => dayjs(date).fromNow()

const fetchConversations = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.current_page,
      ...filters,
    }
    const response = await api.get('/admin/conversations', { params })
    const data = response.data

    conversations.value = data.data || []
    Object.assign(pagination, {
      current_page: data.meta?.current_page || data.current_page || 1,
      last_page: data.meta?.last_page || data.last_page || 1,
      from: data.meta?.from || data.from || 0,
      to: data.meta?.to || data.to || 0,
      total: data.meta?.total || data.total || 0,
    })
  } catch (error) {
    console.error('Failed to fetch conversations', error)
  } finally {
    loading.value = false
  }
}

const debouncedFetch = debounce(() => {
  pagination.current_page = 1
  fetchConversations()
}, 300)

const changePage = (page) => {
  pagination.current_page = page
  fetchConversations()
}

const viewConversation = async (conv) => {
  selectedConversation.value = conv
  loadingMessages.value = true
  messages.value = []

  try {
    const response = await api.get(`/admin/conversations/${conv.id}/messages`)
    messages.value = (response.data.data || []).reverse()

    await nextTick()
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  } catch (error) {
    console.error('Failed to fetch messages', error)
  } finally {
    loadingMessages.value = false
  }
}

onMounted(() => {
  fetchConversations()
})
</script>
