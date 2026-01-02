<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50" @click="$emit('close')"></div>

    <!-- Modal -->
    <div class="relative bg-white w-full sm:max-w-md sm:rounded-xl rounded-t-xl shadow-xl p-6 z-10 max-h-[90vh] overflow-y-auto safe-area-bottom">
      <!-- Close Button -->
      <button
        @click="$emit('close')"
        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"
      >
        <XMarkIcon class="w-6 h-6" />
      </button>

      <!-- Step 1: Phone Number -->
      <div v-if="step === 'phone'">
        <div class="text-center mb-6">
          <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <PhoneIcon class="w-8 h-8 text-primary-600" />
          </div>
          <h2 class="text-xl font-bold text-gray-900">Almost Done!</h2>
          <p class="text-gray-500 mt-1">Enter your phone number to continue</p>
        </div>

        <form @submit.prevent="checkPhone">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
            <div class="flex">
              <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg text-gray-500">
                +91
              </span>
              <input
                v-model="phone"
                type="tel"
                required
                maxlength="10"
                pattern="[0-9]{10}"
                placeholder="Enter 10 digit number"
                class="input rounded-l-none flex-1"
                :disabled="loading"
              />
            </div>
          </div>

          <button
            type="submit"
            :disabled="loading || phone.length !== 10"
            class="btn-primary w-full"
          >
            <span v-if="loading" class="flex items-center justify-center gap-2">
              <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Checking...
            </span>
            <span v-else>Continue</span>
          </button>
        </form>
      </div>

      <!-- Step 2a: Existing User - Login -->
      <div v-else-if="step === 'login'">
        <div class="text-center mb-6">
          <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <UserIcon class="w-8 h-8 text-green-600" />
          </div>
          <h2 class="text-xl font-bold text-gray-900">Welcome Back{{ userName ? `, ${userName}` : '' }}!</h2>
          <p class="text-gray-500 mt-1">Enter your password to post your ad</p>
        </div>

        <form @submit.prevent="login">
          <div class="mb-4">
            <div class="flex items-center justify-between text-sm mb-1">
              <label class="font-medium text-gray-700">Phone</label>
              <button type="button" @click="step = 'phone'" class="text-primary-600 hover:underline">
                Change
              </button>
            </div>
            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg text-gray-700">
              <PhoneIcon class="w-5 h-5 text-gray-400" />
              +91 {{ phone }}
            </div>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <div class="relative">
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                required
                placeholder="Enter your password"
                class="input pr-10"
                :disabled="loading"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
              >
                <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                <EyeSlashIcon v-else class="w-5 h-5" />
              </button>
            </div>
          </div>

          <p v-if="error" class="text-red-500 text-sm mb-4">{{ error }}</p>

          <button
            type="submit"
            :disabled="loading || !password"
            class="btn-primary w-full mb-3"
          >
            <span v-if="loading" class="flex items-center justify-center gap-2">
              <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Logging in...
            </span>
            <span v-else>Login & Post Ad</span>
          </button>

          <div class="text-center">
            <router-link
              to="/forgot-password"
              class="text-sm text-primary-600 hover:underline"
              @click="$emit('close')"
            >
              Forgot Password?
            </router-link>
          </div>
        </form>
      </div>

      <!-- Step 2b: New User - Register -->
      <div v-else-if="step === 'register'">
        <div class="text-center mb-6">
          <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <UserIcon class="w-8 h-8 text-primary-600" />
          </div>
          <h2 class="text-xl font-bold text-gray-900">Create Account</h2>
          <p class="text-gray-500 mt-1">Quick signup to post your ad</p>
        </div>

        <form @submit.prevent="register">
          <div class="mb-4">
            <div class="flex items-center justify-between text-sm mb-1">
              <label class="font-medium text-gray-700">Phone</label>
              <button type="button" @click="step = 'phone'" class="text-primary-600 hover:underline">
                Change
              </button>
            </div>
            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg text-gray-700">
              <PhoneIcon class="w-5 h-5 text-gray-400" />
              +91 {{ phone }}
            </div>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
            <input
              v-model="name"
              type="text"
              required
              placeholder="Enter your name"
              class="input"
              :disabled="loading"
            />
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              v-model="email"
              type="email"
              required
              placeholder="Enter your email"
              class="input"
              :disabled="loading"
            />
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Create Password</label>
            <div class="relative">
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                required
                minlength="8"
                placeholder="Min 8 characters"
                class="input pr-10"
                :disabled="loading"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
              >
                <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                <EyeSlashIcon v-else class="w-5 h-5" />
              </button>
            </div>
          </div>

          <p v-if="error" class="text-red-500 text-sm mb-4">{{ error }}</p>

          <button
            type="submit"
            :disabled="loading || !name || !email || password.length < 8"
            class="btn-primary w-full mb-3"
          >
            <span v-if="loading" class="flex items-center justify-center gap-2">
              <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Creating account...
            </span>
            <span v-else>Create Account & Post Ad</span>
          </button>

          <p class="text-xs text-gray-500 text-center">
            By creating an account, you agree to our
            <router-link to="/pages/terms" class="text-primary-600 hover:underline" @click="$emit('close')">Terms</router-link>
            and
            <router-link to="/pages/privacy" class="text-primary-600 hover:underline" @click="$emit('close')">Privacy Policy</router-link>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { XMarkIcon, PhoneIcon, UserIcon, EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'authenticated'])

const authStore = useAuthStore()

const step = ref('phone') // phone, login, register
const loading = ref(false)
const error = ref('')
const showPassword = ref(false)

// Form fields
const phone = ref('')
const userName = ref('')
const name = ref('')
const email = ref('')
const password = ref('')

const checkPhone = async () => {
  if (phone.value.length !== 10) return

  loading.value = true
  error.value = ''

  try {
    const response = await api.post('/auth/check-phone', {
      phone: '+91' + phone.value
    })

    if (response.data.data.exists) {
      userName.value = response.data.data.name
      step.value = 'login'
    } else {
      step.value = 'register'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Something went wrong. Please try again.'
  } finally {
    loading.value = false
  }
}

const login = async () => {
  loading.value = true
  error.value = ''

  try {
    await authStore.login({
      login: '+91' + phone.value,
      password: password.value
    })

    emit('authenticated')
  } catch (err) {
    error.value = err.response?.data?.message || 'Invalid password. Please try again.'
  } finally {
    loading.value = false
  }
}

const register = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await api.post('/auth/quick-register', {
      name: name.value,
      email: email.value,
      password: password.value,
      phone: '+91' + phone.value
    })

    // Store the token and user
    authStore.setAuth(response.data.data)

    emit('authenticated')
  } catch (err) {
    error.value = err.response?.data?.message || 'Registration failed. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.safe-area-bottom {
  padding-bottom: max(env(safe-area-inset-bottom, 0), 16px);
}

@media (max-width: 640px) {
  .safe-area-bottom {
    padding-bottom: max(env(safe-area-inset-bottom, 0), 24px);
  }
}
</style>
