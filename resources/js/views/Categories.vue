<template>
  <div class="categories-page min-h-screen bg-gray-50">
    <div class="container-app py-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">All Categories</h1>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="category in categories"
          :key="category.id"
          class="card p-6"
        >
          <router-link
            :to="`/category/${category.slug}`"
            class="flex items-center gap-4 mb-4"
          >
            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
              <component :is="getCategoryIcon(category.slug)" class="w-6 h-6 text-primary-600" />
            </div>
            <div>
              <h2 class="font-semibold text-gray-900">{{ category.name }}</h2>
              <p class="text-sm text-gray-500">{{ category.total_active_listings_count || 0 }} ads</p>
            </div>
          </router-link>

          <ul v-if="category.children?.length" class="space-y-2 ml-16">
            <li v-for="child in category.children" :key="child.id">
              <router-link
                :to="`/category/${child.slug}`"
                class="text-gray-600 hover:text-primary-600 text-sm"
              >
                {{ child.name }}
                <span class="text-gray-400">({{ child.total_active_listings_count || 0 }})</span>
              </router-link>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAppStore } from '@/stores/app'
import {
  DevicePhoneMobileIcon,
  TruckIcon,
  HomeModernIcon,
  BriefcaseIcon,
  ComputerDesktopIcon,
  ShoppingBagIcon,
  WrenchIcon,
  SparklesIcon,
} from '@heroicons/vue/24/outline'

const appStore = useAppStore()

const categories = computed(() => appStore.categories)

const getCategoryIcon = (slug) => {
  const icons = {
    'mobiles': DevicePhoneMobileIcon,
    'vehicles': TruckIcon,
    'property': HomeModernIcon,
    'jobs': BriefcaseIcon,
    'electronics': ComputerDesktopIcon,
    'fashion': SparklesIcon,
    'furniture': HomeModernIcon,
    'services': WrenchIcon,
  }
  return icons[slug] || ShoppingBagIcon
}
</script>
