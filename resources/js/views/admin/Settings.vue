<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Settings</h1>

    <!-- Tabs -->
    <div class="flex gap-4 mb-6 border-b">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="activeTab = tab.id"
        class="px-4 py-2 font-medium border-b-2 -mb-px transition"
        :class="activeTab === tab.id
          ? 'border-primary-600 text-primary-600'
          : 'border-transparent text-gray-500 hover:text-gray-700'"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- General Settings -->
    <div v-if="activeTab === 'general'" class="card p-6">
      <h2 class="text-lg font-semibold mb-4">General Settings</h2>
      <form @submit.prevent="saveSettings('general')" class="space-y-4">
        <div>
          <label class="label">Site Name</label>
          <input v-model="settings.site_name" type="text" class="input" />
        </div>
        <div>
          <label class="label">Site Description</label>
          <textarea v-model="settings.site_description" rows="2" class="input"></textarea>
        </div>
        <div>
          <label class="label">Contact Email</label>
          <input v-model="settings.contact_email" type="email" class="input" />
        </div>
        <div>
          <label class="label">Contact Phone</label>
          <input v-model="settings.contact_phone" type="tel" class="input" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Default Currency</label>
            <select v-model="settings.currency" class="input">
              <option value="INR">INR (₹)</option>
              <option value="USD">USD ($)</option>
              <option value="EUR">EUR (€)</option>
            </select>
          </div>
          <div>
            <label class="label">Default Language</label>
            <select v-model="settings.language" class="input">
              <option value="en">English</option>
              <option value="hi">Hindi</option>
            </select>
          </div>
        </div>
        <button type="submit" :disabled="saving" class="btn-primary">
          {{ saving ? 'Saving...' : 'Save Settings' }}
        </button>
      </form>
    </div>

    <!-- Listing Settings -->
    <div v-if="activeTab === 'listings'" class="card p-6">
      <h2 class="text-lg font-semibold mb-4">Listing Settings</h2>
      <form @submit.prevent="saveSettings('listings')" class="space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium">Require Approval</p>
            <p class="text-sm text-gray-500">New listings need admin approval</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input v-model="settings.require_approval" type="checkbox" class="sr-only peer" />
            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-primary-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
          </label>
        </div>

        <div>
          <label class="label">Max Images per Listing</label>
          <input v-model.number="settings.max_images" type="number" min="1" max="20" class="input w-32" />
        </div>

        <div>
          <label class="label">Listing Expiry (days)</label>
          <input v-model.number="settings.listing_expiry_days" type="number" min="7" max="365" class="input w-32" />
        </div>

        <div>
          <label class="label">Max Free Listings per User</label>
          <input v-model.number="settings.max_free_listings" type="number" min="0" class="input w-32" />
          <p class="text-sm text-gray-500 mt-1">Set 0 for unlimited</p>
        </div>

        <button type="submit" :disabled="saving" class="btn-primary">
          {{ saving ? 'Saving...' : 'Save Settings' }}
        </button>
      </form>
    </div>

    <!-- Pages -->
    <div v-if="activeTab === 'pages'" class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Static Pages</h2>
        <button @click="showPageModal(null)" class="btn-primary">
          <PlusIcon class="w-5 h-5 mr-2" />
          Add Page
        </button>
      </div>

      <div class="card overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Title</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Slug</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="page in pages" :key="page.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-medium text-gray-900">{{ page.title }}</td>
              <td class="px-4 py-3 text-gray-600">/page/{{ page.slug }}</td>
              <td class="px-4 py-3">
                <span
                  class="badge"
                  :class="page.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                >
                  {{ page.is_active ? 'Active' : 'Draft' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <button
                    @click="showPageModal(page)"
                    class="p-1 text-blue-600 hover:bg-blue-50 rounded"
                  >
                    <PencilIcon class="w-5 h-5" />
                  </button>
                  <button
                    @click="deletePage(page)"
                    class="p-1 text-red-600 hover:bg-red-50 rounded"
                  >
                    <TrashIcon class="w-5 h-5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Banners -->
    <div v-if="activeTab === 'banners'" class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Homepage Banners</h2>
        <button @click="showBannerModal(null)" class="btn-primary">
          <PlusIcon class="w-5 h-5 mr-2" />
          Add Banner
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div
          v-for="banner in banners"
          :key="banner.id"
          class="card overflow-hidden"
        >
          <img
            :src="banner.image_url"
            :alt="banner.title"
            class="w-full h-40 object-cover"
          />
          <div class="p-4">
            <div class="flex items-center justify-between mb-2">
              <h3 class="font-semibold">{{ banner.title }}</h3>
              <span
                class="badge"
                :class="banner.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
              >
                {{ banner.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <p v-if="banner.link" class="text-sm text-gray-500 truncate">{{ banner.link }}</p>
            <div class="flex gap-2 mt-4">
              <button @click="showBannerModal(banner)" class="btn-secondary btn-sm flex-1">
                Edit
              </button>
              <button @click="deleteBanner(banner)" class="btn-sm bg-red-100 text-red-700 flex-1">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Page Modal -->
    <div v-if="editingPage !== null" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="editingPage = null"></div>
      <div class="relative bg-white rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">{{ editingPage.id ? 'Edit Page' : 'Add Page' }}</h3>
        <form @submit.prevent="savePage" class="space-y-4">
          <div>
            <label class="label">Title</label>
            <input v-model="editingPage.title" type="text" class="input" required />
          </div>
          <div>
            <label class="label">Slug</label>
            <input v-model="editingPage.slug" type="text" class="input" placeholder="auto-generated" />
          </div>
          <div>
            <label class="label">Content</label>
            <textarea v-model="editingPage.content" rows="10" class="input font-mono text-sm"></textarea>
            <p class="text-sm text-gray-500 mt-1">HTML is supported</p>
          </div>
          <div class="flex items-center gap-2">
            <input v-model="editingPage.is_active" type="checkbox" class="rounded text-primary-600" />
            <label>Active</label>
          </div>
          <div class="flex gap-2">
            <button type="button" @click="editingPage = null" class="btn-secondary flex-1">Cancel</button>
            <button type="submit" :disabled="saving" class="btn-primary flex-1">
              {{ saving ? 'Saving...' : 'Save Page' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Banner Modal -->
    <div v-if="editingBanner !== null" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="editingBanner = null"></div>
      <div class="relative bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">{{ editingBanner.id ? 'Edit Banner' : 'Add Banner' }}</h3>
        <form @submit.prevent="saveBanner" class="space-y-4">
          <div>
            <label class="label">Title</label>
            <input v-model="editingBanner.title" type="text" class="input" required />
          </div>
          <div>
            <label class="label">Link (optional)</label>
            <input v-model="editingBanner.link" type="url" class="input" placeholder="https://" />
          </div>
          <div>
            <label class="label">Image</label>
            <input type="file" accept="image/*" @change="handleBannerImage" class="input" />
            <img v-if="editingBanner.image_url" :src="editingBanner.image_url" class="mt-2 h-24 rounded" />
          </div>
          <div>
            <label class="label">Position</label>
            <input v-model.number="editingBanner.position" type="number" class="input w-24" />
          </div>
          <div class="flex items-center gap-2">
            <input v-model="editingBanner.is_active" type="checkbox" class="rounded text-primary-600" />
            <label>Active</label>
          </div>
          <div class="flex gap-2">
            <button type="button" @click="editingBanner = null" class="btn-secondary flex-1">Cancel</button>
            <button type="submit" :disabled="saving" class="btn-primary flex-1">
              {{ saving ? 'Saving...' : 'Save Banner' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'

const tabs = [
  { id: 'general', label: 'General' },
  { id: 'listings', label: 'Listings' },
  { id: 'pages', label: 'Pages' },
  { id: 'banners', label: 'Banners' },
]

const activeTab = ref('general')
const saving = ref(false)
const settings = reactive({})
const pages = ref([])
const banners = ref([])
const editingPage = ref(null)
const editingBanner = ref(null)
const bannerImageFile = ref(null)

const fetchSettings = async () => {
  try {
    const response = await api.get('/admin/settings')
    Object.assign(settings, response.data.data)
  } catch (error) {
    console.error('Failed to fetch settings')
  }
}

const saveSettings = async (section) => {
  saving.value = true
  try {
    await api.put('/admin/settings', settings)
    toast.success('Settings saved')
  } catch (error) {
    toast.error('Failed to save settings')
  } finally {
    saving.value = false
  }
}

const fetchPages = async () => {
  try {
    const response = await api.get('/admin/pages')
    pages.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch pages')
  }
}

const showPageModal = (page) => {
  editingPage.value = page ? { ...page } : { title: '', slug: '', content: '', is_active: true }
}

const savePage = async () => {
  saving.value = true
  try {
    if (editingPage.value.id) {
      await api.put(`/admin/pages/${editingPage.value.id}`, editingPage.value)
    } else {
      await api.post('/admin/pages', editingPage.value)
    }
    toast.success('Page saved')
    editingPage.value = null
    fetchPages()
  } catch (error) {
    toast.error('Failed to save page')
  } finally {
    saving.value = false
  }
}

const deletePage = async (page) => {
  if (!confirm(`Delete "${page.title}"?`)) return
  try {
    await api.delete(`/admin/pages/${page.id}`)
    toast.success('Page deleted')
    fetchPages()
  } catch (error) {
    toast.error('Failed to delete page')
  }
}

const fetchBanners = async () => {
  try {
    const response = await api.get('/admin/banners')
    banners.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch banners')
  }
}

const showBannerModal = (banner) => {
  editingBanner.value = banner ? { ...banner } : { title: '', link: '', position: 0, is_active: true }
  bannerImageFile.value = null
}

const handleBannerImage = (event) => {
  bannerImageFile.value = event.target.files[0]
}

const saveBanner = async () => {
  saving.value = true
  try {
    const formData = new FormData()
    formData.append('title', editingBanner.value.title)
    formData.append('link', editingBanner.value.link || '')
    formData.append('position', editingBanner.value.position)
    formData.append('is_active', editingBanner.value.is_active ? 1 : 0)
    if (bannerImageFile.value) {
      formData.append('image', bannerImageFile.value)
    }

    if (editingBanner.value.id) {
      formData.append('_method', 'PUT')
      await api.post(`/admin/banners/${editingBanner.value.id}`, formData)
    } else {
      await api.post('/admin/banners', formData)
    }
    toast.success('Banner saved')
    editingBanner.value = null
    fetchBanners()
  } catch (error) {
    toast.error('Failed to save banner')
  } finally {
    saving.value = false
  }
}

const deleteBanner = async (banner) => {
  if (!confirm(`Delete "${banner.title}"?`)) return
  try {
    await api.delete(`/admin/banners/${banner.id}`)
    toast.success('Banner deleted')
    fetchBanners()
  } catch (error) {
    toast.error('Failed to delete banner')
  }
}

onMounted(() => {
  fetchSettings()
  fetchPages()
  fetchBanners()
})
</script>
