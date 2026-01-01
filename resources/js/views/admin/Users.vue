<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Manage Users</h1>
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
              placeholder="Search users..."
              class="input pl-10"
              @input="debouncedSearch"
            />
          </div>
        </div>
        <select v-model="filters.role" @change="fetchUsers" class="input w-auto">
          <option value="">All Roles</option>
          <option value="user">User</option>
          <option value="moderator">Moderator</option>
          <option value="admin">Admin</option>
        </select>
        <select v-model="filters.status" @change="fetchUsers" class="input w-auto">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="suspended">Suspended</option>
          <option value="banned">Banned</option>
        </select>
      </div>
    </div>

    <!-- Users Table -->
    <div class="card overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">User</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Email</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Role</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Verified</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Stats</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Joined</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-if="loading">
            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
              Loading...
            </td>
          </tr>
          <tr v-else-if="!users.length">
            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
              No users found
            </td>
          </tr>
          <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <img
                  :src="user.avatar_url"
                  :alt="user.name"
                  class="w-10 h-10 rounded-full object-cover"
                />
                <div>
                  <p class="font-medium text-gray-900">{{ user.name }}</p>
                  <p class="text-sm text-gray-500">{{ user.phone || 'No phone' }}</p>
                </div>
              </div>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ user.email }}</td>
            <td class="px-4 py-3">
              <select
                v-model="user.role"
                @change="updateRole(user)"
                class="text-xs border rounded px-2 py-1"
                :class="getRoleClass(user.role)"
              >
                <option value="user">User</option>
                <option value="moderator">Moderator</option>
                <option value="admin">Admin</option>
              </select>
            </td>
            <td class="px-4 py-3">
              <select
                v-model="user.status"
                @change="updateStatus(user)"
                class="text-xs border rounded px-2 py-1"
                :class="getStatusClass(user.status)"
              >
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
                <option value="banned">Banned</option>
              </select>
            </td>
            <td class="px-4 py-3">
              <button
                @click="toggleVerified(user)"
                :class="user.is_verified_seller ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400'"
                class="px-2 py-1 rounded text-xs font-medium flex items-center gap-1"
              >
                <CheckBadgeIcon class="w-4 h-4" />
                {{ user.is_verified_seller ? 'Verified' : 'Unverified' }}
              </button>
            </td>
            <td class="px-4 py-3 text-xs text-gray-500">
              <div class="flex flex-col gap-1">
                <span><TagIcon class="w-3 h-3 inline" /> {{ user.listings_count || 0 }} listings</span>
                <span><StarIcon class="w-3 h-3 inline" /> {{ user.rating || '0.0' }} rating</span>
              </div>
            </td>
            <td class="px-4 py-3 text-gray-600 text-sm">{{ formatDate(user.created_at) }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-1">
                <button
                  @click="viewUser(user)"
                  class="p-1 text-gray-600 hover:bg-gray-100 rounded"
                  title="View Details"
                >
                  <EyeIcon class="w-5 h-5" />
                </button>
                <button
                  @click="editUser(user)"
                  class="p-1 text-blue-600 hover:bg-blue-50 rounded"
                  title="Edit"
                >
                  <PencilIcon class="w-5 h-5" />
                </button>
                <button
                  v-if="user.role !== 'admin'"
                  @click="deleteUser(user)"
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
          Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, total) }} of {{ total }} users
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

    <!-- View User Modal -->
    <div v-if="viewingUser" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="viewingUser = null"></div>
      <div class="relative bg-white rounded-xl p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-start justify-between mb-6">
          <div class="flex items-center gap-4">
            <img
              :src="viewingUser.avatar_url"
              :alt="viewingUser.name"
              class="w-16 h-16 rounded-full object-cover"
            />
            <div>
              <h3 class="text-xl font-semibold">{{ viewingUser.name }}</h3>
              <p class="text-gray-500">{{ viewingUser.email }}</p>
              <div class="flex items-center gap-2 mt-1">
                <span :class="getStatusClass(viewingUser.status)" class="badge">{{ viewingUser.status }}</span>
                <span :class="getRoleClass(viewingUser.role)" class="badge">{{ viewingUser.role }}</span>
                <span v-if="viewingUser.is_verified_seller" class="badge bg-green-100 text-green-700">Verified Seller</span>
              </div>
            </div>
          </div>
          <button @click="viewingUser = null" class="p-2 text-gray-400 hover:text-gray-600">
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>

        <!-- User Stats -->
        <div class="grid grid-cols-4 gap-4 mb-6">
          <div class="bg-gray-50 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ viewingUser.listings_count || 0 }}</p>
            <p class="text-sm text-gray-500">Listings</p>
          </div>
          <div class="bg-gray-50 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ viewingUser.successful_sales || 0 }}</p>
            <p class="text-sm text-gray-500">Sales</p>
          </div>
          <div class="bg-gray-50 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ viewingUser.rating || '0.0' }}</p>
            <p class="text-sm text-gray-500">Rating</p>
          </div>
          <div class="bg-gray-50 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ viewingUser.reviews_received_count || 0 }}</p>
            <p class="text-sm text-gray-500">Reviews</p>
          </div>
        </div>

        <!-- User Listings -->
        <div>
          <h4 class="font-semibold mb-3">Recent Listings</h4>
          <div v-if="viewingUser.listings?.length" class="space-y-2">
            <div
              v-for="listing in viewingUser.listings"
              :key="listing.id"
              class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg"
            >
              <img
                :src="listing.primary_image_url"
                :alt="listing.title"
                class="w-12 h-12 rounded object-cover"
              />
              <div class="flex-1 min-w-0">
                <p class="font-medium truncate">{{ listing.title }}</p>
                <p class="text-sm text-gray-500">{{ listing.formatted_price }}</p>
              </div>
              <span :class="getListingStatusClass(listing.status)" class="badge text-xs">
                {{ listing.status }}
              </span>
              <a
                :href="`/listing/${listing.slug}`"
                target="_blank"
                class="p-1 text-gray-400 hover:text-gray-600"
              >
                <ArrowTopRightOnSquareIcon class="w-4 h-4" />
              </a>
            </div>
          </div>
          <p v-else class="text-gray-500 text-sm">No listings yet</p>
        </div>

        <!-- User Info -->
        <div class="mt-6 pt-6 border-t">
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <span class="text-gray-500">Phone:</span>
              <span class="ml-2">{{ viewingUser.phone || 'Not provided' }}</span>
            </div>
            <div>
              <span class="text-gray-500">Location:</span>
              <span class="ml-2">{{ viewingUser.city || 'Not provided' }}</span>
            </div>
            <div>
              <span class="text-gray-500">Joined:</span>
              <span class="ml-2">{{ formatDate(viewingUser.created_at) }}</span>
            </div>
            <div>
              <span class="text-gray-500">Last Active:</span>
              <span class="ml-2">{{ viewingUser.last_active_at ? formatDate(viewingUser.last_active_at) : 'Unknown' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit User Modal -->
    <div v-if="editingUser" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="editingUser = null"></div>
      <div class="relative bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Edit User</h3>
        <form @submit.prevent="saveUser" class="space-y-4">
          <div>
            <label class="label">Name</label>
            <input v-model="editForm.name" type="text" class="input" />
          </div>
          <div>
            <label class="label">Email</label>
            <input v-model="editForm.email" type="email" class="input" />
          </div>
          <div>
            <label class="label">Phone</label>
            <input v-model="editForm.phone" type="text" class="input" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Role</label>
              <select v-model="editForm.role" class="input">
                <option value="user">User</option>
                <option value="moderator">Moderator</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div>
              <label class="label">Status</label>
              <select v-model="editForm.status" class="input">
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
                <option value="banned">Banned</option>
              </select>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <input type="checkbox" v-model="editForm.is_verified_seller" id="verified" class="rounded" />
            <label for="verified" class="text-sm">Verified Seller</label>
          </div>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="editingUser = null" class="btn-secondary flex-1">
              Cancel
            </button>
            <button type="submit" class="btn-primary flex-1" :disabled="saving">
              {{ saving ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import dayjs from 'dayjs'
import debounce from 'lodash/debounce'
import {
  MagnifyingGlassIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  CheckBadgeIcon,
  TagIcon,
  StarIcon,
  XMarkIcon,
  ArrowTopRightOnSquareIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()

const loading = ref(true)
const saving = ref(false)
const users = ref([])
const search = ref(route.query.search || '')
const currentPage = ref(1)
const lastPage = ref(1)
const perPage = ref(20)
const total = ref(0)
const editingUser = ref(null)
const viewingUser = ref(null)

const editForm = reactive({
  name: '',
  email: '',
  phone: '',
  role: 'user',
  status: 'active',
  is_verified_seller: false,
})

const filters = reactive({
  role: '',
  status: '',
})

const formatDate = (date) => dayjs(date).format('MMM D, YYYY')

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    suspended: 'bg-yellow-100 text-yellow-800',
    banned: 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getRoleClass = (role) => {
  const classes = {
    admin: 'bg-purple-100 text-purple-800',
    moderator: 'bg-blue-100 text-blue-800',
    user: 'bg-gray-100 text-gray-800',
  }
  return classes[role] || 'bg-gray-100 text-gray-800'
}

const getListingStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    pending: 'bg-yellow-100 text-yellow-800',
    rejected: 'bg-red-100 text-red-800',
    sold: 'bg-blue-100 text-blue-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const fetchUsers = async () => {
  loading.value = true
  try {
    const response = await api.get('/admin/users', {
      params: {
        page: currentPage.value,
        search: search.value,
        role: filters.role,
        status: filters.status,
      }
    })
    users.value = response.data.data
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
  fetchUsers()
}, 300)

const goToPage = (page) => {
  if (page >= 1 && page <= lastPage.value) {
    currentPage.value = page
    fetchUsers()
  }
}

const viewUser = async (user) => {
  try {
    const response = await api.get(`/admin/users/${user.id}`)
    viewingUser.value = response.data.data
  } catch (error) {
    toast.error('Failed to load user details')
  }
}

const editUser = (user) => {
  editingUser.value = user
  editForm.name = user.name
  editForm.email = user.email
  editForm.phone = user.phone || ''
  editForm.role = user.role
  editForm.status = user.status
  editForm.is_verified_seller = user.is_verified_seller || false
}

const saveUser = async () => {
  saving.value = true
  try {
    await api.put(`/admin/users/${editingUser.value.id}`, editForm)
    toast.success('User updated')
    editingUser.value = null
    fetchUsers()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to update user')
  } finally {
    saving.value = false
  }
}

const updateRole = async (user) => {
  try {
    await api.put(`/admin/users/${user.id}`, { role: user.role })
    toast.success('Role updated')
  } catch (error) {
    toast.error('Failed to update role')
    fetchUsers()
  }
}

const updateStatus = async (user) => {
  try {
    await api.put(`/admin/users/${user.id}`, { status: user.status })
    toast.success('Status updated')
  } catch (error) {
    toast.error('Failed to update status')
    fetchUsers()
  }
}

const toggleVerified = async (user) => {
  try {
    const newValue = !user.is_verified_seller
    await api.put(`/admin/users/${user.id}`, { is_verified_seller: newValue })
    user.is_verified_seller = newValue
    toast.success(newValue ? 'Seller verified' : 'Verification removed')
  } catch (error) {
    toast.error('Failed to update verification')
  }
}

const deleteUser = async (user) => {
  if (!confirm(`Delete ${user.name}? This action cannot be undone.`)) return

  try {
    await api.delete(`/admin/users/${user.id}`)
    toast.success('User deleted')
    fetchUsers()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to delete user')
  }
}

onMounted(() => {
  fetchUsers()
})
</script>
