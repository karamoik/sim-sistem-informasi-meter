<template>
  <div>
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
      <div>
        <h2 class="page-title">Titik Ukur</h2>
        <p class="text-muted">Kelola data titik pengukuran</p>
      </div>
      <div class="d-flex gap-2">
        <button @click="openForm()" class="btn btn-primary">
          + Tambah Titik Ukur
        </button>
        <button @click="showImport = true" class="btn btn-outline-secondary">Impor CSV</button>
        <a :href="'/api/export/titik-ukurs'" class="btn btn-outline-secondary" target="_blank">Ekspor CSV</a>
        <button @click="toggleTrashed" :class="showTrashed ? 'btn btn-warning' : 'btn btn-outline-secondary'">
          {{ showTrashed ? 'Kembali' : 'Data Terhapus' }}
        </button>
      </div>
    </div>

    <div class="d-flex flex-column flex-sm-row gap-3 mb-4">
      <div class="flex-fill">
        <input v-model="search" type="text" placeholder="Cari nama, kode, alamat..." class="form-control" @input="fetchData" />
      </div>
      <select v-model="filterGardu" class="form-select" style="width: auto;" @change="fetchData">
        <option value="">Semua Gardu Induk</option>
        <option v-for="gi in allGarduInduks" :key="gi.id" :value="gi.id">{{ gi.name }}</option>
      </select>
      <select v-model="filterStatus" class="form-select" style="width: auto;" @change="fetchData">
        <option value="">Semua Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>

    <div v-if="!showTrashed" class="card mb-4">
      <div class="table-responsive">
        <table class="table table-hover table-striped table-vcenter">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Nama</th>
              <th>Gardu Induk</th>
              <th>Alamat</th>
              <th class="text-center">Total Meter</th>
              <th class="text-center">Status</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="tu in titikUkurs" :key="tu.id">
              <td class="font-monospace fw-semibold small">{{ tu.code }}</td>
              <td class="fw-medium">{{ tu.name }}</td>
              <td>{{ tu.gardu_induk?.name }}</td>
              <td class="small text-truncate" style="max-width:200px;">{{ tu.address || '-' }}</td>
              <td class="text-center">
                <span class="badge bg-secondary">{{ tu.konfigurasi_meters_count }}</span>
              </td>
              <td class="text-center">
                <span :class="tu.status === 'active' ? 'badge bg-success' : 'badge bg-secondary'">
                  {{ tu.status }}
                </span>
              </td>
              <td class="text-center">
                <div class="d-inline-flex gap-1">
                  <button @click="openForm(tu)" class="btn btn-sm btn-outline-primary" title="Edit">
                    Edit
                  </button>
                  <button @click="confirmDelete(tu)" class="btn btn-sm btn-outline-danger" title="Hapus">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!titikUkurs.length">
              <td colspan="7" class="text-center text-muted py-4">Tidak ada data titik ukur</td>
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
      <div class="card-status alert alert-warning mb-0">
        <span class="small fw-medium">Data yang sudah dihapus dapat dipulihkan kembali</span>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-striped table-vcenter">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Nama</th>
              <th>Gardu Induk</th>
              <th class="text-center">Status</th>
              <th class="text-center">Dihapus</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="opacity-75">
            <tr v-for="tu in trashedList" :key="tu.id">
              <td class="font-monospace fw-semibold small">{{ tu.code }}</td>
              <td class="fw-medium">{{ tu.name }}</td>
              <td>{{ tu.gardu_induk?.name }}</td>
              <td class="text-center">
                <span :class="tu.status === 'active' ? 'badge bg-success' : 'badge bg-secondary'">
                  {{ tu.status }}
                </span>
              </td>
              <td class="text-center small text-muted">{{ formatDateTime(tu.deleted_at) }}</td>
              <td class="text-center">
                <div class="d-inline-flex gap-1">
                  <button @click="restoreItem(tu)" class="btn btn-sm btn-outline-success" title="Pulihkan">
                    Pulihkan
                  </button>
                  <button @click="forceDeleteItem(tu)" class="btn btn-sm btn-outline-danger" title="Hapus Permanent">
                    Hapus
                  </button>
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

    <Teleport to="body">
      <div v-if="showForm" class="modal d-block" style="background:rgba(0,0,0,.5);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h3 class="modal-title">{{ editingId ? 'Edit Titik Ukur' : 'Tambah Titik Ukur' }}</h3>
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
                  <input v-model="form.name" type="text" required class="form-control"
                    :class="getFieldEditors('name').length ? 'is-warning' : ''"
                    @focus="broadcastFieldEditing('name')" @blur="broadcastFieldEditing(null)" />
                  <FieldEditorBadge v-if="getFieldEditors('name').length" :editors="getFieldEditors('name')" />
                </div>
                <div class="mb-3">
                  <label class="form-label">Kode</label>
                  <input v-model="form.code" type="text" required class="form-control"
                    :class="getFieldEditors('code').length ? 'is-warning' : ''"
                    @focus="broadcastFieldEditing('code')" @blur="broadcastFieldEditing(null)" />
                  <FieldEditorBadge v-if="getFieldEditors('code').length" :editors="getFieldEditors('code')" />
                </div>
                <div class="mb-3">
                  <label class="form-label">Gardu Induk</label>
                  <select v-model="form.gardu_induk_id" required class="form-select">
                    <option value="">Pilih Gardu Induk</option>
                    <option v-for="gi in allGarduInduks" :key="gi.id" :value="gi.id">{{ gi.name }}</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Alamat</label>
                  <input v-model="form.address" type="text" class="form-control"
                    :class="getFieldEditors('address').length ? 'is-warning' : ''"
                    @focus="broadcastFieldEditing('address')" @blur="broadcastFieldEditing(null)" />
                  <FieldEditorBadge v-if="getFieldEditors('address').length" :editors="getFieldEditors('address')" />
                </div>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Latitude</label>
                    <input v-model="form.latitude" type="number" step="any" class="form-control"
                      :class="getFieldEditors('latitude').length ? 'is-warning' : ''"
                      @focus="broadcastFieldEditing('latitude')" @blur="broadcastFieldEditing(null)" />
                    <FieldEditorBadge v-if="getFieldEditors('latitude').length" :editors="getFieldEditors('latitude')" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Longitude</label>
                    <input v-model="form.longitude" type="number" step="any" class="form-control"
                      :class="getFieldEditors('longitude').length ? 'is-warning' : ''"
                      @focus="broadcastFieldEditing('longitude')" @blur="broadcastFieldEditing(null)" />
                    <FieldEditorBadge v-if="getFieldEditors('longitude').length" :editors="getFieldEditors('longitude')" />
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Status</label>
                  <select v-model="form.status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
                <div v-if="formError" class="text-danger small mb-3">{{ formError }}</div>
                <div class="d-flex gap-3 pt-2">
                  <button type="button" @click="closeForm" class="flex-fill btn btn-outline-secondary">Batal</button>
                  <button type="submit" :disabled="saving" class="flex-fill btn btn-primary">
                    {{ saving ? 'Menyimpan...' : 'Simpan' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <ConfirmDialog ref="confirmDialogRef" />

    <Teleport to="body">
      <div v-if="showImport" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Impor Data Titik Ukur</h5>
              <button type="button" class="btn-close" @click="showImport = false"></button>
            </div>
            <div class="modal-body">
              <p class="text-muted small mb-3">Upload file CSV dengan format yang sesuai template.</p>
              <div class="mb-3">
                <label class="form-label">Pilih File CSV</label>
                <input type="file" accept=".csv,.txt" class="form-control" @change="importFile = $event.target.files[0]" />
              </div>
              <a :href="'/api/import/template?type=titik_ukurs'" class="text-decoration-none small" target="_blank">Download Template</a>
              <div v-if="importResult" :class="['alert', importResult.success !== false ? 'alert-success' : 'alert-danger']" class="mt-3">
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
const titikUkurs = ref([])
const allGarduInduks = ref([])
const search = ref('')
const filterGardu = ref('')
const filterStatus = ref('')
const showTrashed = ref(false)
const trashedList = ref([])
const showForm = ref(false)
const editingId = ref(null)
const editingRecordId = ref(null)
const pagination = ref({ current_page: 1, last_page: 1, from: 0, to: 0, total: 0 })
const showImport = ref(false)
const importFile = ref(null)
const importing = ref(false)
const importResult = ref(null)
const { isEditingByOthers, warningMessage, otherEditors, otherFieldChanges, startEditing, stopEditing, broadcastFieldChange, broadcastFieldEditing, getFieldEditors, applyRemoteChanges } = useEditing('titik_ukur', editingRecordId)
const saving = ref(false)
const formError = ref('')

const form = ref({ name: '', code: '', gardu_induk_id: '', address: '', latitude: '', longitude: '', status: 'active' })

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

async function fetchGarduInduks() {
  try { const { data: res } = await api.get('/gardu-induks'); allGarduInduks.value = res.data } catch (e) { console.error(e) }
}

async function fetchData(page = 1) {
  try {
    const params = { page }
    if (search.value) params.search = search.value
    if (filterGardu.value) params.gardu_induk_id = filterGardu.value
    if (filterStatus.value) params.status = filterStatus.value
    const { data: res } = await api.get('/titik-ukurs', { params })
    titikUkurs.value = res.data.data
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      from: res.data.from, to: res.data.to, total: res.data.total,
    }
  } catch (e) { console.error(e) }
}

function goToPage(page) { fetchData(page) }

async function handleImport() {
  if (!importFile.value) return
  importing.value = true; importResult.value = null
  try {
    const fd = new FormData()
    fd.append('file', importFile.value)
    fd.append('type', 'titik_ukurs')
    const { data: res } = await api.post('/import', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    importResult.value = res; importFile.value = null; fetchData()
  } catch (e) { importResult.value = { success: false, message: e.response?.data?.message || 'Import gagal' } }
  finally { importing.value = false }
}

async function fetchTrashed() {
  try {
    const { data: res } = await api.get('/titik-ukurs/trashed')
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
    message: `Pulihkan titik ukur "${item.name}"?`,
    type: 'warning',
    confirmText: 'Pulihkan',
  })
  if (!ok) return
  try {
    await api.post(`/titik-ukurs/${item.id}/restore`)
    fetchTrashed()
    fetchData()
  } catch (e) { alert(e.response?.data?.message || 'Gagal memulihkan data') }
}

async function forceDeleteItem(item) {
  const ok = await confirmDialogRef.value.show({
    title: 'Hapus Permanen',
    message: `Hapus permanent titik ukur "${item.name}"? Tindakan ini tidak dapat dibatalkan.`,
    type: 'danger',
    confirmText: 'Hapus Permanen',
  })
  if (!ok) return
  try {
    await api.delete(`/titik-ukurs/${item.id}/force-delete`)
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
    form.value = { name: item.name, code: item.code, gardu_induk_id: item.gardu_induk_id, address: item.address || '', latitude: item.latitude || '', longitude: item.longitude || '', status: item.status }
  } else {
    editingId.value = null
    form.value = { name: '', code: '', gardu_induk_id: '', address: '', latitude: '', longitude: '', status: 'active' }
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
    if (editingId.value) await api.put(`/titik-ukurs/${editingId.value}`, form.value)
    else await api.post('/titik-ukurs', form.value)
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
    title: 'Hapus Titik Ukur',
    message: `Hapus titik ukur "${item.name}"?`,
    type: 'danger',
    confirmText: 'Hapus',
  })
  if (!ok) return
  try { await api.delete(`/titik-ukurs/${item.id}`); await fetchData() } catch (e) { console.error(e) }
}

function listenForCrudUpdates() {
  try {
    const echo = initEcho()
    if (!echo) return
    echo.channel('crud.titik_ukur').listen('.updated', () => {
      fetchData()
    })
  } catch (e) {
    console.warn('Crud update listener failed:', e)
  }
}

onMounted(() => { fetchGarduInduks(); fetchData(); listenForCrudUpdates() })

onBeforeUnmount(() => {
  stopFormWatch()
  leaveChannel('crud.titik_ukur')
})
</script>
