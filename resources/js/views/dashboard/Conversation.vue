<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Header -->
    <header class="bg-white border-b sticky top-0 z-10">
      <div class="container-app py-3 flex items-center gap-4">
        <button @click="$router.back()" class="p-2 hover:bg-gray-100 rounded-lg">
          <ArrowLeftIcon class="w-5 h-5" />
        </button>

        <div v-if="conversation" class="flex items-center gap-3 flex-1 min-w-0">
          <img
            :src="otherUser?.avatar_url"
            :alt="otherUser?.name"
            class="w-10 h-10 rounded-full object-cover"
          />
          <div class="min-w-0">
            <h1 class="font-semibold text-gray-900 truncate">{{ otherUser?.name }}</h1>
            <p class="text-sm text-gray-500 truncate">{{ conversation.listing?.title }}</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2">
          <router-link
            :to="`/listing/${conversation?.listing?.slug}`"
            class="p-2 hover:bg-gray-100 rounded-lg"
            title="View listing"
          >
            <EyeIcon class="w-5 h-5 text-gray-600" />
          </router-link>
          <button
            @click="showOptionsMenu = !showOptionsMenu"
            class="p-2 hover:bg-gray-100 rounded-lg relative"
          >
            <EllipsisVerticalIcon class="w-5 h-5 text-gray-600" />

            <!-- Options dropdown -->
            <div
              v-if="showOptionsMenu"
              class="absolute right-0 top-full mt-1 w-48 bg-white rounded-lg shadow-lg border z-50"
            >
              <button
                @click="blockUser"
                class="w-full px-4 py-2 text-left text-red-600 hover:bg-red-50"
              >
                Block User
              </button>
              <button
                @click="deleteConversation"
                class="w-full px-4 py-2 text-left text-red-600 hover:bg-red-50"
              >
                Delete Conversation
              </button>
            </div>
          </button>
        </div>
      </div>
    </header>

    <!-- Messages -->
    <div
      ref="messagesContainer"
      class="flex-1 overflow-y-auto p-4 space-y-4"
    >
      <div v-if="loading" class="flex justify-center py-8">
        <div class="w-8 h-8 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin"></div>
      </div>

      <template v-else>
        <div
          v-for="message in messages"
          :key="message.id"
          class="flex"
          :class="message.sender_id === currentUserId ? 'justify-end' : 'justify-start'"
        >
          <div
            class="max-w-[75%] rounded-2xl px-4 py-2"
            :class="message.sender_id === currentUserId
              ? 'bg-primary-600 text-white rounded-br-md'
              : 'bg-white text-gray-900 rounded-bl-md shadow'"
          >
            <!-- Offer message -->
            <div v-if="message.type === 'offer'" class="mb-2">
              <p class="text-sm opacity-75">Made an offer</p>
              <p class="text-xl font-bold">₹{{ message.offer_amount }}</p>
              <div
                v-if="message.offer_status === 'pending' && message.sender_id !== currentUserId"
                class="flex gap-2 mt-2"
              >
                <button
                  @click="respondToOffer(message.id, 'accept')"
                  class="px-3 py-1 bg-green-500 text-white rounded text-sm"
                >
                  Accept
                </button>
                <button
                  @click="respondToOffer(message.id, 'reject')"
                  class="px-3 py-1 bg-red-500 text-white rounded text-sm"
                >
                  Reject
                </button>
              </div>
              <p
                v-else-if="message.offer_status !== 'pending'"
                class="text-sm mt-1"
                :class="message.offer_status === 'accepted' ? 'text-green-300' : 'text-red-300'"
              >
                Offer {{ message.offer_status }}
              </p>
            </div>

            <!-- Text message -->
            <p>{{ message.body }}</p>

            <!-- Timestamp -->
            <p
              class="text-xs mt-1"
              :class="message.sender_id === currentUserId ? 'text-primary-200' : 'text-gray-400'"
            >
              {{ formatTime(message.created_at) }}
              <span v-if="message.sender_id === currentUserId && message.is_read">
                · Read
              </span>
            </p>
          </div>
        </div>
      </template>
    </div>

    <!-- Input -->
    <div class="bg-white border-t p-4">
      <div class="container-app">
        <form @submit.prevent="sendMessage" class="flex gap-2">
          <!-- Make Offer button -->
          <button
            type="button"
            @click="showOfferModal = true"
            class="p-3 text-primary-600 hover:bg-primary-50 rounded-lg"
            title="Make an offer"
          >
            <CurrencyRupeeIcon class="w-6 h-6" />
          </button>

          <input
            v-model="newMessage"
            type="text"
            placeholder="Type a message..."
            class="flex-1 input"
            :disabled="sending"
          />

          <button
            type="submit"
            :disabled="!newMessage.trim() || sending"
            class="btn-primary px-6"
          >
            <PaperAirplaneIcon class="w-5 h-5" />
          </button>
        </form>
      </div>
    </div>

    <!-- Make Offer Modal -->
    <div v-if="showOfferModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="showOfferModal = false"></div>
      <div class="relative bg-white rounded-xl p-6 w-full max-w-sm">
        <h3 class="text-lg font-semibold mb-4">Make an Offer</h3>
        <p class="text-sm text-gray-500 mb-4">
          Listing price: {{ conversation?.listing?.formatted_price }}
        </p>
        <div class="relative mb-4">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">₹</span>
          <input
            v-model="offerAmount"
            type="number"
            min="1"
            class="input pl-8"
            placeholder="Your offer"
          />
        </div>
        <div class="flex gap-2">
          <button @click="showOfferModal = false" class="btn-secondary flex-1">
            Cancel
          </button>
          <button @click="makeOffer" class="btn-primary flex-1" :disabled="!offerAmount">
            Send Offer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import dayjs from 'dayjs'
import {
  ArrowLeftIcon,
  EyeIcon,
  EllipsisVerticalIcon,
  PaperAirplaneIcon,
  CurrencyRupeeIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const loading = ref(true)
const sending = ref(false)
const conversation = ref(null)
const messages = ref([])
const newMessage = ref('')
const showOptionsMenu = ref(false)
const showOfferModal = ref(false)
const offerAmount = ref('')
const messagesContainer = ref(null)

const currentUserId = computed(() => authStore.user?.id)
const otherUser = computed(() => {
  if (!conversation.value) return null
  return conversation.value.buyer_id === currentUserId.value
    ? conversation.value.seller
    : conversation.value.buyer
})

const formatTime = (date) => {
  return dayjs(date).format('h:mm A')
}

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

const fetchConversation = async () => {
  try {
    const response = await api.get(`/conversations/${route.params.uuid}`)
    conversation.value = response.data.data.conversation
    messages.value = response.data.data.messages.data || []
    scrollToBottom()
  } catch (error) {
    toast.error('Failed to load conversation')
    router.push('/messages')
  } finally {
    loading.value = false
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || sending.value) return

  sending.value = true
  const messageText = newMessage.value
  newMessage.value = ''

  try {
    const response = await api.post(`/conversations/${route.params.uuid}/messages`, {
      message: messageText,
      type: 'text',
    })
    messages.value.push(response.data.data)
    scrollToBottom()
  } catch (error) {
    newMessage.value = messageText
    toast.error('Failed to send message')
  } finally {
    sending.value = false
  }
}

const makeOffer = async () => {
  if (!offerAmount.value) return

  try {
    const response = await api.post(`/conversations/${route.params.uuid}/messages`, {
      message: `I'd like to offer ₹${offerAmount.value}`,
      type: 'offer',
      offer_amount: offerAmount.value,
    })
    messages.value.push(response.data.data)
    showOfferModal.value = false
    offerAmount.value = ''
    scrollToBottom()
    toast.success('Offer sent!')
  } catch (error) {
    toast.error('Failed to send offer')
  }
}

const respondToOffer = async (messageId, action) => {
  try {
    await api.post(`/messages/${messageId}/offer-response`, { action })
    // Update local message
    const msg = messages.value.find(m => m.id === messageId)
    if (msg) {
      msg.offer_status = action === 'accept' ? 'accepted' : 'rejected'
    }
    toast.success(`Offer ${action}ed`)
  } catch (error) {
    toast.error('Failed to respond to offer')
  }
}

const blockUser = async () => {
  try {
    await api.post(`/conversations/${route.params.uuid}/block`)
    toast.success('User blocked')
    router.push('/messages')
  } catch (error) {
    toast.error('Failed to block user')
  }
}

const deleteConversation = async () => {
  try {
    await api.delete(`/conversations/${route.params.uuid}`)
    toast.success('Conversation deleted')
    router.push('/messages')
  } catch (error) {
    toast.error('Failed to delete conversation')
  }
}

onMounted(() => {
  fetchConversation()
})
</script>
