<template>
  <Teleport to="body">
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="visible" class="fixed inset-0 z-[10002] flex items-start sm:items-center justify-center p-4 pt-20" @keydown.escape="cancel">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="cancel"></div>
        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95 translate-y-2" enter-to-class="opacity-100 scale-100 translate-y-0" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100 translate-y-0" leave-to-class="opacity-0 scale-95 translate-y-2">
          <div v-if="visible" class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700/50 overflow-hidden">
            <div class="px-6 pt-6 pb-2">
              <div class="flex items-start gap-4">
                <div :class="['w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0', iconBg]">
                  <svg v-if="type === 'danger'" :class="['w-5 h-5', iconColor]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                  <svg v-else-if="type === 'warning'" :class="['w-5 h-5', iconColor]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                  <svg v-else :class="['w-5 h-5', iconColor]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                  <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
                  <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ message }}</p>
                </div>
              </div>
            </div>
            <div class="flex items-center justify-end gap-3 px-6 py-3 bg-gray-50 dark:bg-gray-750 border-t border-gray-100 dark:border-gray-700/50">
              <button @click="cancel" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 dark:focus:ring-gray-600">
                {{ cancelText }}
              </button>
              <button @click="confirm" :class="['px-4 py-2 text-sm font-medium text-white rounded-xl transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2', confirmBtnClass]">
                {{ confirmText }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'

const visible = ref(false)
const title = ref('')
const message = ref('')
const type = ref('danger')
const confirmText = ref('Konfirmasi')
const cancelText = ref('Batal')
let resolvePromise = null

const typeConfig = {
  danger: {
    iconBg: 'bg-red-100 dark:bg-red-900/30',
    iconColor: 'text-red-600 dark:text-red-400',
    confirmBtn: 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
  },
  warning: {
    iconBg: 'bg-amber-100 dark:bg-amber-900/30',
    iconColor: 'text-amber-600 dark:text-amber-400',
    confirmBtn: 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500',
  },
  info: {
    iconBg: 'bg-blue-100 dark:bg-blue-900/30',
    iconColor: 'text-blue-600 dark:text-blue-400',
    confirmBtn: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
  },
}

const iconBg = computed(() => typeConfig[type.value]?.iconBg || typeConfig.danger.iconBg)
const iconColor = computed(() => typeConfig[type.value]?.iconColor || typeConfig.danger.iconColor)
const confirmBtnClass = computed(() => typeConfig[type.value]?.confirmBtn || typeConfig.danger.confirmBtn)

function handleKeydown(e) {
  if (e.key === 'Escape' && visible.value) cancel()
}

onMounted(() => document.addEventListener('keydown', handleKeydown))
onUnmounted(() => document.removeEventListener('keydown', handleKeydown))

function confirm() {
  visible.value = false
  resolvePromise?.(true)
}

function cancel() {
  visible.value = false
  resolvePromise?.(false)
}

function show(opts = {}) {
  title.value = opts.title || 'Konfirmasi'
  message.value = opts.message || ''
  type.value = opts.type || 'danger'
  confirmText.value = opts.confirmText || 'Konfirmasi'
  cancelText.value = opts.cancelText || 'Batal'
  visible.value = true
  return new Promise((resolve) => { resolvePromise = resolve })
}

defineExpose({ show })
</script>
