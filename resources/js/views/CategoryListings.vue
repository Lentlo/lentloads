<template>
  <div class="category-page min-h-screen bg-gray-50">
    <div class="container-app py-6">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 text-sm mb-4">
        <router-link to="/" class="text-gray-500 hover:text-gray-700">Home</router-link>
        <span class="text-gray-400">/</span>
        <router-link to="/categories" class="text-gray-500 hover:text-gray-700">Categories</router-link>
        <span class="text-gray-400">/</span>
        <span class="text-gray-900">{{ category?.name }}</span>
      </nav>

      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ category?.name }}</h1>
          <p class="text-gray-500">{{ total }} ads found</p>
        </div>

        <select v-model="sort" @change="fetchListings" class="input w-auto">
          <option value="newest">Newest First</option>
          <option value="price_low">Price: Low to High</option>
          <option value="price_high">Price: High to Low</option>
        </select>
      </div>

      <!-- Subcategories -->
      <div v-if="category?.children?.length" class="flex flex-wrap gap-2 mb-6">
        <router-link
          v-for="child in category.children"
          :key="child.id"
          :to="`/category/${child.slug}`"
          class="px-4 py-2 bg-white rounded-full border hover:border-primary-500 hover:text-primary-600 text-sm"
        >
          {{ child.name }}
        </router-link>
      </div>

      <!-- Listings -->
      <div v-if="loading" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div v-for="i in 8" :key="i" class="card p-4">
          <div class="skeleton h-40 mb-3"></div>
          <div class="skeleton h-4 w-20 mb-2"></div>
          <div class="skeleton h-4 w-full"></div>
        </div>
      </div>

      <div v-else-if="listings.length" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <ListingCard
          v-for="listing in listings"
          :key="listing.id"
          :listing="listing"
        />
      </div>

      <div v-else class="card p-12 text-center">
        <p class="text-gray-500">No listings in this category yet</p>
      </div>

      <!-- Load More -->
      <div v-if="hasMore" class="mt-6 text-center">
        <button @click="loadMore" :disabled="loading" class="btn-secondary">
          Load More
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAppStore } from '@/stores/app'
import api from '@/services/api'
import ListingCard from '@/components/common/ListingCard.vue'

const route = useRoute()
const appStore = useAppStore()

const loading = ref(true)
const category = ref(null)
const listings = ref([])
const sort = ref('newest')
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)

const hasMore = computed(() => currentPage.value < lastPage.value)

const fetchCategory = async () => {
  try {
    const response = await api.get(`/categories/${route.params.slug}`)
    category.value = response.data.data.category
  } catch (error) {
    console.error('Failed to fetch category')
  }
}

const fetchListings = async (append = false) => {
  loading.value = true
  try {
    const response = await api.get('/listings', {
      params: {
        category: route.params.slug,
        sort: sort.value,
        page: append ? currentPage.value + 1 : 1,
      }
    })

    if (append) {
      listings.value = [...listings.value, ...response.data.data]
    } else {
      listings.value = response.data.data
    }

    total.value = response.data.meta.total
    currentPage.value = response.data.meta.current_page
    lastPage.value = response.data.meta.last_page
  } finally {
    loading.value = false
  }
}

const loadMore = () => {
  fetchListings(true)
}

watch(() => route.params.slug, () => {
  fetchCategory()
  fetchListings()
})

onMounted(() => {
  fetchCategory()
  fetchListings()
})
</script>
