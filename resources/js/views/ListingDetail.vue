<template>
  <div class="listing-detail-page">
    <!-- Loading -->
    <div v-if="loading" class="container-app py-6">
      <div class="skeleton h-80 mb-4 rounded-lg"></div>
      <div class="skeleton h-8 w-1/3 mb-2"></div>
      <div class="skeleton h-6 w-full mb-4"></div>
      <div class="skeleton h-24 w-full"></div>
    </div>

    <!-- Content -->
    <template v-else-if="listing">
      <!-- Image Gallery Section -->
      <div class="image-gallery-section">
        <!-- Main Image with Swipe -->
        <div
          class="main-image-container"
          @click="openLightbox"
          @touchstart="handleSwipeStart"
          @touchmove="handleSwipeMove"
          @touchend="handleSwipeEnd"
        >
          <img
            :src="currentImage"
            :alt="listing.title"
            class="main-image"
            :style="{ transform: `translateX(${swipeOffset}px)` }"
          />

          <!-- Image Counter -->
          <div v-if="listing.images?.length > 1" class="image-counter">
            {{ currentImageIndex + 1 }} / {{ listing.images.length }}
          </div>

          <!-- Badges -->
          <div class="image-badges">
            <span v-if="listing.is_featured" class="badge-featured">Featured</span>
            <span v-if="listing.is_urgent" class="badge-urgent">Urgent</span>
          </div>

          <!-- Swipe Dots Indicator -->
          <div v-if="listing.images?.length > 1" class="swipe-dots">
            <span
              v-for="(_, index) in listing.images"
              :key="index"
              class="dot"
              :class="{ 'active': currentImageIndex === index }"
            ></span>
          </div>
        </div>

        <!-- Thumbnails -->
        <div v-if="listing.images?.length > 1" class="thumbnails-strip">
          <button
            v-for="(image, index) in listing.images"
            :key="image.id"
            @click="currentImageIndex = index"
            class="thumbnail"
            :class="{ 'active': currentImageIndex === index }"
          >
            <img :src="image.thumbnail_url || image.url" :alt="`Image ${index + 1}`" />
          </button>
        </div>
      </div>

      <!-- Content Section -->
      <div class="container-app py-4">
        <div class="lg:grid lg:grid-cols-3 lg:gap-6">
          <!-- Main Info -->
          <div class="lg:col-span-2 space-y-4">
            <!-- Price & Title Card -->
            <div class="detail-card">
              <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                  <p class="price">{{ cleanPrice }}</p>
                  <h1 class="title">{{ listing.title }}</h1>
                </div>
                <div class="action-buttons">
                  <button @click="toggleFavorite" class="action-btn" :class="{ 'is-favorite': isFavorited }">
                    <HeartIcon class="w-6 h-6" :class="isFavorited ? 'fill-current' : ''" />
                  </button>
                  <button @click="shareList" class="action-btn">
                    <ShareIcon class="w-6 h-6" />
                  </button>
                </div>
              </div>

              <!-- Meta Info -->
              <div class="meta-info">
                <span><MapPinIcon class="w-4 h-4" /> {{ listing.location }}</span>
                <span><ClockIcon class="w-4 h-4" /> {{ formatDate(listing.published_at) }}</span>
                <span><EyeIcon class="w-4 h-4" /> {{ listing.views_count }} views</span>
              </div>
            </div>

            <!-- Details Card -->
            <div class="detail-card">
              <h3 class="section-title">Details</h3>
              <div class="details-grid">
                <div v-if="listing.condition" class="detail-item">
                  <span class="label">Condition</span>
                  <span class="value">{{ formatCondition(listing.condition) }}</span>
                </div>
                <div v-if="listing.brand" class="detail-item">
                  <span class="label">Brand</span>
                  <span class="value">{{ listing.brand }}</span>
                </div>
                <div v-if="listing.model" class="detail-item">
                  <span class="label">Model</span>
                  <span class="value">{{ listing.model }}</span>
                </div>
                <div v-if="listing.year" class="detail-item">
                  <span class="label">Year</span>
                  <span class="value">{{ listing.year }}</span>
                </div>
                <div v-if="listing.category" class="detail-item">
                  <span class="label">Category</span>
                  <span class="value">{{ listing.category.name }}</span>
                </div>
              </div>
            </div>

            <!-- Description Card -->
            <div class="detail-card">
              <h3 class="section-title">Description</h3>
              <p class="description">{{ listing.description }}</p>
            </div>

            <!-- Location Card -->
            <div class="detail-card">
              <h3 class="section-title">Location</h3>
              <LocationDisplay
                :latitude="listing.latitude"
                :longitude="listing.longitude"
                :locality="listing.locality"
                :city="listing.city"
                :state="listing.state"
                :postal-code="listing.postal_code"
              />
            </div>
          </div>

          <!-- Sidebar -->
          <div class="lg:col-span-1 space-y-4 mt-4 lg:mt-0">
            <!-- Seller Card -->
            <div class="seller-card">
              <div class="seller-header">
                <img :src="sellerAvatar" :alt="listing.user.name" class="seller-avatar" />
                <div class="seller-info">
                  <router-link :to="`/user/${listing.user.id}`" class="seller-name">
                    {{ listing.user.name }}
                  </router-link>
                  <div class="seller-rating">
                    <StarIcon class="w-4 h-4 text-yellow-400 fill-current" />
                    <span>{{ listing.user.rating || '0.0' }}</span>
                    <span class="text-gray-400">({{ listing.user.total_reviews || 0 }})</span>
                  </div>
                  <p v-if="listing.user.city" class="seller-location">{{ listing.user.city }}</p>
                </div>
              </div>

              <div v-if="listing.user.is_verified_seller" class="verified-badge">
                <CheckBadgeIcon class="w-5 h-5" />
                Verified Seller
              </div>

              <!-- Action Buttons -->
              <div class="seller-actions">
                <button v-if="!isOwner" @click="startChat" class="btn-chat">
                  <ChatBubbleLeftRightIcon class="w-5 h-5" />
                  Chat with Seller
                </button>

                <button
                  v-if="listing.user.phone && !isOwner && !showPhone"
                  @click="revealPhone"
                  class="btn-phone"
                >
                  <PhoneIcon class="w-5 h-5" />
                  Show Phone Number
                </button>
                <a
                  v-if="listing.user.phone && !isOwner && showPhone"
                  :href="`tel:${listing.user.phone}`"
                  class="btn-phone-revealed"
                >
                  <PhoneIcon class="w-5 h-5" />
                  {{ listing.user.phone }}
                </a>

                <router-link v-if="isOwner" :to="`/listing/${listing.id}/edit`" class="btn-edit">
                  Edit Listing
                </router-link>
              </div>
            </div>

            <!-- Safety Tips -->
            <div class="safety-card">
              <h4>Safety Tips</h4>
              <ul>
                <li>Meet in a safe public place</li>
                <li>Check the item before you pay</li>
                <li>Pay only after collecting the item</li>
                <li>Never share financial information</li>
              </ul>
            </div>

            <!-- Report -->
            <button @click="showReportModal = true" class="report-btn">
              <FlagIcon class="w-4 h-4" />
              Report this ad
            </button>
          </div>
        </div>

        <!-- Similar Listings -->
        <div v-if="similar?.length" class="similar-section">
          <h2 class="similar-title">Similar Ads</h2>
          <div class="similar-grid">
            <ListingCard v-for="item in similar" :key="item.id" :listing="item" />
          </div>
        </div>
      </div>
    </template>

    <!-- Lightbox -->
    <div v-if="lightboxOpen" class="lightbox" @click="closeLightbox">
      <button @click="closeLightbox" class="lightbox-close">
        <XMarkIcon class="w-8 h-8" />
      </button>

      <div class="lightbox-content" @click.stop>
        <img
          :src="currentImage"
          :alt="listing.title"
          class="lightbox-image"
          :style="{ transform: `scale(${zoomLevel}) translate(${panX}px, ${panY}px)` }"
          @touchstart="handleTouchStart"
          @touchmove="handleTouchMove"
          @touchend="handleTouchEnd"
        />
      </div>

      <div class="lightbox-controls">
        <button @click.stop="zoomOut" class="zoom-btn" :disabled="zoomLevel <= 1">
          <MinusIcon class="w-6 h-6" />
        </button>
        <span class="zoom-level">{{ Math.round(zoomLevel * 100) }}%</span>
        <button @click.stop="zoomIn" class="zoom-btn" :disabled="zoomLevel >= 3">
          <PlusIcon class="w-6 h-6" />
        </button>
      </div>

      <button v-if="listing.images?.length > 1" @click.stop="prevImage" class="lightbox-nav lightbox-prev">
        <ChevronLeftIcon class="w-8 h-8" />
      </button>
      <button v-if="listing.images?.length > 1" @click.stop="nextImage" class="lightbox-nav lightbox-next">
        <ChevronRightIcon class="w-8 h-8" />
      </button>

      <div v-if="listing.images?.length > 1" class="lightbox-counter">
        {{ currentImageIndex + 1 }} / {{ listing.images.length }}
      </div>
    </div>

    <!-- Chat Modal -->
    <ChatModal v-if="showChatModal" :listing="listing" @close="showChatModal = false" />

    <!-- Report Modal -->
    <ReportModal v-if="showReportModal" :type="'listing'" :id="listing?.id" @close="showReportModal = false" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useListingsStore } from '@/stores/listings'
import api from '@/services/api'
import ListingCard from '@/components/common/ListingCard.vue'
import LocationDisplay from '@/components/common/LocationDisplay.vue'
import ChatModal from '@/components/modals/ChatModal.vue'
import ReportModal from '@/components/modals/ReportModal.vue'
import dayjs from 'dayjs'
import {
  ChevronLeftIcon,
  ChevronRightIcon,
  HeartIcon,
  ShareIcon,
  MapPinIcon,
  ClockIcon,
  EyeIcon,
  StarIcon,
  CheckBadgeIcon,
  ChatBubbleLeftRightIcon,
  PhoneIcon,
  FlagIcon,
  XMarkIcon,
  PlusIcon,
  MinusIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const listingsStore = useListingsStore()

const loading = ref(true)
const listing = ref(null)
const similar = ref([])
const currentImageIndex = ref(0)
const isFavorited = ref(false)
const showChatModal = ref(false)
const showReportModal = ref(false)
const showPhone = ref(false)

// Lightbox state
const lightboxOpen = ref(false)
const zoomLevel = ref(1)
const panX = ref(0)
const panY = ref(0)
let touchStartX = 0
let touchStartY = 0
let lastTouchDistance = 0

// Swipe state for main gallery
const swipeOffset = ref(0)
let swipeStartX = 0
let isSwiping = false

const handleSwipeStart = (e) => {
  if (e.touches.length === 1) {
    swipeStartX = e.touches[0].clientX
    isSwiping = true
  }
}

const handleSwipeMove = (e) => {
  if (!isSwiping || e.touches.length !== 1) return
  const diff = e.touches[0].clientX - swipeStartX
  // Limit the swipe offset
  swipeOffset.value = Math.max(-100, Math.min(100, diff))
}

const handleSwipeEnd = () => {
  if (!isSwiping) return
  isSwiping = false

  const threshold = 50
  if (swipeOffset.value > threshold && listing.value?.images?.length > 1) {
    prevImage()
  } else if (swipeOffset.value < -threshold && listing.value?.images?.length > 1) {
    nextImage()
  }

  // Reset offset with animation
  swipeOffset.value = 0
}

const currentImage = computed(() => {
  if (!listing.value?.images?.length) return listing.value?.primary_image_url
  return listing.value.images[currentImageIndex.value]?.url
})

const isOwner = computed(() => authStore.user?.id === listing.value?.user?.id)

const cleanPrice = computed(() => {
  const price = listing.value?.formatted_price || ''
  return price.replace(/\s*\(Negotiable\)/gi, '').trim()
})

const sellerAvatar = computed(() => {
  const avatar = listing.value?.user?.avatar_url
  if (avatar && !avatar.includes('default')) return avatar
  return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(listing.value?.user?.name || 'U') + '&background=7c3aed&color=fff&size=120'
})

const formatDate = (date) => {
  if (!date) return 'Recently'
  const d = dayjs(date)
  return d.isValid() ? d.format('MMM D, YYYY') : 'Recently'
}

const formatCondition = (condition) => {
  const map = { 'new': 'New', 'like_new': 'Like New', 'good': 'Good', 'fair': 'Fair', 'poor': 'Poor' }
  return map[condition] || condition?.replace(/_/g, ' ')
}

const prevImage = () => {
  currentImageIndex.value = currentImageIndex.value > 0
    ? currentImageIndex.value - 1
    : listing.value.images.length - 1
  resetZoom()
}

const nextImage = () => {
  currentImageIndex.value = currentImageIndex.value < listing.value.images.length - 1
    ? currentImageIndex.value + 1
    : 0
  resetZoom()
}

const openLightbox = () => {
  lightboxOpen.value = true
  document.body.style.overflow = 'hidden'
}

const closeLightbox = () => {
  lightboxOpen.value = false
  document.body.style.overflow = ''
  resetZoom()
}

const resetZoom = () => {
  zoomLevel.value = 1
  panX.value = 0
  panY.value = 0
}

const zoomIn = () => {
  if (zoomLevel.value < 3) zoomLevel.value += 0.5
}

const zoomOut = () => {
  if (zoomLevel.value > 1) {
    zoomLevel.value -= 0.5
    if (zoomLevel.value === 1) { panX.value = 0; panY.value = 0 }
  }
}

const handleTouchStart = (e) => {
  if (e.touches.length === 2) {
    lastTouchDistance = Math.hypot(
      e.touches[0].clientX - e.touches[1].clientX,
      e.touches[0].clientY - e.touches[1].clientY
    )
  } else if (e.touches.length === 1) {
    touchStartX = e.touches[0].clientX - panX.value
    touchStartY = e.touches[0].clientY - panY.value
  }
}

const handleTouchMove = (e) => {
  if (e.touches.length === 2) {
    const distance = Math.hypot(
      e.touches[0].clientX - e.touches[1].clientX,
      e.touches[0].clientY - e.touches[1].clientY
    )
    const scale = distance / lastTouchDistance
    zoomLevel.value = Math.max(1, Math.min(3, zoomLevel.value * scale))
    lastTouchDistance = distance
  } else if (e.touches.length === 1 && zoomLevel.value > 1) {
    panX.value = e.touches[0].clientX - touchStartX
    panY.value = e.touches[0].clientY - touchStartY
  }
}

const handleTouchEnd = () => {
  lastTouchDistance = 0
}

const toggleFavorite = async () => {
  if (!authStore.isAuthenticated) { router.push('/login'); return }
  const result = await listingsStore.toggleFavorite(listing.value.id)
  isFavorited.value = result.is_favorited
}

const startChat = () => {
  if (!authStore.isAuthenticated) { router.push('/login'); return }
  showChatModal.value = true
}

const shareList = () => {
  if (navigator.share) {
    navigator.share({ title: listing.value.title, text: `Check out: ${listing.value.title}`, url: window.location.href })
  }
}

const revealPhone = async () => {
  if (!authStore.isAuthenticated) { router.push('/login'); return }
  try { await api.post(`/listings/${listing.value.id}/track-contact`, { type: 'phone' }) } catch {}
  showPhone.value = true
}

const fetchListing = async () => {
  try {
    const data = await listingsStore.fetchListing(route.params.slug)
    listing.value = data.listing
    similar.value = data.similar
    isFavorited.value = data.listing.is_favorited
  } catch (error) {
    if (error.response?.status === 404) router.push('/404')
  } finally {
    loading.value = false
  }
}

onMounted(() => fetchListing())

watch(() => route.params.slug, (newSlug, oldSlug) => {
  if (newSlug !== oldSlug) {
    loading.value = true
    currentImageIndex.value = 0
    showPhone.value = false
    fetchListing()
  }
})
</script>

<style scoped>
.listing-detail-page {
  min-height: 100vh;
  background: #f8fafc;
}

/* Image Gallery */
.image-gallery-section {
  background: white;
}

.main-image-container {
  position: relative;
  width: 100%;
  aspect-ratio: 4/3;
  background: #1a1a1a;
  cursor: zoom-in;
}

@media (min-width: 1024px) {
  .main-image-container {
    aspect-ratio: 16/9;
    max-height: 500px;
  }
}

.main-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.image-counter {
  position: absolute;
  bottom: 12px;
  left: 12px;
  padding: 6px 12px;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  font-size: 13px;
  font-weight: 500;
  border-radius: 20px;
}

.image-badges {
  position: absolute;
  top: 12px;
  left: 12px;
  display: flex;
  gap: 8px;
}

.badge-featured, .badge-urgent {
  padding: 4px 12px;
  font-size: 12px;
  font-weight: 600;
  border-radius: 4px;
}

.badge-featured {
  background: linear-gradient(135deg, #f59e0b, #f97316);
  color: white;
}

.badge-urgent {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
}

/* Swipe dots indicator */
.swipe-dots {
  position: absolute;
  bottom: 12px;
  right: 12px;
  display: flex;
  gap: 6px;
}

.swipe-dots .dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.5);
  transition: all 0.2s;
}

.swipe-dots .dot.active {
  background: white;
  width: 20px;
  border-radius: 4px;
}

/* Main image transition */
.main-image {
  transition: transform 0.15s ease-out;
}

/* Hide nav arrows on mobile - only show on desktop */
.nav-arrow {
  display: none;
}

@media (min-width: 1024px) {
  .nav-arrow {
    display: flex;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 44px;
    height: 44px;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 50%;
    color: #333;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    transition: all 0.2s;
  }

  .nav-arrow:hover {
    background: white;
    transform: translateY(-50%) scale(1.05);
  }

  .nav-prev { left: 12px; }
  .nav-next { right: 12px; }
}

.thumbnails-strip {
  display: flex;
  gap: 8px;
  padding: 12px;
  overflow-x: auto;
  background: white;
  border-top: 1px solid #e5e7eb;
}

.thumbnail {
  width: 64px;
  height: 64px;
  flex-shrink: 0;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid transparent;
  transition: border-color 0.2s;
}

.thumbnail.active {
  border-color: #7c3aed;
}

.thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Cards */
.detail-card {
  background: white;
  border-radius: 12px;
  padding: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.price {
  font-size: 24px;
  font-weight: 700;
  color: #7c3aed;
}

.title {
  font-size: 18px;
  font-weight: 600;
  color: #1e293b;
  margin-top: 4px;
  line-height: 1.4;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.action-btn {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: #f1f5f9;
  color: #64748b;
  transition: all 0.2s;
}

.action-btn:hover {
  background: #e2e8f0;
}

.action-btn.is-favorite {
  color: #ef4444;
  background: #fef2f2;
}

.meta-info {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #e5e7eb;
  font-size: 13px;
  color: #64748b;
}

.meta-info span {
  display: flex;
  align-items: center;
  gap: 4px;
}

.section-title {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 12px;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.detail-item .label {
  font-size: 12px;
  color: #94a3b8;
}

.detail-item .value {
  font-size: 14px;
  font-weight: 500;
  color: #334155;
}

.description {
  font-size: 14px;
  line-height: 1.7;
  color: #475569;
  white-space: pre-line;
}

/* Seller Card */
.seller-card {
  background: white;
  border-radius: 12px;
  padding: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.seller-header {
  display: flex;
  gap: 12px;
  margin-bottom: 12px;
}

.seller-avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  object-fit: cover;
  background: #e2e8f0;
}

.seller-info {
  flex: 1;
}

.seller-name {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
}

.seller-name:hover {
  color: #7c3aed;
}

.seller-rating {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 13px;
  color: #64748b;
  margin-top: 2px;
}

.seller-location {
  font-size: 12px;
  color: #94a3b8;
  margin-top: 2px;
}

.verified-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 500;
  color: #16a34a;
  margin-bottom: 12px;
}

.seller-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.btn-chat, .btn-phone, .btn-phone-revealed, .btn-edit {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-chat {
  background: #7c3aed;
  color: white;
}

.btn-chat:hover {
  background: #6d28d9;
}

.btn-phone {
  background: white;
  color: #334155;
  border: 1px solid #e2e8f0;
}

.btn-phone:hover {
  border-color: #7c3aed;
  color: #7c3aed;
}

.btn-phone-revealed {
  background: #dcfce7;
  color: #16a34a;
  border: 1px solid #bbf7d0;
}

.btn-edit {
  background: #f1f5f9;
  color: #334155;
}

/* Safety Card */
.safety-card {
  background: #fefce8;
  border: 1px solid #fef08a;
  border-radius: 12px;
  padding: 16px;
}

.safety-card h4 {
  font-size: 14px;
  font-weight: 600;
  color: #a16207;
  margin-bottom: 8px;
}

.safety-card ul {
  font-size: 13px;
  color: #854d0e;
  padding-left: 16px;
}

.safety-card li {
  margin-bottom: 4px;
}

.report-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: #64748b;
  transition: color 0.2s;
}

.report-btn:hover {
  color: #ef4444;
}

/* Similar Section */
.similar-section {
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid #e5e7eb;
}

.similar-title {
  font-size: 18px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 16px;
}

.similar-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

@media (min-width: 768px) {
  .similar-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (min-width: 1024px) {
  .similar-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

/* Lightbox */
.lightbox {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.95);
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
}

.lightbox-close {
  position: absolute;
  top: 16px;
  right: 16px;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  color: white;
  z-index: 10;
}

.lightbox-content {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.lightbox-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  transition: transform 0.1s;
}

.lightbox-controls {
  position: absolute;
  bottom: 80px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 8px 16px;
  background: rgba(0, 0, 0, 0.7);
  border-radius: 24px;
}

.zoom-btn {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  color: white;
}

.zoom-btn:disabled {
  opacity: 0.3;
}

.zoom-level {
  font-size: 14px;
  color: white;
  min-width: 50px;
  text-align: center;
}

.lightbox-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  color: white;
}

.lightbox-prev { left: 16px; }
.lightbox-next { right: 16px; }

.lightbox-counter {
  position: absolute;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%);
  padding: 8px 16px;
  background: rgba(0, 0, 0, 0.7);
  border-radius: 20px;
  color: white;
  font-size: 14px;
}
</style>
