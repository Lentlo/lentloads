<template>
  <div id="app" class="min-h-screen flex flex-col">
    <!-- Header - hide on conversation page for mobile chat experience -->
    <AppHeader v-if="!isAdminRoute && !(isConversationPage && isMobile)" />
    <AdminHeader v-else-if="isAuthenticated && isAdmin" />

    <!-- Main Content -->
    <main class="flex-1">
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>

    <!-- Footer - hide on conversation page -->
    <AppFooter v-if="!isAdminRoute && !isConversationPage" />

    <!-- Mobile Bottom Navigation - hide on conversation page for chat experience -->
    <MobileNav v-if="!isAdminRoute && isMobile && !isConversationPage" />

    <!-- PWA Install Prompt -->
    <PWAInstallPrompt v-if="showInstallPrompt" @dismiss="showInstallPrompt = false" />

    <!-- Loading Overlay -->
    <LoadingOverlay v-if="isLoading" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import AdminHeader from '@/components/layout/AdminHeader.vue'
import MobileNav from '@/components/layout/MobileNav.vue'
import PWAInstallPrompt from '@/components/common/PWAInstallPrompt.vue'
import LoadingOverlay from '@/components/common/LoadingOverlay.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const appStore = useAppStore()

const showInstallPrompt = ref(false)
const isMobile = ref(false)

const isAuthenticated = computed(() => authStore.isAuthenticated)
const isAdmin = computed(() => authStore.isAdmin)
const isAdminRoute = computed(() => route.path.startsWith('/admin'))
const isConversationPage = computed(() => route.name === 'conversation')
const isLoading = computed(() => appStore.isLoading)

// Check mobile
const checkMobile = () => {
  isMobile.value = window.innerWidth < 768
}

// PWA install prompt
let deferredPrompt = null
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault()
  deferredPrompt = e
  showInstallPrompt.value = true
})

onMounted(async () => {
  checkMobile()
  window.addEventListener('resize', checkMobile)

  // Initialize auth
  if (authStore.token) {
    await authStore.fetchUser()
  }

  // Fetch initial data
  await appStore.initialize()
})
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
