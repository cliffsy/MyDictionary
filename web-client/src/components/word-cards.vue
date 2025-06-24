<script setup>
const items = defineModel()
</script>

<template>
  <div
    v-for="(item, index) in items"
    :key="`item-${index}`"
    class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl m-4 p-4 font-sans"
  >
    <!-- Header Section -->
    <div class="p-6">
      <h1 class="text-2xl font-bold text-gray-800">{{ item.word }}</h1>
      <div class="flex flex-wrap items-center mt-2 text-gray-600 gap-2 italic">
        <template v-for="(phonetic, index) in item.phonetics">
          <div>{{ phonetic.text }}</div>
          <span v-if="index < item.phonetics.length - 1" class="text-xs">●</span>
        </template>
      </div>
      {{ item.partOfSpeech }}
    </div>

    <!-- Meaning Section -->
    <div v-if="item.definitions.length > 0" class="p-6">
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
    <div v-if="item.examples.length > 0" class="p-6">
      <h2 class="text-xl font-semibold text-gray-700 mb-3">Examples</h2>
      <div class="space-y-4">
        <p v-for="example in item.examples" class="italic">{{ example }}</p>
      </div>
    </div>

    <!-- Synonyms Section -->
    <div v-if="item.synonyms.length > 0" class="p-6">
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
</template>
