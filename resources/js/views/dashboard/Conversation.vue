<template>
  <div class="conversation-page">
    <!-- Header -->
    <header class="chat-header">
      <button @click="$router.push('/messages')" class="back-btn">
        <ArrowLeftIcon class="w-6 h-6" />
      </button>

      <div v-if="conversation" class="user-info" @click="goToProfile">
        <img :src="otherUser?.avatar_url || '/images/default-avatar.png'" class="avatar" />
        <div class="user-text">
          <h1>{{ otherUser?.name }}</h1>
          <p>Online</p>
        </div>
      </div>

      <div class="header-actions">
        <a v-if="otherUser?.phone" :href="`tel:${otherUser.phone}`" class="action-btn">
          <PhoneIcon class="w-5 h-5" />
        </a>
        <button @click="toggleMenu" class="action-btn">
          <EllipsisVerticalIcon class="w-5 h-5" />
        </button>
      </div>
    </header>

    <!-- Dropdown Menu -->
    <div v-if="showMenu" class="menu-overlay" @click="showMenu = false">
      <div class="dropdown-menu" @click.stop>
        <router-link :to="`/listing/${conversation?.listing?.slug}`" class="menu-item">View Listing</router-link>
        <router-link :to="`/user/${otherUser?.id}`" class="menu-item">View Profile</router-link>
        <button @click="blockUser" class="menu-item danger">Block User</button>
        <button @click="deleteConversation" class="menu-item danger">Delete Chat</button>
      </div>
    </div>

    <!-- Product Link -->
    <div v-if="conversation?.listing" class="product-bar">
      <router-link :to="`/listing/${conversation.listing.slug}`" class="product-link">
        <img :src="conversation.listing.primary_image_url" class="product-img" />
        <div class="product-text">
          <p class="product-title">{{ conversation.listing.title }}</p>
          <p class="product-price">{{ conversation.listing.formatted_price || '₹' + conversation.listing.price }}</p>
        </div>
        <ChevronRightIcon class="w-5 h-5 text-gray-400" />
      </router-link>
    </div>

    <!-- Messages -->
    <div ref="messagesArea" class="messages-area">
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
      </div>

      <template v-else>
        <div v-if="messages.length" class="date-label">{{ formatDate(messages[0]?.created_at) }}</div>

        <div v-for="msg in messages" :key="msg.id" class="msg-row" :class="msg.sender_id === currentUserId ? 'sent' : 'received'">
          <div class="msg-bubble">
            <!-- Offer -->
            <div v-if="msg.type === 'offer'" class="offer-box">
              <p class="offer-label">Offer</p>
              <p class="offer-price">₹{{ msg.offer_amount?.toLocaleString() }}</p>
              <div v-if="msg.offer_status === 'pending' && msg.sender_id !== currentUserId" class="offer-btns">
                <button @click="respondOffer(msg.id, 'accept')" class="accept">Accept</button>
                <button @click="respondOffer(msg.id, 'reject')" class="reject">Decline</button>
              </div>
              <p v-else-if="msg.offer_status !== 'pending'" class="offer-result">
                {{ msg.offer_status === 'accepted' ? 'Accepted' : 'Declined' }}
              </p>
            </div>
            <p class="msg-text">{{ msg.body }}</p>
            <p class="msg-time">{{ formatTime(msg.created_at) }}</p>
          </div>
        </div>

        <div v-if="!messages.length" class="empty">No messages yet</div>
      </template>
    </div>

    <!-- Input Area -->
    <div class="input-area">
      <button @click="showOfferModal = true" class="offer-btn">
        <CurrencyRupeeIcon class="w-6 h-6" />
      </button>

      <textarea
        ref="inputField"
        v-model="message"
        placeholder="Type a message..."
        rows="1"
        @input="resizeInput"
        @keydown.enter.exact.prevent="send"
      ></textarea>

      <button @click="send" :disabled="!message.trim() || sending" class="send-btn">
        <template v-if="sending">
          <div class="btn-spinner"></div>
        </template>
        <template v-else>
          <PaperAirplaneIcon class="w-5 h-5" />
        </template>
      </button>
    </div>

    <!-- Offer Modal -->
    <div v-if="showOfferModal" class="modal">
      <div class="modal-bg" @click="showOfferModal = false"></div>
      <div class="modal-box">
        <h3>Make an Offer</h3>
        <div v-if="conversation?.listing" class="modal-product">
          <img :src="conversation.listing.primary_image_url" />
          <div>
            <p>{{ conversation.listing.title }}</p>
            <p class="price">{{ conversation.listing.formatted_price || '₹' + conversation.listing.price }}</p>
          </div>
        </div>
        <div class="offer-input">
          <span>₹</span>
          <input v-model="offerAmount" type="number" placeholder="Enter amount" />
        </div>
        <div class="modal-btns">
          <button @click="showOfferModal = false" class="cancel">Cancel</button>
          <button @click="sendOffer" :disabled="!offerAmount" class="submit">Send</button>
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
    toast.success('Blocked')
    router.push('/messages')
  } catch (e) {
    toast.error('Failed')
  }
}

const deleteConversation = async () => {
  showMenu.value = false
  try {
    await api.delete(`/conversations/${route.params.uuid}`)
    toast.success('Deleted')
    router.push('/messages')
  } catch (e) {
    toast.error('Failed')
  }
}

onMounted(load)
</script>

<style scoped>
.conversation-page {
  position: fixed;
  inset: 0;
  display: flex;
  flex-direction: column;
  background: #f5f5f5;
  z-index: 50;
}

/* Header */
.chat-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: white;
  border-bottom: 1px solid #eee;
  padding-top: calc(12px + env(safe-area-inset-top, 0));
}

.back-btn,
.action-btn {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #666;
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
}

.back-btn:active,
.action-btn:active {
  opacity: 0.6;
}

.user-info {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  background: #ddd;
}

.user-text {
  min-width: 0;
}

.user-text h1 {
  font-size: 16px;
  font-weight: 600;
  color: #111;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-text p {
  font-size: 12px;
  color: #22c55e;
}

.header-actions {
  display: flex;
}

/* Menu */
.menu-overlay {
  position: fixed;
  inset: 0;
  z-index: 60;
}

.dropdown-menu {
  position: absolute;
  top: 60px;
  right: 16px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
  overflow: hidden;
  min-width: 160px;
}

.menu-item {
  display: block;
  padding: 14px 16px;
  font-size: 15px;
  color: #333;
  text-decoration: none;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  -webkit-tap-highlight-color: transparent;
}

.menu-item:active {
  background: #f5f5f5;
}

.menu-item.danger {
  color: #dc2626;
}

/* Product Bar */
.product-bar {
  background: white;
  padding: 8px 16px;
  border-bottom: 1px solid #eee;
}

.product-link {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px;
  background: #f9f9f9;
  border-radius: 10px;
  text-decoration: none;
  -webkit-tap-highlight-color: transparent;
}

.product-link:active {
  background: #f0f0f0;
}

.product-img {
  width: 44px;
  height: 44px;
  border-radius: 8px;
  object-fit: cover;
  background: #ddd;
}

.product-text {
  flex: 1;
  min-width: 0;
}

.product-title {
  font-size: 13px;
  font-weight: 500;
  color: #111;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.product-price {
  font-size: 14px;
  font-weight: 700;
  color: #7c3aed;
}

/* Messages */
.messages-area {
  flex: 1;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  padding: 16px;
  overscroll-behavior: contain;
}

.loading {
  display: flex;
  justify-content: center;
  padding: 40px;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #e5e7eb;
  border-top-color: #7c3aed;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.date-label {
  text-align: center;
  padding: 8px 16px;
  font-size: 12px;
  color: #888;
  margin-bottom: 12px;
}

.msg-row {
  display: flex;
  margin-bottom: 8px;
}

.msg-row.sent {
  justify-content: flex-end;
}

.msg-row.received {
  justify-content: flex-start;
}

.msg-bubble {
  max-width: 75%;
  padding: 10px 14px;
  border-radius: 18px;
}

.msg-row.sent .msg-bubble {
  background: #7c3aed;
  color: white;
  border-bottom-right-radius: 4px;
}

.msg-row.received .msg-bubble {
  background: white;
  color: #111;
  border-bottom-left-radius: 4px;
}

.msg-text {
  font-size: 15px;
  line-height: 1.4;
  word-break: break-word;
  white-space: pre-wrap;
}

.msg-time {
  font-size: 10px;
  margin-top: 4px;
  opacity: 0.7;
}

.msg-row.sent .msg-time {
  text-align: right;
}

/* Offer */
.offer-box {
  margin-bottom: 8px;
}

.offer-label {
  font-size: 12px;
  opacity: 0.8;
}

.offer-price {
  font-size: 22px;
  font-weight: 700;
}

.offer-btns {
  display: flex;
  gap: 8px;
  margin-top: 10px;
}

.offer-btns button {
  flex: 1;
  padding: 8px;
  border-radius: 8px;
  font-weight: 500;
  font-size: 13px;
}

.offer-btns .accept {
  background: #22c55e;
  color: white;
}

.offer-btns .reject {
  background: rgba(255,255,255,0.2);
  color: white;
}

.offer-result {
  margin-top: 8px;
  font-size: 13px;
  font-weight: 500;
}

.empty {
  text-align: center;
  padding: 40px;
  color: #888;
}

/* Input */
.input-area {
  display: flex;
  align-items: flex-end;
  gap: 8px;
  padding: 10px 12px;
  background: white;
  border-top: 1px solid #eee;
  padding-bottom: calc(10px + env(safe-area-inset-bottom, 0));
}

.offer-btn {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #7c3aed;
  flex-shrink: 0;
  -webkit-tap-highlight-color: transparent;
}

.offer-btn:active {
  opacity: 0.6;
}

.input-area textarea {
  flex: 1;
  padding: 10px 14px;
  background: #f3f4f6;
  border: none;
  border-radius: 20px;
  font-size: 15px;
  line-height: 1.4;
  resize: none;
  max-height: 100px;
  outline: none;
  -webkit-appearance: none;
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
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
}

.send-btn:active {
  opacity: 0.8;
}

.send-btn:disabled {
  opacity: 0.5;
}

.btn-spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

/* Modal */
.modal {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  z-index: 70;
}

.modal-bg {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.5);
}

.modal-box {
  position: relative;
  background: white;
  width: 100%;
  max-width: 400px;
  border-radius: 16px 16px 0 0;
  padding: 20px;
  padding-bottom: calc(20px + env(safe-area-inset-bottom, 0));
}

.modal-box h3 {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 16px;
}

.modal-product {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #f9f9f9;
  border-radius: 10px;
  margin-bottom: 16px;
}

.modal-product img {
  width: 44px;
  height: 44px;
  border-radius: 8px;
  object-fit: cover;
}

.modal-product p {
  font-size: 13px;
  color: #333;
}

.modal-product .price {
  font-weight: 700;
  color: #7c3aed;
}

.offer-input {
  position: relative;
  margin-bottom: 16px;
}

.offer-input span {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 18px;
  color: #666;
}

.offer-input input {
  width: 100%;
  padding: 12px 12px 12px 36px;
  background: #f3f4f6;
  border: none;
  border-radius: 10px;
  font-size: 18px;
  font-weight: 600;
  outline: none;
  -webkit-appearance: none;
}

.modal-btns {
  display: flex;
  gap: 12px;
}

.modal-btns button {
  flex: 1;
  padding: 14px;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 600;
}

.modal-btns .cancel {
  background: #f3f4f6;
  color: #333;
}

.modal-btns .submit {
  background: #7c3aed;
  color: white;
}

.modal-btns .submit:disabled {
  opacity: 0.5;
}

@media (min-width: 640px) {
  .modal {
    align-items: center;
  }
  .modal-box {
    border-radius: 16px;
  }
}
</style>
