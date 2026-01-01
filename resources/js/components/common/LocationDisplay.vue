<template>
  <div class="location-display">
    <!-- Map Container -->
    <div ref="mapContainer" class="h-48 rounded-lg border border-gray-200 z-0"></div>

    <!-- Location Details -->
    <div class="mt-3 space-y-2">
      <!-- Location Text -->
      <div class="flex items-start gap-2">
        <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        <div>
          <p class="text-gray-900 font-medium">{{ locationText }}</p>
          <p v-if="postalCode" class="text-sm text-gray-500">PIN: {{ postalCode }}</p>
        </div>
      </div>

      <!-- Get Directions Button -->
      <a
        v-if="hasCoordinates"
        :href="directionsUrl"
        target="_blank"
        class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
        </svg>
        Get Directions
      </a>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
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
  latitude: {
    type: Number,
    default: null
  },
  longitude: {
    type: Number,
    default: null
  },
  locality: {
    type: String,
    default: ''
  },
  city: {
    type: String,
    default: ''
  },
  state: {
    type: String,
    default: ''
  },
  postalCode: {
    type: String,
    default: ''
  }
})

const mapContainer = ref(null)
let map = null
let marker = null

const hasCoordinates = computed(() => {
  return props.latitude && props.longitude
})

const locationText = computed(() => {
  const parts = []
  if (props.locality) parts.push(props.locality)
  if (props.city) parts.push(props.city)
  if (props.state) parts.push(props.state)
  return parts.join(', ') || 'Location not specified'
})

const directionsUrl = computed(() => {
  if (!hasCoordinates.value) return '#'
  return `https://www.google.com/maps/dir/?api=1&destination=${props.latitude},${props.longitude}`
})

onMounted(() => {
  initMap()
})

onUnmounted(() => {
  if (map) {
    map.remove()
    map = null
  }
})

const initMap = () => {
  if (!mapContainer.value) return

  // Default center: India (or provided coordinates)
  const lat = props.latitude || 20.5937
  const lng = props.longitude || 78.9629
  const zoom = hasCoordinates.value ? 14 : 5

  map = L.map(mapContainer.value, {
    scrollWheelZoom: false,
    dragging: !L.Browser.mobile,
    tap: false
  }).setView([lat, lng], zoom)

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
  }).addTo(map)

  // Add marker if coordinates exist
  if (hasCoordinates.value) {
    // Add a circle for approximate location (privacy)
    L.circle([props.latitude, props.longitude], {
      color: '#0d9488',
      fillColor: '#0d9488',
      fillOpacity: 0.2,
      radius: 500 // 500 meter radius for privacy
    }).addTo(map)

    // Add marker
    marker = L.marker([props.latitude, props.longitude]).addTo(map)
  }
}

// Watch for coordinate changes
watch(() => [props.latitude, props.longitude], ([newLat, newLng]) => {
  if (map && newLat && newLng) {
    map.setView([newLat, newLng], 14)

    // Remove old marker and circle
    if (marker) {
      map.removeLayer(marker)
    }

    // Add new circle and marker
    L.circle([newLat, newLng], {
      color: '#0d9488',
      fillColor: '#0d9488',
      fillOpacity: 0.2,
      radius: 500
    }).addTo(map)

    marker = L.marker([newLat, newLng]).addTo(map)
  }
})
</script>

<style>
.location-display .leaflet-container {
  z-index: 0;
}
</style>
