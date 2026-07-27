<template>
  <div class="max-w-2xl mx-auto space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Ekspor Data</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Unduh data KWH meter dalam format CSV</p>
    </div>

    <!-- KWH Meter Export -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
      <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">Data KWH Meter</h3>
      <form @submit.prevent="exportMeters" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Gardu Induk</label>
            <select v-model="meterFilter.gardu_induk_id" class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-3 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Semua</option>
              <option v-for="gi in garduInduks" :key="gi.id" :value="gi.id">{{ gi.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Status</label>
            <select v-model="meterFilter.status" class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-3 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Semua</option>
              <option value="online">Online</option>
              <option value="offline">Offline</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Dari Tanggal</label>
            <input v-model="meterFilter.from" type="date" class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-3 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Sampai Tanggal</label>
            <input v-model="meterFilter.to" type="date" class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-3 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
        </div>
        <div class="flex justify-end">
          <button type="submit" :disabled="exporting" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors disabled:opacity-50">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            {{ exporting ? 'Mengunduh...' : 'Unduh CSV' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Dashboard Stats Export -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
      <h3 class="text-base font-bold text-gray-900 dark:text-white mb-2">Statistik Dashboard</h3>
      <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Ekspor ringkasan status semua gardu induk</p>
      <div class="flex justify-end">
        <button @click="exportDashboard" :disabled="exportingDash" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition-colors disabled:opacity-50">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
          {{ exportingDash ? 'Mengunduh...' : 'Unduh Stats CSV' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'

const garduInduks = ref([])
const exporting = ref(false)
const exportingDash = ref(false)

const meterFilter = reactive({
    gardu_induk_id: '',
    status: '',
    from: '',
    to: '',
})

function downloadBlob(blob, filename) {
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = filename
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)
}

async function exportMeters() {
    exporting.value = true
    try {
        const params = {}
        if (meterFilter.gardu_induk_id) params.gardu_induk_id = meterFilter.gardu_induk_id
        if (meterFilter.status) params.status = meterFilter.status
        if (meterFilter.from) params.from = meterFilter.from
        if (meterFilter.to) params.to = meterFilter.to

        const { data, headers } = await api.get('/export/meters', { params, responseType: 'blob' })
        const cd = headers['content-disposition'] || ''
        const fn = cd.match(/filename="?(.+?)"?$/)?.[1] || 'kwh_meters.csv'
        downloadBlob(data, fn)
    } catch (e) {
        console.error(e)
    } finally {
        exporting.value = false
    }
}

async function exportDashboard() {
    exportingDash.value = true
    try {
        const { data, headers } = await api.get('/export/dashboard', { responseType: 'blob' })
        const cd = headers['content-disposition'] || ''
        const fn = cd.match(/filename="?(.+?)"?$/)?.[1] || 'dashboard_stats.csv'
        downloadBlob(data, fn)
    } catch (e) {
        console.error(e)
    } finally {
        exportingDash.value = false
    }
}

async function fetchGarduInduks() {
    try {
        const { data } = await api.get('/gardu-induks/all')
        garduInduks.value = data.data?.data || data.data || []
    } catch (e) { console.error(e) }
}

onMounted(fetchGarduInduks)
</script>
