<template>
  <div class="min-h-screen flex">
    <!-- Left Panel - Branding -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 relative overflow-hidden">
      <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" viewBox="0 0 400 400" fill="none">
          <circle cx="200" cy="200" r="150" stroke="white" stroke-width="0.5" opacity="0.3"/>
          <circle cx="200" cy="200" r="120" stroke="white" stroke-width="0.5" opacity="0.2"/>
          <circle cx="200" cy="200" r="90" stroke="white" stroke-width="0.5" opacity="0.1"/>
          <circle cx="200" cy="200" r="60" stroke="white" stroke-width="0.5" opacity="0.1"/>
        </svg>
      </div>
      <div class="relative z-10 flex flex-col justify-center px-16 text-white">
        <div class="mb-8">
          <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
          </div>
          <h1 class="text-4xl font-bold mb-4 leading-tight">Lupa Password?</h1>
          <p class="text-blue-100 text-lg leading-relaxed">Masukkan email Anda dan kami akan mengirimkan link untuk reset password.</p>
        </div>
      </div>
    </div>

    <!-- Right Panel - Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
      <div class="w-full max-w-md">
        <div class="text-center mb-8 lg:hidden">
          <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
          </div>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mb-2 text-center lg:text-left">Reset Password</h2>
        <p class="text-gray-500 text-sm mb-8 text-center lg:text-left">
          Masukkan email yang terdaftar, kami akan kirimkan link reset password.
        </p>

        <!-- Error -->
        <div v-if="error" class="flex items-center space-x-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
          <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
          </svg>
          <span class="text-sm">{{ error }}</span>
        </div>

        <!-- Success -->
        <div v-if="successMsg" class="bg-emerald-50 border border-emerald-200 rounded-xl p-6 mb-6">
          <div class="flex items-center space-x-3 mb-4">
            <svg class="w-6 h-6 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-medium text-emerald-700">Link berhasil dikirim!</span>
          </div>
          <p class="text-sm text-emerald-600 mb-4">{{ successMsg }}</p>
          <div v-if="resetLink" class="bg-white rounded-lg border border-emerald-200 p-3">
            <p class="text-xs text-gray-500 mb-2">Link reset password (klik untuk reset):</p>
            <a :href="resetLink" class="text-xs text-blue-600 hover:text-blue-700 break-all font-mono leading-relaxed">
              {{ resetLink }}
            </a>
          </div>
        </div>

        <!-- Form -->
        <form v-if="!successMsg" @submit.prevent="sendLink" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                </svg>
              </div>
              <input
                v-model="form.email"
                type="email"
                required
                placeholder="nama@email.com"
                class="w-full pl-11 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder-gray-400"
              />
            </div>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full flex items-center justify-center space-x-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-2.5 px-4 rounded-xl font-medium text-sm hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-blue-500/25"
          >
            <svg v-if="loading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            <span>{{ loading ? 'Mengirim...' : 'Kirim Link Reset' }}</span>
          </button>
        </form>

        <div class="mt-6 text-center">
          <router-link to="/login" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
            &larr; Kembali ke login
          </router-link>
        </div>
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
