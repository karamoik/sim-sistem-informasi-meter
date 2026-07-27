<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen User</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola user dan akses unit gardu induk</p>
      </div>
      <button
        @click="openForm()"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Tambah User
      </button>
    </div>

    <!-- Search & Filters -->
    <div class="flex flex-col sm:flex-row gap-3">
      <div class="relative flex-1">
        <input
          v-model="search"
          type="text"
          placeholder="Cari nama, email..."
          class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
          @input="debouncedFetch"
        />
        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
        </svg>
      </div>
      <select
        v-model="filterRole"
        class="text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        @change="fetchData"
      >
        <option value="">Semua Role</option>
        <option value="admin">Admin</option>
        <option value="operator">Operator</option>
        <option value="viewer">Viewer</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Nama</th>
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Email</th>
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Role</th>
              <th class="text-left px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Unit (Gardu Induk)</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Status</th>
              <th class="text-center px-6 py-3 font-semibold text-gray-600 dark:text-gray-300">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="user in users"
              :key="user.id"
              class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
            >
              <td class="px-6 py-3.5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" :class="roleColors[user.role]">
                    {{ user.name[0].toUpperCase() }}
                  </div>
                  <span class="font-medium text-gray-900 dark:text-white">{{ user.name }}</span>
                </div>
              </td>
              <td class="px-6 py-3.5 text-gray-500 dark:text-gray-400">{{ user.email }}</td>
              <td class="px-6 py-3.5">
                <span :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold', roleBadge[user.role]]">
                  {{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}
                </span>
              </td>
              <td class="px-6 py-3.5">
                <div v-if="user.gardu_induks?.length" class="flex flex-wrap gap-1">
                  <span v-for="gi in user.gardu_induks.slice(0, 3)" :key="gi.id" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                    {{ gi.name }}
                  </span>
                  <span v-if="user.gardu_induks.length > 3" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                    +{{ user.gardu_induks.length - 3 }}
                  </span>
                </div>
                <span v-else class="text-xs text-gray-400 dark:text-gray-500 italic">Semua unit (admin)</span>
              </td>
              <td class="px-6 py-3.5 text-center">
                <span
                  :class="[
                    'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold',
                    user.is_active
                      ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                      : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'
                  ]"
                >
                  <span :class="['w-1.5 h-1.5 rounded-full', user.is_active ? 'bg-green-500' : 'bg-gray-400']"></span>
                  {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="px-6 py-3.5 text-center">
                <div class="flex items-center justify-center gap-1">
                  <button
                    @click="openForm(user)"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                    title="Edit"
                  >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                    </svg>
                  </button>
                  <button
                    @click="openUnitAssignment(user)"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors"
                    title="Assign Unit"
                  >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016A3.001 3.001 0 0121 9.349"/>
                    </svg>
                  </button>
                  <button
                    v-if="user.id !== currentUserId"
                    @click="confirmDelete(user)"
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
            <tr v-if="!users.length">
              <td colspan="6" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                Tidak ada data user
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-gray-100 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Menampilkan {{ pagination.from }}-{{ pagination.to }} dari {{ pagination.total }} user
        </p>
        <div class="flex gap-1">
          <button
            v-for="page in pagination.last_page"
            :key="page"
            @click="goToPage(page)"
            :class="[
              'w-8 h-8 rounded-lg text-sm font-medium transition-colors',
              page === pagination.current_page
                ? 'bg-blue-600 text-white'
                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
          >{{ page }}</button>
        </div>
      </div>
    </div>

    <!-- Modal Form -->
    <Teleport to="body">
      <div v-if="showForm" class="fixed inset-0 z-[10000] flex items-start sm:items-center justify-center p-4 pt-20">
        <div class="absolute inset-0 bg-black/50" @click="showForm = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
            {{ editingId ? 'Edit User' : 'Tambah User' }}
          </h3>
          <EditingIndicator
            :visible="isEditingByOthers"
            :message="warningMessage"
            :editors="otherEditors"
          />
          <form @submit.prevent="saveForm" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
              <input v-model="form.name" type="text" required class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="John Doe" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
              <input v-model="form.email" type="email" required class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="user@example.com" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Password {{ editingId ? '(kosongkan jika tidak diubah)' : '' }}
              </label>
              <input v-model="form.password" :type="showPassword ? 'text' : 'password'" :required="!editingId" class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" :placeholder="editingId ? '••••••••' : 'Minimal 8 karakter'" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
              <select v-model="form.role" required class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="admin">Admin - Full Akses</option>
                <option value="operator">Operator - CRUD Data</option>
                <option value="viewer">Viewer - Hanya Lihat</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit Gardu Induk</label>
              <div class="border border-gray-200 dark:border-gray-600 rounded-xl p-3 max-h-40 overflow-y-auto space-y-2">
                <label v-for="gi in allGarduInduks" :key="gi.id" class="flex items-center gap-2 cursor-pointer">
                  <input type="checkbox" :value="gi.id" v-model="form.gardu_induk_ids" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500" />
                  <span class="text-sm text-gray-700 dark:text-gray-300">{{ gi.name }}</span>
                  <span class="text-xs text-gray-400 dark:text-gray-500">({{ gi.code }})</span>
                </label>
              </div>
              <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Admin melihat semua unit. Operator/Viewer hanya unit yang dipilih.</p>
            </div>
            <div class="flex items-center gap-2">
              <input type="checkbox" v-model="form.is_active" id="is_active" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500" />
              <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Akun Aktif</label>
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

    <!-- Modal Unit Assignment -->
    <Teleport to="body">
      <div v-if="showUnitModal" class="fixed inset-0 z-[10000] flex items-start sm:items-center justify-center p-4 pt-20">
        <div class="absolute inset-0 bg-black/50" @click="showUnitModal = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Assign Unit Gardu Induk</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ selectedUser?.name }} ({{ selectedUser?.email }})</p>

          <div class="space-y-2 max-h-60 overflow-y-auto">
            <label v-for="gi in allGarduInduks" :key="gi.id" class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
              <input type="checkbox" :value="gi.id" v-model="unitForm.gardu_induk_ids" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500" />
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ gi.name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ gi.code }} - {{ gi.region }}</p>
              </div>
            </label>
          </div>

          <div class="flex gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <button @click="showUnitModal = false" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition-colors">Batal</button>
            <button @click="saveUnitAssignment" :disabled="saving" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-xl transition-colors disabled:opacity-50">{{ saving ? 'Menyimpan...' : 'Simpan Unit' }}</button>
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
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import { initEcho, leaveChannel } from '@/echo'

const confirmDialogRef = ref(null)
const users = ref([])
const allGarduInduks = ref([])
const search = ref('')
const filterRole = ref('')
const showForm = ref(false)
const showUnitModal = ref(false)
const editingId = ref(null)
const editingRecordId = ref(null)
const { isEditingByOthers, warningMessage, otherEditors, startEditing, stopEditing } = useEditing('user', editingRecordId)
const saving = ref(false)
const formError = ref('')
const pagination = ref({ current_page: 1, last_page: 1, from: 0, to: 0, total: 0 })
const showPassword = ref(false)
const currentUserId = ref(null)
const selectedUser = ref(null)

const form = ref({
  name: '',
  email: '',
  password: '',
  role: 'viewer',
  is_active: true,
  gardu_induk_ids: [],
})

const unitForm = ref({
  gardu_induk_ids: [],
})

const roleColors = {
  admin: 'bg-red-500',
  operator: 'bg-blue-500',
  viewer: 'bg-green-500',
}

const roleBadge = {
  admin: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
  operator: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
  viewer: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
}

let debounceTimer = null

function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchData, 300)
}

async function fetchGarduInduks() {
  try {
    const { data: res } = await api.get('/available-units')
    allGarduInduks.value = res.data
  } catch (e) {
    console.error('Failed to fetch gardu induks', e)
  }
}

async function fetchData(page = 1) {
  try {
    const params = { page }
    if (search.value) params.search = search.value
    if (filterRole.value) params.role = filterRole.value
    const { data: res } = await api.get('/users', { params })
    users.value = res.data.data
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      from: res.data.from,
      to: res.data.to,
      total: res.data.total,
    }
  } catch (e) {
    console.error('Failed to fetch users', e)
  }
}

function goToPage(page) {
  fetchData(page)
}

function openForm(user = null) {
  formError.value = ''
  showPassword.value = false
  if (user) {
    editingId.value = user.id
    form.value = {
      name: user.name,
      email: user.email,
      password: '',
      role: user.role,
      is_active: user.is_active,
      gardu_induk_ids: user.gardu_induks?.map(g => g.id) || [],
    }
  } else {
    editingId.value = null
    form.value = { name: '', email: '', password: '', role: 'viewer', is_active: true, gardu_induk_ids: [] }
  }
  showForm.value = true
  if (editingId.value) {
    editingRecordId.value = editingId.value
    startEditing()
  }
}

function closeForm() {
  stopEditing()
  showForm.value = false
  editingRecordId.value = null
}

function openUnitAssignment(user) {
  selectedUser.value = user
  unitForm.value = {
    gardu_induk_ids: user.gardu_induks?.map(g => g.id) || [],
  }
  showUnitModal.value = true
}

async function saveForm() {
  saving.value = true
  formError.value = ''
  try {
    const payload = { ...form.value }
    if (editingId.value && !payload.password) {
      delete payload.password
    }
    if (editingId.value) {
      await api.put(`/users/${editingId.value}`, payload)
    } else {
      await api.post('/users', payload)
    }
    showForm.value = false
    stopEditing()
    editingRecordId.value = null
    await fetchData(pagination.value.current_page)
  } catch (e) {
    formError.value = e.response?.data?.message || 'Terjadi kesalahan'
  } finally {
    saving.value = false
  }
}

async function saveUnitAssignment() {
  saving.value = true
  try {
    await api.post(`/users/${selectedUser.value.id}/assign-units`, {
      gardu_induk_ids: unitForm.value.gardu_induk_ids,
    })
    showUnitModal.value = false
    await fetchData(pagination.value.current_page)
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menyimpan unit')
  } finally {
    saving.value = false
  }
}

async function confirmDelete(user) {
  const ok = await confirmDialogRef.value.show({
    title: 'Hapus User',
    message: `Hapus user "${user.name}" (${user.email})?`,
    type: 'danger',
    confirmText: 'Hapus',
  })
  if (!ok) return
  try {
    await api.delete(`/users/${user.id}`)
    await fetchData(pagination.value.current_page)
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menghapus user')
  }
}

function listenForCrudUpdates() {
  try {
    const echo = initEcho()
    if (!echo) return
    echo.channel('crud.user').listen('.updated', () => {
      fetchData()
    })
  } catch (e) {
    console.warn('Crud update listener failed:', e)
  }
}

onMounted(async () => {
  try {
    const { data: me } = await api.get('/auth/me')
    currentUserId.value = me.data.id
  } catch {}
  fetchGarduInduks()
  fetchData()
  listenForCrudUpdates()
})

onBeforeUnmount(() => {
  leaveChannel('crud.user')
})
</script>
