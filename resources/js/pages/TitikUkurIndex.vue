<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Titik Ukur</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola data titik pengukuran</p>
      </div>
      <div class="flex items-center gap-2">
        <button @click="openForm()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
          Tambah Titik Ukur
        </button>
        <button @click="toggleTrashed" :class="['inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl transition-colors', showTrashed ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 hover:bg-amber-200 dark:hover:bg-amber-900/50' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600']">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
          {{ showTrashed ? 'Kembali' : 'Data Terhapus' }}
        </button>
      </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-3">
      <div class="relative flex-1">
        <input v-model="search" type="text" placeholder="Cari nama, kode, alamat..." class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" @input="fetchData" />
        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
      </div>
      <select v-model="filterGardu" class="text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" @change="fetchData">
        <option value="">Semua Gardu Induk</option>
        <option v-for="gi in allGarduInduks" :key="gi.id" :value="gi.id">{{ gi.name }}</option>
      </select>
      <select v-model="filterStatus" class="text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" @change="fetchData">
        <option value="">Semua Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>

    <div v-if="!showTrashed" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Kode</th>
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Nama</th>
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Gardu Induk</th>
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Alamat</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Total Meter</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Status</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="tu in titikUkurs" :key="tu.id" class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
              <td class="px-6 py-3.5 font-mono text-xs font-semibold text-gray-900 dark:text-white">{{ tu.code }}</td>
              <td class="px-6 py-3.5 font-medium text-gray-900 dark:text-white">{{ tu.name }}</td>
              <td class="px-6 py-3.5 text-gray-600 dark:text-gray-400">{{ tu.gardu_induk?.name }}</td>
              <td class="px-6 py-3.5 text-gray-500 dark:text-gray-400 text-xs max-w-[200px] truncate">{{ tu.address || '-' }}</td>
              <td class="px-6 py-3.5 text-center">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 font-semibold text-xs">{{ tu.konfigurasi_meters_count }}</span>
              </td>
              <td class="px-6 py-3.5 text-center">
                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold', tu.status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400']">
                  <span :class="['w-1.5 h-1.5 rounded-full', tu.status === 'active' ? 'bg-green-500' : 'bg-gray-400']"></span>
                  {{ tu.status }}
                </span>
              </td>
              <td class="px-6 py-3.5 text-center">
                <div class="flex items-center justify-center gap-2">
                  <button @click="openForm(tu)" class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors" title="Edit">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                  </button>
                  <button @click="confirmDelete(tu)" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Hapus">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!titikUkurs.length">
              <td colspan="7" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">Tidak ada data titik ukur</td>
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
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Gardu Induk</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Status</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Dihapus</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="tu in trashedList" :key="tu.id" class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors opacity-70">
              <td class="px-6 py-3.5 font-mono text-xs font-semibold text-gray-900 dark:text-white">{{ tu.code }}</td>
              <td class="px-6 py-3.5 font-medium text-gray-900 dark:text-white">{{ tu.name }}</td>
              <td class="px-6 py-3.5 text-gray-600 dark:text-gray-400">{{ tu.gardu_induk?.name }}</td>
              <td class="px-6 py-3.5 text-center">
                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold', tu.status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400']">
                  <span :class="['w-1.5 h-1.5 rounded-full', tu.status === 'active' ? 'bg-green-500' : 'bg-gray-400']"></span>
                  {{ tu.status }}
                </span>
              </td>
              <td class="px-6 py-3.5 text-center text-xs text-gray-500 dark:text-gray-400">{{ formatDateTime(tu.deleted_at) }}</td>
              <td class="px-6 py-3.5 text-center">
                <div class="flex items-center justify-center gap-2">
                  <button @click="restoreItem(tu)" class="p-1.5 rounded-lg text-gray-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors" title="Pulihkan">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/></svg>
                  </button>
                  <button @click="forceDeleteItem(tu)" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Hapus Permanent">
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

    <Teleport to="body">
      <div v-if="showForm" class="fixed inset-0 z-[10000] flex items-start sm:items-center justify-center p-4 pt-20">
        <div class="absolute inset-0 bg-black/50" @click="showForm = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ editingId ? 'Edit Titik Ukur' : 'Tambah Titik Ukur' }}</h3>
          <EditingIndicator
            :visible="isEditingByOthers"
            :message="warningMessage"
            :editors="otherEditors"
          />
          <form @submit.prevent="saveForm" class="space-y-4">
            <div class="relative">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama</label>
              <input v-model="form.name" type="text" required
                class="w-full text-sm border rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:border-transparent transition-colors"
                :class="getFieldEditors('name').length ? 'border-amber-400 dark:border-amber-600 focus:ring-amber-500' : 'border-gray-200 dark:border-gray-600 focus:ring-blue-500'"
                @focus="broadcastFieldEditing('name')" @blur="broadcastFieldEditing(null)" />
              <FieldEditorBadge v-if="getFieldEditors('name').length" :editors="getFieldEditors('name')" />
            </div>
            <div class="relative">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode</label>
              <input v-model="form.code" type="text" required
                class="w-full text-sm border rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:border-transparent transition-colors"
                :class="getFieldEditors('code').length ? 'border-amber-400 dark:border-amber-600 focus:ring-amber-500' : 'border-gray-200 dark:border-gray-600 focus:ring-blue-500'"
                @focus="broadcastFieldEditing('code')" @blur="broadcastFieldEditing(null)" />
              <FieldEditorBadge v-if="getFieldEditors('code').length" :editors="getFieldEditors('code')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gardu Induk</label>
              <select v-model="form.gardu_induk_id" required class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Pilih Gardu Induk</option>
                <option v-for="gi in allGarduInduks" :key="gi.id" :value="gi.id">{{ gi.name }}</option>
              </select>
            </div>
            <div class="relative">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat</label>
              <input v-model="form.address" type="text"
                class="w-full text-sm border rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:border-transparent transition-colors"
                :class="getFieldEditors('address').length ? 'border-amber-400 dark:border-amber-600 focus:ring-amber-500' : 'border-gray-200 dark:border-gray-600 focus:ring-blue-500'"
                @focus="broadcastFieldEditing('address')" @blur="broadcastFieldEditing(null)" />
              <FieldEditorBadge v-if="getFieldEditors('address').length" :editors="getFieldEditors('address')" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="relative">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Latitude</label>
                <input v-model="form.latitude" type="number" step="any"
                  class="w-full text-sm border rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:border-transparent transition-colors"
                  :class="getFieldEditors('latitude').length ? 'border-amber-400 dark:border-amber-600 focus:ring-amber-500' : 'border-gray-200 dark:border-gray-600 focus:ring-blue-500'"
                  @focus="broadcastFieldEditing('latitude')" @blur="broadcastFieldEditing(null)" />
                <FieldEditorBadge v-if="getFieldEditors('latitude').length" :editors="getFieldEditors('latitude')" />
              </div>
              <div class="relative">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Longitude</label>
                <input v-model="form.longitude" type="number" step="any"
                  class="w-full text-sm border rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:border-transparent transition-colors"
                  :class="getFieldEditors('longitude').length ? 'border-amber-400 dark:border-amber-600 focus:ring-amber-500' : 'border-gray-200 dark:border-gray-600 focus:ring-blue-500'"
                  @focus="broadcastFieldEditing('longitude')" @blur="broadcastFieldEditing(null)" />
                <FieldEditorBadge v-if="getFieldEditors('longitude').length" :editors="getFieldEditors('longitude')" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
              <select v-model="form.status" class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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

async function fetchData() {
  try {
    const params = {}
    if (search.value) params.search = search.value
    if (filterGardu.value) params.gardu_induk_id = filterGardu.value
    if (filterStatus.value) params.status = filterStatus.value
    const { data: res } = await api.get('/titik-ukurs', { params })
    titikUkurs.value = res.data
  } catch (e) { console.error(e) }
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
