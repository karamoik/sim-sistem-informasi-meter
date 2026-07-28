<template>
  <div>
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
      <div>
        <h2 class="page-title">Manajemen User</h2>
        <p class="text-muted">Kelola user dan akses unit gardu induk</p>
      </div>
      <button @click="openForm()" class="btn btn-primary">
        + Tambah User
      </button>
    </div>

    <!-- Search & Filters -->
    <div class="d-flex flex-column flex-sm-row gap-3 mb-4">
      <div class="flex-fill">
        <input
          v-model="search"
          type="text"
          placeholder="Cari nama, email..."
          class="form-control"
          @input="debouncedFetch"
        />
      </div>
      <select
        v-model="filterRole"
        class="form-select"
        style="width: auto;"
        @change="fetchData"
      >
        <option value="">Semua Role</option>
        <option value="admin">Admin</option>
        <option value="operator">Operator</option>
        <option value="viewer">Viewer</option>
      </select>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover table-striped table-vcenter mb-0">
          <thead>
            <tr>
              <th>Nama</th>
              <th>Email</th>
              <th>Role</th>
              <th>Unit (Gardu Induk)</th>
              <th class="text-center">Status</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td>
                <div class="d-flex align-items-center">
                  <span class="avatar avatar-sm me-2" :class="roleColors[user.role]">
                    {{ user.name[0].toUpperCase() }}
                  </span>
                  <span>{{ user.name }}</span>
                </div>
              </td>
              <td class="text-secondary">{{ user.email }}</td>
              <td>
                <span class="badge" :class="roleBadge[user.role]">
                  {{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}
                </span>
              </td>
              <td>
                <div v-if="user.gardu_induks?.length" class="d-flex flex-wrap gap-1">
                  <span v-for="gi in user.gardu_induks.slice(0, 3)" :key="gi.id" class="badge bg-lt">
                    {{ gi.name }}
                  </span>
                  <span v-if="user.gardu_induks.length > 3" class="badge bg-lt">
                    +{{ user.gardu_induks.length - 3 }}
                  </span>
                </div>
                <span v-else class="text-muted fst-italic">Semua unit (admin)</span>
              </td>
              <td class="text-center">
                <span class="badge" :class="user.is_active ? 'text-bg-success' : 'text-bg-secondary'">
                  {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="text-center">
                <div class="d-flex justify-content-center gap-1">
                  <button @click="openForm(user)" class="btn btn-sm btn-outline-primary" title="Edit">Edit</button>
                  <button @click="openUnitAssignment(user)" class="btn btn-sm btn-outline-warning" title="Assign Unit">Unit</button>
                  <button v-if="user.id !== currentUserId" @click="confirmDelete(user)" class="btn btn-sm btn-outline-danger" title="Hapus">Hapus</button>
                </div>
              </td>
            </tr>
            <tr v-if="!users.length">
              <td colspan="6" class="text-center text-muted py-5">
                Tidak ada data user
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="card-footer d-flex align-items-center justify-content-between">
        <p class="text-secondary mb-0">
          Menampilkan {{ pagination.from }}-{{ pagination.to }} dari {{ pagination.total }} user
        </p>
        <div class="d-flex gap-1">
          <button
            v-for="page in pagination.last_page"
            :key="page"
            @click="goToPage(page)"
            class="btn btn-sm"
            :class="page === pagination.current_page ? 'btn-primary' : 'btn-outline-secondary'"
          >{{ page }}</button>
        </div>
      </div>
    </div>

    <!-- Modal Form -->
    <Teleport to="body">
      <div v-if="showForm" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">{{ editingId ? 'Edit User' : 'Tambah User' }}</h5>
              <button type="button" class="btn-close" @click="showForm = false"></button>
            </div>
            <div class="modal-body">
              <EditingIndicator
                :visible="isEditingByOthers"
                :message="warningMessage"
                :editors="otherEditors"
              />
              <form @submit.prevent="saveForm">
                <div class="mb-3">
                  <label class="form-label">Nama Lengkap</label>
                  <input v-model="form.name" type="text" required class="form-control" placeholder="John Doe" />
                </div>
                <div class="mb-3">
                  <label class="form-label">Email</label>
                  <input v-model="form.email" type="email" required class="form-control" placeholder="user@example.com" />
                </div>
                <div class="mb-3">
                  <label class="form-label">
                    Password {{ editingId ? '(kosongkan jika tidak diubah)' : '' }}
                  </label>
                  <input v-model="form.password" :type="showPassword ? 'text' : 'password'" :required="!editingId" class="form-control" :placeholder="editingId ? '••••••••' : 'Minimal 8 karakter'" />
                </div>
                <div class="mb-3">
                  <label class="form-label">Role</label>
                  <select v-model="form.role" required class="form-select">
                    <option value="admin">Admin - Full Akses</option>
                    <option value="operator">Operator - CRUD Data</option>
                    <option value="viewer">Viewer - Hanya Lihat</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Unit Gardu Induk</label>
                  <div class="border rounded p-3" style="max-height: 10rem; overflow-y: auto;">
                    <div v-for="gi in allGarduInduks" :key="gi.id" class="form-check">
                      <input type="checkbox" :value="gi.id" v-model="form.gardu_induk_ids" class="form-check-input" />
                      <label class="form-check-label">
                        {{ gi.name }} <span class="text-muted">({{ gi.code }})</span>
                      </label>
                    </div>
                  </div>
                  <small class="text-muted">Admin melihat semua unit. Operator/Viewer hanya unit yang dipilih.</small>
                </div>
                <div class="form-check mb-3">
                  <input type="checkbox" v-model="form.is_active" id="is_active" class="form-check-input" />
                  <label for="is_active" class="form-check-label">Akun Aktif</label>
                </div>
              </form>
              <p v-if="formError" class="text-danger mt-2 mb-0">{{ formError }}</p>
            </div>
            <div class="modal-footer">
              <button type="button" @click="closeForm" class="btn btn-outline-secondary">Batal</button>
              <button type="button" @click="saveForm" :disabled="saving" class="btn btn-primary">{{ saving ? 'Menyimpan...' : 'Simpan' }}</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modal Unit Assignment -->
    <Teleport to="body">
      <div v-if="showUnitModal" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <div>
                <h5 class="modal-title">Assign Unit Gardu Induk</h5>
                <p class="text-secondary small mb-0">{{ selectedUser?.name }} ({{ selectedUser?.email }})</p>
              </div>
              <button type="button" class="btn-close" @click="showUnitModal = false"></button>
            </div>
            <div class="modal-body">
              <div class="d-flex flex-column gap-2" style="max-height: 15rem; overflow-y: auto;">
                <label v-for="gi in allGarduInduks" :key="gi.id" class="d-flex align-items-center gap-3 p-3 border rounded">
                  <input type="checkbox" :value="gi.id" v-model="unitForm.gardu_induk_ids" class="form-check-input" />
                  <div>
                    <p class="mb-0">{{ gi.name }}</p>
                    <small class="text-muted">{{ gi.code }} - {{ gi.region }}</small>
                  </div>
                </label>
              </div>
            </div>
            <div class="modal-footer">
              <button @click="showUnitModal = false" class="btn btn-outline-secondary">Batal</button>
              <button @click="saveUnitAssignment" :disabled="saving" class="btn btn-warning">{{ saving ? 'Menyimpan...' : 'Simpan Unit' }}</button>
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
