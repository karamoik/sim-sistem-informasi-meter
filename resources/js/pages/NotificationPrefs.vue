<template>
  <div>
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-3">
      <div>
        <div class="page-pretitle">Settings</div>
        <h2 class="page-title">Pengaturan Notifikasi</h2>
        <p class="text-secondary">Atur notifikasi yang ingin anda terima</p>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="successMsg" class="alert alert-success d-flex align-items-center gap-2">
          &#10003; {{ successMsg }}
        </div>

        <div v-for="(item, key) in prefs" :key="key" class="d-flex align-items-center justify-content-between py-2">
          <div>
            <p class="fw-medium mb-0">{{ labels[key].title }}</p>
            <p class="text-secondary small mb-0">{{ labels[key].desc }}</p>
          </div>
          <div class="form-check form-switch">
            <input
              class="form-check-input"
              type="checkbox"
              role="switch"
              :checked="item"
              @click="toggle(key)"
            />
          </div>
        </div>

        <div class="d-flex justify-content-end pt-3">
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
const prefs = reactive({
    offline_alert: true,
    daily_report: false,
    weekly_report: true,
    meter_warning: true,
})

const labels = {
    offline_alert: { title: 'Alert Meter Offline', desc: 'Notifikasi saat ada meter yang offline' },
    daily_report: { title: 'Laporan Harian', desc: 'Ringkasan status meter setiap hari' },
    weekly_report: { title: 'Laporan Mingguan', desc: 'Ringkasan status meter setiap minggu' },
    meter_warning: { title: 'Meter Warning', desc: 'Notifikasi saat gardu induk memiliki meter offline' },
}

function toggle(key) {
    prefs[key] = !prefs[key]
}

async function fetchPrefs() {
    try {
        const { data } = await api.get('/user/settings/notifications')
        Object.assign(prefs, data.data)
    } catch (e) { console.error(e) }
}

async function save() {
    saving.value = true
    successMsg.value = ''
    try {
        await api.put('/user/settings/notifications', prefs)
        successMsg.value = 'Pengaturan notifikasi berhasil disimpan'
        setTimeout(() => { successMsg.value = '' }, 3000)
    } catch (e) { console.error(e) }
    finally { saving.value = false }
}

onMounted(fetchPrefs)
</script>
