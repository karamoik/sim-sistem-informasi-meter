<template>
  <div class="split-layout">
    <!-- Left Panel: Illustration & Branding -->
    <div class="split-left">
      <div class="left-content">
        <div class="brand-section">
          <div class="brand-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" stroke-width="1.5" stroke="white" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 3l0 7l6 0l-8 11l0 -7l-6 0l8 -11"/></svg>
          </div>
          <h1 class="brand-title">SIM Sistem Informasi Meter</h1>
          <p class="brand-subtitle">Monitoring &amp; pengelolaan data kWh meter terpadu</p>
        </div>

        <div class="illustration-area">
          <svg viewBox="0 0 400 340" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="350" cy="50" r="180" fill="white" opacity="0.03"/>
            <circle cx="50" cy="290" r="140" fill="white" opacity="0.04"/>
            <circle cx="200" cy="170" r="100" fill="white" opacity="0.02"/>
            <path d="M80 280 Q200 60 320 280" stroke="white" stroke-opacity="0.15" stroke-width="4" fill="none" stroke-linecap="round"/>
            <path d="M100 280 Q200 80 300 280" stroke="white" stroke-opacity="0.3" stroke-width="8" fill="none" stroke-linecap="round"/>
            <path d="M120 280 Q200 100 280 280" stroke="white" stroke-opacity="0.5" stroke-width="12" fill="none" stroke-linecap="round"/>
            <line x1="200" y1="170" x2="170" y2="140" stroke="white" stroke-width="3" stroke-linecap="round" stroke-opacity="0.8"/>
            <circle cx="200" cy="170" r="8" fill="white" stroke="rgba(255,255,255,0.2)" stroke-width="4"/>
            <rect x="100" y="220" width="16" height="60" rx="5" fill="white" fill-opacity="0.3"/>
            <rect x="130" y="190" width="16" height="90" rx="5" fill="white" fill-opacity="0.5"/>
            <rect x="160" y="210" width="16" height="70" rx="5" fill="white" fill-opacity="0.4"/>
            <rect x="190" y="170" width="16" height="110" rx="5" fill="white" fill-opacity="0.7"/>
            <rect x="220" y="200" width="16" height="80" rx="5" fill="white" fill-opacity="0.45"/>
            <rect x="250" y="230" width="16" height="50" rx="5" fill="white" fill-opacity="0.35"/>
            <rect x="280" y="215" width="16" height="65" rx="5" fill="white" fill-opacity="0.4"/>
            <circle cx="200" cy="100" r="6" fill="white"/>
            <circle cx="140" cy="150" r="4" fill="white" fill-opacity="0.6"/>
            <circle cx="260" cy="150" r="4" fill="white" fill-opacity="0.6"/>
            <circle cx="170" cy="200" r="3" fill="white" fill-opacity="0.4"/>
            <circle cx="230" cy="200" r="3" fill="white" fill-opacity="0.4"/>
            <line x1="200" y1="100" x2="140" y2="150" stroke="white" stroke-opacity="0.25" stroke-width="1.5"/>
            <line x1="200" y1="100" x2="260" y2="150" stroke="white" stroke-opacity="0.25" stroke-width="1.5"/>
            <line x1="140" y1="150" x2="170" y2="200" stroke="white" stroke-opacity="0.15" stroke-width="1"/>
            <line x1="260" y1="150" x2="230" y2="200" stroke="white" stroke-opacity="0.15" stroke-width="1"/>
          </svg>
        </div>

        <div class="quote-section">
          <p>"Sistem Informasi Meter untuk pemantauan, pencatatan, dan pengelolaan data kWh meter secara terpadu."</p>
        </div>

        <div class="dots-indicator">
          <span class="dot active"></span>
          <span class="dot"></span>
          <span class="dot"></span>
        </div>
      </div>
    </div>

    <!-- Right Panel: Login Form -->
    <div class="split-right">
      <div class="form-wrapper">
        <div class="form-card">
          <div class="form-header">
            <div class="form-logo">
              <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" stroke-width="1.5" stroke="#4263eb" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 3l0 7l6 0l-8 11l0 -7l-6 0l8 -11"/></svg>
            </div>
            <div class="text-primary fw-semibold small mb-1">SIM Sistem Informasi Meter</div>
            <h2>Selamat Datang</h2>
            <p>Masuk ke akun Anda untuk melanjutkan</p>
          </div>

          <div v-if="sessionKilled" class="alert alert-warning" role="alert">
            <div class="d-flex">
              <div>Session Anda telah ditutup dari device lain.</div>
            </div>
          </div>

          <Transition name="fade-slide">
            <div v-if="error" class="alert alert-danger" role="alert">
              <div class="d-flex">
                <div>{{ error }}</div>
              </div>
            </div>
          </Transition>

          <form @submit.prevent="login" autocomplete="off">
            <div class="input-field">
              <div class="input-group-icon">
                <div class="form-floating">
                  <input v-model="form.email" type="email" class="form-control" id="email" placeholder="name@example.com" required autocomplete="off" />
                  <label for="email">Email address</label>
                </div>
                <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 5a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-14z"/><path d="M3 7l9 6l9 -6"/></svg>
              </div>
            </div>

            <div class="input-field">
              <div class="input-group-icon">
                <div class="form-floating">
                  <input v-model="form.password" :type="showPassword ? 'text' : 'password'" class="form-control" id="password" placeholder="Password" required autocomplete="off" />
                  <label for="password">Password</label>
                </div>
                <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z"/><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0"/><path d="M8 11v-4a4 4 0 1 1 8 0v4"/></svg>
                <button type="button" class="password-toggle" @click="showPassword = !showPassword" tabindex="-1">
                  <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828"/><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.42 2.11 -2.25 3"/><path d="M3 3l18 18"/></svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7"/></svg>
                </button>
              </div>
            </div>

            <div class="input-field">
              <div class="captcha-box">
                <div class="captcha-top">
                  <span class="captcha-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M15 9l-6 6"/><path d="M9 9l6 6"/></svg>
                    Math Captcha
                  </span>
                  <button type="button" class="captcha-refresh" @click="loadCaptcha" :disabled="captchaLoading">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"/><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"/></svg>
                    Refresh
                  </button>
                </div>

                <div v-if="captchaLoading" class="captcha-loading">
                  <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                  <span>Memuat captcha...</span>
                </div>

                <div v-else-if="captchaQuestion" class="captcha-body">
                  <div class="captcha-equation">{{ captchaQuestion }}</div>
                  <input v-model="form.captcha_answer" type="number" class="form-control" required placeholder="Masukkan jawaban..." />
                </div>

                <div v-else class="captcha-empty">
                  <button type="button" class="btn btn-sm btn-link" @click="loadCaptcha">
                    Klik untuk memuat captcha
                  </button>
                </div>
              </div>
            </div>

            <button type="submit" class="btn btn-submit" :disabled="loading || !captchaToken">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
              {{ loading ? 'Memproses...' : 'Masuk' }}
            </button>
          </form>

          <div class="form-footer-links">
            <router-link to="/forgot-password">Lupa password?</router-link>
            <p>Hubungi administrator untuk membuat akun baru.</p>
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

<style scoped>
.split-layout {
  display: flex;
  min-height: 100vh;
}

/* ===== Left Panel ===== */
.split-left {
  flex: 0 0 55%;
  max-width: 55%;
  background: linear-gradient(135deg, #4263eb 0%, #7c3aed 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  padding: 48px;
}

.split-left::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,0.06) 1px, transparent 1px);
  background-size: 40px 40px;
}

.left-content {
  position: relative;
  z-index: 1;
  text-align: center;
  max-width: 420px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 32px;
}

.brand-icon-wrap {
  width: 64px;
  height: 64px;
  background: rgba(255,255,255,0.15);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
  backdrop-filter: blur(4px);
}

.brand-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: white;
  margin: 0;
  letter-spacing: -0.02em;
}

.brand-subtitle {
  font-size: 0.95rem;
  color: rgba(255,255,255,0.7);
  margin: 0;
}

.illustration-area {
  width: 100%;
  max-width: 320px;
  opacity: 0.9;
}

.illustration-area svg {
  width: 100%;
  height: auto;
}

.quote-section {
  padding: 20px 24px;
  background: rgba(255,255,255,0.08);
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.1);
}

.quote-section p {
  color: rgba(255,255,255,0.85);
  font-style: italic;
  font-size: 0.9rem;
  line-height: 1.6;
  margin: 0;
}

.dots-indicator {
  display: flex;
  gap: 8px;
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255,255,255,0.3);
}

.dot.active {
  width: 24px;
  border-radius: 4px;
  background: rgba(255,255,255,0.8);
}

/* ===== Right Panel ===== */
.split-right {
  flex: 0 0 45%;
  max-width: 45%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fc;
  padding: 24px;
  overflow-y: auto;
}

.form-wrapper {
  width: 100%;
  max-width: 420px;
}

.form-card {
  background: white;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.06);
  animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(24px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.form-header {
  text-align: center;
  margin-bottom: 28px;
}

.form-logo {
  width: 56px;
  height: 56px;
  background: #f0f3ff;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
}

.form-header h2 {
  font-size: 1.35rem;
  font-weight: 700;
  color: #1a1d26;
  margin: 0 0 6px;
}

.form-header p {
  color: #6c7a91;
  font-size: 0.9rem;
  margin: 0;
}

/* ===== Input Fields ===== */
.input-field {
  margin-bottom: 16px;
}

.input-group-icon {
  position: relative;
}

.input-group-icon .input-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  z-index: 5;
  width: 20px;
  height: 20px;
  color: #9aa7b9;
  pointer-events: none;
  transition: color 0.2s;
}

.input-group-icon:focus-within .input-icon {
  color: #4263eb;
}

.input-group-icon .form-floating > .form-control {
  padding-left: 46px;
  height: 56px;
  font-size: 0.9rem;
}

.input-group-icon .form-floating > label {
  padding-left: 46px;
  padding-top: 16px;
  color: #8a94a6;
  font-size: 0.9rem;
}

.password-toggle {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  z-index: 5;
  background: none;
  border: none;
  padding: 8px;
  cursor: pointer;
  color: #9aa7b9;
  display: flex;
  align-items: center;
  border-radius: 8px;
  transition: all 0.15s;
}

.password-toggle:hover {
  color: #4263eb;
  background: #f0f3ff;
}

/* ===== Captcha ===== */
.captcha-box {
  background: #f8f9fc;
  border: 1px solid #e9edf2;
  border-radius: 12px;
  padding: 16px;
}

.captcha-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.captcha-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #6c7a91;
}

.captcha-refresh {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: none;
  border: none;
  font-size: 0.8rem;
  color: #4263eb;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  transition: background 0.15s;
}

.captcha-refresh:hover {
  background: rgba(66,99,235,0.08);
}

.captcha-refresh:disabled {
  color: #9aa7b9;
  cursor: not-allowed;
}

.captcha-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 0;
  color: #6c7a91;
  font-size: 0.85rem;
}

.captcha-body {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.captcha-equation {
  text-align: center;
  font-size: 1.25rem;
  font-weight: 700;
  font-family: 'Courier New', monospace;
  color: #1a1d26;
  padding: 8px;
  background: white;
  border-radius: 8px;
  border: 1px solid #e9edf2;
}

.captcha-empty {
  text-align: center;
  padding: 8px 0;
}

/* ===== Submit Button ===== */
.btn-submit {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 48px;
  font-size: 0.95rem;
  font-weight: 600;
  margin-top: 8px;
  border: none;
  background: linear-gradient(135deg, #4263eb, #7c3aed);
  color: white;
  border-radius: 10px;
  cursor: pointer;
  transition: opacity 0.2s, transform 0.15s;
}

.btn-submit:hover:not(:disabled) {
  opacity: 0.92;
  transform: translateY(-1px);
}

.btn-submit:active:not(:disabled) {
  transform: translateY(0);
}

.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* ===== Alert Animation ===== */
.fade-slide-enter-active {
  transition: all 0.3s ease-out;
}
.fade-slide-leave-active {
  transition: all 0.2s ease-in;
}
.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(-8px);
}
.fade-slide-leave-to {
  opacity: 0;
}

/* ===== Form Footer ===== */
.form-footer-links {
  text-align: center;
  margin-top: 24px;
}

.form-footer-links a {
  font-size: 0.85rem;
}

.form-footer-links p {
  font-size: 0.8rem;
  color: #8a94a6;
  margin-top: 12px;
}

/* ===== Mobile ===== */
@media (max-width: 991.98px) {
  .split-left {
    display: none;
  }

  .split-right {
    flex: 0 0 100%;
    max-width: 100%;
    background: white;
    padding: 16px;
  }

  .form-card {
    box-shadow: none;
    padding: 24px 16px;
    animation: none;
  }
}
</style>
