<template>
  <header class="app-header">
    <div class="header-inner">
      <!-- Logo -->
      <router-link to="/" class="logo-link">
        <div class="logo-icon">
          <svg viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <linearGradient id="logoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#6366f1" />
                <stop offset="100%" stop-color="#8b5cf6" />
              </linearGradient>
            </defs>
            <!-- Rounded square background -->
            <rect x="2" y="2" width="40" height="40" rx="10" fill="url(#logoGrad)"/>
            <!-- L shape -->
            <path d="M14 12V28H28V24H18V12H14Z" fill="white"/>
            <!-- Dot/circle accent -->
            <circle cx="30" cy="14" r="4" fill="#fbbf24"/>
          </svg>
        </div>
        <div class="logo-text">
          <span class="logo-name">Lentlo <span class="logo-accent">Ads</span></span>
          <span class="logo-tagline">Post Free. Sell Fast.</span>
        </div>
      </router-link>

      <!-- Search Bar - Desktop Only -->
      <div class="search-container">
        <div class="search-box" :class="{ focused: searchFocused }">
          <MagnifyingGlassIcon class="search-icon" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search for cars, mobiles, jobs..."
            @focus="searchFocused = true"
            @blur="handleSearchBlur"
            @keyup.enter="handleSearch"
            @input="handleSearchInput"
          />
          <button v-if="searchQuery" @click="clearSearch" class="clear-btn">
            <XMarkIcon class="w-4 h-4" />
          </button>
          <button @click="handleSearch" class="search-submit">
            <MagnifyingGlassIcon class="w-5 h-5" />
          </button>
        </div>

        <!-- Suggestions -->
        <div v-if="showSuggestions && suggestions.length" class="suggestions">
          <div
            v-for="(item, i) in suggestions"
            :key="i"
            class="suggestion"
            @mousedown="selectSuggestion(item)"
          >
            <MagnifyingGlassIcon class="w-4 h-4" />
            <span>{{ item.text }}</span>
          </div>
        </div>
      </div>

      <!-- Right Actions -->
      <div class="header-right">
        <!-- Notifications -->
        <button
          v-if="isAuthenticated"
          @click="$router.push('/notifications')"
          class="icon-btn"
        >
          <BellIcon class="w-6 h-6" />
          <span v-if="unreadNotifications > 0" class="notif-badge">
            {{ unreadNotifications > 9 ? '9+' : unreadNotifications }}
          </span>
        </button>

        <!-- Messages -->
        <router-link v-if="isAuthenticated" to="/messages" class="icon-btn">
          <ChatBubbleLeftRightIcon class="w-6 h-6" />
          <span v-if="unreadMessages > 0" class="notif-badge">
            {{ unreadMessages > 9 ? '9+' : unreadMessages }}
          </span>
        </router-link>

        <!-- User Menu -->
        <div v-if="isAuthenticated" class="user-menu">
          <button @click="showUserMenu = !showUserMenu" class="user-btn">
            <div class="avatar">{{ user?.name?.charAt(0) || 'U' }}</div>
            <ChevronDownIcon class="w-4 h-4 chevron" />
          </button>

          <Transition name="menu">
            <div v-if="showUserMenu" class="dropdown">
              <div class="dropdown-user">
                <div class="dropdown-avatar">{{ user?.name?.charAt(0) || 'U' }}</div>
                <div>
                  <p class="dropdown-name">{{ user?.name }}</p>
                  <p class="dropdown-email">{{ user?.email }}</p>
                </div>
              </div>

              <div class="dropdown-body">
                <router-link v-if="isAdmin" to="/admin" class="dropdown-item admin" @click="showUserMenu = false">
                  <ShieldCheckIcon class="w-5 h-5" />
                  Admin Panel
                </router-link>
                <router-link to="/dashboard" class="dropdown-item" @click="showUserMenu = false">
                  <Squares2X2Icon class="w-5 h-5" />
                  Dashboard
                </router-link>
                <router-link to="/my-listings" class="dropdown-item" @click="showUserMenu = false">
                  <ClipboardDocumentListIcon class="w-5 h-5" />
                  My Ads
                </router-link>
                <router-link to="/favorites" class="dropdown-item" @click="showUserMenu = false">
                  <HeartIcon class="w-5 h-5" />
                  Favorites
                </router-link>
                <router-link to="/settings" class="dropdown-item" @click="showUserMenu = false">
                  <Cog6ToothIcon class="w-5 h-5" />
                  Settings
                </router-link>
              </div>

              <div class="dropdown-foot">
                <button @click="handleLogout" class="logout-btn">
                  <ArrowRightOnRectangleIcon class="w-5 h-5" />
                  Logout
                </button>
              </div>
            </div>
          </Transition>
        </div>

        <!-- Login -->
        <router-link v-if="!isAuthenticated" to="/login" class="login-link">
          Login
        </router-link>

        <!-- Post Ad Button -->
        <router-link to="/sell" class="post-btn">
          <PlusIcon class="w-5 h-5" />
          <span class="post-text">Post Free Ad</span>
        </router-link>
      </div>
    </div>

    <!-- Mobile Search -->
    <div class="mobile-search">
      <div class="mobile-search-inner">
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
    const [msgRes, notifRes] = await Promise.all([
      api.get('/conversations/unread-count'),
      api.get('/notifications/unread-count')
    ])
    unreadMessages.value = msgRes.data.data?.count || 0
    unreadNotifications.value = notifRes.data.data?.count || 0
  } catch (e) {}
}

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: '/search', query: { q: searchQuery.value } })
    showSuggestions.value = false
  }
}

const clearSearch = () => {
  searchQuery.value = ''
  suggestions.value = []
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
  if (showUserMenu.value && !e.target.closest('.user-menu')) {
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

watch(isAuthenticated, (val) => {
  if (val) {
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
.app-header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: white;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.header-inner {
  max-width: 1280px;
  margin: 0 auto;
  padding: 10px 16px;
  display: flex;
  align-items: center;
  gap: 16px;
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
  width: 40px;
  height: 40px;
}

.logo-icon svg {
  width: 100%;
  height: 100%;
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
  font-size: 20px;
  font-weight: 800;
  color: #1f2937;
  letter-spacing: -0.5px;
}

.logo-accent {
  color: #6366f1;
}

.logo-tagline {
  font-size: 11px;
  color: #6b7280;
  font-weight: 500;
  margin-top: -2px;
}

/* Search */
.search-container {
  flex: 1;
  max-width: 500px;
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
  background: #f3f4f6;
  border: 2px solid transparent;
  border-radius: 10px;
  padding: 0 4px 0 12px;
  transition: all 0.2s;
}

.search-box.focused {
  background: white;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.search-icon {
  width: 20px;
  height: 20px;
  color: #9ca3af;
  flex-shrink: 0;
}

.search-box input {
  flex: 1;
  padding: 10px 8px;
  border: none;
  background: transparent;
  font-size: 16px;
  color: #1f2937;
  outline: none;
}

.search-box input::placeholder {
  color: #9ca3af;
}

.clear-btn {
  padding: 6px;
  color: #9ca3af;
}

.clear-btn:hover {
  color: #6b7280;
}

.search-submit {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #6366f1;
  color: white;
  border-radius: 8px;
  transition: background 0.2s;
}

.search-submit:hover {
  background: #4f46e5;
}

/* Suggestions */
.suggestions {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  margin-top: 6px;
  background: white;
  border-radius: 10px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
  overflow: hidden;
  z-index: 100;
}

.suggestion {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  color: #374151;
  font-size: 14px;
  cursor: pointer;
}

.suggestion:hover {
  background: #f3f4f6;
}

/* Right Actions */
.header-right {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-left: auto;
}

.icon-btn {
  position: relative;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4b5563;
  border-radius: 10px;
  transition: all 0.2s;
  -webkit-tap-highlight-color: transparent;
}

.icon-btn:hover {
  background: #f3f4f6;
  color: #6366f1;
}

.notif-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  min-width: 16px;
  height: 16px;
  padding: 0 4px;
  background: #ef4444;
  color: white;
  font-size: 10px;
  font-weight: 700;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* User Menu */
.user-menu {
  position: relative;
}

.user-btn {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px 4px 4px;
  border-radius: 20px;
  transition: background 0.2s;
  -webkit-tap-highlight-color: transparent;
}

.user-btn:hover {
  background: #f3f4f6;
}

.avatar {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 14px;
}

.chevron {
  color: #6b7280;
}

/* Dropdown */
.dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 260px;
  background: white;
  border-radius: 14px;
  box-shadow: 0 15px 50px rgba(0, 0, 0, 0.12);
  overflow: hidden;
  z-index: 100;
}

.menu-enter-active,
.menu-leave-active {
  transition: all 0.2s ease;
}

.menu-enter-from,
.menu-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.dropdown-user {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px;
  background: #f9fafb;
  border-bottom: 1px solid #f3f4f6;
}

.dropdown-avatar {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 16px;
}

.dropdown-name {
  font-weight: 600;
  color: #1f2937;
  font-size: 14px;
}

.dropdown-email {
  font-size: 12px;
  color: #6b7280;
  margin-top: 1px;
}

.dropdown-body {
  padding: 6px;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  color: #374151;
  text-decoration: none;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.15s;
}

.dropdown-item:hover {
  background: #f3f4f6;
  color: #6366f1;
}

.dropdown-item.admin {
  color: #6366f1;
  background: #eef2ff;
}

.dropdown-item.admin:hover {
  background: #e0e7ff;
}

.dropdown-foot {
  padding: 6px;
  border-top: 1px solid #f3f4f6;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 10px 12px;
  color: #dc2626;
  font-size: 14px;
  border-radius: 8px;
  transition: background 0.15s;
}

.logout-btn:hover {
  background: #fef2f2;
}

/* Login */
.login-link {
  padding: 8px 16px;
  color: #374151;
  font-weight: 600;
  font-size: 14px;
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.2s;
  display: none;
}

@media (min-width: 480px) {
  .login-link {
    display: block;
  }
}

.login-link:hover {
  background: #f3f4f6;
  color: #6366f1;
}

/* Post Ad Button */
.post-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px 12px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  font-weight: 600;
  font-size: 13px;
  text-decoration: none;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
  transition: all 0.2s;
  white-space: nowrap;
}

.post-btn:hover {
  box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
  transform: translateY(-1px);
}

.post-text {
  display: none;
}

@media (min-width: 400px) {
  .post-text {
    display: inline;
  }
}

/* Mobile Search */
.mobile-search {
  padding: 0 16px 10px;
  display: block;
}

@media (min-width: 768px) {
  .mobile-search {
    display: none;
  }
}

.mobile-search-inner {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  background: #f3f4f6;
  border-radius: 10px;
}

.mobile-search-inner input {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 16px;
  color: #1f2937;
  outline: none;
}

.mobile-search-inner input::placeholder {
  color: #9ca3af;
}
</style>
