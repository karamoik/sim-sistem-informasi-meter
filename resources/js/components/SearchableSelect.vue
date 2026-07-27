<template>
  <div class="relative" ref="containerRef">
    <div
      @click="toggle"
      class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white cursor-pointer flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
    >
      <span :class="modelValue ? '' : 'text-gray-400 dark:text-gray-500'">
        {{ modelValue || placeholder }}
      </span>
      <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
      </svg>
    </div>
    <div v-if="open" class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg overflow-hidden">
      <div class="p-2">
        <input
          ref="searchInput"
          v-model="query"
          type="text"
          placeholder="Cari..."
          class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          @keydown.esc="close"
        />
      </div>
      <ul class="max-h-48 overflow-y-auto pb-1">
        <li
          v-for="option in filtered"
          :key="option"
          @click="select(option)"
          class="px-3 py-2 text-sm cursor-pointer transition-colors"
          :class="option === modelValue
            ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium'
            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
        >
          {{ option }}
        </li>
        <li v-if="filtered.length === 0" class="px-3 py-2 text-sm text-gray-400 dark:text-gray-500">
          Tidak ditemukan
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  options: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Pilih...' },
})

const emit = defineEmits(['update:modelValue'])

const open = ref(false)
const query = ref('')
const containerRef = ref(null)
const searchInput = ref(null)

const filtered = computed(() => {
  if (!query.value) return props.options
  const q = query.value.toLowerCase()
  return props.options.filter(o => o.toLowerCase().includes(q))
})

function toggle() {
  open.value = !open.value
  if (open.value) {
    query.value = ''
    nextTick(() => searchInput.value?.focus())
  }
}

function select(option) {
  emit('update:modelValue', option)
  open.value = false
}

function close() {
  open.value = false
}

function onClickOutside(e) {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    close()
  }
}

onMounted(() => document.addEventListener('click', onClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', onClickOutside))
</script>
