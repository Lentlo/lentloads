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
let lastVisibleTime = Date.now()
let lastRouteChange = Date.now()
let expectedRoute = window.location.pathname

// Recovery mechanism for suspended tabs
// When browser suspends a tab, Vue's JavaScript state can be lost
document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'visible') {
    const timeSuspended = Date.now() - lastVisibleTime

    // If tab was hidden for more than 1 minute, check app health
    if (timeSuspended > 60 * 1000) {
      setTimeout(() => {
        try {
          // Check if router and app are still mounted
          const appEl = document.getElementById('app')
          const hasVueApp = appEl && appEl.__vue_app__

          if (!hasVueApp) {
            console.log('App state lost after suspension, reloading...')
            window.location.reload()
            return
          }

          // Check if router is functional
          const routerState = router.currentRoute?.value
          if (!routerState || !routerState.path) {
            console.log('Router state lost, reloading...')
            window.location.reload()
            return
          }

          // Force router to re-sync with current URL
          const currentPath = window.location.pathname + window.location.search + window.location.hash
          if (routerState.fullPath !== currentPath) {
            router.replace(currentPath).catch(() => window.location.reload())
          }
        } catch (e) {
          console.log('App health check failed, reloading...')
          window.location.reload()
        }
      }, 100)
    }

    // Also check for service worker updates when tab becomes visible
    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
      navigator.serviceWorker.controller.postMessage({ type: 'CHECK_UPDATE' })
    }

    lastVisibleTime = Date.now()
  } else {
    lastVisibleTime = Date.now()
  }
})

// Detect frozen navigation - if clicks don't result in route changes
let clicksWithoutNavigation = 0
let lastClickTime = 0
let lastClickedPath = null

// Track route changes
router.afterEach((to) => {
  lastRouteChange = Date.now()
  expectedRoute = to.fullPath
  clicksWithoutNavigation = 0 // Reset on successful navigation
})

// Listen for ALL clicks on nav elements (router-link, a tags, buttons in nav)
document.addEventListener('click', (e) => {
  // Check if click is on a navigation element
  const navElement = e.target.closest('a, [role="link"], .nav-item, .router-link')
  if (!navElement) return

  // Get the intended destination
  let targetPath = null
  if (navElement.href) {
    try {
      const url = new URL(navElement.href, window.location.origin)
      if (url.origin === window.location.origin) {
        targetPath = url.pathname + url.search
      }
    } catch (err) {}
  } else if (navElement.getAttribute('to')) {
    targetPath = navElement.getAttribute('to')
  }

  if (!targetPath) return

  const currentPath = window.location.pathname + window.location.search
  if (targetPath === currentPath) return // Same page click, ignore

  lastClickedPath = targetPath
  lastClickTime = Date.now()
  clicksWithoutNavigation++

  // Check after delay if navigation happened
  setTimeout(() => {
    const timeSinceClick = Date.now() - lastClickTime
    const currentFullPath = window.location.pathname + window.location.search

    // If we clicked a link but URL didn't change AND route didn't change
    if (clicksWithoutNavigation >= 2 &&
        timeSinceClick < 3000 &&
        currentFullPath !== lastClickedPath &&
        router.currentRoute.value.fullPath === expectedRoute) {
      console.log('Navigation frozen detected, reloading...')
      window.location.href = lastClickedPath // Force hard navigation
    }
  }, 500)
}, { passive: true })

// Fallback: If no route changes happen for 10 seconds after a click, force reload
let navigationWatchdog = null
document.addEventListener('click', (e) => {
  const isNavClick = e.target.closest('a, .nav-item, [role="link"]')
  if (!isNavClick) return

  if (navigationWatchdog) clearTimeout(navigationWatchdog)

  navigationWatchdog = setTimeout(() => {
    const timeSinceRouteChange = Date.now() - lastRouteChange
    // If it's been more than 10 seconds since last route change and we have pending clicks
    if (timeSinceRouteChange > 10000 && clicksWithoutNavigation > 0) {
      console.log('Navigation watchdog triggered, reloading...')
      window.location.reload()
    }
  }, 10000)
}, { passive: true })

// Register service worker with aggressive update detection
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/build/sw.js')
      .then((registration) => {
        // Check for updates frequently (every 5 minutes)
        setInterval(() => {
          registration.update()
        }, 5 * 60 * 1000)

        // Also check on page focus
        document.addEventListener('visibilitychange', () => {
          if (document.visibilityState === 'visible') {
            registration.update()
          }
        })

        // Handle service worker updates - auto activate new version
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing
          if (newWorker) {
            newWorker.addEventListener('statechange', () => {
              if (newWorker.state === 'installed') {
                if (navigator.serviceWorker.controller) {
                  // New version available - skip waiting and activate immediately
                  newWorker.postMessage({ type: 'SKIP_WAITING' })
                  console.log('New version available, updating...')
                }
              }
            })
          }
        })
      })
      .catch((err) => {
        console.log('Service worker registration failed:', err)
      })
  })

  // Reload when new service worker takes control
  let refreshing = false
  navigator.serviceWorker.addEventListener('controllerchange', () => {
    if (!refreshing) {
      refreshing = true
      // Clear all caches before reload
      caches.keys().then(names => {
        Promise.all(names.map(name => caches.delete(name))).then(() => {
          window.location.reload()
        })
      })
    }
  })

  // Handle messages from service worker
  navigator.serviceWorker.addEventListener('message', (event) => {
    if (event.data?.type === 'CACHE_CLEARED') {
      console.log('Cache cleared by service worker')
    }
  })
}
