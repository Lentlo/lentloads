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
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Joined</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-if="loading">
            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
              Loading...
            </td>
          </tr>
          <tr v-else-if="!users.length">
            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
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
              <span
                class="badge"
                :class="user.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'"
              >
                {{ user.role }}
              </span>
            </td>
            <td class="px-4 py-3">
              <span
                class="badge"
                :class="getStatusClass(user.status)"
              >
                {{ user.status }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ formatDate(user.created_at) }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <button
                  @click="viewUser(user)"
                  class="p-1 text-gray-600 hover:bg-gray-100 rounded"
                  title="View"
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
                  v-if="user.status !== 'banned'"
                  @click="banUser(user)"
                  class="p-1 text-red-600 hover:bg-red-50 rounded"
                  title="Ban"
                >
                  <NoSymbolIcon class="w-5 h-5" />
                </button>
                <button
                  v-else
                  @click="unbanUser(user)"
                  class="p-1 text-green-600 hover:bg-green-50 rounded"
                  title="Unban"
                >
                  <CheckCircleIcon class="w-5 h-5" />
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

    <!-- Edit User Modal -->
    <div v-if="editingUser" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="editingUser = null"></div>
      <div class="relative bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Edit User</h3>
        <form @submit.prevent="saveUser" class="space-y-4">
          <div>
            <label class="label">Name</label>
            <input v-model="editingUser.name" type="text" class="input" />
          </div>
          <div>
            <label class="label">Role</label>
            <select v-model="editingUser.role" class="input">
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div>
            <label class="label">Status</label>
            <select v-model="editingUser.status" class="input">
              <option value="active">Active</option>
              <option value="suspended">Suspended</option>
              <option value="banned">Banned</option>
            </select>
          </div>
          <div class="flex gap-2">
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
import { useRouter } from 'vue-router'
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import dayjs from 'dayjs'
import debounce from 'lodash/debounce'
import {
  MagnifyingGlassIcon,
  EyeIcon,
  PencilIcon,
  NoSymbolIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline'

const router = useRouter()

const loading = ref(true)
const saving = ref(false)
const users = ref([])
const search = ref('')
const currentPage = ref(1)
const lastPage = ref(1)
const perPage = ref(20)
const total = ref(0)
const editingUser = ref(null)

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

const viewUser = (user) => {
  router.push(`/user/${user.id}`)
}

const editUser = (user) => {
  editingUser.value = { ...user }
}

const saveUser = async () => {
  saving.value = true
  try {
    await api.put(`/admin/users/${editingUser.value.id}`, {
      name: editingUser.value.name,
      role: editingUser.value.role,
      status: editingUser.value.status,
    })
    toast.success('User updated')
    editingUser.value = null
    fetchUsers()
  } catch (error) {
    toast.error('Failed to update user')
  } finally {
    saving.value = false
  }
}

const banUser = async (user) => {
  if (!confirm(`Ban ${user.name}? They will no longer be able to access the platform.`)) return

  try {
    await api.post(`/admin/users/${user.id}/ban`)
    toast.success('User banned')
    fetchUsers()
  } catch (error) {
    toast.error('Failed to ban user')
  }
}

const unbanUser = async (user) => {
  try {
    await api.post(`/admin/users/${user.id}/unban`)
    toast.success('User unbanned')
    fetchUsers()
  } catch (error) {
    toast.error('Failed to unban user')
  }
}

onMounted(() => {
  fetchUsers()
})
</script>
