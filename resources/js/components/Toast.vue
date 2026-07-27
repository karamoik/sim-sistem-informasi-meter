<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 translate-y-4"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-4"
    >
      <div v-if="show" :class="['fixed bottom-6 right-6 z-[10001] flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-sm font-medium', typeClasses]">
        <svg v-if="type === 'success'" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <svg v-else class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
        </svg>
        {{ message }}
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'

const show = ref(false)
const message = ref('')
const type = ref('success')

const typeClasses = computed(() =>
  type.value === 'success'
    ? 'bg-green-600 text-white'
    : 'bg-red-600 text-white'
)

function toast(msg, toastType = 'success') {
  message.value = msg
  type.value = toastType
  show.value = true
  setTimeout(() => { show.value = false }, 3000)
}

defineExpose({ toast })
</script>
