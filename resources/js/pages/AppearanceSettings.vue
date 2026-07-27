<template>
  <div class="max-w-2xl mx-auto space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengaturan Tampilan</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sesuaikan tampilan aplikasi sesuai preferensi anda</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 space-y-6">
      <div v-if="successMsg" class="flex items-center gap-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-xl text-sm">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ successMsg }}
      </div>

      <!-- Theme -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Tema</label>
        <div class="grid grid-cols-3 gap-3">
          <button v-for="t in themes" :key="t.value" @click="form.theme = t.value" :class="['flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all', form.theme === t.value ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500']">
            <svg v-if="t.value === 'light'" class="w-6 h-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
            <svg v-else-if="t.value === 'dark'" class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
            <svg v-else class="w-6 h-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/></svg>
            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ t.label }}</span>
          </button>
        </div>
      </div>

      <!-- Language -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Bahasa</label>
        <select v-model="form.language" class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-3 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="id">Bahasa Indonesia</option>
          <option value="en">English</option>
        </select>
      </div>

      <!-- Timezone -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Timezone</label>
        <select v-model="form.timezone" class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-3 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz }}</option>
        </select>
      </div>

      <!-- Compact Mode -->
      <div class="flex items-center justify-between py-2">
        <div>
          <p class="text-sm font-medium text-gray-900 dark:text-white">Mode Kompak</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Tampilkan lebih banyak konten dalam satu layar</p>
        </div>
        <button @click="form.compact_mode = !form.compact_mode" :class="['relative w-11 h-6 rounded-full transition-colors', form.compact_mode ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600']">
          <span :class="['absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform', form.compact_mode ? 'left-5.5' : 'left-0.5']"></span>
        </button>
      </div>

      <div class="flex justify-end pt-2">
        <button @click="save" :disabled="saving" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors disabled:opacity-50">
          {{ saving ? 'Menyimpan...' : 'Simpan' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'

const saving = ref(false)
const successMsg = ref('')

const form = reactive({
    theme: 'system',
    language: 'id',
    timezone: 'Asia/Jakarta',
    compact_mode: false,
})

const themes = [
    { value: 'light', label: 'Light' },
    { value: 'dark', label: 'Dark' },
    { value: 'system', label: 'System' },
]

const timezones = [
    'Asia/Jakarta',
    'Asia/Makassar',
    'Asia/Jayapura',
    'Asia/Singapore',
    'UTC',
]

async function fetchAppearance() {
    try {
        const { data } = await api.get('/user/settings/appearance')
        Object.assign(form, data.data)
    } catch (e) { console.error(e) }
}

async function save() {
    saving.value = true
    successMsg.value = ''
    try {
        await api.put('/user/settings/appearance', form)
        successMsg.value = 'Pengaturan tampilan berhasil disimpan'
        setTimeout(() => { successMsg.value = '' }, 3000)
    } catch (e) { console.error(e) }
    finally { saving.value = false }
}

onMounted(fetchAppearance)
</script>
