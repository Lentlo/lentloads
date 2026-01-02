<template>
  <div class="saved-searches-page">
    <div class="container-app py-6">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1>Saved Searches</h1>
          <p>Get notified when new items match your criteria</p>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="searches-list">
        <div v-for="i in 3" :key="i" class="search-card-skeleton">
          <div class="skeleton h-6 w-1/3 mb-3"></div>
          <div class="skeleton h-4 w-2/3 mb-4"></div>
          <div class="skeleton h-10 w-full"></div>
        </div>
      </div>

      <!-- Saved Searches List -->
      <div v-else-if="searches.length" class="searches-list">
        <div v-for="search in searches" :key="search.id" class="search-card">
          <!-- Header -->
          <div class="search-header">
            <div class="search-info">
              <h3 class="search-name">{{ search.name }}</h3>
              <div class="search-tags">
                <span v-if="search.query" class="tag primary">
                  <MagnifyingGlassIcon class="w-3 h-3" />
                  {{ search.query }}
                </span>
                <span v-if="search.category" class="tag">
                  {{ search.category.name }}
                </span>
                <span v-if="search.city" class="tag">
                  <MapPinIcon class="w-3 h-3" />
                  {{ search.city }}
                </span>
                <span v-if="search.min_price || search.max_price" class="tag">
                  ₹{{ search.min_price || 0 }} - ₹{{ search.max_price || '∞' }}
                </span>
              </div>
            </div>
            <div class="search-actions">
              <button @click="runSearch(search)" class="action-btn primary" title="Run search">
                <MagnifyingGlassIcon class="w-5 h-5" />
              </button>
              <button @click="editSearch(search)" class="action-btn" title="Edit">
                <PencilIcon class="w-5 h-5" />
              </button>
              <button @click="deleteSearch(search.id)" class="action-btn danger" title="Delete">
                <TrashIcon class="w-5 h-5" />
              </button>
            </div>
          </div>

          <!-- Notification Settings -->
          <div class="notification-settings">
            <div class="notification-toggles">
              <label class="toggle-item" :class="{ 'active': search.notify_push }">
                <input
                  type="checkbox"
                  :checked="search.notify_push"
                  @change="toggleNotification(search, 'push')"
                />
                <BellIcon class="w-4 h-4" />
                <span>Push</span>
              </label>
              <label class="toggle-item" :class="{ 'active': search.notify_email }">
                <input
                  type="checkbox"
                  :checked="search.notify_email"
                  @change="toggleNotification(search, 'email')"
                />
                <EnvelopeIcon class="w-4 h-4" />
                <span>Email</span>
              </label>
            </div>
            <select v-model="search.notify_frequency" @change="updateFrequency(search)" class="frequency-select">
              <option value="instant">Instant</option>
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <div class="empty-icon">
          <BookmarkIcon class="w-16 h-16" />
        </div>
        <h3>No saved searches</h3>
        <p>Save a search to get notified when new items are posted</p>
        <router-link to="/search" class="btn-primary">
          <MagnifyingGlassIcon class="w-5 h-5" />
          Start Searching
        </router-link>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="editingSearch" class="modal-overlay" @click.self="editingSearch = null">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Edit Saved Search</h3>
          <button @click="editingSearch = null" class="modal-close">
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>
        <div class="modal-body">
          <label class="input-label">Search Name</label>
          <input v-model="editingSearch.name" type="text" class="input" placeholder="Enter a name" />
        </div>
        <div class="modal-footer">
          <button @click="editingSearch = null" class="btn-secondary">Cancel</button>
          <button @click="saveSearch" class="btn-primary">Save Changes</button>
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
  BellIcon,
  EnvelopeIcon,
  MapPinIcon,
  BookmarkIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'

const router = useRouter()

const loading = ref(true)
const searches = ref([])
const editingSearch = ref(null)

const fetchSearches = async () => {
  try {
    const response = await api.get('/saved-searches')
    // Ensure both notifications are enabled by default for display
    searches.value = response.data.data.map(search => ({
      ...search,
      notify_push: search.notify_push ?? true,
      notify_email: search.notify_email ?? true,
    }))
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

<style scoped>
.saved-searches-page {
  min-height: 100vh;
  background: #f8fafc;
}

/* Header */
.page-header {
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

/* Searches List */
.searches-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Search Card */
.search-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.search-card-skeleton {
  background: white;
  border-radius: 16px;
  padding: 20px;
}

.search-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 16px;
}

.search-info {
  flex: 1;
  min-width: 0;
}

.search-name {
  font-size: 18px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 8px;
}

.search-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  background: #f1f5f9;
  color: #475569;
  font-size: 12px;
  font-weight: 500;
  border-radius: 20px;
}

.tag.primary {
  background: #ede9fe;
  color: #7c3aed;
}

.search-actions {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
}

.action-btn {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  background: #f1f5f9;
  color: #64748b;
  transition: all 0.2s;
}

.action-btn:hover {
  background: #e2e8f0;
}

.action-btn.primary {
  background: #ede9fe;
  color: #7c3aed;
}

.action-btn.primary:hover {
  background: #ddd6fe;
}

.action-btn.danger {
  background: #fef2f2;
  color: #ef4444;
}

.action-btn.danger:hover {
  background: #fee2e2;
}

/* Notification Settings */
.notification-settings {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
}

.notification-toggles {
  display: flex;
  gap: 12px;
}

.toggle-item {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}

.toggle-item input {
  display: none;
}

.toggle-item.active {
  background: #ede9fe;
  border-color: #c4b5fd;
  color: #7c3aed;
}

.frequency-select {
  padding: 8px 12px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  color: #334155;
  cursor: pointer;
}

.frequency-select:focus {
  outline: none;
  border-color: #7c3aed;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 64px 20px;
  background: white;
  border-radius: 16px;
}

.empty-icon {
  width: 100px;
  height: 100px;
  margin: 0 auto 20px;
  background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #7c3aed;
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

.empty-state .btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  z-index: 50;
}

.modal-content {
  background: white;
  border-radius: 16px;
  width: 100%;
  max-width: 400px;
  overflow: hidden;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #f1f5f9;
}

.modal-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1e293b;
}

.modal-close {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  color: #64748b;
}

.modal-close:hover {
  background: #f1f5f9;
}

.modal-body {
  padding: 20px;
}

.input-label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #334155;
  margin-bottom: 8px;
}

.modal-footer {
  display: flex;
  gap: 12px;
  padding: 16px 20px;
  background: #f8fafc;
}

.modal-footer button {
  flex: 1;
  padding: 10px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
}

.btn-secondary {
  background: white;
  border: 1px solid #e2e8f0;
  color: #334155;
}

.btn-primary {
  background: #7c3aed;
  color: white;
}
</style>
