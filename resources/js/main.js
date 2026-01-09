import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import './assets/css/app.css'

// Vue3 Toastify
import Vue3Toastify from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

// Capacitor
import { Capacitor } from '@capacitor/core'

// Hide splash screen - called when app is ready
const hideSplashScreen = async () => {
  if (Capacitor.isNativePlatform()) {
    try {
      const { SplashScreen } = await import('@capacitor/splash-screen')
      await SplashScreen.hide({ fadeOutDuration: 500 })
    } catch (e) {
      // Splash screen plugin might not be available
      console.log('SplashScreen hide error:', e)
    }
  }
}

// Configure status bar for native app
const configureStatusBar = async () => {
  if (Capacitor.isNativePlatform()) {
    try {
      const { StatusBar, Style } = await import('@capacitor/status-bar')
      // Set status bar style - LIGHT means white icons (for dark backgrounds)
      await StatusBar.setStyle({ style: Style.Light })
      // Set background color to match header gradient
      await StatusBar.setBackgroundColor({ color: '#6366f1' })
    } catch (e) {
      console.log('StatusBar config error:', e)
    }
  }
}

// Initialize native features
const initNative = async () => {
  await configureStatusBar()
}

// Hide splash after 3 seconds no matter what (failsafe)
setTimeout(hideSplashScreen, 3000)

try {
  // Create app
  const app = createApp(App)

  // Plugins
  app.use(createPinia())
  app.use(router)
  app.use(Vue3Toastify, {
    autoClose: 3000,
    position: 'top-right',
  })

  // Global error handler - shows errors visibly
  app.config.errorHandler = (err, instance, info) => {
    console.error('Vue error:', err, info)
    // Show error on screen for debugging
    const errorDiv = document.createElement('div')
    errorDiv.style.cssText = 'position:fixed;top:0;left:0;right:0;padding:20px;background:red;color:white;z-index:99999;font-size:12px;'
    errorDiv.textContent = `Vue Error: ${err.message}`
    document.body.appendChild(errorDiv)
  }

  // Mount the app
  app.mount('#app')

  // Initialize native features and hide splash screen after router is ready
  router.isReady().then(async () => {
    await initNative()
    // Small delay to ensure UI is rendered
    setTimeout(hideSplashScreen, 500)
  })

} catch (error) {
  // If app creation fails, show error and hide splash
  console.error('App initialization error:', error)
  hideSplashScreen()

  // Show error on screen
  const appDiv = document.getElementById('app')
  if (appDiv) {
    appDiv.innerHTML = `
      <div style="padding:20px;background:#fee2e2;color:#dc2626;margin:20px;border-radius:8px;">
        <h2 style="margin:0 0 10px 0;">App Error</h2>
        <p style="margin:0;">${error.message}</p>
        <pre style="margin-top:10px;font-size:11px;overflow:auto;">${error.stack}</pre>
      </div>
    `
  }
}
