<template>
  <div class="location-picker">
    <!-- Map Container -->
    <div class="relative">
      <div ref="mapContainer" class="h-64 rounded-lg border border-gray-300 z-0"></div>

      <!-- Loading Overlay -->
      <div v-if="loading" class="absolute inset-0 bg-white/70 flex items-center justify-center rounded-lg">
        <div class="flex items-center gap-2 text-gray-600">
          <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ loadingText }}</span>
        </div>
      </div>
    </div>

    <!-- GPS Button -->
    <button
      type="button"
      @click="useMyLocation"
      :disabled="gpsLoading"
      class="mt-3 w-full flex items-center justify-center gap-2 px-4 py-2 bg-primary-50 hover:bg-primary-100 text-primary-700 rounded-lg transition"
    >
      <svg v-if="!gpsLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
      </svg>
      <svg v-else class="animate-spin h-5 w-5" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      {{ gpsLoading ? 'Getting location...' : 'Use My Current Location' }}
    </button>

    <p class="text-xs text-gray-500 mt-2 text-center">
      Click on the map to pin your location, or use GPS
    </p>

    <!-- Location Details Form -->
    <div v-if="hasLocation" class="mt-4 p-4 bg-gray-50 rounded-lg space-y-3">
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Locality / Area</label>
          <input
            v-model="locationData.locality"
            type="text"
            class="input text-sm"
            placeholder="e.g., Koramangala"
            @input="emitLocation"
          />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">City *</label>
          <input
            v-model="locationData.city"
            type="text"
            required
            class="input text-sm"
            placeholder="e.g., Bangalore"
            @input="emitLocation"
          />
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">State</label>
          <input
            v-model="locationData.state"
            type="text"
            class="input text-sm"
            placeholder="e.g., Karnataka"
            @input="emitLocation"
          />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">PIN Code</label>
          <input
            v-model="locationData.postal_code"
            type="text"
            class="input text-sm"
            placeholder="e.g., 560034"
            maxlength="6"
            @input="emitLocation"
          />
        </div>
      </div>
      <p class="text-xs text-gray-400">
        You can edit the auto-filled details if needed
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

// Fix Leaflet default marker icon issue
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
})

const props = defineProps({
  // v-model props
  latitude: {
    type: Number,
    default: null
  },
  longitude: {
    type: Number,
    default: null
  },
  city: {
    type: String,
    default: ''
  },
  state: {
    type: String,
    default: ''
  },
  locality: {
    type: String,
    default: ''
  },
  postalCode: {
    type: String,
    default: ''
  },
  // Legacy initial* props for backward compatibility
  initialLatitude: {
    type: Number,
    default: null
  },
  initialLongitude: {
    type: Number,
    default: null
  },
  initialCity: {
    type: String,
    default: ''
  },
  initialState: {
    type: String,
    default: ''
  },
  initialLocality: {
    type: String,
    default: ''
  },
  initialPostalCode: {
    type: String,
    default: ''
  }
})

const emit = defineEmits([
  'update:location',
  'update:latitude',
  'update:longitude',
  'update:locality',
  'update:city',
  'update:state',
  'update:postalCode'
])

const mapContainer = ref(null)
const loading = ref(false)
const loadingText = ref('Loading map...')
const gpsLoading = ref(false)

let map = null
let marker = null

const locationData = reactive({
  latitude: props.latitude ?? props.initialLatitude,
  longitude: props.longitude ?? props.initialLongitude,
  locality: props.locality || props.initialLocality || '',
  city: props.city || props.initialCity || '',
  state: props.state || props.initialState || '',
  postal_code: props.postalCode || props.initialPostalCode || '',
  address: ''
})

const hasLocation = computed(() => {
  // Show form fields if we have coordinates OR any location text data
  return (locationData.latitude && locationData.longitude) ||
         locationData.city ||
         locationData.state ||
         locationData.locality
})

// Initialize map
onMounted(() => {
  initMap()
  // Auto-detect user's location
  autoDetectLocation()
})

onUnmounted(() => {
  if (map) {
    map.remove()
    map = null
  }
})

const initMap = () => {
  if (!mapContainer.value) return

  // Default center: India (or initial coordinates if provided)
  const hasInitialCoords = props.latitude ?? props.initialLatitude
  const defaultLat = props.latitude ?? props.initialLatitude ?? 20.5937
  const defaultLng = props.longitude ?? props.initialLongitude ?? 78.9629
  const defaultZoom = hasInitialCoords ? 15 : 5

  map = L.map(mapContainer.value, {
    // Disable scroll zoom to prevent page scroll hijacking on mobile
    scrollWheelZoom: false,
    // Enable touch zoom for pinch gestures
    touchZoom: true,
    // Add zoom control
    zoomControl: true
  }).setView([defaultLat, defaultLng], defaultZoom)

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
  }).addTo(map)

  // Add marker if initial coordinates exist
  const initLat = props.latitude ?? props.initialLatitude
  const initLng = props.longitude ?? props.initialLongitude
  if (initLat && initLng) {
    addMarker(initLat, initLng)
  }

  // Click handler to place marker
  map.on('click', (e) => {
    const { lat, lng } = e.latlng
    addMarker(lat, lng)
    reverseGeocode(lat, lng)
  })
}

const addMarker = (lat, lng) => {
  // Remove existing marker
  if (marker) {
    map.removeLayer(marker)
  }

  // Add new draggable marker
  marker = L.marker([lat, lng], { draggable: true }).addTo(map)

  // Update location on drag end
  marker.on('dragend', (e) => {
    const position = e.target.getLatLng()
    reverseGeocode(position.lat, position.lng)
  })

  // Update coordinates
  locationData.latitude = lat
  locationData.longitude = lng
}

const reverseGeocode = async (lat, lng) => {
  loading.value = true
  loadingText.value = 'Getting address...'

  try {
    const response = await fetch(
      `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&addressdetails=1`,
      {
        headers: {
          'Accept-Language': 'en'
        }
      }
    )

    if (!response.ok) throw new Error('Geocoding failed')

    const data = await response.json()
    const address = data.address || {}

    // Update location data
    locationData.latitude = lat
    locationData.longitude = lng
    locationData.locality = address.suburb || address.neighbourhood || address.village || address.town || ''
    locationData.city = address.city || address.town || address.village || address.county || ''
    locationData.state = address.state || ''
    locationData.postal_code = address.postcode || ''
    locationData.address = data.display_name || ''

    emitLocation()
  } catch (error) {
    console.error('Reverse geocoding error:', error)
    // Still update coordinates even if geocoding fails
    locationData.latitude = lat
    locationData.longitude = lng
    emitLocation()
  } finally {
    loading.value = false
  }
}

const useMyLocation = () => {
  if (!navigator.geolocation) {
    alert('Geolocation is not supported by your browser')
    return
  }

  gpsLoading.value = true

  navigator.geolocation.getCurrentPosition(
    (position) => {
      const { latitude, longitude } = position.coords

      // Center map on user's location
      map.setView([latitude, longitude], 16)

      // Add marker
      addMarker(latitude, longitude)

      // Get address details
      reverseGeocode(latitude, longitude)

      gpsLoading.value = false
    },
    (error) => {
      gpsLoading.value = false
      let message = 'Unable to get your location'

      switch (error.code) {
        case error.PERMISSION_DENIED:
          message = 'Location permission denied. Please enable location access in your browser settings.'
          break
        case error.POSITION_UNAVAILABLE:
          message = 'Location information unavailable. Please try again.'
          break
        case error.TIMEOUT:
          message = 'Location request timed out. Please try again.'
          break
      }

      alert(message)
    },
    {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 0
    }
  )
}

const emitLocation = () => {
  // Emit combined location object
  emit('update:location', {
    latitude: locationData.latitude,
    longitude: locationData.longitude,
    locality: locationData.locality,
    city: locationData.city,
    state: locationData.state,
    postal_code: locationData.postal_code,
    address: locationData.address
  })
  // Emit individual v-model updates
  emit('update:latitude', locationData.latitude)
  emit('update:longitude', locationData.longitude)
  emit('update:locality', locationData.locality)
  emit('update:city', locationData.city)
  emit('update:state', locationData.state)
  emit('update:postalCode', locationData.postal_code)
}

// Auto-detect user's location on mount
const autoDetectLocation = async () => {
  // Skip if we already have initial coordinates (edit mode)
  const hasCoords = (props.latitude ?? props.initialLatitude) && (props.longitude ?? props.initialLongitude)
  if (hasCoords) {
    return
  }

  // Step 1: Try IP geolocation first (instant, city-level)
  try {
    loading.value = true
    loadingText.value = 'Detecting your city...'

    // Using ipapi.co for HTTPS support (1000 free requests/day)
    const response = await fetch('https://ipapi.co/json/')
    const data = await response.json()

    if (data.latitude && data.longitude) {
      // Zoom to detected city
      map.setView([data.latitude, data.longitude], 12)

      // Pre-fill city and state
      locationData.city = data.city || ''
      locationData.state = data.region || ''

      loading.value = false

      // Step 2: Try GPS for precise location (optional, better accuracy)
      tryGPSLocation()
    } else {
      loading.value = false
      // IP detection failed, try GPS directly
      tryGPSLocation()
    }
  } catch (error) {
    console.error('IP geolocation failed:', error)
    loading.value = false
    // Fallback to GPS
    tryGPSLocation()
  }
}

// Try GPS location silently (no alert on failure)
const tryGPSLocation = () => {
  if (!navigator.geolocation) return

  navigator.geolocation.getCurrentPosition(
    (position) => {
      const { latitude, longitude } = position.coords

      // Center map and add marker
      map.setView([latitude, longitude], 16)
      addMarker(latitude, longitude)

      // Get full address details
      reverseGeocode(latitude, longitude)
    },
    (error) => {
      // Silent fail - user can still click on map or use the button
      console.log('GPS not available or denied:', error.message)
    },
    {
      enableHighAccuracy: false, // Use low accuracy for faster response
      timeout: 5000,
      maximumAge: 300000 // Cache for 5 minutes
    }
  )
}

// Watch for v-model prop changes (useful for edit mode)
watch(() => [props.latitude, props.longitude], ([newLat, newLng]) => {
  if (newLat && newLng && map) {
    map.setView([newLat, newLng], 15)
    addMarker(newLat, newLng)
    locationData.latitude = newLat
    locationData.longitude = newLng
  }
}, { immediate: false })

// Watch for initial* prop changes (backward compatibility)
watch(() => [props.initialLatitude, props.initialLongitude], ([newLat, newLng]) => {
  if (newLat && newLng && map && !props.latitude && !props.longitude) {
    map.setView([newLat, newLng], 15)
    addMarker(newLat, newLng)
    locationData.latitude = newLat
    locationData.longitude = newLng
  }
}, { immediate: false })

// Watch for v-model text props to sync locationData
watch(
  () => [props.city, props.state, props.locality, props.postalCode],
  ([city, state, locality, postalCode]) => {
    if (city !== undefined && city !== null) locationData.city = city || ''
    if (state !== undefined && state !== null) locationData.state = state || ''
    if (locality !== undefined && locality !== null) locationData.locality = locality || ''
    if (postalCode !== undefined && postalCode !== null) locationData.postal_code = postalCode || ''
  },
  { immediate: true }
)

// Watch for initial* text props (backward compatibility)
watch(
  () => [props.initialCity, props.initialState, props.initialLocality, props.initialPostalCode],
  ([city, state, locality, postalCode]) => {
    // Only apply if v-model props are not set
    if (!props.city && city) locationData.city = city
    if (!props.state && state) locationData.state = state
    if (!props.locality && locality) locationData.locality = locality
    if (!props.postalCode && postalCode) locationData.postal_code = postalCode
  },
  { immediate: true }
)
</script>

<style>
.location-picker .leaflet-container {
  z-index: 0;
}
</style>
