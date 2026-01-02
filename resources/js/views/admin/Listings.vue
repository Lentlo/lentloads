<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Manage Listings</h1>
      <div v-if="selectedIds.length" class="flex items-center gap-2">
        <span class="text-sm text-gray-600">{{ selectedIds.length }} selected</span>
        <button @click="bulkApprove" class="btn-primary btn-sm">
          <CheckIcon class="w-4 h-4 mr-1" />
          Approve All
        </button>
        <button @click="bulkDelete" class="btn-danger btn-sm">
          <TrashIcon class="w-4 h-4 mr-1" />
          Delete All
        </button>
        <button @click="selectedIds = []" class="btn-secondary btn-sm">
          Clear
        </button>
      </div>
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
            <th class="px-4 py-3 text-left">
              <input
                type="checkbox"
                :checked="allSelected"
                @change="toggleSelectAll"
                class="rounded border-gray-300"
              />
            </th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Listing</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Seller</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Category</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Price</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Badges</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Stats</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-if="loading">
            <td colspan="9" class="px-4 py-8 text-center text-gray-500">
              Loading...
            </td>
          </tr>
          <tr v-else-if="!listings.length">
            <td colspan="9" class="px-4 py-8 text-center text-gray-500">
              No listings found
            </td>
          </tr>
          <tr v-for="listing in listings" :key="listing.id" class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <input
                type="checkbox"
                :checked="selectedIds.includes(listing.id)"
                @change="toggleSelect(listing.id)"
                class="rounded border-gray-300"
              />
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <img
                  :src="listing.primary_image_url"
                  :alt="listing.title"
                  class="w-12 h-12 rounded object-cover"
                />
                <div>
                  <p class="font-medium text-gray-900 truncate max-w-[200px]">{{ listing.title }}</p>
                  <p class="text-xs text-gray-500">ID: {{ listing.id }} | {{ formatDate(listing.created_at) }}</p>
                </div>
              </div>
            </td>
            <td class="px-4 py-3">
              <router-link :to="`/admin/users?search=${listing.user?.email}`" class="text-primary-600 hover:underline">
                {{ listing.user?.name }}
              </router-link>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ listing.category?.name }}</td>
            <td class="px-4 py-3 font-medium text-gray-900">{{ listing.formatted_price }}</td>
            <td class="px-4 py-3">
              <select
                v-model="listing.status"
                @change="updateStatus(listing)"
                class="text-xs border rounded px-2 py-1"
                :class="getStatusClass(listing.status)"
              >
                <option value="active">Active</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
                <option value="sold">Sold</option>
                <option value="expired">Expired</option>
              </select>
            </td>
            <td class="px-4 py-3">
              <div class="flex gap-1">
                <button
                  @click="toggleBadge(listing, 'is_featured')"
                  :class="listing.is_featured ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-400'"
                  class="px-2 py-1 rounded text-xs font-medium"
                  title="Featured"
                >
                  <StarIcon class="w-3 h-3" />
                </button>
                <button
                  @click="toggleBadge(listing, 'is_urgent')"
                  :class="listing.is_urgent ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-400'"
                  class="px-2 py-1 rounded text-xs font-medium"
                  title="Urgent"
                >
                  <BoltIcon class="w-3 h-3" />
                </button>
                <button
                  @click="toggleBadge(listing, 'is_highlighted')"
                  :class="listing.is_highlighted ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-400'"
                  class="px-2 py-1 rounded text-xs font-medium"
                  title="Highlighted"
                >
                  <SparklesIcon class="w-3 h-3" />
                </button>
              </div>
            </td>
            <td class="px-4 py-3 text-xs text-gray-500">
              <div class="flex flex-col gap-1">
                <span><EyeIcon class="w-3 h-3 inline" /> {{ listing.views_count || 0 }}</span>
                <span><HeartIcon class="w-3 h-3 inline" /> {{ listing.favorites_count || 0 }}</span>
              </div>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-1">
                <button
                  @click="openEditModal(listing)"
                  class="p-1 text-blue-600 hover:bg-blue-50 rounded"
                  title="Edit"
                >
                  <PencilIcon class="w-5 h-5" />
                </button>
                <a
                  :href="`/listing/${listing.slug}`"
                  target="_blank"
                  class="p-1 text-gray-600 hover:bg-gray-100 rounded"
                  title="View"
                >
                  <ArrowTopRightOnSquareIcon class="w-5 h-5" />
                </a>
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
                  class="p-1 text-orange-600 hover:bg-orange-50 rounded"
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

    <!-- Edit Modal -->
    <div v-if="editingListing" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="editingListing = null"></div>
      <div class="relative bg-white rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">Edit Listing</h3>
        <div class="space-y-4">
          <div>
            <label class="label">Title</label>
            <input v-model="editForm.title" class="input" />
          </div>
          <div>
            <label class="label">Description</label>
            <textarea v-model="editForm.description" rows="4" class="input"></textarea>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Price</label>
              <input v-model="editForm.price" type="number" class="input" />
            </div>
            <div>
              <label class="label">Price Type</label>
              <select v-model="editForm.price_type" class="input">
                <option value="fixed">Fixed</option>
                <option value="negotiable">Negotiable</option>
                <option value="free">Free</option>
                <option value="contact">Contact for Price</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Category</label>
              <select v-model="editForm.category_id" class="input">
                <option v-for="cat in allCategories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="label">Condition</label>
              <select v-model="editForm.condition" class="input">
                <option value="">Not specified</option>
                <option value="new">New</option>
                <option value="like_new">Like New</option>
                <option value="good">Good</option>
                <option value="fair">Fair</option>
                <option value="poor">Poor</option>
              </select>
            </div>
          </div>
          <!-- Location Picker -->
          <div>
            <label class="label">Location</label>
            <LocationPicker
              v-model:latitude="editForm.latitude"
              v-model:longitude="editForm.longitude"
              v-model:locality="editForm.locality"
              v-model:city="editForm.city"
              v-model:state="editForm.state"
              v-model:postalCode="editForm.postal_code"
              :initialLatitude="editForm.latitude"
              :initialLongitude="editForm.longitude"
            />
          </div>
          <div class="flex gap-2 pt-4">
            <button @click="editingListing = null" class="btn-secondary flex-1">
              Cancel
            </button>
            <button @click="saveListing" class="btn-primary flex-1">
              Save Changes
            </button>
          </div>
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
import { ref, reactive, computed, onMounted } from 'vue'
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
  PencilIcon,
  StarIcon,
  BoltIcon,
  SparklesIcon,
  HeartIcon,
  ArrowTopRightOnSquareIcon,
} from '@heroicons/vue/24/outline'
import LocationPicker from '@/components/common/LocationPicker.vue'

const appStore = useAppStore()

const loading = ref(true)
const listings = ref([])
const search = ref('')
const currentPage = ref(1)
const lastPage = ref(1)
const perPage = ref(20)
const total = ref(0)
const selectedIds = ref([])

// Edit modal
const editingListing = ref(null)
const editForm = reactive({
  title: '',
  description: '',
  price: 0,
  price_type: 'fixed',
  category_id: null,
  condition: '',
  city: '',
  state: '',
  locality: '',
  postal_code: '',
  latitude: null,
  longitude: null,
})

// Reject modal
const rejectingListing = ref(null)
const rejectReason = ref('')
const rejectNotes = ref('')

const filters = reactive({
  status: '',
  category: '',
})

const categories = ref([])
const allCategories = ref([])

const allSelected = computed(() => {
  return listings.value.length > 0 && listings.value.every(l => selectedIds.value.includes(l.id))
})

const formatDate = (date) => dayjs(date).format('MMM D, YYYY')

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800 border-green-200',
    pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    rejected: 'bg-red-100 text-red-800 border-red-200',
    sold: 'bg-blue-100 text-blue-800 border-blue-200',
    expired: 'bg-gray-100 text-gray-800 border-gray-200',
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

// Selection
const toggleSelect = (id) => {
  const index = selectedIds.value.indexOf(id)
  if (index > -1) {
    selectedIds.value.splice(index, 1)
  } else {
    selectedIds.value.push(id)
  }
}

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedIds.value = []
  } else {
    selectedIds.value = listings.value.map(l => l.id)
  }
}

// Edit
const openEditModal = (listing) => {
  editingListing.value = listing
  editForm.title = listing.title
  editForm.description = listing.description
  editForm.price = listing.price
  editForm.price_type = listing.price_type
  editForm.category_id = listing.category_id
  editForm.condition = listing.condition || ''
  editForm.city = listing.city || ''
  editForm.state = listing.state || ''
  editForm.locality = listing.locality || ''
  editForm.postal_code = listing.postal_code || ''
  editForm.latitude = listing.latitude ? parseFloat(listing.latitude) : null
  editForm.longitude = listing.longitude ? parseFloat(listing.longitude) : null
}

const saveListing = async () => {
  try {
    // Convert reactive editForm to plain object
    const data = {
      title: editForm.title,
      description: editForm.description,
      price: editForm.price,
      price_type: editForm.price_type,
      category_id: editForm.category_id,
      condition: editForm.condition,
      city: editForm.city,
      state: editForm.state,
      locality: editForm.locality,
      postal_code: editForm.postal_code,
      latitude: editForm.latitude,
      longitude: editForm.longitude,
    }
    await api.put(`/admin/listings/${editingListing.value.id}`, data)
    toast.success('Listing updated')
    editingListing.value = null
    fetchListings()
  } catch (error) {
    console.error('Save listing error:', error.response?.data || error)
    toast.error(error.response?.data?.message || 'Failed to update listing')
  }
}

// Status change
const updateStatus = async (listing) => {
  try {
    await api.put(`/admin/listings/${listing.id}`, { status: listing.status })
    toast.success('Status updated')
  } catch (error) {
    toast.error('Failed to update status')
    fetchListings()
  }
}

// Badge toggles
const toggleBadge = async (listing, badge) => {
  try {
    const newValue = !listing[badge]
    await api.post(`/admin/listings/${listing.id}/toggle-feature`, {
      [badge]: newValue,
    })
    listing[badge] = newValue
    toast.success(`${badge.replace('is_', '').replace('_', ' ')} ${newValue ? 'enabled' : 'disabled'}`)
  } catch (error) {
    toast.error('Failed to update badge')
  }
}

// Approve/Reject
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
      reason: rejectReason.value + (rejectNotes.value ? ': ' + rejectNotes.value : ''),
    })
    toast.success('Listing rejected')
    rejectingListing.value = null
    fetchListings()
  } catch (error) {
    toast.error('Failed to reject listing')
  }
}

// Delete
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

// Bulk actions
const bulkApprove = async () => {
  if (!confirm(`Approve ${selectedIds.value.length} listings?`)) return

  try {
    const response = await api.post('/admin/listings/bulk-approve', { ids: selectedIds.value })
    toast.success(response.data.message)
    selectedIds.value = []
    fetchListings()
  } catch (error) {
    toast.error('Failed to bulk approve')
  }
}

const bulkDelete = async () => {
  if (!confirm(`Delete ${selectedIds.value.length} listings? This cannot be undone.`)) return

  try {
    const response = await api.post('/admin/listings/bulk-delete', { ids: selectedIds.value })
    toast.success(response.data.message)
    selectedIds.value = []
    fetchListings()
  } catch (error) {
    toast.error('Failed to bulk delete')
  }
}

// Fetch all categories for edit modal
const fetchAllCategories = async () => {
  try {
    const response = await api.get('/categories')
    const cats = []
    response.data.data.forEach(parent => {
      cats.push(parent)
      if (parent.children) {
        parent.children.forEach(child => {
          cats.push({ ...child, name: `  └ ${child.name}` })
        })
      }
    })
    allCategories.value = cats
  } catch (error) {
    console.error('Failed to fetch categories')
  }
}

onMounted(async () => {
  await appStore.fetchCategories()
  categories.value = appStore.categories
  await fetchAllCategories()
  fetchListings()
})
</script>
