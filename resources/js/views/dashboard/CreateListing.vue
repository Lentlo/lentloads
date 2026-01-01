<template>
  <div class="min-h-screen bg-gray-50 py-6">
    <div class="container-app max-w-3xl">
      <div class="card p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Post Your Ad</h1>

        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Category Selection -->
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
              placeholder="What are you selling?"
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
              placeholder="Describe your item in detail..."
            ></textarea>
            <p class="text-sm text-gray-500 mt-1">{{ form.description.length }}/5000</p>
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
                  placeholder="0"
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
              <label class="label">Brand (Optional)</label>
              <input v-model="form.brand" type="text" class="input" placeholder="e.g., Apple" />
            </div>
            <div>
              <label class="label">Model (Optional)</label>
              <input v-model="form.model" type="text" class="input" placeholder="e.g., iPhone 15" />
            </div>
          </div>

          <!-- Images -->
          <div>
            <label class="label">Photos * (Max 10)</label>
            <div class="grid grid-cols-4 gap-4">
              <!-- Uploaded images -->
              <div
                v-for="(image, index) in previewImages"
                :key="index"
                class="relative aspect-square bg-gray-100 rounded-lg overflow-hidden"
              >
                <img :src="image" class="w-full h-full object-cover" />
                <button
                  type="button"
                  @click="removeImage(index)"
                  class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center"
                >
                  <XMarkIcon class="w-4 h-4" />
                </button>
              </div>

              <!-- Upload button -->
              <label
                v-if="previewImages.length < 10"
                class="aspect-square bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex flex-col items-center justify-center cursor-pointer hover:border-primary-500 hover:bg-primary-50"
              >
                <CameraIcon class="w-8 h-8 text-gray-400 mb-1" />
                <span class="text-sm text-gray-500">Add Photo</span>
                <input
                  type="file"
                  accept="image/*"
                  multiple
                  class="hidden"
                  @change="handleImageUpload"
                />
              </label>
            </div>
            <p class="text-sm text-gray-500 mt-2">First image will be the cover photo</p>
          </div>

          <!-- Location -->
          <div class="border-t pt-6">
            <h3 class="font-semibold text-gray-900 mb-4">Your Location</h3>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="label">City *</label>
                <input
                  v-model="form.city"
                  type="text"
                  required
                  class="input"
                  placeholder="Your city"
                />
              </div>
              <div>
                <label class="label">State</label>
                <input
                  v-model="form.state"
                  type="text"
                  class="input"
                  placeholder="Your state"
                />
              </div>
            </div>
          </div>

          <!-- Submit -->
          <div class="border-t pt-6 flex justify-end gap-4">
            <router-link to="/my-listings" class="btn-secondary">
              Cancel
            </router-link>
            <button
              type="submit"
              :disabled="loading || !isFormValid"
              class="btn-primary"
            >
              {{ loading ? 'Posting...' : 'Post Ad' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { useListingsStore } from '@/stores/listings'
import { toast } from 'vue3-toastify'
import { CameraIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const appStore = useAppStore()
const listingsStore = useListingsStore()

const loading = ref(false)
const images = ref([])
const previewImages = ref([])

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
})

const categories = computed(() => appStore.categories)

const isFormValid = computed(() => {
  return form.category_id &&
    form.title &&
    form.description &&
    form.price !== '' &&
    form.city &&
    images.value.length > 0
})

const handleImageUpload = (event) => {
  const files = Array.from(event.target.files)
  const remaining = 10 - images.value.length

  files.slice(0, remaining).forEach(file => {
    if (file.type.startsWith('image/')) {
      images.value.push(file)

      const reader = new FileReader()
      reader.onload = (e) => {
        previewImages.value.push(e.target.result)
      }
      reader.readAsDataURL(file)
    }
  })
}

const removeImage = (index) => {
  images.value.splice(index, 1)
  previewImages.value.splice(index, 1)
}

const handleSubmit = async () => {
  if (!isFormValid.value) return

  loading.value = true

  try {
    const data = {
      ...form,
      images: images.value,
    }

    await listingsStore.createListing(data)
    toast.success('Your ad has been submitted for review!')
    router.push('/my-listings')
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to create listing')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  // Pre-fill location from saved location
  if (appStore.currentLocation) {
    form.city = appStore.currentLocation.city
    form.state = appStore.currentLocation.state
  }
})
</script>
