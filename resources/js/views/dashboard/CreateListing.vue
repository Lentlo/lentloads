<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Progress Header -->
    <div class="bg-white border-b sticky top-0 z-40">
      <div class="container-app py-3">
        <div class="flex items-center gap-4">
          <button @click="handleBack" class="p-2 -ml-2 hover:bg-gray-100 rounded-full">
            <ArrowLeftIcon class="w-5 h-5 text-gray-600" />
          </button>
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
              <span class="text-sm font-medium text-gray-900">Step {{ currentStep }} of 4</span>
            </div>
            <div class="flex gap-1">
              <div
                v-for="i in 4"
                :key="i"
                class="h-1 flex-1 rounded-full transition-colors"
                :class="i <= currentStep ? 'bg-primary-600' : 'bg-gray-200'"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Auth Modal -->
    <AuthPromptModal
      :is-open="showAuthModal"
      @close="showAuthModal = false"
      @authenticated="onAuthenticated"
    />

    <div class="container-app py-4 pb-24 max-w-2xl">
      <!-- Step 1: Category -->
      <div v-if="currentStep === 1" class="space-y-4">
        <div class="text-center mb-6">
          <h1 class="text-xl font-bold text-gray-900">What are you selling?</h1>
          <p class="text-gray-500 text-sm mt-1">Select a category for your ad</p>
        </div>

        <div class="space-y-3">
          <div
            v-for="parent in categories"
            :key="parent.id"
            class="card overflow-hidden"
          >
            <button
              @click="toggleCategory(parent.id)"
              class="w-full p-4 flex items-center justify-between hover:bg-gray-50 transition"
            >
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                  <component :is="getCategoryIcon(parent.slug)" class="w-5 h-5 text-primary-600" />
                </div>
                <span class="font-medium text-gray-900">{{ parent.name }}</span>
              </div>
              <ChevronDownIcon
                class="w-5 h-5 text-gray-400 transition-transform"
                :class="expandedCategory === parent.id ? 'rotate-180' : ''"
              />
            </button>

            <div v-if="expandedCategory === parent.id && parent.children?.length" class="border-t bg-gray-50">
              <button
                v-for="child in parent.children"
                :key="child.id"
                @click="selectCategory(child.id)"
                class="w-full p-3 pl-14 text-left hover:bg-gray-100 transition flex items-center justify-between"
                :class="form.category_id === child.id ? 'bg-primary-50' : ''"
              >
                <span class="text-gray-700">{{ child.name }}</span>
                <CheckIcon v-if="form.category_id === child.id" class="w-5 h-5 text-primary-600" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 2: Details -->
      <div v-else-if="currentStep === 2" class="space-y-4">
        <div class="text-center mb-6">
          <h1 class="text-xl font-bold text-gray-900">Add details</h1>
          <p class="text-gray-500 text-sm mt-1">Provide information about your item</p>
        </div>

        <div class="card p-4 space-y-4">
          <!-- Title -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
            <input
              v-model="form.title"
              type="text"
              maxlength="100"
              class="input"
              placeholder="What are you selling?"
            />
            <p class="text-xs text-gray-500 mt-1 text-right">{{ form.title.length }}/100</p>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
            <textarea
              v-model="form.description"
              rows="4"
              maxlength="5000"
              class="input"
              placeholder="Describe your item in detail. Include key features, condition, and any important information."
            ></textarea>
            <p class="text-xs text-gray-500 mt-1 text-right">{{ form.description.length }}/5000</p>
          </div>

          <!-- Price -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Price *</label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">₹</span>
                <input
                  v-model="form.price"
                  type="number"
                  min="0"
                  class="input pl-8"
                  placeholder="0"
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Price Type</label>
              <select v-model="form.price_type" class="input">
                <option value="fixed">Fixed</option>
                <option value="negotiable">Negotiable</option>
                <option value="free">Free</option>
              </select>
            </div>
          </div>

          <!-- Condition -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Condition</label>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="condition in conditions"
                :key="condition.value"
                @click="form.condition = condition.value"
                type="button"
                class="px-4 py-2 rounded-full border text-sm transition"
                :class="form.condition === condition.value ? 'border-primary-500 bg-primary-50 text-primary-700' : 'hover:bg-gray-50'"
              >
                {{ condition.label }}
              </button>
            </div>
          </div>

          <!-- Brand & Model -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
              <input v-model="form.brand" type="text" class="input" placeholder="e.g., Apple" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
              <input v-model="form.model" type="text" class="input" placeholder="e.g., iPhone 15" />
            </div>
          </div>
        </div>
      </div>

      <!-- Step 3: Photos -->
      <div v-else-if="currentStep === 3" class="space-y-4">
        <div class="text-center mb-6">
          <h1 class="text-xl font-bold text-gray-900">Add photos</h1>
          <p class="text-gray-500 text-sm mt-1">Add up to 10 photos. First photo will be the cover.</p>
        </div>

        <div class="card p-4">
          <div class="grid grid-cols-3 gap-3">
            <!-- Uploaded images -->
            <div
              v-for="(image, index) in previewImages"
              :key="index"
              class="relative aspect-square bg-gray-100 rounded-xl overflow-hidden"
            >
              <img :src="image" class="w-full h-full object-cover" />
              <div v-if="index === 0" class="absolute top-2 left-2 px-2 py-0.5 bg-primary-600 text-white text-xs rounded">
                Cover
              </div>
              <button
                type="button"
                @click="removeImage(index)"
                class="absolute top-2 right-2 w-6 h-6 bg-black/50 text-white rounded-full flex items-center justify-center hover:bg-black/70"
              >
                <XMarkIcon class="w-4 h-4" />
              </button>
            </div>

            <!-- Upload button -->
            <label
              v-if="previewImages.length < 10"
              class="aspect-square bg-gray-100 rounded-xl border-2 border-dashed border-gray-300 flex flex-col items-center justify-center cursor-pointer hover:border-primary-500 hover:bg-primary-50 transition"
            >
              <CameraIcon class="w-8 h-8 text-gray-400 mb-1" />
              <span class="text-xs text-gray-500">Add Photo</span>
              <input
                type="file"
                accept="image/*"
                multiple
                class="hidden"
                @change="handleImageUpload"
              />
            </label>
          </div>

          <p v-if="previewImages.length === 0" class="text-center text-sm text-gray-500 mt-4">
            Add at least 1 photo to continue
          </p>
        </div>
      </div>

      <!-- Step 4: Location -->
      <div v-else-if="currentStep === 4" class="space-y-4">
        <div class="text-center mb-6">
          <h1 class="text-xl font-bold text-gray-900">Set your location</h1>
          <p class="text-gray-500 text-sm mt-1">This helps buyers find items near them</p>
        </div>

        <div class="card p-4">
          <LocationPicker
            :initial-city="form.city"
            :initial-state="form.state"
            @update:location="handleLocationUpdate"
          />
        </div>
      </div>
    </div>

    <!-- Bottom Actions -->
    <div class="bottom-actions">
      <div class="container-app flex gap-3">
        <button
          v-if="currentStep > 1"
          @click="prevStep"
          class="btn-secondary flex-1"
        >
          Back
        </button>
        <button
          v-if="currentStep < 4"
          @click="nextStep"
          :disabled="!canProceed"
          class="btn-primary flex-1"
        >
          Continue
        </button>
        <button
          v-else
          @click="handleSubmit"
          :disabled="!isFormValid || loading"
          class="btn-primary flex-1"
        >
          {{ loading ? 'Posting...' : 'Post Ad' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { useAuthStore } from '@/stores/auth'
import { useListingsStore } from '@/stores/listings'
import { toast } from 'vue3-toastify'
import {
  ArrowLeftIcon,
  CameraIcon,
  XMarkIcon,
  ChevronDownIcon,
  CheckIcon,
  ComputerDesktopIcon,
  DevicePhoneMobileIcon,
  HomeIcon,
  TruckIcon,
  ShoppingBagIcon,
  WrenchIcon,
  MusicalNoteIcon,
  SparklesIcon,
} from '@heroicons/vue/24/outline'
import LocationPicker from '@/components/common/LocationPicker.vue'
import AuthPromptModal from '@/components/common/AuthPromptModal.vue'

const router = useRouter()
const appStore = useAppStore()
const authStore = useAuthStore()
const listingsStore = useListingsStore()

const loading = ref(false)
const showAuthModal = ref(false)
const currentStep = ref(1)
const expandedCategory = ref(null)
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
  locality: '',
  postal_code: '',
  latitude: null,
  longitude: null,
})

const conditions = [
  { value: 'new', label: 'New' },
  { value: 'like_new', label: 'Like New' },
  { value: 'good', label: 'Good' },
  { value: 'fair', label: 'Fair' },
]

const categories = computed(() => appStore.categories)

const getCategoryIcon = (slug) => {
  const icons = {
    'electronics': ComputerDesktopIcon,
    'mobiles': DevicePhoneMobileIcon,
    'property': HomeIcon,
    'vehicles': TruckIcon,
    'fashion': ShoppingBagIcon,
    'services': WrenchIcon,
    'entertainment': MusicalNoteIcon,
  }
  return icons[slug] || SparklesIcon
}

const canProceed = computed(() => {
  if (currentStep.value === 1) return !!form.category_id
  if (currentStep.value === 2) return form.title && form.description && form.price !== ''
  if (currentStep.value === 3) return images.value.length > 0
  return true
})

const isFormValid = computed(() => {
  return form.category_id &&
    form.title &&
    form.description &&
    form.price !== '' &&
    form.city &&
    images.value.length > 0
})

const toggleCategory = (id) => {
  expandedCategory.value = expandedCategory.value === id ? null : id
}

const selectCategory = (id) => {
  form.category_id = id
  setTimeout(() => nextStep(), 300)
}

const handleImageUpload = (event) => {
  const files = Array.from(event.target.files)
  const remaining = 10 - images.value.length

  files.slice(0, remaining).forEach(file => {
    if (file.type.startsWith('image/')) {
      images.value.push(file)
      const reader = new FileReader()
      reader.onload = (e) => previewImages.value.push(e.target.result)
      reader.readAsDataURL(file)
    }
  })
}

const removeImage = (index) => {
  images.value.splice(index, 1)
  previewImages.value.splice(index, 1)
}

const handleLocationUpdate = (location) => {
  form.city = location.city
  form.state = location.state
  form.locality = location.locality
  form.postal_code = location.postal_code
  form.latitude = location.latitude
  form.longitude = location.longitude
}

const handleBack = () => {
  if (currentStep.value > 1) {
    prevStep()
  } else {
    router.back()
  }
}

const prevStep = () => {
  if (currentStep.value > 1) currentStep.value--
}

const nextStep = () => {
  if (currentStep.value < 4 && canProceed.value) currentStep.value++
}

const handleSubmit = async () => {
  if (!isFormValid.value) return

  if (!authStore.isAuthenticated) {
    showAuthModal.value = true
    return
  }

  await submitListing()
}

const submitListing = async () => {
  loading.value = true

  try {
    const data = { ...form, images: images.value }
    await listingsStore.createListing(data)
    toast.success('Your ad has been submitted for review!')
    router.push('/my-listings')
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to create listing')
  } finally {
    loading.value = false
  }
}

const onAuthenticated = async () => {
  showAuthModal.value = false
  await submitListing()
}

onMounted(() => {
  if (appStore.currentLocation) {
    form.city = appStore.currentLocation.city
    form.state = appStore.currentLocation.state
  }
})
</script>

<style scoped>
.bottom-actions {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: white;
  border-top: 1px solid #e5e7eb;
  padding: 12px 0;
  padding-bottom: max(12px, env(safe-area-inset-bottom, 12px));
  z-index: 40;
}
</style>
