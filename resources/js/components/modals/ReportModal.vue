<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" @click="$emit('close')"></div>
    <div class="relative bg-white rounded-xl w-full max-w-md animate-slide-up">
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          Report {{ type === 'listing' ? 'Listing' : 'User' }}
        </h3>
        <button @click="$emit('close')" class="p-1 hover:bg-gray-100 rounded">
          <XMarkIcon class="w-5 h-5 text-gray-500" />
        </button>
      </div>

      <!-- Content -->
      <div class="p-4">
        <p class="text-sm text-gray-500 mb-4">
          Please select a reason for reporting this {{ type === 'listing' ? 'listing' : 'user' }}.
          Our team will review your report.
        </p>

        <!-- Report Reasons -->
        <div class="space-y-2">
          <label
            v-for="reason in reasons"
            :key="reason.value"
            class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition"
            :class="selectedReason === reason.value ? 'border-primary-500 bg-primary-50' : ''"
          >
            <input
              type="radio"
              :value="reason.value"
              v-model="selectedReason"
              class="mt-0.5 text-primary-600"
            />
            <div>
              <p class="font-medium text-gray-900">{{ reason.label }}</p>
              <p class="text-sm text-gray-500">{{ reason.description }}</p>
            </div>
          </label>
        </div>

        <!-- Additional Details -->
        <div class="mt-4">
          <label class="label">Additional details (optional)</label>
          <textarea
            v-model="details"
            rows="3"
            class="input resize-none"
            placeholder="Provide any additional information that might help us investigate..."
          ></textarea>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex gap-3 p-4 border-t bg-gray-50 rounded-b-xl">
        <button @click="$emit('close')" class="btn-secondary flex-1">
          Cancel
        </button>
        <button
          @click="submitReport"
          :disabled="!selectedReason || submitting"
          class="btn-danger flex-1"
        >
          <FlagIcon v-if="!submitting" class="w-5 h-5 mr-2" />
          <LoadingSpinner v-else class="w-5 h-5 mr-2" />
          {{ submitting ? 'Submitting...' : 'Submit Report' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import { XMarkIcon, FlagIcon } from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const props = defineProps({
  type: {
    type: String,
    required: true,
    validator: (value) => ['listing', 'user'].includes(value)
  },
  itemId: {
    type: [Number, String],
    required: true
  }
})

const emit = defineEmits(['close', 'submitted'])

const selectedReason = ref('')
const details = ref('')
const submitting = ref(false)

const listingReasons = [
  {
    value: 'spam',
    label: 'Spam or scam',
    description: 'Fake listing, misleading information, or phishing attempt'
  },
  {
    value: 'prohibited',
    label: 'Prohibited item',
    description: 'Item that is not allowed to be sold on this platform'
  },
  {
    value: 'duplicate',
    label: 'Duplicate listing',
    description: 'Same item posted multiple times'
  },
  {
    value: 'wrong_category',
    label: 'Wrong category',
    description: 'Item is listed in the wrong category'
  },
  {
    value: 'offensive',
    label: 'Offensive content',
    description: 'Contains inappropriate language or images'
  },
  {
    value: 'other',
    label: 'Other',
    description: 'Another reason not listed above'
  }
]

const userReasons = [
  {
    value: 'harassment',
    label: 'Harassment',
    description: 'Threatening or abusive behavior'
  },
  {
    value: 'scam',
    label: 'Scammer',
    description: 'Attempting to defraud buyers or sellers'
  },
  {
    value: 'fake_profile',
    label: 'Fake profile',
    description: 'Using fake identity or stolen photos'
  },
  {
    value: 'spam',
    label: 'Spam',
    description: 'Sending unsolicited messages or ads'
  },
  {
    value: 'other',
    label: 'Other',
    description: 'Another reason not listed above'
  }
]

const reasons = computed(() => {
  return props.type === 'listing' ? listingReasons : userReasons
})

const submitReport = async () => {
  if (!selectedReason.value) return

  submitting.value = true
  try {
    await api.post('/reports', {
      reportable_type: props.type,
      reportable_id: props.itemId,
      reason: selectedReason.value,
      description: details.value || null
    })

    toast.success('Report submitted. Thank you for helping keep our community safe.')
    emit('submitted')
    emit('close')
  } catch (error) {
    if (error.response?.status === 409) {
      toast.info('You have already reported this item')
      emit('close')
    } else {
      toast.error('Failed to submit report')
    }
  } finally {
    submitting.value = false
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
</style>
