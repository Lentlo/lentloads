<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="update-modal-overlay" @click.self="handleBackdropClick">
        <div class="update-modal">
          <!-- Icon -->
          <div class="update-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15M9 12l3 3m0 0 3-3m-3 3V2.25" />
            </svg>
          </div>

          <!-- Title -->
          <h2 class="update-title">Update Available</h2>

          <!-- Version info -->
          <p class="update-version">
            Version {{ latestVersion }} is now available
          </p>

          <!-- Current version -->
          <p class="current-version">
            You have version {{ currentVersion }}
          </p>

          <!-- Release notes -->
          <div v-if="releaseNotes" class="release-notes">
            <h3>What's New:</h3>
            <p>{{ releaseNotes }}</p>
          </div>

          <!-- Buttons -->
          <div class="update-buttons">
            <button
              class="btn-update"
              @click="$emit('update')"
            >
              Update Now
            </button>
            <button
              v-if="!forceUpdate"
              class="btn-later"
              @click="$emit('dismiss')"
            >
              Later
            </button>
          </div>

          <!-- Force update notice -->
          <p v-if="forceUpdate" class="force-notice">
            This update is required to continue using the app.
          </p>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  currentVersion: {
    type: String,
    required: true,
  },
  latestVersion: {
    type: String,
    required: true,
  },
  releaseNotes: {
    type: String,
    default: '',
  },
  forceUpdate: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update', 'dismiss'])

const handleBackdropClick = () => {
  if (!props.forceUpdate) {
    emit('dismiss')
  }
}
</script>

<style scoped>
.update-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 99999;
  padding: 20px;
  backdrop-filter: blur(4px);
}

.update-modal {
  background: white;
  border-radius: 20px;
  padding: 32px 24px;
  max-width: 340px;
  width: 100%;
  text-align: center;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.update-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.update-icon svg {
  width: 32px;
  height: 32px;
  color: white;
}

.update-title {
  font-size: 22px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 8px;
}

.update-version {
  font-size: 16px;
  color: #334155;
  margin: 0 0 4px;
  font-weight: 500;
}

.current-version {
  font-size: 13px;
  color: #94a3b8;
  margin: 0 0 16px;
}

.release-notes {
  background: #f8fafc;
  border-radius: 12px;
  padding: 12px 16px;
  margin-bottom: 20px;
  text-align: left;
}

.release-notes h3 {
  font-size: 13px;
  font-weight: 600;
  color: #64748b;
  margin: 0 0 6px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.release-notes p {
  font-size: 14px;
  color: #334155;
  margin: 0;
  line-height: 1.5;
}

.update-buttons {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.btn-update {
  width: 100%;
  padding: 14px 24px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-update:active {
  transform: scale(0.98);
}

.btn-later {
  width: 100%;
  padding: 12px 24px;
  background: transparent;
  color: #64748b;
  border: none;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 500;
  cursor: pointer;
}

.btn-later:active {
  background: #f1f5f9;
}

.force-notice {
  font-size: 12px;
  color: #ef4444;
  margin: 12px 0 0;
  font-weight: 500;
}

/* Transition */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-active .update-modal,
.modal-leave-active .update-modal {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .update-modal,
.modal-leave-to .update-modal {
  transform: scale(0.9);
  opacity: 0;
}
</style>
