<template>
  <div class="page-wrapper">
    <div class="page-body">
      <div class="container-xl" style="max-width: 720px;">
        <div class="page-pretitle">Akun</div>
        <h2 class="page-title">Profil Saya</h2>

        <!-- Profile Card -->
        <div class="card mb-4">
          <div class="card-cover" style="height: 96px; background: linear-gradient(135deg, #2563eb, #4f46e5);"></div>
          <div class="card-body">
            <div class="d-flex align-items-end gap-3 mt-n6">
              <div class="avatar avatar-lg bg-white border-4 border-white shadow-lg d-flex align-items-center justify-content-center">
                <span class="fw-bold" style="color: #2563eb;">{{ initials }}</span>
              </div>
              <div class="pb-1">
                <h3 class="card-title mb-0">{{ form.name }}</h3>
                <div class="text-secondary">{{ form.email }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit Form -->
        <div class="card mb-4">
          <div class="card-header">
            <h3 class="card-title">Informasi Profil</h3>
          </div>
          <div class="card-body">

            <div v-if="successMsg" class="alert alert-success d-flex align-items-center gap-2">
              &#10003; {{ successMsg }}
            </div>

            <form @submit.prevent="updateProfile">
              <div class="mb-3">
                <label class="form-label">Nama</label>
                <input v-model="form.name" type="text" required class="form-control" />
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input v-model="form.email" type="email" required class="form-control" />
              </div>
              <div class="d-flex justify-content-end">
                <button type="submit" :disabled="saving" class="btn btn-primary">
                  {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Account Info -->
        <div class="card mb-4">
          <div class="card-header">
            <h3 class="card-title">Informasi Akun</h3>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-sm-6">
                <div class="text-secondary">Role</div>
                <div class="fw-medium">{{ role }}</div>
              </div>
              <div class="col-sm-6">
                <div class="text-secondary">Status</div>
                <div class="fw-medium text-success">Active</div>
              </div>
              <div class="col-sm-6">
                <div class="text-secondary">Member Sejak</div>
                <div class="fw-medium">{{ formatDate(profile?.created_at) }}</div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

const profile = ref(null)
const form = ref({ name: '', email: '' })
const saving = ref(false)
const successMsg = ref('')
const role = ref('')

const initials = computed(() => {
    const n = form.value.name || ''
    return n.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
})

function formatDate(dt) {
    if (!dt) return '-'
    return new Date(dt).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}

async function fetchProfile() {
    try {
        const { data } = await api.get('/profile')
        profile.value = data.data
        form.value.name = data.data.name
        form.value.email = data.data.email
        role.value = data.data.role
    } catch (e) {
        console.error(e)
    }
}

async function updateProfile() {
    saving.value = true
    successMsg.value = ''
    try {
        await api.put('/profile', form.value)
        successMsg.value = 'Profil berhasil diperbarui'
        const user = JSON.parse(localStorage.getItem('user'))
        if (user) {
            user.name = form.value.name
            user.email = form.value.email
            localStorage.setItem('user', JSON.stringify(user))
        }
        setTimeout(() => { successMsg.value = '' }, 3000)
    } catch (e) {
        console.error(e)
    } finally {
        saving.value = false
    }
}

onMounted(fetchProfile)
</script>
