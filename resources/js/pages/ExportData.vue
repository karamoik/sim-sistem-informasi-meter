<template>
  <div class="container-xl" style="max-width:720px">
    <div>
      <h1 class="page-title mb-1">Ekspor Data</h1>
      <p class="text-muted small">Unduh data KWH meter dalam format CSV</p>
    </div>

    <!-- KWH Meter Export -->
    <div class="card mb-3">
      <div class="card-body">
        <h3 class="card-title">Data KWH Meter</h3>
        <form @submit.prevent="exportMeters">
          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label">Gardu Induk</label>
              <select v-model="meterFilter.gardu_induk_id" class="form-select">
                <option value="">Semua</option>
                <option v-for="gi in garduInduks" :key="gi.id" :value="gi.id">{{ gi.name }}</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label">Status</label>
              <select v-model="meterFilter.status" class="form-select">
                <option value="">Semua</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label">Dari Tanggal</label>
              <input v-model="meterFilter.from" type="date" class="form-control" />
            </div>
            <div class="col-sm-6">
              <label class="form-label">Sampai Tanggal</label>
              <input v-model="meterFilter.to" type="date" class="form-control" />
            </div>
          </div>
          <div class="d-flex justify-content-end mt-3">
            <button type="submit" :disabled="exporting" class="btn btn-primary">
              {{ exporting ? 'Mengunduh...' : 'Unduh CSV' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Dashboard Stats Export -->
    <div class="card mb-3">
      <div class="card-body">
        <h3 class="card-title">Statistik Dashboard</h3>
        <p class="text-muted small">Ekspor ringkasan status semua gardu induk</p>
        <div class="d-flex justify-content-end">
          <button @click="exportDashboard" :disabled="exportingDash" class="btn btn-success">
            {{ exportingDash ? 'Mengunduh...' : 'Unduh Stats CSV' }}
          </button>
        </div>
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
