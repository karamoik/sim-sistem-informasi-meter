<template>
  <div>
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
      <div>
        <h2 class="page-title">Jenis KWH Meter</h2>
        <p class="text-muted">Kelola data jenis/tipe KWH meter</p>
      </div>
      <div class="d-flex gap-2">
        <a href="/api/export/jenis-kwh-meters" class="btn btn-outline-secondary" target="_blank">Ekspor CSV</a>
        <button @click="showImport = true" class="btn btn-outline-secondary">Impor CSV</button>
        <button @click="openForm()" class="btn btn-primary">
          + Tambah Jenis Meter
        </button>
        <button @click="toggleTrashed" :class="['btn', showTrashed ? 'btn-warning' : 'btn-outline-secondary']">
          {{ showTrashed ? 'Kembali' : 'Data Terhapus' }}
        </button>
      </div>
    </div>

    <div class="d-flex flex-column flex-sm-row gap-3">
      <div class="flex-fill">
        <input v-model="search" type="text" placeholder="Cari nama, kode, manufacturer..." class="form-control" @input="fetchData" />
      </div>
      <select v-model="filterPhase" class="form-select" style="width: auto;" @change="fetchData">
        <option value="">Semua Phase</option>
        <option value="single">Single Phase</option>
        <option value="three">Three Phase</option>
      </select>
    </div>

    <div v-if="!showTrashed" class="card">
      <div class="table-responsive">
        <table class="table table-hover table-striped table-vcenter mb-0">
          <thead>
            <tr>
              <th style="width:50px">No</th>
              <th class="text-start">Kode</th>
              <th class="text-start">Nama</th>
              <th class="text-center">Phase</th>
              <th class="text-end">Kapasitas (VA)</th>
              <th class="text-start">Manufacturer</th>
              <th class="text-center">Terpakai</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(j, idx) in jenisList" :key="j.id">
              <td class="text-muted">{{ (pagination.current_page - 1) * pagination.per_page + idx + 1 }}</td>
              <td class="font-monospace fw-semibold">{{ j.code }}</td>
              <td>
                <div class="fw-medium">{{ j.name }}</div>
                <div v-if="j.description" class="text-muted small text-truncate" style="max-width: 200px;">{{ j.description }}</div>
              </td>
              <td class="text-center">
                <span :class="['badge', j.phase === 'single' ? 'bg-blue' : 'bg-purple']">
                  {{ j.phase === 'single' ? '1 Phase' : '3 Phase' }}
                </span>
              </td>
              <td class="text-end font-monospace small">{{ j.max_capacity ? Number(j.max_capacity).toLocaleString('id-ID') : '-' }}</td>
              <td class="text-muted small">{{ j.manufacturer || '-' }}</td>
              <td class="text-center">
                <span class="avatar avatar-sm rounded-circle bg-secondary bg-opacity-10 text-dark">{{ j.kwh_meters_count }}</span>
              </td>
              <td class="text-center">
                <div class="d-flex align-items-center justify-content-center gap-2">
                  <button @click="openForm(j)" class="btn btn-sm btn-outline-primary" title="Edit">✎</button>
                  <button @click="confirmDelete(j)" class="btn btn-sm btn-outline-danger" title="Hapus">✕</button>
                </div>
              </td>
            </tr>
            <tr v-if="!jenisList.length">
              <td colspan="8" class="text-center text-muted py-4">Tidak ada data jenis KWH meter</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="pagination.last_page > 1" class="card-footer d-flex align-items-center justify-content-between">
        <p class="text-muted mb-0 small">Menampilkan {{ pagination.from }}-{{ pagination.to }} dari {{ pagination.total }} data</p>
        <ul class="pagination pagination-sm mb-0">
          <li v-for="page in pagination.last_page" :key="page" :class="['page-item', page === pagination.current_page ? 'active' : '']">
            <button class="page-link" @click="goToPage(page)">{{ page }}</button>
          </li>
        </ul>
      </div>
    </div>

    <div v-else class="card">
      <div class="card-status alert alert-warning py-2 mb-0 rounded-0">
        <small class="fw-medium">Data yang sudah dihapus dapat dipulihkan kembali</small>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-striped table-vcenter mb-0">
          <thead>
            <tr>
              <th style="width:50px">No</th>
              <th class="text-start">Kode</th>
              <th class="text-start">Nama</th>
              <th class="text-center">Phase</th>
              <th class="text-center">Terpakai</th>
              <th class="text-center">Dihapus</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(j, idx) in trashedList" :key="j.id" class="opacity-50">
              <td class="text-muted">{{ idx + 1 }}</td>
              <td class="font-monospace fw-semibold">{{ j.code }}</td>
              <td class="fw-medium">{{ j.name }}</td>
              <td class="text-center">
                <span :class="['badge', j.phase === 'single' ? 'bg-blue' : 'bg-purple']">
                  {{ j.phase === 'single' ? '1 Phase' : '3 Phase' }}
                </span>
              </td>
              <td class="text-center">
                <span class="avatar avatar-sm rounded-circle bg-secondary bg-opacity-10 text-dark">{{ j.kwh_meters_count }}</span>
              </td>
              <td class="text-center small text-muted">{{ formatDateTime(j.deleted_at) }}</td>
              <td class="text-center">
                <div class="d-flex align-items-center justify-content-center gap-2">
                  <button @click="restoreItem(j)" class="btn btn-sm btn-outline-success" title="Pulihkan">↩</button>
                  <button @click="forceDeleteItem(j)" class="btn btn-sm btn-outline-danger" title="Hapus Permanent">⚠</button>
                </div>
              </td>
            </tr>
            <tr v-if="!trashedList.length">
              <td colspan="7" class="text-center text-muted py-4">Tidak ada data terhapus</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showForm" class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h3 class="modal-title">{{ editingId ? 'Edit Jenis KWH Meter' : 'Tambah Jenis KWH Meter' }}</h3>
              <button type="button" class="btn-close" @click="closeForm"></button>
            </div>
            <div class="modal-body">
              <EditingIndicator
                :visible="isEditingByOthers"
                :message="warningMessage"
                :editors="otherEditors"
              />
              <form @submit.prevent="saveForm">
                <div class="mb-3">
                  <label class="form-label">Nama</label>
                  <input v-model="form.name" type="text" required
                    class="form-control"
                    :class="getFieldEditors('name').length ? 'border-warning' : ''"
                    @focus="broadcastFieldEditing('name')" @blur="broadcastFieldEditing(null)" />
                  <FieldEditorBadge v-if="getFieldEditors('name').length" :editors="getFieldEditors('name')" />
                </div>
                <div class="mb-3">
                  <label class="form-label">Kode</label>
                  <input v-model="form.code" type="text" required
                    class="form-control"
                    :class="getFieldEditors('code').length ? 'border-warning' : ''"
                    @focus="broadcastFieldEditing('code')" @blur="broadcastFieldEditing(null)" />
                  <FieldEditorBadge v-if="getFieldEditors('code').length" :editors="getFieldEditors('code')" />
                </div>
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label">Phase</label>
                    <select v-model="form.phase" class="form-select">
                      <option value="single">Single Phase</option>
                      <option value="three">Three Phase</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Kapasitas (VA)</label>
                    <input v-model="form.max_capacity" type="number" min="0" step="any"
                      class="form-control"
                      :class="getFieldEditors('max_capacity').length ? 'border-warning' : ''"
                      @focus="broadcastFieldEditing('max_capacity')" @blur="broadcastFieldEditing(null)" />
                    <FieldEditorBadge v-if="getFieldEditors('max_capacity').length" :editors="getFieldEditors('max_capacity')" />
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Manufacturer</label>
                  <input v-model="form.manufacturer" type="text"
                    class="form-control"
                    :class="getFieldEditors('manufacturer').length ? 'border-warning' : ''"
                    @focus="broadcastFieldEditing('manufacturer')" @blur="broadcastFieldEditing(null)" />
                  <FieldEditorBadge v-if="getFieldEditors('manufacturer').length" :editors="getFieldEditors('manufacturer')" />
                </div>
                <div class="mb-3">
                  <label class="form-label">Deskripsi</label>
                  <textarea v-model="form.description" rows="2"
                    class="form-control"
                    :class="getFieldEditors('description').length ? 'border-warning' : ''"
                    @focus="broadcastFieldEditing('description')" @blur="broadcastFieldEditing(null)"></textarea>
                  <FieldEditorBadge v-if="getFieldEditors('description').length" :editors="getFieldEditors('description')" />
                </div>
                <div v-if="formError" class="alert alert-danger py-2">{{ formError }}</div>
              </form>
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
              <h5 class="modal-title">Impor Data Jenis KWH Meter</h5>
              <button type="button" class="btn-close" @click="showImport = false"></button>
            </div>
            <div class="modal-body">
              <p class="text-muted small mb-3">Upload file CSV dengan format yang sesuai template.</p>
              <div class="mb-3">
                <label class="form-label">Pilih File CSV</label>
                <input type="file" accept=".csv,.txt" class="form-control" @change="importFile = $event.target.files[0]" />
              </div>
              <a href="/api/import/template?type=jenis_kwh_meters" class="text-decoration-none small" target="_blank">Download Template</a>
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
const jenisList = ref([])
const search = ref('')
const filterPhase = ref('')
const showTrashed = ref(false)
const trashedList = ref([])
const showForm = ref(false)
const editingId = ref(null)
const editingRecordId = ref(null)
const { isEditingByOthers, warningMessage, otherEditors, otherFieldChanges, startEditing, stopEditing, broadcastFieldChange, broadcastFieldEditing, getFieldEditors, applyRemoteChanges } = useEditing('jenis_kwh_meter', editingRecordId)
const saving = ref(false)
const formError = ref('')
const pagination = ref({ current_page: 1, last_page: 1, from: 0, to: 0, total: 0 })
const showImport = ref(false)
const importFile = ref(null)
const importing = ref(false)
const importResult = ref(null)

const form = ref({ name: '', code: '', phase: 'single', max_capacity: '', manufacturer: '', description: '' })

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

function goToPage(page) { fetchData(page) }

async function fetchData(page = 1) {
  try {
    const params = { page }
    if (search.value) params.search = search.value
    if (filterPhase.value) params.phase = filterPhase.value
    const { data: res } = await api.get('/jenis-kwh-meters', { params })
    jenisList.value = res.data.data
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      from: res.data.from, to: res.data.to, total: res.data.total,
    }
  } catch (e) { console.error(e) }
}

async function fetchTrashed() {
  try {
    const { data: res } = await api.get('/jenis-kwh-meters/trashed')
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
    message: `Pulihkan jenis meter "${item.name}"?`,
    type: 'warning',
    confirmText: 'Pulihkan',
  })
  if (!ok) return
  try {
    await api.post(`/jenis-kwh-meters/${item.id}/restore`)
    fetchTrashed()
    fetchData()
  } catch (e) { alert(e.response?.data?.message || 'Gagal memulihkan data') }
}

async function forceDeleteItem(item) {
  const ok = await confirmDialogRef.value.show({
    title: 'Hapus Permanen',
    message: `Hapus permanent jenis meter "${item.name}"? Tindakan ini tidak dapat dibatalkan.`,
    type: 'danger',
    confirmText: 'Hapus Permanen',
  })
  if (!ok) return
  try {
    await api.delete(`/jenis-kwh-meters/${item.id}/force-delete`)
    fetchTrashed()
  } catch (e) { alert(e.response?.data?.message || 'Gagal menghapus data') }
}

function formatDateTime(d) {
  if (!d) return '-'
  return new Date(d).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function openForm(item = null) {
  formError.value = ''
  if (item) {
    editingId.value = item.id
    form.value = { name: item.name, code: item.code, phase: item.phase, max_capacity: item.max_capacity || '', manufacturer: item.manufacturer || '', description: item.description || '' }
  } else {
    editingId.value = null
    form.value = { name: '', code: '', phase: 'single', max_capacity: '', manufacturer: '', description: '' }
  }
  showForm.value = true
  if (editingId.value) {
    editingRecordId.value = editingId.value
    startEditing()
    startFormWatch()
  }
}

async function saveForm() {
  saving.value = true; formError.value = ''
  try {
    if (editingId.value) await api.put(`/jenis-kwh-meters/${editingId.value}`, form.value)
    else await api.post('/jenis-kwh-meters', form.value)
    showForm.value = false
    stopFormWatch()
    stopEditing()
    editingRecordId.value = null
    await fetchData()
  } catch (e) { formError.value = e.response?.data?.message || 'Terjadi kesalahan' } finally { saving.value = false }
}

function closeForm() {
  stopFormWatch()
  stopEditing()
  showForm.value = false
  editingRecordId.value = null
}

async function confirmDelete(item) {
  const ok = await confirmDialogRef.value.show({
    title: 'Hapus Jenis Meter',
    message: `Hapus jenis meter "${item.name}"?`,
    type: 'danger',
    confirmText: 'Hapus',
  })
  if (!ok) return
  try { await api.delete(`/jenis-kwh-meters/${item.id}`); await fetchData() } catch (e) { console.error(e) }
}

async function handleImport() {
  if (!importFile.value) return
  importing.value = true; importResult.value = null
  try {
    const fd = new FormData()
    fd.append('file', importFile.value)
    fd.append('type', 'jenis_kwh_meters')
    const { data: res } = await api.post('/import', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    importResult.value = res; importFile.value = null; fetchData()
  } catch (e) { importResult.value = { success: false, message: e.response?.data?.message || 'Import gagal' } }
  finally { importing.value = false }
}

function listenForCrudUpdates() {
  try {
    const echo = initEcho()
    if (!echo) return
    echo.channel('crud.jenis_kwh_meter').listen('.updated', () => {
      fetchData()
    })
  } catch (e) {
    console.warn('Crud update listener failed:', e)
  }
}

onMounted(() => {
  fetchData()
  listenForCrudUpdates()
})

onBeforeUnmount(() => {
  stopFormWatch()
  leaveChannel('crud.jenis_kwh_meter')
})
</script>
