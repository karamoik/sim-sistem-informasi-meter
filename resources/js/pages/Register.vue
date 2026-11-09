<template>
  <div class="page page-center">
    <div class="page wrapper py-4">
      <div class="row g-0">
        <!-- Left Panel - Branding -->
        <div class="col-lg-6 d-none d-lg-flex">
          <div class="flex-fill bg-purple text-white p-5 d-flex flex-column justify-content-center"
               style="background: linear-gradient(135deg, #5f3dc4, #7c3aed, #6d28d9); min-height: 100vh;">
            <div class="mb-4">
              <h1 class="display-5 fw-bold mb-3">Bergabung<br/>dengan SIM</h1>
              <p class="fs-5 text-white-50">
                Sistem Informasi Meter untuk pemantauan, pencatatan, dan pengelolaan data kWh meter secara terpadu.
              </p>
            </div>
            <div class="d-flex gap-4 text-white-50 small">
              <span>Monitoring real-time</span>
              <span>Data terpusat</span>
            </div>
          </div>
        </div>

        <!-- Right Panel - Form -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center p-4">
          <div class="w-100" style="max-width: 26rem;">
            <div class="text-primary fw-semibold small mb-1">SIM Sistem Informasi Meter</div>
            <h2 class="card-title mb-1">Buat Akun</h2>
            <p class="text-muted mb-4">Lengkapi data di bawah ini untuk mulai menggunakan SIM</p>

            <div class="card">
              <div class="card-body">
                <!-- Error Alert -->
                <Transition
                  enter-active-class="transition ease-out duration-200"
                  enter-from-class="opacity-0 -translate-y-1"
                  enter-to-class="opacity-100 translate-y-0"
                  leave-active-class="transition ease-in duration-150"
                  leave-from-class="opacity-100 translate-y-0"
                  leave-to-class="opacity-0 -translate-y-1"
                >
                  <div v-if="error" class="alert alert-danger">
                    {{ error }}
                  </div>
                </Transition>

                <form @submit.prevent="register">
                  <!-- Name -->
                  <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      placeholder="Masukkan nama lengkap"
                      class="form-control"
                    />
                  </div>

                  <!-- Email -->
                  <div class="mb-3">
                    <label class="form-label">Alamat Email</label>
                    <input
                      v-model="form.email"
                      type="email"
                      required
                      placeholder="nama@email.com"
                      class="form-control"
                    />
                  </div>

                  <!-- Password -->
                  <div class="mb-3">
                    <label class="form-label">Password</label>
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
                        class="btn btn-outline-secondary"
                        @click="showPassword = !showPassword"
                      >
                        {{ showPassword ? 'Sembunyikan' : 'Tampilkan' }}
                      </button>
                    </div>
                  </div>

                  <!-- Confirm Password -->
                  <div class="mb-3">
                    <label class="form-label">Konfirmasi Password</label>
                    <input
                      v-model="form.password_confirmation"
                      :type="showPassword ? 'text' : 'password'"
                      required
                      placeholder="Ulangi password"
                      class="form-control"
                    />
                  </div>

                  <!-- Submit -->
                  <div class="form-footer">
                    <button
                      type="submit"
                      :disabled="loading"
                      class="btn btn-primary w-100"
                    >
                      <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                      {{ loading ? 'Membuat akun...' : 'Buat Akun' }}
                    </button>
                  </div>
                </form>
              </div>
            </div>

            <div class="text-center text-muted mt-4">
              Sudah punya akun?
              <router-link to="/login" class="link-primary">Masuk</router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()
const loading = ref(false)
const error = ref('')
const showPassword = ref(false)

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

async function register() {
    loading.value = true
    error.value = ''
    try {
        const { data } = await api.post('/auth/register', form)
        localStorage.setItem('token', data.data.access_token)
        localStorage.setItem('user', JSON.stringify(data.data.user))
        router.push({ name: 'dashboard' })
    } catch (e) {
        if (e.response?.status === 422) {
            const errors = e.response.data.errors
            error.value = Object.values(errors).flat().join(' ')
        } else {
            error.value = e.response?.data?.message || 'Pendaftaran gagal'
        }
    } finally {
        loading.value = false
    }
}
</script>
