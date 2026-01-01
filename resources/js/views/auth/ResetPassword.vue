<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-8">
        <router-link to="/" class="inline-block">
          <h1 class="text-3xl font-bold text-primary-600">Lentloads</h1>
        </router-link>
      </div>

      <div class="card p-8">
        <!-- Success State -->
        <div v-if="resetSuccess" class="text-center">
          <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <CheckCircleIcon class="w-8 h-8 text-green-600" />
          </div>
          <h2 class="text-xl font-bold text-gray-900 mb-2">Password Reset!</h2>
          <p class="text-gray-500 mb-6">
            Your password has been successfully reset. You can now log in with your new password.
          </p>
          <router-link to="/login" class="btn-primary w-full">
            Go to Login
          </router-link>
        </div>

        <!-- Invalid Token State -->
        <div v-else-if="invalidToken" class="text-center">
          <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <ExclamationCircleIcon class="w-8 h-8 text-red-600" />
          </div>
          <h2 class="text-xl font-bold text-gray-900 mb-2">Invalid or Expired Link</h2>
          <p class="text-gray-500 mb-6">
            This password reset link is invalid or has expired. Please request a new one.
          </p>
          <router-link to="/forgot-password" class="btn-primary w-full">
            Request New Link
          </router-link>
        </div>

        <!-- Form State -->
        <div v-else>
          <div class="text-center mb-6">
            <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <KeyIcon class="w-8 h-8 text-primary-600" />
            </div>
            <h2 class="text-xl font-bold text-gray-900">Set new password</h2>
            <p class="text-gray-500 mt-1">
              Enter your new password below
            </p>
          </div>

          <form @submit.prevent="resetPassword" class="space-y-4">
            <div>
              <label class="label">Email address</label>
              <input
                v-model="form.email"
                type="email"
                class="input bg-gray-50"
                readonly
              />
            </div>

            <div>
              <label class="label">New Password</label>
              <div class="relative">
                <LockClosedIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  class="input pl-10 pr-10"
                  placeholder="Minimum 8 characters"
                  required
                  minlength="8"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2"
                >
                  <EyeIcon v-if="!showPassword" class="w-5 h-5 text-gray-400" />
                  <EyeSlashIcon v-else class="w-5 h-5 text-gray-400" />
                </button>
              </div>
            </div>

            <div>
              <label class="label">Confirm New Password</label>
              <div class="relative">
                <LockClosedIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <input
                  v-model="form.password_confirmation"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  class="input pl-10 pr-10"
                  placeholder="Confirm your password"
                  required
                />
                <button
                  type="button"
                  @click="showConfirmPassword = !showConfirmPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2"
                >
                  <EyeIcon v-if="!showConfirmPassword" class="w-5 h-5 text-gray-400" />
                  <EyeSlashIcon v-else class="w-5 h-5 text-gray-400" />
                </button>
              </div>
            </div>

            <!-- Password Strength Indicator -->
            <div v-if="form.password" class="space-y-2">
              <div class="flex gap-1">
                <div
                  v-for="i in 4"
                  :key="i"
                  class="h-1 flex-1 rounded"
                  :class="i <= passwordStrength ? strengthColors[passwordStrength] : 'bg-gray-200'"
                ></div>
              </div>
              <p class="text-xs" :class="strengthTextColors[passwordStrength]">
                {{ strengthLabels[passwordStrength] }}
              </p>
            </div>

            <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

            <button
              type="submit"
              :disabled="submitting || !isValid"
              class="btn-primary w-full"
            >
              <LoadingSpinner v-if="submitting" class="mr-2" />
              {{ submitting ? 'Resetting...' : 'Reset Password' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import {
  KeyIcon,
  LockClosedIcon,
  EyeIcon,
  EyeSlashIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
} from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const route = useRoute()
const router = useRouter()

const form = reactive({
  email: '',
  password: '',
  password_confirmation: '',
  token: ''
})

const showPassword = ref(false)
const showConfirmPassword = ref(false)
const submitting = ref(false)
const resetSuccess = ref(false)
const invalidToken = ref(false)
const error = ref('')

const strengthLabels = ['', 'Weak', 'Fair', 'Good', 'Strong']
const strengthColors = ['', 'bg-red-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500']
const strengthTextColors = ['', 'text-red-600', 'text-yellow-600', 'text-blue-600', 'text-green-600']

const passwordStrength = computed(() => {
  const password = form.password
  if (!password) return 0

  let strength = 0
  if (password.length >= 8) strength++
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++
  if (/\d/.test(password)) strength++
  if (/[^a-zA-Z0-9]/.test(password)) strength++

  return strength
})

const isValid = computed(() => {
  return (
    form.password.length >= 8 &&
    form.password === form.password_confirmation
  )
})

const resetPassword = async () => {
  if (!isValid.value) {
    if (form.password !== form.password_confirmation) {
      error.value = 'Passwords do not match'
    }
    return
  }

  error.value = ''
  submitting.value = true

  try {
    await api.post('/auth/reset-password', form)
    resetSuccess.value = true
  } catch (err) {
    if (err.response?.status === 400 || err.response?.status === 422) {
      error.value = err.response.data.message || 'Invalid or expired reset link'
      if (err.response.data.errors?.token) {
        invalidToken.value = true
      }
    } else {
      error.value = 'Something went wrong. Please try again.'
    }
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  // Get token and email from URL
  form.token = route.query.token || route.params.token || ''
  form.email = route.query.email || ''

  if (!form.token) {
    invalidToken.value = true
  }
})
</script>
