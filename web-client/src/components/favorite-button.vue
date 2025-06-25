<script setup>
import { ref, onMounted, watch } from 'vue'

import favorites from '@/api/favorites'

const props = defineProps({
  hideDataOnRemove: {
    type: Boolean,
    default: false,
  },
})
const emit = defineEmits(['update:model-value'])

var model = defineModel({ default: {} })
var isFavorite = ref(false)
var wordDefinition = ref({})

onMounted(() => {
  isFavorite.value = false
  if (model.value?.word) {
    fetchWord(model.value.word)
  }
})

async function fetchWord(word) {
  const { data } = await favorites.show(word)
  wordDefinition.value = data
  isFavorite.value = Boolean(wordDefinition.value)
}

async function toggleFavorite() {
  try {
    isFavorite.value = !isFavorite.value
    if (isFavorite.value) {
      const { data } = await favorites.save(model.value)
      emit('update:model-value', data?.data)
      wordDefinition.value = data?.data
    } else {
      await favorites.remove(wordDefinition.value)
      wordDefinition.value = null
      delete model.value.id

      if (props.hideDataOnRemove) {
        model.value.hidden = true
      }

      emit('update:model-value', model.value)
    }
  } catch (e) {
    isFavorite.value = false
  }
}

watch(
  () => model.value.word,
  (newValue) => {
    fetchWord(newValue)
  },
  {
    deep: true,
  },
)
</script>

<template>
  <button
    @click="toggleFavorite"
    aria-label="Bookmark"
    :class="[
      'text-gray-400 cursor-pointer hover:text-yellow-400 transition',
      isFavorite ? 'text-yellow-400' : '',
    ]"
    title="Save to My Favorites"
  >
    <!-- Filled bookmark (active) -->
    <svg
      v-if="isFavorite"
      xmlns="http://www.w3.org/2000/svg"
      fill="currentColor"
      viewBox="0 0 24 24"
      class="w-6 h-6"
    >
      <path d="M5 3c-.55 0-1 .45-1 1v17l7-3 7 3V4c0-.55-.45-1-1-1H5z" />
    </svg>

    <!-- Outline bookmark (inactive) -->
    <svg
      v-else
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      class="w-6 h-6"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M5 5v16l7-3 7 3V5a2 2 0 00-2-2H7a2 2 0 00-2 2z"
      />
    </svg>
  </button>
</template>

<style scoped></style>
