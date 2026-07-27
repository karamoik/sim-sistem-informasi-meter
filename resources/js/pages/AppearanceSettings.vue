<template>
  <div class="container-xl" style="max-width:720px">
    <div class="mb-3">
      <h1 class="page-title mb-1">Pengaturan Tampilan</h1>
      <p class="text-muted small">Sesuaikan tampilan aplikasi sesuai preferensi anda</p>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="successMsg" class="alert alert-success">
          {{ successMsg }}
        </div>

        <!-- Theme -->
        <div class="mb-3">
          <label class="form-label fw-bold">Tema</label>
          <div class="row g-2">
            <div v-for="t in themes" :key="t.value" class="col">
              <button
                type="button"
                @click="form.theme = t.value"
                :class="['btn w-100 d-flex flex-column align-items-center gap-1 p-3 border', form.theme === t.value ? 'btn-primary' : 'btn-outline-secondary']"
              >
                <span v-if="t.value === 'light'">☀</span>
                <span v-else-if="t.value === 'dark'">☾</span>
                <span v-else>⟳</span>
                <span class="small">{{ t.label }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Language -->
        <div class="mb-3">
          <label class="form-label fw-bold">Bahasa</label>
          <select v-model="form.language" class="form-select">
            <option value="id">Bahasa Indonesia</option>
            <option value="en">English</option>
          </select>
        </div>

        <!-- Timezone -->
        <div class="mb-3">
          <label class="form-label fw-bold">Timezone</label>
          <select v-model="form.timezone" class="form-select">
            <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz }}</option>
          </select>
        </div>

        <!-- Compact Mode -->
        <div class="d-flex align-items-center justify-content-between py-2">
          <div>
            <p class="mb-0 fw-bold">Mode Kompak</p>
            <p class="text-muted small mb-0">Tampilkan lebih banyak konten dalam satu layar</p>
          </div>
          <div class="form-check form-switch">
            <input
              class="form-check-input"
              type="checkbox"
              role="switch"
              :checked="form.compact_mode"
              @change="form.compact_mode = !form.compact_mode"
            />
          </div>
        </div>

        <div class="d-flex justify-content-end pt-2">
          <button @click="save" :disabled="saving" class="btn btn-primary">
            {{ saving ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'

const saving = ref(false)
const successMsg = ref('')

const form = reactive({
    theme: 'system',
    language: 'id',
    timezone: 'Asia/Jakarta',
    compact_mode: false,
})

const themes = [
    { value: 'light', label: 'Light' },
    { value: 'dark', label: 'Dark' },
    { value: 'system', label: 'System' },
]

const timezones = [
    'Asia/Jakarta',
    'Asia/Makassar',
    'Asia/Jayapura',
    'Asia/Singapore',
    'UTC',
]

async function fetchAppearance() {
    try {
        const { data } = await api.get('/user/settings/appearance')
        Object.assign(form, data.data)
    } catch (e) { console.error(e) }
}

async function save() {
    saving.value = true
    successMsg.value = ''
    try {
        await api.put('/user/settings/appearance', form)
        successMsg.value = 'Pengaturan tampilan berhasil disimpan'
        setTimeout(() => { successMsg.value = '' }, 3000)
    } catch (e) { console.error(e) }
    finally { saving.value = false }
}

onMounted(fetchAppearance)
</script>
