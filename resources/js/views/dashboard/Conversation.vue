<template>
  <div class="conversation-page">
    <!-- Sending Overlay -->
    <div v-if="sending" class="sending-overlay">
      <div class="sending-popup">
        <div class="sending-spinner"></div>
        <p>Sending...</p>
      </div>
    </div>

    <!-- Sticky Header -->
    <header class="conversation-header">
      <div class="header-content">
        <button @click="$router.push('/messages')" class="back-btn">
          <ArrowLeftIcon class="w-5 h-5" />
        </button>

        <div v-if="conversation" class="user-info">
          <div class="avatar-wrapper">
            <img
              :src="otherUser?.avatar_url || '/images/default-avatar.png'"
              :alt="otherUser?.name"
              class="avatar"
            />
            <span class="online-dot"></span>
          </div>
          <div class="user-details">
            <h1>{{ otherUser?.name }}</h1>
            <p>Online</p>
          </div>
        </div>

        <div class="header-actions">
          <a v-if="otherUser?.phone" :href="`tel:${otherUser.phone}`" class="action-btn">
            <PhoneIcon class="w-5 h-5" />
          </a>
          <button @click="showOptionsMenu = !showOptionsMenu" class="action-btn">
            <EllipsisVerticalIcon class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Options Menu -->
      <div v-if="showOptionsMenu" class="options-menu">
        <router-link :to="`/listing/${conversation?.listing?.slug}`" @click="showOptionsMenu = false">
          View Listing
        </router-link>
        <router-link :to="`/user/${otherUser?.id}`" @click="showOptionsMenu = false">
          View Profile
        </router-link>
        <button @click="blockUser" class="danger">Block User</button>
        <button @click="deleteConversation" class="danger">Delete Chat</button>
      </div>

      <!-- Product Card -->
      <div v-if="conversation?.listing" class="product-card">
        <router-link :to="`/listing/${conversation.listing.slug}`" class="product-link">
          <img :src="conversation.listing.primary_image_url" :alt="conversation.listing.title" />
          <div class="product-info">
            <p class="product-title">{{ conversation.listing.title }}</p>
            <p class="product-price">{{ conversation.listing.formatted_price || '₹' + conversation.listing.price }}</p>
          </div>
          <ChevronRightIcon class="w-5 h-5 text-gray-400" />
        </router-link>
      </div>
    </header>

    <!-- Menu Overlay -->
    <div v-if="showOptionsMenu" class="menu-overlay" @click="showOptionsMenu = false"></div>

    <!-- Messages Area -->
    <div ref="messagesContainer" class="messages-area" @touchstart="onTouchStart" @touchmove="onTouchMove">
      <div v-if="loading" class="loading-spinner">
        <div class="spinner"></div>
      </div>

      <template v-else>
        <div v-if="messages.length > 0" class="date-badge">
          {{ formatDate(messages[0]?.created_at) }}
        </div>

        <div
          v-for="message in messages"
          :key="message.id"
          class="message-row"
          :class="message.sender_id === currentUserId ? 'sent' : 'received'"
        >
          <div class="message-bubble" :class="{ 'pending': message.pending }">
            <!-- Offer Message -->
            <div v-if="message.type === 'offer'" class="offer-content">
              <div class="offer-header">
                <CurrencyRupeeIcon class="w-4 h-4" />
                <span>Offer</span>
              </div>
              <p class="offer-amount">₹{{ message.offer_amount?.toLocaleString() }}</p>
              <div v-if="message.offer_status === 'pending' && message.sender_id !== currentUserId" class="offer-actions">
                <button @click="respondToOffer(message.id, 'accept')" class="accept-btn">Accept</button>
                <button @click="respondToOffer(message.id, 'reject')" class="decline-btn">Decline</button>
              </div>
              <p v-else-if="message.offer_status !== 'pending'" class="offer-status" :class="message.offer_status">
                {{ message.offer_status === 'accepted' ? 'Accepted' : 'Declined' }}
              </p>
            </div>

            <p class="message-text">{{ message.body }}</p>

            <p class="message-time">
              <template v-if="message.pending">Sending...</template>
              <template v-else>
                {{ formatTime(message.created_at) }}
                <span v-if="message.sender_id === currentUserId" class="read-status">
                  <CheckIcon class="w-3 h-3" />
                  <CheckIcon v-if="message.is_read" class="w-3 h-3 -ml-1" />
                </span>
              </template>
            </p>
          </div>
        </div>

        <div v-if="messages.length === 0" class="empty-state">
          <p>No messages yet. Say hello!</p>
        </div>
      </template>
    </div>

    <!-- Fixed Input Area -->
    <div class="input-area">
      <button type="button" @click="showOfferModal = true" class="offer-btn" :disabled="sending">
        <CurrencyRupeeIcon class="w-6 h-6" />
      </button>

      <div class="input-wrapper">
        <textarea
          ref="messageInput"
          v-model="newMessage"
          placeholder="Type a message..."
          rows="1"
          :disabled="sending"
          @input="autoResize"
          @keydown.enter.exact.prevent="sendMessage"
        ></textarea>
      </div>

      <button type="button" @click="sendMessage" :disabled="!newMessage.trim() || sending" class="send-btn">
        <PaperAirplaneIcon class="w-5 h-5" />
      </button>
    </div>

    <!-- Make Offer Modal -->
    <div v-if="showOfferModal" class="modal-overlay">
      <div class="modal-backdrop" @click="showOfferModal = false"></div>
      <div class="modal-content">
        <h3>Make an Offer</h3>
        <div v-if="conversation?.listing" class="modal-product">
          <img :src="conversation.listing.primary_image_url" />
          <div>
            <p class="title">{{ conversation.listing.title }}</p>
            <p class="price">{{ conversation.listing.formatted_price || '₹' + conversation.listing.price }}</p>
          </div>
        </div>
        <div class="offer-input">
          <span>₹</span>
          <input v-model="offerAmount" type="number" min="1" placeholder="Enter your offer" />
        </div>
        <div class="modal-actions">
          <button @click="showOfferModal = false" class="cancel-btn">Cancel</button>
          <button @click="makeOffer" class="submit-btn" :disabled="!offerAmount">Send Offer</button>
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

// Touch tracking for scroll
let touchStartY = 0
let isScrolling = false

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

// Touch handlers for better scroll
const onTouchStart = (e) => {
  touchStartY = e.touches[0].clientY
  isScrolling = false
}

const onTouchMove = (e) => {
  const touchY = e.touches[0].clientY
  const diff = Math.abs(touchY - touchStartY)
  if (diff > 10) {
    isScrolling = true
  }
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
    // Refocus input after sending
    nextTick(() => {
      if (messageInput.value) {
        messageInput.value.focus()
      }
    })
  }
}

const makeOffer = async () => {
  if (!offerAmount.value) return

  sending.value = true
  showOfferModal.value = false

  try {
    const response = await api.post(`/conversations/${route.params.uuid}/messages`, {
      message: `I'd like to offer ₹${offerAmount.value}`,
      type: 'offer',
      offer_amount: offerAmount.value,
    })
    messages.value.push(response.data.data)
    offerAmount.value = ''
    scrollToBottom()
    toast.success('Offer sent!')
  } catch (error) {
    toast.error('Failed to send offer')
  } finally {
    sending.value = false
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

onMounted(() => {
  fetchConversation()
})
</script>

<style scoped>
/* Base Layout */
.conversation-page {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  background: #f3f4f6;
  z-index: 40;
}

/* Sending Overlay */
.sending-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

.sending-popup {
  background: white;
  padding: 24px 40px;
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.sending-popup p {
  font-size: 16px;
  font-weight: 500;
  color: #374151;
}

.sending-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e5e7eb;
  border-top-color: #7c3aed;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Header */
.conversation-header {
  background: white;
  border-bottom: 1px solid #e5e7eb;
  flex-shrink: 0;
  padding-top: env(safe-area-inset-top, 0);
  position: relative;
  z-index: 50;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
}

.back-btn {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  color: #4b5563;
}

.back-btn:active {
  background: #f3f4f6;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
  min-width: 0;
}

.avatar-wrapper {
  position: relative;
  flex-shrink: 0;
}

.avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  object-fit: cover;
  background: #e5e7eb;
}

.online-dot {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 12px;
  height: 12px;
  background: #22c55e;
  border: 2px solid white;
  border-radius: 50%;
}

.user-details {
  min-width: 0;
}

.user-details h1 {
  font-size: 16px;
  font-weight: 600;
  color: #111827;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-details p {
  font-size: 12px;
  color: #22c55e;
}

.header-actions {
  display: flex;
  gap: 4px;
}

.action-btn {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  color: #6b7280;
}

.action-btn:active {
  background: #f3f4f6;
}

/* Options Menu */
.options-menu {
  position: absolute;
  top: 100%;
  right: 16px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  z-index: 60;
  min-width: 180px;
}

.options-menu a,
.options-menu button {
  display: block;
  width: 100%;
  padding: 14px 16px;
  text-align: left;
  font-size: 15px;
  color: #374151;
  background: white;
  border: none;
}

.options-menu a:active,
.options-menu button:active {
  background: #f9fafb;
}

.options-menu .danger {
  color: #dc2626;
}

.menu-overlay {
  position: fixed;
  inset: 0;
  z-index: 45;
}

/* Product Card */
.product-card {
  padding: 0 16px 12px;
}

.product-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px;
  background: #f9fafb;
  border-radius: 12px;
}

.product-link:active {
  background: #f3f4f6;
}

.product-link img {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  object-fit: cover;
  background: #e5e7eb;
}

.product-info {
  flex: 1;
  min-width: 0;
}

.product-title {
  font-size: 14px;
  font-weight: 500;
  color: #111827;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.product-price {
  font-size: 14px;
  font-weight: 700;
  color: #7c3aed;
}

/* Messages Area */
.messages-area {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 16px;
  -webkit-overflow-scrolling: touch;
  overscroll-behavior-y: contain;
  min-height: 0;
}

.loading-spinner {
  display: flex;
  justify-content: center;
  padding: 40px 0;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #e5e7eb;
  border-top-color: #7c3aed;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.date-badge {
  display: flex;
  justify-content: center;
  margin-bottom: 16px;
}

.date-badge::after {
  content: attr(data-date);
}

.date-badge {
  padding: 6px 14px;
  background: #e5e7eb;
  border-radius: 20px;
  font-size: 12px;
  color: #6b7280;
  width: fit-content;
  margin: 0 auto 16px;
}

/* Message Rows */
.message-row {
  display: flex;
  margin-bottom: 8px;
}

.message-row.sent {
  justify-content: flex-end;
}

.message-row.received {
  justify-content: flex-start;
}

.message-bubble {
  max-width: 80%;
  padding: 10px 14px;
  border-radius: 18px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.message-row.sent .message-bubble {
  background: #7c3aed;
  color: white;
  border-bottom-right-radius: 4px;
}

.message-row.received .message-bubble {
  background: white;
  color: #111827;
  border-bottom-left-radius: 4px;
}

.message-bubble.pending {
  opacity: 0.7;
}

.message-text {
  font-size: 15px;
  line-height: 1.4;
  word-wrap: break-word;
  white-space: pre-wrap;
}

.message-time {
  font-size: 10px;
  margin-top: 4px;
  display: flex;
  align-items: center;
  gap: 4px;
}

.message-row.sent .message-time {
  color: rgba(255, 255, 255, 0.7);
  justify-content: flex-end;
}

.message-row.received .message-time {
  color: #9ca3af;
}

.read-status {
  display: flex;
  align-items: center;
}

/* Offer Messages */
.offer-content {
  margin-bottom: 8px;
}

.offer-header {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  opacity: 0.8;
  margin-bottom: 4px;
}

.offer-amount {
  font-size: 24px;
  font-weight: 700;
}

.offer-actions {
  display: flex;
  gap: 8px;
  margin-top: 12px;
}

.accept-btn {
  flex: 1;
  padding: 8px;
  background: #22c55e;
  color: white;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
}

.decline-btn {
  flex: 1;
  padding: 8px;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
}

.offer-status {
  font-size: 13px;
  font-weight: 500;
  margin-top: 8px;
}

.offer-status.accepted {
  color: #86efac;
}

.offer-status.rejected {
  color: #fca5a5;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #6b7280;
}

/* Input Area */
.input-area {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  background: white;
  border-top: 1px solid #e5e7eb;
  flex-shrink: 0;
  padding-bottom: calc(12px + env(safe-area-inset-bottom, 0));
}

.offer-btn {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  color: #7c3aed;
  flex-shrink: 0;
}

.offer-btn:active {
  background: #f3e8ff;
}

.offer-btn:disabled {
  opacity: 0.5;
}

.input-wrapper {
  flex: 1;
}

.input-wrapper textarea {
  width: 100%;
  padding: 10px 16px;
  background: #f3f4f6;
  border: none;
  border-radius: 24px;
  font-size: 15px;
  line-height: 1.4;
  resize: none;
  max-height: 120px;
  outline: none;
}

.input-wrapper textarea:focus {
  background: #e5e7eb;
}

.input-wrapper textarea:disabled {
  opacity: 0.5;
}

.send-btn {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #7c3aed;
  color: white;
  border-radius: 50%;
  flex-shrink: 0;
}

.send-btn:active {
  background: #6d28d9;
}

.send-btn:disabled {
  opacity: 0.5;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  z-index: 60;
}

.modal-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
}

.modal-content {
  position: relative;
  background: white;
  width: 100%;
  max-width: 400px;
  border-radius: 20px 20px 0 0;
  padding: 24px;
  padding-bottom: calc(24px + env(safe-area-inset-bottom, 0));
}

.modal-content h3 {
  font-size: 18px;
  font-weight: 600;
  color: #111827;
  margin-bottom: 16px;
}

.modal-product {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 12px;
  margin-bottom: 16px;
}

.modal-product img {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  object-fit: cover;
}

.modal-product .title {
  font-size: 14px;
  font-weight: 500;
  color: #111827;
}

.modal-product .price {
  font-size: 14px;
  font-weight: 700;
  color: #7c3aed;
}

.offer-input {
  position: relative;
  margin-bottom: 20px;
}

.offer-input span {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 18px;
  color: #6b7280;
}

.offer-input input {
  width: 100%;
  padding: 14px 16px 14px 40px;
  background: #f3f4f6;
  border: none;
  border-radius: 12px;
  font-size: 18px;
  font-weight: 600;
  outline: none;
}

.offer-input input:focus {
  background: #e5e7eb;
}

.modal-actions {
  display: flex;
  gap: 12px;
}

.cancel-btn {
  flex: 1;
  padding: 14px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 600;
  color: #374151;
}

.submit-btn {
  flex: 1;
  padding: 14px;
  background: #7c3aed;
  border: none;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 600;
  color: white;
}

.submit-btn:disabled {
  opacity: 0.5;
}

@media (min-width: 640px) {
  .modal-overlay {
    align-items: center;
  }

  .modal-content {
    border-radius: 20px;
    padding-bottom: 24px;
  }
}
</style>
