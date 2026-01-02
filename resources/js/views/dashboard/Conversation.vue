<template>
  <div class="conversation-page">
    <!-- Sticky Header -->
    <header class="conversation-header">
      <div class="flex items-center gap-3 px-4 py-3">
        <button @click="$router.push('/messages')" class="p-2 -ml-2 hover:bg-gray-100 rounded-full">
          <ArrowLeftIcon class="w-5 h-5 text-gray-600" />
        </button>

        <div v-if="conversation" class="flex items-center gap-3 flex-1 min-w-0">
          <div class="relative">
            <img
              :src="otherUser?.avatar_url || '/images/default-avatar.png'"
              :alt="otherUser?.name"
              class="w-10 h-10 rounded-full object-cover bg-gray-200"
            />
            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
          </div>
          <div class="min-w-0 flex-1">
            <h1 class="font-semibold text-gray-900 truncate">{{ otherUser?.name }}</h1>
            <p class="text-xs text-green-600">Online</p>
          </div>
        </div>

        <div class="flex items-center gap-1">
          <a
            v-if="otherUser?.phone"
            :href="`tel:${otherUser.phone}`"
            class="p-2 hover:bg-gray-100 rounded-full"
          >
            <PhoneIcon class="w-5 h-5 text-gray-600" />
          </a>
          <div class="relative">
            <button
              @click="showOptionsMenu = !showOptionsMenu"
              class="p-2 hover:bg-gray-100 rounded-full"
            >
              <EllipsisVerticalIcon class="w-5 h-5 text-gray-600" />
            </button>

            <div
              v-if="showOptionsMenu"
              class="absolute right-0 top-full mt-1 w-48 bg-white rounded-xl shadow-lg border z-50"
            >
              <router-link
                :to="`/listing/${conversation?.listing?.slug}`"
                class="block px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-t-xl"
                @click="showOptionsMenu = false"
              >
                View Listing
              </router-link>
              <router-link
                :to="`/user/${otherUser?.id}`"
                class="block px-4 py-3 text-gray-700 hover:bg-gray-50"
                @click="showOptionsMenu = false"
              >
                View Profile
              </router-link>
              <button
                @click="blockUser"
                class="w-full px-4 py-3 text-left text-red-600 hover:bg-red-50"
              >
                Block User
              </button>
              <button
                @click="deleteConversation"
                class="w-full px-4 py-3 text-left text-red-600 hover:bg-red-50 rounded-b-xl"
              >
                Delete Chat
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Product Card -->
      <div v-if="conversation?.listing" class="px-4 pb-3">
        <router-link
          :to="`/listing/${conversation.listing.slug}`"
          class="flex items-center gap-3 p-2 bg-gray-50 rounded-xl hover:bg-gray-100 transition"
        >
          <img
            :src="conversation.listing.primary_image_url"
            :alt="conversation.listing.title"
            class="w-12 h-12 rounded-lg object-cover bg-gray-200"
          />
          <div class="flex-1 min-w-0">
            <p class="font-medium text-gray-900 truncate text-sm">{{ conversation.listing.title }}</p>
            <p class="text-primary-600 font-bold text-sm">{{ conversation.listing.formatted_price || '₹' + conversation.listing.price }}</p>
          </div>
          <ChevronRightIcon class="w-5 h-5 text-gray-400 flex-shrink-0" />
        </router-link>
      </div>
    </header>

    <!-- Overlay to close menu -->
    <div v-if="showOptionsMenu" class="fixed inset-0 z-40" @click="showOptionsMenu = false"></div>

    <!-- Messages Area -->
    <div ref="messagesContainer" class="messages-area">
      <div v-if="loading" class="flex justify-center py-8">
        <div class="w-8 h-8 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin"></div>
      </div>

      <template v-else>
        <div class="flex justify-center mb-4">
          <span class="px-3 py-1 bg-gray-200 rounded-full text-xs text-gray-600">
            {{ formatDate(messages[0]?.created_at) }}
          </span>
        </div>

        <div
          v-for="message in messages"
          :key="message.id"
          class="flex mb-3"
          :class="message.sender_id === currentUserId ? 'justify-end' : 'justify-start'"
        >
          <div
            class="max-w-[80%] rounded-2xl px-4 py-2 shadow-sm"
            :class="message.sender_id === currentUserId
              ? 'bg-primary-600 text-white rounded-br-sm'
              : 'bg-white text-gray-900 rounded-bl-sm'"
          >
            <div v-if="message.type === 'offer'" class="mb-2">
              <div class="flex items-center gap-2 mb-1">
                <CurrencyRupeeIcon class="w-4 h-4" />
                <span class="text-sm opacity-80">Offer</span>
              </div>
              <p class="text-2xl font-bold">₹{{ message.offer_amount?.toLocaleString() }}</p>
              <div
                v-if="message.offer_status === 'pending' && message.sender_id !== currentUserId"
                class="flex gap-2 mt-3"
              >
                <button
                  @click="respondToOffer(message.id, 'accept')"
                  class="flex-1 px-3 py-2 bg-green-500 text-white rounded-lg text-sm font-medium"
                >
                  Accept
                </button>
                <button
                  @click="respondToOffer(message.id, 'reject')"
                  class="flex-1 px-3 py-2 bg-white/20 text-white rounded-lg text-sm font-medium"
                >
                  Decline
                </button>
              </div>
              <p
                v-else-if="message.offer_status !== 'pending'"
                class="text-sm mt-2 font-medium"
                :class="message.sender_id === currentUserId
                  ? (message.offer_status === 'accepted' ? 'text-green-200' : 'text-red-200')
                  : (message.offer_status === 'accepted' ? 'text-green-600' : 'text-red-600')"
              >
                {{ message.offer_status === 'accepted' ? 'Accepted' : 'Declined' }}
              </p>
            </div>

            <p class="whitespace-pre-wrap break-words">{{ message.body }}</p>

            <p
              class="text-[10px] mt-1 flex items-center gap-1"
              :class="message.sender_id === currentUserId ? 'text-primary-200 justify-end' : 'text-gray-400'"
            >
              {{ formatTime(message.created_at) }}
              <span v-if="message.sender_id === currentUserId" class="flex items-center">
                <CheckIcon v-if="message.is_read" class="w-3 h-3" />
                <CheckIcon v-if="message.is_read" class="w-3 h-3 -ml-1" />
                <CheckIcon v-else class="w-3 h-3" />
              </span>
            </p>
          </div>
        </div>

        <div v-if="messages.length === 0" class="text-center py-8 text-gray-500">
          <p>No messages yet. Say hello!</p>
        </div>
      </template>
    </div>

    <!-- Fixed Input Area -->
    <div class="conversation-input">
      <form @submit.prevent="sendMessage" class="flex items-center gap-2">
        <button
          type="button"
          @click="showOfferModal = true"
          class="w-11 h-11 text-primary-600 hover:bg-primary-50 rounded-full flex-shrink-0 flex items-center justify-center"
          title="Make an offer"
        >
          <CurrencyRupeeIcon class="w-6 h-6" />
        </button>

        <div class="flex-1">
          <textarea
            ref="messageInput"
            v-model="newMessage"
            placeholder="Type a message..."
            class="w-full px-4 py-2.5 bg-gray-100 rounded-2xl resize-none focus:outline-none focus:ring-2 focus:ring-primary-500 leading-normal"
            rows="1"
            :disabled="sending"
            @input="autoResize"
            @keydown.enter.exact.prevent="sendMessage"
            style="max-height: 120px;"
          ></textarea>
        </div>

        <button
          type="submit"
          :disabled="!newMessage.trim() || sending"
          class="w-11 h-11 bg-primary-600 text-white rounded-full flex-shrink-0 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-primary-700 transition flex items-center justify-center"
        >
          <PaperAirplaneIcon class="w-5 h-5" />
        </button>
      </form>
    </div>

    <!-- Make Offer Modal -->
    <div v-if="showOfferModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
      <div class="absolute inset-0 bg-black/50" @click="showOfferModal = false"></div>
      <div class="relative bg-white w-full sm:max-w-sm sm:rounded-xl rounded-t-2xl p-6 pb-safe">
        <h3 class="text-lg font-semibold mb-4">Make an Offer</h3>
        <div v-if="conversation?.listing" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg mb-4">
          <img
            :src="conversation.listing.primary_image_url"
            class="w-12 h-12 rounded-lg object-cover"
          />
          <div class="min-w-0 flex-1">
            <p class="font-medium text-sm truncate">{{ conversation.listing.title }}</p>
            <p class="text-primary-600 font-bold">{{ conversation.listing.formatted_price || '₹' + conversation.listing.price }}</p>
          </div>
        </div>
        <div class="relative mb-4">
          <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-lg">₹</span>
          <input
            v-model="offerAmount"
            type="number"
            min="1"
            class="w-full pl-10 pr-4 py-3 bg-gray-100 rounded-xl text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-primary-500"
            placeholder="Enter your offer"
          />
        </div>
        <div class="flex gap-3">
          <button @click="showOfferModal = false" class="flex-1 py-3 border border-gray-300 rounded-xl font-medium">
            Cancel
          </button>
          <button
            @click="makeOffer"
            class="flex-1 py-3 bg-primary-600 text-white rounded-xl font-medium disabled:opacity-50"
            :disabled="!offerAmount"
          >
            Send Offer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import dayjs from 'dayjs'
import {
  ArrowLeftIcon,
  EllipsisVerticalIcon,
  PaperAirplaneIcon,
  CurrencyRupeeIcon,
  ChevronRightIcon,
  CheckIcon,
  PhoneIcon,
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
const messageInput = ref(null)

const currentUserId = computed(() => authStore.user?.id)
const otherUser = computed(() => {
  if (!conversation.value) return null
  return conversation.value.buyer_id === currentUserId.value
    ? conversation.value.seller
    : conversation.value.buyer
})

const formatTime = (date) => dayjs(date).format('h:mm A')
const formatDate = (date) => {
  if (!date) return ''
  const d = dayjs(date)
  if (d.isSame(dayjs(), 'day')) return 'Today'
  if (d.isSame(dayjs().subtract(1, 'day'), 'day')) return 'Yesterday'
  return d.format('MMM D, YYYY')
}

const autoResize = (e) => {
  const textarea = e.target
  textarea.style.height = 'auto'
  textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px'
}

const resetInputHeight = () => {
  if (messageInput.value) {
    messageInput.value.style.height = 'auto'
  }
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
  resetInputHeight()

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
  showOptionsMenu.value = false
  try {
    await api.post(`/conversations/${route.params.uuid}/block`)
    toast.success('User blocked')
    router.push('/messages')
  } catch (error) {
    toast.error('Failed to block user')
  }
}

const deleteConversation = async () => {
  showOptionsMenu.value = false
  try {
    await api.delete(`/conversations/${route.params.uuid}`)
    toast.success('Conversation deleted')
    router.push('/messages')
  } catch (error) {
    toast.error('Failed to delete conversation')
  }
}

// Handle viewport resize (keyboard open/close on mobile)
const handleResize = () => {
  scrollToBottom()
}

onMounted(() => {
  fetchConversation()
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>

<style scoped>
.conversation-page {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  background: #f3f4f6;
  overflow: hidden;
  z-index: 40;
}

.conversation-header {
  background: white;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  flex-shrink: 0;
  /* Safe area for notch */
  padding-top: env(safe-area-inset-top, 0);
}

.messages-area {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 16px;
  -webkit-overflow-scrolling: touch;
  overscroll-behavior-y: contain;
  min-height: 0;
}

.conversation-input {
  background: white;
  border-top: 1px solid #e5e7eb;
  padding: 12px;
  padding-bottom: calc(12px + env(safe-area-inset-bottom, 0px));
  flex-shrink: 0;
}

.pb-safe {
  padding-bottom: max(24px, env(safe-area-inset-bottom, 24px));
}
</style>
