<template>
  <div class="min-h-screen bg-gray-50 py-6">
    <div class="container-app max-w-2xl">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Saved Searches</h1>
          <p class="text-gray-500">Get notified when new items match your criteria</p>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="card p-4">
          <div class="skeleton h-5 w-1/3 mb-2"></div>
          <div class="skeleton h-4 w-2/3"></div>
        </div>
      </div>

      <!-- Saved Searches List -->
      <div v-else-if="searches.length" class="space-y-4">
        <div
          v-for="search in searches"
          :key="search.id"
          class="card p-4"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <h3 class="font-semibold text-gray-900">{{ search.name }}</h3>
              <div class="flex flex-wrap gap-2 mt-2">
                <span v-if="search.query" class="badge-primary">
                  "{{ search.query }}"
                </span>
                <span v-if="search.category" class="badge bg-gray-100 text-gray-700">
                  {{ search.category.name }}
                </span>
                <span v-if="search.city" class="badge bg-gray-100 text-gray-700">
                  {{ search.city }}
                </span>
                <span v-if="search.min_price || search.max_price" class="badge bg-gray-100 text-gray-700">
                  ₹{{ search.min_price || 0 }} - ₹{{ search.max_price || '∞' }}
                </span>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <button
                @click="runSearch(search)"
                class="p-2 text-primary-600 hover:bg-primary-50 rounded"
                title="Run search"
              >
                <MagnifyingGlassIcon class="w-5 h-5" />
              </button>
              <button
                @click="editSearch(search)"
                class="p-2 text-gray-600 hover:bg-gray-100 rounded"
                title="Edit"
              >
                <PencilIcon class="w-5 h-5" />
              </button>
              <button
                @click="deleteSearch(search.id)"
                class="p-2 text-red-600 hover:bg-red-50 rounded"
                title="Delete"
              >
                <TrashIcon class="w-5 h-5" />
              </button>
            </div>
          </div>

          <!-- Notification settings -->
          <div class="flex items-center gap-4 mt-4 pt-4 border-t">
            <label class="flex items-center gap-2 text-sm">
              <input
                type="checkbox"
                :checked="search.notify_push"
                @change="toggleNotification(search, 'push')"
                class="rounded text-primary-600"
              />
              Push notifications
            </label>
            <label class="flex items-center gap-2 text-sm">
              <input
                type="checkbox"
                :checked="search.notify_email"
                @change="toggleNotification(search, 'email')"
                class="rounded text-primary-600"
              />
              Email notifications
            </label>
            <select
              v-model="search.notify_frequency"
              @change="updateFrequency(search)"
              class="text-sm border rounded px-2 py-1"
            >
              <option value="instant">Instant</option>
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="card p-12 text-center">
        <MagnifyingGlassIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No saved searches</h3>
        <p class="text-gray-500 mb-4">
          Save a search to get notified when new items are posted
        </p>
        <router-link to="/search" class="btn-primary">
          Start Searching
        </router-link>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="editingSearch" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="editingSearch = null"></div>
      <div class="relative bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Edit Saved Search</h3>
        <div class="space-y-4">
          <div>
            <label class="label">Name</label>
            <input v-model="editingSearch.name" type="text" class="input" />
          </div>
          <div class="flex gap-2">
            <button @click="editingSearch = null" class="btn-secondary flex-1">
              Cancel
            </button>
            <button @click="saveSearch" class="btn-primary flex-1">
              Save
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import {
  MagnifyingGlassIcon,
  PencilIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline'

const router = useRouter()

const loading = ref(true)
const searches = ref([])
const editingSearch = ref(null)

const fetchSearches = async () => {
  try {
    const response = await api.get('/saved-searches')
    searches.value = response.data.data
  } finally {
    loading.value = false
  }
}

const runSearch = (search) => {
  const query = {}
  if (search.query) query.q = search.query
  if (search.category_id) query.category = search.category?.slug
  if (search.city) query.city = search.city
  if (search.min_price) query.min_price = search.min_price
  if (search.max_price) query.max_price = search.max_price

  router.push({ path: '/search', query })
}

const editSearch = (search) => {
  editingSearch.value = { ...search }
}

const saveSearch = async () => {
  try {
    await api.put(`/saved-searches/${editingSearch.value.id}`, {
      name: editingSearch.value.name,
    })
    const index = searches.value.findIndex(s => s.id === editingSearch.value.id)
    if (index !== -1) {
      searches.value[index].name = editingSearch.value.name
    }
    editingSearch.value = null
    toast.success('Search updated')
  } catch (error) {
    toast.error('Failed to update search')
  }
}

const deleteSearch = async (id) => {
  if (!confirm('Delete this saved search?')) return

  try {
    await api.delete(`/saved-searches/${id}`)
    searches.value = searches.value.filter(s => s.id !== id)
    toast.success('Search deleted')
  } catch (error) {
    toast.error('Failed to delete search')
  }
}

const toggleNotification = async (search, type) => {
  const key = type === 'push' ? 'notify_push' : 'notify_email'
  search[key] = !search[key]

  try {
    await api.put(`/saved-searches/${search.id}`, {
      [key]: search[key],
    })
  } catch (error) {
    search[key] = !search[key]
    toast.error('Failed to update notification settings')
  }
}

const updateFrequency = async (search) => {
  try {
    await api.put(`/saved-searches/${search.id}`, {
      notify_frequency: search.notify_frequency,
    })
  } catch (error) {
    toast.error('Failed to update frequency')
  }
}

onMounted(() => {
  fetchSearches()
})
</script>
