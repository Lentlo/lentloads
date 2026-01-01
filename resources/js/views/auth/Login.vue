<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="max-w-md w-full">
      <!-- Logo -->
      <div class="text-center mb-8">
        <router-link to="/" class="inline-flex items-center space-x-2">
          <div class="w-12 h-12 bg-primary-600 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-2xl">L</span>
          </div>
          <span class="text-2xl font-bold text-gray-900">Lentloads</span>
        </router-link>
        <h2 class="mt-6 text-3xl font-bold text-gray-900">Welcome back</h2>
        <p class="mt-2 text-gray-600">Sign in to your account</p>
      </div>

      <!-- Form -->
      <div class="card p-8">
        <!-- Login Method Toggle -->
        <div class="flex mb-6 bg-gray-100 rounded-lg p-1">
          <button
            type="button"
            @click="loginMethod = 'email'"
            class="flex-1 py-2 text-sm font-medium rounded-md transition"
            :class="loginMethod === 'email' ? 'bg-white shadow text-primary-600' : 'text-gray-600 hover:text-gray-900'"
          >
            Email
          </button>
          <button
            type="button"
            @click="loginMethod = 'phone'"
            class="flex-1 py-2 text-sm font-medium rounded-md transition"
            :class="loginMethod === 'phone' ? 'bg-white shadow text-primary-600' : 'text-gray-600 hover:text-gray-900'"
          >
            Phone
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Email Input -->
          <div v-if="loginMethod === 'email'">
            <label for="email" class="label">Email address</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              required
              class="input"
              :class="{ 'input-error': errors.login }"
              placeholder="you@example.com"
            />
            <p v-if="errors.login" class="mt-1 text-sm text-red-600">{{ errors.login }}</p>
          </div>

          <!-- Phone Input -->
          <div v-else>
            <label for="phone" class="label">Phone number</label>
            <input
              id="phone"
              v-model="form.phone"
              type="tel"
              autocomplete="tel"
              required
              class="input"
              :class="{ 'input-error': errors.login }"
              placeholder="+91 XXXXX XXXXX"
            />
            <p v-if="errors.login" class="mt-1 text-sm text-red-600">{{ errors.login }}</p>
          </div>

          <!-- Password -->
          <div>
            <div class="flex items-center justify-between">
              <label for="password" class="label">Password</label>
              <router-link to="/forgot-password" class="text-sm text-primary-600 hover:underline">
                Forgot password?
              </router-link>
            </div>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                required
                class="input pr-10"
                :class="{ 'input-error': errors.password }"
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

          <!-- Remember me -->
          <div class="flex items-center">
            <input
              id="remember"
              v-model="form.remember"
              type="checkbox"
              class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
            />
            <label for="remember" class="ml-2 text-sm text-gray-600">
              Remember me
            </label>
          </div>

          <!-- Submit -->
          <button
            type="submit"
            :disabled="loading"
            class="btn-primary w-full"
          >
            <span v-if="loading">Signing in...</span>
            <span v-else>Sign in</span>
          </button>
        </form>

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

        <!-- Need Help -->
        <div class="mt-6 pt-6 border-t">
          <p class="text-sm text-gray-500 text-center mb-3">
            Need help logging in? Contact us
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
              href="https://wa.me/918122116594?text=Hi,%20I%20need%20help%20logging%20into%20my%20Lentloads%20account"
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

      <!-- Register link -->
      <p class="mt-6 text-center text-gray-600">
        Don't have an account?
        <router-link to="/register" class="text-primary-600 font-medium hover:underline">
          Sign up
        </router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue3-toastify'
import { EyeIcon, EyeSlashIcon, PhoneIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const loading = ref(false)
const showPassword = ref(false)
const loginMethod = ref('email') // 'email' or 'phone'

const form = reactive({
  email: '',
  phone: '',
  password: '',
  remember: false,
})

const errors = reactive({
  login: '',
  password: '',
})

const handleSubmit = async () => {
  // Reset errors
  errors.login = ''
  errors.password = ''

  // Validate based on login method
  const loginValue = loginMethod.value === 'email' ? form.email : form.phone
  if (!loginValue) {
    errors.login = loginMethod.value === 'email' ? 'Email is required' : 'Phone number is required'
    return
  }
  if (!form.password) {
    errors.password = 'Password is required'
    return
  }

  loading.value = true

  try {
    await authStore.login({
      login: loginValue,
      password: form.password,
      remember: form.remember,
    })
    toast.success('Welcome back!')

    // Redirect - validate to prevent open redirect attacks
    const redirect = route.query.redirect
    // Only allow internal paths (starting with /) and not protocol-relative URLs (//)
    const safeRedirect = redirect && redirect.startsWith('/') && !redirect.startsWith('//')
      ? redirect
      : '/dashboard'
    router.push(safeRedirect)
  } catch (error) {
    if (error.response?.status === 401) {
      errors.password = loginMethod.value === 'email'
        ? 'Invalid email or password'
        : 'Invalid phone number or password'
    }
  } finally {
    loading.value = false
  }
}
</script>
