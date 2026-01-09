<template>
  <header class="app-header" :class="{ 'header-scrolled': isScrolled }">
    <!-- Mobile Header -->
    <div class="mobile-header">
      <!-- Safe area spacer for Android status bar -->
      <div class="mobile-safe-area"></div>

      <!-- Row 1: Logo + Name + Actions -->
      <div class="mobile-row-1">
        <router-link to="/" class="mobile-logo">
          <div class="mobile-logo-icon">
            <svg viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
              <defs>
                <linearGradient id="mobileLogoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" stop-color="#ffffff" />
                  <stop offset="100%" stop-color="#e0e7ff" />
                </linearGradient>
              </defs>
              <rect x="2" y="2" width="40" height="40" rx="10" fill="url(#mobileLogoGrad)"/>
              <path d="M14 12V28H28V24H18V12H14Z" fill="#6366f1"/>
              <circle cx="30" cy="14" r="4" fill="#fbbf24"/>
            </svg>
          </div>
          <span class="mobile-logo-text">Lentlo Ads</span>
        </router-link>

        <div class="mobile-actions">
          <router-link v-if="isAuthenticated" to="/notifications" class="mobile-icon-btn">
            <BellIcon class="w-5 h-5" />
            <span v-if="unreadNotifications > 0" class="mobile-badge">{{ unreadNotifications > 9 ? '9+' : unreadNotifications }}</span>
          </router-link>

          <router-link v-if="isAuthenticated" to="/messages" class="mobile-icon-btn">
            <ChatBubbleLeftRightIcon class="w-5 h-5" />
            <span v-if="unreadMessages > 0" class="mobile-badge">{{ unreadMessages > 9 ? '9+' : unreadMessages }}</span>
          </router-link>

          <router-link v-if="!isAuthenticated" to="/login" class="mobile-login-btn">
            Login
          </router-link>

          <button v-if="isAuthenticated" @click="showUserMenu = !showUserMenu" class="mobile-avatar-btn">
            <span class="mobile-avatar">{{ user?.name?.charAt(0) || 'U' }}</span>
          </button>
        </div>
      </div>

      <!-- Row 2: Location + Search Bar -->
      <div class="mobile-row-2">
        <button @click="showLocationPicker = true" class="mobile-location-btn">
          <MapPinIcon class="w-4 h-4" />
          <span>{{ shortLocationName }}</span>
          <ChevronDownIcon class="w-3 h-3" />
        </button>

        <div class="mobile-search-box">
          <MagnifyingGlassIcon class="mobile-search-icon" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search cars, mobiles, jobs..."
            @keyup.enter="handleSearch"
          />
          <button v-if="searchQuery" @click="clearSearch" class="mobile-clear-btn">
            <XMarkIcon class="w-4 h-4" />
          </button>
          <button @click="handleSearch" class="mobile-search-submit">
            <MagnifyingGlassIcon class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- User Menu Dropdown -->
      <Transition name="menu">
        <div v-if="showUserMenu" class="mobile-dropdown">
          <div class="dropdown-header">
            <div class="dropdown-avatar">{{ user?.name?.charAt(0) || 'U' }}</div>
            <div class="dropdown-info">
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
          <button @click="handleLogout" class="dropdown-logout">
            <ArrowRightOnRectangleIcon class="w-5 h-5" />
            Logout
          </button>
        </div>
      </Transition>
    </div>

    <!-- Desktop Header -->
    <div class="desktop-header">
      <div class="desktop-inner">
        <!-- Logo -->
        <router-link to="/" class="desktop-logo">
          <div class="desktop-logo-icon">
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
          <span class="desktop-logo-text">Lentlo Ads</span>
        </router-link>

        <!-- Location Selector -->
        <button @click="showLocationPicker = true" class="desktop-location">
          <MapPinIcon class="w-5 h-5" />
          <span>{{ currentLocationName }}</span>
          <ChevronDownIcon class="w-4 h-4" />
        </button>

        <!-- Search Bar -->
        <div class="desktop-search">
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
        <div class="desktop-actions">
          <router-link v-if="isAuthenticated" to="/notifications" class="desktop-icon-btn">
            <BellIcon class="w-5 h-5" />
            <span v-if="unreadNotifications > 0" class="desktop-badge">
              {{ unreadNotifications > 9 ? '9+' : unreadNotifications }}
            </span>
          </router-link>

          <router-link v-if="isAuthenticated" to="/messages" class="desktop-icon-btn">
            <ChatBubbleLeftRightIcon class="w-5 h-5" />
            <span v-if="unreadMessages > 0" class="desktop-badge">
              {{ unreadMessages > 9 ? '9+' : unreadMessages }}
            </span>
          </router-link>

          <!-- User Menu -->
          <div v-if="isAuthenticated" class="desktop-user-menu">
            <button @click="showUserMenu = !showUserMenu" class="desktop-user-btn">
              <div class="desktop-avatar">{{ user?.name?.charAt(0) || 'U' }}</div>
              <ChevronDownIcon class="w-4 h-4" />
            </button>

            <Transition name="menu">
              <div v-if="showUserMenu" class="desktop-dropdown">
                <div class="dropdown-header">
                  <div class="dropdown-avatar">{{ user?.name?.charAt(0) || 'U' }}</div>
                  <div class="dropdown-info">
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
                <button @click="handleLogout" class="dropdown-logout">
                  <ArrowRightOnRectangleIcon class="w-5 h-5" />
                  Logout
                </button>
              </div>
            </Transition>
          </div>

          <router-link v-if="!isAuthenticated" to="/login" class="desktop-login">
            Login
          </router-link>

          <router-link to="/sell" class="desktop-post-btn">
            <PlusIcon class="w-4 h-4" />
            <span>Post Free Ad</span>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Location Picker Modal -->
    <div v-if="showLocationPicker" class="location-modal">
      <div class="location-backdrop" @click="showLocationPicker = false"></div>
      <div class="location-sheet">
        <div class="location-header">
          <h3>Select Location</h3>
          <button @click="showLocationPicker = false" class="location-close">
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>

        <div class="location-search">
          <MagnifyingGlassIcon class="w-5 h-5" />
          <input
            v-model="citySearch"
            type="text"
            placeholder="Search city, area or locality..."
            @input="searchCities"
          />
        </div>

        <button @click="useCurrentLocation" class="location-current" :disabled="detectingLocation">
          <div class="location-current-icon">
            <MapPinIcon class="w-5 h-5" />
          </div>
          <div>
            <span class="location-current-title">{{ detectingLocation ? 'Detecting...' : 'Use current location' }}</span>
            <span class="location-current-sub">Enable GPS for accurate location</span>
          </div>
        </button>

        <div class="location-list">
          <template v-if="cityResults.length">
            <div class="location-section">Search Results</div>
            <button
              v-for="city in cityResults"
              :key="city.id"
              @click="selectCity(city)"
              class="location-item"
            >
              <MapPinIcon class="w-4 h-4" />
              <div>
                <span class="location-city">{{ city.name }}</span>
                <span class="location-state">{{ city.state }}</span>
              </div>
            </button>
          </template>
          <template v-else>
            <div class="location-section">Popular Cities</div>
            <div class="location-grid">
              <button
                v-for="city in popularCities"
                :key="city.id"
                @click="selectCity(city)"
                class="location-city-btn"
              >
                {{ city.name }}
              </button>
            </div>
            <button @click="selectAllIndia" class="location-all">
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

// Scroll state
const isScrolled = ref(false)
let lastScrollY = 0
let ticking = false

// Location state
const showLocationPicker = ref(false)
const citySearch = ref('')
const cityResults = ref([])
const detectingLocation = ref(false)
const selectedLocation = ref(null)

// Handle scroll - adds shadow when scrolled
const handleScroll = () => {
  if (ticking) return

  ticking = true
  requestAnimationFrame(() => {
    const currentScrollY = window.scrollY || window.pageYOffset || document.documentElement.scrollTop || 0
    isScrolled.value = currentScrollY > 10
    lastScrollY = currentScrollY
    ticking = false
  })
}

const isAuthenticated = computed(() => authStore.isAuthenticated)
const isAdmin = computed(() => authStore.isAdmin)
const user = computed(() => authStore.user)
const popularCities = computed(() => appStore.popularCities?.slice(0, 12) || [])

const currentLocationName = computed(() => {
  const loc = selectedLocation.value || appStore.currentLocation
  if (loc?.city) return loc.city
  if (loc?.latitude && loc?.longitude) return 'Near Me'
  return 'All India'
})

const shortLocationName = computed(() => {
  const name = currentLocationName.value
  return name.length > 10 ? name.substring(0, 10) + '...' : name
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
  if (showUserMenu.value && !e.target.closest('.desktop-user-menu') && !e.target.closest('.mobile-avatar-btn')) {
    showUserMenu.value = false
  }
}

// Location functions
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
  localStorage.setItem('selectedCity', JSON.stringify(selectedLocation.value))
  appStore.setLocation(selectedLocation.value)
  showLocationPicker.value = false
  citySearch.value = ''
  cityResults.value = []
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
      localStorage.setItem('nearMeActive', 'true')
      router.push({ path: '/search' })
    }
  } catch (e) {
    console.error('Location detection failed:', e)
    import('vue3-toastify').then(({ toast }) => {
      toast.error('Could not detect location. Please try again.')
    })
  } finally {
    detectingLocation.value = false
  }
}

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
  // Add scroll listeners to both window and document for better compatibility
  window.addEventListener('scroll', handleScroll, { passive: true })
  document.addEventListener('scroll', handleScroll, { passive: true })
  if (isAuthenticated.value) {
    fetchUnreadCounts()
    countInterval = setInterval(fetchUnreadCounts, 30000)
  }
})

onUnmounted(() => {
  document.removeEventListener('click', closeMenu)
  window.removeEventListener('scroll', handleScroll)
  document.removeEventListener('scroll', handleScroll)
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

watch(() => route.query.q, (newQ) => {
  if (route.path === '/search') {
    searchQuery.value = newQ || ''
  }
}, { immediate: true })
</script>

<style scoped>
/* ========================================
   BASE HEADER
   ======================================== */
.app-header {
  position: sticky;
  top: 0;
  z-index: 100;
  background: white;
  transition: box-shadow 0.2s ease;
}

.app-header.header-scrolled {
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

/* ========================================
   MOBILE HEADER (< 768px) - Two Row Design
   ======================================== */
.mobile-header {
  display: block;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  position: relative;
}

@media (min-width: 768px) {
  .mobile-header {
    display: none;
  }
}

/* Safe area for Android status bar */
.mobile-safe-area {
  height: env(safe-area-inset-top, 0);
  background: transparent;
}

/* Row 1: Logo + Actions */
.mobile-row-1 {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px 8px;
}

.mobile-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
}

.mobile-logo-icon {
  width: 34px;
  height: 34px;
}

.mobile-logo-icon svg {
  width: 100%;
  height: 100%;
}

.mobile-logo-text {
  font-size: 20px;
  font-weight: 700;
  color: white;
  letter-spacing: -0.3px;
}

/* Mobile actions */
.mobile-actions {
  display: flex;
  align-items: center;
  gap: 2px;
}

.mobile-icon-btn {
  position: relative;
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.95);
  border-radius: 10px;
  -webkit-tap-highlight-color: transparent;
  transition: background 0.15s ease;
}

.mobile-icon-btn:active {
  background: rgba(255, 255, 255, 0.2);
}

.mobile-badge {
  position: absolute;
  top: 4px;
  right: 4px;
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

.mobile-login-btn {
  padding: 7px 14px;
  background: white;
  color: #6366f1;
  font-size: 13px;
  font-weight: 600;
  border-radius: 8px;
  -webkit-tap-highlight-color: transparent;
  margin-left: 2px;
}

.mobile-login-btn:active {
  opacity: 0.9;
}

.mobile-avatar-btn {
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  -webkit-tap-highlight-color: transparent;
}

.mobile-avatar {
  width: 32px;
  height: 32px;
  background: rgba(255, 255, 255, 0.25);
  border: 2px solid rgba(255, 255, 255, 0.7);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 13px;
}

/* Row 2: Location + Search */
.mobile-row-2 {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 0 14px 12px;
}

.mobile-location-btn {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 8px 10px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  color: white;
  font-size: 12px;
  font-weight: 500;
  white-space: nowrap;
  -webkit-tap-highlight-color: transparent;
  flex-shrink: 0;
}

.mobile-location-btn:active {
  background: rgba(255, 255, 255, 0.3);
}

.mobile-location-btn svg:first-child {
  color: #fbbf24;
}

.mobile-search-box {
  flex: 1;
  display: flex;
  align-items: center;
  background: white;
  border-radius: 10px;
  padding: 0 4px 0 10px;
  height: 40px;
  min-width: 0;
}

.mobile-search-icon {
  width: 18px;
  height: 18px;
  color: #9ca3af;
  flex-shrink: 0;
}

.mobile-search-box input {
  flex: 1;
  padding: 8px;
  border: none;
  background: transparent;
  font-size: 14px;
  color: #1f2937;
  outline: none;
  min-width: 0;
}

.mobile-search-box input::placeholder {
  color: #9ca3af;
}

.mobile-clear-btn {
  padding: 6px;
  color: #9ca3af;
  border-radius: 6px;
  -webkit-tap-highlight-color: transparent;
}

.mobile-clear-btn:active {
  background: #f3f4f6;
}

.mobile-search-submit {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  border-radius: 8px;
  flex-shrink: 0;
  -webkit-tap-highlight-color: transparent;
}

.mobile-search-submit:active {
  opacity: 0.9;
}

/* Mobile Dropdown */
.mobile-dropdown {
  position: absolute;
  top: 100%;
  right: 12px;
  width: 280px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  z-index: 1000;
  margin-top: 8px;
}

/* ========================================
   DESKTOP HEADER (>= 768px)
   ======================================== */
.desktop-header {
  display: none;
}

@media (min-width: 768px) {
  .desktop-header {
    display: block;
    border-bottom: 1px solid #e5e7eb;
  }
}

.desktop-inner {
  max-width: 1280px;
  margin: 0 auto;
  padding: 12px 24px;
  display: flex;
  align-items: center;
  gap: 20px;
}

@media (min-width: 1024px) {
  .desktop-inner {
    padding: 12px 32px;
  }
}

.desktop-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  flex-shrink: 0;
}

.desktop-logo-icon {
  width: 40px;
  height: 40px;
}

.desktop-logo-icon svg {
  width: 100%;
  height: 100%;
}

.desktop-logo-text {
  font-size: 22px;
  font-weight: 800;
  color: #1f2937;
  letter-spacing: -0.5px;
}

.desktop-location {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
  max-width: 180px;
}

.desktop-location:hover {
  border-color: #6366f1;
  color: #6366f1;
}

.desktop-location svg:first-child {
  color: #6366f1;
  flex-shrink: 0;
}

.desktop-location span {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Desktop Search */
.desktop-search {
  flex: 1;
  max-width: 600px;
  position: relative;
}

.search-box {
  display: flex;
  align-items: center;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 0 6px 0 14px;
  transition: all 0.2s;
  height: 46px;
}

.search-box:hover {
  border-color: #cbd5e1;
  background: #f1f5f9;
}

.search-box.focused {
  background: white;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.search-box .search-icon {
  width: 20px;
  height: 20px;
  color: #94a3b8;
  flex-shrink: 0;
}

.search-box.focused .search-icon {
  color: #6366f1;
}

.search-box input {
  flex: 1;
  padding: 12px 10px;
  border: none;
  background: transparent;
  font-size: 15px;
  color: #1e293b;
  outline: none;
  min-width: 0;
}

.search-box input::placeholder {
  color: #94a3b8;
}

.clear-btn {
  padding: 6px;
  color: #94a3b8;
  border-radius: 6px;
}

.clear-btn:hover {
  color: #64748b;
  background: #f1f5f9;
}

.search-submit {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  border-radius: 10px;
  transition: all 0.2s;
}

.search-submit:hover {
  transform: scale(1.05);
}

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

/* Desktop Actions */
.desktop-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-left: auto;
}

.desktop-icon-btn {
  position: relative;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4b5563;
  border-radius: 10px;
  transition: all 0.2s;
}

.desktop-icon-btn:hover {
  background: #f3f4f6;
  color: #6366f1;
}

.desktop-badge {
  position: absolute;
  top: 2px;
  right: 2px;
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

.desktop-user-menu {
  position: relative;
}

.desktop-user-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px 4px 4px;
  border: 1px solid #e5e7eb;
  background: white;
  border-radius: 24px;
  cursor: pointer;
  transition: all 0.2s;
}

.desktop-user-btn:hover {
  border-color: #6366f1;
}

.desktop-avatar {
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

.desktop-user-btn svg {
  color: #6b7280;
}

.desktop-dropdown {
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

.desktop-login {
  padding: 8px 16px;
  color: #374151;
  font-weight: 600;
  font-size: 14px;
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.2s;
}

.desktop-login:hover {
  background: #f3f4f6;
  color: #6366f1;
}

.desktop-post-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 10px 16px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  font-weight: 600;
  font-size: 14px;
  text-decoration: none;
  border-radius: 10px;
  transition: all 0.2s;
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
}

.desktop-post-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
}

/* ========================================
   SHARED DROPDOWN STYLES
   ======================================== */
.dropdown-header {
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
  flex-shrink: 0;
}

.dropdown-info {
  min-width: 0;
}

.dropdown-name {
  font-weight: 600;
  color: #1f2937;
  font-size: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dropdown-email {
  font-size: 12px;
  color: #6b7280;
  margin-top: 1px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dropdown-links {
  padding: 6px;
}

.dropdown-link {
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

.dropdown-link:hover {
  background: #f3f4f6;
  color: #6366f1;
}

.dropdown-link.admin {
  color: #6366f1;
  background: #eef2ff;
}

.dropdown-link.admin:hover {
  background: #e0e7ff;
}

.dropdown-logout {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 12px 18px;
  color: #dc2626;
  font-size: 14px;
  border-top: 1px solid #f3f4f6;
  transition: background 0.15s;
}

.dropdown-logout:hover {
  background: #fef2f2;
}

/* Menu transition */
.menu-enter-active,
.menu-leave-active {
  transition: all 0.2s ease;
}

.menu-enter-from,
.menu-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* ========================================
   LOCATION PICKER MODAL
   ======================================== */
.location-modal {
  position: fixed;
  inset: 0;
  z-index: 10001;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

@media (min-width: 640px) {
  .location-modal {
    align-items: center;
  }
}

.location-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
}

.location-sheet {
  position: relative;
  background: white;
  width: 100%;
  max-height: 80vh;
  border-radius: 20px 20px 0 0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: slideUp 0.3s ease;
}

@media (min-width: 640px) {
  .location-sheet {
    max-width: 420px;
    max-height: 70vh;
    border-radius: 20px;
  }
}

@keyframes slideUp {
  from { transform: translateY(100%); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.location-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #e5e7eb;
}

.location-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: #111827;
}

.location-close {
  padding: 4px;
  color: #6b7280;
}

.location-search {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 16px;
  padding: 12px 14px;
  background: #f3f4f6;
  border-radius: 12px;
}

.location-search svg {
  color: #9ca3af;
  flex-shrink: 0;
}

.location-search input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  font-size: 15px;
  color: #1f2937;
}

.location-search input::placeholder {
  color: #9ca3af;
}

.location-current {
  display: flex;
  align-items: center;
  gap: 14px;
  margin: 0 16px 16px;
  padding: 14px;
  background: #f0fdf4;
  border-radius: 12px;
  text-align: left;
  transition: background 0.2s;
}

.location-current:hover:not(:disabled) {
  background: #dcfce7;
}

.location-current:disabled {
  opacity: 0.7;
}

.location-current-icon {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #22c55e;
  color: white;
  border-radius: 50%;
  flex-shrink: 0;
}

.location-current-title {
  display: block;
  font-weight: 600;
  color: #166534;
  font-size: 14px;
}

.location-current-sub {
  display: block;
  color: #6b7280;
  font-size: 12px;
  margin-top: 2px;
}

.location-list {
  flex: 1;
  overflow-y: auto;
  padding: 0 16px 16px;
  padding-bottom: calc(16px + env(safe-area-inset-bottom, 0));
}

.location-section {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  margin-bottom: 10px;
  letter-spacing: 0.5px;
}

.location-item {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 12px;
  text-align: left;
  border-radius: 10px;
  transition: background 0.2s;
}

.location-item:hover {
  background: #f3f4f6;
}

.location-item svg {
  color: #9ca3af;
  flex-shrink: 0;
}

.location-city {
  display: block;
  font-weight: 500;
  color: #111827;
}

.location-state {
  display: block;
  font-size: 12px;
  color: #6b7280;
}

.location-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  margin-bottom: 16px;
}

.location-city-btn {
  padding: 12px 8px;
  background: #f3f4f6;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 500;
  color: #374151;
  text-align: center;
  transition: all 0.2s;
}

.location-city-btn:hover {
  background: #6366f1;
  color: white;
}

.location-all {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 14px;
  background: #f3f4f6;
  border-radius: 10px;
  font-weight: 500;
  color: #374151;
  transition: all 0.2s;
}

.location-all:hover {
  background: #e5e7eb;
}
</style>
