<template>
  <nav class="mobile-nav">
    <div class="nav-container">
      <!-- Home / My Ads -->
      <router-link
        :to="isAuthenticated ? '/my-listings' : '/'"
        class="nav-item"
        :class="{ active: isAuthenticated ? isActive('/my-listings') : isActive('/') }"
        @click="handleNavClick($event, isAuthenticated ? '/my-listings' : '/')"
      >
        <component :is="isAuthenticated ? ClipboardDocumentListIcon : HomeIcon" class="nav-icon" />
        <span class="nav-label">{{ isAuthenticated ? 'My Ads' : 'Home' }}</span>
      </router-link>

      <!-- Search -->
      <router-link
        to="/search"
        class="nav-item"
        :class="{ active: isActive('/search') }"
        @click="handleNavClick($event, '/search')"
      >
        <MagnifyingGlassIcon class="nav-icon" />
        <span class="nav-label">Search</span>
      </router-link>

      <!-- Post Ad Button -->
      <router-link
        to="/sell"
        class="nav-item sell-item"
        @click="handleNavClick($event, '/sell')"
      >
        <div class="sell-button">
          <PlusIcon class="sell-icon" />
        </div>
        <span class="nav-label">Post Ad</span>
      </router-link>

      <!-- Messages -->
      <router-link
        to="/messages"
        class="nav-item"
        :class="{ active: isActive('/messages') }"
        @click="handleNavClick($event, '/messages')"
      >
        <div class="icon-with-badge">
          <ChatBubbleLeftRightIcon class="nav-icon" />
          <span v-if="unreadMessages > 0" class="badge">{{ unreadMessages > 9 ? '9+' : unreadMessages }}</span>
        </div>
        <span class="nav-label">Chat</span>
      </router-link>

      <!-- Account -->
      <router-link
        :to="isAuthenticated ? '/dashboard' : '/login'"
        class="nav-item"
        :class="{ active: isActive('/dashboard') || isActive('/settings') || isActive('/login') }"
        @click="handleNavClick($event, isAuthenticated ? '/dashboard' : '/login')"
      >
        <UserCircleIcon class="nav-icon" />
        <span class="nav-label">{{ isAuthenticated ? 'Account' : 'Login' }}</span>
      </router-link>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
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

const route = useRoute()
const router = useRouter()
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

// Fallback navigation handler - if router-link fails, force navigation
const handleNavClick = (e, targetPath) => {
  // Use route.path from Vue Router (works with both hash and history mode)
  const currentRoutePath = route.path
  if (currentRoutePath === targetPath) return // Already on this page

  // Set a short timeout to check if navigation happened
  setTimeout(() => {
    // If we're still on the same page after 300ms, force navigation via router
    if (route.path === currentRoutePath) {
      router.push(targetPath).catch(() => {
        // If router.push also fails, force reload
        window.location.reload()
      })
    }
  }, 300)
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
  z-index: 9999; /* Higher than everything else */
  background: #ffffff; /* Solid background - no transparency issues */
  border-top: 1px solid #e5e7eb;
  padding-bottom: env(safe-area-inset-bottom, 0);
  /* Explicitly ensure clicks work */
  pointer-events: auto;
  /* Remove backdrop-filter - can cause touch issues on iOS */
}

.nav-container {
  display: flex;
  align-items: stretch;
  justify-content: space-around;
  height: 64px;
  pointer-events: auto;
}

/*
 * CRITICAL: Minimum 48x48px tap target for iOS/Android accessibility
 * Apple HIG: 44pt minimum
 * Material Design: 48dp minimum
 */
.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
  min-width: 48px;
  min-height: 48px;
  padding: 6px 0;
  text-decoration: none;
  -webkit-tap-highlight-color: rgba(0, 0, 0, 0.1); /* Visible tap feedback */
  touch-action: manipulation;
  cursor: pointer;
  user-select: none;
  position: relative;
  pointer-events: auto;
  /* Ensure the element is interactive */
  -webkit-touch-callout: none;
}

/* Active state feedback - immediate */
.nav-item:active {
  opacity: 0.7;
}

.nav-icon {
  width: 26px;
  height: 26px;
  color: #6b7280;
  flex-shrink: 0;
}

.nav-item.active .nav-icon {
  color: #7c3aed;
}

.nav-label {
  font-size: 11px;
  font-weight: 600;
  color: #6b7280;
  margin-top: 3px;
  line-height: 1;
}

.nav-item.active .nav-label {
  color: #7c3aed;
}

/* Post Ad button - larger center button */
.sell-item {
  position: relative;
}

.sell-button {
  width: 52px;
  height: 52px;
  background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: -18px;
  box-shadow: 0 4px 12px rgba(124, 58, 237, 0.4);
}

.sell-item:active .sell-button {
  transform: scale(0.95);
  box-shadow: 0 2px 8px rgba(124, 58, 237, 0.3);
}

.sell-icon {
  width: 26px;
  height: 26px;
  color: white;
}

.sell-item .nav-label {
  color: #7c3aed;
  font-weight: 700;
  font-size: 10px;
}

/* Badge */
.icon-with-badge {
  position: relative;
}

.badge {
  position: absolute;
  top: -4px;
  right: -8px;
  min-width: 16px;
  height: 16px;
  padding: 0 4px;
  background: #ef4444;
  color: white;
  font-size: 10px;
  font-weight: 600;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Hide on desktop */
@media (min-width: 768px) {
  .mobile-nav {
    display: none;
  }
}
</style>
