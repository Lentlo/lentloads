<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" @click="$emit('cancel')"></div>
    <div class="relative bg-white rounded-xl p-6 w-full max-w-md animate-scale-in">
      <div class="text-center">
        <div
          v-if="type === 'danger'"
          class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"
        >
          <ExclamationTriangleIcon class="w-6 h-6 text-red-600" />
        </div>
        <div
          v-else-if="type === 'warning'"
          class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4"
        >
          <ExclamationCircleIcon class="w-6 h-6 text-yellow-600" />
        </div>
        <div
          v-else
          class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4"
        >
          <QuestionMarkCircleIcon class="w-6 h-6 text-primary-600" />
        </div>

        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ title }}</h3>
        <p class="text-gray-500">{{ message }}</p>
      </div>

      <div class="flex gap-3 mt-6">
        <button
          @click="$emit('cancel')"
          class="btn-secondary flex-1"
        >
          {{ cancelText }}
        </button>
        <button
          @click="$emit('confirm')"
          :class="confirmClass || defaultConfirmClass"
          class="flex-1"
        >
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  ExclamationTriangleIcon,
  ExclamationCircleIcon,
  QuestionMarkCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed?'
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  confirmClass: {
    type: String,
    default: null
  },
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['info', 'warning', 'danger'].includes(value)
  }
})

defineEmits(['confirm', 'cancel'])

const defaultConfirmClass = computed(() => {
  if (props.type === 'danger') return 'btn-danger'
  if (props.type === 'warning') return 'btn-primary'
  return 'btn-primary'
})
</script>

<style scoped>
@keyframes scale-in {
  from {
    transform: scale(0.95);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

.animate-scale-in {
  animation: scale-in 0.2s ease-out;
}
</style>
