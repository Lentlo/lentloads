<template>
  <!-- Mobile: full screen from bottom, Desktop: centered modal -->
  <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
    <div class="absolute inset-0 bg-black/50" @click="$emit('close')"></div>
    <div class="relative bg-white w-full sm:max-w-md sm:rounded-xl rounded-t-xl animate-slide-up max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">Contact Seller</h3>
        <button @click="$emit('close')" class="p-1 hover:bg-gray-100 rounded">
          <XMarkIcon class="w-5 h-5 text-gray-500" />
        </button>
      </div>

      <!-- Listing Preview -->
      <div v-if="listing" class="p-4 bg-gray-50 border-b">
        <div class="flex gap-3">
          <img
            :src="listing.primary_image_url"
            :alt="listing.title"
            class="w-16 h-16 rounded-lg object-cover"
          />
          <div class="flex-1 min-w-0">
            <h4 class="font-medium text-gray-900 truncate">{{ listing.title }}</h4>
            <p class="text-lg font-bold text-primary-600">{{ listing.formatted_price }}</p>
          </div>
        </div>
      </div>

      <!-- Quick Messages -->
      <div class="p-4 border-b">
        <p class="text-sm text-gray-500 mb-3">Quick messages:</p>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="quick in quickMessages"
            :key="quick"
            @click="message = quick"
            class="px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 rounded-full transition"
            :class="message === quick ? 'bg-primary-100 text-primary-700' : ''"
          >
            {{ quick }}
          </button>
        </div>
      </div>

      <!-- Message Input -->
      <div class="p-4">
        <textarea
          v-model="message"
          rows="3"
          class="input resize-none"
          placeholder="Write your message..."
        ></textarea>

        <!-- Make Offer Option -->
        <div class="mt-3">
          <label class="flex items-center gap-2 text-sm">
            <input
              type="checkbox"
              v-model="includeOffer"
              class="rounded text-primary-600"
            />
            Include an offer
          </label>

          <div v-if="includeOffer" class="mt-2 flex items-center gap-2">
            <span class="text-gray-500">₹</span>
            <input
              v-model.number="offerAmount"
              type="number"
              class="input flex-1"
              placeholder="Your offer amount"
              :max="listing?.price"
            />
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex gap-3 p-4 border-t bg-gray-50 sm:rounded-b-xl safe-area-bottom">
        <button @click="$emit('close')" class="btn-secondary flex-1">
          Cancel
        </button>
        <button
          @click="sendMessage"
          :disabled="!message.trim() || sending"
          class="btn-primary flex-1"
        >
          <ChatBubbleLeftIcon v-if="!sending" class="w-5 h-5 mr-2" />
          <LoadingSpinner v-else class="w-5 h-5 mr-2" />
          {{ sending ? 'Sending...' : 'Send Message' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import { useRouter } from 'vue-router'
import { XMarkIcon, ChatBubbleLeftIcon } from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const props = defineProps({
  listing: {
    type: Object,
    required: true
  }
})

// Get seller ID from listing
const sellerId = computed(() => props.listing?.user?.id || props.listing?.user_id)

const emit = defineEmits(['close', 'sent'])

const router = useRouter()

const message = ref('')
const includeOffer = ref(false)
const offerAmount = ref(null)
const sending = ref(false)

const quickMessages = [
  'Is this still available?',
  'Can you share more photos?',
  'What is the condition?',
  'Is the price negotiable?',
  'Can I see it today?'
]

const sendMessage = async () => {
  if (!message.value.trim()) return

  sending.value = true
  try {
    const payload = {
      listing_id: props.listing.id,
      receiver_id: sellerId.value,
      message: message.value.trim(),
    }

    if (includeOffer.value && offerAmount.value) {
      payload.offer_amount = offerAmount.value
    }

    const response = await api.post('/conversations', payload)

    toast.success('Message sent!')
    emit('sent', response.data.data)
    emit('close')

    // Navigate to the conversation using uuid
    const conversation = response.data.data.conversation
    router.push(`/messages/${conversation.uuid}`)
  } catch (error) {
    if (error.response?.status === 409) {
      // Conversation already exists
      toast.info('You already have a conversation about this listing')
      const existingUuid = error.response.data.data?.uuid || error.response.data.uuid
      router.push(`/messages/${existingUuid}`)
      emit('close')
    } else {
      toast.error('Failed to send message')
    }
  } finally {
    sending.value = false
  }
}
</script>

<style scoped>
@keyframes slide-up {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.animate-slide-up {
  animation: slide-up 0.3s ease-out;
}

.safe-area-bottom {
  padding-bottom: max(env(safe-area-inset-bottom, 0), 16px);
}

@media (max-width: 640px) {
  .safe-area-bottom {
    padding-bottom: max(env(safe-area-inset-bottom, 0), 80px);
  }
}
</style>
