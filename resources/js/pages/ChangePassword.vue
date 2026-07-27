<template>
  <div class="page-wrapper">
    <div class="page-body">
      <div class="container-xl" style="max-width: 640px;">
        <div class="page-pretitle mb-2">Akun</div>
        <h2 class="page-title mb-3">Ganti Password</h2>
        <p class="text-muted mb-4">Perbarui password akun anda untuk keamanan</p>

        <div class="card">
          <div class="card-body">
            <div v-if="successMsg" class="alert alert-success d-flex align-items-center mb-3">
              &#10003; {{ successMsg }}
            </div>

            <div v-if="errorMsg" class="alert alert-danger d-flex align-items-center mb-3">
              &#10007; {{ errorMsg }}
            </div>

            <form @submit.prevent="changePassword">
              <div class="mb-3">
                <label class="form-label">Password Saat Ini</label>
                <input v-model="form.current_password" type="password" required class="form-control" />
              </div>
              <div class="mb-3">
                <label class="form-label">Password Baru</label>
                <input v-model="form.password" type="password" required minlength="8" class="form-control" />
                <div class="form-hint">Min 8 karakter, huruf besar + kecil + angka</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input v-model="form.password_confirmation" type="password" required class="form-control" />
              </div>
              <div class="d-flex justify-content-end">
                <button type="submit" :disabled="saving" class="btn btn-primary">
                  {{ saving ? 'Menyimpan...' : 'Ganti Password' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import api from '@/services/api'

const saving = ref(false)
const successMsg = ref('')
const errorMsg = ref('')
const form = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
})

async function changePassword() {
    saving.value = true
    successMsg.value = ''
    errorMsg.value = ''
    try {
        await api.put('/profile/password', form)
        successMsg.value = 'Password berhasil diubah'
        form.current_password = ''
        form.password = ''
        form.password_confirmation = ''
        setTimeout(() => { successMsg.value = '' }, 3000)
    } catch (e) {
        errorMsg.value = e.response?.data?.message || 'Gagal mengubah password'
    } finally {
        saving.value = false
    }
}
</script>
