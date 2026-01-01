<template>
  <div class="min-h-screen bg-gray-50 py-6">
    <div class="container-app max-w-3xl">
      <div class="card p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Listing</h1>

        <div v-if="loading" class="space-y-4">
          <div class="skeleton h-10 w-full"></div>
          <div class="skeleton h-10 w-full"></div>
          <div class="skeleton h-32 w-full"></div>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Category -->
          <div>
            <label class="label">Category *</label>
            <select v-model="form.category_id" required class="input">
              <option value="">Select a category</option>
              <optgroup
                v-for="parent in categories"
                :key="parent.id"
                :label="parent.name"
              >
                <option
                  v-for="child in parent.children"
                  :key="child.id"
                  :value="child.id"
                >
                  {{ child.name }}
                </option>
              </optgroup>
            </select>
          </div>

          <!-- Title -->
          <div>
            <label class="label">Title *</label>
            <input
              v-model="form.title"
              type="text"
              maxlength="100"
              required
              class="input"
            />
            <p class="text-sm text-gray-500 mt-1">{{ form.title.length }}/100</p>
          </div>

          <!-- Description -->
          <div>
            <label class="label">Description *</label>
            <textarea
              v-model="form.description"
              rows="5"
              maxlength="5000"
              required
              class="input"
            ></textarea>
          </div>

          <!-- Price -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Price *</label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">₹</span>
                <input
                  v-model="form.price"
                  type="number"
                  min="0"
                  required
                  class="input pl-8"
                />
              </div>
            </div>
            <div>
              <label class="label">Price Type</label>
              <select v-model="form.price_type" class="input">
                <option value="fixed">Fixed</option>
                <option value="negotiable">Negotiable</option>
                <option value="free">Free</option>
                <option value="contact">Contact for Price</option>
              </select>
            </div>
          </div>

          <!-- Condition -->
          <div>
            <label class="label">Condition</label>
            <select v-model="form.condition" class="input">
              <option value="">Select condition</option>
              <option value="new">New</option>
              <option value="like_new">Like New</option>
              <option value="good">Good</option>
              <option value="fair">Fair</option>
              <option value="poor">Poor</option>
            </select>
          </div>

          <!-- Brand & Model -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Brand</label>
              <input v-model="form.brand" type="text" class="input" />
            </div>
            <div>
              <label class="label">Model</label>
              <input v-model="form.model" type="text" class="input" />
            </div>
          </div>

          <!-- Current Images -->
          <div>
            <label class="label">Current Photos</label>
            <div class="grid grid-cols-4 gap-4">
              <div
                v-for="image in existingImages"
                :key="image.id"
                class="relative aspect-square bg-gray-100 rounded-lg overflow-hidden"
              >
                <img :src="image.thumbnail_url" class="w-full h-full object-cover" />
                <button
                  type="button"
                  @click="deleteImage(image.id)"
                  class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center"
                >
                  <XMarkIcon class="w-4 h-4" />
                </button>
                <button
                  v-if="!image.is_primary"
                  type="button"
                  @click="setPrimaryImage(image.id)"
                  class="absolute bottom-1 left-1 text-xs bg-white px-2 py-1 rounded"
                >
                  Set as primary
                </button>
                <span
                  v-else
                  class="absolute bottom-1 left-1 text-xs bg-primary-500 text-white px-2 py-1 rounded"
                >
                  Primary
                </span>
              </div>

              <!-- Add more images -->
              <label
                v-if="existingImages.length < 10"
                class="aspect-square bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center cursor-pointer hover:border-primary-500"
              >
                <CameraIcon class="w-8 h-8 text-gray-400 mb-1" />
                <span class="text-sm text-gray-500">Add More</span>
                <input
                  type="file"
                  accept="image/*"
                  multiple
                  class="hidden"
                  @change="handleImageUpload"
                />
              </label>
            </div>
          </div>

          <!-- Location -->
          <div class="border-t pt-6">
            <h3 class="font-semibold text-gray-900 mb-4">Location</h3>
            <LocationPicker
              :initial-latitude="form.latitude"
              :initial-longitude="form.longitude"
              :initial-city="form.city"
              :initial-state="form.state"
              :initial-locality="form.locality"
              :initial-postal-code="form.postal_code"
              @update:location="handleLocationUpdate"
            />
          </div>

          <!-- Submit -->
          <div class="border-t pt-6 flex justify-end gap-4">
            <router-link to="/my-listings" class="btn-secondary">
              Cancel
            </router-link>
            <button
              type="submit"
              :disabled="submitting"
              class="btn-primary"
            >
              {{ submitting ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { useListingsStore } from '@/stores/listings'
import { toast } from 'vue3-toastify'
import api from '@/services/api'
import { CameraIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import LocationPicker from '@/components/common/LocationPicker.vue'

const route = useRoute()
const router = useRouter()
const appStore = useAppStore()
const listingsStore = useListingsStore()

const loading = ref(true)
const submitting = ref(false)
const existingImages = ref([])

const form = reactive({
  category_id: '',
  title: '',
  description: '',
  price: '',
  price_type: 'fixed',
  condition: '',
  brand: '',
  model: '',
  city: '',
  state: '',
  locality: '',
  postal_code: '',
  latitude: null,
  longitude: null,
})

const categories = computed(() => appStore.categories)

const fetchListing = async () => {
  try {
    const response = await api.get(`/listings/${route.params.id}`)
    const listing = response.data.data.listing

    form.category_id = listing.category_id
    form.title = listing.title
    form.description = listing.description
    form.price = listing.price
    form.price_type = listing.price_type
    form.condition = listing.condition || ''
    form.brand = listing.brand || ''
    form.model = listing.model || ''
    form.city = listing.city
    form.state = listing.state || ''
    form.locality = listing.locality || ''
    form.postal_code = listing.postal_code || ''
    form.latitude = listing.latitude || null
    form.longitude = listing.longitude || null

    existingImages.value = listing.images || []
  } catch (error) {
    toast.error('Failed to load listing')
    router.push('/my-listings')
  } finally {
    loading.value = false
  }
}

const handleImageUpload = async (event) => {
  const files = Array.from(event.target.files)
  const remaining = 10 - existingImages.value.length

  if (files.length > remaining) {
    toast.error(`You can only add ${remaining} more images`)
    return
  }

  try {
    await listingsStore.addImages(route.params.id, files.slice(0, remaining))
    toast.success('Images added')
    fetchListing() // Refresh to get new images
  } catch (error) {
    toast.error('Failed to upload images')
  }
}

const deleteImage = async (imageId) => {
  if (existingImages.value.length <= 1) {
    toast.error('Listing must have at least one image')
    return
  }

  try {
    await listingsStore.deleteImage(route.params.id, imageId)
    existingImages.value = existingImages.value.filter(img => img.id !== imageId)
    toast.success('Image deleted')
  } catch (error) {
    toast.error('Failed to delete image')
  }
}

const setPrimaryImage = async (imageId) => {
  try {
    await api.put(`/listings/${route.params.id}/images/${imageId}/primary`)
    existingImages.value = existingImages.value.map(img => ({
      ...img,
      is_primary: img.id === imageId
    }))
    toast.success('Primary image updated')
  } catch (error) {
    toast.error('Failed to update primary image')
  }
}

const handleLocationUpdate = (location) => {
  form.city = location.city
  form.state = location.state
  form.locality = location.locality
  form.postal_code = location.postal_code
  form.latitude = location.latitude
  form.longitude = location.longitude
}

const handleSubmit = async () => {
  submitting.value = true

  try {
    await listingsStore.updateListing(route.params.id, form)
    toast.success('Listing updated successfully')
    router.push('/my-listings')
  } catch (error) {
    toast.error('Failed to update listing')
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchListing()
})
</script>
