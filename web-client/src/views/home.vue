<script setup>
import { ref } from 'vue'

import userMenu from '@/components/user-menu.vue'
import wordCards from '@/components/word-cards.vue'
import dialogComponent from '@/components/dialog.vue'
import loaderOverlay from '@/components/loader-overlay.vue'

import dictionary from '@/api/dictionary.js'

const query = ref('')
var words = ref([])

var dialogContent = ref({
  show: false,
})

var loading = ref(false)

async function search() {
  try {
    if (!query.value) return
    loading.value = true
    const { data } = await dictionary.search(query.value)
    words.value = data
    loading.value = false
  } catch (e) {
    loading.value = false
    var response = e.response?.data
    if (response?.statusCode == 404) {
      dialogContent.value.title = response.message
      dialogContent.value.body = 'Try checking your spelling or search for a different word.'
      dialogContent.value.button = {
        label: 'Okay',
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
  <div :class="[words.length > 0 ? '' : 'h-screen', 'bg-slate-100']">
    <div
      class="fixed w-full flex justify-between items-center gap-8 px-6 py-1"
      :class="[words.length > 0 ? 'bg-glass' : '']"
    >
      <div class="flex items-center">
        <h1
          v-if="words.length > 0"
          @click="words = []"
          class="font-[Montserrat] w-50 text-xl text-slate-700 font-semibold tracking-tight transition-all cursor-pointer mr-4"
        >
          My Dictionary
        </h1>

        <div v-if="words.length > 0" class="w-full max-w-xl">
          <div
            class="flex items-center bg-white border border-gray-100 rounded-full px-6 py-1 shadow-md hover:shadow-lg transition-shadow duration-300 focus-within:shadow-xl"
          >
            <img src="@/assets/icons/search.svg" width="28px" class="mr-2" />
            <input
              v-model="query"
              @keyup.enter="search"
              type="text"
              placeholder="Search a word..."
              class="flex-grow outline-none text-lg bg-transparent"
            />
          </div>
        </div>
      </div>

      <div class="flex items-center gap-6">
        <router-link
          to="/favorites"
          class="text-sm font-medium text-slate-600 hover:text-slate-900 transition"
        >
          My Favorites
        </router-link>
        <user-menu />
      </div>
    </div>
    <div
      class="min-h-screen h-full flex flex-col items-center justify-center text-gray-800 px-4 pb-20"
    >
      <!-- Logo / App Name -->
      <h1
        v-if="words.length == 0"
        class="font-[Montserrat] text-5xl text-slate-700 font-semibold mb-10 tracking-tight transition-all cursor-default"
      >
        My Dictionary
      </h1>

      <!-- Search Bar -->
      <div v-if="words.length == 0" class="w-full max-w-xl">
        <div
          class="flex items-center bg-white border border-gray-100 rounded-full px-6 py-3 shadow-md hover:shadow-lg transition-shadow duration-300 focus-within:shadow-xl"
        >
          <img src="@/assets/icons/search.svg" width="28px" class="mr-2" />
          <input
            v-model="query"
            @keyup.enter="search"
            type="text"
            autofocus
            placeholder="Search a word..."
            class="flex-grow outline-none text-lg bg-transparent"
          />
        </div>
      </div>

      <div v-if="words.length > 0" class="mt-18">
        <word-cards v-model="words" auto-expand />
      </div>

      <!-- Footer -->
      <footer class="mt-16 text-sm text-gray-500">
        <p>&copy; 2025 My Dictionary</p>
      </footer>
    </div>
  </div>
  <dialogComponent v-model="dialogContent.show" v-bind="dialogContent" />
  <loader-overlay :show="loading" />
</template>

<style scoped>
body {
  font-family: 'Inter', sans-serif;
}

.bg-glass {
  background: rgba(255, 255, 255, 0.32);
  box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(4.4px);
  -webkit-backdrop-filter: blur(4.4px);
}
</style>
