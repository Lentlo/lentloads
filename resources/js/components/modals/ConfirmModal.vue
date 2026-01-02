<template>
  <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
    <div class="absolute inset-0 bg-black/50" @click="$emit('cancel')"></div>

    <div class="relative bg-white w-full sm:max-w-sm sm:rounded-xl rounded-t-2xl p-6 animate-slide-up safe-area-bottom">
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
        <p class="text-gray-500 text-sm">{{ message }}</p>
      </div>

      <div class="flex gap-3 mt-6">
        <button @click="$emit('cancel')" class="btn-secondary flex-1">
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
  title: { type: String, default: 'Confirm Action' },
  message: { type: String, default: 'Are you sure you want to proceed?' },
  confirmText: { type: String, default: 'Confirm' },
  cancelText: { type: String, default: 'Cancel' },
  confirmClass: { type: String, default: null },
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['info', 'warning', 'danger'].includes(value)
  }
})

defineEmits(['confirm', 'cancel'])

const defaultConfirmClass = computed(() => {
  if (props.type === 'danger') return 'btn-danger'
  return 'btn-primary'
})
</script>

<style scoped>
@keyframes slide-up {
  from {
    transform: translateY(100%);
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
</style>
