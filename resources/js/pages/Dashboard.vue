<template>
  <div>
    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between mb-4 gap-3">
      <div>
        <h1 class="page-title mb-1">SIM Sistem Informasi Meter</h1>
        <p class="text-muted small">Monitoring KWH meter secara real-time se-Indonesia</p>
      </div>
      <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-2 bg-white border rounded-3 px-3 py-2 small">
          <span class="text-muted">Auto-refresh:</span>
          <button @click="toggleAutoRefresh" :class="['btn btn-sm rounded-pill px-0', autoRefresh ? 'btn-primary' : 'btn-secondary']" style="width:2.5rem;height:1.25rem"></button>
          <select v-model="refreshInterval" class="form-select form-select-sm form-select-auto border-0 bg-transparent text-muted" style="width:auto" :disabled="!autoRefresh">
            <option :value="10">10s</option>
            <option :value="30">30s</option>
            <option :value="60">60s</option>
            <option :value="120">120s</option>
          </select>
        </div>
        <div class="d-flex align-items-center gap-2 small text-muted">
          <span class="badge bg-success rounded-pill" style="width:0.5rem;height:0.5rem;padding:0">&nbsp;</span>
          {{ lastUpdated }}
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row row-deck row-cards g-3 mb-4">
      <div class="col-6 col-md-4 col-xl-2">
        <div class="card">
          <div class="card-body">
            <div class="text-muted small">Total Meter</div>
            <div class="fw-bold fs-2 mt-1">{{ data?.total_meters ?? '...' }}</div>
            <div class="text-muted small">{{ data?.total_gardu_induks ?? '-' }} GI</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2">
        <div class="card">
          <div class="card-body">
            <div class="text-muted small">Online</div>
            <div class="fw-bold fs-2 mt-1 text-blue">{{ data?.meters_online ?? '...' }}</div>
            <div class="text-muted small">{{ data?.percentage_online ?? 0 }}%</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2">
        <div class="card">
          <div class="card-body">
            <div class="text-muted small">Offline</div>
            <div class="fw-bold fs-2 mt-1 text-red">{{ data?.meters_offline ?? '...' }}</div>
            <div class="text-muted small">perlu perhatian</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2">
        <div class="card">
          <div class="card-body">
            <div class="text-muted small">GI Warning</div>
            <div class="fw-bold fs-2 mt-1 text-warning">{{ data?.gardu_induks_with_warning ?? '...' }}</div>
            <div class="text-muted small">ada offline</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2">
        <div class="card">
          <div class="card-body">
            <div class="text-muted small">Titik Ukur</div>
            <div class="fw-bold fs-2 mt-1 text-purple">{{ data?.total_titik_ukurs ?? '...' }}</div>
            <div class="text-muted small">titik aktif</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2">
        <div class="card">
          <div class="card-body">
            <div class="text-muted small">Konfigurasi</div>
            <div class="fw-bold fs-2 mt-1 text-green">{{ data?.active_konfigurasi ?? '...' }}</div>
            <div class="text-muted small">/{{ data?.total_konfigurasi ?? 0 }} aktif</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Map (full width) -->
    <div class="card mb-4">
      <div class="card-header">
        <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2">
          <div>
            <h2 class="card-title mb-1">Peta Jaringan Kelistrikan</h2>
            <p class="text-muted small">Lokasi titik ukur & status meter seluruh Indonesia</p>
          </div>
          <div class="d-flex align-items-center gap-4 small">
            <span class="d-flex align-items-center gap-1"><span class="badge bg-blue" style="width:0.75rem;height:0.75rem;padding:0">&nbsp;</span> <span class="text-muted">Gardu Induk</span></span>
            <span class="d-flex align-items-center gap-1"><span class="badge bg-success" style="width:0.625rem;height:0.625rem;padding:0">&nbsp;</span> <span class="text-muted">Titik Ukur Online</span></span>
            <span class="d-flex align-items-center gap-1"><span class="badge bg-red" style="width:0.625rem;height:0.625rem;padding:0">&nbsp;</span> <span class="text-muted">Titik Ukur Offline</span></span>
            <span class="d-flex align-items-center gap-1"><span class="badge bg-warning" style="width:0.75rem;height:0.75rem;padding:0">&nbsp;</span> <span class="text-muted">GI Warning</span></span>
          </div>
        </div>
      </div>
      <div ref="mapContainer" style="height:520px;width:100%"></div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-body">
            <h3 class="card-title">Status Meter</h3>
            <div style="height:200px" class="d-flex align-items-center justify-content-center">
              <Doughnut v-if="doughnutData" :data="doughnutData" :options="doughnutOptions" />
            </div>
            <div class="d-flex justify-content-center gap-4 mt-3 small">
              <span class="d-flex align-items-center gap-2"><span class="badge bg-blue rounded-pill" style="width:0.75rem;height:0.75rem;padding:0">&nbsp;</span> <span class="text-muted">Online</span></span>
              <span class="d-flex align-items-center gap-2"><span class="badge bg-red rounded-pill" style="width:0.75rem;height:0.75rem;padding:0">&nbsp;</span> <span class="text-muted">Offline</span></span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-body">
            <h3 class="card-title">Meter per Gardu Induk</h3>
            <div style="height:240px">
              <Bar v-if="barData" :data="barData" :options="barOptions" />
            </div>
          </div>
        </div>
      </div>

      <div v-if="data?.meters_by_jenis?.length" class="col-lg-4">
        <div class="card h-100">
          <div class="card-body">
            <h3 class="card-title">Jenis KWH Meter</h3>
            <div class="d-flex flex-column gap-3">
              <div v-for="j in data.meters_by_jenis" :key="j.name" class="d-flex align-items-center gap-3">
                <span class="text-muted small" style="width:8rem">{{ j.name }}</span>
                <div class="flex-fill">
                  <div class="progress" style="height:0.75rem">
                    <div class="progress-bar bg-blue" :style="{ width: jenisPercent(j.count) + '%' }"></div>
                  </div>
                </div>
                <span class="fw-semibold small" style="width:2rem">{{ j.count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="d-flex flex-column flex-sm-row gap-3 mb-4">
      <div class="flex-fill">
        <input v-model="searchQuery" type="text" placeholder="Cari gardu induk, kode, region..." class="form-control" />
      </div>
      <select v-model="filterStatus" class="form-select" style="width:auto">
        <option value="">Semua Status</option>
        <option value="normal">Normal</option>
        <option value="warning">Warning</option>
      </select>
    </div>

    <!-- Table: Gardu Induk -->
    <div class="card mb-4">
      <div class="card-header">
        <h2 class="card-title">Daftar Gardu Induk</h2>
        <p class="text-muted small mt-1">Klik baris untuk melihat detail meter</p>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-striped table-vcenter mb-0">
          <thead>
            <tr>
              <th class="text-uppercase text-muted small">Kode</th>
              <th class="text-uppercase text-muted small">Nama Gardu Induk</th>
              <th class="text-uppercase text-muted small">Region</th>
              <th class="text-uppercase text-muted small text-center">Total</th>
              <th class="text-uppercase text-muted small text-center">Online</th>
              <th class="text-uppercase text-muted small text-center">Offline</th>
              <th class="text-uppercase text-muted small text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="gi in filteredGarduInduks" :key="gi.id">
              <tr @click="toggleExpand(gi.id)" class="cursor-pointer">
                <td class="font-monospace fw-semibold small">{{ gi.code }}</td>
                <td class="fw-semibold">{{ gi.name }}</td>
                <td class="text-muted">{{ gi.region }}</td>
                <td class="text-center fw-semibold">{{ gi.meters_count }}</td>
                <td class="text-center">
                  <span class="badge bg-blue-lt fw-semibold">{{ gi.online_count }}</span>
                </td>
                <td class="text-center">
                  <span :class="['badge fw-semibold', gi.offline_count > 0 ? 'bg-red-lt' : 'bg-secondary-lt']">{{ gi.offline_count }}</span>
                </td>
                <td class="text-center">
                  <span :class="['badge fw-semibold', gi.is_warning ? 'bg-warning-lt' : 'bg-blue-lt']">
                    {{ gi.is_warning ? 'Warning' : 'Normal' }}
                  </span>
                </td>
              </tr>
              <tr v-if="expandedId === gi.id">
                <td colspan="7" class="bg-light">
                  <div v-if="expandedMeters.length" class="row g-3 py-2">
                    <div v-for="meter in expandedMeters" :key="meter.id" :class="['col-12 col-sm-6 col-lg-4']">
                      <div :class="['d-flex align-items-center gap-3 p-3 rounded border', meter.status === 'online' ? 'bg-blue-lt border-blue' : 'bg-red-lt border-red']">
                        <span :class="['badge rounded-pill', meter.status === 'online' ? 'bg-blue' : 'bg-red']" style="width:0.625rem;height:0.625rem;padding:0">&nbsp;</span>
                        <div class="flex-fill min-w-0">
                          <p class="font-monospace fw-semibold small text-truncate mb-0">{{ meter.meter_no }}</p>
                          <p class="text-muted small text-truncate mb-0">{{ meter.unit_name }}</p>
                        </div>
                        <span :class="['small fw-medium', meter.status === 'online' ? 'text-blue' : 'text-red']">{{ meter.status }}</span>
                      </div>
                    </div>
                  </div>
                  <p v-else class="text-muted small fst-italic py-2">Memuat data meter...</p>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recent Offline Meters -->
    <div class="card mb-4">
      <div class="card-header">
        <h2 class="card-title">Meter Offline Terbaru</h2>
        <p class="text-muted small mt-1">10 meter offline terakhir yang terdeteksi</p>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-striped table-vcenter mb-0">
          <thead>
            <tr>
              <th class="text-uppercase text-muted small">Nomor Meter</th>
              <th class="text-uppercase text-muted small">Unit</th>
              <th class="text-uppercase text-muted small">Gardu Induk</th>
              <th class="text-uppercase text-muted small">Terakhir Aktif</th>
              <th class="text-uppercase text-muted small text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="meter in data?.recent_offline_meters" :key="meter.id">
              <td class="font-monospace fw-semibold small">{{ meter.meter_no }}</td>
              <td class="text-muted">{{ meter.unit_name }}</td>
              <td class="text-muted">{{ meter.gardu_induk?.name }}</td>
              <td class="text-muted small">{{ formatDateTime(meter.last_update) }}</td>
              <td class="text-center">
                <span class="badge bg-red-lt fw-semibold">Offline</span>
              </td>
            </tr>
            <tr v-if="!data?.recent_offline_meters?.length">
              <td colspan="5" class="text-center text-muted py-4">
                <div class="mb-2 text-green">All Online</div>
                Semua meter online
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Doughnut, Bar } from 'vue-chartjs'
import {
  Chart as ChartJS, CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend,
} from 'chart.js'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import api from '@/services/api'
import { initEcho, leaveChannel } from '@/echo'

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend)

const mapContainer = ref(null)
let map = null
let markerLayer = null
const data = ref(null)
const searchQuery = ref('')
const filterStatus = ref('')
const expandedId = ref(null)
const expandedMeters = ref([])
const lastUpdated = ref(new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }))
const autoRefresh = ref(true)
const refreshInterval = ref(60)
let refreshTimer = null

function toggleAutoRefresh() {
  autoRefresh.value = !autoRefresh.value
  setupRefreshTimer()
}

function setupRefreshTimer() {
  if (refreshTimer) { clearInterval(refreshTimer); refreshTimer = null }
  if (autoRefresh.value) {
    refreshTimer = setInterval(fetchData, refreshInterval.value * 1000)
  }
}

const filteredGarduInduks = computed(() => {
  if (!data.value?.gardu_induks) return []
  let list = data.value.gardu_induks
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(gi => gi.name.toLowerCase().includes(q) || gi.code.toLowerCase().includes(q) || gi.region.toLowerCase().includes(q))
  }
  if (filterStatus.value === 'normal') list = list.filter(gi => !gi.is_warning)
  if (filterStatus.value === 'warning') list = list.filter(gi => gi.is_warning)
  return list
})

const doughnutData = computed(() => {
  if (!data.value?.meters_by_status) return null
  return {
    labels: data.value.meters_by_status.map(d => d.status),
    datasets: [{ data: data.value.meters_by_status.map(d => d.count), backgroundColor: ['#3b82f6', '#ef4444'], borderColor: ['#ffffff', '#ffffff'], borderWidth: 3, hoverOffset: 6 }],
  }
})

const doughnutOptions = {
  responsive: true, maintainAspectRatio: false, cutout: '65%',
  plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1f2937', titleColor: '#f9fafb', bodyColor: '#d1d5db', padding: 10, cornerRadius: 8 } },
}

const barData = computed(() => {
  if (!data.value?.meters_by_gardu_induk) return null
  const giData = data.value.meters_by_gardu_induk
  return {
    labels: giData.map(d => d.name.replace('GI ', '')),
    datasets: [
      { label: 'Online', data: giData.map(d => d.online), backgroundColor: 'rgba(59, 130, 246, 0.8)', borderRadius: 4, barThickness: 12 },
      { label: 'Offline', data: giData.map(d => d.total - d.online), backgroundColor: 'rgba(239, 68, 68, 0.3)', borderRadius: 4, barThickness: 12 },
    ],
  }
})

const barOptions = {
  responsive: true, maintainAspectRatio: false, indexAxis: 'y',
  plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1f2937', titleColor: '#f9fafb', bodyColor: '#d1d5db', padding: 10, cornerRadius: 8 } },
  scales: {
    x: { stacked: true, grid: { color: 'rgba(156, 163, 175, 0.1)' }, ticks: { color: '#9ca3af', font: { size: 10 }, stepSize: 2 } },
    y: { stacked: true, grid: { display: false }, ticks: { color: '#6b7280', font: { size: 10 } } },
  },
}

function jenisPercent(count) {
  if (!data.value?.total_meters) return 0
  return Math.round((count / data.value.total_meters) * 100)
}

function initMap() {
  map = L.map(mapContainer.value, { center: [-2.5489, 118.0149], zoom: 5, zoomControl: true })
  L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { attribution: '&copy; OpenStreetMap contributors &copy; CARTO', maxZoom: 19 }).addTo(map)
  markerLayer = L.layerGroup().addTo(map)
}

function renderMarkers() {
  markerLayer.clearLayers()
  if (!data.value?.gardu_induks) return

  data.value.gardu_induks.forEach(gi => {
    const color = gi.is_warning ? '#f59e0b' : '#3b82f6'
    const radius = Math.max(8, Math.min(14, gi.meters_count * 0.8))
    const marker = L.circleMarker([gi.latitude, gi.longitude], {
      radius, fillColor: color, color: '#ffffff', weight: 3, fillOpacity: 0.85,
    })
    const statusLabel = gi.is_warning
      ? '<span style="color:#d97706;font-weight:600">Warning</span>'
      : '<span style="color:#2563eb;font-weight:600">Normal</span>'
    marker.bindPopup(`<div style="min-width:200px"><div style="font-weight:700;font-size:14px;margin-bottom:4px">${gi.name}</div><div style="color:#6b7280;font-size:12px;margin-bottom:8px">${gi.code} &middot; ${gi.region}</div><div style="display:flex;gap:12px;font-size:12px;margin-bottom:6px"><div><span style="font-weight:600;color:#111827">${gi.meters_count}</span> <span style="color:#6b7280">Total</span></div><div><span style="font-weight:600;color:#2563eb">${gi.online_count}</span> <span style="color:#6b7280">Online</span></div><div><span style="font-weight:600;color:#ef4444">${gi.offline_count}</span> <span style="color:#6b7280">Offline</span></div></div><div style="font-size:12px">Status: ${statusLabel}</div></div>`)
    marker.addTo(markerLayer)
  })

  if (!data.value?.titik_ukurs) return
  data.value.titik_ukurs.forEach(tu => {
    const color = tu.is_warning ? '#ef4444' : '#22c55e'
    const marker = L.circleMarker([tu.latitude, tu.longitude], {
      radius: 5, fillColor: color, color: '#ffffff', weight: 2, fillOpacity: 0.95,
    })

    const label = tu.is_warning
      ? `<div style="position:relative;display:inline-flex;align-items:center;gap:3px;background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:1px 5px;box-shadow:0 1px 3px rgba(0,0,0,.12);font-size:11px;white-space:nowrap;transform:translate(-50%,-130%);left:0;top:0"><span style="color:#16a34a;font-weight:700">${tu.online_count}</span><span style="color:#9ca3af;font-size:10px">/${tu.kwh_meters_count}</span><span style="background:#fef2f2;color:#dc2626;font-size:9px;padding:0 4px;border-radius:9999px;font-weight:600;margin-left:2px">offline</span></div>`
      : `<div style="position:relative;display:inline-flex;align-items:center;gap:3px;background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:1px 5px;box-shadow:0 1px 3px rgba(0,0,0,.12);font-size:11px;white-space:nowrap;transform:translate(-50%,-130%);left:0;top:0"><span style="color:#16a34a;font-weight:700">${tu.online_count}</span><span style="color:#9ca3af;font-size:10px">/${tu.kwh_meters_count}</span><span style="color:#6b7280;font-size:10px;margin-left:1px">online</span></div>`

    const icon = L.divIcon({
      html: label,
      className: '',
      iconSize: [0, 0],
      iconAnchor: [0, 0],
    })
    marker.setIcon(icon)

    marker.bindPopup(`<div style="min-width:180px"><div style="font-weight:700;font-size:13px;margin-bottom:2px">${tu.name}</div><div style="color:#6b7280;font-size:11px;margin-bottom:6px">${tu.code} &middot; ${tu.gardu_induk?.name || ''}</div><div style="display:flex;gap:10px;font-size:12px"><div><span style="font-weight:600;color:#111827">${tu.kwh_meters_count}</span> <span style="color:#6b7280">Total</span></div><div><span style="font-weight:600;color:#16a34a">${tu.online_count}</span> <span style="color:#6b7280">Online</span></div><div><span style="font-weight:600;color:#ef4444">${tu.offline_count}</span> <span style="color:#6b7280">Offline</span></div></div></div>`)
    marker.addTo(markerLayer)
  })
}

function formatDateTime(dt) {
  if (!dt) return '-'
  return new Date(dt).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

async function toggleExpand(giId) {
  if (expandedId.value === giId) { expandedId.value = null; expandedMeters.value = []; return }
  expandedId.value = giId
  expandedMeters.value = []
  try { const { data: res } = await api.get(`/gardu-induks/${giId}`); expandedMeters.value = res.data.meters || [] } catch (e) { console.error(e) }
}

async function fetchData() {
  try {
    const { data: res } = await api.get('/dashboard/stats')
    data.value = res.data
    lastUpdated.value = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
    renderMarkers()
  } catch (e) { console.error(e) }
}

function listenForUpdates() {
  try {
    const echo = initEcho()
    if (!echo) return

    echo.channel('dashboard')
      .listen('.updated', (event) => {
        console.log('[WS] Dashboard update:', event.type, event.meter_no, event.status)
        fetchData()
      })
  } catch (e) {
    console.warn('WebSocket listener failed:', e)
  }
}

onMounted(async () => {
  initMap()
  await fetchData()
  setupRefreshTimer()
  listenForUpdates()
})

onBeforeUnmount(() => {
  if (refreshTimer) clearInterval(refreshTimer)
  leaveChannel('dashboard')
  if (map) { map.remove(); map = null }
})
</script>
