<template>
  <div class="max-w-2xl mx-auto space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pintasan Keyboard</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pintasan keyboard untuk navigasi cepat</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
      <div class="space-y-4">
        <div v-for="(group, gi) in groups" :key="gi">
          <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">{{ group.label }}</h3>
          <div class="space-y-2">
            <div v-for="shortcut in group.items" :key="shortcut.keys.join('+')" class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-700/50 last:border-0">
              <span class="text-sm text-gray-700 dark:text-gray-300">{{ shortcut.desc }}</span>
              <div class="flex items-center gap-1">
                <kbd v-for="key in shortcut.keys" :key="key" class="px-2 py-1 text-xs font-mono bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-400 shadow-sm">{{ key }}</kbd>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
      <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
        <div class="text-sm text-blue-700 dark:text-blue-300">
          <p class="font-medium">Tips</p>
          <p class="mt-0.5">Pintasan keyboard hanya aktif saat tidak sedang mengetik di input field atau textarea.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const groups = [
    {
        label: 'Navigasi',
        items: [
            { keys: ['Ctrl', 'D'], desc: 'Buka Dashboard' },
            { keys: ['Ctrl', 'M'], desc: 'Buka KWH Meter' },
            { keys: ['Ctrl', 'L'], desc: 'Buka Linked Devices' },
            { keys: ['Ctrl', ','], desc: 'Buka Pengaturan' },
        ],
    },
    {
        label: 'Aksi',
        items: [
            { keys: ['Ctrl', 'K'], desc: 'Cari / Quick Search' },
            { keys: ['Ctrl', 'E'], desc: 'Ekspor Data' },
            { keys: ['Ctrl', '/'], desc: 'Tampilkan Pintasan Keyboard' },
            { keys: ['Esc'], desc: 'Tutup Modal / Batal' },
        ],
    },
    {
        label: 'Dark Mode',
        items: [
            { keys: ['Ctrl', 'Shift', 'D'], desc: 'Toggle Dark/Light Mode' },
        ],
    },
]
</script>
