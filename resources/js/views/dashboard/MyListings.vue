<template>
  <div class="my-listings-page">
    <div class="container-app py-6">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1>My Listings</h1>
          <p>Manage your ads</p>
        </div>
        <router-link to="/sell" class="post-btn">
          <PlusIcon class="w-5 h-5" />
          <span>Post New Ad</span>
        </router-link>
      </div>

      <!-- Stats Overview -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon bg-blue-100 text-blue-600">
            <ClipboardDocumentListIcon class="w-6 h-6" />
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ tabs[0].count }}</span>
            <span class="stat-label">Total Listings</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon bg-green-100 text-green-600">
            <CheckCircleIcon class="w-6 h-6" />
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ tabs[1].count }}</span>
            <span class="stat-label">Active</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon bg-yellow-100 text-yellow-600">
            <ClockIcon class="w-6 h-6" />
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ tabs[2].count }}</span>
            <span class="stat-label">Pending</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon bg-purple-100 text-purple-600">
            <ShoppingCartIcon class="w-6 h-6" />
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ tabs[3].count }}</span>
            <span class="stat-label">Sold</span>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters-row">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          @click="currentTab = tab.value"
          class="filter-btn"
          :class="{ 'active': currentTab === tab.value }"
        >
          {{ tab.label }}
          <span class="count">{{ tab.count }}</span>
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="listings-container">
        <div v-for="i in 3" :key="i" class="listing-card-skeleton">
          <div class="skeleton h-40 rounded-lg"></div>
          <div class="skeleton h-5 w-2/3 mt-3"></div>
          <div class="skeleton h-4 w-1/3 mt-2"></div>
        </div>
      </div>

      <!-- Listings -->
      <div v-else-if="listings.length" class="listings-container">
        <div v-for="listing in listings" :key="listing.id" class="listing-card">
          <!-- Image -->
          <router-link :to="`/listing/${listing.slug}`" class="listing-image">
            <img :src="listing.primary_image_url" :alt="listing.title" />
            <span class="status-badge" :class="getStatusClass(listing.status)">
              {{ listing.status }}
            </span>
          </router-link>

          <!-- Content -->
          <div class="listing-content">
            <router-link :to="`/listing/${listing.slug}`" class="listing-title">
              {{ listing.title }}
            </router-link>
            <p class="listing-price">{{ cleanPrice(listing.formatted_price) }}</p>

            <!-- Stats -->
            <div class="listing-stats">
              <span><EyeIcon class="w-4 h-4" /> {{ listing.views_count }}</span>
              <span><HeartIcon class="w-4 h-4" /> {{ listing.favorites_count }}</span>
              <span><ChatBubbleLeftIcon class="w-4 h-4" /> {{ listing.contacts_count || 0 }}</span>
            </div>

            <!-- Actions -->
            <div class="listing-actions">
              <router-link :to="`/listing/${listing.id}/edit`" class="action-btn edit">
                <PencilIcon class="w-4 h-4" />
                Edit
              </router-link>

              <button
                v-if="listing.status === 'active'"
                @click="markAsSold(listing)"
                class="action-btn sold"
              >
                <CheckIcon class="w-4 h-4" />
                Mark Sold
              </button>

              <button
                v-if="listing.status === 'expired' || listing.status === 'sold'"
                @click="renewListing(listing)"
                class="action-btn renew"
              >
                <ArrowPathIcon class="w-4 h-4" />
                Renew
              </button>

              <button @click="confirmDelete(listing)" class="action-btn delete">
                <TrashIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <div class="empty-icon">
          <ClipboardDocumentListIcon class="w-16 h-16" />
        </div>
        <h3>No listings yet</h3>
        <p>Start selling by posting your first ad</p>
        <router-link to="/sell" class="btn-primary">
          Post Your First Ad
        </router-link>
      </div>

      <!-- Pagination -->
      <div v-if="lastPage > 1" class="pagination">
        <button
          v-for="page in lastPage"
          :key="page"
          @click="goToPage(page)"
          class="page-btn"
          :class="{ 'active': currentPage === page }"
        >
          {{ page }}
        </button>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      v-if="showDeleteModal"
      title="Delete Listing"
      message="Are you sure you want to delete this listing? This action cannot be undone."
      confirmText="Delete"
      confirmClass="btn-danger"
      @confirm="deleteListing"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useListingsStore } from '@/stores/listings'
import { toast } from 'vue3-toastify'
import ConfirmModal from '@/components/modals/ConfirmModal.vue'
import {
  PlusIcon,
  EyeIcon,
  HeartIcon,
  ChatBubbleLeftIcon,
  PencilIcon,
  CheckIcon,
  ArrowPathIcon,
  TrashIcon,
  ClipboardDocumentListIcon,
  CheckCircleIcon,
  ClockIcon,
  ShoppingCartIcon,
} from '@heroicons/vue/24/outline'

const listingsStore = useListingsStore()

const loading = ref(true)
const listings = ref([])
const currentTab = ref('all')
const currentPage = ref(1)
const lastPage = ref(1)
const showDeleteModal = ref(false)
const listingToDelete = ref(null)

const tabs = ref([
  { label: 'All', value: 'all', count: 0 },
  { label: 'Active', value: 'active', count: 0 },
  { label: 'Pending', value: 'pending', count: 0 },
  { label: 'Sold', value: 'sold', count: 0 },
  { label: 'Expired', value: 'expired', count: 0 },
])

const getStatusClass = (status) => {
  const classes = {
    active: 'status-active',
    pending: 'status-pending',
    sold: 'status-sold',
    expired: 'status-expired',
    rejected: 'status-rejected',
  }
  return classes[status] || 'status-default'
}

const cleanPrice = (price) => {
  return price?.replace(/\s*\(Negotiable\)/gi, '').trim() || ''
}

const fetchListings = async () => {
  loading.value = true
  try {
    const params = { page: currentPage.value }
    if (currentTab.value !== 'all') {
      params.status = currentTab.value
    }
    const response = await listingsStore.fetchMyListings(params)
    listings.value = response.data
    lastPage.value = response.meta.last_page

    // Update counts
    tabs.value[0].count = response.meta.total
    // For individual status counts, ideally get from API
    if (response.counts) {
      tabs.value[1].count = response.counts.active || 0
      tabs.value[2].count = response.counts.pending || 0
      tabs.value[3].count = response.counts.sold || 0
      tabs.value[4].count = response.counts.expired || 0
    }
  } finally {
    loading.value = false
  }
}

const markAsSold = async (listing) => {
  try {
    await listingsStore.markAsSold(listing.id)
    toast.success('Listing marked as sold!')
    fetchListings()
  } catch (error) {
    toast.error('Failed to update listing')
  }
}

const renewListing = async (listing) => {
  try {
    await listingsStore.renewListing(listing.id)
    toast.success('Listing renewed for 30 days!')
    fetchListings()
  } catch (error) {
    toast.error('Failed to renew listing')
  }
}

const confirmDelete = (listing) => {
  listingToDelete.value = listing
  showDeleteModal.value = true
}

const deleteListing = async () => {
  try {
    await listingsStore.deleteListing(listingToDelete.value.id)
    toast.success('Listing deleted')
    showDeleteModal.value = false
    fetchListings()
  } catch (error) {
    toast.error('Failed to delete listing')
  }
}

const goToPage = (page) => {
  currentPage.value = page
  fetchListings()
}

watch(currentTab, () => {
  currentPage.value = 1
  fetchListings()
})

onMounted(() => {
  fetchListings()
})
</script>

<style scoped>
.my-listings-page {
  min-height: 100vh;
  background: #f8fafc;
}

/* Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
}

.page-header h1 {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
}

.page-header p {
  font-size: 14px;
  color: #64748b;
  margin-top: 4px;
}

.post-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #7c3aed;
  color: white;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s;
}

.post-btn:hover {
  background: #6d28d9;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-bottom: 24px;
}

@media (min-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 12px;
  background: white;
  padding: 16px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
}

.stat-label {
  font-size: 12px;
  color: #64748b;
}

/* Filters */
.filters-row {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;
  overflow-x: auto;
  padding-bottom: 8px;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.filters-row::-webkit-scrollbar {
  display: none;
}

.filter-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 500;
  color: #64748b;
  flex-shrink: 0;
  white-space: nowrap;
  transition: all 0.2s;
}

.filter-btn:hover {
  border-color: #7c3aed;
  color: #7c3aed;
}

.filter-btn.active {
  background: #7c3aed;
  border-color: #7c3aed;
  color: white;
}

.filter-btn .count {
  padding: 2px 8px;
  background: rgba(0, 0, 0, 0.1);
  border-radius: 10px;
  font-size: 11px;
}

.filter-btn.active .count {
  background: rgba(255, 255, 255, 0.2);
}

/* Listings Container */
.listings-container {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Listing Card */
.listing-card {
  display: flex;
  gap: 16px;
  background: white;
  border-radius: 16px;
  padding: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.2s;
}

.listing-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

@media (max-width: 640px) {
  .listing-card {
    flex-direction: column;
  }
}

.listing-image {
  position: relative;
  width: 180px;
  flex-shrink: 0;
}

@media (max-width: 640px) {
  .listing-image {
    width: 100%;
  }
}

.listing-image img {
  width: 100%;
  aspect-ratio: 4/3;
  object-fit: cover;
  border-radius: 12px;
  background: #f1f5f9;
}

.status-badge {
  position: absolute;
  top: 8px;
  left: 8px;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  text-transform: capitalize;
}

.status-active {
  background: #dcfce7;
  color: #16a34a;
}

.status-pending {
  background: #fef3c7;
  color: #d97706;
}

.status-sold {
  background: #dbeafe;
  color: #2563eb;
}

.status-expired {
  background: #f1f5f9;
  color: #64748b;
}

.status-rejected {
  background: #fee2e2;
  color: #dc2626;
}

.listing-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.listing-title {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
  line-height: 1.4;
  margin-bottom: 4px;
}

.listing-title:hover {
  color: #7c3aed;
}

.listing-price {
  font-size: 18px;
  font-weight: 700;
  color: #7c3aed;
  margin-bottom: 12px;
}

.listing-stats {
  display: flex;
  gap: 16px;
  font-size: 13px;
  color: #64748b;
  margin-bottom: 16px;
}

.listing-stats span {
  display: flex;
  align-items: center;
  gap: 4px;
}

.listing-actions {
  display: flex;
  gap: 8px;
  margin-top: auto;
  flex-wrap: wrap;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.2s;
}

.action-btn.edit {
  background: #f1f5f9;
  color: #334155;
}

.action-btn.edit:hover {
  background: #e2e8f0;
}

.action-btn.sold {
  background: #dcfce7;
  color: #16a34a;
}

.action-btn.sold:hover {
  background: #bbf7d0;
}

.action-btn.renew {
  background: #dbeafe;
  color: #2563eb;
}

.action-btn.renew:hover {
  background: #bfdbfe;
}

.action-btn.delete {
  background: #fee2e2;
  color: #dc2626;
  padding: 8px;
}

.action-btn.delete:hover {
  background: #fecaca;
}

/* Skeleton */
.listing-card-skeleton {
  background: white;
  border-radius: 16px;
  padding: 16px;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 64px 20px;
  background: white;
  border-radius: 16px;
}

.empty-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 20px;
  background: #f1f5f9;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
}

.empty-state h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 8px;
}

.empty-state p {
  font-size: 14px;
  color: #64748b;
  margin-bottom: 24px;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 24px;
}

.page-btn {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  background: white;
  color: #64748b;
  font-weight: 500;
  transition: all 0.2s;
}

.page-btn:hover {
  background: #f1f5f9;
}

.page-btn.active {
  background: #7c3aed;
  color: white;
}
</style>
