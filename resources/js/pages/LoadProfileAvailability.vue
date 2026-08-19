<template>
  <div>
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
      <div>
        <h2 class="page-title">Monitoring Load Profile</h2>
        <p class="text-muted">Ketersediaan slot load profile per titik ukur — breakdown per minggu</p>
      </div>
      <button class="btn btn-outline-secondary" :disabled="loading" @click="fetchData">
        {{ loading ? 'Memuat...' : 'Muat Ulang' }}
      </button>
    </div>

    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-md-4">
            <label class="form-label">Bulan</label>
            <input v-model="month" type="month" class="form-control" @change="fetchData" />
          </div>
          <div class="col-md-8 text-muted small">
            Slot load profile dihitung berdasarkan record unik per titik ukur, asumsi interval pencatatan 30 menit
            ({{ slotsPerDay }} slot/hari). Klik persentase untuk melihat detail titik ukur, meter, dan tabel slot.
          </div>
        </div>
      </div>
    </div>

    <div v-if="titikUkurs.length" class="row row-deck row-cards g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="card">
          <div class="card-body">
            <div class="text-muted small">Total Titik Ukur</div>
            <div class="fs-3 fw-bold">{{ titikUkurs.length }}</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card">
          <div class="card-body">
            <div class="text-muted small">Rata-rata Ketersediaan</div>
            <div class="fs-3 fw-bold" :class="avgClass">{{ fmt(avgPercentage, 1) }}%</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card">
          <div class="card-body">
            <div class="text-muted small">Ketersediaan &ge; 75%</div>
            <div class="fs-3 fw-bold text-success">{{ goodCount }} TU</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card">
          <div class="card-body">
            <div class="text-muted small">Ketersediaan &lt; 75%</div>
            <div class="fs-3 fw-bold text-danger">{{ badCount }} TU</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Ketersediaan Slot — {{ month }}</h3>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-striped table-vcenter">
          <thead>
            <tr>
              <th class="text-center">#</th>
              <th>Kode</th>
              <th>Titik Ukur</th>
              <th>Gardu Induk</th>
              <th v-for="w in weekCount" :key="w" class="text-center">Minggu {{ w }}</th>
              <th class="text-center">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(tu, i) in titikUkurs" :key="tu.id">
              <td class="text-center text-muted">{{ i + 1 }}</td>
              <td class="fw-medium text-nowrap">{{ tu.code }}</td>
              <td>{{ tu.name }}</td>
              <td class="text-nowrap">{{ tu.gardu_induk || '-' }}</td>
              <td v-for="w in tu.weeks" :key="w.week" class="text-center align-middle">
                <span class="badge clickable" :class="badgeClass(w.percentage)" role="button" :title="`Klik untuk detail minggu ${w.week}`" @click="openDetail(tu, w.week)">
                  {{ fmt(w.percentage, 0) }}%
                </span>
                <div class="progress mt-1 mx-auto" style="height: 4px; width: 56px;" :title="`${w.actual_slots} dari ${w.expected_slots} slot`">
                  <div class="progress-bar" :class="barClass(w.percentage)" :style="{ width: Math.max(w.percentage, 2) + '%' }"></div>
                </div>
              </td>
              <td class="text-center align-middle">
                <span class="badge clickable" :class="badgeClass(tu.percentage)" role="button" title="Klik untuk detail seluruh bulan" @click="openDetail(tu)">
                  {{ fmt(tu.percentage, 1) }}%
                </span>
                <div class="progress mt-1 mx-auto" style="height: 6px; width: 72px;">
                  <div class="progress-bar" :class="barClass(tu.percentage)" :style="{ width: Math.max(tu.percentage, 2) + '%' }"></div>
                </div>
              </td>
            </tr>
            <tr v-if="!titikUkurs.length">
              <td colspan="9" class="text-center text-muted py-4">Tidak ada titik ukur pada bulan ini</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="card-footer text-muted small">
        Warna: <span class="badge bg-success">hijau</span> &ge; 90% · <span class="badge bg-primary">biru</span> 75–89% · <span class="badge bg-warning">kuning</span> 50–74% · <span class="badge bg-danger">merah</span> &lt; 50%
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showDetail" class="modal d-block" style="background:rgba(0,0,0,.5);" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <div>
                <h3 class="modal-title">Detail Ketersediaan Slot</h3>
                <div class="text-muted small" v-if="detail.titik_ukur">
                  {{ detail.titik_ukur.code }} — {{ detail.titik_ukur.name }} · {{ detail.start }} s/d {{ detail.end }}
                </div>
              </div>
              <button type="button" class="btn-close" @click="showDetail = false"></button>
            </div>
            <div class="modal-body">
              <div v-if="detailLoading" class="text-center text-muted py-5">Memuat...</div>
              <template v-else-if="detail.titik_ukur">
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <div class="card h-100">
                      <div class="card-header py-2">
                        <h4 class="card-title mb-0 fs-6">Informasi Titik Ukur</h4>
                      </div>
                      <div class="card-body py-3">
                        <dl class="row mb-0 small">
                          <dt class="col-4 text-muted">Kode</dt><dd class="col-8">{{ detail.titik_ukur.code }}</dd>
                          <dt class="col-4 text-muted">Nama</dt><dd class="col-8">{{ detail.titik_ukur.name }}</dd>
                          <dt class="col-4 text-muted">Gardu Induk</dt><dd class="col-8">{{ detail.titik_ukur.gardu_induk || '-' }}</dd>
                          <dt class="col-4 text-muted">Status</dt><dd class="col-8">
                            <span class="badge" :class="detail.titik_ukur.status === 'active' ? 'bg-success' : 'bg-danger'">
                              {{ detail.titik_ukur.status }}
                            </span>
                          </dd>
                          <dt class="col-4 text-muted">Alamat</dt><dd class="col-8">{{ detail.titik_ukur.address || '-' }}</dd>
                          <dt class="col-4 text-muted">Koordinat</dt><dd class="col-8">{{ detail.titik_ukur.latitude || '-' }}, {{ detail.titik_ukur.longitude || '-' }}</dd>
                        </dl>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="card h-100">
                      <div class="card-header py-2">
                        <h4 class="card-title mb-0 fs-6">Ketersediaan Slot</h4>
                      </div>
                      <div class="card-body py-3">
                        <div class="text-muted small">Slot tersedia: <strong>{{ fmt(detail.actual_slots, 0) }}</strong> dari <strong>{{ fmt(detail.expected_slots, 0) }}</strong></div>
                        <div class="progress mt-1" style="height: 10px;">
                          <div class="progress-bar" :class="barClass(detail.percentage)" :style="{ width: Math.max(detail.percentage, 2) + '%' }"></div>
                        </div>
                        <div class="fs-3 fw-bold mt-2" :class="barClass(detail.percentage)">{{ fmt(detail.percentage, 1) }}%</div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="card mb-3">
                  <div class="card-header py-2">
                    <h4 class="card-title mb-0 fs-6">Meter yang Digunakan</h4>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-sm table-striped table-vcenter mb-0">
                      <thead>
                        <tr>
                          <th>No. Meter</th>
                          <th>Unit</th>
                          <th>Jenis Meter</th>
                          <th>Periode Konfigurasi</th>
                          <th>Status</th>
                          <th class="text-end">Last Reading</th>
                          <th class="text-end">Last Update</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="m in detail.meters" :key="m.id">
                          <td class="fw-medium text-nowrap">{{ m.meter_no }}</td>
                          <td>{{ m.unit_name || '-' }}</td>
                          <td>{{ m.jenis_meter || '-' }}</td>
                          <td class="text-nowrap">
                            <template v-if="m.period">{{ m.period }} <span class="text-muted">({{ m.start_date }} s/d {{ m.end_date || 'Sekarang' }})</span></template>
                            <span v-else class="text-muted">Tanpa konfigurasi</span>
                          </td>
                          <td>
                            <span class="badge" :class="m.status === 'online' ? 'bg-success' : 'bg-danger'">{{ m.status }}</span>
                          </td>
                          <td class="text-end">{{ fmt(m.last_reading, 2) }}</td>
                          <td class="text-end">{{ m.last_update || '-' }}</td>
                        </tr>
                        <tr v-if="!detail.meters || !detail.meters.length">
                          <td colspan="8" class="text-center text-muted py-3">Tidak ada meter yang terpasang</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <div class="card">
                  <div class="card-header py-2">
                    <h4 class="card-title mb-0 fs-6">Tabel Slot Load Profile</h4>
                  </div>
                  <div class="table-responsive" style="max-height: 45vh;">
                    <table class="table table-sm table-hover table-striped table-vcenter text-nowrap mb-0">
                      <thead class="sticky-top bg-body">
                        <tr>
                          <th>Waktu</th>
                          <th class="text-end">kwh_out</th>
                          <th class="text-end">kvarh_out</th>
                          <th class="text-end">kwh_in</th>
                          <th class="text-end">kvarh_in</th>
                          <th class="text-end">pf</th>
                          <th class="text-end">freq</th>
                          <th class="text-end">mw_out</th>
                          <th class="text-end">mvar_out</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="r in detailRecords" :key="r.id">
                          <td class="fw-medium">{{ r.recorded_at }}</td>
                          <td class="text-end">{{ fmt(r.kwh_out, 4) }}</td>
                          <td class="text-end">{{ fmt(r.kvarh_out, 4) }}</td>
                          <td class="text-end">{{ fmt(r.kwh_in, 4) }}</td>
                          <td class="text-end">{{ fmt(r.kvarh_in, 4) }}</td>
                          <td class="text-end">{{ fmt(r.pf, 4) }}</td>
                          <td class="text-end">{{ fmt(r.freq, 4) }}</td>
                          <td class="text-end">{{ fmt(r.mw_out, 4) }}</td>
                          <td class="text-end">{{ fmt(r.mvar_out, 4) }}</td>
                        </tr>
                        <tr v-if="!detailRecords.length">
                          <td colspan="9" class="text-center text-muted py-4">Tidak ada record pada rentang ini</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div v-if="recordsPagination.last_page > 1" class="card-footer d-flex align-items-center justify-content-between">
                    <p class="text-muted mb-0 small">
                      Menampilkan {{ recordsPagination.from }}-{{ recordsPagination.to }} dari {{ recordsPagination.total }} record
                    </p>
                    <div class="d-flex gap-2">
                      <button class="btn btn-sm btn-outline-secondary" :disabled="recordsPagination.current_page <= 1" @click="fetchDetail(recordsPagination.current_page - 1)">
                        &laquo; Sebelumnya
                      </button>
                      <button class="btn btn-sm btn-outline-secondary" :disabled="recordsPagination.current_page >= recordsPagination.last_page" @click="fetchDetail(recordsPagination.current_page + 1)">
                        Berikutnya &raquo;
                      </button>
                    </div>
                  </div>
                </div>
              </template>
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
import api from '@/services/api'

const month = ref('')
const loading = ref(false)
const titikUkurs = ref([])
const slotsPerDay = ref(48)
const weekCount = ref(5)

const showDetail = ref(false)
const detailLoading = ref(false)
const detail = ref({ titik_ukur: null, meters: [], actual_slots: 0, expected_slots: 0, percentage: 0, start: '', end: '' })
const detailRecords = ref([])
const recordsPagination = ref({ current_page: 1, last_page: 1, from: 0, to: 0, total: 0 })

const goodCount = computed(() => titikUkurs.value.filter(t => t.percentage >= 75).length)
const badCount = computed(() => titikUkurs.value.filter(t => t.percentage < 75).length)
const avgPercentage = computed(() => {
    if (!titikUkurs.value.length) return 0
    return titikUkurs.value.reduce((s, t) => s + t.percentage, 0) / titikUkurs.value.length
})
const avgClass = computed(() => barClass(avgPercentage.value))

function fmt(n, d = 2) {
    if (n === null || n === undefined || isNaN(Number(n))) return '0'
    return Number(n).toLocaleString('id-ID', { minimumFractionDigits: d, maximumFractionDigits: d })
}

function badgeClass(p) {
    if (p >= 90) return 'bg-success'
    if (p >= 75) return 'bg-primary'
    if (p >= 50) return 'bg-warning'
    return 'bg-danger'
}

function barClass(p) {
    if (p >= 90) return 'bg-success'
    if (p >= 75) return 'bg-primary'
    if (p >= 50) return 'bg-warning'
    return 'bg-danger'
}

function currentMonth() {
    const d = new Date()
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
}

function weekRange(monthVal, week) {
    const [y, m] = monthVal.split('-').map(Number)
    const dayStart = (week - 1) * 7 + 1
    const dayEnd = Math.min(week * 7, new Date(y, m, 0).getDate())
    const pad = n => String(n).padStart(2, '0')
    return { start: `${y}-${pad(m)}-${pad(dayStart)}`, end: `${y}-${pad(m)}-${pad(dayEnd)}` }
}

function monthRange(monthVal) {
    const [y, m] = monthVal.split('-').map(Number)
    const pad = n => String(n).padStart(2, '0')
    return { start: `${y}-${pad(m)}-01`, end: `${y}-${pad(m)}-${pad(new Date(y, m, 0).getDate())}` }
}

async function fetchData() {
    if (!month.value) return
    loading.value = true
    try {
        const { data: res } = await api.get('/monitoring/load-profiles/availability', {
            params: { month: month.value },
        })
        titikUkurs.value = res.data.titik_ukurs
        slotsPerDay.value = res.data.slots_per_day
        weekCount.value = res.data.week_count
    } catch (e) { console.error(e) }
    finally { loading.value = false }
}

function openDetail(tu, week = null) {
    const range = week ? weekRange(month.value, week) : monthRange(month.value)
    showDetail.value = true
    detail.value = { titik_ukur: null, meters: [], actual_slots: 0, expected_slots: 0, percentage: 0, start: range.start, end: range.end }
    detailRecords.value = []
    detailTitikUkurId = tu.id
    fetchDetail(1)
}

let detailTitikUkurId = null

async function fetchDetail(page) {
    if (!detailTitikUkurId) return
    detailLoading.value = true
    try {
        const { data: res } = await api.get('/monitoring/load-profiles/availability-detail', {
            params: {
                titik_ukur_id: detailTitikUkurId,
                start: detail.value.start,
                end: detail.value.end,
                page,
                per_page: 100,
            },
        })
        detail.value = {
            titik_ukur: res.data.titik_ukur,
            meters: res.data.meters,
            actual_slots: res.data.actual_slots,
            expected_slots: res.data.expected_slots,
            percentage: res.data.percentage,
            start: res.data.start,
            end: res.data.end,
        }
        detailRecords.value = res.data.records.data
        recordsPagination.value = {
            current_page: res.data.records.current_page,
            last_page: res.data.records.last_page,
            from: res.data.records.from,
            to: res.data.records.to,
            total: res.data.records.total,
        }
    } catch (e) { console.error(e) }
    finally { detailLoading.value = false }
}

onMounted(() => {
    month.value = currentMonth()
    fetchData()
})
</script>

<style scoped>
.clickable {
    cursor: pointer;
    transition: transform .1s ease;
}
.clickable:hover {
    transform: scale(1.1);
}
</style>