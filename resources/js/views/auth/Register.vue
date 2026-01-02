<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 overflow-x-hidden">
    <div class="max-w-md w-full">
      <!-- Logo -->
      <div class="text-center mb-8">
        <router-link to="/" class="inline-flex items-center space-x-2">
          <div class="w-12 h-12 bg-primary-600 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-2xl">L</span>
          </div>
          <span class="text-2xl font-bold text-gray-900">Lentloads</span>
        </router-link>
        <h2 class="mt-6 text-3xl font-bold text-gray-900">Create an account</h2>
        <p class="mt-2 text-gray-600">Join thousands of buyers and sellers</p>
      </div>

      <!-- Progress Indicator -->
      <div class="flex items-center justify-center mb-8">
        <div class="flex items-center space-x-3">
          <!-- Step 1 -->
          <div
            class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold transition-all"
            :class="step === 'name' ? 'bg-primary-600 text-white' : 'bg-green-500 text-white'"
          >
            <CheckIcon v-if="step !== 'name'" class="w-5 h-5" />
            <span v-else>1</span>
          </div>
          <div class="w-8 h-0.5" :class="step === 'name' ? 'bg-gray-300' : 'bg-green-500'"></div>

          <!-- Step 2 -->
          <div
            class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold transition-all"
            :class="{
              'bg-primary-600 text-white': step === 'contact',
              'bg-green-500 text-white': step === 'password',
              'bg-gray-200 text-gray-500': step === 'name'
            }"
          >
            <CheckIcon v-if="step === 'password'" class="w-5 h-5" />
            <span v-else>2</span>
          </div>
          <div class="w-8 h-0.5" :class="step === 'password' ? 'bg-primary-600' : 'bg-gray-300'"></div>

          <!-- Step 3 -->
          <div
            class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold transition-all"
            :class="step === 'password' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-500'"
          >
            3
          </div>
        </div>
      </div>

      <!-- Form -->
      <div class="card p-8">
        <!-- Step 1: Name -->
        <div v-if="step === 'name'">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">What's your name?</h3>
          <p class="text-sm text-gray-500 mb-6">This will be displayed on your profile</p>

          <form @submit.prevent="goToContact">
            <div class="mb-6">
              <label for="name" class="label">Full Name</label>
              <input
                id="name"
                ref="nameInput"
                v-model="form.name"
                type="text"
                autocomplete="name"
                required
                class="input"
                :class="{ 'input-error': errors.name }"
                placeholder="Enter your full name"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <button type="submit" class="btn-primary w-full">
              Continue
            </button>
          </form>
        </div>

        <!-- Step 2: Contact (Email + Phone) -->
        <div v-if="step === 'contact'">
          <div class="flex items-center mb-4">
            <button @click="step = 'name'" class="text-gray-500 hover:text-gray-700 mr-3">
              <ArrowLeftIcon class="w-5 h-5" />
            </button>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Contact details</h3>
              <p class="text-sm text-gray-500">How can buyers reach you?</p>
            </div>
          </div>

          <form @submit.prevent="goToPassword">
            <div class="mb-4">
              <label for="email" class="label">Email address</label>
              <input
                id="email"
                ref="emailInput"
                v-model="form.email"
                type="email"
                autocomplete="email"
                required
                class="input"
                :class="{ 'input-error': errors.email }"
                placeholder="you@example.com"
              />
              <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
            </div>

            <div class="mb-6">
              <label for="phone" class="label">Phone Number</label>
              <input
                id="phone"
                v-model="form.phone"
                type="tel"
                autocomplete="tel"
                required
                class="input"
                :class="{ 'input-error': errors.phone }"
                placeholder="Enter your phone number"
              />
              <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
              <p class="mt-1 text-xs text-gray-500">With or without country code (e.g., 8122116594 or +91 8122116594)</p>
            </div>

            <button type="submit" class="btn-primary w-full">
              Continue
            </button>
          </form>
        </div>

        <!-- Step 3: Password -->
        <div v-if="step === 'password'">
          <div class="flex items-center mb-4">
            <button @click="step = 'contact'" class="text-gray-500 hover:text-gray-700 mr-3">
              <ArrowLeftIcon class="w-5 h-5" />
            </button>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Set your password</h3>
              <p class="text-sm text-gray-500">Keep your account secure</p>
            </div>
          </div>

          <form @submit.prevent="handleSubmit">
            <div class="mb-4">
              <label for="password" class="label">Password</label>
              <div class="relative">
                <input
                  id="password"
                  ref="passwordInput"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="new-password"
                  required
                  class="input pr-10"
                  :class="{ 'input-error': errors.password }"
                  placeholder="At least 8 characters"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500"
                >
                  <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                  <EyeSlashIcon v-else class="w-5 h-5" />
                </button>
              </div>
              <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
            </div>

            <div class="mb-4">
              <label for="password_confirmation" class="label">Confirm Password</label>
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                class="input"
                :class="{ 'input-error': errors.password_confirmation }"
                placeholder="Re-enter your password"
              />
              <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">
                {{ errors.password_confirmation }}
              </p>
            </div>

            <div class="flex items-start mb-6">
              <input
                id="terms"
                v-model="form.terms"
                type="checkbox"
                required
                class="w-4 h-4 mt-1 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
              />
              <label for="terms" class="ml-2 text-sm text-gray-600">
                I agree to the
                <router-link to="/page/terms" class="text-primary-600 hover:underline">Terms of Service</router-link>
                and
                <router-link to="/page/privacy" class="text-primary-600 hover:underline">Privacy Policy</router-link>
              </label>
            </div>

            <button
              type="submit"
              :disabled="loading"
              class="btn-primary w-full"
            >
              <span v-if="loading">Creating account...</span>
              <span v-else>Create Account</span>
            </button>
          </form>
        </div>

        <!-- Divider -->
        <div class="my-6 flex items-center">
          <div class="flex-1 border-t border-gray-300"></div>
          <span class="px-4 text-sm text-gray-500">or continue with</span>
          <div class="flex-1 border-t border-gray-300"></div>
        </div>

        <!-- Social Login -->
        <div class="grid grid-cols-2 gap-4">
          <button
            type="button"
            class="btn-secondary flex items-center justify-center"
          >
            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
              <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
              <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Google
          </button>
          <button
            type="button"
            class="btn-secondary flex items-center justify-center"
          >
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
            Facebook
          </button>
        </div>
      </div>

      <!-- Login link -->
      <p class="mt-6 text-center text-gray-600">
        Already have an account?
        <router-link to="/login" class="text-primary-600 font-medium hover:underline">
          Sign in
        </router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, nextTick, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue3-toastify'
import { EyeIcon, EyeSlashIcon, ArrowLeftIcon, CheckIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()

const step = ref('name') // 'name', 'contact', 'password'
const loading = ref(false)
const showPassword = ref(false)

// Template refs
const nameInput = ref(null)
const emailInput = ref(null)
const passwordInput = ref(null)

const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  terms: false,
})

const errors = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

// Step navigation
const goToContact = () => {
  errors.name = ''
  if (!form.name || form.name.trim().length < 2) {
    errors.name = 'Please enter your name'
    return
  }
  step.value = 'contact'
  nextTick(() => {
    emailInput.value?.focus()
  })
}

const goToPassword = () => {
  errors.email = ''
  errors.phone = ''

  if (!form.email) {
    errors.email = 'Email is required'
    return
  }
  if (!form.email.includes('@')) {
    errors.email = 'Please enter a valid email'
    return
  }
  if (!form.phone) {
    errors.phone = 'Phone number is required'
    return
  }
  if (form.phone.replace(/\D/g, '').length < 10) {
    errors.phone = 'Please enter a valid phone number'
    return
  }

  step.value = 'password'
  nextTick(() => {
    passwordInput.value?.focus()
  })
}

const handleSubmit = async () => {
  // Reset errors
  Object.keys(errors).forEach(key => errors[key] = '')

  // Validate password
  if (!form.password || form.password.length < 8) {
    errors.password = 'Password must be at least 8 characters'
    return
  }
  if (form.password !== form.password_confirmation) {
    errors.password_confirmation = 'Passwords do not match'
    return
  }

  loading.value = true

  try {
    await authStore.register(form)
    toast.success('Account created successfully!')
    router.push('/dashboard')
  } catch (error) {
    if (error.response?.data?.errors) {
      const serverErrors = error.response.data.errors
      Object.assign(errors, serverErrors)

      // Navigate to the step with error
      if (serverErrors.name) {
        step.value = 'name'
      } else if (serverErrors.email || serverErrors.phone) {
        step.value = 'contact'
      }
    }
  } finally {
    loading.value = false
  }
}

// Focus name input on mount
onMounted(() => {
  nextTick(() => {
    nameInput.value?.focus()
  })
})
</script>
