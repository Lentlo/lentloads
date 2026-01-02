<template>
  <header class="app-header" :class="{ 'header-scrolled': isScrolled, 'search-hidden': !showMobileSearch }">
    <!-- Main Header Bar -->
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
            <rect x="2" y="2" width="40" height="40" rx="10" fill="url(#logoGrad)"/>
            <path d="M14 12V28H28V24H18V12H14Z" fill="white"/>
            <circle cx="30" cy="14" r="4" fill="#fbbf24"/>
          </svg>
        </div>
        <span class="logo-name-mobile">Lentlo</span>
      </router-link>

      <!-- Location Selector (Desktop) -->
      <button @click="showLocationPicker = true" class="location-btn desktop-only">
        <MapPinIcon class="w-5 h-5" />
        <span class="location-text">{{ currentLocationName }}</span>
        <ChevronDownIcon class="w-4 h-4" />
      </button>

      <!-- Search Bar - Desktop Only -->
      <div class="search-container desktop-only">
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
          <BellIcon class="w-5 h-5" />
          <span v-if="unreadNotifications > 0" class="notif-badge">
            {{ unreadNotifications > 9 ? '9+' : unreadNotifications }}
          </span>
        </button>

        <!-- Messages -->
        <router-link v-if="isAuthenticated" to="/messages" class="icon-btn">
          <ChatBubbleLeftRightIcon class="w-5 h-5" />
          <span v-if="unreadMessages > 0" class="notif-badge">
            {{ unreadMessages > 9 ? '9+' : unreadMessages }}
          </span>
        </router-link>

        <!-- User Menu -->
        <div v-if="isAuthenticated" class="user-menu">
          <button @click="showUserMenu = !showUserMenu" class="user-btn">
            <div class="avatar">{{ user?.name?.charAt(0) || 'U' }}</div>
            <ChevronDownIcon class="w-4 h-4 chevron desktop-only" />
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
        <router-link v-if="!isAuthenticated" to="/login" class="login-link desktop-only">
          Login
        </router-link>

        <!-- Post Ad Button -->
        <router-link to="/sell" class="post-btn">
          <PlusIcon class="w-4 h-4" />
          <span class="post-text">Post Free Ad</span>
        </router-link>
      </div>
    </div>

    <!-- Mobile Search Bar (Collapsible) -->
    <div class="mobile-search" :class="{ 'search-visible': showMobileSearch }">
      <button @click="showLocationPicker = true" class="mobile-location-btn">
        <MapPinIcon class="w-4 h-4" />
        <span>{{ shortLocationName }}</span>
        <ChevronDownIcon class="w-3 h-3" />
      </button>
      <div class="mobile-search-inner">
        <MagnifyingGlassIcon class="w-4 h-4 text-white/60 flex-shrink-0" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search for anything..."
          @keyup.enter="handleSearch"
        />
        <button v-if="searchQuery" @click="clearSearch" class="mobile-clear-btn">
          <XMarkIcon class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Location Picker Modal -->
    <div v-if="showLocationPicker" class="location-picker-modal">
      <div class="location-picker-backdrop" @click="showLocationPicker = false"></div>
      <div class="location-picker-sheet">
        <div class="location-picker-header">
          <h3>Select Location</h3>
          <button @click="showLocationPicker = false"><XMarkIcon class="w-6 h-6" /></button>
        </div>

        <!-- Search Cities -->
        <div class="location-search">
          <MagnifyingGlassIcon class="w-5 h-5 text-gray-400" />
          <input
            v-model="citySearch"
            type="text"
            placeholder="Search city, area or locality..."
            @input="searchCities"
          />
        </div>

        <!-- Use Current Location -->
        <button @click="useCurrentLocation" class="use-location-btn" :disabled="detectingLocation">
          <div class="use-location-icon">
            <MapPinIcon class="w-5 h-5" />
          </div>
          <div>
            <span class="use-location-title">{{ detectingLocation ? 'Detecting...' : 'Use current location' }}</span>
            <span class="use-location-subtitle">Enable GPS for accurate location</span>
          </div>
        </button>

        <!-- City Results or Popular Cities -->
        <div class="location-list">
          <template v-if="cityResults.length">
            <div class="location-section-title">Search Results</div>
            <button
              v-for="city in cityResults"
              :key="city.id"
              @click="selectCity(city)"
              class="location-item"
            >
              <MapPinIcon class="w-4 h-4 text-gray-400" />
              <div>
                <span class="location-name">{{ city.name }}</span>
                <span class="location-state">{{ city.state }}</span>
              </div>
            </button>
          </template>
          <template v-else>
            <div class="location-section-title">Popular Cities</div>
            <div class="popular-cities-grid">
              <button
                v-for="city in popularCities"
                :key="city.id"
                @click="selectCity(city)"
                class="popular-city-btn"
              >
                {{ city.name }}
              </button>
            </div>
            <button @click="selectAllIndia" class="all-india-btn">
              <GlobeAltIcon class="w-5 h-5" />
              <span>All India</span>
            </button>
          </template>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'
import api from '@/services/api'
import debounce from 'lodash/debounce'
import {
  PlusIcon,
  BellIcon,
  ChatBubbleLeftRightIcon,
  ChevronDownIcon,
  Squares2X2Icon,
  ClipboardDocumentListIcon,
  MapPinIcon,
  GlobeAltIcon,
  HeartIcon,
  Cog6ToothIcon,
  ArrowRightOnRectangleIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
  ShieldCheckIcon,
} from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const appStore = useAppStore()

const showUserMenu = ref(false)
const searchQuery = ref('')
const searchFocused = ref(false)
const suggestions = ref([])
const showSuggestions = ref(false)
const unreadNotifications = ref(0)
const unreadMessages = ref(0)
let searchTimeout = null

// Scroll-based header state
const isScrolled = ref(false)
const showMobileSearch = ref(true)
let lastScrollY = 0
let scrollTimeout = null

// Location picker state
const showLocationPicker = ref(false)
const citySearch = ref('')
const cityResults = ref([])
const detectingLocation = ref(false)
const selectedLocation = ref(null)

// Handle scroll for showing/hiding mobile search
const handleScroll = () => {
  const currentScrollY = window.scrollY

  // Add scrolled class when past threshold
  isScrolled.value = currentScrollY > 10

  // Show search on scroll up, hide on scroll down
  if (currentScrollY < 50) {
    // Always show search at top of page
    showMobileSearch.value = true
  } else if (currentScrollY > lastScrollY + 5) {
    // Scrolling down - hide search
    showMobileSearch.value = false
  } else if (currentScrollY < lastScrollY - 5) {
    // Scrolling up - show search
    showMobileSearch.value = true
  }

  lastScrollY = currentScrollY
}

const isAuthenticated = computed(() => authStore.isAuthenticated)
const isAdmin = computed(() => authStore.isAdmin)
const user = computed(() => authStore.user)

// Popular cities from store
const popularCities = computed(() => appStore.popularCities?.slice(0, 12) || [])

// Current location display
const currentLocationName = computed(() => {
  const loc = selectedLocation.value || appStore.currentLocation
  if (loc?.city) return loc.city
  // If we have coordinates but no city name, show "Near Me"
  if (loc?.latitude && loc?.longitude) return 'Near Me'
  return 'All India'
})

const shortLocationName = computed(() => {
  const name = currentLocationName.value
  return name.length > 12 ? name.substring(0, 12) + '...' : name
})

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
  // If on search page, clear the query param
  if (route.path === '/search' && route.query.q) {
    const newQuery = { ...route.query }
    delete newQuery.q
    router.replace({ query: newQuery })
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
  if (showUserMenu.value && !e.target.closest('.user-menu')) {
    showUserMenu.value = false
  }
}

// Location picker functions
const searchCities = debounce(async () => {
  if (citySearch.value.length < 2) {
    cityResults.value = []
    return
  }
  try {
    const res = await api.get('/locations/search-cities', { params: { q: citySearch.value } })
    cityResults.value = res.data.data || []
  } catch (e) {
    cityResults.value = []
  }
}, 300)

const selectCity = (city) => {
  selectedLocation.value = {
    city: city.name,
    state: city.state,
    latitude: city.latitude,
    longitude: city.longitude,
  }
  // Save to localStorage for persistence
  localStorage.setItem('selectedCity', JSON.stringify(selectedLocation.value))
  appStore.setLocation(selectedLocation.value)
  showLocationPicker.value = false
  citySearch.value = ''
  cityResults.value = []
  // Navigate to search with city filter
  router.push({ path: '/search', query: { city: city.name } })
}

const selectAllIndia = () => {
  selectedLocation.value = null
  localStorage.removeItem('selectedCity')
  showLocationPicker.value = false
  router.push({ path: '/search' })
}

const useCurrentLocation = async () => {
  detectingLocation.value = true
  try {
    await appStore.detectLocation()
    selectedLocation.value = appStore.currentLocation
    localStorage.setItem('selectedCity', JSON.stringify(appStore.currentLocation))
    showLocationPicker.value = false
    if (appStore.currentLocation?.city) {
      router.push({ path: '/search', query: { city: appStore.currentLocation.city } })
    } else {
      // No city found but we have coordinates - enable Near Me mode
      localStorage.setItem('nearMeActive', 'true')
      router.push({ path: '/search' })
    }
  } catch (e) {
    console.error('Location detection failed:', e)
    // Show error to user
    import('vue3-toastify').then(({ toast }) => {
      toast.error('Could not detect location. Please try again.')
    })
  } finally {
    detectingLocation.value = false
  }
}

// Load saved city on init
const loadSavedCity = () => {
  const saved = localStorage.getItem('selectedCity')
  if (saved) {
    try {
      selectedLocation.value = JSON.parse(saved)
    } catch (e) {}
  }
}

let countInterval = null

onMounted(() => {
  loadSavedCity()
  document.addEventListener('click', closeMenu)
  window.addEventListener('scroll', handleScroll, { passive: true })
  if (isAuthenticated.value) {
    fetchUnreadCounts()
    countInterval = setInterval(fetchUnreadCounts, 30000)
  }
})

onUnmounted(() => {
  document.removeEventListener('click', closeMenu)
  window.removeEventListener('scroll', handleScroll)
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

// Sync search query with URL
watch(() => route.query.q, (newQ) => {
  if (route.path === '/search') {
    searchQuery.value = newQ || ''
  }
}, { immediate: true })
</script>

<style scoped>
.app-header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
  transition: box-shadow 0.3s ease;
}

@media (min-width: 768px) {
  .app-header {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  }
}

.app-header.header-scrolled {
  box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
}

@media (min-width: 768px) {
  .app-header.header-scrolled {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }
}

.header-inner {
  max-width: 1280px;
  margin: 0 auto;
  padding: 8px 12px;
  display: flex;
  align-items: center;
  gap: 12px;
}

@media (min-width: 768px) {
  .header-inner {
    padding: 10px 16px;
    gap: 16px;
  }
}

/* Desktop only elements */
.desktop-only {
  display: none !important;
}

@media (min-width: 768px) {
  .desktop-only {
    display: flex !important;
  }
}

/* Logo */
.logo-link {
  display: flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  flex-shrink: 0;
}

.logo-icon {
  width: 32px;
  height: 32px;
}

@media (min-width: 768px) {
  .logo-icon {
    width: 40px;
    height: 40px;
  }
}

.logo-icon svg {
  width: 100%;
  height: 100%;
}

.logo-name-mobile {
  font-size: 18px;
  font-weight: 800;
  color: white;
  letter-spacing: -0.5px;
}

@media (min-width: 768px) {
  .logo-name-mobile {
    color: #1f2937;
  }
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
  gap: 4px;
  margin-left: auto;
}

@media (min-width: 768px) {
  .header-right {
    gap: 6px;
  }
}

.icon-btn {
  position: relative;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.9);
  border-radius: 10px;
  transition: all 0.2s;
  -webkit-tap-highlight-color: transparent;
}

@media (min-width: 768px) {
  .icon-btn {
    width: 40px;
    height: 40px;
    color: #4b5563;
  }
}

.icon-btn:hover {
  background: rgba(255, 255, 255, 0.15);
}

@media (min-width: 768px) {
  .icon-btn:hover {
    background: #f3f4f6;
    color: #6366f1;
  }
}

.notif-badge {
  position: absolute;
  top: 2px;
  right: 2px;
  min-width: 16px;
  height: 16px;
  padding: 0 4px;
  background: #fbbf24;
  color: #1f2937;
  font-size: 10px;
  font-weight: 700;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (min-width: 768px) {
  .notif-badge {
    background: #ef4444;
    color: white;
  }
}

/* User Menu */
.user-menu {
  position: relative;
}

.user-btn {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 2px;
  border-radius: 20px;
  transition: background 0.2s;
  -webkit-tap-highlight-color: transparent;
}

@media (min-width: 768px) {
  .user-btn {
    padding: 4px 8px 4px 4px;
  }
}

.user-btn:hover {
  background: rgba(255, 255, 255, 0.15);
}

@media (min-width: 768px) {
  .user-btn:hover {
    background: #f3f4f6;
  }
}

.avatar {
  width: 28px;
  height: 28px;
  background: rgba(255, 255, 255, 0.25);
  border: 2px solid rgba(255, 255, 255, 0.5);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 12px;
}

@media (min-width: 768px) {
  .avatar {
    width: 32px;
    height: 32px;
    font-size: 14px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border: none;
  }
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
  gap: 4px;
  padding: 6px 10px;
  background: rgba(255, 255, 255, 0.95);
  color: #667eea;
  font-weight: 700;
  font-size: 12px;
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.2s;
  white-space: nowrap;
}

@media (min-width: 768px) {
  .post-btn {
    gap: 6px;
    padding: 8px 14px;
    font-size: 13px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
  }
}

.post-btn:hover {
  background: white;
  transform: scale(1.02);
}

@media (min-width: 768px) {
  .post-btn:hover {
    background: linear-gradient(135deg, #5558e8 0%, #7c4daf 100%);
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
    transform: translateY(-1px);
  }
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
  padding: 0 12px 10px;
  display: flex;
  align-items: center;
  gap: 8px;
  max-height: 52px;
  overflow: hidden;
  transition: all 0.3s ease;
  opacity: 1;
}

.mobile-search:not(.search-visible) {
  max-height: 0;
  padding-top: 0;
  padding-bottom: 0;
  opacity: 0;
}

@media (min-width: 768px) {
  .mobile-search {
    display: none !important;
  }
}

.mobile-location-btn {
  display: flex;
  align-items: center;
  gap: 3px;
  padding: 8px 10px;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 8px;
  font-size: 11px;
  font-weight: 600;
  color: white;
  flex-shrink: 0;
  backdrop-filter: blur(4px);
}

.mobile-location-btn span {
  max-width: 70px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.mobile-search-inner {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 8px;
  backdrop-filter: blur(4px);
}

.mobile-search-inner input {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 14px;
  color: white;
  outline: none;
  min-width: 0;
}

.mobile-search-inner input::placeholder {
  color: rgba(255, 255, 255, 0.6);
}

.mobile-clear-btn {
  padding: 4px;
  color: rgba(255, 255, 255, 0.7);
  flex-shrink: 0;
}

.mobile-clear-btn:hover {
  color: white;
}

/* Location Button (Desktop) */
.location-btn {
  display: none;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
  max-width: 180px;
}

@media (min-width: 768px) {
  .location-btn {
    display: flex;
  }
}

.location-btn:hover {
  border-color: #6366f1;
  color: #6366f1;
}

.location-text {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Mobile Location Button */
.mobile-location-btn {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 6px 10px;
  background: #f3f4f6;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 8px;
}

.mobile-location-btn span {
  max-width: 100px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Location Picker Modal */
.location-picker-modal {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

@media (min-width: 640px) {
  .location-picker-modal {
    align-items: center;
  }
}

.location-picker-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
}

.location-picker-sheet {
  position: relative;
  background: white;
  width: 100%;
  max-height: 70vh;
  border-radius: 1rem 1rem 0 0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: slide-up 0.3s ease-out;
  /* Position above mobile nav */
  margin-bottom: calc(60px + env(safe-area-inset-bottom, 8px));
}

@media (min-width: 640px) {
  .location-picker-sheet {
    max-width: 28rem;
    max-height: 70vh;
    border-radius: 1rem;
    margin-bottom: 0;
  }
}

.location-picker-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.location-picker-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
}

.location-picker-header button {
  padding: 4px;
  color: #6b7280;
}

.location-search {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 1rem;
  padding: 10px 12px;
  background: #f3f4f6;
  border-radius: 8px;
}

.location-search input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  font-size: 15px;
}

.use-location-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 0 1rem;
  padding: 12px;
  background: #f0fdf4;
  border-radius: 8px;
  text-align: left;
  transition: background 0.2s;
}

.use-location-btn:hover:not(:disabled) {
  background: #dcfce7;
}

.use-location-btn:disabled {
  opacity: 0.7;
}

.use-location-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #22c55e;
  color: white;
  border-radius: 50%;
}

.use-location-title {
  display: block;
  font-weight: 600;
  color: #166534;
  font-size: 14px;
}

.use-location-subtitle {
  display: block;
  color: #6b7280;
  font-size: 12px;
}

.location-list {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  padding-bottom: calc(1rem + env(safe-area-inset-bottom, 16px));
}

.location-section-title {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  margin-bottom: 8px;
}

.location-item {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 12px;
  text-align: left;
  border-radius: 8px;
  transition: background 0.2s;
}

.location-item:hover {
  background: #f3f4f6;
}

.location-name {
  display: block;
  font-weight: 500;
  color: #111827;
}

.location-state {
  display: block;
  font-size: 12px;
  color: #6b7280;
}

.popular-cities-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  margin-bottom: 16px;
}

.popular-city-btn {
  padding: 10px 8px;
  background: #f3f4f6;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  color: #374151;
  text-align: center;
  transition: all 0.2s;
}

.popular-city-btn:hover {
  background: #6366f1;
  color: white;
}

.all-india-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 12px;
  background: #f3f4f6;
  border-radius: 8px;
  font-weight: 500;
  color: #374151;
  transition: all 0.2s;
}

.all-india-btn:hover {
  background: #e5e7eb;
}

@keyframes slide-up {
  from { transform: translateY(100%); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
</style>
