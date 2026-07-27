<template>
  <div class="absolute -top-1 -right-1 z-10">
    <div class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-500 text-white text-[10px] font-bold shadow-sm whitespace-nowrap">
      <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
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
