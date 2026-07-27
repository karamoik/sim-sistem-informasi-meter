<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gardu Induk</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola data gardu induk</p>
      </div>
      <div class="flex items-center gap-2">
        <button
          @click="openForm()"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
          </svg>
          Tambah Gardu Induk
        </button>
        <button @click="toggleTrashed" :class="['inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl transition-colors', showTrashed ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 hover:bg-amber-200 dark:hover:bg-amber-900/50' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600']">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
          {{ showTrashed ? 'Kembali' : 'Data Terhapus' }}
        </button>
      </div>
    </div>

    <!-- Search & Filter -->
    <div class="flex flex-col sm:flex-row gap-3">
      <div class="relative flex-1">
        <input
          v-model="search"
          type="text"
          placeholder="Cari nama, kode, atau region..."
          class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
          @input="fetchData"
        />
        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
        </svg>
      </div>
      <select
        v-model="filterStatus"
        class="text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
        @change="fetchData"
      >
        <option value="">Semua Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>

    <!-- Table -->
    <div v-if="!showTrashed" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Kode</th>
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Nama</th>
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Region</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Total</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Online</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Offline</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Status</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="gi in garduInduks"
              :key="gi.id"
              class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
            >
              <td class="px-6 py-3.5 font-mono text-xs font-semibold text-gray-900 dark:text-white">{{ gi.code }}</td>
              <td class="px-6 py-3.5 font-medium text-gray-900 dark:text-white">{{ gi.name }}</td>
              <td class="px-6 py-3.5 text-gray-600 dark:text-gray-400">{{ gi.region }}</td>
              <td class="px-6 py-3.5 text-center font-medium text-gray-900 dark:text-white">{{ gi.meters_count }}</td>
              <td class="px-6 py-3.5 text-center">
                <span class="text-blue-600 dark:text-blue-400 font-semibold">{{ gi.online_count }}</span>
              </td>
              <td class="px-6 py-3.5 text-center">
                <span :class="gi.offline_count > 0 ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-400 dark:text-gray-500'">{{ gi.offline_count }}</span>
              </td>
              <td class="px-6 py-3.5 text-center">
                <span
                  :class="[
                    'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold',
                    gi.status === 'active'
                      ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                      : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'
                  ]"
                >
                  <span :class="['w-1.5 h-1.5 rounded-full', gi.status === 'active' ? 'bg-green-500' : 'bg-gray-400']"></span>
                  {{ gi.status }}
                </span>
              </td>
              <td class="px-6 py-3.5 text-center">
                <div class="flex items-center justify-center gap-2">
                  <button
                    @click="openForm(gi)"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                    title="Edit"
                  >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                    </svg>
                  </button>
                  <button
                    @click="confirmDelete(gi)"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                    title="Hapus"
                  >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!garduInduks.length">
              <td colspan="8" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                Tidak ada data gardu induk
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-else class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="px-6 py-3 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-800">
        <p class="text-sm font-medium text-amber-700 dark:text-amber-400">Data yang sudah dihapus dapat dipulihkan kembali</p>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Kode</th>
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Nama</th>
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Region</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Status</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Dihapus</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="gi in trashedList" :key="gi.id" class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors opacity-70">
              <td class="px-6 py-3.5 font-mono text-xs font-semibold text-gray-900 dark:text-white">{{ gi.code }}</td>
              <td class="px-6 py-3.5 font-medium text-gray-900 dark:text-white">{{ gi.name }}</td>
              <td class="px-6 py-3.5 text-gray-600 dark:text-gray-400">{{ gi.region }}</td>
              <td class="px-6 py-3.5 text-center">
                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold', gi.status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400']">
                  <span :class="['w-1.5 h-1.5 rounded-full', gi.status === 'active' ? 'bg-green-500' : 'bg-gray-400']"></span>
                  {{ gi.status }}
                </span>
              </td>
              <td class="px-6 py-3.5 text-center text-xs text-gray-500 dark:text-gray-400">{{ formatDateTime(gi.deleted_at) }}</td>
              <td class="px-6 py-3.5 text-center">
                <div class="flex items-center justify-center gap-2">
                  <button @click="restoreItem(gi)" class="p-1.5 rounded-lg text-gray-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors" title="Pulihkan">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/></svg>
                  </button>
                  <button @click="forceDeleteItem(gi)" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Hapus Permanent">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!trashedList.length">
              <td colspan="6" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">Tidak ada data terhapus</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form -->
    <Teleport to="body">
      <div v-if="showForm" class="fixed inset-0 z-[10000] flex items-start sm:items-center justify-center p-4 pt-20">
        <div class="absolute inset-0 bg-black/50" @click="showForm = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
            {{ editingId ? 'Edit Gardu Induk' : 'Tambah Gardu Induk' }}
          </h3>
          <EditingIndicator
            :visible="isEditingByOthers"
            :message="warningMessage"
            :editors="otherEditors"
          />
          <form @submit.prevent="saveForm" class="space-y-4">
            <div class="relative">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full text-sm border rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:border-transparent transition-colors"
                :class="getFieldEditors('name').length
                  ? 'border-amber-400 dark:border-amber-600 focus:ring-amber-500'
                  : 'border-gray-200 dark:border-gray-600 focus:ring-blue-500'"
                placeholder="GI Jakarta Pusat"
                @focus="broadcastFieldEditing('name')"
                @blur="broadcastFieldEditing(null)"
              />
              <FieldEditorBadge v-if="getFieldEditors('name').length" :editors="getFieldEditors('name')" />
            </div>
            <div class="relative">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode</label>
              <input
                v-model="form.code"
                type="text"
                required
                class="w-full text-sm border rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:border-transparent transition-colors"
                :class="getFieldEditors('code').length
                  ? 'border-amber-400 dark:border-amber-600 focus:ring-amber-500'
                  : 'border-gray-200 dark:border-gray-600 focus:ring-blue-500'"
                placeholder="GI-JKT-001"
                @focus="broadcastFieldEditing('code')"
                @blur="broadcastFieldEditing(null)"
              />
              <FieldEditorBadge v-if="getFieldEditors('code').length" :editors="getFieldEditors('code')" />
            </div>
            <div class="relative">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Region</label>
              <SearchableSelect v-model="form.region" :options="regions" placeholder="Pilih region..." />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="relative">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Latitude</label>
                <input
                  v-model="form.latitude"
                  type="number"
                  step="any"
                  required
                  class="w-full text-sm border rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:border-transparent transition-colors"
                  :class="getFieldEditors('latitude').length
                    ? 'border-amber-400 dark:border-amber-600 focus:ring-amber-500'
                    : 'border-gray-200 dark:border-gray-600 focus:ring-blue-500'"
                  placeholder="-6.1751"
                  @focus="broadcastFieldEditing('latitude')"
                  @blur="broadcastFieldEditing(null)"
                />
                <FieldEditorBadge v-if="getFieldEditors('latitude').length" :editors="getFieldEditors('latitude')" />
              </div>
              <div class="relative">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Longitude</label>
                <input
                  v-model="form.longitude"
                  type="number"
                  step="any"
                  required
                  class="w-full text-sm border rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:border-transparent transition-colors"
                  :class="getFieldEditors('longitude').length
                    ? 'border-amber-400 dark:border-amber-600 focus:ring-amber-500'
                    : 'border-gray-200 dark:border-gray-600 focus:ring-blue-500'"
                  placeholder="106.8650"
                  @focus="broadcastFieldEditing('longitude')"
                  @blur="broadcastFieldEditing(null)"
                />
                <FieldEditorBadge v-if="getFieldEditors('longitude').length" :editors="getFieldEditors('longitude')" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
              <select v-model="form.status" class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            <div class="flex gap-3 pt-2">
              <button type="button" @click="closeForm" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition-colors">Batal</button>
              <button type="submit" :disabled="saving" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors disabled:opacity-50">{{ saving ? 'Menyimpan...' : 'Simpan' }}</button>
            </div>
          </form>
          <p v-if="formError" class="mt-3 text-sm text-red-600 dark:text-red-400">{{ formError }}</p>
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
