<template>
  <div class="report-modal">
    <div class="report-backdrop" @click="$emit('close')"></div>

    <div class="report-sheet">
      <!-- Header -->
      <div class="report-header">
        <h3 class="text-lg font-semibold text-gray-900">
          Report {{ type === 'listing' ? 'Listing' : 'User' }}
        </h3>
        <button @click="$emit('close')" class="p-2 hover:bg-gray-100 rounded-full -mr-2">
          <XMarkIcon class="w-5 h-5 text-gray-500" />
        </button>
      </div>

      <!-- Content - Scrollable -->
      <div class="report-content">
        <p class="text-sm text-gray-500 mb-4">
          Please select a reason for reporting this {{ type === 'listing' ? 'listing' : 'user' }}.
        </p>

        <!-- Report Reasons -->
        <div class="space-y-2">
          <label
            v-for="reason in reasons"
            :key="reason.value"
            class="flex items-start gap-3 p-3 border rounded-xl cursor-pointer hover:bg-gray-50 transition"
            :class="selectedReason === reason.value ? 'border-primary-500 bg-primary-50' : ''"
          >
            <input
              type="radio"
              :value="reason.value"
              v-model="selectedReason"
              class="mt-0.5 text-primary-600"
            />
            <div class="flex-1 min-w-0">
              <p class="font-medium text-gray-900 text-sm">{{ reason.label }}</p>
              <p class="text-xs text-gray-500">{{ reason.description }}</p>
            </div>
          </label>
        </div>

        <!-- Additional Details -->
        <div class="mt-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Additional details (optional)</label>
          <textarea
            v-model="details"
            rows="2"
            class="input resize-none text-sm"
            placeholder="Any extra information..."
          ></textarea>
        </div>
      </div>

      <!-- Actions - Always visible -->
      <div class="report-actions">
        <button @click="$emit('close')" class="btn-secondary flex-1">
          Cancel
        </button>
        <button
          @click="submitReport"
          :disabled="!selectedReason || submitting"
          class="btn-danger flex-1"
        >
          <span v-if="submitting" class="flex items-center justify-center gap-2">
            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Submitting...
          </span>
          <span v-else>Submit Report</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import { XMarkIcon } from '@heroicons/vue/24/outline'

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
  { value: 'spam', label: 'Spam or scam', description: 'Fake listing or misleading information' },
  { value: 'prohibited', label: 'Prohibited item', description: 'Item not allowed on platform' },
  { value: 'duplicate', label: 'Duplicate listing', description: 'Same item posted multiple times' },
  { value: 'wrong_category', label: 'Wrong category', description: 'Listed in wrong category' },
  { value: 'offensive', label: 'Offensive content', description: 'Inappropriate language or images' },
  { value: 'other', label: 'Other', description: 'Another reason' }
]

const userReasons = [
  { value: 'harassment', label: 'Harassment', description: 'Threatening or abusive behavior' },
  { value: 'scam', label: 'Scammer', description: 'Attempting to defraud users' },
  { value: 'fake_profile', label: 'Fake profile', description: 'Using fake identity' },
  { value: 'spam', label: 'Spam', description: 'Sending unsolicited messages' },
  { value: 'other', label: 'Other', description: 'Another reason' }
]

const reasons = computed(() => props.type === 'listing' ? listingReasons : userReasons)

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

    toast.success('Report submitted successfully')
    emit('submitted')
    emit('close')
  } catch (error) {
    if (error.response?.status === 409) {
      toast.info('You have already reported this')
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
.report-modal {
  position: fixed;
  inset: 0;
  z-index: 60;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

@media (min-width: 640px) {
  .report-modal {
    align-items: center;
  }
}

.report-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
}

.report-sheet {
  position: relative;
  background: white;
  width: 100%;
  max-width: 28rem;
  border-radius: 1rem 1rem 0 0;
  display: flex;
  flex-direction: column;
  max-height: 70vh;
  max-height: 70dvh;
  /* Position above mobile nav */
  margin-bottom: calc(60px + env(safe-area-inset-bottom, 8px));
  animation: slide-up 0.3s ease-out;
}

@media (min-width: 640px) {
  .report-sheet {
    border-radius: 1rem;
    max-height: 85vh;
    max-height: 85dvh;
    margin-bottom: 0;
  }
}

/* Desktop - no mobile nav */
@media (min-width: 768px) {
  .report-sheet {
    margin-bottom: 0;
  }
}

.report-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  flex-shrink: 0;
}

.report-content {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  -webkit-overflow-scrolling: touch;
  min-height: 0;
}

.report-actions {
  display: flex;
  gap: 0.75rem;
  padding: 1rem;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
  border-radius: 0 0 1rem 1rem;
  flex-shrink: 0;
}

@keyframes slide-up {
  from { transform: translateY(100%); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
</style>
