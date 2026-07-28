<template>
  <div class="page page-center">
    <div class="container-xl" style="max-width: 480px;">
      <div class="text-center mb-4">
        <div class="avatar avatar-lg rounded-3 mx-auto mb-3" :style="{ background: 'linear-gradient(135deg,#4263eb,#7c3aed)' }">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-key" width="28" height="28" viewBox="0 0 24 24" stroke-width="2" stroke="white" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M16.25 11.25l-4.5 -4.5l-7.5 7.5a6 6 0 0 0 8.486 8.486l7.5 -7.5" /><circle cx="12.5" cy="7.5" r="1.5" />
            <path d="M18.5 10l-1.5 -1.5" />
          </svg>
        </div>
      </div>

      <h2 class="h3 text-center mb-2">Reset Password</h2>
      <p class="text-secondary text-center mb-4">
        Masukkan email yang terdaftar, kami akan kirimkan link reset password.
      </p>

      <div v-if="error" class="alert alert-danger d-flex align-items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-alert-circle" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
          <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 8l0 4" /><path d="M12 16l.01 0" />
        </svg>
        <span>{{ error }}</span>
      </div>

      <div v-if="successMsg" class="alert alert-success">
        <div class="d-flex align-items-center gap-2 mb-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-check" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M5 12l5 5l10 -10" />
          </svg>
          <strong>Link berhasil dikirim!</strong>
        </div>
        <p class="small mb-2">{{ successMsg }}</p>
        <div v-if="resetLink" class="bg-body rounded border p-3">
          <p class="text-secondary small mb-1">Link reset password (klik untuk reset):</p>
          <a :href="resetLink" class="small font-monospace text-break">
            {{ resetLink }}
          </a>
        </div>
      </div>

      <form v-if="!successMsg" @submit.prevent="sendLink">
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

        <button
          type="submit"
          :disabled="loading"
          class="btn btn-primary w-100"
        >
          <svg v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></svg>
          {{ loading ? 'Mengirim...' : 'Kirim Link Reset' }}
        </button>
      </form>

      <div class="text-center mt-4">
        <router-link to="/login" class="text-secondary text-decoration-none small">
          &larr; Kembali ke login
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import api from '@/services/api'

const loading = ref(false)
const error = ref('')
const successMsg = ref('')
const resetLink = ref('')

const form = reactive({
    email: '',
})

async function sendLink() {
    loading.value = true
    error.value = ''
    successMsg.value = ''
    resetLink.value = ''
    try {
        const { data } = await api.post('/auth/forgot-password', form)
        successMsg.value = data.message
        resetLink.value = data.data.reset_link
    } catch (e) {
        error.value = e.response?.data?.message || 'Gagal mengirim link reset password'
    } finally {
        loading.value = false
    }
}
</script>
