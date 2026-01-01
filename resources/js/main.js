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

// Register service worker
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then((registration) => {
        console.log('SW registered:', registration)
      })
      .catch((error) => {
        console.log('SW registration failed:', error)
      })
  })
}
