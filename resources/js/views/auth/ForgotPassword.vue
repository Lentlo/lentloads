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

          <!-- Contact Support -->
          <div class="mt-6 pt-6 border-t">
            <p class="text-sm text-gray-500 text-center mb-3">
              Can't reset your password? Contact us for help
            </p>
            <div class="flex justify-center gap-3">
              <a
                href="tel:+918122116594"
                class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm text-gray-700 transition"
              >
                <PhoneIcon class="w-4 h-4" />
                Call Us
              </a>
              <a
                href="https://wa.me/918122116594?text=Hi,%20I%20need%20help%20resetting%20my%20Lentloads%20password"
                target="_blank"
                class="flex items-center gap-2 px-4 py-2 bg-green-100 hover:bg-green-200 rounded-lg text-sm text-green-700 transition"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                WhatsApp
              </a>
            </div>
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
  PhoneIcon,
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
