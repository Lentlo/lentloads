<template>
  <header class="app-header">
    <div class="header-inner">
      <!-- Logo -->
      <router-link to="/" class="logo-link">
        <div class="logo-icon">
          <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Gradient definitions -->
            <defs>
              <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#7c3aed" />
                <stop offset="50%" stop-color="#a855f7" />
                <stop offset="100%" stop-color="#ec4899" />
              </linearGradient>
              <linearGradient id="arrowGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="#ffffff" />
                <stop offset="100%" stop-color="#f0f0f0" />
              </linearGradient>
            </defs>
            <!-- Background circle -->
            <circle cx="20" cy="20" r="20" fill="url(#logoGradient)"/>
            <!-- Letter L -->
            <path d="M14 10V26H26V23H17V10H14Z" fill="white"/>
            <!-- Upload arrow -->
            <path d="M26 18L22 14L22 21L24 21L24 14L26 18Z" fill="white" opacity="0.9"/>
            <path d="M22 14L20 16L24 16L22 14Z" fill="white" opacity="0.9"/>
          </svg>
        </div>
        <div class="logo-text">
          <span class="logo-name">Lentloads</span>
          <span class="logo-tagline">Post anything. Find everything.</span>
        </div>
      </router-link>

      <!-- Search Bar - Desktop -->
      <div class="search-container">
        <div class="search-box" :class="{ 'search-focused': searchFocused }">
          <MagnifyingGlassIcon class="search-icon" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search for mobiles, cars, jobs..."
            @focus="searchFocused = true"
            @blur="handleSearchBlur"
            @keyup.enter="handleSearch"
            @input="handleSearchInput"
          />
          <button
            v-if="searchQuery"
            @click="searchQuery = ''; suggestions = []"
            class="search-clear"
            aria-label="Clear search"
          >
            <XMarkIcon class="w-5 h-5" />
          </button>
          <button @click="handleSearch" class="search-btn">
            Search
          </button>
        </div>

        <!-- Search Suggestions Dropdown -->
        <div v-if="showSuggestions && suggestions.length" class="suggestions-dropdown">
          <div
            v-for="(item, index) in suggestions"
            :key="index"
            class="suggestion-item"
            @mousedown="selectSuggestion(item)"
          >
            <MagnifyingGlassIcon class="w-4 h-4 text-gray-400" />
            <span>{{ item.text }}</span>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="header-actions">
        <!-- Notifications -->
        <button
          v-if="isAuthenticated"
          @click="$router.push('/notifications')"
          class="action-btn"
          aria-label="View notifications"
        >
          <BellIcon class="action-icon" />
          <span v-if="unreadNotifications > 0" class="badge">
            {{ unreadNotifications > 9 ? '9+' : unreadNotifications }}
          </span>
        </button>

        <!-- Messages -->
        <router-link
          v-if="isAuthenticated"
          to="/messages"
          class="action-btn"
          aria-label="View messages"
        >
          <ChatBubbleLeftRightIcon class="action-icon" />
          <span v-if="unreadMessages > 0" class="badge">
            {{ unreadMessages > 9 ? '9+' : unreadMessages }}
          </span>
        </router-link>

        <!-- User Menu -->
        <div v-if="isAuthenticated" class="user-menu-container">
          <button @click="showUserMenu = !showUserMenu" class="user-btn">
            <div class="user-avatar">
              {{ user?.name?.charAt(0) || 'U' }}
            </div>
            <span class="user-name">{{ user?.name?.split(' ')[0] }}</span>
            <ChevronDownIcon class="w-4 h-4" />
          </button>

          <!-- Dropdown -->
          <Transition name="dropdown">
            <div v-if="showUserMenu" class="user-dropdown">
              <div class="dropdown-header">
                <div class="dropdown-avatar">{{ user?.name?.charAt(0) || 'U' }}</div>
                <div>
                  <p class="dropdown-name">{{ user?.name }}</p>
                  <p class="dropdown-email">{{ user?.email }}</p>
                </div>
              </div>

              <div class="dropdown-links">
                <router-link v-if="isAdmin" to="/admin" class="dropdown-link admin" @click="showUserMenu = false">
                  <ShieldCheckIcon class="w-5 h-5" />
                  Admin Panel
                </router-link>
                <router-link to="/dashboard" class="dropdown-link" @click="showUserMenu = false">
                  <Squares2X2Icon class="w-5 h-5" />
                  Dashboard
                </router-link>
                <router-link to="/my-listings" class="dropdown-link" @click="showUserMenu = false">
                  <ClipboardDocumentListIcon class="w-5 h-5" />
                  My Ads
                </router-link>
                <router-link to="/favorites" class="dropdown-link" @click="showUserMenu = false">
                  <HeartIcon class="w-5 h-5" />
                  Favorites
                </router-link>
                <router-link to="/settings" class="dropdown-link" @click="showUserMenu = false">
                  <Cog6ToothIcon class="w-5 h-5" />
                  Settings
                </router-link>
              </div>

              <div class="dropdown-footer">
                <button @click="handleLogout" class="logout-btn">
                  <ArrowRightOnRectangleIcon class="w-5 h-5" />
                  Logout
                </button>
              </div>
            </div>
          </Transition>
        </div>

        <!-- Login Button -->
        <router-link v-if="!isAuthenticated" to="/login" class="login-btn">
          Login
        </router-link>

        <!-- Sell Button -->
        <router-link to="/sell" class="sell-btn">
          <PlusIcon class="w-5 h-5" />
          <span>SELL</span>
        </router-link>
      </div>
    </div>

    <!-- Mobile Search -->
    <div class="mobile-search">
      <div class="mobile-search-box">
        <MagnifyingGlassIcon class="w-5 h-5 text-gray-400" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search..."
          @keyup.enter="handleSearch"
        />
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import {
  PlusIcon,
  BellIcon,
  ChatBubbleLeftRightIcon,
  ChevronDownIcon,
  Squares2X2Icon,
  ClipboardDocumentListIcon,
  HeartIcon,
  Cog6ToothIcon,
  ArrowRightOnRectangleIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
  ShieldCheckIcon,
} from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()

const showUserMenu = ref(false)
const searchQuery = ref('')
const searchFocused = ref(false)
const suggestions = ref([])
const showSuggestions = ref(false)
const unreadNotifications = ref(0)
const unreadMessages = ref(0)
let searchTimeout = null

const isAuthenticated = computed(() => authStore.isAuthenticated)
const isAdmin = computed(() => authStore.isAdmin)
const user = computed(() => authStore.user)

const fetchUnreadCounts = async () => {
  if (!isAuthenticated.value) return
  try {
    const [messagesRes, notificationsRes] = await Promise.all([
      api.get('/conversations/unread-count'),
      api.get('/notifications/unread-count')
    ])
    unreadMessages.value = messagesRes.data.data?.count || 0
    unreadNotifications.value = notificationsRes.data.data?.count || 0
  } catch (e) {
    // Silently fail
  }
}

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: '/search', query: { q: searchQuery.value } })
    showSuggestions.value = false
  }
}

const handleSearchInput = () => {
  clearTimeout(searchTimeout)
  if (searchQuery.value.length >= 2) {
    searchTimeout = setTimeout(fetchSuggestions, 300)
  } else {
    suggestions.value = []
  }
}

const fetchSuggestions = async () => {
  try {
    const res = await api.get('/search/suggestions', { params: { q: searchQuery.value } })
    suggestions.value = res.data.data?.suggestions || []
    showSuggestions.value = true
  } catch (e) {
    suggestions.value = []
  }
}

const selectSuggestion = (item) => {
  searchQuery.value = item.text
  showSuggestions.value = false
  handleSearch()
}

const handleSearchBlur = () => {
  setTimeout(() => {
    searchFocused.value = false
    showSuggestions.value = false
  }, 200)
}

const handleLogout = async () => {
  showUserMenu.value = false
  await authStore.logout()
  router.push('/')
}

const closeMenu = (e) => {
  if (showUserMenu.value && !e.target.closest('.user-menu-container')) {
    showUserMenu.value = false
  }
}

let countInterval = null

onMounted(() => {
  document.addEventListener('click', closeMenu)
  if (isAuthenticated.value) {
    fetchUnreadCounts()
    countInterval = setInterval(fetchUnreadCounts, 30000)
  }
})

onUnmounted(() => {
  document.removeEventListener('click', closeMenu)
  if (countInterval) clearInterval(countInterval)
  if (searchTimeout) clearTimeout(searchTimeout)
})

watch(isAuthenticated, (newVal) => {
  if (newVal) {
    fetchUnreadCounts()
    countInterval = setInterval(fetchUnreadCounts, 30000)
  } else {
    unreadMessages.value = 0
    unreadNotifications.value = 0
    if (countInterval) clearInterval(countInterval)
  }
})
</script>

<style scoped>
/* Header Container */
.app-header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: linear-gradient(135deg, #7c3aed 0%, #9333ea 50%, #a855f7 100%);
  box-shadow: 0 4px 20px rgba(124, 58, 237, 0.25);
}

.header-inner {
  max-width: 1280px;
  margin: 0 auto;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 20px;
}

/* Logo */
.logo-link {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  flex-shrink: 0;
}

.logo-icon {
  width: 42px;
  height: 42px;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
  transition: transform 0.3s ease;
}

.logo-link:hover .logo-icon {
  transform: scale(1.05) rotate(-5deg);
}

.logo-text {
  display: none;
}

@media (min-width: 768px) {
  .logo-text {
    display: flex;
    flex-direction: column;
  }
}

.logo-name {
  font-size: 22px;
  font-weight: 800;
  color: white;
  letter-spacing: -0.5px;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.logo-tagline {
  font-size: 11px;
  color: rgba(255, 255, 255, 0.85);
  font-weight: 500;
  margin-top: -2px;
}

/* Search */
.search-container {
  flex: 1;
  max-width: 600px;
  position: relative;
  display: none;
}

@media (min-width: 768px) {
  .search-container {
    display: block;
  }
}

.search-box {
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 12px;
  padding: 4px 4px 4px 16px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.search-box.search-focused {
  background: white;
  border-color: rgba(255, 255, 255, 0.5);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.search-icon {
  width: 20px;
  height: 20px;
  color: #9ca3af;
  flex-shrink: 0;
}

.search-box input {
  flex: 1;
  padding: 10px 12px;
  border: none;
  background: transparent;
  font-size: 15px;
  color: #1f2937;
  outline: none;
  min-width: 0;
}

.search-box input::placeholder {
  color: #9ca3af;
}

.search-clear {
  padding: 8px;
  color: #9ca3af;
  transition: color 0.2s;
}

.search-clear:hover {
  color: #4b5563;
}

.search-btn {
  padding: 10px 20px;
  background: linear-gradient(135deg, #7c3aed 0%, #9333ea 100%);
  color: white;
  font-weight: 600;
  font-size: 14px;
  border-radius: 8px;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(124, 58, 237, 0.3);
}

.search-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(124, 58, 237, 0.4);
}

/* Suggestions */
.suggestions-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  margin-top: 8px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  z-index: 100;
}

.suggestion-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  cursor: pointer;
  transition: background 0.15s;
}

.suggestion-item:hover {
  background: #f3f4f6;
}

.suggestion-item span {
  color: #374151;
  font-size: 14px;
}

/* Actions */
.header-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.action-btn {
  position: relative;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  color: white;
  transition: all 0.2s ease;
  -webkit-tap-highlight-color: transparent;
}

.action-btn:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: translateY(-2px);
}

.action-icon {
  width: 24px;
  height: 24px;
}

.badge {
  position: absolute;
  top: 4px;
  right: 4px;
  min-width: 18px;
  height: 18px;
  padding: 0 5px;
  background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%);
  color: white;
  font-size: 11px;
  font-weight: 700;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(236, 72, 153, 0.4);
  animation: bounceIn 0.3s ease;
}

@keyframes bounceIn {
  0% { transform: scale(0); }
  50% { transform: scale(1.2); }
  100% { transform: scale(1); }
}

/* User Menu */
.user-menu-container {
  position: relative;
}

.user-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px 6px 6px;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border-radius: 50px;
  color: white;
  transition: all 0.2s ease;
  -webkit-tap-highlight-color: transparent;
}

.user-btn:hover {
  background: rgba(255, 255, 255, 0.25);
}

.user-avatar {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.user-name {
  font-weight: 600;
  font-size: 14px;
  display: none;
}

@media (min-width: 640px) {
  .user-name {
    display: block;
  }
}

/* Dropdown */
.user-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 280px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  z-index: 100;
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.95);
}

.dropdown-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: linear-gradient(135deg, #f8f5ff 0%, #fdf2f8 100%);
  border-bottom: 1px solid #f3f4f6;
}

.dropdown-avatar {
  width: 44px;
  height: 44px;
  background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 18px;
  box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
}

.dropdown-name {
  font-weight: 600;
  color: #1f2937;
  font-size: 15px;
}

.dropdown-email {
  font-size: 13px;
  color: #6b7280;
  margin-top: 2px;
}

.dropdown-links {
  padding: 8px;
}

.dropdown-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  color: #374151;
  text-decoration: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.15s ease;
}

.dropdown-link:hover {
  background: #f3f4f6;
  color: #7c3aed;
}

.dropdown-link.admin {
  color: #7c3aed;
  background: #f3e8ff;
}

.dropdown-link.admin:hover {
  background: #ede9fe;
}

.dropdown-footer {
  padding: 8px;
  border-top: 1px solid #f3f4f6;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 12px;
  color: #dc2626;
  font-size: 14px;
  font-weight: 500;
  border-radius: 10px;
  transition: background 0.15s ease;
}

.logout-btn:hover {
  background: #fef2f2;
}

/* Login & Sell Buttons */
.login-btn {
  padding: 10px 20px;
  color: white;
  font-weight: 600;
  font-size: 14px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.login-btn:hover {
  background: rgba(255, 255, 255, 0.15);
  border-radius: 8px;
}

.sell-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 10px 20px;
  background: white;
  color: #7c3aed;
  font-weight: 700;
  font-size: 14px;
  text-decoration: none;
  border-radius: 10px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
}

.sell-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.sell-btn span {
  display: none;
}

@media (min-width: 480px) {
  .sell-btn span {
    display: inline;
  }
}

/* Mobile Search */
.mobile-search {
  padding: 0 16px 12px;
  display: block;
}

@media (min-width: 768px) {
  .mobile-search {
    display: none;
  }
}

.mobile-search-box {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 16px;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.mobile-search-box input {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 15px;
  color: #1f2937;
  outline: none;
}

.mobile-search-box input::placeholder {
  color: #9ca3af;
}
</style>
