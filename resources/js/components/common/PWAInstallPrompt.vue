<template>
  <div class="pwa-install-prompt">
    <div class="flex items-start gap-4">
      <div class="w-12 h-12 bg-primary-600 rounded-lg flex items-center justify-center flex-shrink-0">
        <span class="text-white font-bold text-xl">L</span>
      </div>
      <div class="flex-1">
        <h4 class="font-semibold text-gray-900">Install Lentloads</h4>
        <p class="text-sm text-gray-500 mt-1">Add to home screen for the best experience</p>
      </div>
      <button @click="$emit('dismiss')" class="text-gray-400 hover:text-gray-600">
        <XMarkIcon class="w-5 h-5" />
      </button>
    </div>
    <div class="flex gap-3 mt-4">
      <button @click="$emit('dismiss')" class="btn-secondary flex-1">
        Not Now
      </button>
      <button @click="install" class="btn-primary flex-1">
        Install
      </button>
    </div>
  </div>
</template>

<script setup>
import { XMarkIcon } from '@heroicons/vue/24/outline'

const emit = defineEmits(['dismiss'])

const install = async () => {
  if (window.deferredPrompt) {
    window.deferredPrompt.prompt()
    const { outcome } = await window.deferredPrompt.userChoice
    if (outcome === 'accepted') {
      emit('dismiss')
    }
    window.deferredPrompt = null
  }
}
</script>
