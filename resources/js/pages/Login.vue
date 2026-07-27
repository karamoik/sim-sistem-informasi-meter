<template>
  <div class="page page-center">
    <div class="page wrapper d-flex flex-column">
      <div class="page page-single">
        <div class="container container-tight py-4">
          <div class="text-center mb-4">
            <a href="/" class="navbar-brand navbar-brand-autodark">
              <span class="fs-3 fw-bold">Monitoring Dashboard</span>
            </a>
          </div>
          <div class="card card-md">
            <div class="card-body">
              <h2 class="card-title text-center mb-2">Selamat Datang</h2>
              <p class="text-secondary text-center mb-4">Masuk ke akun anda untuk melanjutkan</p>

              <!-- Session Killed Alert -->
              <div v-if="sessionKilled" class="alert alert-warning alert-dismissible" role="alert">
                <div class="d-flex">
                  <div>Session anda telah ditutup dari device lain.</div>
                </div>
              </div>

              <!-- Error Alert -->
              <Transition
                enter-active-class="fade show"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="fade show"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
              >
                <div v-if="error" class="alert alert-danger alert-dismissible" role="alert">
                  <div class="d-flex">
                    <div>{{ error }}</div>
                  </div>
                </div>
              </Transition>

              <form @submit.prevent="login" autocomplete="off">
                <!-- Email -->
                <div class="mb-3">
                  <label class="form-label">Email address</label>
                  <input
                    v-model="form.email"
                    type="email"
                    class="form-control"
                    required
                    placeholder="you@example.com"
                  />
                </div>

                <!-- Password -->
                <div class="mb-3">
                  <label class="form-label">Password</label>
                  <div class="input-group input-group-flat">
                    <input
                      v-model="form.password"
                      :type="showPassword ? 'text' : 'password'"
                      class="form-control"
                      required
                      placeholder="Enter your password"
                    />
                    <span class="input-group-text">
                      <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="btn btn-link text-decoration-none p-0"
                        style="border: none; background: none;"
                      >
                        {{ showPassword ? '🙈' : '👁' }}
                      </button>
                    </span>
                  </div>
                </div>

                <!-- Math Captcha -->
                <div class="mb-3">
                  <label class="form-label">Verifikasi</label>
                  <div class="bg-light rounded p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                      <small class="text-secondary text-uppercase fw-medium">Math Captcha</small>
                      <button
                        type="button"
                        @click="loadCaptcha"
                        :disabled="captchaLoading"
                        class="btn btn-link btn-sm p-0"
                      >
                        Refresh
                      </button>
                    </div>

                    <div v-if="captchaLoading" class="text-center py-2">
                      <div class="spinner-border spinner-border-sm text-blue" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                    </div>

                    <div v-else-if="captchaQuestion">
                      <div class="text-center mb-3">
                        <span class="badge bg-white text-dark border fs-5 px-4 py-2 font-monospace">
                          {{ captchaQuestion }}
                        </span>
                      </div>
                      <input
                        v-model="form.captcha_answer"
                        type="number"
                        class="form-control text-center font-monospace"
                        required
                        placeholder="Masukkan jawaban..."
                      />
                    </div>

                    <div v-else class="text-center py-2">
                      <button
                        type="button"
                        @click="loadCaptcha"
                        class="btn btn-link btn-sm"
                      >
                        Klik untuk memuat captcha
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Submit -->
                <div class="form-footer">
                  <button
                    type="submit"
                    :disabled="loading || !captchaToken"
                    class="btn btn-primary w-100"
                  >
                    <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                    {{ loading ? 'Signing in...' : 'Sign in' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
          <div class="text-center text-secondary mt-3">
            <router-link to="/forgot-password" class="text-decoration-none">Lupa password?</router-link>
          </div>
          <div class="text-center text-secondary mt-2">
            <p class="small">Hubungi administrator untuk membuat akun baru.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/services/api'
import { useFingerprint } from '@/composables/useFingerprint'

const router = useRouter()
const route = useRoute()
const loading = ref(false)
const error = ref('')
const showPassword = ref(false)
const sessionKilled = ref(false)
const { getFingerprint, loading: fpLoading } = useFingerprint()

const captchaToken = ref('')
const captchaQuestion = ref('')
const captchaLoading = ref(false)

const form = reactive({
    email: '',
    password: '',
    captcha_token: '',
    captcha_answer: '',
    fingerprint_id: '',
    device_info: null,
})

async function loadCaptcha() {
    captchaLoading.value = true
    try {
        const { data } = await api.get('/captcha')
        captchaToken.value = data.data.token
        captchaQuestion.value = data.data.question
        form.captcha_token = data.data.token
        form.captcha_answer = ''
    } catch (e) {
        error.value = 'Gagal memuat captcha'
    } finally {
        captchaLoading.value = false
    }
}

async function login() {
    loading.value = true
    error.value = ''
    try {
        const { fingerprintId, deviceInfo: info } = await getFingerprint()
        form.fingerprint_id = fingerprintId
        form.device_info = info

        const { data } = await api.post('/auth/login', form)

        if (data.data.two_factor_required) {
            localStorage.setItem('two_factor_token', data.data.two_factor_token)
            router.push({ name: '2fa.verify' })
            return
        }

        localStorage.setItem('token', data.data.access_token)
        localStorage.setItem('user', JSON.stringify(data.data.user))
        if (data.data.session_id) {
            localStorage.setItem('session_fingerprint', fingerprintId)
        }
        router.push({ name: 'dashboard' })
    } catch (e) {
        error.value = e.response?.data?.message || 'Login failed'
        if (e.response?.status === 422) {
            loadCaptcha()
        }
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    loadCaptcha()
    if (route.query.reason === 'session_killed') {
        sessionKilled.value = true
    }
})
</script>
