<script setup>
import { ref, watch } from 'vue'
import favoriteButton from './favorite-button.vue'

import favorites from '@/api/favorites'

const props = defineProps({
  autoExpand: {
    type: Boolean,
    default: false,
  },
  hideDataOnRemove: {
    type: Boolean,
    default: false,
  },
  hasNotes: {
    type: Boolean,
    default: false,
  },
})

const items = defineModel()
const expandedItems = ref([])
const editingNotesIndex = ref(null)

function toggleExpand(index) {
  if (expandedItems.value.includes(index)) {
    expandedItems.value = expandedItems.value.filter((i) => i !== index)
  } else {
    expandedItems.value.push(index)
  }
}

function startEditingNotes(index) {
  editingNotesIndex.value = index
}

async function saveNotes(item) {
  editingNotesIndex.value = null
  await favorites.save(item)
}

// Initialize notes if they don't exist
watch(
  items,
  (newItems) => {
    newItems.forEach((item) => {
      if (!item.notes) {
        item.notes = ''
      }
    })
  },
  { immediate: true, deep: true },
)

// Watch for changes in autoExpand prop
watch(
  () => props.autoExpand,
  (newVal) => {
    if (newVal) {
      expandedItems.value = items.value.map((_, index) => index)
    } else {
      expandedItems.value = []
    }
  },
  { immediate: true },
)
</script>

<template>
  <template v-for="(item, index) in items" :key="`item-${index}`">
    <div
      v-if="!item.hidden"
      class="max-w-md mx-auto min-w-2xl bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl m-4 p-4 font-sans"
    >
      <!-- Header Section -->
      <div class="p-6">
        <div class="flex justify-between items-center">
          <h1 class="text-2xl font-bold text-gray-800">{{ item.word }}</h1>
          <favorite-button v-model="items[index]" :hide-data-on-remove="hideDataOnRemove" />
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

      <!-- Notes Section - Always visible when hasNotes is true -->
      <div v-if="hasNotes" class="px-6 pb-4 pt-2 border-t border-gray-200">
        <h3 class="text-lg font-medium text-gray-700 mb-2">My Notes</h3>
        <div v-if="editingNotesIndex === index" class="space-y-2">
          <textarea
            v-model="item.notes"
            class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500"
            rows="3"
            placeholder="Add your personal notes here..."
            autofocus
          ></textarea>
          <button
            @click="saveNotes(item)"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Save Notes
          </button>
        </div>
        <div v-else>
          <p v-if="item.notes" class="text-gray-700 whitespace-pre-wrap">{{ item.notes }}</p>
          <p v-else class="text-gray-400 italic">No notes yet</p>
          <button
            @click="startEditingNotes(index)"
            class="mt-2 text-sm text-blue-600 hover:text-blue-800 focus:outline-none"
          >
            {{ item.notes ? 'Edit Notes' : 'Add Notes' }}
          </button>
        </div>
      </div>
    </div>
  </template>
</template>
