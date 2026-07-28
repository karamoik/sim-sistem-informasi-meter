<template>
  <div>
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
      <div>
        <div class="page-pretitle">Aktivitas</div>
        <h2 class="page-title">Riwayat Aktivitas</h2>
        <p class="text-muted small">Catatan semua aktivitas akun anda</p>
      </div>
      <div class="d-flex gap-3 align-items-center">
        <select v-model="filterType" class="form-select form-select-sm">
          <option value="">Semua Tipe</option>
          <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
        </select>
        <button v-if="logs.length" @click="confirmClear" class="btn btn-outline-danger btn-sm">Hapus Semua</button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="d-flex justify-content-center py-5">
      <div class="spinner-border text-blue" role="status"></div>
    </div>

    <!-- Logs -->
    <div v-else>
      <div class="card">
        <div class="table-responsive">
          <table class="table table-hover table-striped table-vcenter">
            <thead>
              <tr>
                <th>Tipe</th>
                <th>Deskripsi</th>
                <th>Waktu</th>
                <th>IP</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="log in logs" :key="log.id">
                <td>
                  <span :class="['badge', typeStyle[log.type]?.badge || 'bg-secondary']">{{ log.type }}</span>
                </td>
                <td>{{ log.description }}</td>
                <td class="text-muted">{{ formatTime(log.created_at) }}</td>
                <td class="text-muted">{{ log.ip_address || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="!logs.length" class="text-center py-5 text-muted">
        Belum ada aktivitas
      </div>

      <!-- Pagination -->
      <div v-if="logs.length" class="d-flex justify-content-center mt-3">
        <ul class="pagination">
          <li class="page-item" :class="{ disabled: page <= 1 }">
            <button class="page-link" @click="page--" :disabled="page <= 1">Prev</button>
          </li>
          <li class="page-item disabled">
            <span class="page-link">Hal {{ page }}</span>
          </li>
          <li class="page-item" :class="{ disabled: !hasMore }">
            <button class="page-link" @click="page++" :disabled="!hasMore">Next</button>
          </li>
        </ul>
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
    login: { bg: 'bg-blue-100 dark:bg-blue-900/30', text: 'text-blue-600 dark:text-blue-400', badge: 'bg-blue-lt', icon: 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9' },
    logout: { bg: 'bg-gray-100 dark:bg-gray-700', text: 'text-gray-600 dark:text-gray-400', badge: 'bg-secondary-lt', icon: 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9' },
    password_changed: { bg: 'bg-amber-100 dark:bg-amber-900/30', text: 'text-amber-600 dark:text-amber-400', badge: 'bg-warning-lt', icon: 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z' },
    profile_updated: { bg: 'bg-green-100 dark:bg-green-900/30', text: 'text-green-600 dark:text-green-400', badge: 'bg-success-lt', icon: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z' },
    create: { bg: 'bg-emerald-100 dark:bg-emerald-900/30', text: 'text-emerald-600 dark:text-emerald-400', badge: 'bg-success-lt', icon: 'M12 4.5v15m7.5-7.5h-15' },
    update: { bg: 'bg-blue-100 dark:bg-blue-900/30', text: 'text-blue-600 dark:text-blue-400', badge: 'bg-blue-lt', icon: 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10' },
    delete: { bg: 'bg-red-100 dark:bg-red-900/30', text: 'text-red-600 dark:text-red-400', badge: 'bg-danger-lt', icon: 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0' },
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
