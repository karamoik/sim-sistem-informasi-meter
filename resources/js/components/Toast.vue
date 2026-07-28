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
      <div v-if="show" class="position-fixed bottom-0 end-0 p-3" style="z-index: 10001;">
        <div :class="['d-flex align-items-center gap-3 px-4 py-3 rounded-3 shadow-lg small fw-medium', typeClasses]">
          <svg v-if="type === 'success'" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M5 12l5 5l10 -10" />
          </svg>
          <svg v-else class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M12 9v4" /><path d="M10.91 17.9l-.062 .032a1 1 0 0 0 .555 .866l.392 .16c.368 .146 .753 .208 1.138 .182a2 2 0 0 0 1.263 -.606l.2 -.286" />
            <path d="M12 9v-4" /><circle cx="12" cy="17" r="1" fill="currentColor" />
          </svg>
          {{ message }}
        </div>
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
    ? 'bg-success text-white'
    : 'bg-danger text-white'
)

function toast(msg, toastType = 'success') {
  message.value = msg
  type.value = toastType
  show.value = true
  setTimeout(() => { show.value = false }, 3000)
}

defineExpose({ toast })
</script>
