<template>
  <div class="position-absolute top-0 end-0" style="z-index: 10; transform: translate(25%, -50%);">
    <div class="d-flex align-items-center gap-1 px-2 py-05 rounded-pill bg-warning text-white fw-bold shadow-sm small" style="font-size: .6rem; white-space: nowrap; line-height: 1.4;">
      <svg width="10" height="10" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5 -5.5Z" />
        <path d="M15 5l4 4" />
      </svg>
      {{ editors.join(', ') }}
    </div>
  </div>
</template>

<script setup>
defineProps({
  editors: { type: Array, default: () => [] },
})
</script>
