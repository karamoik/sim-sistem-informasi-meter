<template>
  <div class="page-wrapper">
    <!-- Header -->
    <div class="page-header d-print-none mb-4">
      <div class="row align-items-center">
        <div class="col-auto">
          <h2 class="page-title">KWH Meter</h2>
          <p class="text-muted">Kelola data KWH meter</p>
        </div>
        <div class="col-auto ms-auto">
          <div class="d-flex gap-2">
            <button
              @click="openForm()"
              class="btn btn-primary"
            >
              + Tambah Meter
            </button>
            <a href="/api/export/meters" class="btn btn-outline-secondary" target="_blank">Ekspor CSV</a>
            <button @click="showImport = true" class="btn btn-outline-secondary">Impor CSV</button>
            <button @click="toggleTrashed" :class="['btn', showTrashed ? 'btn-warning' : 'btn-outline-secondary']">
              {{ showTrashed ? 'Kembali' : 'Data Terhapus' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Search & Filters -->
    <div class="row g-3 mb-4">
      <div class="col">
        <input
          v-model="search"
          type="text"
          placeholder="Cari nomor meter, nama unit..."
          class="form-control"
          @input="debouncedFetch"
        />
      </div>
      <div class="col-md-3">
        <select
          v-model="filterGardu"
          class="form-select"
          @change="fetchData"
        >
          <option value="">Semua Gardu Induk</option>
          <option v-for="gi in allGarduInduks" :key="gi.id" :value="gi.id">{{ gi.name }}</option>
        </select>
      </div>
      <div class="col-md-2">
        <select
          v-model="filterStatus"
          class="form-select"
          @change="fetchData"
        >
          <option value="">Semua Status</option>
          <option value="online">Online</option>
          <option value="offline">Offline</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div v-if="!showTrashed" class="card">
      <div class="table-responsive">
        <table class="table table-hover table-striped table-vcenter">
          <thead>
            <tr>
              <th>Nomor Meter</th>
              <th>Unit</th>
              <th>Lokasi</th>
              <th>Gardu Induk</th>
              <th class="text-end">Reading (kWh)</th>
              <th>Terakhir Update</th>
              <th class="text-center">Status</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="meter in meters"
              :key="meter.id"
            >
              <td class="font-monospace fw-bold">{{ meter.meter_no }}</td>
              <td>{{ meter.unit_name }}</td>
              <td class="text-muted" style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ meter.location }}</td>
              <td class="text-muted">{{ meter.gardu_induk?.name }}</td>
              <td class="text-end font-monospace">{{ Number(meter.last_reading).toLocaleString('id-ID') }}</td>
              <td class="text-muted small">{{ formatDateTime(meter.last_update) }}</td>
              <td class="text-center">
                <span
                  :class="[
                    'badge',
                    meter.status === 'online'
                      ? 'bg-success'
                      : 'bg-danger'
                  ]"
                >
                  {{ meter.status }}
                </span>
              </td>
              <td class="text-center">
                <div class="d-flex justify-content-center gap-1">
                  <button
                    @click="openForm(meter)"
                    class="btn btn-sm btn-outline-primary"
                    title="Edit"
                  >
                    Edit
                  </button>
                  <button
                    @click="toggleStatus(meter)"
                    :class="[
                      'btn btn-sm',
                      meter.status === 'online'
                        ? 'btn-outline-warning'
                        : 'btn-outline-success'
                    ]"
                    :title="meter.status === 'online' ? 'Set Offline' : 'Set Online'"
                  >
                    {{ meter.status === 'online' ? 'Off' : 'On' }}
                  </button>
                  <button
                    @click="confirmDelete(meter)"
                    class="btn btn-sm btn-outline-danger"
                    title="Hapus"
                  >
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!meters.length">
              <td colspan="8" class="text-center text-muted py-4">
                Tidak ada data meter
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="card-footer d-flex align-items-center justify-content-between">
        <p class="text-muted mb-0 small">
          Menampilkan {{ pagination.from }}-{{ pagination.to }} dari {{ pagination.total }} meter
        </p>
        <ul class="pagination pagination-sm mb-0">
          <li
            v-for="page in pagination.last_page"
            :key="page"
            :class="['page-item', page === pagination.current_page ? 'active' : '']"
          >
            <button class="page-link" @click="goToPage(page)">{{ page }}</button>
          </li>
        </ul>
      </div>
    </div>

    <!-- Trashed Table -->
    <div v-else class="card">
      <div class="card-header">
        <div class="card-title text-warning fw-semibold">Data yang sudah dihapus dapat dipulihkan kembali</div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-striped table-vcenter">
          <thead>
            <tr>
              <th>Nomor Meter</th>
              <th>Unit</th>
              <th>Gardu Induk</th>
              <th class="text-center">Status</th>
              <th class="text-center">Dihapus</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="m in trashedList" :key="m.id" class="opacity-75">
              <td class="font-monospace fw-bold">{{ m.meter_no }}</td>
              <td>{{ m.unit_name }}</td>
              <td class="text-muted">{{ m.gardu_induk?.name }}</td>
              <td class="text-center">
                <span :class="['badge', m.status === 'online' ? 'bg-success' : 'bg-danger']">
                  {{ m.status }}
                </span>
              </td>
              <td class="text-center small text-muted">{{ formatDateTime(m.deleted_at) }}</td>
              <td class="text-center">
                <div class="d-flex justify-content-center gap-1">
                  <button @click="restoreItem(m)" class="btn btn-sm btn-outline-success" title="Pulihkan">Pulihkan</button>
                  <button @click="forceDeleteItem(m)" class="btn btn-sm btn-outline-danger" title="Hapus Permanent">Hapus</button>
                </div>
              </td>
            </tr>
            <tr v-if="!trashedList.length">
              <td colspan="6" class="text-center text-muted py-4">Tidak ada data terhapus</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form -->
    <Teleport to="body">
      <div v-if="showForm" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                {{ editingId ? 'Edit KWH Meter' : 'Tambah KWH Meter' }}
              </h5>
              <button type="button" class="btn-close" @click="closeForm" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <EditingIndicator
                :visible="isEditingByOthers"
                :message="warningMessage"
                :editors="otherEditors"
              />
              <form @submit.prevent="saveForm">
                <div class="mb-3">
                  <label class="form-label">Nomor Meter</label>
                  <input v-model="form.meter_no" type="text" required
                    class="form-control"
                    :class="getFieldEditors('meter_no').length ? 'border-warning' : ''"
                    placeholder="KWH-GI-JKT-001-001"
                    @focus="broadcastFieldEditing('meter_no')" @blur="broadcastFieldEditing(null)" />
                  <FieldEditorBadge v-if="getFieldEditors('meter_no').length" :editors="getFieldEditors('meter_no')" />
                </div>
                <div class="mb-3">
                  <label class="form-label">Gardu Induk</label>
                  <select v-model="form.gardu_induk_id" required class="form-select">
                    <option value="">Pilih Gardu Induk</option>
                    <option v-for="gi in allGarduInduks" :key="gi.id" :value="gi.id">{{ gi.name }}</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Nama Unit</label>
                  <input v-model="form.unit_name" type="text" required
                    class="form-control"
                    :class="getFieldEditors('unit_name').length ? 'border-warning' : ''"
                    placeholder="PLTU Tanjung Jati #1"
                    @focus="broadcastFieldEditing('unit_name')" @blur="broadcastFieldEditing(null)" />
                  <FieldEditorBadge v-if="getFieldEditors('unit_name').length" :editors="getFieldEditors('unit_name')" />
                </div>
                <div class="mb-3">
                  <label class="form-label">Lokasi</label>
                  <input v-model="form.location" type="text" required
                    class="form-control"
                    :class="getFieldEditors('location').length ? 'border-warning' : ''"
                    placeholder="Area Jawa-Bali"
                    @focus="broadcastFieldEditing('location')" @blur="broadcastFieldEditing(null)" />
                  <FieldEditorBadge v-if="getFieldEditors('location').length" :editors="getFieldEditors('location')" />
                </div>
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label">Last Reading (kWh)</label>
                    <input v-model="form.last_reading" type="number" step="any" min="0"
                      class="form-control"
                      :class="getFieldEditors('last_reading').length ? 'border-warning' : ''"
                      placeholder="125000"
                      @focus="broadcastFieldEditing('last_reading')" @blur="broadcastFieldEditing(null)" />
                    <FieldEditorBadge v-if="getFieldEditors('last_reading').length" :editors="getFieldEditors('last_reading')" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select v-model="form.status" class="form-select">
                      <option value="online">Online</option>
                      <option value="offline">Offline</option>
                    </select>
                  </div>
                </div>
              </form>
              <p v-if="formError" class="alert alert-danger mb-0 mt-2">{{ formError }}</p>
            </div>
            <div class="modal-footer">
              <button type="button" @click="closeForm" class="btn btn-outline-secondary">Batal</button>
              <button type="button" @click="saveForm" :disabled="saving" class="btn btn-primary">
                {{ saving ? 'Menyimpan...' : 'Simpan' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div v-if="showImport" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Impor Data KWH Meter</h5>
              <button type="button" class="btn-close" @click="showImport = false"></button>
            </div>
            <div class="modal-body">
              <p class="text-muted small mb-3">Upload file CSV dengan format yang sesuai template.</p>
              <div class="mb-3">
                <label class="form-label">Pilih File CSV</label>
                <input type="file" accept=".csv,.txt" class="form-control" @change="importFile = $event.target.files[0]" />
              </div>
              <a href="/api/import/template?type=meters" class="text-decoration-none small" target="_blank">Download Template</a>
              <div v-if="importResult" :class="['alert mt-3', importResult.success !== false ? 'alert-success' : 'alert-danger']">
                {{ importResult.message || JSON.stringify(importResult) }}
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" @click="showImport = false">Batal</button>
              <button type="button" class="btn btn-primary" :disabled="!importFile || importing" @click="handleImport">
                {{ importing ? 'Mengimpor...' : 'Impor' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <ConfirmDialog ref="confirmDialogRef" />
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import api from '@/services/api'
import { useEditing } from '@/composables/useEditing'
import EditingIndicator from '@/components/EditingIndicator.vue'
import FieldEditorBadge from '@/components/FieldEditorBadge.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import { initEcho, leaveChannel } from '@/echo'

const confirmDialogRef = ref(null)
const meters = ref([])
const allGarduInduks = ref([])
const search = ref('')
const filterGardu = ref('')
const filterStatus = ref('')
const showTrashed = ref(false)
const trashedList = ref([])
const showForm = ref(false)
const editingId = ref(null)
const editingRecordId = ref(null)
const { isEditingByOthers, warningMessage, otherEditors, otherFieldChanges, startEditing, stopEditing, broadcastFieldChange, broadcastFieldEditing, getFieldEditors, applyRemoteChanges } = useEditing('kwh_meter', editingRecordId)
const saving = ref(false)
const formError = ref('')
const pagination = ref({ current_page: 1, last_page: 1, from: 0, to: 0, total: 0 })
const showImport = ref(false)
const importFile = ref(null)
const importing = ref(false)
const importResult = ref(null)

const form = ref({
  meter_no: '',
  gardu_induk_id: '',
  unit_name: '',
  address: '',
  last_reading: '',
  status: 'online',
})

const lastBroadcastValues = ref({})
let formWatcher = null

function startFormWatch() {
  stopFormWatch()
  lastBroadcastValues.value = {}
  for (const [k, v] of Object.entries(form.value)) {
    lastBroadcastValues.value[k] = JSON.parse(JSON.stringify(v))
  }
  formWatcher = watch(
    form,
    () => {
      if (!editingId.value) return
      for (const [field, val] of Object.entries(form.value)) {
        const strNew = JSON.stringify(val)
        const strOld = JSON.stringify(lastBroadcastValues.value[field])
        if (strNew !== strOld) {
          lastBroadcastValues.value[field] = JSON.parse(strNew)
          broadcastFieldChange(field, val)
        }
      }
    },
    { deep: true, flush: 'sync' }
  )
}

function stopFormWatch() {
  if (formWatcher) {
    formWatcher()
    formWatcher = null
  }
}

watch(otherFieldChanges, () => {
  if (showForm.value) {
    applyRemoteChanges(form)
  }
})

let debounceTimer = null

function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchData, 300)
}

async function fetchGarduInduks() {
  try {
    const { data: res } = await api.get('/gardu-induks')
    allGarduInduks.value = res.data
  } catch (e) {
    console.error('Failed to fetch gardu induks', e)
  }
}

async function fetchData(page = 1) {
  try {
    const params = { page }
    if (search.value) params.search = search.value
    if (filterGardu.value) params.gardu_induk_id = filterGardu.value
    if (filterStatus.value) params.status = filterStatus.value
    const { data: res } = await api.get('/kwh-meters', { params })
    meters.value = res.data.data
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      from: res.data.from,
      to: res.data.to,
      total: res.data.total,
    }
  } catch (e) {
    console.error('Failed to fetch meters', e)
  }
}

async function fetchTrashed() {
  try {
    const { data: res } = await api.get('/kwh-meters/trashed')
    trashedList.value = res.data
  } catch (e) { console.error(e) }
}

function toggleTrashed() {
  showTrashed.value = !showTrashed.value
  if (showTrashed.value) fetchTrashed()
  else fetchData()
}

async function restoreItem(item) {
  const ok = await confirmDialogRef.value.show({
    title: 'Pulihkan Data',
    message: `Pulihkan meter "${item.meter_no}"?`,
    type: 'warning',
    confirmText: 'Pulihkan',
  })
  if (!ok) return
  try {
    await api.post(`/kwh-meters/${item.id}/restore`)
    fetchTrashed()
    fetchData()
  } catch (e) { alert(e.response?.data?.message || 'Gagal memulihkan data') }
}

async function forceDeleteItem(item) {
  const ok = await confirmDialogRef.value.show({
    title: 'Hapus Permanen',
    message: `Hapus permanent meter "${item.meter_no}"? Tindakan ini tidak dapat dibatalkan.`,
    type: 'danger',
    confirmText: 'Hapus Permanen',
  })
  if (!ok) return
  try {
    await api.delete(`/kwh-meters/${item.id}/force-delete`)
    fetchTrashed()
  } catch (e) { alert(e.response?.data?.message || 'Gagal menghapus data') }
}

function goToPage(page) {
  fetchData(page)
}

async function handleImport() {
  if (!importFile.value) return
  importing.value = true; importResult.value = null
  try {
    const fd = new FormData()
    fd.append('file', importFile.value)
    fd.append('type', 'meters')
    const { data: res } = await api.post('/import', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    importResult.value = res; importFile.value = null; fetchData()
  } catch (e) {
    importResult.value = { success: false, message: e.response?.data?.message || 'Import gagal' }
  } finally { importing.value = false }
}

function openForm(meter = null) {
  formError.value = ''
  if (meter) {
    editingId.value = meter.id
    form.value = {
      meter_no: meter.meter_no,
      gardu_induk_id: meter.gardu_induk_id,
      unit_name: meter.unit_name,
      location: meter.location,
      last_reading: meter.last_reading,
      status: meter.status,
    }
  } else {
    editingId.value = null
    form.value = { meter_no: '', gardu_induk_id: '', unit_name: '', location: '', last_reading: '', status: 'online' }
  }
  showForm.value = true
  if (editingId.value) {
    editingRecordId.value = editingId.value
    startEditing()
    startFormWatch()
  }
}

async function saveForm() {
  saving.value = true
  formError.value = ''
  try {
    if (editingId.value) {
      await api.put(`/kwh-meters/${editingId.value}`, form.value)
    } else {
      await api.post('/kwh-meters', form.value)
    }
    showForm.value = false
    stopFormWatch()
    stopEditing()
    editingRecordId.value = null
    await fetchData(pagination.value.current_page)
  } catch (e) {
    formError.value = e.response?.data?.message || 'Terjadi kesalahan'
  } finally {
    saving.value = false
  }
}

function closeForm() {
  stopFormWatch()
  stopEditing()
  showForm.value = false
  editingRecordId.value = null
}

async function toggleStatus(meter) {
  const newStatus = meter.status === 'online' ? 'offline' : 'online'
  try {
    await api.put(`/kwh-meters/${meter.id}`, { status: newStatus })
    await fetchData(pagination.value.current_page)
  } catch (e) {
    console.error('Failed to toggle status', e)
  }
}

async function confirmDelete(meter) {
  const ok = await confirmDialogRef.value.show({
    title: 'Hapus Meter',
    message: `Hapus meter "${meter.meter_no}"?`,
    type: 'danger',
    confirmText: 'Hapus',
  })
  if (!ok) return
  try {
    await api.delete(`/kwh-meters/${meter.id}`)
    await fetchData(pagination.value.current_page)
  } catch (e) {
    console.error('Failed to delete', e)
  }
}

function formatDateTime(dt) {
  if (!dt) return '-'
  return new Date(dt).toLocaleString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}

function listenForCrudUpdates() {
  try {
    const echo = initEcho()
    if (!echo) return
    echo.channel('crud.kwh_meter').listen('.updated', () => {
      fetchData()
    })
  } catch (e) {
    console.warn('Crud update listener failed:', e)
  }
}

onMounted(() => {
  fetchGarduInduks()
  fetchData()
  listenForCrudUpdates()
})

onBeforeUnmount(() => {
  stopFormWatch()
  leaveChannel('crud.kwh_meter')
})
</script>
