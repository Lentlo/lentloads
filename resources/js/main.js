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
let lastVisibleTime = Date.now()

// Update activity timestamp on any interaction
const updateActivity = () => {
  lastActiveTime = Date.now()
}
document.addEventListener('click', updateActivity, { passive: true })
document.addEventListener('touchstart', updateActivity, { passive: true })

// Recovery mechanism for suspended tabs
// When browser suspends a tab, Vue's JavaScript state can be lost
// This detects when the page becomes visible and checks if app is still working
document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'visible') {
    const timeSuspended = Date.now() - lastVisibleTime

    // If tab was hidden for more than 2 minutes, check app health
    if (timeSuspended > 2 * 60 * 1000) {
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
            return
          }

          // Check if router is functional by testing if it has current route
          const routerState = router.currentRoute?.value
          if (!routerState || !routerState.path) {
            console.log('Router state lost, reloading...')
            window.location.reload()
            return
          }

          // Force router to refresh its state
          router.replace(router.currentRoute.value.fullPath).catch(() => {
            // If router replace fails, reload the page
            window.location.reload()
          })
        } catch (e) {
          // Error checking state, reload to be safe
          console.log('App health check failed, reloading...')
          window.location.reload()
        }
      }, 100)
    }

    lastVisibleTime = Date.now()
    updateActivity()
  } else {
    // Tab is being hidden, record time
    lastVisibleTime = Date.now()
  }
})

// Periodic health check - detect frozen app
// If user clicks but nothing navigates for 3 seconds, reload
let navigationAttempts = 0
let lastNavigationTime = 0

// Listen for clicks on links to detect if navigation is broken
document.addEventListener('click', (e) => {
  const link = e.target.closest('a[href]')
  if (link && link.href && link.href.startsWith(window.location.origin)) {
    navigationAttempts++
    lastNavigationTime = Date.now()

    // Check after a short delay if URL changed
    setTimeout(() => {
      // If multiple clicks happened without navigation, app may be frozen
      if (navigationAttempts > 3 && (Date.now() - lastNavigationTime) < 5000) {
        // Check if URL actually changed or if we're stuck
        const currentPath = window.location.pathname + window.location.search
        const linkPath = new URL(link.href).pathname + new URL(link.href).search

        // If paths are different but we didn't navigate, app is frozen
        if (currentPath !== linkPath) {
          console.log('Navigation appears frozen, reloading...')
          window.location.reload()
        }
      }
    }, 1000)
  }
}, { passive: true })

// Reset navigation counter on successful route change
router.afterEach(() => {
  navigationAttempts = 0
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
