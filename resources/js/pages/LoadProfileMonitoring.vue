<template>
  <div>
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
      <div>
        <h2 class="page-title">Monitoring Load Profile</h2>
        <p class="text-muted">Beban per titik ukur — bulanan &amp; persentase mingguan</p>
      </div>
      <button class="btn btn-outline-secondary" @click="refreshAll">
        <span v-if="!loading">Muat Ulang</span>
        <span v-else>Memuat...</span>
      </button>
    </div>

    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-md-4">
            <label class="form-label">Titik Ukur</label>
            <select v-model="titikUkurId" class="form-select" @change="onTitikUkurChange">
              <option value="">-- Pilih Titik Ukur --</option>
              <option v-for="tu in titikUkurs" :key="tu.id" :value="tu.id">
                {{ tu.code }} — {{ tu.gardu_induk?.name || tu.name }}
              </option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Periode</label>
            <input v-if="view === 'monthly'" v-model="month" type="month" class="form-control" @change="fetchMonthly" />
            <input v-else v-model="week" type="week" class="form-control" @change="fetchWeekly" />
          </div>
          <div class="col-md-4">
            <div class="btn-group w-100" role="group">
              <button :class="['btn', view === 'monthly' ? 'btn-primary' : 'btn-outline-primary']" @click="switchView('monthly')">
                Per Bulan
              </button>
              <button :class="['btn', view === 'weekly' ? 'btn-primary' : 'btn-outline-primary']" @click="switchView('weekly')">
                Per Minggu (%)
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!titikUkurId" class="card">
      <div class="card-body text-center text-muted py-5">Pilih titik ukur untuk mulai monitoring</div>
    </div>

    <template v-else-if="view === 'monthly'">
      <div v-if="monthly.summary" class="row row-deck row-cards g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="card">
            <div class="card-body">
              <div class="text-muted small">Total Konsumsi (kWh)</div>
              <div class="fs-3 fw-bold">{{ fmt(monthly.summary.total_consumption_kwh, 0) }}</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card">
            <div class="card-body">
              <div class="text-muted small">Rata-rata Beban (MW)</div>
              <div class="fs-3 fw-bold">{{ fmt(monthly.summary.avg_mw, 4) }}</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card">
            <div class="card-body">
              <div class="text-muted small">Beban Puncak (MW)</div>
              <div class="fs-3 fw-bold">{{ fmt(monthly.summary.max_mw, 4) }}</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card">
            <div class="card-body">
              <div class="text-muted small">Rata-rata Power Factor</div>
              <div class="fs-3 fw-bold">{{ fmt(monthly.summary.avg_pf, 4) }}</div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="chartData.labels.length" class="card mb-4">
        <div class="card-header">
          <h3 class="card-title">Konsumsi Harian — {{ month }}</h3>
        </div>
        <div class="card-body">
          <Bar :data="chartData" :options="chartOptions" style="height: 320px;" />
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Rekap Harian {{ month }}</h3>
        </div>
        <div class="table-responsive">
          <table class="table table-hover table-striped table-vcenter">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th class="text-end">Konsumsi (kWh)</th>
                <th class="text-end">Rata-rata (MW)</th>
                <th class="text-end">Puncak (MW)</th>
                <th class="text-end">PF</th>
                <th class="text-end">Frekuensi</th>
                <th class="text-center">Record</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in monthly.days" :key="d.date">
                <td class="fw-medium">{{ d.date }}</td>
                <td class="text-end">{{ fmt(d.consumption_kwh, 2) }}</td>
                <td class="text-end">{{ fmt(d.avg_mw, 4) }}</td>
                <td class="text-end">{{ fmt(d.max_mw, 4) }}</td>
                <td class="text-end">{{ fmt(d.avg_pf, 4) }}</td>
                <td class="text-end">{{ fmt(d.avg_freq, 3) }}</td>
                <td class="text-center">
                  <span class="badge text-bg-secondary">{{ d.records_count }}</span>
                </td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-primary" @click="openDetail(d.date)">Detail</button>
                </td>
              </tr>
              <tr v-if="!monthly.days || !monthly.days.length">
                <td colspan="8" class="text-center text-muted py-4">Tidak ada data load profile pada bulan ini</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <template v-else>
      <div v-if="weekly" class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <div class="text-muted small">Total Minggu (kWh)</div>
              <div class="fs-3 fw-bold">{{ fmt(weekly.week_total_kwh, 2) }}</div>
              <div class="text-muted small mt-1">{{ weekly.start }} s/d {{ weekly.end }}</div>
            </div>
          </div>
        </div>
        <div class="col-md-8">
          <div class="card">
            <div class="card-body">
              <div class="text-muted small mb-2">Persentase beban per hari terhadap total minggu</div>
              <div v-for="d in weekly.days" :key="d.date" class="mb-3">
                <div class="d-flex justify-content-between small mb-1">
                  <span class="fw-medium">{{ d.date }} <span v-if="isToday(d.date)" class="badge text-bg-primary ms-1">Hari Ini</span></span>
                  <span>{{ fmt(d.consumption_kwh, 2) }} kWh — <strong>{{ fmt(d.percentage, 2) }}%</strong></span>
                </div>
                <div class="progress" style="height: 12px;" role="button" @click="openDetail(d.date)">
                  <div class="progress-bar" :class="d.percentage === maxPercentage && d.percentage > 0 ? 'bg-danger' : 'bg-primary'"
                    :style="{ width: Math.max(d.percentage, 0.5) + '%' }"></div>
                </div>
                <div class="text-muted small mt-1">Rata-rata beban: {{ fmt(d.avg_mw, 4) }} MW · {{ d.records_count }} record</div>
              </div>
              <div v-if="weekly.days.every(d => d.records_count === 0)" class="text-center text-muted py-3">
                Tidak ada data pada minggu ini
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <Teleport to="body">
      <div v-if="showDetail" class="modal d-block" style="background:rgba(0,0,0,.5);" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <div>
                <h3 class="modal-title">Detail Load Profile</h3>
                <div class="text-muted small">
                  {{ titikUkurLabel }} — {{ detailStart }} s/d {{ detailEnd }}
                </div>
              </div>
              <button type="button" class="btn-close" @click="showDetail = false"></button>
            </div>
            <div class="modal-body">
              <div class="table-responsive" style="max-height: 60vh;">
                <table class="table table-sm table-hover table-striped table-vcenter text-nowrap">
                  <thead class="sticky-top bg-body">
                    <tr>
                      <th>Date</th>
                      <th class="text-end">kwh_out</th>
                      <th class="text-end">kvarh_out</th>
                      <th class="text-end">kwh_in</th>
                      <th class="text-end">kvarh_in</th>
                      <th class="text-end">kv-r</th>
                      <th class="text-end">kv-s</th>
                      <th class="text-end">kv-t</th>
                      <th class="text-end">ir</th>
                      <th class="text-end">is</th>
                      <th class="text-end">it</th>
                      <th class="text-end">pf</th>
                      <th class="text-end">freq</th>
                      <th class="text-end">mw_out</th>
                      <th class="text-end">mvar_out</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="r in records" :key="r.id">
                      <td class="fw-medium">{{ r.recorded_at }}</td>
                      <td class="text-end">{{ fmt(r.kwh_out, 4) }}</td>
                      <td class="text-end">{{ fmt(r.kvarh_out, 4) }}</td>
                      <td class="text-end">{{ fmt(r.kwh_in, 4) }}</td>
                      <td class="text-end">{{ fmt(r.kvarh_in, 4) }}</td>
                      <td class="text-end">{{ fmt(r.kv_r, 4) }}</td>
                      <td class="text-end">{{ fmt(r.kv_s, 4) }}</td>
                      <td class="text-end">{{ fmt(r.kv_t, 4) }}</td>
                      <td class="text-end">{{ fmt(r.ir, 4) }}</td>
                      <td class="text-end">{{ fmt(r.is, 4) }}</td>
                      <td class="text-end">{{ fmt(r.it, 4) }}</td>
                      <td class="text-end">{{ fmt(r.pf, 4) }}</td>
                      <td class="text-end">{{ fmt(r.freq, 4) }}</td>
                      <td class="text-end">{{ fmt(r.mw_out, 4) }}</td>
                      <td class="text-end">{{ fmt(r.mvar_out, 4) }}</td>
                    </tr>
                    <tr v-if="!records.length">
                      <td colspan="15" class="text-center text-muted py-4">Tidak ada record</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-if="recordsPagination.last_page > 1" class="d-flex align-items-center justify-content-between mt-3">
                <p class="text-muted mb-0 small">
                  Menampilkan {{ recordsPagination.from }}-{{ recordsPagination.to }} dari {{ recordsPagination.total }} record
                </p>
                <div class="d-flex gap-2">
                  <button class="btn btn-sm btn-outline-secondary" :disabled="recordsPagination.current_page <= 1" @click="fetchRecords(recordsPagination.current_page - 1)">
                    &laquo; Sebelumnya
                  </button>
                  <button class="btn btn-sm btn-outline-secondary" :disabled="recordsPagination.current_page >= recordsPagination.last_page" @click="fetchRecords(recordsPagination.current_page + 1)">
                    Berikutnya &raquo;
                  </button>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" @click="showDetail = false">Tutup</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Bar } from 'vue-chartjs'
import {
    Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip, Legend,
} from 'chart.js'
import api from '@/services/api'

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend)

const titikUkurs = ref([])
const titikUkurId = ref('')
const view = ref('monthly')
const month = ref('')
const week = ref('')
const monthly = ref({ days: [], summary: null })
const weekly = ref(null)
const loading = ref(false)
const showDetail = ref(false)
const detailStart = ref('')
const detailEnd = ref('')
const records = ref([])
const recordsPagination = ref({ current_page: 1, last_page: 1, from: 0, to: 0, total: 0 })
const months = ref([])

const titikUkurLabel = computed(() => {
    const tu = titikUkurs.value.find(t => String(t.id) === String(titikUkurId.value))
    return tu ? `${tu.code} — ${tu.gardu_induk?.name || tu.name}` : ''
})

const maxPercentage = computed(() => {
    if (!weekly.value?.days?.length) return 0
    return Math.max(...weekly.value.days.map(d => d.percentage))
})

const chartData = computed(() => {
    const days = monthly.value.days || []
    return {
        labels: days.map(d => d.date.slice(5)),
        datasets: [{
            label: 'Konsumsi (kWh)',
            data: days.map(d => d.consumption_kwh),
            backgroundColor: 'rgba(32, 107, 196, .75)',
            borderRadius: 4,
        }],
    }
})

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: ctx => ` ${Number(ctx.raw).toLocaleString('id-ID')} kWh`,
            },
        },
    },
    scales: {
        y: { beginAtZero: true, ticks: { callback: v => Number(v).toLocaleString('id-ID') } },
    },
}

function fmt(n, d = 2) {
    if (n === null || n === undefined || isNaN(Number(n))) return '0'
    return Number(n).toLocaleString('id-ID', { minimumFractionDigits: d, maximumFractionDigits: d })
}

function isToday(dateStr) {
    const today = new Date()
    const ymd = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`
    return dateStr === ymd
}

function toYmd(d) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}

function mondayOfISOWeek(year, week) {
    const jan4 = new Date(year, 0, 4)
    const day = jan4.getDay() || 7
    const monday = new Date(jan4)
    monday.setDate(jan4.getDate() - day + 1 + (week - 1) * 7)
    return monday
}

function currentISOWeek() {
    const d = new Date()
    const dayNum = d.getDay() || 7
    d.setDate(d.getDate() + 4 - dayNum)
    const yearStart = new Date(d.getFullYear(), 0, 1)
    const week = Math.ceil((((d - yearStart) / 86400000) + 1) / 7)
    return `${d.getFullYear()}-W${String(week).padStart(2, '0')}`
}

function weekToStartDate(weekValue) {
    const m = /^(\d{4})-W(\d{2})$/.exec(weekValue || '')
    if (!m) return null
    return toYmd(mondayOfISOWeek(parseInt(m[1], 10), parseInt(m[2], 10)))
}

async function fetchTitikUkurs() {
    try {
        const { data: res } = await api.get('/titik-ukurs/all')
        titikUkurs.value = res.data
    } catch (e) { console.error(e) }
}

async function fetchMonths() {
    try {
        const params = {}
        if (titikUkurId.value) params.titik_ukur_id = titikUkurId.value
        const { data: res } = await api.get('/monitoring/load-profiles/months', { params })
        months.value = res.data
    } catch (e) { console.error(e) }
}

async function fetchMonthly() {
    if (!titikUkurId.value || !month.value) return
    loading.value = true
    try {
        const { data: res } = await api.get('/monitoring/load-profiles/daily', {
            params: { titik_ukur_id: titikUkurId.value, month: month.value },
        })
        monthly.value = res.data
    } catch (e) { console.error(e) }
    finally { loading.value = false }
}

async function fetchWeekly() {
    const start = weekToStartDate(week.value)
    if (!titikUkurId.value || !start) return
    loading.value = true
    try {
        const { data: res } = await api.get('/monitoring/load-profiles/weekly', {
            params: { titik_ukur_id: titikUkurId.value, start },
        })
        weekly.value = res.data
    } catch (e) { console.error(e) }
    finally { loading.value = false }
}

async function openDetail(date) {
    showDetail.value = true
    detailStart.value = date
    detailEnd.value = date
    await fetchRecords(1)
}

async function fetchRecords(page) {
    try {
        const { data: res } = await api.get('/monitoring/load-profiles/records', {
            params: {
                titik_ukur_id: titikUkurId.value,
                start: detailStart.value,
                end: detailEnd.value,
                page,
                per_page: 100,
            },
        })
        records.value = res.data.records.data
        recordsPagination.value = {
            current_page: res.data.records.current_page,
            last_page: res.data.records.last_page,
            from: res.data.records.from,
            to: res.data.records.to,
            total: res.data.records.total,
        }
    } catch (e) { console.error(e) }
}

async function onTitikUkurChange() {
    await fetchMonths()
    const latest = months.value[0] || ''
    if (latest) {
        month.value = latest
        week.value = currentISOWeek()
        await refreshAll()
    }
}

function switchView(v) {
    view.value = v
    refreshAll()
}

async function refreshAll() {
    if (view.value === 'monthly') await fetchMonthly()
    else await fetchWeekly()
}

onMounted(async () => {
    week.value = currentISOWeek()
    await fetchTitikUkurs()
    await fetchMonths()
    if (titikUkurs.value.length) {
        titikUkurId.value = titikUkurs.value[0].id
        month.value = months.value[0] || ''
        await refreshAll()
    }
})
</script>