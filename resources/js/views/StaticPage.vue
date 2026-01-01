<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="container-app max-w-4xl">
      <!-- Loading -->
      <div v-if="loading" class="card p-8">
        <div class="skeleton h-8 w-1/3 mb-6"></div>
        <div class="space-y-3">
          <div class="skeleton h-4 w-full"></div>
          <div class="skeleton h-4 w-full"></div>
          <div class="skeleton h-4 w-3/4"></div>
          <div class="skeleton h-4 w-full"></div>
          <div class="skeleton h-4 w-5/6"></div>
        </div>
      </div>

      <!-- Page Not Found -->
      <div v-else-if="!page" class="text-center py-12">
        <DocumentTextIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h2 class="text-xl font-bold text-gray-900 mb-2">Page not found</h2>
        <p class="text-gray-500 mb-4">The page you're looking for doesn't exist.</p>
        <router-link to="/" class="btn-primary">Go Home</router-link>
      </div>

      <!-- Page Content -->
      <div v-else>
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm mb-6">
          <router-link to="/" class="text-gray-500 hover:text-primary-600">Home</router-link>
          <ChevronRightIcon class="w-4 h-4 text-gray-400" />
          <span class="text-gray-900">{{ page.title }}</span>
        </nav>

        <article class="card p-8">
          <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ page.title }}</h1>
            <p v-if="page.updated_at" class="text-sm text-gray-500 mt-2">
              Last updated: {{ formatDate(page.updated_at) }}
            </p>
          </header>

          <div class="prose prose-lg max-w-none" v-html="sanitizedContent"></div>
        </article>

        <!-- Related Pages (if applicable) -->
        <div v-if="relatedPages.length" class="mt-8">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Related Pages</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <router-link
              v-for="related in relatedPages"
              :key="related.slug"
              :to="`/page/${related.slug}`"
              class="card p-4 hover:shadow-md transition"
            >
              <p class="font-medium text-gray-900">{{ related.title }}</p>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'
import dayjs from 'dayjs'
import { DocumentTextIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'

const route = useRoute()

const loading = ref(true)
const page = ref(null)
const relatedPages = ref([])

// Sanitize HTML to prevent XSS attacks
// Note: For production, install and use DOMPurify: npm install dompurify
const sanitizeHtml = (html) => {
  if (!html) return ''

  // Create a temporary element
  const temp = document.createElement('div')
  temp.innerHTML = html

  // Remove dangerous elements
  const dangerousTags = ['script', 'iframe', 'object', 'embed', 'form', 'input', 'button']
  dangerousTags.forEach(tag => {
    const elements = temp.querySelectorAll(tag)
    elements.forEach(el => el.remove())
  })

  // Remove dangerous attributes from all elements
  const allElements = temp.querySelectorAll('*')
  allElements.forEach(el => {
    // Remove event handlers
    const attrs = Array.from(el.attributes)
    attrs.forEach(attr => {
      if (attr.name.startsWith('on') || attr.name === 'href' && attr.value.startsWith('javascript:')) {
        el.removeAttribute(attr.name)
      }
    })
  })

  return temp.innerHTML
}

const sanitizedContent = computed(() => sanitizeHtml(page.value?.content))

const formatDate = (date) => dayjs(date).format('MMMM D, YYYY')

const fetchPage = async () => {
  loading.value = true
  try {
    const response = await api.get(`/pages/${route.params.slug}`)
    page.value = response.data.data

    // Optionally fetch related pages
    if (response.data.related) {
      relatedPages.value = response.data.related
    }
  } catch (error) {
    page.value = null
  } finally {
    loading.value = false
  }
}

watch(() => route.params.slug, () => {
  if (route.params.slug) {
    fetchPage()
  }
})

onMounted(() => {
  fetchPage()
})
</script>

<style>
/* Prose styles for rendered HTML content */
.prose h2 {
  @apply text-xl font-bold text-gray-900 mt-8 mb-4;
}

.prose h3 {
  @apply text-lg font-semibold text-gray-900 mt-6 mb-3;
}

.prose p {
  @apply text-gray-600 mb-4 leading-relaxed;
}

.prose ul {
  @apply list-disc list-inside mb-4 text-gray-600;
}

.prose ol {
  @apply list-decimal list-inside mb-4 text-gray-600;
}

.prose li {
  @apply mb-2;
}

.prose a {
  @apply text-primary-600 hover:underline;
}

.prose strong {
  @apply font-semibold text-gray-900;
}

.prose blockquote {
  @apply border-l-4 border-gray-200 pl-4 italic text-gray-500 my-4;
}

.prose code {
  @apply bg-gray-100 px-1 py-0.5 rounded text-sm;
}

.prose pre {
  @apply bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto mb-4;
}

.prose table {
  @apply w-full border-collapse mb-4;
}

.prose th,
.prose td {
  @apply border border-gray-200 px-4 py-2 text-left;
}

.prose th {
  @apply bg-gray-50 font-semibold;
}
</style>
