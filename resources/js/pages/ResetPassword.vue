<template>
  <div class="page page-center">
    <div class="container container-tight py-4">
      <div class="card card-lg">
        <div class="card-body p-5">
          <div class="text-center mb-3">
            <div class="fw-bold">SIM</div>
            <div class="text-secondary small">Sistem Informasi Meter</div>
          </div>
          <h2 class="card-title text-center mb-2">Reset Password</h2>
          <p class="text-secondary text-center mb-4">
            Masukkan password baru Anda.
          </p>

          <div v-if="error" class="alert alert-danger d-flex align-items-center" role="alert">
            <span>{{ error }}</span>
          </div>

          <div v-if="successMsg" class="text-center py-4 mb-4">
            <div class="text-success mb-3" style="font-size: 2rem;">&#10003;</div>
            <h3 class="fw-bold mb-1">Berhasil!</h3>
            <p class="text-secondary mb-4">{{ successMsg }}</p>
            <router-link
              to="/login"
              class="btn btn-primary"
            >
              Login Sekarang
            </router-link>
          </div>

          <form v-if="!successMsg" @submit.prevent="resetPassword">
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input
                v-model="form.email"
                type="email"
                required
                placeholder="nama@email.com"
                class="form-control"
              />
            </div>

            <div class="mb-3">
              <label class="form-label">Password Baru</label>
              <div class="input-group">
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  placeholder="Minimal 8 karakter"
                  class="form-control"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="btn btn-outline-secondary"
                >
                  {{ showPassword ? 'Sembunyikan' : 'Tampilkan' }}
                </button>
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label">Konfirmasi Password</label>
              <input
                v-model="form.password_confirmation"
                :type="showPassword ? 'text' : 'password'"
                required
                placeholder="Ulangi password baru"
                class="form-control"
              />
            </div>

            <button
              type="submit"
              :disabled="loading"
              class="btn btn-primary w-100"
            >
              <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
              <span>{{ loading ? 'Meriset...' : 'Reset Password' }}</span>
            </button>
          </form>

          <div class="text-center mt-3">
            <router-link to="/login" class="text-decoration-none">
              &larr; Kembali ke login
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'

const route = useRoute()

const loading = ref(false)
const error = ref('')
const successMsg = ref('')
const showPassword = ref(false)

const form = reactive({
    email: '',
    token: '',
    password: '',
    password_confirmation: '',
})

onMounted(() => {
    form.email = route.query.email || ''
    form.token = route.query.token || ''
})

async function resetPassword() {
    loading.value = true
    error.value = ''
    try {
        const { data } = await api.post('/auth/reset-password', form)
        successMsg.value = data.message
    } catch (e) {
        error.value = e.response?.data?.message || 'Gagal mereset password'
    } finally {
        loading.value = false
    }
}
</script>
