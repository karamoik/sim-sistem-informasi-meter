<template>
  <div class="page page-center">
    <div class="page-simple" style="max-width: 420px; width: 100%;">
      <!-- Mobile Logo -->
      <div class="d-none d-lg-none d-flex align-items-center mb-4">
        <div class="avatar avatar-lg bg-green text-white d-flex align-items-center justify-content-center">
          <span style="font-size: 1.5rem;">&#128274;</span>
        </div>
        <span class="d-flex flex-column lh-1 ms-2">
          <span class="fw-bold fs-3">SIM</span>
          <span class="text-secondary small">Sistem Informasi Meter</span>
        </span>
      </div>

      <div class="card shadow-lg">
        <div class="card-body p-4">
          <div class="text-center mb-4">
            <div class="avatar avatar-xl bg-green-lt text-green d-flex align-items-center justify-content-center mx-auto mb-3">
              <span style="font-size: 2rem;">&#128274;</span>
            </div>
            <h2 class="card-title fw-bold text-center mb-1">Verifikasi 2FA</h2>
            <p class="text-secondary text-center">Masukkan kode dari Google Authenticator</p>
          </div>

          <!-- Error Alert -->
          <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-1"
          >
            <div v-if="error" class="alert alert-danger d-flex align-items-center mb-3">
              <span class="me-2" style="font-size: 1.2rem;">&#9888;</span>
              <span>{{ error }}</span>
            </div>
          </Transition>

          <form @submit.prevent="verify">
            <div class="mb-4">
              <label class="form-label fw-medium text-center d-block">Kode Verifikasi</label>
              <div class="d-flex justify-content-center">
                <input
                  v-model="code"
                  type="text"
                  inputmode="numeric"
                  maxlength="6"
                  pattern="[0-9]*"
                  autocomplete="one-time-code"
                  required
                  placeholder="000000"
                  class="form-control text-center"
                  style="max-width: 220px; font-size: 1.75rem; font-family: monospace; font-weight: bold; letter-spacing: 0.5em;"
                />
              </div>
            </div>

            <button
              type="submit"
              :disabled="loading || code.length !== 6"
              class="btn btn-primary w-100"
            >
              <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
              <span>{{ loading ? 'Memverifikasi...' : 'Verifikasi' }}</span>
            </button>
          </form>

          <div class="text-center mt-4">
            <button
              @click="backToLogin"
              class="btn btn-link text-secondary text-decoration-none p-0"
            >
              Kembali ke login
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()
const loading = ref(false)
const error = ref('')
const code = ref('')

async function verify() {
    loading.value = true
    error.value = ''
    try {
        const twoFactorToken = localStorage.getItem('two_factor_token')
        const { data } = await api.post('/auth/2fa/verify', {
            code: code.value,
            two_factor_token: twoFactorToken,
        })
        localStorage.removeItem('two_factor_token')
        localStorage.setItem('token', data.data.access_token)
        localStorage.setItem('user', JSON.stringify(data.data.user))
        router.push({ name: 'dashboard' })
    } catch (e) {
        error.value = e.response?.data?.message || 'Verifikasi gagal'
    } finally {
        loading.value = false
    }
}

function backToLogin() {
    localStorage.removeItem('two_factor_token')
    router.push({ name: 'login' })
}
</script>
