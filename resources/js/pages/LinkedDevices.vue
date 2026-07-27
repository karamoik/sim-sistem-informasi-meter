<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Linked Devices</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola semua device yang terhubung ke akun anda</p>
      </div>
      <button
        v-if="isMainDevice && sessions.length > 1"
        @click="confirmLogoutAll"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-colors"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
        </svg>
        Logout Semua Device Lain
      </button>
    </div>

    <!-- Not Main Device Notice -->
    <div v-if="!loading && !isMainDevice" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl px-4 py-3 flex items-start gap-3">
      <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
      </svg>
      <div class="text-sm">
        <p class="font-medium text-amber-800 dark:text-amber-300">Bukan Main Device</p>
        <p class="text-amber-700 dark:text-amber-400 mt-0.5">Hanya main device yang bisa logout device lain. Login dari main device untuk mengelola sessions.</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="flex items-center gap-3 text-gray-500 dark:text-gray-400">
        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
        </svg>
        <span class="text-sm">Memuat sessions...</span>
      </div>
    </div>

    <!-- Sessions List -->
    <div v-else class="space-y-3">
      <div
        v-for="session in sessions"
        :key="session.id"
        :class="[
          'bg-white dark:bg-gray-800 rounded-2xl shadow-sm border p-5 transition-all',
          session.is_current
            ? 'border-blue-300 dark:border-blue-700 ring-2 ring-blue-100 dark:ring-blue-900/30'
            : 'border-gray-100 dark:border-gray-700 hover:border-gray-200 dark:hover:border-gray-600'
        ]"
      >
        <div class="flex items-start gap-4">
          <!-- Device Icon -->
          <div :class="[
            'w-12 h-12 rounded-xl flex items-center justify-center shrink-0',
            session.is_current ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-gray-100 dark:bg-gray-700'
          ]">
            <svg v-if="session.device_type === 'desktop'" :class="['w-6 h-6', session.is_current ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400']" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L4.07 7.409A2.25 2.25 0 013 5.496V5.25" />
            </svg>
            <svg v-else-if="session.device_type === 'mobile'" :class="['w-6 h-6', session.is_current ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400']" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
            </svg>
            <svg v-else :class="['w-6 h-6', session.is_current ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400']" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
            </svg>
          </div>

          <!-- Device Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ session.device_name }}</h3>
              <span v-if="session.is_current" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                Device Ini
              </span>
              <span v-if="session.is_main" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                Main Device
              </span>
            </div>

            <!-- Detail Info -->
            <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-1.5 text-xs">
              <div>
                <span class="text-gray-400 dark:text-gray-500">OS</span>
                <p class="font-medium text-gray-700 dark:text-gray-300">{{ session.os }}</p>
              </div>
              <div>
                <span class="text-gray-400 dark:text-gray-500">Browser</span>
                <p class="font-medium text-gray-700 dark:text-gray-300">{{ session.browser }}</p>
              </div>
              <div>
                <span class="text-gray-400 dark:text-gray-500">Layar</span>
                <p class="font-medium text-gray-700 dark:text-gray-300">{{ session.screen || '-' }}</p>
              </div>
              <div>
                <span class="text-gray-400 dark:text-gray-500">IP Address</span>
                <p class="font-medium text-gray-700 dark:text-gray-300">{{ session.ip_address }}</p>
              </div>
              <div>
                <span class="text-gray-400 dark:text-gray-500">Bahasa</span>
                <p class="font-medium text-gray-700 dark:text-gray-300">{{ session.language || '-' }}</p>
              </div>
              <div>
                <span class="text-gray-400 dark:text-gray-500">Timezone</span>
                <p class="font-medium text-gray-700 dark:text-gray-300">{{ session.timezone || '-' }}</p>
              </div>
              <div class="col-span-2">
                <span class="text-gray-400 dark:text-gray-500">Terakhir Aktif</span>
                <p class="font-medium text-gray-700 dark:text-gray-300">{{ formatTimeAgo(session.last_active_at) }}</p>
              </div>
            </div>

            <!-- User Agent (collapsible) -->
            <details class="mt-2 group">
              <summary class="text-xs text-gray-400 dark:text-gray-500 cursor-pointer hover:text-gray-600 dark:hover:text-gray-400 select-none flex items-center gap-1">
                <svg class="w-3 h-3 transition-transform group-open:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
                User Agent
              </summary>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 break-all leading-relaxed font-mono bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2">{{ session.user_agent }}</p>
            </details>
          </div>

          <!-- Actions -->
          <div v-if="!session.is_current && isMainDevice" class="shrink-0">
            <button
              @click="confirmKill(session)"
              class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
              title="Logout device ini"
            >
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!sessions.length" class="text-center py-16">
        <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L4.07 7.409A2.25 2.25 0 013 5.496V5.25" />
        </svg>
        <p class="text-gray-500 dark:text-gray-400">Tidak ada session aktif</p>
      </div>
    </div>

    <!-- Confirm Dialog -->
    <Teleport to="body">
      <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="showConfirm" class="fixed inset-0 z-[10000] flex items-start sm:items-center justify-center p-4 pt-20">
          <div class="absolute inset-0 bg-black/50" @click="showConfirm = false"></div>
          <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ confirmTitle }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ confirmMessage }}</p>
              </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
              <button @click="showConfirm = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition-colors">
                Batal
              </button>
              <button @click="handleConfirm" :disabled="confirmLoading" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors disabled:opacity-50">
                {{ confirmLoading ? 'Memproses...' : 'Ya, Logout' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Toast -->
    <Teleport to="body">
      <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
        <div v-if="toast.show" :class="['fixed bottom-6 right-6 z-[10001] flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-sm font-medium', toast.type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white']">
          <svg v-if="toast.type === 'success'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
          </svg>
          {{ toast.message }}
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

const sessions = ref([])
const loading = ref(true)
const showConfirm = ref(false)
const confirmTitle = ref('')
const confirmMessage = ref('')
const confirmLoading = ref(false)
const confirmAction = ref(null)
const toast = ref({ show: false, message: '', type: 'success' })

const isMainDevice = computed(() => {
    const current = sessions.value.find(s => s.is_current)
    return current?.is_main === true
})

function formatTimeAgo(dt) {
    if (!dt) return 'Baru saja'
    const diff = Math.floor((Date.now() - new Date(dt).getTime()) / 1000)
    if (diff < 60) return 'Baru saja'
    if (diff < 3600) return `${Math.floor(diff / 60)} menit lalu`
    if (diff < 86400) return `${Math.floor(diff / 3600)} jam lalu`
    return `${Math.floor(diff / 86400)} hari lalu`
}

function showToast(message, type = 'success') {
    toast.value = { show: true, message, type }
    setTimeout(() => { toast.value.show = false }, 3000)
}

function confirmKill(session) {
    confirmTitle.value = 'Logout Device?'
    confirmMessage.value = `Device "${session.device_name}" akan dipaksa logout.`
    confirmAction.value = () => killSession(session.id)
    showConfirm.value = true
}

function confirmLogoutAll() {
    confirmTitle.value = 'Logout Semua Device Lain?'
    confirmMessage.value = 'Semua device lain akan dipaksa logout. Hanya device ini yang tetap aktif.'
    confirmAction.value = killAllSessions
    showConfirm.value = true
}

async function handleConfirm() {
    confirmLoading.value = true
    try {
        await confirmAction.value()
        showConfirm.value = false
    } finally {
        confirmLoading.value = false
    }
}

async function killSession(id) {
    try {
        await api.delete(`/user/sessions/${id}`)
        sessions.value = sessions.value.filter(s => s.id !== id)
        showToast('Device berhasil logout')
    } catch (e) {
        showToast(e.response?.data?.message || 'Gagal logout device', 'error')
    }
}

async function killAllSessions() {
    try {
        await api.delete('/user/sessions')
        const current = sessions.value.find(s => s.is_current)
        sessions.value = current ? [current] : []
        showToast('Semua device lain berhasil logout')
    } catch (e) {
        showToast(e.response?.data?.message || 'Gagal logout devices', 'error')
    }
}

async function fetchSessions() {
    loading.value = true
    try {
        const { data } = await api.get('/user/sessions')
        sessions.value = data.data
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
}

onMounted(fetchSessions)
</script>
