<template>
  <App />

  <!-- Update Modal -->
  <UpdateModal
    :show="showUpdateModal"
    :current-version="currentVersion"
    :latest-version="latestVersion"
    :release-notes="releaseNotes"
    :force-update="forceUpdate"
    @update="handleUpdate"
    @dismiss="handleDismiss"
  />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import App from './App.vue'
import UpdateModal from './components/UpdateModal.vue'
import { useAppUpdate, APP_VERSION } from './composables/useAppUpdate'

const {
  checkForUpdate,
  openUpdateUrl,
  dismissUpdate,
  shouldShowPrompt,
  latestVersion: updateLatestVersion,
  releaseNotes: updateReleaseNotes,
} = useAppUpdate()

const showUpdateModal = ref(false)
const currentVersion = ref(APP_VERSION)
const latestVersion = ref('')
const releaseNotes = ref('')
const forceUpdate = ref(false)

onMounted(async () => {
  // Check for updates after a short delay (let app load first)
  setTimeout(async () => {
    try {
      const result = await checkForUpdate()

      if (result.available && shouldShowPrompt()) {
        latestVersion.value = result.version
        releaseNotes.value = result.releaseNotes || ''
        forceUpdate.value = result.forceUpdate || false
        showUpdateModal.value = true
      }
    } catch (e) {
      console.warn('Update check failed:', e)
    }
  }, 2000)
})

const handleUpdate = () => {
  openUpdateUrl()
}

const handleDismiss = () => {
  dismissUpdate()
  showUpdateModal.value = false
}
</script>
