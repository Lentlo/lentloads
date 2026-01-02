<template>
  <div class="chat-page">
    <!-- Stunning Header -->
    <header class="chat-header">
      <div class="header-bg"></div>
      <div class="header-content">
        <button @click="$router.push('/messages')" class="back-btn">
          <ArrowLeftIcon class="w-5 h-5" />
        </button>

        <div v-if="conversation" class="user-section" @click="goToProfile">
          <div class="avatar-ring">
            <img :src="otherUser?.avatar_url || defaultAvatar" class="avatar" />
            <span class="online-indicator"></span>
          </div>
          <div class="user-details">
            <h1>{{ otherUser?.name }}</h1>
            <div class="status">
              <span class="status-dot"></span>
              <span>Active now</span>
            </div>
          </div>
        </div>

        <div class="header-actions">
          <a v-if="otherUser?.phone" :href="`tel:${otherUser.phone}`" class="header-btn phone">
            <PhoneIcon class="w-5 h-5" />
          </a>
          <button @click="toggleMenu" class="header-btn menu">
            <EllipsisVerticalIcon class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Product Card in Header -->
      <div v-if="conversation?.listing" class="product-strip">
        <router-link :to="`/listing/${conversation.listing.slug}`" class="product-card">
          <img :src="conversation.listing.primary_image_url" class="product-img" />
          <div class="product-info">
            <p class="product-name">{{ conversation.listing.title }}</p>
            <p class="product-price">{{ conversation.listing.formatted_price || '₹' + conversation.listing.price }}</p>
          </div>
          <div class="product-arrow">
            <ChevronRightIcon class="w-4 h-4" />
          </div>
        </router-link>
      </div>
    </header>

    <!-- Dropdown Menu -->
    <div v-if="showMenu" class="menu-overlay" @click="showMenu = false">
      <div class="dropdown-menu" @click.stop>
        <router-link :to="`/listing/${conversation?.listing?.slug}`" class="menu-item">
          <EyeIcon class="w-5 h-5" />
          <span>View Listing</span>
        </router-link>
        <router-link :to="`/user/${otherUser?.id}`" class="menu-item">
          <UserIcon class="w-5 h-5" />
          <span>View Profile</span>
        </router-link>
        <button @click="blockUser" class="menu-item danger">
          <NoSymbolIcon class="w-5 h-5" />
          <span>Block User</span>
        </button>
        <button @click="deleteConversation" class="menu-item danger">
          <TrashIcon class="w-5 h-5" />
          <span>Delete Chat</span>
        </button>
      </div>
    </div>

    <!-- Messages -->
    <div ref="messagesArea" class="messages-area">
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
        <p>Loading messages...</p>
      </div>

      <template v-else>
        <div v-if="messages.length" class="date-chip">
          <span>{{ formatDate(messages[0]?.created_at) }}</span>
        </div>

        <div v-for="msg in messages" :key="msg.id" class="msg-wrapper" :class="msg.sender_id === currentUserId ? 'sent' : 'received'">
          <div class="msg-bubble">
            <div v-if="msg.type === 'offer'" class="offer-content">
              <div class="offer-icon">
                <CurrencyRupeeIcon class="w-5 h-5" />
              </div>
              <p class="offer-label">Price Offer</p>
              <p class="offer-amount">₹{{ msg.offer_amount?.toLocaleString() }}</p>
              <div v-if="msg.offer_status === 'pending' && msg.sender_id !== currentUserId" class="offer-actions">
                <button @click="respondOffer(msg.id, 'accept')" class="accept-btn">Accept</button>
                <button @click="respondOffer(msg.id, 'reject')" class="decline-btn">Decline</button>
              </div>
              <div v-else-if="msg.offer_status !== 'pending'" class="offer-badge" :class="msg.offer_status">
                {{ msg.offer_status === 'accepted' ? 'Accepted' : 'Declined' }}
              </div>
            </div>
            <p class="msg-text">{{ msg.body }}</p>
            <div class="msg-meta">
              <span class="msg-time">{{ formatTime(msg.created_at) }}</span>
              <span v-if="msg.sender_id === currentUserId" class="msg-status">
                <CheckIcon class="w-3.5 h-3.5" />
                <CheckIcon v-if="msg.is_read" class="w-3.5 h-3.5 -ml-1.5" />
              </span>
            </div>
          </div>
        </div>

        <div v-if="!messages.length" class="empty-chat">
          <div class="empty-icon">
            <ChatBubbleLeftRightIcon class="w-12 h-12" />
          </div>
          <h3>Start the conversation</h3>
          <p>Send a message or make an offer</p>
        </div>
      </template>
    </div>

    <!-- Input Area -->
    <div class="input-area">
      <button @click="showOfferModal = true" class="offer-trigger">
        <CurrencyRupeeIcon class="w-6 h-6" />
      </button>

      <div class="input-wrapper">
        <textarea
          ref="inputField"
          v-model="message"
          placeholder="Type your message..."
          rows="1"
          @input="resizeInput"
          @keydown.enter.exact.prevent="send"
        ></textarea>
      </div>

      <button @click="send" :disabled="!message.trim() || sending" class="send-trigger">
        <div v-if="sending" class="send-spinner"></div>
        <PaperAirplaneIcon v-else class="w-5 h-5" />
      </button>
    </div>

    <!-- Offer Modal -->
    <div v-if="showOfferModal" class="modal-overlay">
      <div class="modal-backdrop" @click="showOfferModal = false"></div>
      <div class="modal-sheet">
        <div class="modal-handle"></div>
        <h3>Make an Offer</h3>
        <div v-if="conversation?.listing" class="modal-listing">
          <img :src="conversation.listing.primary_image_url" />
          <div>
            <p class="listing-title">{{ conversation.listing.title }}</p>
            <p class="listing-price">{{ conversation.listing.formatted_price || '₹' + conversation.listing.price }}</p>
          </div>
        </div>
        <div class="offer-field">
          <span class="currency">₹</span>
          <input v-model="offerAmount" type="number" placeholder="Your offer" />
        </div>
        <div class="modal-actions">
          <button @click="showOfferModal = false" class="btn-cancel">Cancel</button>
          <button @click="sendOffer" :disabled="!offerAmount" class="btn-submit">Send Offer</button>
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
  EllipsisVerticalIcon,
  PaperAirplaneIcon,
  CurrencyRupeeIcon,
  ChevronRightIcon,
  PhoneIcon,
  EyeIcon,
  UserIcon,
  NoSymbolIcon,
  TrashIcon,
  CheckIcon,
  ChatBubbleLeftRightIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const loading = ref(true)
const sending = ref(false)
const conversation = ref(null)
const messages = ref([])
const message = ref('')
const showMenu = ref(false)
const showOfferModal = ref(false)
const offerAmount = ref('')
const messagesArea = ref(null)
const inputField = ref(null)

const currentUserId = computed(() => authStore.user?.id)
const otherUser = computed(() => {
  if (!conversation.value) return null
  return conversation.value.buyer_id === currentUserId.value
    ? conversation.value.seller
    : conversation.value.buyer
})

const defaultAvatar = computed(() => {
  const name = otherUser.value?.name || 'U'
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=7c3aed&color=fff&size=100&bold=true`
})

const formatTime = (d) => dayjs(d).format('h:mm A')
const formatDate = (d) => {
  if (!d) return ''
  const day = dayjs(d)
  if (day.isSame(dayjs(), 'day')) return 'Today'
  if (day.isSame(dayjs().subtract(1, 'day'), 'day')) return 'Yesterday'
  return day.format('MMM D, YYYY')
}

const scrollDown = () => {
  nextTick(() => {
    if (messagesArea.value) {
      messagesArea.value.scrollTop = messagesArea.value.scrollHeight
    }
  })
}

const resizeInput = () => {
  if (inputField.value) {
    inputField.value.style.height = 'auto'
    inputField.value.style.height = Math.min(inputField.value.scrollHeight, 100) + 'px'
  }
}

const toggleMenu = () => {
  showMenu.value = !showMenu.value
}

const goToProfile = () => {
  if (otherUser.value?.id) {
    router.push(`/user/${otherUser.value.id}`)
  }
}

const load = async () => {
  try {
    const res = await api.get(`/conversations/${route.params.uuid}`)
    conversation.value = res.data.data.conversation
    messages.value = res.data.data.messages.data || []
    scrollDown()
  } catch (e) {
    toast.error('Failed to load chat')
    router.push('/messages')
  } finally {
    loading.value = false
  }
}

const send = async () => {
  if (!message.value.trim() || sending.value) return

  sending.value = true
  const text = message.value
  message.value = ''

  if (inputField.value) {
    inputField.value.style.height = 'auto'
  }

  try {
    const res = await api.post(`/conversations/${route.params.uuid}/messages`, {
      message: text,
      type: 'text',
    })
    messages.value.push(res.data.data)
    scrollDown()
  } catch (e) {
    message.value = text
    toast.error('Failed to send')
  } finally {
    sending.value = false
  }
}

const sendOffer = async () => {
  if (!offerAmount.value) return

  showOfferModal.value = false
  sending.value = true

  try {
    const res = await api.post(`/conversations/${route.params.uuid}/messages`, {
      message: `Offer: ₹${offerAmount.value}`,
      type: 'offer',
      offer_amount: offerAmount.value,
    })
    messages.value.push(res.data.data)
    offerAmount.value = ''
    scrollDown()
    toast.success('Offer sent')
  } catch (e) {
    toast.error('Failed to send offer')
  } finally {
    sending.value = false
  }
}

const respondOffer = async (id, action) => {
  try {
    await api.post(`/messages/${id}/offer-response`, { action })
    const m = messages.value.find(x => x.id === id)
    if (m) m.offer_status = action === 'accept' ? 'accepted' : 'rejected'
    toast.success(`Offer ${action}ed`)
  } catch (e) {
    toast.error('Failed')
  }
}

const blockUser = async () => {
  showMenu.value = false
  try {
    await api.post(`/conversations/${route.params.uuid}/block`)
    toast.success('User blocked')
    router.push('/messages')
  } catch (e) {
    toast.error('Failed')
  }
}

const deleteConversation = async () => {
  showMenu.value = false
  try {
    await api.delete(`/conversations/${route.params.uuid}`)
    toast.success('Chat deleted')
    router.push('/messages')
  } catch (e) {
    toast.error('Failed')
  }
}

onMounted(load)
</script>

<style scoped>
/* Full Page Layout - iOS Safe */
.chat-page {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
  z-index: 50;
}

/* ==================== STUNNING HEADER ==================== */
.chat-header {
  position: relative;
  flex-shrink: 0;
  background: linear-gradient(135deg, #7c3aed 0%, #a855f7 50%, #ec4899 100%);
  padding-top: env(safe-area-inset-top, 0);
  box-shadow: 0 4px 20px rgba(124, 58, 237, 0.3);
}

.header-bg {
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.header-content {
  position: relative;
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
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  color: white;
  -webkit-tap-highlight-color: transparent;
  transition: all 0.2s;
}

.back-btn:active {
  transform: scale(0.95);
  background: rgba(255, 255, 255, 0.3);
}

.user-section {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
}

.avatar-ring {
  position: relative;
  padding: 3px;
  background: linear-gradient(135deg, #fff 0%, rgba(255,255,255,0.6) 100%);
  border-radius: 50%;
}

.avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  object-fit: cover;
  display: block;
}

.online-indicator {
  position: absolute;
  bottom: 3px;
  right: 3px;
  width: 14px;
  height: 14px;
  background: #22c55e;
  border: 3px solid white;
  border-radius: 50%;
  box-shadow: 0 2px 8px rgba(34, 197, 94, 0.5);
}

.user-details {
  min-width: 0;
}

.user-details h1 {
  font-size: 17px;
  font-weight: 700;
  color: white;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.status {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 2px;
}

.status-dot {
  width: 8px;
  height: 8px;
  background: #4ade80;
  border-radius: 50%;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.7; transform: scale(1.1); }
}

.status span {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.9);
  font-weight: 500;
}

.header-actions {
  display: flex;
  gap: 8px;
}

.header-btn {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  color: white;
  -webkit-tap-highlight-color: transparent;
  transition: all 0.2s;
}

.header-btn:active {
  transform: scale(0.95);
  background: rgba(255, 255, 255, 0.3);
}

.header-btn.phone {
  background: rgba(34, 197, 94, 0.3);
}

/* Product Strip */
.product-strip {
  position: relative;
  padding: 0 16px 12px;
}

.product-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 14px;
  text-decoration: none;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  -webkit-tap-highlight-color: transparent;
  transition: transform 0.2s;
}

.product-card:active {
  transform: scale(0.98);
}

.product-img {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  object-fit: cover;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.product-info {
  flex: 1;
  min-width: 0;
}

.product-name {
  font-size: 14px;
  font-weight: 600;
  color: #1f2937;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.product-price {
  font-size: 15px;
  font-weight: 700;
  color: #7c3aed;
  margin-top: 2px;
}

.product-arrow {
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f3f4f6;
  border-radius: 8px;
  color: #6b7280;
}

/* ==================== DROPDOWN MENU ==================== */
.menu-overlay {
  position: fixed;
  inset: 0;
  z-index: 60;
  background: rgba(0, 0, 0, 0.3);
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.dropdown-menu {
  position: absolute;
  top: calc(env(safe-area-inset-top, 0) + 60px);
  right: 16px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  overflow: hidden;
  min-width: 200px;
  animation: slideDown 0.2s ease;
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  font-size: 15px;
  color: #374151;
  text-decoration: none;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  -webkit-tap-highlight-color: transparent;
  transition: background 0.15s;
}

.menu-item:active {
  background: #f3f4f6;
}

.menu-item.danger {
  color: #dc2626;
}

/* ==================== MESSAGES AREA ==================== */
.messages-area {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-overflow-scrolling: touch;
  padding: 16px;
  overscroll-behavior: contain;
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  gap: 12px;
}

.loading p {
  color: #6b7280;
  font-size: 14px;
}

.spinner {
  width: 36px;
  height: 36px;
  border: 3px solid #e5e7eb;
  border-top-color: #7c3aed;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.date-chip {
  display: flex;
  justify-content: center;
  margin-bottom: 20px;
}

.date-chip span {
  padding: 6px 16px;
  background: white;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  color: #6b7280;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

/* Message Bubbles */
.msg-wrapper {
  display: flex;
  margin-bottom: 12px;
}

.msg-wrapper.sent {
  justify-content: flex-end;
}

.msg-wrapper.received {
  justify-content: flex-start;
}

.msg-bubble {
  max-width: 75%;
  padding: 12px 16px;
  border-radius: 20px;
  position: relative;
}

.msg-wrapper.sent .msg-bubble {
  background: linear-gradient(135deg, #7c3aed 0%, #9333ea 100%);
  color: white;
  border-bottom-right-radius: 6px;
  box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
}

.msg-wrapper.received .msg-bubble {
  background: white;
  color: #1f2937;
  border-bottom-left-radius: 6px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
}

.msg-text {
  font-size: 15px;
  line-height: 1.5;
  word-break: break-word;
  white-space: pre-wrap;
}

.msg-meta {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 4px;
  margin-top: 6px;
}

.msg-time {
  font-size: 11px;
  opacity: 0.7;
}

.msg-status {
  display: flex;
  align-items: center;
  opacity: 0.7;
}

/* Offer Styling */
.offer-content {
  text-align: center;
  padding-bottom: 12px;
  margin-bottom: 8px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.msg-wrapper.received .offer-content {
  border-bottom-color: #e5e7eb;
}

.offer-icon {
  width: 40px;
  height: 40px;
  margin: 0 auto 8px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.msg-wrapper.received .offer-icon {
  background: #f3e8ff;
  color: #7c3aed;
}

.offer-label {
  font-size: 12px;
  opacity: 0.8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.offer-amount {
  font-size: 28px;
  font-weight: 700;
  margin: 4px 0;
}

.offer-actions {
  display: flex;
  gap: 8px;
  margin-top: 12px;
}

.accept-btn, .decline-btn {
  flex: 1;
  padding: 10px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 14px;
  -webkit-tap-highlight-color: transparent;
}

.accept-btn {
  background: #22c55e;
  color: white;
}

.decline-btn {
  background: rgba(0, 0, 0, 0.1);
  color: #374151;
}

.offer-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  margin-top: 8px;
}

.offer-badge.accepted {
  background: rgba(34, 197, 94, 0.2);
  color: #16a34a;
}

.offer-badge.rejected {
  background: rgba(239, 68, 68, 0.2);
  color: #dc2626;
}

/* Empty State */
.empty-chat {
  text-align: center;
  padding: 60px 20px;
}

.empty-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 16px;
  background: linear-gradient(135deg, #ede9fe 0%, #fce7f3 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #7c3aed;
}

.empty-chat h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 8px;
}

.empty-chat p {
  color: #6b7280;
  font-size: 14px;
}

/* ==================== INPUT AREA ==================== */
.input-area {
  display: flex;
  align-items: flex-end;
  gap: 10px;
  padding: 12px 16px;
  background: white;
  border-top: 1px solid #f3f4f6;
  padding-bottom: calc(12px + env(safe-area-inset-bottom, 0));
}

.offer-trigger {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f3e8ff 0%, #fce7f3 100%);
  color: #7c3aed;
  border-radius: 14px;
  flex-shrink: 0;
  -webkit-tap-highlight-color: transparent;
  transition: transform 0.2s;
}

.offer-trigger:active {
  transform: scale(0.95);
}

.input-wrapper {
  flex: 1;
}

.input-wrapper textarea {
  width: 100%;
  padding: 12px 16px;
  background: #f3f4f6;
  border: none;
  border-radius: 24px;
  font-size: 15px;
  line-height: 1.4;
  resize: none;
  max-height: 100px;
  outline: none;
  -webkit-appearance: none;
  transition: background 0.2s;
}

.input-wrapper textarea:focus {
  background: #e5e7eb;
}

.send-trigger {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #7c3aed 0%, #9333ea 100%);
  color: white;
  border-radius: 14px;
  flex-shrink: 0;
  -webkit-tap-highlight-color: transparent;
  box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
  transition: all 0.2s;
}

.send-trigger:active {
  transform: scale(0.95);
}

.send-trigger:disabled {
  opacity: 0.5;
  box-shadow: none;
}

.send-spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

/* ==================== MODAL ==================== */
.modal-overlay {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  z-index: 70;
}

.modal-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  animation: fadeIn 0.2s ease;
}

.modal-sheet {
  position: relative;
  background: white;
  width: 100%;
  max-width: 440px;
  border-radius: 24px 24px 0 0;
  padding: 12px 20px 20px;
  padding-bottom: calc(20px + env(safe-area-inset-bottom, 0));
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}

.modal-handle {
  width: 40px;
  height: 4px;
  background: #e5e7eb;
  border-radius: 2px;
  margin: 0 auto 16px;
}

.modal-sheet h3 {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 16px;
  text-align: center;
}

.modal-listing {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 14px;
  margin-bottom: 16px;
}

.modal-listing img {
  width: 52px;
  height: 52px;
  border-radius: 10px;
  object-fit: cover;
}

.listing-title {
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

.listing-price {
  font-size: 16px;
  font-weight: 700;
  color: #7c3aed;
  margin-top: 2px;
}

.offer-field {
  position: relative;
  margin-bottom: 20px;
}

.currency {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 20px;
  font-weight: 600;
  color: #6b7280;
}

.offer-field input {
  width: 100%;
  padding: 16px 16px 16px 44px;
  background: #f3f4f6;
  border: 2px solid transparent;
  border-radius: 14px;
  font-size: 20px;
  font-weight: 600;
  outline: none;
  -webkit-appearance: none;
  transition: all 0.2s;
}

.offer-field input:focus {
  border-color: #7c3aed;
  background: white;
}

.modal-actions {
  display: flex;
  gap: 12px;
}

.btn-cancel, .btn-submit {
  flex: 1;
  padding: 16px;
  border-radius: 14px;
  font-size: 16px;
  font-weight: 600;
  -webkit-tap-highlight-color: transparent;
  transition: transform 0.2s;
}

.btn-cancel {
  background: #f3f4f6;
  color: #374151;
}

.btn-submit {
  background: linear-gradient(135deg, #7c3aed 0%, #9333ea 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
}

.btn-cancel:active, .btn-submit:active {
  transform: scale(0.98);
}

.btn-submit:disabled {
  opacity: 0.5;
  box-shadow: none;
}

@media (min-width: 640px) {
  .modal-overlay {
    align-items: center;
  }
  .modal-sheet {
    border-radius: 24px;
    margin: 20px;
  }
}
</style>
