<template>
  <div class="home-page">
    <!-- Hero Section - Clean & Modern -->
    <section class="hero-section">
      <div class="hero-bg">
        <div class="hero-shape hero-shape-1"></div>
        <div class="hero-shape hero-shape-2"></div>
        <div class="hero-shape hero-shape-3"></div>
      </div>

      <div class="hero-content">
        <div class="hero-badge">
          <SparklesIcon class="w-4 h-4" />
          <span>India's Growing Marketplace</span>
        </div>

        <h1 class="hero-title">
          Find What You <span class="hero-highlight">Need</span>,<br>
          Sell What You <span class="hero-highlight">Don't</span>
        </h1>

        <p class="hero-subtitle">
          Join thousands of buyers and sellers in your city.
          Post free ads, discover amazing deals, and connect instantly.
        </p>

        <div class="hero-actions">
          <router-link to="/search" class="hero-btn hero-btn-primary">
            <MagnifyingGlassIcon class="w-5 h-5" />
            Browse Listings
          </router-link>
          <router-link to="/sell" class="hero-btn hero-btn-secondary">
            <PlusCircleIcon class="w-5 h-5" />
            Post Free Ad
          </router-link>
        </div>

        <!-- Stats -->
        <div class="hero-stats">
          <div class="stat-item">
            <span class="stat-value">{{ formatNumber(stats.listings) }}</span>
            <span class="stat-label">Active Ads</span>
          </div>
          <div class="stat-divider"></div>
          <div class="stat-item">
            <span class="stat-value">{{ formatNumber(stats.users) }}</span>
            <span class="stat-label">Happy Users</span>
          </div>
          <div class="stat-divider"></div>
          <div class="stat-item">
            <span class="stat-value">{{ stats.cities }}+</span>
            <span class="stat-label">Cities</span>
          </div>
        </div>
      </div>

      <!-- Hero Image/Illustration -->
      <div class="hero-visual">
        <div class="hero-card hero-card-1">
          <div class="card-icon card-icon-purple">
            <DevicePhoneMobileIcon class="w-6 h-6" />
          </div>
          <span>Mobile Phones</span>
        </div>
        <div class="hero-card hero-card-2">
          <div class="card-icon card-icon-blue">
            <TruckIcon class="w-6 h-6" />
          </div>
          <span>Vehicles</span>
        </div>
        <div class="hero-card hero-card-3">
          <div class="card-icon card-icon-green">
            <HomeModernIcon class="w-6 h-6" />
          </div>
          <span>Properties</span>
        </div>
        <div class="hero-card hero-card-4">
          <div class="card-icon card-icon-orange">
            <BriefcaseIcon class="w-6 h-6" />
          </div>
          <span>Jobs</span>
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section">
      <div class="section-container">
        <div class="section-header">
          <div>
            <h2 class="section-title">Browse by Category</h2>
            <p class="section-subtitle">Find exactly what you're looking for</p>
          </div>
          <router-link to="/categories" class="view-all-link">
            View All
            <ChevronRightIcon class="w-4 h-4" />
          </router-link>
        </div>

        <div class="categories-grid">
          <router-link
            v-for="category in categories"
            :key="category.id"
            :to="`/category/${category.slug}`"
            class="category-card"
          >
            <div class="category-icon" :class="getCategoryColor(category.slug)">
              <component :is="getCategoryIcon(category.slug)" class="w-7 h-7" />
            </div>
            <div class="category-info">
              <h3 class="category-name">{{ category.name }}</h3>
              <span class="category-count">{{ category.total_active_listings_count || 0 }} ads</span>
            </div>
            <ChevronRightIcon class="category-arrow w-5 h-5" />
          </router-link>
        </div>
      </div>
    </section>

    <!-- Featured Listings -->
    <section v-if="featuredListings.length > 0" class="listings-section featured-section">
      <div class="section-container">
        <div class="section-header">
          <div class="section-title-group">
            <div class="section-icon featured-icon">
              <SparklesIcon class="w-5 h-5" />
            </div>
            <div>
              <h2 class="section-title">Featured Ads</h2>
              <p class="section-subtitle">Premium listings from verified sellers</p>
            </div>
          </div>
          <router-link to="/search?featured=1" class="view-all-link">
            View All
            <ChevronRightIcon class="w-4 h-4" />
          </router-link>
        </div>

        <div class="listings-grid">
          <ListingCard
            v-for="listing in featuredListings"
            :key="listing.id"
            :listing="listing"
          />
        </div>
      </div>
    </section>

    <!-- Recent Listings -->
    <section class="listings-section">
      <div class="section-container">
        <div class="section-header">
          <div class="section-title-group">
            <div class="section-icon recent-icon">
              <ClockIcon class="w-5 h-5" />
            </div>
            <div>
              <h2 class="section-title">Fresh Recommendations</h2>
              <p class="section-subtitle">Recently posted near you</p>
            </div>
          </div>
          <router-link to="/search" class="view-all-link">
            View All
            <ChevronRightIcon class="w-4 h-4" />
          </router-link>
        </div>

        <!-- Loading Skeleton -->
        <div v-if="loading" class="listings-grid">
          <div v-for="i in 8" :key="i" class="listing-skeleton">
            <div class="skeleton-image"></div>
            <div class="skeleton-content">
              <div class="skeleton-price"></div>
              <div class="skeleton-title"></div>
              <div class="skeleton-location"></div>
            </div>
          </div>
        </div>

        <!-- Listings Grid -->
        <div v-else class="listings-grid">
          <ListingCard
            v-for="listing in recentListings"
            :key="listing.id"
            :listing="listing"
          />
        </div>

        <!-- Explore Button -->
        <div class="explore-more">
          <router-link to="/search" class="explore-btn">
            Explore All Listings
            <ArrowRightIcon class="w-5 h-5" />
          </router-link>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
      <div class="cta-container">
        <div class="cta-content">
          <h2 class="cta-title">Ready to Sell Something?</h2>
          <p class="cta-text">
            Post your ad in just 2 minutes. It's free and reaches thousands of buyers in your area.
          </p>
          <router-link to="/sell" class="cta-btn">
            <PlusCircleIcon class="w-6 h-6" />
            Post Your Free Ad
          </router-link>
        </div>
        <div class="cta-visual">
          <div class="cta-feature">
            <CheckCircleIcon class="w-5 h-5" />
            <span>100% Free</span>
          </div>
          <div class="cta-feature">
            <CheckCircleIcon class="w-5 h-5" />
            <span>Instant Publish</span>
          </div>
          <div class="cta-feature">
            <CheckCircleIcon class="w-5 h-5" />
            <span>Reach Thousands</span>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAppStore } from '@/stores/app'
import api from '@/services/api'
import ListingCard from '@/components/common/ListingCard.vue'
import {
  ChevronRightIcon,
  MagnifyingGlassIcon,
  ArrowRightIcon,
  PlusCircleIcon,
  SparklesIcon,
  ClockIcon,
  CheckCircleIcon,
  DevicePhoneMobileIcon,
  TruckIcon,
  HomeModernIcon,
  BriefcaseIcon,
  ComputerDesktopIcon,
  ShoppingBagIcon,
  WrenchScrewdriverIcon,
  MusicalNoteIcon,
  HeartIcon,
} from '@heroicons/vue/24/outline'

const appStore = useAppStore()

const loading = ref(true)
const featuredListings = ref([])
const recentListings = ref([])
const stats = ref({
  listings: 0,
  users: 0,
  cities: 50
})

const categories = computed(() => appStore.categories)

const formatNumber = (num) => {
  if (num >= 1000) {
    return (num / 1000).toFixed(num >= 10000 ? 0 : 1) + 'K+'
  }
  return num + '+'
}

const getCategoryIcon = (slug) => {
  const icons = {
    'mobiles': DevicePhoneMobileIcon,
    'vehicles': TruckIcon,
    'property': HomeModernIcon,
    'jobs': BriefcaseIcon,
    'electronics': ComputerDesktopIcon,
    'fashion': ShoppingBagIcon,
    'furniture': HomeModernIcon,
    'services': WrenchScrewdriverIcon,
    'bikes': TruckIcon,
    'books': ShoppingBagIcon,
    'sports': HeartIcon,
    'music': MusicalNoteIcon,
  }
  return icons[slug] || ShoppingBagIcon
}

const getCategoryColor = (slug) => {
  const colors = {
    'mobiles': 'icon-purple',
    'vehicles': 'icon-blue',
    'property': 'icon-green',
    'jobs': 'icon-orange',
    'electronics': 'icon-indigo',
    'fashion': 'icon-pink',
    'furniture': 'icon-teal',
    'services': 'icon-amber',
  }
  return colors[slug] || 'icon-gray'
}

const fetchHomeData = async () => {
  try {
    const response = await api.get('/home')
    featuredListings.value = response.data.data.featured_listings || []
    recentListings.value = response.data.data.recent_listings || []
    stats.value.listings = response.data.data.total_listings || 0
    stats.value.users = response.data.data.total_users || 0
  } catch (error) {
    // Silent fail - page will still render
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchHomeData()
})
</script>

<style scoped>
.home-page {
  min-height: 100vh;
  background: #f8fafc;
}

/* Hero Section */
.hero-section {
  position: relative;
  min-height: 500px;
  padding: 40px 16px 60px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  align-items: center;
}

@media (min-width: 768px) {
  .hero-section {
    min-height: 520px;
    padding: 60px 32px 80px;
    flex-direction: row;
    justify-content: center;
    gap: 60px;
  }
}

@media (min-width: 1024px) {
  .hero-section {
    padding: 80px 48px 100px;
    gap: 80px;
  }
}

.hero-bg {
  position: absolute;
  inset: 0;
  overflow: hidden;
}

.hero-shape {
  position: absolute;
  border-radius: 50%;
  opacity: 0.1;
}

.hero-shape-1 {
  width: 400px;
  height: 400px;
  background: white;
  top: -100px;
  right: -100px;
}

.hero-shape-2 {
  width: 300px;
  height: 300px;
  background: #fbbf24;
  bottom: -50px;
  left: -50px;
}

.hero-shape-3 {
  width: 200px;
  height: 200px;
  background: white;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.hero-content {
  position: relative;
  z-index: 10;
  max-width: 540px;
  text-align: center;
}

@media (min-width: 768px) {
  .hero-content {
    text-align: left;
  }
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  color: white;
  font-size: 13px;
  font-weight: 500;
  margin-bottom: 20px;
}

.hero-title {
  font-size: 32px;
  font-weight: 800;
  color: white;
  line-height: 1.2;
  margin-bottom: 16px;
}

@media (min-width: 768px) {
  .hero-title {
    font-size: 42px;
  }
}

@media (min-width: 1024px) {
  .hero-title {
    font-size: 48px;
  }
}

.hero-highlight {
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-subtitle {
  font-size: 16px;
  color: rgba(255, 255, 255, 0.85);
  line-height: 1.6;
  margin-bottom: 28px;
}

@media (min-width: 768px) {
  .hero-subtitle {
    font-size: 18px;
  }
}

.hero-actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 32px;
}

@media (min-width: 480px) {
  .hero-actions {
    flex-direction: row;
    justify-content: center;
  }
}

@media (min-width: 768px) {
  .hero-actions {
    justify-content: flex-start;
  }
}

.hero-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 14px 24px;
  font-size: 15px;
  font-weight: 600;
  border-radius: 12px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.hero-btn-primary {
  background: white;
  color: #667eea;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
}

.hero-btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.hero-btn-secondary {
  background: rgba(255, 255, 255, 0.15);
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.hero-btn-secondary:hover {
  background: rgba(255, 255, 255, 0.25);
  border-color: rgba(255, 255, 255, 0.5);
}

.hero-stats {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 24px;
}

@media (min-width: 768px) {
  .hero-stats {
    justify-content: flex-start;
  }
}

.stat-item {
  text-align: center;
}

@media (min-width: 768px) {
  .stat-item {
    text-align: left;
  }
}

.stat-value {
  display: block;
  font-size: 24px;
  font-weight: 800;
  color: white;
}

@media (min-width: 768px) {
  .stat-value {
    font-size: 28px;
  }
}

.stat-label {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.7);
}

.stat-divider {
  width: 1px;
  height: 36px;
  background: rgba(255, 255, 255, 0.2);
}

/* Hero Visual */
.hero-visual {
  display: none;
  position: relative;
  width: 320px;
  height: 320px;
}

@media (min-width: 768px) {
  .hero-visual {
    display: block;
  }
}

@media (min-width: 1024px) {
  .hero-visual {
    width: 380px;
    height: 380px;
  }
}

.hero-card {
  position: absolute;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 18px;
  background: white;
  border-radius: 14px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  animation: float 3s ease-in-out infinite;
}

.hero-card span {
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
}

.hero-card-1 {
  top: 10%;
  left: 0;
  animation-delay: 0s;
}

.hero-card-2 {
  top: 5%;
  right: 0;
  animation-delay: 0.5s;
}

.hero-card-3 {
  bottom: 20%;
  left: 5%;
  animation-delay: 1s;
}

.hero-card-4 {
  bottom: 10%;
  right: 5%;
  animation-delay: 1.5s;
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

.card-icon {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  color: white;
}

.card-icon-purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.card-icon-blue { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
.card-icon-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.card-icon-orange { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }

/* Section Styles */
.section-container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 16px;
}

@media (min-width: 768px) {
  .section-container {
    padding: 0 24px;
  }
}

.section-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 24px;
}

.section-title-group {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.section-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  flex-shrink: 0;
}

.featured-icon {
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  color: white;
}

.recent-icon {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.section-title {
  font-size: 22px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 4px;
}

@media (min-width: 768px) {
  .section-title {
    font-size: 26px;
  }
}

.section-subtitle {
  font-size: 14px;
  color: #64748b;
}

.view-all-link {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 14px;
  font-weight: 600;
  color: #6366f1;
  text-decoration: none;
  padding: 8px 0;
  transition: gap 0.2s;
}

.view-all-link:hover {
  gap: 8px;
}

/* Categories Section */
.categories-section {
  padding: 48px 0;
  background: white;
}

@media (min-width: 768px) {
  .categories-section {
    padding: 64px 0;
  }
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

@media (min-width: 640px) {
  .categories-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
  }
}

@media (min-width: 1024px) {
  .categories-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

.category-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.category-card:hover {
  background: white;
  border-color: #6366f1;
  box-shadow: 0 4px 20px rgba(99, 102, 241, 0.12);
  transform: translateY(-2px);
}

.category-icon {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  flex-shrink: 0;
}

.icon-purple { background: #ede9fe; color: #7c3aed; }
.icon-blue { background: #dbeafe; color: #2563eb; }
.icon-green { background: #d1fae5; color: #059669; }
.icon-orange { background: #ffedd5; color: #ea580c; }
.icon-indigo { background: #e0e7ff; color: #4f46e5; }
.icon-pink { background: #fce7f3; color: #db2777; }
.icon-teal { background: #ccfbf1; color: #0d9488; }
.icon-amber { background: #fef3c7; color: #d97706; }
.icon-gray { background: #f1f5f9; color: #64748b; }

.category-info {
  flex: 1;
  min-width: 0;
}

.category-name {
  font-size: 15px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 2px;
}

.category-count {
  font-size: 13px;
  color: #64748b;
}

.category-arrow {
  color: #cbd5e1;
  transition: all 0.2s;
}

.category-card:hover .category-arrow {
  color: #6366f1;
  transform: translateX(4px);
}

/* Listings Section */
.listings-section {
  padding: 48px 0;
  background: #f8fafc;
}

.featured-section {
  background: white;
}

@media (min-width: 768px) {
  .listings-section {
    padding: 64px 0;
  }
}

.listings-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

@media (min-width: 768px) {
  .listings-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
  }
}

@media (min-width: 1024px) {
  .listings-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

/* Skeleton */
.listing-skeleton {
  background: white;
  border-radius: 14px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
}

.skeleton-image {
  aspect-ratio: 4 / 3;
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-content {
  padding: 14px;
}

.skeleton-price {
  width: 80px;
  height: 22px;
  background: #e2e8f0;
  border-radius: 6px;
  margin-bottom: 10px;
}

.skeleton-title {
  width: 100%;
  height: 16px;
  background: #e2e8f0;
  border-radius: 4px;
  margin-bottom: 8px;
}

.skeleton-location {
  width: 60%;
  height: 14px;
  background: #e2e8f0;
  border-radius: 4px;
}

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

.explore-more {
  text-align: center;
  margin-top: 40px;
}

.explore-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 14px 28px;
  background: white;
  border: 2px solid #6366f1;
  color: #6366f1;
  font-size: 15px;
  font-weight: 600;
  border-radius: 12px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.explore-btn:hover {
  background: #6366f1;
  color: white;
}

/* CTA Section */
.cta-section {
  padding: 64px 16px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

@media (min-width: 768px) {
  .cta-section {
    padding: 80px 24px;
  }
}

.cta-container {
  max-width: 800px;
  margin: 0 auto;
  text-align: center;
}

.cta-content {
  margin-bottom: 32px;
}

.cta-title {
  font-size: 28px;
  font-weight: 800;
  color: white;
  margin-bottom: 12px;
}

@media (min-width: 768px) {
  .cta-title {
    font-size: 36px;
  }
}

.cta-text {
  font-size: 16px;
  color: rgba(255, 255, 255, 0.85);
  margin-bottom: 24px;
  max-width: 500px;
  margin-left: auto;
  margin-right: auto;
}

.cta-btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 16px 32px;
  background: white;
  color: #667eea;
  font-size: 16px;
  font-weight: 700;
  border-radius: 14px;
  text-decoration: none;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  transition: all 0.2s ease;
}

.cta-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
}

.cta-visual {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 16px;
}

.cta-feature {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 10px;
  color: white;
  font-size: 14px;
  font-weight: 500;
}

.cta-feature svg {
  color: #fbbf24;
}
</style>
