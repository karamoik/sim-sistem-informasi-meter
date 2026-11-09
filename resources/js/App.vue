<template>
  <template v-if="!token">
    <router-view />
  </template>
  <div v-else class="page">
    <aside class="navbar navbar-vertical navbar-expand-lg">
      <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark">
          <router-link to="/" class="d-flex align-items-center gap-2" title="SIM Sistem Informasi Meter">
            <IconBolt size="24" class="text-primary" />
            <span class="d-flex flex-column lh-1">
              <span class="fw-bold">SIM</span>
              <span class="text-body-secondary fw-normal" style="font-size: .625rem; letter-spacing: .04em;">SISTEM INFORMASI METER</span>
            </span>
          </router-link>
        </h1>
        <div class="collapse navbar-collapse" id="sidebar-menu">
          <ul class="navbar-nav nav-vertical pt-lg-3">
            <li class="nav-item">
              <router-link to="/" class="nav-link" :class="{ active: $route.name === 'dashboard' }">
                <span class="nav-link-icon d-none d-lg-inline-block"><IconGauge size="24" /></span>
                <span class="nav-link-title">Dashboard</span>
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/monitoring/load-profiles" class="nav-link" :class="{ active: $route.name === 'load-profiles' }">
                <span class="nav-link-icon d-none d-lg-inline-block"><IconChartLine size="24" /></span>
                <span class="nav-link-title">Load Profile</span>
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/monitoring/load-profile-availability" class="nav-link" :class="{ active: $route.name === 'load-profile-availability' }">
                <span class="nav-link-icon d-none d-lg-inline-block"><IconChartHistogram size="24" /></span>
                <span class="nav-link-title">Monitoring Load Profile</span>
              </router-link>
            </li>
            <li v-if="canWrite" class="nav-item">
              <router-link to="/kwh-meters" class="nav-link" :class="{ active: $route.name === 'kwh-meters' }">
                <span class="nav-link-icon d-none d-lg-inline-block"><IconList size="24" /></span>
                <span class="nav-link-title">KWH Meter</span>
              </router-link>
            </li>
            <li v-if="canWrite" class="nav-item">
              <a class="nav-link" data-bs-toggle="collapse" href="#sub-master" role="button" :aria-expanded="hasMasterActive">
                <span class="nav-link-icon d-none d-lg-inline-block"><IconBuildingFactory size="24" /></span>
                <span class="nav-link-title">Master Data</span>
                <span class="nav-link-toggle"></span>
              </a>
              <div class="collapse" id="sub-master" :class="{ show: hasMasterActive }">
                <ul class="nav">
                  <li class="nav-item">
                    <router-link to="/gardu-induks" class="nav-link" :class="{ active: $route.name === 'gardu-induks' }">
                      <span class="nav-link-title">Gardu Induk</span>
                    </router-link>
                  </li>
                  <li class="nav-item">
                    <router-link to="/titik-ukurs" class="nav-link" :class="{ active: $route.name === 'titik-ukurs' }">
                      <span class="nav-link-title">Titik Ukur</span>
                    </router-link>
                  </li>
                  <li class="nav-item">
                    <router-link to="/jenis-kwh-meters" class="nav-link" :class="{ active: $route.name === 'jenis-kwh-meters' }">
                      <span class="nav-link-title">Jenis KWH Meter</span>
                    </router-link>
                  </li>
                  <li class="nav-item">
                    <router-link to="/konfigurasi-meters" class="nav-link" :class="{ active: $route.name === 'konfigurasi-meters' }">
                      <span class="nav-link-title">Konfigurasi Meter</span>
                    </router-link>
                  </li>
                </ul>
              </div>
            </li>
            <li v-if="isAdmin" class="nav-item">
              <router-link to="/users" class="nav-link" :class="{ active: $route.name === 'users' }">
                <span class="nav-link-icon d-none d-lg-inline-block"><IconUsers size="24" /></span>
                <span class="nav-link-title">User Management</span>
              </router-link>
            </li>
          </ul>
        </div>
      </div>
    </aside>
    <div class="page-wrapper">
      <header class="navbar navbar-expand-lg navbar-top">
        <div class="container-fluid">
          <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle sidebar">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="d-flex align-items-center gap-2 ms-auto">
            <a href="#" @click.prevent="toggle" :title="isDark ? 'Light mode' : 'Dark mode'" class="nav-link px-2" role="button">
              <IconSun v-if="isDark" size="20" />
              <IconMoon v-else size="20" />
            </a>
            <div class="dropdown">
              <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu" role="button">
                <span class="avatar avatar-sm rounded-circle" :style="{ background: 'linear-gradient(135deg,#4263eb,#7c3aed)' }">
                  {{ (userName || 'U')[0].toUpperCase() }}
                </span>
                <div class="d-none d-xl-block ps-2">
                  <div>{{ userName }}</div>
                  <div class="mt-1 small text-body-secondary">{{ userRole }}</div>
                </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header">
                  <strong>{{ userName }}</strong>
                  <div class="small text-body-secondary">{{ userEmail }}</div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><router-link to="/profile" class="dropdown-item">
                  <IconUser size="20" class="dropdown-item-icon" />
                  Profil
                </router-link></li>
                <li><router-link to="/change-password" class="dropdown-item">
                  <IconLock size="20" class="dropdown-item-icon" />
                  Ganti Password
                </router-link></li>
                <li><router-link to="/linked-devices" class="dropdown-item">
                  <IconDevices size="20" class="dropdown-item-icon" />
                  Linked Devices
                </router-link></li>
                <li><router-link to="/activity-log" class="dropdown-item">
                  <IconClock size="20" class="dropdown-item-icon" />
                  Riwayat Aktivitas
                </router-link></li>
                <li><hr class="dropdown-divider"></li>
                <li><router-link to="/notification-prefs" class="dropdown-item">
                  <IconBell size="20" class="dropdown-item-icon" />
                  Notifikasi
                </router-link></li>
                <li><router-link to="/appearance" class="dropdown-item">
                  <IconPalette size="20" class="dropdown-item-icon" />
                  Tampilan
                </router-link></li>
                <li><router-link to="/2fa/setup" class="dropdown-item">
                  <IconShield size="20" class="dropdown-item-icon" />
                  Keamanan (2FA)
                </router-link></li>
                <li><router-link to="/keyboard-shortcuts" class="dropdown-item">
                  <IconKeyboard size="20" class="dropdown-item-icon" />
                  Pintasan Keyboard
                </router-link></li>
                <li><hr class="dropdown-divider"></li>
                <li><a href="#" class="dropdown-item" @click.prevent="logout">
                  <IconLogout size="20" class="dropdown-item-icon" />
                  Logout
                </a></li>
              </ul>
            </div>
          </div>
        </div>
      </header>
      <div class="page-body">
        <div class="container-fluid">
          <router-view />
        </div>
      </div>
      <footer class="footer">
        <div class="container-fluid">
          <div class="row align-items-center">
            <div class="col text-center">
              <span class="text-body-secondary">SIM Sistem Informasi Meter &copy; {{ new Date().getFullYear() }}</span>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/services/api'
import { useDarkMode } from '@/composables/useDarkMode'
import { IconBolt, IconGauge, IconList, IconBuildingFactory, IconUsers, IconUser, IconLock, IconDevices, IconClock, IconBell, IconPalette, IconShield, IconKeyboard, IconMoon, IconSun, IconLogout, IconChartLine, IconChartHistogram } from '@tabler/icons-vue'

const router = useRouter()
const route = useRoute()
const { isDark, toggle } = useDarkMode()

const token = ref(localStorage.getItem('token'))
const userName = ref('')
const userEmail = ref('')
const userRole = ref('')

const isAdmin = computed(() => userRole.value === 'admin')
const canWrite = computed(() => ['admin', 'operator'].includes(userRole.value))
const masterRoutes = ['gardu-induks', 'titik-ukurs', 'jenis-kwh-meters', 'konfigurasi-meters']
const hasMasterActive = computed(() => masterRoutes.includes(route.name))

function loadUser() {
    token.value = localStorage.getItem('token')
    try {
        const u = JSON.parse(localStorage.getItem('user'))
        userName.value = u?.name || ''
        userEmail.value = u?.email || ''
        userRole.value = u?.role || ''
    } catch {
        userName.value = ''
        userEmail.value = ''
        userRole.value = ''
    }
}

onMounted(() => {
    loadUser()
})
watch(() => route.fullPath, loadUser)
router.afterEach(loadUser)

async function logout() {
    try { await api.post('/auth/logout') } catch {}
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    token.value = ''
    userName.value = ''
    userRole.value = ''
    router.push({ name: 'login' })
}
</script>
