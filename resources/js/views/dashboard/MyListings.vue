<template>
  <div class="min-h-screen bg-gray-50 py-6">
    <div class="container-app">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">My Listings</h1>
          <p class="text-gray-500">Manage your ads</p>
        </div>
        <router-link to="/sell" class="btn-primary">
          <PlusIcon class="w-5 h-5 mr-2" />
          Post New Ad
        </router-link>
      </div>

      <!-- Filters -->
      <div class="card p-4 mb-6">
        <div class="flex flex-wrap gap-4">
          <button
            v-for="tab in tabs"
            :key="tab.value"
            @click="currentTab = tab.value"
            class="px-4 py-2 rounded-lg font-medium transition"
            :class="currentTab === tab.value
              ? 'bg-primary-600 text-white'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
          >
            {{ tab.label }}
            <span class="ml-1 text-sm">({{ tab.count }})</span>
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="card p-4 flex gap-4">
          <div class="skeleton w-32 h-24 rounded"></div>
          <div class="flex-1">
            <div class="skeleton h-5 w-1/3 mb-2"></div>
            <div class="skeleton h-4 w-1/4 mb-2"></div>
            <div class="skeleton h-4 w-20"></div>
          </div>
        </div>
      </div>

      <!-- Listings -->
      <div v-else-if="listings.length" class="space-y-4">
        <div
          v-for="listing in listings"
          :key="listing.id"
          class="card p-4 flex flex-col sm:flex-row gap-4"
        >
          <!-- Image -->
          <router-link :to="`/listing/${listing.slug}`" class="sm:w-40 flex-shrink-0">
            <img
              :src="listing.primary_image_url"
              :alt="listing.title"
              class="w-full h-32 object-cover rounded-lg"
            />
          </router-link>

          <!-- Content -->
          <div class="flex-1">
            <div class="flex items-start justify-between">
              <div>
                <router-link
                  :to="`/listing/${listing.slug}`"
                  class="font-semibold text-gray-900 hover:text-primary-600"
                >
                  {{ listing.title }}
                </router-link>
                <p class="text-lg font-bold text-primary-600 mt-1">
                  {{ listing.formatted_price }}
                </p>
              </div>
              <span
                class="badge"
                :class="getStatusClass(listing.status)"
              >
                {{ listing.status }}
              </span>
            </div>

            <!-- Stats -->
            <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
              <span class="flex items-center">
                <EyeIcon class="w-4 h-4 mr-1" />
                {{ listing.views_count }} views
              </span>
              <span class="flex items-center">
                <HeartIcon class="w-4 h-4 mr-1" />
                {{ listing.favorites_count }} favorites
              </span>
              <span class="flex items-center">
                <ChatBubbleLeftIcon class="w-4 h-4 mr-1" />
                {{ listing.contacts_count }} contacts
              </span>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 mt-4">
              <router-link
                :to="`/listing/${listing.id}/edit`"
                class="btn-secondary btn-sm"
              >
                <PencilIcon class="w-4 h-4 mr-1" />
                Edit
              </router-link>

              <button
                v-if="listing.status === 'active'"
                @click="markAsSold(listing)"
                class="btn-sm bg-green-100 text-green-700 hover:bg-green-200"
              >
                <CheckIcon class="w-4 h-4 mr-1" />
                Mark as Sold
              </button>

              <button
                v-if="listing.status === 'expired' || listing.status === 'sold'"
                @click="renewListing(listing)"
                class="btn-sm bg-blue-100 text-blue-700 hover:bg-blue-200"
              >
                <ArrowPathIcon class="w-4 h-4 mr-1" />
                Renew
              </button>

              <button
                @click="confirmDelete(listing)"
                class="btn-sm bg-red-100 text-red-700 hover:bg-red-200"
              >
                <TrashIcon class="w-4 h-4 mr-1" />
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="card p-12 text-center">
        <ClipboardDocumentListIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No listings yet</h3>
        <p class="text-gray-500 mb-4">Start selling by posting your first ad</p>
        <router-link to="/sell" class="btn-primary">
          Post Your First Ad
        </router-link>
      </div>

      <!-- Pagination -->
      <div v-if="lastPage > 1" class="mt-6 flex justify-center gap-2">
        <button
          v-for="page in lastPage"
          :key="page"
          @click="goToPage(page)"
          class="w-10 h-10 rounded-lg"
          :class="currentPage === page
            ? 'bg-primary-600 text-white'
            : 'bg-white text-gray-600 hover:bg-gray-100'"
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
import { ref, computed, watch, onMounted } from 'vue'
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
    active: 'bg-green-100 text-green-800',
    pending: 'bg-yellow-100 text-yellow-800',
    sold: 'bg-blue-100 text-blue-800',
    expired: 'bg-gray-100 text-gray-800',
    rejected: 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
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

    // Update counts (simplified - in real app, get from API)
    tabs.value[0].count = response.meta.total
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
