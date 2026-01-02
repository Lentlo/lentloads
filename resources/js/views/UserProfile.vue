<template>
  <div class="profile-page">
    <!-- Loading -->
    <div v-if="loading" class="container-app py-6">
      <div class="profile-header-skeleton">
        <div class="skeleton w-28 h-28 rounded-full"></div>
        <div class="flex-1 space-y-3">
          <div class="skeleton h-7 w-1/3"></div>
          <div class="skeleton h-4 w-1/4"></div>
          <div class="skeleton h-4 w-1/2"></div>
        </div>
      </div>
    </div>

    <!-- User Not Found -->
    <div v-else-if="!user" class="container-app py-12">
      <div class="empty-state">
        <div class="empty-icon">
          <UserIcon class="w-16 h-16" />
        </div>
        <h2>User not found</h2>
        <p>This user doesn't exist or has been removed.</p>
        <router-link to="/" class="btn-primary">Go Home</router-link>
      </div>
    </div>

    <!-- Profile Content -->
    <template v-else>
      <!-- Profile Header -->
      <div class="profile-header">
        <div class="container-app">
          <div class="header-content">
            <!-- Avatar -->
            <div class="avatar-wrapper">
              <img :src="userAvatar" :alt="user.name" class="avatar" />
              <div v-if="user.is_verified" class="verified-check">
                <CheckIcon class="w-4 h-4" />
              </div>
            </div>

            <!-- Info -->
            <div class="user-info">
              <h1 class="user-name">{{ user.name }}</h1>

              <div class="rating-row">
                <div class="stars">
                  <StarIcon v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= Math.round(user.rating || 0) ? 'text-yellow-400 fill-current' : 'text-gray-300'" />
                </div>
                <span class="rating-text">{{ user.rating || '0.0' }}</span>
                <span class="reviews-count">• {{ user.reviews_count || 0 }} reviews</span>
                <button v-if="!isOwnProfile" @click="showReportModal = true" class="report-user">
                  <FlagIcon class="w-4 h-4" />
                </button>
              </div>

              <div class="meta-row">
                <span v-if="user.city"><MapPinIcon class="w-4 h-4" /> {{ user.city }}{{ user.state ? `, ${user.state}` : '' }}</span>
                <span><CalendarIcon class="w-4 h-4" /> Member since {{ formatJoinDate(user.created_at) }}</span>
              </div>

              <p v-if="user.bio" class="bio">{{ user.bio }}</p>
            </div>
          </div>

          <!-- Stats -->
          <div class="stats-bar">
            <div class="stat">
              <span class="stat-value">{{ user.listings_count || 0 }}</span>
              <span class="stat-label">Listings</span>
            </div>
            <div class="stat">
              <span class="stat-value">{{ user.sold_count || 0 }}</span>
              <span class="stat-label">Sold</span>
            </div>
            <div class="stat">
              <span class="stat-value">{{ responseRate }}%</span>
              <span class="stat-label">Response Rate</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="container-app py-4">
        <div class="tabs">
          <button @click="activeTab = 'listings'" :class="{ 'active': activeTab === 'listings' }">
            Listings
          </button>
          <button @click="activeTab = 'reviews'" :class="{ 'active': activeTab === 'reviews' }">
            Reviews
          </button>
        </div>

        <!-- Listings Tab -->
        <div v-if="activeTab === 'listings'" class="tab-content">
          <div v-if="loadingListings" class="listings-grid">
            <div v-for="i in 6" :key="i" class="listing-skeleton">
              <div class="skeleton aspect-square rounded-lg"></div>
              <div class="skeleton h-4 w-3/4 mt-2"></div>
              <div class="skeleton h-4 w-1/2 mt-1"></div>
            </div>
          </div>

          <div v-else-if="listings.length" class="listings-grid">
            <ListingCard v-for="listing in listings" :key="listing.id" :listing="listing" />
          </div>

          <div v-else class="empty-tab">
            <ShoppingBagIcon class="w-12 h-12" />
            <p>No listings yet</p>
          </div>
        </div>

        <!-- Reviews Tab -->
        <div v-if="activeTab === 'reviews'" class="tab-content">
          <div v-if="loadingReviews" class="reviews-list">
            <div v-for="i in 3" :key="i" class="review-skeleton">
              <div class="skeleton w-10 h-10 rounded-full"></div>
              <div class="flex-1 space-y-2">
                <div class="skeleton h-4 w-1/4"></div>
                <div class="skeleton h-3 w-full"></div>
              </div>
            </div>
          </div>

          <div v-else-if="reviews.length" class="reviews-list">
            <div v-for="review in reviews" :key="review.id" class="review-card">
              <img :src="getReviewerAvatar(review.reviewer)" :alt="review.reviewer?.name" class="reviewer-avatar" />
              <div class="review-content">
                <div class="review-header">
                  <span class="reviewer-name">{{ review.reviewer?.name }}</span>
                  <span class="review-date">{{ formatDate(review.created_at) }}</span>
                </div>
                <div class="review-stars">
                  <StarIcon v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= review.rating ? 'text-yellow-400 fill-current' : 'text-gray-300'" />
                </div>
                <p v-if="review.comment" class="review-text">{{ review.comment }}</p>
              </div>
            </div>
          </div>

          <div v-else class="empty-tab">
            <StarIcon class="w-12 h-12" />
            <p>No reviews yet</p>
          </div>
        </div>
      </div>
    </template>

    <!-- Report Modal -->
    <ReportModal v-if="showReportModal" type="user" :item-id="user?.id" @close="showReportModal = false" />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import dayjs from 'dayjs'
import ListingCard from '@/components/common/ListingCard.vue'
import ReportModal from '@/components/modals/ReportModal.vue'
import {
  UserIcon,
  StarIcon,
  MapPinIcon,
  CalendarIcon,
  CheckIcon,
  FlagIcon,
  ShoppingBagIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const authStore = useAuthStore()

const loading = ref(true)
const loadingListings = ref(true)
const loadingReviews = ref(true)
const user = ref(null)
const listings = ref([])
const reviews = ref([])
const activeTab = ref('listings')
const showReportModal = ref(false)

const isOwnProfile = computed(() => authStore.user?.id === user.value?.id)
const responseRate = computed(() => Math.round(user.value?.response_rate || 0))

const userAvatar = computed(() => {
  const avatar = user.value?.avatar_url
  if (avatar && !avatar.includes('default') && !avatar.includes('null')) return avatar
  return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.value?.name || 'U') + '&background=7c3aed&color=fff&size=200&bold=true'
})

const getReviewerAvatar = (reviewer) => {
  if (reviewer?.avatar_url && !reviewer.avatar_url.includes('default')) return reviewer.avatar_url
  return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(reviewer?.name || 'U') + '&background=e2e8f0&color=64748b&size=80'
}

const formatJoinDate = (date) => dayjs(date).format('MMMM YYYY')
const formatDate = (date) => dayjs(date).format('MMM D, YYYY')

const fetchUser = async () => {
  loading.value = true
  try {
    const response = await api.get(`/users/${route.params.id}`)
    user.value = response.data.data
  } catch (error) {
    user.value = null
  } finally {
    loading.value = false
  }
}

const fetchListings = async () => {
  loadingListings.value = true
  try {
    const response = await api.get(`/users/${route.params.id}/listings`)
    listings.value = response.data.data
  } finally {
    loadingListings.value = false
  }
}

const fetchReviews = async () => {
  loadingReviews.value = true
  try {
    const response = await api.get(`/users/${route.params.id}/reviews`)
    reviews.value = response.data.data
  } finally {
    loadingReviews.value = false
  }
}

watch(activeTab, (tab) => {
  if (tab === 'reviews' && !reviews.value.length) fetchReviews()
})

watch(() => route.params.id, () => {
  if (route.params.id) {
    fetchUser()
    fetchListings()
    reviews.value = []
    activeTab.value = 'listings'
  }
})

onMounted(() => {
  fetchUser()
  fetchListings()
})
</script>

<style scoped>
.profile-page {
  min-height: 100vh;
  background: #f8fafc;
}

/* Profile Header */
.profile-header {
  background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
  padding: 24px 0;
}

.header-content {
  display: flex;
  gap: 20px;
  align-items: flex-start;
}

@media (max-width: 640px) {
  .header-content {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }
}

.avatar-wrapper {
  position: relative;
  flex-shrink: 0;
}

.avatar {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  border: 4px solid white;
  object-fit: cover;
  background: #e2e8f0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

@media (min-width: 640px) {
  .avatar {
    width: 120px;
    height: 120px;
  }
}

.verified-check {
  position: absolute;
  bottom: 4px;
  right: 4px;
  width: 28px;
  height: 28px;
  background: #16a34a;
  border: 3px solid white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.user-info {
  flex: 1;
  color: white;
}

.user-name {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 8px;
}

@media (min-width: 640px) {
  .user-name {
    font-size: 28px;
  }
}

.rating-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  flex-wrap: wrap;
  justify-content: center;
}

@media (min-width: 640px) {
  .rating-row {
    justify-content: flex-start;
  }
}

.stars {
  display: flex;
  gap: 2px;
}

.rating-text {
  font-weight: 600;
}

.reviews-count {
  opacity: 0.8;
  font-size: 14px;
}

.report-user {
  margin-left: auto;
  padding: 6px;
  border-radius: 6px;
  opacity: 0.6;
  transition: opacity 0.2s;
}

.report-user:hover {
  opacity: 1;
  background: rgba(255, 255, 255, 0.1);
}

.meta-row {
  display: flex;
  gap: 16px;
  font-size: 14px;
  opacity: 0.9;
  flex-wrap: wrap;
  justify-content: center;
}

@media (min-width: 640px) {
  .meta-row {
    justify-content: flex-start;
  }
}

.meta-row span {
  display: flex;
  align-items: center;
  gap: 4px;
}

.bio {
  margin-top: 12px;
  font-size: 14px;
  line-height: 1.6;
  opacity: 0.9;
}

/* Stats Bar */
.stats-bar {
  display: flex;
  justify-content: center;
  gap: 32px;
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

@media (min-width: 640px) {
  .stats-bar {
    justify-content: flex-start;
    gap: 48px;
  }
}

.stat {
  text-align: center;
  color: white;
}

.stat-value {
  display: block;
  font-size: 28px;
  font-weight: 700;
}

.stat-label {
  font-size: 13px;
  opacity: 0.8;
}

/* Tabs */
.tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;
}

.tabs button {
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  background: white;
  color: #64748b;
  transition: all 0.2s;
  border: 1px solid #e2e8f0;
}

.tabs button.active {
  background: #7c3aed;
  color: white;
  border-color: #7c3aed;
}

/* Listings Grid */
.listings-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

@media (min-width: 768px) {
  .listings-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
  }
}

.listing-skeleton {
  background: white;
  border-radius: 12px;
  padding: 12px;
}

/* Reviews */
.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.review-card {
  display: flex;
  gap: 12px;
  background: white;
  padding: 16px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.reviewer-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.review-content {
  flex: 1;
  min-width: 0;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.reviewer-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
}

.review-date {
  font-size: 12px;
  color: #94a3b8;
}

.review-stars {
  display: flex;
  gap: 2px;
  margin-bottom: 8px;
}

.review-text {
  font-size: 14px;
  color: #475569;
  line-height: 1.5;
}

.review-skeleton {
  display: flex;
  gap: 12px;
  background: white;
  padding: 16px;
  border-radius: 12px;
}

/* Empty States */
.empty-state {
  text-align: center;
  padding: 48px 20px;
}

.empty-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 16px;
  background: #f1f5f9;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
}

.empty-state h2 {
  font-size: 20px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 8px;
}

.empty-state p {
  color: #64748b;
  margin-bottom: 20px;
}

.empty-tab {
  text-align: center;
  padding: 48px 20px;
  background: white;
  border-radius: 12px;
  color: #94a3b8;
}

.empty-tab svg {
  margin: 0 auto 12px;
}

.empty-tab p {
  font-size: 14px;
}

/* Skeleton */
.profile-header-skeleton {
  display: flex;
  gap: 20px;
  background: white;
  padding: 24px;
  border-radius: 12px;
}
</style>
