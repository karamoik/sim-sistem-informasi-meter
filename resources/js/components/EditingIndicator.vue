<template>
  <Transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div v-if="visible" class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 border small" :class="warningClasses">
      <svg class="icon text-warning flex-shrink-0" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M8 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
      </svg>
      <span class="fw-medium">{{ message }}</span>
      <div v-if="editors.length > 0" class="d-flex ms-1">
        <div v-for="editor in editors" :key="editor.user_id" class="rounded-circle bg-warning text-white d-inline-flex align-items-center justify-content-center fw-bold" style="width:1.5rem;height:1.5rem;font-size:.65rem;margin-left:-.4rem;border:2px solid var(--bs-body-bg);" :title="editor.user_name">
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
    'bg-warning bg-opacity-10 border-warning text-warning',
])
</script>
