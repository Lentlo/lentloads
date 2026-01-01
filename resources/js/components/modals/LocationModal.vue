<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50" @click="$emit('close')"></div>

    <!-- Modal -->
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md max-h-[80vh] overflow-hidden">
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">Select Location</h3>
        <button @click="$emit('close')" class="p-1 hover:bg-gray-100 rounded">
          <XMarkIcon class="w-6 h-6 text-gray-500" />
        </button>
      </div>

      <!-- Content -->
      <div class="p-4">
        <!-- Detect Location -->
        <button
          @click="detectLocation"
          :disabled="detecting"
          class="w-full flex items-center justify-center gap-2 p-3 mb-4 bg-primary-50 text-primary-600 rounded-lg hover:bg-primary-100"
        >
          <MapPinIcon class="w-5 h-5" />
          {{ detecting ? 'Detecting...' : 'Use Current Location' }}
        </button>

        <!-- Search -->
        <div class="relative mb-4">
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search city..."
            class="input pl-10"
            @input="searchCities"
          />
        </div>

        <!-- Search Results -->
        <div v-if="searchResults.length" class="mb-4 max-h-40 overflow-y-auto">
          <button
            v-for="city in searchResults"
            :key="city.id"
            @click="selectCity(city)"
            class="w-full p-3 text-left hover:bg-gray-50 rounded-lg"
          >
            <span class="font-medium">{{ city.name }}</span>
            <span class="text-gray-500 text-sm">, {{ city.state }}</span>
          </button>
        </div>

        <!-- Popular Cities -->
        <div v-if="!searchQuery">
          <h4 class="font-medium text-gray-700 mb-2">Popular Cities</h4>
          <div class="grid grid-cols-2 gap-2">
            <button
              v-for="city in popularCities"
              :key="city.id"
              @click="selectCity(city)"
              class="p-2 text-left text-sm hover:bg-gray-50 rounded-lg"
            >
              {{ city.name }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAppStore } from '@/stores/app'
import api from '@/services/api'
import { XMarkIcon, MapPinIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

const emit = defineEmits(['close', 'select'])

const appStore = useAppStore()

const searchQuery = ref('')
const searchResults = ref([])
const detecting = ref(false)
let debounceTimer = null

const popularCities = computed(() => appStore.popularCities.slice(0, 10))

const searchCities = () => {
  clearTimeout(debounceTimer)

  if (searchQuery.value.length < 2) {
    searchResults.value = []
    return
  }

  debounceTimer = setTimeout(async () => {
    try {
      const response = await api.get('/locations/search-cities', {
        params: { q: searchQuery.value }
      })
      searchResults.value = response.data.data
    } catch (error) {
      searchResults.value = []
    }
  }, 300)
}

const selectCity = (city) => {
  appStore.setLocation(city)
  emit('select', city)
  emit('close')
}

const detectLocation = async () => {
  detecting.value = true
  try {
    const location = await appStore.detectLocation()
    emit('select', location)
    emit('close')
  } catch (error) {
    // Location detection failed
  } finally {
    detecting.value = false
  }
}
</script>
