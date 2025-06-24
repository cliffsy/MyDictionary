<script setup>
import { ref } from 'vue'
import {
  Listbox,
  ListboxLabel,
  ListboxButton,
  ListboxOptions,
  ListboxOption,
} from '@headlessui/vue'

import auth from '@/api/auth.js'

async function logout() {
  await auth.logout()
}
</script>

<template>
  <Listbox v-model="selectedPerson">
    <div class="relative mt-1">
      <ListboxButton
        class="relative cursor-pointer rounded-full sm:text-sm size-12 flex items-center justify-center"
      >
        <img
          src="https://www.gravatar.com/avatar?d=mp&s=32"
          alt="User Avatar"
          class="w-8 h-8 rounded-full"
        />
      </ListboxButton>

      <transition
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <ListboxOptions
          class="absolute -ml-24 max-h-60 w-38 overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none text-xs"
        >
          <ListboxOption v-slot="{ active, selected }" as="template">
            <li
              @click="logout"
              :class="[
                active ? 'bg-blue-100 text-blue-900' : 'text-gray-900',
                'relative cursor-pointer select-none py-2 pl-6',
              ]"
            >
              <span>Logout</span>
            </li>
          </ListboxOption>
        </ListboxOptions>
      </transition>
    </div>
  </Listbox>
</template>
