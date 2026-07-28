<template>
  <div>
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
      <div>
        <h1 class="page-title mb-1">Linked Devices</h1>
        <p class="text-muted small">Kelola semua device yang terhubung ke akun anda</p>
      </div>
      <button
        v-if="isMainDevice && sessions.length > 1"
        @click="confirmLogoutAll"
        class="btn btn-danger"
      >
        Logout Semua Device Lain
      </button>
    </div>

    <!-- Not Main Device Notice -->
    <div v-if="!loading && !isMainDevice" class="alert alert-warning d-flex align-items-start gap-3">
      <div>
        <strong>Bukan Main Device</strong>
        <p class="mb-0">Hanya main device yang bisa logout device lain. Login dari main device untuk mengelola sessions.</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="d-flex align-items-center justify-content-center py-20">
      <div class="d-flex align-items-center gap-3 text-muted">
        <div class="spinner-border spinner-border-sm" role="status"></div>
        <span class="small">Memuat sessions...</span>
      </div>
    </div>

    <!-- Sessions List -->
    <div v-else>
      <div
        v-for="session in sessions"
        :key="session.id"
        class="card mb-3"
        :class="session.is_current ? 'border-primary shadow-sm' : ''"
      >
        <div class="card-body">
          <div class="d-flex align-items-start gap-4">
            <!-- Device Icon -->
            <div class="d-flex align-items-center justify-content-center rounded-3" :class="[
              'width-48 height-48',
              session.is_current ? 'bg-primary-lt' : 'bg-light'
            ]">
              <span v-if="session.device_type === 'desktop'" class="fs-1" :class="session.is_current ? 'text-primary' : 'text-muted'">Desktop</span>
              <span v-else-if="session.device_type === 'mobile'" class="fs-1" :class="session.is_current ? 'text-primary' : 'text-muted'">Mobile</span>
              <span v-else class="fs-1" :class="session.is_current ? 'text-primary' : 'text-muted'">Other</span>
            </div>

            <!-- Device Info -->
            <div class="flex-fill min-w-0">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <h3 class="fw-semibold mb-0">{{ session.device_name }}</h3>
                <span v-if="session.is_current" class="badge bg-primary">Device Ini</span>
                <span v-if="session.is_main" class="badge bg-warning text-dark">Main Device</span>
              </div>

              <!-- Detail Info -->
              <div class="row mt-2 g-2">
                <div class="col-6 col-sm-3">
                  <span class="text-muted small">OS</span>
                  <p class="fw-medium mb-0">{{ session.os }}</p>
                </div>
                <div class="col-6 col-sm-3">
                  <span class="text-muted small">Browser</span>
                  <p class="fw-medium mb-0">{{ session.browser }}</p>
                </div>
                <div class="col-6 col-sm-3">
                  <span class="text-muted small">Layar</span>
                  <p class="fw-medium mb-0">{{ session.screen || '-' }}</p>
                </div>
                <div class="col-6 col-sm-3">
                  <span class="text-muted small">IP Address</span>
                  <p class="fw-medium mb-0">{{ session.ip_address }}</p>
                </div>
                <div class="col-6 col-sm-3">
                  <span class="text-muted small">Bahasa</span>
                  <p class="fw-medium mb-0">{{ session.language || '-' }}</p>
                </div>
                <div class="col-6 col-sm-3">
                  <span class="text-muted small">Timezone</span>
                  <p class="fw-medium mb-0">{{ session.timezone || '-' }}</p>
                </div>
                <div class="col-12 col-sm-6">
                  <span class="text-muted small">Terakhir Aktif</span>
                  <p class="fw-medium mb-0">{{ formatTimeAgo(session.last_active_at) }}</p>
                </div>
              </div>

              <!-- User Agent (collapsible) -->
              <details class="mt-2">
                <summary class="small text-muted cursor-pointer select-none">
                  User Agent
                </summary>
                <p class="mt-1 small text-muted break-all font-monospace bg-light rounded p-2 mb-0">{{ session.user_agent }}</p>
              </details>
            </div>

            <!-- Actions -->
            <div v-if="!session.is_current && isMainDevice" class="shrink-0">
              <button
                @click="confirmKill(session)"
                class="btn btn-outline-danger btn-sm"
                title="Logout device ini"
              >
                Logout
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!sessions.length" class="text-center py-16">
        <p class="text-muted">Tidak ada session aktif</p>
      </div>
    </div>

    <!-- Confirm Dialog -->
    <Teleport to="body">
      <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="showConfirm" class="fixed inset-0 z-[10000] d-flex align-items-start align-items-sm-center justify-content-center p-4 pt-20">
          <div class="position-absolute top-0 start-0 w-100 h-100 bg-black opacity-50" @click="showConfirm = false"></div>
          <div class="position-relative bg-body rounded shadow-lg p-4" style="max-width: 28rem; width: 100%;">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="d-flex align-items-center justify-content-center rounded-circle bg-danger-lt" style="width: 2.5rem; height: 2.5rem;">
                <span class="text-danger fw-bold">!</span>
              </div>
              <div>
                <h3 class="fw-semibold mb-0">{{ confirmTitle }}</h3>
                <p class="text-muted small mb-0">{{ confirmMessage }}</p>
              </div>
            </div>
            <div class="d-flex justify-content-end gap-3 mt-4">
              <button @click="showConfirm = false" class="btn btn-outline-secondary">
                Batal
              </button>
              <button @click="handleConfirm" :disabled="confirmLoading" class="btn btn-danger">
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
        <div v-if="toast.show" :class="['fixed bottom-6 end-6 z-[10001] d-flex align-items-center gap-3 px-4 py-3 rounded shadow text-sm fw-medium', toast.type === 'success' ? 'bg-success text-white' : 'bg-danger text-white']">
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
