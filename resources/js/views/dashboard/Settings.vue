<template>
  <div class="min-h-screen bg-gray-50 py-6">
    <div class="container-app max-w-2xl">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">Account Settings</h1>

      <!-- Profile Section -->
      <div class="card p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile Information</h2>

        <!-- Avatar -->
        <div class="flex items-center gap-4 mb-6">
          <img
            :src="user?.avatar_url"
            :alt="user?.name"
            class="w-20 h-20 rounded-full object-cover"
          />
          <div>
            <label class="btn-secondary cursor-pointer">
              <CameraIcon class="w-4 h-4 mr-2" />
              Change Photo
              <input
                type="file"
                accept="image/*"
                class="hidden"
                @change="updateAvatar"
              />
            </label>
            <p class="text-sm text-gray-500 mt-1">JPG, PNG. Max 2MB</p>
          </div>
        </div>

        <form @submit.prevent="updateProfile" class="space-y-4">
          <div>
            <label class="label">Full Name</label>
            <input v-model="profile.name" type="text" class="input" />
          </div>

          <div>
            <label class="label">Email</label>
            <input :value="user?.email" type="email" class="input bg-gray-50" disabled />
            <p class="text-sm text-gray-500 mt-1">Email cannot be changed</p>
          </div>

          <div>
            <label class="label">Phone Number</label>
            <input v-model="profile.phone" type="tel" class="input" placeholder="+91 XXXXX XXXXX" />
          </div>

          <div>
            <label class="label">Bio</label>
            <textarea v-model="profile.bio" rows="3" class="input" placeholder="Tell buyers about yourself..."></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">City</label>
              <input v-model="profile.city" type="text" class="input" />
            </div>
            <div>
              <label class="label">State</label>
              <input v-model="profile.state" type="text" class="input" />
            </div>
          </div>

          <button type="submit" :disabled="savingProfile" class="btn-primary">
            {{ savingProfile ? 'Saving...' : 'Save Changes' }}
          </button>
        </form>
      </div>

      <!-- Password Section -->
      <div class="card p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h2>

        <form @submit.prevent="changePassword" class="space-y-4">
          <div>
            <label class="label">Current Password</label>
            <input v-model="password.current_password" type="password" class="input" />
          </div>

          <div>
            <label class="label">New Password</label>
            <input v-model="password.password" type="password" class="input" />
          </div>

          <div>
            <label class="label">Confirm New Password</label>
            <input v-model="password.password_confirmation" type="password" class="input" />
          </div>

          <button type="submit" :disabled="savingPassword" class="btn-primary">
            {{ savingPassword ? 'Updating...' : 'Update Password' }}
          </button>
        </form>
      </div>

      <!-- Notification Preferences -->
      <div class="card p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Notification Preferences</h2>

        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium text-gray-900">Email notifications</p>
              <p class="text-sm text-gray-500">Receive updates via email</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input v-model="notifications.email" type="checkbox" class="sr-only peer" />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
            </label>
          </div>

          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium text-gray-900">Push notifications</p>
              <p class="text-sm text-gray-500">Receive push notifications</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input v-model="notifications.push" type="checkbox" class="sr-only peer" />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
            </label>
          </div>

          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium text-gray-900">Message alerts</p>
              <p class="text-sm text-gray-500">Get notified when you receive messages</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input v-model="notifications.messages" type="checkbox" class="sr-only peer" />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
            </label>
          </div>
        </div>

        <button @click="saveNotifications" class="btn-primary mt-4">
          Save Preferences
        </button>
      </div>

      <!-- Danger Zone -->
      <div class="card p-6 border-red-200">
        <h2 class="text-lg font-semibold text-red-600 mb-4">Danger Zone</h2>

        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium text-gray-900">Delete Account</p>
            <p class="text-sm text-gray-500">Permanently delete your account and all data</p>
          </div>
          <button @click="showDeleteModal = true" class="btn-danger">
            Delete Account
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Account Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="showDeleteModal = false"></div>
      <div class="relative bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Account</h3>
        <p class="text-gray-500 mb-4">
          This action cannot be undone. All your listings, messages, and data will be permanently deleted.
        </p>
        <div class="mb-4">
          <label class="label">Enter your password to confirm</label>
          <input v-model="deletePassword" type="password" class="input" />
        </div>
        <div class="flex gap-2">
          <button @click="showDeleteModal = false" class="btn-secondary flex-1">
            Cancel
          </button>
          <button @click="deleteAccount" class="btn-danger flex-1" :disabled="!deletePassword">
            Delete Account
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue3-toastify'
import api from '@/services/api'
import { CameraIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user)

const savingProfile = ref(false)
const savingPassword = ref(false)
const showDeleteModal = ref(false)
const deletePassword = ref('')

const profile = reactive({
  name: '',
  phone: '',
  bio: '',
  city: '',
  state: '',
})

const password = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const notifications = reactive({
  email: true,
  push: true,
  messages: true,
})

onMounted(() => {
  if (user.value) {
    profile.name = user.value.name || ''
    profile.phone = user.value.phone || ''
    profile.bio = user.value.bio || ''
    profile.city = user.value.city || ''
    profile.state = user.value.state || ''

    if (user.value.notification_preferences) {
      Object.assign(notifications, user.value.notification_preferences)
    }
  }
})

const updateAvatar = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  if (file.size > 2 * 1024 * 1024) {
    toast.error('Image size must be less than 2MB')
    return
  }

  try {
    await authStore.updateAvatar(file)
    toast.success('Profile photo updated')
  } catch (error) {
    toast.error('Failed to update photo')
  }
}

const updateProfile = async () => {
  savingProfile.value = true
  try {
    await authStore.updateProfile(profile)
    toast.success('Profile updated')
  } catch (error) {
    toast.error('Failed to update profile')
  } finally {
    savingProfile.value = false
  }
}

const changePassword = async () => {
  if (password.password !== password.password_confirmation) {
    toast.error('Passwords do not match')
    return
  }

  savingPassword.value = true
  try {
    await authStore.changePassword(password)
    toast.success('Password updated')
    password.current_password = ''
    password.password = ''
    password.password_confirmation = ''
  } catch (error) {
    toast.error('Failed to update password')
  } finally {
    savingPassword.value = false
  }
}

const saveNotifications = async () => {
  try {
    await api.put('/notifications/preferences', notifications)
    toast.success('Preferences saved')
  } catch (error) {
    toast.error('Failed to save preferences')
  }
}

const deleteAccount = async () => {
  try {
    await api.delete('/auth/account', {
      data: { password: deletePassword.value }
    })
    toast.success('Account deleted')
    authStore.clearAuth()
    router.push('/')
  } catch (error) {
    toast.error('Failed to delete account')
  }
}
</script>
