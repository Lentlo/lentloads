<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Manage Listings</h1>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-6">
      <div class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input
              v-model="search"
              type="text"
              placeholder="Search listings..."
              class="input pl-10"
              @input="debouncedSearch"
            />
          </div>
        </div>
        <select v-model="filters.status" @change="fetchListings" class="input w-auto">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="active">Active</option>
          <option value="rejected">Rejected</option>
          <option value="sold">Sold</option>
          <option value="expired">Expired</option>
        </select>
        <select v-model="filters.category" @change="fetchListings" class="input w-auto">
          <option value="">All Categories</option>
          <option v-for="category in categories" :key="category.id" :value="category.id">
            {{ category.name }}
          </option>
        </select>
      </div>
    </div>

    <!-- Listings Table -->
    <div class="card overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Listing</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Seller</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Category</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Price</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-if="loading">
            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
              Loading...
            </td>
          </tr>
          <tr v-else-if="!listings.length">
            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
              No listings found
            </td>
          </tr>
          <tr v-for="listing in listings" :key="listing.id" class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <img
                  :src="listing.primary_image_url"
                  :alt="listing.title"
                  class="w-12 h-12 rounded object-cover"
                />
                <div>
                  <p class="font-medium text-gray-900 truncate max-w-[200px]">{{ listing.title }}</p>
                  <p class="text-sm text-gray-500">ID: {{ listing.id }}</p>
                </div>
              </div>
            </td>
            <td class="px-4 py-3">
              <router-link :to="`/user/${listing.user?.id}`" class="text-primary-600 hover:underline">
                {{ listing.user?.name }}
              </router-link>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ listing.category?.name }}</td>
            <td class="px-4 py-3 font-medium text-gray-900">{{ listing.formatted_price }}</td>
            <td class="px-4 py-3">
              <span
                class="badge"
                :class="getStatusClass(listing.status)"
              >
                {{ listing.status }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ formatDate(listing.created_at) }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <router-link
                  :to="`/listing/${listing.slug}`"
                  class="p-1 text-gray-600 hover:bg-gray-100 rounded"
                  title="View"
                  target="_blank"
                >
                  <EyeIcon class="w-5 h-5" />
                </router-link>
                <button
                  v-if="listing.status === 'pending'"
                  @click="approveListing(listing)"
                  class="p-1 text-green-600 hover:bg-green-50 rounded"
                  title="Approve"
                >
                  <CheckIcon class="w-5 h-5" />
                </button>
                <button
                  v-if="listing.status === 'pending'"
                  @click="showRejectModal(listing)"
                  class="p-1 text-red-600 hover:bg-red-50 rounded"
                  title="Reject"
                >
                  <XMarkIcon class="w-5 h-5" />
                </button>
                <button
                  @click="deleteListing(listing)"
                  class="p-1 text-red-600 hover:bg-red-50 rounded"
                  title="Delete"
                >
                  <TrashIcon class="w-5 h-5" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="lastPage > 1" class="p-4 border-t flex items-center justify-between">
        <p class="text-sm text-gray-500">
          Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, total) }} of {{ total }} listings
        </p>
        <div class="flex gap-2">
          <button
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="btn-secondary btn-sm"
          >
            Previous
          </button>
          <button
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage === lastPage"
            class="btn-secondary btn-sm"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="rejectingListing" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="rejectingListing = null"></div>
      <div class="relative bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Reject Listing</h3>
        <div class="space-y-4">
          <div>
            <label class="label">Reason for rejection</label>
            <select v-model="rejectReason" class="input">
              <option value="">Select a reason</option>
              <option value="prohibited_item">Prohibited item</option>
              <option value="misleading_info">Misleading information</option>
              <option value="duplicate">Duplicate listing</option>
              <option value="inappropriate_content">Inappropriate content</option>
              <option value="wrong_category">Wrong category</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div>
            <label class="label">Additional notes (optional)</label>
            <textarea v-model="rejectNotes" rows="3" class="input" placeholder="Explain the issue..."></textarea>
          </div>
          <div class="flex gap-2">
            <button @click="rejectingListing = null" class="btn-secondary flex-1">
              Cancel
            </button>
            <button @click="rejectListing" :disabled="!rejectReason" class="btn-danger flex-1">
              Reject
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAppStore } from '@/stores/app'
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import dayjs from 'dayjs'
import debounce from 'lodash/debounce'
import {
  MagnifyingGlassIcon,
  EyeIcon,
  CheckIcon,
  XMarkIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline'

const appStore = useAppStore()

const loading = ref(true)
const listings = ref([])
const search = ref('')
const currentPage = ref(1)
const lastPage = ref(1)
const perPage = ref(20)
const total = ref(0)
const rejectingListing = ref(null)
const rejectReason = ref('')
const rejectNotes = ref('')

const filters = reactive({
  status: '',
  category: '',
})

const categories = ref([])

const formatDate = (date) => dayjs(date).format('MMM D, YYYY')

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    pending: 'bg-yellow-100 text-yellow-800',
    rejected: 'bg-red-100 text-red-800',
    sold: 'bg-blue-100 text-blue-800',
    expired: 'bg-gray-100 text-gray-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const fetchListings = async () => {
  loading.value = true
  try {
    const response = await api.get('/admin/listings', {
      params: {
        page: currentPage.value,
        search: search.value,
        status: filters.status,
        category_id: filters.category,
      }
    })
    listings.value = response.data.data
    currentPage.value = response.data.meta.current_page
    lastPage.value = response.data.meta.last_page
    perPage.value = response.data.meta.per_page
    total.value = response.data.meta.total
  } finally {
    loading.value = false
  }
}

const debouncedSearch = debounce(() => {
  currentPage.value = 1
  fetchListings()
}, 300)

const goToPage = (page) => {
  if (page >= 1 && page <= lastPage.value) {
    currentPage.value = page
    fetchListings()
  }
}

const approveListing = async (listing) => {
  try {
    await api.post(`/admin/listings/${listing.id}/approve`)
    toast.success('Listing approved')
    fetchListings()
  } catch (error) {
    toast.error('Failed to approve listing')
  }
}

const showRejectModal = (listing) => {
  rejectingListing.value = listing
  rejectReason.value = ''
  rejectNotes.value = ''
}

const rejectListing = async () => {
  try {
    await api.post(`/admin/listings/${rejectingListing.value.id}/reject`, {
      reason: rejectReason.value,
      notes: rejectNotes.value,
    })
    toast.success('Listing rejected')
    rejectingListing.value = null
    fetchListings()
  } catch (error) {
    toast.error('Failed to reject listing')
  }
}

const deleteListing = async (listing) => {
  if (!confirm(`Delete "${listing.title}"? This cannot be undone.`)) return

  try {
    await api.delete(`/admin/listings/${listing.id}`)
    toast.success('Listing deleted')
    fetchListings()
  } catch (error) {
    toast.error('Failed to delete listing')
  }
}

onMounted(async () => {
  await appStore.fetchCategories()
  categories.value = appStore.categories
  fetchListings()
})
</script>
