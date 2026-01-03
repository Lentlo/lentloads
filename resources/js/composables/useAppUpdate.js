/**
 * App Update Checker
 * Checks server for new app version and prompts user to update
 */

import { ref } from 'vue'
import { App } from '@capacitor/app'

// Current app version - UPDATE THIS when releasing new APK
export const APP_VERSION = '1.0.0'

// API endpoint to check version
const VERSION_CHECK_URL = 'https://phplaravel-1016958-6108537.cloudwaysapps.com/api/v1/app/version'

export function useAppUpdate() {
  const updateAvailable = ref(false)
  const latestVersion = ref(null)
  const updateUrl = ref(null)
  const isChecking = ref(false)
  const releaseNotes = ref('')

  /**
   * Compare version strings (e.g., "1.0.0" vs "1.1.0")
   */
  const isNewerVersion = (current, latest) => {
    const currentParts = current.split('.').map(Number)
    const latestParts = latest.split('.').map(Number)

    for (let i = 0; i < Math.max(currentParts.length, latestParts.length); i++) {
      const currentPart = currentParts[i] || 0
      const latestPart = latestParts[i] || 0

      if (latestPart > currentPart) return true
      if (latestPart < currentPart) return false
    }

    return false
  }

  /**
   * Check for app updates
   */
  const checkForUpdate = async () => {
    // Only check in Capacitor environment
    if (!window.Capacitor?.isNativePlatform()) {
      return false
    }

    isChecking.value = true

    try {
      const response = await fetch(VERSION_CHECK_URL, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-App-Version': APP_VERSION,
          'X-Platform': 'android',
        },
      })

      if (!response.ok) {
        throw new Error('Version check failed')
      }

      const data = await response.json()

      if (data.success && data.data) {
        const { version, android_url, release_notes, force_update } = data.data

        if (isNewerVersion(APP_VERSION, version)) {
          updateAvailable.value = true
          latestVersion.value = version
          updateUrl.value = android_url
          releaseNotes.value = release_notes || ''

          return {
            available: true,
            version,
            url: android_url,
            releaseNotes: release_notes,
            forceUpdate: force_update || false,
          }
        }
      }

      return { available: false }
    } catch (error) {
      console.warn('Update check failed:', error)
      return { available: false, error: error.message }
    } finally {
      isChecking.value = false
    }
  }

  /**
   * Open app store or direct download URL
   */
  const openUpdateUrl = async () => {
    if (updateUrl.value) {
      // Open in browser
      window.open(updateUrl.value, '_system')
    }
  }

  /**
   * Dismiss update prompt (for non-forced updates)
   */
  const dismissUpdate = () => {
    updateAvailable.value = false
    // Store dismissal time to not annoy user
    localStorage.setItem('updateDismissedAt', Date.now().toString())
    localStorage.setItem('updateDismissedVersion', latestVersion.value)
  }

  /**
   * Check if we should show update prompt
   * (Don't show if user dismissed recently for same version)
   */
  const shouldShowPrompt = () => {
    const dismissedAt = localStorage.getItem('updateDismissedAt')
    const dismissedVersion = localStorage.getItem('updateDismissedVersion')

    if (!dismissedAt || !dismissedVersion) return true

    // If it's a different version, show prompt
    if (dismissedVersion !== latestVersion.value) return true

    // Don't show again for 24 hours for same version
    const hoursSinceDismissed = (Date.now() - parseInt(dismissedAt)) / (1000 * 60 * 60)
    return hoursSinceDismissed > 24
  }

  return {
    APP_VERSION,
    updateAvailable,
    latestVersion,
    updateUrl,
    releaseNotes,
    isChecking,
    checkForUpdate,
    openUpdateUrl,
    dismissUpdate,
    shouldShowPrompt,
  }
}
