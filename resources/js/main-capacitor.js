/**
 * Capacitor Mobile App Entry Point
 * This is a separate entry point optimized for native mobile apps.
 * It uses hash-based routing, full API URLs, and mobile-optimized code.
 */

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import MobileApp from './MobileApp.vue'
import './assets/css/app.css'
import './assets/css/mobile-fixes.css'

// Vue3 Toastify
import Vue3Toastify from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

// Mobile-specific router (hash-based)
import router from './router/mobile'

// Capacitor plugins
import { SplashScreen } from '@capacitor/splash-screen'
import { StatusBar, Style } from '@capacitor/status-bar'

// Initialize status bar
const initStatusBar = async () => {
  try {
    await StatusBar.setStyle({ style: Style.Light })
    await StatusBar.setBackgroundColor({ color: '#667eea' })
  } catch (e) {
    // Status bar plugin might not be available
  }
}

// Hide splash screen
const hideSplash = async () => {
  try {
    await SplashScreen.hide({ fadeOutDuration: 300 })
  } catch (e) {
    // Splash screen plugin might not be available
  }
}

// Create and mount the app
const initApp = async () => {
  try {
    // Initialize status bar first
    await initStatusBar()

    // Create Vue app
    const app = createApp(MobileApp)

    // Plugins
    app.use(createPinia())
    app.use(router)
    app.use(Vue3Toastify, {
      autoClose: 3000,
      position: 'top-center', // Better for mobile
      hideProgressBar: true,
    })

    // Global error handler
    app.config.errorHandler = (err, instance, info) => {
      console.error('Vue Error:', err, info)
    }

    // Mount the app
    app.mount('#app')

    // Wait for router to be ready, then hide splash
    await router.isReady()

    // Small delay to ensure content is rendered
    setTimeout(hideSplash, 300)

  } catch (error) {
    console.error('App initialization failed:', error)

    // Show error on screen
    document.getElementById('app').innerHTML = `
      <div style="padding: 20px; text-align: center; font-family: sans-serif;">
        <h2 style="color: #dc2626;">App Error</h2>
        <p style="color: #666;">${error.message}</p>
        <button onclick="location.reload()" style="margin-top: 20px; padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 8px;">
          Retry
        </button>
      </div>
    `

    // Still hide splash on error
    hideSplash()
  }
}

// Start the app
initApp()
