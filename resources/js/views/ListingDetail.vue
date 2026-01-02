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
          @touchstart="onGalleryTouchStart"
          @touchmove="onGalleryTouchMove"
          @touchend="onGalleryTouchEnd"
        >
          <div
            class="images-track"
            :style="{
              transform: `translateX(${galleryTranslate}px)`,
              transition: isGallerySwiping ? 'none' : 'transform 0.3s ease-out'
            }"
          >
            <div
              v-for="(image, index) in listing.images"
              :key="image.id"
              class="image-slide"
            >
              <img
                :src="image.medium_url || image.url"
                :alt="`${listing.title} - Image ${index + 1}`"
                class="main-image"
                @error="handleImageError"
              />
            </div>
          </div>

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
            @click="goToImage(index)"
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
    <Teleport to="body">
      <div
        v-if="lightboxOpen"
        class="lightbox"
        @touchstart="onLightboxTouchStart"
        @touchmove="onLightboxTouchMove"
        @touchend="onLightboxTouchEnd"
      >
        <!-- Close button -->
        <button @click="closeLightbox" class="lightbox-close">
          <XMarkIcon class="w-7 h-7" />
        </button>

        <!-- Images container -->
        <div
          class="lightbox-track"
          :style="{
            transform: `translateX(${lightboxTranslateX}px) translateY(${lightboxTranslateY}px)`,
            transition: isLightboxSwiping ? 'none' : 'transform 0.3s ease-out',
            opacity: lightboxOpacity
          }"
        >
          <div
            v-for="(image, index) in listing?.images"
            :key="image.id"
            class="lightbox-slide"
          >
            <img
              :src="image.url"
              :alt="`${listing?.title} - Image ${index + 1}`"
              class="lightbox-image"
              :style="{
                transform: `scale(${index === currentImageIndex ? zoomScale : 1})`,
                transformOrigin: 'center center'
              }"
              @error="handleImageError"
            />
          </div>
        </div>

        <!-- Image counter -->
        <div v-if="listing?.images?.length > 1" class="lightbox-counter">
          {{ currentImageIndex + 1 }} / {{ listing.images.length }}
        </div>

        <!-- Dots indicator -->
        <div v-if="listing?.images?.length > 1" class="lightbox-dots">
          <span
            v-for="(_, index) in listing.images"
            :key="index"
            class="lightbox-dot"
            :class="{ 'active': currentImageIndex === index }"
          ></span>
        </div>
      </div>
    </Teleport>

    <!-- Chat Modal -->
    <ChatModal v-if="showChatModal" :listing="listing" @close="showChatModal = false" />

    <!-- Report Modal -->
    <ReportModal v-if="showReportModal" :type="'listing'" :id="listing?.id" @close="showReportModal = false" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from 'vue'
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

// Gallery swipe state
const isGallerySwiping = ref(false)
const galleryStartX = ref(0)
const galleryCurrentX = ref(0)
const galleryWidth = ref(0)

const galleryTranslate = computed(() => {
  const baseTranslate = -currentImageIndex.value * galleryWidth.value
  if (isGallerySwiping.value) {
    return baseTranslate + (galleryCurrentX.value - galleryStartX.value)
  }
  return baseTranslate
})

const onGalleryTouchStart = (e) => {
  if (e.touches.length !== 1) return
  isGallerySwiping.value = true
  galleryStartX.value = e.touches[0].clientX
  galleryCurrentX.value = e.touches[0].clientX
  galleryWidth.value = e.currentTarget.offsetWidth
}

const onGalleryTouchMove = (e) => {
  if (!isGallerySwiping.value || e.touches.length !== 1) return
  galleryCurrentX.value = e.touches[0].clientX
}

const onGalleryTouchEnd = () => {
  if (!isGallerySwiping.value) return
  isGallerySwiping.value = false

  const diff = galleryCurrentX.value - galleryStartX.value
  const threshold = galleryWidth.value * 0.2

  if (diff > threshold && currentImageIndex.value > 0) {
    currentImageIndex.value--
  } else if (diff < -threshold && currentImageIndex.value < listing.value.images.length - 1) {
    currentImageIndex.value++
  }
}

const goToImage = (index) => {
  currentImageIndex.value = index
}

// Lightbox state
const lightboxOpen = ref(false)
const isLightboxSwiping = ref(false)
const lightboxStartX = ref(0)
const lightboxStartY = ref(0)
const lightboxCurrentX = ref(0)
const lightboxCurrentY = ref(0)
const zoomScale = ref(1)
const lightboxWidth = ref(0)
const lastTapTime = ref(0)
const initialPinchDistance = ref(0)
const isPinching = ref(false)
const swipeDirection = ref(null) // 'horizontal' or 'vertical'

const lightboxTranslateX = computed(() => {
  const baseTranslate = -currentImageIndex.value * lightboxWidth.value
  if (isLightboxSwiping.value && swipeDirection.value === 'horizontal' && zoomScale.value === 1) {
    return baseTranslate + (lightboxCurrentX.value - lightboxStartX.value)
  }
  return baseTranslate
})

const lightboxTranslateY = computed(() => {
  if (isLightboxSwiping.value && swipeDirection.value === 'vertical' && zoomScale.value === 1) {
    return lightboxCurrentY.value - lightboxStartY.value
  }
  return 0
})

const lightboxOpacity = computed(() => {
  if (swipeDirection.value === 'vertical' && zoomScale.value === 1) {
    const dragDistance = Math.abs(lightboxCurrentY.value - lightboxStartY.value)
    return Math.max(0.3, 1 - dragDistance / 300)
  }
  return 1
})

const onLightboxTouchStart = (e) => {
  // Handle double tap to zoom
  const now = Date.now()
  if (now - lastTapTime.value < 300 && e.touches.length === 1) {
    // Double tap
    zoomScale.value = zoomScale.value === 1 ? 2 : 1
    lastTapTime.value = 0
    return
  }
  lastTapTime.value = now

  if (e.touches.length === 2) {
    // Pinch start
    isPinching.value = true
    initialPinchDistance.value = getPinchDistance(e.touches)
  } else if (e.touches.length === 1) {
    isLightboxSwiping.value = true
    lightboxStartX.value = e.touches[0].clientX
    lightboxStartY.value = e.touches[0].clientY
    lightboxCurrentX.value = e.touches[0].clientX
    lightboxCurrentY.value = e.touches[0].clientY
    lightboxWidth.value = window.innerWidth
    swipeDirection.value = null
  }
}

const onLightboxTouchMove = (e) => {
  if (isPinching.value && e.touches.length === 2) {
    // Pinch zoom
    const distance = getPinchDistance(e.touches)
    const scale = distance / initialPinchDistance.value
    zoomScale.value = Math.max(1, Math.min(3, zoomScale.value * scale))
    initialPinchDistance.value = distance
    return
  }

  if (!isLightboxSwiping.value || e.touches.length !== 1) return

  lightboxCurrentX.value = e.touches[0].clientX
  lightboxCurrentY.value = e.touches[0].clientY

  // Determine swipe direction if not set
  if (!swipeDirection.value) {
    const diffX = Math.abs(lightboxCurrentX.value - lightboxStartX.value)
    const diffY = Math.abs(lightboxCurrentY.value - lightboxStartY.value)
    if (diffX > 10 || diffY > 10) {
      swipeDirection.value = diffX > diffY ? 'horizontal' : 'vertical'
    }
  }

  // Prevent default only for horizontal swipes to allow vertical scroll close
  if (swipeDirection.value === 'horizontal' && zoomScale.value === 1) {
    e.preventDefault()
  }
}

const onLightboxTouchEnd = () => {
  isPinching.value = false

  if (!isLightboxSwiping.value) return
  isLightboxSwiping.value = false

  if (zoomScale.value > 1) {
    swipeDirection.value = null
    return
  }

  const diffX = lightboxCurrentX.value - lightboxStartX.value
  const diffY = lightboxCurrentY.value - lightboxStartY.value
  const threshold = lightboxWidth.value * 0.2

  if (swipeDirection.value === 'horizontal') {
    if (diffX > threshold && currentImageIndex.value > 0) {
      currentImageIndex.value--
    } else if (diffX < -threshold && currentImageIndex.value < listing.value.images.length - 1) {
      currentImageIndex.value++
    }
  } else if (swipeDirection.value === 'vertical') {
    // Swipe down to close
    if (Math.abs(diffY) > 100) {
      closeLightbox()
    }
  }

  swipeDirection.value = null
}

const getPinchDistance = (touches) => {
  return Math.hypot(
    touches[0].clientX - touches[1].clientX,
    touches[0].clientY - touches[1].clientY
  )
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

// Handle broken images - show placeholder
const handleImageError = (e) => {
  if (!e.target.dataset.fallback) {
    e.target.dataset.fallback = 'true'
    e.target.src = 'https://placehold.co/800x600/e2e8f0/64748b?text=Image+Not+Available'
  }
}

const openLightbox = () => {
  lightboxOpen.value = true
  zoomScale.value = 1
  document.body.style.overflow = 'hidden'
}

const closeLightbox = () => {
  lightboxOpen.value = false
  zoomScale.value = 1
  document.body.style.overflow = ''
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

// Handle escape key to close lightbox
const handleKeydown = (e) => {
  if (e.key === 'Escape' && lightboxOpen.value) {
    closeLightbox()
  }
}

onMounted(() => {
  fetchListing()
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
})

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
  overflow: hidden;
  cursor: zoom-in;
}

@media (min-width: 1024px) {
  .main-image-container {
    aspect-ratio: 16/9;
    max-height: 500px;
  }
}

.images-track {
  display: flex;
  height: 100%;
  will-change: transform;
}

.image-slide {
  flex: 0 0 100%;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.main-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
  user-select: none;
  -webkit-user-drag: none;
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
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  touch-action: none;
}

.lightbox-close {
  position: absolute;
  top: 16px;
  right: 16px;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 50%;
  color: white;
  z-index: 10;
}

.lightbox-track {
  display: flex;
  height: 100%;
  width: 100%;
  will-change: transform, opacity;
}

.lightbox-slide {
  flex: 0 0 100%;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.lightbox-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  user-select: none;
  -webkit-user-drag: none;
  transition: transform 0.2s ease-out;
}

.lightbox-counter {
  position: absolute;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  padding: 6px 14px;
  background: rgba(0, 0, 0, 0.6);
  border-radius: 20px;
  color: white;
  font-size: 14px;
  font-weight: 500;
}

.lightbox-dots {
  position: absolute;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 8px;
}

.lightbox-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.4);
  transition: all 0.2s;
}

.lightbox-dot.active {
  background: white;
  width: 24px;
  border-radius: 4px;
}
</style>
