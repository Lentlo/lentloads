<template>
  <nav class="mobile-nav" :class="{ 'nav-hidden': isHidden }">
    <!-- Glassmorphism background -->
    <div class="nav-backdrop"></div>

    <div class="nav-container">
      <!-- Home / My Ads -->
      <router-link
        :to="isAuthenticated ? '/my-listings' : '/'"
        class="nav-item"
        :class="{ active: isAuthenticated ? isActive('/my-listings') : isActive('/') }"
      >
        <div class="nav-icon-wrapper">
          <component
            :is="isAuthenticated ? ClipboardDocumentListIcon : HomeIcon"
            class="nav-icon"
          />
        </div>
        <span class="nav-label">{{ isAuthenticated ? 'My Ads' : 'Home' }}</span>
      </router-link>

      <!-- Search -->
      <router-link
        to="/search"
        class="nav-item"
        :class="{ active: isActive('/search') }"
      >
        <div class="nav-icon-wrapper">
          <MagnifyingGlassIcon class="nav-icon" />
        </div>
        <span class="nav-label">Search</span>
      </router-link>

      <!-- Sell Button (Center - Floating) -->
      <router-link to="/sell" class="nav-sell">
        <div class="sell-button">
          <PlusIcon class="sell-icon" />
        </div>
        <span class="nav-label sell-label">Sell</span>
      </router-link>

      <!-- Messages -->
      <router-link
        to="/messages"
        class="nav-item"
        :class="{ active: isActive('/messages') }"
      >
        <div class="nav-icon-wrapper">
          <ChatBubbleLeftRightIcon class="nav-icon" />
          <span v-if="unreadMessages > 0" class="nav-badge">
            {{ unreadMessages > 9 ? '9+' : unreadMessages }}
          </span>
        </div>
        <span class="nav-label">Chat</span>
      </router-link>

      <!-- Account -->
      <router-link
        :to="isAuthenticated ? '/dashboard' : '/login'"
        class="nav-item"
        :class="{ active: isActive('/dashboard') || isActive('/settings') || isActive('/login') }"
      >
        <div class="nav-icon-wrapper">
          <UserCircleIcon class="nav-icon" />
        </div>
        <span class="nav-label">{{ isAuthenticated ? 'Account' : 'Login' }}</span>
      </router-link>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import {
  HomeIcon,
  MagnifyingGlassIcon,
  PlusIcon,
  ChatBubbleLeftRightIcon,
  UserCircleIcon,
  ClipboardDocumentListIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  isHidden: {
    type: Boolean,
    default: false
  }
})

const route = useRoute()
const authStore = useAuthStore()

const isAuthenticated = computed(() => authStore.isAuthenticated)
const unreadMessages = ref(0)

const isActive = (path) => {
  if (path === '/') return route.path === '/'
  return route.path.startsWith(path)
}

const fetchUnreadMessages = async () => {
  if (!isAuthenticated.value) return
  try {
    const response = await api.get('/conversations/unread-count')
    unreadMessages.value = response.data.data?.count || 0
  } catch (e) {}
}

let interval = null

onMounted(() => {
  if (isAuthenticated.value) {
    fetchUnreadMessages()
    interval = setInterval(fetchUnreadMessages, 30000)
  }
})

onUnmounted(() => {
  if (interval) clearInterval(interval)
})

watch(isAuthenticated, (val) => {
  if (val) {
    fetchUnreadMessages()
    interval = setInterval(fetchUnreadMessages, 30000)
  } else {
    unreadMessages.value = 0
    if (interval) clearInterval(interval)
  }
})
</script>

<style scoped>
.mobile-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 40;
  transition: transform 0.3s ease;
}

.mobile-nav.nav-hidden {
  transform: translateY(100%);
}

.nav-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  box-shadow: 0 -4px 30px rgba(0, 0, 0, 0.08);
}

.nav-container {
  position: relative;
  display: flex;
  align-items: flex-end;
  justify-content: space-around;
  height: 60px;
  padding-bottom: env(safe-area-inset-bottom, 8px);
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
  padding: 8px 0;
  text-decoration: none;
  transition: all 0.2s ease;
}

.nav-icon-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 32px;
  border-radius: 16px;
  transition: all 0.2s ease;
}

.nav-item.active .nav-icon-wrapper {
  background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
}

.nav-icon {
  width: 22px;
  height: 22px;
  color: #6b7280;
  transition: all 0.2s ease;
}

.nav-item.active .nav-icon {
  color: white;
}

.nav-label {
  font-size: 10px;
  font-weight: 500;
  color: #6b7280;
  margin-top: 2px;
  transition: color 0.2s ease;
}

.nav-item.active .nav-label {
  color: #0891b2;
}

.nav-badge {
  position: absolute;
  top: -2px;
  right: -2px;
  min-width: 16px;
  height: 16px;
  padding: 0 4px;
  background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
  color: white;
  font-size: 9px;
  font-weight: 700;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
  animation: pulse-badge 2s infinite;
}

@keyframes pulse-badge {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

/* Sell Button - Floating */
.nav-sell {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  text-decoration: none;
  margin-top: -20px;
}

.sell-button {
  width: 52px;
  height: 52px;
  background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 20px rgba(8, 145, 178, 0.4);
  transition: all 0.3s ease;
  animation: float 3s ease-in-out infinite;
}

.sell-button:hover {
  transform: scale(1.05);
  box-shadow: 0 6px 25px rgba(8, 145, 178, 0.5);
}

.sell-button:active {
  transform: scale(0.95);
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-3px); }
}

.sell-icon {
  width: 26px;
  height: 26px;
  color: white;
}

.sell-label {
  color: #0891b2;
  font-weight: 600;
  margin-top: 4px;
}

/* Ripple effect on tap */
.nav-item:active .nav-icon-wrapper,
.sell-button:active {
  animation: ripple 0.4s ease;
}

@keyframes ripple {
  0% { box-shadow: 0 0 0 0 rgba(8, 145, 178, 0.4); }
  100% { box-shadow: 0 0 0 20px rgba(8, 145, 178, 0); }
}

/* Hide on desktop */
@media (min-width: 768px) {
  .mobile-nav {
    display: none;
  }
}
</style>
