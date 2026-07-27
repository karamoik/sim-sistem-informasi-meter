<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Riwayat Aktivitas</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Catatan semua aktivitas akun anda</p>
      </div>
      <div class="flex items-center gap-3">
        <select v-model="filterType" class="text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Semua Tipe</option>
          <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
        </select>
        <button v-if="logs.length" @click="confirmClear" class="text-sm text-red-600 hover:text-red-700 dark:text-red-400 font-medium">Hapus Semua</button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <svg class="animate-spin w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
    </div>

    <!-- Logs -->
    <div v-else class="space-y-2">
      <div v-for="log in logs" :key="log.id" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4 flex items-start gap-3">
        <div :class="['w-9 h-9 rounded-lg flex items-center justify-center shrink-0', typeStyle[log.type]?.bg || 'bg-gray-100 dark:bg-gray-700']">
          <svg :class="['w-4 h-4', typeStyle[log.type]?.text || 'text-gray-500']" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" :d="typeStyle[log.type]?.icon || 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'"/></svg>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm text-gray-900 dark:text-white">{{ log.description }}</p>
          <div class="flex items-center gap-3 mt-1 text-xs text-gray-400 dark:text-gray-500">
            <span>{{ formatTime(log.created_at) }}</span>
            <span v-if="log.ip_address" class="flex items-center gap-1">
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
              {{ log.ip_address }}
            </span>
          </div>
        </div>
        <span :class="['text-xs px-2 py-0.5 rounded-full font-medium shrink-0', typeStyle[log.type]?.badge || 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400']">{{ log.type }}</span>
      </div>

      <div v-if="!logs.length" class="text-center py-16 text-gray-400 dark:text-gray-500 text-sm">
        Belum ada aktivitas
      </div>

      <!-- Pagination -->
      <div v-if="logs.length" class="flex justify-center gap-2 pt-4">
        <button @click="page--" :disabled="page <= 1" class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600 disabled:opacity-40 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Prev</button>
        <span class="px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400">Hal {{ page }}</span>
        <button @click="page++" :disabled="!hasMore" class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600 disabled:opacity-40 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Next</button>
      </div>
    </div>

    <ConfirmDialog ref="confirmDialogRef" />
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import api from '@/services/api'
import ConfirmDialog from '@/components/ConfirmDialog.vue'

const confirmDialogRef = ref(null)

const logs = ref([])
const types = ref([])
const loading = ref(true)
const page = ref(1)
const hasMore = ref(false)
const filterType = ref('')

const typeStyle = {
    login: { bg: 'bg-blue-100 dark:bg-blue-900/30', text: 'text-blue-600 dark:text-blue-400', badge: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400', icon: 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9' },
    logout: { bg: 'bg-gray-100 dark:bg-gray-700', text: 'text-gray-600 dark:text-gray-400', badge: 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400', icon: 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9' },
    password_changed: { bg: 'bg-amber-100 dark:bg-amber-900/30', text: 'text-amber-600 dark:text-amber-400', badge: 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400', icon: 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z' },
    profile_updated: { bg: 'bg-green-100 dark:bg-green-900/30', text: 'text-green-600 dark:text-green-400', badge: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400', icon: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z' },
    create: { bg: 'bg-emerald-100 dark:bg-emerald-900/30', text: 'text-emerald-600 dark:text-emerald-400', badge: 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400', icon: 'M12 4.5v15m7.5-7.5h-15' },
    update: { bg: 'bg-blue-100 dark:bg-blue-900/30', text: 'text-blue-600 dark:text-blue-400', badge: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400', icon: 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10' },
    delete: { bg: 'bg-red-100 dark:bg-red-900/30', text: 'text-red-600 dark:text-red-400', badge: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400', icon: 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0' },
}

function formatTime(dt) {
    return new Date(dt).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

async function fetchLogs() {
    loading.value = true
    try {
        const params = { page: page.value, per_page: 20 }
        if (filterType.value) params.type = filterType.value
        const { data } = await api.get('/activity-logs', { params })
        logs.value = data.data.data
        hasMore.value = data.data.current_page < data.data.last_page
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
}

async function fetchTypes() {
    try {
        const { data } = await api.get('/activity-logs/types')
        types.value = data.data
    } catch (e) { console.error(e) }
}

async function confirmClear() {
    const ok = await confirmDialogRef.value.show({
        title: 'Hapus Semua Riwayat',
        message: 'Hapus semua riwayat aktivitas? Tindakan ini tidak dapat dibatalkan.',
        type: 'danger',
        confirmText: 'Hapus Semua',
    })
    if (ok) {
        api.delete('/activity-logs').then(() => { logs.value = [] })
    }
}

watch([page, filterType], fetchLogs)
onMounted(() => { fetchTypes(); fetchLogs() })
</script>
