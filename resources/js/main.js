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

// Hide splash screen as early as possible for native apps
// This ensures the app is visible even if there are errors later
const hideSplashScreen = async () => {
  if (Capacitor.isNativePlatform()) {
    try {
      const { SplashScreen } = await import('@capacitor/splash-screen')
      await SplashScreen.hide({ fadeOutDuration: 300 })
    } catch (e) {
      // Splash screen plugin might not be available
    }
  }
}

// Hide splash after 2 seconds no matter what (failsafe)
setTimeout(hideSplashScreen, 2000)

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

  // Hide splash screen after router is ready
  router.isReady().then(() => {
    hideSplashScreen()
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
