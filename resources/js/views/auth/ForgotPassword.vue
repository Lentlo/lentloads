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
        <div v-if="emailSent" class="text-center">
          <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <CheckCircleIcon class="w-8 h-8 text-green-600" />
          </div>
          <h2 class="text-xl font-bold text-gray-900 mb-2">Check your email</h2>
          <p class="text-gray-500 mb-6">
            We've sent password reset instructions to <strong>{{ email }}</strong>
          </p>
          <p class="text-sm text-gray-500 mb-4">
            Didn't receive the email? Check your spam folder or
          </p>
          <button @click="resendEmail" :disabled="resending" class="text-primary-600 hover:underline">
            {{ resending ? 'Sending...' : 'click here to resend' }}
          </button>

          <div class="mt-8 pt-6 border-t">
            <router-link to="/login" class="text-primary-600 hover:underline">
              &larr; Back to login
            </router-link>
          </div>
        </div>

        <!-- Form State -->
        <div v-else>
          <div class="text-center mb-6">
            <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <LockClosedIcon class="w-8 h-8 text-primary-600" />
            </div>
            <h2 class="text-xl font-bold text-gray-900">Forgot your password?</h2>
            <p class="text-gray-500 mt-1">
              Enter your email and we'll send you a reset link
            </p>
          </div>

          <form @submit.prevent="submitRequest" class="space-y-4">
            <div>
              <label class="label">Email address</label>
              <div class="relative">
                <EnvelopeIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <input
                  v-model="email"
                  type="email"
                  class="input pl-10"
                  placeholder="you@example.com"
                  required
                  autofocus
                />
              </div>
              <p v-if="error" class="text-sm text-red-600 mt-1">{{ error }}</p>
            </div>

            <button
              type="submit"
              :disabled="submitting || !email"
              class="btn-primary w-full"
            >
              <LoadingSpinner v-if="submitting" class="mr-2" />
              {{ submitting ? 'Sending...' : 'Send Reset Link' }}
            </button>
          </form>

          <div class="mt-6 text-center">
            <router-link to="/login" class="text-primary-600 hover:underline">
              &larr; Back to login
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import {
  LockClosedIcon,
  EnvelopeIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const email = ref('')
const submitting = ref(false)
const resending = ref(false)
const emailSent = ref(false)
const error = ref('')

const submitRequest = async () => {
  error.value = ''
  submitting.value = true

  try {
    await api.post('/auth/forgot-password', { email: email.value })
    emailSent.value = true
  } catch (err) {
    if (err.response?.status === 422) {
      error.value = err.response.data.message || 'Invalid email address'
    } else if (err.response?.status === 429) {
      error.value = 'Too many attempts. Please try again later.'
    } else {
      error.value = 'Something went wrong. Please try again.'
    }
  } finally {
    submitting.value = false
  }
}

const resendEmail = async () => {
  resending.value = true
  try {
    await api.post('/auth/forgot-password', { email: email.value })
    toast.success('Reset link sent again!')
  } catch (err) {
    if (err.response?.status === 429) {
      toast.error('Please wait before requesting another link')
    } else {
      toast.error('Failed to resend. Please try again.')
    }
  } finally {
    resending.value = false
  }
}
</script>
