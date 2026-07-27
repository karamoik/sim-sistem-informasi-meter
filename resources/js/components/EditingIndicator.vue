<template>
  <Transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="opacity-0 -translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 -translate-y-2"
  >
    <div v-if="visible" class="flex items-center gap-2 px-4 py-2.5 rounded-xl border text-sm" :class="warningClasses">
      <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
      </svg>
      <span class="font-medium">{{ message }}</span>
      <div v-if="editors.length > 0" class="flex -space-x-2 ml-1">
        <div v-for="editor in editors" :key="editor.user_id" class="w-6 h-6 rounded-full bg-amber-500 text-white text-xs flex items-center justify-center font-bold ring-2 ring-white dark:ring-gray-800" :title="editor.user_name">
          {{ editor.user_name[0].toUpperCase() }}
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    visible: { type: Boolean, default: false },
    message: { type: String, default: '' },
    editors: { type: Array, default: () => [] },
})

const warningClasses = computed(() => [
    'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400',
])
</script>
