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
        <h2 class="mt-6 text-3xl font-bold text-gray-900">
          {{ step === 'login' ? 'Welcome back' : step === 'register' ? 'Create account' : 'Sign in or Register' }}
        </h2>
        <p class="mt-2 text-gray-600">
          {{ step === 'login' ? `Welcome back, ${userName}!` : step === 'register' ? 'Create your account to get started' : 'Enter your phone number or email' }}
        </p>
      </div>

      <!-- Form -->
      <div class="card p-8">
        <!-- Step 1: Enter Phone/Email -->
        <div v-if="step === 'identify'">
          <form @submit.prevent="checkUser" class="space-y-6">
            <div>
              <label for="login" class="label">Phone number or Email</label>
              <input
                id="login"
                ref="loginInput"
                v-model="form.login"
                type="text"
                autocomplete="email tel"
                required
                class="input"
                :class="{ 'input-error': errors.login }"
                placeholder="Enter phone or email"
              />
              <p class="mt-1 text-xs text-gray-500">Example: 8122116594 or you@example.com</p>
              <p v-if="errors.login" class="mt-1 text-sm text-red-600">{{ errors.login }}</p>
            </div>

            <button
              type="submit"
              :disabled="loading"
              class="btn-primary w-full"
            >
              <span v-if="loading" class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Checking...
              </span>
              <span v-else>Continue</span>
            </button>
          </form>
        </div>

        <!-- Step 2a: Login (User Exists) -->
        <div v-else-if="step === 'login'">
          <!-- Back Button & User Info -->
          <div class="mb-6">
            <button
              type="button"
              @click="goBack"
              class="flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4"
            >
              <ArrowLeftIcon class="w-4 h-4 mr-1" />
              Change
            </button>
            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
              <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                <UserIcon class="w-5 h-5 text-primary-600" />
              </div>
              <div class="ml-3">
                <p class="font-medium text-gray-900">{{ userName }}</p>
                <p class="text-sm text-gray-500">{{ form.login }}</p>
              </div>
            </div>
          </div>

          <form @submit.prevent="handleLogin" class="space-y-6">
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
                  ref="passwordInput"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="current-password"
                  required
                  class="input pr-10"
                  :class="{ 'input-error': errors.password }"
                  placeholder="Enter your password"
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

            <button
              type="submit"
              :disabled="loading"
              class="btn-primary w-full"
            >
              <span v-if="loading">Signing in...</span>
              <span v-else>Sign in</span>
            </button>
          </form>
        </div>

        <!-- Step 2b: Register (User Doesn't Exist) -->
        <div v-else-if="step === 'register'">
          <!-- Back Button -->
          <div class="mb-6">
            <button
              type="button"
              @click="goBack"
              class="flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4"
            >
              <ArrowLeftIcon class="w-4 h-4 mr-1" />
              Change
            </button>
            <div class="flex items-center p-3 bg-blue-50 rounded-lg">
              <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <UserPlusIcon class="w-5 h-5 text-blue-600" />
              </div>
              <div class="ml-3">
                <p class="font-medium text-gray-900">New account</p>
                <p class="text-sm text-gray-500">{{ form.login }}</p>
              </div>
            </div>
          </div>

          <form @submit.prevent="handleRegister" class="space-y-4">
            <div>
              <label for="name" class="label">Full Name</label>
              <input
                id="name"
                ref="nameInput"
                v-model="registerForm.name"
                type="text"
                autocomplete="name"
                required
                class="input"
                :class="{ 'input-error': errors.name }"
                placeholder="Enter your full name"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <!-- Email field (if user entered phone) -->
            <div v-if="loginType === 'phone'">
              <label for="email" class="label">Email address</label>
              <input
                id="email"
                v-model="registerForm.email"
                type="email"
                autocomplete="email"
                required
                class="input"
                :class="{ 'input-error': errors.email }"
                placeholder="you@example.com"
              />
              <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
            </div>

            <!-- Phone field (if user entered email) -->
            <div v-else>
              <label for="phone" class="label">Phone number</label>
              <input
                id="phone"
                v-model="registerForm.phone"
                type="tel"
                autocomplete="tel"
                required
                class="input"
                :class="{ 'input-error': errors.phone }"
                placeholder="Enter your phone number"
              />
              <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
            </div>

            <div>
              <label for="reg-password" class="label">Create Password</label>
              <div class="relative">
                <input
                  id="reg-password"
                  v-model="registerForm.password"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="new-password"
                  required
                  class="input pr-10"
                  :class="{ 'input-error': errors.password }"
                  placeholder="Min 8 characters"
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

            <div>
              <label for="password-confirm" class="label">Confirm Password</label>
              <input
                id="password-confirm"
                v-model="registerForm.password_confirmation"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                class="input"
                :class="{ 'input-error': errors.password_confirmation }"
                placeholder="Confirm your password"
              />
              <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ errors.password_confirmation }}</p>
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

        <!-- Need Help (show on all steps) -->
        <div class="mt-6 pt-6 border-t">
          <p class="text-sm text-gray-500 text-center mb-3">
            Need help? Contact us
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
              href="https://wa.me/918122116594?text=Hi,%20I%20need%20help%20with%20my%20Lentloads%20account"
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
</template>

<script setup>
import { ref, reactive, nextTick, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import { EyeIcon, EyeSlashIcon, PhoneIcon, ArrowLeftIcon, UserIcon, UserPlusIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const loading = ref(false)
const showPassword = ref(false)
const step = ref('identify') // 'identify', 'login', 'register'
const userName = ref('')
const loginType = ref('') // 'phone' or 'email'

// Refs for input focus
const loginInput = ref(null)
const passwordInput = ref(null)
const nameInput = ref(null)

const form = reactive({
  login: '',
  password: '',
  remember: false,
})

const registerForm = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

const errors = reactive({
  login: '',
  password: '',
  name: '',
  email: '',
  phone: '',
  password_confirmation: '',
})

const clearErrors = () => {
  Object.keys(errors).forEach(key => errors[key] = '')
}

const checkUser = async () => {
  clearErrors()

  if (!form.login || form.login.length < 5) {
    errors.login = 'Please enter a valid phone number or email'
    return
  }

  loading.value = true

  try {
    const response = await api.post('/auth/check-user', { login: form.login })
    const { exists, name, type } = response.data.data

    loginType.value = type
    userName.value = name || 'User'

    if (exists) {
      step.value = 'login'
      await nextTick()
      passwordInput.value?.focus()
    } else {
      step.value = 'register'
      // Pre-fill the appropriate field
      if (type === 'email') {
        registerForm.email = form.login
      } else {
        registerForm.phone = form.login
      }
      await nextTick()
      nameInput.value?.focus()
    }
  } catch (error) {
    if (error.response?.status === 429) {
      errors.login = 'Too many attempts. Please try again later.'
    } else {
      errors.login = 'Something went wrong. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

const handleLogin = async () => {
  clearErrors()

  if (!form.password) {
    errors.password = 'Password is required'
    return
  }

  loading.value = true

  try {
    await authStore.login({
      login: form.login,
      password: form.password,
      remember: form.remember,
    })
    toast.success('Welcome back!')

    // Redirect - validate to prevent open redirect attacks
    const redirect = route.query.redirect
    const safeRedirect = redirect && redirect.startsWith('/') && !redirect.startsWith('//')
      ? redirect
      : '/dashboard'
    router.push(safeRedirect)
  } catch (error) {
    if (error.response?.status === 401) {
      errors.password = 'Incorrect password. Please try again.'
    } else if (error.response?.status === 403) {
      errors.password = 'Your account has been suspended.'
    } else {
      errors.password = 'Something went wrong. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

const handleRegister = async () => {
  clearErrors()

  // Validation
  if (!registerForm.name) {
    errors.name = 'Name is required'
    return
  }

  if (loginType.value === 'phone' && !registerForm.email) {
    errors.email = 'Email is required'
    return
  }

  if (loginType.value === 'email' && !registerForm.phone) {
    errors.phone = 'Phone number is required'
    return
  }

  if (!registerForm.password || registerForm.password.length < 8) {
    errors.password = 'Password must be at least 8 characters'
    return
  }

  if (registerForm.password !== registerForm.password_confirmation) {
    errors.password_confirmation = 'Passwords do not match'
    return
  }

  loading.value = true

  try {
    // Prepare registration data
    const data = {
      name: registerForm.name,
      email: loginType.value === 'phone' ? registerForm.email : form.login,
      phone: loginType.value === 'phone' ? form.login : registerForm.phone,
      password: registerForm.password,
      password_confirmation: registerForm.password_confirmation,
    }

    await authStore.register(data)
    toast.success('Account created successfully!')

    // Redirect
    const redirect = route.query.redirect
    const safeRedirect = redirect && redirect.startsWith('/') && !redirect.startsWith('//')
      ? redirect
      : '/dashboard'
    router.push(safeRedirect)
  } catch (error) {
    if (error.response?.data?.errors) {
      const serverErrors = error.response.data.errors
      if (serverErrors.email) errors.email = serverErrors.email[0]
      if (serverErrors.phone) errors.phone = serverErrors.phone[0]
      if (serverErrors.password) errors.password = serverErrors.password[0]
      if (serverErrors.name) errors.name = serverErrors.name[0]
    } else {
      errors.password = 'Something went wrong. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

const goBack = () => {
  step.value = 'identify'
  form.password = ''
  Object.keys(registerForm).forEach(key => registerForm[key] = '')
  clearErrors()
  nextTick(() => loginInput.value?.focus())
}

onMounted(() => {
  loginInput.value?.focus()
})
</script>
