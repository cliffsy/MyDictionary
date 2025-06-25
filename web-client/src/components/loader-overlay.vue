<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  text: {
    type: String,
    default: '',
  },
  color: {
    type: String,
    default: 'indigo',
  },
  showProgress: {
    type: Boolean,
    default: false,
  },
  progress: {
    type: Number,
    default: 30,
  },
})

const isVisible = ref(false)
const isMounted = ref(false)

watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      isMounted.value = true
      setTimeout(() => {
        isVisible.value = true
      }, 10)
    } else {
      isVisible.value = false
      setTimeout(() => {
        if (!props.show) {
          isMounted.value = false
        }
      }, 300) // Match this with your transition duration
    }
  },
  { immediate: true },
)
</script>

<template>
  <Transition name="fade">
    <div
      v-if="isMounted"
      class="fixed inset-0 z-50 bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center"
      :class="{
        'opacity-100': isVisible,
        'opacity-0': !isVisible,
        'pointer-events-auto': isVisible,
        'pointer-events-none': !isVisible,
      }"
      style="
        transition:
          opacity 300ms ease-out,
          backdrop-filter 300ms ease-out;
      "
    >
      <!-- Main loader container -->
      <div class="text-center">
        <!-- Bouncing dots -->
        <div class="flex justify-center space-x-2 mb-4">
          <div
            v-for="i in 3"
            :key="i"
            class="w-3 h-3 rounded-full bg-indigo-400 animate-bounce"
            :style="`animation-delay: ${i * 0.15}s`"
          ></div>
        </div>

        <!-- Text -->
        <p class="text-indigo-600 font-medium text-lg">
          {{ text || 'Loading...' }}
        </p>

        <!-- Optional progress bar -->
        <div
          v-if="showProgress"
          class="w-48 h-1.5 bg-indigo-100 rounded-full overflow-hidden mt-4 mx-auto"
        >
          <div class="h-full bg-indigo-400 animate-progress" :style="`width: ${progress}%`"></div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style>
@keyframes bounce {
  0%,
  100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-8px);
  }
}

@keyframes progress {
  0% {
    width: 0%;
  }
  100% {
    width: 100%;
  }
}

.animate-bounce {
  animation: bounce 0.8s infinite ease-in-out;
}

.animate-progress {
  animation: progress 2s infinite ease-in-out;
}

/* Transition effects */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
