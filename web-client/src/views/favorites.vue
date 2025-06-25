<script setup>
import { ref, onMounted } from 'vue'

import userMenu from '@/components/user-menu.vue'
import wordCards from '@/components/word-cards.vue'

import favorites from '@/api/favorites.js'

var words = ref([])

onMounted(async () => {
  const { data } = await favorites.list()
  words.value = data
})
</script>

<template>
  <div :class="[words.length > 0 ? '' : 'h-screen', 'bg-slate-100']">
    <div
      class="fixed w-full flex justify-between items-center gap-8 px-6 py-1"
      :class="['bg-glass']"
    >
      <div class="flex items-center">
        <router-link
          to="/"
          class="font-[Montserrat] w-50 text-xl text-slate-700 font-semibold tracking-tight transition-all cursor-pointer mr-4"
        >
          My Dictionary
        </router-link>
      </div>

      <div class="flex items-center gap-6">
        <router-link
          to="/"
          class="text-sm font-medium text-slate-600 hover:text-slate-900 transition"
        >
          Home
        </router-link>
        <user-menu />
      </div>
    </div>
    <div
      class="min-h-screen h-full flex flex-col items-center justify-center text-gray-800 px-4 pb-20"
    >
      <div v-if="words.length > 0" class="mt-18">
        <span class="text-2xl">My Favorites</span>
        <word-cards v-model="words" />
      </div>

      <!-- Footer -->
      <footer class="mt-16 text-sm text-gray-500">
        <p>&copy; 2025 My Dictionary</p>
      </footer>
    </div>
  </div>
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
