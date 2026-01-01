<template>
  <form @submit.prevent="handleSearch" class="flex w-full">
    <div class="relative flex-1">
      <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
      <input
        v-model="query"
        type="text"
        placeholder="Search for anything..."
        class="w-full pl-10 pr-4 py-2.5 border border-r-0 border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        @focus="showSuggestions = true"
        @blur="hideSuggestions"
      />

      <!-- Suggestions dropdown -->
      <div
        v-if="showSuggestions && suggestions.length > 0"
        class="absolute top-full left-0 right-0 mt-1 bg-white rounded-lg shadow-lg border border-gray-100 z-50"
      >
        <button
          v-for="suggestion in suggestions"
          :key="suggestion"
          type="button"
          class="w-full px-4 py-2 text-left hover:bg-gray-50 text-gray-700"
          @mousedown="selectSuggestion(suggestion)"
        >
          {{ suggestion }}
        </button>
      </div>
    </div>

    <button
      type="submit"
      class="px-6 py-2.5 bg-primary-600 text-white font-medium rounded-r-lg hover:bg-primary-700 transition"
    >
      Search
    </button>
  </form>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import api from '@/services/api'

const router = useRouter()

const query = ref('')
const suggestions = ref([])
const showSuggestions = ref(false)
let debounceTimer = null

const handleSearch = () => {
  if (query.value.trim()) {
    router.push({ path: '/search', query: { q: query.value } })
    showSuggestions.value = false
  }
}

const selectSuggestion = (suggestion) => {
  query.value = suggestion
  handleSearch()
}

const hideSuggestions = () => {
  setTimeout(() => {
    showSuggestions.value = false
  }, 200)
}

// Fetch suggestions
watch(query, async (value) => {
  clearTimeout(debounceTimer)

  if (value.length < 2) {
    suggestions.value = []
    return
  }

  debounceTimer = setTimeout(async () => {
    try {
      const response = await api.get('/search/suggestions', { params: { q: value } })
      suggestions.value = response.data.data
    } catch (error) {
      suggestions.value = []
    }
  }, 300)
})
</script>
