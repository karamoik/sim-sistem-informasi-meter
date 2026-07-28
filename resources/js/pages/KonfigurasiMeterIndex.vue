<template>
  <div>
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
      <div>
        <h2 class="page-title">Konfigurasi Meter</h2>
        <p class="text-muted">Mapping titik ukur dengan jenis KWH meter dan periode</p>
      </div>
      <div class="d-flex gap-2">
        <a href="/api/export/konfigurasi-meters" class="btn btn-outline-secondary" target="_blank">Ekspor CSV</a>
        <button @click="showImport = true" class="btn btn-outline-secondary">Impor CSV</button>
        <button @click="openForm()" class="btn btn-primary">
          + Tambah Konfigurasi
        </button>
        <button @click="toggleTrashed" :class="['btn', showTrashed ? 'btn-warning' : 'btn-outline-secondary']">
          {{ showTrashed ? 'Kembali' : 'Data Terhapus' }}
        </button>
      </div>
    </div>

    <div class="d-flex flex-column flex-sm-row gap-3 mb-4">
      <div class="flex-grow-1">
        <input v-model="search" type="text" placeholder="Cari periode, catatan..." class="form-control" @input="fetchData" />
      </div>
      <select v-model="filterStatus" class="form-select" style="width:auto;" @change="fetchData">
        <option value="">Semua Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
        <option value="expired">Expired</option>
      </select>
    </div>

    <div v-if="!showTrashed" class="card mb-4">
      <div class="table-responsive">
        <table class="table table-hover table-striped table-vcenter">
          <thead>
            <tr>
              <th style="width:50px">No</th>
              <th>Titik Ukur</th>
              <th>Gardu Induk</th>
              <th>Jenis Meter</th>
              <th class="text-center">Periode</th>
              <th class="text-center">Tanggal</th>
              <th class="text-center">Status</th>
              <th class="text-center">Dokumen</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(k, idx) in konfigurasiList" :key="k.id">
              <td class="text-muted">{{ (pagination.current_page - 1) * pagination.per_page + idx + 1 }}</td>
              <td>
                <p class="fw-bold">{{ k.titik_ukur?.name }}</p>
                <p class="text-muted small font-monospace">{{ k.titik_ukur?.code }}</p>
              </td>
              <td>{{ k.titik_ukur?.gardu_induk?.name }}</td>
              <td>
                <p class="fw-bold">{{ k.jenis_kwh_meter?.name }}</p>
                <p class="text-muted small">{{ k.jenis_kwh_meter?.phase === 'single' ? '1 Phase' : '3 Phase' }} {{ k.jenis_kwh_meter?.max_capacity ? '- ' + Number(k.jenis_kwh_meter.max_capacity).toLocaleString('id-ID') + ' VA' : '' }}</p>
              </td>
              <td class="text-center">
                <span class="badge text-bg-secondary">{{ k.period }}</span>
              </td>
              <td class="text-center">
                <div>{{ formatDate(k.start_date) }}</div>
                <div class="text-muted small">s/d {{ k.end_date ? formatDate(k.end_date) : 'Sekarang' }}</div>
              </td>
              <td class="text-center">
                <span :class="['badge', statusClass(k.status)]">
                  {{ k.status }}
                </span>
              </td>
              <td class="text-center">
                <a v-if="k.document_path" :href="getDocumentUrl(k)" target="_blank" class="badge text-bg-blue">
                  PDF
                </a>
                <span v-else class="text-muted small">-</span>
              </td>
              <td class="text-center">
                <div class="d-flex align-items-center justify-content-center gap-2">
                  <button @click="openForm(k)" class="btn btn-sm btn-outline-primary" title="Edit">
                    Edit
                  </button>
                  <button @click="confirmDelete(k)" class="btn btn-sm btn-outline-danger" title="Hapus">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!konfigurasiList.length">
              <td colspan="9" class="text-center text-muted py-4">Tidak ada data konfigurasi meter</td>
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

    <div v-else class="card mb-4">
      <div class="alert alert-warning py-2 mb-0 rounded-0">
        <small class="fw-medium">Data yang sudah dihapus dapat dipulihkan kembali</small>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-striped table-vcenter">
          <thead>
            <tr>
              <th style="width:50px">No</th>
              <th>Periode</th>
              <th>Titik Ukur</th>
              <th>Jenis Meter</th>
              <th class="text-center">Status</th>
              <th class="text-center">Dihapus</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(k, idx) in trashedList" :key="k.id" class="opacity-75">
              <td class="text-muted">{{ idx + 1 }}</td>
              <td class="fw-bold">{{ k.period }}</td>
              <td>{{ k.titik_ukur?.name }}</td>
              <td>{{ k.jenis_kwh_meter?.name }}</td>
              <td class="text-center">
                <span :class="['badge', statusClass(k.status)]">
                  {{ k.status }}
                </span>
              </td>
              <td class="text-center">{{ formatDateTime(k.deleted_at) }}</td>
              <td class="text-center">
                <div class="d-flex align-items-center justify-content-center gap-2">
                  <button @click="restoreItem(k)" class="btn btn-sm btn-outline-success" title="Pulihkan">
                    Pulihkan
                  </button>
                  <button @click="forceDeleteItem(k)" class="btn btn-sm btn-outline-danger" title="Hapus Permanen">
                    Hapus Permanen
                  </button>
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
      <div v-if="showForm" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">{{ editingId ? 'Edit Konfigurasi' : 'Tambah Konfigurasi' }}</h5>
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
                  <label class="form-label">Titik Ukur</label>
                  <select v-model="form.titik_ukur_id" required class="form-select">
                    <option value="">Pilih Titik Ukur</option>
                    <option v-for="tu in allTitikUkurs" :key="tu.id" :value="tu.id">{{ tu.name }} ({{ tu.gardu_induk?.name }})</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Jenis KWH Meter</label>
                  <select v-model="form.jenis_kwh_meter_id" required class="form-select">
                    <option value="">Pilih Jenis Meter</option>
                    <option v-for="j in allJenisMeters" :key="j.id" :value="j.id">{{ j.name }} ({{ j.phase === 'single' ? '1 Ph' : '3 Ph' }})</option>
                  </select>
                </div>
                <div class="mb-3 position-relative">
                  <label class="form-label">Periode</label>
                  <input v-model="form.period" type="text" required placeholder="Contoh: 2026-Q3"
                    class="form-control"
                    :class="getFieldEditors('period').length ? 'is-warning' : ''"
                    @focus="broadcastFieldEditing('period')" @blur="broadcastFieldEditing(null)" />
                  <FieldEditorBadge v-if="getFieldEditors('period').length" :editors="getFieldEditors('period')" />
                </div>
                <div class="row g-3 mb-3">
                  <div class="col-md-6 position-relative">
                    <label class="form-label">Tanggal Mulai</label>
                    <input v-model="form.start_date" type="date" required
                      class="form-control"
                      :class="getFieldEditors('start_date').length ? 'is-warning' : ''"
                      @focus="broadcastFieldEditing('start_date')" @blur="broadcastFieldEditing(null)" />
                    <FieldEditorBadge v-if="getFieldEditors('start_date').length" :editors="getFieldEditors('start_date')" />
                  </div>
                  <div class="col-md-6 position-relative">
                    <label class="form-label">Tanggal Akhir</label>
                    <input v-model="form.end_date" type="date"
                      class="form-control"
                      :class="getFieldEditors('end_date').length ? 'is-warning' : ''"
                      @focus="broadcastFieldEditing('end_date')" @blur="broadcastFieldEditing(null)" />
                    <FieldEditorBadge v-if="getFieldEditors('end_date').length" :editors="getFieldEditors('end_date')" />
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Status</label>
                  <select v-model="form.status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="expired">Expired</option>
                  </select>
                </div>
                <div class="mb-3 position-relative">
                  <label class="form-label">Catatan</label>
                  <textarea v-model="form.notes" rows="2"
                    class="form-control"
                    :class="getFieldEditors('notes').length ? 'is-warning' : ''"
                    @focus="broadcastFieldEditing('notes')" @blur="broadcastFieldEditing(null)"></textarea>
                  <FieldEditorBadge v-if="getFieldEditors('notes').length" :editors="getFieldEditors('notes')" />
                </div>
                <div class="mb-3">
                  <label class="form-label">Dokumen PDF (opsional, max 2MB)</label>
                  <div
                    class="border border-2 border-dashed rounded p-4 text-center"
                    :class="documentDragOver ? 'border-primary bg-primary-subtle' : documentError ? 'border-danger bg-danger-subtle' : ''"
                    @dragover.prevent="documentDragOver = true"
                    @dragleave="documentDragOver = false"
                    @drop.prevent="handleDocumentDrop"
                  >
                    <input type="file" ref="documentInput" accept=".pdf" class="d-none" @change="handleDocumentSelect" />
                    <div v-if="documentPreview" class="mb-2">
                      <div class="d-flex align-items-center justify-content-center gap-2">
                        <div class="text-start">
                          <p class="fw-medium mb-0">{{ documentPreview.name }}</p>
                          <p class="text-muted small mb-0">{{ formatFileSize(documentPreview.size) }}</p>
                        </div>
                      </div>
                      <button type="button" @click="removeDocument" class="btn btn-link btn-sm text-danger p-0 mt-1">Hapus file</button>
                    </div>
                    <div v-else>
                      <p class="text-muted mb-1">Drag & drop atau <button type="button" @click="documentInput.click()" class="btn btn-link btn-sm p-0">pilih file</button></p>
                      <p class="text-muted small mb-0">Hanya file PDF, maks. 2MB</p>
                    </div>
                  </div>
                  <p v-if="documentError" class="text-danger small mt-1">{{ documentError }}</p>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" @click="closeForm" class="btn btn-outline-secondary flex-fill">Batal</button>
              <button type="button" @click="saveForm" :disabled="saving" class="btn btn-primary flex-fill">{{ saving ? 'Menyimpan...' : 'Simpan' }}</button>
            </div>
            <p v-if="formError" class="text-danger small px-3 pb-3 mb-0">{{ formError }}</p>
          </div>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div v-if="showImport" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Impor Data Konfigurasi Meter</h5>
              <button type="button" class="btn-close" @click="showImport = false"></button>
            </div>
            <div class="modal-body">
              <p class="text-muted small mb-3">Upload file CSV dengan format yang sesuai template.</p>
              <div class="mb-3">
                <label class="form-label">Pilih File CSV</label>
                <input type="file" accept=".csv,.txt" class="form-control" @change="importFile = $event.target.files[0]" />
              </div>
              <a href="/api/import/template?type=konfigurasi_meters" class="text-decoration-none small" target="_blank">Download Template</a>
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
const konfigurasiList = ref([])
const allTitikUkurs = ref([])
const allJenisMeters = ref([])
const search = ref('')
const filterStatus = ref('')
const showTrashed = ref(false)
const trashedList = ref([])
const showForm = ref(false)
const editingId = ref(null)
const editingRecordId = ref(null)
const { isEditingByOthers, warningMessage, otherEditors, otherFieldChanges, startEditing, stopEditing, broadcastFieldChange, broadcastFieldEditing, getFieldEditors, applyRemoteChanges } = useEditing('konfigurasi_meter', editingRecordId)
const saving = ref(false)
const formError = ref('')
const documentInput = ref(null)
const documentPreview = ref(null)
const documentDragOver = ref(false)
const documentError = ref('')
const existingDocumentPath = ref(null)
const pagination = ref({ current_page: 1, last_page: 1, from: 0, to: 0, total: 0 })
const showImport = ref(false)
const importFile = ref(null)
const importing = ref(false)
const importResult = ref(null)

const form = ref({ titik_ukur_id: '', jenis_kwh_meter_id: '', period: '', start_date: '', end_date: '', status: 'active', notes: '' })

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

async function fetchRefs() {
  try {
    const [tu, jm] = await Promise.all([api.get('/titik-ukurs/all'), api.get('/jenis-kwh-meters/all')])
    allTitikUkurs.value = tu.data.data || tu.data
    allJenisMeters.value = jm.data.data || jm.data
  } catch (e) { console.error(e) }
}

function goToPage(page) { fetchData(page) }

async function fetchData(page = 1) {
  try {
    const params = { page }
    if (search.value) params.search = search.value
    if (filterStatus.value) params.status = filterStatus.value
    const { data: res } = await api.get('/konfigurasi-meters', { params })
    konfigurasiList.value = res.data.data
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      per_page: res.data.per_page,
      from: res.data.from, to: res.data.to, total: res.data.total,
    }
  } catch (e) { console.error(e) }
}

async function fetchTrashed() {
  try {
    const { data: res } = await api.get('/konfigurasi-meters/trashed')
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
    message: `Pulihkan konfigurasi "${item.period}"?`,
    type: 'warning',
    confirmText: 'Pulihkan',
  })
  if (!ok) return
  try {
    await api.post(`/konfigurasi-meters/${item.id}/restore`)
    fetchTrashed()
    fetchData()
  } catch (e) { alert(e.response?.data?.message || 'Gagal memulihkan data') }
}

async function forceDeleteItem(item) {
  const ok = await confirmDialogRef.value.show({
    title: 'Hapus Permanen',
    message: `Hapus permanent konfigurasi "${item.period}"? Tindakan ini tidak dapat dibatalkan.`,
    type: 'danger',
    confirmText: 'Hapus Permanen',
  })
  if (!ok) return
  try {
    await api.delete(`/konfigurasi-meters/${item.id}/force-delete`)
    fetchTrashed()
  } catch (e) { alert(e.response?.data?.message || 'Gagal menghapus data') }
}

function formatDate(d) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function formatDateTime(d) {
  if (!d) return '-'
  return new Date(d).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function statusClass(s) {
  return { active: 'text-bg-success', inactive: 'text-bg-secondary', expired: 'text-bg-danger' }[s] || ''
}

function statusDotClass(s) {
  return { active: 'bg-green-500', inactive: 'bg-gray-400', expired: 'bg-red-500' }[s] || ''
}

function openForm(item = null) {
  formError.value = ''
  documentPreview.value = null
  documentError.value = ''
  existingDocumentPath.value = null
  if (item) {
    editingId.value = item.id
    form.value = { titik_ukur_id: item.titik_ukur_id, jenis_kwh_meter_id: item.jenis_kwh_meter_id, period: item.period, start_date: item.start_date?.split('T')[0] || '', end_date: item.end_date?.split('T')[0] || '', status: item.status, notes: item.notes || '' }
    if (item.document_path) {
      existingDocumentPath.value = item.document_path
      documentPreview.value = { name: item.document_path.split('/').pop(), size: 0, file: null }
    }
  } else {
    editingId.value = null
    form.value = { titik_ukur_id: '', jenis_kwh_meter_id: '', period: '', start_date: '', end_date: '', status: 'active', notes: '' }
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
    const hasDocument = documentPreview.value?.file
    if (hasDocument || editingId.value) {
      const formData = new FormData()
      for (const [key, val] of Object.entries(form.value)) {
        if (val !== '' && val !== null && val !== undefined) formData.append(key, val)
      }
      if (hasDocument) formData.append('document', documentPreview.value.file)

      if (editingId.value) {
        formData.append('_method', 'PUT')
        await api.post(`/konfigurasi-meters/${editingId.value}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
      } else {
        await api.post('/konfigurasi-meters', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
      }
    } else {
      if (editingId.value) await api.put(`/konfigurasi-meters/${editingId.value}`, form.value)
      else await api.post('/konfigurasi-meters', form.value)
    }
    showForm.value = false
    stopFormWatch()
    stopEditing()
    editingRecordId.value = null
    await fetchData()
  } catch (e) { formError.value = e.response?.data?.message || e.response?.data?.errors?.document?.[0] || 'Terjadi kesalahan' } finally { saving.value = false }
}

function closeForm() {
  stopFormWatch()
  stopEditing()
  showForm.value = false
  editingRecordId.value = null
}

async function confirmDelete(item) {
  const ok = await confirmDialogRef.value.show({
    title: 'Hapus Konfigurasi',
    message: `Hapus konfigurasi "${item.period}"?`,
    type: 'danger',
    confirmText: 'Hapus',
  })
  if (!ok) return
  try { await api.delete(`/konfigurasi-meters/${item.id}`); await fetchData() } catch (e) { console.error(e) }
}

function handleDocumentSelect(e) {
  const file = e.target.files[0]
  if (file) validateAndSetDocument(file)
}

function handleDocumentDrop(e) {
  documentDragOver.value = false
  const file = e.dataTransfer.files[0]
  if (file) validateAndSetDocument(file)
}

function validateAndSetDocument(file) {
  documentError.value = ''
  if (file.type !== 'application/pdf') {
    documentError.value = 'Hanya file PDF yang diperbolehkan'
    return
  }
  if (file.size > 2 * 1024 * 1024) {
    documentError.value = 'Ukuran file maksimal 2MB'
    return
  }
  documentPreview.value = { name: file.name, size: file.size, file }
}

function removeDocument() {
  documentPreview.value = null
  documentError.value = ''
  existingDocumentPath.value = null
  if (documentInput.value) documentInput.value.value = ''
}

function formatFileSize(bytes) {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}

function getDocumentUrl(item) {
  const token = localStorage.getItem('token')
  return `/api/konfigurasi-meters/${item.id}/document?token=${token}`
}

async function handleImport() {
  if (!importFile.value) return
  importing.value = true; importResult.value = null
  try {
    const fd = new FormData()
    fd.append('file', importFile.value)
    fd.append('type', 'konfigurasi_meters')
    const { data: res } = await api.post('/import', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    importResult.value = res; importFile.value = null; fetchData()
  } catch (e) { importResult.value = { success: false, message: e.response?.data?.message || 'Import gagal' } }
  finally { importing.value = false }
}

function listenForCrudUpdates() {
  try {
    const echo = initEcho()
    if (!echo) return
    echo.channel('crud.konfigurasi_meter').listen('.updated', () => {
      fetchData()
    })
  } catch (e) {
    console.warn('Crud update listener failed:', e)
  }
}

onMounted(() => { fetchRefs(); fetchData(); listenForCrudUpdates() })

onBeforeUnmount(() => {
  stopFormWatch()
  leaveChannel('crud.konfigurasi_meter')
})
</script>
