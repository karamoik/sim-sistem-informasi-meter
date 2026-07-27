<template>
  <div class="page page-center">
    <div class="page wrapper py-4">
      <div class="row g-0">
        <!-- Left Panel - Branding -->
        <div class="col-lg-6 d-none d-lg-flex">
          <div class="flex-fill bg-purple text-white p-5 d-flex flex-column justify-content-center"
               style="background: linear-gradient(135deg, #5f3dc4, #7c3aed, #6d28d9); min-height: 100vh;">
            <div class="mb-4">
              <h1 class="display-5 fw-bold mb-3">Join Us<br/>Today</h1>
              <p class="fs-5 text-white-50">
                Create your account and start managing your content with our powerful CRUD application.
              </p>
            </div>
            <div class="d-flex gap-4 text-white-50 small">
              <span>Fast & Secure</span>
              <span>Free Forever</span>
            </div>
          </div>
        </div>

        <!-- Right Panel - Form -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center p-4">
          <div class="w-100" style="max-width: 26rem;">
            <h2 class="card-title mb-1">Create account</h2>
            <p class="text-muted mb-4">Fill in the details below to get started</p>

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
                    <label class="form-label">Full name</label>
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      placeholder="John Doe"
                      class="form-control"
                    />
                  </div>

                  <!-- Email -->
                  <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input
                      v-model="form.email"
                      type="email"
                      required
                      placeholder="you@example.com"
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
                        placeholder="Min. 8 characters"
                        class="form-control"
                      />
                      <button
                        type="button"
                        class="btn btn-outline-secondary"
                        @click="showPassword = !showPassword"
                      >
                        {{ showPassword ? 'Hide' : 'Show' }}
                      </button>
                    </div>
                  </div>

                  <!-- Confirm Password -->
                  <div class="mb-3">
                    <label class="form-label">Confirm password</label>
                    <input
                      v-model="form.password_confirmation"
                      :type="showPassword ? 'text' : 'password'"
                      required
                      placeholder="Repeat your password"
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
                      {{ loading ? 'Creating account...' : 'Create account' }}
                    </button>
                  </div>
                </form>
              </div>
            </div>

            <div class="text-center text-muted mt-4">
              Already have an account?
              <router-link to="/login" class="link-primary">Sign in</router-link>
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
            error.value = e.response?.data?.message || 'Registration failed'
        }
    } finally {
        loading.value = false
    }
}
</script>
