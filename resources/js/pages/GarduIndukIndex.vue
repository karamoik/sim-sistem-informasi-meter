<template>
  <div>
    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-sm-between gap-3 mb-4">
      <div>
        <h1 class="page-title mb-1">Gardu Induk</h1>
        <p class="text-muted small">Kelola data gardu induk</p>
      </div>
      <div class="d-flex align-items-center gap-2">
        <button
          @click="openForm()"
          class="btn btn-primary d-inline-flex align-items-center gap-2"
        >
          + Tambah Gardu Induk
        </button>
        <button @click="toggleTrashed" :class="showTrashed ? 'btn btn-warning d-inline-flex align-items-center gap-2' : 'btn btn-outline-secondary d-inline-flex align-items-center gap-2'">
          {{ showTrashed ? 'Kembali' : 'Data Terhapus' }}
        </button>
      </div>
    </div>

    <!-- Search & Filter -->
    <div class="d-flex flex-column flex-sm-row gap-3 mb-4">
      <div class="flex-fill">
        <input
          v-model="search"
          type="text"
          placeholder="Cari nama, kode, atau region..."
          class="form-control"
          @input="fetchData"
        />
      </div>
      <select
        v-model="filterStatus"
        class="form-select"
        style="width: auto;"
        @change="fetchData"
      >
        <option value="">Semua Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>

    <!-- Table -->
    <div v-if="!showTrashed" class="card mb-4">
      <div class="table-responsive">
        <table class="table table-hover table-striped table-vcenter">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Nama</th>
              <th>Region</th>
              <th class="text-center">Total</th>
              <th class="text-center">Online</th>
              <th class="text-center">Offline</th>
              <th class="text-center">Status</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="gi in garduInduks"
              :key="gi.id"
            >
              <td class="font-monospace fw-semibold">{{ gi.code }}</td>
              <td class="fw-medium">{{ gi.name }}</td>
              <td>{{ gi.region }}</td>
              <td class="text-center fw-medium">{{ gi.meters_count }}</td>
              <td class="text-center">
                <span class="badge bg-blue">{{ gi.online_count }}</span>
              </td>
              <td class="text-center">
                <span :class="gi.offline_count > 0 ? 'badge bg-red' : 'text-muted'">{{ gi.offline_count }}</span>
              </td>
              <td class="text-center">
                <span
                  :class="gi.status === 'active' ? 'badge bg-success' : 'badge bg-secondary'"
                >
                  {{ gi.status }}
                </span>
              </td>
              <td class="text-center">
                <div class="d-flex align-items-center justify-content-center gap-2">
                  <button
                    @click="openForm(gi)"
                    class="btn btn-sm btn-outline-primary"
                    title="Edit"
                  >
                    Edit
                  </button>
                  <button
                    @click="confirmDelete(gi)"
                    class="btn btn-sm btn-outline-danger"
                    title="Hapus"
                  >
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!garduInduks.length">
              <td colspan="8" class="text-center text-muted py-4">
                Tidak ada data gardu induk
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-else class="card mb-4">
      <div class="card-status card-status-left bg-warning"></div>
      <div class="p-3 border-bottom">
        <p class="small fw-medium text-warning">Data yang sudah dihapus dapat dipulihkan kembali</p>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-striped table-vcenter">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Nama</th>
              <th>Region</th>
              <th class="text-center">Status</th>
              <th class="text-center">Dihapus</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="gi in trashedList" :key="gi.id" class="opacity-75">
              <td class="font-monospace fw-semibold">{{ gi.code }}</td>
              <td class="fw-medium">{{ gi.name }}</td>
              <td>{{ gi.region }}</td>
              <td class="text-center">
                <span :class="gi.status === 'active' ? 'badge bg-success' : 'badge bg-secondary'">
                  {{ gi.status }}
                </span>
              </td>
              <td class="text-center text-muted small">{{ formatDateTime(gi.deleted_at) }}</td>
              <td class="text-center">
                <div class="d-flex align-items-center justify-content-center gap-2">
                  <button @click="restoreItem(gi)" class="btn btn-sm btn-outline-success" title="Pulihkan">
                    Pulihkan
                  </button>
                  <button @click="forceDeleteItem(gi)" class="btn btn-sm btn-outline-danger" title="Hapus Permanen">
                    Hapus Permanen
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

    <!-- Modal Form -->
    <Teleport to="body">
      <div v-if="showForm" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h3 class="modal-title">
                {{ editingId ? 'Edit Gardu Induk' : 'Tambah Gardu Induk' }}
              </h3>
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
                  <label class="form-label">Nama</label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    class="form-control"
                    :class="getFieldEditors('name').length ? 'border-warning' : ''"
                    placeholder="GI Jakarta Pusat"
                    @focus="broadcastFieldEditing('name')"
                    @blur="broadcastFieldEditing(null)"
                  />
                  <FieldEditorBadge v-if="getFieldEditors('name').length" :editors="getFieldEditors('name')" />
                </div>
                <div class="mb-3">
                  <label class="form-label">Kode</label>
                  <input
                    v-model="form.code"
                    type="text"
                    required
                    class="form-control"
                    :class="getFieldEditors('code').length ? 'border-warning' : ''"
                    placeholder="GI-JKT-001"
                    @focus="broadcastFieldEditing('code')"
                    @blur="broadcastFieldEditing(null)"
                  />
                  <FieldEditorBadge v-if="getFieldEditors('code').length" :editors="getFieldEditors('code')" />
                </div>
                <div class="mb-3">
                  <label class="form-label">Region</label>
                  <SearchableSelect v-model="form.region" :options="regions" placeholder="Pilih region..." />
                </div>
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label">Latitude</label>
                    <input
                      v-model="form.latitude"
                      type="number"
                      step="any"
                      required
                      class="form-control"
                      :class="getFieldEditors('latitude').length ? 'border-warning' : ''"
                      placeholder="-6.1751"
                      @focus="broadcastFieldEditing('latitude')"
                      @blur="broadcastFieldEditing(null)"
                    />
                    <FieldEditorBadge v-if="getFieldEditors('latitude').length" :editors="getFieldEditors('latitude')" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Longitude</label>
                    <input
                      v-model="form.longitude"
                      type="number"
                      step="any"
                      required
                      class="form-control"
                      :class="getFieldEditors('longitude').length ? 'border-warning' : ''"
                      placeholder="106.8650"
                      @focus="broadcastFieldEditing('longitude')"
                      @blur="broadcastFieldEditing(null)"
                    />
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
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" @click="closeForm" class="btn btn-outline-secondary flex-fill">Batal</button>
              <button type="button" @click="saveForm" :disabled="saving" class="btn btn-primary flex-fill">{{ saving ? 'Menyimpan...' : 'Simpan' }}</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <Toast ref="toastRef" />
    <ConfirmDialog ref="confirmDialogRef" />
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import api from '@/services/api'
import { useEditing } from '@/composables/useEditing'
import EditingIndicator from '@/components/EditingIndicator.vue'
import FieldEditorBadge from '@/components/FieldEditorBadge.vue'
import Toast from '@/components/Toast.vue'
import SearchableSelect from '@/components/SearchableSelect.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import { initEcho, leaveChannel } from '@/echo'

const toastRef = ref(null)
const confirmDialogRef = ref(null)

const garduInduks = ref([])
const regions = ref([])
const search = ref('')
const filterStatus = ref('')
const showTrashed = ref(false)
const trashedList = ref([])
const showForm = ref(false)
const editingId = ref(null)
const editingRecordId = ref(null)
const {
  isEditingByOthers, warningMessage, otherEditors, otherFieldChanges,
  startEditing, stopEditing,
  broadcastFieldChange, broadcastFieldEditing, getFieldEditors, applyRemoteChanges
} = useEditing('gardu_induk', editingRecordId)
const saving = ref(false)
const formError = ref('')

const form = ref({
  name: '',
  code: '',
  region: '',
  latitude: '',
  longitude: '',
  status: 'active',
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

async function fetchData() {
  try {
    const params = {}
    if (search.value) params.search = search.value
    if (filterStatus.value) params.status = filterStatus.value
    const { data: res } = await api.get('/gardu-induks', { params })
    garduInduks.value = res.data
  } catch (e) {
    console.error('Failed to fetch gardu induks', e)
  }
}

async function fetchTrashed() {
  try {
    const { data: res } = await api.get('/gardu-induks/trashed')
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
    message: `Pulihkan gardu induk "${item.name}"?`,
    type: 'warning',
    confirmText: 'Pulihkan',
  })
  if (!ok) return
  try {
    await api.post(`/gardu-induks/${item.id}/restore`)
    fetchTrashed()
    fetchData()
    toastRef.value?.toast('Berhasil memulihkan data', 'success')
  } catch (e) { alert(e.response?.data?.message || 'Gagal memulihkan data') }
}

async function forceDeleteItem(item) {
  const ok = await confirmDialogRef.value.show({
    title: 'Hapus Permanen',
    message: `Hapus permanent gardu induk "${item.name}"? Tindakan ini tidak dapat dibatalkan.`,
    type: 'danger',
    confirmText: 'Hapus Permanen',
  })
  if (!ok) return
  try {
    await api.delete(`/gardu-induks/${item.id}/force-delete`)
    fetchTrashed()
    toastRef.value?.toast('Berhasil menghapus permanent', 'success')
  } catch (e) { alert(e.response?.data?.message || 'Gagal menghapus data') }
}

function formatDateTime(d) {
  if (!d) return '-'
  return new Date(d).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

async function fetchRegions() {
  try {
    const { data: res } = await api.get('/gardu-induks/regions')
    regions.value = res.data
  } catch (e) {
    console.error('Failed to fetch regions', e)
  }
}

function openForm(gi = null) {
  formError.value = ''
  if (gi) {
    editingId.value = gi.id
    form.value = { name: gi.name, code: gi.code, region: gi.region, latitude: gi.latitude, longitude: gi.longitude, status: gi.status }
  } else {
    editingId.value = null
    form.value = { name: '', code: '', region: '', latitude: '', longitude: '', status: 'active' }
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
      await api.put(`/gardu-induks/${editingId.value}`, form.value)
      toastRef.value?.toast('Gardu induk berhasil diperbarui', 'success')
    } else {
      await api.post('/gardu-induks', form.value)
      toastRef.value?.toast('Gardu induk berhasil ditambahkan', 'success')
    }
    showForm.value = false
    stopFormWatch()
    stopEditing()
    editingRecordId.value = null
    await fetchData()
  } catch (e) {
    formError.value = e.response?.data?.message || 'Terjadi kesalahan'
    toastRef.value?.toast(e.response?.data?.message || 'Gagal menyimpan data', 'error')
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

async function confirmDelete(gi) {
  const ok = await confirmDialogRef.value.show({
    title: 'Hapus Gardu Induk',
    message: `Hapus gardu induk "${gi.name}"? Semua meter terkait juga akan dihapus.`,
    type: 'danger',
    confirmText: 'Hapus',
  })
  if (!ok) return
  try {
    await api.delete(`/gardu-induks/${gi.id}`)
    toastRef.value?.toast('Gardu induk berhasil dihapus', 'success')
    await fetchData()
  } catch (e) {
    console.error('Failed to delete', e)
    toastRef.value?.toast('Gagal menghapus data', 'error')
  }
}

function listenForCrudUpdates() {
  try {
    const echo = initEcho()
    if (!echo) return
    echo.channel('crud.gardu_induk').listen('.updated', () => {
      fetchData()
    })
  } catch (e) {
    console.warn('Crud update listener failed:', e)
  }
}

onMounted(() => {
  fetchData()
  fetchRegions()
  listenForCrudUpdates()
})

onBeforeUnmount(() => {
  stopFormWatch()
  leaveChannel('crud.gardu_induk')
})
</script>
