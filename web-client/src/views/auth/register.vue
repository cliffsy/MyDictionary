<script setup>
import { ref } from 'vue'
import auth from '@/api/auth.js'

import dialogComponent from '@/components/dialog.vue'
import loaderOverlay from '@/components/loader-overlay.vue'

var form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

var dialogContent = ref({
  show: false,
})

var loading = ref(false)

async function handleSubmit(e) {
  try {
    const formWrapper = e.target
    if (!formWrapper.checkValidity()) {
      formWrapper.reportValidity()
      return
    }
    loading.value = true
    const { data } = await auth.register(form.value)
    if (data.statusCode == 200) {
      dialogContent.value.title = `Hey ${data?.data?.name}, welcome to MyDictionary!`
      dialogContent.value.body = data.message
      dialogContent.value.button = {
        label: 'Sign In',
        action: () => {
          window.location.href = '/'
        },
      }
      dialogContent.value.show = true
    }
    loading.value = false
  } catch (e) {
    loading.value = false
    var response = e.response.data
    if (response.statusCode == 400) {
      dialogContent.value.title = 'Error'
      dialogContent.value.body = response.message
      dialogContent.value.button = {
        label: "I'll try again",
        action: () => {
          dialogContent.value.show = false
        },
      }
      dialogContent.value.show = true
    }
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-100 flex items-center justify-center">
    <div
      class="bg-white p-8 rounded-2xl shadow-xl border-t border-gray-100 w-full max-w-md transform transition-all duration-300 hover:shadow-2xl"
    >
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Create Account</h1>

        <p class="text-gray-600">Join us today</p>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <div class="relative">
              <input
                id="name"
                v-model="form.name"
                type="text"
                pattern="^[A-Za-z\s]+$"
                required
                title="Full name must not contain numbers and special characters"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 placeholder-gray-400"
                placeholder="John Doe"
              />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg
                  class="h-5 w-5 text-gray-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                  />
                </svg>
              </div>
            </div>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1"
              >Email Address</label
            >
            <div class="relative">
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 placeholder-gray-400"
                placeholder="you@example.com"
              />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg
                  class="h-5 w-5 text-gray-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                  />
                </svg>
              </div>
            </div>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1"
              >Password</label
            >
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                type="password"
                required
                pattern=".{8,}"
                title="Password must be 6 characters or more"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 placeholder-gray-400"
                placeholder="••••••••"
              />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg
                  class="h-5 w-5 text-gray-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                  />
                </svg>
              </div>
            </div>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1"
              >Confirm Password</label
            >
            <div class="relative">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                required
                pattern=".{8,}"
                title="Password must be 6 characters or more"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 placeholder-gray-400"
                placeholder="••••••••"
              />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg
                  class="h-5 w-5 text-gray-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 13l4 4L19 7"
                  />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <div>
          <button
            type="submit"
            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-[1.01]"
          >
            Register
          </button>
        </div>
      </form>

      <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">
          Already have an account?
          <a href="/" class="font-medium text-blue-600 hover:text-blue-500">Sign in</a>
        </p>
      </div>
    </div>
  </div>

  <dialogComponent v-model="dialogContent.show" v-bind="dialogContent" />
  <loader-overlay :show="loading" />
</template>

<style></style>
