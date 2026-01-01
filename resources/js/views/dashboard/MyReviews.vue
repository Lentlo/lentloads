<template>
  <div class="min-h-screen bg-gray-50 py-6">
    <div class="container-app max-w-2xl">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">My Reviews</h1>

      <!-- Tabs -->
      <div class="flex gap-4 mb-6">
        <button
          @click="activeTab = 'received'"
          class="px-4 py-2 rounded-lg font-medium"
          :class="activeTab === 'received' ? 'bg-primary-600 text-white' : 'bg-white text-gray-600'"
        >
          Received ({{ stats.received }})
        </button>
        <button
          @click="activeTab = 'given'"
          class="px-4 py-2 rounded-lg font-medium"
          :class="activeTab === 'given' ? 'bg-primary-600 text-white' : 'bg-white text-gray-600'"
        >
          Given ({{ stats.given }})
        </button>
      </div>

      <!-- Stats Card (for received) -->
      <div v-if="activeTab === 'received'" class="card p-6 mb-6">
        <div class="flex items-center gap-6">
          <div class="text-center">
            <p class="text-4xl font-bold text-primary-600">{{ averageRating }}</p>
            <div class="flex justify-center mt-1">
              <StarIcon
                v-for="i in 5"
                :key="i"
                class="w-5 h-5"
                :class="i <= Math.round(averageRating) ? 'text-yellow-400 fill-current' : 'text-gray-300'"
              />
            </div>
            <p class="text-sm text-gray-500 mt-1">{{ stats.received }} reviews</p>
          </div>
          <div class="flex-1">
            <div v-for="i in 5" :key="i" class="flex items-center gap-2 mb-1">
              <span class="text-sm w-8">{{ 6 - i }}</span>
              <div class="flex-1 bg-gray-200 rounded-full h-2">
                <div
                  class="bg-yellow-400 h-2 rounded-full"
                  :style="{ width: getRatingPercentage(6 - i) + '%' }"
                ></div>
              </div>
              <span class="text-sm text-gray-500 w-8">{{ ratingBreakdown[6 - i] || 0 }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-4">
        <div v-for="i in 3" :key="i" class="card p-4">
          <div class="flex gap-4">
            <div class="skeleton w-10 h-10 rounded-full"></div>
            <div class="flex-1">
              <div class="skeleton h-4 w-1/4 mb-2"></div>
              <div class="skeleton h-3 w-full"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Reviews List -->
      <div v-else-if="reviews.length" class="space-y-4">
        <div
          v-for="review in reviews"
          :key="review.id"
          class="card p-4"
        >
          <div class="flex items-start gap-4">
            <img
              :src="activeTab === 'received' ? review.reviewer?.avatar_url : review.reviewed?.avatar_url"
              :alt="activeTab === 'received' ? review.reviewer?.name : review.reviewed?.name"
              class="w-10 h-10 rounded-full object-cover"
            />
            <div class="flex-1">
              <div class="flex items-center justify-between">
                <div>
                  <router-link
                    :to="`/user/${activeTab === 'received' ? review.reviewer?.id : review.reviewed?.id}`"
                    class="font-semibold text-gray-900 hover:text-primary-600"
                  >
                    {{ activeTab === 'received' ? review.reviewer?.name : review.reviewed?.name }}
                  </router-link>
                  <div class="flex items-center gap-1 mt-1">
                    <StarIcon
                      v-for="i in 5"
                      :key="i"
                      class="w-4 h-4"
                      :class="i <= review.rating ? 'text-yellow-400 fill-current' : 'text-gray-300'"
                    />
                  </div>
                </div>
                <span class="text-sm text-gray-500">{{ formatDate(review.created_at) }}</span>
              </div>

              <p v-if="review.comment" class="text-gray-600 mt-2">{{ review.comment }}</p>

              <p v-if="review.listing" class="text-sm text-gray-500 mt-2">
                Re: {{ review.listing.title }}
              </p>

              <!-- Seller Response -->
              <div v-if="review.seller_response" class="mt-4 pl-4 border-l-2 border-gray-200">
                <p class="text-sm font-medium text-gray-900">Your response:</p>
                <p class="text-sm text-gray-600">{{ review.seller_response }}</p>
              </div>

              <!-- Add Response (for received reviews without response) -->
              <div v-else-if="activeTab === 'received' && !showResponseFor.includes(review.id)" class="mt-4">
                <button
                  @click="showResponseFor.push(review.id)"
                  class="text-sm text-primary-600 hover:underline"
                >
                  Add response
                </button>
              </div>

              <!-- Response Form -->
              <div v-if="showResponseFor.includes(review.id)" class="mt-4">
                <textarea
                  v-model="responseText[review.id]"
                  rows="2"
                  class="input text-sm"
                  placeholder="Write your response..."
                ></textarea>
                <div class="flex gap-2 mt-2">
                  <button
                    @click="showResponseFor = showResponseFor.filter(id => id !== review.id)"
                    class="btn-secondary btn-sm"
                  >
                    Cancel
                  </button>
                  <button
                    @click="submitResponse(review.id)"
                    class="btn-primary btn-sm"
                  >
                    Submit
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="card p-12 text-center">
        <StarIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No reviews yet</h3>
        <p class="text-gray-500">
          {{ activeTab === 'received' ? 'Complete transactions to receive reviews' : 'Rate your transactions' }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import dayjs from 'dayjs'
import { StarIcon } from '@heroicons/vue/24/outline'

const loading = ref(true)
const activeTab = ref('received')
const reviews = ref([])
const stats = reactive({ received: 0, given: 0 })
const averageRating = ref(0)
const ratingBreakdown = ref({})
const showResponseFor = ref([])
const responseText = reactive({})

const formatDate = (date) => dayjs(date).format('MMM D, YYYY')

const getRatingPercentage = (rating) => {
  const total = stats.received
  if (!total) return 0
  return ((ratingBreakdown.value[rating] || 0) / total) * 100
}

const fetchReviews = async () => {
  loading.value = true
  try {
    const response = await api.get('/reviews/my', {
      params: { type: activeTab.value }
    })
    reviews.value = response.data.data

    if (activeTab.value === 'received' && response.data.stats) {
      averageRating.value = response.data.stats.average || 0
      ratingBreakdown.value = response.data.stats.breakdown || {}
      stats.received = response.data.stats.total || 0
    }
  } finally {
    loading.value = false
  }
}

const submitResponse = async (reviewId) => {
  const text = responseText[reviewId]
  if (!text?.trim()) return

  try {
    await api.post(`/reviews/${reviewId}/respond`, { response: text })
    const review = reviews.value.find(r => r.id === reviewId)
    if (review) {
      review.seller_response = text
    }
    showResponseFor.value = showResponseFor.value.filter(id => id !== reviewId)
    delete responseText[reviewId]
    toast.success('Response added')
  } catch (error) {
    toast.error('Failed to add response')
  }
}

watch(activeTab, () => {
  fetchReviews()
})

onMounted(() => {
  fetchReviews()
})
</script>
