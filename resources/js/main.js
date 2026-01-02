import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import './assets/css/app.css'

// Vue3 Toastify
import Vue3Toastify from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

// Create app
const app = createApp(App)

// Plugins
app.use(createPinia())
app.use(router)
app.use(Vue3Toastify, {
  autoClose: 3000,
  position: 'top-right',
})

// Global error handler
app.config.errorHandler = (err, instance, info) => {
  console.error('Global error:', err)
}

// Mount
app.mount('#app')

// Track app health for recovery from tab suspension
let lastActiveTime = Date.now()
let appHealthy = true

// Update activity timestamp on any interaction
const updateActivity = () => {
  lastActiveTime = Date.now()
  appHealthy = true
}
document.addEventListener('click', updateActivity, { passive: true })
document.addEventListener('touchstart', updateActivity, { passive: true })

// Recovery mechanism for suspended tabs
// When browser suspends a tab, Vue's JavaScript state can be lost
// This detects when the page becomes visible and checks if app is still working
document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'visible') {
    const timeSuspended = Date.now() - lastActiveTime

    // If tab was inactive for more than 5 minutes, check app health
    if (timeSuspended > 5 * 60 * 1000) {
      // Test if Vue Router is still functional
      setTimeout(() => {
        try {
          // Check if router and app are still mounted
          const appEl = document.getElementById('app')
          const hasVueApp = appEl && appEl.__vue_app__

          if (!hasVueApp) {
            // App state is broken, reload
            console.log('App state lost after suspension, reloading...')
            window.location.reload()
          }
        } catch (e) {
          // Error checking state, reload to be safe
          window.location.reload()
        }
      }, 100)
    }

    updateActivity()
  }
})

// Register service worker with update detection
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/build/sw.js')
      .then((registration) => {
        // Check for updates periodically
        setInterval(() => {
          registration.update()
        }, 60 * 60 * 1000) // Check every hour

        // Handle service worker updates
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing
          if (newWorker) {
            newWorker.addEventListener('statechange', () => {
              if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                // New version available, reload on next navigation
                console.log('New version available')
              }
            })
          }
        })
      })
      .catch(() => {
        // Service worker registration failed - app will work without offline support
      })
  })

  // Reload when new service worker takes control
  let refreshing = false
  navigator.serviceWorker.addEventListener('controllerchange', () => {
    if (!refreshing) {
      refreshing = true
      window.location.reload()
    }
  })
}
