<template>
  <div ref="containerRef" class="position-relative">
    <div
      @click="toggle"
      class="form-control d-flex align-items-center justify-content-between cursor-pointer"
      :class="open ? 'border-primary' : ''"
      style="min-height: 38px;"
    >
      <span :class="modelValue ? '' : 'text-secondary'">
        {{ modelValue || placeholder }}
      </span>
      <svg xmlns="http://www.w3.org/2000/svg" class="" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" :class="{ 'rotate-180': open }" style="transition: transform .2s; flex-shrink: 0;">
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M6 9l6 6l6 -6" />
      </svg>
    </div>
    <div v-if="open" class="position-absolute top-100 start-0 w-100 mt-1 bg-body border rounded shadow-lg" style="z-index: 1050;">
      <div class="p-2">
        <input
          ref="searchInput"
          v-model="query"
          type="text"
          placeholder="Cari..."
          class="form-control form-control-sm"
          @keydown.esc="close"
        />
      </div>
      <ul class="list-unstyled mb-0" style="max-height: 12rem; overflow-y: auto;">
        <li
          v-for="option in filtered"
          :key="option"
          @click="select(option)"
          class="px-3 py-2 cursor-pointer"
          :class="option === modelValue
            ? 'bg-primary bg-opacity-10 text-primary fw-medium'
            : 'hover-bg'"
        >
          {{ option }}
        </li>
        <li v-if="filtered.length === 0" class="px-3 py-2 text-secondary small">
          Tidak ditemukan
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'

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

<style scoped>
.cursor-pointer { cursor: pointer; }
.rotate-180 { transform: rotate(180deg); }
.hover-bg:hover { background-color: var(--bs-dropdown-link-hover-bg, rgba(0,0,0,.04)); }
</style>
