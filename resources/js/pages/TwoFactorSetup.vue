<template>
  <div>
    <div class="mb-4">
      <router-link to="/" class="text-muted text-decoration-none small d-inline-flex align-items-center gap-1 mb-3">
        <span>&larr;</span>
        <span>Back to Dashboard</span>
      </router-link>
      <h1 class="h2 fw-bold">Two-Factor Authentication</h1>
      <p class="text-muted small mt-1">Tambahkan lapisan keamanan ekstra ke akun Anda</p>
    </div>

    <!-- Success Alert -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 -translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-1"
    >
      <div v-if="success" class="alert alert-success d-flex align-items-center gap-2 mb-3">
        <span>&#10003;</span>
        <span>{{ success }}</span>
      </div>
    </Transition>

    <!-- Error Alert -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 -translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-1"
    >
      <div v-if="error" class="alert alert-danger d-flex align-items-center gap-2 mb-3">
        <span>!</span>
        <span>{{ error }}</span>
      </div>
    </Transition>

    <!-- Loading State -->
    <div v-if="twoFactorEnabled === null && !setupMode" class="card">
      <div class="card-body text-center py-5">
        <div class="d-flex align-items-center justify-content-center mx-auto mb-4" style="width:80px;height:80px;">
          <div class="spinner-border text-secondary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <h2 class="h5 fw-bold mb-2">Memuat Status 2FA...</h2>
        <p class="text-muted small">Mohon tunggu sebentar</p>
      </div>
    </div>

    <!-- 2FA Status: Not enabled yet -->
    <div v-if="twoFactorEnabled === false && !setupMode" class="card">
      <div class="card-body text-center py-5">
        <div class="d-flex align-items-center justify-content-center mx-auto mb-4" style="width:80px;height:80px;">
          <span class="h1 text-warning mb-0">&#9888;</span>
        </div>
        <h2 class="h5 fw-bold mb-2">2FA Belum Aktif</h2>
        <p class="text-muted small mb-4 mx-auto" style="max-width:24rem;">
          Akun Anda belum dilindungi oleh Two-Factor Authentication. Aktifkan sekarang untuk keamanan ekstra.
        </p>
        <button
          @click="startSetup"
          :disabled="setupLoading"
          class="btn btn-primary d-inline-flex align-items-center gap-2"
        >
          <span v-if="setupLoading" class="spinner-border spinner-border-sm"></span>
          <span>Aktifkan 2FA</span>
        </button>
      </div>
    </div>

    <!-- 2FA Setup: QR Code + Verify -->
    <div v-if="setupMode" class="card">
      <div class="card-body">
        <div class="mx-auto" style="max-width:28rem;">
          <!-- Step 1: Instructions -->
          <div class="text-center mb-4">
            <div class="d-flex align-items-center justify-content-center mx-auto mb-3" style="width:64px;height:64px;">
              <span class="h1 text-primary mb-0">&#9881;</span>
            </div>
            <h2 class="h5 fw-bold mb-2">Setup 2FA</h2>
            <p class="text-muted small">Scan QR code ini menggunakan Google Authenticator</p>
          </div>

          <!-- Steps -->
          <div class="d-flex flex-column gap-3 mb-4">
            <div class="d-flex align-items-start gap-2">
              <div class="rounded-circle bg-blue-lt text-blue d-flex align-items-center justify-content-center flex-shrink-0" style="width:1.5rem;height:1.5rem;">
                <span class="small fw-bold">1</span>
              </div>
              <p class="small text-secondary mb-0">Buka <strong>Google Authenticator</strong> di HP Anda</p>
            </div>
            <div class="d-flex align-items-start gap-2">
              <div class="rounded-circle bg-blue-lt text-blue d-flex align-items-center justify-content-center flex-shrink-0" style="width:1.5rem;height:1.5rem;">
                <span class="small fw-bold">2</span>
              </div>
              <p class="small text-secondary mb-0">Tap <strong>+</strong> lalu pilih <strong>Scan QR Code</strong></p>
            </div>
            <div class="d-flex align-items-start gap-2">
              <div class="rounded-circle bg-blue-lt text-blue d-flex align-items-center justify-content-center flex-shrink-0" style="width:1.5rem;height:1.5rem;">
                <span class="small fw-bold">3</span>
              </div>
              <p class="small text-secondary mb-0">Masukkan kode 6 digit dari aplikasi</p>
            </div>
          </div>

          <!-- QR Code -->
          <div v-if="qrCodeUrl" class="text-center mb-4">
            <div class="d-inline-block p-3 border rounded-3 bg-white">
              <img :src="qrCodeUrl" alt="QR Code 2FA" style="width:12rem;height:12rem;" />
            </div>
          </div>

          <!-- Manual Secret -->
          <div v-if="secret" class="mb-4">
            <p class="text-muted small text-center mb-2">Atau masukkan kode manual:</p>
            <div class="text-center">
              <code class="bg-light px-3 py-2 rounded small user-select-all d-inline-block">{{ secret }}</code>
            </div>
          </div>

          <!-- Verify Code -->
          <div class="bg-light rounded-3 p-4">
            <p class="small fw-medium text-center mb-3">Masukkan kode 6 digit untuk verifikasi:</p>
            <form @submit.prevent="enable2FA">
              <div class="text-center mb-3">
                <input
                  v-model="verifyCode"
                  type="text"
                  inputmode="numeric"
                  maxlength="6"
                  pattern="[0-9]*"
                  required
                  placeholder="000000"
                  class="form-control text-center fw-bold mx-auto"
                  style="width:12rem;font-size:1.5rem;letter-spacing:0.5em;"
                />
              </div>
              <button
                type="submit"
                :disabled="enableLoading || verifyCode.length !== 6"
                class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2"
              >
                <span v-if="enableLoading" class="spinner-border spinner-border-sm"></span>
                <span>{{ enableLoading ? 'Mengaktifkan...' : 'Aktifkan 2FA' }}</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- 2FA Status: Already enabled -->
    <div v-if="twoFactorEnabled === true && !setupMode" class="card">
      <div class="card-body text-center py-5">
        <div class="d-flex align-items-center justify-content-center mx-auto mb-4" style="width:80px;height:80px;">
          <span class="h1 text-success mb-0">&#10003;</span>
        </div>
        <h2 class="h5 fw-bold mb-2">2FA Aktif</h2>
        <p class="text-muted small mb-4 mx-auto" style="max-width:24rem;">
          Akun Anda sudah dilindungi oleh Two-Factor Authentication.
        </p>

        <div class="alert alert-success mx-auto mb-3" style="max-width:24rem;">
          <span class="fw-medium">&#10003; Terlindungi dengan 2FA</span>
        </div>

        <!-- Disable 2FA -->
        <div class="border-top pt-3">
          <p class="small text-muted mb-3">Ingin menonaktifkan 2FA?</p>
          <form v-if="!showDisableForm" @submit.prevent="showDisableForm = true" class="d-inline-block">
            <button type="submit" class="btn btn-link text-danger text-decoration-none p-0 small fw-medium">
              Nonaktifkan 2FA
            </button>
          </form>
          <form v-else @submit.prevent="disable2FA" class="mx-auto" style="max-width:20rem;">
            <div class="mb-3">
              <input
                v-model="disableCode"
                type="text"
                inputmode="numeric"
                maxlength="6"
                pattern="[0-9]*"
                placeholder="Masukkan kode 6 digit"
                class="form-control text-center small"
                style="font-family:monospace;"
              />
            </div>
            <div class="d-flex gap-2">
              <button
                type="button"
                @click="showDisableForm = false; disableCode = ''"
                class="btn btn-outline-secondary flex-fill"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="disableLoading || disableCode.length !== 6"
                class="btn btn-danger flex-fill"
              >
                {{ disableLoading ? '...' : 'Nonaktifkan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const twoFactorEnabled = ref(null)
const setupMode = ref(false)
const setupLoading = ref(false)
const enableLoading = ref(false)
const disableLoading = ref(false)

const secret = ref('')
const qrCodeUrl = ref('')
const verifyCode = ref('')
const disableCode = ref('')
const showDisableForm = ref(false)

const error = ref('')
const success = ref('')

async function checkStatus() {
    try {
        const { data } = await api.get('/auth/2fa/status')
        twoFactorEnabled.value = data.data.enabled
    } catch (e) {
        console.error(e)
        twoFactorEnabled.value = false
    }
}

async function startSetup() {
    setupLoading.value = true
    error.value = ''
    success.value = ''
    try {
        const { data } = await api.post('/auth/2fa/setup')
        secret.value = data.data.secret
        qrCodeUrl.value = data.data.qr_code_url
        setupMode.value = true
    } catch (e) {
        error.value = e.response?.data?.message || 'Gagal memuat setup 2FA'
    } finally {
        setupLoading.value = false
    }
}

async function enable2FA() {
    enableLoading.value = true
    error.value = ''
    success.value = ''
    try {
        await api.post('/auth/2fa/enable', { code: verifyCode.value })
        twoFactorEnabled.value = true
        setupMode.value = false
        success.value = '2FA berhasil diaktifkan! Mulai sekarang, Anda akan memerlukan kode authenticator saat login.'
        verifyCode.value = ''
        secret.value = ''
        qrCodeUrl.value = ''
    } catch (e) {
        error.value = e.response?.data?.message || 'Gagal mengaktifkan 2FA'
    } finally {
        enableLoading.value = false
    }
}

async function disable2FA() {
    disableLoading.value = true
    error.value = ''
    success.value = ''
    try {
        await api.post('/auth/2fa/disable', { code: disableCode.value })
        twoFactorEnabled.value = false
        showDisableForm.value = false
        disableCode.value = ''
        success.value = '2FA berhasil dinonaktifkan.'
    } catch (e) {
        error.value = e.response?.data?.message || 'Gagal menonaktifkan 2FA'
    } finally {
        disableLoading.value = false
    }
}

onMounted(() => checkStatus())
</script>
