<script setup>
import { ref, watch } from 'vue'
import favoriteButton from './favorite-button.vue'

const props = defineProps({
  autoExpand: {
    type: Boolean,
    default: false,
  },
})

const items = defineModel()
const expandedItems = ref([])

// Watch for changes in autoExpand prop
watch(
  () => props.autoExpand,
  (newVal) => {
    if (newVal) {
      // Expand all items
      expandedItems.value = items.value.map((_, index) => index)
    } else {
      // Collapse all items
      expandedItems.value = []
    }
  },
  { immediate: true },
)

const toggleExpand = (index) => {
  if (expandedItems.value.includes(index)) {
    expandedItems.value = expandedItems.value.filter((i) => i !== index)
  } else {
    expandedItems.value.push(index)
  }
}
</script>

<template>
  <div
    v-for="(item, index) in items"
    :key="`item-${index}`"
    class="max-w-md mx-auto min-w-2xl bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl m-4 p-4 font-sans"
  >
    <!-- Header Section -->
    <div class="p-6">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">{{ item.word }}</h1>
        <favorite-button v-model="items[index]" />
      </div>
      <div class="flex flex-wrap items-center mt-2 text-gray-600 gap-2 italic">
        <template v-for="(phonetic, pIndex) in item.phonetics">
          <div>{{ phonetic.text }}</div>
          <span v-if="pIndex < item.phonetics.length - 1" class="text-xs">●</span>
        </template>
      </div>
      <div class="text-gray-600 mt-1">{{ item.partOfSpeech }}</div>

      <!-- Show More/Less Button - Bottom Right -->
      <div v-if="!autoExpand" class="flex justify-end">
        <button
          @click.stop="toggleExpand(index)"
          class="text-sm cursor-pointer text-blue-600 hover:text-blue-800 focus:outline-none"
        >
          {{ expandedItems.includes(index) ? 'Show less' : 'Show more' }}
        </button>
      </div>
    </div>

    <!-- Collapsible Content -->
    <div v-if="autoExpand || expandedItems.includes(index)" class="px-6 pb-6 -mt-4">
      <!-- Meaning Section -->
      <div v-if="item.definitions.length > 0" class="mt-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-3">Meaning</h2>
        <div>
          <span
            v-for="definition in item.definitions"
            class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-medium m-1"
          >
            {{ definition }}
          </span>
        </div>
      </div>

      <!-- Examples Section -->
      <div v-if="item.examples.length > 0" class="mt-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-3">Examples</h2>
        <div class="space-y-4">
          <p v-for="example in item.examples" class="italic">{{ example }}</p>
        </div>
      </div>

      <!-- Synonyms Section -->
      <div v-if="item.synonyms.length > 0" class="mt-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-3">Synonyms</h2>
        <div>
          <span
            v-for="syno in item.synonyms"
            class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-medium m-1"
          >
            {{ syno }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
